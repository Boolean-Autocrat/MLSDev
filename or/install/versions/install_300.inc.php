<?php

// just so we know it is broken
class version extends installer
{
    public function load_prev_settings()
    {
    }
    public function create_tables()
    {
        // this is the setup for the ADODB library
        include_once dirname(__FILE__) . '/../../vendor/adodb/adodb-php/adodb.inc.php';

        $conn = ADONewConnection($_SESSION['db_type']);
        $conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, 'utf8');
        $conn->connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']);

        $config['table_prefix'] = $_SESSION['table_prefix'] . $_SESSION['or_install_lang'] . '_';
        $config['table_prefix_no_lang'] = $_SESSION['table_prefix'];
        if (!isset($_SESSION['autoinstall'])) {
            echo $this->lang['install_populate_db'] . '<br />';
        }
        // include_once('../include/common.php');
        // removed prefixes because there is already going to be a prefix added.
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . "controlpanel (
        controlpanel_version CHAR VARYING(15) NOT NULL ,
        controlpanel_basepath CHAR VARYING(150) NOT NULL,
        controlpanel_baseurl CHAR VARYING(150) NOT NULL,
        controlpanel_admin_name CHAR VARYING(45) NOT NULL,
        controlpanel_admin_email CHAR VARYING(45) NOT NULL,
        controlpanel_company_name CHAR VARYING(45),
        controlpanel_company_location CHAR VARYING(45),
        controlpanel_company_logo CHAR VARYING(45),
        controlpanel_url_style INT2 NOT NULL,
        controlpanel_template CHAR VARYING(45) NOT NULL,
        controlpanel_admin_template CHAR VARYING(45) NOT NULL,
        controlpanel_listing_template CHAR VARYING(45) NOT NULL,
        controlpanel_agent_template CHAR VARYING(45) NOT NULL,
        controlpanel_search_result_template CHAR VARYING(45) NOT NULL,
        controlpanel_lang CHAR(2) NOT NULL,
        controlpanel_listings_per_page INT4 NOT NULL,
        controlpanel_num_featured_listings INT4 NOT NULL,
        controlpanel_add_linefeeds INT2 NOT NULL,
        controlpanel_strip_html INT2 NOT NULL,
        controlpanel_allowed_html_tags CHAR VARYING(45) NOT NULL,
        controlpanel_money_sign CHAR VARYING(20) NOT NULL,
        controlpanel_show_no_photo INT2 NOT NULL,
        controlpanel_number_format_style INT4 NOT NULL,
        controlpanel_money_format INT4 NOT NULL,
        controlpanel_date_format INT4 NOT NULL,
        controlpanel_max_listings_uploads INT4 NOT NULL,
        controlpanel_max_listings_upload_size INT4 NOT NULL,
        controlpanel_max_listings_upload_width INT4 NOT NULL,
        controlpanel_max_listings_upload_height INT4 NOT NULL,
        controlpanel_max_user_uploads INT4 NOT NULL,
        controlpanel_max_user_upload_size INT4 NOT NULL,
        controlpanel_max_user_upload_width INT4 NOT NULL,
        controlpanel_max_user_upload_height INT4 NOT NULL,
        controlpanel_max_vtour_uploads INT4 NOT NULL,
        controlpanel_max_vtour_upload_size INT4 NOT NULL,
        controlpanel_max_vtour_upload_width INT4 NOT NULL,
        controlpanel_allowed_upload_extensions CHAR VARYING(45) NOT NULL,
        controlpanel_make_thumbnail INT2 NOT NULL,
        controlpanel_thumbnail_width INT4 NOT NULL,
        controlpanel_thumbnail_prog CHAR VARYING(20) NOT NULL,
        controlpanel_path_to_imagemagick CHAR VARYING(255) NOT NULL,
        controlpanel_resize_img INT2 NOT NULL,
        controlpanel_jpeg_quality INT2 NOT NULL,
        controlpanel_use_expiration INT2 NOT NULL,
        controlpanel_days_until_listings_expire INT4 NOT NULL,
        controlpanel_allow_member_signup INT2 NOT NULL,
        controlpanel_allow_agent_signup INT2 NOT NULL,
        controlpanel_agent_default_active INT2 NOT NULL,
        controlpanel_agent_default_admin INT2 NOT NULL,
        controlpanel_agent_default_feature INT2 NOT NULL,
        controlpanel_agent_default_moderate INT2 NOT NULL,
        controlpanel_agent_default_logview INT2 NOT NULL,
        controlpanel_agent_default_edit_site_config INT2 NOT NULL,
        controlpanel_agent_default_edit_member_template INT2 NOT NULL,
        controlpanel_agent_default_edit_agent_template INT2 NOT NULL,
        controlpanel_agent_default_edit_listing_template INT2 NOT NULL,
        controlpanel_agent_default_canchangeexpirations INT2 NOT NULL,
        controlpanel_agent_default_editpages INT2 NOT NULL,
        controlpanel_agent_default_havevtours INT2 NOT NULL,
        controlpanel_agent_default_num_listings INT2 NOT NULL,
        controlpanel_agent_default_can_export_listings  INT2 NOT NULL,
        controlpanel_agent_default_edit_all_users INT2 NOT NULL,
        controlpanel_agent_default_edit_all_listings INT2 NOT NULL,
        controlpanel_agent_default_edit_property_classes  INT2 NOT NULL,
        controlpanel_moderate_agents INT2 NOT NULL,
        controlpanel_moderate_members INT2 NOT NULL,
        controlpanel_moderate_listings INT2 NOT NULL,
        controlpanel_export_listings INT2 NOT NULL,
        controlpanel_email_notification_of_new_users INT2 NOT NULL,
        controlpanel_email_notification_of_new_listings INT2 NOT NULL,
        controlpanel_configured_langs CHAR VARYING(100) NOT NULL,
        controlpanel_email_users_notification_of_new_listings INT2 NOT NULL,
        controlpanel_configured_show_count INT2 NOT NULL,
        controlpanel_map_type CHAR VARYING(45) NOT NULL,
        controlpanel_map_address CHAR VARYING(45) NOT NULL,
        controlpanel_map_address2 CHAR VARYING(45) NOT NULL,
        controlpanel_map_address3 CHAR VARYING(45) NOT NULL,
        controlpanel_map_address4 CHAR VARYING(45) NOT NULL,
        controlpanel_map_city CHAR VARYING(45) NOT NULL,
        controlpanel_map_state CHAR VARYING(45) NOT NULL,
        controlpanel_map_zip CHAR VARYING(45) NOT NULL,
        controlpanel_map_country CHAR VARYING(45) NOT NULL,
        controlpanel_map_latitude CHAR VARYING(45) NOT NULL,
        controlpanel_map_longitude CHAR VARYING(45) NOT NULL,
        controlpanel_number_decimals_number_fields INT2 NOT NULL,
        controlpanel_number_decimals_price_fields INT2 NOT NULL,
        controlpanel_automatic_update_check INT2 NOT NULL,
        controlpanel_search_sortby CHAR VARYING(45) NOT NULL,
        controlpanel_search_sorttype CHAR VARYING(45) NOT NULL,
        controlpanel_show_listedby_admin INT2 NOT NULL,
        controlpanel_show_next_prev_listing_page INT2 NOT NULL,
        controlpanel_seo_default_description TEXT NOT NULL,
        controlpanel_seo_default_keywords TEXT NOT NULL,
        controlpanel_seo_listing_description TEXT NOT NULL,
        controlpanel_seo_listing_keywords TEXT NOT NULL,
        controlpanel_seo_default_title TEXT NOT NULL,
        controlpanel_seo_listing_title TEXT NOT NULL,
        controlpanel_template_listing_sections TEXT NOT NULL,
        controlpanel_vtour_template CHAR VARYING(45) NOT NULL,
        controlpanel_vtour_width CHAR VARYING(45) NOT NULL,
        controlpanel_vtour_height CHAR VARYING(45) NOT NULL,
        controlpanel_vt_popup_width CHAR VARYING(45) NOT NULL,
        controlpanel_vt_popup_height CHAR VARYING(45) NOT NULL,
        controlpanel_zero_price INT2 NOT NULL,
        controlpanel_vcard_phone CHAR VARYING(45) NOT NULL,
        controlpanel_vcard_fax CHAR VARYING(45) NOT NULL,
        controlpanel_vcard_mobile CHAR VARYING(45) NOT NULL,
        controlpanel_vcard_address CHAR VARYING(45) NOT NULL,
        controlpanel_vcard_city CHAR VARYING(45) NOT NULL,
        controlpanel_vcard_state CHAR VARYING(45) NOT NULL,
        controlpanel_vcard_zip CHAR VARYING(45) NOT NULL,
        controlpanel_vcard_country CHAR VARYING(45) NOT NULL,
        controlpanel_vcard_notes CHAR VARYING(45) NOT NULL,
        controlpanel_vcard_url CHAR VARYING(45) NOT NULL,
        controlpanel_email_information_to_new_users INT2 NOT NULL,
        controlpanel_demo_mode INT2 NOT NULL,
        controlpanel_max_search_results INT4 NOT NULL,
        controlpanel_feature_list_separator CHAR VARYING(45) NOT NULL,
        controlpanel_search_list_separator CHAR VARYING(45) NOT NULL,
        controlpanel_rss_title_featured CHAR VARYING(45) NOT NULL,
        controlpanel_rss_desc_featured CHAR VARYING(255) NOT NULL,
        controlpanel_rss_listingdesc_featured TEXT NOT NULL,
        controlpanel_rss_title_lastmodified CHAR VARYING(45) NOT NULL,
        controlpanel_rss_desc_lastmodified CHAR VARYING(255) NOT NULL,
        controlpanel_rss_listingdesc_lastmodified TEXT NOT NULL,
        controlpanel_rss_limit_lastmodified INT4 NOT NULL,
        controlpanel_rss_title_latestlisting CHAR VARYING(45) NOT NULL,
        controlpanel_rss_desc_latestlisting CHAR VARYING(255) NOT NULL,
        controlpanel_rss_listingdesc_latestlisting TEXT NOT NULL,
        controlpanel_rss_limit_latestlisting INT4 NOT NULL,
        controlpanel_thumbnail_height INT4 NOT NULL,
        controlpanel_resize_thumb_by CHAR VARYING(20) NOT NULL,
        controlpanel_resize_by CHAR VARYING(20) NOT NULL,
        controlpanel_charset VARCHAR( 15 ) NOT NULL,
        controlpanel_wysiwyg_show_edit INT2 NOT NULL,
        controlpanel_textarea_short_chars INT4 NOT NULL,
        controlpanel_main_image_display_by CHAR VARYING(20) NOT NULL,
        controlpanel_main_image_width INT4 NOT NULL,
        controlpanel_main_image_height INT4 NOT NULL,
        controlpanel_number_columns INT4 NOT NULL,
        controlpanel_rss_limit_featured INT4 NOT NULL,
        controlpanel_force_decimals INT2 NOT NULL,
        controlpanel_icon_image_width INT4 NOT NULL,
        controlpanel_icon_image_height INT4 NOT NULL,
        controlpanel_max_listings_file_uploads INT4 NOT NULL,
        controlpanel_max_listings_file_upload_size INT4 NOT NULL,
        controlpanel_allowed_file_upload_extensions CHAR VARYING(255) NOT NULL,
        controlpanel_show_file_icon INT(2) NOT NULL,
        controlpanel_show_file_size INT(2) NOT NULL,
        controlpanel_file_display_option CHAR VARYING(20) NOT NULL,
        controlpanel_include_senders_ip INT(2) NOT NULL,
        controlpanel_agent_default_havefiles INT(2) NOT NULL,
        controlpanel_agent_default_haveuserfiles INT(2) NOT NULL,
        controlpanel_max_users_file_uploads INT4 NOT NULL,
        controlpanel_max_users_file_upload_size INT4 NOT NULL,
        controlpanel_show_notes_field INT2 NOT NULL,
        controlpanel_disable_referrer_check INT2 NOT NULL,
        controlpanel_seo_url_seperator CHAR VARYING(20) NOT NULL,
        controlpanel_search_step_max INT4 NOT NULL,
        controlpanel_special_search_sortby CHAR VARYING(45) NOT NULL,
        controlpanel_special_search_sorttype CHAR VARYING(45) NOT NULL,
        controlpanel_price_field CHAR VARYING(45) NOT NULL,
        controlpanel_users_per_page INT4 NOT NULL,
        controlpanel_show_admin_on_agent_list INT2 NOT NULL,
        controlpanel_agent_default_num_featuredlistings INT4 NOT NULL,
        controlpanel_use_signup_image_verification INT2 NOT NULL,
        controlpanel_site_email CHAR VARYING(45) NOT NULL,
        controlpanel_mbstring_enabled INT2 NOT NULL,
        controlpanel_require_email_verification INT2 NOT NULL,
        controlpanel_blog_requires_moderation INT2 NOT NULL,
        controlpanel_maintenance_mode INT2 NOT NULL,
        controlpanel_notification_last_timestamp TIMESTAMP NOT NULL DEFAULT now(),
        controlpanel_notify_listings_template CHAR VARYING(45) NOT NULL,
        controlpanel_twitter_consumer_secret CHAR VARYING(255) NULL,
        controlpanel_twitter_consumer_key CHAR VARYING(255) NULL,
        controlpanel_twitter_auth LONGTEXT NULL,
        controlpanel_twitter_new_listings INT(1) NOT NULL DEFAULT 0,
        controlpanel_twitter_update_listings INT(1) NOT NULL DEFAULT 0,
        controlpanel_twitter_new_blog INT(1) NOT NULL DEFAULT 0,
        controlpanel_twitter_listing_photo INT(1) NOT NULL DEFAULT 0,
        controlpanel_blog_pingback_urls LONGTEXT NULL,
        controlpanel_blogs_per_page INT4 NOT NULL DEFAULT 10,
        controlpanel_allow_pingbacks INT(1) NOT NULL DEFAULT 1,
        controlpanel_send_url_pingbacks INT(1) NOT NULL DEFAULT 1,
        controlpanel_send_service_pingbacks INT(1) NOT NULL DEFAULT 1,
        controlpanel_timezone CHAR VARYING(45) NOT NULL default 'America/New_York',
        controlpanel_default_page CHAR VARYING(45) NOT NULL default 'wysiwyg_page',
        controlpanel_banned_domains_signup LONGTEXT NULL,
        controlpanel_banned_ips_signup LONGTEXT NULL,
        controlpanel_banned_ips_site LONGTEXT NULL,
        controlpanel_rss_title_blogposts  CHAR VARYING(45) NOT NULL,
        controlpanel_rss_desc_blogposts CHAR VARYING(255) NOT NULL,
        controlpanel_rss_title_blogcomments CHAR VARYING(45) NOT NULL,
        controlpanel_rss_desc_blogcomments CHAR VARYING(255) NOT NULL,
        controlpanel_phpmailer INT2 NOT NULL DEFAULT 0,
        controlpanel_mailserver CHAR VARYING(255) NULL,
        controlpanel_mailport INT4 NOT NULL DEFAULT 25,
        controlpanel_mailuser CHAR VARYING(255) NULL,
        controlpanel_mailpass CHAR VARYING(255) NULL,
        controlpanel_agent_default_canManageAddons INT4 NOT NULL,
        controlpanel_agent_default_blogUserType INT4 NOT NULL,
        controlpanel_agent_default_edit_all_leads  INT2 NOT NULL,
        controlpanel_agent_default_edit_lead_template INT2 NOT NULL,
        controlpanel_enable_tracking INT2 NOT NULL,
        controlpanel_enable_tracking_crawlers INT2 NOT NULL,
        controlpanel_show_agent_no_photo INT2 NOT NULL,
        controlpanel_template_lead_sections TEXT NOT NULL,
        controlpanel_allow_template_change INT2 NOT NULL,
        controlpanel_allow_language_change INT2 NOT NULL,
        controlpanel_listingimages_slideshow_group_thumb INT4 NOT NULL,
        controlpanel_admin_listing_per_page INT4 NOT NULL,
        controlpanel_mobile_template CHAR VARYING(45) NOT NULL,
        controlpanel_captcha_system CHAR VARYING(45) NOT NULL,
        controlpanel_recaptcha_sitekey CHAR VARYING(255) NULL,
        controlpanel_recaptcha_secretkey CHAR VARYING(255) NULL,
        controlpanel_floor_agent CHAR VARYING(255) NULL,
        controlpanel_floor_agent_last INT4 NULL,
        controlpanel_contact_template CHAR VARYING(45) NOT NULL,
        controlpanel_user_jpeg_quality  INT2 NOT NULL,
        controlpanel_user_resize_img INT2 NOT NULL,
        controlpanel_user_resize_by CHAR VARYING(20) NOT NULL,
		    controlpanel_user_resize_thumb_by CHAR VARYING(20) NOT NULL,
		    controlpanel_user_thumbnail_width INT4 NOT NULL,
		    controlpanel_user_thumbnail_height INT4 NOT NULL,
		    controlpanel_num_popular_listings INT4 NOT NULL,
		    controlpanel_num_random_listings INT4 NOT NULL,
		    controlpanel_num_latest_listings INT4 NOT NULL,
        controlpanel_google_client_id CHAR VARYING(255) NULL,
        controlpanel_google_client_secret CHAR VARYING(255) NULL,
        PRIMARY KEY(controlpanel_version)
      ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";

