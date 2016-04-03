<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Webservice;

use Cake\Network\Http\Response;
use Cake\TestSuite\TestCase;
use CvoTechnologies\StreamEmulation\Emulation\HttpEmulation;
use CvoTechnologies\StreamEmulation\Emulator\HttpEmulator;
use CvoTechnologies\StreamEmulation\StreamWrapper;
use CvoTechnologies\Twitter\Webservice\Driver\Twitter;
use CvoTechnologies\Twitter\Webservice\TwitterWebservice;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Query;
use Psr\Http\Message\RequestInterface;

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

        StreamWrapper::overrideWrapper('https');
    }

    public function testGeneralRead()
    {
        StreamWrapper::emulate(HttpEmulation::fromCallable(function (RequestInterface $request) {
            $this->assertEquals('GET', $request->getMethod());
            $this->assertEquals('/1.1/statuses/user_timeline.json', $request->getUri()->getPath());

            return new \GuzzleHttp\Psr7\Response(200, [], json_encode([
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
            ]));
        }));

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
        StreamWrapper::emulate(HttpEmulation::fromCallable(function (RequestInterface $request) {
            $this->assertEquals('GET', $request->getMethod());
            $this->assertEquals('/1.1/statuses/user_timeline.json', $request->getUri()->getPath());

            $this->assertEquals([
                'count' => 2
            ], \GuzzleHttp\Psr7\parse_query($request->getUri()->getQuery()));

            return new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                [
                    'id' => 1,
                    'text' => 'Status 1'
                ],
                [
                    'id' => 2,
                    'text' => 'Status 2'
                ]
            ]));
        }));

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
        StreamWrapper::emulate(HttpEmulation::fromCallable(function (RequestInterface $request) {
            $this->assertEquals('GET', $request->getMethod());
            $this->assertEquals('/1.1/statuses/user_timeline.json', $request->getUri()->getPath());

            $this->assertEquals([
                'since_id' => 1
            ], \GuzzleHttp\Psr7\parse_query($request->getUri()->getQuery()));

            return new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                [
                    'id' => 2,
                    'text' => 'Status 2'
                ],
                [
                    'id' => 3,
                    'text' => 'Status 3'
                ]
            ]));
        }));

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

    public function testCreate()
    {
        StreamWrapper::emulate(HttpEmulation::fromCallable(function (RequestInterface $request) {
            $this->assertEquals('POST', $request->getMethod());
            $this->assertEquals('/1.1/statuses/create.json', $request->getUri()->getPath());

            $this->assertEquals([
                'status' => 'Status 2'
            ], \GuzzleHttp\Psr7\parse_query($request->getBody()->getContents()));

            return new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                'id' => 2,
                'text' => 'Status 2'
            ]));
        }));

        $query = new Query($this->webservice, new Endpoint());
        $query->action(Query::ACTION_CREATE);
        $query->set([
            'status' => 'Status 2'
        ]);

        $result = $this->webservice->execute($query);
        $this->assertInstanceOf('Muffin\Webservice\Model\Resource', $result);
        $this->assertEquals(2, $result->id);
        $this->assertEquals('Status 2', $result->text);
    }

    /**
     * @expectedException \Cake\Core\Exception\Exception
     * @expectedExceptionMessage Hello123
     */
    public function testCreateError()
    {
        StreamWrapper::emulate(HttpEmulation::fromCallable(function (RequestInterface $request) {
            $this->assertEquals('POST', $request->getMethod());
            $this->assertEquals('/1.1/statuses/create.json', $request->getUri()->getPath());

            $this->assertEquals([
                'status' => 'Hello123'
            ], \GuzzleHttp\Psr7\parse_query($request->getBody()->getContents()));

            return new \GuzzleHttp\Psr7\Response(500, [], json_encode([
                'errors' => [
                    ['message' => 'Hello123']
                ]
            ]));
        }));

        $query = new Query($this->webservice, new Endpoint());
        $query->action(Query::ACTION_CREATE);
        $query->set([
            'status' => 'Hello123'
        ]);

        $this->webservice->execute($query);
    }

    public function testUpdate()
    {
        StreamWrapper::emulate(HttpEmulation::fromCallable(function (RequestInterface $request) {
            $this->assertEquals('POST', $request->getMethod());
            $this->assertEquals('/1.1/statuses/update.json', $request->getUri()->getPath());

            $this->assertEquals([
                'id' => '2',
                'status' => 'Status 2?'
            ], \GuzzleHttp\Psr7\parse_query($request->getBody()->getContents()));

            return new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                'id' => 2,
                'text' => 'Status 2?'
            ]));
        }));

        $query = new Query($this->webservice, new Endpoint([
            'endpoint' => 'statuses',
            'connection' => $this->webservice->driver()
        ]));
        $query->action(Query::ACTION_UPDATE);
        $query->set([
            'status' => 'Status 2?'
        ]);
        $query->where([
            'id' => 2
        ]);

        $result = $this->webservice->execute($query);
        $this->assertInstanceOf('Muffin\Webservice\Model\Resource', $result);
        $this->assertEquals(2, $result->id);
        $this->assertEquals('Status 2?', $result->text);
    }

    public function testDelete()
    {
        StreamWrapper::emulate(HttpEmulation::fromCallable(function (RequestInterface $request) {
            $this->assertEquals('POST', $request->getMethod());
            $this->assertEquals('/1.1/statuses/destroy/2.json', $request->getUri()->getPath());

            return new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                'id' => 2,
                'text' => 'Status 2?'
            ]));
        }));

        $query = new Query($this->webservice, new Endpoint([
            'endpoint' => 'statuses',
            'connection' => $this->webservice->driver()
        ]));
        $query->action(Query::ACTION_DELETE);
        $query->where([
            'id' => 2
        ]);

        $this->assertTrue($this->webservice->execute($query));
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->webservice);
        StreamWrapper::restoreWrapper('https');
    }
}
