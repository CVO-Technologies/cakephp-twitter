<?php

namespace CvoTechnologies\Twitter\Webservice;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Network\Http\Response;
use CvoTechnologies\Twitter\Phirehose\OauthConsumer;
use CvoTechnologies\Twitter\Phirehose\WebservicePhirehoseInterface;
use Muffin\Webservice\Model\Resource;
use Muffin\Webservice\Query;
use Muffin\Webservice\ResultSet;
use Muffin\Webservice\Webservice\Webservice;

class StatusesWebservice extends Webservice implements StreamWebserviceInterface
{

    public function initialize()
    {
        parent::initialize();

        $this->addNestedResource('/1.1/statuses/show/:id.json', ['id']);
        $this->addNestedResource('/1.1/statuses/retweets/:retweeted_status_id.json', ['retweeted_status_id']);
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
        $stream = $this->_getStreamConsumer($query->method());

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

    protected function _executeReadQuery(Query $query, array $options = [])
    {
        $parameters = $query->clause('where');
        $parameters['count'] = $query->clause('limit');

        $url = '/1.1/statuses/user_timeline.json';
        if ($this->nestedResource($query->clause('where'))) {
            $url = $this->nestedResource($query->clause('where'));
        }

        /* @var Response $response */
        $response = $this->driver()->client()->get($url, $parameters);

        if (!$response->isOk()) {
            return false;
        }

        $json = $response->json;
        if (key($json) !== 0) {
            $resource = $this->_transformResource($json, $options['resourceClass']);

            return new ResultSet([$resource]);
        }

        $resources = $this->_transformResults($json, $options['resourceClass']);

        return new ResultSet($resources);
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
}
