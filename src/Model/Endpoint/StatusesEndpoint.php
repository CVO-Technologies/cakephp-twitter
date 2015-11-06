<?php

namespace CvoTechnologies\Twitter\Model\Endpoint;

use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\Event\EventManager;
use CvoTechnologies\Twitter\Webservice\StreamQuery;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Model\Resource;
use Muffin\Webservice\Query;

class StatusesEndpoint extends Endpoint
{

    use EventDispatcherTrait;

    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->primaryKey('id');
        $this->displayField('text');

        $this->eventManager()->on('Status.placed', function (Event $event, Resource $resource) {
        });
        $this->eventManager()->on('Status.deleted', function (Event $event) {
        });
    }

    /**
     * Creates a new Query for this repository and applies some defaults based on the
     * type of search that was selected.
     *
     * ### Model.beforeFind event
     *
     * Each find() will trigger a `Model.beforeFind` event for all attached
     * listeners. Any listener can set a valid result set using $query
     *
     * @param string $method
     * @param string $type the type of query to perform
     * @param array|\ArrayAccess $options An array that will be passed to Query::applyOptions()
     *
     * @return StreamQuery
     */
    public function stream(
        $method = \Phirehose::METHOD_FILTER, EventManager $eventManager = null, $type = 'all', $options = []
    ) {
        $query = $this->streamQuery();

        if ($eventManager === null) {
            $eventManager = $this->eventManager();
        }

        $query->eventManager($eventManager);
        $query->method($method);

        return $this->callFinder($type, $query, $options);
    }

    /**
     * Creates a new Query instance for this repository
     *
     * @return \CvoTechnologies\Twitter\Webservice\StreamQuery
     */
    public function streamQuery()
    {
        return new StreamQuery($this->webservice(), $this);
    }

    public function findRetweets(Query $query, array $options)
    {
        return $query->where([
            'retweeted_status_id' => $options['status']
        ]);
    }
}
