<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Webservice;

use Cake\Network\Http\Response;
use Cake\TestSuite\TestCase;
use CvoTechnologies\Twitter\Webservice\Driver\Twitter;
use CvoTechnologies\Twitter\Webservice\TwitterWebservice;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Query;

/**
 * @property TwitterWebservice webservice
 */
class TwitterWebserviceTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $driver = new Twitter([]);
        $this->webservice = new TwitterWebservice([
            'driver' => $driver,
            'endpoint' => 'statuses'
        ]);
    }

    public function testGeneralRead()
    {
        $client = $this->getMockBuilder('Cake\\Network\\Http\\Client')
            ->setMethods([
                'get'
            ])
            ->getMock();

        $client
            ->expects($this->once())
            ->method('get')
            ->with('/1.1/statuses/user_timeline.json', [])
            ->willReturn(new Response([
                'HTTP/1.1 200 Ok'
            ], json_encode([
                [
                    'id' => 1,
                    'text' => 'Status 1'
                ],
                [
                    'id' => 2,
                    'text' => 'Status 2'
                ],
                [
                    'id' => 3,
                    'text' => 'Status 3'
                ]
            ])));

        $this->webservice->driver()->client($client);

        $query = new Query($this->webservice, new Endpoint());
        $query->read();
        $query->applyOptions([
            'index' => 'user_timeline'
        ]);

        $resultSet = $this->webservice->execute($query);
        $this->assertInstanceOf('Muffin\\Webservice\\ResultSet', $resultSet);
        $this->assertInstanceOf('Muffin\\Webservice\\Model\\Resource', $resultSet->first());
        $this->assertEquals('Status 1', $resultSet->first()->text);
        $this->assertEquals(3, $resultSet->count());
    }

    public function testGeneralLimit()
    {
        $client = $this->getMockBuilder('Cake\\Network\\Http\\Client')
            ->setMethods([
                'get'
            ])
            ->getMock();

        $client
            ->expects($this->once())
            ->method('get')
            ->with('/1.1/statuses/user_timeline.json', [
                'count' => 2
            ])
            ->willReturn(new Response([
                'HTTP/1.1 200 Ok'
            ], json_encode([
                [
                    'id' => 1,
                    'text' => 'Status 1'
                ],
                [
                    'id' => 2,
                    'text' => 'Status 2'
                ]
            ])));

        $this->webservice->driver()->client($client);

        $query = new Query($this->webservice, new Endpoint());
        $query->read();
        $query->limit(2);
        $query->applyOptions([
            'index' => 'user_timeline'
        ]);

        $resultSet = $this->webservice->execute($query);
        $this->assertInstanceOf('Muffin\\Webservice\\ResultSet', $resultSet);
        $this->assertInstanceOf('Muffin\\Webservice\\Model\\Resource', $resultSet->first());
        $this->assertEquals('Status 1', $resultSet->first()->text);
        $this->assertEquals(2, $resultSet->count());
    }

    public function testGeneralOffset()
    {
        $client = $this->getMockBuilder('Cake\\Network\\Http\\Client')
            ->setMethods([
                'get'
            ])
            ->getMock();

        $client
            ->expects($this->once())
            ->method('get')
            ->with('/1.1/statuses/user_timeline.json', [
                'since_id' => 1
            ])
            ->willReturn(new Response([
                'HTTP/1.1 200 Ok'
            ], json_encode([
                [
                    'id' => 2,
                    'text' => 'Status 2'
                ],
                [
                    'id' => 3,
                    'text' => 'Status 3'
                ]
            ])));

        $this->webservice->driver()->client($client);

        $query = new Query($this->webservice, new Endpoint());
        $query->read();
        $query->offset(1);
        $query->applyOptions([
            'index' => 'user_timeline'
        ]);

        $resultSet = $this->webservice->execute($query);
        $this->assertInstanceOf('Muffin\\Webservice\\ResultSet', $resultSet);
        $this->assertInstanceOf('Muffin\\Webservice\\Model\\Resource', $resultSet->first());
        $this->assertEquals('Status 2', $resultSet->first()->text);
        $this->assertEquals(2, $resultSet->count());
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->webservice);
    }
}
