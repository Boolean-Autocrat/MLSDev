<?php
if (file_exists(dirname(__FILE__) . '/c3.php')) {
    include_once dirname(__FILE__) . '/c3.php';
}
if (file_exists(dirname(__FILE__) . '/devignoreinstall')) {
    //Set Session INI to force garbage collection to run 100% of the time.
    @ini_set('session.gc_probability', 100); // phpcs:ignore
    @ini_set('session.gc_divisor', 100); // phpcs:ignore
    error_reporting(E_ALL);
} else {
    error_reporting(E_ALL ^ E_NOTICE);
}
//
// This Fixes XHTML Validation issues, with PHP
$start_mem = memory_get_usage(true);
@ini_set('pcre.backtrack_limit', '10000000'); // phpcs:ignore
@ini_set('precision', 14); // phpcs:ignore


// Make sure install file has been removed
$filename = dirname(__FILE__) . '/install/index.php';
if (file_exists($filename) && !file_exists(dirname(__FILE__) . '/devignoreinstall') && (!isset($_GET['action']) || $_GET['action'] != 'powered_by')) {
    if (!file_exists(dirname(__FILE__) . '/include/common.php')) {
        $host  = $_SERVER['HTTP_HOST'];
        // phpcs:ignore
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'install/index.php';
        header('Location: http://' . $host . $uri . '/' . $extra);
        exit;
    }
    die('<html><div style="color:red;text-align:center">You must delete the file ' . $filename . ' before you can access your open-realty install.</div></html>');
}

// Register global vars
global $config, $conn, $misc, $css_file;

$css_file = '';
require_once dirname(__FILE__) . '/include/common.php';
// Check for User Selected Language
if ($config['allow_language_change'] == 1) {
    if (isset($_POST['select_users_lang'])) {
        if (preg_match('/[^A-Za-z0-9_]/', $_POST['select_users_lang'])) {
            //File name contains non alphanum chars die to prevent file system attacks.
            die;
        }
        $_SESSION['users_lang'] = $_POST['select_users_lang'];
    }
}
if ($config['allow_template_change'] == 1) {
    // Check for User Selected Template
    if (isset($_POST['select_users_template'])) {
        if (!isset($_POST['token']) || !$misc->validate_csrf_token($_POST['token'])) {
            //File name contains non alphanum chars die to prevent file system attacks.
            http_response_code(500);
            echo 'CSRF token validation failed.';
            die;
        }
        if (preg_match('/[^A-Za-z0-9_]/', $_POST['select_users_template'])) {
            http_response_code(500);
            //File name contains non alphanum chars die to prevent file system attacks.
            die;
        }
        $_SESSION['template'] = $_POST['select_users_template'];
        $config['template'] = $_POST['select_users_template'];
        $config['template_path'] = $config['basepath'] . '/template/' . $config['template']; // leave off the trailing slashes
        $config['template_url'] = $config['baseurl'] . '/template/' . $config['template']; // leave off the trailing slashes
        unset($_POST['select_users_template']);
    }
    // Multilingual Add-on
    if (isset($_POST['selected_language_template'])) {
        if (preg_match('/[^A-Za-z0-9_]/', $_POST['selected_language_template'])) {
            //File name contains non alphanum chars die to prevent file system attacks.
            die;
        }
        $_SESSION['language_template'] = $_POST['selected_language_template'];
        $_SESSION['template'] = $_POST['selected_language_template'];
        $config['template'] = $_SESSION['template'];
        $config['template_path'] = $config['basepath'] . '/template/' . $config['template']; // leave off the trailing slashes
        $config['template_url'] = $config['baseurl'] . '/template/' . $config['template']; // leave off the trailing slashes
    } elseif (isset($_GET['selected_language_template'])) {
        if (preg_match('/[^A-Za-z0-9_]/', $_GET['selected_language_template'])) {
            //File name contains non alphanum chars die to prevent file system attacks.
            die;
        }
        $_SESSION['language_template'] = $_GET['selected_language_template'];
        $_SESSION['template'] = $_GET['selected_language_template'];
        $config['template'] = $_SESSION['template'];
        $config['template_path'] = $config['basepath'] . '/template/' . $config['template']; // leave off the trailing slashes
        $config['template_url'] = $config['baseurl'] . '/template/' . $config['template']; // leave off the trailing slashes
    }
}

// Check that the default email address has been changed to something other then an open-realty.org address.
$default_email = strpos($config['admin_email'], 'changeme@default.com');
if (!isset($_GET['action']) || $_GET['action'] != 'powered_by') {
    if ($default_email !== false) {
        die('<div style="color:red;text-align:center">You must set an administrative email address in the site configuration before you can use your site. </div>');
    }
}
// Add GetMicroTime Function
$start_time = $misc->getmicrotime();

