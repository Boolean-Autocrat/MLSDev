<?php

class addon_manager
{
    public function check_addon_name($addon_name)
    {
        global $lang;
        $bad_char = preg_match('/[^A-Za-z0-9_-]/', $addon_name);
        if ($bad_char == 1) {
            die($lang['addon_name_invalid']);
        }
    }
    public function display_addon_help($addon_name)
    {
        global $config, $lang;
        $display = '';
        $this->check_addon_name($addon_name);
        $help_array = [];
        if (file_exists($config['basepath'] . '/addons/' . $addon_name . '/addon.inc.php')) {
            include_once $config['basepath'] . '/addons/' . $addon_name . '/addon.inc.php';
            if (function_exists($addon_name . '_addonmanager_help')) {
                $help_function = $addon_name . '_addonmanager_help';
                $help_array = $help_function();
                //return array($template_tags,$action_urls,$doc_url);
                $help_template_tags = $help_array[0];
                $help_action_urls = $help_array[1];
                $help_doc_url = $help_array[2];
                if ($help_doc_url != '') {
                    $display .= '<div class="addon_manager_ext_help_link"><a href="' . $help_doc_url . '" title="' . $lang['addon_manager_ext_help_link'] . '" onclick="window.open(\'' . $help_doc_url . '\', \'newwindow\', \'width=800, height=700\'); return false;" >' . $lang['addon_manager_ext_help_link'] . '</a></div>';
                }
                if (!empty($help_template_tags)) {
                    $display .= '<div class="addon_manager_template_tag_header">' . $lang['addon_manager_template_tags'] . '</div>';
                    foreach ($help_template_tags as $tagname => $tagdesc) {
                        $display .= '<div class="addon_manager_template_tag_data">
                                <span class="addon_manager_template_tag_name">' . $tagname . '</span>
                                <span class="addon_manager_template_tag_desc">' . $tagdesc . '</span>
                                </div>';
                    }
                }
                if (!empty($help_action_urls)) {
                    $display .= '<div class="addon_manager_action_url_header">' . $lang['addon_manager_action_urls'] . '</div>';
                    foreach ($help_action_urls as $tagname => $tagdesc) {
                        $display .= '<div class="addon_manager_action_url_data">
                                <span class="addon_manager_action_url_name">' . $tagname . '</span>
                                <span class="addon_manager_action_url_desc">' . $tagdesc . '</span>
                                </div>';
                    }
                }
            }
        }
        return $display;
    }

