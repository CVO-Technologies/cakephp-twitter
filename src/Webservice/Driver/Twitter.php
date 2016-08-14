<?php

namespace CvoTechnologies\Twitter\Webservice\Driver;

use Cake\Cache\Cache;
use Cake\Network\Http\Client;
use Cake\Utility\Hash;
use CvoTechnologies\Twitter\Http\StreamClient;
use Muffin\Webservice\AbstractDriver;

/**
 * Class Twitter.
 *
 * @method Client client() client(Client $client = null)
 */
class Twitter extends AbstractDriver
{
    protected $_streamClient;

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        $clientConfig = [
            'host' => 'api.twitter.com',
            'scheme' => 'https',
        ];
        if ($this->config('oauthToken')) {
            $clientConfig['auth'] = [
                'type' => 'CvoTechnologies/Twitter.Twitter',
                'consumerKey' => $this->config('consumerKey'),
                'consumerSecret' => $this->config('consumerSecret'),
                'token' => $this->config('oauthToken'),
                'tokenSecret' => $this->config('oauthSecret')
            ];
        } else {
            $accessToken = $this->accessToken();
            // The access token is invalid
            if (!$accessToken) {
                // Get rid of the invalid access token
                $this->invalidateAccessToken();

                $accessToken = $this->accessToken();
            }

            $clientConfig['headers']['Authorization'] = 'Bearer ' . $accessToken;
        }

        $this->client(new Client($clientConfig));
        $this->streamClient(new StreamClient(Hash::merge($clientConfig, [
            'host' => 'stream.twitter.com',
            'adapter' => 'CvoTechnologies\Twitter\Http\Adapter\TwitterStream'
        ])));
    }

    /**
     * Set or return an instance of the stream client used for communication with the streaming API.
     *
     * @param object $client The client to use
     *
     * @return $this
     */
    public function streamClient($client = null)
    {
        if ($client === null) {
            return $this->_streamClient;
        }

        $this->_streamClient = $client;

        return $this;
    }

    /**
     * Returns a application access token.
     *
     * @return string|bool The access token or false in case of a failure
     */
    public function accessToken()
    {
        $cacheKey = 'twitter-' . $this->config('name') . '-token';
        if (Cache::read($cacheKey) !== false) {
            return Cache::read($cacheKey);
        }

        $bearerToken = $this->bearerToken();
        if (!$bearerToken) {
            return false;
        }

        $client = new Client([
            'headers' => ['Authorization' => 'Basic ' . $bearerToken],
            'host' => 'api.twitter.com',
            'scheme' => 'https',
        ]);

        $response = $client->post('/oauth2/token', [
            'grant_type' => 'client_credentials'
        ]);

        if ((!$response->isOk()) || (!$response->json['token_type'])) {
            return false;
        }

        Cache::write($cacheKey, $response->json['access_token']);

        return $response->json['access_token'];
    }

    /**
     * Returns a bearer token for application authentication.
     *
     * @return string|bool Bearer token or bool in case of an error
     */
    public function bearerToken()
    {
        if ((!$this->config('consumerKey')) || (!$this->config('consumerSecret'))) {
            return false;
        }
        $consumerKey = urlencode($this->config('consumerKey'));
        $consumerSecret = urlencode($this->config('consumerSecret'));

        return base64_encode($consumerKey . ':' . $consumerSecret);
    }

    /**
     * Invalidates the locally stored access token.
     *
     * @return void
     */
    public function invalidateAccessToken()
    {
        Cache::delete('twitter-' . $this->config('name') . '-token');
    }
}
