<?php

namespace CvoTechnologies\Twitter\Webservice;

use Cake\Datasource\ResultSetDecorator;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Network\Exception\NotFoundException;
use Cake\Network\Http\Response;
use CvoTechnologies\Twitter\Webservice\Exception\UnknownStreamEndpointException;
use Exception;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Query;

class ListsWebservice extends TwitterWebservice
{
    public function initialize()
    {
        parent::initialize();

        $this->addNestedResource($this->_baseUrl() . '/show.json?list_id=:id', ['id']);
    }

    protected function _executeUpdateQuery(Query $query, array $options = [])
    {
        if ((!isset($query->where()['id'])) || (is_array($query->where()['id']))) {
            return false;
        }

        $parameters = $query->set();
        $parameters['list_id'] = $query->where()['id'];

        $response = $this->driver()->client()->post($this->_baseUrl() . '/update.json', $parameters);

        if (!$response->isOk()) {
            throw new Exception($response->json['errors'][0]['message']);
        }

        return $this->_transformResource($query->endpoint(), $response->json);
    }

    protected function _executeDeleteQuery(Query $query, array $options = [])
    {
        if ((!isset($query->where()['id'])) || (is_array($query->where()['id']))) {
            return false;
        }

        $url = $this->_baseUrl() . '/destroy.json?list_id=' . $query->where()['id'];

        /* @var Response $response */
        $response = $this->driver()->client()->post($url);

        if (!$response->isOk()) {
            throw new Exception($response->json['errors'][0]['message']);
        }

        return 1;
    }
}
