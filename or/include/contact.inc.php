<?php

/**
 * contact
 * This class contains all functions related to contacting people agents and friends about listings.
 *
 * @author Ryan Bonham
 */
class contact
{
    private function getContactAgentID($listing_id)
    {
        global $conn, $config, $misc;
        include_once $config['basepath'] . '/include/listing.inc.php';
        $listing_pages = new listing_pages();
        $sql_listing_id = intval($listing_id);
        $agent_id = $listing_pages->get_listing_agent_value('userdb_id', $sql_listing_id);
        //Check to see if agent's notifications should be redirected to the floor agent.
        $sql = 'SELECT userdb_send_notifications_to_floor FROM ' . $config['table_prefix'] . 'userdb WHERE userdb_id = ' . $agent_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $userdb_send_notifications_to_floor = $recordSet->fields('userdb_send_notifications_to_floor');
        if ($userdb_send_notifications_to_floor == 1) {
            //Get Floor Agent ID
            $sql = 'SELECT controlpanel_floor_agent, controlpanel_floor_agent_last 
                    FROM ' . $config['table_prefix_no_lang'] . 'controlpanel';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $controlpanel_floor_agent = $recordSet->fields('controlpanel_floor_agent');
            $controlpanel_floor_agent_last = $recordSet->fields('controlpanel_floor_agent_last');
            $floor_agents = explode(',', $controlpanel_floor_agent);
            if (count($floor_agents) > 1) {
                $update_floor = false;
                //Determine Last Floor Agent
                if ($controlpanel_floor_agent_last > 1) {
                    $first_agent_id = $floor_agents[0];
                    $use_next = false;
                    $found_agent = false;
                    foreach ($floor_agents as $key => $floor_id) {
                        if (!$use_next) {
                            if ($floor_id == $controlpanel_floor_agent_last) {
                                $use_next = true;
                            }
                        } else {
                            $new_agent_id = $floor_id;
                            if ($new_agent_id > 0) {
                                $found_agent = true;
                                $update_floor = true;
                                $agent_id = $new_agent_id;
                            }
                        }
                    }
                    if (!$found_agent) {
                        //No Last Agent set so use the first in list
                        $new_agent_id = $floor_agents[0];
                        if ($new_agent_id > 0) {
                            $update_floor = true;
                            $agent_id = $new_agent_id;
                        }
                    }
                } else {
                    //No Last Agent set so use the first in list
                    $new_agent_id = $floor_agents[0];
                    if ($new_agent_id > 0) {
                        $update_floor = true;
                        $agent_id = $new_agent_id;
                    }
                }
                if ($update_floor) {
                    $sql = 'UPDATE ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_floor_agent_last = ' . $agent_id;
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                }
            } else {
                $new_agent_id = $floor_agents[0];
                if ($new_agent_id > 0) {
                    $agent_id = $new_agent_id;
                }
            }
        }
        return $agent_id;
    }