// Deal with &amp; still being in the URL
foreach ($_GET as $k => $v) {
    if (strpos($k, 'amp;') !== false) {
        $new_k = str_replace('amp;', '', $k);
        $_GET[$new_k] = $v;
        unset($_GET[$k]);
    }
}
//Deal with googlebot double encoding URLS.
foreach ($_GET as $k => $v) {
    if (strpos($k, '%5B%5D') !== false) {
        $new_k = str_replace('%5B%5D', '', $k);
        $_GET[$new_k][] = $v;
        unset($_GET[$k]);
    }
}
// Start OutPut Buffer
ob_start();


if (!isset($_GET['printer_friendly'])) {
    $_GET['printer_friendly'] = false;
}
// Determine which Language File to Use
if (isset($_SESSION['users_lang']) && $_SESSION['users_lang'] != $config['lang']) {
    // phpcs:ignore
    include_once $config['basepath'] . '/include/language/' . $_SESSION['users_lang'] . '/lang.inc.php';
} else {
    // Use Site Default Language
    unset($_SESSION['users_lang']);
    // phpcs:ignore
    include_once $config['basepath'] . '/include/language/' . $config['lang'] . '/lang.inc.php';
}

// Multilingual Add-on
$filename = $config['basepath'] . '/addons/multilingual/addon.inc.php';
if (file_exists($filename)) {
    if (!isset($_SESSION['language_template'])) {
        // phpcs:ignore
        include_once $config['basepath'] . '/addons/multilingual/language/' . $config['lang'] . '/lang.inc.php';
    } elseif (isset($_SESSION['language_template'])) {
        // phpcs:ignore
        include_once $config['basepath'] . '/include/language/' . $_SESSION['language_template'] . '/lang.inc.php';
        // phpcs:ignore
        include_once $config['basepath'] . '/addons/multilingual/language/' . $_SESSION['language_template'] . '/lang.inc.php';
    }
}

require_once $config['basepath'] . '/include/core.inc.php';
$page = new page_user;
require_once $config['basepath'] . '/include/tracking.inc.php';
$tracking = new tracking;
if (!isset($_GET['action'])) {
    $page->magicURIParser();
} else {
    $page->set_session_referrer();
}

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    include_once $config['basepath'] . '/include/login.inc.php';
    $login = new login();
    $login->log_out('user');
}
if (strpos($_GET['action'], '://') !== false) {
    $_GET['action'] = 'index';
}
//Load Powered BY
if (isset($_GET['action']) && $_GET['action'] == 'powered_by') {
    echo $page->output_powered_by();
    ob_end_flush();
    die;
}
//Load CSS
if (isset($_GET['action']) && $_GET['action'] == 'load_css' && isset($_GET['css_file'])) {
    if (preg_match('/[^A-Za-z0-9_]/', $_GET['css_file'])) {
        //File name contains non alphanum chars die to prevent file system attacks.
        die;
    }
    global $config, $listing_id, $lang;
    $file = $config['template_path'] . '/' . $_GET['css_file'] . '.css';
    //If we are using seo urls, check to see if page should be sent or if the cached copy is ok
    if ($config['url_style'] != '1') {
        if (file_exists($config['template_path'] . '/' . $_GET['css_file'] . '.css')) {
            $my_file = $config['template_path'] . '/' . $_GET['css_file'] . '.css';
        } elseif (file_exists($config['basepath'] . '/template/default/' . $_GET['css_file'] . '.css')) {
            $my_file = $config['basepath'] . '/template/default/' . $_GET['css_file'] . '.css';
        } else {
            header('HTTP/1.1 404 File Not Found');
            exit;
        }

        $last_modified_time = filemtime($my_file);
        $etag = md5_file($my_file);
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified_time) . ' GMT');
        header('Etag: ' . $etag);
        if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time) ||
            (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag)
        ) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }
    }
    $page->load_page($file);
    $page->replace_tags(['company_logo', 'baseurl', 'template_url']);
    $css_text = $page->return_page();
    header('Content-type: text/css');
    echo $css_text;
    ob_end_flush();
    die;
}
@header('Cache-control: private'); //IE6 Form Refresh Fix
// Allow Addons/Functions to pass back custom jscript.
global $jscript, $jscript_last, $meta_follow, $meta_index, $meta_canonical;
$meta_follow = true;
if (isset($_GET['printer_friendly']) && $_GET['printer_friendly'] == 'yes') {
    $meta_index = false;
} else {
    $meta_index = true;
}
$meta_canonical = null;

$jscript = '';
$jscript_last = '';

