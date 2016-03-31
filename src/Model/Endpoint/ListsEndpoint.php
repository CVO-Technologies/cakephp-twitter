<?php

namespace CvoTechnologies\Twitter\Model\Endpoint;

use Cake\Datasource\RulesChecker;
use Cake\Validation\Validator;
use Muffin\Webservice\Model\Endpoint;

class ListsEndpoint extends Endpoint
{
    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->primaryKey('id');
        $this->displayField('name');
    }

    /**
     * {@inheritDoc}
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->addCreate(function () {
            return $this->find()->count() < 1000;
        }, 'maximumAmount');

        return parent::buildRules($rules);
    }

    /**
     * {@inheritDoc}
     */
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
