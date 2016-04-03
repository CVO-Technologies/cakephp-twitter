<?php

namespace CvoTechnologies\Twitter\Model\Endpoint\Schema;

use Muffin\Webservice\Model\Schema;

class StatusSchema extends Schema
{
    public function initialize()
    {
        parent::initialize();

        $this->addColumn('id', [
            'type' => 'integer',
            'primaryKey' => true
        ]);
        $this->addColumn('text', [
            'type' => 'string',
            'limit' => 140
        ]);
        $this->addColumn('source', [
            'type' => 'string',
        ]);
    }
}
