<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Model\Endpoint;

use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use CvoTechnologies\StreamEmulation\Emulation\HttpEmulation;
use CvoTechnologies\StreamEmulation\StreamWrapper;
use GuzzleHttp\Psr7\Response;
use Muffin\Webservice\Model\EndpointRegistry;

class SavedSearchesEndpointTest extends TestCase
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
        $listsEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.SavedSearches');

        $this->assertEquals('id', $listsEndpoint->primaryKey());
        $this->assertEquals('name', $listsEndpoint->displayField());
    }

    public function testBuildRules()
    {
        $listsEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.SavedSearches');
        $rulesChecker = $this->getMockBuilder('Cake\ORM\RulesChecker')
            ->setMethods(['addCreate'])
            ->getMock();
        $rulesChecker->expects($this->once())
            ->method('addCreate');
        $listsEndpoint->buildRules($rulesChecker);
    }

    public function testCreate()
    {
        StreamWrapper::emulate(HttpEmulation::fromCallable(function () {
            return new Response(200, [], json_encode([
                'id' => 1,
            ]));
        }));

        $listsEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.SavedSearches');
        $list = $listsEndpoint->newEntity([
        ]);
        $this->assertInstanceOf('Muffin\Webservice\Model\Resource', $listsEndpoint->save($list));
    }

    public function tearDown()
    {
        ConnectionManager::drop('twitter');
        StreamWrapper::restoreWrapper('https');
    }
}
