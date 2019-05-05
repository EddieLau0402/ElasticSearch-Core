<?php
namespace Eddie\ElasticSearchCore;

class Search
{
    protected $client;

    /**
     * @var
     */
    protected $query;

    protected $aggs;

    protected $source;

    protected $size = 10;

    protected $from = 0;

    protected $sort;


    public function __construct(\Eddie\ElasticSearchCore\Client $client, $query = null, array $option = [])
    {
        $this->client = $client;
        $this->setQuery($query);
    }

    /**
     * @param array $source
     * @return $this
     */
    public function setSource(array $source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @param number $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = (int)$size;
        return $this;
    }

    /**
     * @param number $from
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = (int)$from;
        return $this;
    }

    /**
     * @param array $sort
     * @return $this
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param $query
     * @return $this
     */
    public function setQuery($query)
    {
        if (is_array($query)) {

            $this->query = $query;

        } elseif (is_object($query) && $query instanceof \Eddie\ElasticSearchCore\Query) {
            $this->query = $query->format();
        }

        return $this;
    }

    public function setAggs($aggs)
    {
        if (is_array($aggs)) {
            $this->aggs = $aggs;
        } elseif (is_object($aggs) && $aggs instanceof \Eddie\ElasticSearchCore\Aggregation) {
            $this->aggs = $aggs->format();
        }
        return $this;
    }


    public function search()
    {
        // Set body
        $body = [];
        if ($this->from) $body['from'] = $this->from;
        if (is_numeric($this->size)) $body['size'] = intval($this->size);
        if ($this->sort) $body['sort'] = $this->sort;

        if (!empty($this->source)) $body['_source'] = $this->source;

        if ($this->query) $body['query'] = $this->query;

        if ($this->aggs) {
            if ($this->aggs instanceof \Eddie\ElasticSearchCore\Aggregation) $this->aggs = $this->aggs->format();
            $body = array_merge($body, $this->aggs);
        }


        // Execute search
        return $this->client->search([
            'index' => $this->client->getIndex(),
            'type' => $this->client->getType(),
            'body' => $body
        ]);
    }


}