    public function install_local_addon()
    {
        global $config, $lang, $misc;

        $display = '';
        $realname = $misc->clean_filename($_FILES['userfile']['name']);
        $install_file = $_FILES['userfile']['tmp_name'];
        $filetype = $_FILES['userfile']['type'];
        $addon_name = substr($realname, 0, strpos($realname, '.'));
        $this->check_addon_name($addon_name);
        //check to make sure it is a zip file
        //$zip_mimetypes = array('application/zip','application/x-zip','application/x-zip-compressed','application/octet-stream','application/x-compress','application/x-compressed','multipart/x-zip');
        //if (in_array($filetype,$zip_mimetypes)){
        $installed_addons = $this->get_installed_addons();
        if (in_array($addon_name, $installed_addons) && (!isset($_POST['upgrade']) || $_POST['upgrade'] != 'yes')) {
            $display .= '<div class="addon_manager_bad_info">' . htmlentities($addon_name) . ' - ' . $lang['addon_already installed'] . '</div>';
        } else {
            $install_status = $this->extract($install_file, $addon_name);
            if ($install_status === false) {
                $display .= '<div class="addon_manager_bad_info">' . htmlentities($addon_name) . ' - ' . $lang['addon_install_failed'] . '</div>';
            } else {
                //install successful, run the addon install function
                include_once $config['basepath'] . '/addons/' . $addon_name . '/addon.inc.php';
                if (function_exists($addon_name . '_install_addon')) {
                    $install_function = $addon_name . '_install_addon';
                    $install_function();
                }
                if (isset($_POST['upgrade']) && $_POST['upgrade'] == 'yes') {
                    $display .= '<div class="addon_manager_good_info">' . htmlentities($addon_name) . ' - ' . $lang['addon_upgade_successful'] . '</div>';
                } else {
                    $display .= '<div class="addon_manager_good_info">' . htmlentities($addon_name) . ' - ' . $lang['addon_install_successful'] . '</div>';
                }
            }
            unlink($install_file);
        }

        //  } else {
        //      $display.='<div class="addon_manager_bad_info">'.htmlentities($addon_name).' - '.$lang['addon_upload_file_not_zip'].'</div>';
        //  }
        return $display;
    }
    public function uninstall_addon($addon_name)
    {
        global $config, $conn, $lang, $misc;

        $display = '';
        $this->check_addon_name($addon_name);
        $has_uninstall = false;
        if (file_exists($config['basepath'] . '/addons/' . $addon_name . '/addon.inc.php')) {
            include_once $config['basepath'] . '/addons/' . $addon_name . '/addon.inc.php';
            if (function_exists($addon_name . '_uninstall_tables')) {
                $has_uninstall = true;
            }
        }
        $folder_removed = false;
        $db_uninstalled = false;
        if ($has_uninstall == true) {
            $uninstall_function = $addon_name . '_uninstall_tables';
            $db_uninstalled = $uninstall_function();
        }
        if ($db_uninstalled) {
            $folder_removed = $this->rmdir_recurse($config['basepath'] . '/addons/' . $addon_name);
        }
        if ($folder_removed) {
            //Ok Addon is now removed, lets remove it from the addon table.
            $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . 'addons WHERE addons_name = ' . $misc->make_db_safe($addon_name);
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $display .= '<div class="addon_manager_good_info">' . $lang['removed_addon'] . ' ' . htmlentities($addon_name) . '</div>';
        }
        return $display;
    }

    public function check_store_addon_update($addon_name)
    {
        global $config, $conn, $lang, $misc;

        $display = '';
        $this->check_addon_name($addon_name);
        //New Update Method
        $details = $this->get_addon_details($addon_name);
        $latest_version = $details['version'];
        if ($latest_version != false) {
            $sql = 'SELECT addons_version 
                    FROM ' . $config['table_prefix_no_lang'] . 'addons 
                    WHERE addons_name =' . $misc->make_db_safe($addon_name);
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $current_version = trim($recordSet->fields('addons_version'));
            if ($current_version == $latest_version) {
                $display .= '<div class="addon_manager_good_info">' . $lang['addon_already_latest_version'] . ' ' . htmlentities($addon_name) . '</div>';
            } else {
                $display .= '<div class="addon_manager_bad_info">' . $lang['addon_update_avaliable'] . ' <a href="' . $config['baseurl'] . '/admin/index.php?action=addon_manager&amp;install_update=' . $addon_name . '" title="' . $lang['addon_check_for_updates'] . '">' . $lang['addon_download_update'] . ' ' . htmlentities($addon_name) . '</a></div>';
            }
        }
        return $display;
    }

    public function install_store_addon($addon_name)
    {
        global $config, $lang, $misc;

        $display = '';
        [$file_download_url, $folder]  = $this->get_download_url($addon_name);
        $file = $misc->get_url($file_download_url);
        if ($file === false) {
            $display .= '<div class="addon_manager_bad_info">' . htmlentities($addon_name) . ' - ' . $lang['addon_install_file_not_avaliable'] . '</div>';
        } else {
            //we have the file unzip it and then install it
            $install_file = $this->write_tmp_zip($file);
            $install_status = $this->extract($install_file, $folder);
            if ($install_status === false) {
                $display .= '<div class="addon_manager_bad_info">' . htmlentities($addon_name) . ' - ' . $lang['addon_install_failed'] . '</div>';
            } else {
                //install successful, run the addon install function
                include_once $config['basepath'] . '/addons/' . $folder . '/addon.inc.php';
                if (function_exists($folder . '_install_addon')) {
                    $install_function = $folder . '_install_addon';
                    $install_function();
                }
                $display .= '<div class="addon_manager_good_info">' . htmlentities($addon_name) . ' - ' . $lang['addon_install_successful'] . '</div>';
            }
            unlink($install_file);
        }
        return $display;
    }

