<?php

namespace CvoTechnologies\Twitter\Model\Endpoint\Schema;

use Muffin\Webservice\Model\Schema;

class ListSchema extends Schema
{
    public function initialize()
    {
        parent::initialize();

        $this->addColumn('name', [
            'type' => 'string',
        ]);
        $this->addColumn('description', [
            'type' => 'string',
        ]);
        $this->addColumn('mode', [
            'type' => 'string',
        ]);
    }
}
