<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Model\Endpoint;

use Cake\TestSuite\TestCase;
use CvoTechnologies\Twitter\Model\Endpoint\UsersEndpoint;

class UsersEndpointTest extends TestCase
{
    public function testInitialize()
    {
        $usersEndpoint = new UsersEndpoint();
        $usersEndpoint->initialize([]);

        $this->assertEquals('user_id', $usersEndpoint->primaryKey());
        $this->assertEquals('screen_name', $usersEndpoint->displayField());
    }
}
