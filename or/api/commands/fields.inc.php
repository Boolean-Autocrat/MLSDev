<?php

/**
 * This File Contains the Listing Field API Commands
 * @package Open-Realty
 * @subpackage API
 * @author Ryan C. Bonham
 * @copyright 2010
 * @link http://www.open-realty.com Open-Realty
 */

/**
 * This is the listings Field API, it contains all api calls for creating and retrieving listing field data.
 *
 * @package Open-Realty
 * @subpackage API
 **/
class fields_api
{
    private $field_types = ['text', 'textarea', 'select', 'select-multiple', 'option', 'checkbox', 'divider', 'price', 'url', 'email', 'number', 'decimal', 'date', 'lat', 'long'];
    private $search_type = ['ptext', 'optionlist', 'optionlist_or', 'fcheckbox', 'fcheckbox_or', 'fpulldown', 'select', 'select_or', 'pulldown', 'checkbox', 'checkbox_or', 'option', 'minmax', 'daterange', 'singledate', 'null_checkbox', 'notnull_checkbox'];
    /*
    * 0 = 'All Visitors';
    *   $lang['display_priv_1'] = 'Members and Agents';
    *   $lang['display_priv_2'] = 'Agents Only';
    *   3 = Admin Only
    */
    private $display_priv = [0, 1, 2, 3];

