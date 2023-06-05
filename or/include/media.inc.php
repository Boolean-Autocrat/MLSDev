<?php


class media_handler
{
    protected $media_types = ['listingsimages', 'listingsfiles', 'listingsvtours', 'userimages', 'usersfiles'];

    public function handleExtLink($media_type, $edit, $owner)
    {
        // deals with incoming uploads
        global $config, $conn, $misc, $lang, $api;

        $display = '';
        $file_x = 0;
        $edit = intval($edit);
        $owner = intval($owner);
        $mediaobject = [];
        foreach ($_POST['extlink'] as $file_x => $ext_link) {
            if (strpos($ext_link, 'http://') !== 0 && strpos($ext_link, 'https://') !== 0 && strpos($ext_link, '//') !== 0) {
                //Invalid URL
                continue;
            }
            $realname = md5($ext_link) . '.jpg';
            $mediaobject[$realname]['description'] = '';
            $mediaobject[$realname]['data'] = $ext_link;
            $mediaobject[$realname]['remote'] = true;
        }
        $result = $api->load_local_api('media__create', ['media_parent_id' => $edit, 'media_type' => $media_type, 'media_data' => $mediaobject]);
        $status = '';
        //print_r($result);
        foreach ($result['media_error'] as $file => $error) {
            if ($error) {
                $status .= 'File ' . $file . ' - ' . $result['media_response'][$file] . "\r\n";
            }
        }
        if ($status == '') {
            $status .= 'File(s) Uploaded Successfully';
        }
        return $status;
    }

    public function handleUpload($media_type, $edit, $owner)
    {
        // deals with incoming uploads
        global $config, $api, $misc, $lang;

        $edit = intval($edit);
        $owner = intval($owner);
        //$media_type
        $mediaobject = [];
        //print_r($_FILES);

        if ($_FILES['userfile']['name'] == '') {
            return 'ERROR';
        }

        foreach ($_FILES['userfile']['name'] as $file_count => $realname) {
            if ($_FILES['userfile']['error'][$file_count] == UPLOAD_ERR_OK) {
                $imgDesc = '';
                $realname = $edit . '_' . $misc->clean_filename($realname);
                $mediaobject[$realname]['description'] = $imgDesc;

                $filename = $_FILES['userfile']['tmp_name'][$file_count];
                $mediaobject[$realname]['data'] = file_get_contents($filename);
            }
        }
        //print_r($mediaobject);

        $result = $api->load_local_api('media__create', ['media_parent_id' => $edit, 'media_type' => $media_type, 'media_data' => $mediaobject]);
        $status = '';
        //print_r($result);
        foreach ($result['media_error'] as $file => $error) {
            if ($error) {
                $status .= 'File ' . $file . ' - ' . $result['media_response'][$file] . "\r\n";
            }
        }
        if ($status == '') {
            $status .= 'File(s) Uploaded Successfully';
        }
        return $status;
    } // end function handleUpload

    public function ajax_display_upload_media($edit_id, $media_type)
    {
        global $conn, $lang, $config, $misc, $listingID, $jscript;
        $edit_id = intval($edit_id);
        $status_text = '';
        $has_permission = false;
        $support_external = false;
        $has_permission = $this->media_permission_check(0, $media_type, $edit_id);
        if ($has_permission != false) {
            include_once $config['basepath'] . '/include/forms.inc.php';
            $forms = new forms();
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/media_upload.html');
            switch ($media_type) {
                case 'listingsimages':
                    $support_external = true;
                    $sql = 'SELECT count(listingsimages_id) as num_images 
							FROM ' . $config['table_prefix'] . "listingsimages 
							WHERE listingsdb_id = $edit_id";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $media_count = $recordSet->fields('num_images');
                    $avaliable_media = $config['max_listings_uploads'] - $media_count;
                    $max_media_size = $config['max_listings_upload_size'];
                    $upload_lang = $lang['upload_a_picture'];
                    break;
                case 'userimages':
                    $support_external = false;
                    $sql = 'SELECT count(userimages_id) as num_images 
							FROM ' . $config['table_prefix'] . "userimages 
							WHERE userdb_id = $edit_id";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $media_count = $recordSet->fields('num_images');
                    $avaliable_media = $config['max_user_uploads'] - $media_count;
                    $max_media_size = $config['max_user_upload_size'];
                    $upload_lang = $lang['upload_a_picture'];
                    break;
                case 'listingsvtours':
                    $support_external = false;
                    $sql = 'SELECT count(listingsvtours_id) as num_images 
							FROM ' . $config['table_prefix'] . "listingsvtours 
							WHERE listingsdb_id = $edit_id";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $media_count = $recordSet->fields('num_images');
                    $avaliable_media = $config['max_vtour_uploads'] - $media_count;
                    $max_media_size = $config['max_vtour_upload_size'];
                    $upload_lang = $lang['upload_a_vtour'];
                    break;
                case 'listingsfiles':
                    $support_external = false;
                    $sql = 'SELECT count(listingsfiles_id) as num_files 
							FROM ' . $config['table_prefix'] . "listingsfiles 
							WHERE listingsdb_id = $edit_id";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $media_count = $recordSet->fields('num_files');
                    $avaliable_media = $config['max_listings_file_uploads'] - $media_count;
                    $max_media_size = $config['max_listings_file_upload_size'];
                    $upload_lang = $lang['upload_a_file'];
                    break;
                case 'usersfiles':
                    $support_external = false;
                    $sql = 'SELECT count(usersfiles_id) as num_files FROM ' . $config['table_prefix'] . "usersfiles WHERE userdb_id = $edit_id";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $media_count = $recordSet->fields('num_files');
                    $avaliable_media = $config['max_users_file_uploads'] - $media_count;
                    $max_media_size = $config['max_users_file_upload_size'];
                    $upload_lang = $lang['upload_a_file'];
                    break;
            }
            $x = 0;
            $html = $page->get_template_section('media_upload_block');
            $new_html = '';
            while ($x < $avaliable_media) {
                //limit this to 10 at a time
                if ($x < 10) {
                    $new_html .= $html;
                }
                $x++;
            }
            $page->replace_template_section('media_upload_block', $new_html);
            //External Image Form
            if ($support_external) {
                $x = 0;
                $html = $page->get_template_section('media_ext_link_block');
                $new_html = '';
                while ($x < $avaliable_media) {
                    //limit this to 10 at a time
                    if ($x < 10) {
                        $new_html .= $html;
                    }
                    $x++;
                }
                $page->replace_template_section('media_ext_link_block', $new_html);
                $page->page = $page->cleanup_template_block('show_ext_upload', $page->page);
            } else {
                $page->page = $page->remove_template_block('show_ext_upload', $page->page);
            }
            $page->page = str_replace('{upload_lang_text}', $upload_lang, $page->page);
            $page->page = str_replace('{edit_id}', $edit_id, $page->page);
            $page->page = str_replace('{media_type}', $media_type, $page->page);
            $page->page = str_replace('{max_upload_size}', $max_media_size, $page->page);
            //Finish Loading Template
            $page->replace_tag('application_status_text', $status_text);
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        } else {
            return 'Permission Denied';
        }
    }

    public function media_permission_check($media_id = 0, $media_type = '', $parent_id = 0)
    {
        global $conn, $lang, $misc, $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $has_permission = false;
        $parent_id = intval($parent_id);
        $media_type = $media_type;
        //Make sure Media Type is valid
        if (!in_array($media_type, $this->media_types)) {
            return false;
        }

        switch ($media_type) {
            case 'listingsimages':
                include_once $config['basepath'] . '/include/listing.inc.php';
                $listing_pages = new listing_pages();

                if ($media_id != 0 && $parent_id == 0) {
                    $sql = 'SELECT listingsdb_id FROM ' . $config['table_prefix'] . "$media_type WHERE ( " . $media_type . "_id = $media_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $parent_id = $recordSet->fields('listingsdb_id');
                }
                //Get Listing owner
                $listing_agent_id = $listing_pages->get_listing_agent_value('userdb_id', $parent_id);
                //Make sure we can Edit this lisitng
                if ($_SESSION['userID'] != $listing_agent_id) {
                    $security = $login->verify_priv('edit_all_listings');
                    if ($security === true) {
                        $has_permission = $parent_id;
                    }
                } else {
                    $has_permission = $parent_id;
                }
                break;

            case 'userimages':
                if ($media_id != 0 && $parent_id == 0) {
                    $sql = 'SELECT userdb_id 
							FROM ' . $config['table_prefix'] . "$media_type 
							WHERE ( " . $media_type . "_id = $media_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $parent_id = $recordSet->fields('userdb_id');
                }
                if ($_SESSION['userID'] != $parent_id) {
                    $security = $login->verify_priv('edit_all_users');
                    if ($security === true) {
                        $has_permission = $parent_id;
                    }
                } else {
                    $has_permission = $parent_id;
                }

                break;
            case 'listingsvtours':
                include_once $config['basepath'] . '/include/listing.inc.php';
                $listing_pages = new listing_pages();
                if ($media_id != 0 && $parent_id == 0) {
                    $sql = 'SELECT listingsdb_id 
							FROM ' . $config['table_prefix'] . "$media_type 
							WHERE ( " . $media_type . "_id = $media_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $parent_id = $recordSet->fields('listingsdb_id');
                }
                //Get Listing owner
                $listing_agent_id = $listing_pages->get_listing_agent_value('userdb_id', $parent_id);
                //Make sure we can Edit this lisitng
                if ($_SESSION['userID'] != $listing_agent_id) {
                    if ($_SESSION['edit_all_listings'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                        $has_permission = $parent_id;
                    }
                } elseif ($_SESSION['havevtours'] == 'yes') {
                    $has_permission = $parent_id;
                }
                break;
            case 'usersfiles':
                if ($media_id != 0 && $parent_id == 0) {
                    $sql = 'SELECT userdb_id 
							FROM ' . $config['table_prefix'] . "$media_type 
							WHERE ( " . $media_type . "_id = $media_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $parent_id = $recordSet->fields('userdb_id');
                }

                //Make sure we can Edit this lisitng
                if ($_SESSION['userID'] != $parent_id) {
                    if ($_SESSION['edit_all_users'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                        $has_permission = $parent_id;
                    }
                } elseif ($_SESSION['havefiles'] == 'yes') {
                    $has_permission = $parent_id;
                }
                break;
            case 'listingsfiles':
                include_once $config['basepath'] . '/include/listing.inc.php';
                $listing_pages = new listing_pages();
                if ($media_id != 0 && $parent_id == 0) {
                    $sql = 'SELECT listingsdb_id FROM ' . $config['table_prefix'] . "$media_type WHERE ( " . $media_type . "_id = $media_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $parent_id = $recordSet->fields('listingsdb_id');
                }
                //Get Listing owner
                $listing_agent_id = $listing_pages->get_listing_agent_value('userdb_id', $parent_id);
                //Make sure we can Edit this lisitng
                if ($_SESSION['userID'] != $listing_agent_id) {
                    if ($_SESSION['edit_all_listings'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                        $has_permission = $parent_id;
                    }
                } elseif ($_SESSION['havefiles'] == 'yes') {
                    $has_permission = $parent_id;
                }
                break;
        }
        return $has_permission;
    }

    public function ajax_update_media()
    {
        global $conn, $lang, $config, $misc, $listingID, $jscript;

        if (isset($_POST['media_id']) && isset($_POST['media_type'])) {
            include_once $config['basepath'] . '/include/listing.inc.php';
            $listing_pages = new listing_pages();
            $media_type = $_POST['media_type'];
            $media_id = intval($_POST['media_id']);
            $has_permission = $this->media_permission_check($media_id, $media_type);
            if ($has_permission != false) {
                if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
                }

                $parent_id = $has_permission;
                switch ($media_type) {
                    case 'listingsimages':
                    case 'listingsvtours':
                    case 'listingsfiles':
                        $foriegn_key = 'listingsdb_id';
                        break;
                    case 'userimages':
                    case 'usersfiles':
                        $foriegn_key = 'userdb_id';
                        break;
                }
                if (isset($_POST['caption']) && isset($_POST['description'])) {
                    $sql_caption = $misc->make_db_safe($_POST['caption']);
                    $sql_description = $misc->make_db_safe($_POST['description']);
                    $sql = 'UPDATE ' . $config['table_prefix'] . "$media_type 
							SET " . $media_type . "_caption = $sql_caption, " . $media_type . "_description = $sql_description 
							WHERE (($foriegn_key = $parent_id) 
							AND (" . $media_type . "_id = $media_id ))";

                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    header('Content-type: application/json');
                    return json_encode(['error' => '0', 'error_msg' => '']);
                } else {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => 'Image Id/Caption/Description Not Set']);
                }
            } else {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => 'Permission Denied']);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => 'Media Type or Listing ID not Set']);
        }
    }

