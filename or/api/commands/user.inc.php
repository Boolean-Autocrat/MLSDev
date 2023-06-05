<?php

/**
 * This is the User  API, it contains all api calls for creating and retrieving user data.
 *
 * @package Open-Realty
 * @subpackage API
 **/
class user_api
{
    //password field removed from list.
    protected $OR_INT_FIELDS = ['userdb_id', 'userdb_user_name', 'userdb_emailaddress', 'userdb_user_first_name', 'userdb_user_last_name', 'userdb_comments', 'userdb_is_admin', 'userdb_can_edit_site_config', 'userdb_can_edit_member_template', 'userdb_can_edit_agent_template', 'userdb_can_edit_listing_template', 'userdb_creation_date', 'userdb_can_feature_listings', 'userdb_can_view_logs', 'userdb_last_modified', 'userdb_hit_count', 'userdb_can_moderate', 'userdb_can_edit_pages', 'userdb_can_have_vtours', 'userdb_is_agent', 'userdb_active', 'userdb_limit_listings', 'userdb_can_edit_expiration', 'userdb_can_export_listings', 'userdb_can_edit_all_users', 'userdb_can_edit_all_listings', 'userdb_can_edit_property_classes', 'userdb_can_have_files', 'userdb_can_have_user_files', 'userdb_blog_user_type', 'userdb_rank', 'userdb_featuredlistinglimit', 'userdb_email_verified', 'userdb_can_manage_addons', 'userdb_can_edit_all_leads', 'userdb_can_edit_lead_template', 'userdb_send_notifications_to_floor'];
    /**
     * This API Command searches the users
     * @param array $data $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['parameters'] - This is a REQUIRED array of the fields and the values we are searching for.</li>
     *      <li>$data['sortby'] - This is an optional array of fields to sort by.</li>
     **     <li>$data['sorttype'] - This is an optional array of sort types (ASC/DESC) to sort the sortby fields by.</li>
     *      <li>$data['offset'] - This is an optional integer of the number of users to offset the search by. To use offset you must set a limit.</li>
     *      <li>$data['limit'] - This is an optional integer of the number of users to limit the search by. 0 or unset will return all users.</li>
     *      <li>$data['count_only'] - This is an optional integer flag 1/0, where 1 returns a record count only, defaults to 0 if not set. Usefull if doing limit/offset search for pagenation to get the inital full record count..</li>
     *  </ul>
     * @return array  - Array retruned will contain the following paramaters.
     *  [error] = true/FASLE
     *  [user_count] = Number of records found, if using a limit this is only the number of records that match your current limit/offset results.
     *  [users] = Array of user_ids.
     *  [info] = The info array contains benchmark information on the search, including process_time, query_time, and total_time
     *  [sortby] = Contains an array of fields that were used to sort the search results. Note if you are doing a CountONly search the sort is not actually used as this would just slow down the query.
     *  [sorttype] = Contains an array of the sorttype (ASC/DESC) used on the sortby fields.
     *   ['limit'] - INT - The numeric limit being imposed on the results.
     *   ['resource'] - TEXT - The resource the search was made against, 'agent' or 'member'
     **/
    public function search($data)
    {
        $DEBUG_SQL = false;
        global $conn, $lapi, $config, $lang, $db_type, $misc;

        $start_time = $misc->getmicrotime();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        extract($data, EXTR_SKIP || EXTR_REFS, '');

        //Check that required settings were passed
        if (!isset($resource) || $resource != 'agent' && $resource != 'member') {
            return ['error' => true, 'error_msg' => 'resource: correct_parameter_not_passed'];
        }
        if (!isset($parameters) || !is_array($parameters)) {
            return ['error' => true, 'error_msg' => 'parameters: correct_parameter_not_passed'];
        }
        if (isset($sortby) && !is_array($sortby)) {
            return ['error' => true, 'error_msg' => 'sortby: correct_parameter_not_passed'];
        }
        if (isset($sorttype) && !is_array($sorttype)) {
            return ['error' => true, 'error_msg' => 'sorttype: correct_parameter_not_passed'];
        }
        if (isset($offset) && !is_numeric($offset)) {
            return ['error' => true, 'error_msg' => 'offset: correct_parameter_not_passed'];
        }

        if (isset($limit) && !is_numeric($limit)) {
            return ['error' => true, 'error_msg' => 'limit: correct_parameter_not_passed'];
        } elseif (!isset($limit)) {
            $limit = 0;
        }

        if (isset($count_only) && $count_only == 1) {
            $count_only = true;
        } else {
            $count_only = false;
        }
        $searchresultSQL = '';
        // Set Default Search Options
        $imageonly = false;

        $tablelist = [];
        $tablelist_fullname = [];

        $login_status = $login->verify_priv('edit_all_users');
        $string_where_clause = '';
        $string_where_clause_nosort = '';
        if ($login_status !== true || !isset($parameters['userdb_active'])) {
            //If we are not an agent only show active agents, or if user did not specify show only actives by default.
            $parameters['userdb_active'] = 'yes';
        }

        //check to see if the admin is set to be shown on the Agent list
        $sql = 'SELECT controlpanel_show_admin_on_agent_list
				FROM ' . $config['table_prefix_no_lang'] . 'controlpanel';
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->user->search', 'log_message' => 'DB Error: ' . $error]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
        } else {
            $display_admin = $recordSet->fields('controlpanel_show_admin_on_agent_list');
        }
        $admin_not_agent = false;
        if (isset($_SESSION['userID']) && $_SESSION['userID'] !== '') {
            //check to see if the present user is an Admin but not an Agent
            $is_admin = $misc->get_admin_status($_SESSION['userID']);
            $is_agent = $misc->get_agent_status($_SESSION['userID']);
            if ($is_agent === false && $is_admin === true && $_SESSION['userID'] != 1) {
                $admin_not_agent = true;
            }
        } else {
            $_SESSION['userID'] = '';
        }
        //Loop through search paramaters
        foreach ($parameters as $k => $v) {
            //Search users By Agent
            if ($k == 'userdb_active') {
                if ($string_where_clause != '') {
                    $string_where_clause .= ' AND ';
                }
                if ($string_where_clause_nosort != '') {
                    $string_where_clause_nosort .= ' AND ';
                }
                if ($v == 'no') {
                    $string_where_clause .= '(' . $config['table_prefix'] . 'userdb.userdb_active = \'no\')';
                    $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'userdb.userdb_active = \'no\')';
                } elseif ($v == 'any') {
                    $string_where_clause .= '(' . $config['table_prefix'] . 'userdb.userdb_active = \'yes\' or ' . $config['table_prefix'] . 'userdb.userdb_active = \'no\')';
                    $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'userdb.userdb_active = \'yes\' or ' . $config['table_prefix'] . 'userdb.userdb_active = \'no\')';
                } else {
                    $string_where_clause .= '(' . $config['table_prefix'] . 'userdb.userdb_active = \'yes\')';
                    $string_where_clause_nosort  .= '(' . $config['table_prefix'] . 'userdb.userdb_active = \'yes\')';
                }
            } elseif ($k == 'userdb_id') {
                $userdb_id = explode(',', $v);
                $i = 0;
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
                foreach ($userdb_id as $id) {
                    $id = intval($id);
                    if ($i == 0) {
                        $searchresultSQL .= '((' . $config['table_prefix'] . 'userdb.userdb_id = ' . $id . ')';
                    } else {
                        $searchresultSQL .= ' OR (' . $config['table_prefix'] . 'userdb.userdb_id = ' . $id . ')';
                    }
                    $i++;
                }
                $searchresultSQL .= ')';
            } elseif ($k == 'imagesOnly') {
                // Grab only users with images if that is what we need.
                if ($v == 'yes') {
                    $imageonly = true;
                }
            } elseif ($k == 'userdb_user_name' || $k == 'userdb_emailaddress') {
                $safe_v = '%' . $conn->addQ($v) . '%';
                if ($string_where_clause != '') {
                    $string_where_clause .= ' AND ';
                }
                if ($string_where_clause_nosort != '') {
                    $string_where_clause_nosort .= ' AND ';
                }
                $string_where_clause .= '(' . $config['table_prefix'] . 'userdb.' . $k . ' LIKE \'' . $safe_v . '\')';
                $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'userdb.' . $k . ' LIKE \'' . $safe_v . '\')';
            } elseif ($k == 'userdb_is_admin' || $k == 'userdb_is_agent') {
                $safe_v = $misc->make_db_safe($v);
                if ($string_where_clause != '') {
                    $string_where_clause .= ' AND ';
                }
                if ($string_where_clause_nosort != '') {
                    $string_where_clause_nosort .= ' AND ';
                }
                if ($admin_not_agent === true && $display_admin == 0) {
                    $string_where_clause .= '(' . $config['table_prefix'] . 'userdb.' . $k . ' = ' . $safe_v . ')';
                    $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'userdb.' . $k . ' = ' . $safe_v . ')';
                } elseif ($display_admin == 1 || $_SESSION['userID'] == 1) {
                    $string_where_clause .= '(' . $config['table_prefix'] . 'userdb.' . $k . ' = ' . $safe_v . ' OR ' . $config['table_prefix'] . 'userdb.userdb_id = \'1\')';
                    $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'userdb.' . $k . ' = ' . $safe_v . ' OR ' . $config['table_prefix'] . 'userdb.userdb_id = \'1\')';
                    ;
                } else {
                    $string_where_clause .= '(' . $config['table_prefix'] . 'userdb.' . $k . ' = ' . $safe_v . ')';
                    $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'userdb.' . $k . ' = ' . $safe_v . ')';
                }
            } elseif ($k == 'user_last_modified_equal') {
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
                $safe_v = $conn->DBTimeStamp($v);
                $searchresultSQL .= ' userdb_last_modified = ' . $safe_v;
            //userdb_last_modified
            } elseif ($k == 'user_last_modified_greater') {
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
                $safe_v = $conn->DBTimeStamp($v);
                $searchresultSQL .= ' userdb_last_modified > ' . $safe_v;
            //userdb_last_modified
            } elseif ($k == 'user_last_modified_less') {
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
                $safe_v = $conn->DBTimeStamp($v);
                $searchresultSQL .= ' userdb_last_modified < ' . $safe_v;
            //userdb_last_modified
            } elseif ($k == 'userdb_creation_date_equal') {
                //$v = intval($v);
                if ($v > 0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $safe_v = $conn->DBDate($v);
                    $searchresultSQL .= ' userdb_creation_date = ' . $safe_v;
                    //userdb_last_modified
                }
            } elseif ($k == 'userdb_creation_date_greater') {
                if ($v > 0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $safe_v = $conn->DBDate($v);
                    $searchresultSQL .= ' userdb_creation_date > ' . $safe_v;
                    //userdb_last_modified
                }
            } elseif ($k == 'userdb_creation_date_less') {
                //$v = intval($v);
                if ($v > 0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $safe_v = $conn->DBDate($v);
                    $searchresultSQL .= ' userdb_creation_date < ' . $safe_v;
                    //userdb_last_modified
                }
            } elseif ($k == 'userdb_creation_date_equal_days') {
                $v = intval($v);
                if ($v > 0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $time = mktime(0, 0, 0, date('m'), date('d') - intval($v), date('Y'));
                    $safe_v = $conn->DBTimeStamp($time);
                    $searchresultSQL .= ' userdb_creation_date = ' . $safe_v;
                    //userdb_last_modified
                }
            } elseif ($k == 'userdb_creation_date_greater_days') {
                $v = intval($v);
                if ($v > 0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $time = mktime(0, 0, 0, date('m'), date('d') - intval($v), date('Y'));
                    $safe_v = $conn->DBTimeStamp($time);
                    ;
                    $searchresultSQL .= ' userdb_creation_date > ' . $safe_v;
                }
            //userdb_last_modified
            } elseif ($k == 'userdb_creation_date_less_days') {
                $v = intval($v);
                if ($v > 0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $time = mktime(0, 0, 0, date('m'), date('d') - intval($v), date('Y'));
                    $safe_v = $conn->DBTimeStamp($time);
                    $searchresultSQL .= ' userdb_creation_date < ' . $safe_v;
                }
            //userdb_last_modified
            } elseif (
                $v != '' && $k != 'userdb_id' && $k != 'cur_page' && $k != 'action' && $k != 'PHPSESSID'
                && $k != 'sortby' && $k != 'sorttype' && $k != 'printer_friendly' && $k != 'template' && $k != 'user_last_modified_less'
                && $k != 'user_last_modified_equal' && $k != 'user_last_modified_greater' && $k != 'userdb_creation_date_equal'
                && $k != 'userdb_creation_date_greater' && $k != 'userdb_creation_date_less' && $k != 'userdb_creation_date_equal_days'
                && $k != 'userdb_creation_date_greater_days' && $k != 'userdb_creation_date_less_days' && $k != 'x' && $k != 'y' && $k != 'userdb_expiration_greater'
                && $k != 'userdb_expiration_less' && $k != 'userdb_active' && $k != 'userdb_user_name' && $k != 'popup' && $k != 'userdb_is_admin'
                && $k != 'userdb_is_agent' && $k != 'userdb_is_admin' && $k != 'userdb_emailaddress'
            ) {
                if (!is_array($v)) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }

                    //Handle NULL/NOTNULL Searches
                    if (substr($k, -5) == '-NULL' && $v == '1') {
                        $subk = substr($k, 0, -5);
                        $searchresultSQL .= "(`$subk`.userdbelements_field_name = '$subk' AND (`$subk`.userdbelements_field_value IS NULL OR `$subk`.userdbelements_field_value = ''))";
                        if (!in_array($subk, $tablelist)) {
                            $tablelist[] = $subk;
                        }
                    } elseif (substr($k, -8) == '-NOTNULL' && $v == '1') {
                        $subk = substr($k, 0, -8);
                        $searchresultSQL .= "(`$subk`.userdbelements_field_name = '$subk' AND (`$subk`.userdbelements_field_value IS NOT NULL  AND `$subk`.userdbelements_field_value <> ''))";
                        if (!in_array($subk, $tablelist)) {
                            $tablelist[] = $subk;
                        }
                    }
                    //Handle Min/Max Searches
                    elseif (substr($k, -4) == '-max') {
                        $subk = $conn->addQ(substr($k, 0, -4));
                        $safe_subk = $misc->make_db_safe(substr($k, 0, -4));
                        $safe_v = $misc->make_db_safe($v);
                        $sql_file_type = 'SELECT ' . $resource . 'formelements_field_type 
											FROM ' . $config['table_prefix'] . $resource . 'formelements 
											WHERE ' . $resource . 'formelements_field_name = ' . $susafe_subkbk;
                        $recordSet_field_type = $conn->Execute($sql_file_type);
                        if (!$recordSet_field_type) {
                            $error = $conn->ErrorMsg();
                            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->user->search', 'log_message' => 'DB Error: ' . $error]);
                            return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                        }

                        if ($resource = 'agent') {
                            $field_type = $recordSet_field_type->fields('agentformelements_field_type');
                        } else {
                            $field_type = $recordSet_field_type->fields('memberformelements_field_type');
                        }

                        if ($db_type == 'mysql' || $db_type == 'mysqli' || $db_type == 'pdo') {
                            if ($field_type == 'lat' || $field_type == 'long') {
                                $searchresultSQL .= "(`$subk`.userdbelements_field_name = $safe_subk AND CAST(`$subk`.userdbelements_field_value as DECIMAL(13,7)) <= $safe_v)";
                            } elseif ($field_type == 'decimal') {
                                $searchresultSQL .= "(`$subk`.userdbelements_field_name = $safe_subk AND CAST(`$subk`.userdbelements_field_value as DECIMAL(64,6)) <= $safe_v)";
                            } else {
                                $searchresultSQL .= "(`$subk`.userdbelements_field_name = $safe_subk AND CAST(`$subk`.userdbelements_field_value as signed) <= $safe_v)";
                            }
                        } else {
                            $searchresultSQL .= "(`$subk`.userdbelements_field_name = $safe_subk AND CAST(`$subk`.userdbelements_field_value as int4) <= $safe_v)";
                        }

                        if (!in_array($subk, $tablelist)) {
                            $tablelist[] = $subk;
                        }
                    } elseif (substr($k, -4) == '-min') {
                        $subk = $conn->addQ(substr($k, 0, -4));
                        $safe_subk = $misc->make_db_safe(substr($k, 0, -4));
                        $safe_v = $misc->make_db_safe($v);
                        $sql_file_type = 'SELECT ' . $resource . 'formelements_field_type 
											FROM ' . $config['table_prefix'] . $resource . 'formelements 
											WHERE ' . $resource . 'formelements_field_name = \'' . $subk . '\'';
                        $recordSet_field_type = $conn->Execute($sql_file_type);
                        if (!$recordSet_field_type) {
                            $error = $conn->ErrorMsg();
                            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->user->search', 'log_message' => 'DB Error: ' . $error]);
                            return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                        }

                        if ($resource = 'agent') {
                            $field_type = $recordSet_field_type->fields('agentformelements_field_type');
                        } else {
                            $field_type = $recordSet_field_type->fields('memberformelements_field_type');
                        }

                        if ($db_type == 'mysql' || $db_type == 'mysqli' || $db_type == 'pdo') {
                            if ($field_type == 'lat' || $field_type == 'long') {
                                $searchresultSQL .= "(`$subk`.userdbelements_field_name = $safe_subk AND CAST(`$subk`.userdbelements_field_value as DECIMAL(13,7)) >= $safe_v)";
                            } elseif ($field_type == 'decimal') {
                                $searchresultSQL .= "(`$subk`.userdbelements_field_name = $safe_subk AND CAST(`$subk`.userdbelements_field_value as DECIMAL(64,6)) >= $safe_v)";
                            } else {
                                $searchresultSQL .= "(`$subk`.userdbelements_field_name = $safe_subk AND CAST(`$subk`.userdbelements_field_value as signed) >= $safe_v)";
                            }
                        } else {
                            $searchresultSQL .= "(`$subk`.userdbelements_field_name = $safe_subk AND CAST(`$subk`.userdbelements_field_value as int4) >= $safe_v)";
                        }
                        if (!in_array($subk, $tablelist)) {
                            $tablelist[] = $subk;
                        }
                    } elseif (substr($k, -8) == '-maxdate') {
                        if ($config['date_format'] == 1) {
                            $format = '%m/%d/%Y';
                        } elseif ($config['date_format'] == 2) {
                            $format = '%Y/%d/%m';
                        } elseif ($config['date_format'] == 3) {
                            $format = '%d/%m/%Y';
                        }
                        $v = $misc->make_db_safe($misc->parseDate($v, $format));
                        $subk = $conn->addQ(urldecode(substr($k, 0, -8)));
                        $searchresultSQL .= "(`$subk`.userdbelements_field_name = '$subk' AND `$subk`.userdbelements_field_value <= $v)";
                        if (!in_array($subk, $tablelist)) {
                            $tablelist[] = $subk;
                        }
                    } elseif (substr($k, -8) == '-mindate') {
                        if ($config['date_format'] == 1) {
                            $format = '%m/%d/%Y';
                        } elseif ($config['date_format'] == 2) {
                            $format = '%Y/%d/%m';
                        } elseif ($config['date_format'] == 3) {
                            $format = '%d/%m/%Y';
                        }
                        $v = $misc->make_db_safe($misc->parseDate($v, $format));
                        $subk = $conn->addQ(urldecode(substr($k, 0, -8)));
                        $searchresultSQL .= "(`$subk`.userdbelements_field_name = '$subk' AND `$subk`.userdbelements_field_value >= $v)";
                        if (!in_array($subk, $tablelist)) {
                            $tablelist[] = $subk;
                        }
                    } elseif (substr($k, -5) == '-date') {
                        if ($config['date_format'] == 1) {
                            $format = '%m/%d/%Y';
                        } elseif ($config['date_format'] == 2) {
                            $format = '%Y/%d/%m';
                        } elseif ($config['date_format'] == 3) {
                            $format = '%d/%m/%Y';
                        }
                        $v = $misc->make_db_safe($misc->parseDate($v, $format));
                        $subk = $conn->addQ(urldecode(substr($k, 0, -5)));
                        $searchresultSQL .= "(`$subk`.userdbelements_field_name = '$subk' AND `$subk`.userdbelements_field_value = $v)";
                        if (!in_array($subk, $tablelist)) {
                            $tablelist[] = $subk;
                        }
                    } elseif ($k == 'searchtext') {
                        $safe_v = $conn->addQ($v);
                        $searchresultSQL .= "((`$k`.userdbelements_field_value like '%$safe_v%') OR (userdb_user_name like '%$safe_v%'))";
                        $tablelist[] = $k;
                    } else {
                        $safe_k = $conn->addQ($k);
                        $safe_v = $misc->make_db_safe($v);
                        $searchresultSQL .= "(`$safe_k`.userdbelements_field_name = '$safe_k' AND `$safe_k`.userdbelements_field_value = $safe_v)";
                        $tablelist[] = $safe_k;
                    }
                } else {
                    // Make Sure Array is not empty
                    $use = false;
                    $comma_separated = implode(' ', $v);
                    if (trim($comma_separated) != '') {
                        $use = true;
                        if ($searchresultSQL != '') {
                            $searchresultSQL .= ' AND ';
                        }
                    }
                    if ($use === true) {
                        if (substr($k, -3) == '_or') {
                            $k = substr($k, 0, strlen($k) - 3);
                            $safe_k = $conn->addQ($k);
                            $searchresultSQL .= "(`$safe_k`.userdbelements_field_name = '$safe_k' AND (";
                            $vitem_count = 0;
                            foreach ($v as $vitem) {
                                $safe_vitem = $conn->addQ($vitem);
                                if ($vitem != '') {
                                    if ($vitem_count != 0) {
                                        $searchresultSQL .= " OR `$safe_k`.userdbelements_field_value LIKE '%$safe_vitem%'";
                                    } else {
                                        $searchresultSQL .= " `$safe_k`.userdbelements_field_value LIKE '%$safe_vitem%'";
                                    }
                                    $vitem_count++;
                                }
                            }
                            $searchresultSQL .= '))';
                            $tablelist[] = $safe_k;
                        } else {
                            $safe_k = $conn->addQ($k);
                            $searchresultSQL .= "(`$safe_k`.userdbelements_field_name = '$safe_k' AND (";
                            $vitem_count = 0;
                            foreach ($v as $vitem) {
                                $safe_vitem = $conn->addQ($vitem);
                                if ($vitem != '') {
                                    if ($vitem_count != 0) {
                                        $searchresultSQL .= " AND `$safe_k`.userdbelements_field_value LIKE '%$safe_vitem%'";
                                    } else {
                                        $searchresultSQL .= " `$safe_k`.userdbelements_field_value LIKE '%$safe_vitem%'";
                                    }
                                    $vitem_count++;
                                }
                            }
                            $searchresultSQL .= '))';
                            $tablelist[] = $safe_k;
                        }
                    }
                }
            }
        }

        // Handle Sorting
        // sort the users
        // this is the main SQL that grabs the users
        // basic sort by title..
        $group_order_text = '';
        $sortby_array = [];
        $sorttype_array = [];
        //Set array
        if (isset($sortby) && !empty($sortby)) {
            $sortby_array = $sortby;
        }
        if (isset($sorttype) && !empty($sorttype)) {
            $sorttype_array = $sorttype;
        }
        $sql_sort_type = '';
        $sort_text = '';
        $order_text = '';
        $group_order_text = '';
        $tablelist_nosort = $tablelist;
        $sort_count = count($sortby_array);
        for ($x = 0; $x < $sort_count; $x++) {
            if (!isset($sorttype_array[$x])) {
                $sorttype_array[$x] = '';
            } elseif ($sorttype_array[$x] != 'ASC' && $sorttype_array[$x] != 'DESC') {
                $sorttype_array[$x] = '';
            }
            if ($sortby_array[$x] == 'userdb_id') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY userdb_id ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',userdb_id ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'userdb_user_name') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY userdb_user_name ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',userdb_user_name ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'userdb_hit_count') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY userdb_hit_count ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',userdb_hit_count ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'random') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY rand() ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',rand() ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'userdb_last_modified') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY userdb_last_modified ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',userdb_last_modified ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'userdb_rank') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY userdb_rank ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',userdb_rank ' . $sorttype_array[$x];
                }
            } else {
                // Check if field is a number or price field and cast the order.
                $sort_by_field = $misc->make_db_safe($sortby_array[$x]);
                $sql_sort_type = 'SELECT ' . $resource . 'formelements_field_type FROM ' . $config['table_prefix'] . $resource . 'formelements 
								WHERE ' . $resource . 'formelements_field_name = ' . $sort_by_field;
                $recordSet_sort_type = $conn->Execute($sql_sort_type);
                if (!$recordSet_sort_type) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->user->search', 'log_message' => 'DB Error: ' . $error]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }

                if ($resource = 'agent') {
                    $field_type = $recordSet_sort_type->fields('agentformelements_field_type');
                } else {
                    $field_type = $recordSet_sort_type->fields('memberformelements_field_type');
                }

                if ($field_type == 'price' || $field_type == 'number') {
                    $tablelist[] = 'sort' . $x;
                    $sort_text .= 'AND (sort' . $x . '.userdbelements_field_name = ' . $sort_by_field . ') ';
                    if ($db_type == 'mysql' || $db_type == 'mysqli' || $db_type == 'pdo') {
                        if ($x == 0) {
                            $order_text .= ' ORDER BY CAST(sort' . $x . '.userdbelements_field_value as signed) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.userdbelements_field_value';
                        } else {
                            $order_text .= ',CAST(sort' . $x . '.userdbelements_field_value as signed) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.userdbelements_field_value';
                        }
                    } else {
                        if ($x == 0) {
                            $order_text .= ' ORDER BY CAST(sort' . $x . '.userdbelements_field_value as int4) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.userdbelements_field_value';
                        } else {
                            $order_text .= ',CAST(sort' . $x . '.userdbelements_field_value as int4) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.userdbelements_field_value';
                        }
                    }
                } elseif ($field_type == 'decimal') {
                    $tablelist[] = 'sort' . $x;
                    $sort_text .= 'AND (sort' . $x . '.userdbelements_field_name = ' . $sort_by_field . ') ';
                    if ($db_type == 'mysql' || $db_type == 'mysqli' || $db_type == 'pdo' || $db_type == 'pdo') {
                        if ($x == 0) {
                            $order_text .= ' ORDER BY CAST(sort' . $x . '.userdbelements_field_value as decimal(64,6)) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.userdbelements_field_value';
                        } else {
                            $order_text .= ',CAST(sort' . $x . '.userdbelements_field_value as signed) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.userdbelements_field_value';
                        }
                    } else {
                        if ($x == 0) {
                            $order_text .= ' ORDER BY CAST(sort' . $x . '.userdbelements_field_value as int4) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.userdbelements_field_value';
                        } else {
                            $order_text .= ',CAST(sort' . $x . '.userdbelements_field_value as int4) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.userdbelements_field_value';
                        }
                    }
                } else {
                    $tablelist[] = 'sort' . $x;
                    $sort_text .= 'AND (sort' . $x . '.userdbelements_field_name = ' . $sort_by_field . ') ';
                    if ($x == 0) {
                        $order_text .= ' ORDER BY sort' . $x . '.userdbelements_field_value ' . $sorttype_array[$x];
                    } else {
                        $order_text .= ', sort' . $x . '.userdbelements_field_value ' . $sorttype_array[$x];
                    }
                    $group_order_text .= ',sort' . $x . '.userdbelements_field_value';
                }
            }
        }
        $group_order_text = $group_order_text . ' ' . $order_text;

        if ($imageonly == true) {
            $order_text = 'GROUP BY ' . $config['table_prefix'] . 'userdb.userdb_id, ' . $config['table_prefix'] . 'userdb.userdb_user_name ' . $group_order_text;
        }
        if ($DEBUG_SQL) {
            echo '<strong>Sort Type SQL:</strong> ' . $sql_sort_type . '<br />';
            echo '<strong>Sort Text:</strong> ' . $sort_text . '<br />';
            echo '<strong>Order Text:</strong> ' . $order_text . '<br />';
        }

        //$guidestring_with_sort = $guidestring_with_sort . $guidestring;
        // End of Sort
        $arrayLength = count($tablelist);
        if ($DEBUG_SQL) {
            echo '<strong>Table List Array Length:</strong> ' . $arrayLength . '<br />';
        }
        $string_table_list = '';
        for ($i = 0; $i < $arrayLength; $i++) {
            $string_table_list .= ' ,' . $config['table_prefix'] . 'userdbelements `' . $tablelist[$i] . '`';
        }
        $arrayLength = count($tablelist_nosort);
        $string_table_list_no_sort = '';
        for ($i = 0; $i < $arrayLength; $i++) {
            $string_table_list_no_sort .= ' ,' . $config['table_prefix'] . 'userdbelements `' . $tablelist[$i] . '`';
        }
        $arrayLength = count($tablelist_fullname);
        if ($DEBUG_SQL) {
            echo '<strong>Table List Array Length:</strong> ' . $arrayLength . '<br />';
        }
        for ($i = 0; $i < $arrayLength; $i++) {
            $string_table_list .= ' ,' . $tablelist_fullname[$i];
            $string_table_list_no_sort .= ' ,' . $tablelist_fullname[$i];
        }

        if ($DEBUG_SQL) {
            echo '<strong>Table List String:</strong> ' . $string_table_list . '<br />';
        }
        $arrayLength = count($tablelist);
        for ($i = 0; $i < $arrayLength; $i++) {
            if ($string_where_clause != '') {
                $string_where_clause .= ' AND ';
            }
            $string_where_clause .= ' (' . $config['table_prefix'] . 'userdb.userdb_id = `' . $tablelist[$i] . '`.userdb_id)';
        }
        $arrayLength = count($tablelist_nosort);
        for ($i = 0; $i < $arrayLength; $i++) {
            if ($string_where_clause_nosort != '') {
                $string_where_clause_nosort .= ' AND ';
            }
            $string_where_clause_nosort .= ' (' . $config['table_prefix'] . 'userdb.userdb_id = `' . $tablelist[$i] . '`.userdb_id)';
        }

        if ($imageonly) {
            $searchSQL = 'SELECT distinct(' . $config['table_prefix'] . 'userdb.userdb_id), ' . $config['table_prefix'] . 'userdb.userdb_id,
						' . $config['table_prefix'] . 'userdb.userdb_user_name FROM ' . $config['table_prefix'] . 'userdb,
						' . $config['table_prefix'] . 'userimages ' . $string_table_list . '
						WHERE ' . $string_where_clause . '
						AND (' . $config['table_prefix'] . 'userimages.userdb_id = ' . $config['table_prefix'] . 'userdb.userdb_id)';

            $searchSQLCount = 'SELECT COUNT(distinct(' . $config['table_prefix'] . 'userdb.userdb_id)) as total_users
						FROM ' . $config['table_prefix'] . 'userdb, ' . $config['table_prefix'] . 'userimages ' . $string_table_list_no_sort . '
						WHERE ' . $string_where_clause_nosort . '
						AND (' . $config['table_prefix'] . 'userimages.userdb_id = ' . $config['table_prefix'] . 'userdb.userdb_id)';
        } else {
            $searchSQL = 'SELECT distinct(' . $config['table_prefix'] . 'userdb.userdb_id)
						FROM ' . $config['table_prefix'] . 'userdb ' . $string_table_list . '
						WHERE ' . $string_where_clause;
            $searchSQLCount = 'SELECT COUNT(distinct(' . $config['table_prefix'] . 'userdb.userdb_id)) as total_users
						FROM ' . $config['table_prefix'] . 'userdb ' . $string_table_list_no_sort . '
						WHERE ' . $string_where_clause_nosort;
        }
        if ($searchresultSQL != '') {
            $searchSQL .= ' AND ' . $searchresultSQL;
            $searchSQLCount .= ' AND ' . $searchresultSQL;
        }

        $sql = $searchSQL . ' ' . $sort_text . ' ' . $order_text;
        if ($count_only) {
            $sql = $searchSQLCount;
        }
        //$searchSQLCount = $searchSQLCount;
        // We now have a complete SQL Query. Now grab the results
        $process_time = $misc->getmicrotime();
        //echo 'Limit: '.$limit .'<br>';
        //echo 'Offset: '.$offset;

        if ($limit > 0) {
            $recordSet = $conn->SelectLimit($sql, $limit, $offset);
        //$recordSet = $conn->Execute($sql . ' LIMIT '.$limit. ' OFFSET ' .$offset);
        } else {
            $recordSet = $conn->Execute($sql);
        }

        $query_time = $misc->getmicrotime();
        $query_time = $query_time - $process_time;
        $process_time = $process_time - $start_time;
        if ($DEBUG_SQL) {
            echo '<strong>Search Query:</strong> ' . $sql . '<br />';
        }
        if (!$recordSet) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->user->search', 'log_message' => 'DB Error: ' . $error . ' Full SQL: ' . $sql]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
        }
        $users_found = [];

        if ($count_only) {
            $user_count = $recordSet->fields('total_users');
        } else {
            $user_count = $recordSet->RecordCount();
        }
        if (!$count_only) {
            while (!$recordSet->EOF) {
                $users_found[] = $recordSet->fields('userdb_id');
                $recordSet->MoveNext();
            }
        }
        $total_time = $misc->getmicrotime();
        $total_time = $total_time - $start_time;
        $info['process_time'] = sprintf('%.3f', $process_time);
        $info['query_time'] = sprintf('%.3f', $query_time);
        $info['total_time'] = sprintf('%.3f', $total_time);
        return [
            'error' => false,
            'user_count' => $user_count,
            'users' => $users_found,
            'info' => $info,
            'sortby' => $sortby_array,
            'sorttype' => $sorttype_array,
            'limit' => $limit,
            'resource' => $resource,
        ];
    }

    /**
     * This API Command creates users.
     * @param array $data $data expects an array containing the following array keys.
     *  <ul>
     *         <li>$data['user_details'] - This should be an array containg the following three settings.</li>
     *      <li>$data['user_details']['title'] - Required. This is the title for the user.</li>
     *      <li>$data['user_details']['seotitle'] - Optional. If not set the API will create a seo friendly title based on the title supplied. The API will ensure teh seotitle is unique, so the seotitle you supply will be modified if needed..</li>
     *      <li>$data['user_details']['notes'] - Options - notes about this user, only visible to admin and agents.</li>
     *      <li>$data['user_details']['featured'] - Required Boolean - Is this a featured users. true/false</li>
     *      <li>$data['user_details']['active'] - - Required Boolean - Is this a active users. true/false</li>
     *      <li>$data['user_agents'] - This should be an array of up to agent ids. This sets the user agent ID, the primary user agent ID must be key 0.
     *      <code>$data['user_agents'][0]=5; //This lising belongs to agent 5. All other keys are currently ignored.</code></li>
     *      <li>$data['user_fields'] - This should be an array of the actual user data. The array keys should be the field name and the array values should be the field values. Only valid fields will be used, other data will be dropped.
     *      <code>$data['user_fields'] =array('mls_id' => 126,'address'=>'126 E Buttler Ave');  // This example defines a field value of 126 for a field called mls_id and a value of "126 E Buttler Ave" for the address field.</code></li>
     *      <li>$data['user_media'] - Currently not used and MUST be an empty array.</li>
     *  </ul>
     * @return array
     *
     */
    public function create($data)
    {
        global $conn, $lapi, $config, $lang, $misc;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->verify_priv('edit_all_users');
        if ($login_status !== true && $config['demo_mode'] != 1) {
            return ['error' => true, 'error_msg' => 'Login Failure or Demo mode'];
        }
        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed

        if (!isset($user_details) || !is_array($user_details)) {
            return ['error' => true, 'error_msg' => 'user_details: correct_parameter_not_passed'];
        }

        if (!isset($user_details['user_name']) || empty($user_details['user_name'])) {
            return ['error' => true, 'error_msg' => '$user_details[user_name]: correct_parameter_not_passed'];
        }
        if (!isset($user_details['user_first_name']) || empty($user_details['user_first_name'])) {
            return ['error' => true, 'error_msg' => '$user_details[user_first_name]: correct_parameter_not_passed'];
        }
        if (!isset($user_details['user_last_name']) || empty($user_details['user_last_name'])) {
            return ['error' => true, 'error_msg' => '$user_details[user_last_name]: correct_parameter_not_passed'];
        }
        if (!isset($user_details['emailaddress']) || empty($user_details['emailaddress'])) {
            return ['error' => true, 'error_msg' => '$user_details[emailaddress]: correct_parameter_not_passed'];
        }
        if (!isset($user_details['user_password']) || empty($user_details['user_password'])) {
            return ['error' => true, 'error_msg' => '$user_details[user_password]: correct_parameter_not_passed'];
        }
        if (!isset($user_details['active']) || $user_details['active'] != 'yes' && $user_details['active'] != 'no') {
            $user_details['active'] = 'no';
        }
        if (!isset($user_details['is_admin']) || $user_details['is_admin'] != 'yes' && $user_details['is_admin'] != 'no') {
            $user_details['is_admin'] = 'no';
        }
        if (!isset($user_details['is_agent']) || $user_details['is_agent'] != 'yes') {
            $user_details['is_agent'] = 'no';
            $resource = 'member';
        } else {
            $resource = 'agent';
        }
        if (isset($user_fields) && !is_array($user_fields)) {
            return ['error' => true, 'error_msg' => 'user_fields: correct_parameter_not_passed'];
        }

        //get a list of agent or member user fields based on $resource
        $result = $lapi->load_local_api('fields__metadata', [
            'resource' => $resource,
        ]);

        if ($result['error']) {
            //If an error occurs die and show the error msg;
            die($result['error_msg']);
        }
        // if there are no user fields in the request
        // check the $result array to see if any are required.
        if (!isset($user_fields) || empty($user_fields)) {
            $column_names = array_column($result['fields'], 'required');
            if (in_array('Yes', $column_names)) {
                return ['error' => true, 'error_msg' => 'user_fields: Fields set as required are missing'];
            }
        }

        //Ok we this far now we bulid the user.
        $user_details['creation_date'] = time();
        $user_details['last_modified'] = time();
        $errors = '';
        $this_shall_pass = 'Yes';

        // first, make sure the user name isn't in use
        $result = $lapi->load_local_api('user__search', [
            'parameters' => [
                'userdb_user_name' => $user_details['user_name'],
                'userdb_active' => 'any',
            ],
            'resource' => 'agent',
            'count_only' => 0,
        ]);
        if ($result['error']) {
            return ['error' => true, 'error_msg' => $result['error_msg']];
        }
        $num = $result['user_count'];

        // second, make sure the user email isn't in use
        $result = $lapi->load_local_api('user__search', [
            'parameters' => [
                'userdb_emailaddress' => $user_details['emailaddress'],
                'userdb_active' => 'any',
            ],
            'resource' => 'agent',
            'count_only' => 1,
        ]);
        if ($result['error']) {
            return ['error' => true, 'error_msg' => $result['error_msg']];
        }
        $num2 = $result['user_count'];

        if ($num >= 1) {
            $this_shall_pass = 'No';
            $errors .= $lang['user_creation_username_taken'] . '<br />';
        } // end if
        elseif ($num2 >= 1) {
            $this_shall_pass = 'No';
            $errors .= $lang['email_address_already_registered'] . '<br />';
        } // end if
        if (!empty($errors)) {
            return ['error' => true, 'error_msg' => $errors];
        }

        if ($this_shall_pass == 'Yes') {
            $sql_user_name = $misc->make_db_safe($user_details['user_name']);
            $sql_user_first_name = $misc->make_db_safe($user_details['user_first_name']);
            $sql_user_last_name = $misc->make_db_safe($user_details['user_last_name']);
            $sql_user_email = $misc->make_db_safe($user_details['emailaddress']);
            $hash_user_pass = password_hash($user_details['user_password'], PASSWORD_DEFAULT);
            $hash_user_pass = $misc->make_db_safe($hash_user_pass);
            $sql_active = strtolower($misc->make_db_safe($user_details['active']));
            $sql_isAgent = strtolower($misc->make_db_safe($user_details['is_agent']));
            $sql_isAdmin = strtolower($misc->make_db_safe($user_details['is_admin']));

            if (strtolower($user_details['is_admin']) == 'yes') {
                $resource = 'agent';
                //set this again because we don;t want an admin who is not an also Agent.
                $sql_isAgent = $misc->make_db_safe('yes');

                $sql_limitFeaturedListings = $misc->make_db_safe('-1');
                if (isset($user_details['rank'])) {
                    $sql_rank = intval($user_details['rank']);
                } else {
                    $sql_rank = 1;
                }
                $sql_limitListings = $misc->make_db_safe('-1');
                $sql_canEditSiteConfig = $misc->make_db_safe('no');
                $sql_canEditMemberTemplate = $misc->make_db_safe('no');
                $sql_canEditAgentTemplate = $misc->make_db_safe('no');
                $sql_canEditListingTemplate = $misc->make_db_safe('no');
                $sql_canFeatureListings = $misc->make_db_safe('no');
                $sql_canViewLogs = $misc->make_db_safe('no');
                $sql_canModerate = $misc->make_db_safe('no');
                $sql_canPages = $misc->make_db_safe('no');
                $sql_canVtour = $misc->make_db_safe('no');
                $sql_canFiles = $misc->make_db_safe('no');
                $sql_canUserFiles = $misc->make_db_safe('no');
                $sql_canExportListings = $misc->make_db_safe('no');
                $sql_canEditListingExpiration = $misc->make_db_safe('no');
                $sql_canEditAllListings = $misc->make_db_safe('no');
                $sql_canEditAllUsers = $misc->make_db_safe('no');
                $sql_canEditPropertyClasses = $misc->make_db_safe('no');
                $sql_canManageAddons = $misc->make_db_safe('no');
                $sql_blogUserType = $misc->make_db_safe('4');
            } elseif (strtolower($user_details['is_agent']) == 'yes') {
                $resource = 'agent';
                if ($config['agent_default_edit_site_config'] == 1) {
                    $sql_canEditSiteConfig = $misc->make_db_safe('yes');
                } else {
                    $sql_canEditSiteConfig = $misc->make_db_safe('no');
                }
                if ($config['agent_default_edit_member_template'] == 1) {
                    $sql_canEditMemberTemplate = $misc->make_db_safe('yes');
                } else {
                    $sql_canEditMemberTemplate = $misc->make_db_safe('no');
                }
                if ($config['agent_default_edit_agent_template'] == 1) {
                    $sql_canEditAgentTemplate = $misc->make_db_safe('yes');
                } else {
                    $sql_canEditAgentTemplate = $misc->make_db_safe('no');
                }

                if ($config['agent_default_edit_listing_template'] == 1) {
                    $sql_canEditListingTemplate = $misc->make_db_safe('yes');
                } else {
                    $sql_canEditListingTemplate = $misc->make_db_safe('no');
                }
                if ($config['agent_default_feature'] == 1) {
                    $sql_canFeatureListings = $misc->make_db_safe('yes');
                } else {
                    $sql_canFeatureListings = $misc->make_db_safe('no');
                }
                if ($config['agent_default_logview'] == 1) {
                    $sql_canViewLogs = $misc->make_db_safe('yes');
                } else {
                    $sql_canViewLogs = $misc->make_db_safe('no');
                }
                if ($config['agent_default_moderate'] == 1) {
                    $sql_canModerate = $misc->make_db_safe('yes');
                } else {
                    $sql_canModerate = $misc->make_db_safe('no');
                }
                if ($config['agent_default_editpages'] == 1) {
                    $sql_canPages = $misc->make_db_safe('yes');
                } else {
                    $sql_canPages = $misc->make_db_safe('no');
                }
                if ($config['agent_default_havevtours'] == 1) {
                    $sql_canVtour = $misc->make_db_safe('yes');
                } else {
                    $sql_canVtour = $misc->make_db_safe('no');
                }
                if ($config['agent_default_havefiles'] == 1) {
                    $sql_canFiles = $misc->make_db_safe('yes');
                } else {
                    $sql_canFiles = $misc->make_db_safe('no');
                }
                if ($config['agent_default_haveuserfiles'] == 1) {
                    $sql_canUserFiles = $misc->make_db_safe('yes');
                } else {
                    $sql_canUserFiles = $misc->make_db_safe('no');
                }
                $sql_limitListings = $misc->make_db_safe($config['agent_default_num_listings']);
                $sql_limitFeaturedListings = $misc->make_db_safe($config['agent_default_num_featuredlistings']);

                if ($config['agent_default_can_export_listings'] == 1) {
                    $sql_canExportListings = $misc->make_db_safe('yes');
                } else {
                    $sql_canExportListings = $misc->make_db_safe('no');
                }
                if ($config['agent_default_canchangeexpirations'] == 1) {
                    $sql_canEditListingExpiration = $misc->make_db_safe('yes');
                } else {
                    $sql_canEditListingExpiration = $misc->make_db_safe('no');
                }
                if ($config['agent_default_edit_all_listings'] == 1) {
                    $sql_canEditAllListings = $misc->make_db_safe('yes');
                } else {
                    $sql_canEditAllListings = $misc->make_db_safe('no');
                }
                if ($config['agent_default_edit_all_users'] == 1) {
                    $sql_canEditAllUsers = $misc->make_db_safe('yes');
                } else {
                    $sql_canEditAllUsers = $misc->make_db_safe('no');
                }

                if ($config['agent_default_edit_property_classes'] == 1) {
                    $sql_canEditPropertyClasses = $misc->make_db_safe('yes');
                } else {
                    $sql_canEditPropertyClasses = $misc->make_db_safe('no');
                }

                if ($config['agent_default_canManageAddons'] == 1) {
                    $sql_canManageAddons = $misc->make_db_safe('yes');
                } else {
                    $sql_canManageAddons = $misc->make_db_safe('no');
                }
                if (isset($user_details['rank'])) {
                    $sql_rank = intval($user_details['rank']);
                } else {
                    //could improve this to set to last rank position.
                    $sql_rank = 5;
                }
                $sql_blogUserType = $misc->make_db_safe($config['agent_default_blogUserType']);
            } else {
                $resource = 'member';
                $sql_canEditSiteConfig = $misc->make_db_safe('no');
                $sql_canEditMemberTemplate = $misc->make_db_safe('no');
                $sql_canEditAgentTemplate = $misc->make_db_safe('no');
                $sql_canEditListingTemplate = $misc->make_db_safe('no');
                $sql_canFeatureListings = $misc->make_db_safe('no');
                $sql_canViewLogs = $misc->make_db_safe('no');
                $sql_canModerate = $misc->make_db_safe('no');
                $sql_canPages = $misc->make_db_safe('no');
                $sql_canVtour = $misc->make_db_safe('no');
                $sql_canFiles = $misc->make_db_safe('no');
                $sql_canUserFiles = $misc->make_db_safe('no');
                $sql_canExportListings = $misc->make_db_safe('no');
                $sql_canEditListingExpiration = $misc->make_db_safe('no');
                $sql_canEditAllListings = $misc->make_db_safe('no');
                $sql_canEditAllUsers = $misc->make_db_safe('no');
                $sql_limitListings = 0;
                $sql_limitFeaturedListings = 0;
                $sql_rank = 0;
                $sql_canEditPropertyClasses = $misc->make_db_safe('no');
                $sql_canManageAddons = $misc->make_db_safe('no');
                $sql_blogUserType = $misc->make_db_safe('1');
            }
            // create the account
            $sql = 'INSERT INTO ' . $config['table_prefix'] . 'userdb (
						userdb_user_name, 
						userdb_user_password,
						userdb_user_first_name,
						userdb_user_last_name,
						userdb_emailAddress,
						userdb_creation_date,
						userdb_last_modified,
						userdb_active,
						userdb_is_agent,
						userdb_is_admin,
						userdb_can_edit_member_template,
						userdb_can_edit_agent_template,
						userdb_can_edit_listing_template,
						userdb_can_feature_listings,
						userdb_can_view_logs,
						userdb_can_moderate,
						userdb_can_edit_pages,
						userdb_can_have_vtours,
						userdb_can_have_files,
						userdb_can_have_user_files,
						userdb_limit_listings,
						userdb_comments,
						userdb_hit_count,
						userdb_can_edit_expiration,
						userdb_can_export_listings,
						userdb_can_edit_all_users,
						userdb_can_edit_all_listings,
						userdb_can_edit_site_config,
						userdb_can_edit_property_classes,
						userdb_can_manage_addons,
						userdb_rank,
						userdb_featuredlistinglimit,
						userdb_email_verified,
						userdb_blog_user_type
					) 
					VALUES(
						' . $sql_user_name . ',
						' . $hash_user_pass . ',
						' . $sql_user_first_name . ',
						' . $sql_user_last_name . ',
						' . $sql_user_email . ',
						' . $conn->DBDate(time()) . ',
						' . $conn->DBTimeStamp(time()) . ',
						' . $sql_active . ',
						' . $sql_isAgent . ',
						' . $sql_isAdmin . ',
						' . $sql_canEditMemberTemplate . ',
						' . $sql_canEditAgentTemplate . ',
						' . $sql_canEditListingTemplate . ',
						' . $sql_canFeatureListings . ',
						' . $sql_canViewLogs . ',
						' . $sql_canModerate . ',
						' . $sql_canPages . ',
						' . $sql_canVtour . ',
						' . $sql_canFiles . ',
						' . $sql_canUserFiles . ',
						' . $sql_limitListings . ',
						\'\',
						0,
						' . $sql_canEditListingExpiration . ',
						' . $sql_canExportListings . ',
						' . $sql_canEditAllUsers . ',
						' . $sql_canEditAllListings . ',
						' . $sql_canEditSiteConfig . ',
						' . $sql_canEditPropertyClasses . ',
						' . $sql_canManageAddons . ',
						' . $sql_rank . ',
						' . $sql_limitFeaturedListings . ',
						\'yes\',
						' . $sql_blogUserType . '
					)';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $new_user_id = $conn->Insert_ID(); // this is the new user's ID number

            // Insert custom user fields
            if (isset($user_fields) && is_array($user_fields)) {
                $sql = 'SELECT ' . $resource . 'formelements_default_text, 
								' . $resource . 'formelements_field_name, 
								' . $resource . 'formelements_id, 
								' . $resource . 'formelements_field_type, 
								' . $resource . 'formelements_field_elements,
								' . $resource . 'formelements_required
						FROM  ' . $config['table_prefix'] . $resource . 'formelements';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->user->create', 'log_message' => 'DB Error: ' . $error]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }
                //Verify user fields passed via API exist
                while (!$recordSet->EOF) {
                    $name = $recordSet->fields($resource . 'formelements_field_name');
                    $id = $recordSet->fields($resource . 'formelements_id');
                    $data_type = $recordSet->fields($resource . 'formelements_field_type');
                    $data_elements = $recordSet->fields($resource . 'formelements_field_elements');
                    $default_text = $recordSet->fields($resource . 'formelements_default_text');
                    if (array_key_exists($name, $user_fields)) {
                        switch ($data_type) {
                            case 'number':
                                if ($user_fields[$name] === '') {
                                    $insert_user_fields[$name] = null;
                                } else {
                                    if (is_numeric($user_fields[$name])) {
                                        $insert_user_fields[$name] = $user_fields[$name];
                                    } else {
                                        $price = str_replace(',', '', $user_fields[$name]);
                                        $insert_user_fields[$name] = intval($price);
                                    }
                                }
                                break;
                            case 'decimal':
                            case 'price':
                                if ($user_fields[$name] === '') {
                                    $insert_user_fields[$name] = null;
                                } else {
                                    $price = str_replace(',', '', $user_fields[$name]);
                                    $insert_user_fields[$name] = (float)$price;
                                }
                                break;
                            case 'date':
                                if ($user_fields[$name] === '') {
                                    $insert_user_fields[$name] = null;
                                } else {
                                    $insert_user_fields[$name] = $this->convert_date($user_fields[$name], $or_date_format);
                                }
                                break;
                            case 'select':
                            case 'select-multiple':
                            case 'option':
                            case 'checkbox':
                                //This is a lookup field. Make sure values passed are allowed by the system.
                                //Get Array of allowed data elements
                                $data_elements_array = explode('||', $data_elements);
                                //Get array of passed data eleements
                                if (!is_array($user_fields[$name])) {
                                    $t_value = $user_fields[$name];
                                    unset($user_fields[$name]);
                                    $user_fields[$name][] = $t_value;
                                }
                                $good_elements = [];
                                foreach ($user_fields[$name] as $fvalue) {
                                    if (in_array($fvalue, $data_elements_array) && !in_array($fvalue, $good_elements)) {
                                        $good_elements[] = $fvalue;
                                    }
                                }
                                $insert_user_fields[$name] = $good_elements;
                                break;
                            default:
                                $insert_user_fields[$name] = $user_fields[$name];
                                break;
                        }
                    } else {
                        if ($default_text != '') {
                            $insert_user_fields[$name] = $default_text;
                        } else {
                            $insert_user_fields[$name] = '';
                        }
                    }

                    $recordSet->Movenext();
                }
                $sql = 'INSERT INTO ' . $config['table_prefix'] . 'userdbelements (userdbelements_field_name, userdbelements_field_value, userdb_id) VALUES ';
                $sql2 = [];
                foreach ($insert_user_fields as $name => $value) {
                    $sql_name = $misc->make_db_safe($name);
                    if (is_array($value)) {
                        $sql_value = $misc->make_db_safe(implode('||', $value));
                    } else {
                        $sql_value = $misc->make_db_safe($value);
                    }
                    $sql2[] = "($sql_name, $sql_value, $new_user_id)";
                }
                if (count($sql2) > 0) {
                    $sql .= implode(',', $sql2);
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $error = $conn->ErrorMsg();
                        $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->user->create', 'log_message' => 'DB Error: ' . $error]);
                        return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                    }
                }
            }

            //call the new user hook
            include_once $config['basepath'] . '/include/hooks.inc.php';
            $hooks = new hooks();
            $hooks->load('after_user_signup', $new_user_id);

            return ['error' => false, 'user_id' => $new_user_id];
        }
    }

    /**
     * This API Command reads user info
     * @param array $data $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['userdb_id'] - This is the user ID that we are updating.</li>
     *      <li>$data['resource'] - This is the resource you want to get fields for. Allowed Options are: 'agent' or 'member'</li>
     *      <li>$data['fields'] - This is an optional array of fields to retrieve, if left empty or not passed all fields will be retrieved.</li>
     *  </ul>
     * @return array
     **/
    public function read($data)
    {
        global $conn, $lapi, $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_is_agent = $login->verify_priv('edit_all_users');

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed
        if (!isset($user_id) || !is_numeric($user_id)) {
            return ['error' => true, 'error_msg' => 'user_id: correct_parameter_not_passed'];
        }
        if (isset($fields) && !is_array($fields)) {
            return ['error' => true, 'error_msg' => 'fields: correct_parameter_not_passed'];
        }

        if (!isset($resource) || $resource != 'agent' && $resource != 'member') {
            return ['error' => true, 'error_msg' => 'resource: correct_parameter_not_passed'];
        }
        //This will hold our user data
        $user_data = [];
        //If no fields were passed make an empty array to save checking for if !isset later
        if (!isset($fields)) {
            $fields = [];
        }
        //
        $sql = 'SELECT userdb_active 
				FROM ' . $config['table_prefix'] . 'userdb 
				WHERE userdb_id = ' . $user_id;
        //TODO: Move this active check up higher to ensure user is active no matter if we ask for one field or all fields
        if (!$login_is_agent) {
            $sql .= ' AND userdb_active = \'yes\'';
        }
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->user->read', 'log_message' => 'DB Error: ' . $error]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
        }

        if ($recordSet->RecordCount() == 0) {
            return ['error' => true, 'error_msg' => 'User does not exist or you do not have permission'];
        }

        //Get Base user information
        if (empty($fields)) {
            $sql = 'SELECT ' . implode(',', $this->OR_INT_FIELDS) . ' 
					FROM ' . $config['table_prefix'] . 'userdb 
					WHERE userdb_id = ' . $user_id;

            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->user->read', 'log_message' => 'DB Error: ' . $error]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
            }

            if ($recordSet->RecordCount() == 1) {
                foreach ($this->OR_INT_FIELDS as $field) {
                    $user_data[$field] = $recordSet->fields($field);
                }
            }
            $field_list = $lapi->load_local_api('fields__metadata', ['resource' => $resource]);
            //echo '<pre>'.print_r($field_list,true).'</pre>';

            $allowed_fields = [];
            $allowed_fields_values = [];
            $allowed_fields_type = [];
            foreach ($field_list['fields'] as $field) {
                $field_id = $field['field_id'];
                $field_name = $field['field_name'];
                $field_elements = $field['field_elements'];
                $ftype = $field['field_type'];
                //Make sure field does not have same name as a core field for saftey
                if (!in_array($field_name, $this->OR_INT_FIELDS)) {
                    $allowed_fields[$field_id] = $field_name;
                    if ($ftype == 'select' || $ftype == 'select-multiple' || $ftype == 'option' || $ftype == 'checkbox') {
                        $allowed_fields_values[$field_name] = $field_elements;
                        $allowed_fields_type[$field_name] = $ftype;
                    }
                }
            }
            //Get The fields
            $sql = 'SELECT userdbelements_field_name, userdbelements_field_value 
					FROM ' . $config['table_prefix'] . 'userdbelements 
					WHERE userdb_id =' . $user_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->user->read', 'log_message' => 'DB Error: ' . $error]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
            }
            while (!$recordSet->EOF) {
                $field_name = $recordSet->fields('userdbelements_field_name');
                $field_value = $recordSet->fields('userdbelements_field_value');

                if (in_array($field_name, $allowed_fields)) {
                    //See if this is a lookup
                    if (isset($allowed_fields_values[$field_name])) {
                        if ($allowed_fields_type[$field_name] == 'select' || $allowed_fields_type[$field_name] == 'option') {
                            if (in_array($field_value, $allowed_fields_values[$field_name])) {
                                $user_data[$field_name] = $field_value;
                            } else {
                                $user_data[$field_name] = '';
                            }
                        } else {
                            $field_values = explode('||', $field_value);
                            $real_values = array_intersect($allowed_fields_values[$field_name], $field_values);
                            $user_data[$field_name] = $real_values;
                        }
                    } else {
                        $user_data[$field_name] = $field_value;
                    }
                }

                $recordSet->MoveNext();
            }
        } else {
            $core_fields = array_intersect($this->OR_INT_FIELDS, $fields);
            $noncore_fields = array_diff($fields, $this->OR_INT_FIELDS);
            if (!empty($core_fields)) {
                $sql = 'SELECT ' . implode(',', $core_fields) . ' 
						FROM ' . $config['table_prefix'] . 'userdb 
						WHERE userdb_id = ' . $user_id;

                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->user->read', 'log_message' => 'DB Error: ' . $error]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }

                if ($recordSet->RecordCount() == 1) {
                    foreach ($core_fields as $field) {
                        $user_data[$field] = $recordSet->fields($field);
                    }
                }
            }

            if (!empty($noncore_fields)) {
                $field_list = $lapi->load_local_api('fields__metadata', ['resource' => $resource]);

                $allowed_fields = [];
                $allowed_fields_values = [];
                $allowed_fields_type = [];

                foreach ($field_list['fields'] as $field) {
                    $field_id = $field['field_id'];
                    $field_name = $field['field_name'];
                    $field_elements = $field['field_elements'];
                    $ftype = $field['field_type'];
                    //Make sure field does not have same name as a core field for saftey
                    if (in_array($field_name, $noncore_fields)) {
                        $allowed_fields[$field_id] = $field_name;
                        if ($ftype == 'select' || $ftype == 'select-multiple' || $ftype == 'option' || $ftype == 'checkbox') {
                            $allowed_fields_values[$field_name] = $field_elements;
                            $allowed_fields_type[$field_name] = $ftype;
                        }
                    }
                    $recordSet->MoveNext();
                }

                //Get The fields
                $sql = 'SELECT userdbelements_field_name, userdbelements_field_value 
						FROM ' . $config['table_prefix'] . 'userdbelements 
						WHERE userdb_id =' . $user_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->metadata', 'log_message' => 'DB Error: ' . $error]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }
                while (!$recordSet->EOF) {
                    $field_name = $recordSet->fields('userdbelements_field_name');
                    $field_value = $recordSet->fields('userdbelements_field_value');
                    if (in_array($field_name, $allowed_fields)) {
                        //See if this is a lookup
                        if (isset($allowed_fields_values[$field_name])) {
                            if ($allowed_fields_type[$field_name] == 'select' || $allowed_fields_type[$field_name] == 'option') {
                                if (in_array($field_value, $allowed_fields_values[$field_name])) {
                                    $user_data[$field_name] = $field_value;
                                } else {
                                    $user_data[$field_name] = '';
                                }
                            } else {
                                $field_values = explode('||', $field_value);
                                $real_values = array_intersect($allowed_fields_values[$field_name], $field_values);
                                $user_data[$field_name] = $real_values;
                            }
                        } else {
                            $user_data[$field_name] = $field_value;
                        }
                    }

                    $recordSet->MoveNext();
                }
            }
        }
        return ['error' => false, 'user' => $user_data];
    }

    /**
     * This API Command updates users.
     * @param array $data $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['userdb_id'] - This is the user ID that we are updating.</li>
     *      <li>$data['user_details'] - This should be an array containg the following three settings.</li>
     *      <li>$data['user_details']['active'] -Set if this a active users, only set if you need to change. true/false</li>
     *      <li>$data['user_details'] - This should be an array of the actual user data. The array keys should be the field name and the array values should be the field values. Only valid fields will be used, other data will be dropped.
     *      <code>$data['user_fields'] =array('phone' => '555-555-1212','fax'=>'555-555-1313');  </code></li>
     *  </ul>
     * @return array
     *
     */
    public function update($data)
    {
        global $conn, $config, $lang, $lapi, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        $login_status = false;
        $has_permission = true;

        extract($data, EXTR_SKIP || EXTR_REFS, '');

        //setup array that will contain our SQL fields for update
        $sql_fields = [];

        //Check that required settings were passed
        if (!isset($user_id) || !is_numeric($user_id)) {
            return ['error' => true, 'error_msg' => 'user_id: correct_parameter_not_passed'];
        } else {
            $userdb_id = intval($user_id);

            //get user status (resource) of user
            $is_admin = $misc->get_admin_status($userdb_id);
            $is_agent = $misc->get_agent_status($userdb_id);

            if ($is_agent === true || $is_admin === true) {
                $resource = 'agent';
            } else {
                $resource = 'member';
            }

            //does this user exist?
            $resultc = $lapi->load_local_api('user__search', [
                'parameters' => [
                    'userdb_id' => $userdb_id,
                    'userdb_active' => 'any',
                ],
                'resource' => $resource,
            ]);
            if ($resultc['user_count'] == 0) {
                return ['error' => true, 'error_msg' => 'user_id: ' . $userdb_id . ' not present'];
            }
        }

        //check permissions.
        if ($_SESSION['userID'] != $user_id) {
            $login_status = $login->verify_priv('edit_all_users');
            if (!$login_status) {
                $has_permission = false;
            }
        }

        //Check that required settings were passed
        if (!isset($userdb_id) || !is_numeric($userdb_id)) {
            return ['error' => true, 'error_msg' => 'user_id: correct_parameter_not_passed'];
        }
        if (isset($user_details) && !is_array($user_details)) {
            return ['error' => true, 'error_msg' => 'user_details: correct_parameter_not_passed'];
        }
        if (isset($user_fields) && !is_array($user_fields)) {
            return ['error' => true, 'error_msg' => 'user_fields: correct_parameter_not_passed'];
        }
        //echo '<pre>User Fields Array: '.print_r($user_fields,true).'</pre>';
        $userdb_id = intval($userdb_id);
        //Non-editable fields.
        //can't touch this
        if (isset($user_details['id']) && !empty($user_details['id'])) {
            return ['error' => true, 'error_msg' => 'user_details[id]: User ID# (userdb_id) cannot be changed.'];
        }
        //can't touch this
        if (isset($user_details['user_name']) && !empty($user_details['user_name'])) {
            return ['error' => true, 'error_msg' => 'user_details[user_name]: Username cannot be changed.'];
        }
        //can't touch this
        if (isset($user_details['is_admin']) && !empty($user_details['is_admin'])) {
            return ['error' => true, 'error_msg' => 'user_details[is_admin]: Status cannot be changed'];
        }
        //can't touch this
        if (isset($user_details['is_agent']) && !empty($user_details['is_agent'])) {
            return ['error' => true, 'error_msg' => 'user_details[is_agent]: Status cannot be changed'];
        }
        //can't touch this
        if (isset($user_details['creation_date']) && empty($user_details['creation_date'])) {
            return ['error' => true, 'error_msg' => 'user_details[creation_date]: cannot be modified'];
        }
        //can't touch this
        if (isset($user_details['last_modified']) && empty($user_details['last_modified'])) {
            return ['error' => true, 'error_msg' => 'user_details[last_modified]: cannot be modified'];
        }

        //these can't be set empty, and have lowest restrictions
        if (isset($user_details['user_first_name']) && empty($user_details['user_first_name'])) {
            return ['error' => true, 'error_msg' => 'user_details[user_first_name]: cannot be empty'];
        } elseif (isset($user_details['user_first_name'])) {
            $sql_fields['userdb_user_first_name'] = $user_details['user_first_name'];
        }

        if (isset($user_details['user_last_name']) && empty($user_details['user_last_name'])) {
            return ['error' => true, 'error_msg' => 'user_details[user_last_name]: cannot be empty'];
        } elseif (isset($user_details['user_last_name'])) {
            $sql_fields['userdb_user_last_name'] = $user_details['user_last_name'];
        }
        if (isset($user_details['emailaddress']) && !filter_var($user_details['emailaddress'], FILTER_VALIDATE_EMAIL)) {
            return ['error' => true, 'error_msg' => 'user_details[emailaddress]: not a valid address'];
        } elseif (isset($user_details['emailaddress'])) {
            //make sure this address does not already exist
            $result = $lapi->load_local_api('user__search', [
                'parameters' => [
                    'userdb_emailaddress' => $user_details['emailaddress'],
                    'userdb_active' => 'any',
                ],
                'resource' => $resource,
                'count_only' => 1,
            ]);
            if ($result['error']) {
                return ['error' => true, 'error_msg' => $result['error_msg']];
            }
            $num = $result['user_count'];
            if ($num >= 1) {
                return ['error' => true, 'error_msg' => 'user_details[emailaddress]: address already exists'];
            } else {
                $sql_fields['userdb_emailaddress'] = $user_details['emailaddress'];
            }
        }

        if (isset($user_details['user_password']) && empty($user_details['user_password'])) {
            return ['error' => true, 'error_msg' => 'user_details[user_password]: cannot be empty'];
        } elseif (isset($user_details['user_password'])) {
            $sql_fields['userdb_user_password'] = password_hash($user_details['user_password'], PASSWORD_DEFAULT);
        }

        //Admin or edit_all_users permissions required
        if (($login_status === true || $_SESSION['admin_privs'] == 'yes')) {
            if (isset($user_details['active']) && !is_bool($user_details['active'])) {
                return ['error' => true, 'error_msg' => 'user_details[active]: correct_parameter_not_passed'];
            } elseif (isset($user_details['active'])) {
                if ($user_details['active'] === true) {
                    $sql_fields['userdb_active'] = 'yes';
                } else {
                    $sql_fields['userdb_active'] = 'no';
                }
            }
            //See if the user is currently active.
            $api_result = $lapi->load_local_api('user__read', ['user_id' => $userdb_id, 'fields' => ['userdb_active'], 'resource' => $resource]);
            if (!$api_result['error']) {
                $oldstatus = $api_result['user']['userdb_active'];
            }

            if (isset($user_details['hit_count']) && !is_int($user_details['hit_count'])) {
                return ['error' => true, 'error_msg' => 'user_details[hit_count]: correct_parameter_not_passed'];
            } elseif (isset($user_details['hit_count'])) {
                $sql_fields['userdb_hit_count'] = intval($user_details['hit_count']);
            }

            //check and set values only if an Agent account.
            if ($is_agent === true) {
                //
                //boolean permission fields. These can only be set by an Admin or an Agent with edit_all_users permissions
                if (isset($user_details['can_edit_site_config']) && !is_bool($user_details['can_edit_site_config'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_edit_site_config]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_edit_site_config'])) {
                    if ($user_details['can_edit_site_config']) {
                        $sql_fields['userdb_can_edit_site_config'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_edit_site_config'] = 'no';
                    }
                }

                if (isset($user_details['can_edit_member_template']) && !is_bool($user_details['can_edit_member_template'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_edit_member_template]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_edit_member_template'])) {
                    if ($user_details['can_edit_member_template']) {
                        $sql_fields['userdb_can_edit_member_template'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_edit_member_template'] = 'no';
                    }
                }

                if (isset($user_details['can_edit_agent_template']) && !is_bool($user_details['can_edit_agent_template'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_edit_agent_template]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_edit_agent_template'])) {
                    if ($user_details['can_edit_agent_template']) {
                        $sql_fields['userdb_can_edit_agent_template'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_edit_agent_template'] = 'no';
                    }
                }

                if (isset($user_details['can_edit_listing_template']) && !is_bool($user_details['can_edit_listing_template'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_edit_listing_template]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_edit_listing_template'])) {
                    if ($user_details['can_edit_listing_template']) {
                        $sql_fields['userdb_can_edit_listing_template'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_edit_listing_template'] = 'no';
                    }
                }

                if (isset($user_details['can_feature_listings']) && !is_bool($user_details['can_feature_listings'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_feature_listings]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_feature_listings'])) {
                    if ($user_details['can_feature_listings']) {
                        $sql_fields['userdb_can_feature_listings'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_feature_listings'] = 'no';
                    }
                }

                if (isset($user_details['can_view_logs']) && !is_bool($user_details['can_view_logs'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_view_logs]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_view_logs'])) {
                    if ($user_details['can_view_logs']) {
                        $sql_fields['userdb_can_view_logs'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_view_logs'] = 'no';
                    }
                }

                if (isset($user_details['can_moderate']) && !is_bool($user_details['can_moderate'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_moderate]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_moderate'])) {
                    if ($user_details['can_edit_site_config']) {
                        $sql_fields['userdb_can_edit_site_config'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_edit_site_config'] = 'no';
                    }
                }

                if (isset($user_details['can_edit_pages']) && !is_bool($user_details['can_edit_pages'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_edit_pages]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_edit_pages'])) {
                    if ($user_details['can_edit_pages']) {
                        $sql_fields['userdb_can_edit_pages'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_edit_pages'] = 'no';
                    }
                }

                if (isset($user_details['can_have_vtours']) && !is_bool($user_details['can_have_vtours'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_have_vtours]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_have_vtours'])) {
                    if ($user_details['can_have_vtours']) {
                        $sql_fields['userdb_can_have_vtours'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_have_vtours'] = 'no';
                    }
                }

                if (isset($user_details['can_edit_expiration']) && !is_bool($user_details['can_edit_expiration'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_edit_expiration]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_edit_expiration'])) {
                    if ($user_details['can_edit_expiration']) {
                        $sql_fields['userdb_can_edit_expiration'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_edit_expiration'] = 'no';
                    }
                }

                if (isset($user_details['can_export_listings']) && !is_bool($user_details['can_export_listings'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_export_listings]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_export_listings'])) {
                    if ($user_details['can_export_listings']) {
                        $sql_fields['userdb_can_export_listings'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_export_listings'] = 'no';
                    }
                }

                if (isset($user_details['can_edit_all_users']) && !is_bool($user_details['can_edit_all_users'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_edit_all_users]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_edit_all_users'])) {
                    if ($user_details['can_edit_all_users']) {
                        $sql_fields['userdb_can_edit_all_users'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_edit_all_users'] = 'no';
                    }
                }

                if (isset($user_details['can_edit_all_listings']) && !is_bool($user_details['can_edit_all_listings'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_edit_all_listings]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_edit_all_listings'])) {
                    if ($user_details['can_edit_all_listings']) {
                        $sql_fields['userdb_can_edit_all_listings'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_edit_all_listings'] = 'no';
                    }
                }

                if (isset($user_details['can_edit_property_classes']) && !is_bool($user_details['can_edit_property_classes'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_edit_property_classes]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_edit_property_classes'])) {
                    if ($user_details['can_edit_property_classes']) {
                        $sql_fields['userdb_can_edit_property_classes'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_edit_property_classes'] = 'no';
                    }
                }

                if (isset($user_details['can_have_files']) && !is_bool($user_details['can_have_files'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_have_files]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_have_files'])) {
                    if ($user_details['can_have_files']) {
                        $sql_fields['userdb_can_have_files'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_have_files'] = 'no';
                    }
                }

                if (isset($user_details['can_have_user_files']) && !is_bool($user_details['can_have_user_files'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_have_user_files]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_have_user_files'])) {
                    if ($user_details['can_have_user_files']) {
                        $sql_fields['userdb_can_have_user_files'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_have_user_files'] = 'no';
                    }
                }

                if (isset($user_details['can_manage_addons']) && !is_bool($user_details['can_manage_addons'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_manage_addons]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_manage_addons'])) {
                    if ($user_details['can_manage_addons']) {
                        $sql_fields['userdb_can_manage_addons'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_manage_addons'] = 'no';
                    }
                }

                if (isset($user_details['can_edit_all_leads']) && !is_bool($user_details['can_edit_all_leads'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_edit_all_leads]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_edit_all_leads'])) {
                    if ($user_details['can_edit_all_leads']) {
                        $sql_fields['userdb_can_edit_all_leads'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_edit_all_leads'] = 'no';
                    }
                }

                if (isset($user_details['can_edit_lead_template']) && !is_bool($user_details['can_edit_lead_template'])) {
                    return ['error' => true, 'error_msg' => 'user_details[can_edit_lead_template]: correct_parameter_not_passed'];
                } elseif (isset($user_details['can_edit_lead_template'])) {
                    if ($user_details['can_edit_lead_template']) {
                        $sql_fields['userdb_can_edit_lead_template'] = 'yes';
                    } else {
                        $sql_fields['userdb_can_edit_lead_template'] = 'no';
                    }
                }

                //
                //misc settings. These can also only be set by an Admin or an Agent with edit_all_users permissions
                if (isset($user_details['comments']) && empty($user_details['comments'])) {
                    return ['error' => true, 'error_msg' => 'user_details[comments]: correct_parameter_not_passed'];
                } elseif (isset($user_details['comments'])) {
                    $sql_fields['userdb_comments'] = $user_details['comments'];
                }

                if (isset($user_details['limit_listings']) && !is_int($user_details['limit_listings'])) {
                    return ['error' => true, 'error_msg' => 'user_details[limit_listings]: correct_parameter_not_passed'];
                } elseif (isset($user_details['limit_listings'])) {
                    $sql_fields['userdb_limit_listings'] = intval($user_details['limit_listings']);
                }

                if (isset($user_details['blog_user_type']) && !is_int($user_details['blog_user_type'])) {
                    return ['error' => true, 'error_msg' => 'user_details[blog_user_type]: correct_parameter_not_passed'];
                } elseif (isset($user_details['blog_user_type'])) {
                    $sql_fields['userdb_blog_user_type'] = intval($user_details['blog_user_type']);
                }

                if (isset($user_details['rank']) && !is_int($user_details['rank'])) {
                    return ['error' => true, 'error_msg' => 'user_details[rank]: correct_parameter_not_passed'];
                } elseif (isset($user_details['rank'])) {
                    $sql_fields['userdb_rank'] = intval($user_details['rank']);
                }

                if (isset($user_details['featuredlistinglimit']) && !is_int($user_details['featuredlistinglimit'])) {
                    return ['error' => true, 'error_msg' => 'user_details[featuredlistinglimit]: correct_parameter_not_passed'];
                } elseif (isset($user_details['featuredlistinglimit'])) {
                    $sql_fields['userdb_featuredlistinglimit'] = intval($user_details['featuredlistinglimit']);
                }

                if (isset($user_details['email_verified']) && !is_bool($user_details['email_verified'])) {
                    return ['error' => true, 'error_msg' => 'user_details[email_verified]: correct_parameter_not_passed'];
                } elseif (isset($user_details['email_verified'])) {
                    if ($user_details['email_verified']) {
                        $sql_fields['userdb_email_verified'] = 'yes';
                    } else {
                        $sql_fields['userdb_email_verified'] = 'no';
                    }
                }

                if (isset($user_details['send_notifications_to_floor']) && !is_bool($user_details['send_notifications_to_floor'])) {
                    return ['error' => true, 'error_msg' => 'user_details[send_notifications_to_floor]: correct_parameter_not_passed'];
                } elseif (isset($user_details['send_notifications_to_floor'])) {
                    if ($user_details['send_notifications_to_floor'] === true) {
                        $sql_fields['userdb_send_notifications_to_floor'] = '1';
                    } else {
                        $sql_fields['userdb_send_notifications_to_floor'] = '0';
                    }
                }
            }

            ////    }

            //echo '<pre>User Field Array: '.print_r($user_details,true).'</pre>';

            //update the last modified timestamp
            $user_details['last_modified'] = time();

            $sql_a = [];
            foreach ($sql_fields as $field => $value) {
                $sql_a[] = $field . ' = ' . $misc->make_db_safe($value);
            }

            $sql_a[]  = 'userdb_last_modified = ' . $conn->DBTimeStamp($user_details['last_modified']);

            $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb 
					SET ' . implode(',', $sql_a) . ' 
					WHERE userdb_id = ' . $userdb_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->user->update', 'log_message' => 'DB Error: ' . $error]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
            }

            //Deal with user fields
            if (isset($user_fields)) {
                //Get List of user fields
                $sql = 'SELECT ' . $resource . 'formelements_field_name, ' . $resource . 'formelements_id,' . $resource . 'formelements_field_type, ' . $resource . 'formelements_field_elements
							FROM  ' . $config['table_prefix'] . $resource . 'formelements';

                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->user->create', 'log_message' => 'DB Error: ' . $error]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }

                //Verify user fields passed via API exist in the applicable formelements table.
                $insert_user_fields = [];
                //print_r($user_fields);
                while (!$recordSet->EOF) {
                    $name = $recordSet->fields($resource . 'formelements_field_name');
                    if (array_key_exists($name, $user_fields)) {
                        $id = $recordSet->fields($resource . 'formelements_id');
                        $data_type = $recordSet->fields($resource . 'formelements_field_type');
                        $data_elements = $recordSet->fields($resource . 'formelements_field_elements');
                        switch ($data_type) {
                            case 'number':
                                if ($user_fields[$name] === '') {
                                    $insert_user_fields[$name] = null;
                                } else {
                                    if (is_numeric($user_fields[$name])) {
                                        $insert_user_fields[$name] = $user_fields[$name];
                                    } else {
                                        $price = str_replace(',', '', $user_fields[$name]);
                                        $insert_user_fields[$name] = intval($price);
                                    }
                                }
                                break;
                            case 'decimal':
                            case 'price':
                                if ($user_fields[$name] === '') {
                                    $insert_user_fields[$name] = null;
                                } else {
                                    $price = str_replace(',', '', $user_fields[$name]);
                                    $insert_user_fields[$name] = (float)$price;
                                }
                                break;
                            case 'date':
                                if ($user_fields[$name] === '') {
                                    $insert_user_fields[$name] = null;
                                } else {
                                    $insert_user_fields[$name] = $this->convert_date($user_fields[$name], $or_date_format);
                                }
                                break;
                            case 'select':
                            case 'select-multiple':
                            case 'option':
                            case 'checkbox':
                                //This is a lookup field. Make sure values passed are allowed by the system.
                                //Get Array of allowed data elements
                                $data_elements_array = explode('||', $data_elements);
                                //echo '<pre> Data Elements: '.print_r($data_elements_array,true).'</pre>';
                                //Get array of passed data eleements
                                if (!is_array($user_fields[$name])) {
                                    $t_value = $user_fields[$name];
                                    unset($user_fields[$name]);
                                    $user_fields[$name][] = $t_value;
                                }
                                //echo '<pre> Field Elements: '.print_r($user_fields[$name],true).'</pre>';
                                $good_elements = [];
                                foreach ($user_fields[$name] as $fvalue) {
                                    if (in_array($fvalue, $data_elements_array) && !in_array($fvalue, $good_elements)) {
                                        $good_elements[] = $fvalue;
                                    }
                                }
                                //echo '<pre> Good Elements: '.print_r($good_elements,true).'</pre>';
                                $insert_user_fields[$name] = $good_elements;
                                break;
                            default:
                                $insert_user_fields[$name] = $user_fields[$name];
                                break;
                        }
                    }
                    $recordSet->Movenext();
                }
                
                foreach ($insert_user_fields as $name => $value) {
                    $sql_name = $misc->make_db_safe($name);
                    if (is_array($value)) {
                        $sql_value = $misc->make_db_safe(implode('||', $value));
                    } else {
                        $sql_value = $misc->make_db_safe($value);
                    }
                    $sql = 'UPDATE ' . $config['table_prefix'] . "userdbelements 
							SET userdbelements_field_value = $sql_value 
							WHERE userdbelements_field_name = $sql_name 
							AND userdb_id = $userdb_id";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $error = $conn->ErrorMsg();
                        $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->user->update', 'log_message' => 'DB Error: ' . $error]);
                        return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                    }
                }
            }
            //Deal with Hooks and social sites
            if (isset($user_details['active']) && $user_details['active'] && $oldstatus == 'no') {
                include_once $config['basepath'] . '/include/hooks.inc.php';
                $hooks = new hooks();
                $hooks->load('after_activated_user', $userdb_id);
            }

            // ta da! we're done...
            $admin_status = $login->verify_priv('Admin');
            if ($admin_status == false) {
                $lapi->load_local_api('log__log_create_entry', [
                    '
					log_type' => 'CRIT',
                    'log_api_command' => 'api->user->update',
                    'log_message' => $lang['log_updated_user'] . ' ' . $userdb_id . ' by ' . $_SESSION['username'],
                ]);
            }
            //call the changed user change hook
            include_once $config['basepath'] . '/include/hooks.inc.php';
            $hooks = new hooks();
            $hooks->load('after_user_change', $userdb_id);
            return ['error' => false, 'userdb_id' => $userdb_id];
        } //end permission check
    }

    /**
     * This API Command deletes users.
     * @param array $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['user_id'] - Number - User ID to delete</li>
     *  <ul>
     */
    public function delete($data)
    {
        global $conn, $config, $lang, $lapi, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        $login_status = $login->verify_priv('edit_all_users');
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure'];
        }

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed
        if (!isset($user_id) || !is_numeric($user_id)) {
            return ['error' => true, 'error_msg' => 'user_id: ' . $lang['user_manager_invalid_user_id']];
        }

        // Set Variable to hold errors
        $errors = '';

        if ($config['demo_mode'] == 1 && $_SESSION['admin_privs'] != 'yes') {
            return ['error' => true, 'error_msg' => $lang['demo_mode'] . ' - ' . $lang['user_manager_permission_denied']];
        }

        // if this is the admin account, forget it
        if ($user_id === 1) {
            $errors .= $lang['admin_delete_warning'];
            return ['error' => true, 'error_msg' => $errors];
        }

        $is_admin = $misc->get_admin_status($user_id);

        // Admins can delete any user but Admin. Anyone can delete their own information as this is needed for updates.
        if (($_SESSION['admin_privs'] == 'yes' || $_SESSION['edit_all_users'] == 'yes') && $user_id != '') {
            $sql_delete = $conn->qstr($user_id);
            $delete_id = $user_id;
        } elseif (($_SESSION['admin_privs'] == 'yes' && $user_id == '') || ($_SESSION['userID'] == $user_id)) {
            $sql_delete = $conn->qstr($_SESSION['userID']);
            $delete_id = $_SESSION['userID'];
        } else {
            //return $lang['user_manager_permission_denied'];
            return ['error' => true, 'error_msg' => $lang['user_manager_permission_denied'] . '1'];
        }
        if ($is_admin && $_SESSION['admin_privs'] == 'no') {
            //return $lang['user_manager_permission_denied'];
            return ['error' => true, 'error_msg' => $lang['user_manager_permission_denied'] . '2'];
        }

        //delete all Agent photos
        $result = $lapi->load_local_api('media__delete', [
            'media_type' => 'userimages',
            'media_parent_id' => $user_id,
            'media_object_id' => '*',
        ]);

        if ($result['error'] == true) {
            die($result['error_msg']);
        }

        //delete all Agent Files
        $result = $lapi->load_local_api('media__delete', [
            'media_type' => 'usersfiles',
            'media_parent_id' => $user_id,
            'media_object_id' => '*',
        ]);
        if ($result['error'] == true) {
            die($result['error_msg']);
        }

        //get a list of this agent's listing ID#s
        $result = $lapi->load_local_api(
            'listing__search',
            [
                'parameters' => [
                    'user_ID' => $user_id,
                    'userdb_active' => 'any',
                ],
            ]
        );
        if ($result['error'] == true) {
            die($result['error_msg']);
        }

        $num_rows =  $result['listing_count'];

        if ($num_rows > 0) {
            //delete all associated listings
            foreach ($result['listings'] as $listing_id) {
                $result_d = $lapi->load_local_api('listing__delete', ['listing_id' => $listing_id]);
                if ($result_d['error']) {
                    $errors .= $result_d['error_msg'];
                }
                //$recordSet->MoveNext();
            }
        }
        if ($errors != '') {
            //return any $errors;
            return ['error' => true, 'error_msg' => $errors];
        }
        
        // delete all the favorites associated with a user
        $sql = 'DELETE FROM ' . $config['table_prefix'] . 'userfavoritelistings 
            WHERE (userdb_id = ' . $sql_delete . ')';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        // delete all the saved searches associated with a user
        $sql = 'DELETE FROM ' . $config['table_prefix'] . 'usersavedsearches 
                WHERE (userdb_id = ' . $sql_delete . ')';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
    
        // delete all the elements associated with the user
        $sql = 'DELETE FROM ' . $config['table_prefix'] . 'userdbelements 
				WHERE userdb_id = ' . $sql_delete;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        
        // delete the user
        $sql = 'DELETE FROM ' . $config['table_prefix'] . 'userdb 
        WHERE userdb_id = ' . $sql_delete;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }

        // ta da! we're done...
        $lapi->load_local_api('log__log_create_entry', [
            'log_type' => 'CRIT',
            'log_api_command' => 'api->users->delete',
            'log_message' => $lang['log_deleted_user'] . ' ' . $user_id . ' by ' . $_SESSION['username'],
        ]);

        include_once $config['basepath'] . '/include/hooks.inc.php';
        $hooks = new hooks();
        $hooks->load('after_user_delete', $delete_id);

        //success
        return ['error' => false, 'user_id' => $user_id];
    }

    /*
    *
    *   PRIVATE FUNCTIONS
    */
    // Updates the user fields (userdbelements) info
    private function update_userdata($user_id, $formdata)
    {
        global $conn, $lang, $config, $misc;

        $sql_user_id = intval($user_id);

        $sql = 'DELETE FROM ' . $config['table_prefix'] . 'userdbelements 
				WHERE userdb_id = ' . $sql_user_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }

        $is_admin = $misc->get_admin_status($user_id);
        $is_agent = $misc->get_agent_status($user_id);

        if ($is_agent || $is_admin) {
            $db_to_use = 'agent';
        } else {
            $db_to_use = 'member';
        }

        foreach ($formdata as $ElementIndexValue => $ElementContents) {
            $sql2 = 'SELECT ' . $db_to_use . 'formelements_field_type 
					FROM ' . $config['table_prefix'] . $db_to_use . 'formelements 
					WHERE ' . $db_to_use . "formelements_field_name='" . $ElementIndexValue . "'";
            $recordSet2 = $conn->Execute($sql2);
            if (!$recordSet2) {
                $misc->log_error($sql2);
            }
            if ($recordSet2->RecordCount() == 1) {
                $field_type = $recordSet2->fields($db_to_use . 'formelements_field_type');
                // first, ignore all the stuff that's been taken care of above
                if ($ElementIndexValue == 'user_user_name' || $ElementIndexValue == 'edit_user_pass' || $ElementIndexValue == 'edit_user_pass2' || $ElementIndexValue == 'user_email' || $ElementIndexValue == 'PHPSESSID' || $ElementIndexValue == 'edit' || $ElementIndexValue == 'edit_isAdmin' || $ElementIndexValue == 'edit_active' || $ElementIndexValue == 'edit_isAgent' || $ElementIndexValue == 'edit_limitListings' || $ElementIndexValue == 'edit_canEditSiteConfig' || $ElementIndexValue == 'edit_canMemberTemplate' || $ElementIndexValue == 'edit_canAgentTemplate' || $ElementIndexValue == 'edit_canListingTemplate' || $ElementIndexValue == 'edit_canViewLogs' || $ElementIndexValue == 'edit_canModerate' || $ElementIndexValue == 'edit_canFeatureListings' || $ElementIndexValue == 'edit_canPages' || $ElementIndexValue == 'edit_canVtour' || $ElementIndexValue == 'edit_canFiles' || $ElementIndexValue == 'edit_canUserFiles') {
                    // do nothing
                }
                // this is currently set up to handle two feature lists
                // it could easily handle more...
                // just write handlers for 'em
                elseif (is_array($ElementContents)) {
                    // deal with checkboxes & multiple selects elements
                    $feature_insert = '';
                    foreach ($ElementContents as $feature_item) {
                        $feature_insert = $feature_insert . '||' . $feature_item;
                    } // end foreach
                    // now remove the first two characters
                    $feature_insert_length = strlen($feature_insert);
                    $feature_insert_length = $feature_insert_length - 2;
                    $feature_insert = substr($feature_insert, 2, $feature_insert_length);
                    $sql_ElementIndexValue = $misc->make_db_safe($ElementIndexValue);
                    $sql_feature_insert = $misc->make_db_safe($feature_insert);
                    $sql = 'INSERT INTO ' . $config['table_prefix'] . 'userdbelements (userdbelements_field_name, userdbelements_field_value, userdb_id) 
							VALUES (' . $sql_ElementIndexValue . ', ' . $sql_feature_insert . ', ' . $sql_user_id . ')';

                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                } // end elseif
                else {
                    // it's time to actually insert the form data into the db
                    $sql_ElementIndexValue = $misc->make_db_safe($ElementIndexValue);
                    $sql_ElementContents = $misc->make_db_safe($ElementContents);

                    if ($field_type == 'date' && $ElementContents != '') {
                        if ($config['date_format'] == 1) {
                            $format = '%m/%d/%Y';
                        } elseif ($config['date_format'] == 2) {
                            $format = '%Y/%d/%m';
                        } elseif ($config['date_format'] == 3) {
                            $format = '%d/%m/%Y';
                        }
                        $returnValue = $misc->parseDate($ElementContents, $format);
                        $sql_ElementContents = $misc->make_db_safe($returnValue);
                    }
                    $sql = 'INSERT INTO ' . $config['table_prefix'] . 'userdbelements (userdbelements_field_name, userdbelements_field_value, userdb_id) 
							VALUES (' . $sql_ElementIndexValue . ', ' . $sql_ElementContents . ', ' . $sql_user_id . ')';
                    $recordSet = $conn->Execute($sql);
                } // end else
            }
        } // end while
        //success
        return true;
    }

    private function convert_date($date, $format)
    {
        global $config;
        //Supported formats
        //%Y - year as a decimal number including the century
        //%m - month as a decimal number (range 01 to 12)
        //%d - day of the month as a decimal number (range 01 to 31)
        //%H - hour as a decimal number using a 24-hour clock (range 00 to 23)
        //%M - minute as a decimal number
        // Builds up date pattern from the given $format, keeping delimiters in place.
        //echo 'Date: "'.$date.'"';
        //echo 'Format "'.$format.'"';
        if (!preg_match_all('/%([YmdHMp])([^%])*/', $format, $formatTokens, PREG_SET_ORDER)) {
            //return 'BAD FORMAT';
            return false;
        }
        $datePattern = '';
        //echo '<pre>'.print_r($formatTokens,true).'</pre>';
        foreach ($formatTokens as $formatToken) {
            if (isset($formatToken[2])) {
                $delimiter = preg_quote($formatToken[2], '/');
            } else {
                $delimiter = '';
            }

            $datePattern .= '(.*)' . $delimiter;
        }
        // Splits up the given $date
        if (!preg_match('/' . $datePattern . '/', $date, $dateTokens)) {
            //return 'BAD SPLIT';
            return false;
        }
        $dateSegments = [];
        $formatTokenCount = count($formatTokens);
        for ($i = 0; $i < $formatTokenCount; $i++) {
            $dateSegments[$formatTokens[$i][1]] = $dateTokens[$i + 1];
        }
        // Reformats the given $date into US English date format, suitable for strtotime()
        if ($dateSegments['Y'] && $dateSegments['m'] && $dateSegments['d']) {
            $dateReformated = $dateSegments['Y'] . '-' . $dateSegments['m'] . '-' . $dateSegments['d'];
        } else {
            //return 'BAD DATE';
            return false;
        }
        if (isset($dateSegments['H']) && isset($dateSegments['M'])) {
            $dateReformated .= ' ' . $dateSegments['H'] . ':' . $dateSegments['M'];
        }

        return strtotime($dateReformated);
    }
}
