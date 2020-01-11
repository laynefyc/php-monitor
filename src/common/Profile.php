<?php
namespace pm\common;
class Profile
{

    const NO_PARENT = '__top__';

    protected $_data;
    protected $_collapsed;
    protected $_indexed;
    protected $_visited;
    protected $_sql = array();

    protected $_keys = array('ct', 'wt', 'cpu', 'mu', 'pmu');
    protected $_exclusiveKeys = array('ewt', 'ecpu', 'emu', 'epmu');
    protected $_functionCount;

    public function __construct($profile)
    {
        $result = array();
        foreach ($profile as $name => $values) {
            list($parent, $func) = $this->splitName($name);
            if (isset($result[$func])) {
                $result[$func] = $this->_sumKeys($result[$func], $values);
                $result[$func]['p'][] = $parent;
            } else {
                foreach($this->_keys as $v){
                    $result[$func][$v] = $values[$v];
                    $result[$func]['e'.$v] = $values[$v];
                }
                $result[$func]['p'] = array($parent);
            }
            // Build the indexed data.
            if ($parent === null) {
                $parent = self::NO_PARENT;
            }
            if (!isset($this->_indexed[$parent])) {
                $this->_indexed[$parent] = array();
            }
            $this->_indexed[$parent][$func] = $values;
        }
        $this->_collapsed = $result;
    }

    public function splitName($name)
    {
        $a = explode("==>", $name);
        return isset($a[1])?$a:array(null, $a[0]);
    }

    /**
     * Sum up the values in $this->_keys;
     *
     * @param array $a The first set of profile data
     * @param array $b The second set of profile data.
     * @return array Merged profile data.
     */
    protected function _sumKeys($a, $b)
    {
        foreach ($this->_keys as $key) {
            if (!isset($a[$key])) {
                $a[$key] = 0;
            }
            $a[$key] += isset($b[$key]) ? $b[$key] : 0;
        }
        return $a;
    }

    public function getProfileBySort()
    {
        $arr = [];
        foreach($this->_collapsed as $k=>$val){
            $arr[] = [
                'id' => $k,
                'ct' => $val['ct'],
                'ecpu' => $val['ecpu'],
                'ewt' => $val['wt'] - $val['ewt'] ,
                'emu' => $val['mu'] - $val['emu'] ,
                'epmu' => $val['pmu'] - $val['epmu']
            ];
        }
        usort($arr, function($a,$b){
            return $a['ewt'] > $b['ewt']?-1:1;
        });
        return $arr;
    }

    /**
     * Get the parent methods for a given symbol.
     *
     * @param string $symbol The name of the function/method to find
     *    parents for.
     * @return array List of parents
     */
    protected function _getParents($symbol)
    {
        $parents = array();
        $current = $this->_collapsed[$symbol];
        foreach ($current['parents'] as $parent) {
            if (isset($this->_collapsed[$parent])) {
                $parents[] = array('function' => $parent) + $this->_collapsed[$parent];
            }
        }
        return $parents;
    }

    /**
     * Find symbols that are the children of the given name.
     *
     * @param string $symbol The name of the function to find children of.
     * @param string $metric The metric to compare $threshold with.
     * @param float $threshold The threshold to exclude functions at. Any
     *   function that represents less than
     * @return array An array of child methods.
     */
    protected function _getChildren($symbol, $metric = null, $threshold = 0)
    {
        $children = array();
        if (!isset($this->_indexed[$symbol])) {
            return $children;
        }

        $total = 0;
        if (isset($metric)) {
            $top = $this->_indexed[self::NO_PARENT];
            // Not always 'main()'
            $mainFunc = current($top);
            $total = $mainFunc[$metric];
        }

        foreach ($this->_indexed[$symbol] as $name => $data) {
            if (
                $metric && $total > 0 && $threshold > 0 &&
                ($this->_collapsed[$name][$metric] / $total) < $threshold
            ) {
                continue;
            }
            $children[] = $data + array('function' => $name);
        }
        return $children;
    }

