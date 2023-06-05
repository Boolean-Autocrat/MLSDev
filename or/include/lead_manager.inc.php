<?php

class lead_manager
{
    public function show_add_lead()
    {
        global $config, $jscript;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        include_once $config['basepath'] . '/include/lead_functions.inc.php';
        $lead_functions = new lead_functions();
        $jscript .= '{load_css_add_lead}';
        $page->load_page($config['admin_template_path'] . '/add_lead.html');
        $lead_fields = $lead_functions->get_feedback_formelements();
        $page->replace_tag('feedback_formelements', $lead_fields);
        return $page->return_page();
    }

    /*
     *  feedbackview() View and edit leads
     */
    public function feedbackview($all_lead_view = false)
    {
        global $config, $lang, $conn, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        include_once $config['basepath'] . '/include/lead_functions.inc.php';
        $lead_functions = new lead_functions();
        include_once $config['basepath'] . '/include/listing.inc.php';
        $listing = new listing_pages();
        include_once $config['basepath'] . '/include/user.inc.php';
        $user = new user();
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();

        if (isset($_GET['feedback_id'])) {
            $feedback_id = intval($_GET['feedback_id']);
            if (isset($_GET['active']) && $_GET['active'] == 1) {
                $active = 1;
            } else {
                $active = 0;
            }
            $sql = 'SELECT feedbackdb_creation_date, feedbackdb_status, feedbackdb_notes, listingdb_id, feedbackdb_last_modified, userdb_id, feedbackdb_member_userdb_id
					FROM ' . $config['table_prefix'] . "feedbackdb
					WHERE feedbackdb_id = " . $feedback_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $user_id = $recordSet->fields('userdb_id');
            $listingsdb_id = $recordSet->fields('listingdb_id');
            $permission = false;
            if ($_SESSION['userID'] !== $user_id) {
                if ($all_lead_view == true) {
                    $permission = $login->verify_priv('edit_all_leads');
                }
            } else {
                $permission = true;
            }
            if (!$permission) {
                return $lang['listing_editor_permission_denied'];
            }

            $member_id = $recordSet->fields('feedbackdb_member_userdb_id');
            $feedback_last_modified = $recordSet->UserTimeStamp($recordSet->fields('feedbackdb_last_modified'), 'd m, Y g:ia');
            $feedbackdb_creation_date = $recordSet->UserTimeStamp($recordSet->fields('feedbackdb_creation_date'), 'd m, Y g:ia');
            $creation_day = $recordSet->UserTimeStamp($recordSet->fields('feedbackdb_creation_date'), 'd');
            $creation_month = $recordSet->UserTimeStamp($recordSet->fields('feedbackdb_creation_date'), 'm');
            $creation_year = $recordSet->UserTimeStamp($recordSet->fields('feedbackdb_creation_date'), 'Y');
            $creation_hour = $recordSet->UserTimeStamp($recordSet->fields('feedbackdb_creation_date'), 'G');
            $creation_minute = $recordSet->UserTimeStamp($recordSet->fields('feedbackdb_creation_date'), 'i');
            $feedbackdb_status = $recordSet->fields('feedbackdb_status');
            //Format Y-m-d per ISO8601 for fullcalendar
            $calendar_creation_date = $recordSet->UserTimeStamp($recordSet->fields('feedbackdb_creation_date'), 'Y-m-d');
            $notes = htmlentities($recordSet->fields('feedbackdb_notes'), ENT_COMPAT, $config['charset']);

            $listing_title = $listing->get_listing_single_value('listingsdb_title', $listingsdb_id);
            $url_listing_title = rawurlencode($listing_title);
            $user_email = $user->get_user_single_item('userdb_emailaddress', $member_id);





            $page->load_page($config['admin_template_path'] . '/view_leads.html');
            $page->page = str_replace('{creation_hour}', $creation_hour, $page->page);
            $page->page = str_replace('{creation_minute}', $creation_minute, $page->page);
            $page->page = str_replace('{creation_day}', $creation_day, $page->page);
            $page->page = str_replace('{creation_month}', $creation_month, $page->page);
            $page->page = str_replace('{creation_year}', $creation_year, $page->page);
            $page->page = str_replace('{calendar_creation_date}', $calendar_creation_date, $page->page);
            $page->page = $this->leadmanager_pagination_tags($all_lead_view, $page->page);

            //$page->replace_tag('leadmanager_status', $this->leadmanager_status());

            if ($feedbackdb_status == 1) {
                $page->page = $page->cleanup_template_block('leadmanager_status', $page->page);
            } else {
                $page->page = $page->remove_template_block('leadmanager_status', $page->page);
            }

            $page->page = $lead_functions->get_leadmanager_priority($feedback_id, $page->page);

            $field = $lead_functions->renderTemplateArea('headline', $feedback_id);
            $page->page = str_replace('{headline}', $field, $page->page);

            $field = $lead_functions->renderTemplateArea('top_left', $feedback_id);
            $page->page = str_replace('{top_left}', $field, $page->page);
            $field = $lead_functions->renderTemplateArea('top_right', $feedback_id);
            $page->page = str_replace('{top_right}', $field, $page->page);

            $field = $lead_functions->renderTemplateArea('center', $feedback_id);

            $page->page = str_replace('{center}', $field, $page->page);

            $field = $lead_functions->renderTemplateArea('bottom_left', $feedback_id);
            $page->page = str_replace('{bottom_left}', $field, $page->page);
            $field = $lead_functions->renderTemplateArea('bottom_right', $feedback_id);
            $page->page = str_replace('{bottom_right}', $field, $page->page);
            //End of Template Areas

            $page->page = str_replace('{notes}', $notes, $page->page);
            $feedback_last_modified = $recordSet->UserTimeStamp($recordSet->fields('feedbackdb_last_modified'), 'm/d/Y g:ia');
            $feedbackdb_creation_date = $recordSet->UserTimeStamp($recordSet->fields('feedbackdb_creation_date'), 'm/d/Y g:ia');
            $modifiedby = $lead_functions->getFeedbackModData($feedback_id, 'modifiedby');
            $page->replace_tag('modifiedby', $modifiedby);

            //Agent Dropdown
            $sql = 'SELECT userdb_user_first_name,  userdb_user_last_name, userdb_id
             FROM ' . $config['table_prefix'] . "userdb
             WHERE userdb_is_agent = 'yes' ";
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $agents = [];
            // get main users data
            while (!$recordSet->EOF) {
                $agent = $recordSet->fields('userdb_id');
                $first_name = $recordSet->fields('userdb_user_first_name');
                $last_name = $recordSet->fields('userdb_user_last_name');
                $user_name = $first_name . ' ' . $last_name;
                $agents[$agent] = $user_name;
                $recordSet->MoveNext();
            } //end while

            $html = $page->get_template_section('leadmanager_agent_block');
            $html = $page->form_options($agents, $user_id, $html);
            $page->replace_template_section('leadmanager_agent_block', $html);

            $page->replace_tag('lastmodified', $feedback_last_modified);
            $page->replace_tag('creationdate', $feedbackdb_creation_date);
            $page->replace_tag('feedback_id', $feedback_id);
            $page->replace_user_field_tags($member_id);
            $edit_listing_link = $config['baseurl'] . '/admin/index.php?action=edit_listing&edit=' . $listingsdb_id;
            $page->replace_tag('edit_listing_link', $edit_listing_link);
            $page->replace_listing_field_tags($listingsdb_id);
            $page->replace_permission_tags();
            $page->replace_urls();
            //$page->replace_meta_template_tags();
            $page->auto_replace_tags();
            //$page->replace_tags(array('load_js', 'load_ORjs', 'load_js_last'));
            $page->replace_lang_template_tags();
            $page->replace_css_template_tags();

            $output = $page->return_page();
        } else {
            $output = '<a href="index.php">Not a valid request</a>';
        }
        return $output;
    }

