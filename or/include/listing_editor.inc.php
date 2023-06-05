<?php


class listing_editor
{
    public $debug = false;

    public function ajax_addlead_lookup_listing($term)
    {
        global $config, $conn, $misc;

        $sql_term = $conn->addQ($term);
        $sql = 'SELECT listingsdb_id,listingsdb_title
							FROM ' . $config['table_prefix'] . 'listingsdb
							WHERE listingsdb_active = \'yes\' 
							AND ( listingsdb_title LIKE \'%' . $sql_term . '%\' OR listingsdb_id = \'' . $sql_term . '\')';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        // get main listings data
        $results = [];
        $x = 0;
        while (!$recordSet->EOF) {
            $results[$x]['value'] = $recordSet->fields('listingsdb_id');
            $results[$x]['label'] = $recordSet->fields('listingsdb_title') . ' (' . $recordSet->fields('listingsdb_id') . ')';
            $x++;
            $recordSet->MoveNext();
        } // end while
        return $results;
    }

    public function notify_new_listing($listingID)
    {
        global $conn, $lang, $config, $misc;

        $display = '';
        include_once $config['basepath'] . '/include/search.inc.php';
        $search_page = new search_page();
        $notify_count = 0;
        $sql = 'SELECT userdb_id, usersavedsearches_title, usersavedsearches_query_string, usersavedsearches_notify FROM ' . $config['table_prefix'] . "usersavedsearches WHERE usersavedsearches_notify = 'yes'";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        while (!$recordSet->EOF) {
            $query_string = $recordSet->fields('usersavedsearches_query_string');
            $user_id = $recordSet->fields('userdb_id');
            $search_title = $recordSet->fields('usersavedsearches_title');
            // Break Quesry String up into $_GET variables.
            unset($_GET);
            $query_string = urldecode($query_string);
            $criteria = explode('&', $query_string);
            foreach ($criteria as $crit) {
                if ($crit != '') {
                    $pieces = explode('=', $crit);
                    $pos = strpos($pieces[0], '[]');
                    if ($pos !== false) {
                        $name = substr($pieces[0], 0, -2);
                        $_GET[$name][] = $pieces[1];
                    } else {
                        $_GET[$pieces[0]] = $pieces[1];
                    }
                }
            }
            if (!isset($_GET)) {
                $_GET[] = '';
            }
            $matched_listing_ids = $search_page->search_results(true);
            if (in_array($listingID, $matched_listing_ids)) {
                // Listing Matches Search
                $sql = 'SELECT userdb_user_name, userdb_emailaddress FROM ' . $config['table_prefix'] . 'userdb WHERE userdb_id = ' . $user_id;
                $recordSet2 = $conn->Execute($sql);
                if ($recordSet2 === false) {
                    $misc->log_error($sql);
                }
                $email = $recordSet2->fields('userdb_emailaddress');
                $user_name = $recordSet2->fields('userdb_user_name');
                $message = $lang['automated_email'] . "\r\n\r\n\r\n" . date('F j, Y, g:i:s a') . "\r\n\r\n" . $lang['new_listing_notify_long'] . "'" . $search_title . "'.\r\n\r\n" . $lang['click_on_link_to_view_listing'] . "\r\n\r\n$config[baseurl]/index.php?action=listingview&listingID=" . $listingID . "\r\n\r\n\r\n" . $lang['click_to_view_saved_searches'] . "\r\n\r\n$config[baseurl]/index.php?action=view_saved_searches\r\n\r\n\r\n" . $lang['automated_email'] . "\r\n";
                // Send Mail
                if (isset($config['site_email']) && $config['site_email'] != '') {
                    $sender_email = $config['site_email'];
                } else {
                    $sender_email = $config['admin_email'];
                }
                $subject = $lang['new_listing_notify'] . $search_title;
                $sent = $misc->send_email($config['admin_name'], $sender_email, $email, $message, $subject);
                $notify_count++;
            }
            $recordSet->MoveNext();
            if ($notify_count > 0) {
                $display .= $lang['new_listing_email_sent'] . $notify_count . $lang['new_listing_email_users'] . '<br />';
            }
        } // while
        return $display;
    }

    public function ajax_make_inactive_listing($listing_id)
    {
        global $api, $lang, $config;
        $listing_id = intval($listing_id);
        if ($config['moderate_listings'] == 0 || ($_SESSION['admin_privs'] == 'yes' || $_SESSION['moderator'] == 'yes')) {
            return $api->load_local_api('listing__update', ['listing_id' => $listing_id, 'listing_details' => ['active' => false]]);
        } else {
            return ['error' => true, 'error_msg' => $lang['listing_editor_permission_denied']];
        }
    }

    public function ajax_make_active_listing($listing_id)
    {
        global $api, $lang, $config;
        $listing_id = intval($listing_id);
        if ($config['moderate_listings'] == 0 || ($_SESSION['admin_privs'] == 'yes' || $_SESSION['moderator'] == 'yes')) {
            return $api->load_local_api('listing__update', ['listing_id' => $listing_id, 'listing_details' => ['active' => true]]);
        } else {
            return ['error' => true, 'error_msg' => $lang['listing_editor_permission_denied']];
        }
    }

    public function ajax_make_unfeatured_listing($listing_id)
    {
        global $api, $lang;
        $listing_id = intval($listing_id);
        if ($_SESSION['featureListings'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
            return $api->load_local_api('listing__update', ['listing_id' => $listing_id, 'listing_details' => ['featured' => false]]);
        } else {
            return ['error' => true, 'error_msg' => $lang['listing_editor_permission_denied']];
        }
    }

    public function ajax_make_featured_listing($listing_id)
    {
        global $api, $lang;
        $listing_id = intval($listing_id);
        if ($_SESSION['featureListings'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
            return $api->load_local_api('listing__update', ['listing_id' => $listing_id, 'listing_details' => ['featured' => true]]);
        } else {
            return ['error' => true, 'error_msg' => $lang['listing_editor_permission_denied']];
        }
    }

    public function ajax_display_add_listing()
    {
        global $conn, $config, $misc;

        $display = '';
        $status_text = '';
        //Load the Core Template
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        $page->load_page($config['admin_template_path'] . '/add_listing.html');

        // get list of all property clases
        $sql = 'SELECT class_name, class_id 
				FROM ' . $config['table_prefix'] . 'class 
				ORDER BY class_rank';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $classes = [];
        while (!$recordSet->EOF) {
            $classes[$recordSet->fields('class_id')] = $recordSet->fields('class_name');
            $recordSet->MoveNext();
        }

        $html = $page->get_template_section('pclass_block');
        $html = $page->form_options($classes, '', $html);
        $page->replace_template_section('pclass_block', $html);

        $sql = 'SELECT userdb_id, userdb_user_first_name, userdb_user_last_name 
				FROM ' . $config['table_prefix'] . "userdb 
				WHERE userdb_is_agent = 'yes' or userdb_is_admin = 'yes' 
				ORDER BY userdb_user_last_name,userdb_user_first_name";
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $all_agents = [];
        while (!$recordSet->EOF) {
            // strip slashes so input appears correctly
            $agent_ID = $recordSet->fields('userdb_id');
            $agent_first_name = $recordSet->fields('userdb_user_first_name');
            $agent_last_name = $recordSet->fields('userdb_user_last_name');
            $all_agents[$agent_ID] = $agent_last_name . ', ' . $agent_first_name;
            $recordSet->MoveNext();
        }
        $listing_agent_id = intval($_SESSION['userID']);
        $html = $page->get_template_section('listing_agent_option_block');
        $html = $page->form_options($all_agents, $listing_agent_id, $html);
        $page->replace_template_section('listing_agent_option_block', $html);

        $page->replace_tag('application_status_text', $status_text);
        $page->replace_lang_template_tags(true);
        $page->replace_permission_tags();
        $page->auto_replace_tags('', true);
        return $page->return_page();
    }

    public function ajax_add_listing()
    {
        global $lang, $api;

        if (isset($_POST['pclass']) && isset($_POST['title'])) {
            if (isset($_POST['or_owner'])) {
                $new_listing_owner = $_POST['or_owner'];
            } else {
                $new_listing_owner = $_SESSION['userID'];
            }
            $result = $api->load_local_api('listing__create', ['class_id' => $_POST['pclass'][0], 'listing_details' => ['title' => $_POST['title'], 'featured' => false, 'active' => false, 'notes' => ''], 'listing_agents' => [$new_listing_owner], 'listing_fields' => [], 'listing_media' => []]);
            if ($result['error'] == true) {
                header('Content-type: application/json');
                return json_encode(['error' => 1, 'error_msg' => $result['error_msg']]);
            } else {
                $new_listing_id = $result['listing_id'];
                header('Content-type: application/json');
                return json_encode(['error' => 0, 'listing_id' => $new_listing_id]);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['listing_editor_permission_denied']]);
        }
    }

    public function ajax_update_listing_data($listing_id)
    {
        global $conn, $lang, $config, $listingID, $jscript;

        $listing_id = intval($listing_id);
        $display = '';
        include_once $config['basepath'] . '/include/listing.inc.php';
        $listing_pages = new listing_pages();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        //Get Listing owner
        $listing_agent_id = $listing_pages->get_listing_agent_value('userdb_id', $listing_id);
        //Make sure we can Edit this lisitng
        $has_permission = true;
        if ($_SESSION['userID'] != $listing_agent_id) {
            $security = $login->verify_priv('edit_all_listings');
            if ($security !== true) {
                $has_permission = false;
            }
        }
        if ($has_permission) {
            if ($_SESSION['admin_privs'] == 'yes' || $_SESSION['edit_all_listings'] == 'yes') {
                $display .= $this->update_listing(false);
            } else {
                $display .= $this->update_listing(true);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['listing_editor_permission_denied']]);
        }
        return $display;
    }

    public function ajax_delete_listing($listing_id)
    {
        global $conn, $lang, $config, $listingID, $jscript, $api;

        $listing_id = intval($listing_id);
        $display = '';
        include_once $config['basepath'] . '/include/listing.inc.php';
        $listing_pages = new listing_pages();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        //Get Listing owner
        $listing_agent_id = $listing_pages->get_listing_agent_value('userdb_id', $listing_id);
        //Make sure we can Edit this lisitng
        $has_permission = true;
        if ($_SESSION['userID'] != $listing_agent_id) {
            $security = $login->verify_priv('edit_all_listings');
            if ($security !== true) {
                $has_permission = false;
            }
        }
        if ($has_permission) {
            $result = $api->load_local_api('listing__delete', ['listing_id' => $listing_id]);
            header('Content-type: application/json');
            return json_encode(['error' => '0', 'listing_id' => $listing_id]);
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['listing_editor_permission_denied']]);
        }
        return $display;
    }

    public function display_listing_editor($listing_id)
    {
        global $config, $conn, $lang, $listingID, $jscript, $api;

        $status_text = '';
        $listing_id = intval($listing_id);
        include_once $config['basepath'] . '/include/listing.inc.php';
        $listing_pages = new listing_pages();
        //Get Listing owner
        $listing_agent_id = $listing_pages->get_listing_agent_value('userdb_id', $listing_id);
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        //Make sure we can Edit this lisitng
        $has_permission = true;
        if ($_SESSION['userID'] != $listing_agent_id) {
            $security = $login->verify_priv('edit_all_listings');
            if ($security !== true) {
                $has_permission = false;
            }
        }
        if ($has_permission) {
            global $misc;
            include_once $config['basepath'] . '/include/forms.inc.php';
            $forms = new forms();
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/edit_listing.html');
            //Set Global Listing ID
            $listingID = $listing_id;

            $yes_no['no'] = $lang['no'];
            $yes_no['yes'] = $lang['yes'];
            $api_result = $api->load_local_api('listing__read', ['listing_id' => $listing_id, 'fields' => []]);
            if (!$api_result['error']) {
                //echo '<pre>'.print_r($api_result,TRUE).'</pre>';
                $edit_title = $api_result['listing']['listingsdb_title'];
                $edit_seotitle = $api_result['listing']['listing_seotitle'];
                $edit_notes = $api_result['listing']['listingsdb_notes'];
                $edit_active = $api_result['listing']['listingsdb_active'];
                $edit_featured = $api_result['listing']['listingsdb_featured'];
                $edit_mlsexport = $api_result['listing']['listingsdb_mlsexport'];
                $edit_pclass = $api_result['listing']['listingsdb_pclass_id'];
                $hit_count = $api_result['listing']['listingsdb_hit_count'];
                //$email = $recordSet->fields('userdb_emailaddress');
                $last_modified = date('D M j G:i:s T Y', strtotime($api_result['listing']['listingsdb_last_modified']));
                $expiration = date($config['date_format_timestamp'], strtotime($api_result['listing']['listingsdb_expiration']));

                $listing_url = $page->magicURIGenerator('listing', $listing_id, true);
                //Start Template Replacement
                $page->page = str_replace('{template_url}', $config['admin_template_url'], $page->page);
                $page->page = str_replace('{listing_hit_count}', $hit_count, $page->page);
                $page->page = str_replace('{listing_url}', $listing_url, $page->page);
                $page->page = str_replace('{listing_last_modified}', $last_modified, $page->page);
                $page->page = str_replace('{listing_id}', $listing_id, $page->page);
                $page->page = str_replace('{listing_agent_id}', $listing_agent_id, $page->page);
                $page->page = str_replace('{listing_title}', htmlentities($edit_title, ENT_COMPAT, $config['charset']), $page->page);
                $page->page = str_replace('{listing_seotitle}', htmlentities($edit_seotitle, ENT_COMPAT, $config['charset']), $page->page);
                $page->page = str_replace('{listing_note}', htmlentities($edit_notes, ENT_COMPAT, $config['charset']), $page->page);
                $page->page = str_replace('{baseurl}', $config['baseurl'], $page->page);
                $page->page  = $page->replace_listing_field_tags($listing_id, $page->page);

                $creationdate = $api_result['listing']['listingsdb_creation_date'];
                $formatted_creationdate = date($config['date_format_timestamp'], strtotime($creationdate));
                $page->page = str_replace('{listing_creation_date}', $formatted_creationdate, $page->page);
                //Build Property Class Drop Down
                $pclass_result = $api->load_local_api('pclass__metadata', []);
                if ($pclass_result['error']) {
                    $misc->log_error($pclass_result['error_msg']);
                }

                $all_classes = [];
                foreach ($pclass_result['metadata'] as $class_id => $classarray) {
                    $all_classes[$class_id] = $classarray['name'];
                }
                $html = $page->get_template_section('pclass_block');
                $html = $page->form_options($all_classes, $edit_pclass, $html);
                $page->replace_template_section('pclass_block', $html);
                //Featured Listings
                if ($_SESSION['featureListings'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                    $page->page = $page->cleanup_template_block('featured_listing', $page->page);
                    $html = $page->get_template_section('featured_listing_option_block');
                    $html = $page->form_options($yes_no, $edit_featured, $html);
                    $page->replace_template_section('featured_listing_option_block', $html);
                } else {
                    $page->page = $page->remove_template_block('featured_listing', $page->page);
                }
                //Show Listing Satus Dropdown
                if ($config['moderate_listings'] == 1) {
                    if ($_SESSION['admin_privs'] == 'yes' || $_SESSION['moderator'] == 'yes') {
                        $page->page = $page->cleanup_template_block('listing_status', $page->page);
                        $html = $page->get_template_section('listing_status_option_block');
                        $html = $page->form_options($yes_no, $edit_active, $html);
                        $page->replace_template_section('listing_status_option_block', $html);
                    } else {
                        $page->page = $page->remove_template_block('listing_status', $page->page);
                    }
                } else {
                    $page->page = $page->cleanup_template_block('listing_status', $page->page);
                    $html = $page->get_template_section('listing_status_option_block');
                    $html = $page->form_options($yes_no, $edit_active, $html);
                    $page->replace_template_section('listing_status_option_block', $html);
                }
                if (($_SESSION['admin_privs'] == 'yes' || $_SESSION['edit_expiration'] == 'yes') && $config['use_expiration'] == '1') {
                    $page->page = str_replace('{listing_expiration}', $expiration, $page->page);
                    $page->replace_tag('config_date_format_long', $config['date_format_long']);
                    $page->replace_tag('config_date_format', $config['date_format']);
                    $page->page = $page->cleanup_template_block('listing_expiration', $page->page);
                } else {
                    $page->page = $page->remove_template_block('listing_expiration', $page->page);
                }
                if ($config['export_listings'] == 1 && $_SESSION['export_listings'] == 'yes') {
                    $page->page = $page->cleanup_template_block('listing_export', $page->page);
                    $html = $page->get_template_section('listing_export_option_block');
                    $html = $page->form_options($yes_no, $edit_mlsexport, $html);
                    $page->replace_template_section('listing_export_option_block', $html);
                    $page->page = $page->remove_template_block('!listing_export', $page->page);
                } else {
                    $page->page = $page->remove_template_block('listing_export', $page->page);
                    $page->page = $page->cleanup_template_block('!listing_export', $page->page);
                }
                if ($_SESSION['admin_privs'] == 'yes' || $_SESSION['edit_all_listings'] == 'yes') {
                    //Get List of ALl Agents
                    $sql = 'SELECT userdb_id, userdb_user_first_name, userdb_user_last_name 
							FROM ' . $config['table_prefix'] . "userdb 
							WHERE userdb_is_agent = 'yes' or userdb_is_admin = 'yes' 
							ORDER BY userdb_user_last_name,userdb_user_first_name";
                    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $all_agents = [];
                    while (!$recordSet->EOF) {
                        // strip slashes so input appears correctly
                        $agent_ID = $recordSet->fields('userdb_id');
                        $agent_first_name = $recordSet->fields('userdb_user_first_name');
                        $agent_last_name = $recordSet->fields('userdb_user_last_name');
                        $all_agents[$agent_ID] = $agent_last_name . ', ' . $agent_first_name;
                        $recordSet->MoveNext();
                    }
                    $page->page = $page->cleanup_template_block('listing_agent', $page->page);
                    $html = $page->get_template_section('listing_agent_option_block');
                    $html = $page->form_options($all_agents, $listing_agent_id, $html);
                    $page->replace_template_section('listing_agent_option_block', $html);
                    $page->page = $page->remove_template_block('!listing_agent', $page->page);
                } else {
                    $page->page = $page->remove_template_block('listing_agent', $page->page);
                    $page->page = $page->cleanup_template_block('!listing_agent', $page->page);
                }
                if ($config['show_notes_field'] == 1) {
                    $page->page = $page->cleanup_template_block('listing_notes', $page->page);
                    $page->page = $page->remove_template_block('!listing_notes', $page->page);
                } else {
                    $page->page = $page->remove_template_block('listing_notes', $page->page);
                    $page->page = $page->cleanup_template_block('!listing_notes', $page->page);
                }
                //Load Template Area From Config
                $sections = explode(',', $config['template_listing_sections']);
                $template_holder = ['misc_hold' => ''];
                foreach ($sections as $section) {
                    if (strpos($page->page, $section) !== false) {
                        $template_holder[$section] = '';
                    }
                }
                $pclass_list = [$edit_pclass];
                $field_result = $api->load_local_api('fields__metadata', ['resource' => 'listing', 'class' => $pclass_list]);
                if ($field_result['error']) {
                    //If an error occurs die and show the error msg;
                    $misc->log_error($pclass_result['error_msg']);
                }
                //No error so get the fields that were returned..
                $field_list = $field_result['fields'];
                foreach ($field_list as $field) {
                    $field_name = $field['field_name'];
                    $field_value = $api_result['listing'][$field_name];
                    //echo $field_name.' : '.$field_value.'<br />';
                    $field_type = $field['field_type'];
                    $field_caption = $field['field_caption'];
                    $default_text = $field['default_text'];
                    $field_elements = $field['field_elements'];
                    $required = $field['required'];
                    $field_length = $field['field_length'];
                    $tool_tip = $field['tool_tip'];
                    $location = $field['location'];
                    // pass the data to the function
                    $field = $forms->renderFormElement($field_type, $field_name, $field_value, $field_caption, $default_text, $required, $field_elements, $field_length, $tool_tip);

                    if (array_key_exists($location, $template_holder) == true) {
                        $template_holder[$location] .= $field;
                    } else {
                        $template_holder['misc_hold'] .= $field;
                    }
                }
                foreach ($template_holder as $tag => $value) {
                    $page->page = str_replace('{' . $tag . '}', $value, $page->page);
                }

                $page->page = $page->remove_template_block('listing_not_found', $page->page);
                $page->page = $page->cleanup_template_block('listing_found', $page->page);
            } else {
                $page->page = $page->remove_template_block('listing_found', $page->page);
                $page->page = $page->cleanup_template_block('listing_not_found', $page->page);
            }
            //Finish Loading Template
            $page->replace_tags(['curley_open', 'curley_close', 'baseurl']);
            $page->replace_tag('application_status_text', $status_text);
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        } else {
            return 'Permission Denied';
        }
    }

    public function edit_listings($only_my_listings = true)
    {
        global $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        if ($only_my_listings == false) {
            $security = $login->verify_priv('edit_all_listings');
        } else {
            $security = $login->verify_priv('Agent');
        }
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        $page->load_page($config['admin_template_path'] . '/listing_editor.html');

        $display = '';
        if ($security === true) {
            global $conn, $listingID, $jscript, $api, $misc;

            if (isset($_POST['filter']) || isset($_POST['lookup_field']) || isset($_POST['lookup_value'])) {
                if (!isset($_POST['token']) || !$misc->validate_csrf_token($_POST['token'])) {
                    $display .= '<div class="text-danger text-center">' . $lang['invalid_csrf_token'] . '</div>';
                    unset($_POST);
                    return $display;
                }
            }

            include_once $config['basepath'] . '/include/forms.inc.php';
            $forms = new forms();
            $status_text = '';

            if (!isset($_GET['edit'])) {
                $_GET['edit'] = '';
            }
            if (isset($_POST['lookup_field']) && isset($_POST['lookup_value'])) {
                $_SESSION['edit_listing_qeb_lookup_field'] = $_POST['lookup_field'];
                $_SESSION['edit_listing_qeb_lookup_value'] = $_POST['lookup_value'];
            }
            if (isset($_SESSION['edit_listing_qeb_lookup_field']) && isset($_SESSION['edit_listing_qeb_lookup_value'])) {
                if ($_SESSION['edit_listing_qeb_lookup_field'] != 'listingsdb_id') {
                    $_POST['lookup_field'] = $_SESSION['edit_listing_qeb_lookup_field'];
                    $_POST['lookup_value'] = $_SESSION['edit_listing_qeb_lookup_value'];
                }
            }
            if (isset($_POST['filter'])) {
                $_GET['cur_page'] = 0;
                $_SESSION['edit_listing_qeb_filter'] = $_POST['filter'];
            }
            if (isset($_SESSION['edit_listing_qeb_filter'])) {
                $_POST['filter'] = $_SESSION['edit_listing_qeb_filter'];
            }
            if (isset($_POST['agent_filter'])) {
                $_GET['cur_page'] = 0;
                $_SESSION['edit_listing_qeb_agent_filter'] = $_POST['agent_filter'];
            }
            if (isset($_SESSION['edit_listing_qeb_agent_filter'])) {
                $_POST['agent_filter'] = $_SESSION['edit_listing_qeb_agent_filter'];
            }
            if (isset($_POST['pclass_filter'])) {
                $_GET['cur_page'] = 0;
                $_SESSION['edit_listing_qeb_pclass_filter'] = $_POST['pclass_filter'];
            }
            if (isset($_SESSION['edit_listing_qeb_pclass_filter'])) {
                $_POST['pclass_filter'] = $_SESSION['edit_listing_qeb_pclass_filter'];
            }
            if (isset($_POST['lookup_field']) && isset($_POST['lookup_value']) && $_POST['lookup_field'] == 'listingsdb_id' && $_POST['lookup_value'] != '') {
                $_GET['edit'] = intval($_POST['lookup_value']);
                //TODO FIX THIS CRAPPY TONIGHT!!
                $api_result = $api->load_local_api('listing__read', ['listing_id' => intval($_POST['lookup_value']), 'fields' => ['listingsdb_pclass_id', 'listingsdb_active']]);
                if (!$api_result['error']) {
                    return $this->display_listing_editor($_GET['edit']);
                }
            }
            $ARGS = [];
            if ($only_my_listings == true) {
                unset($_POST['agent_filter']);
                $ARGS['user_ID'] = $_SESSION['userID'];
            }
            //Set Default to show all listings, not just active.
            $ARGS['listingsdb_active'] = 'any';
            // show all the listings
            if (isset($_POST['filter'])) {
                if ($_POST['filter'] == 'active') {
                    $ARGS['listingsdb_active'] = 'yes';
                } elseif ($_POST['filter'] == 'inactive') {
                    $ARGS['listingsdb_active'] = 'no';
                } else {
                    $ARGS['listingsdb_active'] = 'any';
                }
                if ($_POST['filter'] == 'expired') {
                    $ARGS['listingsdb_expiration_less'] = time();
                }
                if ($_POST['filter'] == 'featured') {
                    $ARGS['featuredOnly'] = 'yes';
                }
                if ($_POST['filter'] == 'created_1week') {
                    $ARGS['listingsdb_creation_date_greater'] = date('Y-m-d', strtotime('-1 week'));
                }
                if ($_POST['filter'] == 'created_1month') {
                    $ARGS['listingsdb_creation_date_greater'] = date('Y-m-d', strtotime('-1 month'));
                }
                if ($_POST['filter'] == 'created_3month') {
                    $ARGS['listingsdb_creation_date_greater'] = date('Y-m-d', strtotime('-3 month'));
                }
            }

            $lookup_sql = '';
            if (isset($_POST['lookup_field']) && isset($_POST['lookup_value']) && $_POST['lookup_field'] != 'listingsdb_id' && $_POST['lookup_field'] != 'listingsdb_title' && $_POST['lookup_value'] != '') {
                $ARGS[$_POST['lookup_field']][] = $_POST['lookup_value'];
            }
            if (isset($_POST['lookup_field']) && isset($_POST['lookup_value']) && $_POST['lookup_field'] == 'listingsdb_title' && $_POST['lookup_value'] != '') {
                $ARGS['listingsdb_title'] = $_POST['lookup_value'];
            }
            if (isset($_POST['pclass_filter']) &&  is_numeric($_POST['pclass_filter']) && $_POST['pclass_filter'] > 0) {
                $ARGS['pclass'][] = $_POST['pclass_filter'];
            }
            if (isset($_POST['agent_filter']) &&  $_POST['agent_filter'] != '') {
                $ARGS['user_ID'] = intval($_POST['agent_filter']);
            }
            //Do Search to get total record count, no need pass in soring information
            $result = $api->load_local_api('listing__search', ['parameters' => $ARGS, 'limit' => 0, 'offset' => 0, 'count_only' => 1]);
            //echo '<pre>'.print_r($result,TRUE).'</pre>';die;
            $num_rows =  $result['listing_count'];
            $max_pages = ceil($num_rows /  $config['admin_listing_per_page']);
            if (!isset($_GET['cur_page']) ||  $_GET['cur_page'] == 0) {
                $_GET['cur_page'] = 0;
            } else {
                if (intval($_GET['cur_page']) + 1 > $max_pages || intval($_GET['cur_page'])  < 0) {
                    header('HTTP/1.0 403 Forbidden');
                    $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
                    return $display;
                }
            }
            $next_prev = '<div class="d-flex justify-content-center">' . $misc->next_prev($num_rows, $_GET['cur_page'], '', '', true) . '</div>'; // put in the next/previous stuff

            $quick_edit = $this->show_quick_edit_bar($next_prev, $only_my_listings);
            // build the string to select a certain number of listings per page
            $limit_str = $_GET['cur_page'] * $config['admin_listing_per_page'];
            $result = $api->load_local_api('listing__search', ['parameters' => $ARGS, 'limit' => $config['admin_listing_per_page'], 'offset' => $limit_str /*,'sortby'=>$_GET['sortby'],'sorttype'=>$_GET['sorttype']*/]);
            //print_r($result);
            $count = 0;
            $display .= '';
            $page->replace_lang_template_tags();
            $page->replace_tags();
            $addons = $page->load_addons();
            $page->page = str_replace('{quick_edit_bar}', $quick_edit, $page->page);
            $listing_section = $page->get_template_section('listing_dataset');
            $listing = '';
            foreach ($result['listings'] as $listingID) {
                // alternate the colors
                if ($count == 0) {
                    $count = $count + 1;
                } else {
                    $count = 0;
                }
                $listing .= $listing_section;
                // strip slashes so input appears correctly
                //echo $listingID;
                $api_result = $api->load_local_api('listing__read', ['listing_id' => $listingID, 'fields' => [
                    'listingsdb_pclass_id', 'listingsdb_title', 'listingsdb_notes',
                    'listingsdb_active', 'listingsdb_featured', 'listingsdb_mlsexport', 'userdb_id', 'listingsdb_last_modified', 'listingsdb_expiration', 'listingsdb_hit_count', 'listingsdb_creation_date',
                ]]);
                //echo '<pre>'.print_r($api_result,TRUE).'</pre>';
                $title = $api_result['listing']['listingsdb_title'];
                $notes = $api_result['listing']['listingsdb_notes'];
                $active = $api_result['listing']['listingsdb_active'];
                $featured = $api_result['listing']['listingsdb_featured'];
                $mlsexport = $api_result['listing']['listingsdb_mlsexport'];
                $creationdate = $api_result['listing']['listingsdb_creation_date'];

                $formatted_creationdate = date($config['date_format_timestamp'], strtotime($creationdate));
                //$email = $recordSet->fields('userdb_emailaddress');
                $last_modified = date('D M j G:i:s T Y', strtotime($api_result['listing']['listingsdb_last_modified']));
                $formatted_expiration = date($config['date_format_timestamp'], strtotime($api_result['listing']['listingsdb_expiration']));
                $hit_count = $api_result['listing']['listingsdb_hit_count'];
                $active_raw = $active;
                $featured_raw = $featured;
                //Add filters to link
                if (isset($_POST['lookup_field']) && isset($_POST['lookup_value'])) {
                    $_GET['lookup_field'] = $_POST['lookup_field'];
                    $_GET['lookup_value'] = $_POST['lookup_value'];
                }
                if (isset($_GET['lookup_field']) && isset($_GET['lookup_value'])) {
                    $_POST['lookup_field'] = $_GET['lookup_field'];
                    $_POST['lookup_value'] = $_GET['lookup_value'];
                }

                $edit_link = $config['baseurl'] . '/admin/index.php?action=edit_listing&amp;edit=' . $listingID;
                $listing = $page->replace_listing_field_tags($listingID, $listing);
                $listing = $page->parse_template_section($listing, 'listingid', $listingID);
                $listing = $page->parse_template_section($listing, 'edit_listing_link', $edit_link);
                $listing = $page->parse_template_section($listing, 'listing_last_modified', $last_modified);
                $listing = $page->parse_template_section($listing, 'listing_creation_date', $formatted_creationdate);
                $listing = $page->parse_template_section($listing, 'listing_active_status', $active_raw);
                $listing = $page->parse_template_section($listing, 'listing_featured_status', $featured_raw);
                $listing = $page->parse_template_section($listing, 'listing_expiration', $formatted_expiration);
                $listing = $page->parse_template_section($listing, 'listing_notes', $notes);
                $listing = $page->parse_template_section($listing, 'row_num_even_odd', $count);
                $listing = $page->parse_template_section($listing, 'listing_hit_count', $hit_count);
                $listing_url = $page->magicURIGenerator('listing', $listingID, true);
                $listing = $page->parse_template_section($listing, 'listing_url', $listing_url);
                $addon_fields = $page->get_addon_template_field_list($addons);
                $listing = $page->parse_addon_tags($listing, $addon_fields);
                if ($config['use_expiration']  == 0) {
                    $listing = $page->remove_template_block('show_expiration', $listing);
                } else {
                    $listing = $page->cleanup_template_block('show_expiration', $listing);
                }
            } // end while

            $page->replace_template_section('listing_dataset', $listing);
            $page->replace_permission_tags();
            $display .= $page->return_page();
        } else {
            $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        $page->replace_tag('content', $display);
        $page->replace_tag('application_status_text', $status_text);
        $page->replace_lang_template_tags(true);
        $page->replace_permission_tags();
        $page->auto_replace_tags('', true);
        return $page->return_page();
    }

    public function show_quick_edit_bar($next_prev = '', $only_my_listings = true)
    {
        global $conn, $config, $lang, $misc;



        $display = '';

        $display .= '<div class="card card-frame  mb-4">
        <div class="card-body py-2">';



        $display .= '<form method="post" action="" class="row align-items-end mb-2 g-3">
                    <input type="hidden" name="token" value="' . $misc->generate_csrf_token() . '" />
                        <div class="order-2 order-md-1 col-5 col-md-auto">
                            <div class="input-group input-group-static">
                            <label for="lookup_fields" class="ms-0">' . $lang['listing_editor_lookup'] . '</label>
							<select id="lookup_fields" name="lookup_field"  class="form-control" aria-label="Lookup Field">
								<option value="listingsdb_id" ';

        if (isset($_POST['lookup_field']) && $_POST['lookup_field'] == 'listingsdb_id') {
            $display .= ' selected';
        }
        $display .= '>' . $lang['admin_listings_editor_listing_number'] . '</option>';

        $display .= '<option value="listingsdb_title" ';
        if (isset($_POST['lookup_field']) && $_POST['lookup_field'] == 'listingsdb_title') {
            $display .= ' selected';
        }
        $display .= '>' . $lang['admin_listings_editor_listing_title'] . '</option>';

        $sql = 'SELECT listingsformelements_field_name, listingsformelements_field_caption, listingsformelements_field_type
				FROM ' . $config['table_prefix'] . 'listingsformelements 
				WHERE listingsformelements_field_type != \'divider\'';
        $recordSet = $conn->execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        while (!$recordSet->EOF) {
            $field_name = $recordSet->fields('listingsformelements_field_name');
            $field_caption = $recordSet->fields('listingsformelements_field_caption');
            $display .= '<option value="' . $field_name . '" ';
            if (isset($_POST['lookup_field']) && $_POST['lookup_field'] == $field_name) {
                $display .= ' selected ';
            }
            $display .= '>' . $field_caption . '</option>';
            $recordSet->MoveNext();
        }
        $display .= '</select></div></div>
        <div class="order-3 order-md-2 col-4 col-md-auto">
        <div class="input-group input-group-outline">
        <label class="form-label">' . $lang['listing_editor_field_value'] . '</label>
        ';

        $display .= '<input name="lookup_value" type="text" class="form-control" value="';
        if (isset($_POST['lookup_value'])) {
            $display .= $_POST['lookup_value'];
        }
        $display .= '" autofocus></div></div>
        <div class="order-4 order-md-3 col-3 col-md-auto"><div class="input-group input-group-outline justify-content-end just-content-md-start">';

        $display .= '<button type="submit" class="btn btn-primary mt-auto mb-0"><i class="fa fa-search"></i><span class="d-none d-md-inline-block">' . $lang['search_button'] . '</span></button></div></div>
        <div class="order-1 col-12 order-md-4 col-md justify-content-end">
        <a class="btn btn-primary float-end mt-auto mb-0" id="add_listing_link" href="#!" role="button">
        <i class="fa-solid fa-plus"></i> 
        ' . $lang['admin_add_listing'] . '</a>
        </div>
					</form>
                    
                    <div class="row">
						<form name="listing_editor_filter_form" class="col-12 col-md-auto" method="post" action="">
                        <input type="hidden" name="token" value="' . $misc->generate_csrf_token() . '" />
                        <div class="row align-items-end">
                        <div class="col-8 col-md-auto">
                        <div class="input-group input-group-static">
                        <label for="filter" class="ms-0"
                          >' . $lang['listing_editor_show'] . '</label
                        >
                        <select
                          name="filter"
                          class="form-control"
                          id="filter"
                        >
                          <option value="" ';

        if (!isset($_POST['filter']) || $_POST['filter'] == '') {
            $display .= ' selected="selected" ';
        }
        $display .= '>' . $lang['listing_editor_show_all'] . '</option>
        <option value="active" ';

        if (isset($_POST['filter']) && $_POST['filter'] == 'active') {
            $display .= ' selected="selected" ';
        }
        $display .= '>' . $lang['listing_editor_active'] . '</option>';
        $display .= '<option value="inactive" ';
        if (isset($_POST['filter']) && $_POST['filter'] == 'inactive') {
            $display .= ' selected="selected" ';
        }
        $display .= '>' . $lang['listing_editor_inactive'] . '</option>';
        $display .= '<option value="expired" ';
        if (isset($_POST['filter']) && $_POST['filter'] == 'expired') {
            $display .= ' selected="selected" ';
        }
        $display .= '>' . $lang['listing_editor_expired'] . '</option>';
        $display .= '<option value="featured" ';
        if (isset($_POST['filter']) && $_POST['filter'] == 'featured') {
            $display .= ' selected="selected" ';
        }
        $display .= '>' . $lang['listing_editor_featured'] . '</option>';
        //This Weeks Listings
        $display .= '<option value="created_1week" ';
        if (isset($_POST['filter']) && $_POST['filter'] == 'created_1week') {
            $display .= ' selected="selected" ';
        }
        $display .= '>' . $lang['listing_editor_created_1week'] . '</option>';
        //This Month's Listings
        $display .= '<option value="created_1month" ';
        if (isset($_POST['filter']) && $_POST['filter'] == 'created_1month') {
            $display .= ' selected="selected" ';
        }
        $display .= '>' . $lang['listing_editor_created_1month'] . '</option>';
        //Last 3 Month's Listings
        $display .= '<option value="created_3month" ';
        if (isset($_POST['filter']) && $_POST['filter'] == 'created_3month') {
            $display .= ' selected="selected" ';
        }
        $display .= '>' . $lang['listing_editor_created_3month'] . '</option>';

        $display .= '	</select>
                        </div>
                        </div>
                        <div class="col-4 col-md-auto">
                            <div class="input-group input-group-outline">
                                <button type="submit" class="btn btn-primary mt-auto mb-0">
                                <i class="fa-solid fa-filter"></i><span class="d-none d-md-inline-block"> ' . $lang['listing_editor_filter'] . '</span>
                                </button>
                            </div>
                        </div>
                        </div>
					</form>
                    ';

        if (!$only_my_listings) {
            $display .= '
							<form name="listing_editor_agent_filter_form" method="post" class="col-12 col-md-auto" action="">
                            <input type="hidden" name="token" value="' . $misc->generate_csrf_token() . '" />
                            <div class="row align-items-end">
                                <div class="col-8 col-md-auto">
                                <div class="input-group input-group-static">
                        <label for="agent_filter" class="ms-0"
                          >' . $lang['listing_editor_show_agent'] . '</label
                        >
                        <select
                          name="agent_filter"
                          class="form-control"
                          id="agent_filter"
                        >
						<option value="" selected="selected">' . $lang['listing_editor_show_all'] . '</option>';

            $sql = 'SELECT userdb_id, userdb_user_first_name, userdb_user_last_name 
					FROM ' . $config['table_prefix'] . 'userdb
					WHERE userdb_is_agent = \'yes\'
					ORDER BY userdb_user_last_name,userdb_user_first_name';
            $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            while (!$recordSet->EOF) {
                $agent_ID = $recordSet->fields('userdb_id');
                $agent_first_name = $recordSet->fields('userdb_user_first_name');
                $agent_last_name = $recordSet->fields('userdb_user_last_name');
                if (isset($_POST['agent_filter']) && $_POST['agent_filter'] == $agent_ID) {
                    $display .= '<option value="' . $agent_ID . '" selected="selected">' . $agent_last_name . ',' . $agent_first_name . '</option>';
                } else {
                    $display .= '<option value="' . $agent_ID . '">' . $agent_last_name . ',' . $agent_first_name . '</option>';
                }
                $recordSet->MoveNext();
            }

            $display .= '</select>
                </div>
                </div>
                <div class="col-4 col-md-auto">
                <button  type="submit" class="btn btn-primary mt-auto mb-0">
                <i class="fa-solid fa-filter"></i><span class="d-none d-md-inline-block"> ' . $lang['listing_editor_filter'] . '</span>
                </button>
                </div>
                </div>
					</form>';
        }

        $display .= '<form name="form1" method="post" class="col-12 col-md-auto" action="">
        <input type="hidden" name="token" value="' . $misc->generate_csrf_token() . '" />
        <div class="row align-items-end">
                                <div class="col-8 col-md-auto">
                                
        <div class="input-group input-group-static">
        <label for="pclass_filter" class="ms-0"
          >' . $lang['listing_editor_show_pclass'] . '</label
        >
        <select
          name="pclass_filter"
          class="form-control"
          id="pclass_filter"
        >';

        $sql2 = 'SELECT class_id,class_name 
					FROM ' . $config['table_prefix'] . 'class';
        $recordSet2 = $conn->execute($sql2);
        if (!$recordSet2) {
            $misc->log_error($sql2);
        }

        $display .= '<option value="" selected="selected">' . $lang['listing_editor_show_all'] . '</option>';

        while (!$recordSet2->EOF) {
            $class_id = $recordSet2->fields('class_id');
            $class_name = $recordSet2->fields('class_name');
            if (isset($_POST['pclass_filter']) && $_POST['pclass_filter'] == $class_id) {
                $display .= '<option value="' . $class_id . '" selected="selected">' . $class_name . '</option>';
            } else {
                $display .= '<option value="' . $class_id . '">' . $class_name . '</option>';
            }
            $recordSet2->MoveNext();
        }

        $display .= '	</select>
        </div>
        </div>
        <div class="col-4 col-md-auto">
            <div class="input-group input-group-outline">
            <button  type="submit" class="btn btn-primary mt-auto mb-0">
                                <i class="fa-solid fa-filter"></i><span class="d-none d-md-inline-block"> ' . $lang['listing_editor_filter'] . '</span>
                                </button>
            </div>
        </div>
                    </div>
                        
					</form>
			</div>';

        if ($next_prev != '') {
            $display .= $next_prev;
        }
        $display .= '</div></div>';

        return $display;
    }

    public function update_listing($verify_user = true)
    {
        global $conn, $lang, $config, $api, $misc;

        include_once $config['basepath'] . '/include/forms.inc.php';
        $forms = new forms();
        include_once $config['basepath'] . '/include/listing.inc.php';
        $listing_pages = new listing_pages();
        include_once $config['basepath'] . '/include/social.inc.php';
        $social = new social();
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        $display = '';
        // update the listing
        $sql_edit = intval($_POST['edit']);
        $api_result = $api->load_local_api('listing__read', ['listing_id' => intval($sql_edit), 'fields' => ['listingsdb_pclass_id']]);
        if ($api_result['error']) {
            //If an error occurs die and show the error msg;
            die($api_result['error_msg']);
        }
        $edit_pclass = $api_result['listing']['listingsdb_pclass_id'];

        //$edit_pclass =intval($_POST['pclass']);
        if ($verify_user) {
            $listing_ownerID = $listing_pages->get_listing_agent_value('userdb_id', $sql_edit);
            if (intval($_SESSION['userID']) != $listing_ownerID) {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => $lang['listing_editor_permission_denied']]);
            }
        }
        //CSRF
        if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
        }
        if (trim($_POST['title']) == '') {
            // if the title is blank
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['admin_new_listing_enter_a_title']]);
        } // end if
        if (!isset($_POST['or_owner']) || !intval($_POST['or_owner']) > 0) {
            // if the title is blank
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['listing_error_invalid_agent']]);
        } // end if
        else {
            $pass_the_form = $forms->validateForm('listingsformelements', [$edit_pclass]);
            if ($pass_the_form !== 'Yes') {
                // if we're not going to pass it, tell that they forgot to fill in one of the fields
                $error_msg = '';
                foreach ($pass_the_form as $k => $v) {
                    if ($v == 'REQUIRED') {
                        $error_msg .= "$k: $lang[required_fields_not_filled]<br />";
                    }
                    if ($v == 'TYPE') {
                        $error_msg .= "$k: $lang[field_type_does_not_match]<br/>";
                    }
                }
                if ($error_msg != '') {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $error_msg]);
                }
                // $display .= "<p>$lang[required_fields_not_filled]</p>";
            }
            if ($pass_the_form == 'Yes') {
                $listing_details = [];
                $listing_details['title'] = trim($_POST['title']);
                $edit_seotitle = trim($_POST['seotitle']);
                if ($edit_seotitle == '') {
                    $edit_seotitle = 'AUTO';
                }
                $listing_details['seotitle'] = $edit_seotitle;
                if (isset($_POST['edit_expiration'])) {
                    $listing_details['expiration'] = trim($_POST['edit_expiration']);
                }
                if (isset($_POST['featured'])) {
                    if ($_POST['featured'] == 'yes') {
                        $listing_details['featured'] = true;
                    } else {
                        $listing_details['featured'] = false;
                    }
                }
                if (isset($_POST['edit_active'])) {
                    if ($_POST['edit_active'] == 'yes') {
                        $listing_details['active'] = true;
                    } else {
                        $listing_details['active'] = false;
                    }
                }
                if (isset($_POST['notes'])) {
                    $listing_details['notes'] = trim($_POST['notes']);
                }

                $edit_or_owner = intval($_POST['or_owner']);

                $result = $api->load_local_api('listing__update', ['class_id' => $edit_pclass, 'listing_id' => $sql_edit, 'listing_details' => $listing_details, 'listing_agents' => [$edit_or_owner], 'listing_fields' => $_POST, 'listing_media' => []]);
                //Get Seo Title
                $seoresult = $api->load_local_api('listing__read', ['listing_id' => $sql_edit, 'fields' => ['listing_seotitle']]);
                $seotitle = $seoresult['listing']['listing_seotitle'];
                if ($result['error'] == true) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $result['error_msg']]);
                } else {
                    header('Content-type: application/json');
                    $listing_url = $page->magicURIGenerator('listing', $sql_edit, true);
                    return json_encode(['error' => '0', 'listing_id' => $sql_edit, 'listing_seotitle' => $seotitle, 'listing_url' => $listing_url]);
                }
            } // end if $pass_the_form == "Yes"
        } // end else
        return $display;
    }
}