    public function ajax_delete_all($media_type, $media_parent_id, $media_object_id)
    {
        global $api;
        $result = $api->load_local_api('media__delete', ['media_parent_id' => $media_parent_id, 'media_type' => $media_type, 'media_object_id' => $media_object_id]);
        if ($result['error']) {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $result['error_msg']]);
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '0', 'error_msg' => '']);
        }
    }

    public function ajax_delete_media()
    {
        global $conn, $lang, $config, $misc, $listingID, $jscript, $api;

        if (isset($_POST['media_type']) && isset($_POST['media_id'])) {
            include_once $config['basepath'] . '/include/listing.inc.php';
            $listing_pages = new listing_pages();
            $media_id = intval($_POST['media_id']);
            $media_type = $_POST['media_type'];
            $has_permission = $this->media_permission_check($media_id, $media_type);
            if ($has_permission != false) {
                $result = $api->load_local_api('media__delete', ['media_parent_id' => $has_permission, 'media_type' => $media_type, 'media_object_id' => $media_id]);
                if ($result['error']) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $result['error_msg']]);
                } else {
                    header('Content-type: application/json');
                    return json_encode(['error' => '0', 'error_msg' => '']);
                }
            } else {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => 'Permission Denied']);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => 'Media Type or Listing ID not Set']);
        }
    }

    public function ajax_save_media_order()
    {
        global $conn, $lang, $config, $misc, $listingID, $jscript;

        if (isset($_POST['media_type']) && isset($_POST['mediaOrder'])) {
            include_once $config['basepath'] . '/include/listing.inc.php';
            $listing_pages = new listing_pages();
            $media_order = $_POST['mediaOrder'];
            $media_type = $_POST['media_type'];
            $parent_id = intval($_POST['parent_id']);
            $has_permission = false;
            //  Make sure Media Type is valid
            $has_permission = $this->media_permission_check(0, $media_type, $parent_id);
            if ($has_permission != false) {
                foreach ($media_order as $image_rank => $image_id) {
                    $image_id = str_replace('mediaOrder_', '', $image_id);
                    $image_rank = intval($image_rank);
                    $image_id = intval($image_id);
                    switch ($media_type) {
                        case 'listingsimages':
                        case 'listingsvtours':
                        case 'listingsfiles':
                            $foriegn_key = 'listingsdb_id';
                            break;
                        case 'userimages':
                        case 'usersfiles':
                            $foriegn_key = 'userdb_id';
                            break;
                    }
                    $sql = 'UPDATE ' . $config['table_prefix'] . $media_type . ' 
							SET ' . $media_type . '_rank = ' . $image_rank . ' 
							WHERE ((' . $foriegn_key . ' = ' . $parent_id . ') 
							AND (' . $media_type . '_id = ' . $image_id . '))';
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'error_msg' => ""]);
            } else {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => 'Permission Denied']);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => 'Media Type or Listing ID not Set']);
        }
        //$.post("ajax_save_listing_media_order", { media_type: "image",listing_id,"{listing_id}", order },
    }

    public function ajax_upload_media_JSON()
    {
        global $conn, $lang, $config, $misc, $listingID, $jscript;

        $status = '';
        if (isset($_POST['edit']) && isset($_POST['media_type'])) {
            include_once $config['basepath'] . '/include/listing.inc.php';
            $listing_pages = new listing_pages();
            $media_type = $_POST['media_type'];
            if (!in_array($media_type, $this->media_types)) {
                $status = 'Invalid Media Type';
            }
            $edit_id = intval($_POST['edit']);
            $has_permission = $this->media_permission_check(0, $media_type, $edit_id);
            if ($has_permission != false) {
                switch ($media_type) {
                    case 'listingsimages':
                        $status = $this->handleUpload('listingsimages', $edit_id, $has_permission);
                        break;
                    case 'listingsvtours':
                        $status = $this->handleUpload('listingsvtours', $edit_id, $has_permission);
                        break;
                    case 'listingsfiles':
                        $status = $this->handleUpload('listingsfiles', $edit_id, $has_permission);
                        break;
                    case 'userimages':
                        $status = $this->handleUpload('userimages', $edit_id, $edit_id);
                        break;
                    case 'usersfiles':
                        $status = $this->handleUpload('usersfiles', $edit_id, $edit_id);
                        break;
                }
            } else {
                $status = 'Permission Denined';
            }
        }

        return json_encode(['status' => $status]);
    }

    public function ajax_upload_media()
    {
        global $conn, $lang, $config, $misc, $listingID, $jscript;

        if (isset($_POST['edit']) && isset($_POST['media_type'])) {
            include_once $config['basepath'] . '/include/listing.inc.php';
            $listing_pages = new listing_pages();
            $media_type = $_POST['media_type'];
            if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
                return $lang['invalid_csrf_token'];
            }
            if (!in_array($media_type, $this->media_types)) {
                return 'Invalid Media Type';
            }
            $edit_id = intval($_POST['edit']);
            $has_permission = $this->media_permission_check(0, $media_type, $edit_id);
            if ($has_permission != false) {
                switch ($media_type) {
                    case 'listingsimages':
                        if (isset($_POST['extlink'])) {
                            $display = $this->handleExtLink('listingsimages', $edit_id, $has_permission);
                        } else {
                            $display = $this->handleUpload('listingsimages', $edit_id, $has_permission);
                        }
                        break;
                    case 'listingsvtours':
                        if (isset($_POST['extlink'])) {
                            //Skip as vtours do not support external links at this time
                            return 'External Images Not Supported';
                        } else {
                            $display = $this->handleUpload('listingsvtours', $edit_id, $has_permission);
                        }
                        break;
                    case 'listingsfiles':
                        if (isset($_POST['extlink'])) {
                            //Skip as vtours do not support external links at this time
                            return 'External Media Not Supported';
                        } else {
                            $display = $this->handleUpload('listingsfiles', $edit_id, $has_permission);
                        }
                        break;
                    case 'userimages':
                        if (isset($_POST['extlink'])) {
                            $display = $this->handleExtLink('userimages', $edit_id, $edit_id);
                        } else {
                            $display = $this->handleUpload('userimages', $edit_id, $edit_id);
                        }
                        break;
                    case 'usersfiles':
                        if (isset($_POST['extlink'])) {
                            //Skip as vtours do not support external links at this time
                            return 'External Media Not Supported';
                        } else {
                            $display = $this->handleUpload('usersfiles', $edit_id, $edit_id);
                        }
                        break;
                }

                return $display;
            } else {
                return 'Permission Denined';
            }
        }
    }

    public function get_media_caption($media_type, $media_id)
    {
        global $conn, $config, $misc, $lang;

        $media_id = intval($media_id);
        $caption = '';
        //Check for allowed mediatypes
        if (in_array($media_type, $this->media_types)) {
            $sql = 'SELECT ' . $media_type . '_caption 
					FROM ' . $config['table_prefix'] . $media_type . ' 
					WHERE (' . $media_type . '_id = ' . $media_id . ')';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $caption = $recordSet->fields($media_type . '_caption');
        }
        return $caption;
    }

    public function ajax_get_media_info($media_id, $media_type)
    {
        global $conn, $lang, $config, $misc, $api, $listingID, $jscript;

        $media_id = intval($media_id);
        include_once $config['basepath'] . '/include/listing.inc.php';
        $listing_pages = new listing_pages();

        //Get Listing ID for Image
        $has_permission = $this->media_permission_check($media_id, $media_type);
        switch ($media_type) {
            case 'listingsvtours':
                $view_path = $config['vtour_view_images_path'];
                $upload_path = $config['vtour_upload_path'];
                $full_displaywidth = 600;
                break;
            case 'listingsimages':
                $view_path = $config['listings_view_images_path'];
                $upload_path = $config['listings_upload_path'];
                $full_displaywidth = '';
                break;
            case 'listingsfiles':
                $view_path = $config['listings_view_file_path'];
                $upload_path = $config['listings_file_upload_path'];
                $full_displaywidth = '';
                // no break
            case 'userimages':
                $view_path = $config['user_view_images_path'];
                $upload_path = $config['user_upload_path'];
                $full_displaywidth = '';
                break;
            case 'usersfiles':
                $view_path = $config['users_view_file_path'];
                $upload_path = $config['users_file_upload_path'];
                $full_displaywidth = '';
        }
        if ($has_permission !== false) {
            switch ($media_type) {
                case 'listingsvtours':
                case 'listingsimages':
                case 'userimages':
                    /*
                                         $result = $api->load_local_api('media__read',array(
                                                'media_type'=>$media_type,
                                                'media_parent_id'=>$media_id,
                                                'media_output'=>'URL'
                                        ));
                                        if($result['error']){
                                            //If an error occurs die and show the error msg;
                                            die($result['error_msg']);
                                        }
                                        $caption =  $result['media_object'][0]['caption'];
                                        $description = $result['media_object'][0]['description'];
                                        $thumb_file_name = $result['media_object'][0]['thumb_file_name'];
                                        $file_name = $result['media_object'][0]['file_name'];
                    */
                    $sql = 'SELECT ' . $media_type . '_caption, ' . $media_type . '_file_name, ' . $media_type . '_thumb_file_name, ' . $media_type . '_description ,' . $media_type . '_id
							FROM ' . $config['table_prefix'] . "$media_type 
							WHERE ( " . $media_type . "_id = $media_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $caption = $recordSet->fields($media_type . '_caption');
                    $description = $recordSet->fields($media_type . '_description');
                    $thumb_file_name = $recordSet->fields($media_type . '_thumb_file_name');
                    $file_name = $recordSet->fields($media_type . '_file_name');
                    $parent_id = $recordSet->fields($media_type . '_id');

                    if (strpos($thumb_file_name, 'http://') === 0 || strpos($thumb_file_name, 'https://') === 0 || strpos($thumb_file_name, '//') === 0) {
                        $image_full_src = $file_name;
                        $image_thumb_src = $thumb_file_name;
                        $thumb_displaywidth = $config['thumbnail_width'];
                        $thumb_displayheight = $config['thumbnail_height'];
                        $media_filesize = 0;
                        $full_imagedata = GetImageSize($file_name);
                        $full_imagewidth = $full_imagedata[0];
                        $full_imageheight = $full_imagedata[1];
                        $head = array_change_key_case(get_headers($file_name, true));
                        $media_filesize = round(intval($head['content-length']) * .0009765625, 0, PHP_ROUND_HALF_UP);
                    } else {
                        $image_full_src = $view_path . '/' . $file_name;
                        $image_thumb_src = $view_path . '/' . $thumb_file_name;

                        if (file_exists("$upload_path/$file_name")) {
                            $full_imagedata = GetImageSize("$upload_path/$file_name");
                            if ($full_imagedata !== false) {
                                $full_imagewidth = $full_imagedata[0];
                                $full_imageheight = $full_imagedata[1];
                            }
                            $media_filesize = round(filesize("$upload_path/$file_name") * .0009765625, 0, PHP_ROUND_HALF_UP);
                        }

                        if (file_exists("$upload_path/$thumb_file_name")) {
                            $thumb_imagedata = GetImageSize("$upload_path/$thumb_file_name");
                            if ($thumb_imagedata !== false) {
                                $thumb_imagewidth = $thumb_imagedata[0];
                                $thumb_imageheight = $thumb_imagedata[1];
                                $thumb_max_width = $config['thumbnail_width'];
                                $thumb_max_height = $config['thumbnail_height'];
                                $resize_by = $config['resize_thumb_by'];
                                $shrinkage = 1;

                                if (($thumb_max_width == $thumb_imagewidth) || ($thumb_max_height == $thumb_imageheight)) {
                                    $thumb_displaywidth = $thumb_imagewidth;
                                    $thumb_displayheight = $thumb_imageheight;
                                } else {
                                    if ($resize_by == 'width') {
                                        $shrinkage = $thumb_imagewidth / $thumb_max_width;
                                        $thumb_displaywidth = $thumb_max_width;
                                        $thumb_displayheight = round($thumb_imageheight / $shrinkage);
                                    } elseif ($resize_by == 'height') {
                                        $shrinkage = $thumb_imageheight / $thumb_max_height;
                                        $thumb_displayheight = $thumb_max_height;
                                        $thumb_displaywidth = round($thumb_imagewidth / $shrinkage);
                                    } elseif ($resize_by == 'both') {
                                        $thumb_displayheight = $thumb_max_height;
                                        $thumb_displaywidth = $thumb_max_width;
                                    } elseif ($resize_by == 'bestfit') {
                                        $shrinkage_width = $thumb_imagewidth / $thumb_max_width;
                                        $shrinkage_height = $thumb_imageheight / $thumb_max_height;
                                        $shrinkage = max($shrinkage_width, $shrinkage_height);
                                        $thumb_displayheight = round($thumb_imageheight / $shrinkage);
                                        $thumb_displaywidth = round($thumb_imagewidth / $shrinkage);
                                    }
                                }
                            } else {
                                $thumb_displayheight = '';
                                $thumb_displaywidth = '';
                            }
                        } else {
                            $thumb_displayheight = '';
                            $thumb_displaywidth = '';
                        }
                    }
                    break;
                case 'listingsfiles':
                case 'usersfiles':
                    $sql = 'SELECT ' . $media_type . '_caption, ' . $media_type . '_file_name, ' . $media_type . '_description , ' . $media_type . '_id
							FROM ' . $config['table_prefix'] . "$media_type 
							WHERE ( " . $media_type . "_id = $media_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $caption = $recordSet->fields($media_type . '_caption');
                    $description = $recordSet->fields($media_type . '_description');
                    $file_name = $recordSet->fields($media_type . '_file_name');
                    $parent_id = $recordSet->fields($media_type . '_id');
                    $thumb_name = '';
                    $thumb_displaywidth = '';
                    $thumb_displayheight = '';
                    $image_full_src = '';
                    $image_thumb_src = '';
                    $media_filesize = '';
                    $full_imagewidth = '';
                    $full_imageheight = '';


                    break;
            }

            header('Content-type: application/json');
            return json_encode([
                'error' => '0',
                'media_id' => "$media_id",
                'parent_id' => "$parent_id",
                'thumb_width' => "$thumb_displaywidth",
                'thumb_height' => "$thumb_displayheight",
                'full_width' => "$full_displaywidth",
                'media_caption' => "$caption",
                'media_description' => "$description",
                'media_full_src' => "$image_full_src",
                'media_thumb_src' => "$image_thumb_src",
                'file_name' => $file_name,
                'media_file_size' => $media_filesize . 'KB',
                'media_file_width' => $full_imagewidth,
                'media_file_height' => $full_imageheight,
            ]);
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => 'Permission Denied']);
        }
    }
}

