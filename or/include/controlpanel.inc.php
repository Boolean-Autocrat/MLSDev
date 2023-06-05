<?php

use Abraham\TwitterOAuth\TwitterOAuth;


use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * configurator
 * This class contains all functions related the site configurator.
 *
 * @author Ryan Bonham
 */
class configurator
{
    /**
     * configurator::show_configurator()
     * This function handles the display and updates for the site configurator.
     *
     * @param string $guidestring
     * @return
     */

    private $yes_no = [0 => 'No', 1 => 'Yes'];
    private $asc_desc = ['ASC' => 'ASC', 'DESC' => 'DESC'];
    private $captcha_system = ['recaptcha' => 'reCAPTCHA', 'securimage' => 'Local Captcha'];

    public function show_configurator()
    {
        global $conn, $lang, $config, $jscript;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        $display = '';
        if ($security === true) {
            // Open Connection to the Control Panel Table
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // DISABLE MULTILINGUAL SUPPORT AS IT IS NOT READY FOR THIS RELEASE
            $ml_support = false;
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/site_config.html');
            $page->replace_tag('view_warnings', $this->view_warnings());
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        }
        return $display;
    }

    public function ajax_configure_general()
    {
        global $conn, $lang, $config, $jscript;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        $display = '';
        if ($security === true) {
            // Open Connection to the Control Panel Table
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // DISABLE MULTILINGUAL SUPPORT AS IT IS NOT READY FOR THIS RELEASE
            $ml_support = false;
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/site_config_general.html');
            // New Charset Settings - Current charsets supported by PHP 4.3.0 and up
            $charset['ISO-8859-1'] = 'ISO-8859-1';
            $charset['ISO-8859-15'] = 'ISO-8859-15';
            $charset['UTF-8'] = 'UTF-8';
            $charset['cp866'] = 'cp866';
            $charset['cp1251'] = 'cp1251';
            $charset['cp1252'] = 'cp1252';
            $charset['KOI8-R'] = 'KOI8-R';
            $charset['BIG5'] = 'BIG5';
            $charset['GB2312'] = 'GB2312';
            $charset['BIG5-HKSCS'] = 'BIG5-HKSCS';
            $charset['Shift_JIS'] = 'Shift_JIS';
            $charset['EUC-JP'] = 'EUC-JP';
            //Replace Tags
            $page->replace_tag('controlpanel_admin_name', htmlentities($config['admin_name'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('basepath', htmlentities($config['basepath'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_admin_email', htmlentities($config['admin_email'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_site_email', htmlentities($config['site_email'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_company_name', htmlentities($config['company_name'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_company_location', htmlentities($config['company_location'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_company_logo', htmlentities($config['company_logo'], ENT_COMPAT, $config['charset']));

            $page->replace_tag('baseurl', htmlentities($config['baseurl'], ENT_COMPAT, $config['charset']));

            $page->replace_tag('controlpanel_seo_default_keywords', htmlentities($config['seo_default_keywords'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_seo_default_description', htmlentities($config['seo_default_description'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_seo_listing_keywords', htmlentities($config['seo_listing_keywords'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_seo_listing_description', htmlentities($config['seo_listing_description'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_seo_default_title', htmlentities($config['seo_default_title'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_seo_listing_title', htmlentities($config['seo_listing_title'], ENT_COMPAT, $config['charset']));

            $html = $page->get_template_section('automatic_update_block');
            $html = $page->form_options($this->yes_no, $config['automatic_update_check'], $html);
            $page->replace_template_section('automatic_update_block', $html);

            $html = $page->get_template_section('demo_mode_block');
            $html = $page->form_options($this->yes_no, $config['demo_mode'], $html);
            $page->replace_template_section('demo_mode_block', $html);

            $html = $page->get_template_section('maintenance_mode_block');
            $html = $page->form_options($this->yes_no, $config['maintenance_mode'], $html);
            $page->replace_template_section('maintenance_mode_block', $html);

            $url_type[1] = $lang['url_standard'];
            $url_type[2] = $lang['url_search_friendly'];
            $html = $page->get_template_section('url_type_block');
            $html = $page->form_options($url_type, $config['seo_url_seperator'], $html);
            $page->replace_template_section('url_type_block', $html);

            $url_seperator['+'] = $lang['url_seperator_default'];
            $url_seperator['-'] = $lang['url_seperator_hyphen'];
            $html = $page->get_template_section('url_seperator_block');
            $html = $page->form_options($url_seperator, $config['seo_url_seperator'], $html);
            $page->replace_template_section('url_seperator_block', $html);
            //Default page
            $default_page['blog_index'] = $lang['default_page_blog_index'];
            $default_page['wysiwyg_page'] = $lang['default_page_wysiwyg_page'];
            $html = $page->get_template_section('default_page_block');
            $html = $page->form_options($default_page, $config['default_page'], $html);
            $page->replace_template_section('default_page_block', $html);

            //Include time zones stored as the $timezonelist[] array
            include_once $config['basepath'] . '/include/tz.inc.php';

            $html = $page->get_template_section('timezone_block');
            $html = $page->form_options($timezonelist, $config['timezone'], $html);
            $page->replace_template_section('timezone_block', $html);

            $html = $page->get_template_section('charset_block');
            $html = $page->form_options($charset, $config['charset'], $html);
            $page->replace_template_section('charset_block', $html);


            // Mail Settings Start
            $mailsystems = [0 => 'PHP Mail()', 1 => 'SMTP w/ PHPMailer'];
            $html = $page->get_template_section('phpmailer_block');
            $html = $page->form_options($mailsystems, $config['phpmailer'], $html);
            $page->replace_template_section('phpmailer_block', $html);

            $page->replace_tag('controlpanel_mailserver', htmlentities($config['mailserver'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_mailport', intval($config['mailport']));
            $page->replace_tag('controlpanel_mailuser', htmlentities($config['mailuser'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_mailpass', htmlentities($config['mailpass'], ENT_COMPAT, $config['charset']));

            //Mail Settings End

            $html = $page->get_template_section('wysiwyg_show_edit_block');
            $html = $page->form_options($this->yes_no, $config['wysiwyg_show_edit'], $html);
            $page->replace_template_section('wysiwyg_show_edit_block', $html);


            $html = $page->get_template_section('mbstring_enabled_block');
            $html = $page->form_options($this->yes_no, $config['controlpanel_mbstring_enabled'], $html);
            $page->replace_template_section('mbstring_enabled_block', $html);

            $html = $page->get_template_section('add_linefeeds_block');
            $html = $page->form_options($this->yes_no, $config['add_linefeeds'], $html);
            $page->replace_template_section('add_linefeeds_block', $html);

            $html = $page->get_template_section('strip_html_block');
            $html = $page->form_options($this->yes_no, $config['strip_html'], $html);
            $page->replace_template_section('strip_html_block', $html);

            $html = $page->get_template_section('allow_template_change_block');
            $html = $page->form_options($this->yes_no, $config['allow_template_change'], $html);
            $page->replace_template_section('allow_template_change_block', $html);

            $html = $page->get_template_section('allow_language_change_block');
            $html = $page->form_options($this->yes_no, $config['allow_language_change'], $html);
            $page->replace_template_section('allow_language_change_block', $html);

            $html = $page->get_template_section('captcha_system_block');
            $html = $page->form_options($this->captcha_system, $config['captcha_system'], $html);
            $page->replace_template_section('captcha_system_block', $html);

            $page->replace_tag('controlpanel_allowed_html_tags', htmlentities($config['allowed_html_tags'], ENT_COMPAT, $config['charset']));

            //Show reCaptcha stuff
            $sql = 'SELECT controlpanel_recaptcha_sitekey, controlpanel_recaptcha_secretkey 
				FROM ' . $config['table_prefix_no_lang'] . 'controlpanel';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $recaptchasecretkey = $recordSet->fields('controlpanel_recaptcha_secretkey');
            $recaptchasitekey = $recordSet->fields('controlpanel_recaptcha_sitekey');

            $page->replace_tag('controlpanel_recaptcha_sitekey', $recaptchasitekey);
            $page->replace_tag('controlpanel_recaptcha_secretkey', $recaptchasecretkey);

            $page->replace_tag('controlpanel_google_client_id', htmlentities($config['google_client_id'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_google_client_secret', htmlentities($config['google_client_secret'], ENT_COMPAT, $config['charset']));

            $page->replace_permission_tags();
            $page->replace_lang_template_tags(true);
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        }

        return $display;
    }

    public function ajax_configure_seo()
    {
        global $conn, $lang, $config, $jscript;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        $display = '';
        if ($security === true) {
            // Open Connection to the Control Panel Table
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // DISABLE MULTILINGUAL SUPPORT AS IT IS NOT READY FOR THIS RELEASE
            $ml_support = false;
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/site_config_seo.html');
            //Replace Tags
            //Deal with
            $page->replace_tag('baseurl', htmlentities($config['baseurl'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_seo_default_keywords', htmlentities($config['seo_default_keywords'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_seo_default_description', htmlentities($config['seo_default_description'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_seo_listing_keywords', htmlentities($config['seo_listing_keywords'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_seo_listing_description', htmlentities($config['seo_listing_description'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_seo_default_title', htmlentities($config['seo_default_title'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_seo_listing_title', htmlentities($config['seo_listing_title'], ENT_COMPAT, $config['charset']));

            $url_type[1] = $lang['url_standard'];
            $url_type[2] = $lang['url_search_friendly'];
            $html = $page->get_template_section('url_type_block');
            $html = $page->form_options($url_type, $config['url_style'], $html);
            $page->replace_template_section('url_type_block', $html);

            $url_seperator['+'] = $lang['url_seperator_default'];
            $url_seperator['-'] = $lang['url_seperator_hyphen'];
            $html = $page->get_template_section('url_seperator_block');
            $html = $page->form_options($url_seperator, $config['seo_url_seperator'], $html);
            $page->replace_template_section('url_seperator_block', $html);
            //Default page
            $default_page['blog_index'] = $lang['default_page_blog_index'];
            $default_page['wysiwyg_page'] = $lang['default_page_wysiwyg_page'];
            $html = $page->get_template_section('default_page_block');
            $html = $page->form_options($default_page, $config['default_page'], $html);
            $page->replace_template_section('default_page_block', $html);

            $page->replace_permission_tags();
            $page->replace_lang_template_tags(true);
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        }
        return $display;
    }

    public function ajax_configure_seo_links()
    {
        global $conn, $lang, $config, $jscript;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        $display = '';
        if ($security === true) {
            // Open Connection to the Control Panel Table
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // DISABLE MULTILINGUAL SUPPORT AS IT IS NOT READY FOR THIS RELEASE
            $ml_support = false;
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/site_config_seo_links.html');
            //Replace Tags

            //Deal with SEO URL Structures
            $sql = 'SELECT action,slug,uri FROM ' . $config['table_prefix_no_lang'] . 'seouri';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            while (!$recordSet->EOF) {
                $action  = $recordSet->fields('action');
                $slug  = $recordSet->fields('slug');
                $uri  = $recordSet->fields('uri');
                $page->replace_tag($action . '_slug', htmlentities($slug, ENT_COMPAT, $config['charset']));
                $page->replace_tag($action . '_uri', htmlentities($uri, ENT_COMPAT, $config['charset']));

                $recordSet->MoveNext();
            }
            $page->replace_tag('baseurl', htmlentities($config['baseurl'], ENT_COMPAT, $config['charset']));

            $page->replace_permission_tags();
            $page->replace_lang_template_tags(true);
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        }
        return $display;
    }

    public function ajax_configure_uploads()
    {
        global $conn, $lang, $config, $jscript;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        $display = '';
        if ($security === true) {
            // Open Connection to the Control Panel Table
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // DISABLE MULTILINGUAL SUPPORT AS IT IS NOT READY FOR THIS RELEASE
            $ml_support = false;
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/site_config_uploads.html');
            //Load Jscript
            $thumbnail_prog['gd'] = 'GD Libs';
            $thumbnail_prog['imagemagick'] = 'ImageMagick';
            $resize_opts['width'] = 'Width';
            $resize_opts['height'] = 'Height';
            $resize_opts['bestfit'] = 'Best Fit';
            $resize_opts['both'] = 'Both';
            $mainimage_opts['width'] = 'Width';
            $mainimage_opts['height'] = 'Height';
            $mainimage_opts['both'] = 'Both';
            $filedisplay['filename'] = 'Filename';
            $filedisplay['caption'] = 'Caption';
            $filedisplay['both'] = 'Both';

            $page->replace_tag('controlpanel_allowed_upload_extensions', htmlentities($config['allowed_upload_extensions'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_thumbnail_width', htmlentities($config['thumbnail_width'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_thumbnail_height', htmlentities($config['thumbnail_height'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_user_jpeg_quality', htmlentities($config['user_jpeg_quality'], ENT_COMPAT, $config['charset']));
            $html = $page->get_template_section('user_resize_thumb_by_block');
            $html = $page->form_options($resize_opts, $config['user_resize_thumb_by'], $html);
            $page->replace_template_section('user_resize_thumb_by_block', $html);
            $html = $page->get_template_section('user_resize_img_block');
            $html = $page->form_options($this->yes_no, $config['user_resize_img'], $html);
            $page->replace_template_section('user_resize_img_block', $html);
            $html = $page->get_template_section('user_resize_by_block');
            $html = $page->form_options($resize_opts, $config['user_resize_by'], $html);
            $page->replace_template_section('user_resize_by_block', $html);
            $page->replace_tag('controlpanel_user_thumbnail_width', htmlentities($config['user_thumbnail_width'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_user_thumbnail_height', htmlentities($config['user_thumbnail_height'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_path_to_imagemagick', htmlentities($config['path_to_imagemagick'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_jpeg_quality', htmlentities($config['jpeg_quality'], ENT_COMPAT, $config['charset']));
            $html = $page->get_template_section('make_thumbnail_block');
            $html = $page->form_options($this->yes_no, $config['make_thumbnail'], $html);
            $page->replace_template_section('make_thumbnail_block', $html);
            $html = $page->get_template_section('resize_thumb_by_block');
            $html = $page->form_options($resize_opts, $config['resize_thumb_by'], $html);
            $page->replace_template_section('resize_thumb_by_block', $html);
            $html = $page->get_template_section('thumbnail_prog_block');
            $html = $page->form_options($thumbnail_prog, $config['thumbnail_prog'], $html);
            $page->replace_template_section('thumbnail_prog_block', $html);
            $html = $page->get_template_section('resize_img_block');
            $html = $page->form_options($this->yes_no, $config['resize_img'], $html);
            $page->replace_template_section('resize_img_block', $html);
            $html = $page->get_template_section('resize_by_block');
            $html = $page->form_options($resize_opts, $config['resize_by'], $html);
            $page->replace_template_section('resize_by_block', $html);
            $html = $page->get_template_section('show_no_photo_block');
            $html = $page->form_options($this->yes_no, $config['show_no_photo'], $html);
            $page->replace_template_section('show_no_photo_block', $html);
            $html = $page->get_template_section('show_agent_no_photo_block');
            $html = $page->form_options($this->yes_no, $config['show_agent_no_photo'], $html);
            $page->replace_template_section('show_agent_no_photo_block', $html);
            $page->replace_tag('controlpanel_max_listings_uploads', htmlentities($config['max_listings_uploads'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_max_listings_upload_size', htmlentities($config['max_listings_upload_size'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_max_listings_upload_width', htmlentities($config['max_listings_upload_width'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_max_listings_upload_height', htmlentities($config['max_listings_upload_height'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_max_user_uploads', htmlentities($config['max_user_uploads'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_max_user_upload_size', htmlentities($config['max_user_upload_size'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_max_user_upload_width', htmlentities($config['max_user_upload_width'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_max_user_upload_height', htmlentities($config['max_user_upload_height'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_max_vtour_uploads', htmlentities($config['max_vtour_uploads'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_max_vtour_upload_size', htmlentities($config['max_vtour_upload_size'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_max_vtour_upload_width', htmlentities($config['max_vtour_upload_width'], ENT_COMPAT, $config['charset']));

            $html = $page->get_template_section('main_image_display_by_block');
            $html = $page->form_options($mainimage_opts, $config['main_image_display_by'], $html);
            $page->replace_template_section('main_image_display_by_block', $html);
            $page->replace_tag('controlpanel_main_image_width', htmlentities($config['main_image_width'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_main_image_height', htmlentities($config['main_image_height'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_number_columns', htmlentities($config['number_columns'], ENT_COMPAT, $config['charset']));

            $page->replace_tag('controlpanel_allowed_file_upload_extensions', htmlentities($config['allowed_file_upload_extensions'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_max_listings_file_uploads', htmlentities($config['max_listings_file_uploads'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_max_listings_file_upload_size', htmlentities($config['max_listings_file_upload_size'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_max_users_file_uploads', htmlentities($config['max_users_file_uploads'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_max_users_file_upload_size', htmlentities($config['max_users_file_upload_size'], ENT_COMPAT, $config['charset']));

            $html = $page->get_template_section('show_file_icon_block');
            $html = $page->form_options($this->yes_no, $config['show_file_icon'], $html);
            $page->replace_template_section('show_file_icon_block', $html);

            $html = $page->get_template_section('file_display_option_block');
            $html = $page->form_options($filedisplay, $config['file_display_option'], $html);
            $page->replace_template_section('file_display_option_block', $html);

            $html = $page->get_template_section('show_file_size_block');
            $html = $page->form_options($this->yes_no, $config['file_display_size'], $html);
            $page->replace_template_section('show_file_size_block', $html);

            $page->replace_tag('controlpanel_icon_image_width', htmlentities($config['file_icon_width'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_icon_image_height', htmlentities($config['file_icon_height'], ENT_COMPAT, $config['charset']));

            $page->replace_tag('controlpanel_vtour_width', htmlentities($config['vtour_width'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_vtour_height', htmlentities($config['vtour_height'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_vt_popup_width', htmlentities($config['vt_popup_width'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_vt_popup_height', htmlentities($config['vt_popup_height'], ENT_COMPAT, $config['charset']));

            $page->replace_tag('controlpanel_listingimages_slideshow_group_thumb', htmlentities($config['listingimages_slideshow_group_thumb'], ENT_COMPAT, $config['charset']));
            $page->replace_permission_tags();
            $page->replace_lang_template_tags(true);
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        }
        return $display;
    }

    public function ajax_configure_templates()
    {
        global $conn, $lang, $config, $jscript;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        $display = '';
        if ($security === true) {
            // Open Connection to the Control Panel Table
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // DISABLE MULTILINGUAL SUPPORT AS IT IS NOT READY FOR THIS RELEASE
            $ml_support = false;
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/site_config_templates.html');
            // Get Template List
            $dir = 0;

            // Get Site and mobile Template List
            $options = [];
            $directories = glob($config['basepath'] . '/template/*', GLOB_ONLYDIR);
            foreach ($directories as $file) {
                $file = basename($file);
                if ($file == 'default') {
                    continue;
                }
                $options[$file] = $file;
            }
            $html = $page->get_template_section('template_block');
            $html = $page->form_options($options, $config['template'], $html);
            $page->replace_template_section('template_block', $html);
            $html = $page->get_template_section('mobile_template_block');
            $html = $page->form_options($options, $config['mobile_template'], $html);
            $page->replace_template_section('mobile_template_block', $html);

            // Get Admin Template List
            $options = [];
            $directories = glob($config['basepath'] . '/admin/template/*', GLOB_ONLYDIR);
            foreach ($directories as $file) {
                $file = basename($file);
                if ($file == 'default') {
                    continue;
                }
                $options[$file] = $file;
            }
            $html = $page->get_template_section('admin_template_block');
            $html = $page->form_options($options, $config['admin_template'], $html);
            $page->replace_template_section('admin_template_block', $html);

            // Get Listing Template List
            $options = [];
            //Get default templates
            $files = glob($config['basepath'] . '/template/default/*');
            foreach ($files as $file) {
                $file = basename($file);
                if (substr($file, 0, 14) == 'listing_detail') {
                    $options[$file] = substr($file, 15, -5);
                }
            }
            //Get Custom listing detail Templates
            $files = glob($config['basepath'] . '/template/' . $config['template'] . '/*');
            foreach ($files as $file) {
                $file = basename($file);
                if (substr($file, 0, 14) == 'listing_detail' && substr($file, 0, 21) != 'listing_detail_pclass') {
                    $options[$file] = substr($file, 15, -5);
                }
            }
            $html = $page->get_template_section('listing_template_block');
            $html = $page->form_options($options, $config['listing_template'], $html);
            $page->replace_template_section('listing_template_block', $html);
            $page->replace_tag('controlpanel_template_listing_sections', htmlentities($config['template_listing_sections'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_template_lead_sections', htmlentities($config['template_lead_sections'], ENT_COMPAT, $config['charset']));

            // Get Search Result Template List
            $options = [];
            $files = glob($config['basepath'] . '/template/default/*');
            foreach ($files as $file) {
                $file = basename($file);
                if (substr($file, 0, 13) == 'search_result') {
                    $options[$file] = substr($file, 14, -5);
                }
            }
            //Get Custom search result Templates
            $files = glob($config['basepath'] . '/template/' . $config['template'] . '/*');
            foreach ($files as $file) {
                $file = basename($file);
                if (substr($file, 0, 13) == 'search_result') {
                    $options[$file] = substr($file, 14, -5);
                }
            }
            $html = $page->get_template_section('search_result_template_block');
            $html = $page->form_options($options, $config['search_result_template'], $html);
            $page->replace_template_section('search_result_template_block', $html);

            // Get View Agent Template List
            $options = [];
            $files = glob($config['basepath'] . '/template/default/*');
            foreach ($files as $file) {
                $file = basename($file);
                if (substr($file, 0, 10) == 'view_user_') {
                    $options[$file] = substr($file, 10, -5);
                }
            }
            //Get Custom View Agent Templates
            $files = glob($config['basepath'] . '/template/' . $config['template'] . '/*');
            foreach ($files as $file) {
                $file = basename($file);
                if (substr($file, 0, 10) == 'view_user_') {
                    $options[$file] = substr($file, 10, -5);
                }
            }
            $html = $page->get_template_section('agent_template_block');
            $html = $page->form_options($options, $config['agent_template'], $html);
            $page->replace_template_section('agent_template_block', $html);

            // Get VTour Template List
            $options = [];
            //Get default templates
            if ($handle = opendir($config['basepath'] . '/template/default/')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != '.' && $file != '..' && $file != 'CVS' && $file != '.svn') {
                        if (!is_dir($config['basepath'] . '/template/default/' . $file)) {
                            if (substr($file, 0, 6) == 'vtour_') {
                                $options[$file] = substr($file, 6, -5);
                            }
                        }
                    }
                }
                closedir($handle);
            }
            //Get Custom Vtour Templates
            if ($handle = opendir($config['basepath'] . '/template/' . $config['template'])) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != '.' && $file != '..' && $file != 'CVS' && $file != '.svn') {
                        if (!is_dir($config['basepath'] . '/template/' . $config['template'] . '/' . $file)) {
                            if (substr($file, 0, 6) == 'vtour_') {
                                $options[$file] = substr($file, 6, -5);
                            }
                        }
                    }
                }
                closedir($handle);
            }
            $html = $page->get_template_section('vtour_template_block');
            $html = $page->form_options($options, $config['vtour_template'], $html);
            $page->replace_template_section('vtour_template_block', $html);

            // Get Notify New Listing Template List
            $options = [];
            //Get default templates
            if ($handle = opendir($config['basepath'] . '/template/default/')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != '.' && $file != '..' && $file != 'CVS' && $file != '.svn') {
                        if (!is_dir($config['basepath'] . '/template/default/' . $file)) {
                            if (substr($file, 0, 16) == 'notify_listings_') {
                                $options[$file] = substr($file, 16, -5);
                            }
                        }
                    }
                }
                closedir($handle);
            }
            //Get Custom Notify New Listing Templates
            if ($handle = opendir($config['basepath'] . '/template/' . $config['template'])) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != '.' && $file != '..' && $file != 'CVS' && $file != '.svn') {
                        if (!is_dir($config['basepath'] . '/template/' . $config['template'] . '/' . $file)) {
                            if (substr($file, 0, 16) == 'notify_listings_') {
                                $options[$file] = substr($file, 16, -5);
                            }
                        }
                    }
                }
                closedir($handle);
            }
            $html = $page->get_template_section('notify_listings_template_block');
            $html = $page->form_options($options, $config['notify_listings_template'], $html);
            $page->replace_template_section('notify_listings_template_block', $html);

            // Get contact Agent Template List
            $options = [];
            //Get default templates
            if ($handle = opendir($config['basepath'] . '/template/default/')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != '.' && $file != '..' && $file != 'CVS' && $file != '.svn') {
                        if (!is_dir($config['basepath'] . '/template/default/' . $file)) {
                            if (substr($file, 0, 14) == 'contact_agent_') {
                                $options[$file] = substr($file, 14, -5);
                            }
                        }
                    }
                }
                closedir($handle);
            }
            //Get Custom contact Agent Template  Templates
            if ($handle = opendir($config['basepath'] . '/template/' . $config['template'])) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != '.' && $file != '..' && $file != 'CVS' && $file != '.svn') {
                        if (!is_dir($config['basepath'] . '/template/' . $config['template'] . '/' . $file)) {
                            if (substr($file, 0, 14) == 'contact_agent_') {
                                $options[$file] = substr($file, 14, -5);
                            }
                        }
                    }
                }
                closedir($handle);
            }
            $html = $page->get_template_section('contact_template_block');
            $html = $page->form_options($options, $config['contact_template'], $html);
            $page->replace_template_section('contact_template_block', $html);

            $page->replace_permission_tags();
            $page->replace_lang_template_tags(true);
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        }
        return $display;
    }

    public function ajax_configure_listings()
    {
        global $conn, $lang, $config, $jscript;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        $display = '';
        if ($security === true) {
            // Open Connection to the Control Panel Table
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // DISABLE MULTILINGUAL SUPPORT AS IT IS NOT READY FOR THIS RELEASE
            $ml_support = false;
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/site_config_listings.html');
            // Listing Template Field Names for Map Field Selection
            $sql = 'SELECT listingsformelements_field_name, listingsformelements_field_caption FROM ' . $config['table_prefix'] . 'listingsformelements';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $listing_field_name_options[''] = '';
            while (!$recordSet->EOF) {
                $field_name = $recordSet->fields('listingsformelements_field_name');
                $listing_field_name_options[$field_name] = $field_name . ' (' . $recordSet->fields('listingsformelements_field_caption') . ')';
                $recordSet->MoveNext();
            }
            // Listing Template Field Names for Search Field Selection
            $sql = 'SELECT listingsformelements_field_name, listingsformelements_field_caption FROM ' . $config['table_prefix'] . "listingsformelements WHERE listingsformelements_display_on_browse = 'Yes'";
            $recordSet = $conn->Execute($sql);
            $search_field_sortby_options['random'] = $lang['random'];
            $search_field_sortby_options['listingsdb_id'] = $lang['id_caption'];
            $search_field_sortby_options['listingsdb_title'] = $lang['title'];
            $search_field_sortby_options['listingsdb_featured'] = $lang['featured'];
            $search_field_sortby_options['listingsdb_last_modified'] = $lang['last_modified'];
            $search_field_special_sortby_options['none'] = $lang['none'];
            $search_field_special_sortby_options['listingsdb_featured'] = $lang['featured'];
            $search_field_special_sortby_options['listingsdb_id'] = $lang['id_caption'];
            $search_field_special_sortby_options['listingsdb_title'] = $lang['title'];
            $search_field_special_sortby_options['listingsdb_last_modified'] = $lang['last_modified'];
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            while (!$recordSet->EOF) {
                $field_name = $recordSet->fields('listingsformelements_field_name');
                $search_field_sortby_options[$field_name] = $field_name . ' (' . $recordSet->fields('listingsformelements_field_caption') . ')';
                $search_field_special_sortby_options[$field_name] = $field_name . ' (' . $recordSet->fields('listingsformelements_field_caption') . ')';
                $recordSet->MoveNext();
            }
            $asc_desc['ASC'] = 'ASC';
            $asc_desc['DESC'] = 'DESC';
            $number_format[1] = '1,000.00';
            $number_format[2] = '1.000,00';
            $number_format[3] = '1 000.00';
            $number_format[4] = '1 000,00';
            $number_format[5] = '1\'000,00';
            $number_format[6] = '1-000 00';
            $money_format[1] = htmlentities($config['money_sign'], ENT_COMPAT, $config['charset']) . '1';
            $money_format[2] = '1' . htmlentities($config['money_sign'], ENT_COMPAT, $config['charset']);
            $money_format[3] = htmlentities($config['money_sign'], ENT_COMPAT, $config['charset']) . ' 1';
            $date_format[1] = 'mm/dd/yyyy';
            $date_format[2] = 'yyyy/dd/mm';
            $date_format[3] = 'dd/mm/yyyy';
            //Map Types
            // New Global Maps
            $map_types['global_mapquest'] = $lang['global_mapquest'];
            $map_types['global_multimap'] = $lang['global_multimap'];
            // Map Options
            $map_types['mapquest_AD'] = $lang['mapquest_AD'];
            $map_types['mapquest_AE'] = $lang['mapquest_AE'];
            $map_types['mapquest_AF'] = $lang['mapquest_AF'];
            $map_types['mapquest_AG'] = $lang['mapquest_AG'];
            $map_types['mapquest_AI'] = $lang['mapquest_AI'];
            $map_types['mapquest_AL'] = $lang['mapquest_AL'];
            $map_types['mapquest_AM'] = $lang['mapquest_AM'];
            $map_types['mapquest_AN'] = $lang['mapquest_AN'];
            $map_types['mapquest_AO'] = $lang['mapquest_AO'];
            $map_types['mapquest_AR'] = $lang['mapquest_AR'];
            $map_types['mapquest_AS'] = $lang['mapquest_AS'];
            $map_types['mapquest_AT'] = $lang['mapquest_AT'];
            $map_types['mapquest_AU'] = $lang['mapquest_AU'];
            $map_types['mapquest_AW'] = $lang['mapquest_AW'];
            $map_types['mapquest_AZ'] = $lang['mapquest_AZ'];
            $map_types['mapquest_BA'] = $lang['mapquest_BA'];
            $map_types['mapquest_BB'] = $lang['mapquest_BB'];
            $map_types['mapquest_BD'] = $lang['mapquest_BD'];
            $map_types['mapquest_BE'] = $lang['mapquest_BE'];
            $map_types['mapquest_BF'] = $lang['mapquest_BF'];
            $map_types['mapquest_BG'] = $lang['mapquest_BG'];
            $map_types['mapquest_BH'] = $lang['mapquest_BH'];
            $map_types['mapquest_BI'] = $lang['mapquest_BI'];
            $map_types['mapquest_BJ'] = $lang['mapquest_BJ'];
            $map_types['mapquest_BM'] = $lang['mapquest_BM'];
            $map_types['mapquest_BN'] = $lang['mapquest_BN'];
            $map_types['mapquest_BO'] = $lang['mapquest_BO'];
            $map_types['mapquest_BR'] = $lang['mapquest_BR'];
            $map_types['mapquest_BS'] = $lang['mapquest_BS'];
            $map_types['mapquest_BT'] = $lang['mapquest_BT'];
            $map_types['mapquest_BV'] = $lang['mapquest_BV'];
            $map_types['mapquest_BW'] = $lang['mapquest_BW'];
            $map_types['mapquest_BY'] = $lang['mapquest_BY'];
            $map_types['mapquest_BZ'] = $lang['mapquest_BZ'];
            $map_types['mapquest_CA'] = $lang['mapquest_CA'];
            $map_types['mapquest_CC'] = $lang['mapquest_CC'];
            $map_types['mapquest_CD'] = $lang['mapquest_CD'];
            $map_types['mapquest_CF'] = $lang['mapquest_CF'];
            $map_types['mapquest_CG'] = $lang['mapquest_CG'];
            $map_types['mapquest_CH'] = $lang['mapquest_CH'];
            $map_types['mapquest_CI'] = $lang['mapquest_CI'];
            $map_types['mapquest_CK'] = $lang['mapquest_CK'];
            $map_types['mapquest_CL'] = $lang['mapquest_CL'];
            $map_types['mapquest_CM'] = $lang['mapquest_CM'];
            $map_types['mapquest_CN'] = $lang['mapquest_CN'];
            $map_types['mapquest_CO'] = $lang['mapquest_CO'];
            $map_types['mapquest_CR'] = $lang['mapquest_CR'];
            $map_types['mapquest_CS'] = $lang['mapquest_CS'];
            $map_types['mapquest_CU'] = $lang['mapquest_CU'];
            $map_types['mapquest_CV'] = $lang['mapquest_CV'];
            $map_types['mapquest_CX'] = $lang['mapquest_CX'];
            $map_types['mapquest_CY'] = $lang['mapquest_CY'];
            $map_types['mapquest_CZ'] = $lang['mapquest_CZ'];
            $map_types['mapquest_DE'] = $lang['mapquest_DE'];
            $map_types['mapquest_DJ'] = $lang['mapquest_DJ'];
            $map_types['mapquest_DK'] = $lang['mapquest_DK'];
            $map_types['mapquest_DM'] = $lang['mapquest_DM'];
            $map_types['mapquest_DO'] = $lang['mapquest_DO'];
            $map_types['mapquest_DZ'] = $lang['mapquest_DZ'];
            $map_types['mapquest_EC'] = $lang['mapquest_EC'];
            $map_types['mapquest_EE'] = $lang['mapquest_EE'];
            $map_types['mapquest_EG'] = $lang['mapquest_EG'];
            $map_types['mapquest_EH'] = $lang['mapquest_EH'];
            $map_types['mapquest_ER'] = $lang['mapquest_ER'];
            $map_types['mapquest_ES'] = $lang['mapquest_ES'];
            $map_types['mapquest_ET'] = $lang['mapquest_ET'];
            $map_types['mapquest_FI'] = $lang['mapquest_FI'];
            $map_types['mapquest_FJ'] = $lang['mapquest_FJ'];
            $map_types['mapquest_FK'] = $lang['mapquest_FK'];
            $map_types['mapquest_FM'] = $lang['mapquest_FM'];
            $map_types['mapquest_FO'] = $lang['mapquest_FO'];
            $map_types['mapquest_FR'] = $lang['mapquest_FR'];
            $map_types['multimap_FR'] = $lang['multimap_FR'];
            $map_types['mapquest_GA'] = $lang['mapquest_GA'];
            $map_types['mapquest_GB'] = $lang['mapquest_GB'];
            $map_types['mapquest_GD'] = $lang['mapquest_GD'];
            $map_types['mapquest_GE'] = $lang['mapquest_GE'];
            $map_types['mapquest_GF'] = $lang['mapquest_GF'];
            $map_types['mapquest_GH'] = $lang['mapquest_GH'];
            $map_types['mapquest_GI'] = $lang['mapquest_GI'];
            $map_types['mapquest_GL'] = $lang['mapquest_GL'];
            $map_types['mapquest_GM'] = $lang['mapquest_GM'];
            $map_types['mapquest_GN'] = $lang['mapquest_GN'];
            $map_types['mapquest_GP'] = $lang['mapquest_GP'];
            $map_types['mapquest_GQ'] = $lang['mapquest_GQ'];
            $map_types['mapquest_GR'] = $lang['mapquest_GR'];
            $map_types['mapquest_GS'] = $lang['mapquest_GS'];
            $map_types['mapquest_GT'] = $lang['mapquest_GT'];
            $map_types['mapquest_GU'] = $lang['mapquest_GU'];
            $map_types['mapquest_GW'] = $lang['mapquest_GW'];
            $map_types['mapquest_GY'] = $lang['mapquest_GY'];
            $map_types['mapquest_GZ'] = $lang['mapquest_GZ'];
            $map_types['mapquest_HK'] = $lang['mapquest_HK'];
            $map_types['mapquest_HM'] = $lang['mapquest_HM'];
            $map_types['mapquest_HN'] = $lang['mapquest_HN'];
            $map_types['mapquest_HR'] = $lang['mapquest_HR'];
            $map_types['mapquest_HT'] = $lang['mapquest_HT'];
            $map_types['mapquest_HU'] = $lang['mapquest_HU'];
            $map_types['mapquest_ID'] = $lang['mapquest_ID'];
            $map_types['mapquest_IE'] = $lang['mapquest_IE'];
            $map_types['mapquest_IL'] = $lang['mapquest_IL'];
            $map_types['mapquest_IN'] = $lang['mapquest_IN'];
            $map_types['mapquest_IO'] = $lang['mapquest_IO'];
            $map_types['mapquest_IQ'] = $lang['mapquest_IQ'];
            $map_types['mapquest_IR'] = $lang['mapquest_IR'];
            $map_types['mapquest_IS'] = $lang['mapquest_IS'];
            $map_types['mapquest_IT'] = $lang['mapquest_IT'];
            $map_types['mapquest_JM'] = $lang['mapquest_JM'];
            $map_types['mapquest_JO'] = $lang['mapquest_JO'];
            $map_types['mapquest_JP'] = $lang['mapquest_JP'];
            $map_types['mapquest_KE'] = $lang['mapquest_KE'];
            $map_types['mapquest_KG'] = $lang['mapquest_KG'];
            $map_types['mapquest_KH'] = $lang['mapquest_KH'];
            $map_types['mapquest_KI'] = $lang['mapquest_KI'];
            $map_types['mapquest_KM'] = $lang['mapquest_KM'];
            $map_types['mapquest_KN'] = $lang['mapquest_KN'];
            $map_types['mapquest_KP'] = $lang['mapquest_KP'];
            $map_types['mapquest_KR'] = $lang['mapquest_KR'];
            $map_types['mapquest_KW'] = $lang['mapquest_KW'];
            $map_types['mapquest_KY'] = $lang['mapquest_KY'];
            $map_types['mapquest_KZ'] = $lang['mapquest_KZ'];
            $map_types['mapquest_LA'] = $lang['mapquest_LA'];
            $map_types['mapquest_LB'] = $lang['mapquest_LB'];
            $map_types['mapquest_LC'] = $lang['mapquest_LC'];
            $map_types['mapquest_LI'] = $lang['mapquest_LI'];
            $map_types['mapquest_LK'] = $lang['mapquest_LK'];
            $map_types['mapquest_LR'] = $lang['mapquest_LR'];
            $map_types['mapquest_LS'] = $lang['mapquest_LS'];
            $map_types['mapquest_LT'] = $lang['mapquest_LT'];
            $map_types['mapquest_LU'] = $lang['mapquest_LU'];
            $map_types['mapquest_LV'] = $lang['mapquest_LV'];
            $map_types['mapquest_LY'] = $lang['mapquest_LY'];
            $map_types['mapquest_MA'] = $lang['mapquest_MA'];
            $map_types['mapquest_MC'] = $lang['mapquest_MC'];
            $map_types['mapquest_MD'] = $lang['mapquest_MD'];
            $map_types['mapquest_MG'] = $lang['mapquest_MG'];
            $map_types['mapquest_MH'] = $lang['mapquest_MH'];
            $map_types['mapquest_MK'] = $lang['mapquest_MK'];
            $map_types['mapquest_ML'] = $lang['mapquest_ML'];
            $map_types['mapquest_MM'] = $lang['mapquest_MM'];
            $map_types['mapquest_MN'] = $lang['mapquest_MN'];
            $map_types['mapquest_MO'] = $lang['mapquest_MO'];
            $map_types['mapquest_MP'] = $lang['mapquest_MP'];
            $map_types['mapquest_MQ'] = $lang['mapquest_MQ'];
            $map_types['mapquest_MR'] = $lang['mapquest_MR'];
            $map_types['mapquest_MS'] = $lang['mapquest_MS'];
            $map_types['mapquest_MT'] = $lang['mapquest_MT'];
            $map_types['mapquest_MU'] = $lang['mapquest_MU'];
            $map_types['mapquest_MV'] = $lang['mapquest_MV'];
            $map_types['mapquest_MW'] = $lang['mapquest_MW'];
            $map_types['mapquest_MX'] = $lang['mapquest_MX'];
            $map_types['mapquest_MY'] = $lang['mapquest_MY'];
            $map_types['mapquest_MZ'] = $lang['mapquest_MZ'];
            $map_types['mapquest_NA'] = $lang['mapquest_NA'];
            $map_types['mapquest_NC'] = $lang['mapquest_NC'];
            $map_types['mapquest_NE'] = $lang['mapquest_NE'];
            $map_types['mapquest_NF'] = $lang['mapquest_NF'];
            $map_types['mapquest_NG'] = $lang['mapquest_NG'];
            $map_types['mapquest_NI'] = $lang['mapquest_NI'];
            $map_types['mapquest_NL'] = $lang['mapquest_NL'];
            $map_types['mapquest_NO'] = $lang['mapquest_NO'];
            $map_types['mapquest_NP'] = $lang['mapquest_NP'];
            $map_types['mapquest_NR'] = $lang['mapquest_NR'];
            $map_types['mapquest_NU'] = $lang['mapquest_NU'];
            $map_types['mapquest_NZ'] = $lang['mapquest_NZ'];
            $map_types['mapquest_OM'] = $lang['mapquest_OM'];
            $map_types['mapquest_PA'] = $lang['mapquest_PA'];
            $map_types['mapquest_PE'] = $lang['mapquest_PE'];
            $map_types['mapquest_PF'] = $lang['mapquest_PF'];
            $map_types['mapquest_PG'] = $lang['mapquest_PG'];
            $map_types['mapquest_PH'] = $lang['mapquest_PH'];
            $map_types['mapquest_PK'] = $lang['mapquest_PK'];
            $map_types['mapquest_PL'] = $lang['mapquest_PL'];
            $map_types['mapquest_PM'] = $lang['mapquest_PM'];
            $map_types['mapquest_PN'] = $lang['mapquest_PN'];
            $map_types['mapquest_PR'] = $lang['mapquest_PR'];
            $map_types['mapquest_PS'] = $lang['mapquest_PS'];
            $map_types['mapquest_PT'] = $lang['mapquest_PT'];
            $map_types['mapquest_PW'] = $lang['mapquest_PW'];
            $map_types['mapquest_PY'] = $lang['mapquest_PY'];
            $map_types['mapquest_QA'] = $lang['mapquest_QA'];
            $map_types['mapquest_RE'] = $lang['mapquest_RE'];
            $map_types['mapquest_RO'] = $lang['mapquest_RO'];
            $map_types['mapquest_RU'] = $lang['mapquest_RU'];
            $map_types['mapquest_RW'] = $lang['mapquest_RW'];
            $map_types['mapquest_SA'] = $lang['mapquest_SA'];
            $map_types['mapquest_SB'] = $lang['mapquest_SB'];
            $map_types['mapquest_SC'] = $lang['mapquest_SC'];
            $map_types['mapquest_SD'] = $lang['mapquest_SD'];
            $map_types['mapquest_SE'] = $lang['mapquest_SE'];
            $map_types['mapquest_SG'] = $lang['mapquest_SG'];
            $map_types['mapquest_SH'] = $lang['mapquest_SH'];
            $map_types['mapquest_SI'] = $lang['mapquest_SI'];
            $map_types['mapquest_SJ'] = $lang['mapquest_SJ'];
            $map_types['mapquest_SK'] = $lang['mapquest_SK'];
            $map_types['mapquest_SL'] = $lang['mapquest_SL'];
            $map_types['mapquest_SM'] = $lang['mapquest_SM'];
            $map_types['mapquest_SN'] = $lang['mapquest_SN'];
            $map_types['mapquest_SO'] = $lang['mapquest_SO'];
            $map_types['mapquest_SR'] = $lang['mapquest_SR'];
            $map_types['mapquest_ST'] = $lang['mapquest_ST'];
            $map_types['mapquest_SV'] = $lang['mapquest_SV'];
            $map_types['mapquest_SY'] = $lang['mapquest_SY'];
            $map_types['mapquest_SZ'] = $lang['mapquest_SZ'];
            $map_types['mapquest_TC'] = $lang['mapquest_TC'];
            $map_types['mapquest_TD'] = $lang['mapquest_TD'];
            $map_types['mapquest_TF'] = $lang['mapquest_TF'];
            $map_types['mapquest_TG'] = $lang['mapquest_TG'];
            $map_types['mapquest_TH'] = $lang['mapquest_TH'];
            $map_types['mapquest_TJ'] = $lang['mapquest_TJ'];
            $map_types['mapquest_TK'] = $lang['mapquest_TK'];
            $map_types['mapquest_TM'] = $lang['mapquest_TM'];
            $map_types['mapquest_TN'] = $lang['mapquest_TN'];
            $map_types['mapquest_TO'] = $lang['mapquest_TO'];
            $map_types['mapquest_TP'] = $lang['mapquest_TP'];
            $map_types['mapquest_TR'] = $lang['mapquest_TR'];
            $map_types['mapquest_TT'] = $lang['mapquest_TT'];
            $map_types['mapquest_TV'] = $lang['mapquest_TV'];
            $map_types['mapquest_TW'] = $lang['mapquest_TW'];
            $map_types['mapquest_TZ'] = $lang['mapquest_TZ'];
            $map_types['mapquest_UA'] = $lang['mapquest_UA'];
            $map_types['mapquest_UG'] = $lang['mapquest_UG'];
            $map_types['multimap_GB'] = $lang['multimap_uk'];
            $map_types['google_us'] = $lang['google_us'];
            $map_types['mapquest_US'] = $lang['mapquest_US'];
            $map_types['yahoo_us'] = $lang['yahoo_us'];
            $map_types['mapquest_UY'] = $lang['mapquest_UY'];
            $map_types['mapquest_UZ'] = $lang['mapquest_UZ'];
            $map_types['mapquest_VA'] = $lang['mapquest_VA'];
            $map_types['mapquest_VC'] = $lang['mapquest_VC'];
            $map_types['mapquest_VE'] = $lang['mapquest_VE'];
            $map_types['mapquest_VG'] = $lang['mapquest_VG'];
            $map_types['mapquest_VI'] = $lang['mapquest_VI'];
            $map_types['mapquest_VN'] = $lang['mapquest_VN'];
            $map_types['mapquest_VU'] = $lang['mapquest_VU'];
            $map_types['mapquest_WF'] = $lang['mapquest_WF'];
            $map_types['mapquest_WS'] = $lang['mapquest_WS'];
            $map_types['mapquest_YE'] = $lang['mapquest_YE'];
            $map_types['mapquest_YT'] = $lang['mapquest_YT'];
            $map_types['mapquest_ZA'] = $lang['mapquest_ZA'];
            $map_types['mapquest_ZM'] = $lang['mapquest_ZM'];
            $map_types['mapquest_ZW'] = $lang['mapquest_ZW'];
            $html = $page->get_template_section('number_format_style_block');
            $html = $page->form_options($number_format, $config['number_format_style'], $html);
            $page->replace_template_section('number_format_style_block', $html);

            $page->replace_tag('controlpanel_number_decimals_number_fields', htmlentities($config['number_decimals_number_fields'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_number_decimals_price_fields', htmlentities($config['number_decimals_price_fields'], ENT_COMPAT, $config['charset']));

            $html = $page->get_template_section('force_decimals_block');
            $html = $page->form_options($this->yes_no, $config['force_decimals'], $html);
            $page->replace_template_section('force_decimals_block', $html);

            $html = $page->get_template_section('money_format_block');
            $html = $page->form_options($money_format, $config['money_format'], $html);
            $page->replace_template_section('money_format_block', $html);

            $page->replace_tag('controlpanel_money_sign', htmlentities($config['money_sign'], ENT_COMPAT, $config['charset']));

            $html = $page->get_template_section('date_format_block');
            $html = $page->form_options($date_format, $config['date_format'], $html);
            $page->replace_template_section('date_format_block', $html);

            $html = $page->get_template_section('zero_price_block');
            $html = $page->form_options($this->yes_no, $config['zero_price'], $html);
            $page->replace_template_section('zero_price_block', $html);

            $html = $page->get_template_section('price_field_block');
            $html = $page->form_options($listing_field_name_options, $config['price_field'], $html);
            $page->replace_template_section('price_field_block', $html);

            $page->replace_tag('controlpanel_num_featured_listings', htmlentities($config['num_featured_listings'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_num_popular_listings', htmlentities($config['num_popular_listings'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_num_random_listings', htmlentities($config['num_random_listings'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_num_latest_listings', htmlentities($config['num_latest_listings'], ENT_COMPAT, $config['charset']));

            $html = $page->get_template_section('use_expiration_block');
            $html = $page->form_options($this->yes_no, $config['use_expiration'], $html);
            $page->replace_template_section('use_expiration_block', $html);

            $page->replace_tag('controlpanel_days_until_listings_expire', htmlentities($config['days_until_listings_expire'], ENT_COMPAT, $config['charset']));

            $html = $page->get_template_section('moderate_listings_block');
            $html = $page->form_options($this->yes_no, $config['moderate_listings'], $html);
            $page->replace_template_section('moderate_listings_block', $html);

            $html = $page->get_template_section('export_listings_block');
            $html = $page->form_options($this->yes_no, $config['export_listings'], $html);
            $page->replace_template_section('export_listings_block', $html);

            $html = $page->get_template_section('show_listedby_admin_block');
            $html = $page->form_options($this->yes_no, $config['show_listedby_admin'], $html);
            $page->replace_template_section('show_listedby_admin_block', $html);

            $html = $page->get_template_section('show_next_prev_listing_page_block');
            $html = $page->form_options($this->yes_no, $config['show_next_prev_listing_page'], $html);
            $page->replace_template_section('show_next_prev_listing_page_block', $html);

            $html = $page->get_template_section('show_notes_field_block');
            $html = $page->form_options($this->yes_no, $config['show_notes_field'], $html);
            $page->replace_template_section('show_notes_field_block', $html);

            $page->replace_tag('controlpanel_feature_list_separator', htmlentities($config['feature_list_separator'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_admin_listing_per_page', htmlentities($config['admin_listing_per_page'], ENT_COMPAT, $config['charset']));

            $page->replace_tag('controlpanel_search_step_max', htmlentities($config['search_step_max'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_listings_per_page', htmlentities($config['listings_per_page'], ENT_COMPAT, $config['charset']));

            $html = $page->get_template_section('search_sortby_block');
            $html = $page->form_options($search_field_sortby_options, $config['sortby'], $html);
            $page->replace_template_section('search_sortby_block', $html);

            $html = $page->get_template_section('search_sorttype_block');
            $html = $page->form_options($asc_desc, $config['sorttype'], $html);
            $page->replace_template_section('search_sorttype_block', $html);

            $html = $page->get_template_section('special_search_sortby_block');
            $html = $page->form_options($search_field_special_sortby_options, $config['special_sortby'], $html);
            $page->replace_template_section('special_search_sortby_block', $html);

            $html = $page->get_template_section('special_search_sorttype_block');
            $html = $page->form_options($asc_desc, $config['special_sorttype'], $html);
            $page->replace_template_section('special_search_sorttype_block', $html);

            $html = $page->get_template_section('configured_show_count_block');
            $html = $page->form_options($this->yes_no, $config['configured_show_count'], $html);
            $page->replace_template_section('configured_show_count_block', $html);

            $page->replace_tag('controlpanel_max_search_results', htmlentities($config['max_search_results'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_search_list_separator', htmlentities($config['search_list_separator'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_textarea_short_chars', htmlentities($config['textarea_short_chars'], ENT_COMPAT, $config['charset']));

            $html = $page->get_template_section('map_type_block');
            $html = $page->form_options($map_types, $config['map_type'], $html);
            $page->replace_template_section('map_type_block', $html);

            $html = $page->get_template_section('map_address_block');
            $html = $page->form_options($listing_field_name_options, $config['map_address'], $html);
            $page->replace_template_section('map_address_block', $html);

            $html = $page->get_template_section('map_address2_block');
            $html = $page->form_options($listing_field_name_options, $config['map_address2'], $html);
            $page->replace_template_section('map_address2_block', $html);

            $html = $page->get_template_section('map_address3_block');
            $html = $page->form_options($listing_field_name_options, $config['map_address3'], $html);
            $page->replace_template_section('map_address3_block', $html);

            $html = $page->get_template_section('map_address4_block');
            $html = $page->form_options($listing_field_name_options, $config['map_address4'], $html);
            $page->replace_template_section('map_address4_block', $html);

            $html = $page->get_template_section('map_city_block');
            $html = $page->form_options($listing_field_name_options, $config['map_city'], $html);
            $page->replace_template_section('map_city_block', $html);

            $html = $page->get_template_section('map_state_block');
            $html = $page->form_options($listing_field_name_options, $config['map_state'], $html);
            $page->replace_template_section('map_state_block', $html);

            $html = $page->get_template_section('map_zip_block');
            $html = $page->form_options($listing_field_name_options, $config['map_zip'], $html);
            $page->replace_template_section('map_zip_block', $html);

            $html = $page->get_template_section('map_country_block');
            $html = $page->form_options($listing_field_name_options, $config['map_country'], $html);
            $page->replace_template_section('map_country_block', $html);

            $html = $page->get_template_section('map_latitude_block');
            $html = $page->form_options($listing_field_name_options, $config['map_latitude'], $html);
            $page->replace_template_section('map_latitude_block', $html);

            $html = $page->get_template_section('map_longitude_block');
            $html = $page->form_options($listing_field_name_options, $config['map_longitude'], $html);
            $page->replace_template_section('map_longitude_block', $html);

            $page->replace_permission_tags();
            $page->replace_lang_template_tags(true);
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        }

        return $display;
    }

    public function ajax_configure_users()
    {
        global $conn, $lang, $config, $jscript;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        $display = '';
        if ($security === true) {
            // Open Connection to the Control Panel Table
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // DISABLE MULTILINGUAL SUPPORT AS IT IS NOT READY FOR THIS RELEASE
            $ml_support = false;
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/site_config_users.html');
            // Agent Template Field Names for Vcard Selection
            $sql = 'SELECT agentformelements_field_name, agentformelements_field_caption FROM ' . $config['table_prefix'] . 'agentformelements';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $agent_field_name_options[''] = '';
            while (!$recordSet->EOF) {
                $field_name = $recordSet->fields('agentformelements_field_name');
                $agent_field_name_options[$field_name] = $field_name . ' (' . $recordSet->fields('agentformelements_field_caption') . ')';
                $recordSet->MoveNext();
            }

            $html = $page->get_template_section('use_signup_image_verification_block');
            $html = $page->form_options($this->yes_no, $config['use_signup_image_verification'], $html);
            $page->replace_template_section('use_signup_image_verification_block', $html);

            $html = $page->get_template_section('require_email_verification_block');
            $html = $page->form_options($this->yes_no, $config['require_email_verification'], $html);
            $page->replace_template_section('require_email_verification_block', $html);

            $html = $page->get_template_section('moderate_members_block');
            $html = $page->form_options($this->yes_no, $config['moderate_members'], $html);
            $page->replace_template_section('moderate_members_block', $html);

            $html = $page->get_template_section('allow_member_signup_block');
            $html = $page->form_options($this->yes_no, $config['allow_member_signup'], $html);
            $page->replace_template_section('allow_member_signup_block', $html);

            //Agent Permissions
            $html = $page->get_template_section('moderate_agents_block');
            $html = $page->form_options($this->yes_no, $config['moderate_agents'], $html);
            $page->replace_template_section('moderate_agents_block', $html);

            $html = $page->get_template_section('allow_agent_signup_block');
            $html = $page->form_options($this->yes_no, $config['allow_agent_signup'], $html);
            $page->replace_template_section('allow_agent_signup_block', $html);

            $html = $page->get_template_section('agent_default_active_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_active'], $html);
            $page->replace_template_section('agent_default_active_block', $html);

            $html = $page->get_template_section('agent_default_admin_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_admin'], $html);
            $page->replace_template_section('agent_default_admin_block', $html);

            $html = $page->get_template_section('agent_default_edit_all_users_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_edit_all_users'], $html);
            $page->replace_template_section('agent_default_edit_all_users_block', $html);

            $html = $page->get_template_section('agent_default_edit_all_listings_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_edit_all_listings'], $html);
            $page->replace_template_section('agent_default_edit_all_listings_block', $html);

            $html = $page->get_template_section('agent_default_feature_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_feature'], $html);
            $page->replace_template_section('agent_default_feature_block', $html);

            $html = $page->get_template_section('agent_default_moderate_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_moderate'], $html);
            $page->replace_template_section('agent_default_moderate_block', $html);

            $html = $page->get_template_section('agent_default_logview_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_logview'], $html);
            $page->replace_template_section('agent_default_logview_block', $html);

            $html = $page->get_template_section('agent_default_edit_site_config_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_edit_site_config'], $html);
            $page->replace_template_section('agent_default_edit_site_config_block', $html);

            $html = $page->get_template_section('agent_default_edit_member_template_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_edit_member_template'], $html);
            $page->replace_template_section('agent_default_edit_member_template_block', $html);

            $html = $page->get_template_section('agent_default_edit_agent_template_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_edit_agent_template'], $html);
            $page->replace_template_section('agent_default_edit_agent_template_block', $html);

            $html = $page->get_template_section('agent_default_edit_listing_template_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_edit_listing_template'], $html);
            $page->replace_template_section('agent_default_edit_listing_template_block', $html);

            $html = $page->get_template_section('agent_default_can_export_listings_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_can_export_listings'], $html);
            $page->replace_template_section('agent_default_can_export_listings_block', $html);

            $html = $page->get_template_section('agent_default_canchangeexpirations_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_canchangeexpirations'], $html);
            $page->replace_template_section('agent_default_canchangeexpirations_block', $html);

            $html = $page->get_template_section('agent_default_editpages_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_editpages'], $html);
            $page->replace_template_section('agent_default_editpages_block', $html);

            $html = $page->get_template_section('agent_default_havevtours_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_havevtours'], $html);
            $page->replace_template_section('agent_default_havevtours_block', $html);

            $html = $page->get_template_section('agent_default_havefiles_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_havefiles'], $html);
            $page->replace_template_section('agent_default_havefiles_block', $html);

            $html = $page->get_template_section('agent_default_canManageAddons_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_canManageAddons'], $html);
            $page->replace_template_section('agent_default_canManageAddons_block', $html);

            $blog_type = [1 => $lang['blog_perm_subscriber'], 2 => $lang['blog_perm_contributor'], 3 => $lang['blog_perm_author'], 4 => $lang['blog_perm_editor']];
            $html = $page->get_template_section('agent_default_blogUserType_block');
            $html = $page->form_options($blog_type, $config['agent_default_blogUserType'], $html);
            $page->replace_template_section('agent_default_blogUserType_block', $html);

            $html = $page->get_template_section('agent_default_edit_all_leads_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_edit_all_leads'], $html);
            $page->replace_template_section('agent_default_edit_all_leads_block', $html);
            //$config["agent_default_edit_lead_template"]



            $html = $page->get_template_section('agent_default_edit_lead_template_block');
            $html = $page->form_options($this->yes_no, $config['agent_default_edit_lead_template'], $html);
            $page->replace_template_section('agent_default_edit_lead_template_block', $html);

            $page->replace_tag('controlpanel_agent_default_num_listings', htmlentities($config['agent_default_num_listings'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_agent_default_num_featuredlistings', htmlentities($config['agent_default_num_featuredlistings'], ENT_COMPAT, $config['charset']));

            $page->replace_tag('controlpanel_users_per_page', htmlentities($config['users_per_page'], ENT_COMPAT, $config['charset']));

            $html = $page->get_template_section('show_admin_on_agent_list_block');
            $html = $page->form_options($this->yes_no, $config['show_admin_on_agent_list'], $html);
            $page->replace_template_section('show_admin_on_agent_list_block', $html);

            //Agent VCard
            $html = $page->get_template_section('vcard_phone_block');
            $html = $page->form_options($agent_field_name_options, $config['vcard_phone'], $html);
            $page->replace_template_section('vcard_phone_block', $html);

            $html = $page->get_template_section('vcard_fax_block');
            $html = $page->form_options($agent_field_name_options, $config['vcard_fax'], $html);
            $page->replace_template_section('vcard_fax_block', $html);

            $html = $page->get_template_section('vcard_mobile_block');
            $html = $page->form_options($agent_field_name_options, $config['vcard_mobile'], $html);
            $page->replace_template_section('vcard_mobile_block', $html);

            $html = $page->get_template_section('vcard_address_block');
            $html = $page->form_options($agent_field_name_options, $config['vcard_address'], $html);
            $page->replace_template_section('vcard_address_block', $html);

            $html = $page->get_template_section('vcard_city_block');
            $html = $page->form_options($agent_field_name_options, $config['vcard_city'], $html);
            $page->replace_template_section('vcard_city_block', $html);

            $html = $page->get_template_section('vcard_state_block');
            $html = $page->form_options($agent_field_name_options, $config['vcard_state'], $html);
            $page->replace_template_section('vcard_state_block', $html);

            $html = $page->get_template_section('vcard_zip_block');
            $html = $page->form_options($agent_field_name_options, $config['vcard_zip'], $html);
            $page->replace_template_section('vcard_zip_block', $html);

            $html = $page->get_template_section('vcard_country_block');
            $html = $page->form_options($agent_field_name_options, $config['vcard_country'], $html);
            $page->replace_template_section('vcard_country_block', $html);

            $html = $page->get_template_section('vcard_notes_block');
            $html = $page->form_options($agent_field_name_options, $config['vcard_notes'], $html);
            $page->replace_template_section('vcard_notes_block', $html);

            $html = $page->get_template_section('vcard_url_block');
            $html = $page->form_options($agent_field_name_options, $config['vcard_url'], $html);
            $page->replace_template_section('vcard_url_block', $html);

            //Banned Settings
            $page->replace_tag('controlpanel_banned_domains_signup', htmlentities($config['banned_domains_signup'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_banned_ips_signup', htmlentities($config['banned_ips_signup'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_banned_ips_site', htmlentities($config['banned_ips_site'], ENT_COMPAT, $config['charset']));

            $page->replace_permission_tags();
            $page->replace_lang_template_tags(true);
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        }

        return $display;
    }

    public function ajax_configure_social()
    {
        global $conn, $lang, $config, $jscript;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        $display = '';
        if ($security === true) {
            // Open Connection to the Control Panel Table
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // DISABLE MULTILINGUAL SUPPORT AS IT IS NOT READY FOR THIS RELEASE
            $ml_support = false;
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/site_config_social.html');
            //Load Jscript
            $html = $page->get_template_section('email_notification_of_new_users_block');
            $html = $page->form_options($this->yes_no, $config['email_notification_of_new_users'], $html);
            $page->replace_template_section('email_notification_of_new_users_block', $html);

            $html = $page->get_template_section('email_notification_of_new_listings_block');
            $html = $page->form_options($this->yes_no, $config['email_notification_of_new_listings'], $html);
            $page->replace_template_section('email_notification_of_new_listings_block', $html);

            $html = $page->get_template_section('email_users_notification_of_new_listings_block');
            $html = $page->form_options($this->yes_no, $config['email_users_notification_of_new_listings'], $html);
            $page->replace_template_section('email_users_notification_of_new_listings_block', $html);

            $html = $page->get_template_section('email_information_to_new_users_block');
            $html = $page->form_options($this->yes_no, $config['email_information_to_new_users'], $html);
            $page->replace_template_section('email_information_to_new_users_block', $html);

            $html = $page->get_template_section('disable_referrer_check_block');
            $html = $page->form_options($this->yes_no, $config['disable_referrer_check'], $html);
            $page->replace_template_section('disable_referrer_check_block', $html);

            $html = $page->get_template_section('include_senders_ip_block');
            $html = $page->form_options($this->yes_no, $config['include_senders_ip'], $html);
            $page->replace_template_section('include_senders_ip_block', $html);

            $page->replace_tag('controlpanel_rss_title_featured', htmlentities($config['rss_title_featured'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_rss_desc_featured', htmlentities($config['rss_desc_featured'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_rss_listingdesc_featured', htmlentities($config['rss_listingdesc_featured'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_rss_limit_featured', htmlentities($config['rss_limit_featured'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_rss_title_lastmodified', htmlentities($config['rss_title_lastmodified'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_rss_desc_lastmodified', htmlentities($config['rss_desc_lastmodified'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_rss_listingdesc_lastmodified', htmlentities($config['rss_listingdesc_lastmodified'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_rss_limit_lastmodified', htmlentities($config['rss_limit_lastmodified'], ENT_COMPAT, $config['charset']));

            $page->replace_tag('controlpanel_rss_title_latestlisting', htmlentities($config['rss_title_latestlisting'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_rss_desc_latestlisting', htmlentities($config['rss_desc_latestlisting'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_rss_listingdesc_latestlisting', htmlentities($config['rss_listingdesc_latestlisting'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_rss_limit_latestlisting', htmlentities($config['rss_limit_latestlisting'], ENT_COMPAT, $config['charset']));

            $page->replace_tag('controlpanel_rss_title_blogposts', htmlentities($config['rss_title_blogposts'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_rss_desc_blogposts', htmlentities($config['rss_desc_blogposts'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_rss_title_blogcomments', htmlentities($config['rss_title_blogcomments'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_rss_desc_blogcomments', htmlentities($config['rss_desc_blogcomments'], ENT_COMPAT, $config['charset']));

            $page->replace_tag('baseurl', htmlentities($config['baseurl'], ENT_COMPAT, $config['charset']));

            $page->replace_tag('controlpanel_twitter_consumer_secret', htmlentities($config['twitter_consumer_secret'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('controlpanel_twitter_consumer_key', htmlentities($config['twitter_consumer_key'], ENT_COMPAT, $config['charset']));

            if ($config['twitter_auth'] == '' &&  $config['twitter_consumer_secret'] != '' &&  $config['twitter_consumer_key'] !== '') {
                $exception_message = '';
                $url = '';
                $connection = new TwitterOAuth($config['twitter_consumer_key'], $config['twitter_consumer_secret']);
                try {
                    $request_token = $connection->oauth('oauth/request_token', ['oauth_callback' => $config['baseurl'] . '/admin/index.php?action=twitterback']);
                    /* Save temporary credentials to session. */
                    $_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
                    $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
                } catch (Throwable $t) {
                    $exception_message = $t->getMessage() . "\n";
                    // Executed only in PHP 7, will not match in PHP 5
                }

                /* If last connection failed don't display authorization link. */
                switch ($connection->getLastHttpCode()) {
                    case 200:
                        /* Build authorize URL and redirect user to Twitter. */
                        $url = $connection->url('oauth/authorize', ['oauth_token' =>  $token]);
                        //$url = $connection->url("oauth/token", array("oauth_token" => $token));
                        //$url = $connection->getAuthorizeURL($token);
                        break;
                    case 415:
                        // Callback not approved. Crap
                        $url = '';
                        break;
                }
                if ($url != '') {
                    $page->replace_tag('twitter_auth', '<a href="' . $url . '" class="btn btn-primary">' . $lang['connect_to_twitter'] . '</a>');
                } else {
                    $page->replace_tag('twitter_auth', 'Could not connect to Twitter. Refresh the page or try again later.' . $exception_message);
                }
            } elseif ($config['twitter_auth'] != '') {
                $page->replace_tag('twitter_auth', '<a href="#" id="disconnect_twitter" class="btn btn-danger">' . $lang['disconnect_from_twitter'] . '</a>');
            } else {
                $page->replace_tag('twitter_auth', '');
            }
            $html = $page->get_template_section('twitter_new_listings_block');
            $html = $page->form_options($this->yes_no, $config['twitter_new_listings'], $html);
            $page->replace_template_section('twitter_new_listings_block', $html);

            $html = $page->get_template_section('twitter_update_listings_block');
            $html = $page->form_options($this->yes_no, $config['twitter_update_listings'], $html);
            $page->replace_template_section('twitter_update_listings_block', $html);

            $html = $page->get_template_section('twitter_new_blog_block');
            $html = $page->form_options($this->yes_no, $config['twitter_new_blog'], $html);
            $page->replace_template_section('twitter_new_blog_block', $html);

            $html = $page->get_template_section('twitter_new_listing_photo_block');
            $html = $page->form_options($this->yes_no, $config['twitter_listing_photo'], $html);
            $page->replace_template_section('twitter_new_listing_photo_block', $html);

            $sql = 'SELECT userdb_id, userdb_user_first_name, userdb_user_last_name, userdb_is_admin 
					FROM ' . $config['table_prefix'] . "userdb 
					WHERE userdb_is_agent = 'yes' 
					OR userdb_is_admin = 'yes' 
					ORDER BY userdb_is_admin 
					DESC,userdb_user_last_name,userdb_user_first_name";
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
                $userdb_is_admin = $recordSet->fields('userdb_is_admin');
                $all_agents[$agent_ID] = $agent_last_name . ', ' . $agent_first_name;
                if ($userdb_is_admin == 'yes') {
                    $all_agents[$agent_ID] .= ' *';
                }

                $recordSet->MoveNext();
            }
            $current_floor_agent = explode(',', $config['floor_agent']);
            $html = $page->get_template_section('floor_agent_block');
            $html = $page->form_options($all_agents, $current_floor_agent, $html);
            $page->replace_template_section('floor_agent_block', $html);

            $page->replace_permission_tags();
            $page->replace_lang_template_tags(true);
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        }

        return $display;
    }

    public function ajax_update_site_config()
    {
        global $conn, $lang, $config, $misc;
        header('Content-type: application/json');
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        $display = '';
        if ($security === true) {
            if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
            }
            global $misc;
            $sql = 'UPDATE ' . $config['table_prefix_no_lang'] . 'controlpanel SET ';
            $sql_part = '';
            foreach ($_POST as $field => $value) {
                if ($field == "token") {
                    continue;
                }
                $sql_field = $conn->addQ($field);
                if (is_array($value)) {
                    $value2 = '';
                    foreach ($value as $f) {
                        if ($value2 == '') {
                            $value2 = "$f";
                        } else {
                            $value2 .= ",$f";
                        }
                    }
                    $value2 = $conn->qStr($value2);
                    if ($sql_part == '') {
                        $sql_part = "`$sql_field` = $value2";
                    } else {
                        $sql_part .= " , `$sql_field` = $value2";
                    }
                } else {
                    $value = $conn->qStr($value);
                    if ($sql_part == '') {
                        $sql_part = "`$sql_field` = $value";
                    } else {
                        $sql_part .= " , `$sql_field` = $value";
                    }
                }
            }
            $sql .= $sql_part;
            $recordSet = $conn->Execute($sql);
            if (isset($POST['controlpanel_template'])) {
                unset($_SESSION['template']);
            }
            if (!$recordSet) {
                $misc->log_error($sql, 'skip');
                return json_encode(['error' => '1', 'error_msg' => $sql]);
            }
            return json_encode(['error' => '0', 'status' => $lang['configuration_saved']]);
        }
    }

    public function ajax_update_seouris()
    {
        global $conn, $lang, $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        $display = '';
        if ($security === true) {
            global $misc;
            foreach ($_POST as $field => $value) {
                if (strpos($field, '_slug') !== false) {
                    $slug = $misc->make_db_safe($value);
                    $action = str_replace('_slug', '', $field);
                    $action = $misc->make_db_safe($action);
                    $sql = 'UPDATE ' . $config['table_prefix_no_lang'] . 'seouri SET slug = ' . $slug . ' WHERE action = ' . $action;
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql, 'skip');
                        return json_encode(['error' => '1', 'error_msg' => $sql]);
                    }
                } elseif (strpos($field, '_uri') !== false) {
                    $uri = $misc->make_db_safe($value);
                    $action = str_replace('_uri', '', $field);
                    $action = $misc->make_db_safe($action);
                    $sql = 'UPDATE ' . $config['table_prefix_no_lang'] . 'seouri SET uri = ' . $uri . ' WHERE action = ' . $action;
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql, 'skip');
                        return json_encode(['error' => '1', 'error_msg' => $sql]);
                    }
                }
            }
            return json_encode(['error' => '0', 'status' => $lang['configuration_saved']]);
        }
    }

    public function ajax_smtp_test()
    {
        global $conn, $lang, $config;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        $display = '';

        if ($security === true) {
            if (isset($_POST['controlpanel_mailserver']) && !empty($_POST['controlpanel_mailserver'])) {
                $mailserver = $_POST['controlpanel_mailserver'];
            }
            if (isset($_POST['controlpanel_mailport']) && !empty($_POST['controlpanel_mailport'])) {
                $mailport = $_POST['controlpanel_mailport'];
            }
            if (isset($_POST['controlpanel_mailuser']) && !empty($_POST['controlpanel_mailuser'])) {
                $mailuser = $_POST['controlpanel_mailuser'];
            }
            if (isset($_POST['controlpanel_mailpass']) && !empty($_POST['controlpanel_mailpass'])) {
                $mailpass = $_POST['controlpanel_mailpass'];
            }

            //Create a new SMTP instance
            $smtp = new SMTP;
            //$smtp->IsSMTP(); // telling the class to use SMTP

            //Enable connection-level debug output
            //$smtp->do_debug = SMTP::DEBUG_CONNECTION;
            try {
                //Connect to an SMTP server
                if (!$smtp->connect($mailserver, $mailport)) {
                    throw new Exception('Connect failed');
                }
                //Say hello
                if (!$smtp->hello(gethostname())) {
                    throw new Exception('EHLO failed: ' . $smtp->getError()['error']);
                }
                //Get the list of ESMTP services the server offers
                $e = $smtp->getServerExtList();
                //If server can do TLS encryption, use it
                if (is_array($e) && array_key_exists('STARTTLS', $e)) {
                    $tlsok = $smtp->startTLS();
                    if (!$tlsok) {
                        throw new Exception('Failed to start encryption: ' . $smtp->getError()['error']);
                    }
                    //Repeat EHLO after STARTTLS
                    if (!$smtp->hello(gethostname())) {
                        throw new Exception('EHLO (2) failed: ' . $smtp->getError()['error']);
                    }
                    //Get new capabilities list, which will usually now include AUTH if it didn't before
                    $e = $smtp->getServerExtList();
                }
                //If server supports authentication, do it (even if no encryption)
                if (is_array($e) && array_key_exists('AUTH', $e)) {
                    if ($smtp->authenticate($mailuser, $mailpass)) {
                        return 'Connection successful!';
                    } else {
                        throw new Exception('Authentication failed: ' . $smtp->getError()['error']);
                    }
                }
            } catch (Exception $e) {
                return 'SMTP error: ' . $e->getMessage() . "\n";
            }
            //Whatever happened, close the connection.
            $smtp->quit(true);

            //return json_encode(array('error' => "0",'status' => $lang['configuration_saved']));
        }
    }

    public function ajax_export_emails()
    {
        global $conn, $lang, $config, $misc;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        $display = '';

        if ($security === true) {
            if (isset($_POST['email_export_type']) && !empty($_POST['email_export_type'])) {
                $export_type = $_POST['email_export_type'];

                switch ($export_type) {
                    case 'all':
                        $whereclause = "	WHERE userdb_active ='yes'";
                        $user_type = $lang['all'];
                        break;
                    case 'admins':
                        $whereclause = "	WHERE userdb_is_admin = 'yes'";
                        $user_type = $lang['user_manager_admins'];
                        break;
                    case 'agents':
                        $whereclause = "	WHERE userdb_is_agent = 'yes'";
                        $user_type = $lang['user_manager_agents'];
                        break;
                    case 'members':
                        $whereclause = "	WHERE userdb_is_agent = 'no' AND userdb_id <> 1";
                        $user_type = $lang['user_manager_members'];
                        break;
                    default:
                        return 'Error: Required export type is invalid';
                }

                $sql = 'SELECT userdb_emailaddress, userdb_user_first_name, userdb_user_last_name 
						FROM ' . $config['table_prefix'] . 'userdb 
						' . $whereclause . " 
						AND userdb_active = 'yes'";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                    return 'DB error';
                }

                $headings = ['userdb_emailaddress', 'userdb_user_first_name', 'userdb_user_last_name'];
                $fh = fopen('php://output', 'w');
                //fputs($fh, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
                fputcsv($fh, $headings);

                while (!$recordSet->EOF) {
                    fputcsv(
                        $fh,
                        [
                            $recordSet->fields('userdb_emailaddress'),
                            $recordSet->fields('userdb_user_first_name'),
                            $recordSet->fields('userdb_user_last_name'),
                        ]
                    );
                    $recordSet->MoveNext();
                }

                fclose($fh);
                $csv = ob_get_clean();

                header('Content-Type: text/csv');
                header("Content-Disposition: attachment; filename=\"$user_type.csv\";");

                return ($csv);
                exit;
            } else {
                return 'Error: Required export type missing';
            }
        } else {
            return 'Unauthorized access';
        }
    }

    public function view_warnings()
    {
        global $conn, $config, $lang;
        $counter = 0;
        $display = '';
        $warnings = '';
        if (ini_get('safe_mode')) {
            $warnings .= '<div class="warnings_message">' . $lang['warnings_safe_mode'] . '</div>';
            $counter++;
        }
        // CHECK MBString
        if (!in_array('mbstring', get_loaded_extensions()) && $config['controlpanel_mbstring_enabled'] == 1) {
            $warnings .= '<div class="warnings_message">' . $lang['warnings_mb_convert_encoding'] . '</div>';
            $counter++;
        }
        if (!in_array('curl', get_loaded_extensions())) {
            $warnings .= '<div class="warnings_message">' . $lang['curl_not_enabled'] . '</div>';
            $counter++;
        }
        if (!in_array('zip', get_loaded_extensions())) {
            $warnings .= '<div class="warnings_message">' . $lang['warnings_php_zip'] . '</div>';
            $counter++;
        }
        if (!in_array('gd', get_loaded_extensions())) {
            $warnings[] = '<div class="warnings_message">' . $this->lang['warnings_php_gd'] . '</div>';
        }
        $gdinfo = gd_info();
        if (!isset($gdinfo['FreeType Support']) || !$gdinfo['FreeType Support']) {
            $warnings[] = '<div class="warnings_message">' . $lang['warnings_php_freetype'] . '</div>';
        }
        // CHECK OpenSSL
        if (!in_array('openssl', get_loaded_extensions())) {
            $warnings .= '<div class="warnings_message">' . $lang['warnings_openssl'] . '</div>';
            $counter++;
        }

        // CHECK mod_rewrite AND htaccess file
        if (defined('APACHE_GET_MODULES')) {
            if (!in_array('mod_rewrite', apache_get_modules())) {
                $warnings .= '<div class="warnings_message">' . $lang['warnings_mod_rewrite'] . '</div>';
                $counter++;
            }
            if (!file_exists($config['basepath'] . '/.htaccess')) {
                $warnings .= '<div class="warnings_message">' . $lang['warnings_htaccess'] . '</div>';
                $counter++;
            }
        }

        // CHECK default password for userdb_user_name = admin
        $sql = 'SELECT userdb_user_password FROM ' . $config['table_prefix'] . 'userdb WHERE userdb_user_name = "admin"';
        $recordSet = $conn->Execute($sql);
        $default_admin_password = $recordSet->fields('userdb_user_password');
        if (password_verify('password', $default_admin_password)) {
            $warnings .= '<div class="warnings_message">' . $lang['warnings_admin_password'] . '</div>';
            $counter++;
        }
        // END CHECKING
        if ($counter > 0) {
            $display .= '<div id="warnings">' . $warnings . '</div>';
        }
        return $display;
    }
}
