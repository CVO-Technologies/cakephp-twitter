<?php

namespace CvoTechnologies\Twitter\Webservice\Exception;

use Cake\Core\Exception\Exception;

class UnknownStreamEndpointException extends Exception
{
    protected $_messageTemplate = 'Unknown stream endpoint %s';
}
