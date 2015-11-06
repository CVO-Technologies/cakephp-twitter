<?php

namespace CvoTechnologies\Twitter\Phirehose;

use Cake\Event\Event;
use Cake\Event\EventManager;

trait WebservicePhirehoseTrait
{

    /**
     * @return EventManager
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    public function setEventManager(EventManager $eventManager)
    {
        $this->_eventManager = $eventManager;

        return null;
    }

    protected function log($message, $level = 'notice')
    {
        if (!$this->logger) {
            return;
        }

        $this->logger->log($level, $message);
    }

    protected function statusUpdate()
    {
    }

    /**
     * This is the one and only method that must be implemented additionally. As per the streaming API documentation,
     * statuses should NOT be processed within the same process that is performing collection
     *
     * @param string $status
     */
    public function enqueueStatus($status)
    {
        $data = json_decode($status, true);

        $eventName = 'Statuses.raw.user_update';

        if (isset($data['deleted'])) {
            $eventName = 'Statuses.raw.delete';
        }
        if (isset($data['friends'])) {
            $eventName = 'Statuses.raw.friends';
        }

        $this->getEventManager()->dispatch(new Event(
            $eventName,
            $this,
            ['data' => $data]
        ));
    }
}
