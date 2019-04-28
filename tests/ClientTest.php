<?php
declare(strict_types=1);

namespace Eddie\ElasticSearch\Tests;

use Eddie\ElasticSearch\Client;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../vendor/autoload.php';

class ClientTest extends TestCase
{
    protected $client;

    /**
     * This method is called before each test.
     */
    protected function setUp()
    {
        //
        $this->client = new Client([
            'hosts' => [
                'localhost:9200'
            ]
        ]);
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown()
    {
        //
    }


    public function testGetAndSetIndex()
    {

        $index = 'test';
        $this->client->setIndex($index);

        $this->assertEquals($index, $this->client->getIndex());
    }


    public function testIndexExists()
    {
        $ret = $this->client->indexExists('test');

        $this->assertTrue($ret);
    }


    public function testIndexNotExists()
    {
        $ret = $this->client->indexExists('not_exists');

        $this->assertFalse($ret);
    }

}