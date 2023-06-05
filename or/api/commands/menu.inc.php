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
 * This is the menu API, it contains all api calls for creating, modifying, and reading menus.
 *
 * @package Open-Realty
 * @subpackage API
 **/
class menu_api
{
    /**
     * This API Command provides a list of the avaliable menus.
     * @param array $data expects an array containing the following array keys.
     *  <ul>
     *
     *  </ul>
     */
    public function metadata($data)
    {
        global $conn,$config;

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        $sql = 'SELECT * FROM ' . $config['table_prefix'] . 'menu ORDER BY menu_name';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            return ['error' => true, 'error_msg' => $conn->ErrorMsg()];
        }
        $menus = [];
        while (!$recordSet->EOF) {
            $menus[$recordSet->fields('menu_id')] = $recordSet->fields('menu_name');
            $recordSet->MoveNext();
        }
        return ['error' => false, 'menus'=>$menus];
    }
    public function create($data)
    {
        global $conn, $config,$lapi, $misc;

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        if (!isset($menu_name)||!is_string($menu_name) || trim($menu_name) == '') {
            return ['error' => true, 'error_msg' => 'menu_name: correct_parameter_not_passed'];
        }
        $menu_name = trim($menu_name);

        $sql = 'INSERT INTO ' . $config['table_prefix'] . 'menu (menu_name) VALUES ('.$misc->make_db_safe($menu_name).')';
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->menu->create','log_message'=>'DB Error: '.$error]);
        }
        $menu_id = $conn->Insert_ID();
        $lapi->load_local_api('log__log_create_entry', ['log_type'=>'INFO','log_api_command'=>'api->menu->create','log_message'=>'Menu Created: '.$menu_name.'('.$menu_id.')']);
        return ['error' => false,'menu_id'=>$menu_id,'menu_name'=>$menu_name];
    }
    public function delete($data)
    {
        global $conn, $config,$lapi;

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        if (!isset($menu_id)||!is_numeric($menu_id)) {
            return ['error' => true, 'error_msg' => 'menu_id: correct_parameter_not_passed'];
        }
        $sql = 'DELETE FROM ' . $config['table_prefix'] . 'menu_items WHERE menu_id = '.intval($menu_id);
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->menu->delete','log_message'=>'DB Error: '.$error]);
        }
        $sql = 'DELETE FROM ' . $config['table_prefix'] . 'menu WHERE menu_id = '.intval($menu_id);
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->menu->delete','log_message'=>'DB Error: '.$error]);
        }
        $lapi->load_local_api('log__log_create_entry', ['log_type'=>'INFO','log_api_command'=>'api->menu->delete','log_message'=>'Menu Deleted: '.$menu_id]);
        return ['error' => false];
    }
    public function read($data)
    {
        global $conn, $config;

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        if (!isset($menu_id)||!is_numeric($menu_id)) {
            return ['error' => true, 'error_msg' => 'menu_id: correct_parameter_not_passed'];
        }
        $sql = 'SELECT * FROM ' . $config['table_prefix'] . 'menu_items  WHERE menu_id = '.intval($menu_id).' ORDER BY parent_id, item_order';
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->menu->read','log_message'=>'DB Error: '.$error]);
        }
        $menu=[];
        $x = 1;
        while (!$recordSet->EOF) {
            $menu[$recordSet->fields('parent_id')][$x]= [
                    'item_id'=>$recordSet->fields('item_id'),
                    'item_name'=>$recordSet->fields('item_name'),
                    'item_type'=>$recordSet->fields('item_type'),
                    'item_value'=>$recordSet->fields('item_value'),
                    'item_target'=>$recordSet->fields('item_target'),
                    'item_class'=>$recordSet->fields('item_class'),
                    'visible_guest'=>$recordSet->fields('visible_guest'),
                    'visible_member'=>$recordSet->fields('visible_member'),
                    'visible_agent'=>$recordSet->fields('visible_agent'),
                    'visible_admin'=>$recordSet->fields('visible_admin'),
            ];
            $x++;
            $recordSet->MoveNext();
        }
        return ['error' => false, 'menu'=>$menu];
    }
    public function item_details($data)
    {
        global $conn, $config;

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        if (!isset($item_id)||!is_numeric($item_id)) {
            return ['error' => true, 'error_msg' => 'item_id: correct_parameter_not_passed'];
        }
        $sql = 'SELECT * FROM ' . $config['table_prefix'] . 'menu_items WHERE item_id = '.intval($item_id);
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->menu->read','log_message'=>'DB Error: '.$error]);
        }
        $item=[];
        while (!$recordSet->EOF) {
            $item['item_id']= $recordSet->fields('item_id');
            $item['parent_id']= $recordSet->fields('parent_id');
            $item['menu_id']= $recordSet->fields('menu_id');
            $item['item_name']= $recordSet->fields('item_name');
            $item['item_order']= $recordSet->fields('item_order');
            $item['item_type']= $recordSet->fields('item_type');
            $item['item_value']= $recordSet->fields('item_value');
            $item['item_target']= $recordSet->fields('item_target');
            $item['item_class']= $recordSet->fields('item_class');
            $item['visible_guest']= $recordSet->fields('visible_guest');
            $item['visible_member']= $recordSet->fields('visible_member');
            $item['visible_agent']= $recordSet->fields('visible_agent');
            $item['visible_admin']= $recordSet->fields('visible_admin');
            $recordSet->MoveNext();
        }
        return ['error' => false, 'menu_item'=>$item];
    }
    public function set_menu_order($data)
    {
        global $conn, $config;

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        if (!isset($menu_id)||!is_numeric($menu_id)) {
            return ['error' => true, 'error_msg' => 'item_id: correct_parameter_not_passed'];
        }
        if (!isset($menu_items)||!is_array($menu_items)) {
            return ['error' => true, 'error_msg' => 'menu_items: correct_parameter_not_passed'];
        }
        //Make Sure this is a valid menu
        $sql = 'SELECT * FROM ' . $config['table_prefix'] . 'menu WHERE menu_id = '.intval($menu_id);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            return ['error' => true, 'error_msg' => $conn->ErrorMsg()];
        }
        if ($recordSet->RecordCount()==0) {
            return ['error' => true, 'error_msg' => 'Invalid Menu ID'];
        }
        foreach ($menu_items as $item_id => $item_array) {
            $order_id = $item_array['order'];
            $parent_id = $item_array['parent'];
            $sql = 'UPDATE ' . $config['table_prefix'] . 'menu_items SET item_order = '.intval($order_id).', parent_id = '.intval($parent_id).' WHERE menu_id = '.intval($menu_id).' AND item_id = '.intval($item_id);
            //echo $sql;
            $recordSet = $conn->Execute($sql);
            if ($recordSet === false) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->menu->set_menu_order','log_message'=>'DB Error: '.$error]);
            }
        }
        return ['error' => false];
    }
    public function delete_menu_item($data)
    {
        global $conn, $config,$lapi;

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        if (!isset($item_id)||!is_numeric($item_id)) {
            return ['error' => true, 'error_msg' => 'item_name: correct_parameter_not_passed'];
        }
        if ($item_id == 0) {
            return ['error' => true, 'error_msg' => 'item_id can not be zero.'];
        }
        $sql = 'DELETE FROM ' . $config['table_prefix'] . 'menu_items WHERE item_id = '.intval($item_id).' OR parent_id = '.intval($item_id);
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->menu->add_menu_item','log_message'=>'DB Error: '.$error]);
        }
        $lapi->load_local_api('log__log_create_entry', ['log_type'=>'INFO','log_api_command'=>'api->menu->delete_menu_item','log_message'=>'Menu Item Deleted: '.$item_id]);
        return ['error' => false];
    }
    public function add_menu_item($data)
    {
        global $conn, $config,$lapi, $misc;
        extract($data, EXTR_SKIP || EXTR_REFS, '');

        if (!isset($item_name)||!is_string($item_name)||empty($item_name)) {
            return ['error' => true, 'error_msg' => 'item_name: correct_parameter_not_passed'];
        }
        if (!isset($menu_id)||!is_numeric($menu_id)) {
            return ['error' => true, 'error_msg' => 'menu_id: correct_parameter_not_passed'];
        }
        if (!isset($parent_id)||!is_numeric($parent_id)) {
            return ['error' => true, 'error_msg' => 'parent_id: correct_parameter_not_passed'];
        }
        //Get Highest Item Order
        $sql = 'SELECT max(item_order) as max_order FROM ' . $config['table_prefix'] . 'menu_items WHERE menu_id = '.intval($menu_id).' AND parent_id = '.intval($parent_id);
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->menu->add_menu_item','log_message'=>'DB Error: '.$error]);
        }
        $max_order = $recordSet->fields('max_order');
        $max_order++;
        $sql = 'INSERT INTO ' . $config['table_prefix'] . 'menu_items (menu_id,item_name,parent_id,item_order) VALUES
			('.intval($menu_id).','.$misc->make_db_safe($item_name).','.intval($parent_id).','.intval($max_order).')';
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->menu->add_menu_item','log_message'=>'DB Error: '.$error]);
        }
        $item_id = $conn->Insert_ID();
        return ['error' => false,'item_id'=>$item_id,'parent_id'=>intval($parent_id),'item_name'=>"$item_name"];
    }
    public function save_menu_item($data)
    {
        global $conn, $config,$lapi, $misc;

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        if (!isset($item_id)||!is_numeric($item_id)) {
            return ['error' => true, 'error_msg' => 'item_id: correct_parameter_not_passed'];
        }
        if (!isset($item_name)||!is_string($item_name)) {
            return ['error' => true, 'error_msg' => 'item_name: correct_parameter_not_passed'];
        }
        if (!isset($item_type)||!is_numeric($item_type)) {
            return ['error' => true, 'error_msg' => 'item_type: correct_parameter_not_passed'];
        }
        if (!isset($item_value)) {
            $item_value='';
        }
        if (!isset($item_target)) {
            $item_target='_self';
        } elseif (!in_array($item_target, ['_self','_blank','_parent','_top'])) {
            return ['error' => true, 'error_msg' => 'item_target: correct_parameter_not_passed'];
        }
        if (!isset($item_class)||!is_string($item_class)) {
            return ['error' => true, 'error_msg' => 'item_class: correct_parameter_not_passed'];
        }
        if (!isset($visible_guest)||!is_numeric($visible_guest)) {
            return ['error' => true, 'error_msg' => 'visible_guest: correct_parameter_not_passed'];
        }
        if (!isset($visible_member)||!is_numeric($visible_member)) {
            return ['error' => true, 'error_msg' => 'visible_member: correct_parameter_not_passed'];
        }
        if (!isset($visible_agent)||!is_numeric($visible_agent)) {
            return ['error' => true, 'error_msg' => 'visible_agent: correct_parameter_not_passed'];
        }
        if (!isset($visible_admin)||!is_numeric($visible_admin)) {
            return ['error' => true, 'error_msg' => 'visible_admin: correct_parameter_not_passed'];
        }
        $sql = 'UPDATE ' . $config['table_prefix'] . 'menu_items
			SET item_type = '.intval($item_type).',
			visible_guest = '.intval($visible_guest).',
			visible_member = '.intval($visible_member).',
			visible_agent = '.intval($visible_agent).',
			visible_admin = '.intval($visible_admin).',
			item_name = '.$misc->make_db_safe($item_name).',
			item_value = '.$misc->make_db_safe($item_value).',
			item_target = '.$misc->make_db_safe($item_target).',
			item_class = '.$misc->make_db_safe($item_class).'
			WHERE item_id = '.intval($item_id);
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->menu->set_menu_order','log_message'=>'DB Error: '.$error]);
        }
        return ['error' => false];
    }
}