class vtour_handler extends media_handler
{
    public function ajax_display_listing_vtours($listing_id)
    {
        global $conn, $lang, $config, $misc, $listingID, $jscript;

        $listing_id = intval($listing_id);
        include_once $config['basepath'] . '/include/listing.inc.php';
        $listing_pages = new listing_pages();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $status_text = '';
        //Get Listing owner
        $listing_agent_id = $listing_pages->get_listing_agent_value('userdb_id', $listing_id);
        //Make sure we can Edit this lisitng
        $has_permission = true;
        if ($_SESSION['userID'] != $listing_agent_id) {
            $security = $login->verify_priv('edit_all_listings');
            if (!$security) {
                $has_permission = false;
            }
        }
        if ($has_permission) {
            include_once $config['basepath'] . '/include/forms.inc.php';
            $forms = new forms();
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/listing_editor_vtour_display.html');
            //Load Listing Images
            $sql = 'SELECT listingsvtours_id, listingsvtours_caption, listingsvtours_file_name, listingsvtours_thumb_file_name FROM ' . $config['table_prefix'] . "listingsvtours WHERE (listingsdb_id = $listing_id) ORDER BY listingsvtours_rank";
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $page->page = str_replace('{listing_id}', $listing_id, $page->page);
            $html = $page->get_template_section('vtour_block');
            $new_html = '';
            $num_images = $recordSet->RecordCount();
            while (!$recordSet->EOF) {
                $new_html .= $html;
                $caption = $recordSet->fields('listingsvtours_caption');
                $thumb_file_name = $recordSet->fields('listingsvtours_thumb_file_name');
                $file_name = $recordSet->fields('listingsvtours_file_name');
                $vtour_id = $recordSet->fields('listingsvtours_id');
                // gotta grab the image size
                $ext = substr(strrchr($file_name, '.'), 1);
                $new_html = str_replace('{vtour_caption}', htmlentities($caption, ENT_COMPAT, $config['charset']), $new_html);
                $new_html = str_replace('{vtour_id}', $vtour_id, $new_html);
                if ($ext == 'jpg' || $ext == 'jpeg') {
                    // gotta grab the image size
                    //echo '<br />'."$config[vtour_upload_path]/$thumb_file_name".'<br />';
                    $imagedata = GetImageSize("$config[vtour_upload_path]/$thumb_file_name");
                    $imagewidth = $imagedata[0];
                    $imageheight = $imagedata[1];
                    $shrinkage = $config['thumbnail_width'] / $imagewidth;
                    $displaywidth = $imagewidth * $shrinkage;
                    $displayheight = $imageheight * $shrinkage;
                    $new_html = str_replace('{vtour_thumb_src}', "$config[vtour_view_images_path]/$thumb_file_name", $new_html);
                    $new_html = str_replace('{vtour_height}', $displayheight, $new_html);
                    $new_html = str_replace('{vtour_width}', $displaywidth, $new_html);
                    $new_html = $page->remove_template_block('vtour_unsupported', $new_html);
                    $new_html = $page->cleanup_template_block('!vtour_unsupported', $new_html);
                } else {
                    $new_html = $page->remove_template_block('!vtour_unsupported', $new_html);
                    $new_html = $page->cleanup_template_block('vtour_unsupported', $new_html);
                }

                $recordSet->MoveNext();
            } // end while
            $page->replace_template_section('vtour_block', $new_html);
            $avaliable_images = $config['max_vtour_uploads'] - $num_images;
            if ($avaliable_images > 0) {
                $page->page = $page->cleanup_template_block('vtour_upload', $page->page);
            } else {
                $page->page = $page->remove_template_block('vtour_upload', $page->page);
            }
            //End Listing Images
            //Finish Loading Template
            $page->replace_tag('application_status_text', $status_text);
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        } else {
            return 'Permission Denied';
        }
    } //END function ajax_display_listing_vtours()

    public function show_vtour($listingID, $popup = true)
    {
        global $lang, $conn, $config, $misc, $jscript;

        $display = '';
        if (isset($_GET['listingID'])) {
            if ($_GET['listingID'] != '') {
                include_once $config['basepath'] . '/include/core.inc.php';
                $page = new page_user();
                $page->load_page($config['template_path'] . '/' . $config['vtour_template']);
                $listingID = intval($listingID);
                $page->replace_listing_field_tags($listingID);

                $sql = 'SELECT listingsvtours_caption, listingsvtours_description, listingsvtours_file_name, listingsvtours_rank 
						FROM ' . $config['table_prefix'] . "listingsvtours 
						WHERE (listingsdb_id = $listingID) 
						ORDER BY listingsvtours_rank";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $num_images = $recordSet->RecordCount();
                $vtour_iamges = [];
                $a = 0;

                while (!$recordSet->EOF) {
                    $caption = $recordSet->fields('listingsvtours_caption');
                    $description = $recordSet->fields('listingsvtours_description');
                    $file_name = $recordSet->fields('listingsvtours_file_name');
                    $url = $config['vtour_view_images_path'] . '/' . $file_name;
                    if ($caption == '') {
                        $caption = 'Virtual Tour Image ' . $a;
                    }
                    $vtour_iamges[$a] = array(
                        'title' => $caption,
                        'url' => $url,
                        'description' => $description
                    );
                    $a++;
                    $recordSet->MoveNext();
                } // end while


                $html = $page->get_template_section('vtour_block');
                $html_replace = '';
                foreach ($vtour_iamges as $options) {
                    //Yes Option
                    $html_replace .= $html;
                    $html_replace = str_replace('{url}', $options['url'], $html_replace);
                    $html_replace = str_replace('{title}', $options['title'], $html_replace);
                    $html_replace = str_replace('{description}', $options['description'], $html_replace);
                }
                $page->replace_template_section('vtour_block', $html_replace);

                if ($popup == false) {
                    $page->page = $page->remove_template_block('vtour_header', $page->page);
                    $page->page = $page->remove_template_block('vtour_footer', $page->page);
                    $page->page = $page->remove_template_block('vtour_content', $page->page);
                } else {
                    $page->page = $page->cleanup_template_block('vtour_header', $page->page);
                    $page->page = $page->cleanup_template_block('vtour_footer', $page->page);
                    $page->page = $page->cleanup_template_block('vtour_content', $page->page);
                }
                $page->page = str_replace('{template_url}', $config['template_url'], $page->page);
                $display = $page->return_page();
            } // end elseif ($listingID != "")
            else {
                $display .= "<a href=\"index.php\">$lang[perhaps_you_were_looking_something_else]</a>";
            }
        } else {
            $display .= "<a href=\"index.php\">$lang[perhaps_you_were_looking_something_else]</a>";
        }
        return $display;
    } // END function showvtour()

    public function rendervtourlink($listingID, $use_small_image = false)
    {
        // shows the images connected to a given image
        global $config, $lang, $misc, $conn;

        // grab the images
        $listingID_sql = intval($listingID);
        $output = '';
        $sql = 'SELECT listingsvtours_file_name 
				FROM ' . $config['table_prefix'] . "listingsvtours 
				WHERE (listingsdb_id = $listingID_sql) 
				ORDER BY listingsvtours_rank";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $num_images = $recordSet->RecordCount();
        if ($num_images > 0) {
            while (!$recordSet->EOF) {
                $file_name = $recordSet->fields('listingsvtours_file_name');
                $ext = substr(strrchr($file_name, '.'), 1);
                $recordSet->MoveNext();
            } // end while
            if ($ext == 'jpg') { // if it's a supported VTour then display the link button
                if ($use_small_image === true) {
                    $image = 'vtourbuttonsmall.jpg';
                } else {
                    $image = 'vtourbutton.jpg';
                }
                if (file_exists($config['template_path'] . '/images/' . $image)) {
                    $output .= '<a href="index.php?action=show_vtour&amp;popup=blank&amp;listingID=' . $listingID . '" onclick="window.open(\'index.php?action=show_vtour&amp;popup=blank&amp;listingID=' . $listingID . '\',\'\',\'width=' . $config['vt_popup_width'] . ',height=' . $config['vt_popup_height'] . '\');return false;"><img src="' . $config['template_url'] . '/images/' . $image . '" alt="' . $lang['click_here_for_vtour'] . '" /></a>';
                } else {
                    $output = '<a href="index.php?action=show_vtour&amp;popup=blank&amp;listingID=' . $listingID . '" onclick="window.open(\'index.php?action=show_vtour&amp;popup=blank&amp;listingID=' . $listingID . '\',\'\',\'width=' . $config['vt_popup_width'] . ',height=' . $config['vt_popup_height'] . '\');return false;">' . $lang['click_here_for_vtour'] . '</a>';
                }
            } //end if it's a supported VTour
        } // end if ($num_images > 0)
        return $output;
    } // END function rendervtourlink()
} //END class vtour_handler

