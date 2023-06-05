<?php


class login
{
    public $debug;

    private function get_google_client($redirectUri='')
    {
        global $config;

        if (strlen($config['google_client_id'])>0 && strlen($config['google_client_secret']) > 0) {
            // create Client Request to access Google API
            $client = new Google_Client();
            $client->setClientId($config['google_client_id']);
            $client->setClientSecret($config['google_client_secret']);
            $client->setRedirectUri($redirectUri);
            $client->addScope("email");
            $client->addScope("profile");
            return $client;
        }
        return false;
    }

    public function clear_rememberme_cookie()
    {
        global $config, $conn, $misc;
        $parse = parse_url($config['baseurl']);
        $domain = $parse['host'];
        if ($parse['scheme'] == 'https') {
            $https = true;
        } else {
            $https=false;
        }


        if (isset($_COOKIE['user_id']) && isset($_COOKIE['cookie_selector'])) {
            $sql_user_id = $misc->make_db_safe($_COOKIE['user_id']);
            $sql_selector = $misc->make_db_safe($_COOKIE['cookie_selector']);
            $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . 'auth_tokens
            WHERE userdb_id = '.$sql_user_id.' AND  selector = '.$sql_selector;
            
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
        }
       
        
        
        $misc->setcookie('user_id', '', time() - 3600, '/', $domain, $https, true);
        $misc->setcookie('cookie_validator', '', time() - 3600, '/', $domain, $https, true);
        $misc->setcookie('cookie_selector', '', time() - 3600, '/', $domain, $https, true);
    }

    public function set_session_vars($username='', $userid=0)
    {
        global $config, $misc, $conn;

        if ($username !== '') {
            $sql_username = $misc->make_db_safe($username);
            $sql = 'SELECT * FROM ' . $config['table_prefix'] . 'userdb 
                WHERE  userdb_user_name=' . $sql_username;
        } elseif ($userid>0) {
            $sql_userid = $misc->make_db_safe($userid);
            $sql = 'SELECT * FROM ' . $config['table_prefix'] . 'userdb 
                WHERE  userdb_id=' . $sql_userid;
        } else {
            return false;
        }

       
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }

        $_SESSION['userID'] = $recordSet->fields('userdb_id');
        $_SESSION['username'] = $recordSet->fields('userdb_user_name');
        $_SESSION['admin_privs'] = $recordSet->fields('userdb_is_admin');
        $_SESSION['active'] = $recordSet->fields('userdb_active');
        $_SESSION['isAgent'] = $recordSet->fields('userdb_is_agent');
        $_SESSION['featureListings'] = $recordSet->fields('userdb_can_feature_listings');
        $_SESSION['viewLogs'] = $recordSet->fields('userdb_can_view_logs');
        $_SESSION['moderator'] = $recordSet->fields('userdb_can_moderate');
        $_SESSION['editpages'] = $recordSet->fields('userdb_can_edit_pages');
        $_SESSION['havevtours'] = $recordSet->fields('userdb_can_have_vtours');
        $_SESSION['haveuserfiles'] = $recordSet->fields('userdb_can_have_user_files');
        $_SESSION['havefiles'] = $recordSet->fields('userdb_can_have_files');
        $_SESSION['is_member'] = 'yes';

        // New Permissions with OR 2.1
        $_SESSION['edit_site_config'] = $recordSet->fields('userdb_can_edit_site_config');
        $_SESSION['edit_member_template'] = $recordSet->fields('userdb_can_edit_member_template');
        $_SESSION['edit_agent_template'] = $recordSet->fields('userdb_can_edit_agent_template');
        $_SESSION['edit_listing_template'] = $recordSet->fields('userdb_can_edit_listing_template');
        $_SESSION['export_listings'] = $recordSet->fields('userdb_can_export_listings');
        $_SESSION['edit_all_listings'] = $recordSet->fields('userdb_can_edit_all_listings');
        $_SESSION['edit_all_users'] = $recordSet->fields('userdb_can_edit_all_users');
        $_SESSION['edit_property_classes'] = $recordSet->fields('userdb_can_edit_property_classes');
        $_SESSION['edit_expiration'] =  $recordSet->fields('userdb_can_edit_expiration');
        $_SESSION['blog_user_type'] = $recordSet->fields('userdb_blog_user_type');
        $_SESSION['can_manage_addons'] = $recordSet->fields('userdb_can_manage_addons');
        $_SESSION['edit_lead_template'] = $recordSet->fields('userdb_can_edit_lead_template');
        $_SESSION['edit_all_leads'] = $recordSet->fields('userdb_can_edit_all_leads');

