<?php

namespace CvoTechnologies\Twitter\Phirehose;
use ErrorException;

/**
 * A class that makes it easy to connect to and consume the Twitter stream via the Streaming API.
 *
 * Note: This is beta software - Please read the following carefully before using:
 *  - http://code.google.com/p/phirehose/wiki/Introduction
 *  - http://dev.twitter.com/pages/streaming_api
 * @author  Fenn Bailey <fenn.bailey@gmail.com>
 * @version 1.0RC
 */
interface PhirehoseInterface
{
    /**
     * Returns public statuses from or in reply to a set of users. Mentions ("Hello @user!") and implicit replies
     * ("@user Hello!" created without pressing the reply button) are not matched. It is up to you to find the integer
     * IDs of each twitter user.
     * Applies to: METHOD_FILTER
     *
     * @param array $userIds Array of Twitter integer userIDs
     */
    public function setFollow($userIds);

    /**
     * Returns an array of followed Twitter userIds (integers)
     *
     * @return array
     */
    public function getFollow();

    /**
     * Specifies keywords to track. Track keywords are case-insensitive logical ORs. Terms are exact-matched, ignoring
     * punctuation. Phrases, keywords with spaces, are not supported. Queries are subject to Track Limitations.
     * Applies to: METHOD_FILTER
     *
     * See: http://apiwiki.twitter.com/Streaming-API-Documentation#TrackLimiting
     *
     * @param array $trackWords
     */
    public function setTrack(array $trackWords);

    /**
     * Returns an array of keywords being tracked
     *
     * @return array
     */
    public function getTrack();

    /**
     * Specifies a set of bounding boxes to track as an array of 4 element lon/lat pairs denoting <south-west point>,
     * <north-east point>. Only tweets that are both created using the Geotagging API and are placed from within a tracked
     * bounding box will be included in the stream. The user's location field is not used to filter tweets. Bounding boxes
     * are logical ORs and must be less than or equal to 1 degree per side. A locations parameter may be combined with
     * track parameters, but note that all terms are logically ORd.
     *
     * NOTE: The argument order is Longitude/Latitude (to match the Twitter API and GeoJSON specifications).
     *
     * Applies to: METHOD_FILTER
     *
     * See: http://apiwiki.twitter.com/Streaming-API-Documentation#locations
     *
     * Eg:
     *  setLocations(array(
     *      array(-122.75, 36.8, -121.75, 37.8), // San Francisco
     *      array(-74, 40, -73, 41),             // New York
     *  ));
     *
     * @param array $boundingBoxes
     */
    public function setLocations($boundingBoxes);

    /**
     * Returns an array of 4 element arrays that denote the monitored location bounding boxes for tweets using the
     * Geotagging API.
     *
     * @see setLocations()
     * @return array
     */
    public function getLocations();

    /**
     * Convenience method that sets location bounding boxes by an array of lon/lat/radius sets, rather than manually
     * specified bounding boxes. Each array element should contain 3 element subarray containing a latitude, longitude and
     * radius. Radius is specified in kilometers and is approximate (as boxes are square).
     *
     * NOTE: The argument order is Longitude/Latitude (to match the Twitter API and GeoJSON specifications).
     *
     * Eg:
     *  setLocationsByCircle(array(
     *      array(144.9631, -37.8142, 30), // Melbourne, 3km radius
     *      array(-0.1262, 51.5001, 25),   // London 10km radius
     *  ));
     *
     *
     * @see setLocations()
     * @param array
     */
    public function setLocationsByCircle($locations);

    /**
     * Sets the number of previous statuses to stream before transitioning to the live stream. Applies only to firehose
     * and filter + track methods. This is generally used internally and should not be needed by client applications.
     * Applies to: METHOD_FILTER, METHOD_FIREHOSE, METHOD_LINKS
     *
     * @param integer $count
     */
    public function setCount($count);

    /**
     * Restricts tweets to the given language, given by an ISO 639-1 code (http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).
     *
     * @param string $lang
     */
    public function setLang($lang);

    /**
     * Returns the ISO 639-1 code formatted language string of the current setting. (http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).
     *
     * @param string $lang
     */
    public function getLang();

    /**
     * Connects to the stream API and consumes the stream. Each status update in the stream will cause a call to the
     * handleStatus() method.
     *
     * Note: in normal use this function does not return.
     * If you pass $reconnect as false, it will still not return in normal use: it will only return
     *   if the remote side (Twitter) close the socket. (Or the socket dies for some other external reason.)
     *
     * @see handleStatus()
     * @param boolean $reconnect Reconnects as per recommended
     * @throws ErrorException
     */
    public function consume($reconnect = true);

    /**
     * Returns the last error message (TCP or HTTP) that occured with the streaming API or client. State is cleared upon
     * successful reconnect
     * @return string
     */
    public function getLastErrorMsg();

    /**
     * Returns the last error number that occured with the streaming API or client. Numbers correspond to either the
     * fsockopen() error states (in the case of TCP errors) or HTTP error codes from Twitter (in the case of HTTP errors).
     *
     * State is cleared upon successful reconnect.
     *
     * @return string
     */
    public function getLastErrorNo();

    /**
     * This is the one and only method that must be implemented additionally. As per the streaming API documentation,
     * statuses should NOT be processed within the same process that is performing collection
     *
     * @param string $status
     */
    public function enqueueStatus($status);

    /**
     * Reports a periodic heartbeat. Keep execution time minimal.
     *
     * @return NULL
     */
    public function heartbeat();

    /**
     * Set host port
     *
     * @param string $host
     * @return void
     */
    public function setHostPort($port);

    /**
     * Set secure host port
     *
     * @param int $port
     * @return void
     */
    public function setSecureHostPort($port);
}