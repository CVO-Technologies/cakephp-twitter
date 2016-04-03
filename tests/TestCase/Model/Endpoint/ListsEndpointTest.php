<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Model\Endpoint;

use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use CvoTechnologies\StreamEmulation\Emulation\HttpEmulation;
use CvoTechnologies\StreamEmulation\StreamWrapper;
use GuzzleHttp\Psr7\Response;
use Muffin\Webservice\Model\EndpointRegistry;

class ListsEndpointTest extends TestCase
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
        $listsEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.Lists');

        $this->assertEquals('id', $listsEndpoint->primaryKey());
        $this->assertEquals('name', $listsEndpoint->displayField());
    }

    public function testBuildRules()
    {
        $listsEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.Lists');
        $rulesChecker = $this->getMockBuilder('Cake\ORM\RulesChecker')
            ->setMethods(['addCreate'])
            ->getMock();
        $rulesChecker->expects($this->once())
            ->method('addCreate');
        $listsEndpoint->buildRules($rulesChecker);
    }

    public function testValidationDefault()
    {
        $listsEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.Lists');
        $validator = $listsEndpoint->validationDefault(new Validator());
        $validationRule = $validator->field('mode')->rule('mode');

        $this->assertEquals('inList', $validationRule->get('rule'));
    }

    public function testCreate()
    {
        StreamWrapper::emulate(HttpEmulation::fromCallable(function () {
            return new Response(200, [], json_encode([
                'id' => 1,
                'mode' => 'public'
            ]));
        }));

        $listsEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.Lists');
        $list = $listsEndpoint->newEntity([
            'mode' => 'public'
        ]);
        $this->assertInstanceOf('Muffin\Webservice\Model\Resource', $listsEndpoint->save($list));
    }

    public function tearDown()
    {
        ConnectionManager::drop('twitter');
        StreamWrapper::restoreWrapper('https');
    }
}
