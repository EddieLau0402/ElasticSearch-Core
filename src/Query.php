<?php
namespace Eddie\ElasticSearch;

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


    public function where()
    {
        $args = func_get_args();
        array_unshift($args, 'must');
        return call_user_func_array([$this, 'setParam'], $args);
    }

    public function orWhere()
    {
        $args = func_get_args();
        array_unshift($args, 'must');
        return call_user_func_array([$this, 'setParam'], $args);
    }

    public function whereNot()
    {
        $args = func_get_args();
        array_unshift($args, 'must');
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

    protected function setParam($type, $param)
    {
        $type = strtolower($type);

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