    public function ajax_leadmanager_datatable($show_all_leads = false)
    {
        global $config, $conn, $lang, $misc;
        header('Content-type: application/json');
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        $aColumns = ['feedbackdb_id', 'feedbackdb_member_userdb_id', 'feedbackdb_creation_date', 'feedbackdb_priority', 'feedbackdb_status', 'userdb_id'];
        $where = ' WHERE userdb_id = ' . intval($_SESSION['userID']) . ' ';
        if ($show_all_leads == true) {
            $login_status = $login->verify_priv('edit_all_leads');
            if ($login_status === true) {
                $where = '';
            } else {
                return $lang['permission_denied'];
            }
        }

        //Do Search to get total record count, no need pass in soring information
        $limit = 0;
        $offset = 0;
        $sql = 'SELECT count(*) as mycount 
				FROM ' . $config['table_prefix'] . "feedbackdb $where";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $iTotal = $recordSet->fields('mycount');
        if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
            $limit = intval($_GET['iDisplayLength']);
            $offset = intval($_GET['iDisplayStart']);
            //$sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".mysql_real_escape_string( $_GET['iDisplayLength'] );
        }
        $sortby = [];
        $sorttype = [];
        //Deal with sorting
        if (isset($_GET['order'])) {
            for ($i = 0; $i < intval($_GET['order']); $i++) {
                if (isset($_GET['order'][$i]['column']) && isset($_GET['columns'][$_GET['order'][$i]['column']]['orderable']) && $_GET['columns'][$_GET['order'][$i]['column']]['orderable'] == 'true') {
                    $sortby[$i] = $aColumns[$_GET['order'][$i]['column']];
                    $sorttype[$i] = strtoupper($_GET['order'][$i]['dir']);
                }
            }
        }
        $sWhere = '';
        if (isset($_GET['search']['value']) && $_GET['search']['value'] != '') {
            $sql_sSearch = $conn->qstr('%' . $_GET['search']['value'] . '%');

            if ($where == '') {
                $sWhere = 'WHERE (';
            } else {
                $sWhere = 'AND (';
            }
            for ($i = 0; $i < count($aColumns); $i++) {
                if ($aColumns[$i] == 'userdb_id' || $aColumns[$i] == 'feedbackdb_member_userdb_id') {
                    $sWhere .= '`' . $aColumns[$i] . '` IN (SELECT userdb_id FROM ' . $config['table_prefix'] . "userdb 
																		WHERE CONCAT(userdb_user_last_name,', ',userdb_user_first_name) 
																		LIKE " . $sql_sSearch . ') OR ';
                } else {
                    $sWhere .= '`' . $aColumns[$i] . '` LIKE ' . $sql_sSearch . ' OR ';
                }
            }
            $sWhere = substr_replace($sWhere, '', -3);
            $sWhere .= ')';
        }
        //Deal with column filters
        if (isset($_GET['columns']) && is_array($_GET['columns'])) {
            for ($i = 0; $i < count($_GET['columns']); $i++) {
                if (isset($_GET['columns'][$i]['searchable']) && isset($_GET['columns'][$i]['search']['value']) && $_GET['columns'][$i]['searchable'] == 'true' && $_GET['columns'][$i]['search']['value'] != '') {
                    $ARGS[$aColumns[$i]] = $_GET['columns'][$i]['search']['value'];
                }
            }
        }

