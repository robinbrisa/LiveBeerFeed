<?php
// src/Service/UntappdAPISerializer.php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User\User as User;
use App\Entity\User\Friendship as Friendship;
use App\Entity\Checkin\Checkin as Checkin;
use App\Entity\Checkin\Source as Source;
use App\Entity\Checkin\Toast as Toast;
use App\Entity\Checkin\Comment as Comment;
use App\Entity\Checkin\Media as Media;
use App\Entity\Brewery\Brewery as Brewery;
use App\Entity\Brewery\Type as Type;
use App\Entity\Venue\Venue as Venue;
use App\Entity\Venue\Category as Category;
use App\Entity\Badge\Badge as Badge;
use App\Entity\Badge\BadgeRelation as BadgeRelation;
use App\Entity\Beer\Beer as Beer;
use App\Entity\Beer\Style as Style;

class UntappdAPISerializer
{
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    /**
     * This function will add a User and all related information in the database
     *
     * @param string $user A JSON User object returned by the User/Info API.
     * @param string $accessToken Provide an access token if necessary
     *
     * @return User Returns a User object
     */
    public function handleUserObject($userData, $accessToken = null)
    {
        $user = $this->buildUserWithFullInformation($userData, $accessToken);
        $this->em->persist($user);
        $this->em->flush();
        $this->handleCheckinsArray($userData->checkins->items, $user);
        return $user;
    }
    
    public function handleCheckinsArray($checkins) {
        foreach ($checkins as $checkinData) {
            $checkin = $this->buildCheckin($checkinData);
            $user = $this->buildUserWithLowInformation($checkinData->user);
            $checkin->setUser($user);
            $brewery = $this->buildBreweryWithLowInformation($checkinData->brewery);
            $beer = $this->buildBeerWithLowInformation($checkinData->beer, $brewery);
            $checkin->setBeer($beer);
            if (isset($checkinData->venue->venue_id)) {
                $venue = $this->buildVenueWithLowInformation($checkinData->venue);
                $this->em->persist($venue);
            } else {
                $venue = null;
            }
            $checkin->setVenue($venue);
            $source = $this->buildSource($checkinData->source);
            $checkin->setSource($source);
            $checkin->resetToasts();
            foreach ($checkinData->toasts->items as $toastData) {
                $toast = $this->buildToast($toastData);
                $checkin->addToast($toast);
            }
            $checkin->resetComments();
            foreach ($checkinData->comments->items as $commentData) {
                $comment = $this->buildComment($commentData);
                $checkin->addComment($comment);
            }
            $checkin->resetMedias();
            foreach ($checkinData->media->items as $mediaData) {
                $media = $this->buildCheckinMedia($mediaData);
                $checkin->addMedia($media);
            }
            foreach ($checkinData->badges->items as $badgeData) {
                $badge = $this->buildBadgeWithLowInformation($badgeData);
                $this->createBadgeRelation($badge, $user, $checkin, $badgeData->user_badge_id, $badgeData->created_at);
            }
            $this->em->persist($checkin);
        }
        $this->em->flush();
        return true;
    }
    
    public function handleBreweryObject($breweryData) {
        $brewery = $this->buildBreweryWithFullInformation($breweryData);
        $this->em->persist($brewery);
        $this->em->flush();
        $this->handleBeersArray($breweryData->beer_list->items, $brewery);
        return $brewery;
    }
    
    public function handleBeersArray($beers, $brewery) {
        $beersCollection = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($beers as $beerData) {
            $beer = $this->buildBeerWithMediumInformation($beerData->beer, $brewery);
            $this->em->persist($beer);
        }
        $this->em->flush();
        return $beersCollection;
    }
    
    public function handleBeerObject($beer) {
        $beer = $this->buildBeerWithFullInformation($beer);
        $this->em->persist($beer);
        $this->em->flush();
        return $beer;
    }
    
    public function handleVenueObject($venueData) {
        $venue = $this->buildVenueWithFullInformation($venueData);
        $this->em->persist($venue);
        $this->em->flush();
        $this->handleCheckinsArray($venueData->checkins->items, $venue);
        return $venue;
    }
    
