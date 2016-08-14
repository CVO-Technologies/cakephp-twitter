<?php

namespace CvoTechnologies\Twitter\Http\Client\Auth;

use Cake\Http\Client\Auth\Oauth;

class Twitter extends Oauth
{
    /**
     * Use HMAC-SHA1 signing.
     *
     * This method is suitable for plain HTTP or HTTPS.
     *
     * @param \Cake\Http\Client\Request $request The request object.
     * @param array $credentials Authentication credentials.
     * @return string
     */
    protected function _hmacSha1($request, $credentials)
    {
        $nonce = isset($credentials['nonce']) ? $credentials['nonce'] : uniqid();
        $timestamp = isset($credentials['timestamp']) ? $credentials['timestamp'] : time();
        $values = [
            'oauth_version' => '1.0',
            'oauth_nonce' => $nonce,
            'oauth_timestamp' => $timestamp,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => $credentials['token'],
            'oauth_consumer_key' => $credentials['consumerKey'],
        ];
        $baseString = $this->baseString($request, $values);

        if (isset($credentials['realm'])) {
            $values['oauth_realm'] = $credentials['realm'];
        }
        $key = [$credentials['consumerSecret'], $credentials['tokenSecret']];
        $key = array_map([$this, '_encode'], $key);
        $key = implode('&', $key);

        $values['oauth_signature'] = rawurlencode(base64_encode(
            hash_hmac('sha1', $baseString, $key, true)
        ));

        return $this->_buildAuth($values);
    }

    /**
     * Builds the Oauth Authorization header value.
     *
     * @param array $data The oauth_* values to build
     *
     * @return string
     */
    protected function _buildAuth($data)
    {
        $out = 'OAuth ';
        $params = [];
        foreach ($data as $key => $value) {
            $params[] = $key . '="' . $value . '"';
        }
        $out .= implode(',', $params); // Required for Twitter

        return $out;
    }

    /**
     * Sorts and normalizes request data and oauthValues.
     *
     * Section 9.1.1 of Oauth spec.
     *
     * - URL encode keys + values.
     * - Sort keys & values by byte value.
     *
     * @param \Cake\Network\Http\Request $request The request object.
     * @param array $oauthValues Oauth values.
     *
     * @return string sorted and normalized values
     */
    protected function _normalizedParams($request, $oauthValues)
    {
        $query = parse_url($request->url(), PHP_URL_QUERY);
        parse_str($query, $queryArgs);

        $post = [];
        $body = $request->body();
        if (is_string($body) && $request->getHeaderLine('content-type') === 'application/x-www-form-urlencoded') {
            parse_str($body, $post);
        }
        if (is_array($body)) {
            $post = $body;
        }

        $args = array_merge($queryArgs, $oauthValues, $post);
        uksort($args, 'strcmp');

        $pairs = [];
        foreach ($args as $k => $val) {
            if (is_array($val)) {
                sort($val, SORT_STRING);
                foreach ($val as $nestedVal) {
                    $pairs[] = "$k=$nestedVal";
                }
            } else {
                $val = rawurlencode($val); // Required for Twitter signatures
                $pairs[] = "$k=$val";
            }
        }

        return implode('&', $pairs);
    }
}