class image_handler extends media_handler
{
    /**
     * renderUserImages
     *
     * @param   integer $userdb_id
     * @return  string $display
     */
    public function renderUserImages($userdb_id)
    {
        global $conn, $config, $misc, $lang, $api;

        $display = '';
        $userdb_id = intval($userdb_id);

        // grab the image data
        $result = $api->load_local_api('media__read', [
            'media_type' => 'userimages',
            'media_parent_id' => $userdb_id,
            'media_output' => 'URL',
        ]);
        if ($result['error']) {
            die($result['error_msg']);
        }
        $num_images = $result['media_count'];

        if ($num_images > 0) {
            foreach ($result['media_object'] as $obj) {
                $thumb_file_name = $obj['thumb_file_name'];
                $caption = $obj['caption'];
                $imageID = $obj['media_id'];
                // gotta grab the image size
                $imagedata = GetImageSize("$config[user_upload_path]/$thumb_file_name");
                $imagewidth = $imagedata[0];
                $imageheight = $imagedata[1];
                $max_width = $config['thumbnail_width'];
                $max_height = $config['thumbnail_height'];
                $resize_by = $config['resize_thumb_by'];
                $shrinkage = 1;
                if (($max_width == $imagewidth) || ($max_height == $imageheight)) {
                    $displaywidth = $imagewidth;
                    $displayheight = $imageheight;
                } else {
                    if ($resize_by == 'width') {
                        $shrinkage = $imagewidth / $max_width;
                        $displaywidth = $max_width;
                        $displayheight = round($imageheight / $shrinkage);
                    } elseif ($resize_by == 'height') {
                        $shrinkage = $imageheight / $max_height;
                        $displayheight = $max_height;
                        $displaywidth = round($imagewidth / $shrinkage);
                    } elseif ($resize_by == 'both') {
                        $displayheight = $max_height;
                        $displaywidth = $max_width;
                    } elseif ($resize_by == 'bestfit') {
                        $shrinkage_width = $imagewidth / $max_width;
                        $shrinkage_height = $imageheight / $max_height;
                        $shrinkage = max($shrinkage_width, $shrinkage_height);
                        $displayheight = round($imageheight / $shrinkage);
                        $displaywidth = round($imagewidth / $shrinkage);
                    }
                }
                $display .= "<a href=\"index.php?action=view_user_image&amp;image_id=$imageID\"> ";
                $display .= "<img src=\"$config[user_view_images_path]/$thumb_file_name\" height=\"$displayheight\" width=\"$displaywidth\"></a>";
                $display .= "<div class=\"user_images_caption\">$caption</div>";
            }
        } else {
            $display .= '<img src="' . $config['baseurl'] . '/images/nophoto.gif" alt="' . $lang['no_photo'] . '" /><br />';
        } // end ($num_images > 0)
        return $display;
    }

    public function view_image($type)
    {
        global $conn, $config, $misc, $lang;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $display = '';
        if (!isset($_GET['image_id'])) {
            return $lang['image_not_found'];
        }
        $sql_imageID = intval($_GET['image_id']);
        if ($type == 'listing') {
            // get the image data
            $sql = 'SELECT listingsimages_caption, listingsimages_file_name, listingsimages_description, listingsdb_id 
					FROM ' . $config['table_prefix'] . "listingsimages 
					WHERE (listingsimages_id = $sql_imageID)";
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            while (!$recordSet->EOF) {
                $caption = $recordSet->fields('listingsimages_caption');
                $file_name = $recordSet->fields('listingsimages_file_name');
                $description = $recordSet->fields('listingsimages_description');
                $listing_id = $recordSet->fields('listingsdb_id');
                $recordSet->MoveNext();
            }
            $display .= '<div class="view_image">';
            $display .= '<span class="image_caption">';
            if ($caption != '') {
                $display .= "$caption - ";
            }
            //SEO Friendly Links
            $url = $page->magicURIGenerator('listing', $listing_id, true);
            $url = '<a href="' . $url . '">';
            $display .= $url . $lang['return_to_listing'] . '</a></span><br />';

            if (strpos($file_name, 'http://') === 0 || strpos($file_name, 'https://') === 0 || strpos($file_name, '//') === 0) {
                $display .= '		<img src="' . htmlentities($file_name) . '" alt="' . $caption . '">';
            } else {
                $display .= '		<img src="' . $config['listings_view_images_path'] . '/' . $file_name . '" alt="' . $caption . '">';
            }

            $display .= '<br />';
            $display .= $description;
            $display .= '</div>';
        } elseif ($type == 'userimage') {
            // get the image data
            $sql = 'SELECT userimages_caption, userimages_file_name, userimages_description, userdb_id 
					FROM ' . $config['table_prefix'] . "userimages 
					WHERE (userimages_id = $sql_imageID)";
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            while (!$recordSet->EOF) {
                $caption = $recordSet->fields('userimages_caption');
                $file_name = $recordSet->fields('userimages_file_name');
                $description = $recordSet->fields('userimages_description');
                $user_id = $recordSet->fields('userdb_id');
                $recordSet->MoveNext();
            }
            $display .= '<table class="form_" align="center">';
            $display .= '<tr>';
            $display .= '	<td class="row_main">';
            $display .= '		<h3>';
            if ($caption != '') {
                $display .= "$caption - ";
            }
            $display .= '<a href="index.php?action=view_user&amp;user=' . $user_id . '">' . $lang['return_to_user'] . '</a></h3>';
            $display .= '		<center>';
            $display .= '		<img src="' . $config['user_view_images_path'] . '/' . $file_name . '" alt="' . $caption . '" border="1">';

            $display .= '		</center>';
            $display .= '		<br />';
            $display .= $description;
            $display .= '	</td>';
            $display .= '</tr>';
            $display .= '</table>';
        }
        return $display;
    }

    public function ajax_display_listing_images($listing_id)
    {
        global $conn, $lang, $config, $misc, $listingID, $jscript;

        $status_text = '';
        $listing_id = intval($listing_id);
        include_once $config['basepath'] . '/include/listing.inc.php';
        $listing_pages = new listing_pages();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        //Get Listing owner
        $listing_agent_id = $listing_pages->get_listing_agent_value('userdb_id', $listing_id);
        //Make sure we can Edit this lisitng
        $has_permission = true;
        if ($_SESSION['userID'] != $listing_agent_id) {
            $security = $login->verify_priv('edit_all_listings');
            if ($security !== true) {
                $has_permission = false;
            }
        }
        if ($has_permission) {
            include_once $config['basepath'] . '/include/forms.inc.php';
            $forms = new forms();
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/listing_editor_image_display.html');
            //Load Listing Images
            $sql = 'SELECT listingsimages_id, listingsimages_caption, listingsimages_file_name, listingsimages_thumb_file_name 
					FROM ' . $config['table_prefix'] . "listingsimages 
					WHERE (listingsdb_id = $listing_id) 
					ORDER BY listingsimages_rank";
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $page->page = str_replace('{listing_id}', $listing_id, $page->page);
            $html = $page->get_template_section('image_block');
            $new_html = '';
            $num_images = $recordSet->RecordCount();
            while (!$recordSet->EOF) {
                $new_html .= $html;
                $caption = $recordSet->fields('listingsimages_caption');
                $thumb_file_name = $recordSet->fields('listingsimages_thumb_file_name');
                $file_name = $recordSet->fields('listingsimages_file_name');
                $image_id = $recordSet->fields('listingsimages_id');
                // gotta grab the image size
                if (strpos($thumb_file_name, 'http://') === 0 || strpos($thumb_file_name, 'https://') === 0 || strpos($thumb_file_name, '//') === 0) {
                    $new_html = str_replace('{image_thumb_src}', htmlentities($file_name), $new_html);
                    $resize_by = $config['resize_thumb_by'];
                    if ($resize_by == 'width') {
                        $new_html = str_replace('{thumb_height}', '', $new_html);
                        $new_html = str_replace('{thumb_width}', $config['thumbnail_width'], $new_html);
                    } elseif ($resize_by == 'height') {
                        $new_html = str_replace('{thumb_height}', $config['thumbnail_height'], $new_html);
                        $new_html = str_replace('{thumb_width}', '', $new_html);
                    } else {
                        $new_html = str_replace('{thumb_height}', $config['thumbnail_height'], $new_html);
                        $new_html = str_replace('{thumb_width}', $config['thumbnail_width'], $new_html);
                    }

                    $new_html = str_replace('{image_caption}', htmlentities($caption, ENT_COMPAT, $config['charset']), $new_html);
                    $new_html = str_replace('{image_id}', $image_id, $new_html);
                } else {
                    if (file_exists("$config[listings_upload_path]/$thumb_file_name")) {
                        $thumb_imagedata = GetImageSize("$config[listings_upload_path]/$thumb_file_name");
                        $thumb_imagewidth = $thumb_imagedata[0];
                        $thumb_imageheight = $thumb_imagedata[1];
                        $thumb_max_width = $config['thumbnail_width'];
                        $thumb_max_height = $config['thumbnail_height'];
                        $resize_by = $config['resize_thumb_by'];
                        $shrinkage = 1;
                        if (($resize_by == 'width' && $thumb_max_width == $thumb_imagewidth) || ($resize_by == 'height' && $thumb_max_height == $thumb_imageheight)) {
                            $thumb_displaywidth = $thumb_imagewidth;
                            $thumb_displayheight = $thumb_imageheight;
                        } else {
                            if ($resize_by == 'width') {
                                $shrinkage = $thumb_imagewidth / $thumb_max_width;
                                $thumb_displaywidth = $thumb_max_width;
                                $thumb_displayheight = round($thumb_imageheight / $shrinkage);
                            } elseif ($resize_by == 'height') {
                                $shrinkage = $thumb_imageheight / $thumb_max_height;
                                $thumb_displayheight = $thumb_max_height;
                                $thumb_displaywidth = round($thumb_imagewidth / $shrinkage);
                            } elseif ($resize_by == 'both') {
                                $thumb_displayheight = $thumb_max_height;
                                $thumb_displaywidth = $thumb_max_width;
                            } elseif ($resize_by == 'bestfit') {
                                $shrinkage_width = $thumb_imagewidth / $thumb_max_width;
                                $shrinkage_height = $thumb_imageheight / $thumb_max_height;
                                $shrinkage = max($shrinkage_width, $shrinkage_height);
                                $thumb_displayheight = round($thumb_imageheight / $shrinkage);
                                $thumb_displaywidth = round($thumb_imagewidth / $shrinkage);
                            }
                        }
                        $new_html = str_replace('{image_thumb_src}', "$config[listings_view_images_path]/$thumb_file_name", $new_html);
                        $new_html = str_replace('{thumb_height}', $thumb_displayheight, $new_html);
                        $new_html = str_replace('{thumb_width}', $thumb_displaywidth, $new_html);
                        $new_html = str_replace('{image_caption}', htmlentities($caption, ENT_COMPAT, $config['charset']), $new_html);
                        $new_html = str_replace('{image_id}', $image_id, $new_html);
                    } else {
                        $new_html = str_replace('{image_thumb_src}', "$config[baseurl]/images/nophoto.gif", $new_html);
                        $new_html = str_replace('{thumb_height}', '', $new_html);
                        $new_html = str_replace('{thumb_width}', '', $new_html);
                        $new_html = str_replace('{image_caption}', htmlentities($caption, ENT_COMPAT, $config['charset']), $new_html);
                        $new_html = str_replace('{image_id}', $image_id, $new_html);
                    }
                }
                $recordSet->MoveNext();
            } // end while
            $page->replace_template_section('image_block', $new_html);
            $avaliable_images = $config['max_listings_uploads'] - $num_images;
            if ($avaliable_images > 0) {
                $page->page = $page->cleanup_template_block('image_upload', $page->page);
            } else {
                $page->page = $page->remove_template_block('image_upload', $page->page);
            }
            //End Listing Images
            //Finish Loading Template
            $page->replace_tag('application_status_text', $status_text);
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        } else {
            return 'Permission Denied';
        }
    }

