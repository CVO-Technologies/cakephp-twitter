<?php

namespace CvoTechnologies\Twitter\Webservice;

use Muffin\Webservice\Webservice\WebserviceInterface;

interface StreamWebserviceInterface extends WebserviceInterface
{

    /**
     * Executes a query
     *
     * @param StreamQuery $query The query to execute
     * @param array $options The options to use
     *
     * @return bool
     */
    public function executeStream(StreamQuery $query, array $options = []);
}
