<?php

namespace CvoTechnologies\Twitter\Test\TestCase\Model\Endpoint;

use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use CvoTechnologies\Twitter\Model\Endpoint\ListsEndpoint;

class ListsEndpointTest extends TestCase
{
    public function testInitialize()
    {
        $listsEndpoint = new ListsEndpoint();
        $listsEndpoint->initialize([]);

        $this->assertEquals('id', $listsEndpoint->primaryKey());
        $this->assertEquals('name', $listsEndpoint->displayField());
    }

    public function testBuildRules()
    {
        $listsEndpoint = new ListsEndpoint();
        $rulesChecker = $this->getMockBuilder('Cake\ORM\RulesChecker')
            ->setMethods(['addCreate'])
            ->getMock();
        $rulesChecker->expects($this->once())
            ->method('addCreate');
        $listsEndpoint->buildRules($rulesChecker);
    }

    public function testValidationDefault()
    {
        $listsEndpoint = new ListsEndpoint();
        $validator = $listsEndpoint->validationDefault(new Validator());
        $validationRule = $validator->field('mode')->rule('mode');

        $this->assertEquals('inList', $validationRule->get('rule'));
    }
}
