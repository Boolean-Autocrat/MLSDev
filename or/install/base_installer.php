<?php
class installer
{
    public $version_number;
    public $lang;
    public function set_version()
    {
        $this->version_number = '3.6.3';
    }
    public function get_new_settings($old_db_type = 'mysqli', $old_db_server = 'localhost', $old_db_name = '', $old_db_user = '', $old_db_password = '', $old_table_prefix = 'default_')
    {
        $www = $this->get_base_url();
        $physical = $this->get_base_path();
        echo '<p>' . $this->lang['install_setup__database_settings'] . '</p>
      <form name="db_connection" method="post" action="index.php?step=5">
      <p>' . $this->lang['install_Database_Type'] . '
        <select name="db_type">
        <option value="mysqli" selected="selected">' . $this->lang['install_mySQL'] . '</option>
    
      </select>
      </p>
      <p> ' . $this->lang['install_Database_Server'] . '
        <input name="db_server" type="text" value="' . $old_db_server . '" />
      </p>
      <p> ' . $this->lang['install_Database_Name'] . '
        <input type="text" name="db_database" value="' . $old_db_name . '" />
      </p>
      <p> ' . $this->lang['install_Database_User'] . '
        <input type="text" name="db_user" value="' . $old_db_user . '" />
      </p>
      <p> ' . $this->lang['install_Database_Password'] . '
        <input type="text" name="db_password" value="' . $old_db_password . '" />
      </p>
      <p> ' . $this->lang['install_Table Prefix'] . '
        <input type="text" name="table_prefix" value="' . $old_table_prefix . '" />
      </p>
      <p> ' . $this->lang['install_Base_URL'] . '
        <input type="text" name="baseurl" size="60" value="' . $www . '" />
      </p>
      <p>' . $this->lang['install_Base_Path'] . '
        <input type="text" name="basepath" size="60" value="' . $physical . '" />
      </p>
      <p>' . $this->lang['install_devel_mode'] . '
        <select name="devel_mode"><option value="no">' . $this->lang['no'] . '</option><option value="yes">' . $this->lang['yes'] . '</option></select>
      </p>
      <p>
        <input type="submit" name="Submit" value="Next" />
      </p>
      </form>';
    }

    public function write_config()
    {
        include dirname(__FILE__) . '/../vendor/adodb/adodb-php/adodb.inc.php';
        $conn = ADONewConnection($_SESSION['db_type']);
        $conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, 'utf8');
        @$conn->connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']) or die('<strong>' . $this->lang['install_connection_fail'] . '</strong><br>');
        //@$conn->Connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']) or die('<strong>' . $this->lang['install_connection_fail'] . '</strong><br>');

        // Database connection made.
        if (!isset($_SESSION['autoinstall'])) {
            echo '' . $this->lang['install_connection_ok'] . '<br />';
        }
        $sqlversion = true;
        if ($_SESSION['db_type'] == 'mysqli') {
            $sqlversion = $this->check_mysql_version();
        }
        if ($sqlversion === false) {
            echo '<span style="color: red"><strong>' . $this->lang['install_sqlversion_warn'] . '</strong></span><br />';
            echo '<strong>' . $this->lang['install_sql_required'] . '5.0</strong>';
        } else {
            if (!isset($_SESSION['autoinstall'])) {
                echo '' . $this->lang['install_save_settings'] . '<br />';
            }
            $filecontent = '<?php
        $config = array();
        global $config, $conn, $misc, $db_type, $api, $db_server, $db_database, $options, $ADODB_SESS_CONN, $ADODB_FETCH_MODE;
        
        $db_type = "' . $_SESSION['db_type'] . '";
        $db_user = "' . $_SESSION['db_user'] . '";
        $db_password = "' . $_SESSION['db_password'] . '";
        $db_database = "' . $_SESSION['db_database'] . '";
        $db_server = "' . $_SESSION['db_server'] . '";
        $config["table_prefix_no_lang"] = "' . $_SESSION['table_prefix'] . '";
        
        if(session_id()){
          session_unset();
          session_write_close();
        }

        // this is the setup for the ADODB library
        require_once "' . $_SESSION['basepath'] . '/vendor/autoload.php";
        include_once("' . $_SESSION['basepath'] . '/vendor/adodb/adodb-php/adodb.inc.php");
        require_once "' . $_SESSION['basepath'] . '/vendor/adodb/adodb-php/session/adodb-cryptsession2.php";
        global $ADODB_SESS_DEBUG, $ADODB_SESSION_TBL, $ADODB_SESS_CONN;
        
        //establish DB connection
        $conn = ADONewConnection($db_type);
        $conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, "utf8");
        $conn->connect($db_server, $db_user, $db_password, $db_database);
        
        // Set Session Table and Connection
        $ADODB_SESSION_TBL = $config["table_prefix_no_lang"]."sessions";
        $ADODB_SESS_CONN = $conn;

        include_once("' . $_SESSION['basepath'] . '/include/misc.inc.php");
        $misc = new misc();
     
