<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Webservice\Driver;

use Cake\TestSuite\TestCase;
use CvoTechnologies\Twitter\Webservice\Driver\Twitter;

class TwitterTest extends TestCase
{
    public function testInitializeOAuth()
    {
        $twitter = new Twitter([
            'consumerKey' => 'consumerKey-1',
            'consumerSecret' => 'consumerSecret-2',
            'oauthToken' => 'oauthToken-3',
            'oauthSecret' => 'oauthSecret-4',
        ]);

        $this->assertEquals([
            'type' => 'CvoTechnologies/Twitter.Twitter',
            'consumerKey' => 'consumerKey-1',
            'consumerSecret' => 'consumerSecret-2',
            'token' => 'oauthToken-3',
            'tokenSecret' => 'oauthSecret-4'
        ], $twitter->client()->config()['auth']);
    }

    public function testInitializeStreamClient()
    {
        $twitter = new Twitter([]);

        $this->assertEquals('stream.twitter.com', $twitter->streamClient()->config('host'));
    }

    public function testInitializeAccessToken()
    {
        $driver = $this->getMockBuilder('CvoTechnologies\Twitter\Webservice\Driver\Twitter')
            ->setMethods(['accessToken'])
            ->setConstructorArgs([[]])
            ->getMock();

        $driver->expects($this->once())
            ->method('accessToken')
            ->willReturn('testToken');

        $driver->initialize([]);

        $this->assertEquals('Bearer testToken', $driver->client()->config()['headers']['Authorization']);
    }

    public function testInitializeAccessTokenInvalid()
    {
        $driver = $this->getMockBuilder('CvoTechnologies\Twitter\Webservice\Driver\Twitter')
            ->setMethods(['accessToken', 'invalidateAccessToken'])
            ->setConstructorArgs([[]])
            ->getMock();

        $driver->expects($this->exactly(2))
            ->method('accessToken')
            ->willReturnOnConsecutiveCalls(false, 'testToken');
        $driver->expects($this->once())
            ->method('invalidateAccessToken');

        $driver->initialize([]);
        $this->assertEquals('Bearer testToken', $driver->client()->config()['headers']['Authorization']);
    }
}
