<?php

global $config;
class memberssearch
{
    public function delete_search()
    {
        global $config;

        $display = '';
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $status = $login->loginCheck('Member');

        if ($status === true) {
            global  $lang, $conn, $misc;
            if (!isset($_GET['searchID'])) {
                $display .= '<a href="' . $config['baseurl'] . '/index.php">' . $lang['perhaps_you_were_looking_something_else'] . '</a>';
            } elseif ($_GET['searchID'] == '') {
                $display .= '<a href="' . $config['baseurl'] . '/index.php">' . $lang['perhaps_you_were_looking_something_else'] . '</a>';
            } elseif ($_GET['searchID'] != '') {
                $userID = intval($_SESSION['userID']);
                $searchID = intval($_GET['searchID']);
                $sql = 'DELETE FROM ' . $config['table_prefix'] . 'usersavedsearches 
						WHERE usersavedsearches_id = '.$searchID.' 
						AND userdb_id = '.$userID.'';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $display .= '<br />'.$lang['search_deleted_from_favorites'];
                $display .= $this->view_saved_searches();
                return $display;
            }
        } else {
            $display = $status;
        }
    }

    public function save_search()
    {
        global $config;

        $display = '';
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $status = $login->loginCheck('Member');

        //Load the Core Template class
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();

        //Load the Template
        $page->load_page($config['template_path'] . '/saved_searches_add.html');

        if ($status === true) {
            global $lang, $conn, $misc;
            $userID = intval($_SESSION['userID']);
            $guidestring = '';
            $query = '';
            
            if (isset($_POST['title']) && $_POST['title'] != '') {
                if (!isset($_POST['token']) || !$misc->validate_csrf_token($_POST['token'])) {
                    //File name contains non alphanum chars die to prevent file system attacks.
                    $display .= '<div style="text-align: center" class="redtext">
                    '.$lang['invalid_csrf_token'].'</div>';
                    unset($_POST['title']);
                    $display .= $this->save_search();
                    return $display;
                }
                $title = $misc->make_db_safe($_POST['title']);
                $query = $misc->make_db_safe($_POST['query']);
                $notify = $misc->make_db_safe($_POST['notify']);
                $misc->make_db_safe($_POST['title']);
                $sql = 'SELECT * 
						FROM ' . $config['table_prefix'] . "usersavedsearches 
						WHERE userdb_id = $userID 
						AND usersavedsearches_query_string = $query";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $num_columns = $recordSet->RecordCount();
                if ($num_columns == 0) {
                    $sql = 'INSERT INTO ' . $config['table_prefix'] . "usersavedsearches 
									(userdb_id, usersavedsearches_title, usersavedsearches_query_string,usersavedsearches_last_viewed,usersavedsearches_new_listings,usersavedsearches_notify) 
							VALUES ($userID, $title, $query, now(), 0, $notify)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    } else {
                        $display .= '<br />'. $lang['search_added_to_saved_searches']. ' - '. $_POST['title'];
                        $display .= $this->view_saved_searches();
                    }

                    //strip out the form, we don't need it.
                    $page->replace_template_section('saved_search_block', '');
                    $page->replace_tag('lang_notify_saved_search', '');
                    $page->replace_tag('lang_notify_saved_search_disabled', '');
                } else {
                    $saved_title = $recordSet->fields('usersavedsearches_title');
                    $saved_search_exists = $lang['search_already_in_saved_searches'] .': <a href="' . $config['baseurl'] . '/index.php?action=searchresults' . $_POST['query'] . '">' . htmlentities($saved_title, ENT_COMPAT, $config['charset']) . '</a>';
                    $page->replace_tag('saved_search_exists', $saved_search_exists);
                    foreach ($_GET as $k => $v) {
                        if (is_array($v)) {
                            foreach ($v as $vitem) {
                                $guidestring .= '&amp;' . urlencode("$k") . '[]=' . urlencode("$vitem");
                            }
                        } else {
                            $guidestring .= '&amp;' . urlencode("$k") . '=' . urlencode("$v");
                        }
                    }

                    if ($config['email_users_notification_of_new_listings'] == '1') {
                        $page->replace_tag('lang_notify_saved_search', $lang['notify_saved_search']);
                        $page->replace_tag('lang_notify_saved_search_disabled', '');
                        $notify_element = ' <select name="notify" id="notify" size="1">
												<option value="yes">' . $lang['yes'] . '
												<option value="no">' . $lang['no'] . '
											</select>';
                        $page->replace_tag('saved_search_notify', $notify_element);
                    } else {
                        $page->replace_tag('lang_notify_saved_search', '');
                        $page->replace_tag('lang_notify_saved_search_disabled', $lang['notify_saved_search_disabled']);
                        $page->replace_tag('saved_search_notify', '');
                    }

                    $template_section = $page->get_template_section('saved_search_block');

                    //Replace the section in the Page object with our alterations
                    $page->replace_template_section('saved_search_block', $template_section);
                    $query = $misc->make_db_unsafe($query);
                    $query = str_replace("'", '', $query);
                }
            } else {
                foreach ($_GET as $k => $v) {
                    if ($v && $k != 'action' && $k != 'PHPSESSID') {
                        if (is_array($v)) {
                            foreach ($v as $vitem) {
                                $query .= '&amp;' . urlencode("$k") . '[]=' . urlencode("$vitem");
                            }
                        } else {
                            $query .= '&amp;' . urlencode("$k") . '=' . urlencode("$v");
                        }
                    }
                }
                if (substr($query, 0, strcspn($query, '=')) == 'cur_page') {
                    $query = substr($query, strcspn($query, '&') + 1);
                    // echo $QUERY_STRING;
                }
                $sql = 'SELECT usersavedsearches_title, usersavedsearches_query_string 
						FROM ' . $config['table_prefix'] . "usersavedsearches 
						WHERE userdb_id = $_SESSION[userID] 
						AND usersavedsearches_query_string = '$query'";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $num_columns = $recordSet->RecordCount();

                if ($num_columns != 0) {
                    $saved_search_exists = $lang['search_already_in_saved_searches'] . ': <a href="' . $config['baseurl'] . '/index.php?searchresults&amp;' . $recordSet->fields('usersavedsearches_query_string') . '">' . htmlentities($recordSet->fields('usersavedsearches_title'), ENT_COMPAT, $config['charset']) . '</a>';
                    $page->replace_tag('saved_search_exists', $saved_search_exists);
                } else {
                    // Get full guidesting
                    
                    foreach ($_GET as $k => $v) {
                        if (is_array($v)) {
                            foreach ($v as $vitem) {
                                $guidestring .= '&amp;' . urlencode("$k") . '[]=' . urlencode("$vitem");
                            }
                        } else {
                            $guidestring .= '&amp;' . urlencode("$k") . '=' . urlencode("$v");
                        }
                    }

                    if ($config['email_users_notification_of_new_listings'] == '1') {
                        $page->replace_tag('lang_notify_saved_search', $lang['notify_saved_search']);
                        $page->replace_tag('lang_notify_saved_search_disabled', '');
                        //$display .= $lang['notify_saved_search'];
                        $notify_element = ' <select name="notify" id="notify" size="1">
												<option value="yes">' . $lang['yes'] . '
												<option value="no">' . $lang['no'] . '
											</select>';
                        $page->replace_tag('saved_search_notify', $notify_element);
                    } else {
                        $page->replace_tag('saved_search_notify', '');
                        $page->replace_tag('lang_notify_saved_search', '');
                        $page->replace_tag('lang_notify_saved_search_disabled', $lang['notify_saved_search_disabled']);
                    }
                }

                $template_section = $page->get_template_section('saved_search_block');

                //Replace the section in the Page object with our alterations
                $page->replace_template_section('saved_search_block', $template_section);
            }

            $page->replace_tag('saved_search_guidestring', $guidestring);
            $page->replace_tag('saved_search_query', $query);
            $page->replace_permission_tags();
            $page->replace_urls();
            $page->auto_replace_tags();
            $page->replace_lang_template_tags();

            $display .= $page->return_page();
        } else {
            $display = $status;
        }
        return $display;
    }

    public function view_saved_searches()
    {
        global $config;

        $display = '';
        $saved_searches='';
        $saved_search_error='';

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        $status = $login->loginCheck('Member');
        if ($status === true) {
            global  $lang, $conn, $misc;
            //Load the Core Template class
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_user();

            //Load the Notify Listing Template specified in the Site Config
            $page->load_page($config['template_path'] . '/saved_searches.html');

            $userID = intval($_SESSION['userID']);
            $sql = 'SELECT usersavedsearches_id, usersavedsearches_title, usersavedsearches_query_string, usersavedsearches_last_viewed
					FROM ' . $config['table_prefix'] . 'usersavedsearches 
					WHERE userdb_id = '.$userID.' 
					ORDER BY usersavedsearches_title';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $num_columns = $recordSet->RecordCount();
            if ($num_columns == 0) {
                $saved_search_error = $lang['no_saved_searches'] . '<br /><br />';
            } else {
                $saved_search_dataset = $page->get_template_section('saved_search_dataset');
                while (!$recordSet->EOF) {
                    $saved_searches .= $saved_search_dataset;
                    $title = $recordSet->fields('usersavedsearches_title');
                    $last_viewed =  $misc->convert_timestamp(strtotime($recordSet->fields('usersavedsearches_last_viewed')), true);
                    if ($title == '') {
                        $title = $lang['saved_search'];
                    }
                    $saved_search_link = '<a href="index.php?action=searchresults' . htmlspecialchars($recordSet->fields('usersavedsearches_query_string')) . '">' . htmlentities($title, ENT_COMPAT, $config['charset']) . '</a>';
                    $saved_search_delete = '<a href="index.php?action=delete_search&amp;searchID=' . $recordSet->fields('usersavedsearches_id') . '" onclick="return confirmDelete()">' . $lang['delete_search'] . '</a>';
                    $saved_searches = $page->parse_template_section($saved_searches, 'saved_search_link', $saved_search_link);
                    $saved_searches = $page->parse_template_section($saved_searches, 'saved_search_delete', $saved_search_delete);
                    $saved_searches = $page->parse_template_section($saved_searches, 'saved_search_last_viewed', $last_viewed);
                    $recordSet->MoveNext();
                }
            }

            $page->replace_tag('saved_search_error', $saved_search_error);
            $page->replace_template_section('saved_search_dataset', $saved_searches);
            $page->replace_permission_tags();
            $page->replace_urls();
            $page->auto_replace_tags();
            $page->replace_lang_template_tags();
            $display .= $page->return_page();
        } else {
            $display = $status;
        }
        return $display;
    }
}
