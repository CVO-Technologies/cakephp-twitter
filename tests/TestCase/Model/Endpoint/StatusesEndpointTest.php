<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Model\Endpoint;

use Cake\TestSuite\TestCase;
use CvoTechnologies\Twitter\Model\Endpoint\StatusesEndpoint;
use CvoTechnologies\Twitter\Webservice\Driver\Twitter;
use Muffin\Webservice\Connection;
use Muffin\Webservice\Query;

class StatusesEndpointTest extends TestCase
{
    public function testInitialize()
    {
        $statusesEndpoint = new StatusesEndpoint();
        $statusesEndpoint->initialize([]);

        $this->assertEquals('id', $statusesEndpoint->primaryKey());
        $this->assertEquals('text', $statusesEndpoint->displayField());
    }

    public function testFindFavorite()
    {
        $connection = new Connection([
            'service' => 'CvoTechnologies/Twitter.Twitter'
        ]);
        $statusesEndpoint = new StatusesEndpoint([
            'connection' => $connection
        ]);

        $query = new Query($connection->webservice('statuses'), $statusesEndpoint);
        $query = $statusesEndpoint->findFavorites($query);

        $this->assertEquals('favorites', $query->webservice()->endpoint());
    }

    public function testFindFavoriteConditions()
    {
        $connection = new Connection([
            'service' => 'CvoTechnologies/Twitter.Twitter'
        ]);
        $statusesEndpoint = new StatusesEndpoint([
            'connection' => $connection
        ]);

        $query = new Query($connection->webservice('statuses'), $statusesEndpoint);
        $query = $statusesEndpoint->findFavorites($query, [
            'condition1' => 'value1'
        ]);

        $this->assertEquals('favorites', $query->webservice()->endpoint());
        $this->assertEquals([
            'condition1' => 'value1'
        ], $query->where());
    }

    public function testFindRetweets()
    {
        $connection = new Connection([
            'service' => 'CvoTechnologies/Twitter.Twitter'
        ]);
        $statusesEndpoint = new StatusesEndpoint([
            'connection' => $connection
        ]);

        $query = new Query($connection->webservice('statuses'), $statusesEndpoint);
        $query = $statusesEndpoint->findRetweets($query, [
            'status' => 123
        ]);

        $this->assertEquals([
            'retweeted_status_id' => 123
        ], $query->where());
    }

    public function testfindSampleStream()
    {
        $connection = new Connection([
            'service' => 'CvoTechnologies/Twitter.Twitter'
        ]);
        $statusesEndpoint = new StatusesEndpoint([
            'connection' => $connection
        ]);

        $query = new Query($connection->webservice('statuses'), $statusesEndpoint);
        $query = $statusesEndpoint->findSampleStream($query);

        $this->assertEquals([
            'streamEndpoint' => 'sample',
        ], $query->getOptions());
    }

    public function testfindFilterStream()
    {
        $connection = new Connection([
            'service' => 'CvoTechnologies/Twitter.Twitter'
        ]);
        $statusesEndpoint = new StatusesEndpoint([
            'connection' => $connection
        ]);

        $query = new Query($connection->webservice('statuses'), $statusesEndpoint);
        $query = $statusesEndpoint->findFilterStream($query, [
            'words' => [
                'Word1'
            ]
        ]);

        $this->assertEquals([
            'streamEndpoint' => 'filter',
        ], $query->getOptions());
        $this->assertEquals([
            'words' => [
                'Word1'
            ],
        ], $query->where());
    }
}