    public function handleFriendsArray(User $user, $friends) {
        foreach ($friends as $friendData) {
            $friend = $this->buildUserWithLowInformation($friendData->user);
            $this->em->persist($friend);
            if (!$this->em->getRepository('\App\Entity\User\Friendship')->findOneBy(array('user' => $user, 'friend' => $friend))) {
                $user->addFriend($friend, \DateTime::createFromFormat(DATE_RFC2822, $friendData->created_at)->setTimeZone(new \DateTimeZone(date_default_timezone_get())));
            }
        }
        $this->em->persist($user);
        return $user;
    }
    
    private function buildUserWithLowInformation($user) {
        $output = $this->em->getRepository('\App\Entity\User\User')->find($user->uid);
        if (!$output) {
            $output = new User();
            $output->setId($user->uid);
            $output->setInternalDataGathered(false);
            $output->setIsSupporter(false);
            $output->setIsModerator(false);
        }
        $output->setUserName($user->user_name);
        $output->setFirstName($user->first_name);
        $output->setLastName($user->last_name);
        $output->setUserAvatar($user->user_avatar);
        if (isset($user->bio)) { $output->setBio($user->bio); }
        if (isset($user->location)) { $output->setLocation($user->location); }
        if (isset($user->account_type)) { $output->setAccountType($user->account_type); }
        if (isset($user->is_supporter)) { $output->setIsSupporter($user->is_supporter); }
        $this->em->persist($output);
        return $output;
    }
    
    private function buildUserWithFullInformation($user, $accessToken = null) {
        $output = $this->em->getRepository('\App\Entity\User\User')->find($user->id);
        if (!$output) {
            $output = new User();
            $output->setId($user->id);
        }
        $output->setUserName($user->user_name);
        $output->setFirstName($user->first_name);
        $output->setLastName($user->last_name);
        $output->setUserAvatar($user->user_avatar);
        $output->setUserAvatarHd($user->user_avatar_hd);
        $output->setUserCoverPhoto($user->user_cover_photo);
        $output->setUserCoverPhotoOffset($user->user_cover_photo_offset);
        $output->setIsPrivate($user->is_private);
        $output->setLocation($user->location);
        $output->setUrl($user->url);
        $output->setBio($user->bio);
        $output->setIsSupporter($user->is_supporter);
        $output->setIsModerator($user->is_moderator);
        $output->setUntappdUrl($user->untappd_url);
        $output->setAccountType($user->account_type);
        $output->setTotalBadges($user->stats->total_badges);
        $output->setTotalFriends($user->stats->total_friends);
        $output->setTotalCheckins($user->stats->total_checkins);
        $output->setTotalBeers($user->stats->total_beers);
        $output->setTotalCreatedBeers($user->stats->total_created_beers);
        $output->setTotalFollowings($user->stats->total_followings);
        $output->setTotalPhotos($user->stats->total_photos);
        if (!is_null($accessToken)) { $output->setInternalUntappdAccessToken($accessToken); }
        if (isset($user->contact->facebook)) { $output->setFacebook($user->contact->facebook); }
        if (isset($user->contact->instagram)) { $output->setInstagram($user->contact->instagram); }
        if (isset($user->contact->twitter)) { $output->setTwitter($user->contact->twitter); }
        if (isset($user->contact->url)) { $output->setUrl($user->contact->url); }
        $output->setDateJoined(\DateTime::createFromFormat(DATE_RFC2822, $user->date_joined)->setTimeZone(new \DateTimeZone(date_default_timezone_get())));
        $output->setInternalDataGathered(true);
        return $output;
    }
    
    private function buildBeerWithLowInformation($beer, $brewery) {
        $output = $this->em->getRepository('\App\Entity\Beer\Beer')->find($beer->bid);
        if (!$output) {
            $output = new Beer();
            $output->setId($beer->bid);
            $output->setInternalDataGathered(false);
        }
        $output->setName($beer->beer_name);
        $output->setLabel($beer->beer_label);
        $output->setSlug($beer->beer_slug);
        $output->setAbv($beer->beer_abv);
        $output->setActive($beer->beer_active);
        $output->setBrewery($brewery);
        
        $style = $this->em->getRepository('\App\Entity\Beer\Style')->findOneBy(array('name' => $beer->beer_style));
        if (!$style) {
            $style = new Style();
            $style->setName($beer->beer_style);
            $this->em->persist($style);
        }
        $output->setStyle($style);
        return $output;
    }
    
