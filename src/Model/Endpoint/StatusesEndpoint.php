<?php

namespace CvoTechnologies\Twitter\Model\Endpoint;

use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Query;

class StatusesEndpoint extends Endpoint
{
    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->primaryKey('id');
        $this->displayField('text');
    }

    /**
     * Find tweets that were favorited by a user.
     *
     * @param Query $query Query to modify.
     * @param array $options Where conditions to add.
     * @return Query
     */
    public function findFavorites(Query $query, array $options = [])
    {
        $query
            ->webservice($this->connection()->webservice('favorites'))
            ->where($options);

        return $query;
    }

    /**
     * Find retweets of a particular status.
     *
     * @param Query $query Query to modify.
     * @param array $options Set of options.
     *     - status - Id of the status to get the retweets off
     * @return Query
     */
    public function findRetweets(Query $query, array $options)
    {
        return $query->where([
            'retweeted_status_id' => $options['status']
        ]);
    }

    /**
     * Stream the sample stream endpoint.
     *
     * @param Query $query Query to modify.
     * @return Query
     */
    public function findSampleStream(Query $query)
    {
        return $query->applyOptions([
            'streamEndpoint' => 'sample',
        ]);
    }

    /**
     * Stream the filter stream endpoint.
     *
     * @param Query $query Query to modify.
     * @param array $options Where conditions to add.
     * @return Query
     */
    public function findFilterStream(Query $query, array $options)
    {
        return $query->applyOptions([
            'streamEndpoint' => 'filter',
        ])->where($options);
    }
}
