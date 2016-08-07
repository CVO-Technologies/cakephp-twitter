<?php

namespace CvoTechnologies\Twitter\Webservice;

class UsersWebservice extends TwitterWebservice
{
    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();

        $this->addNestedResource($this->_baseUrl() . '/show.json?user_id=:id', ['id']);
    }

    /**
     * {@inheritDoc}
     */
    protected function _defaultIndex()
    {
        return 'search';
    }
}
