<?php

namespace CvoTechnologies\Twitter\Notifier\Transport;

use Cake\Core\Exception\Exception;
use Cake\Datasource\ModelAwareTrait;
use CvoTechnologies\Notifier\AbstractTransport;
use CvoTechnologies\Notifier\Notification;

/**
 * @property \CvoTechnologies\Twitter\Model\Endpoint\StatusesEndpoint Statuses
 */
class TwitterTransport extends AbstractTransport
{
    use ModelAwareTrait;

    const TYPE = 'twitter';

    /**
     * Send notification.
     *
     * @param \CvoTechnologies\Notifier\Notification $notification Notification instance.
     * @return array
     */
    public function send(Notification $notification)
    {
        $this->modelFactory('Endpoint', ['Muffin\Webservice\Model\EndpointRegistry', 'get']);
        $this->loadModel('CvoTechnologies/Twitter.Statuses', 'Endpoint');

        try {
            $status = $this->Statuses->save($this->Statuses->newEntity([
                'text' => $notification->message(static::TYPE)
            ]));
        } catch (Exception $exception) {
            return false;
        }
        if (!$status) {
            return false;
        }

        return $status->toArray();
    }
}
