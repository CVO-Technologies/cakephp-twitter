<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Notifier\Transport;

use Cake\Datasource\ConnectionManager;
use Cake\Network\Http\Response;
use Cake\TestSuite\TestCase;
use CvoTechnologies\Twitter\Notifier\Transport\TwitterTransport;

class TwitterTransportTest extends TestCase
{
    public function setUp()
    {
        ConnectionManager::config('twitter', [
            'className' => 'Muffin\Webservice\Connection',
            'service' => 'CvoTechnologies/Twitter.Twitter',
        ]);
    }

    public function tearDown()
    {
        ConnectionManager::drop('twitter');
    }

    public function testSend()
    {
        $clientMock = $this->getMockBuilder('Cake\Network\Http\Client');
        $clientMock->setMethods(['post']);

        $client = $clientMock->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/1.1/statuses/update.json',
                [
                    'status' => 'Test123'
                ]
            )
            ->willReturn(new Response(
                [
                    'HTTP/1.1 200 OK'
                ],
                json_encode([
                    'id' => '1',
                    'status' => 'Test123'
                ])
            ));

        ConnectionManager::get('twitter')->client($client);

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
}