    /**
     * ajax_display_user_images
     *
     * @param   integer $userdb_id
     * @return  string $page
     */
    public function ajax_display_user_images($userdb_id)
    {
        global $lang, $config, $api, $listingID, $jscript;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $userdb_id = intval($userdb_id);
        $status_text = '';
        //Make sure we can Edit this lisitng
        $has_permission = true;
        if ($_SESSION['userID'] != $userdb_id) {
            $security = $login->verify_priv('edit_all_users');
            if ($security !== true) {
                $has_permission = false;
            }
        }
        if ($has_permission) {
            include_once $config['basepath'] . '/include/forms.inc.php';
            $forms = new forms();
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/user_editor_image_display.html');

            // grab the image data
            $result = $api->load_local_api('media__read', [
                'media_type' => 'userimages',
                'media_parent_id' => $userdb_id,
                'media_output' => 'URL',
            ]);
            if ($result['error']) {
                die($result['error_msg']);
            }
            $num_images = $result['media_count'];
            $page->page = str_replace('{user_id}', $userdb_id, $page->page);
            $html = $page->get_template_section('image_block');
            $new_html = '';

            foreach ($result['media_object'] as $obj) {
                $new_html .= $html;
                $thumb_file_name = $obj['thumb_file_name'];
                $caption = $obj['caption'];
                $file_name = $obj['file_name'];
                $image_id = $obj['media_id'];

                // gotta grab the image size
                if (strpos($thumb_file_name, 'http://') === 0 || strpos($thumb_file_name, 'https://') === 0 || strpos($thumb_file_name, '//') === 0) {
                    $new_html = str_replace('{image_thumb_src}', htmlentities($file_name), $new_html);
                    $resize_by = $config['user_resize_thumb_by'];
                    if ($resize_by == 'width') {
                        $new_html = str_replace('{thumb_height}', '', $new_html);
                        $new_html = str_replace('{thumb_width}', $config['user_thumbnail_width'], $new_html);
                    } elseif ($resize_by == 'height') {
                        $new_html = str_replace('{thumb_height}', $config['user_thumbnail_height'], $new_html);
                        $new_html = str_replace('{thumb_width}', '', $new_html);
                    } else {
                        $new_html = str_replace('{thumb_height}', $config['user_thumbnail_height'], $new_html);
                        $new_html = str_replace('{thumb_width}', $config['user_thumbnail_width'], $new_html);
                    }

                    $new_html = str_replace('{image_caption}', htmlentities($caption, ENT_COMPAT, $config['charset']), $new_html);
                    $new_html = str_replace('{image_id}', $image_id, $new_html);
                } else {
                    if (file_exists("$config[user_upload_path]/$thumb_file_name")) {
                        $thumb_imagedata = GetImageSize("$config[user_upload_path]/$thumb_file_name");
                        $thumb_imagewidth = $thumb_imagedata[0];
                        $thumb_imageheight = $thumb_imagedata[1];
                        $thumb_max_width = $config['user_thumbnail_width'];
                        $thumb_max_height = $config['user_thumbnail_height'];
                        $resize_by = $config['user_resize_thumb_by'];
                        $shrinkage = 1;
                        if (($thumb_max_width == $thumb_imagewidth) || ($thumb_max_height == $thumb_imageheight)) {
                            $thumb_displaywidth = $thumb_imagewidth;
                            $thumb_displayheight = $thumb_imageheight;
                        } else {
                            if ($resize_by == 'width') {
                                $shrinkage = $thumb_imagewidth / $thumb_max_width;
                                $thumb_displaywidth = $thumb_max_width;
                                $thumb_displayheight = round($thumb_imageheight / $shrinkage);
                            } elseif ($resize_by == 'height') {
                                $shrinkage = $thumb_imageheight / $thumb_max_height;
                                $thumb_displayheight = $thumb_max_height;
                                $thumb_displaywidth = round($thumb_imagewidth / $shrinkage);
                            } elseif ($resize_by == 'both') {
                                $thumb_displayheight = $thumb_max_height;
                                $thumb_displaywidth = $thumb_max_width;
                            } elseif ($resize_by == 'bestfit') {
                                $shrinkage_width = $thumb_imagewidth / $thumb_max_width;
                                $shrinkage_height = $thumb_imageheight / $thumb_max_height;
                                $shrinkage = max($shrinkage_width, $shrinkage_height);
                                $thumb_displayheight = round($thumb_imageheight / $shrinkage);
                                $thumb_displaywidth = round($thumb_imagewidth / $shrinkage);
                            }
                        }
                        $new_html = str_replace('{image_thumb_src}', "$config[user_view_images_path]/$thumb_file_name", $new_html);
                        $new_html = str_replace('{thumb_height}', $thumb_displayheight, $new_html);
                        $new_html = str_replace('{thumb_width}', $thumb_displaywidth, $new_html);
                        $new_html = str_replace('{image_caption}', htmlentities($caption, ENT_COMPAT, $config['charset']), $new_html);
                        $new_html = str_replace('{image_id}', $image_id, $new_html);
                    } else {
                        $new_html = str_replace('{image_thumb_src}', "$config[baseurl]/images/nophoto.gif", $new_html);
                        $new_html = str_replace('{thumb_height}', '', $new_html);
                        $new_html = str_replace('{thumb_width}', '', $new_html);
                        $new_html = str_replace('{image_caption}', htmlentities($caption, ENT_COMPAT, $config['charset']), $new_html);
                        $new_html = str_replace('{image_id}', $image_id, $new_html);
                    }
                }
            } // end while

            $page->replace_template_section('image_block', $new_html);
            $avaliable_images = $config['max_listings_uploads'] - $num_images;
            if ($avaliable_images > 0) {
                $page->page = $page->cleanup_template_block('image_upload', $page->page);
            } else {
                $page->page = $page->remove_template_block('image_upload', $page->page);
            }
            //End Listing Images
            //Finish Loading Template
            $page->replace_tag('application_status_text', $status_text);
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        } else {
            return 'Permission Denied';
        }
    }

    public function renderListingsMainImageSlideShow($listingID, $template)
    {
        // shows the images connected to a given image
        global $conn, $lang, $config, $misc, $jscript;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        // grab the images
        $listingID = intval($listingID);
        $display_method = $config['main_image_display_by'];
        $max_width = $config['main_image_width'];
        $max_height = $config['main_image_height'];
        $real_max_height = 0;
        //$template = str_replace('{slideshow_max_width}',$max_width,$template);

        $slidshow_thumb_group_template = $page->get_template_section('slideshow_thumbnail_group_block', $template);
        $slidshow_thumb_group_output = '';

        $slidshow_thumb_template = $page->get_template_section('slideshow_thumbnail_block', $slidshow_thumb_group_template);
        $slidshow_thumb_output = '';

        $sql = 'SELECT listingsimages_id,listingsimages_thumb_file_name,listingsimages_file_name,listingsimages_caption,listingsimages_description 
				FROM ' . $config['table_prefix'] . "listingsimages 
				WHERE (listingsdb_id = $listingID) 
				ORDER BY listingsimages_rank";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $num_images = $recordSet->RecordCount();
        //TODO Make Number of Thumbs per Group a Site Config Option.
        $group_max_thumbs = $config['listingimages_slideshow_group_thumb'];
        $thumb_count = 1;
        if ($num_images > 0) {
            while (!$recordSet->EOF) {
                $file_name = $recordSet->fields('listingsimages_file_name');
                $thumb_file_name = $recordSet->fields('listingsimages_thumb_file_name');
                $imageID = $recordSet->fields('listingsimages_id');
                $caption = $recordSet->fields('listingsimages_caption');
                $description = $recordSet->fields('listingsimages_description');
                if (strpos($file_name, 'http://') === 0 || strpos($file_name, 'https://') === 0 || strpos($file_name, '//') === 0) {
                    $displaywidth = $max_width;
                    $displayheight = $max_height;
                    $src = $file_name;
                    $thumb_src = $thumb_file_name;
                } else {
                    //Make Sure File Exists.
                    if (!file_exists($config['listings_upload_path'] . '/' . $file_name)) {
                        $recordSet->MoveNext();
                        continue;
                    }
                    $imagedata = GetImageSize("$config[listings_upload_path]/$file_name");
                    $imagewidth = $imagedata[0];
                    $imageheight = $imagedata[1];

                    if ($display_method == 'width') {
                        $shrinkage = $max_width / $imagewidth;
                        $displaywidth = $max_width;
                        $displayheight = $imageheight * $shrinkage;
                    } elseif ($display_method == 'height') {
                        $shrinkage = $max_height / $imageheight;
                        $displayheight = $max_height;
                        $displaywidth = $imagewidth * $shrinkage;
                    } elseif ($display_method == 'both') {
                        $displaywidth = $max_width;
                        $displayheight = $max_height;
                    }
                    $src = $config['listings_view_images_path'] . '/' . $file_name;
                    $thumb_src = $config['listings_view_images_path'] . '/' . $thumb_file_name;
                }
                $slidshow_thumb_output .= $slidshow_thumb_template;
                $url = $page->magicURIGenerator('listing_image', $imageID, true);
                $slidshow_thumb_output = str_replace('{slideshow_thumbnail}', $thumb_src, $slidshow_thumb_output);
                $slidshow_thumb_output = str_replace('{slideshow_width}', $config['thumbnail_width'], $slidshow_thumb_output);
                $slidshow_thumb_output = str_replace('{slideshow_width_mainimage}', $config['max_listings_upload_width'], $slidshow_thumb_output);
                //$slidshow_thumb_output=str_replace('{slideshow_height}',$config['thumbnail_height'],$slidshow_thumb_output);
                $slidshow_thumb_output = str_replace('{slideshow_caption}', htmlentities($caption, ENT_COMPAT, $config['charset']), $slidshow_thumb_output);
                $slidshow_thumb_output = str_replace('{slideshow_description}', htmlentities($description, ENT_COMPAT, $config['charset']), $slidshow_thumb_output);
                $slidshow_thumb_output = str_replace('{slideshow_mainimage}', $src, $slidshow_thumb_output);
                $slidshow_thumb_output = str_replace('{slideshow_title}', $src, $slidshow_thumb_output);
                //Ensure we have the largest height
                if ($real_max_height < $displayheight) {
                    $real_max_height = round($displayheight + 50, 0);
                }
                if ($thumb_count == $group_max_thumbs) {
                    //Complete this group
                    $slidshow_thumb_group_output .= $page->replace_template_section('slideshow_thumbnail_block', $slidshow_thumb_output, $slidshow_thumb_group_template);
                    $slidshow_thumb_output = '';
                    $thumb_count = 1;
                } else {
                    $thumb_count++;
                }
                $recordSet->MoveNext();
            } // end while
            if ($slidshow_thumb_output != '') {
                $slidshow_thumb_group_output .= $page->replace_template_section('slideshow_thumbnail_block', $slidshow_thumb_output, $slidshow_thumb_group_template);
            }
            $template = $page->replace_template_section('slideshow_thumbnail_group_block', $slidshow_thumb_group_output, $template);
            $template = $page->cleanup_template_block('slideshow_display', $template);

        //$template = str_replace('{slideshow_max_height}',$real_max_height,$template);
        } // end if ($num_images > 0)
        else {
            if ($config['show_no_photo'] == 1) {
                $template = str_replace('{slideshow_thumbnail}', $config['baseurl'] . '/images/nophoto.gif', $template);
                $template = str_replace('{slideshow_width}', $config['thumbnail_width'], $template);
                $template = str_replace('{slideshow_width_mainimage}', $config['max_listings_upload_width'], $template);
                //$slidshow_thumb_output=str_replace('{slideshow_height}',$config['thumbnail_height'],$slidshow_thumb_output);
                $template = str_replace('{slideshow_alt}', htmlentities($lang['no_photo'], ENT_COMPAT, $config['charset']), $template);
                $template = str_replace('{slideshow_mainimage}', $config['baseurl'] . '/images/nophotobig.gif', $template);
                $template = str_replace('{slideshow_title}', $config['baseurl'] . '/images/nophotobig.gif', $template);
                $template = $page->cleanup_template_block('slideshow_display', $template);
                $template = $page->cleanup_template_block('slideshow_thumbnail', $template);
                $template = $page->cleanup_template_block('slideshow_thumbnail_group', $template);
            } else {
                $template = $page->remove_template_block('slideshow_display', $template);
                $template = $page->cleanup_template_block('slideshow_thumbnail', $template);
                $template = $page->cleanup_template_block('slideshow_thumbnail_group', $template);
            }
        }
        return $template;
    } // end function renderListingsMainImageSlideShow

