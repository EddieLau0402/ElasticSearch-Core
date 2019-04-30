<?php
namespace Eddie\ElasticSearch;

use Elasticsearch\ClientBuilder;

class Client
{
    /**
     * @var
     */
    protected $client;

    /**
     * @var
     */
    protected $index;

    /**
     * @var
     */
    protected $type;



    public function __construct(array $option)
    {
        $clientBuilder = ClientBuilder::create();

        // Set option
        $clientBuilder
            ->setHosts($option['hosts'])
            //->setLogger()
            //->setRetries()
        ;

        $this->setType($option['type'] ?? 'doc');

        $this->client = $clientBuilder->build();

        // Check - ping
        if (! $this->client->ping() ) {
            // TODO :
        }
    }

    public function createSearch($query = null, array $option = [])
    {
        // TODO : Setting option...
        if (isset($option['index'])) $this->setIndex($option['index']);
        if (isset($option['type'])) $this->setType($option['type']);

        return new Search($this, $query);
    }

    public function search(array $param)
    {
        return $this->client->search($param);
    }


    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    public function createIndex($index, array $option = [])
    {
        //
    }

    public function indexExists($index = '')
    {
        if (!empty($index)) $this->setIndex($index);
        return $this->client->indices()->exists(['index' => $this->getIndex()]);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

}