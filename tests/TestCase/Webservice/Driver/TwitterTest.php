<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Webservice\Driver;

use Cake\Cache\Cache;
use Cake\TestSuite\TestCase;
use CvoTechnologies\StreamEmulation\Emulation\HttpEmulation;
use CvoTechnologies\StreamEmulation\StreamWrapper;
use CvoTechnologies\Twitter\Webservice\Driver\Twitter;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class TwitterTest extends TestCase
{
    public function setUp()
    {
        StreamWrapper::overrideWrapper('https');
    }

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
        StreamWrapper::emulate(HttpEmulation::fromCallable(function (RequestInterface $request) {
            $this->assertEquals('/oauth2/token', $request->getUri()->getPath());

            return new Response(200, [], json_encode([
                'token_type' => 'bearer',
                'access_token' => 'testToken'
            ]));
        }));

        $driver = new Twitter([
            'consumerKey' => 'consumerKey-1',
            'consumerSecret' => 'consumerSecret-2',
        ]);
        $driver->initialize([]);

        $this->assertEquals('Bearer testToken', $driver->client()->config()['headers']['Authorization']);
    }

    public function testInitializeAccessTokenInvalid()
    {
        $invocation = 0;
        StreamWrapper::emulate(HttpEmulation::fromCallable(function (RequestInterface $request) use (&$invocation) {
            ++$invocation;

            $this->assertEquals('/oauth2/token', $request->getUri()->getPath());

            if ($invocation === 1) {
                return new Response(500);
            }

            return new Response(200, [], json_encode([
                'token_type' => 'bearer',
                'access_token' => 'testToken'
            ]));
        }));

        $driver = new Twitter([
            'consumerKey' => 'consumerKey-1',
            'consumerSecret' => 'consumerSecret-2',
        ]);

        $driver->initialize([]);
        $this->assertEquals('Bearer testToken', $driver->client()->config()['headers']['Authorization']);
        $this->assertEquals(2, $invocation);
    }

    public function tearDown()
    {
        StreamWrapper::restoreWrapper('https');
        Cache::clear();
    }
}