        return true;
    }
    public function create_rememberme_cookie()
    {
        global $config, $misc, $conn;

        $parse = parse_url($config['baseurl']);
        $domain = $parse['host'];
        if ($parse['scheme'] == 'https') {
            $https = true;
        } else {
            $https=false;
        }
        $validator = bin2hex(random_bytes(32));
        $selector = bin2hex(random_bytes(32));
        $arr_cookie_options = array(
            'expires' => time() + 60*60*24*30,
            'path' => '/',
            'domain' => $domain, // leading dot for compatibility or use subdomain
            'secure' => $https,     // or false
            'httponly' => true,    // or false
            'samesite' => 'Strict'
            );
        setcookie('user_id', $_SESSION['userID'], $arr_cookie_options);
        setcookie('cookie_validator', $validator, $arr_cookie_options);
        setcookie('cookie_selector', $selector, $arr_cookie_options);


        $sql_userID = $misc->make_db_safe($_SESSION['userID']);
        $sql_selector=$misc->make_db_safe($selector);
        $sql_hash=$misc->make_db_safe(password_hash($validator, PASSWORD_DEFAULT));
        $sql = 'INSERT INTO ' . $config['table_prefix_no_lang'] . 'auth_tokens
            SET 
                expires = DATE_ADD(NOW(),INTERVAL 30 DAY),
                userdb_id = '.$sql_userID.',
                selector = '.$sql_selector.',
                validator = '.$sql_hash;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
    }


    public function check_login()
    {
        global $conn, $misc, $config;
        /* User already has a session */
        if (isset($_SESSION['username'])) {
            return true;
        }

        /* Check if user has been remembered */
        elseif (isset($_COOKIE['user_id']) && isset($_COOKIE['cookie_validator']) && isset($_COOKIE['cookie_selector'])) {
            $sql_selector= $misc->make_db_safe($_COOKIE['cookie_selector']);
            $sql_user_id= $misc->make_db_safe($_COOKIE['user_id']);
            $sql = 'SELECT validator  FROM ' . $config['table_prefix_no_lang'] . 'auth_tokens WHERE selector = '.$sql_selector.' AND userdb_id = '.$sql_user_id.' AND expires > NOW()';
            $recordSet = $conn->Execute($sql);
            //print_r($recordSet);
            if (!$recordSet || ($recordSet->RecordCount() < 1)) {
                //Clear Cookie as it was invalid
                $this->clear_rememberme_cookie();
                return false;
            } elseif (password_verify($_COOKIE['cookie_validator'], $recordSet->fields('validator'))) {
                //Hash is valid
                if (!$this->set_session_vars('', $_COOKIE['user_id'])) {
                    return false;
                }
                return true;
            } else {
                //Hash was invalid
                $this->clear_rememberme_cookie();
                return false;
            }
        }
       
        /* User not logged in */
        return false;
    }

    public function verify_member_email($email)
    {
        global $config, $conn, $misc;

        header('Content-type: application/json');

        //Make sure this from our site.
        if (!$misc->referer_check()) {
            return json_encode(['error' => true, 'error_code' => 1, 'error_msg' => 'Form Security Violation1']);
        } else {
            //Make Sure Email is Valid
            $valid = $misc->validate_email($email);
            if (!$valid) {
                return json_encode(['error' => true, 'error_code' => 4, 'error_msg' => 'The Email address you entered is invalid.']);
            }
            $sql = 'SELECT * FROM ' . $config['table_prefix'] . 'userdb 
							WHERE  userdb_emailaddress = ' . $misc->make_db_safe($email);
            $recordSet = $conn->Execute($sql);
            //print_r($recordSet);
            if (!$recordSet || ($recordSet->RecordCount() < 1)) {
                return json_encode(['error' => true, 'error_code' => 2, 'error_msg' => 'Not A Member']);
            }
            if ($recordSet->fields('userdb_active') == 'yes') {
                return json_encode(['error' => false]);
            } else {
                return json_encode(['error' => true, 'error_code' => 3, 'error_msg' => 'Member Account is Disabled']);
            }
        }
    }

    public function ajax_check_member_login($email, $pass)
    {
        global $config, $conn, $misc;

        header('Content-type: application/json');

        //Make sure this from our site.
        if (!$misc->referer_check()) {
            return json_encode(['error' => true, 'error_code' => 1, 'error_msg' => 'Form Security Violation2']);
        } else {
            //Make Sure Email is Valid
            $valid = $misc->validate_email($email);
            if (!$valid) {
                return json_encode(['error' => true, 'error_code' => 4, 'error_msg' => 'The Email address you entered is invalid.']);
            }
            $sql = 'SELECT * FROM ' . $config['table_prefix'] . 'userdb WHERE  userdb_emailaddress = ' . $misc->make_db_safe($email) . '';
            $recordSet = $conn->Execute($sql);
            //print_r($recordSet);
            if (!$recordSet || ($recordSet->RecordCount() < 1)) {
                return json_encode(['error' => true, 'error_code' => 2, 'error_msg' => 'Not A Member']);
            }
            if ($recordSet->fields('userdb_active') == 'yes') {
                $_POST['user_name'] = $recordSet->fields('userdb_user_name');
                $_POST['user_pass'] = trim($pass);
                [$login_status, $error_msg] = $this->loginCheck('Member', 'v2');
                if (!$login_status) {
                    return json_encode(['error' => true, 'error_code' => 5, 'error_msg' => 'Password Incorrect']);
                } else {
                    //Return Users First and Last Name
                    include_once $config['basepath'] . '/include/user.inc.php';
                    $user = new user();
                    $fname = $user->get_user_single_item('userdb_user_first_name', $_SESSION['userID']);
                    $lname = $user->get_user_single_item('userdb_user_last_name', $_SESSION['userID']);
                    return json_encode(['error' => false, 'fname' => $fname, 'lname' => $lname]);
                }
            } else {
            }
        }
    }

    public function verify_google_user($gclient, $priv_level_needed)
    {
        global $api, $misc, $config;
        if (isset($_SESSION['username']) && $_SESSION['username']) {
            return true;
        } else {
            //Create Object of Google Service OAuth 2 class
            $google_service = new Google_Service_Oauth2($gclient);
            //Get user profile data from google
            $google_account_info = $google_service->userinfo->get();
            $email =  $google_account_info->email;
            $familyName =  $google_account_info->familyName;
            $givenName = $google_account_info->givenName;
        
            //Check if emai
            list($confirm_err, $confirm_result) = $this->confirm_user_by_email($email);
            
            if ($confirm_err !== true) {
                $this->set_session_vars($confirm_result);
                return true;
            } else {
                //User was not found. Create it if signup is supported
                if ($confirm_result === 1) {
                    if ($priv_level_needed=='Member') {
                        $type='member';
                        $is_agent='no';
                    } else {
                        $type='agent';
                        $is_agent='yes';
                    }
                    if ($config['allow_' . $type . '_signup'] == 1) {
                        $set_active = 'no';
                        if ($config['moderate_' . $type . 's'] != 1) {
                            if ($type == 'agent') {
                                if ($config['agent_default_active'] == 0) {
                                    $set_active = 'no';
                                } else {
                                    $set_active = 'yes';
                                }
                            } else {
                                $set_active = 'yes';
                            }
                        }
                        include_once $config['basepath'] . '/include/user_manager.inc.php';
                        $user_manager = new user_managment();
                        $user_id = $user_manager->create_base_user($type, $email, $email, $misc->generatePassword(), $set_active, $givenName, $familyName);
                        
                        list($confirm_err, $confirm_result) = $this->confirm_user_by_email($email);
        
                        if ($confirm_err !== true) {
                            $this->set_session_vars($confirm_result);
                            return true;
                        }
                    }
                }
            }
            
            /* Variables are incorrect, user not logged in */
            unset($_SESSION['access_token']);
            unset($_SESSION['username']);
            return false;
        }
    }
    /**
     * loginCheck - Verify the username and password passed by session or post and then verify privilage level.
     * @param string $priv_level_needed - Permission level to check for.
     * @param string $internal - True, False, or v2.
     *
     * @return array|boolean|string - Returns an array with the login status and error message.
     */
    public function loginCheck($priv_level_needed, $internal = false, $skip_csrf = false)
    {
        global $conn, $config, $lang, $misc;

        $display = '';
        $new_login = true;
        if (isset($_SESSION['username'])) {
            $new_login = false;
        }
        if ($priv_level_needed == 'Member') {
            $redirectUri=$config['baseurl'].'/';
        } else {
            $redirectUri=$config['baseurl'].'/admin/';
        }
        $gclient = $this->get_google_client($redirectUri);
        $google_auth_success=false;
        if ($gclient!== false) {
            //Handle Google Auth Redirect
            
            if (isset($_GET["code"])) {
                unset($_SESSION['access_token']);
                unset($_SESSION['username']);
                //It will Attempt to exchange a code for an valid authentication token.
                $token = $gclient->fetchAccessTokenWithAuthCode($_GET["code"]);
                //This condition will check there is any error occur during geting authentication token. If there is no any error occur then it will execute if block of code/
                if (!isset($token['error'])) {
                    //Set the access token used for requests
                    $gclient->setAccessToken($token['access_token']);
                    //Store "access_token" value in $_SESSION variable for future use.
                    $_SESSION['access_token'] = $token['access_token'];
                    $google_auth_success = $this->verify_google_user($gclient, $priv_level_needed);
                    if (!$google_auth_success) {
                        if ($internal === false) {
                            $display .= $this->display_login($priv_level_needed, $lang['google_auth_invalid']);
                            return $display;
                        } elseif ($internal === 'v2') {
                            return [false, $lang['google_auth_invalid']];
                        } else {
                            return false;
                        }
                    }
                }
            } elseif (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
                $gclient->setAccessToken($_SESSION['access_token']);
                $google_auth_success = $this->verify_google_user($gclient, $priv_level_needed);
                if (!$google_auth_success) {
                    if ($internal === false) {
                        $display .= $this->display_login($priv_level_needed, $lang['google_auth_invalid']);
                        return $display;
                    } elseif ($internal === 'v2') {
                        return [false, $lang['google_auth_invalid']];
                    } else {
                        return false;
                    }
                }
            }
        }
        
        if ($google_auth_success) {
            $checked = true;
        } else {
            $checked = $this->check_login();
        }
        if (!$checked and !isset($_POST['user_name'])) {
            if ($internal === false) {
                return $this->display_login($priv_level_needed);
            } elseif ($internal === 'v2') {
                return [false, ''];
            } else {
                return false;
            }
        } elseif (isset($_POST['user_name']) && !$checked) {
            if (php_sapi_name() != 'cli' && !$skip_csrf) {
                if (!isset($_POST['token']) || !$misc->validate_csrf_token($_POST['token'])) {
                    if ($internal === false) {
                        $display .= $this->display_login($priv_level_needed, $lang['invalid_csrf_token']);
                        return $display;
                    } elseif ($internal === 'v2') {
                        return [false, $lang['invalid_csrf_token']];
                    } else {
                        return false;
                    }
                }
            }
            if (!$_POST['user_name'] || !$_POST['user_pass']) {
                if ($internal === false) {
                    $display .= $this->display_login($priv_level_needed, $lang['required_field_not_filled']);
                    return $display;
                } elseif ($internal === 'v2') {
                    return [false, $lang['required_field_not_filled']];
                } else {
                    return false;
                }
            }
            /* Spruce up username, check length */
            $_POST['user_name'] = trim($_POST['user_name']);
            if (strlen($_POST['user_name']) > 30) {
                if ($internal === false) {
                    $display .= $this->display_login($priv_level_needed, $lang['username_excessive_length']);
                    return $display;
                } elseif ($internal === 'v2') {
                    return [false, $lang['username_excessive_length']];
                } else {
                    return false;
                }
            }
            /* Checks that username is in database and password is correct */
            $result = $this->confirm_user($_POST['user_name'], $_POST['user_pass']);
            /* Check error codes */
            if ($result == 1) {
                if ($internal === false) {
                    $display .= $this->display_login($priv_level_needed, $lang['incorrect_username_password']);
                    return $display;
                } elseif ($internal === 'v2') {
                    return [false, $lang['incorrect_username_password']];
                } else {
                    return false;
                }
            } elseif ($result == 2) {
                if ($internal === false) {
                    $display .= $this->display_login($priv_level_needed, $lang['incorrect_username_password']);
                    return $display;
                } elseif ($internal === 'v2') {
                    return [false, $lang['incorrect_username_password']];
                } else {
                    return false;
                }
            } elseif ($result == 3) {
                if ($internal === false) {
                    $display .= $this->display_login($priv_level_needed, $lang['inactive_user']);
                    return $display;
                } elseif ($internal === 'v2') {
                    return [false, $lang['inactive_user']];
                } else {
                    return false;
                }
            }
        }
        if (isset($_POST['user_name'])) {
            global $misc;

            if (!$this->set_session_vars($_POST['user_name'])) {
                if ($internal === false) {
                    $display .= $this->display_login($priv_level_needed, $lang['access_denied']);
                    return $display;
                } elseif ($internal === 'v2') {
                    return [false, $lang['access_denied']];
                } else {
                    return false;
                }
            }
            
            if (isset($_POST['remember'])) {
                $this->create_rememberme_cookie();
                // setcookie('cookname', $_SESSION['username'], time() + 60 * 60 * 24 * 100, '/', '', false, true);
                // setcookie('cookpass', $_SESSION['userpassword'], time() + 60 * 60 * 24 * 100, '/', '', false, true);
            }
            //call the after login plugin function
            if ($new_login) {
                include_once $config['basepath'] . '/include/hooks.inc.php';
                $hooks = new hooks();
                $hooks->load('after_user_login', $_SESSION['userID']);
            }
        }
        if (!$this->verify_priv($priv_level_needed)) {
            if ($internal === false) {
                $display .= $this->display_login($priv_level_needed, $lang['access_denied']);
                return $display;
            } elseif ($internal === 'v2') {
                return [false, $lang['access_denied']];
            } else {
                return false;
            }
        } else {
            if ($internal === false) {
                return true;
            } elseif ($internal === 'v2') {
                return [true, ''];
            } else {
                return true;
            }
        }
    }

    public function display_login($priv_level_needed, $error_msg = '')
    {
        global $config;
        $display = '';
        if ($error_msg != '') {
            $login_status = false;
        } else {
            [$login_status, $error_msg] = $this->loginCheck('Member', 'v2');
        }

        if (isset($_SERVER['HTTP_REFERER']) && isset($_GET['action']) && $_GET['action'] == 'member_login') {
            if (strpos($_SERVER['HTTP_REFERER'], $config['baseurl']) === 0) {
                if (!isset($_SESSION['login_referer'])) {
                    $_SESSION['login_referer'] = $_SERVER['HTTP_REFERER'];
                }
            }
        }
        if ($login_status == true) {
            if ($_SESSION['login_referer']) {
                $referer_url = $_SESSION['login_referer'];
                unset($_SESSION['login_referer']);
            } else {
                $referer_url = $config['baseurl'] . '/index.php';
            }
            header('Location: ' . $referer_url);
        } else {
            global $lang, $conn, $misc;
            if ($priv_level_needed == 'Member') {
                include_once $config['basepath'] . '/include/core.inc.php';
                $page = new page_user();
                $page->load_page($config['template_path'] . '/login.html');
                $redirectUri=$config['baseurl'].'/';
            } else {
                include_once $config['basepath'] . '/include/core.inc.php';
                $page = new page_admin();
                $page->load_page($config['admin_template_path'] . '/login.html');
                $redirectUri=$config['baseurl'].'/admin/';
            }

            $gclient = $this->get_google_client($redirectUri);
            if ($gclient !== false) {
                $page->page = $page->cleanup_template_block('google_auth', $page->page);
                $page->replace_tag('google_auth_url', $gclient->createAuthUrl());
            } else {
                $page->page = $page->remove_template_block('google_auth', $page->page);
            }

            if ($error_msg != '') {
                $page->page = $page->cleanup_template_block('login_error_msg', $page->page);
                $page->replace_tag('login_error_msg', $error_msg);
            } else {
                $page->page = $page->remove_template_block('login_error_msg', $page->page);
            }

            $display .= $page->return_page();

            // Run the cleanup for the forgot password table
            global $db_type;
            if ($db_type == 'mysql' || $db_type == 'mysqli' || $db_type == 'pdo') {
                $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . 'forgot WHERE forgot_time < NOW() - INTERVAL 1 DAY';
            } else {
                $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . 'forgot WHERE forgot_time < NOW() - INTERVAL \'1 DAY\'';
            }
            $recordSet = $conn->execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            return $display;
        }
    }

    public function verify_priv($priv_level_needed)
    {
        if (!isset($_SESSION['is_member'])) {
            return false;
        }
        switch ($priv_level_needed) {
            case 'Agent':
                if ($_SESSION['isAgent'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
                // case 'canEditForms':
                // if($_SESSION['editForms'] == 'yes' || $_SESSION['admin_privs'] == 'yes')
                // {
                // return TRUE;
                // }
                // break;
            case 'Admin':
                if ($_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'canViewLogs':
                if ($_SESSION['viewLogs'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'CanEditExpiration':
                if ($_SESSION['edit_expiration']  == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'editpages':
                if ($_SESSION['editpages'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'havevtours':
                if ($_SESSION['havevtours'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'havefiles':
                if ($_SESSION['havefiles'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'Member':
                if ($_SESSION['is_member'] == 'yes') {
                    return true;
                }
                break;
            case 'edit_site_config':
                if ($_SESSION['edit_site_config'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'edit_member_template':
                if ($_SESSION['edit_member_template'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'edit_lead_template':
                if ($_SESSION['edit_lead_template'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'edit_agent_template':
                if ($_SESSION['edit_agent_template'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'edit_listing_template':
                if ($_SESSION['edit_listing_template'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'export_listings':
                if ($_SESSION['export_listings'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'edit_all_listings':
                if ($_SESSION['edit_all_listings'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'edit_all_leads':
                if ($_SESSION['edit_all_leads'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'edit_all_users':
                if ($_SESSION['edit_all_users'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'edit_property_classes':
                if ($_SESSION['edit_property_classes'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'can_access_blog_manager':
                if ($_SESSION['blog_user_type'] > 1 || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'is_blog_editor':
                if ($_SESSION['blog_user_type'] == 4 || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            case 'can_manage_addons':
                if ($_SESSION['can_manage_addons'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    return true;
                }
                break;
            default:
                return false;
                break;
        } // End switch($priv_level_needed)
        return false;
    } // End Function verify_priv()

    public function confirm_user_by_email($email)
    {
        global $conn, $config, $lang, $misc;

        //$username = array($username);
        $email = $conn->qstr($email);

        /* Verify that user is in database */
        $sql = 'SELECT userdb_user_name, userdb_active
				FROM ' . $config['table_prefix'] . 'userdb 
				WHERE  userdb_emailaddress = ' . $email . '';
        $recordSet = $conn->Execute($sql);

        if (!$recordSet || ($recordSet->RecordCount() < 1)) {
            return [true, 1]; //Indicates email not found
        }
        if ($recordSet->fields('userdb_active') != 'yes') {
            return [true, 2]; //Indicates user is inactive
        }
       
        return [false, $recordSet->fields('userdb_user_name')]; //Success! Email Found and User is Active
    }
    public function confirm_user($username, $password)
    {
        global $conn, $config, $lang, $misc;

        //$username = array($username);
        $username = $conn->qstr($username);

        /* Verify that user is in database */
        $sql = 'SELECT * 
				FROM ' . $config['table_prefix'] . 'userdb 
				WHERE  userdb_user_name=' . $username . '';
        $recordSet = $conn->Execute($sql);

        if (!$recordSet || ($recordSet->RecordCount() < 1)) {
            return 1; //Indicates username failure
        }
        if ($recordSet->fields('userdb_active') != 'yes') {
            return 3; //Indicates user is inactive
        }
        /* Retrieve password from result, strip slashes */
        $dbarray['password'] = $recordSet->fields('userdb_user_password');


        if (password_verify($password, $dbarray['password'])) {
            return 0; //Success! Username and password confirmed
        } elseif (md5($password) == $dbarray['password']) {
            //Test For Old MD5 Password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql_hash = $misc->make_db_safe($hash);
            $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb 
								SET userdb_user_password = ' . $sql_hash . '
								WHERE userdb_user_name = ' . $username;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            return 0; //Success! Username and password confirmed
        } else {
            return 2; //Indicates password failure
        }
    }

    public function log_out($type = 'admin')
    {
        $old_id = $_SESSION['userID'];
        unset($_SESSION['access_token']);
        unset($_SESSION['username']);
        unset($_SESSION['userpassword']);
        unset($_SESSION['userID']);
        unset($_SESSION['featureListings']);
        unset($_SESSION['viewLogs']);
        unset($_SESSION['admin_privs']);
        unset($_SESSION['active']);
        unset($_SESSION['isAgent']);
        unset($_SESSION['moderator']);
        unset($_SESSION['editpages']);
        unset($_SESSION['havevtours']);
        unset($_SESSION['is_member']);
        // New Permissions with OR 2.1
        unset($_SESSION['edit_site_config']);
        unset($_SESSION['edit_member_template']);
        unset($_SESSION['edit_agent_template']);
        unset($_SESSION['edit_listing_template']);
        unset($_SESSION['export_listings']);
        unset($_SESSION['edit_all_listings']);
        unset($_SESSION['edit_all_users']);
        unset($_SESSION['edit_property_classes']);
        unset($_SESSION['edit_expiration']);
        unset($_SESSION['blog_user_type']);
        // Destroy Cookie
        $this->clear_rememberme_cookie();
        @session_destroy();

        global $config;

        //call the after logout plugin function
        include_once $config['basepath'] . '/include/hooks.inc.php';
        $hooks = new hooks();
        $hooks->load('after_user_logout', $old_id);
        unset($old_id);

        // Refresh the screen
        if ($type == 'admin') {
            header('Location:' . $config['baseurl'] . '/admin/');
        } else {
            header('Location:' . $config['baseurl'] . '/index.php');
        }
        exit();
    }

    public function ajax_forgot_password($admin = true)
    {
        global $lang;
        $result = $this->forgot_password($admin);
        if ($result == $lang['check_your_email']) {
            return json_encode(['error' => false, 'msg' => '<div style="font-weight: bold; text-align: center;  padding: 20px;">' . $lang['check_your_email'] . '</div>']);
        } else {
            return json_encode(['error' => true, 'error_msg' => $result]);
        }
    }

    public function forgot_password($admin = true)
    {
        global  $config, $lang, $conn, $misc;

        $email = null;
        if (isset($_POST['email'])) {
            $email = $_POST['email'];
        };
        if (is_string($email)) {
            if (!isset($_POST['token']) || !$misc->validate_csrf_token($_POST['token'])) {
                return '<font color="red">' . $lang['invalid_csrf_token'] . '</font>';
            }

            $valid = $misc->validate_email($email);
            if ($valid) {
                $email = $conn->qstr($email);
                //$email = mysql_real_escape_string($email);
                // Verify the user has not tried to reset more then 3 times in 24 hours.
                $sql = 'SELECT forgot_id FROM ' . $config['table_prefix_no_lang'] . 'forgot 
						WHERE forgot_email = ' . $email . ' 
						AND forgot_time > NOW() - INTERVAL 1 DAY';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                if ($recordSet->Recordcount() > 3) {
                    return $lang['to_many_password_reset_attempts'];
                }
                if ($config['demo_mode'] == 1) {
                    return $lang['password_reset_denied_demo_mode'];
                }
                $sql = 'SELECT userdb_user_name, userdb_emailaddress 
						FROM ' . $config['table_prefix'] . 'userdb 
						WHERE userdb_emailaddress=' . $email . '';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $num = $recordSet->RecordCount();
                if ($num == 1) {
                    $forgot_rand = mt_rand(100000, 999999);
                    $user_email = $recordSet->fields('userdb_emailaddress');
                    $user_name = $recordSet->fields('userdb_user_name');
                    $sql = 'INSERT INTO ' . $config['table_prefix_no_lang'] . "forgot (forgot_rand, forgot_email, forgot_time) 
							VALUES ($forgot_rand,'$user_email', CURRENT_TIMESTAMP())";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    if ($admin) {
                        $forgot_link = $config['baseurl'] . '/admin/index.php?action=forgot&id=' . $forgot_rand . '&email=' . $user_email;
                    } else {
                        $forgot_link = $config['baseurl'] . '/index.php?action=forgot&id=' . $forgot_rand . '&email=' . $user_email;
                    }
                    $message = $lang['your_username'] . ' ' . $user_name . "\r\n";
                    ;
                    $message .= $lang['click_to_reset_password'] . "\r\n";
                    $message .= $forgot_link . "\r\n";
                    $message .= $lang['link_expires'] . "\r\n";
                    $misc->send_email($config['admin_name'], $config['admin_email'], $user_email, $message, $lang['forgotten_password']);

                    return '<div style="font-weight: bold; text-align: center; padding: 20px;">' . $lang['check_your_email'] . '</div>';
                } else {
                    return '<font color="red">' . $lang['email_invalid_email_address'] . '</font>';
                }
            } else {
                return '<font color="red">' . $lang['email_invalid_email_address'] . '</font>';
            }
        }
    }

    public function forgot_password_reset()
    {
        global $config, $lang, $conn, $misc;

        $data = '';

        if (!isset($_POST['user_pass'])) {
            if (isset($_GET['id']) || isset($_GET['email'])) {
                $id = intval($_GET['id']);

                $email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
                if ($email !== false) {
                    $email = $conn->qstr($email);
                } else {
                    $email = $conn->qstr('');
                }

                //is this an agent
                $is_agent = $this->is_user_agent('', $_GET['email']);

                //set where the form posts to based on user type
                if ($is_agent) {
                    $action_var =  $config['baseurl'] . '/admin/index.php?action=forgot';
                } else {
                    $action_var =  $config['baseurl'] . '/index.php?action=forgot';
                }

                $sql = 'SELECT forgot_id FROM ' . $config['table_prefix_no_lang'] . 'forgot 
						WHERE forgot_email = ' . $email . " 
						AND forgot_rand = $id 
						AND forgot_time > NOW() - INTERVAL 1 DAY";
                $recordSet = $conn->execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $num = $recordSet->RecordCount();
                if ($num == 1) {
                    $data .= '<div style="text-align: center; padding: 20px;">
								<form id="pass_reset" action="' . $action_var . '" method="post">
									<input type="hidden" name="rand_id" value="' . htmlentities($_GET['id']) . '">
									<input type="hidden" name="email" value="' . htmlentities($_GET['email']) . '">
									<p>' . $lang['enter_new_password'] . ' <input type="password" spellcheck="false" name="user_pass" /></p>
									<p><input class="or_std_button" type="submit" value="' . $lang['reset_password'] . '" /></p>
								</form>
							</div>';
                } else {
                    $data .= $lang['invalid_expired_link'];
                }
            } else {
                $data .= $lang['invalid_expired_link'];
            }
        } else {
            $id = intval($_POST['rand_id']);

            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

            if ($email !== false) {
                $email = $conn->qstr($email);
            } else {
                $email = $conn->qstr('');
            }

            $sql = 'SELECT forgot_id 
					FROM ' . $config['table_prefix_no_lang'] . 'forgot 
					WHERE forgot_email = ' . $email . " 
					AND forgot_rand = $id 
					AND forgot_time > NOW() - INTERVAL 1 DAY";
            $recordSet = $conn->execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $num = $recordSet->RecordCount();
            if ($num == 1) {
                // Delete ID from Forgot list
                $delete_id = intval($recordSet->fields('forgot_id'));
                $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . "forgot 
						WHERE forgot_id = $delete_id";
                $recordSet = $conn->execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                // Set Password
                $hash = password_hash($_POST['user_pass'], PASSWORD_DEFAULT);

                $sql_hash = $conn->qstr($hash);

                $sql = 'UPDATE ' . $config['table_prefix'] . "userdb 
						SET userdb_user_password = $sql_hash 
						WHERE userdb_emailaddress = " . $email . '';
                $recordSet = $conn->execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                } else {
                    $data .= '<div style="text-align: center; padding: 20px;">
								<h3>' . $lang['password_changed'] . '</h3>
								<br />
								' . $lang['login'] . ': <a href="' . $config['baseurl'] . '/admin/index.php">' . $config['baseurl'] . '/admin/index.php</a>
							</div>';
                }
            } else {
                $data .= $lang['invalid_expired_link'];
            }
        }
        return $data;
    }

    public function ajax_reset_password()
    {
        global $config, $lang, $conn, $misc;

        $display = '';
        $has_permission = true;

        //Check for edit all user permissions
        $security = $this->verify_priv('edit_all_users');
        if (!$security) {
            //No permission to change
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['access_denied']]);
        } else {
            $userID = intval($_GET['user_id']);
            // no touching the admin if you ain't the admin yo-self
            if ($userID === 1 && $_SESSION['userID'] !== 1) {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => $lang['access_denied']]);
            }

            // Generate and set password
            $newpass = $misc->generatePassword();
            $hash = password_hash($newpass, PASSWORD_DEFAULT);
            $sql_hash = $conn->qstr($hash);

            $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb 
					SET userdb_user_password = ' . $sql_hash . ' 
					WHERE userdb_id = ' . $userID . '';
            $recordSet = $conn->execute($sql);

            if (!$recordSet) {
                //no such user ID
                $misc->log_error($sql);
            } else {
                //send account info email
                include_once $config['basepath'] . '/include/user_manager.inc.php';
                $usermg = new user_managment();

                //is this an agent
                $is_agent = $this->is_user_agent($userID);

                if ($is_agent || $userID === 1) {
                    $usermg->send_user_signup_email($userID, 'agent', $newpass);
                } else {
                    $usermg->send_user_signup_email($userID, 'member', $newpass);
                }

                header('Content-type: application/json');
                return json_encode(['error' => '0', 'user_id' => $_GET['user_id']]);
            }
        }
    }

    public function is_user_agent($user_id = '', $user_email = '')
    {
        global $config, $conn, $misc;

        //returns true if the given user_id or email addy is an Agent
        $is_agent = false;

        if ($user_id != '') {
            $user_id = intval($user_id);
            $where_clause = 'userdb_id = ' . $user_id . '';
        } else {
            $user_email = $conn->qstr($user_email);
            $where_clause = 'userdb_emailaddress = ' . $user_email;
        }

        $sql = 'SELECT userdb_is_agent, userdb_is_admin 
				FROM ' . $config['table_prefix'] . 'userdb 
				WHERE ' . $where_clause . '';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $userdb_is_agent = $recordSet->fields('userdb_is_agent');
        $userdb_is_admin = $recordSet->fields('userdb_is_admin');
        if ($userdb_is_agent == 'yes') {
            $is_agent = true;
        } else {
            if ($userdb_is_admin == 'yes') {
                $is_agent = true;
            }
        }

        return $is_agent;
    }
} //End class login