    public function renderListingsImages($listingID, $showcap)
    {
        // shows the images connected to a given image
        global $conn, $lang, $misc, $config;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        // grab the images
        $listingID = intval($listingID);
        $sql = 'SELECT listingsimages_id, listingsimages_caption, listingsimages_thumb_file_name 
				FROM ' . $config['table_prefix'] . "listingsimages 
				WHERE (listingsdb_id = $listingID) 
				ORDER BY listingsimages_rank";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $display = '';
        $num_images = $recordSet->RecordCount();
        if ($num_images > 0) {
            while (!$recordSet->EOF) {
                $caption = $recordSet->fields('listingsimages_caption');
                $thumb_file_name = $recordSet->fields('listingsimages_thumb_file_name');
                // $file_name = $recordSet->fields('listingsimages_file_name');
                $imageID = $recordSet->fields('listingsimages_id');
                if (strpos($thumb_file_name, 'http://') === 0 || strpos($thumb_file_name, 'https://') === 0 || strpos($thumb_file_name, '//') === 0) {
                    $url = $page->magicURIGenerator('listing_image', $imageID, true);
                    $display .= '<a href="' . $url . '">';
                    if ($caption != '') {
                        $alt = $caption;
                    } else {
                        $alt = $thumb_file_name;
                    }
                    $display .= '<img src="' . htmlentities($thumb_file_name) . "\" height=\"$config[thumbnail_height]\" width=\"$config[thumbnail_width]\" alt=\"$alt\" /></a><br /> ";
                    if ($showcap == 'yes') {
                        $display .= '<strong>' . urldecode($caption) . '</strong><br /><br />';
                    } else {
                        $display .= '<br />';
                    }
                } elseif ($thumb_file_name != '' && file_exists("$config[listings_upload_path]/$thumb_file_name")) {
                    $thumb_imagedata = GetImageSize("$config[listings_upload_path]/$thumb_file_name");
                    $thumb_imagewidth = $thumb_imagedata[0];
                    $thumb_imageheight = $thumb_imagedata[1];
                    $thumb_max_width = $config['thumbnail_width'];
                    $thumb_max_height = $config['thumbnail_height'];
                    $resize_by = $config['resize_thumb_by'];
                    $shrinkage = 1;
                    if (($thumb_max_width == $thumb_imagewidth) || ($thumb_max_height == $thumb_imageheight)) {
                        $thumb_displaywidth = $thumb_imagewidth;
                        $thumb_displayheight = $thumb_imageheight;
                    } else {
                        if ($resize_by == 'width') {
                            $shrinkage = $thumb_imagewidth / $thumb_max_width;
                            $thumb_displaywidth = $thumb_max_width;
                            $thumb_displayheight = round($thumb_imageheight / $shrinkage);
                        } elseif ($resize_by == 'height') {
                            $shrinkage = $thumb_imageheight / $thumb_max_height;
                            $thumb_displayheight = $thumb_max_height;
                            $thumb_displaywidth = round($thumb_imagewidth / $shrinkage);
                        } elseif ($resize_by == 'both') {
                            $thumb_displayheight = $thumb_max_height;
                            $thumb_displaywidth = $thumb_max_width;
                        } elseif ($resize_by == 'bestfit') {
                            $shrinkage_width = $thumb_imagewidth / $thumb_max_width;
                            $shrinkage_height = $thumb_imageheight / $thumb_max_height;
                            $shrinkage = max($shrinkage_width, $shrinkage_height);
                            $thumb_displayheight = round($thumb_imageheight / $shrinkage);
                            $thumb_displaywidth = round($thumb_imagewidth / $shrinkage);
                        }
                    }
                    $url = $page->magicURIGenerator('listing_image', $imageID, true);
                    $display .= '<a href="' . $url . '">';
                    if ($caption != '') {
                        $alt = $caption;
                    } else {
                        $alt = $thumb_file_name;
                    }
                    $display .= "<img src=\"$config[listings_view_images_path]/$thumb_file_name\" height=\"$thumb_displayheight\" width=\"$thumb_displaywidth\" alt=\"$alt\" /></a><br /> ";
                    if ($showcap == 'yes') {
                        $display .= '<strong>' . urldecode($caption) . '</strong><br /><br />';
                    } else {
                        $display .= '<br />';
                    }
                } // end if ($thumb_file_name != "")
                $recordSet->MoveNext();
            } // end while
            // $display .= "</td>";
        } // end if ($num_images > 0)
        else {
            if ($config['show_no_photo'] == 1) {
                $display .= "<img src=\"$config[baseurl]/images/nophoto.gif\" width=\"$config[thumbnail_width]\" alt=\"$lang[no_photo]\" /><br /> ";
            }
        }
        return $display;
    } // end function renderListingsImages

    public function renderListingsMainImage($listingID, $showdesc, $java)
    {
        // shows the main image
        global $config, $lang, $misc, $conn;
        $display = '';
        // grab the images
        $listingID = intval($listingID);
        $sql = 'SELECT listingsimages_id, listingsimages_caption, listingsimages_file_name, listingsimages_description 
				FROM ' . $config['table_prefix'] . "listingsimages 
				WHERE (listingsdb_id = $listingID) 
				ORDER BY listingsimages_rank";
        $recordSet = $conn->execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $num_images = $recordSet->RecordCount();
        $first = false;
        $width = '';
        $height = '';
        if ($num_images > 0) {
            while (!$recordSet->EOF && !$first) {
                $file_name = $recordSet->fields('listingsimages_file_name');
                $caption = $recordSet->fields('listingsimages_caption');
                $description = $recordSet->fields('listingsimages_description');
                $shrinkage = 1;
                $display_method = $config['main_image_display_by'];
                $max_width = $config['main_image_width'];
                $max_height = $config['main_image_height'];
                $display_width = $max_width;
                $display_height = $max_height;

                if (strpos($file_name, 'http://') === 0 || strpos($file_name, 'https://') === 0 || strpos($file_name, '//') === 0) {
                    $img_src = htmlentities($file_name);
                    $width = $display_width;
                    $height = $display_height;
                } else {
                    if (!file_exists($config['listings_upload_path'] . '/' . $file_name)) {
                        $recordSet->MoveNext();
                        continue;
                    }
                    $imagedata = GetImageSize("$config[listings_upload_path]/$file_name");
                    $imagewidth = $imagedata[0];
                    $imageheight = $imagedata[1];
                    // Figure out display sizes based on display method
                    if ($display_method == 'width') {
                        $width = ' width="' . $max_width . '"';
                    } elseif ($display_method == 'height') {
                        $height = ' height="' . $max_height . '"';
                    } elseif ($display_method == 'both') {
                        $width = ' width="' . $max_width . '"';
                        $height = ' height="' . $max_height . '"';
                    }
                    $img_src = $config['listings_view_images_path'] . '/' . $file_name;
                    $first = true;
                }

                if ($java == 'yes') {
                    if ($showdesc == 'yes') {
                        $display = "<script type=\"text/javascript\"> function imgchange(id,caption,description){if(document.images){document.getElementById('main').src = id; document.getElementById('main').alt = caption; document.getElementById('main_image_description').innerHTML = description; } else { document.getElementById('main').src = \"images/nophoto.gif\";document.getElementById('main_image_description').innerHTML = ''; }}</script>";
                        $display .= "<img src=\"$img_src\" $width$height id=\"main\" alt=\"$caption\" /><br /><div id=\"main_image_description\">$description</div>";
                    } else {
                        $display = "<script type=\"text/javascript\"> function imgchange(id,caption,description){if(document.images){document.getElementById('main').src = id; document.getElementById('main').alt = caption;} else { document.getElementById('main').src = \"images/nophoto.gif\"; }}</script>";
                        $display .= "<img src=\"$img_src\" $width$height id=\"main\" alt=\"$caption\" /><br />";
                    }
                } else {
                    if ($showdesc == 'yes') {
                        $display .= "<img src=\"$img_src\" $width$height alt=\"$caption\" /><br /><div id=\"main_image_description\">$description</div>";
                    } else {
                        $display .= "<img src=\"$img_src\" $width$height alt=\"$caption\" /><br />";
                    }
                }

                $recordSet->MoveNext();
            } // end while
        } // end if ($num_images > 0)

        else {
            if ($config['show_no_photo'] == 1) {
                $display .= "<img src=\"$config[baseurl]/images/nophotobig.gif\" width=\"$width\" id=\"main\" alt=\"$lang[no_photo]\" /><br />";
            }
        }
        return $display;
    } // end function renderListingsMainImage

    public function renderListingsImagesJava($listingID, $showcap, $mouseover = 'no')
    {
        // shows the images connected to a given image
        global $config, $lang, $conn, $misc, $style;

        // grab the images
        $listingID = intval($listingID);
        $sql = 'SELECT listingsimages_caption, listingsimages_file_name, listingsimages_thumb_file_name, listingsimages_description 
				FROM ' . $config['table_prefix'] . "listingsimages 
				WHERE (listingsdb_id = $listingID) 
				ORDER BY listingsimages_rank";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $display = '';
        $num_images = $recordSet->RecordCount();
        if ($num_images > 0) {
            // $display .= "<td width=\"$style[image_column_width]\" valign=\"top\" class=\"row_main\" align=\"center\">";
            while (!$recordSet->EOF) {
                $caption = $recordSet->fields('listingsimages_caption');
                $thumb_file_name = $recordSet->fields('listingsimages_thumb_file_name');
                $file_name = $recordSet->fields('listingsimages_file_name');
                // $imageID = $recordSet->fields('listingsimages_id');
                $description = $recordSet->fields('listingsimages_description');
                $description = htmlentities($description, ENT_COMPAT, $config['charset']);
                $caption = htmlentities($caption, ENT_COMPAT, $config['charset']);
                $thumb_max_width = $config['thumbnail_width'];
                $thumb_max_height = $config['thumbnail_height'];
                if (strpos($thumb_file_name, 'http://') === 0 || strpos($thumb_file_name, 'https://') === 0 || strpos($thumb_file_name, '//') === 0) {
                    $thumb_displayheight = $thumb_max_height;
                    $thumb_displaywidth = $thumb_max_width;
                    $thumb_src = $thumb_file_name;
                    $image_src = $file_name;
                } else {
                    if (!file_exists($config['listings_upload_path'] . '/' . $thumb_file_name)) {
                        $recordSet->MoveNext();
                        continue;
                    }
                    // gotta grab the image size
                    $thumb_imagedata = GetImageSize("$config[listings_upload_path]/$thumb_file_name");
                    $thumb_imagewidth = $thumb_imagedata[0];
                    $thumb_imageheight = $thumb_imagedata[1];
                    $thumb_src = $config['listings_view_images_path'] . '/' . $thumb_file_name;
                    $resize_by = $config['resize_thumb_by'];
                    $shrinkage = 1;
                    if (($thumb_max_width == $thumb_imagewidth) || ($thumb_max_height == $thumb_imageheight)) {
                        $thumb_displaywidth = $thumb_imagewidth;
                        $thumb_displayheight = $thumb_imageheight;
                    } else {
                        if ($resize_by == 'width') {
                            $shrinkage = $thumb_imagewidth / $thumb_max_width;
                            $thumb_displaywidth = $thumb_max_width;
                            $thumb_displayheight = round($thumb_imageheight / $shrinkage);
                        } elseif ($resize_by == 'height') {
                            $shrinkage = $thumb_imageheight / $thumb_max_height;
                            $thumb_displayheight = $thumb_max_height;
                            $thumb_displaywidth = round($thumb_imagewidth / $shrinkage);
                        } elseif ($resize_by == 'both') {
                            $thumb_displayheight = $thumb_max_height;
                            $thumb_displaywidth = $thumb_max_width;
                        } elseif ($resize_by == 'bestfit') {
                            $shrinkage_width = $thumb_imagewidth / $thumb_max_width;
                            $shrinkage_height = $thumb_imageheight / $thumb_max_height;
                            $shrinkage = max($shrinkage_width, $shrinkage_height);
                            $thumb_displayheight = round($thumb_imageheight / $shrinkage);
                            $thumb_displaywidth = round($thumb_imagewidth / $shrinkage);
                        }
                    }
                    $image_src = $config['listings_view_images_path'] . '/' . $file_name;
                }
                if ($mouseover == 'no') {
                    $display .= "<a href=\"javascript:imgchange('$image_src'," . $misc->make_db_safe($caption) . "," . $misc->make_db_safe($description) . ")\"> ";
                    $display .= "<img src=\"$thumb_src\" height=\"$thumb_displayheight\" width=\"$thumb_displaywidth\" alt=\"$caption\" /></a><br />";
                } else {
                    $display .= '<img src="' . $thumb_src . '" height="' . $thumb_displayheight . '" width="' . $thumb_displaywidth . '" alt="' . $caption . '" onmouseover="imgchange(' . $misc->make_db_safe($image_src) . ',' . $misc->make_db_safe($caption) . ',' . $misc->make_db_safe($description) . ')" /><br />';
                }
                if ($showcap == 'yes') {
                    $display .= '<strong>' . $caption . '</strong><br /><br />';
                } else {
                    $display .= '<br />';
                }
                $recordSet->MoveNext();
            } // end while
            // $display .= "</td>";
        } // end if ($num_images > 0)
        else {
            if ($config['show_no_photo'] == 1) {
                $display .= "<img src=\"$config[baseurl]/images/nophoto.gif\" width=\"$config[thumbnail_width]\" alt=\"$lang[no_photo]\" /><br /> ";
            }
        }
        return $display;
    } // end function renderListingsImagesJava

