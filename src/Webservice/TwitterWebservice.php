<?php

namespace CvoTechnologies\Twitter\Webservice;

use Cake\Network\Exception\NotFoundException;
use CvoTechnologies\Twitter\Webservice\Exception\RateLimitExceededException;
use CvoTechnologies\Twitter\Webservice\Exception\UnknownErrorException;
use Muffin\Webservice\Query;
use Muffin\Webservice\ResultSet;
use Muffin\Webservice\Webservice\Webservice;
use Psr\Http\Message\ResponseInterface;

/**
 * Class TwitterWebservice
 *
 * @method Driver\Twitter driver()
 *
 * @package CvoTechnologies\Twitter\Webservice
 */
class TwitterWebservice extends Webservice
{
    /**
     * Return the base URL to start URLs with.
     *
     * @return string
     */
    protected function _baseUrl()
    {
        return '/1.1/' . $this->endpoint();
    }

    /**
     * {@inheritDoc}
     */
    protected function _executeCreateQuery(Query $query, array $options = [])
    {
        /* @var Response $response */
        $response = $this->driver()->client()->post($this->_baseUrl() . '/create.json', $query->set());

        if (!$response->isOk()) {
            throw new Exception($response->json['errors'][0]['message']);
        }

        return $this->_transformResource($response->json, $options['resourceClass']);
    }

    /**
     * {@inheritDoc}
     */
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

        $index = $this->_defaultIndex();
        if (isset($query->getOptions()['index'])) {
            $index = $query->getOptions()['index'];
        }
        $url = $this->_baseUrl() . '/' . $index . '.json';
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
            $resource = $this->_transformResource($query->endpoint(), $json);

            return new ResultSet([$resource], 1);
        }

        $resources = $this->_transformResults($query->endpoint(), $json);

        return new ResultSet($resources, count($resources));
    }

    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     */
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

    /**
     * Returns the default index action to run.
     *
     * @return string
     */
    protected function _defaultIndex()
    {
        return 'list';
    }

    /**
     * Execute a HTTP query to the Twitter webservice.
     *
     * @param string $url URL to request.
     * @param array $parameters Query parameters to pass.
     * @return mixed JSON response
     */
    protected function _doRequest($url, $parameters)
    {
        /* @var Response $response */
        $response = $this->driver()->client()->get($url, $parameters);

        $this->_checkResponse($response);

        return $response->json;
    }

    /**
     * Check the Twitter response for errors.
     *
     * @param ResponseInterface $response Twitter response to check.
     * @return void
     */
    protected function _checkResponse(ResponseInterface $response)
    {
        if (isset($response->json['errors'][0]['message'])) {
            $error = $response->json['errors'][0]['message'];
        } else {
            $error = $response->body();
        }
        switch ($response->statusCode()) {
            case 404:
                throw new NotFoundException($error);
            case 429:
                throw new RateLimitExceededException($error, 429);
        }

        if (!$response->isOk()) {
            throw new UnknownErrorException([$response->statusCode(), $error]);
        }
    }
}
