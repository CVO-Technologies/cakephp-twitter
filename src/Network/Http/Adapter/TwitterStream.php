<?php

namespace CvoTechnologies\Twitter\Network\Http\Adapter;

use Cake\Network\Http\Adapter\Stream;
use Cake\Network\Http\Request;
use Cake\Network\Http\Response;

class TwitterStream extends Stream
{

    /**
     * Stream buffer
     *
     * @var string
     */
    protected $_buff;

    /**
     * Open the stream and stream responses using a generator
     *
     * @param \Cake\Network\Http\Request $request The request object.
     * @return \Generator Response generator
     */
    protected function _send(Request $request)
    {
        $this->_open($request->url());

        return $this->_stream();
    }

    /**
     * Reads the Twitter streaming API and yields responses
     *
     * @return \Generator Response generator
     * @yield \Cake\Network\Http\Response HTTP response
     */
    protected function _stream()
    {
        while (!feof($this->_stream)) {
            $this->_buff .= fread($this->_stream, 8192);

            $meta = stream_get_meta_data($this->_stream);

            while ($eolOffset = strpos($this->_buff, "\r\n")) {
                $line = substr($this->_buff, 0, $eolOffset);

                $this->_buff = (string)substr($this->_buff, $eolOffset + 2);

                yield new Response($meta['wrapper_data'], $line);
            }
        }
    }
}
