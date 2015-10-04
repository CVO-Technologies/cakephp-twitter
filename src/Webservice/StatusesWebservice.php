<?php

namespace CvoTechnologies\Twitter\Webservice;

use Cake\Network\Http\Response;
use Muffin\Webservice\ResultSet;
use Muffin\Webservice\Webservice\Webservice;
use Muffin\Webservice\WebserviceQuery;

class StatusesWebservice extends Webservice
{

    protected function _executeReadQuery(WebserviceQuery $query, array $options = [])
    {
        $parameters = $query->conditions();
        $parameters['count'] = $query->limit();

        /* @var Response $response */
        $response = $this->driver()->client()->get('/1.1/statuses/user_timeline.json', $parameters);

        if (!$response->isOk()) {
            return false;
        }

        $resources = $this->_transformResults($response->json, $options['resourceClass']);

        return new ResultSet($resources);
    }
}
