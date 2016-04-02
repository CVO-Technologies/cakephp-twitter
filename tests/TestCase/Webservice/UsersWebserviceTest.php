<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Webservice;

use Cake\TestSuite\TestCase;
use CvoTechnologies\Twitter\Webservice\Driver\Twitter;
use CvoTechnologies\Twitter\Webservice\UsersWebservice;
use Muffin\Webservice\Model\Endpoint;

/**
 * @property UsersWebservice webservice
 */
class UsersWebserviceTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $driver = new Twitter([]);
        $this->webservice = new UsersWebservice([
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

    public function tearDown()
    {
        parent::tearDown();

        unset($this->webservice);
    }
}
