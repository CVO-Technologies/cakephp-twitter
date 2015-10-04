<?php

namespace CvoTechnologies\Twitter\Controller;

use App\Controller\AppController;
use Cake\Event\Event;

class StatusesController extends AppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(Event $event)
    {
        unset($this->Statuses);

        $this->loadModel('CvoTechnologies/Twitter.Statuses', 'Endpoint');
    }

    public function index()
    {
        debug($this->Statuses->find()->conditions([
            'screen_name' => 'Marlin_MMS'
        ])->first());

        exit();
    }
}
