<?php
// src/Service/UntappdAPI.php
namespace App\Service;

use Unirest;

class UntappdAPI
{
    private $untappdAPIUrl;
    private $untappdAPIClientID;
    private $untappdAPIClientSecret;
    private $untappdAPIOAuthRedirectURL;
    
    public function __construct($untappdAPIUrl, $untappdAPIClientID, $untappdAPIClientSecret, $untappdAPIOAuthRedirectURL)
    {
        $this->APIUrl = $untappdAPIUrl;
        $this->clientID = $untappdAPIClientID;
        $this->clientSecret = $untappdAPIClientSecret;
        $this->OAuthRedirectURL = $untappdAPIOAuthRedirectURL;
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
            throw new \Exception("API Error. HTTP code: " . $response->code);
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
            throw new \Exception("API Error. HTTP code: " . $response->code);
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
            throw new \Exception("API Error. HTTP code: " . $response->code);
        }
        return $response;
    }
    
    /**
     * This method will return a list of the user's friends.
     *
     * @param string $username The username that you wish to call the request upon.
     * @param integer $offset The numeric offset that you what results to start
     * @param integer $limit The number of records that you will return
     *
     * @return string Returns a JSON object containing the user wishlist
     */
    public function getUserFriends($username, $offset = 0, $limit = 25)
    {
        $headers = array('Accept' => 'application/json');
        $query = array(
            'client_id' => $this->clientID,
            'client_secret' => $this->clientSecret,
            'offset' => $offset,
            'limit' => $limit
        );
        $response = Unirest\Request::get($this->APIUrl . '/v4/user/friends/' . $username, $headers, $query);
        if ($response->code != 200) {
            throw new \Exception("API Error. HTTP code: " . $response->code);
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
        $headers = array('Accept' => 'application/json');
        $query = array(
            'client_id' => $this->clientID,
            'client_secret' => $this->clientSecret,
            'offset' => $offset,
            'limit' => $limit
        );
        $response = Unirest\Request::get($this->APIUrl . '/v4/user/badges/' . $username, $headers, $query);
        if ($response->code != 200) {
            throw new \Exception("API Error. HTTP code: " . $response->code);
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
            throw new \Exception("API Error. HTTP code: " . $response->code);
        }
        return $response;
    }
    
    /**
     * This method will allow you to see extended information about a brewery.
     *
     * @param integer $breweryID The Brewery ID that you want to display checkins
     * @param string $compact You can pass "true" here only show the brewery infomation, and remove the "checkins", "media", "beer_list", etc attributes
     *      *
     * @return string Returns a JSON object containing the user badges
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
        }
        return $response;
    }
    
    /**
     * This method will allow you to see extended information about a beer.
     *
     * @param integer $beerID The Brewery ID that you want to display checkins
     * @param string $compact You can pass "true" here only show the beer infomation, and remove the "checkins", "media", "variants", etc attributes
     *
     * @return string Returns a JSON object containing the user badges
     */
    public function getBeerInfo($beerID, $compact = "false")
    {
        $headers = array('Accept' => 'application/json');
        $query = array(
            'client_id' => $this->clientID,
            'client_secret' => $this->clientSecret,
            'compact' => $compact
        );
        $response = Unirest\Request::get($this->APIUrl . '/v4/beer/info/' . $beerID, $headers, $query);
        if ($response->code != 200) {
            throw new \Exception("API Error. HTTP code: " . $response->code);
        }
        return $response;
    }
    
    /**
     * This method will allow you to see extended information about a beer.
     *
     * @param integer $venueID The Venue ID that you want to display checkins
     * @param string $compact You can pass "true" here only show the venue infomation, and remove the "checkins", "media", "top_beers", etc attributes
     *
     * @return string Returns a JSON object containing the user badges
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
            throw new \Exception("API Error. HTTP code: " . $response->code);
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
    public function searchBeer($querystr, $offset = 0, $limit = 25, $sort = "checkin")
    {
        $headers = array('Accept' => 'application/json');
        $query = array(
            'client_id' => $this->clientID,
            'client_secret' => $this->clientSecret,
            'q' => $querystr,
            'offset' => $offset,
            'limit' => $limit,
            'sort' => $sort
        );
        $response = Unirest\Request::get($this->APIUrl . '/v4/search/beer/', $headers, $query);
        if ($response->code != 200) {
            throw new \Exception("API Error. HTTP code: " . $response->code);
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
            throw new \Exception("API Error. HTTP code: " . $response->code);
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
            throw new \Exception("API Error. HTTP code: " . $response->code);
        }
        return $response;
    }
}