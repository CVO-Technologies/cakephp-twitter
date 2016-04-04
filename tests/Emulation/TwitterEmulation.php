<?php

namespace CvoTechnologies\Twitter\Test\Emulation;

use CvoTechnologies\StreamEmulation\Emulation\HttpEmulation;
use Psr\Http\Message\RequestInterface;

abstract class TwitterEmulation extends HttpEmulation
{
    protected $error;

    /**
     * Run the HTTP emulation.
     *
     * @param \Psr\Http\Message\RequestInterface $request The request object/
     * @return \Psr\Http\Message\ResponseInterface The response object.
     */
    protected function _run(RequestInterface $request)
    {
        if ($this->getError()) {
            return new \GuzzleHttp\Psr7\Response($this->getError()['code'], [], json_encode([
                'errors' => [
                    'message' => $this->getError()['message']
                ]
            ]));
        }
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($errorCode, $message)
    {
        $this->error = [
            'code' => $errorCode,
            'message' => $message
        ];

        return $this;
    }
}