        $sql = "SET SESSION sql_mode = \'\'"; // To prevent errors from servers running sql_mode = ANSI
        $recordSet = $conn->Execute($sql);
        $sql = "SELECT * FROM ".$config["table_prefix_no_lang"]."controlpanel";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
          header("Location: ' . $_SESSION['baseurl'] . '/500.shtml");die;
        }
        
        // Loop throught Control Panel and save to Array
        $config["version"] = $recordSet->fields("controlpanel_version");
        $config["basepath"] = $recordSet->fields("controlpanel_basepath");
        $config["baseurl"] = $recordSet->fields("controlpanel_baseurl");

        $secure_cookie = false;
        if(strpos($config["baseurl"],"https://") !== false){
            $secure_cookie = true;
        }
        session_start([
            "cookie_httponly" => true,
            "cookie_samesite" => "Strict",
            "cookie_secure" => $secure_cookie,
            "use_strict_mode" => true,
            "sid_bits_per_character" => 6
        ]);


        $config["admin_name"] = $recordSet->fields("controlpanel_admin_name");
        $config["admin_email"] = $recordSet->fields("controlpanel_admin_email");
        $config["site_email"] = $recordSet->fields("controlpanel_site_email");
        $config["company_name"] = $recordSet->fields("controlpanel_company_name");
        $config["company_location"] = $recordSet->fields("controlpanel_company_location");
        $config["company_logo"] = $recordSet->fields("controlpanel_company_logo");
        $config["automatic_update_check"] = $recordSet->fields("controlpanel_automatic_update_check");
        $config["url_style"] = $recordSet->fields("controlpanel_url_style");
        $config["seo_default_keywords"] = $recordSet->fields("controlpanel_seo_default_keywords");
        $config["seo_default_description"] = $recordSet->fields("controlpanel_seo_default_description");
        $config["seo_listing_keywords"] = $recordSet->fields("controlpanel_seo_listing_keywords");
        $config["seo_listing_description"] = $recordSet->fields("controlpanel_seo_listing_description");
        $config["seo_default_title"] = $recordSet->fields("controlpanel_seo_default_title");
        $config["seo_listing_title"] = $recordSet->fields("controlpanel_seo_listing_title");
        $config["seo_url_seperator"] = $recordSet->fields("controlpanel_seo_url_seperator");
		if (isset($_SESSION["template"])){
			$config["template"] = $_SESSION["template"];
		} 
		else {
			$config["template"] = $recordSet->fields("controlpanel_template");
		}
        $config["full_template"] = $recordSet->fields("controlpanel_template");
        $config["admin_template"] = $recordSet->fields("controlpanel_admin_template");
        $config["listing_template"] = $recordSet->fields("controlpanel_listing_template");
        $config["agent_template"] = $recordSet->fields("controlpanel_agent_template");
        $config["template_listing_sections"] = $recordSet->fields("controlpanel_template_listing_sections");
        $config["search_result_template"] = $recordSet->fields("controlpanel_search_result_template");
        $config["vtour_template"] = $recordSet->fields("controlpanel_vtour_template");
        $config["lang"] = $recordSet->fields("controlpanel_lang");
        $config["listings_per_page"] = $recordSet->fields("controlpanel_listings_per_page");
        $config["search_step_max"] = $recordSet->fields("controlpanel_search_step_max");
        $config["max_search_results"] = $recordSet->fields("controlpanel_max_search_results");
        $config["add_linefeeds"] = $recordSet->fields("controlpanel_add_linefeeds");
        $config["strip_html"] = $recordSet->fields("controlpanel_strip_html");
        $config["allowed_html_tags"] = $recordSet->fields("controlpanel_allowed_html_tags");
        $config["money_sign"] = $recordSet->fields("controlpanel_money_sign");
        $config["show_no_photo"] = $recordSet->fields("controlpanel_show_no_photo");
        $config["number_format_style"] = $recordSet->fields("controlpanel_number_format_style");
        $config["number_decimals_number_fields"] = $recordSet->fields("controlpanel_number_decimals_number_fields");
        $config["number_decimals_price_fields"] = $recordSet->fields("controlpanel_number_decimals_price_fields");
        $config["money_format"] = $recordSet->fields("controlpanel_money_format");
        $config["date_format"] = $recordSet->fields("controlpanel_date_format");
        $date_format[1] = "mm/dd/yyyy";
        $date_format[2] = "yyyy/dd/mm";
        $date_format[3] = "dd/mm/yyyy";
        $date_format_timestamp[1] = "m/d/Y";
        $date_format_timestamp[2] = "Y/d/m";
        $date_format_timestamp[3] = "d/m/Y";
        $date_to_timestamp[1] = "%m/%d/%Y";
        $date_to_timestamp[2] = "%Y/%d/%m";
        $date_to_timestamp[3] = "%d/%m/%Y";
		    $config["date_to_timestamp"] = $date_to_timestamp[$config["date_format"]];
        $config["date_format_long"] = $date_format[$config["date_format"]];
        $config["date_format_timestamp"] = $date_format_timestamp[$config["date_format"]];
        $config["max_listings_uploads"] = $recordSet->fields("controlpanel_max_listings_uploads");
        $config["max_listings_upload_size"] = $recordSet->fields("controlpanel_max_listings_upload_size");
        $config["max_listings_upload_width"] = $recordSet->fields("controlpanel_max_listings_upload_width");
        $config["max_listings_upload_height"] = $recordSet->fields("controlpanel_max_listings_upload_height");
        $config["max_user_uploads"] = $recordSet->fields("controlpanel_max_user_uploads");
        $config["max_user_upload_size"] = $recordSet->fields("controlpanel_max_user_upload_size");
        $config["max_user_upload_width"] = $recordSet->fields("controlpanel_max_user_upload_width");
        $config["max_user_upload_height"] = $recordSet->fields("controlpanel_max_user_upload_height");
        $config["max_vtour_uploads"] = $recordSet->fields("controlpanel_max_vtour_uploads");
        $config["max_vtour_upload_size"] = $recordSet->fields("controlpanel_max_vtour_upload_size");
        $config["max_vtour_upload_width"] = $recordSet->fields("controlpanel_max_vtour_upload_width");
        $config["allowed_upload_extensions"] = $recordSet->fields("controlpanel_allowed_upload_extensions");
        $config["make_thumbnail"] = $recordSet->fields("controlpanel_make_thumbnail");
        $config["thumbnail_width"] = $recordSet->fields("controlpanel_thumbnail_width");
        $config["thumbnail_prog"] = $recordSet->fields("controlpanel_thumbnail_prog");
        $config["path_to_imagemagick"] = $recordSet->fields("controlpanel_path_to_imagemagick");
        $config["resize_img"] = $recordSet->fields("controlpanel_resize_img");
        $config["jpeg_quality"] = $recordSet->fields("controlpanel_jpeg_quality");
        $config["use_expiration"] = $recordSet->fields("controlpanel_use_expiration");
        $config["days_until_listings_expire"] = $recordSet->fields("controlpanel_days_until_listings_expire");
        $config["allow_member_signup"] = $recordSet->fields("controlpanel_allow_member_signup");
        $config["allow_agent_signup"] = $recordSet->fields("controlpanel_allow_agent_signup");
        $config["agent_default_active"] = $recordSet->fields("controlpanel_agent_default_active");
        $config["agent_default_admin"] = $recordSet->fields("controlpanel_agent_default_admin");
        $config["agent_default_feature"] = $recordSet->fields("controlpanel_agent_default_feature");
        $config["agent_default_moderate"] = $recordSet->fields("controlpanel_agent_default_moderate");
        $config["agent_default_logview"] = $recordSet->fields("controlpanel_agent_default_logview");
        $config["agent_default_edit_site_config"] = $recordSet->fields("controlpanel_agent_default_edit_site_config");
        $config["agent_default_edit_member_template"] = $recordSet->fields("controlpanel_agent_default_edit_member_template");
        $config["agent_default_edit_agent_template"] = $recordSet->fields("controlpanel_agent_default_edit_agent_template");
        $config["agent_default_edit_listing_template"] = $recordSet->fields("controlpanel_agent_default_edit_listing_template");
        $config["agent_default_canchangeexpirations"] = $recordSet->fields("controlpanel_agent_default_canchangeexpirations");
        $config["agent_default_editpages"] = $recordSet->fields("controlpanel_agent_default_editpages");
        $config["agent_default_havevtours"] = $recordSet->fields("controlpanel_agent_default_havevtours");
        $config["agent_default_num_listings"] = $recordSet->fields("controlpanel_agent_default_num_listings");
        $config["agent_default_num_featuredlistings"] = $recordSet->fields("controlpanel_agent_default_num_featuredlistings");
        $config["agent_default_can_export_listings"] = $recordSet->fields("controlpanel_agent_default_can_export_listings");
        $config["agent_default_edit_all_users"] = $recordSet->fields("controlpanel_agent_default_edit_all_users");
        $config["agent_default_edit_all_listings"] = $recordSet->fields("controlpanel_agent_default_edit_all_listings");
        $config["agent_default_edit_property_classes"] = $recordSet->fields("controlpanel_agent_default_edit_property_classes");
        $config["moderate_agents"] = $recordSet->fields("controlpanel_moderate_agents");
        $config["moderate_members"] = $recordSet->fields("controlpanel_moderate_members");
        $config["moderate_listings"] = $recordSet->fields("controlpanel_moderate_listings");
        $config["export_listings"] = $recordSet->fields("controlpanel_export_listings");
        $config["email_notification_of_new_users"] = $recordSet->fields("controlpanel_email_notification_of_new_users");
        $config["email_information_to_new_users"] = $recordSet->fields("controlpanel_email_information_to_new_users");
        $config["email_notification_of_new_listings"] = $recordSet->fields("controlpanel_email_notification_of_new_listings");
        $config["configured_langs"] = $recordSet->fields("controlpanel_configured_langs");
        $config["configured_show_count"] = $recordSet->fields("controlpanel_configured_show_count");
        $config["sortby"] = $recordSet->fields("controlpanel_search_sortby");
        $config["sorttype"] = $recordSet->fields("controlpanel_search_sorttype");
        $config["special_sortby"] = $recordSet->fields("controlpanel_special_search_sortby");
        $config["special_sorttype"] = $recordSet->fields("controlpanel_special_search_sorttype");
        $config["email_users_notification_of_new_listings"] = $recordSet->fields("controlpanel_email_users_notification_of_new_listings");
        $config["num_featured_listings"] = $recordSet->fields("controlpanel_num_featured_listings");
        $config["map_type"] = $recordSet->fields("controlpanel_map_type");
        $config["map_address"] =$recordSet->fields("controlpanel_map_address");
        $config["map_address2"] =$recordSet->fields("controlpanel_map_address2");
        $config["map_address3"] =$recordSet->fields("controlpanel_map_address3");
        $config["map_address4"] =$recordSet->fields("controlpanel_map_address4");
        $config["map_city"] = $recordSet->fields("controlpanel_map_city");
        $config["map_state"] = $recordSet->fields("controlpanel_map_state");
        $config["map_zip"] = $recordSet->fields("controlpanel_map_zip");
        $config["map_country"] = $recordSet->fields("controlpanel_map_country");
        $config["map_latitude"] = $recordSet->fields("controlpanel_map_latitude");
        $config["map_longitude"] = $recordSet->fields("controlpanel_map_longitude");
        $config["show_listedby_admin"] = $recordSet->fields("controlpanel_show_listedby_admin");
        $config["show_next_prev_listing_page"] = $recordSet->fields("controlpanel_show_next_prev_listing_page");
        $config["show_notes_field"] = $recordSet->fields("controlpanel_show_notes_field");
        $config["vtour_width"] = $recordSet->fields("controlpanel_vtour_width");
        $config["vtour_height"] = $recordSet->fields("controlpanel_vtour_height");
        $config["vt_popup_width"] = $recordSet->fields("controlpanel_vt_popup_width");
        $config["vt_popup_height"] = $recordSet->fields("controlpanel_vt_popup_height");
        $config["zero_price"] = $recordSet->fields("controlpanel_zero_price");
        $config["vcard_phone"] = $recordSet->fields("controlpanel_vcard_phone");
        $config["vcard_fax"] = $recordSet->fields("controlpanel_vcard_fax");
        $config["vcard_mobile"] = $recordSet->fields("controlpanel_vcard_mobile");
        $config["vcard_address"] = $recordSet->fields("controlpanel_vcard_address");
        $config["vcard_city"] = $recordSet->fields("controlpanel_vcard_city");
        $config["vcard_state"] = $recordSet->fields("controlpanel_vcard_state");
        $config["vcard_zip"] = $recordSet->fields("controlpanel_vcard_zip");
        $config["vcard_country"] = $recordSet->fields("controlpanel_vcard_country");
        $config["vcard_url"] = $recordSet->fields("controlpanel_vcard_url");
        $config["vcard_notes"] = $recordSet->fields("controlpanel_vcard_notes");
        $config["demo_mode"] = $recordSet->fields("controlpanel_demo_mode");
        $config["feature_list_separator"] = $recordSet->fields("controlpanel_feature_list_separator");
        $config["search_list_separator"] = $recordSet->fields("controlpanel_search_list_separator");
        $config["rss_title_featured"] = $recordSet->fields("controlpanel_rss_title_featured");
        $config["rss_desc_featured"] = $recordSet->fields("controlpanel_rss_desc_featured");
        $config["rss_listingdesc_featured"] = $recordSet->fields("controlpanel_rss_listingdesc_featured");
        $config["rss_title_lastmodified"] = $recordSet->fields("controlpanel_rss_title_lastmodified");
        $config["rss_desc_lastmodified"] = $recordSet->fields("controlpanel_rss_desc_lastmodified");
        $config["rss_listingdesc_lastmodified"] = $recordSet->fields("controlpanel_rss_listingdesc_lastmodified");
        $config["rss_limit_lastmodified"] = $recordSet->fields("controlpanel_rss_limit_lastmodified");
        $config["rss_title_latestlisting"] = $recordSet->fields("controlpanel_rss_title_latestlisting");
        $config["rss_desc_latestlisting"] = $recordSet->fields("controlpanel_rss_desc_latestlisting");
        $config["rss_listingdesc_latestlisting"] = $recordSet->fields("controlpanel_rss_listingdesc_latestlisting");
        $config["rss_limit_latestlisting"] = $recordSet->fields("controlpanel_rss_limit_latestlisting");
        $config["resize_by"] = $recordSet->fields("controlpanel_resize_by");
        $config["resize_thumb_by"] = $recordSet->fields("controlpanel_resize_thumb_by");
        $config["thumbnail_height"] = $recordSet->fields("controlpanel_thumbnail_height");
        $config["charset"] = $recordSet->fields("controlpanel_charset");
        $config["wysiwyg_show_edit"] = $recordSet->fields("controlpanel_wysiwyg_show_edit");
        $config["textarea_short_chars"] = $recordSet->fields("controlpanel_textarea_short_chars");
        $config["main_image_display_by"] = $recordSet->fields("controlpanel_main_image_display_by");
        $config["main_image_width"] = $recordSet->fields("controlpanel_main_image_width");
        $config["main_image_height"] = $recordSet->fields("controlpanel_main_image_height");
        $config["number_columns"] = $recordSet->fields("controlpanel_number_columns");
        $config["rss_limit_featured"] = $recordSet->fields("controlpanel_rss_limit_featured");
        $config["force_decimals"] = $recordSet->fields("controlpanel_force_decimals");
        $config["max_listings_file_uploads"] = $recordSet->fields("controlpanel_max_listings_file_uploads");
        $config["max_listings_file_upload_size"] = $recordSet->fields("controlpanel_max_listings_file_upload_size");
        $config["allowed_file_upload_extensions"] = $recordSet->fields("controlpanel_allowed_file_upload_extensions");
        $config["file_icon_width"] = $recordSet->fields("controlpanel_icon_image_width");
        $config["file_icon_height"] = $recordSet->fields("controlpanel_icon_image_height");
        $config["show_file_icon"] = $recordSet->fields("controlpanel_show_file_icon");
        $config["file_display_option"] = $recordSet->fields("controlpanel_file_display_option");
        $config["file_display_size"] = $recordSet->fields("controlpanel_show_file_size");
        $config["include_senders_ip"] = $recordSet->fields("controlpanel_include_senders_ip");
        $config["agent_default_havefiles"] = $recordSet->fields("controlpanel_agent_default_havefiles");
        $config["agent_default_haveuserfiles"] = $recordSet->fields("controlpanel_agent_default_haveuserfiles");
        $config["max_users_file_uploads"] = $recordSet->fields("controlpanel_max_users_file_uploads");
        $config["max_users_file_upload_size"] = $recordSet->fields("controlpanel_max_users_file_upload_size");
        $config["disable_referrer_check"] = $recordSet->fields("controlpanel_disable_referrer_check");
        $config["price_field"] = $recordSet->fields("controlpanel_price_field");
        $config["users_per_page"] = $recordSet->fields("controlpanel_users_per_page");
        $config["show_admin_on_agent_list"] = $recordSet->fields("controlpanel_show_admin_on_agent_list");
        $config["use_signup_image_verification"] = $recordSet->fields("controlpanel_use_signup_image_verification");
        $config["controlpanel_mbstring_enabled"] = $recordSet->fields("controlpanel_mbstring_enabled");
        $config["require_email_verification"] = $recordSet->fields("controlpanel_require_email_verification");
        $config["blog_requires_moderation"] = $recordSet->fields("controlpanel_blog_requires_moderation");
        $config["maintenance_mode"] = $recordSet->fields("controlpanel_maintenance_mode");
        $config["notify_listings_template"] = $recordSet->fields("controlpanel_notify_listings_template");
        $config["twitter_consumer_secret"] = $recordSet->fields("controlpanel_twitter_consumer_secret");
        $config["twitter_consumer_key"] = $recordSet->fields("controlpanel_twitter_consumer_key");
        $config["twitter_auth"] = $recordSet->fields("controlpanel_twitter_auth");
        $config["twitter_new_listings"] = $recordSet->fields("controlpanel_twitter_new_listings");
        $config["twitter_update_listings"] = $recordSet->fields("controlpanel_twitter_update_listings");
        $config["twitter_new_blog"] = $recordSet->fields("controlpanel_twitter_new_blog");
        $config["twitter_listing_photo"] = $recordSet->fields("controlpanel_twitter_listing_photo");
        $config["pingback_services"] = $recordSet->fields("controlpanel_blog_pingback_urls");
        $config["blogs_per_page"] = $recordSet->fields("controlpanel_blogs_per_page");
        $config["allow_pingbacks"] = $recordSet->fields("controlpanel_allow_pingbacks");
        $config["send_url_pingbacks"] = $recordSet->fields("controlpanel_send_url_pingbacks");
        $config["send_service_pingbacks"] = $recordSet->fields("controlpanel_send_service_pingbacks");
        $config["timezone"] = $recordSet->fields("controlpanel_timezone");
        $config["default_page"] = $recordSet->fields("controlpanel_default_page");
        $config["blog_pingback_urls"]=$recordSet->fields("controlpanel_blog_pingback_urls");
        $config["banned_domains_signup"]=$recordSet->fields("controlpanel_banned_domains_signup");
        $config["banned_ips_signup"]=$recordSet->fields("controlpanel_banned_ips_signup");
        $config["banned_ips_site"]=$recordSet->fields("controlpanel_banned_ips_site");
        $config["rss_title_blogposts"]=$recordSet->fields("controlpanel_rss_title_blogposts");
        $config["rss_desc_blogposts"]=$recordSet->fields("controlpanel_rss_desc_blogposts");
        $config["rss_title_blogcomments"]=$recordSet->fields("controlpanel_rss_title_blogcomments");
        $config["rss_desc_blogcomments"]=$recordSet->fields("controlpanel_rss_desc_blogcomments");
        $config["phpmailer"]=$recordSet->fields("controlpanel_phpmailer");
        $config["mailserver"]=$recordSet->fields("controlpanel_mailserver");
        $config["mailport"]=$recordSet->fields("controlpanel_mailport");
        $config["mailuser"]=$recordSet->fields("controlpanel_mailuser");
        $config["mailpass"]=$recordSet->fields("controlpanel_mailpass");
        $config["agent_default_canManageAddons"]=$recordSet->fields("controlpanel_agent_default_canManageAddons");
        $config["agent_default_blogUserType"]=$recordSet->fields("controlpanel_agent_default_blogUserType");
        $config["agent_default_edit_all_leads"]=$recordSet->fields("controlpanel_agent_default_edit_all_leads");
        $config["agent_default_edit_lead_template"]=$recordSet->fields("controlpanel_agent_default_edit_lead_template");
        $config["enable_tracking"]=$recordSet->fields("controlpanel_enable_tracking");
        $config["enable_tracking_crawlers"]=$recordSet->fields("controlpanel_enable_tracking_crawlers");
        $config["show_agent_no_photo"] = $recordSet->fields("controlpanel_show_agent_no_photo");
        $config["template_lead_sections"] = $recordSet->fields("controlpanel_template_lead_sections");
        $config["allow_template_change"]= $recordSet->fields("controlpanel_allow_template_change");
        $config["allow_language_change"]= $recordSet->fields("controlpanel_allow_language_change");
        $config["listingimages_slideshow_group_thumb"]= $recordSet->fields("controlpanel_listingimages_slideshow_group_thumb");
        $config["admin_listing_per_page"]= $recordSet->fields("controlpanel_admin_listing_per_page");
        $config["mobile_template"] = $recordSet->fields("controlpanel_mobile_template");
        $config["captcha_system"] = $recordSet->fields("controlpanel_captcha_system");
        $config["floor_agent"] = $recordSet->fields("controlpanel_floor_agent");
        $config["contact_template"] = $recordSet->fields("controlpanel_contact_template");
        $config["user_jpeg_quality"] = $recordSet->fields("controlpanel_user_jpeg_quality");
        $config["user_resize_img"] = $recordSet->fields("controlpanel_user_resize_img");
        $config["user_resize_by"] = $recordSet->fields("controlpanel_user_resize_by");
        $config["user_resize_thumb_by"] = $recordSet->fields("controlpanel_user_resize_thumb_by");
        $config["user_thumbnail_width"] = $recordSet->fields("controlpanel_user_thumbnail_width");
        $config["user_thumbnail_height"] = $recordSet->fields("controlpanel_user_thumbnail_height");
        $config["num_popular_listings"] = $recordSet->fields("controlpanel_num_popular_listings");
        $config["num_random_listings"] = $recordSet->fields("controlpanel_num_random_listings");
        $config["num_latest_listings"] = $recordSet->fields("controlpanel_num_latest_listings");
        $config["google_client_id"] = $recordSet->fields("controlpanel_google_client_id");
        $config["google_client_secret"] = $recordSet->fields("controlpanel_google_client_secret");
        
        date_default_timezone_set($config["timezone"]);
        
        //Determine which table to use based on language
        $config["table_prefix"] = "' . $_SESSION['table_prefix'] . '$config[lang]_";
        if (!isset($_SESSION["users_lang"])){
          $config["lang_table_prefix"] = "' . $_SESSION['table_prefix'] . '$config[lang]_";
        }
        else{
          $config["lang_table_prefix"] = "' . $_SESSION['table_prefix'] . '$_SESSION[users_lang]_";
        }
        ///////////////////////////////////////////////////
        // Path Settings
        // These Paths are set based on setting in the control panel
        $config["path_to_thumbnailer"] = $config["basepath"]."/include/thumbnail".$config["thumbnail_prog"].".php"; // path to the thumnailing tool
        if($misc->detect_mobile_browser() && !isset($_SESSION["template"])){
          $config["template"] =  $config["mobile_template"];
          $config["template_path"] = $config["basepath"]."/template/".$config["mobile_template"]; // leave off the trailing slashes
          $config["template_url"] = $config["baseurl"]."/template/".$config["mobile_template"]; // leave off the trailing slashes
        }
        else{
          $config["template_path"] = $config["basepath"]."/template/".$config["template"]; // leave off the trailing slashes
          $config["template_url"] = $config["baseurl"]."/template/".$config["template"]; // leave off the trailing slashes
        }
        $config["admin_template_path"] = $config["basepath"]."/admin/template/".$config["admin_template"]; // leave off the trailing slashes
        $config["admin_template_url"] = $config["baseurl"]."/admin/template/".$config["admin_template"]; // leave off the trailing slashes
        ///////////////////////////////////////////////////
        // MISCELLENEOUS SETTINGS
        // you shouldn"t have to mess with these things unless you rename a folder, etc...
        $config["listings_upload_path"] = $config["basepath"]."/images/listing_photos";
        $config["listings_view_images_path"] = $config["baseurl"]."/images/listing_photos";
        $config["user_upload_path"] = $config["basepath"]."/images/user_photos";
        $config["user_view_images_path"] = $config["baseurl"]."/images/user_photos";
        $config["vtour_upload_path"] = $config["basepath"]."/images/vtour_photos";
        $config["vtour_view_images_path"] = $config["baseurl"]."/images/vtour_photos";
        $config["file_icons_path"] = $config["basepath"]."/files";
        $config["listings_view_file_icons_path"] = $config["baseurl"]."/files";
        $config["listings_file_upload_path"] = $config["basepath"]."/files/listings";
        $config["listings_view_file_path"] = $config["baseurl"]."/files/listings";
        $config["users_file_upload_path"] = $config["basepath"]."/files/users";
        $config["users_view_file_path"] = $config["baseurl"]."/files/users";
        define("BR","\r\n");
        //Load URI Parts
        $recordSet = $conn->Execute($sql);
        
        $sql = "SELECT slug,uri,action FROM ".$config["table_prefix_no_lang"]."seouri";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet){
          header("Location: ' . $_SESSION['baseurl'] . '/500.shtml");die;
        }
        while(!$recordSet->EOF){
		      $config["uri_parts"][$recordSet->fields("action")]["slug"]=$recordSet->fields("slug");
		      $config["uri_parts"][$recordSet->fields("action")]["uri"]=$recordSet->fields("uri");
		      $recordSet->MoveNext();
		    }
		    include_once("' . $_SESSION['basepath'] . '/api/api.inc.php");
        $api = new api();
