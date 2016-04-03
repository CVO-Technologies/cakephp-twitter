<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Model\Endpoint;

use Cake\Datasource\ConnectionManager;
use Cake\Network\Http\Response;
use Cake\TestSuite\TestCase;
use CvoTechnologies\StreamEmulation\Emulation\HttpEmulation;
use CvoTechnologies\StreamEmulation\StreamWrapper;
use Muffin\Webservice\Model\EndpointRegistry;
use Muffin\Webservice\Query;
use Psr\Http\Message\RequestInterface;

class StatusesEndpointTest extends TestCase
{
    public function setUp()
    {
        StreamWrapper::overrideWrapper('https');
        ConnectionManager::config('twitter', [
            'className' => 'Muffin\Webservice\Connection',
            'service' => 'CvoTechnologies/Twitter.Twitter'
        ]);
    }

    public function testInitialize()
    {
        $statusesEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.Statuses');

        $this->assertEquals('id', $statusesEndpoint->primaryKey());
        $this->assertEquals('text', $statusesEndpoint->displayField());
    }

    public function testFindFavorite()
    {
        $statusesEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.Statuses');

        $query = $statusesEndpoint->findFavorites($statusesEndpoint->query());

        $this->assertEquals('favorites', $query->webservice()->endpoint());
    }

    public function testFindFavoriteConditions()
    {
        $statusesEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.Statuses');

        $query = $statusesEndpoint->findFavorites($statusesEndpoint->query(), [
            'condition1' => 'value1'
        ]);

        $this->assertEquals('favorites', $query->webservice()->endpoint());
        $this->assertEquals([
            'condition1' => 'value1'
        ], $query->where());
    }

    public function testFindRetweets()
    {
        $statusesEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.Statuses');

        $query = $statusesEndpoint->findRetweets($statusesEndpoint->query(), [
            'status' => 123
        ]);

        $this->assertEquals([
            'retweeted_status_id' => 123
        ], $query->where());
    }

    public function testfindSampleStream()
    {
        $statusesEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.Statuses');

        $query = $statusesEndpoint->findSampleStream($statusesEndpoint->query());

        $this->assertEquals([
            'streamEndpoint' => 'sample',
        ], $query->getOptions());
    }

    public function testfindFilterStream()
    {
        $statusesEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.Statuses');

        $query = $statusesEndpoint->findFilterStream($statusesEndpoint->query(), [
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

    public function testSave()
    {
        StreamWrapper::emulate(HttpEmulation::fromCallable(function (RequestInterface $request) {
            $this->assertEquals('POST', $request->getMethod());
            $this->assertEquals('/1.1/statuses/update.json', $request->getUri()->getPath());

            $this->assertEquals([
                'status' => 'Hello!'
            ], \GuzzleHttp\Psr7\parse_query($request->getBody()->getContents()));

            return new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                'id' => 1234,
                'text' => 'Hello!'
            ]));
        }));

        $statusesEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.Statuses');

        $resource = $statusesEndpoint->newEntity([
            'text' => 'Hello!'
        ]);
        $this->assertInstanceOf('Muffin\Webservice\Model\Resource', $statusesEndpoint->save($resource));
    }

    public function tearDown()
    {
        ConnectionManager::drop('twitter');
        StreamWrapper::restoreWrapper('https');
    }
}
