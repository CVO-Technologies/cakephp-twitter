<?php

namespace CvoTechnologies\Twitter\Phirehose;

use Phirehose;
use Psr\Log\LoggerAwareTrait;

class OauthConsumer extends \OauthPhirehose implements WebservicePhirehoseInterface
{

    use LoggerAwareTrait;
    use WebservicePhirehoseTrait;

    protected $_callback;

    public function __construct($username, $password, $method = Phirehose::METHOD_FILTER, $lang = false)
    {
        parent::__construct($username, $password, $method, self::FORMAT_JSON, $lang);
    }
}
