<?php

namespace CvoTechnologies\Twitter\Model\Endpoint;

use Muffin\Webservice\Model\Endpoint;

class UsersEndpoint extends Endpoint
{
    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->primaryKey('user_id');
        $this->displayField('screen_name');
    }
}