    /**
     * Generate the approximate exclusive values for each metric.
     *
     * We get a==>b as the name, we need a key for a and b in the array
     * to get exclusive values for A we need to subtract the values of B (and any other children);
     * call passing in the entire profile only, should return an array of
     * functions with their regular timing, and exclusive numbers inside ['exclusive']
     *
     * Consider:
     *              /---c---d---e
     *          a -/----b---d---e
     *
     * We have c==>d and b==>d, and in both instances d invokes e, yet we will
     * have but a single d==>e result. This is a known and documented limitation of XHProf
     *
     * We have one d==>e entry, with some values, including ct=2
     * We also have c==>d and b==>d
     *
     * We should determine how many ==>d options there are, and equally
     * split the cost of d==>e across them since d==>e represents the sum total of all calls.
     *
     * Notes:
     *  Function names are not unique, but we're merging them
     *
     * @return Xhgui_Profile A new instance with exclusive data set.
     */
    public function calculateSelf()
    {
        // Init exclusive values
        foreach ($this->_collapsed as &$data) {
            $data['ewt'] = $data['wt'];
            $data['emu'] = $data['mu'];
            $data['ecpu'] = $data['cpu'];
            $data['ect'] = $data['ct'];
            $data['epmu'] = $data['pmu'];
        }
        unset($data);

        // Go over each method and remove each childs metrics
        // from the parent.
        foreach ($this->_collapsed as $name => $data) {
            $children = $this->_getChildren($name);
            foreach ($children as $child) {
                $this->_collapsed[$name]['ewt'] -= $child['wt'];
                $this->_collapsed[$name]['emu'] -= $child['mu'];
                $this->_collapsed[$name]['ecpu'] -= $child['cpu'];
                $this->_collapsed[$name]['ect'] -= $child['ct'];
                $this->_collapsed[$name]['epmu'] -= $child['pmu'];
            }
        }
        return $this;
    }

    /**
     * Sort data by a dimension.
     *
     * @param string $dimension The dimension to sort by.
     * @param array $data The data to sort.
     * @return array The sorted data.
     */
    public function sort($dimension, $data)
    {
        $sorter = function ($a, $b) use ($dimension) {
            if ($a[$dimension] == $b[$dimension]) {
                return 0;
            }
            return $a[$dimension] > $b[$dimension] ? -1 : 1;
        };
        uasort($data, $sorter);
        return $data;
    }


    /**
     * Get the max value for any give metric.
     *
     * @param string $metric The metric to get a max value for.
     */
    protected function _maxValue($metric)
    {
        return array_reduce(
            $this->_collapsed,
            function ($result, $item) use ($metric) {
                if ($item[$metric] > $result) {
                    return $item[$metric];
                }
                return $result;
            },
            0
        );
    }

    /**
     * Return a structured array suitable for generating flamegraph visualizations.
     *
     * Functions whose inclusive time is less than 1% of the total time will
     * be excluded from the callgraph data.
     *
     * @return array
     */
    public function getFlamegraph($metric = 'wt', $threshold = 0.01)
    {
        $valid = array_merge($this->_keys, $this->_exclusiveKeys);
        if (!in_array($metric, $valid)) {
            throw new \Exception("Unknown metric '$metric'. Cannot generate flamegraph.");
        }
        $this->calculateSelf();

        // Non exclusive metrics are always main() because it is the root call scope.
        if (in_array($metric, $this->_exclusiveKeys)) {
            $main = $this->_maxValue($metric);
        } else {
            $main = $this->_collapsed['main()'][$metric];
        }

        $this->_visited = $this->_nodes = $this->_links = array();
        $flamegraph = $this->_flamegraphData(self::NO_PARENT, $main, $metric, $threshold);
        return array('data' => array_shift($flamegraph), 'sort' => $this->_visited);
    }

    protected function _flamegraphData($parentName, $main, $metric, $threshold, $parentIndex = null)
    {
        $result = array();
        // Leaves don't have children, and don't have links/nodes to add.
        if (!isset($this->_indexed[$parentName])) {
            return $result;
        }

        $children = $this->_indexed[$parentName];
        foreach ($children as $childName => $metrics) {
            $metrics = $this->_collapsed[$childName];
            if ($metrics[$metric] / $main <= $threshold) {
                continue;
            }
            $current = array(
                'name' => $childName,
                'value' => $metrics[$metric],
            );
            $revisit = false;

            // Keep track of which nodes we've visited and their position
            // in the node list.
            if (!isset($this->_visited[$childName])) {
                $index = count($this->_nodes);
                $this->_visited[$childName] = $index;
                $this->_nodes[] = $current;
            } else {
                $revisit = true;
                $index = $this->_visited[$childName];
            }

            // If the current function has more children,
            // walk that call subgraph.
            if (isset($this->_indexed[$childName]) && !$revisit) {
                $grandChildren = $this->_flamegraphData($childName, $main, $metric, $threshold, $index);
                if (!empty($grandChildren)) {
                    $current['children'] = $grandChildren;
                }
            }

            $result[] = $current;
        }
        return $result;
    }
}