    public function ContactAgentForm($listing_id = 0, $agent_id = 0)
    {
        global $conn, $config, $misc, $jscript, $lang;
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        include_once $config['basepath'] . '/include/captcha.inc.php';
        $captcha = new captcha();
        include_once $config['basepath'] . '/include/lead_functions.inc.php';
        $lead_functions = new lead_functions();
        $sql_listing_id = intval($listing_id);
        $sql_agent_id = intval($agent_id);
        $display = '';


        $extra_url = '';
        if ($sql_listing_id != 0) {
            $extra_url .= '&amp;listing_id=' . $sql_listing_id;
        }
        if (isset($_GET['popup']) && $_GET['popup'] == 'yes') {
            $extra_url .= '&amp;popup=yes';
        }
        if ($sql_agent_id != 0) {
            $extra_url .= '&amp;agent_id=' . $sql_agent_id;
        }

        $is_member = $login->loginCheck('Member');
        if ($is_member === true) {
            //User is logged in, so lets load the contact form
            $page->load_page($config['template_path'] . '/' . $config['contact_template']);
            if (isset($_POST['formaction']) && $_POST['formaction'] == 'create_new_lead') {
                //Check CSRF token
                if (!isset($_POST['token']) || !$misc->validate_csrf_token($_POST['token'])) {
                    $correct_token = false;
                } else {
                    $correct_token = true;
                }
                //Check Captcha
                $correct_captcha = $captcha->validate();
                if (!$correct_token || !$correct_captcha) {
                    if (!$correct_captcha) {
                        $page->page = $page->cleanup_template_block('user_captcha_failed', $page->page);
                    }
                    if (!$correct_token) {
                        $page->page = $page->cleanup_template_block('invalid_csrf_token', $page->page);
                    }
                } else {
                    //Form Submittion is valid, so lets process the form
                    if ($listing_id > 0) {
                        $sql_agent_id = $this->getContactAgentID($listing_id);
                    }
                    $sql_member_id = intval($_SESSION['userID']);

                    $sql = 'INSERT INTO ' . $config['table_prefix'] . "feedbackdb (feedbackdb_notes, userdb_id, listingdb_id, feedbackdb_creation_date, feedbackdb_last_modified, feedbackdb_status, feedbackdb_priority,feedbackdb_member_userdb_id )
                            VALUES ('', " . $sql_agent_id . ", " . $sql_listing_id . ", " . $conn->DBTimeStamp(time()) . ',' . $conn->DBTimeStamp(time()) . ", 1, 'Normal',$sql_member_id) ";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $new_feedback_id = $conn->Insert_ID();
                    $message = $lead_functions->updateFeedbackData($new_feedback_id, $agent_id, $sql_listing_id);
                    if ($message == 'success') {
                        //get the Agent's full name & email
                        $sql = 'SELECT userdb_user_first_name, userdb_user_last_name, userdb_emailaddress
                                    FROM ' . $config['table_prefix'] . "userdb
                                    WHERE userdb_id = $sql_agent_id";
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                        $user_name = $recordSet->fields('userdb_user_first_name') . ' ' . $recordSet->fields('userdb_user_last_name');
                        $listing_emailAddress = $recordSet->fields('userdb_emailaddress');
                        // Report that Feedback has been sent to the AGENT
                        return '<div id="feedback_sent_message">' . $lang['your_feedback_has_been_sent'] . ' ' . $user_name . '</div>';
                        if ($sql_listing_id > 0) {
                            $_GET['listingID'] = $sql_listing_id;
                            include_once $config['basepath'] . '/include/listing.inc.php';
                            $listing_pages = new listing_pages();
                            $output .= $listing_pages->listing_view();
                        }
                        $misc->log_action("Created feedback $new_feedback_id for $user_name");
                        //notifyNewFeedback($new_feedback_id);
                        $lead_functions->send_agent_feedback_notice($new_feedback_id);
                        $lead_functions->send_user_feedback_notice($new_feedback_id);
                        include_once $config['basepath'] . '/include/hooks.inc.php';
                        $hooks = new hooks();
                        $hooks->load('after_new_lead', $new_feedback_id);
                    } else {
                        return '<p>There\'s been a problem -- please contact the site administrator</p>';
                    } // end else
                }
            }
            $page->page = $page->remove_template_block('user_captcha_failed', $page->page);
            $page->page = $page->remove_template_block('invalid_csrf_token', $page->page);
            $page->page = $page->remove_template_block('misc_signup_error', $page->page);
            $page->page = $page->remove_template_block('user_email_activation', $page->page);
            $page->page = $page->remove_template_block('user_moderation', $page->page);

            $page->replace_permission_tags();
            $page->replace_urls();
            if ($listing_id > 0) {
                $page->replace_listing_field_tags($listing_id);
                $page->replace_tag('listing_id', $listing_id);
                $page->page = $page->remove_template_block('agent_contact', $page->page);
                $page->page = $page->cleanup_template_block('listing_contact', $page->page);
            } elseif ($sql_agent_id > 0) {
                $page->replace_user_field_tags($sql_agent_id, '', 'listing_agent');
                $page->replace_tag('agent_id', $sql_agent_id);
                $page->page = $page->cleanup_template_block('agent_contact', $page->page);
                $page->page = $page->remove_template_block('listing_contact', $page->page);
            }
            $page->replace_tag('extra_url', $extra_url);
            $lead_fields = $lead_functions->get_feedback_formelements();
            $page->replace_tag('feedback_formelements', $lead_fields);


            $page->replace_user_field_tags($_SESSION['userID']);
            $page->replace_tag('captcha_display', $captcha->show());
            $page->auto_replace_tags();
            $page->replace_lang_template_tags();

            $display = $page->return_page();
        } else {
            $display = $is_member;
        }
        return $display;
    }

