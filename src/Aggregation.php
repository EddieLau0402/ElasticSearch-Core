<?php
namespace Eddie\ElasticSearchCore;

class Aggregation
{
    /*
     * Sample of struct :
     *
     * [
     *     '$alias' => [
     *         'terms|avg|min|max|cardinality' => [
     *             'field' => '$key',
     *             'size' => '$size',
     *
     *             // sub-aggregation
     *         ]
     *     ],
     * ];
     */


    private $dirty = false;

    protected $alias;
    protected $type;
    protected $field;

    protected $option = [];

    protected $subAggs = [];


    /**
     * 桶类型
     * @var array
     */
    protected static $bucketTypes = [
        'avg', // 求平均
        'cardinality', // 去重统计, 相当于SQL中的: distinct count
        'extended_stats', // 其他属性,包括: 最大/最小/方差 等
        'geo_bounds', // 坐标
        'geo_centroid', // 中心点
        'max', // 最大值
        'min', // 最小值
        'terms', // 分组
        'sum', // 求和
    ];


    public function __construct()
    {
        //
    }

    protected function setAgg($type, $field, $alias = '', array $option = null)
    {
        if (!$this->dirty) {
            $type = strtolower($type);
            if (!in_array($type, self::$bucketTypes)) throw new \Exception('Aggregation类型非法');

            $this->type = $type;
            $this->field = $field;

            $this->alias = empty($alias) ? "{$type}_{$field}" : $alias;

            if (!empty($option)) $this->option = $option;

            $this->dirty = !$this->dirty;
        }
        return $this;
    }

    public function addSubAgg($agg)
    {
        $this->subAggs[] = $agg;
        return $this;
    }


    public function format()
    {
        $item = ['field' => $this->field];
        if (!empty($this->option)) {
            unset($this->option['field']);
            $item = array_merge($item, $this->option);
        }

        $ret = [
            'aggs' => [
                $this->alias => [
                    $this->type => $item
                ]
            ]
        ];

        // sub-aggregation
        if (!empty($this->subAggs)) {
            $aggs = [];
            foreach ($this->subAggs ?? [] as $subAgg) {
                if ( $subAgg instanceof \Eddie\ElasticSearchCore\Aggregation ) {
                    $aggs = array_merge($aggs, $subAgg->format()['aggs']);
                }
            }
            if (!empty($aggs)) $ret['aggs'][$this->alias]['aggs'] = $aggs;
        }

        return $ret;
    }


    public function __call($name, array $args)
    {
        if ( method_exists($this, $name)) {
            call_user_func_array([$this, $name], $args);
        } else {
            // TODO
        }

    }

    public function setAvg($field, $alias = '', array $option = null)
    {
        return $this->setAgg('avg', $field, $alias, $option);
    }

    public function setMax($field, $alias = '', array $option = null)
    {
        return $this->setAgg('max', $field, $alias, $option);
    }

    public function setMin($field, $alias = '', array $option = null)
    {
        return $this->setAgg('min', $field, $alias, $option);
    }

    public function setTerms($field, $alias = '', array $option = null)
    {
        return $this->setAgg('terms', $field, $alias, $option);
    }

    /**
     * Alias of "setCardinality"
     *
     * @author Eddie
     *
     * @param $field
     * @param string $alias
     * @param array|null $option
     * @return Aggregation
     */
    public function distinct($field, $alias = '', array $option = null)
    {
        return $this->setCardinality($field, $alias, $option);
    }

    public function setCardinality($field, $alias = '', array $option = null)
    {
        return $this->setAgg('cardinality', $field, $alias, $option);
    }

    public function setSum($field, $alias = '', array $option = null)
    {
        return $this->setAgg('sum', $field, $alias, $option);
    }

}