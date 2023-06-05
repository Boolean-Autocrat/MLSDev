<?php


//DO NOT MODIFY THIS EXAMPLE HOOK FILE. Copy any required functions to your own custom hook file and class per the documentation.

// Name of our hook. Must be named the same as the PHP file it is contained within, e.g.: sample_hook = sample_hook.php.
class sample_hook
{
    // after_new_listing($listingID) - called after a listing is added to the db
    public function after_new_listing($listingID)
    {
        /*global $conn, $lang, $config, $jscript, $misc;
        $sent = $misc->send_email($config['admin_name'], $config['admin_email'], $config['admin_email'], "New Listing Hook Triggered - ".$listingID, "New Listing Hook Triggered - ".$listingID);
        */
    }

    // after_listing_change($listingID) - called after a listing is modified
    public function after_listing_change($listingID)
    {
    }

    // after_listing_delete($listingID) - called after a listing is deleted
    public function after_listing_delete($listingID)
    {
    }

    // before_listing_delete($listingID) - called before a listing is deleted
    public function before_listing_delete($listingID)
    {
    }

    // function after_actived_listing($listingID) - called after a listing is marked as active.
    public function after_actived_listing($listingID)
    {
    }

    //after_user_signup($userID) - called after a successful registration
    public function after_user_signup($userID)
    {
    }

    //befure_user_change($userID,$postvars) - called after a user change is verified and before it is actually executed.
    //This allows you to get teh current users email address, etc before it is changed.
    public function befure_user_change($userID, $postvars)
    {
    }

    // after_user_change($userID) - called after a user changes their profile
    public function after_user_change($userID)
    {
    }

    // after_user_delete($userID) - called after a user is deleted
    public function after_user_delete($userID)
    {
    }

    //after_user_login($userID) - called after a user logs in
    public function after_user_login($userID)
    {
    }

    //  after_user_logout($userID) - called after a user logs out
    public function after_user_logout($userID)
    {
    }

    /**
     * Used to Releplace the Meta Description, Keywords, and/or title for a page.
     * This function must return an array with one or more of the following keys. keywords, description, title
     *
     *  eg.
     *  return array('title'=>'This is My Custom Title','keywords' =>'custom keywords rock');
     */
    public function replace_meta_template_tags($action)
    {
    }

    /**
     * Hooked used to override internal mobile browser detection
     * This function must return an array the key is_mobile if you want to override the built in detection.
     * To fall back to the built in detection return NULL.
     *
     *  eg.
     *  //If this is a Motorol Xoom For it to Desktop
     *  if(stripos($user_agent,'xoom build') !== FALSE){
     *  return array('is_mobile'=>FALSE);
     *  }
     *  //If not the xoom then fall back to internal detection.
     *  return NULL;
     */
    public function detect_mobile_browser($user_agent)
    {
    }

    // after_new_lead($lead_id) - called after a lead is added to the db
    public function after_new_lead($lead_id)
    {
    }

    /** after_new_media($filename, $media_type)
    * runs after a new media item is uploaded and added to OR
    * $mediainfo (array) contains 3 items
    * ['file_name'] (string) Name of media file being deleted
    * ['media_type'] (string) type of media file deleted. Possible types:
    *   listingsimages
    *   userimages
    *   listingsfiles
    *   usersfiles
    *   listingsvtours
    * ['media_parent_id'] (int) numeric listing or user ID (whicherver is applicable)
    * be aware: linked (remote) listing photos will contain http:// or https:// URLs
    * Do not echo or print any results from your script, this can break the json return codes Return NULL when completed
    */
    public function after_new_media($mediainfo)
    {
        //return NULL;
    }

    /** before_media_delete($file_name, $media_type, $media_parent_id)
    * runs BEFORE a media item is deleted
    * $mediainfo (array) contains 3 items
    * ['file_name'] (string) Name of media file being deleted
    * ['media_type'] (string) type of media file deleted. Possible types:
    *   listingsimages
    *   userimages
    *   listingsfiles
    *   usersfiles
    *   listingsvtours
    * ['media_parent_id'] (int) numeric listing or user ID (whicherver is applicable)
    * be aware: linked (remote) listing photos will contain http:// or https:// URLs
    * Do not echo or print any results from your script, this can break the json return codes Return NULL when completed
    */
    public function before_media_delete($mediainfo)
    {
        //return NULL;
    }

    /** after_media_delete($file_name, $media_type, $media_parent_id)
    * runs AFTER  a media item is deleted
    * $mediainfo (array) contains 3 items
    * ['file_name'] (string) Name of media file being deleted
    * ['media_type'] (string) type of media file deleted. Possible types:
    *   listingsimages
    *   userimages
    *   listingsfiles
    *   usersfiles
    *   listingsvtours
    * ['media_parent_id'] (int) numeric listing or user ID (whicherver is applicable)
    * be aware: linked (remote) listing photos will contain http:// or https:// URLs
    * Do not echo or print any results from your script, this can break the json return codes Return NULL when completed
    */
    public function after_media_delete($mediainfo)
    {
        //return NULL;
    }
}
