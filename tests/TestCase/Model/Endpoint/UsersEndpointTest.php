<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Model\Endpoint;

use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use Muffin\Webservice\Model\EndpointRegistry;

class UsersEndpointTest extends TestCase
{
    public function setUp()
    {
        ConnectionManager::config('twitter', [
            'className' => 'Muffin\Webservice\Connection',
            'service' => 'CvoTechnologies/Twitter.Twitter'
        ]);
    }

    public function testInitialize()
    {
        $usersEndpoint = EndpointRegistry::get('CvoTechnologies/Twitter.Users');

        $this->assertEquals('user_id', $usersEndpoint->primaryKey());
        $this->assertEquals('screen_name', $usersEndpoint->displayField());
    }

    public function tearDown()
    {
        ConnectionManager::drop('twitter');
    }
}