    public function renderListingsImagesJavaRows($listingID, $mouseover = 'no')
    {
        // shows the images connected to a given image
        global $config, $lang, $conn, $misc, $style;

        // grab the images
        $var_reset = 1; // Reset the var (counter) (DO NOT CHANGE)
        $user_col_max = $config['number_columns']; // How Many To show Per Row
        $listingID = intval($listingID);
        $sql = 'SELECT listingsimages_caption, listingsimages_file_name, listingsimages_thumb_file_name, listingsimages_description 
				FROM ' . $config['table_prefix'] . "listingsimages 
				WHERE (listingsdb_id = $listingID) 
				ORDER BY listingsimages_rank";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $display = '';
        $num_images = $recordSet->RecordCount();
        if ($num_images > 0) {
            $display .= '<table id="imagerows">';

            while (!$recordSet->EOF) {
                $caption = $recordSet->fields('listingsimages_caption');
                $thumb_file_name = $recordSet->fields('listingsimages_thumb_file_name');
                $file_name = $recordSet->fields('listingsimages_file_name');
                $description = $recordSet->fields('listingsimages_description');
                $description = htmlentities($description, ENT_COMPAT, $config['charset']);
                $caption = htmlentities($caption, ENT_COMPAT, $config['charset']);

                $thumb_max_width = $config['thumbnail_width'];
                $thumb_max_height = $config['thumbnail_height'];
                if (strpos($thumb_file_name, 'http://') === 0 || strpos($thumb_file_name, 'https://') === 0 || strpos($thumb_file_name, '//') === 0) {
                    $thumb_displayheight = $thumb_max_height;
                    $thumb_displaywidth = $thumb_max_width;
                    $thumb_src = $thumb_file_name;
                    $image_src = $file_name;
                } else {
                    // gotta grab the image size
                    $thumb_imagedata = GetImageSize("$config[listings_upload_path]/$thumb_file_name");
                    $thumb_imagewidth = $thumb_imagedata[0];
                    $thumb_imageheight = $thumb_imagedata[1];
                    $thumb_src = $config['listings_view_images_path'] . '/' . $thumb_file_name;
                    $resize_by = $config['resize_thumb_by'];
                    $shrinkage = 1;
                    if (($thumb_max_width == $thumb_imagewidth) || ($thumb_max_height == $thumb_imageheight)) {
                        $thumb_displaywidth = $thumb_imagewidth;
                        $thumb_displayheight = $thumb_imageheight;
                    } else {
                        if ($resize_by == 'width') {
                            $shrinkage = $thumb_imagewidth / $thumb_max_width;
                            $thumb_displaywidth = $thumb_max_width;
                            $thumb_displayheight = round($thumb_imageheight / $shrinkage);
                        } elseif ($resize_by == 'height') {
                            $shrinkage = $thumb_imageheight / $thumb_max_height;
                            $thumb_displayheight = $thumb_max_height;
                            $thumb_displaywidth = round($thumb_imagewidth / $shrinkage);
                        } elseif ($resize_by == 'both') {
                            $thumb_displayheight = $thumb_max_height;
                            $thumb_displaywidth = $thumb_max_width;
                        } elseif ($resize_by == 'bestfit') {
                            $shrinkage_width = $thumb_imagewidth / $thumb_max_width;
                            $shrinkage_height = $thumb_imageheight / $thumb_max_height;
                            $shrinkage = max($shrinkage_width, $shrinkage_height);
                            $thumb_displayheight = round($thumb_imageheight / $shrinkage);
                            $thumb_displaywidth = round($thumb_imagewidth / $shrinkage);
                        }
                    }
                    $image_src = $config['listings_view_images_path'] . '/' . $file_name;
                }
                if ($var_reset == 1) {
                    $display .= '<tr>';
                }
                if ($caption == '') {
                    $caption = $thumb_file_name;
                }
                if ($mouseover == 'no') {
                    $display .= "<td><a href=\"javascript:imgchange('$image_src'," . $misc->make_db_safe($caption) . "," . $misc->make_db_safe($description) . ")\"> ";
                    $display .= "<img src=\"$thumb_src\" height=\"$thumb_displayheight\" width=\"$thumb_displaywidth\" alt=\"$caption\" /></a>";
                    $display .= '</td>';
                } else {
                    $display .= '<td><img src="' . $thumb_src . '" height="' . $thumb_displayheight . '" width="' . $thumb_displaywidth . '" alt="' . $caption . '" onmouseover="imgchange(\'' . $image_src . '\',' . $misc->make_db_safe($caption) . ',' . $misc->make_db_safe($description) . ')" /></td>';
                }
                if ($var_reset == $user_col_max) {
                    $display .= '</tr>';
                    $var_reset = 1;
                } else {
                    $var_reset++;
                }
                $recordSet->MoveNext();
            } // end while
            if ($var_reset != 1) {
                $display .= '</tr>';
            }
            $display .= '</table>';
        } // end if ($num_images > 0)
        else {
            if ($config['show_no_photo'] == 1) {
                $display .= '<table id="imagerows">';
                $display .= "<tr><td><img src=\"$config[baseurl]/images/nophoto.gif\" width=\"$config[thumbnail_width]\" alt=\"$lang[no_photo]\" /></td></tr></table>";
            }
        }
        return $display;
    } // end function renderListingsImagesJavaRows
}

class file_handler extends media_handler
{
    public $debug = false;

    public function ajax_display_listing_files($listing_id)
    {
        global $conn, $lang, $config, $misc, $listingID, $jscript;
        $listing_id = intval($listing_id);
        include_once $config['basepath'] . '/include/listing.inc.php';
        $listing_pages = new listing_pages();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $status_text = '';
        //Get Listing owner
        $listing_agent_id = $listing_pages->get_listing_agent_value('userdb_id', $listing_id);
        //Make sure we can Edit this lisitng
        $has_permission = true;
        if ($_SESSION['userID'] != $listing_agent_id) {
            $security = $login->verify_priv('edit_all_listings');
            if ($security !== true) {
                $has_permission = false;
            }
        }
        if ($has_permission) {
            include_once $config['basepath'] . '/include/forms.inc.php';
            $forms = new forms();
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/listing_editor_files_display.html');
            //Load Listing Images
            $sql = 'SELECT listingsfiles_id, listingsfiles_caption, listingsfiles_file_name FROM ' . $config['table_prefix'] . "listingsfiles WHERE (listingsdb_id = $listing_id) ORDER BY listingsfiles_rank";
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $page->page = str_replace('{listing_id}', $listing_id, $page->page);
            $html = $page->get_template_section('file_block');
            $new_html = '';
            $num_images = $recordSet->RecordCount();
            while (!$recordSet->EOF) {
                $new_html .= $html;
                $caption = $recordSet->fields('listingsfiles_caption');
                $file_name = $recordSet->fields('listingsfiles_file_name');
                if (strlen($file_name) > 27) {
                    $file_name = substr(strip_tags($file_name), 0, 23) . ' ...';
                    ;
                }
                $file_id = $recordSet->fields('listingsfiles_id');
                // gotta grab the image size
                $ext = substr(strrchr($file_name, '.'), 1);
                //Lookup Icon
                $iconpath = $config['file_icons_path'] . '/' . $ext . '.png';
                if (file_exists($iconpath)) {
                    $icon = $config['listings_view_file_icons_path'] . '/' . $ext . '.png';
                } else {
                    $icon = $config['listings_view_file_icons_path'] . '/default.png';
                }
                $new_html = str_replace('{file_caption}', htmlentities($caption, ENT_COMPAT, $config['charset']), $new_html);
                $new_html = str_replace('{file_id}', $file_id, $new_html);
                $new_html = str_replace('{icon_src}', $icon, $new_html);
                $new_html = str_replace('{file_name}', $file_name, $new_html);
                // gotta grab the image size
                //echo '<br />'."$config[file_upload_path]/$thumb_file_name".'<br />';

                $recordSet->MoveNext();
            } // end while
            $page->replace_template_section('file_block', $new_html);
            $avaliable_images = $config['max_listings_file_uploads'] - $num_images;
            if ($avaliable_images > 0) {
                $page->page = $page->cleanup_template_block('file_upload', $page->page);
            } else {
                $page->page = $page->remove_template_block('file_upload', $page->page);
            }
            //End Listing Images
            //Finish Loading Template
            $page->replace_tag('application_status_text', $status_text);
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        } else {
            return 'Permission Denied';
        }
    }

    public function ajax_display_user_files($user_id)
    {
        global $conn, $lang, $config, $misc, $listingID, $jscript;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $user_id = intval($user_id);
        $status_text = '';
        //Make sure we can Edit this lisitng
        $has_permission = true;
        if ($_SESSION['userID'] != $user_id) {
            $security = $login->verify_priv('edit_all_users');
            if ($security !== true) {
                $has_permission = false;
            }
        }
        if ($has_permission) {
            include_once $config['basepath'] . '/include/forms.inc.php';
            $forms = new forms();
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/user_editor_files_display.html');
            //Load Listing Images
            $sql = 'SELECT usersfiles_id, usersfiles_caption, usersfiles_file_name 
					FROM ' . $config['table_prefix'] . "usersfiles 
					WHERE (userdb_id = $user_id) 
					ORDER BY usersfiles_rank";
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $page->page = str_replace('{user_id}', $user_id, $page->page);
            $html = $page->get_template_section('file_block');
            $new_html = '';
            $num_images = $recordSet->RecordCount();
            while (!$recordSet->EOF) {
                $new_html .= $html;
                $caption = $recordSet->fields('usersfiles_caption');
                $file_name = $recordSet->fields('usersfiles_file_name');
                if (strlen($file_name) > 27) {
                    $file_name = substr(strip_tags($file_name), 0, 23) . ' ...';
                    ;
                }
                $file_id = $recordSet->fields('usersfiles_id');
                // gotta grab the image size
                $ext = substr(strrchr($file_name, '.'), 1);
                //Lookup Icon
                $iconpath = $config['file_icons_path'] . '/' . $ext . '.png';
                if (file_exists($iconpath)) {
                    $icon = $config['listings_view_file_icons_path'] . '/' . $ext . '.png';
                } else {
                    $icon = $config['listings_view_file_icons_path'] . '/default.png';
                }
                $new_html = str_replace('{file_caption}', htmlentities($caption, ENT_COMPAT, $config['charset']), $new_html);
                $new_html = str_replace('{file_id}', $file_id, $new_html);
                $new_html = str_replace('{icon_src}', $icon, $new_html);
                $new_html = str_replace('{file_name}', $file_name, $new_html);
                // gotta grab the image size
                //echo '<br />'."$config[file_upload_path]/$thumb_file_name".'<br />';

                $recordSet->MoveNext();
            } // end while
            $page->replace_template_section('file_block', $new_html);
            $avaliable_images = $config['max_listings_file_uploads'] - $num_images;
            if ($avaliable_images > 0) {
                $page->page = $page->cleanup_template_block('file_upload', $page->page);
            } else {
                $page->page = $page->remove_template_block('file_upload', $page->page);
            }
            //End Listing Images
            //Finish Loading Template
            $page->replace_tag('application_status_text', $status_text);
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        } else {
            return 'Permission Denied';
        }
    }