    private function buildBeerWithMediumInformation($beer, $brewery) {
        $output = $this->em->getRepository('\App\Entity\Beer\Beer')->find($beer->bid);
        if (!$output) {
            $output = new Beer();
            $output->setId($beer->bid);
            $output->setInternalDataGathered(false);
        }
        $output->setName($beer->beer_name);
        $output->setLabel($beer->beer_label);
        $output->setSlug($beer->beer_slug);
        $output->setAbv($beer->beer_abv);
        $output->setIbu($beer->beer_ibu);
        $output->setDescription($beer->beer_description);
        $output->setActive($beer->is_in_production);
        $output->setCreatedAt(\DateTime::createFromFormat(DATE_RFC2822, $beer->created_at)->setTimeZone(new \DateTimeZone(date_default_timezone_get())));
        $output->setRatingScore($beer->rating_score);
        $output->setRatingCount($beer->rating_count);
        $output->setBrewery($brewery);
        $style = $this->em->getRepository('\App\Entity\Beer\Style')->findOneBy(array('name' => $beer->beer_style));
        if (!$style) {
            $style = new Style();
            $style->setName($beer->beer_style);
            $this->em->persist($style);
        }
        $output->setStyle($style);
        return $output;
    }
    
    private function buildBeerWithFullInformation($beer) {
        $output = $this->em->getRepository('\App\Entity\Beer\Beer')->find($beer->bid);
        if (!$output) {
            $output = new Beer();
            $output->setId($beer->bid);
            $output->setInternalDataGathered(false);
        }
        $output->setName($beer->beer_name);
        $output->setLabel($beer->beer_label);
        $output->setSlug($beer->beer_slug);
        $output->setAbv($beer->beer_abv);
        $output->setIbu($beer->beer_ibu);
        $output->setDescription($beer->beer_description);
        $output->setActive($beer->is_in_production);        
        $output->setCreatedAt(\DateTime::createFromFormat(DATE_RFC2822, $beer->created_at)->setTimeZone(new \DateTimeZone(date_default_timezone_get())));
        $output->setRatingScore($beer->rating_score);
        $output->setRatingCount($beer->rating_count);
        $output->setUniqueCount($beer->total_user_count);
        $output->setTotalCount($beer->stats->total_count);
        $brewery = $this->em->getRepository('\App\Entity\Brewery\Brewery')->find($beer->brewery->brewery_id);
        if (!$brewery) {
            $brewery = $this->buildBreweryWithLowInformation($beer->brewery);
        }
        $output->setBrewery($brewery);
        $style = $this->em->getRepository('\App\Entity\Beer\Style')->findOneBy(array('name' => $beer->beer_style));
        if (!$style) {
            $style = new Style();
            $style->setName($beer->beer_style);
            $this->em->persist($style);
        }
        $output->setStyle($style);
        return $output;
    }
    
    private function buildBreweryWithLowInformation($brewery) {
        $output = $this->em->getRepository('\App\Entity\Brewery\Brewery')->find($brewery->brewery_id);
        if (!$output) {
            $output = new Brewery();
            $output->setId($brewery->brewery_id);
            $output->setInternalDataGathered(false);
            $output->setActive(true);
            $output->setIsIndependent(true);
        }
        $output->setName($brewery->brewery_name);
        $output->setSlug($brewery->brewery_slug);
        $output->setLabel($brewery->brewery_label);
        $output->setCountryName($brewery->country_name);
        if (isset($brewery->brewery_active)) { $output->setActive($brewery->brewery_active); }
        if (isset($brewery->contact->facebook)) { $output->setFacebook($brewery->contact->facebook); }
        if (isset($brewery->contact->instagram)) { $output->setInstagram($brewery->contact->instagram); }
        if (isset($brewery->contact->twitter)) { $output->setTwitter($brewery->contact->twitter); }
        if (isset($brewery->contact->url)) { $output->setUrl($brewery->contact->url); }
        if ($brewery->location->brewery_city != "") { $output->setCity($brewery->location->brewery_city); }
        if ($brewery->location->brewery_state != "") { $output->setState($brewery->location->brewery_state); }
        if ($brewery->location->lat != "0") { $output->setLatitude($brewery->location->lat); }
        if ($brewery->location->lng != "0") { $output->setLongitude($brewery->location->lng); }
        if (isset($brewery->brewery_type)) {
            $type = $this->em->getRepository('\App\Entity\Brewery\Type')->findOneBy(array('name' => $brewery->brewery_type));   
            if ($type) {
                $output->setType($type);
            }
        }
        return $output;
    }
    
