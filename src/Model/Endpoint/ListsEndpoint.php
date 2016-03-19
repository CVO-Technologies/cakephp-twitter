<?php

namespace CvoTechnologies\Twitter\Model\Endpoint;

use Cake\Datasource\RulesChecker;
use Cake\Validation\Validator;
use Muffin\Webservice\Model\Endpoint;
use Search\Manager;
use Search\Model\Behavior\SearchableTrait;

class ListsEndpoint extends Endpoint
{
    use SearchableTrait;

    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->primaryKey('id');
        $this->displayField('name');
    }

    public function searchManager()
    {
        return new Manager($this);
    }

    public function buildRules(RulesChecker $rules)
    {
        $rules->addCreate(function () {
            return $this->find()->count() < 1000;
        }, 'maximumAmount');

        return parent::buildRules($rules);
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->allowEmpty('mode')
            ->add('mode', 'mode', [
                'rule' => ['inList', ['public', 'private']]
            ]);

        return $validator;
    }
}
