<?php

namespace CvoTechnologies\Twitter\Webservice\Exception;

use Cake\Core\Exception\Exception;

class UnknownErrorException extends Exception
{
    protected $_messageTemplate = 'Unknown error: %2$s (%1$d)';
}