    private function buildBreweryWithFullInformation($brewery) {
        $output = $this->em->getRepository('\App\Entity\Brewery\Brewery')->find($brewery->brewery_id);
        if (!$output) {
            $output = new Brewery();
            $output->setId($brewery->brewery_id);
        }
        $output->setName($brewery->brewery_name);
        $output->setSlug($brewery->brewery_slug);
        $output->setLabel($brewery->brewery_label);
        $output->setCountryName($brewery->country_name);
        $output->setIsIndependent($brewery->is_independent);
        $output->setActive(!$brewery->brewery_in_production); // Bool returned by the API is wrong
        $output->setIsClaimed($brewery->claimed_status->is_claimed);
        $output->setClaimedSlug($brewery->claimed_status->claimed_slug);
        $output->setFollowerCount($brewery->claimed_status->follower_count);
        //$output->setClaimUser($brewery->claimed_status->uid); [TODO]
        $output->setBeerCount($brewery->beer_count);
        $output->setDescription($brewery->brewery_description);
        $output->setTotalCount($brewery->stats->total_count);
        $output->setUniqueCount($brewery->stats->unique_count);
        $output->setMonthlyCount($brewery->stats->monthly_count);
        $output->setWeeklyCount($brewery->stats->weekly_count);
        $output->setRatingsCount($brewery->rating->count);
        $output->setRatingScore($brewery->rating->rating_score);
        $createdAt = new \DateTime();
        $output->setCreatedAt($createdAt->sub(new \DateInterval('PT'.round($brewery->stats->age_on_service * 24 * 60).'M')));
        if (isset($brewery->contact->facebook)) { $output->setFacebook($brewery->contact->facebook); }
        if (isset($brewery->contact->instagram)) { $output->setInstagram($brewery->contact->instagram); }
        if (isset($brewery->contact->twitter)) { $output->setTwitter($brewery->contact->twitter); }
        if (isset($brewery->contact->url)) { $output->setUrl($brewery->contact->url); }
        if ($brewery->location->brewery_address != "") { $output->setAddress($brewery->location->brewery_address); }
        if ($brewery->location->brewery_city != "") { $output->setCity($brewery->location->brewery_city); }
        if ($brewery->location->brewery_state != "") { $output->setState($brewery->location->brewery_state); }
        if ($brewery->location->brewery_lat != "0") { $output->setLatitude($brewery->location->brewery_lat); }
        if ($brewery->location->brewery_lng != "0") { $output->setLongitude($brewery->location->brewery_lng); }
        $type = $this->em->getRepository('\App\Entity\Brewery\Type')->find($brewery->brewery_type_id);
        if (!$type) {
            $type = new Type();
            $type->setId($brewery->brewery_type_id);
            $type->setName($brewery->brewery_type);
            $this->em->persist($type);
        }
        $output->setType($type);
        $output->setInternalDataGathered(true);
        return $output;
    }
    
