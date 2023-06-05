<?php

use Abraham\TwitterOAuth\TwitterOAuth;

class social
{
    //Send data to twitter message can be no longer then 140 chars
    public function twitter_callback()
    {
        global $config, $conn, $misc;

        /* If the oauth_token is old redirect to the connect page. */
        if (isset($_REQUEST['oauth_token']) && isset($_SESSION['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
            //  Old Session Go Back to Site Config.
        }

        /* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
        $connection = new TwitterOAuth($config['twitter_consumer_key'], $config['twitter_consumer_secret'], $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

        /* Request access tokens from twitter */

        //$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

        $access_token = $connection->oauth('oauth/access_token', ['oauth_verifier' => $_REQUEST['oauth_verifier']]);
        /* Save the access tokens. Normally these would be saved in a database for future use. */
        $sql = 'UPDATE ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_twitter_auth = ' . $misc->make_db_safe(serialize($access_token)) . '';
        $recordSet = $conn->Execute($sql);
        /* Remove no longer needed request tokens */
        unset($_SESSION['oauth_token']);
        unset($_SESSION['oauth_token_secret']);
        $display = 'Twitter Connected';
        return $display;
    }
    public function twitter_disconnect()
    {
        global $config, $conn;
        $sql = 'UPDATE ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_twitter_auth = \'\'';
        $recordSet = $conn->Execute($sql);
        $display = 'Twitter Disconnected';
        return $display;
    }
}
