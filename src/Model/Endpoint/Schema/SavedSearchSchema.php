<?php

namespace CvoTechnologies\Twitter\Model\Endpoint\Schema;

use Muffin\Webservice\Model\Schema;

class SavedSearchSchema extends Schema
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
    }
}