//Load RSS
//Handle Legacy RSS Feed Calls
//Handle Logo
if (isset($_GET['action']) && $_GET['action'] === 'listingqrcode') {
    $page->page = '{content}';
} elseif (isset($_GET['action']) && strpos($_GET['action'], 'rss_') === 0) {
    $page->page = '{content}';
} elseif (isset($_GET['action']) && $_GET['action'] == 'show_rss' && isset($_GET['rss_feed'])) {
    if (preg_match('/[^A-Za-z0-9_]/', $_GET['rss_feed'])) {
        //File name contains non alphanum chars die to prevent file system attacks.
        die;
    }
    $page->page = '{content}';
} else {
    //This is a regular Open-Realty page determine which template to use.
    if (isset($_GET['popup']) && $_GET['popup'] != 'blank') {
        $page->load_page($config['template_path'] . '/popup.html', true);
    } elseif (isset($_GET['popup']) && $_GET['popup'] == 'blank') {
        $page->load_page($config['template_path'] . '/blank.html', true);
    } elseif (isset($_GET['printer_friendly']) && $_GET['printer_friendly'] == 'yes') {
        $page->load_page($config['template_path'] . '/printer_friendly.html', true, true);
    } else {
        if (isset($_GET['PageID']) && file_exists($config['template_path'] . '/page' . $_GET['PageID'] . '_main.html')) {
            $page->load_page($config['template_path'] . '/page' . $_GET['PageID'] . '_main.html', true, true);
        } elseif ($_GET['action'] == 'index' && file_exists($config['template_path'] . '/page1_main.html')) {
            $page->load_page($config['template_path'] . '/page1_main.html', true, true);
        } elseif ($_GET['action'] == 'searchresults' && file_exists($config['template_path'] . '/searchresults_main.html')) {
            $page->load_page($config['template_path'] . '/searchresults_main.html', true, true);
        } elseif ($_GET['action'] == 'listingview' && file_exists($config['template_path'] . '/listingview_main.html')) {
            $page->load_page($config['template_path'] . '/listingview_main.html', true, true);
        } else {
            $page->load_page($config['template_path'] . '/main.html', true, true);
        }
    }
}
// Are we in maintenance mode?
if ($config['maintenance_mode'] == 1 && (!isset($_SESSION['username']) || (isset($_SESSION['username']) && $_SESSION['username'] !== 'admin'))) {
    @header('HTTP/1.1 503 Service Temporarily Unavailable');
    @header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 7200'); // in seconds
    $page->load_page($config['template_path'] . '/maintenance_mode.html', true);
}
//Is User Banned?
if ($misc->is_banned_site_ip()) {
    $page->load_page($config['template_path'] . '/banned_ip.html', true);
}
//Handle Google Auth Redirect
if (isset($_GET['code']) && isset($_GET['scope']) && (!isset($_GET['action']) || $_GET['action'] == 'notfound')) {
    include_once $config['basepath'] . '/include/login.inc.php';
    $login = new login();
    $login_status = $login->loginCheck('Member');
    if ($login_status !== true) {
        $page->replace_tag('content', $login_status);
    }
    if (isset($_GET['action']) && $_GET['action'] == 'notfound') {
        $_GET['action']='index';
    }
}

$page->replace_tags(['content']);


//Replace Permission tags first
$page->replace_foreach_pclass_block();
$page->replace_if_addon_block();
$page->replace_custom_agent_search_block();
$page->replace_custom_listing_search_block();
$page->replace_custom_blog_search_block();
$page->replace_current_user_tags();
$page->replace_permission_tags();
$page->replace_urls();
$page->replace_meta_template_tags();
$page->replace_blog_template_tags();
$page->replace_search_field_tags();
$page->auto_replace_tags();
// Load js last to make sure all custom js was added
$page->replace_tags(['load_js', 'load_ORjs', 'load_js_last']);
//Replace Languages
$page->replace_lang_template_tags();
$page->replace_css_template_tags();
$page->output_page();

// Display TIme
$end_time = $misc->getmicrotime();
$render_time = $end_time - $start_time;
$track_status = $tracking->record($render_time);


if (isset($config['render_time']) && $config['render_time'] == true) {
    $render_time = sprintf('%.3f', $render_time);
    if (isset($_GET['popup']) && $_GET['popup'] == 'blank') {
    } else {
        echo '<!-- This page was generated in ' . $render_time . ' seconds -->';
    }
}
if (!function_exists('memory_get_peak_usage')) {
    $peak_mem = 'NA';
} else {
    $peak_mem = memory_get_peak_usage(true);
}
$end_mem = memory_get_usage(false);

if (isset($config['mem_usage']) && $config['mem_usage'] == true) {
    echo '<!-- Start Memory: ' . $start_mem . '
		End Memory: ' . $end_mem . '
		Peak Memory: ' . $peak_mem . '
		-->';
}
ob_end_flush();
session_write_close();
$conn->Close();
