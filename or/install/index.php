<?php
if (file_exists(dirname(__FILE__) . '/../c3.php')) {
    // define('C3_CODECOVERAGE_MEDIATE_STORAGE', dirname(__FILE__) . '/../c3tmp');
    // define('C3_CODECOVERAGE_ERROR_LOG_FILE', dirname(__FILE__) . '/../tests/_output/c3_error.log');
    include_once dirname(__FILE__) . '/../c3.php';
}
error_reporting(E_ALL ^ E_NOTICE);
// just so we know it is broken
session_start([
    "cookie_httponly" => true,
    "cookie_samesite" => "Strict",
    "use_strict_mode" => true,
    "sid_bits_per_character" => 6
]);
require_once dirname(__FILE__).'/base_installer.php';
$installer = new installer();
$installer->show_header();
$installer->run_installer();
$installer->show_footer();
