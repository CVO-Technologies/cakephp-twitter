<?php

namespace CvoTechnologies\Twitter\Model\Endpoint\Schema;

use Muffin\Webservice\Model\Schema;

class SavedSearchSchema extends Schema
{
    public function initialize()
    {
        parent::initialize();

        $this->addColumn('query', [
            'type' => 'string',
            'limit' => 140
        ]);
    }
}
