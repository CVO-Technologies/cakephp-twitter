<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Webservice;

use Cake\Network\Http\Response;
use Cake\TestSuite\TestCase;
use CvoTechnologies\Twitter\Webservice\Driver\Twitter;
use CvoTechnologies\Twitter\Webservice\StatusesWebservice;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Query;

/**
 * @property StatusesWebservice webservice
 */
class StatusesWebserviceTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $driver = new Twitter([]);
        $this->webservice = new StatusesWebservice([
            'driver' => $driver,
            'endpoint' => 'statuses'
        ]);
    }

    public function testGeneralReadDefaultIndex()
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

        $resultSet = $this->webservice->execute($query);
        $this->assertInstanceOf('Muffin\\Webservice\\ResultSet', $resultSet);
        $this->assertInstanceOf('Muffin\\Webservice\\Model\\Resource', $resultSet->first());
        $this->assertEquals('Status 1', $resultSet->first()->text);
        $this->assertEquals(3, $resultSet->count());
    }

    public function testFilterStream()
    {
        $client = $this->getMockBuilder('CvoTechnologies\\Twitter\\Network\\Http\\StreamClient')
            ->setMethods([
                'post'
            ])
            ->getMock();

        $responseGenerator = function () {
            yield new Response([
                'HTTP/1.1 200 Ok'
            ], json_encode([
                'id' => 1,
                'text' => 'Status 1'
            ]));
            yield new Response([
                'HTTP/1.1 200 Ok'
            ], json_encode([
                'id' => 2,
                'text' => 'Status 2'
            ]));
            yield new Response([
                'HTTP/1.1 200 Ok'
            ], json_encode([
                'id' => 3,
                'text' => 'Status 3'
            ]));
        };

        $client
            ->expects($this->once())
            ->method('post')
            ->with('/1.1/statuses/filter.json', [
                'track' => 'twitter',
                'follow' => '1',
                'locations' => '123,123'
            ])
            ->willReturn($responseGenerator());

        $this->webservice->driver()->streamClient($client);

        $query = new Query($this->webservice, new Endpoint());
        $query->read();
        $query->where([
            'word' => 'twitter',
            'user' => 1,
            'location' => '123,123'
        ]);
        $query->applyOptions([
            'streamEndpoint' => 'filter'
        ]);

        $resultSet = $this->webservice->execute($query);
        $this->assertInstanceOf('Cake\\Datasource\\ResultSetDecorator', $resultSet);
        $this->assertEquals('Status 1', $resultSet->first()->text);
        foreach ($resultSet as $resource) {
            $this->assertInstanceOf('Muffin\\Webservice\\Model\\Resource', $resource);
        }
    }

    public function testSampleStream()
    {
        $client = $this->getMockBuilder('CvoTechnologies\\Twitter\\Network\\Http\\StreamClient')
            ->setMethods([
                'get'
            ])
            ->getMock();

        $responseGenerator = function () {
            yield new Response([
                'HTTP/1.1 200 Ok'
            ], json_encode([
                'id' => 1,
                'text' => 'Status 1'
            ]));
            yield new Response([
                'HTTP/1.1 200 Ok'
            ], json_encode([
                'id' => 2,
                'text' => 'Status 2'
            ]));
            yield new Response([
                'HTTP/1.1 200 Ok'
            ], json_encode([
                'id' => 3,
                'text' => 'Status 3'
            ]));
        };

        $client
            ->expects($this->once())
            ->method('get')
            ->with('/1.1/statuses/sample.json')
            ->willReturn($responseGenerator());

        $this->webservice->driver()->streamClient($client);

        $query = new Query($this->webservice, new Endpoint());
        $query->read();
        $query->applyOptions([
            'streamEndpoint' => 'sample'
        ]);

        $resultSet = $this->webservice->execute($query);
        $this->assertInstanceOf('Cake\\Datasource\\ResultSetDecorator', $resultSet);
        $this->assertEquals('Status 1', $resultSet->first()->text);
        foreach ($resultSet as $resource) {
            $this->assertInstanceOf('Muffin\\Webservice\\Model\\Resource', $resource);
        }
    }

    /**
     * @expectedException \CvoTechnologies\Twitter\Webservice\Exception\UnknownStreamEndpointException
     */
    public function testUnknownStreamEndpoint()
    {
        $query = new Query($this->webservice, new Endpoint());
        $query->read();
        $query->applyOptions([
            'streamEndpoint' => 'test123'
        ]);

        $this->webservice->execute($query);
    }

    public function testStatusUpdate()
    {
        $client = $this->getMockBuilder('Cake\\Network\\Http\\Client')
            ->setMethods([
                'post'
            ])
            ->getMock();

        $client
            ->expects($this->once())
            ->method('post')
            ->with('/1.1/statuses/update.json', [
                'status' => 'Test123'
            ])
            ->willReturn(new Response([
                'HTTP/1.1 200 Ok'
            ], json_encode([
                'id' => 1234,
                'text' => 'Test123'
            ])));

        $this->webservice->driver()->client($client);

        $query = new Query($this->webservice, new Endpoint());
        $query->create();
        $query->set([
            'text' => 'Test123'
        ]);

        $resource = $this->webservice->execute($query);
        $this->assertInstanceOf('Muffin\\Webservice\\Model\\Resource', $resource);
        $this->assertEquals($resource->text, 'Test123');
    }

    /**
     * @expectedException \CvoTechnologies\Twitter\Webservice\Exception\UnknownErrorException
     */
    public function testStatusUpdateDuplicate()
    {
        $client = $this->getMockBuilder('Cake\\Network\\Http\\Client')
            ->setMethods([
                'post'
            ])
            ->getMock();

        $client
            ->expects($this->once())
            ->method('post')
            ->with('/1.1/statuses/update.json', [
                'status' => 'Test123'
            ])
            ->willReturn(new Response([
                'HTTP/1.1 403 Forbidden'
            ], json_encode([
                'errors' => [
                    [
                        'message' => 'Status is a duplicate.'
                    ]
                ]
            ])));

        $this->webservice->driver()->client($client);

        $query = new Query($this->webservice, new Endpoint());
        $query->create();
        $query->set([
            'text' => 'Test123'
        ]);

        $resource = $this->webservice->execute($query);
        $this->assertInstanceOf('Muffin\\Webservice\\Model\\Resource', $resource);
        $this->assertEquals($resource->text, 'Test123');
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->webservice);
    }
}
