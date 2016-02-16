<?php

namespace CvoTechnologies\Twitter\Model\Endpoint;

use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Query;

class StatusesEndpoint extends Endpoint
{

    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->primaryKey('id');
        $this->displayField('text');
    }

    public function findFavorites(Query $query, array $options = [])
    {
        $query
            ->webservice($this->connection()->webservice('favorites'))
            ->where($options);

        return $query;
    }

    public function findRetweets(Query $query, array $options)
    {
        return $query->where([
            'retweeted_status_id' => $options['status']
        ]);
    }

    public function findSampleStream(Query $query, array $options)
    {
        return $query->applyOptions([
            'streamEndpoint' => 'sample',
        ]);
    }

    public function findFilterStream(Query $query, array $options)
    {
        return $query->applyOptions([
            'streamEndpoint' => 'filter',
        ])->where($options);
    }
}
