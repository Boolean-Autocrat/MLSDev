<?php
// ENGLISH LANGUAGE FILE
$lang = array();
//Step 1: Check File Permissions:
$lang['install_Page_Title'] = "Open-Realty Install";
$lang['install_version_warn'] = "Your version of php is not able to run the current version of Open-Realty Installation has been cancelled";
$lang['install_sqlversion_warn'] = "Your version of mysql is not able to run the current version of Open-Realty Installation has been cancelled";
$lang['install_php_version'] = "Your Current PHP version is ";
$lang['install_sql_version'] = "Your Current MySql version is ";
$lang['install_php_required'] = "The current version of Open-Realty requires a minimum PHP version of ";
$lang['install_sql_required'] = "The current version of Open-Realty requires a minimum MySql version of ";
$lang['install_welcome'] = "Welcome to the Open-Realty install tool.";
$lang['install_intro'] = "This tool will guide you through setting up your Open-Realty install. Before you begin you must have created a blank database on your system. You must also have file permissions set so the following files and directories are writeable by the web server.";
$lang['install_step_one_header'] = "Step 1: Check File Permissions:";
$lang['install_Permission_on'] = "Permission on";
$lang['install_are_correct'] = "are correct";
$lang['install_are_incorrect'] = "are incorrect";
$lang['install_all_correct'] = "All Permissions are correct.";
$lang['install_continue'] = "Click To Continue Installation";
$lang['install_please_fix'] = "Please have your host enable the above requirements.";

//Step 1: Determine Install Type
$lang['install_select_type'] = 'Select Installation Type:';
$lang['install_new'] = 'New Install Of Open-Realty';
$lang['move'] = 'Update Path and URL information only';
$lang['upgrade_200'] = 'Upgrade from Open-Realty 2.x.x (2.0.0 Beta 1) or newer)';

//Step 2: Setup Database Connection:
$lang['install_setup__database_settings'] = "Setup Database Connection:";
$lang['install_Database_Type'] = "Database Type:";
$lang['install_mySQL'] = "mySQL";
$lang['install_PostgreSQL'] = "PostgreSQL";
$lang['install_Database_Server'] = "Database Server:";
$lang['install_Database_Name'] = "Database Name:";
$lang['install_Database_User'] = "Database User:";
$lang['install_Database_Password'] = "Database Password:";
$lang['install_Table Prefix'] = "Table Prefix:";
$lang['install_Base_URL'] = "Base URL:";
$lang['install_Base_Path'] = "Base Path:";
$lang['install_Language'] = "Language:";
$lang['install_English'] = "English";
$lang['install_Spanish'] = "Spanish";
$lang['install_Italian'] = "Italian";
$lang['install_French'] = "French";
$lang['install_Portuguese'] = "Portuguese";
$lang['install_Russian'] = "Russian";
$lang['install_Turkish'] = "Turkish";
$lang['install_German'] = "German";
$lang['install_Dutch'] = "Dutch";
$lang['install_Lithuanian'] = "Lithuanian";
$lang['install_Arabic'] = "Arabic";
$lang['install_Polish'] = "Polish";
$lang['install_Czech'] = "Czech";
$lang['install_Indonesian'] = "Indonesian";
$lang['install_Bulgarian'] = "Bulgarian";
$lang['install_connection_fail'] = "We are unable to connect to your database. Please Check your settings and try again.";

//Step Three
$lang['install_get_old_version'] = 'Determining old Open-Realty version';
$lang['install_get_old_version_error'] = 'Error determining old Open-Realty version. Upgrade can not continue.';
$lang['install_cleared_cache'] = "Cleared Cache";
$lang['install_connection_ok'] = "We are able to connect to the database.";
$lang['install_save_settings'] = "We are now going to save your settings to your common.php file";
$lang['install_settings_saved'] = "Database Settings Saved.";
$lang['install_continue_db_setup'] = "Continue to setup the database.";
$lang['install_populate_db'] = "We are now going to populate the database.";

//finalize installation
$lang['install_installation_complete'] = "Installation is complete.";
$lang['install_configure_installation'] = "Click here to configure your installation";

//2.2.0 additions.
$lang['install_devel_mode'] = "Developer Mode Install - This will allow the install to continue even with errors. THIS IS NOT RECOMMENDED.";
$lang['yes'] = "Yes";
$lang['no'] = "No";

//3.0.4 additions
$lang['curl_not_enabled'] = 'PHP Curl Extenstion is not installed';
$lang['warnings_php_zip'] = 'Your PHP Install does not have the PHP ZIP Functions Installed';
$lang['warnings_nothing'] = 'Nothing wrong was detected for the settings tested.';
$lang['warnings_magic_quotes_gpc'] = '- You have "magic_quotes_gpc" actually set to "ON" at your server while you should have it set to "OFF". Contact your host support and ask to turn it off.';
$lang['warnings_mb_convert_encoding'] = '- MBString is not enabled at your server and you have it set to "Yes". Modify this setting to "No" (at "Site Config", "Editor/HTML" tab) or contact your host support and ask to enable it.';
$lang['warnings_mod_rewrite'] = '- Your server have "mod_rewrite" DISABLED and you have enabled URL TYPE as "Search Engine Friendly" (at "Site Config", "SEO" tab). Contact your host support and ask to turn it on.';
$lang['warnings_htaccess'] = '- You don\'t have a ".htaccess" file but you have enabled URL TYPE as "Search Engine Friendly" (at "Site Config", "SEO" tab). Revert back to "Standard URL" and read Open-Realty® Documentation (<a href="http://docs.google.com/View?id=dhk2ckgx_4dw62x5fh#_SEO_Search_Engine_Optimizatio" title="Search engine optimization settings">here</a>).';
$lang['warnings_admin_password'] = '- For security reasons you should modify the password for the "admin" user name account. Actually it is still set as the default one set during installation: "password".';
$lang['warnings_openssl'] = '- Open-Realty® v.3.0.0 and later, requires "openssl" to be loaded by PHP at your server. Actually you have it DISABLED - contact your Host Support and ask to enable it.';
$lang['warnings_safe_mode'] = 'PHP Safe Mode is enabled, this must be turned off.';
$lang['warnings_php_gd'] = 'Your PHP Install does not have the GD Libraries Installed';
$lang['warnings_php_exif'] = 'Your PHP Install does not have the Exif Extension Installed';
$lang['file_path_contains_a_space'] = 'Your File System Path contains a space, this is not allowed.';
$lang['warnings_php_freetype'] = 'Your PHP Install does not have the TTF FreeType Libraries Installed';
