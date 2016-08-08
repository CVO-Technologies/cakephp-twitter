<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Network\Http\Auth;

use Cake\Network\Http\Request;
use Cake\TestSuite\TestCase;
use CvoTechnologies\Twitter\Network\Http\Auth\Twitter;

/**
 * Oauth test.
 */
class TwitterTest extends TestCase
{
    /**
     * @expectedException \Cake\Core\Exception\Exception
     */
    public function testExceptionUnknownSigningMethod()
    {
        $auth = new Twitter();
        $creds = [
            'consumerSecret' => 'it is secret',
            'consumerKey' => 'a key',
            'token' => 'a token value',
            'tokenSecret' => 'also secret',
            'method' => 'silly goose',
        ];
        $request = new Request();
        $auth->authentication($request, $creds);
    }

    /**
     * Test that baseString() normalizes the URL.
     *
     * @return void
     */
    public function testBaseStringNormalizeUrl()
    {
        $request = new Request();
        $request->url('HTTP://exAmple.com:80/parts/foo');

        $auth = new Twitter();
        $creds = [];
        $result = $auth->baseString($request, $creds);
        $this->assertContains('GET&', $result, 'method was missing.');
        $this->assertContains('http%3A%2F%2Fexample.com%2Fparts%2Ffoo', $result);
    }

    /**
     * Test that the query string is stripped from the normalized host.
     *
     * @return void
     */
    public function testBaseStringWithQueryString()
    {
        $request = new Request();
        $request->url('http://example.com/search?q=pogo&cat=2');

        $auth = new Twitter();
        $values = [
            'oauth_version' => '1.0',
            'oauth_nonce' => uniqid(),
            'oauth_timestamp' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => 'token',
            'oauth_consumer_key' => 'consumer-key',
        ];
        $result = $auth->baseString($request, $values);
        $this->assertContains('GET&', $result, 'method was missing.');
        $this->assertContains(
            'http%3A%2F%2Fexample.com%2Fsearch&',
            $result
        );
        $this->assertContains(
            'cat%3D2%26oauth_consumer_key%3Dconsumer-key' .
            '%26oauth_nonce%3D' . $values['oauth_nonce'] .
            '%26oauth_signature_method%3DHMAC-SHA1' .
            '%26oauth_timestamp%3D' . $values['oauth_timestamp'] .
            '%26oauth_token%3Dtoken' .
            '%26oauth_version%3D1.0' .
            '%26q%3Dpogo',
            $result
        );
    }

    /**
     * Ensure that post data is sorted and encoded.
     *
     * Keys with array values have to be serialized using
     * a more standard HTTP approach. PHP flavoured HTTP
     * is not part of the Oauth spec.
     *
     * See Normalize Request Parameters (section 9.1.1)
     * http://wiki.oauth.net/w/page/12238556/TestCases
     *
     * @return void
     */
    public function testBaseStringWithPostData()
    {
        $request = new Request();
        $request->url('http://example.com/search?q=pogo')
            ->method(Request::METHOD_POST)
            ->body([
                'address' => 'post',
                'tags' => ['oauth', 'cake'],
                'zed' => 'last'
            ]);

        $auth = new Twitter();
        $values = [
            'oauth_version' => '1.0',
            'oauth_nonce' => uniqid(),
            'oauth_timestamp' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => 'token',
            'oauth_consumer_key' => 'consumer-key',
        ];
        $result = $auth->baseString($request, $values);

        $this->assertContains('POST&', $result, 'method was missing.');
        $this->assertContains(
            'http%3A%2F%2Fexample.com%2Fsearch&',
            $result
        );
        $this->assertContains(
            '&address%3Dpost' .
            '%26oauth_consumer_key%3Dconsumer-key' .
            '%26oauth_nonce%3D' . $values['oauth_nonce'] .
            '%26oauth_signature_method%3DHMAC-SHA1' .
            '%26oauth_timestamp%3D' . $values['oauth_timestamp'] .
            '%26oauth_token%3Dtoken' .
            '%26oauth_version%3D1.0' .
            '%26q%3Dpogo' .
            '%26tags%3Dcake' .
            '%26tags%3Doauth' .
            '%26zed%3Dlast',
            $result
        );
    }

    /**
     * Test HMAC-SHA1 signing.
     *
     * Hash result + parameters taken from
     * http://wiki.oauth.net/w/page/12238556/TestCases
     *
     * @return void
     */
    public function testHmacSigning()
    {
        $request = new Request();
        $request->url('http://photos.example.net/photos')
            ->body([
                'file' => 'vacation.jpg',
                'size' => 'original'
            ]);

        $options = [
            'consumerKey' => 'dpf43f3p2l4k3l03',
            'consumerSecret' => 'kd94hf93k423kf44',
            'tokenSecret' => 'pfkkdhi9sl3r4s00',
            'token' => 'nnch734d00sl2jdk',
            'nonce' => 'kllo9940pd9333jh',
            'timestamp' => '1191242096'
        ];
        $auth = new Twitter();
        $request = $auth->authentication($request, $options);

        $result = $request->header('Authorization');
        $expected = 'tR3+Ty81lMeYAr/Fid0kMTYa/WM=';
        $this->assertContains(
            'oauth_signature="' . $expected . '"',
            urldecode($result)
        );
    }
}