        $sql_insert[] = 'CREATE TABLE IF NOT EXISTS `' . $config['table_prefix_no_lang'] . "open-realty_license` ( `license_key` varchar(155) NOT NULL default '', `hash` BLOB );";

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'addons (
        addons_version CHAR VARYING(15) NOT NULL,
        addons_name CHAR VARYING(150) NOT NULL,
        PRIMARY KEY(addons_name)
      ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] = 'CREATE TABLE  ' . $config['table_prefix_no_lang'] . 'zipdist (
        zipdist_zipcode char(5) NULL,
        zipdist_ziptype char(1) NULL,
        zipdist_cityname CHAR VARYING(64) NULL,
        zipdist_citytype char(1) NULL,
        zipdist_statename CHAR VARYING(64) NULL,
        zipdist_stateabbr char(2) NULL,
        zipdist_areacode char(3) NULL,
        zipdist_latitude decimal(9,6) NULL,
        zipdist_longitude  decimal(9,6) NULL,
        zipdist_id INT4 NOT NULL AUTO_INCREMENT,
        PRIMARY KEY  (zipdist_id)
      ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'activitylog (
          activitylog_id INT4 NOT NULL AUTO_INCREMENT,
          activitylog_log_date TIMESTAMP NOT NULL,
          userdb_id INT4 NOT NULL,
          activitylog_action TEXT NULL,
          activitylog_ip_address CHAR VARYING(15) NOT NULL,
          PRIMARY KEY(activitylog_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'agentformelements (
          agentformelements_id INT4 NOT NULL AUTO_INCREMENT,
          agentformelements_field_type CHAR VARYING(20) NOT NULL,
          agentformelements_field_name CHAR VARYING(80) NOT NULL,
          agentformelements_field_caption CHAR VARYING(80) NOT NULL,
          agentformelements_default_text TEXT NOT NULL,
          agentformelements_field_elements TEXT NOT NULL,
          agentformelements_rank INT4 NOT NULL,
          agentformelements_required CHAR(3) NOT NULL,
          agentformelements_display_priv INT4 NOT NULL,
          agentformelements_tool_tip TEXT NULL,
          PRIMARY KEY(agentformelements_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'listingsdb (
          listingsdb_id INT4 NOT NULL AUTO_INCREMENT,
          userdb_id INT4 NOT NULL,
          listingsdb_title TEXT NOT NULL,
          listing_seotitle TEXT NULL,
          listingsdb_pclass_id INT4 NOT NULL,
          listingsdb_expiration DATE NOT NULL,
          listingsdb_notes TEXT NOT NULL,
          listingsdb_creation_date DATE NOT NULL,
          listingsdb_last_modified DATETIME NOT NULL,
          listingsdb_hit_count INT4 NOT NULL,
          listingsdb_featured CHAR(3) NOT NULL,
          listingsdb_active CHAR(3) NOT NULL,
          listingsdb_mlsexport CHAR(3) NOT NULL,
          PRIMARY KEY(listingsdb_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'listingsdbelements (
          listingsdbelements_id INT4 NOT NULL AUTO_INCREMENT,
          listingsdbelements_field_name CHAR VARYING(80) NOT NULL,
          listingsdbelements_field_value TEXT NOT NULL,
          listingsdb_id INT4 NOT NULL,
          userdb_id INT4 NOT NULL,
          PRIMARY KEY(listingsdbelements_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'listingsformelements (
          listingsformelements_id INT4 NOT NULL AUTO_INCREMENT,
          listingsformelements_field_type CHAR VARYING(20) NOT NULL,
          listingsformelements_field_name CHAR VARYING(80) NOT NULL,
          listingsformelements_field_caption CHAR VARYING(80) NOT NULL,
          listingsformelements_default_text TEXT NOT NULL,
          listingsformelements_field_elements TEXT NOT NULL,
          listingsformelements_rank INT4 NOT NULL,
          listingsformelements_search_rank INT4 NOT NULL,
          listingsformelements_search_result_rank INT4 NOT NULL,
          listingsformelements_required CHAR(3) NOT NULL,
          listingsformelements_location CHAR VARYING(50) NOT NULL,
          listingsformelements_display_on_browse CHAR(3) NOT NULL,
          listingsformelements_searchable INT4 NOT NULL,
          listingsformelements_search_type CHAR VARYING(50) NULL,
          listingsformelements_search_label CHAR VARYING(50) NULL,
          listingsformelements_search_step CHAR VARYING(25) NULL,
          listingsformelements_display_priv INT4 NOT NULL,
          listingsformelements_field_length INT4 NULL,
          listingsformelements_tool_tip TEXT NULL,
          PRIMARY KEY(listingsformelements_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'listingsimages (
          listingsimages_id INT4 NOT NULL AUTO_INCREMENT,
          userdb_id INT4 NOT NULL,
          listingsimages_caption CHAR VARYING(255) NOT NULL,
          listingsimages_file_name CHAR VARYING(255) NOT NULL,
          listingsimages_thumb_file_name CHAR VARYING(255) NOT NULL,
          listingsimages_description TEXT NOT NULL,
          listingsdb_id INT4 NOT NULL,
          listingsimages_rank INT4 NOT NULL,
          listingsimages_active CHAR(3) NOT NULL,
          PRIMARY KEY(listingsimages_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] = 'CREATE TABLE  ' . $config['table_prefix'] . 'listingsfiles (
          listingsfiles_id INT4 NOT NULL AUTO_INCREMENT,
          userdb_id INT4 NOT NULL,
          listingsfiles_caption CHAR VARYING(255) NOT NULL,
          listingsfiles_file_name CHAR VARYING(255) NOT NULL,
          listingsfiles_description TEXT NOT NULL,
          listingsdb_id INT4 NOT NULL,
          listingsfiles_rank INT4 NOT NULL,
          listingsfiles_active CHAR(3) NOT NULL,
          PRIMARY KEY (listingsfiles_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $sql_insert[] = 'CREATE TABLE  ' . $config['table_prefix'] . 'usersfiles (
          usersfiles_id INT4 NOT NULL AUTO_INCREMENT,
          userdb_id INT4 NOT NULL,
          usersfiles_caption CHAR VARYING(255) NOT NULL,
          usersfiles_file_name CHAR VARYING(255) NOT NULL,
          usersfiles_description TEXT NOT NULL,
          usersfiles_rank INT4 NOT NULL,
          usersfiles_active CHAR(3) NOT NULL,
          PRIMARY KEY (usersfiles_id)
      ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'listingsvtours (
          listingsvtours_id INT4 NOT NULL AUTO_INCREMENT,
          userdb_id INT4 NOT NULL,
          listingsvtours_caption CHAR VARYING(255) NOT NULL,
          listingsvtours_file_name CHAR VARYING(255) NOT NULL,
          listingsvtours_thumb_file_name CHAR VARYING(255) NOT NULL,
          listingsvtours_description TEXT NOT NULL,
          listingsdb_id INT4 NOT NULL,
          listingsvtours_rank INT4 NOT NULL,
          listingsvtours_active CHAR(3) NOT NULL,
          PRIMARY KEY(listingsvtours_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'memberformelements (
          memberformelements_id INT4 NOT NULL AUTO_INCREMENT,
          memberformelements_field_type CHAR VARYING(20) NOT NULL,
          memberformelements_field_name CHAR VARYING(80) NOT NULL,
          memberformelements_field_caption CHAR VARYING(80) NOT NULL,
          memberformelements_default_text TEXT NOT NULL,
          memberformelements_field_elements TEXT NOT NULL,
          memberformelements_rank INT4 NOT NULL,
          memberformelements_required CHAR(3) NOT NULL,
          memberformelements_tool_tip TEXT NULL,
          PRIMARY KEY(memberformelements_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'pagesmain (
          pagesmain_id INT4 NOT NULL AUTO_INCREMENT,
          pagesmain_title TEXT NOT NULL,
          pagesmain_date CHAR VARYING(20) NOT NULL,
          pagesmain_summary CHAR VARYING(255) NOT NULL,
          pagesmain_full LONGTEXT NOT NULL,
          pagesmain_published INT2 NOT NULL,
          pagesmain_description LONGTEXT NOT NULL,
          pagesmain_keywords LONGTEXT NOT NULL,
          pagesmain_full_autosave LONGTEXT NOT NULL,
          page_seotitle TEXT NULL,
          PRIMARY KEY(pagesmain_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'userdb (
          userdb_id INT4 NOT NULL AUTO_INCREMENT,
          userdb_user_name CHAR VARYING(80) NOT NULL,
          userdb_emailaddress CHAR VARYING(80) NOT NULL,
          userdb_user_first_name CHAR VARYING(100) NOT NULL,
          userdb_user_last_name CHAR VARYING(100) NOT NULL,
          userdb_comments TEXT NOT NULL,
          userdb_user_password CHAR VARYING(255) NOT NULL,
          userdb_is_admin CHAR(3) NOT NULL,
          userdb_can_edit_site_config CHAR(3) NOT NULL,
          userdb_can_edit_member_template CHAR(3) NOT NULL,
          userdb_can_edit_agent_template CHAR(3) NOT NULL,
          userdb_can_edit_listing_template CHAR(3) NOT NULL,
          userdb_creation_date DATE NOT NULL,
          userdb_can_feature_listings CHAR(3) NOT NULL,
          userdb_can_view_logs CHAR(3) NOT NULL,
          userdb_last_modified TIMESTAMP NOT NULL,
          userdb_hit_count INT4 NOT NULL,
          userdb_can_moderate CHAR(3) NOT NULL,
          userdb_can_edit_pages CHAR(3) NOT NULL,
          userdb_can_have_vtours CHAR(3) NOT NULL,
          userdb_is_agent CHAR(3) NOT NULL,
          userdb_active CHAR(3) NOT NULL,
          userdb_limit_listings INT4 NOT NULL,
          userdb_can_edit_expiration CHAR VARYING(100) NOT NULL,
          userdb_can_export_listings CHAR VARYING(100) NOT NULL,
          userdb_can_edit_all_users CHAR(3) NOT NULL,
          userdb_can_edit_all_listings CHAR(3) NOT NULL,
          userdb_can_edit_property_classes CHAR(3) NOT NULL,
          userdb_can_have_files CHAR(3) NOT NULL,
          userdb_can_have_user_files CHAR(3) NOT NULL,
          userdb_blog_user_type INT4 NOT NULL DEFAULT 1,
          userdb_can_manage_addons CHAR(3) NOT NULL,
          userdb_rank INT4 NOT NULL,
          userdb_featuredlistinglimit INT4 NOT NULL,
          userdb_email_verified CHAR(3) NOT NULL,
          userdb_can_edit_all_leads CHAR(3) NOT NULL,
          userdb_can_edit_lead_template CHAR(3) NOT NULL,
          userdb_send_notifications_to_floor INT4 NOT NULL default 0,
          userdb_verification_hash CHAR VARYING(32) NULL,
          PRIMARY KEY(userdb_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'userdbelements (
          userdbelements_id INT4 NOT NULL AUTO_INCREMENT,
          userdbelements_field_name CHAR VARYING(80) NOT NULL,
          userdbelements_field_value TEXT NOT NULL,
          userdb_id INT4 NOT NULL,
          PRIMARY KEY(userdbelements_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'userfavoritelistings (
          userfavoritelistings_id INT4 NOT NULL AUTO_INCREMENT,
          userdb_id INT4 NOT NULL,
          listingsdb_id INT4 NOT NULL,
          PRIMARY KEY(userfavoritelistings_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'userimages (
          userimages_id INT4 NOT NULL AUTO_INCREMENT,
          userdb_id INT4 NOT NULL,
          userimages_caption CHAR VARYING(255) NOT NULL,
          userimages_file_name CHAR VARYING(255) NOT NULL,
          userimages_thumb_file_name CHAR VARYING(255) NOT NULL,
          userimages_description TEXT NOT NULL,
          userimages_rank INT4 NOT NULL,
          userimages_active CHAR(3) NOT NULL,
          PRIMARY KEY(userimages_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'usersavedsearches (
          usersavedsearches_id INT4 NOT NULL AUTO_INCREMENT,
          userdb_id INT4 NOT NULL,
          usersavedsearches_title CHAR VARYING(255) NOT NULL,
          usersavedsearches_query_string TEXT NOT NULL,
          usersavedsearches_last_viewed TIMESTAMP NOT NULL,
          usersavedsearches_new_listings INT2 NOT NULL,
          usersavedsearches_notify CHAR VARYING(3) NOT NULL,
          PRIMARY KEY(usersavedsearches_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'forgot (
          forgot_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
          forgot_rand INTEGER UNSIGNED NOT NULL,
          forgot_email CHAR VARYING(45) NOT NULL,
          forgot_time TIMESTAMP NOT NULL,
          PRIMARY KEY(forgot_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        // Create Tables for property classing
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'classformelements (
                  classformelements_id INT4 NOT NULL AUTO_INCREMENT,
                  class_id INT4 NOT NULL,
                  listingsformelements_id INT4 NOT NULL,
                  PRIMARY KEY(classformelements_id)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'class (
                  class_id INT4 NOT NULL AUTO_INCREMENT,
                  class_name CHAR VARYING(80) NOT NULL,
                  class_rank INT2 NOT NULL,
                  PRIMARY KEY(class_id)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'blogmain (
          blogmain_id INT4 NOT NULL AUTO_INCREMENT,
          userdb_id INT4 NOT NULL,
          blogmain_title TEXT NOT NULL,
          blog_seotitle TEXT NULL,
          blogmain_date CHAR VARYING(20) NOT NULL,
          blogmain_full LONGTEXT NOT NULL,
          blogmain_description LONGTEXT NOT NULL,
          blogmain_keywords LONGTEXT NOT NULL,
          blogmain_published INT4 NOT NULL,
          blogmain_full_autosave LONGTEXT NOT NULL,
          PRIMARY KEY(blogmain_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'blogcomments (
          blogcomments_id INT4 NOT NULL AUTO_INCREMENT,
          blogmain_id INT4 NOT NULL,
          userdb_id INT4 NULL,
          blogcomments_timestamp INT4 NOT NULL,
          blogcomments_text LONGTEXT NOT NULL,
          blogcomments_moderated BOOLEAN,
          PRIMARY KEY(blogcomments_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'blogpingbacks (
          blogpingback_id INT4 NOT NULL AUTO_INCREMENT,
          blogmain_id INT4 NOT NULL,
          blogpingback_timestamp INT4 NOT NULL,
          blogpingback_source CHAR VARYING(2000) NOT NULL,
          blogcomments_moderated BOOLEAN,
          PRIMARY KEY(blogpingback_id)
        ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $sql_insert[] =  'CREATE TABLE ' . $config['table_prefix'] . 'blogcategory (
                  category_id INT4 NOT NULL AUTO_INCREMENT,
                  category_name CHAR VARYING(80) NOT NULL,
                  category_seoname CHAR VARYING(80) NOT NULL,
                  category_description LONGTEXT NULL,
                  category_rank INT2 NOT NULL,
                  parent_id INT4 NULL default 0,
                  PRIMARY KEY(category_id)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $sql_insert[] =  'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'blogcategory_relation (
                  relation_id INT4 NOT NULL AUTO_INCREMENT,
                  category_id INT4 NOT NULL,
                  blogmain_id INT4 NOT NULL,
                  PRIMARY KEY(relation_id),
                  INDEX blot_cat_rel (category_id,blogmain_id)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';

        $sql_insert[] =  'CREATE TABLE ' . $config['table_prefix'] . 'blogtags (
                  tag_id INT4 NOT NULL AUTO_INCREMENT,
                  tag_name CHAR VARYING(80) NOT NULL,
                  tag_seoname CHAR VARYING(80) NOT NULL,
                  tag_description LONGTEXT NULL,
                  PRIMARY KEY(tag_id)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $sql_insert[] =  'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'blogtag_relation (
                  relation_id INT4 NOT NULL AUTO_INCREMENT,
                  tag_id INT4 NOT NULL,
                  blogmain_id INT4 NOT NULL,
                  PRIMARY KEY(relation_id),
                  INDEX blot_tag_rel (tag_id,blogmain_id)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'seouri (
                  `action` varchar(25)  NOT NULL,
                  `slug` varchar(25)  NOT NULL,
                  `uri` varchar(255)  NOT NULL,
                  `seouri_id` INT(4)  NOT NULL AUTO_INCREMENT,
                  PRIMARY KEY (`seouri_id`),
                  INDEX `slug_uidx`(`slug`(5))
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . "feedbackdb (
                  `feedbackdb_id` int(11) NOT NULL auto_increment,
                  `userdb_id` int(11) NOT NULL default '0',
                  `feedbackdb_member_userdb_id` int(11) NOT NULL,
                  `feedbackdb_notes` text NOT NULL,
                  `feedbackdb_creation_date` datetime NOT NULL,
                  `feedbackdb_last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
                  `feedbackdb_last_modified_by` int(11) NOT NULL default '0',
                  `listingdb_id` int(11) default NULL,
                  `feedbackdb_status` tinyint(1) NOT NULL default '0',
                  `feedbackdb_priority` varchar(20) NOT NULL default 'Normal',
                  PRIMARY KEY  (`feedbackdb_id`)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . "feedbackdbelements (
                  `feedbackdbelements_id` int(11) NOT NULL auto_increment,
                  `feedbackdbelements_field_name` varchar(20) NOT NULL default '',
                  `feedbackdbelements_field_value` text NOT NULL,
                  `feedbackdb_id` int(11) NOT NULL default '0',
                  `userdb_id` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`feedbackdbelements_id`)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . "feedbackformelements (
                  `feedbackformelements_id` int(11) NOT NULL auto_increment,
                  `feedbackformelements_field_type` varchar(20) NOT NULL default '',
                  `feedbackformelements_field_name` varchar(20) NOT NULL default '',
                  `feedbackformelements_field_caption` varchar(80) NOT NULL default '',
                  `feedbackformelements_default_text` text NOT NULL,
                  `feedbackformelements_field_elements` text NOT NULL,
                  `feedbackformelements_rank` int(11) NOT NULL default '0',
                  `feedbackformelements_required` char(3) NOT NULL default 'No',
                  `feedbackformelements_location` varchar(15) NOT NULL default '',
                  `feedbackformelements_tool_tip` text NOT NULL,
                  PRIMARY KEY  (`feedbackformelements_id`)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . "tracking (
                  `tracking_id` int(11) NOT NULL auto_increment,
                  `tracking_timestamp` int(11) NOT NULL,
                  `userdb_id`  int(11) NOT NULL default '0',
                  `tracking_ip` varchar(20) NOT NULL default '',
                  `tracking_referal` varchar(255) NOT NULL default '',
                  `tracking_link_type` varchar(20) NOT NULL default '',
                  `tracking_link_type_id` int(11) NOT NULL default '0',
                  `tracking_link_url` varchar(255) NOT NULL default '',
                  `tracking_agentstring` varchar(255) NOT NULL default '',
                  `tracking_browser` varchar(15) NOT NULL default '',
                  `tracking_browserversion` varchar(5) NOT NULL default '',
                  `tracking_os` varchar(10) NOT NULL default '',
                  `tracking_country` varchar(3) NOT NULL default '',
                  `tracking_city` varchar(255) NOT NULL default '',
                  `tracking_loadtime` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`tracking_id`),
                  INDEX idx_broswer (tracking_os,tracking_browser,tracking_browserversion),
                  INDEX idx_location (tracking_country,tracking_city),
                  INDEX idx_user (userdb_id),
                  INDEX idx_ip (tracking_ip),
                  INDEX idx_timestamp (tracking_timestamp),
                  INDEX idx_loadtime (tracking_loadtime)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . "menu (
                  `menu_id` int(11) NOT NULL auto_increment,
                  `menu_name` varchar(25) NOT NULL default '',
                  PRIMARY KEY  (`menu_id`)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . "menu_items (
                  `item_id` int(11) NOT NULL auto_increment,
                  `parent_id` int(11) NOT NULL default '0',
                  `menu_id`  int(11) NOT NULL default '0',
                  `item_name` varchar(255) NOT NULL default '',
                  `item_order`  int(11) NOT NULL default '0',
                  `item_type`  int(11) NOT NULL default '0',
                  `item_value` varchar(255) NOT NULL default '',
                  `item_target` varchar(255) NOT NULL default '_self',
                  `item_class` varchar(255) NOT NULL default '',
                  `visible_guest`  BOOL NOT NULL default '0',
                  `visible_member`  BOOL NOT NULL default '0',
                  `visible_agent`  BOOL NOT NULL default '0',
                  `visible_admin`  BOOL NOT NULL default '0',
                  PRIMARY KEY  (`item_id`),
                  INDEX idx_menu (menu_id,item_id,parent_id,item_order)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . "sessions(
                      sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
	                    expiry DATETIME NOT NULL ,
                      expireref VARCHAR( 250 ) DEFAULT '',
                      created DATETIME NOT NULL ,
                      modified DATETIME NOT NULL ,
                      sessdata LONGTEXT,
                      PRIMARY KEY ( sesskey ) ,
                      INDEX sess_expiry( expiry ),
                      INDEX sess_expireref( expireref )
                    ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'auth_tokens (
                      token_id INT4 NOT NULL AUTO_INCREMENT,
                      selector CHAR VARYING(255) NOT NULL,
                      validator CHAR VARYING(255) NOT NULL,
                      userdb_id INT4 NOT NULL,
                      expires DATETIME NOT NULL,
                      PRIMARY KEY(token_id)
                    )';

        foreach ($sql_insert as $elementIndexValue => $elementContents) {
            $recordSet = $conn->Execute($elementContents);
            if ($recordSet === false) {
                if ($_SESSION['devel_mode'] == 'no') {
                    die("<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />");
                } else {
                    echo "<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />";
                }
            }
        }
        if (!isset($_SESSION['autoinstall'])) {
            echo 'Tables Created<br />';
        }
    }

    public function update_tables()
    {
    }

    public function create_index()
    {
        // this is the setup for the ADODB library
        include_once dirname(__FILE__) . '/../../vendor/adodb/adodb-php/adodb.inc.php';

        $conn = ADONewConnection($_SESSION['db_type']);
        $conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, 'utf8');
        $conn->connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']);

        $config['table_prefix'] = $_SESSION['table_prefix'] . $_SESSION['or_install_lang'] . '_';
        $config['table_prefix_no_lang'] = $_SESSION['table_prefix'];

        $sql_insert[] = 'CREATE INDEX idx_user_name ON ' . $config['table_prefix'] . 'userdb (userdb_user_name);';
        $sql_insert[] = 'CREATE INDEX idx_active ON ' . $config['table_prefix'] . 'listingsdb (listingsdb_active);';
        $sql_insert[] = 'CREATE INDEX idx_user ON ' . $config['table_prefix'] . 'listingsdb (userdb_id);';
        $sql_insert[] = 'CREATE INDEX idx_name ON ' . $config['table_prefix'] . 'listingsdbelements (listingsdbelements_field_name);';
        // CHANGED: blob or text fields can only index a max of 255 (mysql) and you have to specify
        if ($_SESSION['db_type'] == 'mysqli') {
            $sql_insert[] = 'CREATE INDEX idx_value ON ' . $config['table_prefix'] . 'listingsdbelements (listingsdbelements_field_value(255));';
        } else {
            $sql_insert[] = 'CREATE INDEX idx_value ON ' . $config['table_prefix'] . 'listingsdbelements (listingsdbelements_field_value);';
        }
        $sql_insert[] = 'CREATE INDEX idx_images_listing_id ON ' . $config['table_prefix'] . 'listingsimages (listingsdb_id);';
        $sql_insert[] = 'CREATE INDEX idx_searchable ON ' . $config['table_prefix'] . 'listingsformelements (listingsformelements_searchable);';
        $sql_insert[] = 'CREATE INDEX idx_mlsexport ON ' . $config['table_prefix'] . 'listingsdb (listingsdb_mlsexport);';
        $sql_insert[] = 'CREATE INDEX idx_listing_id ON ' . $config['table_prefix'] . 'listingsdbelements (listingsdb_id);';
        $sql_insert[] = 'CREATE INDEX idx_field_type ON ' . $config['table_prefix'] . 'listingsformelements (listingsformelements_field_type);';
        $sql_insert[] = 'CREATE INDEX idx_browse ON ' . $config['table_prefix'] . 'listingsformelements (listingsformelements_display_on_browse);';
        $sql_insert[] = 'CREATE INDEX idx_field_name ON ' . $config['table_prefix'] . 'listingsformelements (listingsformelements_field_name);';
        $sql_insert[] = 'CREATE INDEX idx_rank ON ' . $config['table_prefix'] . 'listingsformelements (listingsformelements_rank);';
        $sql_insert[] = 'CREATE INDEX idx_search_rank ON ' . $config['table_prefix'] . 'listingsformelements (listingsformelements_search_rank);';
        $sql_insert[] = 'CREATE INDEX idx_images_rank ON ' . $config['table_prefix'] . 'listingsimages (listingsimages_rank);';
        $sql_insert[] = 'CREATE INDEX idx_forgot_email ON ' . $config['table_prefix_no_lang'] . 'forgot (forgot_email);';

        $sql_insert[] = 'CREATE INDEX idx_classformelements_class_id ON ' . $config['table_prefix_no_lang'] . 'classformelements (class_id);';
        $sql_insert[] = 'CREATE INDEX idx_classformelements_listingsformelements_id ON ' . $config['table_prefix_no_lang'] . 'classformelements (listingsformelements_id);';

        //$sql_insert[] = "CREATE INDEX idx_classlistingsdb_class_id ON " . $config['table_prefix_no_lang'] . "classlistingsdb (class_id);";

        $sql_insert[] = 'CREATE INDEX idx_class_rank ON ' . $config['table_prefix'] . 'class (class_rank);';
        //Add indexes for userdbelements tables
        // CHANGED: blob or text fields can only index a max of 255 (mysql) and you have to specify
        if ($_SESSION['db_type'] == 'mysqli') {
            $sql_insert[] = 'CREATE INDEX idx_user_field_value ON ' . $config['table_prefix'] . 'userdbelements (userdbelements_field_value(255));';
        } else {
            $sql_insert[] = 'CREATE INDEX idx_user_field_value ON ' . $config['table_prefix'] . 'userdbelements (userdbelements_field_value);';
        }
        $sql_insert[] = 'CREATE INDEX idx_user_field_name ON ' . $config['table_prefix'] . 'userdbelements (userdbelements_field_name);';
        $sql_insert[] = 'CREATE INDEX idx_userdb_userid ON ' . $config['table_prefix'] . 'userdbelements (userdb_id);';
        $sql_insert[] = 'CREATE INDEX idx_listfieldmashup  ON ' . $config['table_prefix'] . 'listingsdb (listingsdb_id ,listingsdb_pclass_id,listingsdb_active,userdb_id);';
        $sql_insert[] = 'CREATE INDEX idx_fieldmashup  ON ' . $config['table_prefix'] . 'listingsdbelements (listingsdbelements_field_name,listingsdb_id);';

        //3.1
        $sql_insert[] = 'CREATE INDEX idx_listingsdb_pclass_id ON ' . $config['table_prefix'] . 'listingsdb (listingsdb_pclass_id);';
        //3.2.2
        $sql_insert[] = 'CREATE INDEX idx_listingsimages_file_name ON ' . $config['table_prefix'] . 'listingsimages(listingsimages_file_name);';
        $sql_insert[] = 'CREATE INDEX idx_listingsfiles_file_name ON ' . $config['table_prefix'] . 'listingsfiles(listingsfiles_file_name);';
        $sql_insert[] = 'CREATE INDEX idx_listingsvtours_file_name ON ' . $config['table_prefix'] . 'listingsvtours(listingsvtours_file_name);';
        $sql_insert[] = 'CREATE INDEX idx_userimages_file_name ON ' . $config['table_prefix'] . 'userimages(userimages_file_name);';
        $sql_insert[] = 'CREATE INDEX idx_usersfiles_file_name ON ' . $config['table_prefix'] . 'usersfiles(usersfiles_file_name);';
        // ADDED foreach to run through array with errorchecking to see if something went wrong
        foreach ($sql_insert as $elementIndexValue => $elementContents) {
            $recordSet = $conn->Execute($elementContents);
            if ($recordSet === false) {
                if ($_SESSION['devel_mode'] == 'no') {
                    die("<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />");
                } else {
                    echo "<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />";
                }
            }
        }
        if (!isset($_SESSION['autoinstall'])) {
            echo 'Indexes Created<br />';
        }
    }

    public function password_gen($len = 15)
    {
        $chars = '0123456789aeiouyAEIOUYbdghjmnpqrstvzBDGHJLMNPQRSTVWXZ!@#$%^&*()_+`-_[]{}\<>,./?';
        $password = '';
        $alt = time() % 2;
        for ($i = 0; $i < $len; $i++) {
            $password .= $chars[(rand() % strlen($chars))];
        }
        return $password;
    }

    public function insert_values()
    {
        // this is the setup for the ADODB library
        $this->set_version();
        $default_email = 'changeme@default.com';
        if (isset($_SESSION['default_email'])) {
            $default_email = $_SESSION['default_email'];
        }
        include_once dirname(__FILE__) . '/../../vendor/adodb/adodb-php/adodb.inc.php';

        $conn = ADONewConnection($_SESSION['db_type']);
        $conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, 'utf8');
        $conn->connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']);

        $config['table_prefix'] = $_SESSION['table_prefix'] . $_SESSION['or_install_lang'] . '_';
        $config['table_prefix_no_lang'] = $_SESSION['table_prefix'];
        // Insert Data
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "pagesmain (pagesmain_id, pagesmain_title, pagesmain_date, pagesmain_summary, pagesmain_full, pagesmain_published ,pagesmain_description,pagesmain_keywords,page_seotitle,pagesmain_full_autosave) 
                    VALUES (1, 'Welcome', '" . time() . "', 'Welcome Page', '
    <div>
      <h2>Open-Realty v3</h2>
      <p>Open-Realty is a open source, web-based, real estate listing Content Management System (CMS). Designed to be flexible, powerful, MLS-connectable, easy to install and maintain, Open-Realty powers millions of real estate web pages worldwide and has been in active use and development since 2003.</p>
      {featured_listings_horizontal}
      <h3>Features:</h3>
      <ul>
        <li>Written in PHP and javascript for compatibility and ease of third party development.</li>
        <li>Integrated add-on system framework and program API allows developers to connect, extend, and alter Open-Realty\'s capabilities while maximizing upgrade compatibility with newer versions.</li>
        <li>All dynamically generated site-visitor content validates to W3C web standards.</li>
        <li>Graphical, easy to use site administration interface.</li>
        <li>Built-in WYSIWYG page editor for creating and modifying the content of key pages, including this one!</li>
        <li>Built-in Blog system allows for painless rich content creation.</li>
        <li>Property information is easily updated. No special skills are required to add, delete, or modify listings.</li>
        <li>Upload photos via a web browser. If photos are not available for a listing after creation, a default photo not available image is automatically displayed.</li>
        <li>Automatic thumbnail creation. Smaller versions of any uploaded photos are created using the industry standard <a href=\"http://www.libgd.org\" title=\"The GD Graphics Library home page\">GD Graphics Library</a>.</li>
        <li>Optional Search Engine Optimized (SEO) page links, dynamically generated site map, and other desirable and configurable SEO features.</li>
        <li>Secured access. Only the site Administrator or delegated Agents can modify or add listings and content.</li>
        <li>Showcase special or exceptional properties as &quot;Featured&quot; listings.</li>
        <li>Search and browse properties according to a user-definable criteria.</li>
        <li>Configurable search forms and fields -- All search forms and fields in Open-Realty are completely definable to meet virtually any needs.</li>
        <li>Site visitors can easily elect to become Members to save searches and be informed of new listings that would interest them.</li>
        <li>HTML/CSS/jQuery template system -- Web Designers can produce a sophisticated functional site template without any knowledge of PHP.</li>
        <li>Optional one-click upgrade system helps keep your software up to date.</li>
        <li>Many, many, many more features.</li>
      </ul>
      <p>Open-Realty utilizes a W3C standards compliant HTML &amp; CSS template system. Designing a new template from scratch or incorporating the look of an existing web site is greatly simplified.</p>
    </div>
    
    <div class=\"space_10\">&nbsp;</div>
    <!--  Latest, Popular and Random listings -->
    <div>
       {latest_listings_horizontal_class_1}
       <div class=\"space_10\">&nbsp;</div>
       {popular_listings_horizontal}
    </div>
    <div class=\"space_10\">&nbsp;</div>
    <p>{random_listings_horizontal} <!--  end Latest, Popular and Random listings --></p>
    <div class=\"space_10\">&nbsp;</div>
    <!--  Statistics block -->

    <div id=\"listingstats\">
    <div class=\"listingstats_header\"><span class=\"stat_caption\">Listing statistics for:</span> <script type=\"text/javascript\">
              <!--
              var currentTime = new Date()
              var month = currentTime.getMonth() + 1
              var day = currentTime.getDate()
              var year = currentTime.getFullYear()
              document.write(month + \"/\" + day + \"/\" + year)
              //-->
            </script></div>

    <div id=\"mini-tabs\" class=\"ui-tabs-nav\">
    <ul>
      <li><a href=\"#listing_stats_data_all\" id=\"tab_stats_{pclass_id}\">All Listings</a></li>
      {foreach_pclass_block}
      <li><a href=\"#listing_stats_data_{pclass_id}\" id=\"tab_stats_{pclass_id}\">{pclass_name}</a></li>
      {/foreach_pclass_block}
    </ul>


    <div id=\"listing_stats_data_all\"><span class=\"stat_caption\">Average price:</span> {listing_stat_avg_field_price_value}<br />
    <span class=\"stat_caption\">Median price:</span> {listing_stat_median_field_price_value}<br />
    <span class=\"stat_caption\">Highest price:</span> {listing_stat_max_field_price_value}<br />
    <span class=\"stat_caption\">Lowest price:</span> {listing_stat_min_field_price_value}<br />
    <span class=\"stat_caption\">Average Square Feet:</span> {listing_stat_avg_field_sq_feet_value}</div>
    {foreach_pclass_block}

    <div id=\"listing_stats_data_{pclass_id}\"><span class=\"stat_caption\">Total {pclass_name} Listings:</span> {active_listing_count_pclass_{pclass_id}}<br />
    <span class=\"stat_caption\">Average price:</span> {listing_stat_avg_field_price_value_pclass_{pclass_id}}<br />
    <span class=\"stat_caption\">Median price:</span> {listing_stat_median_field_price_value_pclass_{pclass_id}}<br />
    <span class=\"stat_caption\">Highest price:</span> {listing_stat_max_field_price_value_pclass_{pclass_id}}<br />
    <span class=\"stat_caption\">Lowest price:</span> {listing_stat_min_field_price_value_pclass_{pclass_id}}<br />
    <span class=\"stat_caption\">Average Square Feet:</span> {listing_stat_avg_field_sq_feet_value_pclass_{pclass_id}}</div>
    {/foreach_pclass_block} 
    </div>
    <script type=\"text/javascript\">
            $(document).ready(function() {
              $(\"#mini-tabs\").tabs();
            });
          </script></div>
    <!--  End Statistics block -->
    
    <div class=\"space_10\">&nbsp;</div>', 1,'','','index','');";

        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "pagesmain (pagesmain_id, pagesmain_title, pagesmain_date, pagesmain_summary, pagesmain_full, pagesmain_published,pagesmain_description,pagesmain_keywords,page_seotitle,pagesmain_full_autosave) VALUES (2, 'Contact Us', '" . time() . "', 'contact us page', '<H3>Contact Open-Realty, Inc.</h3><br /><strong>You can contact Us in a number of ways.</strong> \r\n<P></p>\r\n<P>By Phone (07) 00000000<br />By Email <A href=\"mailto:admin@here.com\">admin@here.com</a><br />By Fax (00) 00000000<br />By Mail 100 Main Street, Anytown, QLD 0000</p>\r\n<P>We look forward to hearing from you!</p><br /><br />', 1,'','','contact_us','');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "pagesmain (pagesmain_id, pagesmain_title, pagesmain_date, pagesmain_summary, pagesmain_full, pagesmain_published,pagesmain_description,pagesmain_keywords,page_seotitle,pagesmain_full_autosave) VALUES (3, 'About Us', '" . time() . "', 'About Us Page', '<FONT size=6>About Us</font><br /><br />This is the page where you tell clients about your company ', 1,'','','about_us','');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "pagesmain (pagesmain_id, pagesmain_title, pagesmain_date, pagesmain_summary, pagesmain_full, pagesmain_published,pagesmain_description,pagesmain_keywords,page_seotitle,pagesmain_full_autosave) VALUES (4, 'Legal Page', '" . time() . "', 'Legal Page', ' <p><font size=\"4\"><strong>Legal Disclaimer</strong></font></p><p align=\"center\"><strong>Use of this legal page is not suggested or reccomended., you should consult your lawyer to get proper legal disclaimers for your state/country.</strong> </p> <br />    <strong><i>Information Not Warranted or Guaranteed:</i></strong><br />     The official {company_name} website and all pages linked to it or from it, are PROVIDED ON AN \"AS IS, AS AVAILABLE\" BASIS. <span style=\"text-transform: uppercase;\">{company_name}</span> MAKES NO WARRANTIES, EXPRESSED OR IMPLIED, INCLUDING, WITHOUT LIMITATION, THOSE OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE, WITH RESPECT TO ANY INFORMATION OR USE OF INFORMATION CONTAINED IN THE WEBSITE, OR LINKED FROM IT OR TO IT.<br /><br />{company_name} does not warrant or guarantee the accuracy, adequacy, quality, currentness, completeness, or suitability of any information for any purpose; that any information will be free of infection from viruses, worms, Trojan horses or other destructive contamination; that the information presented will not be objectionable to some individuals or that this service will remain uninterrupted.<br /> <br /> <i><strong>No Liability:</strong></i><br /> {company_name}, its agents or employees shall not be held liable to anyone for any errors, omissions or inaccuracies under any circumstances. The entire risk for utilizing the information contained on this site or linked to this site rests solely with the users of this site.', 1,'','','legal','');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix_no_lang'] . "controlpanel VALUES ('" . $this->version_number . "', '" . trim($_SESSION['basepath']) . "',
       '" . trim($_SESSION['baseurl']) . "', 'Administrator', '" . trim($default_email) . "', NULL, NULL, 'title.jpg', 1, '" . $_SESSION['template'] . "', 'material',
       'listing_detail_default.html', 'view_user_default.html', 'search_result_default.html', '" . $_SESSION['or_install_lang'] . "', 10, 4, 0, 1,
       '<a><strong><i><u><br />', '$', 1, 0, 1, 1, 10, 500000, 700,700, 5, 500000, 600,750, 15, 5000000, 3000, 'jpg,gif,png', 1, 100, 'gd',
       '/usr/X11R6/bin/convert',1,75, 0, 365, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,-1,0, 0, 0,0,0,0, 0, 0, 1, 1,
       '" . $_SESSION['or_install_lang'] . "',1,1,'google_us','address','','','','city',
       'state','zip','','latitude','longitude',0,0,1,'listingsdb_id','ASC',0,0,'','','','','Open-Realty v3','Open-Realty v3',
       'headline,top_left,top_right,center,feature1,feature2,bottom_left,bottom_right','vtour_default.html','400','250','800','480','0','','','','','','','',
       '','','',0,0,0,'<br />','<br />','Featured Listing Feed','RSS feed of our featured listings',
       '<table><tr><td>{image_thumb_fullurl_1}</td><td>{listing_field_full_desc_rawvalue}</td></tr></table>','Last Modified Listing Feed',
       'RSS feed of our last modified listings','<table><tr><td>{image_thumb_fullurl_1}</td><td>{listing_field_full_desc_rawvalue}</td></tr></table>','50','Latest Listing Feed',
       'RSS feed of our latest listings','<table><tr><td>{image_thumb_fullurl_1}</td><td>{listing_field_full_desc_rawvalue}</td></tr></table>','50','100','width',
       'width','UTF-8','1','100','width','500','700','4','50','0','16','16','7','2097152','jpg,gif,png,pdf,doc,swf,avi,mov,mpg,zip,sbd,stc,std,sti,stw,svw,sxc,sxd,sxg,sxi,sxm',
       '1','1','both','0','0','0','7','2097152','1','0','-','100','none','DESC','price',5,'0','-1','0','','0','0','0','0',CURRENT_TIMESTAMP,
       'notify_listings_default.html',null,null,'',0,0,0,0,'http://blogsearch.google.com/ping/RPC2
http://api.moreover.com/ping
http://ping.weblogalot.com/rpc.php', 10, 1, 1, 1,'America/New_York','wysiwyg_page','','','','Rece`nt Blog Posts','RSS Feed of our Recent Blog Posts',
'Recent Blog Comments','RSS Feed of our Recent Blog Comments',0,'',25,'','',0,3,0,0,0,0,1,'headline,top_left,top_right,center,bottom_left,bottom_right',0,0,4,10,'mobile','securimage',null,null,null,null,'contact_agent_default.html',
    75,1,'height','height',80,100,4,4,4,'','')";

        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "userdb VALUES (1,'admin','changeme@default.com','Default','Admin','','\$2y\$10\$TEtBw1IgBa9FbZ5FEHHhY.kRqAoQeGGkbbLUjP1M4tTpGAKapZbK6','yes','yes','yes','yes','yes','2002-07-01','yes','yes','2002-07-01 22:38:50',1,'yes','yes','yes','no','yes',-1,'yes','yes','yes','yes','yes','yes','yes',4,'yes',1,-1,'yes','yes','yes',0,'')";

        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "userdbelements (userdbelements_id, userdbelements_field_name, userdbelements_field_value, userdb_id) VALUES (1, 'edit_user_name', 'admin', 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "userdbelements (userdbelements_id, userdbelements_field_name, userdbelements_field_value, userdb_id) VALUES (2, 'phone', '215.850.0710', 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "userdbelements (userdbelements_id, userdbelements_field_name, userdbelements_field_value, userdb_id) VALUES (3, 'mobile', '215.850.0710', 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "userdbelements (userdbelements_id, userdbelements_field_name, userdbelements_field_value, userdb_id) VALUES (4, 'fax', '702.995.6591', 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "userdbelements (userdbelements_id, userdbelements_field_name, userdbelements_field_value, userdb_id) VALUES (5, 'homepage', 'http://www.open-realty.org', 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "userdbelements (userdbelements_id, userdbelements_field_name, userdbelements_field_value, userdb_id) VALUES (6, 'info', 'I am the system administrator!', 1);";

        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdb (listingsdb_id, userdb_id, listingsdb_title, listing_seotitle, listingsdb_expiration, listingsdb_notes, listingsdb_creation_date, listingsdb_last_modified, listingsdb_hit_count, listingsdb_featured, listingsdb_active,listingsdb_mlsexport,listingsdb_pclass_id) VALUES (1, 1, 'White House', 'white_house', '2013-01-01', 'This is an example listing of the white hosue!', '2013-01-01', '2012-01-01 22:39:58', 17, 'yes', 'yes','no',1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdb (listingsdb_id, userdb_id, listingsdb_title, listing_seotitle, listingsdb_expiration, listingsdb_notes, listingsdb_creation_date, listingsdb_last_modified, listingsdb_hit_count, listingsdb_featured, listingsdb_active,listingsdb_mlsexport,listingsdb_pclass_id) VALUES (2, 1, 'U.S. Capitol Building', 'us_capitol_building', '2013-02-01', 'This is an example listing of the us. capitol building!', '2012-02-01', '2012-02-01 22:39:58', 17, 'yes', 'yes','no',1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdb (listingsdb_id, userdb_id, listingsdb_title, listing_seotitle, listingsdb_expiration, listingsdb_notes, listingsdb_creation_date, listingsdb_last_modified, listingsdb_hit_count, listingsdb_featured, listingsdb_active,listingsdb_mlsexport,listingsdb_pclass_id) VALUES (3, 1, 'Washington Monument', 'washington_monument', '2013-03-01', 'This is an example listing of the Washington Monument!', '2012-03-01', '2012-03-01 22:39:58', 17, 'yes', 'yes','no',1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdb (listingsdb_id, userdb_id, listingsdb_title, listing_seotitle, listingsdb_expiration, listingsdb_notes, listingsdb_creation_date, listingsdb_last_modified, listingsdb_hit_count, listingsdb_featured, listingsdb_active,listingsdb_mlsexport,listingsdb_pclass_id) VALUES (4, 1, 'Tallgrass Prairie', 'tallgrass_prairie', '2013-03-05', 'This is an example land listing of the Tallgrass Prairie!', '2012-03-05', '2012-03-05 22:39:58', 17, 'yes', 'yes','no',2);";

        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'home_features', 'Balcony||Patio/Deck||Waterfront||Dishwasher||Disposal||Gas Range||Microwave||Washer/Dryer||Carpeted Floors||Hardwood Floors||Air Conditioning||Alarm||Cable/Satellite TV||Fireplace||Wheelchair Access', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'community_features', 'Fitness Center||Golf Course||Pool||Spa/Jacuzzi||Sports Complex||Tennis Courts||Bike Paths||Boating||Courtyard||Playground/Park||Association Fee||Clubhouse||Controlled Access||Public Transportation', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'mls', '13013', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'status', 'Active', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'prop_tax', '0', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'garage_size', '40 car', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'lot_size', '20 Acres', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id)VALUES ( 'sq_feet', '35000', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'floors', '6', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'year_built', '1800', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'baths', '35', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'beds', '10', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'full_desc', 'Exclusive to this site! For two hundred years, the White House has stood as a symbol of the Presidency, the United States government, and the American people.', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'price', '2500000', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'neighborhood', 'Capitol', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'zip', '20500', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'state', 'DC', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'city', 'Washington', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'address', '1600 Pennsylvania Avenue', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'country', 'USA', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'latitude', '38.897969', 1, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ( 'longitude', '-77.036605', 1, 1);";

        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('home_features', 'Balcony||Patio/Deck||Dishwasher||Disposal||Gas Range||Microwave||Washer/Dryer||Carpeted Floors||Hardwood Floors||Air Conditioning||Alarm||Cable/Satellite TV||Fireplace||Wheelchair Access', 2, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('community_features', 'Golf Course||Pool||Spa/Jacuzzi||Sports Complex||Tennis Courts||Courtyard||Playground/Park||Association Fee||Clubhouse||Controlled Access||Public Transportation', 2, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('mls', '13014', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('status', 'Active', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('prop_tax', '0', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('garage_size', '0', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('lot_size', '4 Acres', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id)VALUES ('sq_feet', '175170', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('floors', '5', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('year_built', '1793', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('baths', '5', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('beds', '0', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('full_desc', 'The United States Capitol Building in Washington, D.C., is among the most architecturally impressive and symbolically important buildings in the world. It has housed the meeting chambers of the Senate and the House of Representatives for over two centuries. Begun in 1793, the Capitol building has been built, burnt, rebuilt, extended, and restored; today, it stands as a monument not only to its builders but also to the American people and their government.', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('price', '133000000', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('neighborhood', 'Capitol', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('zip', '20004', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('state', 'DC', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('city', 'Washington', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('address', ' East Capitol St NE & First St', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('country', 'USA', 2,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('latitude', '38.892072', 2, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('longitude', '-77.009184', 2, 1);";

        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('home_features', 'Carpeted Floors||Hardwood Floors||Air Conditioning||Alarm||Cable/Satellite TV||Fireplace||Wheelchair Access', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('community_features', 'Golf Course||Pool||Spa/Jacuzzi||Sports Complex||Tennis Courts||Courtyard||Playground/Park||Association Fee||Clubhouse||Controlled Access||Public Transportation', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('mls', '13015', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('status', 'Active', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('prop_tax', '0', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('garage_size', '0', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('lot_size', '106 Acres', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id)VALUES ('sq_feet', '175170', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('floors', '50', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('year_built', '1884', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('baths', '2', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('beds', '0', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('full_desc', 'The Washington Monument is the most prominent structure in Washington, D.C. and one of the city\'s early attractions. It was built in honor of George Washington, who led the country to independence and then became its first President. The Monument is shaped like an Egyptian obelisk, stands 555\' 5 1/8\" tall, and offers views in excess of thirty miles. It was finished on December 6, 1884.', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('price', '1187710', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('neighborhood', 'Capitol', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('zip', '20001', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('state', 'DC', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('city', 'Washington', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('address', ' Madison Dr NW & 15th St NW', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('country', 'USA', 3,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('latitude', '38.889983', 3, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('longitude', '-77.035160', 3, 1);";

        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('home_features', '', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('community_features', 'Playground/Park||Association Fee||Clubhouse', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('mls', '13016', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('status', 'Active', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('prop_tax', '0', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('garage_size', '25', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('lot_size', '700 Acres', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id)VALUES ('sq_feet', '0', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('floors', '0', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('year_built', '1990', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('baths', '0', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('beds', '0', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('full_desc', 'Neal Smith National Wildlife Refuge (formerly Walnut Creek), located in Jasper County, Iowa, is a unit of the National Wildlife Refuge System administered by the federal government. The Refuge was created by an act of Congress in 1990 to re-create 8600 acres of tallgrass prairie and oak savanna, the native plant and animal communities existing in central Iowa prior to Euro-American settlement in the 1840\'s.', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('price', '1187710', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('neighborhood', 'Des Moines', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('zip', '50228', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('state', 'IA', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('city', 'Prairie City', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('address', 'PO Box 399', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('country', 'USA', 4,1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('latitude', '41.559458', 4, 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ('longitude', '-93.280051', 4, 1);";

        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (1,'text','city','City','','',2,1,1,'Yes','top_left','Yes',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (2,'text','address','Address','','',0,2,2,'Yes','top_left','Yes',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (3,'text','mls','mls','','',33,0,16,'No','top_right','No',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (4,'number','prop_tax','Annual Property Tax','','',29,0,15,'No','top_right','No',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (5,'select','status','Status','','Active||Pending||Sold',31,0,19,'No','top_right','No',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (6,'text','lot_size','Lot Size','','',27,0,12,'No','top_right','No',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (7,'text','garage_size','Garage Size','','',0,29,9,'No','top_right','No',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (8,'text','year_built','Year Built','','',23,0,11,'No','top_left','No',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (9,'number','sq_feet','Square Feet','','',25,0,10,'No','top_right','No',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (10,'text','baths','Baths','','',19,0,7,'No','top_left','Yes',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (11,'number','floors','Floors','','',21,0,8,'No','top_left','No',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (12,'text','beds','Beds','','',17,0,6,'No','top_left','Yes',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (13,'textarea','full_desc','Full Description','','',13,0,1,'No','center','Yes',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (14,'text','neighborhood','Neighborhood','','',7,0,14,'No','top_left','No',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (15,'price','price','Price','','',9,1,5,'No','top_left','Yes',1,'minmax','Price',5000,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (16,'text','zip','Zip','','',5,3,3,'Yes','top_left','Yes',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (17,'select','state','State','','AK||AL||AR||AZ||CA||CO||CT||DC||DE||FL||GA||HI||IA||ID||IL||IN||KS||KY||LA||MA||MD||ME||MH||MI||MN||MO||MS||MT||NC||ND||NE||NH||NJ||NM||NV||NY||OH||OK||OR||PA||RI||SC||SD||TN||TX||UT||VA||VT||WA||WI||WV||WY',4,4,4,'Yes','top_left','Yes',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (18,'checkbox','home_features','Home Features','','Balcony||Patio/Deck||Waterfront||Dishwasher||Disposal||Gas Range||Microwave||Washer/Dryer||Carpeted Floors||Hardwood Floors||Air Conditioning||Alarm||Cable/Satellite TV||Fireplace||Wheelchair Access',80,0,17,'No','feature1','No',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (19,'checkbox','community_features','Community Features','','Fitness Center||Golf Course||Pool||Spa/Jacuzzi||Sports Complex||Tennis Courts||Bike Paths||Boating||Courtyard||Playground/Park||Association Fee||Clubhouse||Controlled Access||Public Transportation',85,2,18,'No','feature2','No',1,'optionlist','Community Features',0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (20,'text','country','Country','','',6,0,18,'No','top_left','No',0,NULL,NULL,0,0,NULL,'');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (21,'lat','latitude','Latitude','','',2,0,0,'No','bottom_left','No',0,'','',0,0,0,'' );";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements VALUES (22,'long','longitude','Longitude','','',3,0,0,'No','bottom_left','No',0,'','',0,0,0,'' );";

        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsimages (listingsimages_id, userdb_id, listingsimages_caption, listingsimages_file_name, listingsimages_thumb_file_name, listingsimages_description, listingsdb_id, listingsimages_rank, listingsimages_active) VALUES (1, 1, 'View From the Lawn', '1_white-house.jpg', 'thumb_1_white-house.jpg', 'This property has six floors, 132 rooms, 35 bathrooms, 147 windows, 412 doors, 12 chimneys, 8 staircases, and 3 elevators.', 1, 1, 'yes');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsimages (listingsimages_id, userdb_id, listingsimages_caption, listingsimages_file_name, listingsimages_thumb_file_name, listingsimages_description, listingsdb_id, listingsimages_rank, listingsimages_active) VALUES (2, 1, 'Vermeil Room', '1_vermeil_room.jpg', 'thumb_1_vermeil_room.jpg', 'The Vermeil Room, sometimes called the Gold Room, was last refurbished in 1991; it serves as a display room and, for formal occasions, as a ladies sitting room. The soft yellow of the paneled walls complements the collection of vermeil, or gilded silver, bequeathed to the White House in 1956 by Mrs. Margaret Thompson Biddle.', 1, 5, 'yes');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsimages (listingsimages_id, userdb_id, listingsimages_caption, listingsimages_file_name, listingsimages_thumb_file_name, listingsimages_description, listingsdb_id, listingsimages_rank, listingsimages_active) VALUES (3, 1, 'The China Room', '1_china_room.jpg', 'thumb_1_china_room.jpg', 'The Presidential Collection Room, now the China Room, was designated by Mrs. Woodrow Wilson in 1917 to display the growing collection of White House china. The room was redecorated in 1970, retaining the traditional red color scheme determined by the portrait of Mrs. Calvin Coolidge--painted by Howard Chandler Christy in 1924. President Coolidge, who was scheduled to sit for Christy, was too occupied that day with events concerning the Teapot Dome oil scandal. So the President postponed his appointment, and Mrs. Coolidge posed instead.', 1, 5, 'yes');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsimages (listingsimages_id, userdb_id, listingsimages_caption, listingsimages_file_name, listingsimages_thumb_file_name, listingsimages_description, listingsdb_id, listingsimages_rank, listingsimages_active) VALUES (4, 1, 'State Dining Room', '1_dining_room.jpg', 'thumb_1_dining_room.jpg', 'The State Dining Room, which now seats as many as 140 guests, was originally much smaller and served at various times as a drawing room, office, and Cabinet Room. Not until the Andrew Jackson administration was it called the State Dining Room, although it had been used for formal dinners by previous Presidents.', 1, 5, 'yes');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsimages (listingsimages_id, userdb_id, listingsimages_caption, listingsimages_file_name, listingsimages_thumb_file_name, listingsimages_description, listingsdb_id, listingsimages_rank, listingsimages_active) VALUES (5, 1, 'The Green Room', '1_green_room.jpg', 'thumb_1_green_room.jpg', 'Although intended by architect James Hoban to be the Common Dining Room, the Green Room has served many purposes since the White House was first occupied in 1800. The inventory of February 1801 indicates that it was first used as a Lodging Room. Thomas Jefferson, the second occupant of the White House, used it as a dining room with a canvas floor cloth, painted green, foreshadowing the present color scheme. James Madison made it a sitting room since his Cabinet met in the East Room next door, and the Monroes used it as the Card Room with two tables for the whist players among their guests. ', 1, 5, 'yes');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsimages (`listingsimages_id`,`userdb_id`,`listingsimages_caption`,`listingsimages_file_name`,`listingsimages_thumb_file_name`,`listingsimages_description`,`listingsdb_id`,`listingsimages_rank`,`listingsimages_active`) VALUES (6,1,'US Capitol Building','2_IMG_1369.JPG','thumb_2_IMG_1369.JPG','The number of steps represents each day of the year',2,1,'yes');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsimages (`listingsimages_id`,`userdb_id`,`listingsimages_caption`,`listingsimages_file_name`,`listingsimages_thumb_file_name`,`listingsimages_description`,`listingsdb_id`,`listingsimages_rank`,`listingsimages_active`) VALUES (7,1,'Washington Monument','3_IMG_1373.JPG','thumb_3_IMG_1373.JPG','Looking over the lawn to the monument.',3,1,'yes');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "listingsimages  (`listingsimages_id`,`userdb_id`,`listingsimages_caption`,`listingsimages_file_name`,`listingsimages_thumb_file_name`,`listingsimages_description`,`listingsdb_id`,`listingsimages_rank`,`listingsimages_active`) VALUES (8,1,'Tallgrass Prairie','4_IMAG0146.jpg','thumb_4_IMAG0146.jpg','Tallgrass Prairie on a windy spring day.',4,1,'yes');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "memberformelements (memberformelements_id, memberformelements_field_type, memberformelements_field_name, memberformelements_field_caption, memberformelements_default_text, memberformelements_field_elements, memberformelements_rank, memberformelements_required) VALUES (3, 'textarea', 'info', 'Info', '', '', 10, 'No');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "memberformelements (memberformelements_id, memberformelements_field_type, memberformelements_field_name, memberformelements_field_caption, memberformelements_default_text, memberformelements_field_elements, memberformelements_rank, memberformelements_required) VALUES (4, 'text', 'phone', 'Phone', '', '', 1, 'No');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "memberformelements (memberformelements_id, memberformelements_field_type, memberformelements_field_name, memberformelements_field_caption, memberformelements_default_text, memberformelements_field_elements, memberformelements_rank, memberformelements_required) VALUES (5, 'text', 'mobile', 'Mobile', '', '', 3, 'No');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "memberformelements (memberformelements_id, memberformelements_field_type, memberformelements_field_name, memberformelements_field_caption, memberformelements_default_text, memberformelements_field_elements, memberformelements_rank, memberformelements_required) VALUES (6, 'text', 'fax', 'Fax', '', '', 5, 'No');";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "memberformelements (memberformelements_id, memberformelements_field_type, memberformelements_field_name, memberformelements_field_caption, memberformelements_default_text, memberformelements_field_elements, memberformelements_rank, memberformelements_required) VALUES (7, 'url', 'homepage', 'Homepage', '', '', 7, 'No');";

        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "agentformelements (agentformelements_id, agentformelements_field_type, agentformelements_field_name, agentformelements_field_caption, agentformelements_default_text, agentformelements_field_elements, agentformelements_rank, agentformelements_required,agentformelements_display_priv) VALUES (3, 'textarea', 'info', 'Info', '', '', 10, 'No',0);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "agentformelements (agentformelements_id, agentformelements_field_type, agentformelements_field_name, agentformelements_field_caption, agentformelements_default_text, agentformelements_field_elements, agentformelements_rank, agentformelements_required,agentformelements_display_priv) VALUES (4, 'text', 'phone', 'Phone', '', '', 1, 'No',0);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "agentformelements (agentformelements_id, agentformelements_field_type, agentformelements_field_name, agentformelements_field_caption, agentformelements_default_text, agentformelements_field_elements, agentformelements_rank, agentformelements_required,agentformelements_display_priv) VALUES (5, 'text', 'mobile', 'Mobile', '', '', 3, 'No',0);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "agentformelements (agentformelements_id, agentformelements_field_type, agentformelements_field_name, agentformelements_field_caption, agentformelements_default_text, agentformelements_field_elements, agentformelements_rank, agentformelements_required,agentformelements_display_priv) VALUES (6, 'text', 'fax', 'Fax', '', '', 5, 'No',0);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "agentformelements (agentformelements_id, agentformelements_field_type, agentformelements_field_name, agentformelements_field_caption, agentformelements_default_text, agentformelements_field_elements, agentformelements_rank, agentformelements_required,agentformelements_display_priv) VALUES (7, 'url', 'homepage', 'Homepage', '', '', 7, 'No',0);";

        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "class (class_id, class_name, class_rank) VALUES (1, 'Home', 1);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "class (class_id, class_name, class_rank) VALUES (2, 'Land', 2);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "class (class_id, class_name, class_rank) VALUES (3, 'Farms', 3);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "class (class_id, class_name, class_rank) VALUES (4, 'Commercial', 4);";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "class (class_id, class_name, class_rank) VALUES (5, 'Rental', 5);";

        $sql_insert[] = 'INSERT INTO  ' . $config['table_prefix'] . "blogcategory (`category_name`,`category_seoname`,`category_rank`) VALUES('Default','default','0')";
        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix_no_lang'] . "seouri (action,slug,uri) VALUES
        ('listing','listing/','{listing_seotitle}.html'),
        ('page','','{page_seotitle}.html'),
        ('agent','agent/','{agent_id}/{agent_fname}-{agent_lname}.html'),
        ('contact_listing_agent','contact-agent/','{listing_id}/{agent_fname}-{agent_lname}.html'),
        ('blog','blog/','{blog_seotitle}.html'),
        ('css','css_','{css_name}.css'),
        ('searchpage','','search.html'),
        ('searchresults','','searchresults.html'),
        ('blogindex','','blog.html'),
        ('view_agents','','agents.html'),
        ('index','','index.html'),
        ('member_signup','','member_signup.html'),
        ('agent_signup','','agent_signup.html'),
        ('member_login','','member_login.html'),
        ('view_favorites','','view_favorites.html'),
        ('calculator','','calculator.html'),
        ('saved_searches','','saved_searches.html'),
        ('logout','','logout.html'),
        ('edit_profile','','edit_profile.html'),
        ('listing_image','listing/image/','{image_id}.html'),
        ('rss','rss_','{rss_feed}'),
        ('blog_tag','blog/tag/','{tag_seoname}'),
        ('blog_cat','blog/category/','{cat_seoname},'),
        ('blog_archive','blog/archive/','{archive_date}')";
        $sql_insert[] = 'INSERT INTO  ' . $config['table_prefix'] . "feedbackformelements
        (feedbackformelements_field_type,feedbackformelements_field_name,feedbackformelements_field_caption,feedbackformelements_default_text,
         feedbackformelements_field_elements,feedbackformelements_rank,feedbackformelements_required,feedbackformelements_location,feedbackformelements_tool_tip)
        VALUES
        ('select','source','How did you hear about us?','',
        'Referral||Phone Book||TV ad||Radio ad||Newspaper||Search Engine',1,'No','center','')";
        $sql_insert[] = 'INSERT INTO  ' . $config['table_prefix'] . "feedbackformelements
        (feedbackformelements_field_type,feedbackformelements_field_name,feedbackformelements_field_caption,feedbackformelements_default_text,
         feedbackformelements_field_elements,feedbackformelements_rank,feedbackformelements_required,feedbackformelements_location,feedbackformelements_tool_tip)
        VALUES
        ('textarea','comments','Comments, questions or suggestions?','','',2,'No','center','')";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu (`menu_id`,`menu_name`) VALUES ('1', 'vertical');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu (`menu_id`,`menu_name`) VALUES ('2', 'horizontal');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('1', '2', '1', 'prop_class_search_block', '0', '5', 'pclass_searchlinks_block', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('2', '0', '1', 'Property Classes', '5', '3', '#', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('9', '0', '1', 'View All Listings', '1', '1', 'url_search_results', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('10', '0', '1', 'View Blog', '14', '1', 'url_blog', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('11', '0', '1', 'View Agents', '2', '1', 'url_view_agents', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('12', '0', '1', 'Loan Calculators', '3', '1', 'url_view_calculator', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('32', '0', '2', 'Home', '1', '1', 'url_index', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('33', '0', '2', 'View All Listings', '2', '1', 'url_search_results', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('34', '0', '2', 'Search Listings', '3', '3', '{baseurl}/index.php?action=search_step_2', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('35', '0', '2', 'About Us', '4', '3', '{page_link_3}', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('36', '0', '2', 'Legal', '5', '3', '{page_link_4}', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('37', '0', '2', 'Contact Us', '6', '3', '{page_link_2}', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('38', '0', '1', 'Main Menu', '0', '4', '', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('39', '0', '1', 'Search', '4', '4', '', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('40', '0', '1', 'Your Account', '6', '4', '', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('41', '0', '1', 'Member Signup', '10', '1', 'url_member_signup', '1', '0', '0', '0', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('42', '0', '1', 'Agent Signup', '9', '1', 'url_agent_signup', '1', '0', '0', '0', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('43', '0', '1', 'Member Login', '12', '1', 'url_member_login', '1', '0', '0', '0', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('44', '0', '1', 'Syndication', '17', '4', '', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('45', '61', '1', 'Featured Listings', '0', '1', 'rss_featured', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('46', '61', '1', 'Last Added Listings', '1', '1', 'rss_latestlisting', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('47', '61', '1', 'Last Modified Listings', '2', '1', 'rss_lastmodified', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('48', '61', '1', 'Recent Blog Posts', '3', '1', 'rss_blog_posts', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('50', '61', '1', 'Recent Blog Comments', '4', '1', 'rss_blog_comments', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('52', '63', '1', 'blog_cat_Block', '0', '5', 'blog_category_link_block', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('53', '0', '1', 'Blog', '13', '4', '', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('54', '62', '1', 'blog_archive_block', '0', '5', 'blog_archive_link_block', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('55', '0', '1', 'Recent Posts', '19', '4', '', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('56', '0', '1', 'recent_post_block', '20', '5', 'blog_recent_post_block', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('57', '0', '1', 'Recent Comments', '21', '4', '', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('58', '0', '1', 'recent_comments_block', '22', '5', 'blog_recent_comments_block', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('61', '0', '1', 'RSS Feeds', '18', '3', '#', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('62', '0', '1', 'Archives', '15', '3', '#', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('63', '0', '1', 'Categories', '16', '3', '#', '1', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('83', '0', '1', 'Edit profile', '7', '1', 'url_edit_profile', '0', '1', '0', '0', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('84', '0', '1', 'Logout', '8', '1', 'url_logout', '0', '1', '1', '1', '_self', '');";
        $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('85', '0', '1', 'Agent Login', '11', '1', 'url_agent_login', '1', '0', '0', '0', '_self', '');";
        $z = 1;
        for ($x = 1; $x < 6; $x++) {
            for ($y = 1; $y < 23; $y++) {
                $sql_insert[] = 'INSERT INTO ' . $config['table_prefix_no_lang'] . "classformelements (classformelements_id, class_id, listingsformelements_id) VALUES ($z, $x, $y);";
                $z++;
            }
        }
        foreach ($sql_insert as $elementIndexValue => $elementContents) {
            $recordSet = $conn->Execute($elementContents);
            if ($recordSet === false) {
                if ($_SESSION['devel_mode'] == 'no') {
                    die("<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />");
                } else {
                    echo "<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />";
                }
            }
        }
        if (!isset($_SESSION['autoinstall'])) {
            echo 'Tables Populated<br />';
        }
    }

    public function load_version()
    {
        $this->load_lang($_SESSION['or_install_lang']);
        switch ($_GET['step']) {
            case 'autoinstall':
                $_SESSION['autoinstall'] = 'true';
                $_SESSION['table_prefix'] = trim($_GET['table_prefix']);
                $_SESSION['db_type'] = trim($_GET['db_type']);
                $_SESSION['db_user'] = trim($_GET['db_user']);
                $_SESSION['db_password'] = trim($_GET['db_password']);
                $_SESSION['db_database'] = trim($_GET['db_database']);
                $_SESSION['db_server'] = trim($_GET['db_server']);
                $_SESSION['basepath'] = trim($_GET['basepath']);
                $_SESSION['baseurl'] = trim($_GET['baseurl']);
                $_SESSION['default_email'] = trim($_GET['default_email']);
                if (isset($_GET['devel_mode'])) {
                    $_SESSION['devel_mode'] = $_GET['devel_mode'];
                } else {
                    $_SESSION['devel_mode'] = 'no';
                }
                $_SESSION['template'] = 'html5';
                $this->write_config();
                $this->create_tables();
                $this->create_index();
                $this->insert_values();
                if (!isset($_SESSION['autoinstall'])) {
                    echo '<br /><strong>' . $this->lang['install_installation_complete'] . ' <a href="../admin/index.php?action=configure">' . $this->lang['install_configure_installation'] . '</a></strong>';
                }
                break;
            case 4:
                $this->get_new_settings();
                break;
            case 5:
                $_SESSION['table_prefix'] = trim($_POST['table_prefix']);
                $_SESSION['db_type'] = trim($_POST['db_type']);
                $_SESSION['db_user'] = trim($_POST['db_user']);
                $_SESSION['db_password'] = trim($_POST['db_password']);
                $_SESSION['db_database'] = trim($_POST['db_database']);
                $_SESSION['db_server'] = trim($_POST['db_server']);
                $_SESSION['basepath'] = trim($_POST['basepath']);
                $_SESSION['baseurl'] = trim($_POST['baseurl']);
                $_SESSION['devel_mode'] = $_POST['devel_mode'];
                $_SESSION['template'] = 'html5';
                $this->write_config();
                break;
            case 6:
                $this->create_tables();
                $this->create_index();
                $this->insert_values();
                $this->database_maintenance();
                echo '<br /><strong>' . $this->lang['install_installation_complete'] . ' <a href="../admin/index.php?action=configure">' . $this->lang['install_configure_installation'] . '</a></strong>';
                
                break;
        }
    }
}
