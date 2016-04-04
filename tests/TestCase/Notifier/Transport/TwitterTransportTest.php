<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Notifier\Transport;

use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use CvoTechnologies\StreamEmulation\StreamWrapper;
use CvoTechnologies\Twitter\Notifier\Transport\TwitterTransport;
use CvoTechnologies\Twitter\Test\Emulation\StatusUpdateEmulation;
use Psr\Http\Message\RequestInterface;

class TwitterTransportTest extends TestCase
{
    public function setUp()
    {
        ConnectionManager::config('twitter', [
            'className' => 'Muffin\Webservice\Connection',
            'service' => 'CvoTechnologies/Twitter.Twitter',
        ]);
        StreamWrapper::overrideWrapper('https');
    }

    public function testSend()
    {
        StreamWrapper::emulate(new StatusUpdateEmulation(function (RequestInterface $request) {
            $this->assertEquals('POST', $request->getMethod());
            $this->assertEquals('/1.1/statuses/update.json', $request->getUri()->getPath());

            $this->assertEquals([
                'status' => 'Test123'
            ], \GuzzleHttp\Psr7\parse_query($request->getBody()->getContents()));
        }));

        $notification = $this->getMock('CvoTechnologies\Notifier\Notification', ['message']);
        $notification
            ->expects($this->once())
            ->method('message')
            ->with('twitter')
            ->willReturn('Test123');

        $twitterTransport = new TwitterTransport();
        $this->assertEquals([
            'id' => '1',
            'status' => 'Test123'
        ], $twitterTransport->send($notification));
    }

    public function tearDown()
    {
        StreamWrapper::restoreWrapper('https');
        ConnectionManager::drop('twitter');
    }
}