    /**
     * Get field metadata(information)
     *
     * Example
     * <code>
     * //Call the API and Get all Field for Property Class 1
     * $pclass_list=array(1);
     * $api_result = $lapi->load_local_api('fields__metadata',array('resource'=>'listing','class'=>$pclass_list));
     * if($api_result['error']){
     *  //If an error occurs die and show the error msg;
     *  die($api_result['error_msg']);
     * }
     * //No error so get the fields that were returned..
     * $field_list = $api_result['fields']
     * </code>
     *
     *
     * @param array $data $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['resource'] - Text - What resource do you want to get fields for. Allowed Options are "listing".</li>
     *      <li>$data['class'] - Array - Optional array of property class IDs. Only fields assigned to these IDs will be returned.</li>
     *      <li>$data['field_id'] - Integer - Optional Field ID. If set only the metadata for this field is returned..</li>
     *      <li>$data['searchable_only'] - Boolean - Optional - If Set only searchable fields are returned.</li>
     *  </ul>
     * @return array
     */
    public function metadata($data)
    {
        global $conn, $lang, $config, $lapi;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed

        if (!isset($resource) || $resource != 'listing' && $resource != 'agent' && $resource != 'member' && $resource != 'feedback') {
            return ['error' => true, 'error_msg' => 'resource: correct_parameter_not_passed'];
        }
        if (isset($class) && !is_array($class)) {
            return ['error' => true, 'error_msg' => 'class: correct_parameter_not_passed'];
        }
        if (isset($field_id) && !is_numeric($field_id)) {
            return ['error' => true, 'error_msg' => 'field_id: correct_parameter_not_passed'];
        }
        if (!isset($searchable_only) || !is_bool($searchable_only)) {
            $searchable_only = false;
        }
        if (!isset($browseable_only) || !is_bool($browseable_only)) {
            $browseable_only = false;
        }
        if ($resource == 'listing') {
            $sql = 'SELECT  * FROM ' . $config['table_prefix'] . 'listingsformelements';
            $sql_where = [];
            $order_by = '';
            $order_by = 'ORDER BY listingsformelements_rank';
            if (isset($field_id)) {
                $sql_where[] = 'listingsformelements_id = ' . $field_id;
            }
            if (!empty($class)) {
                if (count($class) > 1) {
                    $sql .= ' as lfe LEFT JOIN ' . $config['table_prefix_no_lang'] . 'classformelements  as cfe
					ON lfe.listingsformelements_id = cfe.listingsformelements_id';
                    $sql_where[] = 'class_id IN (' . implode(',', $class) . ')';
                } else {
                    if (intval($class[0]) > 0) {
                        $sql .= ' as lfe LEFT JOIN ' . $config['table_prefix_no_lang'] . 'classformelements  as cfe
					ON lfe.listingsformelements_id = cfe.listingsformelements_id';
                        $sql_where[] = 'class_id = ' . $class[0];
                    }
                }
            }
            if ($searchable_only == true) {
                $sql_where[] = 'listingsformelements_searchable = 1';
                $order_by = 'ORDER BY listingsformelements_search_rank';
            }
            if ($browseable_only == true) {
                $sql_where[] = 'listingsformelements_display_on_browse = \'Yes\'';
                $order_by = 'ORDER BY listingsformelements_search_result_rank';
            }
            //Show only fields that are visible to the user
            $display_status = false;

            $display_status = $login->verify_priv('Admin');
            if ($display_status != true) {
                $display_status = $login->verify_priv('Agent');
                if ($display_status == true) {
                    $sql_where[] = '(listingsformelements_display_priv = 0 OR listingsformelements_display_priv = 1 OR listingsformelements_display_priv = 2)';
                } else {
                    $display_status = $login->verify_priv('Member');
                    if ($display_status == true) {
                        $sql_where[] = '(listingsformelements_display_priv = 0 OR listingsformelements_display_priv = 1)';
                    } else {
                        $sql_where[] = 'listingsformelements_display_priv = 0';
                    }
                }
            } else {
                $sql_where[] = '(listingsformelements_display_priv = 0 OR listingsformelements_display_priv = 1 OR listingsformelements_display_priv = 2 OR listingsformelements_display_priv = 3)';
            }
            $sql .= ' WHERE ' . implode(' AND ', $sql_where) . ' ' . $order_by;

            $recordSet = $conn->GetAll($sql);
            //echo $sql;
            if ($recordSet === false) {
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->metadata', 'log_message' => 'Listing Metadata DB Error: ' . $sql]);
                return ['error' => true, 'error_msg' => 'Fields Metadata API DB Error: ' . $sql];
            }
            $fields = [];
            foreach ($recordSet as $row) {
                //print_r($row);die;
                $fields[$row['listingsformelements_id']]['field_id'] = $row['listingsformelements_id'];
                $fields[$row['listingsformelements_id']]['field_type'] = $row['listingsformelements_field_type'];
                $fields[$row['listingsformelements_id']]['field_name'] = $row['listingsformelements_field_name'];
                $fields[$row['listingsformelements_id']]['field_caption'] = $row['listingsformelements_field_caption'];
                $fields[$row['listingsformelements_id']]['default_text'] = $row['listingsformelements_default_text'];
                $fields[$row['listingsformelements_id']]['field_elements'] = explode('||', $row['listingsformelements_field_elements']);
                $fields[$row['listingsformelements_id']]['rank'] = $row['listingsformelements_rank'];
                $fields[$row['listingsformelements_id']]['search_rank'] = $row['listingsformelements_search_rank'];
                $fields[$row['listingsformelements_id']]['search_result_rank'] = $row['listingsformelements_search_result_rank'];
                $fields[$row['listingsformelements_id']]['required'] = $row['listingsformelements_required'];
                $fields[$row['listingsformelements_id']]['location'] = $row['listingsformelements_location'];
                $fields[$row['listingsformelements_id']]['display_on_browse'] = $row['listingsformelements_display_on_browse'];
                $fields[$row['listingsformelements_id']]['searchable'] = $row['listingsformelements_searchable'];
                $fields[$row['listingsformelements_id']]['search_type'] = $row['listingsformelements_search_type'];
                $fields[$row['listingsformelements_id']]['search_label'] = $row['listingsformelements_search_label'];
                $fields[$row['listingsformelements_id']]['search_step'] = $row['listingsformelements_search_step'];
                $fields[$row['listingsformelements_id']]['display_priv'] = $row['listingsformelements_display_priv'];
                $fields[$row['listingsformelements_id']]['field_length'] = $row['listingsformelements_field_length'];
                $fields[$row['listingsformelements_id']]['tool_tip'] = $row['listingsformelements_tool_tip'];
            }
        }
        if ($resource == 'agent') {
            $sql = 'SELECT  * FROM ' . $config['table_prefix'] . 'agentformelements';
            $sql_where = [];
            $order_by = '';
            $order_by = 'ORDER BY agentformelements_rank';
            if (isset($field_id)) {
                $sql_where[] = 'agentformelements_id = ' . $field_id;
            }

            //Show only fields that are visible to the user
            $display_status = false;

            $display_status = $login->verify_priv('Admin');
            if ($display_status != true) {
                $display_status = $login->verify_priv('Agent');
                if ($display_status == true) {
                    $sql_where[] = '(agentformelements_display_priv = 0 OR agentformelements_display_priv = 1 OR agentformelements_display_priv = 2)';
                } else {
                    $display_status = $login->verify_priv('Member');
                    if ($display_status == true) {
                        $sql_where[] = '(agentformelements_display_priv = 0 OR agentformelements_display_priv = 1)';
                    } else {
                        $sql_where[] = 'agentformelements_display_priv = 0';
                    }
                }
            } else {
                $sql_where[] = '(agentformelements_display_priv = 0 OR agentformelements_display_priv = 1 OR agentformelements_display_priv = 2 OR agentformelements_display_priv = 3)';
            }
            $sql .= ' WHERE ' . implode(' AND ', $sql_where) . ' ' . $order_by;

            $recordSet = $conn->GetAll($sql);
            //echo $sql;
            if (!$recordSet) {
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->metadata', 'log_message' => 'Agent Matadata DB Error: ' . $sql]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $sql];
            }
            $fields = [];
            foreach ($recordSet as $row) {
                //print_r($row);die;
                $fields[$row['agentformelements_id']]['field_id'] = $row['agentformelements_id'];
                $fields[$row['agentformelements_id']]['field_type'] = $row['agentformelements_field_type'];
                $fields[$row['agentformelements_id']]['field_name'] = $row['agentformelements_field_name'];
                $fields[$row['agentformelements_id']]['field_caption'] = $row['agentformelements_field_caption'];
                $fields[$row['agentformelements_id']]['default_text'] = $row['agentformelements_default_text'];
                $fields[$row['agentformelements_id']]['field_elements'] = explode('||', $row['agentformelements_field_elements']);
                $fields[$row['agentformelements_id']]['rank'] = $row['agentformelements_rank'];
                $fields[$row['agentformelements_id']]['required'] = $row['agentformelements_required'];
                $fields[$row['agentformelements_id']]['display_priv'] = $row['agentformelements_display_priv'];
                $fields[$row['agentformelements_id']]['tool_tip'] = $row['agentformelements_tool_tip'];
            }
        }
        if ($resource == 'member') {
            $sql = 'SELECT  * FROM ' . $config['table_prefix'] . 'memberformelements ';
            $sql_where = [];
            $order_by = '';
            $order_by = 'ORDER BY memberformelements_rank';
            if (isset($field_id)) {
                $sql_where[] = 'memberformelements = ' . $field_id;
                $sql .= ' WHERE ' . implode(' AND ', $sql_where) . ' ' . $order_by;
            } else {
                $sql .= $order_by;
            }

            //$sql .= ' WHERE '.implode(' AND ',$sql_where).' '.$order_by;

            $recordSet = $conn->GetAll($sql);
            //echo $sql;
            if (!$recordSet) {
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->metadata', 'log_message' => 'Member Metadata DB Error: ' . $sql]);
                return ['error' => true, 'error_msg' => 'Member Resource DB Error: ' . $sql];
            }
            $fields = [];
            foreach ($recordSet as $row) {
                //print_r($row);die;
                $fields[$row['memberformelements_id']]['field_id'] = $row['memberformelements_id'];
                $fields[$row['memberformelements_id']]['field_type'] = $row['memberformelements_field_type'];
                $fields[$row['memberformelements_id']]['field_name'] = $row['memberformelements_field_name'];
                $fields[$row['memberformelements_id']]['field_caption'] = $row['memberformelements_field_caption'];
                $fields[$row['memberformelements_id']]['default_text'] = $row['memberformelements_default_text'];
                $fields[$row['memberformelements_id']]['field_elements'] = explode('||', $row['memberformelements_field_elements']);
                $fields[$row['memberformelements_id']]['rank'] = $row['memberformelements_rank'];
                $fields[$row['memberformelements_id']]['required'] = $row['memberformelements_required'];
                $fields[$row['memberformelements_id']]['tool_tip'] = $row['memberformelements_tool_tip'];
            }
        }
        if ($resource == 'feedback') {
            $sql = 'SELECT  * FROM ' . $config['table_prefix'] . 'feedbackformelements ';
            $sql_where = [];
            $order_by = '';
            $order_by = 'ORDER BY feedbackformelements_rank';
            if (isset($field_id)) {
                $sql_where[] = 'feedbackformelements_id = ' . $field_id;
            }

            $sql .= ' WHERE ' . implode(' AND ', $sql_where) . ' ' . $order_by;

            $recordSet = $conn->GetAll($sql);
            //echo $sql;
            if (!$recordSet) {
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->metadata', 'log_message' => 'Feedback Metadata DB Error: ' . $sql]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $sql];
            }
            $fields = [];
            foreach ($recordSet as $row) {
                //print_r($row);die;
                $fields[$row['feedbackformelements_id']]['field_id'] = $row['feedbackformelements_id'];
                $fields[$row['feedbackformelements_id']]['field_type'] = $row['feedbackformelements_field_type'];
                $fields[$row['feedbackformelements_id']]['field_name'] = $row['feedbackformelements_field_name'];
                $fields[$row['feedbackformelements_id']]['field_caption'] = $row['feedbackformelements_field_caption'];
                $fields[$row['feedbackformelements_id']]['default_text'] = $row['feedbackformelements_default_text'];
                $fields[$row['feedbackformelements_id']]['field_elements'] = explode('||', $row['feedbackformelements_field_elements']);
                $fields[$row['feedbackformelements_id']]['rank'] = $row['feedbackformelements_rank'];
                $fields[$row['feedbackformelements_id']]['required'] = $row['feedbackformelements_required'];
                $fields[$row['feedbackformelements_id']]['location'] = $row['feedbackformelements_location'];
                $fields[$row['feedbackformelements_id']]['tool_tip'] = $row['feedbackformelements_tool_tip'];
            }
        }

        return ['error' => false, 'fields' => $fields];
    }
    public function values($data)
    {
        //TODO Add ability to limit fields to member only fields
        global $conn, $lang, $config, $lapi, $misc;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed
        if (!isset($field_name)) {
            return ['error' => true, 'error_msg' => 'field_name: correct_parameter_not_passed'];
        }
        if (!isset($field_type)) {
            return ['error' => true, 'error_msg' => 'field_type: correct_parameter_not_passed'];
        }
        if (isset($pclass) && !is_array($pclass)) {
            return ['error' => true, 'error_msg' => 'pclass: correct_parameter_not_passed'];
        }
        $class_sql = '';
        $class_sql_array = [];
        foreach ($pclass as $class_id) {
            if ($class_id > 0) {
                $class_sql_array[] = $config['table_prefix'] . 'listingsdb.listingsdb_pclass_id = ' . intval($class_id);
            }
        }
        if (!empty($class_sql_array)) {
            $class_sql .= ' AND (' . implode(' OR ', $class_sql_array) . ')';
        }
        $sql_field_name = $misc->make_db_safe($field_name);
        switch ($field_type) {
            case 'decimal':
                $sortby = 'ORDER BY listingsdbelements_field_value+0 ASC';
                break;
            case 'number':
                global $db_type;
                if ($db_type == 'mysql' || $db_type == 'mysqli' || $db_type == 'pdo') {
                    $sortby = 'ORDER BY CAST(listingsdbelements_field_value as signed) ASC';
                } else {
                    $sortby = 'ORDER BY CAST(listingsdbelements_field_value as int4) ASC';
                }
                break;
            default:
                $sortby = 'ORDER BY listingsdbelements_field_value ASC';
                break;
        }

        if ($config['configured_show_count'] == 1) {
            $sql = 'SELECT listingsdbelements_field_value, count(listingsdbelements_field_value)
					AS num_type
					FROM ' . $config['table_prefix'] . 'listingsdbelements, ' . $config['table_prefix'] . 'listingsdb
					WHERE listingsdbelements_field_name = ' . $sql_field_name . '
					AND listingsdb_active = \'yes\' AND listingsdbelements_field_value <> \'\'
					AND ' . $config['table_prefix'] . 'listingsdbelements.listingsdb_id = ' . $config['table_prefix'] . 'listingsdb.listingsdb_id ' . $class_sql;
        } else {
            $sql = 'SELECT listingsdbelements_field_value
					FROM ' . $config['table_prefix'] . 'listingsdbelements, ' . $config['table_prefix'] . 'listingsdb
					WHERE listingsdbelements_field_name = ' . $sql_field_name . '
					AND listingsdb_active = \'yes\' AND listingsdbelements_field_value <> \'\'
					AND ' . $config['table_prefix'] . 'listingsdbelements.listingsdb_id = ' . $config['table_prefix'] . 'listingsdb.listingsdb_id ' . $class_sql;
        }

        if ($config['use_expiration'] === '1') {
            $sql .= ' AND listingsdb_expiration > ' . $conn->DBDate(time());
        }

        $sql .= ' GROUP BY ' . $config['table_prefix'] . 'listingsdbelements.listingsdbelements_field_value ' . $sortby;
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->read_distinct_values', 'log_message' => 'DB Error: ' . $sql]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $sql];
        }
        //echo $sql;
        $field_values = [];
        $field_counts = [];
        while (!$recordSet->EOF) {
            $field_values[] = $recordSet->fields('listingsdbelements_field_value');
            if ($config['configured_show_count'] == 1) {
                $field_counts[$recordSet->fields('listingsdbelements_field_value')] = $recordSet->fields('num_type');
            }
            $recordSet->MoveNext();
        }
        return ['error' => false, 'field_values' => $field_values, 'field_counts' => $field_counts];
    }
    /**
     * API command to insert a listing field into the class.
     *
     * @param array $data $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['resource'] - Text - What type of resource are you creating a field for. Allowed Options are "listing".</li>
     *      <li>$data['class'] - Array - Array of property class ID that you want to insert an listing field into.</li>
     *      <li>$data['field_type'] - Text - Type of field to create. Valid options are 'text','textarea','select','select-multiple','option','checkbox','divider','price','url','email','number','decimal','date','lat','long' </li>
     *      <li>$data['field_name'] - Text - Name of field to create</li>
     *      <li>$data['field_caption'] - Text - Caption to display for field.</li>
     *      <li>$data['field_elements'] - Array - Array of options if this is a select type of field.</li>
     *      <li>$data['rank'] - Numberic - Order of Field.</li>
     *      <li>$data['search_rank'] - Numberic - Order of Field on Search pages.</li>
     *      <li>$data['search_result_rank'] - Numberic - Order of field on search results page.</li>
     *      <li>$data['required'] - Boolean - Field is Reuired TRUE/FALSE</li>
     *      <li>$data['location'] - Text - Template Location to display field at.</li>
     *      <li>$data['display_on_browse'] - Boolean - Should this field be displayed on the search results page?.</li>
     *      <li>$data['search_step'] - Numberic - Value to step search values by for min/max fields</li>
     *      <li>$data['display_priv'] - Numberic - 0 = Show Field to All Visitors, 1 = members &amp; Agents, 2 = Agents Only, 3 = Admin Only</li>
     *      <li>data['field_length'] - Numberic - Maximum Length of Field</li>
     *      <li>$data['tool_tip'] - Text -Tooltip to show</li>
     *      <li>$data['search_label'] - Text - Label to show user when searching this field</li>
     *      <li>$data['search_type'] - Text - Allowed Values 'ptext','optionlist','optionlist_or','fcheckbox','fcheckbox_or','fpulldown','select','select_or','pulldown','checkbox','checkbox_or','option','minmax','daterange','singledate','null_checkbox','notnull_checkbox'</li>
     *      <li>$data[$searchable'] - Boolean - Is the field searchable?</li>
     *  </ul>
     * @return array
     */

    public function create($data)
    {
        global $conn, $lang, $config, $lapi, $misc;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->verify_priv('Agent');
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure'];
        }
        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed

        if (!isset($resource) || $resource != 'listing') {
            return ['error' => true, 'error_msg' => 'resource: correct_parameter_not_passed'];
        }
        if (!isset($class) || !is_array($class)) {
            return ['error' => true, 'error_msg' => 'class: correct_parameter_not_passed'];
        }
        if (!isset($field_type) || !in_array($field_type, $this->field_types)) {
            return ['error' => true, 'error_msg' => 'field_type: correct_parameter_not_passed'];
        }
        if (!isset($field_name) || trim($field_name) == '') {
            return ['error' => true, 'error_msg' => 'field_name: correct_parameter_not_passed'];
        }
        if (!isset($field_caption)) {
            return ['error' => true, 'error_msg' => 'field_caption: correct_parameter_not_passed'];
        }
        if (!isset($default_text)) {
            return ['error' => true, 'error_msg' => 'default_text: correct_parameter_not_passed'];
        }
        if (!isset($field_elements) || !is_array($field_elements)) {
            return ['error' => true, 'error_msg' => 'field_elements: correct_parameter_not_passed'];
        }
        if (!isset($rank) || !is_numeric($rank)) {
            return ['error' => true, 'error_msg' => 'rank: correct_parameter_not_passed'];
        }
        if (!isset($search_rank) || !is_numeric($search_rank)) {
            return ['error' => true, 'error_msg' => 'search_rank: correct_parameter_not_passed'];
        }
        if (!isset($search_result_rank) || !is_numeric($search_result_rank)) {
            return ['error' => true, 'error_msg' => 'search_result_rank: correct_parameter_not_passed'];
        }
        if (!isset($required) || !is_bool($required)) {
            return ['error' => true, 'error_msg' => 'required: correct_parameter_not_passed'];
        }

        if (!isset($location)) {
            return ['error' => true, 'error_msg' => 'location: correct_parameter_not_passed'];
        }
        if (!isset($display_on_browse) || !is_bool($display_on_browse)) {
            return ['error' => true, 'error_msg' => 'display_on_browse: correct_parameter_not_passed'];
        }
        if (!isset($search_step) || !is_numeric($search_step)) {
            return ['error' => true, 'error_msg' => 'search_step: correct_parameter_not_passed'];
        }
        if (!isset($display_priv) || !in_array($display_priv, $this->display_priv)) {
            return ['error' => true, 'error_msg' => 'display_priv: correct_parameter_not_passed'];
        }
        if (!isset($field_length) || !is_numeric($field_length)) {
            return ['error' => true, 'error_msg' => 'field_length: correct_parameter_not_passed'];
        }
        if (!isset($tool_tip)) {
            return ['error' => true, 'error_msg' => 'tool_tip: correct_parameter_not_passed'];
        }
        if (!isset($search_label)) {
            return ['error' => true, 'error_msg' => 'search_label: correct_parameter_not_passed'];
        }
        if (!isset($search_type) || ($search_type != '' && !in_array($search_type, $this->search_type))) {
            return ['error' => true, 'error_msg' => 'search_type: correct_parameter_not_passed'];
        }
        if (!isset($searchable) || !is_bool($searchable)) {
            return ['error' => true, 'error_msg' => 'searchable: correct_parameter_not_passed'];
        }

        $security = $login->verify_priv('edit_listing_template');
        if ($security !== true) {
            return ['error' => true, 'error_msg' => 'permission_denied'];
        }
        //Make data db safe

        $sql_field_type = $misc->make_db_safe($field_type);
        $field_name = str_replace(' ', '_', $field_name);
        $sql_field_name = $misc->make_db_safe($field_name);

        $sql_field_caption = $misc->make_db_safe($field_caption);
        $sql_default_text = $misc->make_db_safe($default_text);
        $field_elements = implode('||', $field_elements);
        $sql_field_elements = $misc->make_db_safe($field_elements);
        $sql_rank = intval($rank);
        $sql_search_rank = intval($search_rank);
        $sql_search_result_rank = intval($search_result_rank);

        if ($required) {
            $sql_required = $misc->make_db_safe('Yes');
        } else {
            $sql_required = $misc->make_db_safe('No');
        }
        $sql_location = $misc->make_db_safe($location);
        if ($display_on_browse) {
            $sql_display_on_browse = $misc->make_db_safe('Yes');
        } else {
            $sql_display_on_browse = $misc->make_db_safe('No');
        }
        $sql_searchable = intval($searchable);
        $sql_tool_tip = $misc->make_db_safe($tool_tip);
        $sql_search_label = $misc->make_db_safe($search_label);
        $sql_search_type = $misc->make_db_safe($search_type);
        //Check for duplicate field names
        $sql = 'SELECT listingsformelements_field_name FROM ' . $config['table_prefix'] . 'listingsformelements WHERE listingsformelements_field_name = ' . $sql_field_type;
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->create', 'log_message' => 'DB Error: ' . $sql]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $sql];
        }
        if ($recordSet->RecordCount() > 0) {
            return ['error' => true, 'error_msg' => 'Field Already Exists: ' . $field_name];
        }
        $sql = 'INSERT INTO ' . $config['table_prefix'] . "listingsformelements
			(listingsformelements_field_type, listingsformelements_field_name, listingsformelements_field_caption, listingsformelements_default_text,
			listingsformelements_field_elements, listingsformelements_rank, listingsformelements_search_rank, listingsformelements_search_result_rank,
			listingsformelements_required, listingsformelements_location, listingsformelements_display_on_browse, listingsformelements_search_step,
			listingsformelements_searchable, listingsformelements_search_label, listingsformelements_search_type,listingsformelements_display_priv,
			listingsformelements_field_length, listingsformelements_tool_tip) VALUES
			($sql_field_type,$sql_field_name,$sql_field_caption,$sql_default_text,$sql_field_elements,$sql_rank,$sql_search_rank,$sql_search_result_rank,$sql_required,
			$sql_location,$sql_display_on_browse,$search_step,$sql_searchable,$sql_search_label,$sql_search_type,$display_priv, $field_length, $sql_tool_tip)";
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->create', 'log_message' => 'DB Error: ' . $sql]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $sql];
        }

        $listingsformelements_id = $conn->Insert_ID();

        // Add Listing Field to property class
        foreach ($class as $class_id) {
            $sql = 'INSERT INTO ' . $config['table_prefix_no_lang'] . 'classformelements
								(class_id,listingsformelements_id)
								VALUES (' . $class_id . ',' . $listingsformelements_id . ')';
            $recordSet = $conn->Execute($sql);
            if ($recordSet === false) {
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->create', 'log_message' => 'DB Error: ' . $sql]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $sql];
            }
            $aftc_result = $this->add_fields_to_class($class_id, $field_name);
            if ($aftc_result['error']) {
                return ['error' => true, 'error_msg' => $aftc_result['error_msg']];
            }
        }

        return ['error' => false, 'field_id' => $listingsformelements_id];
    }
    private function add_fields_to_class($class, $field_name)
    {
        global $conn, $lang, $config, $lapi, $misc;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->verify_priv('Agent');
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure'];
        }
        $sql_field_name = $misc->make_db_safe($field_name);
        //Get List of Listings in Class that already contain the field.
        $sql = 'SELECT listingsdb_id FROM ' . $config['table_prefix'] . 'listingsdbelements WHERE listingsdbelements_field_name = ' . $sql_field_name . ' AND listingsdb_id IN (SELECT  listingsdb_id FROM ' . $config['table_prefix'] . 'listingsdb WHERE listingsdb_pclass_id = ' . intval($class) . ')';

        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->add_fields_to_class', 'log_message' => 'DB Error: ' . $sql]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $sql];
        }
        $skip_listings = [];

        while (!$recordSet->EOF) {
            $skip_listings[] = $recordSet->fields('listingsdb_id');
            $recordSet->MoveNext();
        }
        //Get All Listings in Class
        $sql = 'SELECT listingsdb_id FROM ' . $config['table_prefix'] . 'listingsdb WHERE listingsdb_pclass_id = ' . intval($class);
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->add_fields_to_class', 'log_message' => 'DB Error: ' . $sql]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $sql];
        }
        $all_listings = [];

        while (!$recordSet->EOF) {
            $all_listings[] = $recordSet->fields('listingsdb_id');
            $recordSet->MoveNext();
        }
        $do_listings = array_diff($all_listings, $skip_listings);

        foreach ($do_listings as $listing_id) {
            $sql = 'INSERT INTO ' . $config['table_prefix'] . 'listingsdbelements
					(listingsdbelements_field_name, listingsdb_id,userdb_id,listingsdbelements_field_value)
					VALUES (' . $sql_field_name . ',' . $listing_id . ',' . $_SESSION['userID'] . ',\'\')';
            $recordSet = $conn->Execute($sql);
            if ($recordSet === false) {
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->add_fields_to_class', 'log_message' => 'DB Error: ' . $sql]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $sql];
            }
        }
        return ['error' => false];
    }
    /**
     * Simple API Command to add an existing field to a new property class.
     *
     @param array $data Expects an array containing the following array keys.
     *      <ul>
     *          <li>$data['class'] - Number - Class ID that we are assigned field to</li>
     *          <li>$data['field_id'] - Number - Field ID to assign</li>
     *      </ul>
     *
     * @return multitype:string |multitype:string
     */
    public function assign_class($data)
    {
        global $conn, $lang, $config, $lapi;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->verify_priv('Agent');
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure'];
        }
        extract($data, EXTR_SKIP || EXTR_REFS, '');
        if (!isset($class) || !is_numeric($class)) {
            return ['error' => true, 'error_msg' => 'class: correct_parameter_not_passed'];
        }
        if (!isset($field_id) || !is_numeric($field_id)) {
            return ['error' => true, 'error_msg' => 'field_id: correct_parameter_not_passed'];
        }
        //Make sure this is a valid field
        $sql = 'SELECT listingsformelements_id,listingsformelements_field_name FROM ' . $config['table_prefix'] . 'listingsformelements WHERE listingsformelements_id = ' . $field_id;
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->create', 'log_message' => 'DB Error: ' . $sql]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $sql];
        }
        if ($recordSet->RecordCount() == 0) {
            return ['error' => true, 'error_msg' => 'Invalid Field'];
        }
        $field_name = $recordSet->fields('listingsformelements_field_name');
        //Make sure this is a valid class
        $sql = 'SELECT class_id FROM ' . $config['table_prefix'] . 'class WHERE class_id = ' . $class;
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->create', 'log_message' => 'DB Error: ' . $sql]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $sql];
        }
        if ($recordSet->RecordCount() == 0) {
            return ['error' => true, 'error_msg' => 'Invalid Class'];
        }

        //Make sure Field is not in property class
        $sql = 'SELECT class_id FROM ' . $config['table_prefix_no_lang'] . 'classformelements WHERE class_id = ' . $class . ' AND listingsformelements_id =' . $field_id;
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->create', 'log_message' => 'DB Error: ' . $sql]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $sql];
        }
        if ($recordSet->RecordCount() == 0) {
            $sql = 'INSERT INTO ' . $config['table_prefix_no_lang'] . 'classformelements
								(class_id,listingsformelements_id)
								VALUES (' . $class . ',' . $field_id . ')';
            $recordSet = $conn->Execute($sql);
            if ($recordSet === false) {
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->fields->create', 'log_message' => 'DB Error: ' . $sql]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $sql];
            }
            //Get List of
            $addfield_status = $this->add_fields_to_class($class, $field_name);
            if ($addfield_status['error'] == false) {
                return ['error' => false, 'field_id' => $field_id, 'class_id' => $class];
            } else {
                return ['error' => true, 'error_msg' => $addfield_status['error_msg']];
            }
        } else {
            //Make sure field exists on all listings in class.
            $addfield_status = $this->add_fields_to_class($class, $field_name);
            if ($addfield_status['error'] == false) {
                return ['error' => true, 'error_msg' => 'Field is already in this class'];
            } else {
                return ['error' => true, 'error_msg' => $addfield_status['error_msg']];
            }
        }
    }
}
