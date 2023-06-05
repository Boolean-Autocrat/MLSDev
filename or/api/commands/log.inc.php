<?php
/**
 * This File Contains the Log API Commands
 * @package Open-Realty
 * @subpackage API
 * @author Ryan C. Bonham
 * @copyright 2010

 * @link http://www.open-realty.com Open-Realty
 */

/**
 * This is the log API, it contains all api calls for creating and retrieving activity log data.
 *
 * @package Open-Realty
 * @subpackage API
 **/
class log_api
{
    /**
     * This API Command creates an entry in the activity log.
     * @param array $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['log_api_command'] - API Command you are running when the error occured</li>
     *      <li>$data['log_message'] - Log Message to insert</li>
     *  </ul>
     */
    public function log_create_entry($data)
    {
        global $conn,$config, $misc;
        extract($data, EXTR_SKIP || EXTR_REFS, '');

        if (!isset($log_api_command)) {
            return ['error' => true, 'error_msg' => 'correct_parameter_not_passed'];
        }
        if (!isset($log_message)) {
            return ['error' => true, 'error_msg' => 'correct_parameter_not_passed'];
        }

        $sql_log_message = $misc->make_db_safe($log_api_command.' : '.$log_message);

        if (isset($_SESSION['userID'])) {
            $id = intval($_SESSION['userID']);
        } else {
            $id = 0;
        }
        
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $sql_remote_addr = $misc->make_db_safe($_SERVER['REMOTE_ADDR']);
        } else {
            $sql_remote_addr = $misc->make_db_safe('');
        }
        $sql = 'INSERT INTO ' . $config['table_prefix'] . 'activitylog (activitylog_log_date, userdb_id, activitylog_action, activitylog_ip_address) VALUES (' . $conn->DBTimeStamp(time()) . ', ' . $id . ', '.$sql_log_message.', '.$sql_remote_addr.')';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            return ['error' => true, 'error_msg' => $conn->ErrorMsg()];
        }
        return ['error' => false, 'status_msg' => 'Log Generated'];
    }
}
