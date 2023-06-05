<?php
class template_editor
{
    /*********************************/
    /* EDIT AGENT AND MEMBER FIELDS */
    /*******************************/

    public function edit_user_template($type)
    {
        global $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        $page->load_page($config['admin_template_path'] . '/user_template_editor.html');
        if ($type == 'member') {
            $security = $login->verify_priv('edit_member_template');
        } elseif ($type == 'agent') {
            $security = $login->verify_priv('edit_agent_template');
        } else {
            return ('Invalid user type');
        }

        $display = '';

        if ($security === true) {
            global $conn, $misc, $jscript, $lang;

            $display1 = $this->delete_user_field($type);
            $display .= $display1;

            //Get Fileds for

            // Grab the list of fields set to be on the search results page sorted by search_result_rank
            $sql = 'SELECT ' . $type . 'formelements_id, ' . $type . 'formelements_field_name,
						' . $type . 'formelements_required, ' . $type . 'formelements_field_caption, 
						' . $type . 'formelements_rank
						FROM ' . $config['table_prefix'] . $type . 'formelements
						ORDER BY ' . $type . 'formelements_rank;';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $all_fields = [];
            $html_results = '';
            $html = $page->get_template_section('user_field_block');
            while (!$recordSet->EOF) {
                $new_field_block = $html;
                $all_fields[$recordSet->fields($type . 'formelements_field_name')] = $recordSet->fields($type . 'formelements_field_caption') . ' (' . $recordSet->fields($type . 'formelements_field_name') . ')';
                $fid = $recordSet->fields($type . 'formelements_id');
                $f_rank = $recordSet->fields($type . 'formelements_rank');
                // Get Caption from users selected language
                if (!isset($_SESSION['users_lang'])) {
                    $caption = $recordSet->fields($type . 'formelements_field_caption');
                } else {
                    $field_id = intval($fid);
                    $sql2 = 'SELECT ' . $type . 'formelements_field_caption
								FROM ' . $config['lang_table_prefix'] . $type . "formelements
								WHERE $type.'formelements_id = $field_id";
                    $recordSet2 = $conn->Execute($sql2);
                    if (!$recordSet2) {
                        $misc->log_error($sql2);
                    }
                    $caption = $recordSet2->fields($type . 'formelements_field_caption');
                }

                if ($recordSet->fields($type . 'formelements_required') == 'Yes') {
                    $new_field_block = $page->cleanup_template_block('required', $new_field_block);
                } else {
                    $new_field_block = $page->remove_template_block('required', $new_field_block);
                }

                $field_name = $recordSet->fields($type . 'formelements_field_name');
                $new_field_block = $page->replace_tag_safe('field_rank', $f_rank, $new_field_block);
                $new_field_block = $page->replace_tag_safe('field_id', $fid, $new_field_block);
                $new_field_block = $page->replace_tag_safe('field_name', $field_name, $new_field_block);
                $new_field_block = $page->replace_tag_safe('field_caption', $caption, $new_field_block);
                $html_results .= $new_field_block;
                $recordSet->MoveNext();
            }

            $page->replace_template_section('user_field_block', $html_results);
            $selected_field = '';
            if (isset($_GET['edit_field'])) {
                $selected_field = $_GET['edit_field'];
            }
            $html = $page->get_template_section('user_template_editor_field_edit_block');
            $html = $page->form_options($all_fields, $selected_field, $html);
            $page->replace_template_section('user_template_editor_field_edit_block', $html);

            $page->replace_tag_safe('user_type', $type);



            $page->replace_tag('application_status_text', '');
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        } else {
            $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return  $display;
    }


    public function ajax_add_user_field($type)
    {
        global $config, $lang;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        if ($type == 'member') {
            $security = $login->verify_priv('edit_member_template');
        } elseif ($type == 'agent') {
            $security = $login->verify_priv('edit_agent_template');
        } else {
            return ('Invalid user type');
        }

        if ($security) {
            $display = $this->add_user_template_field($type);
        } else {
            $display = '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return $display;
    }

    public function add_user_template_field($type)
    {
        global $config, $lang, $jscript;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        $page->load_page($config['admin_template_path'] . '/user_template_add_field.html');

        if ($type == 'member') {
            $security = $login->verify_priv('edit_member_template');
        } else {
            $security = $login->verify_priv('edit_agent_template');
        }

        if ($security === true) {
            $page->replace_tag_safe('user_type', $type);
            $page->replace_tag('application_status_text', '');
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        }
        return '<div class="error_text">' . $lang['access_denied'] . '</div>';
    }

    public function ajax_insert_user_field()
    {
        global $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = false;
        if (isset($_POST['user_type'])) {
            $type = $_POST['user_type'];

            if ($type == 'member') {
                $security = $login->verify_priv('edit_member_template');
            } else {
                $security = $login->verify_priv('edit_agent_template');
            }
        }
        if ($security === true) {
            global $conn, $misc;
            if (isset($_POST['edit_field']) && !isset($_POST['lang_change'])) {
                $field_type = $misc->make_db_safe($_POST['field_type']);
                $_POST['edit_field'] = str_replace(' ', '_', $_POST['edit_field']);
                $field_name = $misc->make_db_safe($_POST['edit_field']);
                $field_caption = $misc->make_db_safe($_POST['field_caption']);
                $default_text = $misc->make_db_safe($_POST['default_text']);
                $field_elements = $misc->make_db_safe($_POST['field_elements']);
                $rank = intval($_POST['rank']);
                $required = $misc->make_db_safe($_POST['required']);
                $tool_tip = $misc->make_db_safe($_POST['tool_tip']);
                if ($type == 'member') {
                    $sql = 'INSERT INTO ' . $config['table_prefix'] . $type . 'formelements
							(' . $type . 'formelements_field_type, ' . $type . 'formelements_field_name, ' . $type . 'formelements_field_caption, ' . $type . 'formelements_default_text, ' . $type . 'formelements_field_elements, ' . $type . 'formelements_rank, ' . $type . 'formelements_required, ' . $type . "formelements_tool_tip)
							VALUES ($field_type,$field_name,$field_caption,$default_text,$field_elements,$rank,$required,$tool_tip)";
                } else {
                    $sql = 'INSERT INTO ' . $config['table_prefix'] . $type . 'formelements
							(' . $type . 'formelements_field_type, ' . $type . 'formelements_field_name, ' . $type . 'formelements_field_caption, ' . $type . 'formelements_default_text, ' . $type . 'formelements_field_elements, ' . $type . 'formelements_rank, ' . $type . 'formelements_required,' . $type . 'formelements_display_priv, ' . $type . "formelements_tool_tip)
							VALUES ($field_type,$field_name,$field_caption,$default_text,$field_elements,$rank,$required,0,$tool_tip)";
                }
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'field_name' => $field_name]);
            }
        }
        header('Content-type: application/json');
        return json_encode(['error' => '1', 'error_msg' =>  $lang['access_denied']]);
    }

    public function edit_user_field($listing_field_name, $type)
    {
        global $conn, $config, $lang, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        $security = false;
        if ($type == 'member') {
            $security = $login->verify_priv('edit_member_template');
        } else {
            $security = $login->verify_priv('edit_agent_template');
        }

        if ($security === true) {
            $page->load_page($config['admin_template_path'] . '/user_template_edit_field.html');

            $edit_listing_field_name = $misc->make_db_safe($listing_field_name);
            $sql = 'SELECT * FROM ' . $config['table_prefix'] . $type . 'formelements
					WHERE ' . $type . 'formelements_field_name = ' . $edit_listing_field_name;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $id = $recordSet->fields($type . 'formelements_id');
            $field_type = $recordSet->fields($type . 'formelements_field_type');
            $field_name = $recordSet->fields($type . 'formelements_field_name');
            $field_caption = $recordSet->fields($type . 'formelements_field_caption');
            $default_text = $recordSet->fields($type . 'formelements_default_text');
            $field_elements = $recordSet->fields($type . 'formelements_field_elements');
            $rank = $recordSet->fields($type . 'formelements_rank');
            $required = $recordSet->fields($type . 'formelements_required');
            $tool_tip = $recordSet->fields($type . 'formelements_tool_tip');

            if ($type == 'agent') {
                $display_priv = $recordSet->fields('agentformelements_display_priv');
                $page->replace_tag_safe('display_priv', $display_priv);
                $page->page = $page->cleanup_template_block('display_priv', $page->page);
            } else {
                $page->page = $page->remove_template_block('display_priv', $page->page);
            }

            $page->replace_tag_safe('field_id', $id);
            $page->replace_tag_safe('user_type', $type);
            $page->replace_tag_safe('field_name', $field_name);
            $page->replace_tag_safe('field_caption', $field_caption);
            $page->replace_tag_safe('field_type', $field_type);
            $page->replace_tag_safe('required', $required);
            $page->replace_tag_safe('required_lower', strtolower($required));
            $page->replace_tag_safe('field_elements', $field_elements);
            $page->replace_tag_safe('default_text', $default_text);

            $page->replace_tag_safe('tool_tip', $tool_tip);
            $page->replace_tag_safe('rank', $rank);


            $page->replace_tag('application_status_text', '');
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        } else {
            return '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
    }

    public function ajax_update_user_field()
    {
        global $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        if (isset($_POST['user_type'])) {
            $type = $_POST['user_type'];
        }
        if ($type == 'member') {
            $security = $login->verify_priv('edit_member_template');
        } else {
            $security = $login->verify_priv('edit_agent_template');
        }
        if ($security === true) {
            global $conn, $misc;
            if (isset($_POST['update_id']) && !isset($_POST['lang_change'])) {
                $id = $_POST['update_id'];
                $field_type = $misc->make_db_safe($_POST['field_type']);
                $_POST['edit_field'] = str_replace(' ', '_', $_POST['edit_field']);
                $_POST['old_field_name'] = str_replace(' ', '_', $_POST['old_field_name']);
                $field_name = $misc->make_db_safe($_POST['edit_field']);
                $old_field_name = $misc->make_db_safe($_POST['old_field_name']);
                //See if we are updating field name
                $update_field_name = false;
                if ($old_field_name != $field_name) {
                    $update_field_name = true;
                }
                $field_caption = $misc->make_db_safe($_POST['field_caption']);
                $default_text = $misc->make_db_safe($_POST['default_text']);
                $field_elements = $misc->make_db_safe($_POST['field_elements']);
                $rank = intval($_POST['rank']);
                $required = $misc->make_db_safe($_POST['required']);
                $tool_tip = $misc->make_db_safe($_POST['tool_tip']);
                if ($type == 'agent') {
                    $display_priv = $misc->make_db_safe($_POST['display_priv']);
                    $sql = 'UPDATE ' . $config['table_prefix'] . $type . 'formelements
							SET ' . $type . 'formelements_field_type = ' . $field_type . ', ' . $type . 'formelements_field_name = ' . $field_name . ', ' . $type . 'formelements_field_caption = ' . $field_caption . ', ' . $type . 'formelements_default_text = ' . $default_text . ', ' . $type . 'formelements_field_elements = ' . $field_elements . ', ' . $type . 'formelements_rank = ' . $rank . ', ' . $type . 'formelements_required = ' . $required . ', ' . $type . 'formelements_display_priv  = ' . $display_priv . ', ' . $type . 'formelements_tool_tip  = ' . $tool_tip . '
							WHERE ' . $type . 'formelements_id = ' . $id;
                } else {
                    $sql = 'UPDATE ' . $config['table_prefix'] . $type . 'formelements
							SET ' . $type . 'formelements_field_type = ' . $field_type . ', ' . $type . 'formelements_field_name = ' . $field_name . ', ' . $type . 'formelements_field_caption = ' . $field_caption . ', ' . $type . 'formelements_default_text = ' . $default_text . ', ' . $type . 'formelements_field_elements = ' . $field_elements . ', ' . $type . 'formelements_rank = ' . $rank . ', ' . $type . 'formelements_required = ' . $required . ', ' . $type . 'formelements_tool_tip  = ' . $tool_tip . '
							WHERE ' . $type . 'formelements_id = ' . $id;
                }
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                if ($update_field_name) {
                    $lang_sql = 'UPDATE  ' . $config['table_prefix'] . "userdbelements 
								SET userdbelements_field_name = $field_name
								WHERE userdbelements_field_name = $old_field_name";
                    $lang_recordSet = $conn->Execute($lang_sql);
                    if (!$lang_recordSet) {
                        $misc->log_error($lang_sql);
                    }
                }
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }

                header('Content-type: application/json');
                return json_encode(['error' => '0', 'field_id' =>  $id]);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' =>  $lang['access_denied']]);
        }
    }

    public function ajax_get_user_field_info($type)
    {
        global $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        if ($type == 'member') {
            $security = $login->verify_priv('edit_member_template');
        } elseif ($type == 'agent') {
            $security = $login->verify_priv('edit_agent_template');
        } else {
            return ('Invalid user type');
        }

        $display = '';

        if ($security === true) {
            if (isset($_GET['edit_field'])) {
                $display .= $this->edit_user_field($_GET['edit_field'], $type);
            }
        } else {
            $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return $display;
    }

    public function ajax_save_user_rank()
    {
        global $conn, $misc, $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        if (isset($_POST['user_type'])) {
            if ($_POST['user_type'] == 'agent') {
                $rank_field = 'agentformelements_rank';
            } elseif ($_POST['user_type'] == 'member') {
                $rank_field = 'memberformelements_rank';
            }
            $type = $_POST['user_type'];
        }
        $sec_type = 'edit_' . $type . '_template';

        //security check
        $security = $login->verify_priv($sec_type);
        if ($security === true && isset($_POST['field_name'])) {
            if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
            }


            $rank = 0;
            $num_of_fields = count($_POST['field_name']);
            foreach ($_POST['field_name'] as $field_name) {
                //empty locations are skipped
                if (!empty($field_name)) {
                    $rank = $rank + 1;

                    $sql = 'UPDATE ' . $config['table_prefix'] . $type . 'formelements
					SET  ' . $rank_field . " = '" . $rank . "'
					WHERE " . $type . "formelements_field_name = '" . $field_name . "'";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                }
            }
            header('Content-type: application/json');
            return json_encode(['error' => false, 'status_msg' =>  $lang['admin_template_editor_field_order_set']]);
        }
        header('Content-type: application/json');
        return json_encode(['error' => true, 'error_msg' =>  $lang['access_denied']]);
    }

    public function delete_user_field($type)
    {
        global $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        if ($type == 'member') {
            $security = $login->verify_priv('edit_member_template');
        } else {
            $security = $login->verify_priv('edit_agent_template');
        }
        if ($security) {
            global $conn, $misc;
            if (isset($_GET['delete_field']) && !isset($_POST['lang_change'])) {
                $field_name = $misc->make_db_safe($_GET['delete_field']);
                $sql = 'DELETE FROM ' . $config['table_prefix'] . $type . 'formelements
						WHERE ' . $type . 'formelements_field_name = ' . $field_name;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
            }
        } else {
            return '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
    }

    /************************/
    /* EDIT LISTING FIELDS */
    /**********************/

    public function edit_listing_template()
    {
        global $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_listing_template');

        //Load the Core Template
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        $page->load_page($config['admin_template_path'] . '/listing_template_editor.html');
        $display = '';
        $display1 = '';
        $quick_edit = '';

        if ($security) {
            global $conn, $lang, $jscript, $misc;
            $display1 .= $this->delete_listing_field();
            //$display1 .= $this->save_search_setup();
            $display .= $display1;

            //Replace NavBar Field
            $sql = 'SELECT listingsformelements_field_name, listingsformelements_field_caption
            FROM ' . $config['table_prefix'] . 'listingsformelements';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $all_fields = [];

            while (!$recordSet->EOF) {
                $all_fields[$recordSet->fields('listingsformelements_field_name')] = $recordSet->fields('listingsformelements_field_caption') . ' (' . $recordSet->fields('listingsformelements_field_name') . ')';
                $recordSet->MoveNext();
            }
            $selected_field = '';
            if (isset($_GET['edit_field'])) {
                $selected_field = $_GET['edit_field'];
            }
            $page->replace_tag('content', $display);

            $html = $page->get_template_section('listing_template_editor_field_edit_block');
            $html = $page->form_options($all_fields, $selected_field, $html);
            $page->replace_template_section('listing_template_editor_field_edit_block', $html);




            $page->replace_tag('application_status_text', '');
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        } else {
            return '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return $display;
    }

    public function show_quick_field_edit()
    {
        global $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_listing_template');
        if ($security === true) {
            global $conn, $misc;
            $display = '';

            $sqlDL = 'SELECT controlpanel_template_listing_sections
					FROM ' . $config['table_prefix_no_lang'] . 'controlpanel';
            $recordSetDL = $conn->Execute($sqlDL);
            if (!$recordSetDL) {
                $misc->log_error($sql);
            }

            $dla = explode(',', $recordSetDL->fields['controlpanel_template_listing_sections']);
            $dla[] = '';
            foreach ($dla as $key => $display_loc) {
                $display_loc = trim($display_loc);
                // Grab a list of field_names in the Database to Edit
                $sql = 'SELECT listingsformelements_id, listingsformelements_field_name,
						listingsformelements_required, listingsformelements_searchable,
						listingsformelements_field_caption, listingsformelements_rank
						FROM ' . $config['table_prefix'] . "listingsformelements
						WHERE listingsformelements_location = '" . $display_loc . "'
						ORDER BY listingsformelements_rank";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }

                // if the display location was added after "bottom_right", it is custom
                // definitely not the best way to handle this
                if ($display_loc == '') {
                    $display_loc_class = 'center_box';
                    $display .= '<div class="' . $display_loc_class . '">' . $display_loc . BR;
                } else {
                    if (!in_array($display_loc, ['headline', 'top_left', 'top_right', 'center', 'feature1', 'feature2', 'bottom_left', 'bottom_right'])) {
                        $display_loc_class = 'center_box ' . $display_loc;
                    } else {
                        $display_loc_class = $display_loc;
                    }
                    if (strpos($display_loc, 'top_') == false) {
                        $eclass = 'equal_top';
                    } elseif (strpos($display_loc, 'bottom_') == false) {
                        $eclass = 'equal_bottom';
                    } else {
                        $eclass = '';
                    }

                    $display .= '<div class="' . $display_loc_class . '">' . $display_loc . BR;
                }

                $display .= '	<ul id="' . $display_loc . '" class="qed_list display_location" title="' . $display_loc . '">' . BR;

                while (!$recordSet->EOF) {
                    $fid = $recordSet->fields('listingsformelements_id');
                    $f_rank = $recordSet->fields('listingsformelements_rank');

                    // Get Caption from users selected language
                    if (!isset($_SESSION['users_lang'])) {
                        $caption = $recordSet->fields('listingsformelements_field_caption');
                    } else {
                        $field_id = $misc->make_db_safe($fid);
                        $sql2 = 'SELECT listingsformelements_field_caption
								FROM ' . $config['lang_table_prefix'] . "listingsformelements
								WHERE listingsformelements_id = $field_id";
                        $recordSet2 = $conn->Execute($sql2);
                        if (!$recordSet2) {
                            $misc->log_error($sql2);
                        }
                        $caption = htmlspecialchars($recordSet2->fields('listingsformelements_field_caption'));
                    }

                    if ($recordSet->fields('listingsformelements_searchable') == 1) {
                        $searchable = '<span class="src_field">&nbsp;</span>';
                    } else {
                        $searchable = '<span class="fill">&nbsp;</span>';
                    }
                    if ($recordSet->fields('listingsformelements_required') == 'Yes') {
                        $required = '<span class="req_field">&nbsp;</span>';
                    } else {
                        $required = '<span class="fill">&nbsp;</span>';
                    }

                    $field_name = $recordSet->fields('listingsformelements_field_name');

                    $display .= '<li id="' . $field_name . '">' . BR;
                    $display .= '	<div class="block">' . BR;
                    $display .= '		<a href="" class="edit_field_link" id="rank_' . $f_rank . '" name="' . $field_name . '">' . $caption . '</a>' . $searchable . $required . BR;
                    $display .= '	</div>' . BR;
                    $display .= '</li>' . BR;
                    //<span class="sortable-number"></span>'
                    $recordSet->MoveNext();
                    $f_rank++;
                }

                $display .= '	</ul>' . BR;
                $display .= '</div>' . BR;

                if ($key  == 7) {
                    $display .= '<div class="clear"></div>';
                }
            } //end foreach

            return $display;
        } else {
            return '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
    }

    public function edit_listing_template_spo()
    {
        global $config, $lang, $conn, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_listing_template');
        $display = '';

        if ($security) {
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/listing_template_editor_spo.html');

            // Grab the list of Searchable fields sorted by search_rank
            $sql = 'SELECT listingsformelements_id, listingsformelements_field_name,
						listingsformelements_required, listingsformelements_searchable,
						listingsformelements_field_caption, listingsformelements_search_rank
						FROM ' . $config['table_prefix'] . "listingsformelements
						WHERE listingsformelements_searchable = '1'
		 				ORDER BY listingsformelements_search_rank";
            $recordSet = $conn->Execute($sql);

            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $html_results = '';
            $html = $page->get_template_section('spo_item_block');
            while (!$recordSet->EOF) {
                $new_field_block = $html;
                $fid = $recordSet->fields('listingsformelements_id');
                $f_rank = $recordSet->fields('listingsformelements_search_rank');

                // Get Caption from users selected language
                if (!isset($_SESSION['users_lang'])) {
                    $caption = $recordSet->fields('listingsformelements_field_caption');
                } else {
                    $field_id = $misc->make_db_safe($fid);
                    $sql2 = 'SELECT listingsformelements_field_caption
								FROM ' . $config['lang_table_prefix'] . "listingsformelements
								WHERE listingsformelements_id = $field_id";
                    $recordSet2 = $conn->Execute($sql2);
                    if (!$recordSet2) {
                        $misc->log_error($sql2);
                    }
                    $caption = $recordSet2->fields('listingsformelements_field_caption');
                }

                $field_name = htmlentities($recordSet->fields('listingsformelements_field_name'));

                $new_field_block = str_replace('{field_rank}', $f_rank, $new_field_block);
                $new_field_block = str_replace('{field_id}', $fid, $new_field_block);
                $new_field_block = str_replace('{field_name}', $field_name, $new_field_block);
                $new_field_block = str_replace('{field_caption}', $caption, $new_field_block);

                if ($recordSet->fields('listingsformelements_searchable') == 1) {
                    $new_field_block = $page->cleanup_template_block('searchable', $new_field_block);
                } else {
                    $new_field_block = $page->remove_template_block('searchable', $new_field_block);
                }
                if ($recordSet->fields('listingsformelements_required') == 'Yes') {
                    $new_field_block = $page->cleanup_template_block('required', $new_field_block);
                } else {
                    $new_field_block = $page->remove_template_block('required', $new_field_block);
                }
                $html_results .= $new_field_block;
                $recordSet->MoveNext();
            }
            $page->replace_template_section('spo_item_block', $html_results);
            $page->replace_lang_template_tags();
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        } else {
            $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return  $display;
    }

    public function edit_listing_template_sro()
    {
        global $config, $lang, $conn, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_listing_template');
        $display = '';

        if ($security === true) {
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/listing_template_editor_sro.html');

            // Grab the list of fields set to be on the search results page sorted by search_result_rank
            $sql = 'SELECT listingsformelements_id, listingsformelements_field_name,
						listingsformelements_required, listingsformelements_searchable,
						listingsformelements_field_caption, listingsformelements_search_result_rank
						FROM ' . $config['table_prefix'] . "listingsformelements
						WHERE listingsformelements_display_on_browse = 'Yes'
						ORDER BY listingsformelements_search_result_rank;";
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $html_results = '';
            $html = $page->get_template_section('sro_item_block');
            while (!$recordSet->EOF) {
                $new_field_block = $html;
                $fid = $recordSet->fields('listingsformelements_id');
                $f_rank = $recordSet->fields('listingsformelements_search_result_rank');
                // Get Caption from users selected language
                if (!isset($_SESSION['users_lang'])) {
                    $caption = $recordSet->fields('listingsformelements_field_caption');
                } else {
                    $field_id = intval($fid);
                    $sql2 = 'SELECT listingsformelements_field_caption
								FROM ' . $config['lang_table_prefix'] . "listingsformelements
								WHERE listingsformelements_id = $field_id";
                    $recordSet2 = $conn->Execute($sql2);
                    if (!$recordSet2) {
                        $misc->log_error($sql2);
                    }
                    $caption = $recordSet2->fields('listingsformelements_field_caption');
                }

                $field_name = htmlentities($recordSet->fields('listingsformelements_field_name'));

                $new_field_block = str_replace('{field_rank}', $f_rank, $new_field_block);
                $new_field_block = str_replace('{field_id}', $fid, $new_field_block);
                $new_field_block = str_replace('{field_name}', $field_name, $new_field_block);
                $new_field_block = str_replace('{field_caption}', $caption, $new_field_block);

                if ($recordSet->fields('listingsformelements_searchable') == 1) {
                    $new_field_block = $page->cleanup_template_block('searchable', $new_field_block);
                } else {
                    $new_field_block = $page->remove_template_block('searchable', $new_field_block);
                }
                if ($recordSet->fields('listingsformelements_required') == 'Yes') {
                    $new_field_block = $page->cleanup_template_block('required', $new_field_block);
                } else {
                    $new_field_block = $page->remove_template_block('required', $new_field_block);
                }
                $html_results .= $new_field_block;
                $recordSet->MoveNext();
            }
            $page->replace_template_section('sro_item_block', $html_results);
            $page->replace_lang_template_tags();
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        } else {
            $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return  $display;
    }

    public function edit_listing_template_qed()
    {
        global $config, $lang, $conn, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_listing_template');
        $display = '';

        if ($security === true) {
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/listing_template_editor_qed.html');
            // Get template section
            $sqlTS = 'SELECT controlpanel_template_listing_sections
					FROM ' . $config['table_prefix_no_lang'] . 'controlpanel';
            $recordSetTS = $conn->Execute($sqlTS);
            if (!$recordSetTS) {
                $misc->log_error($sql);
            }

            $sections = explode(',', $recordSetTS->fields['controlpanel_template_listing_sections']);
            $sections[] = 'misc';
            foreach ($sections as $key => $section) {
                $section_name = trim($section);
                if ($section_name == 'misc') {
                    $sql_section_name = $misc->make_db_safe('');
                } else {
                    $sql_section_name = $misc->make_db_safe($section_name);
                }

                // Grab a list of field_names in the Database to Edit
                $sql = 'SELECT listingsformelements_id, listingsformelements_field_name,
						listingsformelements_required, listingsformelements_searchable,
						listingsformelements_field_caption, listingsformelements_rank
						FROM ' . $config['table_prefix'] . "listingsformelements
						WHERE listingsformelements_location = " . $sql_section_name . "
						ORDER BY listingsformelements_rank";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $html_results = '';
                $html = $page->get_template_section($section_name . '_item_block');
                while (!$recordSet->EOF) {
                    $new_field_block = $html;
                    $fid = $recordSet->fields('listingsformelements_id');
                    $f_rank = $recordSet->fields('listingsformelements_rank');

                    // Get Caption from users selected language
                    if (!isset($_SESSION['users_lang'])) {
                        $caption = htmlentities($recordSet->fields('listingsformelements_field_caption'));
                    } else {
                        $field_id = $misc->make_db_safe($fid);
                        $sql2 = 'SELECT listingsformelements_field_caption
								FROM ' . $config['lang_table_prefix'] . "listingsformelements
								WHERE listingsformelements_id = $field_id";
                        $recordSet2 = $conn->Execute($sql2);
                        if (!$recordSet2) {
                            $misc->log_error($sql2);
                        }
                        $caption = htmlentities($recordSet2->fields('listingsformelements_field_caption'));
                    }
                    $field_name = htmlentities($recordSet->fields('listingsformelements_field_name'));

                    $new_field_block = str_replace('{field_rank}', $f_rank, $new_field_block);
                    $new_field_block = str_replace('{field_id}', $fid, $new_field_block);
                    $new_field_block = str_replace('{field_name}', $field_name, $new_field_block);
                    $new_field_block = str_replace('{field_caption}', $caption, $new_field_block);

                    if ($recordSet->fields('listingsformelements_searchable') == 1) {
                        $new_field_block = $page->cleanup_template_block('searchable', $new_field_block);
                    } else {
                        $new_field_block = $page->remove_template_block('searchable', $new_field_block);
                    }
                    if ($recordSet->fields('listingsformelements_required') == 'Yes') {
                        $new_field_block = $page->cleanup_template_block('required', $new_field_block);
                    } else {
                        $new_field_block = $page->remove_template_block('required', $new_field_block);
                    }
                    $html_results .= $new_field_block;
                    $recordSet->MoveNext();
                }
                $page->replace_template_section($section_name . '_item_block', $html_results);
            }
            $page->replace_lang_template_tags();
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        } else {
            $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return $display;
    }

    public function ajax_add_listing_field()
    {
        global $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_listing_template');
        if ($security) {
            $display = $this->add_listing_template_field();
        } else {
            $display = '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return $display;
    }

    public function add_listing_template_field()
    {
        global $config, $lang, $conn, $misc;

        $display = '';
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        // Verify User has cred
        $security = $login->verify_priv('edit_listing_template');

        if ($security) {
            $page->load_page($config['admin_template_path'] . '/listing_template_add_field.html');


            $yes_no = array();
            //Define our Yes and No Options
            $yes_no['No'] = $lang['no'];
            $yes_no['Yes'] = $lang['yes'];

            // Define our Field Types
            $field_types = array();
            $field_types['text'] = $lang['text'];
            $field_types['textarea'] = $lang['textarea'];
            $field_types['select'] = $lang['select'];
            $field_types['select-multiple'] = $lang['select-multiple'];
            $field_types['option'] = $lang['option'];
            $field_types['checkbox'] = $lang['checkbox'];
            $field_types['divider'] = $lang['divider'];
            $field_types['price'] = $lang['price'];
            $field_types['url'] = $lang['url'];
            $field_types['email'] = $lang['email'];
            $field_types['number'] = $lang['number'];
            $field_types['decimal'] = $lang['decimal'];
            $field_types['date'] = $lang['date'];
            $field_types['lat'] = $lang['lat'];
            $field_types['long'] = $lang['long'];


            $display_privs = array();
            $display_privs[0] = $lang['display_priv_0'];
            $display_privs[1] = $lang['display_priv_1'];
            $display_privs[2] = $lang['display_priv_2'];
            $display_privs[3] = $lang['display_priv_3'];

            $locations = array();
            $locations[""] = $lang['do_not_display'];
            $sections = explode(',', $config['template_listing_sections']);
            foreach ($sections as $section) {
                $locations[$section] = $section;
            }

            $search_types = array();
            $search_types['ptext'] = $lang['ptext_description'];
            $search_types['optionlist'] = $lang['optionlist_description'];
            $search_types['optionlist_or'] = $lang['optionlist_or_description'];
            $search_types['fcheckbox'] = $lang['fcheckbox_description'];
            $search_types['fcheckbox_or'] = $lang['fcheckbox_or_description'];
            $search_types['fpulldown'] = $lang['fpulldown_description'];
            $search_types['select'] = $lang['select_description'];
            $search_types['select_or'] = $lang['select_or_description'];
            $search_types['pulldown'] = $lang['pulldown_description'];
            $search_types['checkbox'] = $lang['checkbox_description'];
            $search_types['checkbox_or'] = $lang['checkbox_or_description'];
            $search_types['option'] = $lang['option_description'];
            $search_types['minmax'] = $lang['minmax_description'];
            $search_types['daterange'] = $lang['daterange_description'];
            $search_types['singledate'] = $lang['singledate_description'];
            $search_types['null_checkbox'] = $lang['null_checkbox_description'];
            $search_types['notnull_checkbox'] = $lang['notnull_checkbox_description'];

            $html = $page->get_template_section('required_block');
            $html = $page->form_options($yes_no, "", $html);
            $page->replace_template_section('required_block', $html);

            $html = $page->get_template_section('field_type_block');
            $html = $page->form_options($field_types, "", $html);
            $page->replace_template_section('field_type_block', $html);

            $html = $page->get_template_section('display_priv_block');
            $html = $page->form_options($display_privs, "", $html);
            $page->replace_template_section('display_priv_block', $html);

            $html = $page->get_template_section('location_block');
            $html = $page->form_options($locations, "", $html);
            $page->replace_template_section('location_block', $html);

            $html = $page->get_template_section('search_type_block');
            $html = $page->form_options($search_types, "", $html);
            $page->replace_template_section('search_type_block', $html);

            $html = $page->get_template_section('display_on_browse_block');
            $html = $page->form_options($yes_no, "", $html);
            $page->replace_template_section('display_on_browse_block', $html);

            // get list of all property clases
            $sql = 'SELECT class_name, class_id
					FROM ' . $config['table_prefix'] . 'class
					ORDER BY class_rank';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $classes = array();


            while (!$recordSet->EOF) {
                $class_id = $recordSet->fields('class_id');
                $class_name = $recordSet->fields('class_name');
                $classes[$class_id] = $class_name;
                $recordSet->MoveNext();
            }

            $html = $page->get_template_section('pclass_select_block');
            $html = $page->form_options($classes, "", $html);
            $page->replace_template_section('pclass_select_block', $html);



            $page->replace_tag('application_status_text', '');
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        }
        return '<div class="error_text">' . $lang['access_denied'] . '</div>';
    }

    public function ajax_insert_listing_field()
    {
        global $lang, $config, $misc, $api;
        ;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_listing_template');

        if ($security) {
            if (isset($_POST['edit_field']) && !isset($_POST['lang_change'])) {
                if (empty($_POST['property_class'])) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' =>  $lang['no_pclass_selected']]);
                }
                if (!isset($_POST['searchable'])) {
                    $_POST['searchable'] = false;
                } else {
                    $_POST['searchable'] = true;
                }
                if (!isset($_POST['field_length'])) {
                    $_POST['field_length'] = '';
                }
                if (!isset($_POST['tool_tip'])) {
                    $_POST['tool_tip'] = '';
                }
                if ($_POST['required'] == 'Yes') {
                    $_POST['required'] = true;
                } else {
                    $_POST['required'] = false;
                }
                if ($_POST['display_on_browse'] == 'Yes') {
                    $_POST['display_on_browse'] = true;
                } else {
                    $_POST['display_on_browse'] = false;
                }
                $create_result = $api->load_local_api(
                    'fields__create',
                    [
                        'resource' => 'listing',
                        'class' => $_POST['property_class'],
                        'field_type' => $_POST['field_type'],
                        'field_name' => $_POST['edit_field'],
                        'field_caption' => $_POST['field_caption'],
                        'default_text' => $_POST['default_text'],
                        'field_elements' => explode('||', $_POST['field_elements']),
                        'rank' => $_POST['rank'],
                        'search_rank' => $_POST['search_rank'],
                        'search_result_rank' => $_POST['search_result_rank'],
                        'required' => $_POST['required'],
                        'location' => $_POST['location'],
                        'display_on_browse' => $_POST['display_on_browse'],
                        'search_step' => $_POST['search_step'],
                        'display_priv' => $_POST['display_priv'],
                        'field_length' => intval($_POST['field_length']),
                        'tool_tip' => $_POST['tool_tip'],
                        'search_label' => $_POST['search_label'],
                        'search_type' => $_POST['search_type'],
                        'searchable' => $_POST['searchable'],
                    ]
                );
                if ($create_result['error'] == false) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '0', 'field_name' => $_POST['edit_field']]);
                } else {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $create_result['error_msg']]);
                }
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' =>  $lang['access_denied']]);
        }
    }

    public function edit_listing_field($edit_listing_field_name)
    {
        global $config, $conn, $lang, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_listing_template');
        include_once $config['basepath'] . '/include/core.inc.php';
        $display = '';

        if ($security === true) {
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/listing_template_edit_field.html');

            $yes_no = array();
            //Define our Yes and No Options
            $yes_no['No'] = $lang['no'];
            $yes_no['Yes'] = $lang['yes'];

            // Define our Field Types
            $field_types = array();
            $field_types['text'] = $lang['text'];
            $field_types['textarea'] = $lang['textarea'];
            $field_types['select'] = $lang['select'];
            $field_types['select-multiple'] = $lang['select-multiple'];
            $field_types['option'] = $lang['option'];
            $field_types['checkbox'] = $lang['checkbox'];
            $field_types['divider'] = $lang['divider'];
            $field_types['price'] = $lang['price'];
            $field_types['url'] = $lang['url'];
            $field_types['email'] = $lang['email'];
            $field_types['number'] = $lang['number'];
            $field_types['decimal'] = $lang['decimal'];
            $field_types['date'] = $lang['date'];
            $field_types['lat'] = $lang['lat'];
            $field_types['long'] = $lang['long'];


            $display_privs = array();
            $display_privs[0] = $lang['display_priv_0'];
            $display_privs[1] = $lang['display_priv_1'];
            $display_privs[2] = $lang['display_priv_2'];
            $display_privs[3] = $lang['display_priv_3'];

            $locations = array();
            $locations[""] = $lang['do_not_display'];
            $sections = explode(',', $config['template_listing_sections']);
            foreach ($sections as $section) {
                $locations[$section] = $section;
            }


            $search_types = array();
            $search_types['ptext'] = $lang['ptext_description'];
            $search_types['optionlist'] = $lang['optionlist_description'];
            $search_types['optionlist_or'] = $lang['optionlist_or_description'];
            $search_types['fcheckbox'] = $lang['fcheckbox_description'];
            $search_types['fcheckbox_or'] = $lang['fcheckbox_or_description'];
            $search_types['fpulldown'] = $lang['fpulldown_description'];
            $search_types['select'] = $lang['select_description'];
            $search_types['select_or'] = $lang['select_or_description'];
            $search_types['pulldown'] = $lang['pulldown_description'];
            $search_types['checkbox'] = $lang['checkbox_description'];
            $search_types['checkbox_or'] = $lang['checkbox_or_description'];
            $search_types['option'] = $lang['option_description'];
            $search_types['minmax'] = $lang['minmax_description'];
            $search_types['daterange'] = $lang['daterange_description'];
            $search_types['singledate'] = $lang['singledate_description'];
            $search_types['null_checkbox'] = $lang['null_checkbox_description'];
            $search_types['notnull_checkbox'] = $lang['notnull_checkbox_description'];


            $edit_listing_field_name = $misc->make_db_safe($edit_listing_field_name);
            $sql = 'SELECT * FROM ' . $config['table_prefix'] . "listingsformelements
					WHERE listingsformelements_field_name = $edit_listing_field_name";
            $recordSet = $conn->Execute($sql);
            if ($recordSet === false) {
                $misc->log_error($sql);
            }
            $id = $recordSet->fields('listingsformelements_id');
            $field_type = $recordSet->fields('listingsformelements_field_type');
            $field_name = $recordSet->fields('listingsformelements_field_name');

            // Multi Lingual Support
            if (!isset($_SESSION['users_lang'])) {
                // Hold empty string for translation fields, as we are workgin with teh default lang
                $default_lang_field_caption = '';
                $default_lang_default_text = '';
                $default_lang_field_elements = '';
                $default_lang_search_label = '';

                $field_caption = $recordSet->fields('listingsformelements_field_caption');
                $default_text = $recordSet->fields('listingsformelements_default_text');
                $field_elements = $recordSet->fields('listingsformelements_field_elements');
                $search_label = $recordSet->fields('listingsformelements_search_label');
            } else {
                // Store default lang to show for tanslator
                $default_lang_field_caption = $recordSet->fields('listingsformelements_field_caption');
                $default_lang_default_text = $recordSet->fields('listingsformelements_default_text');
                $default_lang_field_elements = $recordSet->fields('listingsformelements_field_elements');
                $default_lang_search_label = $recordSet->fields('listingsformelements_search_label');
                $default_lang_tool_tip = $recordSet->fields('listingsformelements_tool_tip');
                $field_id = intval($recordSet->fields('listingsformelements_id'));
                $lang_sql = 'SELECT listingsformelements_field_caption,listingsformelements_default_text,listingsformelements_field_elements,listingsformelements_search_label
							FROM ' . $config['lang_table_prefix'] . "listingsformelements
							WHERE listingsformelements_id = $field_id";
                $lang_recordSet = $conn->Execute($lang_sql);
                if (!$lang_recordSet) {
                    $misc->log_error($lang_sql);
                }
                $field_caption = $lang_recordSet->fields('listingsformelements_field_caption');
                $default_text = $lang_recordSet->fields('listingsformelements_default_text');
                $field_elements = $lang_recordSet->fields('listingsformelements_field_elements');
                $search_label = $lang_recordSet->fields('listingsformelements_search_label');
            }

            $rank = $recordSet->fields('listingsformelements_rank');
            $search_rank = $recordSet->fields('listingsformelements_search_rank');
            $search_result_rank = $recordSet->fields('listingsformelements_search_result_rank');
            $required = $recordSet->fields('listingsformelements_required');
            $location = $recordSet->fields('listingsformelements_location');
            $display_on_browse = $recordSet->fields('listingsformelements_display_on_browse');
            $display_priv = $recordSet->fields('listingsformelements_display_priv');
            $search_step = $recordSet->fields('listingsformelements_search_step');
            $searchable = $recordSet->fields('listingsformelements_searchable');
            $search_type = $recordSet->fields('listingsformelements_search_type');
            $field_length = $recordSet->fields('listingsformelements_field_length');
            $tool_tip = $recordSet->fields('listingsformelements_tool_tip');

            $page->replace_tag_safe("field_id", $id);
            $page->replace_tag_safe("field_name", $field_name);
            $page->replace_tag_safe("field_caption", $field_caption);
            $page->replace_tag_safe("field_elements", $field_elements);
            $page->replace_tag_safe("default_text", $default_text);
            $page->replace_tag_safe("rank", $rank);
            $page->replace_tag_safe("search_rank", $search_rank);
            $page->replace_tag_safe("search_result_rank", $search_result_rank);
            $page->replace_tag_safe("search_step", $search_step);
            $page->replace_tag_safe("field_length", $field_length);
            $page->replace_tag_safe("tool_tip", $tool_tip);
            $page->replace_tag_safe("search_label", $search_label);
            //$page->replace_tag_safe("searchable", $searchable);


            if ($searchable > 0) {
                $page->page = $page->cleanup_template_block('searchable', $page->page);
            } else {
                $page->page = $page->remove_template_block('searchable', $page->page);
            }

            $html = $page->get_template_section('required_block');
            $html = $page->form_options($yes_no, $required, $html);
            $page->replace_template_section('required_block', $html);

            $html = $page->get_template_section('field_type_block');
            $html = $page->form_options($field_types, $field_type, $html);
            $page->replace_template_section('field_type_block', $html);

            $html = $page->get_template_section('display_priv_block');
            $html = $page->form_options($display_privs, $display_priv, $html);
            $page->replace_template_section('display_priv_block', $html);

            $html = $page->get_template_section('location_block');
            $html = $page->form_options($locations, $location, $html);
            $page->replace_template_section('location_block', $html);

            $html = $page->get_template_section('search_type_block');
            $html = $page->form_options($search_types, $search_type, $html);
            $page->replace_template_section('search_type_block', $html);

            $html = $page->get_template_section('display_on_browse_block');
            $html = $page->form_options($yes_no, $display_on_browse, $html);
            $page->replace_template_section('display_on_browse_block', $html);

            // get list of all property clases
            $sql = 'SELECT class_name, class_id
					FROM ' . $config['table_prefix'] . 'class
					ORDER BY class_rank';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $classes = array();
            $selectedClasses = array();

            while (!$recordSet->EOF) {
                $class_id = $recordSet->fields('class_id');
                $class_name = $recordSet->fields('class_name');
                $classes[$class_id] = $class_name;
                // check if this field is part of this class
                $sql = 'SELECT count(class_id) 
						AS exist 
						FROM ' . $config['table_prefix_no_lang'] . 'classformelements
						WHERE listingsformelements_id = ' . $id . '
						AND class_id =' . $class_id;
                $recordSet2 = $conn->Execute($sql);
                if (!$recordSet2) {
                    $misc->log_error($sql);
                }
                $select = $recordSet2->fields('exist');
                if ($select > 0) {
                    $selectedClasses[] = $class_id;
                }
                $recordSet->MoveNext();
            }

            $html = $page->get_template_section('pclass_select_block');
            $html = $page->form_options($classes, $selectedClasses, $html);
            $page->replace_template_section('pclass_select_block', $html);


            $page->replace_lang_template_tags();
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);

            return $page->return_page();
        } else {
            return '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
    }

    public function ajax_update_listing_field()
    {
        global $lang, $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $display = '';
        $security = $login->verify_priv('edit_listing_template');

        if ($security) {
            global $conn, $misc, $api;

            if (isset($_POST['update_id']) && !isset($_POST['lang_change'])) {
                $id = intval($_POST['update_id']);
                $_POST['old_field_name'] = str_replace(' ', '_', $_POST['old_field_name']);
                $_POST['edit_field'] = str_replace(' ', '_', $_POST['edit_field']);
                $field_name = $misc->make_db_safe($_POST['edit_field']);
                $old_field_name = $misc->make_db_safe($_POST['old_field_name']);
                $required = $misc->make_db_safe($_POST['required']);
                $update_field_name = false;
                if ($old_field_name != $field_name) {
                    $update_field_name = true;
                }
                $field_type = $misc->make_db_safe($_POST['field_type']);
                $field_caption = $misc->make_db_safe($_POST['field_caption']);
                $default_text = $misc->make_db_safe($_POST['default_text']);
                $field_elements = $misc->make_db_safe($_POST['field_elements']);
                $rank = intval($_POST['rank']);
                $search_rank = intval($_POST['search_rank']);
                $search_result_rank = intval($_POST['search_result_rank']);
                $location = $misc->make_db_safe($_POST['location']);
                $display_on_browse = $misc->make_db_safe($_POST['display_on_browse']);
                $display_priv = intval($_POST['display_priv']);
                $search_step = intval($_POST['search_step']);
                $field_length = intval($_POST['field_length']);
                $tool_tip = $misc->make_db_safe($_POST['tool_tip']);
                if (isset($_POST['searchable'])) {
                    $searchable = intval($_POST['searchable']);
                } else {
                    $searchable = intval(0);
                }
                if ($searchable == '1' && $_POST['search_type'] == '') {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' =>  $lang['no_search_type']]);
                } elseif (count($_POST['property_class']) == 0) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' =>  $lang['no_property_class_selected']]);
                } else {
                    $search_label = $misc->make_db_safe($_POST['search_label']);
                    $search_type = $misc->make_db_safe($_POST['search_type']);
                    $sql = 'UPDATE ' . $config['table_prefix'] . "listingsformelements
							SET listingsformelements_field_type = $field_type, listingsformelements_field_name = $field_name, listingsformelements_rank = $rank, listingsformelements_search_rank = $search_rank, listingsformelements_search_result_rank = $search_result_rank, listingsformelements_required = $required, listingsformelements_location = $location, listingsformelements_display_on_browse = $display_on_browse, listingsformelements_search_step = $search_step, listingsformelements_searchable = $searchable, listingsformelements_search_type = $search_type, listingsformelements_display_priv = $display_priv, listingsformelements_field_length = $field_length, listingsformelements_tool_tip = $tool_tip
							WHERE listingsformelements_id = $id";
                    $recordSet = $conn->Execute($sql);
                    if ($recordSet === false) {
                        $misc->log_error($sql);
                    }
                    // Update Current language
                    if (!isset($_SESSION['users_lang'])) {
                        $lang_sql = 'UPDATE  ' . $config['table_prefix'] . "listingsformelements SET listingsformelements_field_caption = $field_caption, listingsformelements_default_text = $default_text,listingsformelements_field_elements = $field_elements,listingsformelements_search_label = $search_label
									WHERE listingsformelements_id = $id";
                        $lang_recordSet = $conn->Execute($lang_sql);
                        if (!$lang_recordSet) {
                            $misc->log_error($lang_sql);
                        }
                    } else {
                        $lang_sql = 'DELETE FROM  ' . $config['lang_table_prefix'] . "listingsformelements WHERE listingsformelements_id = $id";
                        $lang_recordSet = $conn->Execute($lang_sql);
                        if (!$lang_recordSet) {
                            $misc->log_error($lang_sql);
                        }
                        $lang_sql = 'INSERT INTO ' . $config['lang_table_prefix'] . "listingsformelements (listingsformelements_id, listingsformelements_field_caption,listingsformelements_default_text,listingsformelements_field_elements,listingsformelements_search_label)
									VALUES ($id, $field_caption,$default_text,$field_elements,$search_label)";
                        $lang_recordSet = $conn->Execute($lang_sql);
                        if (!$lang_recordSet) {
                            $misc->log_error($lang_sql);
                        }
                    }
                    // Check if field name changed, if it as update all listingsdbelement tables
                    if ($update_field_name) {
                        $lang_sql = 'UPDATE  ' . $config['table_prefix'] . "listingsdbelements SET listingsdbelements_field_name = $field_name
									WHERE listingsdbelements_field_name = $old_field_name";
                        $lang_recordSet = $conn->Execute($lang_sql);
                        if (!$lang_recordSet) {
                            $misc->log_error($lang_sql);
                        }
                    }
                    // Delete from classform elements.
                    $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . 'classformelements WHERE listingsformelements_id = ' . $id;
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    // Insert new selections into class formelements
                    $class_sql = '';
                    foreach ($_POST['property_class'] as $class_id) {
                        $class_id = intval($class_id);
                        if ($class_id > 0) {
                            //Add to Property Class
                            $result = $api->load_local_api('fields__assign_class', ['class' => $class_id, 'field_id' => $id]);
                            if (!empty($class_sql)) {
                                $class_sql .= ' OR listingsdb_pclass_id = ' . $class_id;
                            } else {
                                $class_sql .= ' listingsdb_pclass_id = ' . $class_id;
                            }
                        }
                    }
                    // Remove fields from any listings that are not in this class.
                    $pclass_list = '';
                    $sql = 'SELECT listingsdb_id FROM ' . $config['table_prefix'] . 'listingsdb WHERE ' . $class_sql;
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    while (!$recordSet->EOF) {
                        if (empty($pclass_list)) {
                            $pclass_list .= $recordSet->fields('listingsdb_id');
                        } else {
                            $pclass_list .= ',' . $recordSet->fields('listingsdb_id');
                        }
                        $recordSet->Movenext();
                    }
                    if ($pclass_list == '') {
                        $pclass_list = 0;
                    }
                    $sql = 'DELETE FROM ' . $config['table_prefix'] . 'listingsdbelements
							WHERE listingsdbelements_field_name = ' . $field_name . '
							AND listingsdb_id NOT IN (' . $pclass_list . ')';
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    header('Content-type: application/json');
                    return json_encode(['error' => '0', 'field_id' =>  $id]);
                    //$display .= '<center>' . $lang['field_has_been_updated'] . '</center><br />';
                }
            } else {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' =>  $lang['access_denied']]);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' =>  $lang['access_denied']]);
        }
        return $display;
    }

    public function ajax_get_listing_field_info()
    {
        global $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_listing_template');

        $display = '';

        if ($security === true) {
            if (isset($_GET['edit_field'])) {
                $display .= $this->edit_listing_field($_GET['edit_field']);
            }
        } else {
            $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return $display;
    }

    public function ajax_save_listing_field_order()
    {
        global $config, $lang, $conn, $misc;
        header('Content-type: application/json');

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_listing_template');

        // echo '<pre>'.print_r($_POST['field_name'], true).'</pre>';
        // echo '<pre>'.print_r($_POST['search_setup'], true).'</pre>';

        $display = '';
        if ($security === true) {
            if (isset($_POST['section']) && isset($_POST['fields'])) {
                //Verify Section is valid
                $valid_section = explode(',', $config["template_listing_sections"]);
                if (in_array($_POST['section'], $valid_section)) {
                    $section = $_POST['section'];
                } else {
                    return json_encode(['error' => true, 'error_msg' =>  $lang['invalid_template_section']]);
                }
                $sql_section = $misc->make_db_safe($_POST['section']);
                foreach ($_POST['fields'] as $rank => $field_name) {
                    //empty locations are skipped
                    if (!empty($field_name)) {
                        $sql_field_name = $misc->make_db_safe($field_name);
                        $sql_rank = intval($rank);

                        $sql = 'UPDATE ' . $config['table_prefix'] . "listingsformelements
                        SET listingsformelements_location = " . $sql_section . ",
                            listingsformelements_rank = " . $sql_rank . "
                        WHERE listingsformelements_field_name = " . $sql_field_name;
                        $recordSet = $conn->Execute($sql);
                        //echo $sql.'<br>';
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                    }
                }
                return json_encode(['error' => false, 'status_msg' =>  $lang['admin_template_editor_field_order_set']]);
            }
        }
        return json_encode(['error' => true, 'error_msg' =>  $lang['access_denied']]);
    }

    public function ajax_save_listing_search_order($order_form)
    {
        global $config, $lang;
        header('Content-type: application/json');
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_listing_template');

        //echo print_r($_POST['search_setup'],TRUE).' zzz<br>';

        $display = '';
        if ($security === true) {
            global $conn, $misc;
            if (isset($_POST['fields'])) {
                if ($order_form == 'spo') {
                    $rank_field = 'listingsformelements_search_rank';
                } elseif ($order_form == 'sro') {
                    $rank_field = 'listingsformelements_search_result_rank';
                }
                foreach ($_POST['fields'] as $search_rank => $field_name) {
                    //empty locations are skipped
                    if (!empty($field_name)) {
                        $sql_field_name = $misc->make_db_safe($field_name);
                        $sql_rank = intval($search_rank);
                        $sql_rank = $sql_rank + 1;

                        $sql = 'UPDATE ' . $config['table_prefix'] . 'listingsformelements
						SET  ' . $rank_field . " = " . $sql_rank . "
						WHERE listingsformelements_field_name = " . $sql_field_name;
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                    }
                }
                return json_encode(['error' => false, 'status_msg' =>  $lang['admin_template_editor_field_order_set']]);
            }
        } else {
            return json_encode(['error' => true, 'error_msg' =>  $lang['access_denied']]);
        }
        return $display;
    }

    public function delete_listing_field()
    {
        global $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_listing_template');

        if ($security) {
            global $conn, $misc, $api;

            if (isset($_GET['delete_field']) && !isset($_POST['lang_change'])) {
                $field_name = $misc->make_db_safe($_GET['delete_field']);
                $sql = 'SELECT listingsformelements_id 
						FROM ' . $config['table_prefix'] . "listingsformelements
						WHERE listingsformelements_field_name = $field_name";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                // Delete All Translationf for this field.
                $configured_langs = explode(',', $config['configured_langs']);
                while (!$recordSet->EOF) {
                    $listingsformelements_id = intval($recordSet->fields('listingsformelements_id'));
                    foreach ($configured_langs as $configured_lang) {
                        $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . $configured_lang . "_listingsformelements
								WHERE listingsformelements_id = $listingsformelements_id";
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                    }
                    // Remove field from property class.
                    $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . 'classformelements
							WHERE listingsformelements_id = ' . $listingsformelements_id;
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                }
                // Cleanup any listingdbelemts entries from this field.
                foreach ($configured_langs as $configured_lang) {
                    $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . $configured_lang . "_listingsdbelements
							WHERE listingsdbelements_field_name = $field_name";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                }
                $api->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'function->delete_listing_field', 'log_message' => 'Deleted Listing Field ' . $field_name]);
            }
        } else {
            return '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
    }
}
