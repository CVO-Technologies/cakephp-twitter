<?php

namespace CvoTechnologies\Twitter\Webservice;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Network\Http\Response;
use CvoTechnologies\Twitter\Phirehose\OauthConsumer;
use CvoTechnologies\Twitter\Phirehose\WebservicePhirehoseInterface;
use Muffin\Webservice\Model\Resource;
use Muffin\Webservice\Query;
use Muffin\Webservice\StreamQuery;

class StatusesWebservice extends TwitterWebservice
{

    public function initialize()
    {
        parent::initialize();

        $this->addNestedResource($this->_baseUrl() . '/show/:id.json', ['id']);
        $this->addNestedResource($this->_baseUrl() . '/retweets/:retweeted_status_id.json', ['retweeted_status_id']);
    }

    public function stream(WebservicePhirehoseInterface $phirehose, EventManager $eventManager, array $options)
    {
        $eventManager->on(
            'Statuses.raw.user_update',
            ['priority' => 5],
            function (Event $event) use ($options) {
                $event->data = [
                    'data' => $this->_transformResource($event->data['data'], $options['resourceClass'])
                ];
            }
        );
        $phirehose->setEventManager($eventManager);

        return $phirehose->consume();
    }

    /**
     * Executes a query
     *
     * @param StreamQuery $query The query to execute
     * @param array $options The options to use
     *
     * @return bool
     */
    public function executeStream(StreamQuery $query, array $options = [])
    {
        $method = \Phirehose::METHOD_FILTER;
        if (isset($query->getOptions()['method'])) {
            $method = $query->getOptions()['method'];
        }

        $stream = $this->_getStreamConsumer($method);

        if ($query->clause('where')['words']) {
            $stream->setTrack($query->clause('where')['words']);
        }
        if ($query->clause('limit')) {
            $stream->setCount($query->clause('limit'));
        }

        $eventManager = new EventManager();
        $eventManager->on('Statuses.raw.friends', function (Event $event) use ($query) {
            $event = new Event('Status.friends', $this, $event->data['data']);

            $query->eventManager()->dispatch($event);
        });
        $eventManager->on('Statuses.raw.delete', function (Event $event) use ($query) {
            $event = new Event('Status.deleted', $this, $event->data['data']);

            $query->eventManager()->dispatch($event);
        });
        $eventManager->on('Statuses.raw.user_update', function (Event $event, Resource $resource) use ($query) {
            $event = new Event('Status.placed', $this,['result' => $query->decorateResult($resource)]);

            $query->eventManager()->dispatch($event);
        });

        $this->stream($stream, $eventManager, $options);
    }

    public function streamSample($param, array $options)
    {
        $sc = $this->_getStreamConsumer(OauthConsumer::METHOD_SAMPLE);

        return $this->stream($sc, $param, $options);
    }

    protected function _defaultIndex()
    {
        return 'user_timeline';
    }

    protected function _getStreamConsumer($method)
    {
        $sc = new OauthConsumer(
            $this->driver()->config('oauthToken'),
            $this->driver()->config('oauthSecret'),
            $method
        );

        $sc->setLogger($this->driver()->logger());

        $sc->consumerKey = $this->driver()->config('consumerKey');
        $sc->consumerSecret = $this->driver()->config('consumerSecret');

        return $sc;
    }

    protected function _executeCreateQuery(Query $query, array $options = [])
    {
        /* @var Response $response */
        $response = $this->driver()->client()->post($this->_baseUrl() . '/update.json', $query->set());

        if (!$response->isOk()) {
            return false;
        }

        return $this->_transformResource($response->json, $options['resourceClass']);
    }
}
