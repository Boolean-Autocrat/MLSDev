<?php
if (file_exists(dirname(__FILE__) . '/../c3.php')) {
    include_once dirname(__FILE__) . '/../c3.php';
}
//Support CLI Commands
if (php_sapi_name() == 'cli') {
    parse_str($argv[1], $_POST);
    parse_str($argv[2], $_GET);
}

if (file_exists(dirname(__FILE__) . '/../devignoreinstall')) {
    //Set Session INI to force garbage collection to run 100% of the time.
    @ini_set('session.gc_probability', 100);
    @ini_set('session.gc_divisor', 100);
    error_reporting(E_ALL);
} else {
    error_reporting(E_ALL ^ E_NOTICE);
}
// This Fixes XHTML Validation issues, with PHP
$start_mem = memory_get_usage(true);
@ini_set('pcre.backtrack_limit', '10000000'); // phpcs:ignore
@ini_set('precision', 14); // phpcs:ignore


// Register $config as a global variable
global $config, $conn, $misc;
ob_start();
require_once dirname(__FILE__) . '/../include/common.php';


// Start OutPut Buffer
if (!isset($_GET['printer_friendly'])) {
    $_GET['printer_friendly'] = false;
}
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
// Multilingual Add-on
if ($config['allow_template_change'] == 1) {
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

// Determine which Language File to Use
if (isset($_SESSION['users_lang']) && $_SESSION['users_lang'] != $config['lang']) {
    include_once $config['basepath'] . '/include/language/' . $_SESSION['users_lang'] . '/lang.inc.php';
} else {
    // Use Site Default Language
    unset($_SESSION['users_lang']);
    include_once $config['basepath'] . '/include/language/' . $config['lang'] . '/lang.inc.php';
}
// Multilingual Add-on
$filename = $config['basepath'] . '/addons/multilingual/addon.inc.php';
if (file_exists($filename)) {
    if (!isset($_SESSION['language_template'])) {
        include_once $config['basepath'] . '/addons/multilingual/language/' . $config['lang'] . '/lang.inc.php';
    } elseif (isset($_SESSION['language_template'])) {
        include_once $config['basepath'] . '/include/language/' . $_SESSION['language_template'] . '/lang.inc.php';
        include_once $config['basepath'] . '/addons/multilingual/language/' . $_SESSION['language_template'] . '/lang.inc.php';
    }
}
require_once $config['basepath'] . '/include/login.inc.php';
$login = new login();

if (isset($_GET['action']) && $_GET['action'] == 'log_out') {
    $login->log_out();
}

require_once $config['basepath'] . '/include/core.inc.php';
$page = new page_admin();

if (!isset($_GET['action'])) {
    $page->magicURIParser(true);
}
if (strpos($_GET['action'], '://') !== false || $_GET['action'] == 'notfound') {
    $_GET['action'] = 'index';
}
// if (isset($_POST['orapi_query'])) {
//     include_once $config['basepath'] . '/api/api.inc.php';
//     $api = new api();
//     $api_result = $api->load_remote_api($_POST['orapi_query']);
//     echo $api_result;
//     exit;
// }

$start_time = $misc->getmicrotime();

//Load CSS
if ($_GET['action'] == 'load_css' && isset($_GET['css_file'])) {
    if (preg_match('/[^A-Za-z0-9_\-]/', $_GET['css_file'])) {
        //File name contains non alphanum chars die to prevent file system attacks.
        die;
    }
    global $config, $listing_id, $lang;
    $file = $config['admin_template_path'] . '/' . $_GET['css_file'] . '.css';
    //If we are using seo urls, check to see if page should be sent or if the cached copy is ok
    if ($config['url_style'] != '1') {
        if (file_exists($config['admin_template_path'] . '/' . $_GET['css_file'] . '.css')) {
            $my_file = $config['admin_template_path'] . '/' . $_GET['css_file'] . '.css';
        } elseif (file_exists($config['basepath'] . '/admin/template/default/' . $_GET['css_file'] . '.css')) {
            $my_file = $config['basepath'] . '/admin/template/default/' . $_GET['css_file'] . '.css';
        } else {
            header('HTTP/1.1 404 File Not Found');
            exit;
        }

        $last_modified_time = filemtime($my_file);
        $etag = md5_file($my_file);
        header('Cache-Control: public');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified_time) . ' GMT');
        header("Etag: $etag");

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
    die;
}
if (isset($_GET['popup']) && $_GET['popup'] != 'blank') {
    $page->load_page($config['admin_template_path'] . '/popup.html', true);
} else {
    $page->load_page($config['admin_template_path'] . '/main.html', true, true);
}
// Allow Addons/Functions to pass back custom jscript.
global $jscript, $jscript_last;
$jscript = '';
$jscript_last = '';

// Load Old JS Body Stuff. - Remove for 2.6 by placing calls in the correct functions to reduce load on pages where this is not needed.
require_once $config['basepath'] . '/include/admin.inc.php';
$admin = new general_admin();
// header("Content-Security-Policy-Report-Only: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' data: 'unsafe-inline';  img-src 'self' data:; font-src 'self' data:; worker-src blob: 'self';");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' data: 'unsafe-inline'; img-src * 'self' data:; font-src 'self' data:; worker-src blob: 'self';");

$page->replace_tags(['content']);
$page->replace_current_user_tags();
$page->replace_permission_tags();
//$login_status = $login->loginCheck('Agent');
$page->replace_urls();
$page->auto_replace_tags('', true);
$page->replace_tags(['load_js', 'load_ORjs', 'load_js_last']);
$page->replace_css_template_tags(true);
$page->replace_meta_template_tags();
$page->replace_lang_template_tags(true);
$page->output_page();


// Close Buffer
$buffer = ob_get_contents();
ob_end_clean();
echo $buffer;

// NEW TEMPLATE SYSTEM END
$end_time = $misc->getmicrotime();
$render_time = sprintf('%.16f', $end_time - $start_time);
echo "<!-- This page was generated in $render_time seconds -->";
$end_mem = memory_get_usage(false);
if (!function_exists('memory_get_peak_usage')) {
    $peak_mem = 'NA';
} else {
    $peak_mem = memory_get_peak_usage(true);
}
if (isset($config['mem_usage']) && $config['mem_usage'] == true) {
    echo '<!-- Start Memory: ' . $start_mem . '
		End Memory: ' . $end_mem . '
		Peak Memory: ' . $peak_mem . '
		-->';
}
session_write_close();
$conn->Close();
