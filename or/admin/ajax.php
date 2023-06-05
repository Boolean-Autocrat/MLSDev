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

@ini_set('pcre.backtrack_limit', '10000000'); // phpcs:ignore
@ini_set('precision', 14); // phpcs:ignore
// Register $config as a global variable
global $config, $conn, $css_file;
$css_file = '';

require_once dirname(__FILE__) . '/../include/common.php';
header('Cache-control: no-cache');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' data: 'unsafe-inline'; img-src * 'self' data:; font-src 'self' data:; worker-src blob: 'self';");

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
if (file_exists($filename) && !isset($_SESSION['language_template'])) {
    include_once $config['basepath'] . '/addons/multilingual/language/' . $config['lang'] . '/lang.inc.php';
} elseif (isset($_SESSION['language_template'])) {
    include_once $config['basepath'] . '/include/language/' . $_SESSION['language_template'] . '/lang.inc.php';
    include_once $config['basepath'] . '/addons/multilingual/language/' . $_SESSION['language_template'] . '/lang.inc.php';
}

require_once $config['basepath'] . '/include/core.inc.php';

// NEW TEMPLATE SYSTEM
$page = new page_admin_ajax();
//Create dumb page for the template engine
$page->page = $page->call_ajax();
$page->output_page();
session_write_close();
$conn->Close();
