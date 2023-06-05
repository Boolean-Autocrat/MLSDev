<?php

class user_managment
{
    public function create_base_user($type, $user_name, $email, $password, $active, $fist_name, $last_name)
    {
        global $misc, $conn, $config;
        $sql_user_name = $misc->make_db_extra_safe($user_name);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sqh_hash = $misc->make_db_safe($hash);
        $sql_user_email = $misc->make_db_extra_safe($user_name);
        $sql_set_active = $misc->make_db_safe($active);
        $sql_user_first_name = $misc->make_db_extra_safe($fist_name);
        $sql_user_last_name = $misc->make_db_extra_safe($last_name);
        // create the account with the random number as the password
        $sql = 'INSERT INTO ' . $config['table_prefix'] . 'userdb (
                    userdb_user_name, userdb_user_password, userdb_user_first_name,userdb_user_last_name, userdb_emailaddress, userdb_creation_date,
                    userdb_last_modified, userdb_active, userdb_comments, userdb_is_admin, userdb_can_edit_site_config, userdb_can_edit_member_template,
                    userdb_can_edit_agent_template, userdb_can_edit_listing_template, userdb_can_feature_listings, userdb_can_view_logs, userdb_hit_count,
                    userdb_can_moderate, userdb_can_edit_pages, userdb_can_have_vtours, userdb_is_agent, userdb_limit_listings, userdb_can_edit_expiration,
                    userdb_can_export_listings, userdb_can_edit_all_users, userdb_can_edit_all_listings, userdb_can_edit_property_classes,
                    userdb_can_have_files,userdb_can_have_user_files) 
                VALUES (' . $sql_user_name . ', ' . $sqh_hash . ', ' . $sql_user_first_name . ', ' . $sql_user_last_name . ', ' . $sql_user_email . ', 
                        ' . $conn->DBDate(time()) . ',' . $conn->DBTimeStamp(time()) . ',' . $sql_set_active . ',\'\',\'no\',\'no\',\'no\',\'no\',\'no\',
                        \'no\',\'no\',0,\'no\',\'no\',\'no\',\'no\',0,\'no\',\'no\',\'no\',\'no\',\'no\',\'no\',\'no\')';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $new_user_id = $conn->Insert_ID(); // this is the new user's ID number
        // Update Agent Settings
        if ($type == 'agent') {
            $is_agent = $misc->make_db_safe('yes');
            if ($config['agent_default_admin'] == 0) {
                $agent_default_admin = $misc->make_db_safe('no');
            } else {
                $agent_default_admin = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_feature'] == 0) {
                $agent_default_feature = $misc->make_db_safe('no');
            } else {
                $agent_default_feature = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_moderate'] == 0) {
                $agent_default_moderate = $misc->make_db_safe('no');
            } else {
                $agent_default_moderate = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_logview'] == 0) {
                $agent_default_logview = $misc->make_db_safe('no');
            } else {
                $agent_default_logview = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_edit_site_config'] == 0) {
                $agent_default_edit_site_config = $misc->make_db_safe('no');
            } else {
                $agent_default_edit_site_config = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_edit_member_template'] == 0) {
                $agent_default_edit_member_template = $misc->make_db_safe('no');
            } else {
                $agent_default_edit_member_template = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_edit_agent_template'] == 0) {
                $agent_default_edit_agent_template = $misc->make_db_safe('no');
            } else {
                $agent_default_edit_agent_template = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_edit_listing_template'] == 0) {
                $agent_default_edit_listing_template = $misc->make_db_safe('no');
            } else {
                $agent_default_edit_listing_template = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_canchangeexpirations'] == 0) {
                $agent_default_canchangeexpirations = $misc->make_db_safe('no');
            } else {
                $agent_default_canchangeexpirations = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_editpages'] == 0) {
                $agent_default_editpages = $misc->make_db_safe('no');
            } else {
                $agent_default_editpages = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_havevtours'] == 0) {
                $agent_default_havevtours = $misc->make_db_safe('no');
            } else {
                $agent_default_havevtours = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_havefiles'] == 0) {
                $agent_default_havefiles = $misc->make_db_safe('no');
            } else {
                $agent_default_havefiles = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_haveuserfiles'] == 0) {
                $agent_default_have_user_files = $misc->make_db_safe('no');
            } else {
                $agent_default_have_user_files = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_can_export_listings'] == 0) {
                $agent_default_can_export_listings = $misc->make_db_safe('no');
            } else {
                $agent_default_can_export_listings = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_edit_all_users'] == 0) {
                $agent_default_edit_all_users = $misc->make_db_safe('no');
            } else {
                $agent_default_edit_all_users = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_edit_all_listings'] == 0) {
                $agent_default_edit_all_listings = $misc->make_db_safe('no');
            } else {
                $agent_default_edit_all_listings = $misc->make_db_safe('yes');
            }
            if ($config['agent_default_edit_property_classes'] == 0) {
                $agent_default_edit_property_classes = $misc->make_db_safe('no');
            } else {
                $agent_default_edit_property_classes = $misc->make_db_safe('yes');
            }
            $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb 
                    SET userdb_is_agent = ' . $is_agent . ', 
                        userdb_is_admin = ' . $agent_default_admin . ', 
                        userdb_can_feature_listings = ' . $agent_default_feature . ', 
                        userdb_can_moderate = ' . $agent_default_moderate . ', 
                        userdb_can_view_logs =' . $agent_default_logview . ', 
                        userdb_can_edit_site_config = ' . $agent_default_edit_site_config . ', 
                        userdb_can_edit_member_template = ' . $agent_default_edit_member_template . ', 
                        userdb_can_edit_agent_template = ' . $agent_default_edit_agent_template . ', 
                        userdb_can_edit_listing_template = ' . $agent_default_edit_listing_template . ', 
                        userdb_can_edit_pages = ' . $agent_default_editpages . ',
                        userdb_can_have_vtours = ' . $agent_default_havevtours . ',
                        userdb_can_have_files = ' . $agent_default_havefiles . ',
                        userdb_can_have_user_files = ' . $agent_default_have_user_files . ', 
                        userdb_limit_listings = ' . $config['agent_default_num_listings'] . ', 
                        userdb_can_edit_expiration = ' . $agent_default_canchangeexpirations . ', 
                        userdb_can_export_listings = ' . $agent_default_can_export_listings . ', 
                        userdb_can_edit_all_users = ' . $agent_default_edit_all_users . ', 
                        userdb_can_edit_all_listings = ' . $agent_default_edit_all_listings . ', 
                        userdb_can_edit_property_classes = ' . $agent_default_edit_property_classes . ' 
                    WHERE userdb_id = ' . $new_user_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
        } else {
            $is_agent = $misc->make_db_safe('no');
            $agent_default_admin = $misc->make_db_safe('no');
            $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb 
                    SET userdb_is_agent = ' . $is_agent . ', 
                        userdb_is_admin = ' . $agent_default_admin . ' 
                    WHERE userdb_id = ' . $new_user_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
        }
        return $new_user_id;
    }
    public function user_signup($type)
    {
        global $lang, $config, $misc, $conn, $jscript;

        include_once $config['basepath'] . '/include/forms.inc.php';
        $forms = new forms();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $display = '';

        //See if we are already logged in
        $login_passed = $login->loginCheck('Member', true);
        if ($login_passed) {
            $temp = $lang['signup_already_logged_in'];
            return $temp;
        }
        if ($config['allow_' . $type . '_signup'] == 1) {
            if (isset($_POST['edit_user_name'])) {
                //Check CRSF token
                if (!isset($_POST['token']) || !$misc->validate_csrf_token($_POST['token'])) {
                    $temp = $lang['invalid_csrf_token'];
                    return $temp;
                }
                if ($_POST['edit_user_pass'] != $_POST['edit_user_pass2']) {
                    $display .= '<p>' . $lang['user_creation_password_identical'] . '</p>';
                    $display .= '<form><input type="button" value="' . $lang['back_button_text'] . '" onclick="history.back()" /></form>';
                } elseif ($_POST['edit_user_pass'] == '') {
                    $display .= '<p>' . $lang['user_creation_password_blank'] . '</p>';
                    $display .= '<form><input type="button" value="' . $lang['back_button_text'] . '" onclick="history.back()" /></form>';
                } elseif ($_POST['edit_user_name'] == '') {
                    $display .= '<p>' . $lang['user_editor_need_username'] . '</p>';
                    $display .= '<form><input type="button" value="' . $lang['back_button_text'] . '" onclick="history.back()" /></form>';
                } elseif ($_POST['user_email'] == '') {
                    $display .= '<p>' . $lang['user_editor_need_email_address'] . '</p>';
                    $display .= '<form><input type="button" value="' . $lang['back_button_text'] . '" onclick="history.back()" /></form>';
                } elseif ($_POST['user_first_name'] == '') {
                    $display .= '<p>' . $lang['user_editor_need_first_name'] . '</p>';
                    $display .= '<form><input type="button" value="' . $lang['back_button_text'] . '" onclick="history.back()" /></form>';
                } elseif ($_POST['user_last_name'] == '') {
                    $display .= '<p>' . $lang['user_editor_need_last_name'] . '</p>';
                    $display .= '<form><input type="button" value="' . $lang['back_button_text'] . '" onclick="history.back()" /></form>';
                } else {
                    if ($config['use_signup_image_verification'] == 1) {
                        include_once $config['basepath'] . '/include/captcha.inc.php';
                        $captcha = new captcha();
                        $correct_code = $captcha->validate();

                        if (!$correct_code) {
                            $display .= '<p>' . $lang['signup_verification_code_not_valid'] . '</p>';
                            $display .= '<form><input type="button" value="' . $lang['back_button_text'] . '" onclick="history.back()" /></form>';
                            return $display;
                        }
                    }
                    $sql_user_name = $misc->make_db_safe(strip_tags($_POST['edit_user_name']));
                    $sql_user_email = $misc->make_db_safe(strip_tags($_POST['user_email']));
                    $sql_user_first_name = $misc->make_db_safe(strip_tags($_POST['user_first_name']));
                    $sql_user_last_name = $misc->make_db_safe(strip_tags($_POST['user_last_name']));
                    $pass_the_form = 'No';
                    // first, make sure the user name isn't in use
                    $sql = 'SELECT userdb_user_name 
							FROM ' . $config['table_prefix'] . 'userdb 
							WHERE userdb_user_name = ' . $sql_user_name;
                    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $num = $recordSet->RecordCount();
                    // second, make sure the user eamail isn't in use
                    $sql2 = 'SELECT userdb_emailaddress 
							FROM ' . $config['table_prefix'] . 'userdb 
							WHERE userdb_emailaddress = ' . $sql_user_email;
                    $recordSet2 = $conn->Execute($sql2);
                    if (!$recordSet2) {
                        $misc->log_error($sql2);
                    }
                    $num2 = $recordSet2->RecordCount();
                    //Make sure email address is not banned.
                    $banned_domains = explode("\n", $config['banned_domains_signup']);
                    $is_banned_domain = false;
                    foreach ($banned_domains as $bd) {
                        if ($bd !== '') {
                            if (stripos($_POST['user_email'], $bd) !== false) {
                                $is_banned_domain = true;
                            }
                        }
                    }
                    //Get Users IP
                    if (isset($_SERVER['HTTP_X_FORWARD_FOR'])) {
                        $ip = $_SERVER['HTTP_X_FORWARD_FOR'];
                    } else {
                        $ip = $_SERVER['REMOTE_ADDR'];
                    }
                    $banned_ips = explode("\n", $config['banned_ips_signup']);
                    $is_banned_ip = false;
                    foreach ($banned_ips as $bi) {
                        if ($bi !== '') {
                            if (stripos($ip, $bi) === 0) {
                                $is_banned_ip = true;
                            }
                        }
                    }
                    if ($is_banned_ip) {
                        $pass_the_form = 'No';
                        $display .= $lang['ip_banned_signup_ban'];
                        $display .= '<form><input type="button" value="' . $lang['back_button_text'] . '" onclick="history.back()" /></form>';
                    } elseif ($is_banned_domain) {
                        $pass_the_form = 'No';
                        $display .= $lang['email_domain_signup_ban'];
                        $display .= '<form><input type="button" value="' . $lang['back_button_text'] . '" onclick="history.back()" /></form>';
                    } elseif ($num >= 1) {
                        $pass_the_form = 'No';
                        $display .= $lang['user_creation_username_taken'];
                        $display .= '<form><input type="button" value="' . $lang['back_button_text'] . '" onclick="history.back()" /></form>';
                    } // end if
                    elseif ($num2 >= 1) {
                        $pass_the_form = 'No';
                        $display .= $lang['email_address_already_registered'];
                        $display .= '<form><input type="button" value="' . $lang['back_button_text'] . '" onclick="history.back()" /></form>';
                    } // end if
                    else {
                        // validate the user form
                        $pass_the_form = $forms->validateForm($type . 'formelements');
                        if ($pass_the_form == 'No') {
                            // if we're not going to pass it, tell that they forgot to fill in one of the fields

                            $display .= '<p>' . $lang['required_fields_not_filled'] . '</p>';
                            $display .= '<form><input type="button" value="' . $lang['back_button_text'] . '" onclick="history.back()" /></form>';
                        }
                        if ($pass_the_form != 'Yes') {
                            // if we're not going to pass it, tell that they forgot to fill in one of the fields
                            $display .= '<p>' . $lang['required_fields_not_filled'] . '</p>';
                            $display .= '<form><input type="button" value="' . $lang['back_button_text'] . '" onclick="history.back()" /></form>';
                        }
                    }

                    if ($pass_the_form == 'Yes') {
                        // what the program should do if the form is valid
                        // generate a random number to enter in as the password (initially)
                        // we'll need to know the actual account id to help with retrieving the user
                        // We will be putting in a random number that we know the value of, we can easily
                        // retrieve the account id in a few moment
                        // check to see if moderation is turned on...
                        if ($config['moderate_' . $type . 's'] == 1) {
                            $set_active = 'no';
                        } else {
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

                        if ($config['require_email_verification'] == 1) {
                            $set_active = 'no';
                        }

                        #Todo: Call Create User
                        $new_user_id = $this->create_base_user(
                            $type,
                            $_POST['edit_user_name'],
                            $_POST['user_email'],
                            $_POST['edit_user_pass'],
                            $set_active,
                            $_POST['user_first_name'],
                            $_POST['user_last_name']
                        );
                        // Update Remaining Variables
                   
                        $message = $this->updateUserData($new_user_id);
                        if ($message == 'success') {
                            // $user_name = $_POST['edit_user_name'];
                            $display .= '<p>' . $lang['user_creation_username_success'] . ', ' . $_POST['edit_user_name'] . '</p>';
                            if ($config['moderate_' . $type . 's'] == 1) {
                                // if moderation is turned on...
                                $display .= '<p>' . $lang['admin_new_user_moderated'] . '</p>';
                            } elseif ($config['require_email_verification'] == 1) {
                                $display .= '<p>' . $lang['admin_new_user_email_verification'] . '</p>';
                            } else {
                                //log the user in
                                $_POST['user_name'] = $_POST['edit_user_name'];
                                $_POST['user_pass'] = $_POST['edit_user_pass'];
                                $login->verify_priv('Member');
                                $display .= '<p>' . $lang['you_may_now_view_priv'] . '</p>';
                            }
                            $misc->log_action($lang['log_created_user'] . ' #' . $new_user_id . ' : ' . $_POST['edit_user_name']);
                            //call the new user plugin function
                            include_once $config['basepath'] . '/include/hooks.inc.php';
                            $hooks = new hooks();
                            $hooks->load('after_user_signup', $new_user_id);

                            if ($config['email_notification_of_new_users'] == 1 && $config['require_email_verification'] == 0) {
                                // if the site admin should be notified when a new user is added
                                $remote_ip = $_SERVER['REMOTE_ADDR'];
                                $signup_timestamp = date('F j, Y, g:i:s a');
                                $this->send_user_signup_notification($new_user_id, $type, $remote_ip, $signup_timestamp);
                            }
                            if (($config['email_information_to_new_users'] == 1) || ($config['require_email_verification'] == 1)) {
                                $this->send_user_signup_email($new_user_id, $type);
                            }
                        } else {
                            $display .= '<p>' . $lang['alert_site_admin'] . '</p>';
                        }
                    } // end if ($pass_the_form == 'Yes')
                } // end else
            } else {
                if ($type == 'agent') {
                    $page->load_page($config['template_path'] . '/agent_signup.html');
                } else {
                    $page->load_page($config['template_path'] . '/member_signup.html');
                }

                $custom_fields = '';
                if ($type == 'agent') {
                    $sql = 'SELECT ' . $type . 'formelements_field_type, ' . $type . 'formelements_field_name, ' . $type . 'formelements_field_caption, 
									' . $type . 'formelements_default_text, ' . $type . 'formelements_field_elements, ' . $type . 'formelements_required, 
									' . $type . 'formelements_tool_tip
							FROM ' . $config['table_prefix'] . $type . 'formelements 
							WHERE agentformelements_display_priv <= 2
							ORDER BY ' . $type . 'formelements_rank, ' . $type . 'formelements_field_caption';
                } else {
                    $sql = 'SELECT ' . $type . 'formelements_field_type, ' . $type . 'formelements_field_name, ' . $type . 'formelements_field_caption, 
									' . $type . 'formelements_default_text, ' . $type . 'formelements_field_elements, ' . $type . 'formelements_required, 
									' . $type . 'formelements_tool_tip
							FROM ' . $config['table_prefix'] . $type . 'formelements
							ORDER BY ' . $type . 'formelements_rank, ' . $type . 'formelements_field_caption';
                }
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                while (!$recordSet->EOF) {
                    $field_type = $recordSet->fields($type . 'formelements_field_type');
                    $field_name = $recordSet->fields($type . 'formelements_field_name');
                    $field_caption = $recordSet->fields($type . 'formelements_field_caption');
                    $default_text = $recordSet->fields($type . 'formelements_default_text');
                    $field_elements = $recordSet->fields($type . 'formelements_field_elements');
                    $required = $recordSet->fields($type . 'formelements_required');
                    $tool_tip = $recordSet->fields($type . 'formelements_tool_tip');

                    $field_type = $field_type;
                    $field_name = $field_name;
                    $field_caption = $field_caption;
                    $default_text = $default_text;
                    $field_elements = $field_elements;
                    $required = $required;
                    $tool_tip = $tool_tip;
                    $custom_fields .= $forms->renderFormElement($field_type, $field_name, '', $field_caption, $default_text, $required, $field_elements, '', $tool_tip);

                    $recordSet->MoveNext();
                } // end while

                $page->replace_tag('custom_fields', $custom_fields);
                if ($config['use_signup_image_verification'] == 1) {
                    include_once $config['basepath'] . '/include/captcha.inc.php';
                    $captcha = new captcha();
                    $page->replace_tag('captcha_display', $captcha->show());
                } else {
                    $page->replace_tag('captcha_display', '');
                }

                $display .= $page->return_page();
            }
        } // end if ($config[allow_user_signup] === "1")
        else {
            // if users can't sign up...
            $display .= '<h3>' . $lang['no_user_signup'] . '</h3>';
        }
        return $display;
    } //End function user_signup()

    public function ajax_member_creation($email, $fname, $lname, $login = true)
    {
        $_POST['edit_user_pass'] = $this->generatePassword();
        $_POST['edit_user_pass2'] = $_POST['edit_user_pass'];
        $_POST['user_email'] = trim($email);
        $_POST['edit_user_name'] = trim($email);
        $_POST['user_first_name'] = trim($fname);
        $_POST['user_last_name'] = trim($lname);
        $user_signup_status = $this->member_creation($login);
        if (is_array($user_signup_status)) {
            //return array('user_id'=>$new_user_id,'active'=>$set_active);
            return json_encode(['error' => false, 'active' => $user_signup_status['active'], 'fname' => $_POST['user_first_name'], 'lname' => $_POST['user_last_name'], 'user_id' => $user_signup_status['user_id'], 'email' => $_POST['user_email']]);
        } else {
            return json_encode(['error' => true, 'error_msg' => $user_signup_status]);
        }
    }

    public function member_creation($do_login = true)
    {
        global $lang, $config, $misc, $conn, $jscript;

        include_once $config['basepath'] . '/include/forms.inc.php';
        $forms = new forms();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $display = '';
        $type = 'member';
        if ($_POST['edit_user_pass'] != $_POST['edit_user_pass2']) {
            return $lang['user_creation_password_identical'];
        } elseif ($_POST['edit_user_pass'] == '') {
            return $lang['user_creation_password_blank'];
        } elseif ($_POST['edit_user_name'] == '') {
            return $lang['user_editor_need_username'];
        } elseif ($_POST['user_email'] == '') {
            return $lang['user_editor_need_email_address'];
        } elseif ($_POST['user_first_name'] == '') {
            return $lang['user_editor_need_first_name'];
        } elseif ($_POST['user_last_name'] == '') {
            return $lang['user_editor_need_last_name'];
        } else {
            $sql_user_name = $misc->make_db_safe(strip_tags($_POST['edit_user_name']));
            $sql_user_email = $misc->make_db_safe(strip_tags($_POST['user_email']));
            $sql_user_first_name = $misc->make_db_safe(strip_tags($_POST['user_first_name']));
            $sql_user_last_name = $misc->make_db_safe(strip_tags($_POST['user_last_name']));
            // first, make sure the user name isn't in use
            $sql = 'SELECT userdb_user_name from ' . $config['table_prefix'] . 'userdb WHERE userdb_user_name = ' . $sql_user_name;
            $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $num = $recordSet->RecordCount();
            // second, make sure the user eamail isn't in use
            $sql2 = 'SELECT userdb_emailaddress from ' . $config['table_prefix'] . 'userdb WHERE userdb_emailaddress = ' . $sql_user_email;
            $recordSet2 = $conn->Execute($sql2);
            if (!$recordSet2) {
                $misc->log_error($sql2);
            }
            $num2 = $recordSet2->RecordCount();
            //Make sure email address is not banned.
            $banned_domains = explode("\n", $config['banned_domains_signup']);
            $is_banned_domain = false;
            foreach ($banned_domains as $bd) {
                if ($bd != '') {
                    if (stripos($_POST['user_email'], $bd) !== false) {
                        $is_banned_domain = true;
                    }
                }
            }
            //Get Users IP
            if (in_array('HTTP_X_FORWARD_FOR', $_SERVER)) {
                $ip = $_SERVER['HTTP_X_FORWARD_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $banned_ips = explode("\n", $config['banned_ips_signup']);
            $is_banned_ip = false;
            foreach ($banned_ips as $bi) {
                if ($bi != '') {
                    if (stripos($ip, $bi) === 0) {
                        $is_banned_ip = true;
                    }
                }
            }
            if ($is_banned_ip) {
                return $lang['ip_banned_signup_ban'];
            } elseif ($is_banned_domain) {
                return $lang['email_domain_signup_ban'];
            } elseif ($num >= 1) {
                return $lang['user_creation_username_taken'];
            } // end if
            elseif ($num2 >= 1) {
                return $lang['email_address_already_registered'];
            } // end if

            // what the program should do if the form is valid
            // generate a random number to enter in as the password (initially)
            // we'll need to know the actual account id to help with retrieving the user
            // We will be putting in a random number that we know the value of, we can easily
            // retrieve the account id in a few moment
            // check to see if moderation is turned on...
            $set_active = 'yes';

            if ($config['moderate_' . $type . 's'] == 1) {
                $set_active = 'mod';
            }

            if ($config['require_email_verification'] == 1) {
                $set_active = 'email';
            }

            $hash = password_hash($_POST['edit_user_pass'], PASSWORD_DEFAULT);
            $sql_hash = $misc->make_db_safe($hash);
            if ($set_active == 'yes') {
                $sql_set_active = $misc->make_db_safe($set_active);
            } else {
                $sql_set_active = $misc->make_db_safe('no');
            }

            // create the account with the random number as the password
            $sql = 'INSERT INTO ' . $config['table_prefix'] . 'userdb (
					userdb_user_name, userdb_user_password, userdb_user_first_name,userdb_user_last_name, userdb_emailaddress, userdb_creation_date,userdb_last_modified, 
					userdb_active, userdb_comments, userdb_is_admin, userdb_can_edit_site_config, userdb_can_edit_member_template, userdb_can_edit_agent_template, 
					userdb_can_edit_listing_template, userdb_can_feature_listings,userdb_can_view_logs, userdb_hit_count, userdb_can_moderate, userdb_can_edit_pages,
					userdb_can_have_vtours, userdb_is_agent, userdb_limit_listings, userdb_can_edit_expiration, userdb_can_export_listings,userdb_can_edit_all_users,
					userdb_can_edit_all_listings, userdb_can_edit_property_classes, userdb_can_have_files, userdb_can_have_user_files ) 
					VALUES (' . $sql_user_name . ', ' . $sql_hash . ', ' . $sql_user_first_name . ', ' . $sql_user_last_name . ', ' . $sql_user_email . ', 
					' . $conn->DBDate(time()) . ',' . $conn->DBTimeStamp(time()) . ',' . $sql_set_active . ',\'\',\'no\',\'no\',\'no\',\'no\',\'no\',\'no\',
					\'no\',0,\'no\',\'no\',\'no\',\'no\',0,\'no\',\'no\',\'no\',\'no\',\'no\',\'no\',\'no\')';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $new_user_id = $conn->Insert_ID(); // this is the new user's ID number
            if ($set_active == 'yes' && $do_login) {
                //User should be logged in.
                $_POST['user_name'] = $_POST['edit_user_name'];
                $_POST['user_pass'] = $_POST['edit_user_pass'];
                $login_passed = $login->loginCheck('Member', true);
            }
            //call the new user plugin function
            include_once $config['basepath'] . '/include/hooks.inc.php';
            $hooks = new hooks();
            $hooks->load('after_user_signup', $new_user_id);

            if ($config['email_notification_of_new_users'] == 1 && $config['require_email_verification'] == 0) {
                $remote_ip = $_SERVER['REMOTE_ADDR'];
                $signup_timestamp = date('F j, Y, g:i:s a');

                $this->send_user_signup_notification($new_user_id, 'member', $remote_ip, $signup_timestamp);
                //refactored/deprecated for v3.2.11
                //$this->send_member_signup_notification($new_user_id,$remote_ip,$signup_timestamp);
            } // end if
            if ($config['email_information_to_new_users'] == 1 || $config['require_email_verification'] == 1) {
                $this->send_user_signup_email($new_user_id, 'member', $_POST['edit_user_pass']);
            } //end if
            return ['user_id' => $new_user_id, 'active' => $set_active];
        }
    }

    public function edit_member_profile($user_id)
    {
        global $conn, $config, $misc, $lang;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        include_once $config['basepath'] . '/include/forms.inc.php';
        $forms = new forms();
        $display = '';
        // Set Variable to hold errors
        // Verify ID is Numeric
        if (!is_numeric($user_id)) {
            return $lang['user_manager_invalid_user_id'];
        }
        if ($_SESSION['userID'] == $user_id && $_SESSION['is_member'] == 'yes') {
            $sql_edit = intval($_SESSION['userID']);
            $raw_id = intval($_SESSION['userID']);
        } else {
            return $lang['user_manager_permission_denied'];
        }
        // $raw_id = $sql_edit;
        // Save any Changes that were posted
        $status = '';
        if (isset($_POST['edit'])) {
            $status = $this->update_member_profile($raw_id);
        }

        // Show Account Edit Form
        //Load Template
        $page->load_page($config['template_path'] . '/edit_profile.html');
        // first, grab the user's main info
        $sql = 'SELECT * 
				FROM ' . $config['table_prefix'] . 'userdb 
				WHERE userdb_id = ' . $sql_edit;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }

        // collect up the main DB's various fields
        $edit_user_name = $recordSet->fields('userdb_user_name');
        $edit_emailAddress = $recordSet->fields('userdb_emailaddress');
        $edit_isAgent = $recordSet->fields('userdb_is_agent');
        $edit_firstname = $recordSet->fields('userdb_user_first_name');
        $edit_lastname = $recordSet->fields('userdb_user_last_name');
        $last_modified = $recordSet->UserTimeStamp($recordSet->fields('userdb_last_modified'), $config['date_format_timestamp']);
        $page->replace_tag('user_id', $raw_id);
        $page->replace_tag('user_name', htmlentities($edit_user_name, ENT_COMPAT, $config['charset']));
        $page->replace_tag('user_firstname', htmlentities($edit_firstname, ENT_COMPAT, $config['charset']));
        $page->replace_tag('user_lastname', htmlentities($edit_lastname, ENT_COMPAT, $config['charset']));
        $page->replace_tag('user_last_modified', htmlentities($last_modified, ENT_COMPAT, $config['charset']));
        $page->replace_tag('user_emailAddress', htmlentities($edit_emailAddress, ENT_COMPAT, $config['charset']));

        if ($config['demo_mode'] != 1 || $_SESSION['admin_privs'] == 'yes') {
            $page->page = $page->cleanup_template_block('password', $page->page);
        } else {
            $page->page = $page->remove_template_block('password', $page->page);
        }

        if ($edit_isAgent == 'yes') {
            $db_to_use = 'agentformelements';
        } else {
            $db_to_use = 'memberformelements';
        }

        $sql = 'SELECT ' . $db_to_use . '_field_name, userdbelements_field_value, ' . $db_to_use . '_field_type, ' . $db_to_use . '_rank, ' . $db_to_use . '_field_caption, ' . $db_to_use . '_default_text, ' . $db_to_use . '_required, ' . $db_to_use . '_field_elements, ' . $db_to_use . '_tool_tip 
				FROM ' . $config['table_prefix'] . $db_to_use . ' 
				LEFT JOIN ' . $config['table_prefix'] . 'userdbelements on userdbelements_field_name = ' . $db_to_use . '_field_name and userdb_id = ' . $sql_edit . ' 
				ORDER BY ' . $db_to_use . '_rank';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $detail_fields = '';
        while (!$recordSet->EOF) {
            $field_name = $recordSet->fields($db_to_use . '_field_name');
            $field_value = $recordSet->fields('userdbelements_field_value');
            $field_type = $recordSet->fields($db_to_use . '_field_type');
            $field_caption = $recordSet->fields($db_to_use . '_field_caption');
            $default_text = $recordSet->fields($db_to_use . '_default_text');
            $field_elements = $recordSet->fields($db_to_use . '_field_elements');
            $required = $recordSet->fields($db_to_use . '_required');
            $tool_tip = $recordSet->fields($db_to_use . '_tool_tip');
            // pass the data to the function
            $detail_fields .= $forms->renderFormElement($field_type, $field_name, $field_value, $field_caption, $default_text, $required, $field_elements, '', $tool_tip);
            $recordSet->MoveNext();
        } // end while

        $page->replace_tag('user_detail_fields', $detail_fields);
        $page->replace_permission_tags();
        $page->auto_replace_tags('', true);
        $display .= $page->return_page();
        //print_r($_SESSION);
        return $display;
    }

    public function update_member_profile($user_id)
    {
        global $conn, $config, $misc, $lang;

        include_once $config['basepath'] . '/include/forms.inc.php';
        $forms = new forms();
        $display = '';
        if ($_SESSION['edit_all_users'] == 'yes' || $_SESSION['admin_privs'] == 'yes' || $user_id = $_SESSION['userID']) {
            $do_update = true;
            if ($_POST['edit_user_pass'] != $_POST['edit_user_pass2']) {
                $display .= '<p>' . $lang['user_manager_password_identical'] . '</p>';
                $do_update = false;
            } elseif ($_POST['edit_user_pass'] == '') {
                $do_update = true;
            } // end elseif
            if ($_POST['user_email'] == '' || $_POST['user_first_name'] == '' || $_POST['user_last_name'] == '') {
                $display .= '<p class="redtext">' . $lang['required_fields_not_filled'] . '</p>';
                $do_update = false;
            }
            // Get Current User type
            $sql = 'SELECT userdb_is_agent, userdb_is_admin, userdb_active 
					FROM ' . $config['table_prefix'] . 'userdb 
					WHERE userdb_id = ' . $user_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $is_agent = $recordSet->fields('userdb_is_agent');
            $is_admin = $recordSet->fields('userdb_is_admin');
            $is_active = $recordSet->fields('userdb_active');
            if ($do_update) {
                global $pass_the_form;
                if ($is_agent == 'yes' || $is_admin == 'yes') {
                    $db_to_validate = 'agentformelements';
                } else {
                    $db_to_validate = 'memberformelements';
                }
                $pass_the_form = $forms->validateForm($db_to_validate);
                $sql_user_email = $misc->make_db_safe(strip_tags($_POST['user_email']));
                $sql_user_first_name = $misc->make_db_safe(strip_tags($_POST['user_first_name']));
                $sql_user_last_name = $misc->make_db_safe(strip_tags($_POST['user_last_name']));
                //Make sure no other user has this email address.
                $sql = 'SELECT userdb_id 
						FROM ' . $config['table_prefix'] . 'userdb 
						WHERE  userdb_emailaddress = ' . $sql_user_email;

                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                while (!$recordSet->EOF) {
                    if ($recordSet->fields('userdb_id') != $user_id) {
                        $display .= "<p class=\"redtext\">$lang[email_address_already_used]</p>";
                        return $display;
                    }
                    $recordSet->MoveNext();
                }

                if (is_array($pass_the_form)) {
                    // if we're not going to pass it, tell that they forgot to fill in one of the fields
                    foreach ($pass_the_form as $k => $v) {
                        if ($v == 'REQUIRED') {
                            $display .= "<p class=\"redtext\">$k: $lang[required_fields_not_filled]</p>";
                        }
                        if ($v == 'TYPE') {
                            $display .= "<p class=\"redtext\">$k: $lang[field_type_does_not_match]</p>";
                        }
                    }
                } else {
                    include_once $config['basepath'] . '/include/hooks.inc.php';
                    $hooks = new hooks();
                    $hooks->load('before_user_change', $user_id);
                    if ($_POST['edit_user_pass'] == '') {
                        $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb 
								SET userdb_emailaddress = ' . $sql_user_email . ', 
									userdb_user_first_name = ' . $sql_user_first_name . ', 
									userdb_user_last_name = ' . $sql_user_last_name . ', 
									userdb_last_modified = ' . $conn->DBTimeStamp(time()) . ' 
								WHERE userdb_id = ' . $user_id;
                    } else {
                        $hash = password_hash($_POST['edit_user_pass'], PASSWORD_DEFAULT);
                        $sql_hash = $misc->make_db_safe($hash);
                        $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb 
								SET userdb_emailaddress = ' . $sql_user_email . ', 
									userdb_user_first_name = ' . $sql_user_first_name . ', 
									userdb_user_last_name = ' . $sql_user_last_name . ', 
									userdb_user_password = ' . $sql_hash . ', 
									userdb_last_modified = ' . $conn->DBTimeStamp(time()) . ' 
								WHERE userdb_id = ' . $user_id;
                    }
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }

                    $message = $this->updateUserData($user_id);
                    if ($message == 'success') {
                        //call the user change plugin function
                        include_once $config['basepath'] . '/include/hooks.inc.php';
                        $hooks = new hooks();
                        $hooks->load('after_user_change', $user_id);

                        $display .= '<p>' . $lang['user_editor_account_updated'] . ', ' . htmlentities($_SESSION['username']) . '</p>';
                    } // end if
                    else {
                        $display .= '<p>' . $lang['alert_site_admin'] . '</p>';
                    } // end else
                } // end if $pass_the_form == "Yes"
            } // end else
            return $display;
        } // end if $_POST['action'] == "update_user"
    }

    public function ajax_display_add_user()
    {
        global $conn, $config, $misc, $lang, $jscript;
        $display = '';
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_all_users');
        $status_text = '';
        if ($security) {
            //Load the Core Template
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/add_user.html');
            $yes_no['no'] = $lang['no'];
            $yes_no['yes'] = $lang['yes'];

            //Load CSS File
            $jscript .= '{load_css_user_manager}';

            $html = $page->get_template_section('user_status_option_block');
            $html = $page->form_options($yes_no, '', $html);
            $page->replace_template_section('user_status_option_block', $html);

            $html = $page->get_template_section('user_isAdmin_option_block');
            $html = $page->form_options($yes_no, '', $html);
            $page->replace_template_section('user_isAdmin_option_block', $html);

            $html = $page->get_template_section('user_isAgent_option_block');
            $html = $page->form_options($yes_no, '', $html);
            $page->replace_template_section('user_isAgent_option_block', $html);

            $page->replace_tags(['curley_open', 'curley_close', 'baseurl']);
            $page->replace_tag('application_status_text', $status_text);
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        }
    }

    public function ajax_add_user()
    {
        global $conn, $config, $lang;
        $display = '';
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_all_users');
        if ($security) {
            $result = $this->create_user();
            if (is_numeric($result)) {
                header('Content-type: application/json');
                return json_encode(['error' => 0, 'user_id' => $result]);
            } else {
                header('Content-type: application/json');
                return json_encode(['error' => 1, 'error_msg' => $result]);
            }
        }
    }

    public function ajax_delete_user($user_id)
    {
        global $conn, $lang, $config, $misc, $listingID, $jscript;

        $user_id = intval($user_id);
        $display = '';
        $is_admin = $misc->get_admin_status($user_id);
        $has_permission = false;
        if ((($config['demo_mode'] != 1) && ($_SESSION['edit_all_users'] == 'yes')) || ($_SESSION['admin_privs'] == 'yes')) {
            if ($is_admin && $_SESSION['admin_privs'] == 'no') {
                $has_permission = false;
            } else {
                $has_permission = true;
            }
        }
        if ($has_permission) {
            $result = $this->delete_user($user_id);
            //will need to change this when switching to the user API it returns false
            if (is_numeric($result)) {
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'user_id' => $user_id]);
            } else {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => $result]);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['listing_editor_permission_denied']]);
        }
        return $display;
    }

    public function ajax_update_user_data($user_id)
    {
        global $conn, $misc, $lang, $config, $jscript, $api;

        //$user_id is current logged-in user
        $display = '';
        include_once $config['basepath'] . '/include/forms.inc.php';
        $forms = new forms();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        //Make sure we can edit this listing.
        //if you're not the current user you better have edit_all permissions
        $has_permission = true;

        if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
        }

        if ($_SESSION['userID'] != $user_id) {
            $security = $login->verify_priv('edit_all_users');
            if (!$security) {
                $has_permission = false;
            }
        }
        if ($has_permission) {
            $user_id = intval($user_id);

            $do_update = true;
            if ($_POST['edit_user_pass'] != $_POST['edit_user_pass2']) {
                $display .= $lang['user_manager_password_identical'];
                $do_update = false;
            } elseif ($_POST['edit_user_pass'] == '') {
                $do_update = true;
            } // end elseif
            if ($_POST['user_email'] == '' || $_POST['user_first_name'] == '' || $_POST['user_last_name'] == '') {
                $display .= $lang['required_fields_not_filled'];
                $do_update = false;
            }

            $is_admin = $misc->get_admin_status($user_id);
            $is_agent = $misc->get_agent_status($user_id);

            $sql_user_email = $misc->make_db_safe(strip_tags($_POST['user_email']));
            $sql_user_first_name = $misc->make_db_safe(strip_tags($_POST['user_first_name']));
            $sql_user_last_name = $misc->make_db_safe(strip_tags($_POST['user_last_name']));
            /*
                        //Make sure no other user has this email address.
                        $result = $api->load_local_api('user__search',array(
                                    'parameters'=>array(
                                    'userdb_active' =>'any',
                                    'userdb_emailaddress' => $sql_user_email
                                ),
                                'resource' =>'agent',
                                'count_only'=>0
                            ));
                        // there was an ID match, no dice

                echo '<pre>';
                print_r($result);
                echo $result['user_count'].'-z';
                echo $result['users'][0].' - UID '.$user_id.' m- ';
                echo $_POST['user_email'];
                echo '</pre>';

                        if ($result['users'][0] != $user_id) {
                            $display .= $lang['email_address_already_used'];
                            $do_update = false;
                        }

                            $result['user_count']
            */

            $sql = 'SELECT userdb_id 
					FROM ' . $config['table_prefix'] . 'userdb 
					WHERE  userdb_emailaddress = ' . $sql_user_email;

            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            while (!$recordSet->EOF) {
                if ($recordSet->fields('userdb_id') != $user_id) {
                    $display .= $lang['email_address_already_used'];
                    $do_update = false;
                }
                $recordSet->MoveNext();
            }

            if ($do_update) {
                global $pass_the_form;
                if ($is_agent === true || $is_admin === true) {
                    $db_to_validate = 'agentformelements';
                } else {
                    $db_to_validate = 'memberformelements';
                }
                $pass_the_form = $forms->validateForm($db_to_validate);

                if (is_array($pass_the_form)) {
                    // if we're not going to pass it, tell that they forgot to fill in one of the fields
                    foreach ($pass_the_form as $k => $v) {
                        if ($v == 'REQUIRED') {
                            $display .= "$k: $lang[required_fields_not_filled]";
                        }
                        if ($v == 'TYPE') {
                            $display .= "$k: $lang[field_type_does_not_match]";
                        }
                    }
                } else {
                    include_once $config['basepath'] . '/include/hooks.inc.php';
                    $hooks = new hooks();
                    $hooks->load('before_user_change', $user_id);
                    if ($_POST['edit_user_pass'] == '') {
                        $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb 
								SET userdb_emailaddress = ' . $sql_user_email . ', userdb_last_modified = ' . $conn->DBTimeStamp(time()) . ' 
								WHERE userdb_id = ' . $user_id;
                    } else {
                        $hash = password_hash($_POST['edit_user_pass'], PASSWORD_DEFAULT);
                        $sql_hash = $misc->make_db_safe($hash);
                        $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb 
								SET userdb_emailaddress = ' . $sql_user_email . ', 
									userdb_user_password = ' . $sql_hash . ', 
									userdb_last_modified = ' . $conn->DBTimeStamp(time()) . ' 
								WHERE userdb_id = ' . $user_id;
                    }
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    if ($_SESSION['admin_privs'] == 'yes' && $is_admin === true) {
                        $sql_edit_limitListings = $misc->make_db_safe($_POST['edit_limitListings']);
                        $sql_edit_limitFeaturedListings = $misc->make_db_safe($_POST['edit_limitFeaturedListings']);
                        $edit_userFloorNotify = intval($_POST['edit_userFloorNotify']);
                        $sql_edit_userRank = $misc->make_db_safe($_POST['edit_userRank']);
                        $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb 
								SET userdb_send_notifications_to_floor = ' . $edit_userFloorNotify . ', 
									userdb_rank = ' . $sql_edit_userRank . ', 
									userdb_featuredlistinglimit = ' . $sql_edit_limitFeaturedListings . ', 
									userdb_limit_listings = ' . $sql_edit_limitListings . ' 
								WHERE userdb_id = ' . $user_id;

                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                    }
                    // If Admin is upadting and agent set other fields
                    if ($_SESSION['admin_privs'] == 'yes' && $is_agent == true) {
                        $edit_userFloorNotify = intval($_POST['edit_userFloorNotify']);
                        $edit_first_name = $misc->make_db_safe(strip_tags($_POST['user_first_name']));
                        $edit_last_name = $misc->make_db_safe(strip_tags($_POST['user_last_name']));
                        $edit_canEditSiteConfig = $misc->make_db_safe($_POST['edit_canEditSiteConfig']);
                        $edit_canEditMemberTemplate = $misc->make_db_safe($_POST['edit_canEditMemberTemplate']);
                        $edit_canEditAgentTemplate = $misc->make_db_safe($_POST['edit_canEditAgentTemplate']);
                        $edit_canEditListingTemplate = $misc->make_db_safe($_POST['edit_canEditListingTemplate']);
                        $edit_canEditAllListings = $misc->make_db_safe($_POST['edit_canEditAllListings']);
                        $edit_canEditAllUsers = $misc->make_db_safe($_POST['edit_canEditAllUsers']);
                        $edit_can_view_logs = $misc->make_db_safe($_POST['edit_canViewLogs']);
                        $edit_can_moderate = $misc->make_db_safe($_POST['edit_canModerate']);
                        $edit_can_feature_listings = $misc->make_db_safe($_POST['edit_canFeatureListings']);
                        $edit_can_edit_pages = $misc->make_db_safe($_POST['edit_canPages']);
                        $edit_can_have_vtours = $misc->make_db_safe($_POST['edit_canVtour']);
                        $edit_can_have_files = $misc->make_db_safe($_POST['edit_canFiles']);
                        $edit_can_have_user_files = $misc->make_db_safe($_POST['edit_canUserFiles']);
                        $edit_limitListings = $misc->make_db_safe($_POST['edit_limitListings']);
                        $sql_edit_canExportListings = $misc->make_db_safe($_POST['edit_canExportListings']);
                        $sql_edit_canEditListingExpiration = $misc->make_db_safe($_POST['edit_canEditListingExpiration']);
                        $sql_edit_canEditPropertyClasses = $misc->make_db_safe($_POST['edit_canEditPropertyClasses']);
                        $sql_userdb_blog_user_type = $misc->make_db_safe($_POST['edit_BlogPrivileges']);
                        $sql_edit_limitFeaturedListings = $misc->make_db_safe($_POST['edit_limitFeaturedListings']);
                        $sql_edit_userRank = $misc->make_db_safe($_POST['edit_userRank']);
                        $sql_edit_canManageAddons = $misc->make_db_safe($_POST['edit_canManageAddons']);
                        $sql_edit_can_edit_all_leads = $misc->make_db_safe($_POST['edit_can_edit_all_leads']);
                        $sql_edit_can_edit_lead_template = $misc->make_db_safe($_POST['edit_can_edit_lead_template']);

                        $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb SET
						userdb_user_first_name = ' . $edit_first_name . ',
						userdb_user_last_name = ' . $edit_last_name . ',
						userdb_can_edit_site_config = ' . $edit_canEditSiteConfig . ',
						userdb_can_edit_member_template = ' . $edit_canEditMemberTemplate . ',
						userdb_can_edit_agent_template = ' . $edit_canEditAgentTemplate . ',
						userdb_can_edit_listing_template = ' . $edit_canEditListingTemplate . ',
						userdb_can_view_logs = ' . $edit_can_view_logs . ',
						userdb_can_moderate = ' . $edit_can_moderate . ',
						userdb_can_feature_listings = ' . $edit_can_feature_listings . ',
						userdb_can_edit_pages = ' . $edit_can_edit_pages . ',
						userdb_can_have_vtours = ' . $edit_can_have_vtours . ',
						userdb_can_have_files = ' . $edit_can_have_files . ',
						userdb_can_have_user_files = ' . $edit_can_have_user_files . ',
						userdb_limit_listings = ' . $edit_limitListings . ',
						userdb_can_edit_expiration = ' . $sql_edit_canEditListingExpiration . ',
						userdb_can_export_listings = ' . $sql_edit_canExportListings . ',
						userdb_can_edit_all_users = ' . $edit_canEditAllUsers . ',
						userdb_can_edit_all_listings = ' . $edit_canEditAllListings . ',
						userdb_can_edit_property_classes = ' . $sql_edit_canEditPropertyClasses . ',
						userdb_can_manage_addons = ' . $sql_edit_canManageAddons . ',
						userdb_rank = ' . $sql_edit_userRank . ',
						userdb_featuredlistinglimit = ' . $sql_edit_limitFeaturedListings . ',
						userdb_blog_user_type = ' . $sql_userdb_blog_user_type . ',
						userdb_can_edit_all_leads = ' . $sql_edit_can_edit_all_leads . ',
						userdb_can_edit_lead_template = ' . $sql_edit_can_edit_lead_template . ',
						userdb_send_notifications_to_floor = ' . $edit_userFloorNotify . '
						WHERE userdb_id = ' . $user_id;
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                    } else {
                        $edit_first_name = $conn->qstr(strip_tags($_POST['user_first_name']));
                        $edit_last_name = $conn->qstr(strip_tags($_POST['user_last_name']));

                        //  $edit_first_name = mysql_real_escape_string(strip_tags($_POST['user_first_name']));
                        //  $edit_last_name = mysql_real_escape_string(strip_tags($_POST['user_last_name']));
                        $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb 
								SET	userdb_user_first_name = ' . $edit_first_name . ', 
									userdb_user_last_name = ' . $edit_last_name . ' 
									WHERE userdb_id = ' . $user_id . '';
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                    }

                    $message = $this->updateUserData($user_id);
                    if ($message == 'success') {
                        //call the user change plugin function
                        include_once $config['basepath'] . '/include/hooks.inc.php';
                        $hooks = new hooks();
                        $hooks->load('after_user_change', $user_id);
                        $display .= $lang['user_editor_account_updated'] . ', ' . $_SESSION['username'];
                    } // end if
                    else {
                        $display .= $lang['alert_site_admin'];
                    } // end else

                    $misc->log_action($lang['log_updated_user'] . ': ' . $user_id);
                    header('Content-type: application/json');
                    return json_encode(['error' => '0', 'status_msg' => $display, 'user_id' => $user_id]);
                } // end if $pass_the_form == "Yes"
            } // end else
        }
        header('Content-type: application/json');
        return json_encode(['error' => '1', 'error_msg' => $lang['listing_editor_permission_denied']]);
    }

    public function create_user()
    {
        global $conn, $config, $misc, $lang;

        $security = false;
        if ((($config['demo_mode'] != 1) && ($_SESSION['edit_all_users'] == 'yes')) || ($_SESSION['admin_privs'] == 'yes')) {
            $security = true;
        }
        $display = '';
        if ($security) {
            // create the user
            if ($_POST['edit_user_pass'] != $_POST['edit_user_pass2']) {
                $display .= '<p>' . $lang['user_creation_password_identical'] . '</p>';
            } elseif ($_POST['edit_user_pass'] == '') {
                $display .= '<p>' . $lang['user_creation_password_blank'] . '</p>';
            } elseif ($_POST['edit_user_name'] == '') {
                $display .= '<p>' . $lang['user_editor_need_username'] . '</p>';
            } elseif ($_POST['user_email'] == '') {
                $display .= '<p>' . $lang['user_editor_need_email_address'] . '</p>';
            } elseif ($_POST['user_first_name'] == '') {
                $display .= '<p>' . $lang['user_editor_need_first_name'] . '</p>';
            } elseif ($_POST['user_last_name'] == '') {
                $display .= '<p>' . $lang['user_editor_need_last_name'] . '</p>';
            } else {
                $sql_user_name = $misc->make_db_safe(strip_tags($_POST['edit_user_name']));
                $sql_user_email = $misc->make_db_safe(strip_tags($_POST['user_email']));
                $pass_the_form = 'Yes';

                // first, make sure the user name isn't in use
                $sql = 'SELECT userdb_user_name from ' . $config['table_prefix'] . 'userdb WHERE userdb_user_name = ' . $sql_user_name;
                $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $num = $recordSet->RecordCount();
                // second, make sure the user eamail isn't in use
                $sql2 = 'SELECT userdb_emailaddress from ' . $config['table_prefix'] . 'userdb WHERE userdb_emailaddress = ' . $sql_user_email;
                $recordSet2 = $conn->Execute($sql2);
                if (!$recordSet2) {
                    $misc->log_error($sql2);
                }
                $num2 = $recordSet2->RecordCount();
                if ($num >= 1) {
                    $pass_the_form = 'No';
                    $display .= $lang['user_creation_username_taken'];
                } // end if
                elseif ($num2 >= 1) {
                    $pass_the_form = 'No';
                    $display .= $lang['email_address_already_registered'];
                } // end if
                if ($pass_the_form == 'Yes') {
                    // what the program should do if the form is valid
                    // generate a random number to enter in as the password (initially)
                    // we'll need to know the actual account id to help with retrieving the user
                    // We will be putting in a random number that we know the value of, we can easily
                    // retrieve the account id in a few moments
                    $random_number = $misc->make_db_safe(rand(1, 10000));
                    $sql_user_name = $misc->make_db_safe(strip_tags($_POST['edit_user_name']));
                    $hash = password_hash($_POST['edit_user_pass'], PASSWORD_DEFAULT);
                    $sql_hash = $misc->make_db_safe($hash);
                    $sql_user_email = $misc->make_db_safe(strip_tags($_POST['user_email']));
                    $sql_user_first_name = $misc->make_db_safe(strip_tags($_POST['user_first_name']));
                    $sql_user_last_name = $misc->make_db_safe(strip_tags($_POST['user_last_name']));
                    $sql_edit_active = $misc->make_db_safe($_POST['edit_active']);
                    $sql_edit_isAgent = $misc->make_db_safe($_POST['edit_isAgent']);
                    $sql_edit_isAdmin = $misc->make_db_safe($_POST['edit_isAdmin']);
                    if ($_POST['edit_isAdmin'] == 'yes') {
                        $sql_edit_limitFeaturedListings = $misc->make_db_safe('-1');
                        if (isset($_POST['edit_userRank'])) {
                            $sql_edit_userRank = intval($_POST['edit_userRank']);
                        } else {
                            $sql_edit_userRank = 1;
                        }
                        $sql_limitListings = $misc->make_db_safe('-1');
                        $sql_edit_canEditSiteConfig = $misc->make_db_safe('no');
                        $sql_edit_canEditMemberTemplate = $misc->make_db_safe('no');
                        $sql_edit_canEditAgentTemplate = $misc->make_db_safe('no');
                        $sql_edit_canEditListingTemplate = $misc->make_db_safe('no');
                        $sql_edit_canFeatureListings = $misc->make_db_safe('no');
                        $sql_edit_canViewLogs = $misc->make_db_safe('no');
                        $sql_edit_canModerate = $misc->make_db_safe('no');
                        $sql_edit_canPages = $misc->make_db_safe('no');
                        $sql_edit_canVtour = $misc->make_db_safe('no');
                        $sql_edit_canFiles = $misc->make_db_safe('no');
                        $sql_edit_canUserFiles = $misc->make_db_safe('no');
                        $sql_edit_canExportListings = $misc->make_db_safe('no');
                        $sql_edit_canEditListingExpiration = $misc->make_db_safe('no');
                        $sql_edit_canEditAllListings = $misc->make_db_safe('no');
                        $sql_edit_canEditAllUsers = $misc->make_db_safe('no');
                        $sql_edit_canEditPropertyClasses = $misc->make_db_safe('no');
                        $sql_edit_canManageAddons = $misc->make_db_safe('no');
                        $sql_edit_blogUserType = $misc->make_db_safe('4');
                    } elseif ($_POST['edit_isAgent'] == 'yes') {
                        if ($config['agent_default_edit_site_config'] == 1) {
                            $sql_edit_canEditSiteConfig = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canEditSiteConfig = $misc->make_db_safe('no');
                        }
                        if ($config['agent_default_edit_member_template'] == 1) {
                            $sql_edit_canEditMemberTemplate = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canEditMemberTemplate = $misc->make_db_safe('no');
                        }
                        if ($config['agent_default_edit_agent_template'] == 1) {
                            $sql_edit_canEditAgentTemplate = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canEditAgentTemplate = $misc->make_db_safe('no');
                        }

                        if ($config['agent_default_edit_listing_template'] == 1) {
                            $sql_edit_canEditListingTemplate = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canEditListingTemplate = $misc->make_db_safe('no');
                        }
                        if ($config['agent_default_feature'] == 1) {
                            $sql_edit_canFeatureListings = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canFeatureListings = $misc->make_db_safe('no');
                        }
                        if ($config['agent_default_logview'] == 1) {
                            $sql_edit_canViewLogs = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canViewLogs = $misc->make_db_safe('no');
                        }
                        if ($config['agent_default_moderate'] == 1) {
                            $sql_edit_canModerate = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canModerate = $misc->make_db_safe('no');
                        }
                        if ($config['agent_default_editpages'] == 1) {
                            $sql_edit_canPages = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canPages = $misc->make_db_safe('no');
                        }
                        if ($config['agent_default_havevtours'] == 1) {
                            $sql_edit_canVtour = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canVtour = $misc->make_db_safe('no');
                        }
                        if ($config['agent_default_havefiles'] == 1) {
                            $sql_edit_canFiles = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canFiles = $misc->make_db_safe('no');
                        }
                        if ($config['agent_default_haveuserfiles'] == 1) {
                            $sql_edit_canUserFiles = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canUserFiles = $misc->make_db_safe('no');
                        }
                        $sql_limitListings = $misc->make_db_safe($config['agent_default_num_listings']);
                        $sql_edit_limitFeaturedListings = $misc->make_db_safe($config['agent_default_num_featuredlistings']);

                        if ($config['agent_default_can_export_listings'] == 1) {
                            $sql_edit_canExportListings = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canExportListings = $misc->make_db_safe('no');
                        }
                        if ($config['agent_default_canchangeexpirations'] == 1) {
                            $sql_edit_canEditListingExpiration = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canEditListingExpiration = $misc->make_db_safe('no');
                        }
                        if ($config['agent_default_edit_all_listings'] == 1) {
                            $sql_edit_canEditAllListings = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canEditAllListings = $misc->make_db_safe('no');
                        }
                        if ($config['agent_default_edit_all_users'] == 1) {
                            $sql_edit_canEditAllUsers = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canEditAllUsers = $misc->make_db_safe('no');
                        }

                        if ($config['agent_default_edit_property_classes'] == 1) {
                            $sql_edit_canEditPropertyClasses = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canEditPropertyClasses = $misc->make_db_safe('no');
                        }

                        if ($config['agent_default_canManageAddons'] == 1) {
                            $sql_edit_canManageAddons = $misc->make_db_safe('yes');
                        } else {
                            $sql_edit_canManageAddons = $misc->make_db_safe('no');
                        }
                        if (isset($_POST['edit_userRank'])) {
                            $sql_edit_userRank = intval($_POST['edit_userRank']);
                        } else {
                            $sql_edit_userRank = 1;
                        }
                        $sql_edit_blogUserType = $misc->make_db_safe($config['agent_default_blogUserType']);
                    } else {
                        $sql_edit_canEditSiteConfig = $misc->make_db_safe('no');
                        $sql_edit_canEditMemberTemplate = $misc->make_db_safe('no');
                        $sql_edit_canEditAgentTemplate = $misc->make_db_safe('no');
                        $sql_edit_canEditListingTemplate = $misc->make_db_safe('no');
                        $sql_edit_canFeatureListings = $misc->make_db_safe('no');
                        $sql_edit_canViewLogs = $misc->make_db_safe('no');
                        $sql_edit_canModerate = $misc->make_db_safe('no');
                        $sql_edit_canPages = $misc->make_db_safe('no');
                        $sql_edit_canVtour = $misc->make_db_safe('no');
                        $sql_edit_canFiles = $misc->make_db_safe('no');
                        $sql_edit_canUserFiles = $misc->make_db_safe('no');
                        $sql_edit_canExportListings = $misc->make_db_safe('no');
                        $sql_edit_canEditListingExpiration = $misc->make_db_safe('no');
                        $sql_edit_canEditAllListings = $misc->make_db_safe('no');
                        $sql_edit_canEditAllUsers = $misc->make_db_safe('no');
                        $sql_limitListings = 0;
                        $sql_edit_limitFeaturedListings = 0;
                        $sql_edit_userRank = 0;
                        $sql_edit_canEditPropertyClasses = $misc->make_db_safe('no');
                        $sql_edit_canManageAddons = $misc->make_db_safe('no');
                        $sql_edit_blogUserType = $misc->make_db_safe('1');
                    }
                    // create the account with the random number as the password
                    $sql = 'INSERT INTO ' . $config['table_prefix'] . 'userdb (userdb_user_name, userdb_user_password,userdb_user_first_name ,userdb_user_last_name, userdb_emailAddress,
						userdb_creation_date,userdb_last_modified,userdb_active,userdb_is_agent,userdb_is_admin,userdb_can_edit_member_template,
						userdb_can_edit_agent_template,userdb_can_edit_listing_template,userdb_can_feature_listings,userdb_can_view_logs,
						userdb_can_moderate,userdb_can_edit_pages,userdb_can_have_vtours,userdb_can_have_files,userdb_can_have_user_files,userdb_limit_listings,userdb_comments,userdb_hit_count,
						userdb_can_edit_expiration,userdb_can_export_listings,userdb_can_edit_all_users,userdb_can_edit_all_listings,userdb_can_edit_site_config,userdb_can_edit_property_classes,userdb_can_manage_addons,userdb_rank,userdb_featuredlistinglimit,userdb_email_verified,userdb_blog_user_type) VALUES
						(' . $sql_user_name . ',' . $sql_hash . ',' . $sql_user_first_name . ',' . $sql_user_last_name . ',' . $sql_user_email . ',' . $conn->DBDate(time()) . ',' . $conn->DBTimeStamp(time()) . ','
                        . $sql_edit_active . ',' . $sql_edit_isAgent . ',' . $sql_edit_isAdmin . ',' . $sql_edit_canEditMemberTemplate . ',' . $sql_edit_canEditAgentTemplate . ',' . $sql_edit_canEditListingTemplate . ',' . $sql_edit_canFeatureListings . ',' . $sql_edit_canViewLogs . ',' . $sql_edit_canModerate . ','
                        . $sql_edit_canPages . ',' . $sql_edit_canVtour . ',' . $sql_edit_canFiles . ',' . $sql_edit_canUserFiles . ',' . $sql_limitListings . ',\'\',0,' . $sql_edit_canEditListingExpiration . ',' . $sql_edit_canExportListings
                        . ',' . $sql_edit_canEditAllUsers . ',' . $sql_edit_canEditAllListings . ',' . $sql_edit_canEditSiteConfig . ',' . $sql_edit_canEditPropertyClasses . ',' . $sql_edit_canManageAddons . ',' . $sql_edit_userRank . ',' . $sql_edit_limitFeaturedListings . ',\'yes\',' . $sql_edit_blogUserType . ')';
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $new_user_id = $conn->Insert_ID(); // this is the new user's ID number

                    //call the new user plugin function
                    include_once $config['basepath'] . '/include/hooks.inc.php';
                    $hooks = new hooks();
                    $hooks->load('after_user_signup', $new_user_id);

                    return $new_user_id;
                }
            }
        }
        return $display;
    }

    public function delete_user($user_id)
    {
        global $lang, $api;

        // Set Variable to hold errors
        $errors = '';
        // Verify ID is Numeric
        if (!is_numeric($user_id)) {
            return $lang['user_manager_invalid_user_id'];
        }

        //deletes userdb_id #4 and any associated listings and media
        $result = $api->load_local_api('user__delete', [
            'user_id' => $user_id,
        ]);

        if ($result['error']) {
            return 'Deletion of ' . $user_id . ' Failed';
        } else {
            //success
            return 1;
        }
    }

    public function updateUserData($user_id)
    {
        // UPDATES THE USER INFORMATION
        global $conn, $misc, $lang, $config;

        $sql_user_id = intval($user_id);
        $sql = 'DELETE FROM ' . $config['table_prefix'] . 'userdbelements 
				WHERE userdb_id = ' . $sql_user_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $sql3 = 'SELECT userdb_is_agent, userdb_is_admin 
				FROM ' . $config['table_prefix'] . 'userdb 
				WHERE userdb_id = ' . $sql_user_id;
        $recordSet3 = $conn->Execute($sql3);
        if (!$recordSet3) {
            $misc->log_error($sql3);
        }
        if ($recordSet3->fields('userdb_is_agent') == 'yes' || $recordSet3->fields('userdb_is_admin') == 'yes') {
            $db_to_use = 'agent';
        } else {
            $db_to_use = 'member';
        }
        foreach ($_POST as $ElementIndexValue => $ElementContents) {
            $sql_field_name = $misc->make_db_safe($ElementIndexValue);
            $sql2 = 'SELECT ' . $db_to_use . 'formelements_field_type 
					FROM ' . $config['table_prefix'] . $db_to_use . 'formelements 
					WHERE ' . $db_to_use . "formelements_field_name=" . $sql_field_name;
            $recordSet2 = $conn->Execute($sql2);
            if (!$recordSet2) {
                $misc->log_error($sql2);
            }
            if ($recordSet2->RecordCount() == 1) {
                $field_type = $recordSet2->fields($db_to_use . 'formelements_field_type');
                // first, ignore all the stuff that's been taken care of above
                if ($ElementIndexValue == 'token' || $ElementIndexValue == 'user_user_name' || $ElementIndexValue == 'edit_user_pass' || $ElementIndexValue == 'edit_user_pass2' || $ElementIndexValue == 'user_email' || $ElementIndexValue == 'PHPSESSID' || $ElementIndexValue == 'edit' || $ElementIndexValue == 'edit_isAdmin' || $ElementIndexValue == 'edit_active' || $ElementIndexValue == 'edit_isAgent' || $ElementIndexValue == 'edit_limitListings' || $ElementIndexValue == 'edit_canEditSiteConfig' || $ElementIndexValue == 'edit_canMemberTemplate' || $ElementIndexValue == 'edit_canAgentTemplate' || $ElementIndexValue == 'edit_canListingTemplate' || $ElementIndexValue == 'edit_canViewLogs' || $ElementIndexValue == 'edit_canModerate' || $ElementIndexValue == 'edit_canFeatureListings' || $ElementIndexValue == 'edit_canPages' || $ElementIndexValue == 'edit_canVtour' || $ElementIndexValue == 'edit_canFiles' || $ElementIndexValue == 'edit_canUserFiles') {
                    // do nothing
                }
                // this is currently set up to handle two feature lists
                // it could easily handle more...
                // just write handlers for 'em
                elseif (is_array($ElementContents)) {
                    // deal with checkboxes & multiple selects elements
                    $feature_insert = $misc->make_db_safe(implode('||', $ElementContents));
                    $sql = 'INSERT INTO ' . $config['table_prefix'] . 'userdbelements (userdbelements_field_name, userdbelements_field_value, userdb_id) 
							VALUES (' . $sql_field_name . ', ' . $feature_insert . ', ' . $sql_user_id . ')';
                    // }
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                } // end elseif
                else {
                    // it's time to actually insert the form data into the db
                    $sql_ElementIndexValue = $misc->make_db_safe($ElementIndexValue);
                    $sql_ElementContents = $misc->make_db_safe($ElementContents);
                    // if ($_SESSION['admin_privs'] == 'yes' && $_GET['edit'] != "")
                    // {
                    // $sql_edit = $misc->make_db_safe($_GET['edit']);
                    // $sql = 'INSERT INTO ' . $config['table_prefix'] . 'userdbelements (userdbelements_field_name, userdbelements_field_value, userdb_id) VALUES ('.$sql_ElementIndexValue.', '.$sql_ElementContents.', '.$sql_edit.')';
                    // }
                    // else
                    // {
                    // $sql_user_id = $misc->make_db_safe($_SESSION['userID']);
                    if ($field_type == 'date' && $ElementContents != '') {
                        if ($config['date_format'] == 1) {
                            $format = '%m/%d/%Y';
                        } elseif ($config['date_format'] == 2) {
                            $format = '%Y/%d/%m';
                        } elseif ($config['date_format'] == 3) {
                            $format = '%d/%m/%Y';
                        }
                        $returnValue = $misc->parseDate($ElementContents, $format);
                        $sql_ElementContents = $misc->make_db_safe($returnValue);
                    }
                    $sql = 'INSERT INTO ' . $config['table_prefix'] . 'userdbelements (userdbelements_field_name, userdbelements_field_value, userdb_id) 
							VALUES (' . $sql_ElementIndexValue . ', ' . $sql_ElementContents . ', ' . $sql_user_id . ')';
                    // }
                    $recordSet = $conn->Execute($sql);
                } // end else
            }
        } // end while
        return 'success';
    } // end function updateUserData

    public function verify_email()
    {
        global $conn, $config, $misc, $lang;
        $display = '';

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        if (isset($_GET['hash']) &&  $_GET['hash'] != '' && isset($_GET['id']) &&  $_GET['id'] != '') {
            //Read Key From DB
            $id = intval($_GET['id']);
            $userID = $misc->make_db_safe($id);
            $sql = 'SELECT  userdb_verification_hash FROM ' . $config['table_prefix'] . 'userdb WHERE userdb_id = ' . $userID;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $key = $recordSet->fields('userdb_verification_hash');

            $raw = base64_decode($_GET['hash']);
            $decrypted = openssl_decrypt($raw, 'AES-128-ECB', $key);

            $params = explode(',', $decrypted);

            $userID = intval($params['0']);
            if ($id !== $userID) {
                $display .= '<p class="notice">' . $lang['verify_email_invalid_link'] . '</div>';
                return $display;
            }
            //the User API won't get the password, so we leave this a SQL lookup
            $sql = 'SELECT userdb_id, userdb_user_name, userdb_user_password, userdb_emailaddress, userdb_is_agent 
					FROM ' . $config['table_prefix'] . "userdb 
					WHERE userdb_id = '" . $userID . "'";

            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $user_id = $recordSet->fields('userdb_id');
            $user_name = $recordSet->fields('userdb_user_name');
            $user_pass = $recordSet->fields('userdb_user_password');
            $emailAddress = $recordSet->fields('userdb_emailaddress');

            if ($emailAddress == $params['1']) {
                $valid = true;
            }
            if ($recordSet->fields('userdb_is_agent') == 'yes') {
                $type = 'agent';
            } else {
                $type = 'member';
            }
            if ($config['moderate_' . $type . 's'] == 0) {
                if ($type == 'agent') {
                    if ($config['agent_default_active'] == 0) {
                        $set_active = 'no';
                    } else {
                        $set_active = 'yes';
                    }
                } else {
                    $set_active = 'yes';
                }
            } else {
                $set_active = 'no';
            }
            $sql_set_active = $misc->make_db_safe($set_active);
            if ($valid == true) {
                if ($config['email_notification_of_new_users'] == 1) {
                    // if the site admin should be notified when a new user is added
                    $remote_ip = $_SERVER['REMOTE_ADDR'];
                    $signup_timestamp = date('F j, Y, g:i:s a');
                    $this->send_user_signup_notification($user_id, $type, $remote_ip, $signup_timestamp);
                }
                $verified = $misc->make_db_safe('yes');
                $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb 
						SET userdb_active = ' . $sql_set_active . ', 
                        userdb_email_verified = ' . $verified . ',
                        userdb_verification_hash = NULL
						WHERE userdb_id = ' . $userID;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $display .= '<p class="notice">' . $lang['verify_email_thanks'] . '</p>';
                if ($config['moderate_' . $type . 's'] == 1) {
                    // if moderation is turned on...
                    $display .= '<p>' . $lang['admin_new_user_moderated'] . '</p>';
                } else {
                    //log the user in
                    $login->set_session_vars($user_name);
                    $login->verify_priv('Member');
                    $display .= '<p>' . $lang['you_may_now_view_priv'] . '</p>';
                }
            } else {
                $display .= '<p class="notice">' . $lang['verify_email_invalid_link'] . '</div>';
            }
        } else {
            $display .= '<p class="notice">' . $lang['verify_email_invalid_link'] . '</div>';
        }
        return $display;
    }

    //consolidated from send_agent_signup_email() and send_member_signup_email()
    //refactored for v3.2.11
    public function send_user_signup_email($userid, $type, $pass = '')
    {
        global $conn, $config, $misc;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        include_once $config['basepath'] . '/include/user.inc.php';
        $user = new user();

        if ($type == 'agent') {
            $page->load_page($config['admin_template_path'] . '/email/agent_signup_email.html');
            $page->replace_tag('agent_login_link', $page->magicURIGenerator('agent_login', null, true));
            $page->replace_user_field_tags($userid, '', 'agent');
            $passtag = 'agent_password';
        } else {
            $page->load_page($config['admin_template_path'] . '/email/member_signup_email.html');
            $page->replace_tag('member_login_link', $page->magicURIGenerator('member_login', null, true));
            $page->replace_user_field_tags($userid, '');
            $passtag = 'member_password';
        }

        $reg_info = $user->get_user_reg_info($userid);
        $user_email = $reg_info['emailaddress'];
        //$user_email = $user->get_user_single_item('userdb_emailaddress', $userid);

        if (($type == 'agent' && $config['moderate_agents'] == 1) || ($type == 'member' && $config['moderate_members'] == 1)) {
            $page->page = $page->cleanup_template_block('moderated', $page->page);
        } else {
            $page->page = $page->remove_template_block('moderated', $page->page);
        }
        if ($config['require_email_verification'] == 1 && $pass == '') {
            $key = substr(base64_encode(random_bytes(32)), 0, 32);
            $safe_key = $misc->make_db_safe($key);
            //Store Key in DB
            $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb 
                SET userdb_verification_hash = ' . $safe_key . '
                WHERE userdb_id = ' . $userid;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $params = $userid . ',' . $user_email;

            $encrypted = urlencode(base64_encode(openssl_encrypt($params, 'AES-128-ECB', $key)));

            $page->page = $page->cleanup_template_block('require_email_verification', $page->page);
            $verification_link = $config['baseurl'] . '/index.php?action=verify_email&hash=' . $encrypted . '&amp;id=' . $userid;
            $page->replace_tag('verification_email_link', $verification_link);
        } else {
            $page->page = $page->remove_template_block('require_email_verification', $page->page);
        }

        if ($pass == '') {
            $page->page = $page->cleanup_template_block('!password', $page->page);
            $page->page = $page->remove_template_block('password', $page->page);
        } else {
            $page->replace_tag($passtag, $pass);
            $page->page = $page->cleanup_template_block('password', $page->page);
            $page->page = $page->remove_template_block('!password', $page->page);
        }

        $page->replace_lang_template_tags();
        $subject = $page->get_template_section('subject_block');
        $page->page = $page->remove_template_block('subject', $page->page);
        $page->auto_replace_tags();
        $message = $page->return_page();

        if (isset($config['site_email']) && $config['site_email'] != '') {
            $sender_email = $config['site_email'];
        } else {
            $sender_email = $config['admin_email'];
        }
        $misc->send_email($config['admin_name'], $sender_email, $user_email, $message, $subject, true);
    }

    //consolidated from send_agent_signup_notification() and send_member_signup_notification()
    //refactored for v3.2.11
    public function send_user_signup_notification($userid, $type, $signup_ip, $signup_timestamp)
    {
        global $config, $misc;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        include_once $config['basepath'] . '/include/user.inc.php';
        $user = new user();

        if ($type == 'agent') {
            $page->load_page($config['admin_template_path'] . '/email/agent_signup_notification.html');
            $page->replace_user_field_tags($userid, '', 'agent');
        } else {
            $page->load_page($config['admin_template_path'] . '/email/member_signup_notification.html');
            $page->replace_user_field_tags($userid, '');
        }

        $page->replace_tag('user_ip', $signup_ip);
        $page->replace_tag('notification_time', $signup_timestamp);
        $page->replace_lang_template_tags();
        $subject = $page->get_template_section('subject_block');
        $page->page = $page->remove_template_block('subject', $page->page);
        $page->auto_replace_tags();
        $message = $page->return_page();
        if (isset($config['site_email']) && $config['site_email'] != '') {
            $sender_email = $config['site_email'];
        } else {
            $sender_email = $config['admin_email'];
        }
        $misc->send_email($config['admin_name'], $sender_email, $config['admin_email'], $message, $subject, true, true);
    }

    //consolidated from send_agent_verification_email() and send_member_verification_email()
    //refactored for v3.2.11
    public function send_user_verification_email($userid, $type)
    {
        global $config, $misc;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        include_once $config['basepath'] . '/include/user.inc.php';
        $user = new user();

        if ($type == 'agent') {
            $page->load_page($config['admin_template_path'] . '/email/agent_activation_email.html');
            $page->replace_tag('agent_login_link', $page->magicURIGenerator('agent_login', null, true));
            $page->replace_user_field_tags($userid, '', 'agent');
        } else {
            $page->load_page($config['admin_template_path'] . '/email/member_activation_email.html');
            $page->replace_tag('member_login_link', $page->magicURIGenerator('member_login', null, true));
            $page->replace_user_field_tags($userid, '');
        }

        $reg_info = $user->get_user_reg_info($userid);
        $user_email = $reg_info['emailaddress'];

        //$user_email = $user->get_user_single_item('userdb_emailaddress', $userid);
        $page->replace_lang_template_tags();
        $subject = $page->get_template_section('subject_block');
        $page->page = $page->remove_template_block('subject', $page->page);
        $page->auto_replace_tags();
        $message = $page->return_page();

        if (isset($config['site_email']) && $config['site_email'] != '') {
            $sender_email = $config['site_email'];
        } else {
            $sender_email = $config['admin_email'];
        }
        $misc->send_email($config['admin_name'], $sender_email, $user_email, $message, $subject, true);
    }



    public function show_edit_user()
    {
        global $api, $config, $misc, $lang, $jscript;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        include_once $config['basepath'] . '/include/forms.inc.php';
        $forms = new forms();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $page->load_page($config['admin_template_path'] . '/edit_user.html');

        $display = '';

        if (isset($_GET['user_id']) && $_GET['user_id'] == $_SESSION['userID']) {
            $security = true;
        } else {
            $security = $login->verify_priv('edit_all_users');
        }

        if ($security) {
            if (isset($_GET['user_id'])) {
                $user_id = intval($_GET['user_id']);
                $yes_no = ['no' => 'No', 'yes' => 'Yes'];
                $yes_no_10 = [0 => 'No', 1 => 'Yes'];
                /*//Blog Permissions
                 * 1 - Subscriber - A subscriber can read posts, comment on posts.
                 * 2 - Contributor - A contributor can post and manage their own post but they cannot publish the posts. An administrator must first approve the post before it can be published.
                 * 3 - Author - The Author role allows someone to publish and manage posts. They can only manage their own posts, no one else’s.
                 * 4 - Editor - An editor can publish posts. They can also manage and edit other users posts. If you are looking for someone to edit your posts, you would assign the Editor role to that person.
                 $lang['blog_perm_subscriber'] = 'Subscriber';
                 $lang['blog_perm_contributor'] = 'Contributor';
                 $lang['blog_perm_author'] = 'Author';
                 $lang['blog_perm_editor'] = 'Editor';
                 */
                $blog_type = [1 => $lang['blog_perm_subscriber'], 2 => $lang['blog_perm_contributor'], 3 => $lang['blog_perm_author'], 4 => $lang['blog_perm_editor']];

                $is_admin = $misc->get_admin_status($user_id);
                $is_agent = $misc->get_agent_status($user_id);

                if ($is_agent === true || $is_admin === true) {
                    $resource = 'agent';
                } else {
                    $resource = 'member';
                }

                //read all data for this user
                $result = $api->load_local_api('user__read', [
                    'user_id' => $user_id,
                    'resource' => $resource,
                ]);
                if ($result['error']) {
                    $display .= 'User lookup failure';
                    return $display;
                }

                $edit_username = $result['user']['userdb_user_name'];
                $edit_emailAddress = $result['user']['userdb_emailaddress'];
                // $edit_comments = $recordSet->fields('userdb_comments');
                $edit_firstname = $result['user']['userdb_user_first_name'];
                $edit_lastname = $result['user']['userdb_user_last_name'];
                $edit_active = $result['user']['userdb_active'];
                $edit_isAgent = $result['user']['userdb_is_agent'];
                $edit_isAdmin = $result['user']['userdb_is_admin'];
                $edit_limitListings = $result['user']['userdb_limit_listings'];
                $edit_limitFeaturedListings = $result['user']['userdb_featuredlistinglimit'];
                $edit_userRank = $result['user']['userdb_rank'];
                $edit_canEditAllListings = $result['user']['userdb_can_edit_all_listings'];
                $edit_canEditAllUsers = $result['user']['userdb_can_edit_all_users'];
                $edit_canEditSiteConfig = $result['user']['userdb_can_edit_site_config'];
                $edit_canEditMemberTemplate = $result['user']['userdb_can_edit_member_template'];
                $edit_canEditAgentTemplate = $result['user']['userdb_can_edit_agent_template'];
                $edit_canEditListingTemplate = $result['user']['userdb_can_edit_listing_template'];
                $edit_canExportListings = $result['user']['userdb_can_export_listings'];
                $edit_canEditListingExpiration = $result['user']['userdb_can_edit_expiration'];
                $edit_canEditPropertyClasses = $result['user']['userdb_can_edit_property_classes'];
                $edit_canModerate = $result['user']['userdb_can_moderate'];
                $edit_canViewLogs = $result['user']['userdb_can_view_logs'];
                $edit_canVtour = $result['user']['userdb_can_have_vtours'];
                $edit_canFiles = $result['user']['userdb_can_have_files'];
                $edit_canUserFiles = $result['user']['userdb_can_have_user_files'];
                $edit_canFeatureListings = $result['user']['userdb_can_feature_listings'];
                $edit_canPages = $result['user']['userdb_can_edit_pages'];
                $edit_BlogPrivileges = $result['user']['userdb_blog_user_type'];
                $last_modified = date($config['date_format_timestamp'], strtotime($result['user']['userdb_last_modified']));

                //$last_modified = $recordSet->UserTimeStamp($result['user']['userdb_last_modified'], $config["date_format_timestamp"]);
                $edit_canManageAddons = $result['user']['userdb_can_manage_addons'];
                $edit_can_edit_all_leads = $result['user']['userdb_can_edit_all_leads'];
                $edit_can_edit_lead_template = $result['user']['userdb_can_edit_lead_template'];
                $edit_userFloorNotify = $result['user']['userdb_send_notifications_to_floor'];

                //Replace Fields
                $page->page = str_replace('{user_id}', $user_id, $page->page);
                $page->page = str_replace('{user_name}', $edit_username, $page->page);
                $page->page = str_replace('{user_email}', $edit_emailAddress, $page->page);
                $page->page = str_replace('{user_first_name}', $edit_firstname, $page->page);
                $page->page = str_replace('{user_last_name}', $edit_lastname, $page->page);
                $page->page = str_replace('{last_modified}', $last_modified, $page->page);

                if ($_SESSION['admin_privs'] == 'yes' || (isset($_GET['user_id']) && $_GET['user_id'] == $_SESSION['userID'] && $config['demo_mode'] != 1)) {
                    $page->page = $page->cleanup_template_block('user_password', $page->page);
                } else {
                    $page->page =  $page->remove_template_block('user_password', $page->page);
                }

                $html = $page->get_template_section('user_status_option_block');
                $html = $page->form_options($yes_no, $edit_active, $html);
                $page->replace_template_section('user_status_option_block', $html);

                $page->replace_tag('user_isAdmin', $edit_isAdmin);
                $page->replace_tag('user_isAgent', $edit_isAgent);

                if ($is_agent  === true || $is_admin === true) {
                    $page->page = $page->cleanup_template_block('agentadmin', $page->page);
                } else {
                    $page->page = $page->remove_template_block('agentadmin', $page->page);
                }

                $page->replace_tag('user_limitListings', $edit_limitListings);
                $page->replace_tag('user_limitFeaturedListings', $edit_limitFeaturedListings);
                $page->replace_tag('user_userRank', $edit_userRank);

                //Floor Notification
                $html = $page->get_template_section('userFloorNotify_block');
                $html = $page->form_options($yes_no_10, $edit_userFloorNotify, $html);
                $page->replace_template_section('userFloorNotify_block', $html);

                if ($is_agent === true) {
                    $page->page = $page->cleanup_template_block('agent', $page->page);
                } else {
                    $page->page = $page->remove_template_block('agent', $page->page);
                }

                //Agent Permissions
                $html = $page->get_template_section('canEditAllListings_block');
                $html = $page->form_options($yes_no, $edit_canEditAllListings, $html);
                $page->replace_template_section('canEditAllListings_block', $html);

                $html = $page->get_template_section('canEditAllUsers_block');
                $html = $page->form_options($yes_no, $edit_canEditAllUsers, $html);
                $page->replace_template_section('canEditAllUsers_block', $html);

                $html = $page->get_template_section('canEditSiteConfig_block');
                $html = $page->form_options($yes_no, $edit_canEditSiteConfig, $html);
                $page->replace_template_section('canEditSiteConfig_block', $html);

                $html = $page->get_template_section('canEditMemberTemplate_block');
                $html = $page->form_options($yes_no, $edit_canEditMemberTemplate, $html);
                $page->replace_template_section('canEditMemberTemplate_block', $html);

                $html = $page->get_template_section('canEditAgentTemplate_block');
                $html = $page->form_options($yes_no, $edit_canEditAgentTemplate, $html);
                $page->replace_template_section('canEditAgentTemplate_block', $html);

                $html = $page->get_template_section('canEditListingTemplate_block');
                $html = $page->form_options($yes_no, $edit_canEditListingTemplate, $html);
                $page->replace_template_section('canEditListingTemplate_block', $html);

                $html = $page->get_template_section('canEditPropertyClasses_block');
                $html = $page->form_options($yes_no, $edit_canEditPropertyClasses, $html);
                $page->replace_template_section('canEditPropertyClasses_block', $html);

                $html = $page->get_template_section('canViewLogs_block');
                $html = $page->form_options($yes_no, $edit_canViewLogs, $html);
                $page->replace_template_section('canViewLogs_block', $html);

                $html = $page->get_template_section('canModerate_block');
                $html = $page->form_options($yes_no, $edit_canModerate, $html);
                $page->replace_template_section('canModerate_block', $html);

                $html = $page->get_template_section('canFeatureListings_block');
                $html = $page->form_options($yes_no, $edit_canFeatureListings, $html);
                $page->replace_template_section('canFeatureListings_block', $html);

                $html = $page->get_template_section('canPages_block');
                $html = $page->form_options($yes_no, $edit_canPages, $html);
                $page->replace_template_section('canPages_block', $html);

                $html = $page->get_template_section('canVtour_block');
                $html = $page->form_options($yes_no, $edit_canVtour, $html);
                $page->replace_template_section('canVtour_block', $html);

                $html = $page->get_template_section('canFiles_block');
                $html = $page->form_options($yes_no, $edit_canFiles, $html);
                $page->replace_template_section('canFiles_block', $html);

                $html = $page->get_template_section('canUserFiles_block');
                $html = $page->form_options($yes_no, $edit_canUserFiles, $html);
                $page->replace_template_section('canUserFiles_block', $html);

                $html = $page->get_template_section('canEditListingExpiration_block');
                $html = $page->form_options($yes_no, $edit_canEditListingExpiration, $html);
                $page->replace_template_section('canEditListingExpiration_block', $html);

                $html = $page->get_template_section('BlogPrivileges_block');
                $html = $page->form_options($blog_type, $edit_BlogPrivileges, $html);
                $page->replace_template_section('BlogPrivileges_block', $html);

                $html = $page->get_template_section('canManageAddons_block');
                $html = $page->form_options($yes_no, $edit_canManageAddons, $html);
                $page->replace_template_section('canManageAddons_block', $html);

                $html = $page->get_template_section('canExportListings_block');
                $html = $page->form_options($yes_no, $edit_canExportListings, $html);
                $page->replace_template_section('canExportListings_block', $html);

                $html = $page->get_template_section('can_edit_all_leads_block');
                $html = $page->form_options($yes_no, $edit_can_edit_all_leads, $html);
                $page->replace_template_section('can_edit_all_leads_block', $html);

                $html = $page->get_template_section('can_edit_lead_template_block');
                $html = $page->form_options($yes_no, $edit_can_edit_lead_template, $html);
                $page->replace_template_section('can_edit_lead_template_block', $html);

                //Handle Custom User Fields
                $field_list = $api->load_local_api('fields__metadata', ['resource' => $resource]);
                $misc_hold = '';
                foreach ($field_list['fields'] as $field) {
                    $field_name =  $field['field_name'];
                    if (array_key_exists($field_name, $result['user'])) {
                        $field_value = $result['user'][$field_name];
                    } else {
                        $field_value = '';
                    }

                    $field_type = $field['field_type'];
                    $field_caption = $field['field_caption'];
                    $default_text = $field['default_text'];
                    $field_elements =  $field['field_elements'];
                    $required = $field['required'];
                    $tool_tip = $field['tool_tip'];
                    $location = ''; //Holder for adding locations to user fields.
                    // pass the data to the function
                    $field = $forms->renderFormElement($field_type, $field_name, $field_value, $field_caption, $default_text, $required, $field_elements, '', $tool_tip);

                    switch ($location) {
                        default:
                            $misc_hold .= $field;
                            break;
                    }
                }

                $page->page = str_replace('{misc_hold}', $misc_hold, $page->page);
            }
            $page->replace_tags(['curley_open', 'curley_close', 'baseurl']);
            $page->replace_tag('application_status_text', '');
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        } else {
            $display .= $lang['listing_editor_permission_denied'];
            return $display;
        }
    }

    public function show_user_manager()
    {
        global $conn, $config, $misc, $lang, $jscript;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_all_users');
        $display = '';
        //Load the Core Template
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        $page->load_page($config['admin_template_path'] . '/user_manager.html');


        if ($security) {
            if (isset($_POST['filter']) || isset($_POST['lookup_field']) || isset($_POST['lookup_value'])) {
                if (!isset($_POST['token']) || !$misc->validate_csrf_token($_POST['token'])) {
                    $display .= '<div class="text-danger text-center">' . $lang['invalid_csrf_token'] . '</div>';
                    unset($_POST);
                    return $display;
                }
            }
            $options = array();
            $options[""] = $lang['user_manager_show_all'];
            $options["agents"] = $lang['user_manager_agents'];
            $options["admins"] = $lang['user_manager_admins'];
            $options["members"] = $lang['user_manager_members'];

            $default_option = "";
            if (isset($_POST['filter'])) {
                $default_option = $_POST['filter'];
            }


            $html = $page->get_template_section('user_manager_show_filter_block');
            $html = $page->form_options($options, $default_option, $html);
            $page->replace_template_section('user_manager_show_filter_block', $html);


            $filter_sql = '';
            if (isset($_POST['filter'])) {
                $filter = $_POST['filter'];
                $_GET['cur_page'] = 0;
                $_SESSION['um_filter'] = $_POST['filter'];
            } else {
                if (isset($_SESSION['um_filter'])) {
                    $filter = $_SESSION['um_filter'];
                } else {
                    $filter = null;
                }
            }
            if ($filter == 'agents') {
                $filter_sql = " WHERE userdb_is_agent = 'yes'";
            } elseif ($filter == 'members') {
                $filter_sql = " WHERE userdb_is_agent = 'no' AND userdb_is_admin = 'no'";
            } elseif ($filter == 'admins') {
                $filter_sql = " WHERE userdb_is_admin = 'yes'";
            }

            $security2 = $login->verify_priv('Admin');
            if ($security2) {
            } else {
                if ($filter === 'Show All' || $filter === '') {
                    $filter_sql = " WHERE userdb_is_admin = 'no'";
                }
            }
            if (isset($_POST['lookup_field']) && isset($_POST['lookup_value']) && $_POST['lookup_value'] != '') {
                $lookup_value = $misc->make_db_safe($_POST['lookup_value']);
                $lookup_field = $conn->addQ($_POST['lookup_field']);
                $sql = 'SELECT * FROM ' . $config['table_prefix'] . 'userdb WHERE ' . $lookup_field . ' = ' . $lookup_value;
            } else {
                $sql = 'SELECT * FROM ' . $config['table_prefix'] . "userdb $filter_sql ORDER BY userdb_id ";
            }
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $num_rows = $recordSet->RecordCount();

            $max_pages = ceil($num_rows /  $config['listings_per_page']);

            if (!isset($_GET['cur_page'])  ||  $_GET['cur_page'] == 0) {
                $_GET['cur_page'] = 0;
            } else {
                if (intval($_GET['cur_page']) + 1 > $max_pages || intval($_GET['cur_page'])  < 0) {
                    header('HTTP/1.0 403 Forbidden');
                    $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
                    return $display;
                }
            }

            $next_prev = $misc->next_prev($num_rows, intval($_GET['cur_page']), '', '', true);
            $page->replace_tag('next_prev', $next_prev);

            // build the string to select a certain number of users per page
            $limit_str = intval($_GET['cur_page']) * $config['listings_per_page'];
            $recordSet = $conn->SelectLimit($sql, $config['listings_per_page'], $limit_str);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $count = 0;
            //Get Template Setion
            $user_template = $page->get_template_section('user_dataset');
            $user_section = '';
            // $display .= "<br /><br />";
            while (!$recordSet->EOF) {
                // alternate the colors
                if ($count == 0) {
                    $count = $count + 1;
                } else {
                    $count = 0;
                }
                $user_section .= $user_template;
                // strip slashes so input appears correctly
                $user_id = $recordSet->fields('userdb_id');
                $edit_user_name = $recordSet->fields('userdb_user_name');
                $edit_user_first_name = $recordSet->fields('userdb_user_first_name');
                $edit_user_last_name = $recordSet->fields('userdb_user_last_name');
                $edit_emailAddress = $recordSet->fields('userdb_emailaddress');
                $edit_active = $recordSet->fields('userdb_active');
                $edit_isAgent = $recordSet->fields('userdb_is_agent');
                $edit_isAdmin = $recordSet->fields('userdb_is_admin');
                $edit_canEditSiteConfig = $recordSet->fields('userdb_can_edit_site_config');
                $edit_canEditMemberTemplate = $recordSet->fields('userdb_can_edit_member_template');
                $edit_canEditAgentTemplate = $recordSet->fields('userdb_can_edit_agent_template');
                $edit_canEditListingTemplate = $recordSet->fields('userdb_can_edit_listing_template');
                $edit_canFeatureListings = $recordSet->fields('userdb_can_feature_listings');
                $edit_canViewLogs = $recordSet->fields('userdb_can_view_logs');
                $edit_canModerate = $recordSet->fields('userdb_can_moderate');
                $edit_can_have_vtours = $recordSet->fields('userdb_can_have_vtours');
                $edit_can_edit_expiration = $recordSet->fields('userdb_can_edit_expiration');
                $edit_can_export_listings = $recordSet->fields('userdb_can_export_listings');
                $edit_canEditAllListings = $recordSet->fields('userdb_can_edit_all_listings');
                $edit_canEditAllUsers = $recordSet->fields('userdb_can_edit_all_users');
                $edit_canEditPropertyClasses = $recordSet->fields('userdb_can_edit_property_classes');

                $edit_last_modified = date($config['date_format_timestamp'], strtotime($recordSet->fields('userdb_last_modified')));
                $edit_creation = date($config['date_format_timestamp'], strtotime($recordSet->fields('userdb_creation_date')));

                // Determine user type
                if ($edit_isAgent == 'yes') {
                    $user_type = $lang['user_manager_agent'];
                } elseif ($edit_isAdmin == 'yes') {
                    $user_type = $lang['user_manager_admin'];
                } else {
                    $user_type = $lang['user_manager_member'];
                }

                if ($edit_active == 'yes') {
                    $user_make_active = 'make_inactive';
                //  $edit_active = '<a href="#" onclick="make_inactive(\''.$user_id.'\', \''.$edit_user_first_name.'\', \''.$edit_user_last_name.'\', \''.$thumb_file_name.'\');return false;" class="edit_user_'.$edit_active.'">'.$lang['yes'].'</a>';
                } elseif ($edit_active == 'no') {
                    $user_make_active = 'make_active';
                    //$edit_active = '<a href="#" onclick="make_active(\''.$user_id.'\', \''.$edit_user_first_name.'\', \''.$edit_user_last_name.'\', \''.$thumb_file_name.'\');return false;" class="edit_user_'.$edit_active.'">'.$lang['no'].'</a>';
                }

                $user_section = $page->replace_user_field_tags($user_id, $user_section, 'user');
                $user_section = $page->parse_template_section($user_section, 'user_type', $user_type);
                $user_section = $page->parse_template_section($user_section, 'is_agent', $edit_isAgent);

                $user_section = $page->parse_template_section($user_section, 'user_last_modified', $edit_last_modified);
                $user_section = $page->parse_template_section($user_section, 'user_creation_date', $edit_creation);

                $user_section = $page->parse_template_section($user_section, 'user_status', $edit_active);
                $user_section = $page->parse_template_section($user_section, 'user_make_active', $user_make_active);

                $recordSet->MoveNext();
            } // end while
            $page->replace_template_section('user_dataset', $user_section);
        }

        $page->replace_tag('application_status_text', '');
        $page->replace_lang_template_tags(true);
        $page->replace_permission_tags();
        $page->auto_replace_tags('', true);
        return $page->return_page();
    }

    public function change_user_status($user_id, $active_status)
    {
        global $conn, $config, $misc, $lang, $api;

        $display = '';
        // no touching the admin
        if ($user_id == 1) {
            header('Content-type: application/json');
            return json_encode(['error' => '2', 'error_msg' => $lang['admin_status_warning']]);
            die;
        }
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_all_users');

        if ($security) {
            //based on the active status, set some vars
            if ($active_status == 'yes') {
                $active_state = true;
                $lang_var = $lang['alert_user_active'];
            } else {
                $active_state = false;
                $lang_var = $lang['alert_user_inactive'];
            }

            //is this an agent?
            $agent_check = $misc->get_agent_status($user_id);

            //what was the previous active status?
            $prev_status = $misc->get_active_status($user_id);

            //is this user the admin?
            $is_admin = $misc->get_admin_status($user_id);

            // set user status to $active_state
            $result = $api->load_local_api('user__update', [
                'user_id' => $user_id,
                'user_details' => [
                    'active' => $active_state,
                ],
            ]);
            if ($result['error']) {
                die($result['error_msg']);
            }

            if ($agent_check) {
                //get a list of this agent's listing ID#s
                $result = $api->load_local_api(
                    'listing__search',
                    [
                        'parameters' => [
                            'user_ID' => $user_id,
                            'listingsdb_active' => 'any',
                        ],
                    ]
                );

                //only do this if there are listings
                if ($result['listing_count'] > 0) {
                    //set each listing in the array to $active_state T/F
                    foreach ($result['listings'] as $listing_id) {
                        //get pclass of the agent's listings
                        $api_class = $api->load_local_api(
                            'listing__read',
                            [
                                'listing_id' => $listing_id,
                                'fields' => ['listingsdb_pclass_id'],
                            ]
                        );
                        if ($api_class['error']) {
                            die($api_class['error_msg']);
                        }

                        //set listing status based on $active_state
                        $ret_result = $api->load_local_api(
                            'listing__update',
                            [
                                'class_id' => $api_class['listing']['listingsdb_pclass_id'],
                                'listing_id' => $listing_id,
                                'listing_details' => [
                                    'active' => $active_state,
                                ],
                                'listing_agents' => [
                                    $user_id,
                                ],
                                'or_int_disable_log' => false,
                                'or_date_format' => '%Y-%m-%d',
                            ]
                        );
                        if ($ret_result['error']) {
                            die($ret_result['error_msg']);
                        }
                    }
                } // end if ($result['listing_count'] > 0)
            } // end if ($agent_check)

            //if this account was inactive and is now active
            if ($prev_status === false && $active_state === true) {
                //if agent moderation is active, and this user is an agent, or member moderation is active and this user is not an Agent.
                //these conditions seem kind of broad, and should probably be reconsidered.
                if (($config['moderate_agents'] == 1 && $agent_check === true) || ($config['moderate_members'] == 1 && $agent_check === false)) {
                    // if the site admin should be notified when a new user is added
                    if ($agent_check === true || $is_admin === true) {
                        $this->send_user_verification_email($user_id, 'agent');
                    } else {
                        $this->send_user_verification_email($user_id, 'member');
                    }
                }
            }
            header('Content-type: application/json');
            return json_encode(['error' => '0', 'status_msg' => $user_id . ' ' . $lang_var]);
        } // end if($security)
        else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['user_manager_permission_denied']]);
        }
    }


    /**
     * Add Lead lookup user
     *
     * @param  text $is_agent (yes/no), text $term (user serach term)
     * @return array $results  (id, name, email)
     * consolidated from prior ajax_addlead_lookup_member() and ajax_addlead_lookup_agent() functions
     */
    public function ajax_addlead_lookup_user($is_agent = 'no', $term = '')
    {
        global $config, $conn, $misc;

        $sql_term = $conn->addQ($term);
        $sql = 'SELECT userdb_user_last_name,userdb_user_first_name, userdb_emailaddress,userdb_id
				FROM ' . $config['table_prefix'] . 'userdb
				WHERE userdb_is_admin = \'no\' 
				AND userdb_is_agent = \'' . $is_agent . '\' 
				AND ( userdb_user_last_name LIKE \'' . $sql_term . '%\' 
					OR userdb_user_first_name LIKE \'' . $sql_term . '%\' 
					OR userdb_emailaddress LIKE \'' . $sql_term . '%\'
				)';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        // get main listings data
        $results = [];
        $x = 0;
        while (!$recordSet->EOF) {
            $results[$x]['value'] = $recordSet->fields('userdb_id');
            $results[$x]['label'] = $recordSet->fields('userdb_user_last_name') . ', ' . $recordSet->fields('userdb_user_first_name') . ' (' . $recordSet->fields('userdb_emailaddress') . ')';
            $x++;
            $recordSet->MoveNext();
        } // end while
        return $results;
    }

    public function generatePassword($length = 8)
    {
        $password = '';

        // define possible characters
        $possible = '0123456789bcdfghjkmnpqrstvwxyzaeiuo$!@%AEIOUBCDEFGHJKLMNPQRSTVWXYZ';

        // set up a counter
        $i = 0;

        // add random characters to $password until $length is reached
        while ($i < $length) {
            // pick a random character from the possible ones
            $char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);

            // we don't want this character if it's already in the password
            if (!strstr($password, $char)) {
                $password .= $char;
                $i++;
            }
        }
        // done!
        return $password;
    }
}
