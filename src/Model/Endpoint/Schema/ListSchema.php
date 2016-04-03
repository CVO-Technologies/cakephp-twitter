<?php

namespace CvoTechnologies\Twitter\Model\Endpoint\Schema;

use Muffin\Webservice\Model\Schema;

class ListSchema extends Schema
{
    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();

        $this->addColumn('id', [
            'type' => 'integer',
            'primaryKey' => true
        ]);
        $this->addColumn('mode', [
            'type' => 'string',
        ]);
    }
}
