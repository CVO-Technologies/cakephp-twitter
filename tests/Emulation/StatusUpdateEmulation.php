<?php

namespace CvoTechnologies\Twitter\Test\Emulation;

use Psr\Http\Message\RequestInterface;

class StatusUpdateEmulation extends TwitterEmulation
{
    /**
     * Run the HTTP emulation.
     *
     * @param \Psr\Http\Message\RequestInterface $request The request object/
     * @return \Psr\Http\Message\ResponseInterface The response object.
     */
    protected function run(RequestInterface $request)
    {
        $response = parent::run($request);
        if ($response) {
            return $response;
        }

        return new \GuzzleHttp\Psr7\Response(200, [], json_encode([
            'id' => '1',
            'status' => 'Test123'
        ]));
    }
}
