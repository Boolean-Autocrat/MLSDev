<?php

class notification
{
    public function NotifyUsersOfAllNewListings()
    {
        global $conn, $lang, $config, $misc;

        $display = '';
        $ORIGIONAL_GET = $_GET;
        include_once $config['basepath'] . '/include/search.inc.php';
        $search_page = new search_page();
        //Get Last Notification Timestamp
        $sql = 'SELECT controlpanel_notification_last_timestamp 
				FROM ' . $config['table_prefix_no_lang'] . 'controlpanel';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $last_timestamp = $conn->UnixTimeStamp($recordSet->fields('controlpanel_notification_last_timestamp'));
        //echo 'Timestamp'.$last_timestamp;

        //generate message with date since last notification was run
        $display .= $lang['notification_task_message'] . date(DATE_RFC822, $last_timestamp) . "<br />\r\n";
        $current_timestamp = time();
        $notify_count = 0;
        $sql = 'SELECT ' . $config['table_prefix'] . 'usersavedsearches.userdb_id, usersavedsearches_title, usersavedsearches_query_string, usersavedsearches_notify, userdb_user_name, userdb_emailaddress
				FROM ' . $config['table_prefix'] . 'userdb , ' . $config['table_prefix'] . 'usersavedsearches
				WHERE ' . $config['table_prefix'] . 'userdb.userdb_id = ' . $config['table_prefix'] . "usersavedsearches.userdb_id 
				AND usersavedsearches_notify = 'yes'";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }

        while (!$recordSet->EOF) {
            $query_string = $recordSet->fields('usersavedsearches_query_string');
            $user_id = $recordSet->fields('userdb_id');
            $search_title = $recordSet->fields('usersavedsearches_title');
            $email = $recordSet->fields('userdb_emailaddress');
            $user_name = $recordSet->fields('userdb_user_name');

            //generate checking message
            $display .= $lang['notification_task_check'] . $search_title . ' -> ' . $user_name . "<br />\r\n";
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
                        if (isset($pieces[1])) {
                            $_GET[$name][] = $pieces[1];
                        } else {
                            $_GET[$name][] = '';
                        }
                    } else {
                        if (isset($pieces[1])) {
                            $_GET[$pieces[0]] = $pieces[1];
                        } else {
                            $_GET[$pieces[0]] = '';
                        }
                    }
                }
            }
            if (!isset($_GET)) {
                $_GET[] = '';
            }
            $_GET['listingsdb_active'] = 'yes';
            if ($config['use_expiration'] == 1) {
                $_GET['listingsdb_expiration_greater'] = time();
            }
            $_GET['listing_last_modified_greater'] = $last_timestamp;
            //echo '<pre>'.print_r($_GET,TRUE).'</pre>';
            $matched_listing_ids = $search_page->search_results(true);
            //echo '<pre>'.print_r($matched_listing_ids,TRUE).'</pre>';
            if (count($matched_listing_ids) >= 1) {
                //print_r($matched_listing_ids);
                //Get User Details
                //Now that we have a list of the listings, render the template
                $template = $this->renderNotifyListings($matched_listing_ids, $search_title, $user_name, $email);
                // Send Mail
                if (isset($config['site_email']) && $config['site_email'] != '') {
                    $sender_email = $config['site_email'];
                } else {
                    $sender_email = $config['admin_email'];
                }
                $subject = $lang['new_listing_notify'] . $search_title;
                $sent = $misc->send_email($config['admin_name'], $sender_email, $email, $template, $subject, true, true);
                if ($sent !== true) {
                    $display .= '<span class="redtext">Error sending listing notification to ' . $user_name . '&lt;' . $email . '&gt; - ' . $sent . '</span>';
                } else {
                    $display .= '<span>Sent Listing Notification to ' . $user_name . '&lt;' . $email . '&gt; for listings ' . implode(',', $matched_listing_ids) . "</span><br />\r\n";
                }
            }
            $recordSet->MoveNext();
        }
        //Swt Last Notification Timestamp
        $db_timestamp = $conn->DBTimeStamp($current_timestamp);
        $sql = 'UPDATE ' . $config['table_prefix_no_lang'] . 'controlpanel 
				SET controlpanel_notification_last_timestamp = ' . $db_timestamp;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $_GET = $ORIGIONAL_GET;
        $display .= "Finish Sending Notifications<br />\r\n";
        return $display;
    }

    public function renderNotifyListings($listingIDArray, $search_title, $user_name, $email)
    {
        global $conn, $lang, $config, $current_ID, $misc;

        //Load the Core Template class and the Misc Class
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        include_once $config['basepath'] . '/include/listing.inc.php';
        $listingclass = new listing_pages();
        //Declare an empty display variable to hold all output from function.
        $display = '';
        //If We have a $current_ID save it
        $old_current_ID = '';
        if ($current_ID != '') {
            $old_current_ID = $current_ID;
        }

        //Load the Notify Listing Template specified in the Site Config
        $page->load_page($config['template_path'] . '/' . $config['notify_listings_template']);

        // Determine if the template uses rows.
        // First item in array is the row conent second item is the number of block per block row
        $notify_template_row = $page->get_template_section_row('notify_listing_block_row');

        if (is_array($notify_template_row)) {
            $row = $notify_template_row[0];
            $col_count = $notify_template_row[1];
            $user_rows = true;
            $x = 1;
            //Create an empty array to hold the row conents
            $new_row_data = [];
        } else {
            $user_rows = false;
        }
        $notify_template_section = '';
        $listingIDString = implode(',', $listingIDArray);
        $page->replace_tag('notify_results_link', $config['baseurl'] . '/index.php?action=searchresults&listing_id=' . $listingIDString);

        foreach ($listingIDArray as $current_ID) {
            if ($user_rows == true && $x > $col_count) {
                //We are at then end of a row. Save the template section as a new row.
                $new_row_data[] = $page->replace_template_section('notify_listing_block', $notify_template_section, $row);
                //$new_row_data[] = $notify_template_section;
                $notify_template_section = $page->get_template_section('notify_listing_block');
                $x = 1;
            } else {
                $notify_template_section .= $page->get_template_section('notify_listing_block');
            }
            $listing_title = $listingclass->get_listing_single_value('listingsdb_title', $current_ID);
            $notify_url = $page->magicURIGenerator('listing', $current_ID, true);
            $notify_template_section = $page->replace_listing_field_tags($current_ID, $notify_template_section);
            $notify_template_section = $page->parse_template_section($notify_template_section, 'notify_url', $notify_url);
            $notify_template_section = $page->parse_template_section($notify_template_section, 'listingid', $current_ID);

            // Setup Image Tags
            $sql2 = 'SELECT listingsimages_thumb_file_name,listingsimages_file_name
				FROM ' . $config['table_prefix'] . "listingsimages
				WHERE (listingsdb_id = $current_ID)
				ORDER BY listingsimages_rank";
            $recordSet2 = $conn->SelectLimit($sql2, 1, 0);
            if (!$recordSet2) {
                $misc->log_error($sql2);
            }
            if ($recordSet2->RecordCount() > 0) {
                $thumb_file_name = $recordSet2->fields('listingsimages_thumb_file_name');
                $file_name = $recordSet2->fields('listingsimages_file_name');
                if ($thumb_file_name != '' && file_exists("$config[listings_upload_path]/$thumb_file_name")) {
                    // gotta grab the thumbnail image size
                    $imagedata = GetImageSize("$config[listings_upload_path]/$thumb_file_name");
                    $imagewidth = $imagedata[0];
                    $imageheight = $imagedata[1];
                    $shrinkage = $config['thumbnail_width'] / $imagewidth;
                    $notify_thumb_width = $imagewidth * $shrinkage;
                    $notify_thumb_height = $imageheight * $shrinkage;
                    $notify_thumb_src = $config['listings_view_images_path'] . '/' . $thumb_file_name;
                    // gotta grab the thumbnail image size
                    $imagedata = GetImageSize("$config[listings_upload_path]/$file_name");
                    $imagewidth = $imagedata[0];
                    $imageheight = $imagedata[1];
                    $notify_width = $imagewidth;
                    $notify_height = $imageheight;
                    $notify_src = $config['listings_view_images_path'] . '/' . $file_name;
                }
            } else {
                if ($config['show_no_photo'] == 1) {
                    $imagedata = GetImageSize($config['basepath'] . '/images/nophoto.gif');
                    $imagewidth = $imagedata[0];
                    $imageheight = $imagedata[1];
                    $shrinkage = $config['thumbnail_width'] / $imagewidth;
                    $notify_thumb_width = $imagewidth * $shrinkage;
                    $notify_thumb_height = $imageheight * $shrinkage;
                    $notify_thumb_src = $config['baseurl'] . '/images/nophoto.gif';
                    $notify_width = $notify_thumb_width;
                    $notify_height = $notify_thumb_height;
                    $notify_src = $config['baseurl'] . '/images/nophoto.gif';
                } else {
                    $notify_thumb_width = '';
                    $notify_thumb_height = '';
                    $notify_thumb_src = '';
                    $notify_width = '';
                    $notify_height = '';
                    $notify_src = '';
                }
            }
            if (!empty($notify_thumb_src)) {
                $notify_template_section = $page->parse_template_section($notify_template_section, 'notify_thumb_src', $notify_thumb_src);
                $notify_template_section = $page->parse_template_section($notify_template_section, 'notify_thumb_height', $notify_thumb_height);
                $notify_template_section = $page->parse_template_section($notify_template_section, 'notify_thumb_width', $notify_thumb_width);
                $notify_template_section = $page->cleanup_template_block('notify_img', $notify_template_section);
            } else {
                $notify_template_section = $page->remove_template_block('notify_img', $notify_template_section);
            }
            if (!empty($notify_src)) {
                $notify_template_section = $page->parse_template_section($notify_template_section, 'notify_large_src', $notify_src);
                $notify_template_section = $page->parse_template_section($notify_template_section, 'notify_large_height', $notify_height);
                $notify_template_section = $page->parse_template_section($notify_template_section, 'notify_large_width', $notify_width);
                $notify_template_section = $page->cleanup_template_block('notify_img_large', $notify_template_section);
            } else {
                $notify_template_section = $page->remove_template_block('notify_img_large', $notify_template_section);
            }
            if ($user_rows == true) {
                $x++;
            }
        }
        if ($user_rows == true) {
            $notify_template_section = $page->cleanup_template_block('notify_listing', $notify_template_section);
            $new_row_data[] = $page->replace_template_section('notify_listing_block', $notify_template_section, $row);
            $replace_row = '';
            foreach ($new_row_data as $rows) {
                $replace_row .= $rows;
            }
            $page->replace_template_section_row('notify_listing_block_row', $replace_row);
        } else {
            $page->replace_template_section('notify_listing_block', $notify_template_section);
        }
        $page->replace_permission_tags();
        $page->replace_urls();
        $page->auto_replace_tags();
        $page->replace_lang_template_tags();
        $display .= $page->return_page();

        $current_ID = '';
        if ($old_current_ID != '') {
            $current_ID = $old_current_ID;
        }
        return $display;
    }
}