?>';

            // End of File Content now write it
            $common = '../include/common.php';
            $commondist = '../include/common.dist.php';
            if (!file_exists($common)) {
                @rename($commondist, $common);
            }
            $out = fopen($_SESSION['basepath'].'/include/common.php', 'w');
            fwrite($out, $filecontent, strlen($filecontent));
            fclose($out);
            @chmod($_SESSION['basepath'].'include/common.php', 0644);
            // File has been writen
            //Rename plugin files if they don't exist
            $userplg = '../plugins/user_plg.inc.php';
            $userplgdist = '../plugins/user_plg.dist.php';
            if (!file_exists($userplg)) {
                @rename($userplgdist, $userplg);
            }
            $loginplg = '../plugins/login_plg.inc.php';
            $loginplgdist = '../plugins/login_plg.dist.php';
            if (!file_exists($loginplg)) {
                @rename($loginplgdist, $loginplg);
            }
            $listingplg = '../plugins/listing_plg.inc.php';
            $listingplgdist = '../plugins/listing_plg.dist.php';
            if (!file_exists($listingplg)) {
                @rename($listingplgdist, $listingplg);
            }

            if (!isset($_SESSION['autoinstall'])) {
                $message = '<strong>' . $this->lang['install_settings_saved'] . ' <a href="index.php?step=6">' . $this->lang['install_continue_db_setup'] . '</a></strong>';
                echo $message;
            }
        }
    }
    public function get_base_path()
    {
        $physical = substr(dirname(__FILE__), 0, -8);
        $physical = str_replace('\\', '/', $physical);
        return $physical;
    }
    public function get_previous_version()
    {
        global $lang;
        include_once dirname(__FILE__) . '/../vendor/adodb/adodb-php/adodb.inc.php';
        $config['table_prefix'] = $_SESSION['table_prefix'] . $_SESSION['or_install_lang'] . '_';
        $config['table_prefix_no_lang'] = $_SESSION['table_prefix'];

        $conn = ADONewConnection($_SESSION['db_type']);
        $conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, 'utf8');
        @$conn->connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']) or die('<strong>' . $this->lang['install_connection_fail'] . '</strong><br>');
        //@$conn->connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']) or die('<strong>' . $this->lang['install_connection_fail'] . '</strong><br>');

        // Database connection made.
        echo $this->lang['install_get_old_version'] . '<br />';
        $sql = 'SELECT controlpanel_version 
            FROM ' . $config['table_prefix_no_lang'] . 'controlpanel';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            echo 'ERROR: ' . $sql;
        }
        // Loop throught Control Panel and save to Array
        $old_version = $recordSet->fields('controlpanel_version');
        if ($old_version == '') {
            echo $lang['install_get_old_version_error'];
            exit();
        }
        return $old_version;
    }

    public function get_base_url()
    {
        $me = $_SERVER['PHP_SELF'];
        $Apathweb = explode('/', $me);
        $myFileName = array_pop($Apathweb);
        $pathweb = implode('/', $Apathweb);
        if (
            isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
        ) {
            $http_proto = 'https://';
        } else {
            $http_proto = 'http://';
        }
        $myURL = $http_proto . $_SERVER['HTTP_HOST'] . $pathweb . '/' . $myFileName;
        $PAGE_BASE['www'] = $myURL;
        // this is so you can verify the results:
        $www = substr($PAGE_BASE['www'], 0, -18);
        return $www;
    }
    public function check_file_permissions($files)
    {
        $permission_pass = true;
        $OS = $this->os_type();
        foreach ($files as $file) {
            if (is_writeable($file)) {
                echo '' . $this->lang['install_Permission_on'] . ' ' . $file . ' ' . $this->lang['install_are_correct'] . '<br>';
            } else {
                echo '' . $this->lang['install_Permission_on'] . ' ' . $file . ' ' . $this->lang['install_are_incorrect'] . ' (' . substr(sprintf('%o', fileperms($file)), -3) . ')<br>';
                $permission_pass = false;
            }
        }
        return $permission_pass;
    }
    public function os_type()
    {
        // Get OS
        $test1 = explode('Win32', $_SERVER['SERVER_SOFTWARE']);
        $test2 = explode('Microsoft', $_SERVER['SERVER_SOFTWARE']);
        $test3 = explode('BadBlue', $_SERVER['SERVER_SOFTWARE']);
        // REMOVED EMPTY $OS = ""; else will always catch if the if fails so no point in defining it here.
        if (count($test1) > 1 || count($test2) > 1 || count($test3) > 1) {
            $OS = 'Windows';
        } else {
            $OS = 'Linux';
        }
        return $OS;
    }
    public function check_php_version($requiredPHPVersion)
    {
        // Check PHP Version
        if (version_compare(PHP_VERSION, $requiredPHPVersion, '<')) {
            return false;
        } else {
            return true;
        }
    }
    public function check_mysql_version()
    {
        include_once dirname(__FILE__) . '/../vendor/adodb/adodb-php/adodb.inc.php';
        $config['table_prefix'] = $_SESSION['table_prefix'] . $_SESSION['or_install_lang'] . '_';
        $config['table_prefix_no_lang'] = $_SESSION['table_prefix'];

        $conn = ADONewConnection($_SESSION['db_type']);
        $conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, 'utf8');

        @$conn->connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']) or die('<strong>' . $this->lang['install_connection_fail'] . '</strong><br>');
        //@$conn->Connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']) or die('<strong>' . $this->lang['install_connection_fail'] . '</strong><br>');

        $appmysql = '5.0';
        $sql = 'SELECT VERSION()';
        $rs = $conn->Execute($sql);
        $currentSQLversion = $rs->fields('VERSION()');
        $check = version_compare($currentSQLversion, $appmysql, '>=');
        return $check;
    }
    public function show_header()
    {
        echo '<html>
      <head>
      <title>Open-Realty® ' . $this->version_number . 'Install</title>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
      <style type="text/css">
      .warnings_message {color:#FF0000; font-weight:bold; padding:5px 0 5px 0;}
      </style>
      </head>
      <body style="text-align:center;width:100%;">
      <div style="width:600px;margin-left:auto;margin-right:auto;text-align:left;">
      <div style="width:600px;height:100px;background-repeat:no-repeat;background-image:url(\'logo.png\');display:block;background-color:#88909B;background-position:25px center;"></div>
      <div style="border:1px solid #88909B;padding:5px;">';
    }
    public function show_footer()
    {
        echo '</div></div></body></html>';
    }
    public function load_lang($lang)
    {
        include dirname(__FILE__) . '/language/' . $lang . '/lang.inc.php';
        $this->lang = $lang;
    }

    /**
     * Drops all open-realty tables from the database.
     * This function is ony used for integration testing
     *
     * @param [string] $string
     * @param [string] $server
     * @param [string] $user
     * @param [string] $pass
     * @param [string] $database
     * @return void
     */
    public function drop_all_tables($type, $server, $user, $pass, $database)
    {
        // this is the setup for the ADODB library
        include_once dirname(__FILE__) . '/../vendor/adodb/adodb-php/adodb.inc.php';
        $conn = ADONewConnection($type);
        $conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, 'utf8');
        $connected = $conn->Connect($server, $user, $pass, $database);
        if (!$connected) {
            die("<strong><span style=\"red\">ERROR - $type, $server, $user, $pass, $database</span></strong><br />");
        }
        $sql = 'DROP TABLE IF EXISTS `default_addons`, `default_auth_tokens`, `default_blogcategory_relation`, `default_blogpingbacks`, `default_blogtag_relation`, `default_classformelements`, `default_controlpanel`, `default_en_activitylog`, `default_en_agentformelements`, `default_en_blogcategory`, `default_en_blogcomments`, `default_en_blogmain`, `default_en_blogtags`, `default_en_class`, `default_en_feedbackdb`, `default_en_feedbackdbelements`, `default_en_feedbackformelements`, `default_en_listingsdb`, `default_en_listingsdbelements`, `default_en_listingsfiles`, `default_en_listingsformelements`, `default_en_listingsimages`, `default_en_listingsvtours`, `default_en_memberformelements`, `default_en_menu`, `default_en_menu_items`, `default_en_pagesmain`, `default_en_userdb`, `default_en_userdbelements`, `default_en_userfavoritelistings`, `default_en_userimages`, `default_en_usersavedsearches`, `default_en_usersfiles`, `default_forgot`, `default_open-realty_license`, `default_seouri`, `default_sessions`, `default_tracking`, `default_zipdist`;';
        
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            if ($_SESSION['devel_mode'] == 'no') {
                die("<strong><span style=\"red\">ERROR - $sql</span></strong><br />");
            } else {
                echo "<strong><span style=\"red\">ERROR - $sql</span></strong><br />";
            }
        }
    }
    public function database_maintenance()
    {
        // this is the setup for the ADODB library
        include_once dirname(__FILE__) . '/../vendor/adodb/adodb-php/adodb.inc.php';
        $conn = ADONewConnection($_SESSION['db_type']);
        $conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, 'utf8');
        $conn->Connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']);
        if (strpos($_SESSION['db_type'], 'postgres') !== false) {
            $sql_insert[] = 'VACUUM VERBOSE ANALYZE';
        } else {
            return;
        }

        foreach ($sql_insert as $elementContents) {
            $recordSet = $conn->Execute($elementContents);
            if ($recordSet === false) {
                if ($_SESSION['devel_mode'] == 'no') {
                    die("<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />");
                } else {
                    echo "<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />";
                }
            }
        }
    }
    public function run_installer()
    {
        if (isset($_GET['step'])) {
            if ($_GET['step'] == 'autoupdate') {
                $_SESSION['or_install_lang'] = $_GET['or_install_lang'];
                $_SESSION['or_install_type'] = $_GET['or_install_type'];

                $this->load_lang($_SESSION['or_install_lang']);
                $this->set_version();
                include_once dirname(__FILE__) . '/versions/' . $_SESSION['or_install_type'] . '.inc.php';
                $version = new version();
                $version->load_version();
            } elseif ($_GET['step'] == 'autoinstall') {
                $_SESSION['or_install_lang'] = $_GET['or_install_lang'];
                $_SESSION['or_install_type'] = $_GET['or_install_type'];

                $this->load_lang($_SESSION['or_install_lang']);
                $this->set_version();
                include_once dirname(__FILE__) . '/versions/' . $_SESSION['or_install_type'] . '.inc.php';
                $version = new version();
                $version->load_version();
            } elseif ($_GET['step'] > 3) {
                $this->load_lang($_SESSION['or_install_lang']);
                $this->set_version();
                if (isset($_POST['install_type'])) {
                    $_SESSION['or_install_type'] = $_POST['install_type'];
                }
                // Possible Values
                // upgrade_115, install_200, ,install_200_beta_2 move
                include_once dirname(__FILE__) . '/versions/' . $_SESSION['or_install_type'] . '.inc.php';
                $version = new version();
                $version->load_version();
            } else {
                $runme = 'run_installer_' . $_GET['step'];
                $this->$runme();
            }
        } else {
            $this->run_installer_0();
        }
    }
    public function run_installer_0()
    {
        echo '<p><strong>Before you may install Open-Realty®, You must first read and agree to the following license agreement!</strong></p>';
        echo str_replace("\n", '<br>', file_get_contents(dirname(__FILE__) . '/../license.txt'));
        echo '<form name="form1" method="post" action="index.php?step=1">
            <p class="align-center"><input type="submit" name="Submit" value="I Agree" />
              <input type="Reset" name="Reset" value="I don\'t Agree" />
            </p>
          </form>';
    }
    public function run_installer_1()
    {
        $dirs = array_filter(glob('language/*'), 'is_dir');
        $dirs = str_replace('language/', '', $dirs);
        echo '<form name="form1" method="post" action="index.php?step=2">
          Language:
          <select name="lang">';
        $langs = array();
        foreach ($dirs as $lang) {
            $langs[$lang] = $this->return_langs($lang);
        }
        asort($langs);
        foreach ($langs as $key => $value) {
            echo '<option value="' . $key . '">' . $value . '</option>';
        }
        echo '</select>
          <input type="submit" name="Submit" value="Submit" />
    </form>';
    }

    private function return_langs($lang)
    {
        $lang_text = 'UnKnown';
        switch ($lang) {
            case 'en':
                $lang_text = 'English';
                break;
            case 'es':
                $lang_text = 'Español';
                break;
            case 'br':
                $lang_text = 'Português de Brasil';
                break;
            case 'pt':
                $lang_text = 'Português';
                break;
        }
        return $lang_text;
    }
    public function run_installer_2()
    {
        global $config;
        // Load Lang
        $_SESSION['or_install_lang'] = $_POST['lang'];
        $this->load_lang($_SESSION['or_install_lang']);
        // Show welcome text
        echo '<h2>' . $this->lang['install_welcome'] . '</h2>
      <p>' . $this->lang['install_intro'] . '</p>

      <p>' . $this->lang['install_step_one_header'] . '</p>
      ';
        // Check PHP version
        $check_for_version = "7.4.3";
        $php_version = $this->check_php_version($check_for_version);
        if ($php_version) {
            $common = '../include/common.php';
            if (file_exists($common)) {
                $files[] = '../include/common.php';
            } else {
                $files[] = '../include/common.dist.php';
                $files[] = '../include';
            }
            $files[] = '../images/listing_photos';
            $files[] = '../images/user_photos';
            $files[] = '../images/vtour_photos';
            $files[] = '../images/page_upload';
            $files[] = '../images/blog_uploads';
            $files[] = '../files/listings';
            $files[] = '../files/users';
            $files[] = '../addons';
            $files[] = '../files/browsercap_cache';
            $files[] = '../files/download_cache';
            $file_perm = $this->check_file_permissions($files);
            //Check Required Moduels
            $warnings = [];
            $warnings_warn = [];
            if (ini_get('safe_mode')) {
                $warnings[] = '<div class="warnings_message">' . $this->lang['warnings_safe_mode'] . '</div>';
            }
            //CHECK session.auto-start
            if (ini_get('session.auto_start')) {
                $warnings[] = '<div class="warnings_message">PHP session.auto_start is enabled. Disable this option to continue</div>';
            }
            // CHECK MBString
            if (!extension_loaded('mbstring') && $config['controlpanel_mbstring_enabled'] == 1) {
                $warnings[] = '<div class="warnings_message">' . $this->lang['warnings_mb_convert_encoding'] . '</div>';
            }
            if (!extension_loaded('curl')) {
                $warnings[] = '<div class="warnings_message">' . $this->lang['curl_not_enabled'] . '</div>';
            }
            if (!extension_loaded('zip')) {
                $warnings[] = '<div class="warnings_message">' . $this->lang['warnings_php_zip'] . '</div>';
            }
            if (!extension_loaded('gd')) {
                $warnings[] = '<div class="warnings_message">' . $this->lang['warnings_php_gd'] . '</div>';
            }
            if (!extension_loaded('exif')) {
                $warnings[] = '<div class="warnings_message">' . $this->lang['warnings_php_exif'] . '</div>';
            }
            if (!extension_loaded('zip')) {
                $warnings[] = '<div class="warnings_message">' . $this->lang['warnings_php_zip'] . '</div>';
            }
            $gdinfo = gd_info();
            if (!isset($gdinfo['FreeType Support']) || !$gdinfo['FreeType Support']) {
                $warnings[] = '<div class="warnings_message">' . $this->lang['warnings_php_freetype'] . '</div>';
            }
            // CHECK OpenSSL
            if (!in_array('openssl', get_loaded_extensions())) {
                $warnings[] = '<div class="warnings_message">' . $this->lang['warnings_openssl'] . '</div>';
            }
            // CHECK mod_rewrite AND htaccess file

            if (defined('APACHE_GET_MODULES')) {
                if (!in_array('mod_rewrite', apache_get_modules())) {
                    $warnings_warn[] =  '<div class="warnings_message">' . $this->lang['warnings_mod_rewrite'] . '</div>';
                }
                if (!file_exists($config['basepath'] . '/.htaccess')) {
                    $warnings_warn[] = '<div class="warnings_message">' . $this->lang['warnings_htaccess'] . '</div>';
                }
            }
            $physical = $this->get_base_path();
            //echo $physical;
            if (strpos($physical, ' ') !== false) {
                $warnings[] = '<div class="warnings_message">' . $this->lang['file_path_contains_a_space'] . '</div>';
            }

            if ($file_perm === true && count($warnings) == 0) {
                echo '<br /><strong>' . $this->lang['install_all_correct'] . '</strong> <form action="index.php?step=3" method="post"><input type="submit" name="Submit" value="' . $this->lang['install_continue'] . '" /></form>';
            } else {
                if ($file_perm !== true) {
                    echo '<br /><strong>' . $this->lang['install_please_fix'] . '</strong>';
                } elseif (count($warnings) > 0) {
                    foreach ($warnings as $warn) {
                        echo $warn;
                    }
                    echo '<br /><strong>' . $this->lang['install_please_fix'] . '</strong>';
                }
            }
        } else {
            echo '<span style="color: red"><strong>' . $this->lang['install_version_warn'] . '</strong></span><br />';
            echo '<strong>' . $this->lang['install_php_version'];
            print phpversion();
            echo $this->lang['install_php_required'] . ' ' . $check_for_version . '</strong>';
        }
    }
    public function run_installer_3()
    {
        $this->load_lang($_SESSION['or_install_lang']);
        $this->set_version();
        echo '<form name="install_type_form" method="post" action="index.php?step=4">';
        echo $this->lang['install_select_type'] . ' <select name="install_type">';
        // install options\
        echo '<option value="install_300">' . $this->lang['install_new'] . ' ' . $this->version_number . '</option>';
        echo '<option value="upgrade_200">' . $this->lang['upgrade_200'] . '</option>';
        //echo'<option value="upgrade_115">' . $this->lang['upgrade_115'] . '</option>';
        echo '<option value="move">' . $this->lang['move'] . '</option>';

        echo '</select>
      <input type="submit" name="Submit" value="Submit" />
      </form>';
    }
}
