<?php

/****
 * general_admin
 * This class contains the functions related to the administrative index page section.
 *
 * @author Ryan Bonham
 *
 */

use Composer\Semver\VersionParser;

class general_admin
{
    public function index_page()
    {
        global $config, $conn;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $page->load_page($config['admin_template_path'] . '/or_index.html');
        $page->replace_tags(['general_info', 'openrealty_links', 'baseurl', 'lang', 'user_id', 'addon_links']);
        $page->replace_foreach_pclass_block();
        $page->replace_if_addon_block();
        //Handle Blog Counts
        //Replace Status Counts
        //{blog_edit_status_all_count}
        $blog_user_type = intval($_SESSION['blog_user_type']);
        $blog_user_id = intval($_SESSION['userID']);
        if ($blog_user_type == 4 || $_SESSION['admin_privs'] == 'yes') {
            $sql = 'SELECT count(blogmain_id) as blogcount  FROM ' . $config['table_prefix'] . 'blogmain';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $count_all = $recordSet->fields('blogcount');

            $sql = 'SELECT count(blogmain_id) as blogcount  FROM ' . $config['table_prefix'] . 'blogmain WHERE blogmain_published = 1';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $count_published = $recordSet->fields('blogcount');

            $sql = 'SELECT count(blogmain_id) as blogcount  FROM ' . $config['table_prefix'] . 'blogmain WHERE blogmain_published = 0';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $count_draft = $recordSet->fields('blogcount');

            $sql = 'SELECT count(blogmain_id) as blogcount  FROM ' . $config['table_prefix'] . 'blogmain WHERE blogmain_published = 2';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $count_review = $recordSet->fields('blogcount');
        } else {
            $sql = 'SELECT count(blogmain_id) as blogcount  FROM ' . $config['table_prefix'] . 'blogmain WHERE userdb_id = ' . $blog_user_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $count_all = $recordSet->fields('blogcount');

            $sql = 'SELECT count(blogmain_id) as blogcount  FROM ' . $config['table_prefix'] . 'blogmain WHERE blogmain_published = 1 AND userdb_id = ' . $blog_user_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $count_published = $recordSet->fields('blogcount');

            $sql = 'SELECT count(blogmain_id) as blogcount  FROM ' . $config['table_prefix'] . 'blogmain WHERE blogmain_published = 0 AND userdb_id = ' . $blog_user_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $count_draft = $recordSet->fields('blogcount');
            $sql = 'SELECT count(blogmain_id) as blogcount  FROM ' . $config['table_prefix'] . 'blogmain WHERE blogmain_published = 2 AND userdb_id = ' . $blog_user_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $count_review = $recordSet->fields('blogcount');
        }

        $page->replace_tag('blog_edit_status_all_count', $count_all);
        $page->replace_tag('blog_edit_status_published_count', $count_published);
        $page->replace_tag('blog_edit_status_draft_count', $count_draft);
        $page->replace_tag('blog_edit_status_review_count', $count_review);
        //Get Status
        /* Handle Page Counts */
        //Replace Status Counts
        //{page_edit_status_all_count}

        $sql = 'SELECT count(pagesmain_id) as pagecount  
				FROM ' . $config['table_prefix'] . 'pagesmain';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $count_all = $recordSet->fields('pagecount');

        $sql = 'SELECT count(pagesmain_id) as pagecount  
				FROM ' . $config['table_prefix'] . 'pagesmain 
				WHERE pagesmain_published = 1';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $count_published = $recordSet->fields('pagecount');

        $sql = 'SELECT count(pagesmain_id) as pagecount  
				FROM ' . $config['table_prefix'] . 'pagesmain 
				WHERE pagesmain_published = 0';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $count_draft = $recordSet->fields('pagecount');

        $sql = 'SELECT count(pagesmain_id) as pagecount  
				FROM ' . $config['table_prefix'] . 'pagesmain 
				WHERE pagesmain_published = 2';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $count_review = $recordSet->fields('pagecount');

        $page->replace_tag('page_edit_status_all_count', $count_all);
        $page->replace_tag('page_edit_status_published_count', $count_published);
        $page->replace_tag('page_edit_status_draft_count', $count_draft);
        $page->replace_tag('page_edit_status_review_count', $count_review);
        /* Handle Lead Counts */

        $login_status = $login->verify_priv('edit_all_leads');
        if ($login_status === true) {
            $perm_sql = '';
        } else {
            $perm_sql = ' WHERE userdb_id = ' . intval($_SESSION['userID']) . ' ';
        }

        //Get Counts

        if ($perm_sql == '') {
            $perm_sql = ' WHERE ';
        } else {
            $perm_sql .= ' AND ';
        }
        $sql2 = 'SELECT count(feedbackdb_id) as count
		FROM ' . $config['table_prefix'] . 'feedbackdb ' . $perm_sql . ' feedbackdb_status = \'1\'';
        $recordSet2 = $conn->Execute($sql2);
        if (!$recordSet2) {
            $misc->log_error($sql2);
        }
        $active_lead_count = $recordSet2->fields('count');
        $sql2 = 'SELECT count(feedbackdb_id) as count FROM ' . $config['table_prefix'] . 'feedbackdb ' . $perm_sql . ' feedbackdb_status = \'0\'';
        $recordSet2 = $conn->Execute($sql2);
        if (!$recordSet2) {
            $misc->log_error($sql2);
        }
        $inactive_lead_count = $recordSet2->fields('count');

        //lead_inactive_count
        //lead_active_count
        $page->replace_tag('lead_inactive_count', $inactive_lead_count);
        $page->replace_tag('lead_active_count', $active_lead_count);
        return $page->return_page();
    }

