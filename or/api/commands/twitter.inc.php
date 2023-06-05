<?php
/**
 * This File Contains the Twitter API Commands
 * @package Open-Realty
 * @subpackage API
 * @author Ryan C. Bonham
 * @copyright 2010

 * @link http://www.open-realty.com Open-Realty
 */

/**
 * This is the Twitter API, it contains all api calls for posting to twitter.
 *
 * @package Open-Realty
 * @subpackage API
 **/

use Abraham\TwitterOAuth\TwitterOAuth;

class twitter_api
{
    /**
     * This API Command posts a message to twitter.
     * @param array $data $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['message'] - This is a message 280 chars or less that you want to post to twitter.</li>
     *  </ul>
     * @return array
     *
     */

    public function post($data)
    {
        global $conn, $lapi, $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->verify_priv('Agent');
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure'];
        }

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed
        if (!isset($message)||empty($message)) {
            return ['error' => true, 'error_msg' => 'message: correct_parameter_not_passed'];
        }
        if (strlen($message)>280) {
            return ['error' => true, 'error_msg' => 'message: longer then 280 characters'];
        }

        $access_token = unserialize($config['twitter_auth']);

        $connection = new TwitterOAuth($config['twitter_consumer_key'], $config['twitter_consumer_secret'], $access_token['oauth_token'], $access_token['oauth_token_secret']);
        $content = $connection->get('account/verify_credentials');

        //if media set
        if (isset($media) && $media !='') {
            if (isset($media_remote) && $media_remote === true) {
                $media_file = $connection->upload('media/upload', ['media' => $media]);
            } else {
                $media_file = $connection->upload('media/upload', ['media' => $config['listings_upload_path'] . '/' . $media]);
            }
            $parameters = [
                'status' => $message,
                'include_entities' => 1,
                'media_ids' => $media_file->media_id_string, ];
        } else {
            $parameters = [
                'status' => $message,
                'include_entities' => 1,
            ];
        }

        //post our tweet and get response
        $connection->post('statuses/update', $parameters);
        $http_code = intval($connection->getLastHttpCode());
        $body = ($connection->getLastBody());

        switch ($http_code) {
            case 200:
                //success
                return ['error' => false, 'message' => $message];
                break;
            case 304:
                return ['error' => true, 'message' => '304 Not Modified'];
                break;
            case 403:
                //forbidden?? get the errors
                $errors= $body->errors;
                //get rid of object crap
                $error_array = json_decode(json_encode($errors[0]), true);
                return ['error' => true, 'message' => '403 Twitter post Failed: ' .$error_array['message']];
                break;
            default:
                return ['error' => true, 'message' => 'Twitter post Failed'];
        }
    }
}
