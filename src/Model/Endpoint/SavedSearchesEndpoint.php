<?php

namespace CvoTechnologies\Twitter\Model\Endpoint;

use Cake\Datasource\RulesChecker;
use Muffin\Webservice\Model\Endpoint;

class SavedSearchesEndpoint extends Endpoint
{

    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->primaryKey('id');
        $this->displayField('name');
    }

    public function buildRules(RulesChecker $rules)
    {
        $rules->addCreate(function () {
            return $this->find()->count() < 25;
        }, 'maximumAmount');

        return parent::buildRules($rules);
    }
}
