<?php
// src/Service/UntappdAPI.php
namespace App\Service;

use Unirest;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\APIQueryLog as APIQueryLog;

class UntappdAPI
{
    private $untappdAPIUrl;
    private $untappdAPIClientID;
    private $untappdAPIClientSecret;
    private $untappdAPIOAuthRedirectURL;
    
    public function __construct($untappdAPIUrl, $untappdAPIClientID, $untappdAPIClientSecret, $untappdAPIOAuthRedirectURL, EntityManagerInterface $em)
    {
        $this->APIUrl = $untappdAPIUrl;
        $this->clientID = $untappdAPIClientID;
        $this->clientSecret = $untappdAPIClientSecret;
        $this->OAuthRedirectURL = $untappdAPIOAuthRedirectURL;
        $this->em = $em;
    }
    
    /**
     * This function will authenticate the user with OAuth to get an access token.
     *
     * @param string $code The OAuth code provided by Untappd.
     *
     * @return string Returns an access token
     */
    public function authorize($code)
    {
        $headers = array('Accept' => 'application/json');
        $query = array(
            'client_id' => $this->clientID,
            'client_secret' => $this->clientSecret,
            'response_type' => 'code',
            'redirect_url' => $this->OAuthRedirectURL,
            'code' => $code
        );
        $response = Unirest\Request::get('https://untappd.com/oauth/authorize/', $headers, $query);
        if ($response->code != 200) {
            if ($response->code == 401 && $response->body->meta->error_type == "invalid_token") {
                return false;
            } else {
                throw new \Exception("API Error. HTTP code: " . $response->code);
            }
        }
        return $response->body->response->access_token;
    }
    