    private function buildVenueWithLowInformation($venue) {
        $output = $this->em->getRepository('\App\Entity\Venue\Venue')->find($venue->venue_id);
        if (!$output) {
            $output = new Venue();
            $output->setId($venue->venue_id);
            $output->setInternalDataGathered(false);
        }
        $output->setName($venue->venue_name);
        $output->setSlug($venue->venue_slug);
        $output->setMainCategory($venue->primary_category);
        $output->setFoursquareId($venue->foursquare->foursquare_id);
        $output->setFoursquareUrl($venue->foursquare->foursquare_url);
        if (isset($venue->contact->facebook)) { $output->setFacebook($venue->contact->facebook); }
        if (isset($venue->contact->yelp)) { $output->setYelp($venue->contact->yelp); }
        if (isset($venue->contact->twitter)) { $output->setTwitter($venue->contact->twitter); }
        if (isset($venue->contact->venue_url)) { $output->setVenueUrl($venue->contact->venue_url); }
        $output->setIconLg($venue->venue_icon->lg);
        $output->setIconMd($venue->venue_icon->md);
        $output->setIconSm($venue->venue_icon->sm);
        $output->setIsVerified($venue->is_verified);
        $output->setAddress($venue->location->venue_address);
        $output->setCity($venue->location->venue_city);
        $output->setState($venue->location->venue_state);
        $output->setCountry($venue->location->venue_country);
        $output->setLatitude($venue->location->lat);
        $output->setLongitude($venue->location->lng);
        foreach ($venue->categories->items as $categoryData) {
            $category = $this->em->getRepository('\App\Entity\Venue\Category')->find($categoryData->category_id);
            if (!$category) {
                $category = new Category();
                $category->setId($categoryData->category_id);
                $category->setName($categoryData->category_name);
                $category->setIsPrimary($categoryData->is_primary);
                $this->em->persist($category);
            }
            $output->addCategory($category);
        }
        return $output;
    }
    
    private function buildVenueWithFullInformation($venue) {
        $output = $this->em->getRepository('\App\Entity\Venue\Venue')->find($venue->venue_id);
        if (!$output) {
            $output = new Venue();
            $output->setId($venue->venue_id);
        }
        $output->setInternalDataGathered(true);
        $output->setName($venue->venue_name);
        $output->setSlug($venue->venue_slug);
        $output->setMainCategory($venue->primary_category);
        $output->setFoursquareId($venue->foursquare->foursquare_id);
        $output->setFoursquareUrl($venue->foursquare->foursquare_url);
        if (isset($venue->contact->facebook)) { $output->setFacebook($venue->contact->facebook); }
        if (isset($venue->contact->yelp)) { $output->setYelp($venue->contact->yelp); }
        if (isset($venue->contact->twitter)) { $output->setTwitter($venue->contact->twitter); }
        if (isset($venue->contact->venue_url)) { $output->setVenueUrl($venue->contact->venue_url); }
        $output->setIconLg($venue->venue_icon->lg);
        $output->setIconMd($venue->venue_icon->md);
        $output->setIconSm($venue->venue_icon->sm);
        $output->setIsVerified($venue->is_verified);
        $output->setAddress($venue->location->venue_address);
        $output->setCity($venue->location->venue_city);
        $output->setState($venue->location->venue_state);
        $output->setCountry($venue->location->venue_country);
        $output->setLatitude($venue->location->lat);
        $output->setLongitude($venue->location->lng);
        $output->setPublicVenue($venue->public_venue);
        $output->setTotalCount($venue->stats->total_count);
        $output->setMonthlyCount($venue->stats->monthly_count);
        $output->setWeeklyCount($venue->stats->weekly_count);
        $output->setLastUpdated(\DateTime::createFromFormat(DATE_RFC2822, $venue->last_updated)->setTimeZone(new \DateTimeZone(date_default_timezone_get())));
        foreach ($venue->categories->items as $categoryData) {
            $category = $this->em->getRepository('\App\Entity\Venue\Category')->find($categoryData->category_id);
            if (!$category) {
                $category = new Category();
                $category->setId($categoryData->category_id);
                $category->setName($categoryData->category_name);
                $category->setIsPrimary($categoryData->is_primary);
                $this->em->persist($category);
            }
            $output->addCategory($category);
        }
        return $output;
    }
    
