<?php
declare(strict_types=1);

namespace Eddie\ElasticSearch\Tests;

require_once __DIR__.'/../vendor/autoload.php';

use Eddie\ElasticSearch\Query;
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
}