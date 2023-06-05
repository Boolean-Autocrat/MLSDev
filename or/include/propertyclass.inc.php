<?php
class propertyclass
{
    public function show_classes($status_text = '')
    {
        global $config;

        $display = '';
        // Verify User is an Admin
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_property_classes');

        if ($security === true) {
            global $conn, $misc, $lang;

            //Load the Core Template
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/pclass_editor.html');

            $display = '';

            $sql = 'SELECT * FROM ' . $config['table_prefix'] . 'class 
					ORDER BY class_rank';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $html_template = $page->get_template_section('pclass_block');
            $html = '';
            while (!$recordSet->EOF) {
                $html .= $html_template;
                $class_name = $recordSet->fields('class_name');
                $class_id = $recordSet->fields('class_id');
                $class_rank = $recordSet->fields('class_rank');
                $html = $page->replace_tag_safe('class_name', $class_name, $html);
                $html = $page->replace_tag_safe('class_id', $class_id, $html);
                $html =  $page->replace_tag_safe('class_rank', $class_rank, $html);
                //Replace Template Block
                $recordSet->MoveNext();
            }
            $page->replace_template_section('pclass_block', $html);
            $page->replace_tag('application_status_text', $status_text);
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);

            return $page->return_page();
        }
        return $display;
    }

    public function insert_property_class()
    {
        global  $config;
        // Verify User is an Admin
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_property_classes');
        $display = '';
        if ($security === true) {
            global $conn, $misc, $lang, $jscript;

            //Load the Core Template
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/pclass_insert.html');

            // Get Max rank
            $sql = 'SELECT max(class_rank) as max_rank 
					FROM ' . $config['table_prefix'] . 'class';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $rank = $recordSet->fields('max_rank');
            $rank++;

            if (isset($_POST['class_name']) && $_POST['class_name']) {
                if (!isset($_POST['token']) || !$misc->validate_csrf_token($_POST['token'])) {
                    $display .= $lang['invalid_csrf_token'] . '<br />';
                } else {
                    $class_name = $misc->make_db_safe($_POST['class_name']);

                    $class_rank = abs(intval($_POST['class_rank']));
                    if ($class_rank == 0) {
                        $class_rank = $rank;
                    }

                    $sql = 'INSERT INTO ' . $config['table_prefix'] . 'class (class_name,class_rank) 
						VALUES (' . $class_name . ',' . $class_rank . ')';
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    } else {
                        $new_class_id = $conn->Insert_ID();
                    }
                    if (isset($_POST['field_id']) && $_POST['field_id'] != '') {
                        foreach ($_POST['field_id'] as $field_id) {
                            $sql = 'INSERT INTO ' . $config['table_prefix_no_lang'] . 'classformelements (class_id,listingsformelements_id) 
								VALUES (' . $new_class_id . ',' . $field_id . ')';
                            $recordSet2 = $conn->Execute($sql);
                            if (!$recordSet2) {
                                $misc->log_error($sql);
                            }
                        }
                    }
                    $display .= $lang['property_class_updated'] . '<br />';
                }
                $display .= $this->show_classes();
            } else {
                $sql = 'SELECT listingsformelements_id, listingsformelements_field_caption 
						FROM ' . $config['table_prefix'] . 'listingsformelements 
						ORDER BY listingsformelements_field_caption';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }

                $html_template = $page->get_template_section('formelement_block');
                $html = '';
                while (!$recordSet->EOF) {
                    $html .= $html_template;
                    $field_id = $recordSet->fields('listingsformelements_id');
                    $field_caption = $recordSet->fields('listingsformelements_field_caption');
                    $html = $page->replace_tag_safe('field_id', $field_id, $html);
                    $html = $page->replace_tag_safe('field_caption', $field_caption, $html);

                    $recordSet->MoveNext();
                }
                $page->replace_template_section('formelement_block', $html);
                $page->replace_tag_safe('rank', $rank);
                $page->replace_tag('application_status_text', '');
                $page->replace_lang_template_tags(true);
                $page->replace_permission_tags();
                $page->auto_replace_tags('', true);
                return $page->return_page();
            }
        }
        return $display;
    }

    public function delete_property_class()
    {
        global $config;

        // Verify User is an Admin
        $display = '';
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_property_classes');

        if ($security === true) {
            global $conn, $lang, $misc;

            //Load the Core Template
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/pclass_editor.html');

            if (isset($_GET['id'])) {
                $class_id = intval($_GET['id']);
                // Now remove any fields associated with the class that are no longer associtaed with any other classes
                // First we have to determine which form elements belong to other classes.
                $sql = 'SELECT DISTINCT (listingsformelements_id) 
						FROM ' . $config['table_prefix_no_lang'] . "classformelements 
						WHERE class_id <> $class_id";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $other_class_id = '';
                while (!$recordSet->EOF) {
                    if ($other_class_id == '') {
                        $other_class_id .= $recordSet->fields('listingsformelements_id');
                    } else {
                        $other_class_id .= ',' . $recordSet->fields('listingsformelements_id');
                    }
                    $recordSet->MoveNext();
                }
                if ($other_class_id == '') {
                    $other_class_id = '0';
                }
                // Ok now grab a list of the id's to delete them from the listingformelements table.
                // Also delete them from the lass_form_elements.
                $sql = 'SELECT DISTINCT (listingsformelements_id) 
						FROM ' . $config['table_prefix_no_lang'] . "classformelements 
						WHERE class_id = $class_id 
						AND listingsformelements_id NOT IN ($other_class_id)";

                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $ids = '';
                while (!$recordSet->EOF) {
                    if ($ids == '') {
                        $ids .= $recordSet->fields('listingsformelements_id');
                    } else {
                        $ids .= ',' . $recordSet->fields('listingsformelements_id');
                    }
                    $recordSet->MoveNext();
                }
                if ($ids == '') {
                    $ids = '0';
                }
                $sql = 'DELETE FROM  ' . $config['table_prefix_no_lang'] . "classformelements 
						WHERE listingsformelements_id  IN ($ids)";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $sql = 'SELECT listingsformelements_field_name 
						FROM ' . $config['table_prefix'] . "listingsformelements 
						WHERE listingsformelements_id  IN ($ids)";
                $recordSet1 = $conn->Execute($sql);
                if (!$recordSet1) {
                    $misc->log_error($sql);
                }
                while (!$recordSet1->EOF) {
                    $field_name = $misc->make_db_safe($recordSet1->fields('listingsformelements_field_name'));
                    // Delete All Translationf for this field.
                    $configured_langs = explode(',', $config['configured_langs']);
                    while (!$recordSet->EOF) {
                        $listingsformelements_id = $recordSet->fields('listingsformelements_id');
                        foreach ($configured_langs as $configured_lang) {
                            $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . $configured_lang . "_listingsformelements 
									WHERE listingsformelements_id IN ($ids)";
                            $recordSet = $conn->Execute($sql);
                            if (!$recordSet) {
                                $misc->log_error($sql);
                            }
                        }
                    }
                    // Cleanup any listingdbelemts entries from this field.
                    $sql = 'SELECT listingsdbelements_id 
							FROM ' . $config['table_prefix'] . "listingsdbelements 
							WHERE listingsdbelements_field_name = $field_name";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    while (!$recordSet->EOF) {
                        $listingsdbelements_id = $recordSet->fields('listingsdbelements_id');
                        foreach ($configured_langs as $configured_lang) {
                            $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . $configured_lang . "_listingsdbelements 
									WHERE listingsdbelements_id = $listingsdbelements_id";
                            $recordSet = $conn->Execute($sql);
                            if (!$recordSet) {
                                $misc->log_error($sql);
                            }
                        }
                    }
                    $recordSet1->MoveNext();
                }

                $sql = 'SELECT listingsdb_id 
						FROM ' . $config['table_prefix'] . "listingsdb 
						WHERE listingsdb_pclass_id = $class_id";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $ids = '';
                while (!$recordSet->EOF) {
                    if ($ids == '') {
                        $ids .= $recordSet->fields('listingsdb_id');
                    } else {
                        $ids .= ',' . $recordSet->fields('listingsdb_id');
                    }
                    $recordSet->MoveNext();
                }
                if ($ids == '') {
                    $ids = '0';
                }
                // now that we have the listingids delete the listings and any associated listingsdbelements
                $configured_langs = explode(',', $config['configured_langs']);
                $listingsformelements_id = $recordSet->fields('listingsformelements_id');
                foreach ($configured_langs as $configured_lang) {
                    $sql = 'DELETE FROM  ' . $config['table_prefix_no_lang'] . $configured_lang . "_listingsdb 
							WHERE listingsdb_id  IN ($ids)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $sql = 'DELETE FROM  ' . $config['table_prefix_no_lang'] . $configured_lang . "_listingsdbelements 
							WHERE listingsdb_id  IN ($ids)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                }
                // Get all images and vtours and delete the images.
                // listingsvtours_id, userdb_id, listingsvtours_caption, listingsvtours_file_name, listingsvtours_thumb_file_name, listingsvtours_description, listingsdb_id, listingsvtours_rank, listingsvtours_active
                $sql = 'SELECT  listingsvtours_thumb_file_name, listingsvtours_file_name 
						FROM  ' . $config['table_prefix'] . "listingsvtours 
						WHERE listingsdb_id  IN ($ids)";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                while (!$recordSet->EOF) {
                    $file_name = $recordSet->fields('listingsvtours_file_name');
                    $thumb_name = $recordSet->fields('listingsvtours_thumb_file_name');
                    @unlink("$config[vtour_upload_path]/$file_name");
                    @unlink("$config[vtour_upload_path]/$file_name");
                    $recordSet->MoveNext();
                }
                // listingsimages_id, userdb_id, listingsimages_caption, listingsimages_file_name, listingsimages_thumb_file_name, listingsimages_description, listingsdb_id, listingsimages_rank, listingsimages_active
                $sql = 'SELECT  listingsimages_thumb_file_name, listingsimages_file_name 
						FROM  ' . $config['table_prefix'] . "listingsimages 
						WHERE listingsdb_id  IN ($ids)";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                while (!$recordSet->EOF) {
                    $file_name = $recordSet->fields('listingsimages_file_name');
                    $thumb_name = $recordSet->fields('listingsimages_thumb_file_name');
                    @unlink("$config[listings_upload_path]/$file_name");
                    @unlink("$config[listings_upload_path]/$file_name");
                    $recordSet->MoveNext();
                }
                // Now delete DB records of the images and vtours for all langs.
                foreach ($configured_langs as $configured_lang) {
                    $sql = 'DELETE FROM  ' . $config['table_prefix_no_lang'] . $configured_lang . "_listingsimages 
							WHERE listingsdb_id  IN ($ids)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $sql = 'DELETE FROM  ' . $config['table_prefix_no_lang'] . $configured_lang . "_listingsvtours 
							WHERE listingsdb_id  IN ($ids)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                }
                // Now we jsut need to delete all associates from the classformelements and class tables.
                $sql = 'DELETE FROM  ' . $config['table_prefix_no_lang'] . "classformelements 
						WHERE class_id = $class_id";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $configured_langs = explode(',', $config['configured_langs']);
                $listingsformelements_id = $recordSet->fields('listingsformelements_id');
                foreach ($configured_langs as $configured_lang) {
                    $sql = 'DELETE FROM  ' . $config['table_prefix_no_lang'] . $configured_lang . "_class 
							WHERE class_id = $class_id";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                }
                $display = $this->show_classes($lang['property_class_deleted']);
            }
        } else {
            $display = 'Permission Denied - Delete Pclass';
        }
        return $display;
    }

    public function ajax_save_class_rank()
    {
        global $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_property_classes');

        if ($security === true) {
            global $conn, $misc;
            if (isset($_POST['search_setup']) && isset($_POST['class_id'])) {
                $rank_field = 'class_rank';
                $class_rank = 0;

                foreach ($_POST['class_id'] as $class_id) {
                    //empty locations are skipped
                    if (!empty($class_id)) {
                        $class_rank = $class_rank + 1;

                        $sql = 'UPDATE ' . $config['table_prefix'] . 'class
						SET  ' . $rank_field . " = '" . $class_rank . "'
						WHERE class_id = '" . $class_id . "'";
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                    }
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'status_msg' => $lang['admin_template_editor_field_order_set']]);
            }
        }
        header('Content-type: application/json');
        return json_encode(['error' => '1', 'error_msg' => $lang['access_denied']]);
    }

    public function ajax_modify_property_class()
    {
        global  $config;

        // Verify User is an Admin
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_property_classes');
        $display = '';
        if ($security === true) {
            //Load the Core Template
            global $conn, $misc, $lang, $jscript;

            if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
            }

            if (isset($_POST['class_id'])) {
                // Get Max rank
                $sql = 'SELECT max(class_rank) as max_rank 
						FROM ' . $config['table_prefix'] . 'class';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $rank = $recordSet->fields('max_rank');
                $rank++;

                $class_id = intval($_POST['class_id']);
                $class_name = $misc->make_db_safe($_POST['class_name']);
                $class_rank = abs(intval($_POST['class_rank']));
                if ($class_rank == 0) {
                    $class_rank = $rank;
                }
                $sql = 'UPDATE ' . $config['table_prefix'] . 'class 
						SET class_name = ' . $class_name . ',class_rank = ' . $class_rank . ' 
						WHERE class_id = ' . $class_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'statustext' => $lang['property_class_updated']]);
            }
        }
        header('Content-type: application/json');
        return json_encode(['error' => '1', 'error_msg' => $lang['blog_permission_denied']]);
    }
}
