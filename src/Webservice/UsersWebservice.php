<?php

namespace CvoTechnologies\Twitter\Webservice;

class UsersWebservice extends TwitterWebservice
{

    protected function _defaultIndex()
    {
        return 'search';
    }

    public function initialize()
    {
        parent::initialize();

        $this->addNestedResource($this->_baseUrl() . '/show.json?user_id=:id', ['id']);
    }
}
