<?php

namespace CvoTechnologies\Twitter\Webservice\StreamConsumer;

use Muffin\Webservice\Model\Resource;

interface StreamConsumerInterface
{

    public function processDelete(array $data);

    public function processStatus(Resource $resource);
}
