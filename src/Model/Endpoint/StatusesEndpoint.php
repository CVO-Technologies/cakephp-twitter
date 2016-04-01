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
     * Find the tweets favourited by the current user.
     *
     * @param \Muffin\Webservice\Query $query The query to modify.
     * @param array $options Extra conditions to apply.
     * @return \Muffin\Webservice\Query The modified query.
     */
    public function findFavorites(Query $query, array $options = [])
    {
        $query
            ->webservice($this->connection()->webservice('favorites'))
            ->where($options);

        return $query;
    }

    /**
     * Find the retweets of the specified tweet.
     *
     * @param \Muffin\Webservice\Query $query The query to modify.
     * @param array $options The options to pass, including the tweet to get the retweets from.
     * @return \Muffin\Webservice\Query The modified query.
     */
    public function findRetweets(Query $query, array $options)
    {
        return $query->where([
            'retweeted_status_id' => $options['status']
        ]);
    }

    /**
     * Use the Twitter sample stream.
     *
     * @param \Muffin\Webservice\Query $query The query to modify.
     * @return \Muffin\Webservice\Query The modified query.
     */
    public function findSampleStream(Query $query)
    {
        return $query->applyOptions([
            'streamEndpoint' => 'sample',
        ]);
    }

    /**
     * Use the Twitter filter stream.
     *
     * @param \Muffin\Webservice\Query $query The query to modify.
     * @param array $options The conditions to apply to the query.
     * @return Query The modified query.
     */
    public function findFilterStream(Query $query, array $options)
    {
        return $query->applyOptions([
            'streamEndpoint' => 'filter',
        ])->where($options);
    }
}
