<?php
declare(strict_types=1);

namespace Eddie\ElasticSearchCore\Tests;

use Eddie\ElasticSearchCore\Aggregation;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../vendor/autoload.php';

class AggregationTest extends TestCase
{
    /**
     * This method is called before each test.
     */
    protected function setUp()
    {
        //
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown()
    {
        //
    }


    /**
     * @test
     */
    public function testMakeTerms()
    {
        $aggs = new Aggregation();

        $alias = 'group_by_gender';
        $aggs->setTerms('gender', $alias, ['size' => 1]);
        $aggsArr = $aggs->format();

        $this->assertArrayHasKey('terms', $aggsArr['aggs'][$alias]);
        $this->assertArrayHasKey('size', $aggsArr['aggs'][$alias]['terms']);
    }

    /**
     * @test
     */
    public function testMakeAvg()
    {
        $aggs = new Aggregation();

        $aggs->setAvg('price');
        $aggsArr = $aggs->format();

        $this->assertArrayHasKey('avg', $aggsArr['aggs']['avg_price']);
    }

    /**
     * @test
     */
    public function testMakeMin()
    {
        $aggs = new Aggregation();

        $aggs->setMin('age');
        $aggsArr = $aggs->format();

        $this->assertArrayHasKey('min', $aggsArr['aggs']['min_age']);
    }

    /**
     * @test
     */
    public function testMakeMax()
    {
        $aggs = new Aggregation();

        $aggs->setMax('age');
        $aggsArr = $aggs->format();

        $this->assertArrayHasKey('max', $aggsArr['aggs']['max_age']);
    }

    /**
     * @test
     */
    public function testMakeTermsWithSubTerms()
    {
        $aggs = new Aggregation();

        $alias = 'group_by_gender';
        $aggs->setTerms('gender', $alias);

        $aggs->addSubAgg(
            (new Aggregation())->setTerms('grade')
        );

        $aggsArr = $aggs->format();

        $this->assertArrayHasKey('aggs', $aggsArr['aggs'][$alias]);
    }
}