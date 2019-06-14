<?php
declare(strict_types=1);

namespace Eddie\ElasticSearchCore\Tests;

require_once __DIR__.'/../vendor/autoload.php';

use Eddie\ElasticSearchCore\Query;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    /**
     * This method is called before each test.
     */
    protected function setUp()
    {
        //
    }


    /**
     * @test
     *
     * @author Eddie
     */
    public function testMakeQueryBoolMust()
    {
        //
        $query = new Query();
        $query
            ->where('name', 'Jack')
            ->where(['age' => 23])
            ->where('city', ['Guangzhou', 'Shenzhen', 'Shanghai'])
            ->where(['gender' => ['male', 'female']])
        ;
        $queryArr = $query->format();

        // Struct
        $this->assertArrayHasKey('bool', $queryArr);
        $this->assertArrayHasKey('must', $queryArr['bool']);

        // Content
        $this->assertContains(['term' => ['name' => 'Jack']], $queryArr['bool']['must']);
        $this->assertContains(['term' => ['age' => 23]], $queryArr['bool']['must']);
        $this->assertContains(['terms' => ['city' => ['Guangzhou', 'Shenzhen', 'Shanghai']]], $queryArr['bool']['must']);
        $this->assertContains(['terms' => ['gender' => ['male', 'female']]], $queryArr['bool']['must']);
    }

    /**
     * @test
     *
     * @author Eddie
     */
    public function testMakeQueryBoolMustNot()
    {
        $query = new Query();
        $query
            ->whereNot('name', 'Tom')
            ->whereNot(['age' => 22])
        ;
        $queryArr = $query->format();

        // Struct
        $this->assertArrayHasKey('bool', $queryArr);
        $this->assertArrayHasKey('must_not', $queryArr['bool']);
    }

    /**
     * @test
     *
     * @author Eddie
     */
    public function testMakeQueryGreaterThan()
    {
        $field = 'age';
        $val = 20;

        $query = new Query();

        $ret = $query
            ->whereGt($field, $val)
            ->whereGte($field, $val)

            ->orWhereGt($field, $val)
            ->orWhereGte($field, $val)

            ->whereNotGt($field, $val)
            ->whereNotGte($field, $val)

            ->format()
        ;

        $this->assertArrayHasKey('range', $ret['bool']['must'][0]);
        $this->assertArrayHasKey('range', $ret['bool']['must_not'][0]);
        $this->assertArrayHasKey('range', $ret['bool']['should'][0]);

        $this->assertArrayHasKey('gt', $ret['bool']['must'][0]['range'][$field]);
        $this->assertArrayHasKey('gt', $ret['bool']['must_not'][0]['range'][$field]);
        $this->assertArrayHasKey('gt', $ret['bool']['should'][0]['range'][$field]);

    }

    /**
     * @test
     *
     * @author Eddie
     */
    public function testMakeQueryLighterThan()
    {
        $field = 'age';
        $val = 20;

        $query = new Query();

        $ret = $query
            ->whereLt($field, $val)
            ->whereLte($field, $val)

            ->orWhereLt($field, $val)
            ->orWhereLte($field, $val)

            ->whereNotLt($field, $val)
            ->whereNotLte($field, $val)

            ->format()
        ;

        $this->assertArrayHasKey('range', $ret['bool']['must'][0]);
        $this->assertArrayHasKey('range', $ret['bool']['must_not'][0]);
        $this->assertArrayHasKey('range', $ret['bool']['should'][0]);

        $this->assertArrayHasKey('lt', $ret['bool']['must'][0]['range'][$field]);
        $this->assertArrayHasKey('lt', $ret['bool']['must_not'][0]['range'][$field]);
        $this->assertArrayHasKey('lt', $ret['bool']['should'][0]['range'][$field]);

    }

    /**
     * @test
     *
     * @author Eddie
     */
    public function testMakeQueryBetween()
    {
        $field = 'age';
        $val = [20, 30];

        $query = new Query();

        $ret = $query
            ->whereBetween($field, $val)

            ->orWhereBetween($field, $val)

            ->whereNotBetween($field, $val)

            ->format()
        ;

        $this->assertArrayHasKey('range', $ret['bool']['must'][0]);
        $this->assertArrayHasKey('range', $ret['bool']['must_not'][0]);
        $this->assertArrayHasKey('range', $ret['bool']['should'][0]);

        $this->assertArrayHasKey('gte', $ret['bool']['must'][0]['range'][$field]);
        $this->assertArrayHasKey('lte', $ret['bool']['must'][0]['range'][$field]);

        $this->assertArrayHasKey('gte', $ret['bool']['must_not'][0]['range'][$field]);
        $this->assertArrayHasKey('lte', $ret['bool']['must_not'][0]['range'][$field]);

        $this->assertArrayHasKey('gte', $ret['bool']['should'][0]['range'][$field]);
        $this->assertArrayHasKey('lte', $ret['bool']['should'][0]['range'][$field]);
    }

    /**
     * @test
     *
     * @author Eddie
     */
    public function testMakeQueryHas()
    {
        $field = 'name';

        $query = new Query();

        $ret = $query
            ->whereHas($field)
            ->orWhereHas($field)
            ->whereNotHas($field)
            ->format()
        ;


        $this->assertArrayHasKey('exists', $ret['bool']['must'][0]);
        $this->assertEquals($ret['bool']['must'][0]['exists']['field'], $field);

        $this->assertArrayHasKey('exists', $ret['bool']['must_not'][0]);
        $this->assertEquals($ret['bool']['must_not'][0]['exists']['field'], $field);

        $this->assertArrayHasKey('exists', $ret['bool']['should'][0]);
        $this->assertEquals($ret['bool']['should'][0]['exists']['field'], $field);
    }
}