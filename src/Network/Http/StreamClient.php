<?php

namespace CvoTechnologies\Twitter\Network\Http;

use Cake\Network\Http\Client;
use Cake\Network\Http\Request;

class StreamClient extends Client
{
    /**
     * Send a stream request.
     *
     * Streams the responses from an iterator
     *
     * @param \Cake\Network\Http\Request $request The request to send.
     * @param array $options Additional options to use.
     * @return \Iterator
     */
    public function send(Request $request, $options = [])
    {
        return $this->_adapter->send($request, $options);
    }
}
