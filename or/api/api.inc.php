<?php

/**
 * This File Contains the main API Framework
 *
 * @package Open-Realty
 * @subpackage API
 * @author Ryan C. Bonham
 * @copyright 2010
 * @link http://www.open-realty.com Open-Realty
 */


/**
 * This is the base API, all other API functions simply extend this class.
 *
 * @package Open-Realty
 * @subpackage API
 **/

define('BETA_API', false);

class api
{
    public function __construct()
    {
        global $lang, $lapi, $config;
        $lapi = $this;
        $this->load_setting();
    }

    /**
     * This builds the corerct xml response. It takes care of serialzing any arrays and encrypting the values
     * before sending them back to the client. All api commands should use this function when returning results.
     *
     * @param array $value
     * @return array
     * @access private
     */
    public function build_xml_response($value, $type)
    {
        //echo '<pre>Raw XMl Response: '.print_r($value,TRUE).'</pre>';
        if (is_array($value)) {
            $success = $value['error'];
            unset($value['error']);
            $value = serialize($value);
        }
        if ($type == 'RSXM2') {
            $value = base64_encode($value);
            $response = '<?xml version="1.0" ?>
					<open-realty_api>
					<open-realty_api_type>RSXM2</open-realty_api_type>
					<open-realty_version>3.0</open-realty_version>
					<result_code>' . intval($success) . '</result_code>
					<result>' . $value . '</result>
					</open-realty_api>';
        } else {
        }
        return $response;
    }

