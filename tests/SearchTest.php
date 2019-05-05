<?php
declare(strict_types=1);

namespace Eddie\ElasticSearchCore\Tests;

use Eddie\ElasticSearchCore\Aggregation;
use Eddie\ElasticSearchCore\Client;
use Eddie\ElasticSearchCore\Query;
use Eddie\ElasticSearchCore\Search;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../vendor/autoload.php';

class SearchTest extends TestCase
{
    protected $hosts = [
        '172.16.1.10:9200',
//        'localhost:9200',
    ];
    protected $client;

    /**
     * This method is called before each test.
     */
    protected function setUp()
    {
        //
        $this->createClient();
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
     *
     * @return \Eddie\ElasticSearchCore\Search
     */
    public function testCreateSearch()
    {
        $search = $this->client
//            ->setIndex('test')
//            ->setType('users')
//            ->createSearch()
            ->createSearch(null, ['index' => 'social-1-wx_group_logs'])
        ;

        $this->assertInstanceOf(Search::class, $search);

        return $search;
    }

    /**
     * @depends testCreateSearch
     *
     * @param \Eddie\ElasticSearchCore\Search $search
     */
    public function testSearchWithSimpleQuery(Search $search)
    {
        $ret = $search
            //->setQuery([])
            ->setSize(0)
            ->search()
        ;
        $this->assertArrayHasKey('hits', $ret);
    }

    /**
     * @depends testCreateSearch
     *
     * @param \Eddie\ElasticSearchCore\Search $search
     */
    public function testSearchWithConditionsQuery(Search $search)
    {
        $query = new Query();
        $query
            ->where('merchant_id', 1)
            ->where(['store_id' => 1])
            ->where('event', '2004')
        ;

        $ret = $search
            ->setQuery($query)
            ->search()
        ;

        $this->assertArrayHasKey('hits', $ret);
        $this->assertGreaterThanOrEqual(0, $ret['hits']['total']);
    }

    /**
     * @depends testCreateSearch
     *
     * @param \Eddie\ElasticSearchCore\Search $search
     */
    public function testSearchWithAggregation(Search $search)
    {
        $query = new Query();
        $query
            ->where('merchant_id', 1)
            ->where(['store_id' => 1])
            ->where('event', '2004')
        ;

        $aggs = new Aggregation();
        $aggTermsAlias = 'open_gids';
        $aggs->setTerms('open_gid.keyword', $aggTermsAlias);

        $ret = $search
            ->setQuery($query)
            ->setSize(0)
            ->setAggs($aggs)
            ->search()
        ;

        $this->assertArrayHasKey('aggregations', $ret);
        $this->assertArrayHasKey($aggTermsAlias, $ret['aggregations']);
    }

    /**
     * @depends testCreateSearch
     *
     * @param \Eddie\ElasticSearchCore\Search $search
     */
    public function testSearchWithSubAggregation(Search $search)
    {
        $query = new Query();
        $query
            ->where('merchant_id', 1)
            ->where(['store_id' => 1])
            ->where('event', '2004')
        ;

        $aggs = new Aggregation();
        $aggTermsAlias = 'open_gids';
        $aggs->setTerms('open_gid.keyword', $aggTermsAlias, ['size' => 1000]);
        $aggs->addSubAgg(
            (new Aggregation())
                ->setTerms('store_id', 'store_id', ['size' => 1000])
                ->addSubAgg((new Aggregation())->setTerms('shop_id', 'shop_id', ['size' => 1000]))
        );

        $ret = $search
            ->setQuery($query)
            ->setSize(0)
            ->setAggs($aggs)
            ->search()
        ;

        $this->assertArrayHasKey('aggregations', $ret);
        $this->assertArrayHasKey($aggTermsAlias, $ret['aggregations']);
    }


    private function createClient()
    {
        if (!$this->client) {
            $this->client = new Client([
                'hosts' => $this->hosts
            ]);
        }
    }
}