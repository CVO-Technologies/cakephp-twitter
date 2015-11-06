<?php

namespace CvoTechnologies\Twitter\Phirehose;

use Cake\Event\EventManager;
use Psr\Log\LoggerAwareInterface;

interface WebservicePhirehoseInterface extends PhirehoseInterface, LoggerAwareInterface
{

    /**
     * @return EventManager
     */
    public function getEventManager();

    public function setEventManager(EventManager $eventManager);
}
