<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Webservice;

use Cake\TestSuite\TestCase;
use CvoTechnologies\Twitter\Webservice\Driver\Twitter;
use CvoTechnologies\Twitter\Webservice\UsersWebservice;
use Muffin\Webservice\Model\Endpoint;

class TestUsersWebservice extends UsersWebservice
{
    public function defaultIndex()
    {
        return $this->_defaultIndex();
    }
}

/**
 * @property TestUsersWebservice webservice
 */
class UsersWebserviceTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $driver = new Twitter([]);
        $this->webservice = new TestUsersWebservice([
            'driver' => $driver,
            'endpoint' => 'users'
        ]);
    }

    public function testInitialize()
    {
        $this->assertEquals('/1.1/users/show.json?user_id=123', $this->webservice->nestedResource([
            'id' => 123
        ]));
    }

    public function testDefaultIndex()
    {
        $this->assertEquals('search', $this->webservice->defaultIndex());
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->webservice);
    }
}