        if (!empty($ARGS)) {
            foreach ($ARGS as $f => $k) {
                if ($sWhere == '') {
                    if ($where == '') {
                        $where .= ' WHERE ';
                    } else {
                        $where .= ' AND ';
                    }
                } else {
                    $where .= 'AND ';
                }

                if ($f == 'feedbackdb_status') {
                    $where .= $conn->addQ($f) . ' = ' . intval($k);
                } elseif ($f == 'feedbackdb_member_userdb_id') {
                    $where .= '`' . $conn->addQ($f) . '` IN (SELECT userdb_id 
													FROM ' . $config['table_prefix'] . "userdb 
													WHERE CONCAT(userdb_user_last_name,', ',userdb_user_first_name) 
													LIKE '%" . $conn->addQ($k) . "%') ";
                } else {
                    $where .= $conn->addQ($f) . ' LIKE \'%' . $conn->addQ($k) . '%\'';
                }
            }
        }
        $sort = '';
        if (count($sortby) > 0) {
            $sort .= ' ORDER BY ';
            foreach ($sortby as $x => $f) {
                if ($sort != ' ORDER BY ') {
                    $sort .= ', ';
                }
                $sort .= $conn->addQ($f) . ' ' . $conn->addQ($sorttype[$x]);
            }
        }
        $limitstr = '';
        if ($limit > 0) {
            $limitstr .= 'LIMIT ' . $offset . ', ' . $limit;
        }
        $sql = 'SELECT count(*) as filteredcount FROM ' . $config['table_prefix'] . "feedbackdb $sWhere $where";
        $filtersql = $sql;
        //echo $sql;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $iFilteredTotal = $recordSet->fields('filteredcount');
        $sql = 'SELECT * FROM ' . $config['table_prefix'] . "feedbackdb $sWhere $where $sort $limitstr ";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }

        $draw = 0;
        if (isset($_GET['draw'])) {
            $draw = intval($_GET['draw']);
        }

        $output = [
            'draw' => $draw,
            'recordsTotal' => $iTotal,
            'recordsFiltered' => $iFilteredTotal,
            'data' => [],
        ];
        $status_array[0] = $lang['leadmanager_status_inactive'];
        $status_array[1] = $lang['leadmanager_status_active'];
        if ($show_all_leads == true) {
            $lead_edit_action = 'leadmanager_feedback_edit';
            $lead_view_action = 'leadmanager_viewfeedback';
        } else {
            $lead_edit_action = 'leadmanager_my_feedback_edit';
            $lead_view_action = 'leadmanager_my_viewfeedback';
        }
        while (!$recordSet->EOF) {
            $row = [];
            //feedbackdb_id, feedbackdb_member_userdb_id, userdb_id, feedbackdb_creation_date, feedbackdb_priority, feedbackdb_status, ,
            $lead_id = $recordSet->fields('feedbackdb_id');
            $row[] = $recordSet->fields('feedbackdb_id');
            $sqlMember = 'SELECT userdb_user_first_name, userdb_user_last_name
			FROM ' . $config['table_prefix'] . 'userdb
			WHERE userdb_id =' . $recordSet->fields('feedbackdb_member_userdb_id');

            $recordSet2 = $conn->execute($sqlMember);
            if ($recordSet2 === false) {
                $misc->log_error($sqlMember);
            }
            $first_name = $recordSet2->fields('userdb_user_first_name');
            $last_name = $recordSet2->fields('userdb_user_last_name');
            $row[] = $last_name . ', ' . $first_name;

            $row[] = $recordSet->UserTimeStamp($recordSet->fields('feedbackdb_creation_date'), 'D M j G:i:s T Y');
            $row[] = $recordSet->fields('feedbackdb_priority');
            $row[] = $status_array[$recordSet->fields('feedbackdb_status')];
            $sqlMember = 'SELECT userdb_user_first_name, userdb_user_last_name
			FROM ' . $config['table_prefix'] . 'userdb
			WHERE userdb_id =' . $recordSet->fields('userdb_id');

            $recordSet2 = $conn->execute($sqlMember);
            if ($recordSet2 === false) {
                $misc->log_error($sqlMember);
            }
            $first_name = $recordSet2->fields('userdb_user_first_name');
            $last_name = $recordSet2->fields('userdb_user_last_name');
            $row[] = $last_name . ', ' . $first_name;
            $row[] = '<a class="btn btn-sm btn-primary mb-0" onclick="gotoLead(\'' . $config['baseurl'] . '/admin/index.php?action=' . $lead_edit_action . '&feedback_id=' . $lead_id . '\');" href="#" title="' . $lang['edit'] . '">
                <i class="fa-solid fa-pencil-alt"></i>
				</a>

				<a class="btn btn-sm btn-primary mb-0" onclick="gotoLead(\'' . $config['baseurl'] . '/admin/index.php?action=' . $lead_view_action . '&feedback_id=' . $lead_id . '\');" href="#" title="' . $lang['view'] . '">
                <i class="fa-solid fa-eye"></i>
				</a>

				<a class="btn btn-sm btn-danger mb-0" href="#"	onclick="deleteLead(' . $lead_id . ');" title="' . $lang['delete'] . '">
                <i class="fa-solid fa-trash"></i>
				</a> 
				';
            $output['data'][] = $row;
            $recordSet->MoveNext();
        }
        return json_encode($output);
    }

    /*
     * show_leads() Displays a list of available leads
     */
    public function show_leads($show_all_leads = false)
    {
        global $config, $conn, $lang, $misc;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        include_once $config['basepath'] . '/include/user.inc.php';
        $user = new user();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        if ($show_all_leads == true) {
            $login_status = $login->verify_priv('edit_all_leads');
            if ($login_status !== true) {
                return '<div class="error_text">' . $lang['access_denied'] . '</div>';
            }
        } else {
            $login_status = $login->verify_priv('Agent');
            if ($login_status !== true) {
                return '<div class="error_text">' . $lang['access_denied'] . '</div>';
            }
        }

        //Load Template File
        $page->load_page($config['admin_template_path'] . '/leadmanager.html');

        $display = '';

        if ($show_all_leads) {
            $page->replace_tag('show_agent_column', 'true');
        //$assigned_agent_visible = '{ "sWidth": "120px" }';
        } else {
            $page->replace_tag('show_agent_column', 'false');
            //$assigned_agent_visible = '{ "sWidth": "120px", "bVisible": false }';
        }


        //Get Counts
        if ($show_all_leads == true) {
            $sql2 = 'SELECT count(feedbackdb_id) as count 
					FROM ' . $config['table_prefix'] . 'feedbackdb 
					WHERE feedbackdb_status = \'1\'';
        } else {
            //
            $sql2 = 'SELECT count(feedbackdb_id) as count 
					FROM ' . $config['table_prefix'] . 'feedbackdb 
					WHERE userdb_id = ' . intval($_SESSION['userID']) . ' 
					AND feedbackdb_status = \'1\'';
        }
        $recordSet2 = $conn->Execute($sql2);
        if (!$recordSet2) {
            $misc->log_error($sql2);
        }
        $active_lead_count = $recordSet2->fields('count');
        if ($show_all_leads == true) {
            $sql2 = 'SELECT count(feedbackdb_id) as count 
					FROM ' . $config['table_prefix'] . 'feedbackdb 
					WHERE feedbackdb_status = \'0\'';
        } else {
            // WHERE userdb_id = '.intval($_SESSION['userID']).'
            $sql2 = 'SELECT count(feedbackdb_id) as count 
					FROM ' . $config['table_prefix'] . 'feedbackdb 
					WHERE userdb_id = ' . intval($_SESSION['userID']) . ' 
					AND feedbackdb_status = \'0\'';
        }
        $recordSet2 = $conn->Execute($sql2);
        if (!$recordSet2) {
            $misc->log_error($sql2);
        }
        $inactive_lead_count = $recordSet2->fields('count');

        //lead_inactive_count
        //lead_active_count
        $page->replace_tag('lead_inactive_count', $inactive_lead_count);
        $page->replace_tag('lead_active_count', $active_lead_count);
        //Feed Back Status Array

        $page->replace_permission_tags();
        $page->auto_replace_tags('', true);
        $display .= $page->return_page();
        return $display;
    }

    public function ajax_change_lead_notes()
    {
        global $config, $lang;

        if (isset($_POST['feedback_id']) && is_numeric($_POST['feedback_id']) && isset($_POST['notes'])) {
            include_once $config['basepath'] . '/include/lead_functions.inc.php';
            $lead_functions = new lead_functions();
            include_once $config['basepath'] . '/include/login.inc.php';
            $login = new login();
            $feedback_id = intval($_POST['feedback_id']);
            $notes = $_POST['notes'];
            $user_id = intval($_SESSION['userID']);
            $current_owner = $lead_functions->get_feedback_owner($feedback_id);
            //Make sure we have permissions.
            $permission = false;
            if ($_SESSION['userID'] !== $current_owner) {
                $permission = $login->verify_priv('edit_all_leads');
            } else {
                $permission = true;
            }
            if (!$permission) {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => $lang['listing_editor_permission_denied']]);
            }
            $lead_functions->set_notes($feedback_id, $notes);
            $lead_functions->set_feedback_mods($feedback_id, $user_id);
            header('Content-type: application/json');
            return json_encode(['error' => '0', 'lead_id' => $feedback_id]);
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['listing_editor_permission_denied']]);
        }
    }

    public function ajax_change_lead_priority()
    {
        global $lang;

        if (isset($_POST['feedback_id']) && is_numeric($_POST['feedback_id']) && isset($_POST['priority']) && in_array($_POST['priority'], ['Low', 'Normal', 'Urgent'])) {
            global $conn, $config, $misc;

            include_once $config['basepath'] . '/include/lead_functions.inc.php';
            $lead_functions = new lead_functions();
            include_once $config['basepath'] . '/include/login.inc.php';
            $login = new login();
            $feedback_id = intval($_POST['feedback_id']);
            $priority = $_POST['priority'];
            $user_id = $_SESSION['userID'];
            $current_owner = $lead_functions->get_feedback_owner($feedback_id);
            //Make sure we have permissions.
            $permission = false;
            if ($_SESSION['userID'] !== $current_owner) {
                $permission = $login->verify_priv('edit_all_leads');
            } else {
                $permission = true;
            }
            if (!$permission) {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => $lang['listing_editor_permission_denied']]);
            }
            $lead_functions->set_feedback_priority($feedback_id, $priority);
            $lead_functions->set_feedback_mods($feedback_id, $user_id);
            header('Content-type: application/json');
            return json_encode(['error' => '0', 'lead_id' => $feedback_id]);
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['listing_editor_permission_denied']]);
        }
    }

    public function ajax_change_lead_status()
    {
        global $config, $lang;

        if (isset($_POST['feedback_id']) && is_numeric($_POST['feedback_id']) && isset($_POST['status']) && is_numeric($_POST['status'])) {
            include_once $config['basepath'] . '/include/lead_functions.inc.php';
            $lead_functions = new lead_functions();
            include_once $config['basepath'] . '/include/login.inc.php';
            $login = new login();
            $feedback_id = intval($_POST['feedback_id']);
            $status = intval($_POST['status']);
            $user_id = intval($_SESSION['userID']);
            $current_owner = $lead_functions->get_feedback_owner($feedback_id);
            //Make sure we have permissions.
            $permission = false;
            if ($_SESSION['userID'] !== $current_owner) {
                $permission = $login->verify_priv('edit_all_leads');
            } else {
                $permission = true;
            }
            if (!$permission) {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => $lang['listing_editor_permission_denied']]);
            }
            $lead_functions->set_feedback_status($feedback_id, $status);
            $lead_functions->set_feedback_mods($feedback_id, $user_id);
            header('Content-type: application/json');
            return json_encode(['error' => '0', 'lead_id' => $feedback_id]);
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['listing_editor_permission_denied']]);
        }
    }

    public function ajax_change_lead_agent()
    {
        global $lang;

        if (isset($_POST['feedback_id']) && is_numeric($_POST['feedback_id']) && isset($_POST['user']) && is_numeric($_POST['user'])) {
            global $conn, $config, $misc;

            include_once $config['basepath'] . '/include/lead_functions.inc.php';
            $lead_functions = new lead_functions();
            include_once $config['basepath'] . '/include/login.inc.php';
            $login = new login();

            $feedback_id = intval($_POST['feedback_id']);
            $new_user_id = intval($_POST['user']);
            $user_id = intval($_SESSION['userID']);
            $current_owner = $lead_functions->get_feedback_owner($feedback_id);
            //Make sure we have permissions.
            $permission = false;
            if ($_SESSION['userID'] !== $current_owner) {
                $permission = $login->verify_priv('edit_all_leads');
            } else {
                $permission = true;
            }
            if (!$permission) {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => $lang['listing_editor_permission_denied']]);
            }
            $sql = 'UPDATE ' .  $config['table_prefix'] . 'feedbackdb
           		SET userdb_id = ' . $new_user_id . '
           		WHERE (feedbackdb_id = ' . $feedback_id . ')';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $sql = 'UPDATE ' .  $config['table_prefix'] . 'feedbackdbelements
           		SET userdb_id = ' . $new_user_id . '
           		WHERE (feedbackdb_id = ' . $feedback_id . ')';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $lead_functions->set_feedback_mods($feedback_id, $user_id);
            $lead_functions->send_agent_lead_assigned_notice($feedback_id, $user_id);
            header('Content-type: application/json');
            return json_encode(['error' => '0', 'lead_id' => $feedback_id]);
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['listing_editor_permission_denied']]);
        }
    }

    /*
     *  show_feedback_edit() Edit a specific lead
     */
    public function show_feedback_edit($edit, $all_lead_view = false)
    {
        global $conn, $config, $misc, $jscript, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        include_once $config['basepath'] . '/include/forms.inc.php';
        $forms = new forms();
        include_once $config['basepath'] . '/include/lead_functions.inc.php';
        $lead_functions = new lead_functions();
        include_once $config['basepath'] . '/include/listing.inc.php';
        $listing = new listing_pages();
        //include_once $config['basepath'].'/include/user.inc.php';
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        $display = '';

        $sql_edit = intval($edit);
        // first, grab the feedback's main info
        $sql = 'SELECT feedbackdb_id, feedbackdb_notes, userdb_id, feedbackdb_creation_date, feedbackdb_last_modified,
				listingdb_id, feedbackdb_member_userdb_id, feedbackdb_status
        		FROM ' .  $config['table_prefix'] . 'feedbackdb
        		WHERE (feedbackdb_id = ' . $sql_edit . ')';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $num_records = $recordSet->RecordCount();
        if ($num_records == 0) {
            die('Failed to access Lead DB');
        }
        while (!$recordSet->EOF) {
            // collect up the main DB's various fields
            $feedback_id = $recordSet->fields('feedbackdb_id');
            $listingsdb_id = $recordSet->fields('listingdb_id');
            $edit_notes = $recordSet->fields('feedbackdb_notes');
            $creation_date = $recordSet->UserTimeStamp($recordSet->fields('feedbackdb_creation_date'), 'm-d-Y g:ia');
            $user_id = $recordSet->fields('userdb_id');
            $member_id = $recordSet->fields('feedbackdb_member_userdb_id');
            $last_modified = $recordSet->UserTimeStamp($recordSet->fields('feedbackdb_last_modified'), 'm-d-Y g:ia');
            $feedbackdb_status = $recordSet->fields('feedbackdb_status');
            
            $recordSet->MoveNext();
        } // end while
        //Make sure we have permission.
        $permission = false;
        $user_id = intval($user_id);
        if (intval($_SESSION['userID']) !== $user_id) {
            if ($all_lead_view == true) {
                $permission = $login->verify_priv('edit_all_leads');
            }
        } else {
            $permission = true;
        }
        if (!$permission) {
            return $lang['listing_editor_permission_denied'];
        }
        // find the name of the agent listed as ID in $user_id
        $sql = 'SELECT userdb_user_first_name, userdb_user_last_name
                		FROM ' .  $config['table_prefix'] . 'userdb
                		WHERE (userdb_id = ' . intval($user_id) . ')';
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $agent_html = '';
        $agentfname = $recordSet->fields('userdb_user_first_name');
        $agentlname = $recordSet->fields('userdb_user_last_name');
        $edit_owner_name =  $agentfname . ' ' . $agentlname;

        $agent_html .= '<option value="' . $user_id . '">' . $edit_owner_name . '</option>';

        // fill list with names of all agents
        $sql = 'SELECT userdb_id, userdb_user_first_name, userdb_user_last_name
                		FROM ' .  $config['table_prefix'] . 'userdb
                		WHERE userdb_is_agent = \'yes\'
                		ORDER BY userdb_user_name';
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $misc->log_error($sql);
        }

        while (!$recordSet->EOF) {
            $agentfname = $recordSet->fields('userdb_user_first_name');
            $agentlname = $recordSet->fields('userdb_user_last_name');
            $agent_name =  $agentfname . ' ' . $agentlname;
            $agent_ID = $recordSet->fields('userdb_id');
            $agent_html .= '<option value="' . $agent_ID . '">' . $agent_name . '</option>';
            $recordSet->MoveNext();
        }
        $headline = '';
        $top_left = '';
        $top_right = '';
        $center = '';
        $bottom_left = '';
        $bottom_right = '';
        $misc_hold = '';

        $sql = 'SELECT feedbackformelements_field_name, feedbackdbelements_field_value, feedbackformelements_location,
						feedbackformelements_field_type, feedbackformelements_field_caption, feedbackformelements_default_text,
						feedbackformelements_field_elements, feedbackformelements_required, feedbackformelements_tool_tip
        		FROM ' .  $config['table_prefix'] . 'feedbackformelements
        		LEFT JOIN ' .  $config['table_prefix'] . 'feedbackdbelements
        		ON feedbackdbelements_field_name = feedbackformelements_field_name and feedbackdb_id = ' . $edit . '
        		ORDER BY feedbackformelements_rank';
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $misc->log_error($sql);
        }

        $field = '';
        while (!$recordSet->EOF) {
            $field_type = $recordSet->fields('feedbackformelements_field_type');
            $field_name = $recordSet->fields('feedbackformelements_field_name');
            $field_value = $recordSet->fields('feedbackdbelements_field_value');
            $field_caption = $recordSet->fields('feedbackformelements_field_caption');
            $default_text = $recordSet->fields('feedbackformelements_default_text');
            $field_elements = $recordSet->fields('feedbackformelements_field_elements');
            $required = $recordSet->fields('feedbackformelements_required');
            $tool_tip = $recordSet->fields('feedbackformelements_tool_tip');
            $location = $recordSet->fields('feedbackformelements_location');
            $field_length = '';
            // pass the data to the function
            $field = $forms->renderFormElement($field_type, $field_name, $field_value, $field_caption, $default_text, $required, $field_elements, $field_length, $tool_tip);

            switch ($location) {
                case 'headline':
                    $headline .= $field;
                    break;
                case 'top_left':
                    $top_left .= $field;
                    break;
                case 'top_right':
                    $top_right .= $field;
                    break;
                case 'center':
                    $center .= $field;
                    break;
                case 'bottom_left':
                    $bottom_left .= $field;
                    break;
                case 'bottom_right':
                    $bottom_right .= $field;
                    break;
                default:
                    $misc_hold .= $field;
                    break;
            }
            $recordSet->MoveNext();
        }



        //get the name of the last person who modified this
        $modified_by = $lead_functions->getFeedbackModData($sql_edit, 'modifiedby');
        $page->load_page($config['admin_template_path'] . '/leadmanager_edit.html');

        $page->page = str_replace('{headline}', $headline, $page->page);
        $page->page = str_replace('{top_left}', $top_left, $page->page);
        $page->page = str_replace('{top_right}', $top_right, $page->page);
        $page->page = str_replace('{center}', $center, $page->page);
        $page->page = str_replace('{bottom_left}', $bottom_left, $page->page);
        $page->page = str_replace('{bottom_right}', $bottom_right, $page->page);
        $page->page = str_replace('{misc_hold}', $misc_hold, $page->page);

        $page->page = $this->leadmanager_pagination_tags($all_lead_view, $page->page);

        $page->page = $lead_functions->get_leadmanager_priority($feedback_id, $page->page);


        if ($feedbackdb_status == 1) {
            $page->page = $page->cleanup_template_block('leadmanager_status', $page->page);
        } else {
            $page->page = $page->remove_template_block('leadmanager_status', $page->page);
        }

        //Agent Dropdown
        $sql = 'SELECT userdb_user_first_name,  userdb_user_last_name, userdb_id
        FROM ' . $config['table_prefix'] . "userdb
        WHERE userdb_is_agent = 'yes' ";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $agents = [];
        // get main users data
        while (!$recordSet->EOF) {
            $agent = $recordSet->fields('userdb_id');
            $first_name = $recordSet->fields('userdb_user_first_name');
            $last_name = $recordSet->fields('userdb_user_last_name');
            $user_name = $first_name . ' ' . $last_name;
            $agents[$agent] = $user_name;
            $recordSet->MoveNext();
        } //end while

        $html = $page->get_template_section('leadmanager_agent_block');
        $html = $page->form_options($agents, $user_id, $html);
        $page->replace_template_section('leadmanager_agent_block', $html);


        $page->replace_tag('agent_id', $user_id);
        $page->replace_tag('feedback_id', $feedback_id);
        $page->replace_tag('creationdate', $creation_date);
        $page->replace_tag('modifiedby', $modified_by);
        $page->replace_tag('lastmodified', $last_modified);

        $page->replace_tag('edit_notes', $edit_notes);

        $page->replace_listing_field_tags($listingsdb_id);

        $edit_listing_link = $config['baseurl'] . '/admin/index.php?action=edit_listing&edit=' . $listingsdb_id;
        $page->replace_tag('edit_listing_link', $edit_listing_link);
        $page->replace_user_field_tags($member_id);
        $page->replace_permission_tags();
        $page->auto_replace_tags('', true);

        $display .= $page->return_page();

        return $display;
    }

    public function ajax_save_feedback_status()
    {
        global $config;

        include_once $config['basepath'] . '/include/lead_functions.inc.php';
        $lead_functions = new lead_functions();
        if (isset($_POST['feedback_id']) && isset($_POST['status'])) {
            $feedback_id = intval($_POST['feedback_id']);
            $status = intval($_POST['status']);

            if ($status == 1) {
                echo 'Active';
            } else {
                echo 'Inactive';
            }

            $lead_functions->set_feedback_status($feedback_id, $status);
            $user_id = $_SESSION['userID'];

            $result = $lead_functions->set_feedback_mods($feedback_id, $user_id);
            if ($result === false) {
                echo 'Cannot Set Modifier';
            }
        }
    }

    public function ajax_delete_lead($lead_id)
    {
        global $conn, $config, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login->verify_priv('Admin');

        // delete a listing
        $sql_delete = intval($lead_id);
        // delete a listing
        $sql = 'DELETE FROM ' .  $config['table_prefix'] . 'feedbackdb 
				WHERE feedbackdb_id = ' . $sql_delete;
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $misc->log_error($sql);
        }
        // delete all the elements associated with a listing
        $sql = 'DELETE FROM ' .  $config['table_prefix'] . 'feedbackdbelements 
				WHERE feedbackdb_id = ' . $sql_delete;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        header('Content-type: application/json');
        return json_encode(['error' => 0, 'lead_id' => $sql_delete]);
    }

    public function ajax_save_feedback()
    {
        global $conn, $config, $misc, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        include_once $config['basepath'] . '/include/forms.inc.php';
        $forms = new forms();
        $login = new login();
        include_once $config['basepath'] . '/include/lead_functions.inc.php';
        $lead_functions = new lead_functions();
        $login->verify_priv('Admin');

        // validates the form
        $pass_the_form = $forms->validateForm('feedbackformelements');

        if ($pass_the_form != 'Yes') {
            // if we're not going to pass it, tell that they forgot to fill in one of the fields
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['required_fields_not_filled']]);
        }

        if ($pass_the_form == 'Yes') {
            if (isset($_POST['notes']) && $_POST['edit']) {
                $notes = $_POST['notes'];
                $feedbackdb_id = $_POST['edit'];
                $sql_notes = $misc->make_db_safe($notes);
                $sql_feedbackdb_id = intval($feedbackdb_id);
                $owner = $lead_functions->get_feedback_owner($sql_feedbackdb_id);
                $user_id = intval($_SESSION['userID']);
                //Make sure we have permission
                $login_status = $login->verify_priv('edit_all_leads');
                if ($login_status !== true) {
                    if ($user_id != $owner) {
                        header('Content-type: application/json');
                        return json_encode(['error' => '1', 'error_msg' => 'Permission Denied']);
                    }
                }

                // update the feedback data
                $sql = 'UPDATE ' .  $config['table_prefix'] . 'feedbackdb
	                		SET feedbackdb_notes = ' . $sql_notes . '
	                		WHERE feedbackdb_id = ' . $feedbackdb_id;
                $recordSet = $conn->Execute($sql);

                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $message = $lead_functions->updateFeedbackData($feedbackdb_id, $owner);
                if ($message == 'success') {
                    $result = $lead_functions->set_feedback_mods($feedbackdb_id, $user_id);
                    if ($result === false) {
                        echo 'Error Cannot set Mod data';
                    }
                    $misc->log_action('Lead number ' . $feedbackdb_id . ' updated');
                    header('Content-type: application/json');
                    return json_encode(['error' => '0', 'lead_id' => $feedbackdb_id]);
                } else {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => 'Misc Error']);
                } // end else
            } else {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => 'Incomplete Form Data']);
            }
        }
    }



    public function leadmanager_pagination_tags($all_leads = false, $template = '')
    {
        global $conn, $config, $misc;
        $page = new page_admin();

        $sql_queries = [];
        $active_query_string = '';

        if (isset($_GET['active']) && $_GET['active'] == true) {
            $sql_queries[] = 'feedbackdb_status = 1';
            $active_query_string = '&active=1';
        } elseif (isset($_GET['active']) && $_GET['active'] == false) {
            $sql_queries[] = 'feedbackdb_status = 0';
            $active_query_string = '&active=0';
        }
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
        }
        if (isset($_GET['feedback_id']) && !empty($_GET['feedback_id'])) {
            $feedback_id = intval($_GET['feedback_id']);

            //Set SQL Based ON view my or view all leads
            if ($all_leads != true) {
                $sql_queries[] = 'userdb_id = ' . intval($_SESSION['userID']);
            }
            $sql_queries_string = '';
            if (count($sql_queries) > 0) {
                $sql_queries_string = ' WHERE ' . implode(' AND ', $sql_queries);
            }
            $sql = 'SELECT feedbackdb_id
				FROM ' . $config['table_prefix'] . 'feedbackdb
				' . $sql_queries_string . '
				ORDER by feedbackdb_id';
            $recordSet = $conn->Execute($sql);

            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $num_rows = $recordSet->RecordCount();

            $loop = 0;
            while (!$recordSet->EOF) {
                $ID[$loop] =  $recordSet->fields('feedbackdb_id');
                if ($ID[$loop] ==  $feedback_id) {
                    $cur_ID = $loop;
                }
                $loop++;
                $recordSet->MoveNext();
            }
            $real_rows = $num_rows - 1;

            if ($feedback_id == $ID[0]) {
                $previous = $cur_ID;
            } else {
                $previous = $cur_ID - 1;
            }

            if ($cur_ID == $real_rows) {
                $next = $real_rows;
            } else {
                $next = $cur_ID + 1;
            }

            $first_page_action = 'index.php?action=' . $action . '&feedback_id=' . $ID[0] . '' . $active_query_string;
            $previous_page_action = 'index.php?action=' . $action . '&feedback_id=' . $ID[$previous] . '' . $active_query_string;
            $next_page_action = 'index.php?action=' . $action . '&feedback_id=' . $ID[$next] . '' . $active_query_string;
            $last_page_action = 'index.php?action=' . $action . '&feedback_id=' . $ID[$real_rows] . '' . $active_query_string;

            $template = $page->replace_tag_safe('leadmanager_pg_first_action', $first_page_action, $template);
            $template = $page->replace_tag_safe('leadmanager_pg_previous_action', $previous_page_action, $template);
            $template = $page->replace_tag_safe('leadmanager_pg_next_action', $next_page_action, $template);
            $template = $page->replace_tag_safe('leadmanager_pg_last_action', $last_page_action, $template);
        }
        return $template;
    }

    /*
     * form_edit() Arrange and edit the Lead Forms
     */
    public function form_edit()
    {
        global $config, $lang, $jscript, $conn;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_lead_template');

        //Load the Core Template
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        $page->load_page($config['admin_template_path'] . '/lead_template_editor.html');
        $display = '';
        $display1 = '';

        if ($security) {
            $display1 .= $this->delete_lead_field();
            $display .= $display1;


            $page->replace_tag('content', $display);


            $sql = 'SELECT feedbackformelements_field_name, feedbackformelements_field_caption, feedbackformelements_id
					FROM ' . $config['table_prefix'] . 'feedbackformelements';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            while (!$recordSet->EOF) {
                $all_fields[$recordSet->fields('feedbackformelements_field_name')] = $recordSet->fields('feedbackformelements_field_caption') . ' (' . $recordSet->fields('feedbackformelements_field_name') . ')';
                $recordSet->MoveNext();
            }
            $selected_field = '';
            if (isset($_GET['edit_field'])) {
                $selected_field = $_GET['edit_field'];
            }
            $page->replace_tag('content', $display);

            $html = $page->get_template_section('lead_template_editor_field_edit_block');
            $html = $page->form_options($all_fields, $selected_field, $html);
            $page->replace_template_section('lead_template_editor_field_edit_block', $html);


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

    public function edit_lead_template_qed()
    {
        global $config, $lang, $conn, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_lead_template');
        $display = '';

        if ($security === true) {
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/lead_template_editor_qed.html');
            $sections = explode(',', $config['template_lead_sections']);
            $sections[] = 'misc';
            foreach ($sections as $key => $section) {
                $section_name = trim($section);
                if ($section_name == 'misc') {
                    $sql_section_name = $misc->make_db_safe('');
                } else {
                    $sql_section_name = $misc->make_db_safe($section_name);
                }

                // Grab a list of field_names in the Database to Edit
                $sql = 'SELECT feedbackformelements_id, feedbackformelements_field_name,
						feedbackformelements_required,
						feedbackformelements_field_caption, feedbackformelements_rank
						FROM ' . $config['table_prefix'] . "feedbackformelements
						WHERE feedbackformelements_location = " . $sql_section_name . "
						ORDER BY feedbackformelements_rank";

                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $html_results = '';
                $html = $page->get_template_section($section_name . '_item_block');
                while (!$recordSet->EOF) {
                    $new_field_block = $html;
                    $fid = $recordSet->fields('feedbackformelements_id');
                    $f_rank = $recordSet->fields('feedbackformelements_rank');

                    // Get Caption from users selected language
                    if (!isset($_SESSION['users_lang'])) {
                        $caption = htmlentities($recordSet->fields('feedbackformelements_field_caption'));
                    } else {
                        $field_id = $misc->make_db_safe($fid);
                        $sql2 = 'SELECT feedbackformelements_field_caption
								FROM ' . $config['lang_table_prefix'] . "feedbackformelements
								WHERE feedbackformelements_id = $field_id";
                        $recordSet2 = $conn->Execute($sql2);
                        if (!$recordSet2) {
                            $misc->log_error($sql2);
                        }
                        $caption = htmlentities($recordSet2->fields('feedbackformelements_field_caption'));
                    }
                    $field_name = htmlentities($recordSet->fields('feedbackformelements_field_name'));

                    $new_field_block = str_replace('{field_rank}', $f_rank, $new_field_block);
                    $new_field_block = str_replace('{field_id}', $fid, $new_field_block);
                    $new_field_block = str_replace('{field_name}', $field_name, $new_field_block);
                    $new_field_block = str_replace('{field_caption}', $caption, $new_field_block);

                    if ($recordSet->fields('feedbackformelements_required') == 'Yes') {
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
    
    public function edit_form_field($edit_form_field_name)
    {
        global $conn, $config, $lang, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        $security = $login->verify_priv('edit_lead_template');

        if ($security === true) {
            $page->load_page($config['admin_template_path'] . '/lead_template_edit_field.html');
            $edit_form_field_name = $misc->make_db_safe($edit_form_field_name);

            $sql = 'SELECT * FROM ' . $config['table_prefix'] . 'feedbackformelements
					WHERE feedbackformelements_field_name = ' . $edit_form_field_name;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $id = $recordSet->fields('feedbackformelements_id');
            $field_type = $recordSet->fields('feedbackformelements_field_type');
            $field_name = $recordSet->fields('feedbackformelements_field_name');

            // Multi Lingual Support
            if (!isset($_SESSION['users_lang'])) {
                // Hold empty string for translation fields, as we are workgin with teh default lang
                $default_lang_field_caption = '';
                $default_lang_default_text = '';
                $default_lang_field_elements = '';

                $field_caption = $recordSet->fields('feedbackformelements_field_caption');
                $default_text = $recordSet->fields('feedbackformelements_default_text');
                $field_elements = $recordSet->fields('feedbackformelements_field_elements');
            } else {
                // Store default lang to show for tanslator
                $default_lang_field_caption = $recordSet->fields('feedbackformelements_field_caption');
                $default_lang_default_text = $recordSet->fields('feedbackformelements_default_text');
                $default_lang_field_elements = $recordSet->fields('feedbackformelements_field_elements');
                $default_lang_tool_tip = $recordSet->fields('feedbackformelements_tool_tip');
                $field_id = intval($recordSet->fields('feedbackformelements_id'));

                $lang_sql = 'SELECT feedbackformelements_field_caption,feedbackformelements_default_text,
								feedbackformelements_field_elements, feedbackformelements_search_label
							FROM ' . $config['lang_table_prefix'] . 'feedbackformelements
							WHERE feedbackformelements_id = ' . $field_id;
                $lang_recordSet = $conn->Execute($lang_sql);
                if (!$lang_recordSet) {
                    $misc->log_error($lang_sql);
                }
                $field_caption = $lang_recordSet->fields('feedbackformelements_field_caption');
                $default_text = $lang_recordSet->fields('feedbackformelements_default_text');
                $field_elements = $lang_recordSet->fields('feedbackformelements_field_elements');
            }

            $rank = $recordSet->fields('feedbackformelements_rank');
            $required = $recordSet->fields('feedbackformelements_required');
            $location = $recordSet->fields('feedbackformelements_location');
            $tool_tip = $recordSet->fields('feedbackformelements_tool_tip');

            $page->replace_tag_safe('field_id', $id);
            $page->replace_tag_safe('field_name', $field_name);
            $page->replace_tag_safe('field_caption', $field_caption);
            $page->replace_tag_safe('field_type', $field_type);
            $page->replace_tag_safe('required', $required);


            $locations = array();
            $sections = explode(',', $config['template_lead_sections']);
            foreach ($sections as $section) {
                $locations[$section] = $section;
            }

            $html = $page->get_template_section('location_block');
            $html = $page->form_options($locations, $location, $html);
            $page->replace_template_section('location_block', $html);

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

    
    

    public function ajax_get_form_field_info()
    {
        global $lang, $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_lead_template');
        $display = '';

        if ($security === true) {
            if (isset($_GET['edit_field'])) {
                $display .= $this->edit_form_field($_GET['edit_field']);
            }
        } else {
            $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return $display;
    }

    public function ajax_add_form_field()
    {
        global $lang, $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_lead_template');

        if ($security) {
            $display = $this->add_form_template_field();
        } else {
            $display = '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return $display;
    }

    public function ajax_save_form_field_order()
    {
        global $config, $lang ,$conn, $misc;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Verify User is an Admin
        $security = $login->verify_priv('edit_lead_template');

        $display = '';
        if ($security === true) {
            if (isset($_POST['section']) && isset($_POST['fields'])) {
                //Verify Section is valid
                $valid_section = explode(',', $config["template_lead_sections"]);
                $valid_section[] = 'misc';
                if (in_array($_POST['section'], $valid_section)) {
                    if ($_POST['section'] == "misc") {
                        $section = "";
                    } else {
                        $section = $_POST['section'];
                    }
                } else {
                    return json_encode(['error' => true, 'error_msg' =>  $lang['invalid_template_section']]);
                }
                $sql_section = $misc->make_db_safe($section);
                foreach ($_POST['fields'] as $rank => $field_name) {
                    //empty locations are skipped
                    if (!empty($field_name)) {
                        $sql_field_name = $misc->make_db_safe($field_name);
                        $sql_rank = intval($rank);

                        $sql = 'UPDATE ' . $config['table_prefix'] . "feedbackformelements
                        SET feedbackformelements_location = " . $sql_section . ",
                            feedbackformelements_rank = " . $sql_rank . "
                        WHERE feedbackformelements_field_name = " . $sql_field_name;
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

    public function ajax_insert_form_field()
    {
        global $lang, $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_lead_template');

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
                $location = $misc->make_db_safe($_POST['location']);
                $tool_tip = $misc->make_db_safe($_POST['tool_tip']);

                $id_rand = rand(0, 999999);

                $sql = 'INSERT INTO ' . $config['table_prefix'] . 'feedbackformelements
						(feedbackformelements_field_type, feedbackformelements_field_name, feedbackformelements_field_caption,
						feedbackformelements_default_text, feedbackformelements_field_elements, feedbackformelements_rank,
						feedbackformelements_required, feedbackformelements_location, feedbackformelements_tool_tip)
						VALUES (' . $field_type . ',' . $id_rand . ',' . $field_caption . ',' . $default_text . ',' . $field_elements . ', ' . $rank . ', ' . $required . ', ' . $location . ', ' . $tool_tip . ')';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                // Now we need to get the field ID
                $sql = 'SELECT feedbackformelements_id 
						FROM ' . $config['table_prefix'] . 'feedbackformelements
						WHERE feedbackformelements_field_name = ' . $id_rand;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $feedbackformelements_id = $recordSet->fields('feedbackformelements_id');
                // Set Real Name
                $sql = 'UPDATE ' . $config['table_prefix'] . 'feedbackformelements 
						SET feedbackformelements_field_name = ' . $field_name . '
						WHERE feedbackformelements_field_name = ' . $id_rand;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                // We should now add a blank field for each lead that already exist.
                $sql = 'SELECT feedbackdb_id, userdb_id 
						FROM ' . $config['table_prefix'] . 'feedbackdb';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $id = [];
                $user = [];
                while (!$recordSet->EOF) {
                    $id[] = $recordSet->fields('feedbackdb_id');
                    $user[] = $recordSet->fields('userdb_id');
                    $recordSet->MoveNext();
                }
                $count = count($id);
                $x = 0;
                while ($x < $count) {
                    $sql = 'INSERT INTO ' . $config['table_prefix'] . 'feedbackdbelements
							(feedbackdbelements_field_name, feedbackdb_id,userdb_id,feedbackdbelements_field_value)
							VALUES (' . $field_name . ',' . $id[$x] . ',' . $user[$x] . ', \'\')';
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $x++;
                }
            }
            header('Content-type: application/json');
            return json_encode(['error' => '0', 'field_id' => $feedbackformelements_id]);
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' =>  $lang['access_denied']]);
        }
    }

    public function ajax_update_lead_field()
    {
        global $lang, $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $display = '';
        $security = $login->verify_priv('edit_lead_template');
        if ($security === true) {
            global $conn, $misc;

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
                $rank = $misc->make_db_safe($_POST['rank']);
                $location = $misc->make_db_safe($_POST['location']);
                $tool_tip = $misc->make_db_safe($_POST['tool_tip']);

                $sql = 'UPDATE ' . $config['table_prefix'] . 'feedbackformelements
							SET feedbackformelements_field_type = ' . $field_type . ', feedbackformelements_field_name = ' . $field_name . ',
							feedbackformelements_rank = ' . $rank . ', feedbackformelements_required = ' . $required . ',
							feedbackformelements_location = ' . $location . ', feedbackformelements_tool_tip = ' . $tool_tip . '
							WHERE feedbackformelements_id = ' . $id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                // Update Current language
                if (!isset($_SESSION['users_lang'])) {
                    $lang_sql = 'UPDATE  ' . $config['table_prefix'] . 'feedbackformelements
									SET feedbackformelements_field_caption = ' . $field_caption . ', feedbackformelements_field_elements = ' . $field_elements . ', feedbackformelements_default_text = ' . $default_text . ',
									feedbackformelements_tool_tip = ' . $tool_tip . '
									WHERE feedbackformelements_id = ' . $id;
                    $lang_recordSet = $conn->Execute($lang_sql);
                    if (!$lang_recordSet) {
                        $misc->log_error($lang_sql);
                    }
                } else {
                    $lang_sql = 'DELETE FROM  ' . $config['lang_table_prefix'] . 'feedbackformelements
									WHERE feedbackformelements_id = ' . $id;
                    $lang_recordSet = $conn->Execute($lang_sql);
                    if (!$lang_recordSet) {
                        $misc->log_error($lang_sql);
                    }
                    $lang_sql = 'INSERT INTO ' . $config['lang_table_prefix'] . 'feedbackformelements
									(feedbackformelements_id, feedbackformelements_field_caption, feedbackformelements_default_text,
									feedbackformelements_field_elements, feedbackformelements_tool_tip)
									VALUES (' . $id . ', ' . $field_caption . ',' . $default_text . ',' . $field_elements . ',' . $tool_tip . ')';
                    $lang_recordSet = $conn->Execute($lang_sql);
                    if (!$lang_recordSet) {
                        $misc->log_error($lang_sql);
                    }
                }
                // Check if field name changed, if it as update all feedbackdbelement tables
                if ($update_field_name) {
                    $lang_sql = 'UPDATE  ' . $config['table_prefix'] . 'feedbackdbelements
									SET feedbackdbelements_field_name = ' . $field_name . '
									WHERE feedbackdbelements_field_name = ' . $old_field_name;
                    $lang_recordSet = $conn->Execute($lang_sql);
                    if (!$lang_recordSet) {
                        $misc->log_error($lang_sql);
                    }
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'field_id' =>  $id]);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' =>  $lang['access_denied']]);
        }
        return $display;
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

    public function add_form_template_field()
    {
        global $config, $lang, $jscript;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        $page->load_page($config['admin_template_path'] . '/lead_template_add_field.html');
        
        $security = $login->verify_priv('edit_lead_template');

        if ($security === true) {
            $sections = explode(',', $config['template_lead_sections']);
            $html = $page->get_template_section('location_block');
            $html = $page->form_options($sections, "", $html);
            $page->replace_template_section('location_block', $html);



            $page->replace_tag('application_status_text', '');
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        }
        return '<div class="error_text">' . $lang['access_denied'] . '</div>';
    }

    public function addlead_create_lead()
    {
        global $config, $conn, $misc;

        include_once $config['basepath'] . '/include/listing.inc.php';
        $listing_pages = new listing_pages();
        include_once $config['basepath'] . '/include/lead_functions.inc.php';
        $lead_functions = new lead_functions();

        if (isset($_POST['listing_id'])) {
            $listingID = intval($_POST['listing_id']);
            $memberID = intval($_POST['member_id']);
            $agent_id = $listing_pages->get_listing_agent_value('userdb_id', $listingID);
        } else {
            $listingID = 0;
            $agent_id = intval($_POST['agent_id']);
            $memberID = intval($_POST['member_id']);
        }

        $notes = $_POST['notes'];
        if ($agent_id > 0 && $memberID > 0) {
            //START - Determine AgentID Based on listingID
            //Check to see if agent's notifications should be redirected to the floor agent.
            $sql = 'SELECT userdb_send_notifications_to_floor FROM ' . $config['table_prefix'] . 'userdb WHERE userdb_id = ' . $agent_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $userdb_send_notifications_to_floor = $recordSet->fields('userdb_send_notifications_to_floor');
            if ($userdb_send_notifications_to_floor == 1) {
                //Get Floor Agent ID
                $sql = 'SELECT controlpanel_floor_agent, controlpanel_floor_agent_last FROM ' . $config['table_prefix_no_lang'] . 'controlpanel';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $controlpanel_floor_agent = $recordSet->fields('controlpanel_floor_agent');
                $controlpanel_floor_agent_last = $recordSet->fields('controlpanel_floor_agent_last');
                $floor_agents = explode(',', $controlpanel_floor_agent);
                if (count($floor_agents) > 1) {
                    $update_floor = false;
                    //Determine Last Floor Agent
                    if ($controlpanel_floor_agent_last > 1) {
                        $first_agent_id = $floor_agents[0];
                        $use_next = false;
                        $found_agent = false;
                        foreach ($floor_agents as $key => $floor_id) {
                            if (!$use_next) {
                                if ($floor_id == $controlpanel_floor_agent_last) {
                                    $use_next = true;
                                }
                            } else {
                                $new_agent_id = $floor_id;
                                if ($new_agent_id > 1) {
                                    $found_agent = true;
                                    $update_floor = true;
                                    $agent_id = $new_agent_id;
                                }
                            }
                        }
                        if (!$found_agent) {
                            //No Last Agent set so use the first in list
                            $new_agent_id = $floor_agents[0];
                            if ($new_agent_id > 1) {
                                $update_floor = true;
                                $agent_id = $new_agent_id;
                            }
                        }
                    } else {
                        //No Last Agent set so use the first in list
                        $new_agent_id = $floor_agents[0];
                        if ($new_agent_id > 1) {
                            $update_floor = true;
                            $agent_id = $new_agent_id;
                        }
                    }
                    if ($update_floor) {
                        $sql = 'UPDATE ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_floor_agent_last = ' . $agent_id;
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                    }
                } else {
                    $new_agent_id = $floor_agents[0];
                    if ($new_agent_id > 1) {
                        $agent_id = $new_agent_id;
                    }
                }
            }
            //END Agent ID Detection
            $sql_notes = $misc->make_db_safe($notes);
            $sql = 'INSERT INTO ' . $config['table_prefix'] . "feedbackdb (feedbackdb_notes, userdb_id, listingdb_id, feedbackdb_creation_date, feedbackdb_last_modified, feedbackdb_status, feedbackdb_priority,feedbackdb_member_userdb_id )
			                		VALUES ($sql_notes, $agent_id, $listingID, " . $conn->DBTimeStamp(time()) . ',' . $conn->DBTimeStamp(time()) . ", 1, 'Normal',$memberID) ";
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $new_feedback_id = $conn->Insert_ID(); // this is the new feedback's ID number
            // now that's taken care of, it's time to insert all the rest
            // of the variables into the database
            $message = $lead_functions->updateFeedbackData($new_feedback_id, $agent_id, $listingID);
            //$sql_user = $misc->make_db_safe($agent_id);
            if ($message == 'success') {
                //get the Agent's full name & email
                $sql = 'SELECT userdb_user_first_name, userdb_user_last_name, userdb_emailaddress
										FROM ' . $config['table_prefix'] . "userdb
										WHERE userdb_id = $agent_id";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $user_name = $recordSet->fields('userdb_user_first_name') . ' ' . $recordSet->fields('userdb_user_last_name');
                $listing_emailAddress = $recordSet->fields('userdb_emailaddress');
                // Report that Feedback has been sent to the AGENT
                //$output .= '<div id="feedback_sent_message">'.$lang['your_feedback_has_been_sent'].' '.$user_name.'</div>';
                $misc->log_action("Created feedback $new_feedback_id for $user_name");
                $lead_functions->send_agent_feedback_notice($new_feedback_id);
                if (isset($_POST['sendmember_email']) && $_POST['sendmember_email'] == 1) {
                    $lead_functions->send_user_feedback_notice($new_feedback_id);
                }
                include_once $config['basepath'] . '/include/hooks.inc.php';
                $hooks = new hooks();
                $hooks->load('after_new_lead', $new_feedback_id);
                return ['error' => false, 'lead_id' => $new_feedback_id];
            } else {
                return ['error' => true];
                //$output .= '<p>There\'s been a problem -- please contact the site administrator</p>';
            } // end else
        }
    }

    public function delete_lead_field()
    {
        global $lang, $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_lead_template');
        if ($security === true) {
            global  $conn, $misc;

            if (isset($_GET['delete_field']) && !isset($_POST['lang_change'])) {
                $field_name = $misc->make_db_safe($_GET['delete_field']);
                $sql = 'SELECT feedbackformelements_id FROM ' . $config['table_prefix'] . 'feedbackformelements
						WHERE feedbackformelements_field_name = ' . $field_name;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                // Delete All Translation for this field.
                $configured_langs = explode(',', $config['configured_langs']);
                while (!$recordSet->EOF) {
                    $feedbackformelements_id = $recordSet->fields('feedbackformelements_id');
                    foreach ($configured_langs as $configured_lang) {
                        $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . $configured_lang . '_feedbackformelements
								WHERE feedbackformelements_id = ' . $feedbackformelements_id;
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                    }
                }
                // Cleanup any feedbackdbelements entries from this field.
                foreach ($configured_langs as $configured_lang) {
                    $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . $configured_lang . '_feedbackdbelements
							WHERE feedbackdbelements_field_name = ' . $field_name;
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                }
            }
        } else {
            return '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
    }
}