    public function render_files_select($ID, $type)
    {
        // shows the files connected to a given image
        global $conn, $lang,  $misc, $config;

        $folderid = intval($ID);
        $ID = intval($ID);
        if ($type == 'listing') {
            $file_upload_path = $config['listings_file_upload_path'];
            $file_view_path = $config['listings_view_file_path'];
            $sqltype = 'listings';
        } else {
            $file_upload_path = $config['users_file_upload_path'];
            $file_view_path = $config['users_view_file_path'];
            $sqltype = 'user';
        }
        //Declare an empty display variable to hold all output from function.
        $display = '';
        $optionvalue = '';
        $sql = 'SELECT ' . $type . 'sfiles_id, ' . $type . 'sfiles_caption, ' . $type . 'sfiles_description, ' . $type . 'sfiles_file_name 
				FROM ' . $config['table_prefix'] . '' . $type . 'sfiles 
				WHERE (' . $sqltype . "db_id = $ID) 
				ORDER BY " . $type . 'sfiles_rank';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $num_files = $recordSet->RecordCount();
        if ($num_files >= 1) {
            //ENTER OPENING FORM TAG, ACTION AND HIDDEN FORM FIELDS
            $display .= '<form action="index.php?action=create_download" method="POST">';
            $display .= '<input type="hidden" name="ID" value="' . $folderid . '" />';
            $display .= '<input type="hidden" name="type" value="' . $type . '" />';
            $display .= '<select name="file_id">';
            while (!$recordSet->EOF) {
                $optionvalue = '';
                $file_caption = $recordSet->fields($type . 'sfiles_caption');
                $file_filename = $recordSet->fields($type . 'sfiles_file_name');
                $file_id = $recordSet->fields($type . 'sfiles_id');
                $file_url = $file_view_path . '/' . $folderid . '/' . $file_filename;
                $file_download_url = 'index.php?action=create_download&amp;ID=' . $folderid . '&amp;file_id=' . $file_id . '&amp;type=' . $type;
                $file_description = urldecode($recordSet->fields($type . 'sfiles_description'));
                $file_icon_height = $config['file_icon_height'];
                $file_icon_width = $config['file_icon_width'];
                if ($file_filename != '' && file_exists("$file_upload_path/$folderid/$file_filename")) {
                    $ext = substr(strrchr($file_filename, '.'), 1);
                    $filesize = filesize($file_upload_path . '/' . $folderid . '/' . $file_filename);
                    if ($file_caption != '') {
                        $alt = $file_caption;
                    }
                    $iconpath = $config['file_icons_path'] . '/' . $ext . '.png';
                    if (file_exists($iconpath)) {
                        $file_icon = $config['listings_view_file_icons_path'] . '/' . $ext . '.png';
                    } else {
                        $file_icon = $config['listings_view_file_icons_path'] . '/default.png';
                    }
                    $file_filesize = $this->bytesize($filesize);
                }
                if ($config['file_display_option'] == 'filename') {
                    $optionvalue .= $file_filename;
                    if ($config['file_display_size'] == '1') {
                        $optionvalue .= ' - ' . $this->bytesize($filesize);
                    }
                } elseif ($config['file_display_option'] == 'caption') {
                    if ($file_caption != '') {
                        $optionvalue .= $file_caption;
                    } else {
                        $optionvalue .= $file_filename;
                    }
                    if ($config['file_display_size'] == '1') {
                        $optionvalue .= ' - ' . $this->bytesize($filesize);
                    }
                } elseif ($config['file_display_option'] == 'both') {
                    if ($file_caption != '') {
                        $optionvalue .= $file_caption . ' - ';
                    }
                    $optionvalue .= $file_filename;
                    if ($config['file_display_size'] == '1') {
                        $optionvalue .= ' - ' . $this->bytesize($filesize);
                    }
                }
                //ENTER SINGLE FORM OPTION HERE
                $display .= '<option value="' . $file_id . '">' . $optionvalue . '</option>';
                $recordSet->MoveNext();
            }
            //END while (!$recordSet->EOF)
            //ENTER SUBMIT BUTTONS AND CLOSING FORM TAG HERE
            $display .= '</select>';
            $display .= '<input type="button" value="' . $lang['download_file'] . '" onclick="submit();" />';
            $display .= '</form>';
        }
        return $display;
    } // end function renderListingsfiles

    public function bytesize($bytes)
    {
        $size = $bytes / 1024;
        if ($size < 1024) {
            $size = number_format($size, 2);
            $size .= ' KB';
        } else {
            if ($size / 1024 < 1024) {
                $size = number_format($size / 1024, 2);
                $size .= ' MB';
            } elseif ($size / 1024 / 1024 < 1024) {
                $size = number_format($size / 1024 / 1024, 2);
                $size .= ' GB';
            }
        }
        return $size;
    }

    public function render_templated_files($ID, $type, $template)
    {
        global $conn, $lang, $misc, $config;

        //Load the Core Template class and the Misc Class
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $folderid = $ID;
        $ID = $misc->make_db_extra_safe($ID);
        //Declare an empty display variable to hold all output from function.
        $display = '';
        if ($type == 'listing') {
            $file_upload_path = $config['listings_file_upload_path'];
            $file_view_path = $config['listings_view_file_path'];
            $sqltype = 'listings';
        } else {
            $file_upload_path = $config['users_file_upload_path'];
            $file_view_path = $config['users_view_file_path'];
            $sqltype = 'user';
        }
        $sql = 'SELECT ' . $type . 'sfiles_id, ' . $type . 'sfiles_caption, ' . $type . 'sfiles_description, ' . $type . 'sfiles_file_name 
				FROM ' . $config['table_prefix'] . '' . $type . 'sfiles 
				WHERE (' . $sqltype . "db_id = $ID) 
				ORDER BY " . $type . 'sfiles_rank';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $num_files = $recordSet->RecordCount();
        if ($num_files >= 1) {
            //Load the File Template specified by the calling tag unless a template was specified in the calling template tag.
            $page->load_page($config['template_path'] . '/files_' . $type . '_' . $template . '.html');
            // Determine if the template uses rows.
            // First item in array is the row conent second item is the number of block per block row
            $file_template_row = $page->get_template_section_row('file_block_row');
            if (is_array($file_template_row)) {
                $row = $file_template_row[0];
                $col_count = $file_template_row[1];
                $uses_rows = true;
                $x = 1;
                //Create an empty array to hold the row contents
                $new_row_data = [];
            } else {
                $uses_rows = false;
            }
            $file_template_section = '';
            while (!$recordSet->EOF) {
                if ($uses_rows == true && $x > $col_count) {
                    //We are at then end of a row. Save the template section as a new row.
                    $new_row_data[] = $page->replace_template_section('file_block', $file_template_section, $row);
                    //$new_row_data[] = $file_template_section;
                    $file_template_section = $page->get_template_section('file_block');
                    $x = 1;
                } else {
                    $file_template_section .= $page->get_template_section('file_block');
                }
                $file_caption = $recordSet->fields($type . 'sfiles_caption');
                $file_filename = $recordSet->fields($type . 'sfiles_file_name');
                $file_id = $recordSet->fields($type . 'sfiles_id');
                $file_url = $file_view_path . '/' . $folderid . '/' . $file_filename;
                $file_download_url = 'index.php?action=create_download&amp;ID=' . $folderid . '&amp;file_id=' . $file_id . '&amp;type=' . $type;
                $file_description = urldecode($recordSet->fields($type . 'sfiles_description'));
                $file_icon_height = $config['file_icon_height'];
                $file_icon_width = $config['file_icon_width'];
                if ($file_filename != '' && file_exists("$file_upload_path/$folderid/$file_filename")) {
                    $ext = substr(strrchr($file_filename, '.'), 1);
                    $filesize = filesize($file_upload_path . '/' . $folderid . '/' . $file_filename);
                    if ($caption != '') {
                        $alt = $caption;
                    } else {
                        $alt = $thumb_file_name;
                    }
                    $iconpath = $config['file_icons_path'] . '/' . $ext . '.png';
                    if (file_exists($iconpath)) {
                        $file_icon = $config['listings_view_file_icons_path'] . '/' . $ext . '.png';
                    } else {
                        $file_icon = $config['listings_view_file_icons_path'] . '/default.png';
                    }
                    $file_filesize = $this->bytesize($filesize);
                }
                $file_template_section = $page->parse_template_section($file_template_section, 'file_url', $file_url);
                $file_template_section = $page->parse_template_section($file_template_section, 'file_download_url', $file_download_url);
                $file_template_section = $page->parse_template_section($file_template_section, 'file_filename', $file_filename);
                $file_template_section = $page->parse_template_section($file_template_section, 'file_caption', $file_caption);
                $file_template_section = $page->parse_template_section($file_template_section, 'file_description', $file_description);
                $file_template_section = $page->parse_template_section($file_template_section, 'file_icon', $file_icon);
                $file_template_section = $page->parse_template_section($file_template_section, 'file_icon_height', $file_icon_height);
                $file_template_section = $page->parse_template_section($file_template_section, 'file_icon_width', $file_icon_width);
                $file_template_section = $page->parse_template_section($file_template_section, 'file_filesize', $file_filesize);
                $recordSet->MoveNext();
                if ($uses_rows == true) {
                    $x++;
                }
            }
            //END while (!$recordSet->EOF)
            if ($uses_rows == true) {
                $file_template_section = $page->cleanup_template_block('file', $file_template_section);
                $new_row_data[] = $page->replace_template_section('file_block', $file_template_section, $row);
                $replace_row = '';
                foreach ($new_row_data as $rows) {
                    $replace_row .= $rows;
                }
                $page->replace_template_section_row('file_block_row', $replace_row);
            } else {
                $page->replace_template_section('file_block', $file_template_section);
            }
            $page->replace_permission_tags();
            $display .= $page->return_page();
        }
        return $display;
    } // End Render Templated Listing Files

    //Create Download Function to prevent direct links to files
    public function create_download($ID, $file_id, $type)
    {
        global $config, $misc, $conn;

        $folderid = intval($ID);
        $ID = intval($ID);
        $fileID = intval($file_id);

        if ($type == 'listing') {
            $file_upload_path = $config['listings_file_upload_path'];
            $file_view_path = $config['listings_view_file_path'];
            $sqltype = 'listings';
        } else {
            $file_upload_path = $config['users_file_upload_path'];
            $file_view_path = $config['users_view_file_path'];
            $sqltype = 'user';
        }
        $sql = 'SELECT DISTINCT ' . $type . 'sfiles_file_name 
				FROM ' . $config['table_prefix'] . '' . $type . 'sfiles 
				WHERE (' . $sqltype . 'db_id = ' . $ID . ') 
				AND (' . $type . 'sfiles_id = ' . $fileID . ') 
				ORDER BY ' . $type . 'sfiles_rank';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        while (!$recordSet->EOF) {
            $file_filename = $recordSet->fields($type . 'sfiles_file_name');
            $recordSet->MoveNext();
        }
        $fullPath = $file_upload_path . '/' . $folderid . '/' . $file_filename;

        if ($fd = fopen($fullPath, 'r')) {
            $fsize = filesize($fullPath);
            $path_parts = pathinfo($fullPath);
            header('Content-type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $path_parts['basename'] . '"');
            header("Content-length: $fsize");
            header('Cache-control: private'); //use this to open files directly
            while (!feof($fd)) {
                $buffer = fread($fd, 2048);
                echo $buffer;
                ob_flush();
                flush();
            }
        }
        fclose($fd);
        exit;
    } // end create_download
} // End FileHandler Class
