<?php
/**
 * This File Contains the Property Class API Commands
 * @package Open-Realty
 * @subpackage API
 * @author Ryan C. Bonham
 * @copyright 2010

 * @link http://www.open-realty.com Open-Realty
 */

/**
 * This is the pclass API, it contains all api calls for setting and retrieving property class data.
 *
 * @package Open-Realty
 * @subpackage API
 **/
class pclass_api
{
    /**
     * This function create a new property class.
     *
     * @param array $data Data array should contain the following elements.
     *  <ul>
     *      <li>$data['class_system_name'] - The Name of the Property Class</li>
     *      <li>$data['class_rank'] - Rank that Class should be displayed in</li>
     *      <li>$data['field_id'] - Optional Array or Field IDs that should be assigned to this property class on creation. If used this MUST be an array.</li>
     *  </ul>
     *
     * @return array
     */
    public function create($data)
    {
        global $conn,$config,$lapi,$misc;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->verify_priv('Agent');
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure'];
        }
        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed
        if (!isset($class_system_name) || !is_string($class_system_name)) {
            return ['error' => true, 'error_msg' => 'class_system_name: correct_parameter_not_passed'];
        }
        if (!isset($class_rank) || !is_numeric($class_rank)) {
            return ['error' => true, 'error_msg' => 'class_rank: correct_parameter_not_passed'];
        }
        if (isset($field_id) && !is_array($field_id)) {
            return ['error' => true, 'error_msg' => 'field_id: correct_parameter_not_passed'];
        }

        $security = $login->verify_priv('edit_property_classes');
        $display = '';
        if ($security !== true) {
            return ['error' => true, 'error_msg' => 'permission_denied'];
        }

        $class_system_name = $misc->make_db_safe($class_system_name);
        //Check for duplicate class names
        $sql = 'SELECT class_name FROM '.$config['table_prefix'].'class WHERE class_name = '.$class_system_name.'';
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->fields->create','log_message'=>'DB Error: '.$sql]);
            return ['error' => true,'error_msg'=>'DB Error: '.$sql];
        }
        if ($recordSet->RecordCount() >0) {
            return ['error' => true,'error_msg'=>'Class Already Exists: '.$class_system_name];
        }

        $sql = 'INSERT INTO ' . $config['table_prefix'] . 'class (class_name,class_rank) VALUES (' . $class_system_name . ',' . $class_rank . ')';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->pclass->create','log_message'=>'DB Error: '.$sql]);
            return ['error' => true,'error_msg'=>'DB Error: '.$sql];
        }
        $new_class_id = $conn->Insert_ID();

        if (isset($field_id)) {
            foreach ($field_id as $fid) {
                $fid = intval($fid);
                $sql = 'INSERT INTO ' . $config['table_prefix_no_lang'] . 'classformelements (class_id,listingsformelements_id) VALUES ('.$new_class_id.','.$fid.')';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->pclass->create','log_message'=>'DB Error: '.$sql]);
                    return ['error' => true,'error_msg'=>'DB Error: '.$sql];
                }
            }
        }
        return ['error' => false,'class_id' => $new_class_id];
    }
    /**
     * This function reads information on single property class..
     *
     * @param array $data Data array should contain the following elements.
     *  <ul>
     *      <li>$data['class_id'] - The ID of the property class to return infrormation on.</li>
     *  </ul>
     *
     * @return array array('error' => FALSE,'class_id' => $class_id,'class_name' => $class_name, 'class_rank'=>$class_rank)
     */
    public function read($data)
    {
        global $conn,$config,$lapi;
        include_once $config['basepath'] . '/include/login.inc.php';
        extract($data, EXTR_SKIP || EXTR_REFS, '');

        //Check that required settings were passed
        if (!isset($class_id) || !is_numeric($class_id)) {
            return ['error' => true, 'error_msg' => 'class_id: correct_parameter_not_passed'];
        }

        $sql = 'SELECT class_name, class_rank
				FROM ' . $config['table_prefix'] . 'class
				WHERE class_id = '.$class_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->pclass->read','log_message'=>'DB Error: '.$sql]);
            return ['error' => true,'error_msg'=>'DB Error: '.$sql];
        }
        $class_name = $recordSet->fields('class_name');
        $class_rank = $recordSet->fields('class_rank');
        return ['error' => false,'class_id' => $class_id,'class_name' => $class_name, 'class_rank'=>$class_rank];
    }
    /**
     * This function reads information on all property class..
     *
     * @param array $data Data array should be empty.
     *
     * @return array The metadata array return will bet setup as a multi demension array where they key is the property class ID.
     */
    public function metadata($data)
    {
        global $conn,$config,$lapi;
        include_once $config['basepath'] . '/include/login.inc.php';
        extract($data, EXTR_SKIP || EXTR_REFS, '');

        //Check that required settings were passed
        if (!isset($class_id) || !is_numeric($class_id)) {
            //return array('error' => TRUE, 'error_msg' => 'class_id: correct_parameter_not_passed');
        }

        $sql = 'SELECT class_id, class_name, class_rank
				FROM ' . $config['table_prefix'] . 'class ORDER BY class_rank';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->pclass->read','log_message'=>'DB Error: '.$sql]);
            return ['error' => true,'error_msg'=>'DB Error: '.$sql];
        }
        $class_metadata=[];
        while (!$recordSet->EOF) {
            $class_metadata[$recordSet->fields('class_id')]['name'] = $recordSet->fields('class_name');
            $class_metadata[$recordSet->fields('class_id')]['rank'] = $recordSet->fields('class_rank');
            $recordSet->MoveNext();
        }
        return ['error' => false,'metadata' => $class_metadata];
    }
}
