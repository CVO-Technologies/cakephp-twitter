<?php

namespace CvoTechnologies\Twitter\Webservice;

use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Model\Resource;
use Muffin\Webservice\Query;

class StreamQuery extends Query
{

    public function __construct(StreamWebserviceInterface $webservice, Endpoint $endpoint)
    {
        parent::__construct($webservice, $endpoint);

        $this->read();
    }

    /**
     * @param null $eventManager
     *
     * @return \Cake\Event\EventManager|self
     */
    public function eventManager($eventManager = null)
    {
        if ($eventManager === null) {
            return $this->clause('eventManager');
        }

        $this->_parts['eventManager'] = $eventManager;

        return $this;
    }

    public function method($method = null)
    {
        if ($method === null) {
            return $this->clause('method');
        }

        $this->_parts['method'] = $method;

        return $this;
    }

    /**
     * Decorates the results iterator with MapReduce routines and formatters
     *
     * @param \Traversable $result Original results
     * @return \Cake\Datasource\ResultSetInterface
     */
    public function decorateResult(Resource $result)
    {
        $result = $this->_decorateResults(collection([$result]))->toArray();

        if (isset($result[0])) {
            return $result[0];
        }

        return $result;
    }

    protected function _execute()
    {
        return $this->_webservice->executeStream($this, [
            'resourceClass' => $this->endpoint()->resourceClass()
        ]);
    }
}