    /**
     * Contact::ContactFriendForm()
     *
     * @param integer $listing_id This should hold the listing ID that you aer emailing your friend about.
     * @return
     */
    public function ContactFriendForm($listing_id)
    {
        global $conn, $config, $lang, $jscript, $misc;

        include_once $config['basepath'] . '/include/core.inc.php';
        include_once $config['basepath'] . '/include/captcha.inc.php';
        include_once $config['basepath'] . '/include/user.inc.php';
        include_once $config['basepath'] . '/include/login.inc.php';
        include_once $config['basepath'] . '/include/user_manager.inc.php';
        $user_manager = new user_managment();
        $login = new login();

        $captcha = new captcha();
        $user = new user();

        $page = new page_user();
        $page->load_page($config['template_path'] . '/email_a_friend.html');
        $page->replace_lang_template_tags();
        $page->replace_listing_field_tags($listing_id);

        $display = '';
        $error = [];
        $login_passed = false;
        if (isset($_POST['user_name']) && isset($_POST['user_pass'])) {
            $login_passed = $login->loginCheck('Member');
            if ($login_passed !== true) {
                $page->page = $page->cleanup_template_block('login_failed', $page->page);
            } else {
                $page->page = $page->remove_template_block('login_failed', $page->page);
            }
        }
        $is_member = $login->verify_priv('Member');

        $singup_passed = true;
        if ($is_member) {
            if (isset($_POST['message'])) {
                // Make sure there is a message
                if (!isset($_POST['token']) || !$misc->validate_csrf_token($_POST['token'])) {
                    $correct_token = false;
                } else {
                    $correct_token = true;
                }
                if (!$correct_token) {
                    $page->page = $page->cleanup_template_block('invalid_csrf_token', $page->page);
                } else {
                    $correct_code = $captcha->validate();
                    if (!$correct_code) {
                        $error[] = 'email_verification_code_not_valid';
                    }

                    if (trim($_POST['friend_email']) == '') {
                        $error[] = 'email_no_email_address';
                    } elseif ($misc->validate_email($_POST['friend_email']) !== true) {
                        $error[] = 'email_invalid_email_address';
                    }
                    if (trim($_POST['subject']) == '') {
                        $error[] = 'email_no_subject';
                    }
                    if (trim($_POST['message']) == '') {
                        $error[] = 'email_no_message';
                    }

                    if (count($error) == 0) {
                        // Send Mail
                        //Get Member Info
                        $member_name = $user->get_user_single_item('userdb_user_first_name', $_SESSION['userID']) . ' ' . $user->get_user_single_item('userdb_user_last_name', $_SESSION['userID']);
                        $member_email = $user->get_user_single_item('userdb_emailaddress', $_SESSION['userID']);
                        $sent = $misc->send_email($member_name, $member_email, $_POST['friend_email'], $_POST['message'], $_POST['subject']);
                        if ($sent === true) {
                            $display .= $lang['email_listing_sent'] . ' ' . $_POST['friend_email'];
                        } else {
                            $display .= $sent;
                        }
                    } else {
                        if (count($error) != 0) {
                            foreach ($error as $err) {
                                $display .= '<div class="error_text">' . $lang[$err] . '</div>';
                            }
                        }
                    }
                }
            }

            $name = '';
            $email = '';
            $subject = '';
            $friend_email = '';
            $message = '';
            if (isset($_POST['message'])) {
                $message = stripslashes($_POST['message']);
                $subject = stripslashes($_POST['subject']);
                // $friend_name = $_POST['friend_name'];
                $friend_email = stripslashes($_POST['friend_email']);
                $page->page = $page->remove_template_block('default_subject', $page->page);
                $page->page = $page->remove_template_block('default_message', $page->page);
                $page->page = $page->remove_template_block('invalid_csrf_token', $page->page);
            } else {
                $subject = $page->get_template_section('default_subject_block');
                $page->page = $page->remove_template_block('default_subject', $page->page);
                $message = $page->get_template_section('default_message_block');
                $page->page = $page->remove_template_block('default_message', $page->page);
                $page->page = $page->remove_template_block('invalid_csrf_token', $page->page);
            }

            $page->replace_tag('captcha_display', $captcha->show());

            $page->replace_tag('message', htmlentities($message, ENT_COMPAT, $config['charset']));
            $page->replace_tag('name', htmlentities($name, ENT_COMPAT, $config['charset']));
            $page->replace_tag('email', htmlentities($email, ENT_COMPAT, $config['charset']));
            $page->replace_tag('friend_email', htmlentities($friend_email, ENT_COMPAT, $config['charset']));
            $page->replace_tag('subject', htmlentities($subject, ENT_COMPAT, $config['charset']));
        } else {
            if (isset($_POST['edit_user_name'])) {
                if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
                    $correct_token = false;
                } else {
                    $correct_token = true;
                }
                if (!$correct_token) {
                    $page->page = $page->cleanup_template_block('invalid_csrf_token', $page->page);
                    $page->page = $page->remove_template_block('user_captcha_failed', $page->page);
                    $page->page = $page->remove_template_block('misc_signup_error', $page->page);
                    $page->page = $page->remove_template_block('user_email_activation', $page->page);
                    $page->page = $page->remove_template_block('user_moderation', $page->page);
                    $page->page = $page->remove_template_block('invalid_csrf_token', $page->page);
                } else {
                    $page->page = $page->remove_template_block('invalid_csrf_token', $page->page);


                    if ($config['use_signup_image_verification'] == 1) {
                        $correct_code = $captcha->validate();
                    } else {
                        $correct_code = true;
                    }

                    if (!$correct_code) {
                        //Failed
                        //echo 'Incorrect Code';
                        $page->page = $page->cleanup_template_block('user_captcha_failed', $page->page);
                        $page->page = $page->remove_template_block('misc_signup_error', $page->page);
                        $page->page = $page->remove_template_block('user_email_activation', $page->page);
                        $page->page = $page->remove_template_block('user_moderation', $page->page);
                    } else {
                        //echo 'Correct Code';
                        $page->page = $page->remove_template_block('user_captcha_failed', $page->page);
                        $user_signup_status = $user_manager->member_creation();
                        //print_r($user_signup_status);
                        if (is_array($user_signup_status)) {
                            //User was signed up.. CHeck to see if they were moderated.
                            $user_id = $user_signup_status['user_id'];
                            $user_active = $user_signup_status['active'];
                            if ($user_active == 'mod') {
                                $page->page = $page->cleanup_template_block('user_moderation', $page->page);
                            } else {
                                $page->page = $page->remove_template_block('user_moderation', $page->page);
                            }
                            if ($user_active == 'email') {
                                $page->page = $page->cleanup_template_block('user_email_activation', $page->page);
                            } else {
                                $page->page = $page->remove_template_block('user_email_activation', $page->page);
                            }
                            if ($user_active == 'yes') {
                                $login_passed = $login->loginCheck('Member');
                            }
                            $page->page = $page->remove_template_block('misc_signup_error', $page->page);
                        } else {
                            $page->replace_tag('misc_signup_error_msg', $user_signup_status);
                            $page->page = $page->cleanup_template_block('misc_signup_error', $page->page);
                            $page->page = $page->remove_template_block('user_email_activation', $page->page);
                            $page->page = $page->remove_template_block('user_moderation', $page->page);

                            $page->replace_tag('user_email', htmlentities($_POST['user_email'], ENT_NOQUOTES, $config['charset']));
                            $page->replace_tag('user_last_name', htmlentities($_POST['user_last_name'], ENT_NOQUOTES, $config['charset']));
                            $page->replace_tag('user_first_name', htmlentities($_POST['user_first_name'], ENT_NOQUOTES, $config['charset']));
                            $page->replace_tag('edit_user_name', htmlentities($_POST['edit_user_name'], ENT_NOQUOTES, $config['charset']));
                            $singup_passed = false;
                        }
                    }
                }
            } else {
                $page->page = $page->remove_template_block('user_captcha_failed', $page->page);
                $page->page = $page->remove_template_block('misc_signup_error', $page->page);
                $page->page = $page->remove_template_block('user_email_activation', $page->page);
                $page->page = $page->remove_template_block('user_moderation', $page->page);
                $page->page = $page->remove_template_block('invalid_csrf_token', $page->page);
            }

            $jscript .= '<script type="text/javascript">

									$(document).ready(function() {

										$("#signup_section_link").click(function(){
											$("#login_section").hide();
											$("#signup_section").show();
											return false;
										});
										$("#login_section_link").click(function(){
											$("#signup_section").hide();
											$("#login_section").show();
											return false;
										});
									});

			    				</script>';

            $jscript .= "\n";

            $page->page = $page->remove_template_block('login_failed', $page->page);

            if ($config['use_signup_image_verification'] == 1) {
                include_once $config['basepath'] . '/include/captcha.inc.php';
                $captcha = new captcha();
                $page->replace_tag('captcha_display', $captcha->show());
            } else {
                $page->replace_tag('captcha_display', '');
            }
        }
        $page->replace_lang_template_tags();
        $subject = $page->get_template_section('default_subject_block');
        $page->page = $page->remove_template_block('default_subject', $page->page);
        $message = $page->get_template_section('default_message_block');
        $page->page = $page->remove_template_block('default_message', $page->page);
        $page->replace_tag('message', htmlentities($message, ENT_COMPAT, $config['charset']));
        $page->replace_tag('subject', htmlentities($subject, ENT_COMPAT, $config['charset']));
        $page->replace_permission_tags();
        $page->replace_meta_template_tags();
        $page->auto_replace_tags();
        $page->replace_tags(['load_js', 'load_ORjs', 'load_js_last']);
        $page->replace_lang_template_tags();
        $page->replace_css_template_tags();

        $display .= $page->return_page();
        return $display;
    }
}
