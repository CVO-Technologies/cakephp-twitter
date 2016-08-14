<?php

namespace CvoTechnologies\Twitter\Http;

use Cake\Http\Client;
use Cake\Http\Client\Request;

class StreamClient extends Client
{
    /**
     * Send a stream request.
     *
     * Streams the responses from an iterator
     *
     * @param \Cake\Http\Cloent\Request $request The request to send.
     * @param array $options Additional options to use.
     * @return \Iterator
     */
    public function send(Request $request, $options = [])
    {
        return $this->_adapter->send($request, $options);
    }
}
