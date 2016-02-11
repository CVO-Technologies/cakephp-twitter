<?php

namespace CvoTechnologies\Twitter\Webservice;

use Cake\Network\Exception\NotFoundException;
use Cake\Network\Http\Response;
use CvoTechnologies\Twitter\Webservice\Exception\RateLimitExceededException;
use CvoTechnologies\Twitter\Webservice\Exception\UnknownErrorException;
use Muffin\Webservice\Query;
use Muffin\Webservice\ResultSet;
use Muffin\Webservice\Webservice\Webservice;

/**
 * Class TwitterWebservice
 *
 * @method Driver\Twitter driver()
 *
 * @package CvoTechnologies\Twitter\Webservice
 */
class TwitterWebservice extends Webservice
{

    protected function _baseUrl()
    {
        return '/1.1/' . $this->endpoint();
    }

    protected function _executeCreateQuery(Query $query, array $options = [])
    {
        /* @var Response $response */
        $response = $this->driver()->client()->post($this->_baseUrl() . '/create.json', $query->set());

        if (!$response->isOk()) {
            throw new Exception($response->json['errors'][0]['message']);
        }

        return $this->_transformResource($response->json, $options['resourceClass']);
    }

    protected function _executeReadQuery(Query $query, array $options = [])
    {
        $parameters = $query->where();
        if ($query->clause('limit')) {
            $parameters['count'] = $query->clause('limit');
        }
        if ($query->clause('page')) {
            $parameters['page'] = $query->clause('page');
        }
        if ($query->clause('offset')) {
            $parameters['since_id'] = $query->clause('offset');
        }

        if (!empty($query->where())) {
            $displayField = $query->endpoint()->aliasField($query->endpoint()->displayField());
            if (isset($query->where()[$displayField])) {
                $parameters['q'] = $query->where()[$displayField];
            }
        }

        $url = $this->_baseUrl() . '/' . $this->_defaultIndex() . '.json';
        if ($this->nestedResource($query->clause('where'))) {
            $url = $this->nestedResource($query->clause('where'));
        }
        if ((isset($query->where()['id'])) && (is_array($query->where()['id']))) {
            $parameters[$query->endpoint()->primaryKey()] = implode(',', $query->where()['id']);

            $url = $this->_baseUrl() . '/lookup.json';
        }

        try {
            $json = $this->_doRequest($url, $parameters);
        } catch (NotFoundException $exception) {
            return new ResultSet([], 0);
        }

        if ($json === false) {
            return false;
        }

        if (key($json) !== 0) {
            $resource = $this->_transformResource($json, $query->endpoint()->resourceClass());

            return new ResultSet([$resource], 1);
        }

        $resources = $this->_transformResults($json, $query->endpoint()->resourceClass());

        return new ResultSet($resources, count($resources));
    }

    protected function _executeUpdateQuery(Query $query, array $options = [])
    {
        if ((!isset($query->where()['id'])) || (is_array($query->where()['id']))) {
            return false;
        }

        $parameters = $query->set();
        $parameters[$query->endpoint()->primaryKey()] = $query->where()['id'];

        $response = $this->driver()->client()->post($this->_baseUrl() . '/update.json', $parameters);

        if (!$response->isOk()) {
            throw new Exception($response->json['errors'][0]['message']);
        }

        return $this->_transformResource($response->json, $options['resourceClass']);
    }

    protected function _executeDeleteQuery(Query $query, array $options = [])
    {
        if ((!isset($query->where()['id'])) || (is_array($query->where()['id']))) {
            return false;
        }

        $url = $this->_baseUrl() . '/destroy/' . $query->where()['id'] . '.json';

        /* @var Response $response */
        $response = $this->driver()->client()->post($url);

        if (!$response->isOk()) {
            throw new Exception($response->json['errors'][0]['message']);
        }

        return 1;
    }

    protected function _defaultIndex()
    {
        return 'list';
    }

    protected function _doRequest($url, $parameters)
    {
        /* @var Response $response */
        $response = $this->driver()->client()->get($url, $parameters);

        $this->_checkResponse($response);

        return $response->json;
    }

    protected function _checkResponse(Response $response)
    {
        switch ($response->statusCode()) {
            case 404:
                throw new NotFoundException($response->json['errors'][0]['message']);
            case 429:
                throw new RateLimitExceededException($response->json['errors'][0]['message'], 429);
        }

        if (!$response->isOk()) {
            throw new UnknownErrorException([$response->statusCode(), $response->json['errors'][0]['message']]);
        }
    }

}
