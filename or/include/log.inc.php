<?php


class log
{
    public function ajax_viewlog_datatable()
    {
        global $config, $conn, $misc;
        header('Content-type: application/json');
        $aColumns = ['activitylog_id', 'activitylog_log_date', 'activitylog_ip_address', 'userdb_id', 'activitylog_action'];

        //Do Search to get total record count, no need pass in soring information
        $limit = 0;
        $offset = 0;
        $sql = 'SELECT count(*) as mycount FROM ' . $config['table_prefix'] . 'activitylog';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $iTotal = $recordSet->fields('mycount');
        if (isset($_GET['start']) && $_GET['length'] != '-1') {
            $limit = intval($_GET['length']);
            $offset = intval($_GET['start']);
            //          $sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".mysql_real_escape_string( $_GET['iDisplayLength'] );
        }
        $sortby = [];
        $sorttype = [];
        //Deal with sorting
        if (isset($_GET['order'])) {
            for ($i = 0; $i < intval($_GET['order']); $i++) {
                $sortby[$i] = $aColumns[intval($_GET['order'][$i]['column'])];
                $sorttype[$i] = strtoupper($_GET['order'][$i]['dir']);
            }
        }
        $sWhere = '';
        if (isset($_GET['search']['value']) && $_GET['search']['value'] != '') {
            $sql_sSearch = $conn->qstr('%' . $_GET['search']['value'] . '%');

            $sWhere = 'WHERE (';
            for ($i = 0; $i < count($aColumns); $i++) {
                if ($aColumns[$i] == 'userdb_id') {
                    $sWhere .= '`' . $aColumns[$i] . '` IN (
														SELECT userdb_id 
														FROM ' . $config['table_prefix'] . "userdb 
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
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_GET['columns'][$i]['searchable']) && $_GET['columns'][$i]['searchable'] == 'true' && in_array('sSearch_' . $i, $_GET) && $_GET['sSearch_' . $i] != '') {
                $ARGS[$aColumns[$i]] = $_GET['sSearch_' . $i];
            }
        }
        $where = '';
        if (!empty($ARGS)) {
            if ($sWhere == '') {
                $where .= ' WHERE ';
            }
            foreach ($ARGS as $f => $k) {
                if ($where != ' WHERE ') {
                    $where .= ' AND ';
                }
                $where .= $conn->addQ($f) . ' LIKE \'%' . $conn->addQ($k) . '%\'';
            }
        }
        $sort = '';
        if (!empty($sortby)) {
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
        $sql = 'SELECT count(*) as filteredcount 
				FROM ' . $config['table_prefix'] . "activitylog $sWhere $where";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $iFilteredTotal = $recordSet->fields('filteredcount');
        $sql = 'SELECT * FROM ' . $config['table_prefix'] . "activitylog $sWhere $where $sort $limitstr ";
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
        while (!$recordSet->EOF) {
            $row = [];
            $row[] = $recordSet->fields('activitylog_id');
            $row[] = $recordSet->UserTimeStamp($recordSet->fields('activitylog_log_date'), 'D M j G:i:s T Y');
            $row[] = $recordSet->fields('activitylog_ip_address');
            $sqlUser = 'SELECT userdb_user_first_name, userdb_user_last_name
						FROM ' . $config['table_prefix'] . 'userdb
						WHERE userdb_id =' . $recordSet->fields('userdb_id');
            $recordSet2 = $conn->execute($sqlUser);
            if (!$recordSet2) {
                $misc->log_error($sqlUser);
            }
            $first_name = $recordSet2->fields('userdb_user_first_name');
            $last_name = $recordSet2->fields('userdb_user_last_name');
            $row[] = $last_name . ', ' . $first_name;
            $row[] = $recordSet->fields('activitylog_action');
            $output['data'][] = $row;
            $recordSet->MoveNext();
        }
        return json_encode($output);
    }

    public function view($app_status_text = '')
    {
        global $config;
        // Verify User is an Admin
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('canViewLogs');
        $display = '';
        if ($security) {
            //Load the Core Template
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            $page->load_page($config['admin_template_path'] . '/view_log.html');
            $page->replace_tag('application_status_text', $app_status_text);
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            return $page->return_page();
        }
        return $display;
    }

    public function clear_log()
    {
        global $conn, $config, $lang, $misc;

        $display = '';
        //$display .= "<h3>$lang[log_delete]</h3>";
        // Check for Admin privs before doing anything
        if ($_SESSION['admin_privs'] == 'yes') {
            // find the number of log items
            $sql = 'TRUNCATE TABLE ' . $config['table_prefix'] . 'activitylog';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
                $display .= $this->view($lang['log_clear_error']);
            } else {
                $misc->log_action($lang['log_reset']);
                $display .= $this->view($lang['log_cleared']);
            }
        } else {
            $display .= $this->view($lang['clear_log_need_privs']);
        }
        return $display;
    }

    public function ajax_export_logs()
    {
        global $conn, $lang, $config, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('canViewLogs');
        $display = '';

        if ($security === true) {
            $sql = 'SELECT * 
					FROM ' . $config['table_prefix'] . 'activitylog';
            $recordSet = $conn->Execute($sql);

            if (!$recordSet) {
                $misc->log_error($sql);
                return 'DB error';
            }

            $headings = ['activitylog_id', 'activitylog_log_date', 'userdb_id', 'activitylog_action', 'activitylog_ip_address'];

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="activitylog.csv";');

            $fh = fopen('php://output', 'w');

            fputcsv($fh, $headings);


            while (!$recordSet->EOF) {
                fputcsv(
                    $fh,
                    [
                        $recordSet->fields('activitylog_id'),
                        $recordSet->fields('activitylog_log_date'),
                        $recordSet->fields('userdb_id'),
                        $recordSet->fields('activitylog_action'),
                        $recordSet->fields('activitylog_ip_address'),
                    ]
                );
                $recordSet->MoveNext();
            }

            fclose($fh);

            $csv = ob_get_clean();

            return ($csv);
            exit;
        } else {
            return 'Unauthorized access';
        }
    }
}