    /**
     * display_addons()
     * This functions first calls the add-on install function to make sure it is intalled/updated. Then calls the addons show_admin_icons function.
     *
     * @param string $addon Should be the name of the addon to install and then load.
     * @return
     */
    public function display_addons($addon = '')
    {
        global $config;
        $addon_file = $config['basepath'] . '/addons/' . $addon . '/addon.inc.php';
        $links = '';
        if (file_exists($addon_file)) {
            include_once $addon_file;
            $function_name = $addon . '_install_addon';
            $function_name();
            $function_name = $addon . '_show_admin_icons';
            $links = $function_name();
        }
        return $links;
    }

    /**
     * openrealty_links()
     * This function displays the Open-Realty Support, Wiki, Defects, and Upgrade Links.
     *
     * @return
     */
    public function openrealty_links()
    {
        global $lang, $jscript, $config;
        $display = '';

        if ($config['automatic_update_check'] == 1) {
            $check_for_updates = $this->update_check();
            if ($check_for_updates === true) {
                $display .= '<a id="updatemelink" class="upgrade_true" rel="#upgradeyesno" href="#"> ' . $lang['link_upgrade_available'] . '(' . $_SESSION['updateversion'] . ')</a>';
            }
        } else {
            $display .= '<a id="updatemelink" class="upgrade_false" rel="#upgradeyesno" href="#">' . $lang['link_upgrade_manual'] . '</a>';
        }

        return $display;
    }
    /**
     * get_latest_release($prerelease=false)
     * This function contacts gitlab to get the latest release
     */
    public function get_latest_release()
    {
        global $config, $misc;
        $result = false;
        // Determine Current Stability
        $prerelease = false;
        $current_stability = VersionParser::parseStability($config['version']);
        if ($current_stability != 'stable') {
            $prerelease = true;
        }

        $releases = $misc->get_url('https://gitlab.com/api/v4/projects/appsbytherealryanbonham%2Fopen-realty/releases', 1800);
        if ($releases == false) {
            return $result;
        } else {
            $releases = json_decode($releases, true);
            foreach ($releases as &$release) {
                $version = $release['tag_name'];
                $stability = VersionParser::parseStability($version);
                if ($prerelease == false && $stability != 'stable') {
                    continue;
                }
                //Check if this version has a download link
                $download_url = '';
                if (array_key_exists('links', $release['assets'])) {
                    foreach ($release['assets']['links'] as $link) {
                        if ($link['name'] == 'Open-Realty-' . $version . '.zip') {
                            $download_url = $link['direct_asset_url'];
                            break;
                        }
                    }
                }
                if ($download_url !== '') {
                    $result =  (object) [
                        'version' => $version,
                        'download_url' => $download_url,
                    ];
                    break;
                }
            }
        }
        return $result;
    }
    /**
     * update_check()
     * This function Check to see if it is necessary to update Open-Realty. It looks at the Version number in the config table and compares it to the number located in http://www.open-realty.org/release/version.txt. If allow_url_fopen is off, it show a manual update link, which the user cna click on to open the open-realty site.
     *
     * @return
     */
    public function update_check($force = false)
    {
        global $config, $misc;

        if ($force == true || (!isset($_SESSION['updatechecked']) || (isset($_SESSION['updatechecked']) && $_SESSION['updatechecked'] < time()))) {
            $lastest_version = $this->get_latest_release();
        } else {
            $lastest_version =  (object) [
                'version' => $_SESSION['updateversion'],
                'download_url' => '',
            ];
        }
        if ($lastest_version != false) {
            $new_version = $lastest_version->version;
            $check = version_compare($config['version'], $new_version, '>=');
            //Dont Check for Updates again for 24 Hours durring this session.
            $_SESSION['updatechecked'] = time() + (24 * 60 * 60);
            $_SESSION['updateversion'] = $new_version;
            if ($check == 1) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    private function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir . '/' . $object) == 'dir') {
                        $this->rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public function do_upgrade()
    {
        global $config, $conn, $misc;
        error_reporting(E_ALL ^ E_DEPRECATED);
        $lastest_version = $this->get_latest_release();
        if ($lastest_version !== false) {
            $download_url = $lastest_version->download_url;
            $misc->log_action('Attempting Upgrading From Open-Realty ' . $config['version']);
            $data = $misc->get_url($download_url);
            echo 'Upgrade Downloaded.<br />';
            //Write Upgrade File
            $temp_file = tempnam(sys_get_temp_dir(), 'upgrade' . $config['version']);
            $fp = fopen($temp_file, 'wb');
            fwrite($fp, $data);
            fclose($fp);

            echo 'Upgrade Extracting...<br/>';
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive;
                $res = $zip->open($temp_file);
                if ($res === true) {
                    echo 'Removing Old Templates.<br />';
                    //Remove Old Template Files.
                    if (is_dir($config['basepath'] . '/template/lazuli')) {
                        $this->rrmdir($config['basepath'] . '/template/lazuli');
                    }
                    if (is_dir($config['basepath'] . '/template/defualt')) {
                        $this->rrmdir($config['basepath'] . '/template/defualt');
                    }
                    if (is_dir($config['basepath'] . '/template/cms_integration')) {
                        $this->rrmdir($config['basepath'] . '/template/cms_integration');
                    }
                    if (is_dir($config['basepath'] . '/template/html5')) {
                        $this->rrmdir($config['basepath'] . '/template/html5');
                    }
                    if (is_dir($config['basepath'] . '/template/mobile')) {
                        $this->rrmdir($config['basepath'] . '/template/mobile');
                    }
                    //Remove Old Admin Template Files.
                    if (is_dir($config['basepath'] . '/admin/template/defualt')) {
                        $this->rrmdir($config['basepath'] . '/admin/template/defualt');
                    }
                    if (is_dir($config['basepath'] . '/admin/template/cms_integration')) {
                        $this->rrmdir($config['basepath'] . '/admin/template/cms_integration');
                    }
                    if (is_dir($config['basepath'] . '/admin/template/OR_small')) {
                        $this->rrmdir($config['basepath'] . '/admin/template/OR_small');
                    }
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $zip_name = $zip->getNameIndex($i);
                        $path_parts = pathinfo($config['basepath'] . '/' . $zip_name);
                        if (!file_exists($config['basepath'] . '/' . $zip_name) && (!isset($path_parts['extension']) || (isset($path_parts['extension']) && isset($path_parts['filename']) && $path_parts['filename'] == ""))) {
                            $success = mkdir($config['basepath'] . '/' . $zip_name);
                            if (!$success) {
                                echo '<div class="alert alert-danger">Failed to create directory: ' . $config['basepath'] . '/' . $zip_name . '</div>';
                                die;
                            }
                        }
                        if (!is_dir($config['basepath'] . '/' . $zip_name)) {
                            if ($zip_name == ".htaccess") {
                                $fileToWrite = $config['basepath'] . '/.htaccess.upgrade';
                            } else {
                                $fileToWrite = $config['basepath'] . '/' . $zip_name;
                            }
                            if (file_exists($fileToWrite)) {
                                if (!is_writable($fileToWrite)) { // Test if the file is writable
                                    echo "Permissions are not writeable {$fileToWrite}";
                                    die;
                                }
                            } else {
                                $fileToWriteDir = dirname($fileToWrite);
                                if (!file_exists($fileToWriteDir)) {
                                    $success = mkdir($fileToWriteDir);
                                    if (!$success) {
                                        echo '<div class="alert alert-danger">Failed to create directory: ' . $fileToWriteDir . '</div>';
                                        die;
                                    }
                                }
                                if (!is_writable($fileToWriteDir)) { // Test if the file is writable
                                    echo "Permissions are not writeable {$fileToWriteDir}";
                                    die;
                                }
                            }

                            $fp = fopen($fileToWrite, 'wb');
                            if (!is_resource($fp)) { // Test if PHP could open the file
                                echo "Could not open {$fileToWrite} for writting.";
                                die;
                            }
                            $buf = $zip->getStream($zip->getNameIndex($i));
                            stream_copy_to_stream($buf, $fp);
                            fclose($buf);
                            fclose($fp);
                        }
                    }
                    echo "Extraction Complete.<br />";
                    $zip->close();
                    unlink($temp_file);

                    //Run installer

                    echo 'Upgrading Database...<br/>';
                    $display = $misc->get_url($config['baseurl'] . '/install/index.php?step=autoupdate&or_install_lang=en&or_install_type=upgrade_200');
                    if ($display === false) {
                        echo 'Calling Installer Failed, check site logs in Open-Realty admin. <br />';
                        die;
                    }
                    echo $display;
                    //Remove Install
                    if (is_dir($config['basepath'] . '/install')) {
                        $this->rrmdir($config['basepath'] . '/install');
                    }
                    $old_version = $config['version'];
                    $sql = 'SELECT controlpanel_version 
							FROM ' . $config['table_prefix_no_lang'] . 'controlpanel';
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error();
                    }
                    // Loop throught Control Panel and save to Array
                    $config['version'] = $recordSet->fields('controlpanel_version');
                    if ($old_version == $config['version']) {
                        echo 'Post Version Sanity Check Failed. Stopping Update Process Contact Support.<br />';
                        die;
                    } else {
                        echo 'Upgrade Complete From Version ' . $old_version . ' to ' . $config['version'] . '<br />';
                        $misc->log_action('Upgrade Complete From Version ' . $old_version . ' to ' . $config['version']);
                    }
                    //Refresh the update check
                    $this->update_check(true);
                }
            }
        } else {
            echo 'Failed Getting Upgrade Data';
        }
    }

    /**
     * general_info()
     * This displays the general information section on the index page. It is showing the following information.
     *
     * @see listing_count()
     * @see listing_count()
     * @see agent_count()
     * @return
     */
    public function general_info()
    {
        global $lang, $config;
        $display = '<div >
						<a href="' . $config['baseurl'] . '/admin/index.php?action=edit_listings">
							<span class="general_info_cap">' . $lang['total_listings'] . '</span> <span class="general_info_data">' . $this->listing_count() . '</span>
						</a>
					</div>
					<div>
						<a href="javascript:document.getElementById(\'edit_active\').submit()">
							<span class="general_info_cap">' . $lang['active_listings'] . '</span> <span class="general_info_data">' . $this->listing_count('yes') . '</span>
						</a>
					</div>
					<div>
						<a href="javascript:document.getElementById(\'edit_inactive\').submit()">
						<span class="general_info_cap">' . $lang['inactive_listings'] . '</span> <span class="general_info_data">' . $this->listing_count('no') . '</span>
						</a>
					</div>
					<div>
						<a href="javascript:document.getElementById(\'edit_featured\').submit()">
							<span class="general_info_cap">' . $lang['featured_listings'] . '</span> <span class="general_info_data">' . $this->listing_count('featured') . '</span>
						</a>
					</div>';
        if ($config['use_expiration'] == 1) {
            $display .= '<div>
							<a href="javascript:document.getElementById(\'edit_expired\').submit()">
								<span class="general_info_cap">' . $lang['expired_listings'] . '</span> <span class="general_info_data">' . $this->listing_count('expired') . '</span>
							</a>
						</div>';
        }
        $display .= '<div>
						<a href="javascript:document.getElementById(\'edit_agents\').submit()">
							<span class="general_info_cap">' . $lang['number_of_agents'] . '</span> <span class="general_info_data">' . $this->agent_count() . '</span>
						</a>
					</div>
					<div>
						<a href="javascript:document.getElementById(\'edit_members\').submit()">
							<span class="general_info_cap">' . $lang['number_of_members'] . '</span> <span class="general_info_data">' . $this->member_count() . '</span>
						</a>
					</div>';

        $display .= '<div id="HiddenFilterForm" style="display:none">';
        $display .= '<form id="edit_active" action="' . $config['baseurl'] . '/admin/index.php?action=edit_listings" method="post"><input type="hidden" name="token" value="{csrf_token}" /><fieldset><input type="hidden" name="filter" value="active" /></fieldset></form>';
        $display .= '<form id="edit_inactive" action="' . $config['baseurl'] . '/admin/index.php?action=edit_listings" method="post"><input type="hidden" name="token" value="{csrf_token}" /><fieldset><input type="hidden" name="filter" value="inactive" /></fieldset></form>';
        $display .= '<form id="edit_featured" action="' . $config['baseurl'] . '/admin/index.php?action=edit_listings" method="post"><input type="hidden" name="token" value="{csrf_token}" /><fieldset><input type="hidden" name="filter" value="featured" /></fieldset></form>';
        if ($config['use_expiration'] == 1) {
            $display .= '<form id="edit_expired" action="' . $config['baseurl'] . '/admin/index.php?action=edit_listings" method="post"><input type="hidden" name="token" value="{csrf_token}" /><fieldset><input type="hidden" name="filter" value="expired" /></fieldset></form>';
        }
        $display .= '<form id="edit_agents" action="' . $config['baseurl'] . '/admin/index.php?action=user_manager" method="post"><input type="hidden" name="token" value="{csrf_token}" /><fieldset><input type="hidden" name="filter" value="agents" /></fieldset></form>';
        $display .= '<form id="edit_members" action="' . $config['baseurl'] . '/admin/index.php?action=user_manager" method="post"><input type="hidden" name="token" value="{csrf_token}" /><fieldset><input type="hidden" name="filter" value="members" /></fieldset></form>';
        $display .= '</div>';
        return $display;
    }

    /**
     * agent_count()
     * Returns the number of agents currently in the database.
     *
     * @return string Number of agents
     */
    public function agent_count()
    {
        global $conn, $config;
        $agent_count_sql = 'SELECT count(userdb_id)
							AS agent_count
							FROM ' . $config['table_prefix'] . 'userdb
							WHERE userdb_is_agent = \'yes\'';
        $agent_count = $conn->Execute($agent_count_sql);
        $agent_count = $agent_count->fields('agent_count');
        return $agent_count;
    }

    /**
     * listing_count()
     * Returns the number of listings currently in the database.
     *
     * @param string $active if set to yes it returns only active listings.
     * @return Number of listings found
     */
    public function listing_count($view = 'all')
    {
        global $conn, $config;
        if ($view == 'all') {
            $listing_count_sql = 'SELECT count(listingsdb_id) as listing_count FROM ' . $config['table_prefix'] . 'listingsdb';
        } elseif ($view == 'yes') {
            $listing_count_sql = 'SELECT count(listingsdb_id) as listing_count FROM ' . $config['table_prefix'] . 'listingsdb WHERE listingsdb_active = \'yes\'';
        } elseif ($view == 'no') {
            $listing_count_sql = 'SELECT count(listingsdb_id) as listing_count FROM ' . $config['table_prefix'] . 'listingsdb WHERE listingsdb_active = \'no\'';
        } elseif ($view == 'featured') {
            $listing_count_sql = 'SELECT count(listingsdb_id) as listing_count FROM ' . $config['table_prefix'] . 'listingsdb WHERE listingsdb_featured = \'yes\'';
        } elseif ($view == 'expired') {
            $listing_count_sql = 'SELECT count(listingsdb_id) as listing_count FROM ' . $config['table_prefix'] . 'listingsdb WHERE listingsdb_expiration < ' . $conn->DBDate(time());
        }

        $listing_count = $conn->Execute($listing_count_sql);
        $listing_count = $listing_count->fields('listing_count');
        return $listing_count;
    }

    /**
     * member_count()
     * Returns the number of members currently in the database.
     *
     * @return string Number of members
     */
    public function member_count()
    {
        global $conn, $config;
        $member_count_sql = 'SELECT count(userdb_id) as member_count
							FROM ' . $config['table_prefix'] . 'userdb
							WHERE userdb_is_agent = \'no\'
							AND userdb_is_admin = \'no\'';
        $member_count = $conn->Execute($member_count_sql);
        $member_count = $member_count->fields('member_count');
        return $member_count;
    }
}