    /**
     * @param unknown_type $command
     * @param unknown_type $data
     * @param unknown_type $api_user
     * @param unknown_type $api_password
     * @return string
     * @access private
     */
    public function build_xml_query($command, $data, $api_user, $api_password)
    {
        $xmldata = '';
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $xmldata .= '<' . $key . '>' . serialize($value) . '</' . $key . '>';
            }
        }
        //print_r($xmldata);
        $data = serialize($xmldata);
        $data = htmlspecialchars($data, ENT_XML1, 'UTF-8');
        //User
        $api_user = htmlspecialchars($api_user, ENT_XML1, 'UTF-8');
        //Pass
        $api_password = htmlspecialchars($api_password, ENT_XML1, 'UTF-8');
        //$data = base64_encode($data);
        //$command = serialize($command);
        $command = htmlspecialchars($command, ENT_XML1, 'UTF-8');
        $query = '<?xml version="1.0" ?>
				<open-realty_api>
				<open-realty_api_type>RSXM2</open-realty_api_type>
				<request>
					<user>' . $api_user . '</user>
					<password>' . $api_password . '</password>
					<command>' . $command . '</command>
					<options>' . $data . '</options>

				</request>
				</open-realty_api>';
        return $query;
    }

    /**
     * This function is used to retrieve the soap responce from the API Server and unencrypt and unserialze it.
     *
     * @param string $value This is the encrpted string returned by the API server
     * @return array
     * @access private
     */
    public function retrieve_xml_query($value)
    {
        //echo '<pre>Raw Query Value: '.$value."</pre>";
        $value = simplexml_load_string($value);
        //echo '<pre>XML Query Value: '.print_r($value,TRUE).'</pre>';die;
        if ($value->{'open-realty_api_type'} == 'RSXM2') {
            $api_result_options = $value->{'request'}->options;
            $api_result_options = unserialize($api_result_options);
            //print_r($api_result_options);
            $api_result_options = simplexml_load_string('<options>' . $api_result_options . '</options>');
            //print_r($api_result_options);
            $api_result_options = (array)$api_result_options;
            foreach ($api_result_options as $akey => $avalue) {
                $api_result_options[$akey] = unserialize($avalue);
            }
            // print_r($api_result_options);
            $api_result_command = $value->{'request'}->command;
            // $api_result_command = unserialize($api_result_command);
            //Get User
            $api_result_user = $value->{'request'}->user;
            
            //Get Password
            $api_result_password = $value->{'request'}->password;
            //echo $api_result_command;
            $api_result_type = 'RSXM2';
        } else {
            $api_result = base64_decode($value->{'request'});
            $api_result = unserialize($api_result);
        }

        $api_result = ['command' => $api_result_command, 'options' => $api_result_options, 'type' => $api_result_type, 'user' => $api_result_user, 'password' => $api_result_password];
        //echo '<pre>API Query Result: '.print_r($api_result,TRUE).'</pre>';
        return $api_result;
    }

    /**
     * @param unknown_type $value
     * @return multitype:unknown string
     * @access private
     */
    public function retrieve_xml_respose($value)
    {
        //echo '<pre>Raw Response Value: '.$value."</pre>";
        $value = simplexml_load_string((string)$value);
        //  echo '<pre>XML Response Value: '.print_r($value,TRUE).'</pre>';
        if ($value->{'open-realty_api_type'} == 'RSXM2') {
            $api_or_version = (string)$value->{'open-realty_version'};
            $api_result_code = (string)$value->{'result_code'};
            $api_result = base64_decode($value->{'result'});
            $api_result = unserialize($api_result);
        } else {
            $api_result = base64_decode($value->{'request'});
            $api_result = unserialize($api_result);
        }

        $api_result = ['api_or_version' => $api_or_version, 'api_result_code' => $api_result_code, 'api_result' => $api_result];
        //echo '<pre>API Response Result: '.print_r($api_result,TRUE).'</pre>';
        return $api_result;
    }

    /**
     * @paramccl unknown_type $server
     * @param unknown_type $post
     * @return string|mixed
     */
    private function exec_curl($server, $post)
    {
        $link = curl_init();
        curl_setopt($link, CURLOPT_URL, $server);
        curl_setopt($link, CURLOPT_POST, 1);
        curl_setopt($link, CURLOPT_POSTFIELDS, $post);
        curl_setopt($link, CURLOPT_VERBOSE, 0);
        curl_setopt($link, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($link, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($link, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($link, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($link, CURLOPT_MAXREDIRS, 6);
        curl_setopt($link, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($link, CURLOPT_TIMEOUT, 60);
        $results = curl_exec($link);
        if (curl_errno($link) != 0) {
            curl_close($link);
            return false;
        }
        curl_close($link);
        return $results;
    }

    /**
     *  * This function is used to call remote api commands.
     *
     * @param string $api_server - URL of the Open-Realty install to run the api command on. Ex http://yourdomain.com/admin/index.php
     * @param string $api_command - Name of the api command to run. Example. pclass__create
     * @param array $api_data -  Array of data to pass to the API Command. See Each API Command for details on what to pass.
     * @param string $api_user - Username to run the api command as.
     * @param string $api_password - Password for teh user running api command as.
     * @return array
     */
    public function send_api_command($api_server, $api_command, $api_data, $api_user = '', $api_password = '')
    {
        $query = $this->build_xml_query($api_command, $api_data, $api_user, $api_password);
        //echo '<pre>Sending Query:'.$query."</pre>";
        $query = urlencode($query);
        $result = $this->exec_curl($api_server, "orapi_query=$query");
        //echo '<pre>Raw CURL Result: '.print_r($result,TRUE)."</pre>";
        $result = $this->retrieve_xml_respose($result);
        //echo '<pre>Query Result: '.print_r($result,TRUE)."</pre>";
        return $result;
    }

    /**
     * api::load_remote_api()
     *
     * @param string $api_command This should contain the name of the api_command to execute
     * @param array $api_data This should contain an array or the variables to pass to the api_command.
     * @return string
     * @access private
     *
     **/
    public function load_remote_api($query)
    {
        global $lang, $lapi, $config;

        $query = $this->retrieve_xml_query($query);
        $api_command = $query['command'];
        $api_data = $query['options'];
        $api_type = $query['type'];
        $api_user = $query['user'];
        $api_password = $query['password'];

        //Log User In
        $_POST['user_name'] = $api_user;
        $_POST['user_pass'] = $api_password;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->loginCheck('Agent', true, true);
        if ($login_status !== true) {
            $result = ['0', ['error_code' => 10001, 'error_msg' => 'Login Failed']];
        } else {
            // Check that api_data is an array
            if (!is_array($api_data)) {
                $result = ['0', ['error_code' => 4001, 'error_msg' => $lang['error_value_not_array']]];
            //Log error Next
            } else {
                //extract($api_data, EXTR_SKIP || EXTR_REFS, '');
                $api_command = explode('__', $api_command);
                if (BETA_API) {
                    include_once dirname(__FILE__) . '/beta-commands/' . $api_command[0] . '.inc.php';
                } else {
                    include_once dirname(__FILE__) . '/commands/' . $api_command[0] . '.inc.php';
                }

                $apiclass = $api_command[0] . '_api';
                $api_sub = new $apiclass();
                $result = $api_sub->{$api_command[1]}($api_data);
            }
        }
        $result = $this->build_xml_response($result, $api_type);
        return $result;
    }

    /**
     * This function is used to call local api commands.
     *
     * @param string $api_command - Name of the api command to run. Example. pclass__create
     * @param array $api_data - REQUIRED - Array of data to pass to the API Command. See Each API Command for details on what to pass.
     * @param string $api_user - OPTIONAL username to run the api command as. This is only required if called from outside of Open-Realty.
     * @param string $api_password - OPTIONAL password for the user running api command as. This is only required if called from outside of Open-Realty.
     *
     * @return array
     */
    public function load_local_api($api_command, $api_data, $api_user = '', $api_password = '')
    {
        global $lang, $lapi, $config;

        if ($api_user != '') {
            $_POST['user_name'] = $api_user;
            $_POST['user_pass'] = $api_password;
            include_once $config['basepath'] . '/include/login.inc.php';
            $login = new login();
            $login_status = $login->loginCheck('Agent', true);
            if ($login_status !== true) {
                return ['error' => true, 'error_msg' => 'Login Failed'];
            }
        }

        //echo '<pre>Lang: '.print_r($lang,TRUE).'</pre>';
        // Check that api_data is an array
        if (!is_array($api_data)) {
            return [false, $lang['error_value_not_array'], $api_data];
        }
        //Extract the api data passed via SOAP into individual variables. These new variables are actually just references to save on memory
        //extract($api_data, EXTR_SKIP || EXTR_REFS, '');
        $api_command = explode('__', $api_command);
        if (preg_match('/[^A-Za-z0-9]/', $api_command[0])) {
            //File name contains non alphanum chars die to prevent file system attacks.
            die('Invalid API Call - File Attack');
        }

        if (BETA_API) {
            if (!file_exists(dirname(__FILE__) . '/beta-commands/' . $api_command[0] . '.inc.php')) {
                die('Invalid API Call');
            }
            include_once dirname(__FILE__) . '/beta-commands/' . $api_command[0] . '.inc.php';
        } else {
            if (!file_exists(dirname(__FILE__) . '/commands/' . $api_command[0] . '.inc.php')) {
                die('Invalid API Call');
            }
            include_once dirname(__FILE__) . '/commands/' . $api_command[0] . '.inc.php';
        }
        //require_once(dirname(__FILE__).'/commands/'.$api_command[0].'.inc.php');
        $this->load_command_lang($api_command[0]);
        $apiclass = $api_command[0] . '_api';
        $api_sub = new $apiclass();
        $api_data = $this->scrub_api_data($api_data);
        $result = $api_sub->{$api_command[1]}($api_data);
        return $result;
    }

    /**
     * This makes sure the user has not tried to override any global variables in the API data.
     *
     * @param array $data This is the api data to scrub
     * @return array
     * @access private
     */
    public function scrub_api_data($data)
    {
        unset($data['conn']);
        unset($data['lang']);
        unset($data['config']);
        unset($data['db_type']);
        unset($data['api']);
        unset($data['lapi']);
        unset($data['misc']);
        return $data;
    }

    /**
     * This Creates the $db connection for the API and also creates the $settings and $lang variables.
     *
     * @access private
     */
    public function load_setting()
    {
        global $config, $lang;
        //Load Config Look first for OR's common.php file then fall back to a common.php in this folder for remote usage
        if (file_exists(dirname(__FILE__) . '/../include/common.php')) {
            include_once dirname(__FILE__) . '/../include/common.php';
        }

        /*
        // Load the default API Language File
        if(file_exists(dirname(__FILE__).'/../lang/'.$config['lang'].'/api.inc.php')){
            include_once dirname(__FILE__).'/../lang/'.$config['lang'].'/api.inc.php';
        }
        */
    }

    /**
     * This function loads any languages files needed by the API Command. This is mainly used for error handling.
     *
     * @param string $command Name of the command set being run.
     * @access private
     */
    public function load_command_lang($command)
    {
        // These langs should be seperated like we had before.....
        global $config;
        if (file_exists(dirname(__FILE__).'/../include/language/'.$config['lang'].'/lang.inc.php')) {
            include_once dirname(__FILE__).'/../include/language/'.$config['lang'].'/lang.inc.php';
        }
        // global $config;
        // if (file_exists(dirname(__FILE__).'/../lang/'.$config['lang'].'/'.$command.'.inc.php')) {
        //     include_once dirname(__FILE__).'/../lang/'.$config['lang'].'/'.$command.'.inc.php';
        // }
    }
}
