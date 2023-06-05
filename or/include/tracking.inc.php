<?php
global $config;
//require_once $config['basepath'] . '/vendor/browscap/browscap-php/src/Browscap.php';

class tracking
{
    public function view_statistics($app_status_text = '')
    {
        global $conn, $config, $lang, $jscript, $misc;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        $display = '';
        $yes_no = [0 => 'No', 1 => 'Yes'];

        $page->load_page($config['admin_template_path'] . '/site_statistics.html');
        $html = $page->get_template_section('enable_tracking_block');
        $html = $page->form_options($yes_no, $config['enable_tracking'], $html);
        $page->replace_template_section('enable_tracking_block', $html);

        $html = $page->get_template_section('enable_tracking_crawlers_block');
        $html = $page->form_options($yes_no, $config['enable_tracking_crawlers'], $html);
        $page->replace_template_section('enable_tracking_crawlers_block', $html);

        $page->replace_tag('application_status_text', $app_status_text);

        $page->replace_permission_tags();
        $page->replace_lang_template_tags(true);
        $page->auto_replace_tags('', true);
        $display .= $page->return_page();
        return $display;
    }

    public function record($render_time)
    {
        global $conn, $config, $lang, $misc;
        if ($config['enable_tracking']==0 || php_sapi_name() == 'cli') {
            return false;
        }
        $cacheDir = $config['basepath'] . '/files/browsercap_cache';

        $fileCache = new \League\Flysystem\Local\LocalFilesystemAdapter($cacheDir);
        $filesystem = new \League\Flysystem\Filesystem($fileCache);
        $cache = new \MatthiasMullie\Scrapbook\Psr16\SimpleCache(
            new \MatthiasMullie\Scrapbook\Adapters\Flysystem($filesystem)
        );

        $logger = new \Monolog\Logger('name');

        $bc = new \BrowscapPHP\Browscap($cache, $logger);
        $arrayFormatter = new \BrowscapPHP\Formatter\LegacyFormatter();
        $bc->setFormatter($arrayFormatter);

        if (isset($_SERVER['HTTP_X_FORWARD_FOR'])) {
            $tracking_ip = $_SERVER['HTTP_X_FORWARD_FOR'];
        } else {
            $tracking_ip = $_SERVER['REMOTE_ADDR'];
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $tacking_agentstring = $_SERVER['HTTP_USER_AGENT'];
            $browser_data = (array)$bc->getBrowser($tacking_agentstring);
            $tracking_browser = $browser_data['Browser'];
            $tracking_browserversion = $browser_data['Version'];
            $tracking_os = $browser_data['Platform'];
            $is_crawler = $browser_data['Crawler'];
            if ($is_crawler == true && $config['enable_tracking_crawlers'] == 0) {
                //Skip Crawlers
                return false;
            }
        } else {
            $tacking_agentstring = '';
            $tracking_browser = '';
            $tracking_browserversion = '';
            $tracking_os = '';
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $tracking_referal = $_SERVER['HTTP_REFERER'];
        } else {
            $tracking_referal = '';
        }
        if (isset($_SESSION['userID'])) {
            $tracking_user = $_SESSION['userID'];
        } else {
            $tracking_user = 0;
        }
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) {
            $tracking_link_uri = 'https';
        } else {
            $tracking_link_uri = 'http';
        }
        $tracking_link_uri .= '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        //Determine Interal Location
        $link_id = 0;
        $link_type = '';
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'listingview':
                    $link_type = 'listingview';
                    if (isset($_GET['listingID'])) {
                        $link_id = $_GET['listingID'];
                    }
                    break;
                case 'blog_view_article':
                    $link_type = 'blog_view_article';
                    if (isset($_GET['ArticleID'])) {
                        $link_id = $_GET['ArticleID'];
                    }
                    break;
                case 'blog_archive':
                    $link_type = 'blog_archive';
                    break;
                case 'page_display':
                    $link_type = 'page_display';
                    if (isset($_GET['PageID'])) {
                        $link_id = $_GET['PageID'];
                    }
                    break;
                case 'agent':
                    $link_type = 'agent';
                    if (isset($_GET['user'])) {
                        $link_id = $_GET['user'];
                    }
                    break;
                case 'blog_tag':
                    $link_type = 'blog_tag';
                    if (isset($_GET['tag_id'])) {
                        $link_id = $_GET['tag_id'];
                    }
                    break;
                case 'blog_cat':
                    $link_type = 'blog_cat';
                    if (isset($_GET['cat_id'])) {
                        $link_id = $_GET['cat_id'];
                    }
                    break;
                case 'view_listing_image':
                    $link_type = 'view_listing_image';
                    if (isset($_GET['image_id'])) {
                        $link_id = $_GET['image_id'];
                    }
                    break;
                case 'searchpage':
                    $link_type = 'searchpage';
                    break;
                case 'searchresults':
                    $link_type = 'searchresults';
                    break;
                case 'blog_index':
                    $link_type = 'blog_index';
                    break;
                case 'view_users':
                    $link_type = 'view_users';
                    break;
                case 'signup':
                    if (isset($_GET['type'])) {
                        if ($_GET['type'] == 'member') {
                            $link_type = 'member_signup';
                        } elseif ($_GET['type'] == 'agent') {
                            $link_type = 'agent_signup';
                        }
                    }
                    break;
                case 'member_login':
                    $link_type = 'member_login';
                    break;
                case 'view_favorites':
                    $link_type = 'view_favorites';
                    break;
                case 'calculator':
                    $link_type = 'calculator';
                    break;
                case 'view_saved_searches':
                    $link_type = 'view_saved_searches';
                    break;
                case 'edit_profile':
                    $link_type = 'edit_profile';
                    break;
                case 'index':
                    $link_type = 'index';
                    break;
                default:
                    return false;
                    break;
            }
        }
        if ($link_type == '') {
            return false;
        }
        //Make SQL Safe
        $sql_link_type = $misc->make_db_safe($link_type);
        $sql_link_id = intval($link_id);
        $sql_tracking_ip = $misc->make_db_safe($tracking_ip);
        $sql_tacking_agentstring = $misc->make_db_safe($tacking_agentstring);
        $sql_tracking_browser = $misc->make_db_safe($tracking_browser);
        $sql_tracking_browserversion = $misc->make_db_safe($tracking_browserversion);
        $sql_tracking_os = $misc->make_db_safe($tracking_os);
        $sql_tracking_referal = $misc->make_db_safe($tracking_referal);
        $sql_tracking_user =  intval($tracking_user);
        $sql_tracking_link_uri = $misc->make_db_safe($tracking_link_uri);
        $sql_tracking_timestamp = $misc->make_db_safe(time());
        $sql_tracking_loadtime = intval($render_time);

        $sql = 'INSERT INTO ' . $config['table_prefix_no_lang'] . "tracking
		(tracking_timestamp,userdb_id,tracking_ip,tracking_referal,tracking_link_type,
		tracking_link_type_id,tracking_link_url,tracking_agentstring,tracking_browser,
		tracking_browserversion,tracking_os,tracking_loadtime)
		VALUES
		($sql_tracking_timestamp,$sql_tracking_user,$sql_tracking_ip,$sql_tracking_referal,$sql_link_type,
		$sql_link_id,$sql_tracking_link_uri,$sql_tacking_agentstring,$sql_tracking_browser,
		$sql_tracking_browserversion,$sql_tracking_os,$sql_tracking_loadtime);";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
    }

    public function clear_statistics_log()
    {
        global $conn, $config, $lang, $misc;

        $display = '';
        //$display .= "<h3>$lang[log_delete]</h3>";
        // Check for Admin privs before doing anything
        if ($_SESSION['admin_privs'] == 'yes') {
            // find the number of log items
            $sql = 'TRUNCATE TABLE ' . $config['table_prefix_no_lang'] . 'tracking';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
                $display .= $this->view_statistics($lang['log_clear_error']);
            } else {
                $misc->log_action($lang['log_reset']);
                $display .= $this->view_statistics($lang['log_cleared']);
            }
        } else {
            $display .= $this->view_statistics($lang['clear_log_need_privs']);
        }
        return $display;
    }
}
