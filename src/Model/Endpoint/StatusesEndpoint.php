<?php

namespace CvoTechnologies\Twitter\Model\Endpoint;

use Cake\Datasource\QueryInterface;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Query;
use Search\Manager;
use Search\Model\Behavior\SearchableTrait;

class StatusesEndpoint extends Endpoint
{

    use SearchableTrait;

    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->primaryKey('id');
        $this->displayField('text');
    }

    public function searchManager()
    {
        $manager = new Manager($this);
        $manager->callback('q', [
            'field' => 'q',
            'callback' => function (QueryInterface $query, $args) {
                return $query->where([
                    'q' => $args['q']
                ]);
            }
        ]);
        return $manager;
    }

    public function findHomeTimeline(Query $query, array $options = [])
    {
        $query->applyOptions([
            'index' => 'home_timeline'
        ]);

        return $query;
    }

    public function findFavorites(Query $query, array $options = [])
    {
        $query
            ->webservice($this->connection()->webservice('favorites'))
            ->where($options);

        return $query;
    }

    public function findRetweets(Query $query, array $options = [])
    {
        return $query->where([
            'retweeted_status_id' => $options['status']
        ]);
    }

    public function findSampleStream(Query $query, array $options = [])
    {
        return $query->applyOptions([
            'streamEndpoint' => 'sample',
        ]);
    }

    public function findFilterStream(Query $query, array $options = [])
    {
        return $query->applyOptions([
            'streamEndpoint' => 'filter',
        ])->where($options);
    }
}
