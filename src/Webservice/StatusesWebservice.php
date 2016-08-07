<?php

namespace CvoTechnologies\Twitter\Webservice;

use Cake\Datasource\ResultSetDecorator;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\I18n\Time;
use Cake\Network\Exception\NotFoundException;
use Cake\Network\Http\Response;
use CvoTechnologies\Twitter\Webservice\Exception\UnknownStreamEndpointException;
use Exception;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Query;

class StatusesWebservice extends TwitterWebservice
{

    public function initialize()
    {
        parent::initialize();

        $this->addNestedResource($this->_baseUrl() . '/show/:id.json', ['id']);
        $this->addNestedResource($this->_baseUrl() . '/retweets/:retweeted_status_id.json', ['retweeted_status_id']);
    }

    protected function _defaultIndex()
    {
        return 'user_timeline';
    }

    protected function _executeReadQuery(Query $query, array $options = [])
    {
        if (!isset($query->getOptions()['streamEndpoint'])) {
            return parent::_executeReadQuery($query, $options);
        }

        $client = $this->driver()->streamClient();

        switch ($query->getOptions()['streamEndpoint']) {
            case 'sample':
                $responses = $client->get($this->_baseUrl() . '/sample.json');
                break;
            case 'filter':
                $postOptions = [];
                if (isset($query->clause('where')['word'])) {
                    $postOptions['track'] = implode(',', (array)$query->clause('where')['word']);
                }
                if (isset($query->clause('where')['user'])) {
                    $postOptions['follow'] = implode(',', (array)$query->clause('where')['user']);
                }
                if (isset($query->clause('where')['location'])) {
                    $postOptions['locations'] = $query->clause('where')['location'];
                }

                $responses = $client->post($this->_baseUrl() . '/filter.json', $postOptions);
                break;
            default:
                throw new UnknownStreamEndpointException([$query->getOptions()['streamEndpoint']]);
        }

        return new ResultSetDecorator($this->_transformStreamResponses($query->endpoint(), $responses));
    }

    protected function _executeCreateQuery(Query $query, array $options = [])
    {
        $postArguments = [
            'status' => $query->set()['text']
        ];

        /* @var Response $response */
        $response = $this->driver()->client()->post($this->_baseUrl() . '/update.json', $postArguments);

        $this->_checkResponse($response);

        return $this->_transformResource($query->endpoint(), $response->json);
    }

    /**
     * Transforms streamed responses into resources
     *
     * @param \Muffin\Webservice\Model\Endpoint $endpoint Endpoint to use for resource class
     * @param \Iterator $responseIterator Iterator to get responses from
     * @yield \Muffin\Webservice\Model\Resource Webservice resource
     * @return \Generator Resource generator
     * @throws \Exception HTTP exception
     */
    protected function _transformStreamResponses(Endpoint $endpoint, \Iterator $responseIterator)
    {
        foreach ($responseIterator as $response) {
            $this->_checkResponse($response);

            yield $this->_transformResource($endpoint, $response->json);
        }
    }

    protected function _transformResource(Endpoint $endpoint, array $result)
    {
        $result['created_at'] = new Time($result['created_at']);

        return parent::_transformResource($endpoint, $result);
    }
}