    /**
     * This method will return the user information for a selected user.
     *
     * @param string $username The username that you wish to call the request upon.
     * @param string $accessToken The authenticated user's access token. If $username is null, this function will return info for the authenticated user
     * @param string $compact You can pass "true" here only show the user infomation, and remove the "checkins", "media", "recent_brews", etc attributes
     *
     * @return string Returns a JSON object containing the user information
     */
    public function getUserInfo($username, $accessToken = null, $compact = "false")
    {
        $headers = array('Accept' => 'application/json');
        $query = array('compact' => $compact);
        $path = '/v4/user/info/';
        if (!is_null($username)) {
            $path = $path . $username;
        }
        if (is_null($accessToken)) {
            if (is_null($username)) {
                throw new \Exception("Username can't be null when the request isn't authenticated.");
            }
            $query['client_id'] = $this->clientID;
            $query['client_secret'] = $this->clientSecret;
        } else {
            $query['access_token'] = $accessToken;
        }
        $response = Unirest\Request::get($this->APIUrl . $path, $headers, $query);
        if ($response->code != 200) {
            if ($response->code == 401 && $response->body->meta->error_type == "invalid_token") {
                $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('internal_untappd_access_token' => $accessToken));
                $user->setInternalUntappdAccessToken(null);
                $this->em->persist($user);
                $this->em->flush();
                return false;
            } else {
                throw new \Exception("API Error. HTTP code: " . $response->code);
            }
        } else {
            $this->logAPIQuery($path, $response, $accessToken);
        }
        return $response;
    }
        
    /**
     * This method will return a list of the user's wish listed beers.
     *
     * @param string $username The username that you wish to call the request upon.
     * @param integer $offset The numeric offset that you what results to start
     * @param integer $limit The number of results to return, max of 50, default is 25
     * @param string $sort You can sort the results using these values: date - sorts by date (default), checkin - sorted by highest checkin, highest_rated - sorts by global rating descending order, lowest_rated - sorts by global rating ascending order, highest_abv - highest ABV from the wishlist, lowest_abv - lowest ABV from the wishlist
     *
     * @return string Returns a JSON object containing the user wishlist
     */
    public function getUserWishList($username, $offset = 0, $limit = 25, $sort = "date")
    {
        if (!is_null($limit) && $limit > 25) {
            throw new \Exception('Maximum for limit is 25 (requested ' . $limit . ')');
        }
        $headers = array('Accept' => 'application/json');
        $query = array(
            'client_id' => $this->clientID, 
            'client_secret' => $this->clientSecret,
            'offset' => $offset,
            'limit' => $limit,
            'sort' => $sort
        );
        $response = Unirest\Request::get($this->APIUrl . '/v4/user/wishlist/' . $username, $headers, $query);
        if ($response->code != 200) {
            if ($response->code == 401 && $response->body->meta->error_type == "invalid_token") {
                $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('internal_untappd_access_token' => $accessToken));
                $user->setInternalUntappdAccessToken(null);
                $this->em->persist($user);
                $this->em->flush();
                return false;
            } else {
                throw new \Exception("API Error. HTTP code: " . $response->code);
            }
        } else {
            $this->logAPIQuery('/v4/user/wishlist/' . $username, $response);
        }
        return $response;
    }
    
    /**
     * This method will return a list of the user's friends.
     *
     * @param string $username The username that you wish to call the request upon.
     * @param string $accessToken The authenticated user's access token. If $username is null, this function will return info for the authenticated user
     * @param integer $offset The numeric offset that you what results to start
     * @param integer $limit The number of records that you will return (max 25)
     *
     * @return string Returns a JSON object containing the user wishlist
     */
    public function getUserFriends($username, $accessToken = null, $offset = 0, $limit = 25)
    {
        if (!is_null($limit) && $limit > 25) {
            throw new \Exception('Maximum for limit is 25 (requested ' . $limit . ')');
        }
        $headers = array('Accept' => 'application/json');
        $query = array(
            'offset' => $offset,
            'limit' => $limit
        );
        $path = '/v4/user/friends/';
        if (!is_null($username)) {
            $path = $path . $username;
        }
        if (is_null($accessToken)) {
            if (is_null($username)) {
                throw new \Exception("Username can't be null when the request isn't authenticated.");
            }
            $query['client_id'] = $this->clientID;
            $query['client_secret'] = $this->clientSecret;
        } else {
            $query['access_token'] = $accessToken;
        }
        $response = Unirest\Request::get($this->APIUrl . $path, $headers, $query);
        if ($response->code != 200) {
            if ($response->code == 401 && $response->body->meta->error_type == "invalid_token") {
                $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('internal_untappd_access_token' => $accessToken));
                $user->setInternalUntappdAccessToken(null);
                $this->em->persist($user);
                $this->em->flush();
                return false;
            } else {
                throw new \Exception("API Error. HTTP code: " . $response->code);
            }
        } else {
            $this->logAPIQuery($path, $response, $accessToken);
        }
        return $response;
    }
    
    /**
     * This method will return a list of the user's badges.
     *
     * @param string $username The username that you wish to call the request upon.
     * @param integer $offset The numeric offset that you what results to start
     * @param integer $limit The number of records that you will return (max 25, default 25)
     *
     * @return string Returns a JSON object containing the user badges
     */
    public function getUserBadges($username, $offset = 0, $limit = 25)
    {
        if (!is_null($limit) && $limit > 25) {
            throw new \Exception('Maximum for limit is 25 (requested ' . $limit . ')');
        }
        $headers = array('Accept' => 'application/json');
        $query = array(
            'client_id' => $this->clientID,
            'client_secret' => $this->clientSecret,
            'offset' => $offset,
            'limit' => $limit
        );
        $response = Unirest\Request::get($this->APIUrl . '/v4/user/badges/' . $username, $headers, $query);
        if ($response->code != 200) {
            if ($response->code == 401 && $response->body->meta->error_type == "invalid_token") {
                $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('internal_untappd_access_token' => $accessToken));
                $user->setInternalUntappdAccessToken(null);
                $this->em->persist($user);
                $this->em->flush();
                return false;
            } else {
                throw new \Exception("API Error. HTTP code: " . $response->code);
            }
        } else {
            $this->logAPIQuery('/v4/user/badges/' . $username, $response);
        }
        return $response;
    }
    
    /**
     * This method will return a list of the user's distinct beers.
     *
     * @param string $username The username that you wish to call the request upon.
     * @param integer $offset The numeric offset that you what results to start
     * @param integer $limit The number of records that you will return (max 50, default 25)
     * @param string $sort Your can sort the results using these values: date - sorts by date (default), checkin - sorted by highest checkin, highest_rated - sorts by global rating descending order, lowest_rated - sorts by global rating ascending order, highest_rated_you - the user's highest rated beer, lowest_rated_you - the user's lowest rated beer
     *
     * @return string Returns a JSON object containing the user distinct beers
     */
    public function getUserBeers($username, $offset = 0, $limit = 25, $sort = "date")
    {
        if (!is_null($limit) && $limit > 25) {
            throw new \Exception('Maximum for limit is 25 (requested ' . $limit . ')');
        }
        $headers = array('Accept' => 'application/json');
        $query = array(
            'client_id' => $this->clientID,
            'client_secret' => $this->clientSecret,
            'offset' => $offset,
            'limit' => $limit,
            'sort' => $sort
        );
        $response = Unirest\Request::get($this->APIUrl . '/v4/user/beers/' . $username, $headers, $query);
        if ($response->code != 200) {
            if ($response->code == 401 && $response->body->meta->error_type == "invalid_token") {
                $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('internal_untappd_access_token' => $accessToken));
                $user->setInternalUntappdAccessToken(null);
                $this->em->persist($user);
                $this->em->flush();
                return false;
            } else {
                throw new \Exception("API Error. HTTP code: " . $response->code);
            }
        } else {
            $this->logAPIQuery('/v4/user/beers/' . $username, $response);
        }
        return $response;
    }
    
    /**
     * This method will allow you to see extended information about a brewery.
     *
     * @param integer $breweryID The Brewery ID that you want to display checkins
     * @param string $compact You can pass "true" here only show the brewery infomation, and remove the "checkins", "media", "beer_list", etc attributes
     *      *
     * @return string Returns a JSON object containing the brewery info
     */
    public function getBreweryInfo($breweryID, $compact = "false")
    {
        $headers = array('Accept' => 'application/json');
        $query = array(
            'client_id' => $this->clientID,
            'client_secret' => $this->clientSecret,
            'compact' => $compact
        );
        $response = Unirest\Request::get($this->APIUrl . '/v4/brewery/info/' . $breweryID, $headers, $query);
        if ($response->code != 200) {
            throw new \Exception("API Error. HTTP code: " . $response->code);
        } else {
            if ($response->code == 401 && $response->body->meta->error_type == "invalid_token") {
                $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('internal_untappd_access_token' => $accessToken));
                $user->setInternalUntappdAccessToken(null);
                $this->em->persist($user);
                $this->em->flush();
                return false;
            } else {
                throw new \Exception("API Error. HTTP code: " . $response->code);
            }
        }
        return $response;
    }
    
    /**
     * This method will allow you to see extended information about a beer.
     *
     * @param integer $beerID The Brewery ID that you want to display checkins
     * @param string $accessToken The API token to use
     * @param string $compact You can pass "true" here only show the beer infomation, and remove the "checkins", "media", "variants", etc attributes
     *
     * @return string Returns a JSON object containing the beer info
     */
    public function getBeerInfo($beerID, $accessToken = null, $compact = "false")
    {
        $headers = array('Accept' => 'application/json');
        $query = array(
            'compact' => $compact
        );
        if (is_null($accessToken)) {
            $query['client_id'] = $this->clientID;
            $query['client_secret'] = $this->clientSecret;
        } else {
            $query['access_token'] = $accessToken;
        }
        
        $response = Unirest\Request::get($this->APIUrl . '/v4/beer/info/' . $beerID, $headers, $query);
        if ($response->code == 404) {
            return "DELETED";
        } elseif ($response->code != 200) {
            if ($response->code == 401 && $response->body->meta->error_type == "invalid_token") {
                $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('internal_untappd_access_token' => $accessToken));
                $user->setInternalUntappdAccessToken(null);
                $this->em->persist($user);
                $this->em->flush();
                return false;
            } else {
                throw new \Exception("API Error. HTTP code: " . $response->code);
            }
        } else {
            $this->logAPIQuery('/v4/beer/info/' . $beerID, $response, $accessToken);
        }
        return $response;
    }
    
    /**
     * This method will allow you to see extended information about a venue.
     *
     * @param integer $venueID The Venue ID that you want to get info
     * @param string $compact You can pass "true" here only show the venue infomation, and remove the "checkins", "media", "top_beers", etc attributes
     *
     * @return string Returns a JSON object containing the venue info
     */
    public function getVenueInfo($venueID, $compact = "false")
    {
        $headers = array('Accept' => 'application/json');
        $query = array(
            'client_id' => $this->clientID,
            'client_secret' => $this->clientSecret,
            'compact' => $compact
        );
        $response = Unirest\Request::get($this->APIUrl . '/v4/venue/info/' . $venueID, $headers, $query);
        if ($response->code != 200) {
            if ($response->code == 401 && $response->body->meta->error_type == "invalid_token") {
                $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('internal_untappd_access_token' => $accessToken));
                $user->setInternalUntappdAccessToken(null);
                $this->em->persist($user);
                $this->em->flush();
                return false;
            } else {
                throw new \Exception("API Error. HTTP code: " . $response->code);
            }
        } else {
            $this->logAPIQuery('/v4/venue/info/' . $venueID, $response);
        }
        return $response;
    }
    
    /**
     * This method will allow you to see the history of checkins for a venue.
     *
     * @param integer $venueID The Venue ID that you want to display checkins
     * @param string $accessToken The API token to use
     * @param integer $max_id The checkin ID that you want the results to start with
     * @param integer $min_id Returns only checkins that are newer than this value
     * @param integer $limit The number of results to return, max of 50, default is 25
     *
     * @return string Returns a JSON object containing the venue checkins
     */
    public function getVenueCheckins($venueID, $accessToken = null, $max_id = null, $min_id = null, $limit = 25)
    {
        if (!is_null($limit) && $limit > 25) {
            throw new \Exception('Maximum for limit is 25 (requested ' . $limit . ')');
        }
        $headers = array('Accept' => 'application/json');
        
        $query = array('limit' => $limit);
        if (!is_null($max_id)) {
            $query['max_id'] = $max_id;
        }
        if (!is_null($min_id)) {
            $query['min_id'] = $min_id;
        }
        if (is_null($accessToken)) {
            $query['client_id'] = $this->clientID;
            $query['client_secret'] = $this->clientSecret;
        } else {
            $query['access_token'] = $accessToken;
        }
        $response = Unirest\Request::get($this->APIUrl . '/v4/venue/checkins/' . $venueID, $headers, $query);
        if ($response->code != 200) {
            if ($response->body->meta->error_detail = "Your 'max_id' is too low, please use a valid that is closer to the most recent ID. We only allow scanning back to a max of 300 checkins.") {
                $response->body = json_decode('{"response":{"pagination":{"max_id":""}}}');
                return $response;
            }
            if ($response->code == 401 && $response->body->meta->error_type == "invalid_token") {
                $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('internal_untappd_access_token' => $accessToken));
                $user->setInternalUntappdAccessToken(null);
                $this->em->persist($user);
                $this->em->flush();
                return false;
            } else {
                throw new \Exception("API Error. HTTP code: " . $response->code);
            }
        } else {
            $log = $this->logAPIQuery('/v4/venue/checkins/' . $venueID, $response, $accessToken);
        }
        return $response;
    }
    
    /**
     * This will allow you to search across the Untappd database for beers and breweries.
     *
     * @param string $querystr The search term that you want to search
     * @param integer $offset The numeric offset that you what results to start
     * @param integer $limit The number of results to return, max of 50, default is 25
     * @param string $sort Your can sort the results using these values: checkin - sorts by checkin count (default), name - sorted by alphabetic beer name
     *
     * @return string Returns a JSON object containing the search results
     */
    public function searchBeer($querystr, $accessToken = null, $offset = 0, $limit = 50, $sort = "checkin")
    {
        if (!is_null($limit) && $limit > 50) {
            throw new \Exception('Maximum for limit is 50 (requested ' . $limit . ')');
        }
        $headers = array('Accept' => 'application/json');
        $query = array(
            'q' => str_replace("#", "", $querystr),
            'offset' => $offset,
            'limit' => $limit,
            'sort' => $sort
        );
        if (is_null($accessToken)) {
            $query['client_id'] = $this->clientID;
            $query['client_secret'] = $this->clientSecret;
        } else {
            $query['access_token'] = $accessToken;
        }
        $response = Unirest\Request::get($this->APIUrl . '/v4/search/beer/', $headers, $query);
        if ($response->code != 200) {
            if ($response->code == 401 && $response->body->meta->error_type == "invalid_token") {
                $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('internal_untappd_access_token' => $accessToken));
                $user->setInternalUntappdAccessToken(null);
                $this->em->persist($user);
                $this->em->flush();
                return false;
            } else {
                throw new \Exception("API Error. HTTP code: " . $response->code);
            }
        } else {
            $this->logAPIQuery('/v4/search/beer/ (' . $querystr . ')', $response, $accessToken);
        }
        return $response;
    }
    
    /**
     * This will allow you to search exclusively for breweries in the Untappd system.
     *
     * @param string $querystr The search term that you want to search
     * @param integer $offset The numeric offset that you what results to start
     * @param integer $limit The number of results to return, max of 50, default is 25
     *
     * @return string Returns a JSON object containing the search results
     */
    public function searchBrewery($querystr, $offset = 0, $limit = 25, $sort = "checkin")
    {
        if (!is_null($limit) && $limit > 25) {
            throw new \Exception('Maximum for limit is 25 (requested ' . $limit . ')');
        }
        $headers = array('Accept' => 'application/json');
        $query = array(
            'client_id' => $this->clientID,
            'client_secret' => $this->clientSecret,
            'q' => $querystr,
            'offset' => $offset,
            'limit' => $limit
        );
        $response = Unirest\Request::get($this->APIUrl . '/v4/search/brewery/', $headers, $query);
        if ($response->code != 200) {
            if ($response->code == 401 && $response->body->meta->error_type == "invalid_token") {
                $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('internal_untappd_access_token' => $accessToken));
                $user->setInternalUntappdAccessToken(null);
                $this->em->persist($user);
                $this->em->flush();
                return false;
            } else {
                throw new \Exception("API Error. HTTP code: " . $response->code);
            }
        } else {
            $this->logAPIQuery('/v4/search/brewery/', $response);
        }
        return $response;
    }
    
    /**
     * This will allow you to check-in to a beer as the authenticated user.
     *
     * @param string $accessToken The access token for the acting user
     * @param integer $bid The numeric Beer ID you want to check into
     * @param string $shout The text you would like to include as a comment of the checkin.
     * @param integer $rating The rating score you would like to add for the beer. This can only be 1 to 5 (half ratings are included).
     * @param string $foursquareId The MD5 hash ID of the Venue you want to attach the beer checkin. This HAS TO BE the MD5 non-numeric hash from the foursquare v2
     * @param integer $geolat The numeric Latitude of the user. This is required if you add a location.
     * @param integer $geolng The numeric Longitude of the user. This is required if you add a location.
     * @param integer $gmtOffset The numeric value of hours the user is away from the GMT (Greenwich Mean Time)
     * @param string $facebook If you want to push this check-in to the users' Facebook account, pass this value as "on", default is "off"
     * @param string $twitter If you want to push this check-in to the users' Twitter account, pass this value as "on", default is "off"
     * @param string $foursquare If you want to push this check-in to the users' Foursquare account, pass this value as "on", default is "off". You must include a location for this to enabled.
     * @return string Returns a JSON object containing the posted checkin
     */
    public function addCheckin($accessToken, $bid, $shout = null, $rating = null, $foursquareId = null, $geolat = null, $geolng = null, $gmtOffset = 0, $timezone = 'GMT', $facebook = 'off', $twitter = 'off', $foursquare = 'off')
    {
        $headers = array('Accept' => 'application/json');
        $query = array(
            'access_token' => $accessToken,
            'gmt_offset' => $gmtOffset,
            'timezone' => $timezone,
            'bid' => $bid,
            'facebook' => $facebook,
            'twitter' => $twitter,
            'foursquare' => $foursquare
        );
        if ($foursquareId && $geolat && $geolng) {
            $query['foursquare_id'] = $foursquareId;
            $query['geolat'] = $geolat;
            $query['geolng'] = $geolng;
        }
        if ($shout) {
            $query['shout'] = $shout;
        };
        if ($rating) {
            $query['rating'] = $rating;
        }
        $response = Unirest\Request::post($this->APIUrl . '/v4/checkin/add?access_token=' . $accessToken, $headers, $query);
        if ($response->code != 200) {
            if ($response->code == 401 && $response->body->meta->error_type == "invalid_token") {
                $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('internal_untappd_access_token' => $accessToken));
                $user->setInternalUntappdAccessToken(null);
                $this->em->persist($user);
                $this->em->flush();
                return false;
            } else {
                throw new \Exception("API Error. HTTP code: " . $response->code);
            }
        } else {
            $this->logAPIQuery('/v4/checkin/add/', $response, $accessToken);
        }
        return $response;
    }
    
    /**
     * This method allows you the obtain all the check-in feed of the selected user. 
     *
     * @param string $username The username that you wish to call the request upon.
     * @param string $accessToken The authenticated user's access token. If $username is null, this function will return info for the authenticated user
     * @param integer $max_id The checkin ID that you want the results to start with
     * @param integer $min_id Returns only checkins that are newer than this value
     * @param integer $limit The number of results to return, max of 50, default is 25
     *
     * @return string Returns a JSON object containing the search results
     */
    public function getUserActivityFeed($username, $accessToken = null, $max_id = null, $min_id = null, $limit = 25)
    {
        if (!is_null($limit) && $limit > 50) {
            throw new \Exception('Maximum for limit is 50 (requested ' . $limit . ')');
        }
        $headers = array('Accept' => 'application/json');
        $query = array('limit' => $limit);
        if (!is_null($max_id)) {
            $query['max_id'] = $max_id;
        }
        if (!is_null($min_id)) {
            $query['min_id'] = $min_id;
        }
        $path = '/v4/user/checkins/';
        if (!is_null($username)) {
            $path = $path . $username;
        }
        if (is_null($accessToken)) {
            if (is_null($username)) {
                throw new \Exception("Username can't be null when the request isn't authenticated.");
            }
            $query['client_id'] = $this->clientID;
            $query['client_secret'] = $this->clientSecret;
        } else {
            $query['access_token'] = $accessToken;
        }
        $response = Unirest\Request::get($this->APIUrl . $path, $headers, $query);
        if ($response->code != 200) {
            if ($response->code == 401 && $response->body->meta->error_type == "invalid_token") {
                $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('internal_untappd_access_token' => $accessToken));
                $user->setInternalUntappdAccessToken(null);
                $this->em->persist($user);
                $this->em->flush();
                return false;
            } else {
                throw new \Exception("API Error. HTTP code: " . $response->code);
            }
        } else {
            $this->logAPIQuery($path, $response, $accessToken);
        }
        return $response;
    }
    
    public function logAPIQuery($url, $response, $accessToken = null) {
        $log = new APIQueryLog();
        $log->setMethod($url);
        $log->setDate(new \DateTime());
        if (array_key_exists('x-ratelimit-remaining', $response->headers)) {
            $log->setRemainingQueries($response->headers['x-ratelimit-remaining']);
        } elseif (array_key_exists('X-Ratelimit-Remaining', $response->headers)) {
            $log->setRemainingQueries($response->headers['X-Ratelimit-Remaining']);
        }
        if ($accessToken) {
            $log->setUser($this->em->getRepository('\App\Entity\User\User')->findOneBy(array('internal_untappd_access_token' => $accessToken)));
        }
        $this->em->persist($log);
        $this->em->flush();
        $this->em->detach($log);
        return $log;
    }
        
    public function disableSqlLogger() {
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
    }
    
}