    public function rmdir_recurse($path)
    {
        $path = rtrim($path, '/') . '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' and $file != '..') {
                $fullpath = $path . $file;
                if (is_dir($fullpath)) {
                    $this->rmdir_recurse($fullpath);
                } else {
                    unlink($fullpath);
                }
            }
        }
        closedir($handle);
        rmdir($path);
        return true;
    }

    public function get_tmp()
    {
        $tmpfile = @tempnam('dummy', '');
        $path = dirname($tmpfile);
        unlink($tmpfile);
        return $path;
    }

    public function extract($file, $addon_name)
    {
        global $config;
        $unzipped = false;
        $file_structure = true;
        $path_insert = '';
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            $res = $zip->open($file);
            if ($res === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $zip_name = $zip->getNameIndex($i);
                    if (substr($zip_name, 0, strlen($addon_name)) != $addon_name) {
                        $file_structure = false;
                    }
                }
                if ($file_structure === false) {
                    $path_insert = $addon_name . '/';
                }

                if (!is_dir($config['basepath'] . '/addons/' . $addon_name)) {
                    mkdir($config['basepath'] . '/addons/' . $addon_name);
                }

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $zip_name = $zip->getNameIndex($i);
                    $path_parts = pathinfo($config['basepath'] . '/addons/' . $path_insert . $zip_name);
                    //echo'<pre>'.print_r($path_parts,true).'</pre>';
                    if (!isset($path_parts['extension']) && !file_exists($config['basepath'] . '/addons/' . $path_insert . $zip_name)) {
                        mkdir($config['basepath'] . '/addons/' . $path_insert . $zip_name);
                    }
                    if (!is_dir($config['basepath'] . '/addons/' . $path_insert . $zip_name)) {
                        $fp = fopen($config['basepath'] . '/addons/' . $path_insert . $zip_name, 'wb');
                        if ($fp) {
                            $unzipped = true;

                            $buf = $zip->getStream($zip->getNameIndex($i));
                            stream_copy_to_stream($buf, $fp);

                            fclose($buf);
                        }
                        fclose($fp);
                    }
                }
                $zip->close();
                return true;
            } else {
                echo '<pre>Failed to Open Zip</pre>';
            }
        }
        return false;
    }

    public function write_tmp_zip($data)
    {
        $tmp_path = $this->get_tmp();
        $file_name = time() . '.zip';
        $fp = fopen($tmp_path . '/' . $file_name, 'wb');
        fwrite($fp, $data);
        fclose($fp);
        return $tmp_path . '/' . $file_name;
    }

    public function display_addon_manager()
    {
        global $config, $conn, $lang, $jscript, $misc;
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();

        $display = '';
        //Load TEmplate File
        $page->load_page($config['admin_template_path'] . '/addon_manager.html');


        //Check addon folder is writeable
        $addon_permission = is_writeable($config['basepath'] . '/addons');
        $status_message = '';
        if ($addon_permission == false) {
            $display .= '<div class="redtext">' . $lang['warning_addon_folder_not_writeable'] . '</div>';
            return $display;
        }
        //Are we deleting?
        if (isset($_GET['uninstall'])) {
            $uninstall_name = $_GET['uninstall'];
            $status_message .= $this->uninstall_addon($uninstall_name);
        }
        //Are we installing?
        if (isset($_GET['install'])) {
            $status_message .= $this->install_store_addon($_GET['install']);
        }
        if (isset($_GET['install_update'])) {
            $status_message .= $this->install_store_addon($_GET['install_update']);
        }
        //Are we Updating an addon?
        if (isset($_GET['check_update'])) {
            $update_name = $_GET['check_update'];
            $status_message .=  $this->check_store_addon_update($update_name);
        }

        //Are we manually installing?
        if (isset($_POST['action']) && $_POST['action'] == 'man_install' && $_FILES['userfile']['error'] == 0) {
            if (!isset($_POST['token']) || !$misc->validate_csrf_token($_POST['token'])) {
                $status_message .= $lang['invalid_csrf_token'];
            } else {
                $status_message .= $this->install_local_addon();
            }
        }

        if (isset($_GET['view_help'])) {
            $help_name = $_GET['view_help'];
            $display .= $this->display_addon_help($help_name);
            return $display;
        }

        //Load Intalled Addons into Template.
        $sql = 'SELECT * FROM ' . $config['table_prefix_no_lang'] . 'addons ORDER BY addons_name;';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }

        if ($recordSet->RecordCount() == 0) {
            $display .= '<tr><td colspan="4" style="text-align:center;">' . $lang['addon_manager_none_installed'] . '</td></tr>';
        }

        $install_addon_template = '';
        while (!$recordSet->EOF) {
            $install_addon_template .= $page->get_template_section('addon_installed_block');
            $name = $recordSet->fields('addons_name');
            $version = $recordSet->fields('addons_version');
            $install_addon_template = $page->parse_template_section($install_addon_template, 'addon_name', $name);
            $install_addon_template = $page->parse_template_section($install_addon_template, 'addon_version', $version);
            //Check Addon Status
            $status_msg = $lang['addon_ok'];
            //Status Code 0=ok 1=FatalError 2=Warngin
            $status_code = 0;
            //Define action variables
            $template_tags = [];
            $action_urls = [];
            $doc_url = '';
            $has_help = false;
            $in_store = false;
            $has_uninstall = false;
            //See if addon was removed.
            $still_here = file_exists($config['basepath'] . '/addons/' . $name);
            if ($still_here) {
                $still_here = file_exists($config['basepath'] . '/addons/' . $name . '/addon.inc.php');
                if (!$still_here) {
                    $status_msg = $lang['addon_files_removed'];
                    $status_code = 1;
                } else {
                    //Ok Adon is here lets get a list of actions.
                    include_once $config['basepath'] . '/addons/' . $name . '/addon.inc.php';
                    if (function_exists($name . '_addonmanager_help')) {
                        $help_funtion = $name . '_addonmanager_help';
                        $help_array = $help_funtion();
                        //return array($template_tags,$action_urls,$doc_url);
                        $template_tags = $help_array[0];
                        $action_urls = $help_array[1];
                        $doc_url = $help_array[2];
                        if (!empty($template_tags)) {
                            $has_help = true;
                        }
                        if (!empty($action_urls)) {
                            $has_help = true;
                        }
                        if (!empty($doc_url)) {
                            $has_help = true;
                        }
                    }
                    if (function_exists($name . '_uninstall_tables')) {
                        $has_uninstall = true;
                    }
                    //Check if addon is the store
                    $in_store = $this->get_addon_details($name);
                }
            } else {
                $status_msg = $lang['addon_dir_removed'];
                $status_code = 1;
            }

            if ($in_store == false) {
                $install_addon_template = $page->remove_template_block('action_update', $install_addon_template);
            } else {
                $install_addon_template = $page->cleanup_template_block('action_update', $install_addon_template);
            }


            if ($has_help != true) {
                $install_addon_template = $page->remove_template_block('action_help', $install_addon_template);
            } else {
                $install_addon_template = $page->cleanup_template_block('action_help', $install_addon_template);
            }
            if ($has_uninstall != true) {
                $install_addon_template = $page->remove_template_block('action_uninstall', $install_addon_template);
            } else {
                $install_addon_template = $page->cleanup_template_block('action_uninstall', $install_addon_template);
            }
            $install_addon_template = $page->parse_template_section($install_addon_template, 'status_code', $status_code);
            $install_addon_template = $page->parse_template_section($install_addon_template, 'status_msg', $status_msg);

            $recordSet->MoveNext();
        }

        $page->replace_template_section('addon_installed_block', $install_addon_template);
        $page->replace_tag('application_status_text', $status_message);
        $page->replace_permission_tags();
        $page->auto_replace_tags('', true);
        $display .= $page->return_page();
        return $display;
    }

    public function get_addon_details($addon_name)
    {
        global $config, $misc;

        $installed_addons = $this->get_installed_addons();
        $store_url = 'https://www.open-realty.org/addons.json';
        $data = $misc->get_url($store_url, 1800);
        $parsed_data = $this->parse_store_response($data);
        foreach ($parsed_data as $addon) {
            $installed = false;
            if (in_array($addon->title, $installed_addons)) {
                $installed = true;
            }
            if ($addon->folder == $addon_name) {
                $details = [];
                $details['name'] = (string)$addon->folder;
                $details['title'] = (string)$addon->title;
                $details['author'] = (string)$addon->author;
                $details['version'] = (string)$addon->version;
                $details['installed'] = (bool)$installed;
                return $details;
            }
        }
        return false;
    }

    private function get_download_url($addon_name)
    {
        global $misc;
        $url = false;
        $folder = false;
        $store_url = 'https://www.open-realty.org/addons.json';
        $data = $misc->get_url($store_url, 1800);
        $parsed_data = $this->parse_store_response($data);
        foreach ($parsed_data as $addon) {
            if ($addon->folder == $addon_name) {
                $url = $addon->download_url;
                $folder = $addon->folder;
                break;
            }
        }
        return [$url, $folder];
    }

    private function parse_store_response($data, $prerelease = true)
    {
        global $config;
        $addons = [];
        if ($data !== false) {
            $feed = json_decode($data, true);
            if ($feed) {
                foreach ($feed['items'] as $item) {
                    $addon = (object)[
                        'title' => $item['title'],
                        'author' => $item['author'],
                        'homepage' => $item['homepage'],
                        'docs' => $item['docs'],
                        'folder' => $item['folder'],
                        'version' => '',
                        'download_url' => '',
                        'stability' => '',
                        'min_compatibility' => '',
                        'max_compatibility' => '',

                    ];
                    foreach ($item['versions'] as $version) {
                        if (!array_key_exists('min_compatibility', $version)) {
                            //Skipping item as it does correctly define a min_compatibility
                            continue;
                        }
                        if (!version_compare($version['min_compatibility'], $config['version'], '<=')) {
                            //We do not meet minimum capabilities
                            continue;
                        }
                        if (array_key_exists('max_compatibility', $version) && !empty($version['max_compatibility'])) {
                            //This addon has a max_compatibility check it
                            if (!version_compare($version['max_compatibility'], $config['version'], '>=')) {
                                //We do not meet maximum capabilities
                                continue;
                            }
                        }
                        if (!$prerelease && $version['stability'] !== 'stable') {
                            continue;
                        }
                        $addon->version = $version['version'];
                        $addon->download_url = $version['download_url'];
                        $addon->stability = $version['stability'];
                        $addon->min_compatibility = $version['min_compatibility'];
                        $addon->max_compatibility = $version['max_compatibility'];
                        $addons[] = $addon;
                        break;
                    }
                }
            }
        }
        return $addons;
    }

    public function ajax_show_store_addons()
    {
        global $config, $misc;

        $display = '';
        $url = 'https://www.open-realty.org/addons.json';
        $data = $misc->get_url($url, 1800);
        $parsed_data = $this->parse_store_response($data);

        $x = new stdClass();
        $x->data = $parsed_data;
        header('Content-type: application/json');
        return json_encode($x);
    }

    public function get_installed_addons()
    {
        global $config, $conn, $misc;

        $sql = 'SELECT addons_name 
                FROM ' . $config['table_prefix_no_lang'] . 'addons;';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $installed_addons = [];
        while (!$recordSet->EOF) {
            $installed_addons[] = $recordSet->fields('addons_name');
            $recordSet->MoveNext();
        }
        return $installed_addons;
    }
}
