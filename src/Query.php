<?php
namespace Eddie\ElasticSearchCore;

use function GuzzleHttp\Psr7\str;

class Query
{
//    [
//        'bool' => [
//            'filter' => [
//                ['term' => ['$field' => '$value']], // single
//
//                ['terms' => ['$field' => ['$value1', '$value2', '$value3'] ]], // multi
//
//                ['range' => ['$field' => [
//                    'gt' => '$value',
//                    'gte' => '$value',
//                    'lt' => '$value',
//                    'lte' => '$value',
//                ] ]],
//
//                ['exists' => ['$field' => '$value']], // exist field
//                ['missing' => ['$field' => '$value']], // miss field
//            ],
//            'must' => [
//                // As same to "filter"
//            ],
//            'must_not' => [
//                // As same to "filter"
//            ],
//            'should' => [
//                // As same to "filter"
//            ],
//        ]
//    ];

    protected $filter  = [];
    protected $must    = [];
    protected $mustNot = [];
    protected $should  = [];

    public function __construct()
    {
        //
    }


    public function __call($method, array $args)
    {
        $method = strtolower($method);

        $mapping = [
            'wherenot' => 'mustNot',
            'orwhere' => 'should',
            'where' => 'must',
        ];
        foreach ($mapping as $k => $v) {
            if (substr($method, 0, strlen($k)) === $k) {
                $type = $v;
                $symbol = substr($method, strlen($k));
                break;
            }
        }
//        var_dump(['type' => $type, 'symbol' => $symbol]);
//        die;

        switch ($symbol) {
            case 'gt':
            case 'gte':
            case 'lt':
            case 'lte':
            case 'between':
                $parmas = ($symbol == 'between') ? $args : [$args[0], $symbol, $args[1]];
                array_push($this->$type, call_user_func_array([$this, 'getRangeTerm'], $parmas));
                return $this;

            default:
                break;
        }
    }

    protected function getRangeTerm()
    {
        // Sample :
        // getRangeTerm($field, 'gt|lt|gte|lte', $val)
        // getRangeTerm($field, [$min, $max])

        $args = func_get_args();
        switch (count($args)) {
            case 2:
                list($field, $val) = $args;
                return [
                    'range' => [
                        $field => ['gte' => $val[0], 'lte' => $val[1]]
                    ]
                ];
                break;

            case 3:
                list($field, $symbol, $val) = $args;
                return [
                    'range' => [
                        $field => [$symbol => $val]
                    ]
                ];
                break;

            default:
                throw new \Exception('illegal');
                break;
        }
    }


    public function where()
    {
        $args = func_get_args();
        array_unshift($args, 'must');
        return call_user_func_array([$this, 'setParam'], $args);
    }

    public function orWhere()
    {
        $args = func_get_args();
        array_unshift($args, 'should');
        return call_user_func_array([$this, 'setParam'], $args);
    }

    public function whereNot()
    {
        $args = func_get_args();
        array_unshift($args, 'mustNot');
        return call_user_func_array([$this, 'setParam'], $args);
    }

    public function format()
    {
        // TODO
        return [
            'bool' => [
                'filter' => $this->filter,
                'must' => $this->must,
                'must_not' => $this->mustNot,
                'should' => $this->should
            ]
        ];
    }

    /**
     * Clean conditions
     *
     * @return $this
     */
    public function flush()
    {
        $this->filter = [];
        $this->must = [];
        $this->mustNot = [];
        $this->should = [];
        return $this;
    }

    protected function setParam($type, $param)
    {
        $args = func_get_args();
        array_shift($args);

        switch (count($args)) {
            case 1: // [k => v, ...]
                if (is_array($args[0])) {
                    foreach ($args[0] as $k => $v) {
                        if (!is_int($k)) $this->where($k, $v);
                    }
                }
                break;

            case 2: // k => v
                $term = [
                    is_array($args[1]) ? 'terms' : 'term' => [$args[0] => $args[1]]
                ];
                array_push($this->$type, $term);
                break;
        }

        return $this;
    }
}