    private function buildCheckin($checkin) {
        $output = $this->em->getRepository('\App\Entity\Checkin\Checkin')->find($checkin->checkin_id);
        if (!$output) {
            $output = new Checkin();
            $output->setId($checkin->checkin_id);
        }
        $output->setCreatedAt(\DateTime::createFromFormat(DATE_RFC2822, $checkin->created_at)->setTimeZone(new \DateTimeZone(date_default_timezone_get())));
        $output->setComment($checkin->checkin_comment);
        if ($checkin->rating_score > 0) { $output->setRatingScore($checkin->rating_score); }
        $output->setTotalComments($checkin->comments->total_count);
        $output->setTotalToasts($checkin->toasts->total_count);
        $output->setTotalBadges($checkin->badges->count);
        return $output;
    }
    
    private function buildToast($toast) {
        $output = $this->em->getRepository('\App\Entity\Checkin\Toast')->find($toast->like_id);
        if (!$output) {
            $output = new Toast();
            $output->setId($toast->like_id);
        }
        $output->setCreatedAt(\DateTime::createFromFormat(DATE_RFC2822, $toast->created_at)->setTimeZone(new \DateTimeZone(date_default_timezone_get())));
        $output->setUser($this->buildUserWithLowInformation($toast->user));
        return $output;
    }
    
    private function buildComment($comment) {
        $output = $this->em->getRepository('\App\Entity\Checkin\Comment')->find($comment->comment_id);
        if (!$output) {
            $output = new Comment();
            $output->setId($comment->comment_id);
        }
        $output->setCreatedAt(\DateTime::createFromFormat(DATE_RFC2822, $comment->created_at)->setTimeZone(new \DateTimeZone(date_default_timezone_get())));
        $output->setCommentSource($comment->comment_source);
        $output->setUser($this->buildUserWithLowInformation($comment->user));
        $output->setComment($comment->comment);
        return $output;
    }
    
    private function buildCheckinMedia($media) {
        $output = $this->em->getRepository('\App\Entity\Checkin\Comment')->find($media->photo_id);
        if (!$output) {
            $output = new Media();
            $output->setId($media->photo_id);
        }
        $output->setPhotoImgSm($media->photo->photo_img_sm);
        $output->setPhotoImgMd($media->photo->photo_img_md);
        $output->setPhotoImgLg($media->photo->photo_img_lg);
        $output->setPhotoImgOg($media->photo->photo_img_og);
        return $output;
    }
    
    private function buildSource($source) {
        $output = $this->em->getRepository('\App\Entity\Checkin\Source')->findOneBy(array('app_name' => $source->app_name, 'app_website' => $source->app_website));
        if (!$output) {
            $output = new Source();
            $output->setAppName($source->app_name);
            $output->setAppWebsite($source->app_website);
            $this->em->persist($output);
            $this->em->flush();
        }
        return $output;
    }
    
    private function buildBadgeWithLowInformation($badge) {
        $output = $this->em->getRepository('\App\Entity\Badge\Badge')->find($badge->badge_id);
        if (!$output) {
            $output = new Badge();
            $output->setId($badge->badge_id);
        }
        $output->setBadgeName($badge->badge_name);
        $output->setBadgeDescription($badge->badge_description);
        $output->setBadgeImageLg($badge->badge_image->lg);
        $output->setBadgeImageMd($badge->badge_image->md);
        $output->setBadgeImageSm($badge->badge_image->sm);
        return $output;
    }
    
    private function createBadgeRelation($badge, $user, $checkin, $userBadgeID, $createdAt) {
        $relation = $this->em->getRepository('\App\Entity\Badge\BadgeRelation')->findOneBy(array('badge' => $badge, 'user' => $user));
        if (!$relation) {
            $relation = new BadgeRelation();
            $relation->setBadge($badge);
            $relation->setUser($user);
            $relation->setCheckin($checkin);
            $relation->setUserBadgeId($userBadgeID);
            $relation->setCreatedAt(\DateTime::createFromFormat(DATE_RFC2822, $createdAt)->setTimeZone(new \DateTimeZone(date_default_timezone_get())));
            $this->em->persist($relation);
        }
        return true;
    }
    
    public function disableSqlLogger() {
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
    }
}