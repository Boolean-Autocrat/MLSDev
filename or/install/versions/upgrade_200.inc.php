<?php

// just so we know it is broken
class version extends installer
{
    public function password_gen($len = 15)
    {
        $chars = '0123456789aeiouyAEIOUYbdghjmnpqrstvzBDGHJLMNPQRSTVWXZ!@#$%^&*()_+`-_[]{}\<>,./?';

        $password = '';
        $alt = time() % 2;
        for ($i = 0; $i < $len; $i++) {
            $password .= $chars[(rand() % strlen($chars))];
        }
        return $password;
    }
    public function load_prev_settings($return = false)
    {
        // phpcs:ignore
        $old_file = file_get_contents(dirname(__FILE__) . '/../../include/common.php');
        // Get values
        preg_match('/\$db_type = "(.*?)"/', $old_file, $old_db_type);
        preg_match('/\$db_server = "(.*?)"/', $old_file, $old_db_server);
        preg_match('/\$db_database = "(.*?)"/', $old_file, $old_db_name);
        preg_match('/\$db_user = "(.*?)"/', $old_file, $old_db_user);
        preg_match('/\$db_password = "(.*?)"/', $old_file, $old_db_password);
        preg_match('/\$config\["table_prefix_no_lang"\] = "(.*?)"/', $old_file, $old_table_prefix);
        if ($return == false) {
            $this->get_new_settings($old_db_type[1], $old_db_server[1], $old_db_name[1], $old_db_user[1], $old_db_password[1], $old_table_prefix[1]);
        } else {
            return ['db_type' => $old_db_type[1], 'db_server' => $old_db_server[1], 'db_database' => $old_db_name[1], 'db_user' => $old_db_user[1], 'db_password' => $old_db_password[1], 'table_prefix' => $old_table_prefix[1]];
        }
    }
    public function create_tables($old_version)
    {
        global $conn;
        $sql_insert = [];
        switch ($old_version) {
            case '2.0 Beta 1':
            case '2.0 Beta 2':
            case '2.0.0':
            case '2.0.1':
            case '2.0.2':
            case '2.0.3':
            case '2.0.4':
            case '2.0.5':
            case '2.0.6':
                break;
        }
        foreach ($sql_insert as $elementContents) {
            $recordSet = $conn->Execute($elementContents);
            if ($recordSet === false) {
                if ($_SESSION['devel_mode'] == 'no') {
                    die("<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />");
                } else {
                    echo "<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />";
                }
            }
        }
    }
    public function update_tables($old_version)
    {
        global $config, $lang;
        $sql_insert = [];
        $this->set_version();
        // this is the setup for the ADODB library
        // phpcs:ignore
        include_once dirname(__FILE__) . '/../../vendor/adodb/adodb-php/adodb.inc.php';
        $conn = ADONewConnection($_SESSION['db_type']);
        $conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, 'utf8');

        $conn->connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']);

        $config['table_prefix'] = $_SESSION['table_prefix'] . $_SESSION['or_install_lang'] . '_';
        $config['table_prefix_no_lang'] = $_SESSION['table_prefix'];
        switch ($old_version) {
            case '2.0 Beta 1':
            case '2.0 Beta 2':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD controlpanel_wysiwyg_execute_php INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD controlpanel_agent_default_num_listings INT4 NOT NULL';
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_agent_default_num_listings = -1';
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix'] . "userdb SET  userdb_is_agent = 'no'";
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.0.0':
            case '2.0.1':
            case '2.0.2':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_number_decimals_number_fields INT2 NOT NULL DEFAULT 0 ';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_number_decimals_price_fields INT2 NOT NULL DEFAULT 0 ';
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_agent_default_num_listings = 0';
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_number_decimals_price_fields = 0';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.0.3':
            case '2.0.4':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'memberformelements ADD COLUMN memberformelements_display_priv INT4 NOT NULL DEFAULT 0';
                // Remove bad fields that might were incorrectly saved.
                $bad_value[] = 'user_user_name';
                $bad_value[] = 'edit_user_pass';
                $bad_value[] = 'edit_user_pass2';
                $bad_value[] = 'user_email';
                $bad_value[] = 'PHPSESSID';
                $bad_value[] = 'edit';
                $bad_value[] = 'edit_isAdmin';
                $bad_value[] = 'edit_active';
                $bad_value[] = 'edit_isAgent';
                $bad_value[] = 'edit_limitListings';
                $bad_value[] = 'edit_canEditForms';
                $bad_value[] = 'edit_canViewLogs';
                $bad_value[] = 'edit_canModerate';
                $bad_value[] = 'edit_canFeatureListings';
                $bad_value[] = 'edit_canPages';
                $bad_value[] = 'edit_canVtour';
                $sql_bad_values = '';
                foreach ($bad_value as $value) {
                    if ($sql_bad_values != '') {
                        $sql_bad_values .= ' OR ';
                    }
                    $sql_bad_values .= "userdbelements_field_name = '$value'";
                }
                $sql_insert[] = 'DELETE FROM ' . $config['table_prefix'] . "userdbelements WHERE $sql_bad_values";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_automatic_update_check INT2 NOT NULL DEFAULT 0 ';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.0.5':
                if ($_SESSION['db_type'] == 'mysqli') {
                    $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'pagesmain MODIFY COLUMN pagesmain_full LONGTEXT';
                }
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_search_sortby varchar(45) NOT NULL DEFAULT 0 ';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_search_sorttype varchar(45) NOT NULL DEFAULT 0 ';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.0.6':
                // Putt settings back to correct values after messed up 2.0.5 upgrade
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_agent_default_num_listings = 0';
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_number_decimals_price_fields = 0';
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_search_sortby = 'listingsdb_id'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_search_sorttype = 'ASC'";
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.0.7':
            case '2.0.8':
                echo 'Creating New User Permissions<br />';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_agent_default_edit_site_config INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_agent_default_edit_member_template  INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_agent_default_edit_agent_template INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_agent_default_edit_listing_template  INT2 NOT NULL DEFAULT 0';
                // Remove old permission default
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_agent_default_editforms';
                // Add new user fields.
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "userdb ADD COLUMN userdb_can_edit_site_config CHAR(3) NOT NULL DEFAULT 'no'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "userdb ADD COLUMN userdb_can_edit_member_template CHAR(3) NOT NULL DEFAULT 'no'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "userdb ADD COLUMN userdb_can_edit_agent_template CHAR(3) NOT NULL DEFAULT 'no'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "userdb ADD COLUMN userdb_can_edit_listing_template CHAR(3) NOT NULL DEFAULT 'no'";
                // Populate new fields
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix'] . "userdb SET  userdb_can_edit_member_template = 'yes',userdb_can_edit_agent_template = 'yes',userdb_can_edit_listing_template = 'yes' WHERE userdb_can_edit_forms = 'yes'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb DROP COLUMN userdb_can_edit_forms';
                // Add new user fields
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix'] . "userdb ADD COLUMN userdb_user_first_name CHAR VARYING(100) NOT NULL default ''";
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix'] . "userdb ADD COLUMN userdb_user_last_name CHAR VARYING(100) NOT NULL default ''";
                // Update Controlpanel with new export option
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_export_listings INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix'] . "userdb ADD COLUMN userdb_can_export_listings CHAR VARYING(100) NOT NULL default ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_agent_default_can_export_listings  INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix'] . "userdb ADD COLUMN userdb_can_edit_expiration CHAR VARYING(100) NOT NULL default ''";
                // Add New permissions for edit_all_listings and edit_all_users.
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_agent_default_edit_all_users INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_agent_default_edit_all_listings  INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "userdb ADD COLUMN userdb_can_edit_all_users CHAR(3) NOT NULL DEFAULT 'no'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "userdb ADD COLUMN userdb_can_edit_all_listings CHAR(3) NOT NULL DEFAULT 'no'";

                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'agentformelements MODIFY COLUMN agentformelements_field_name CHAR VARYING(80) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsdbelements MODIFY COLUMN listingsdbelements_field_name CHAR VARYING(80) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsformelements MODIFY COLUMN listingsformelements_field_name CHAR VARYING(80) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'memberformelements MODIFY COLUMN memberformelements_field_name CHAR VARYING(80) NOT NULL';
                // Create Tables for property classing
                foreach ($sql_insert as $elementContents) {
                    $recordSet = $conn->Execute($elementContents);
                    if ($recordSet === false) {
                        if ($_SESSION['devel_mode'] == 'no') {
                            die("<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />");
                        } else {
                            echo "<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />";
                        }
                    }
                }
                $sql_insert = [];
                echo 'Creating Property Class Tables<br />';
                if (strpos($_SESSION['db_type'], 'postgres') !== false) {
                    $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'classformelements (
                    classformelements_id SERIAL NOT NULL,
                    class_id INT4 NOT NULL,
                    listingsformelements_id INT4 NOT NULL,
                    PRIMARY KEY(classformelements_id)
                  );';
                    $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'classlistingsdb (
                      classlistingsdb_id SERIAL NOT NULL,
                      class_id INT4 NOT NULL,
                      listingsdb_id INT4 NOT NULL,
                      PRIMARY KEY(classlistingsdb_id)
                    );';
                    $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'class (
                      class_id SERIAL NOT NULL,
                      class_name CHAR VARYING(80) NOT NULL,
                      class_rank INT2 NOT NULL,
                      PRIMARY KEY(class_id)
                    );';
                } else {
                    $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'classformelements (
                    classformelements_id INT4 NOT NULL AUTO_INCREMENT,
                    class_id INT4 NOT NULL,
                    listingsformelements_id INT4 NOT NULL,
                    PRIMARY KEY(classformelements_id)
                  );';
                    $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'classlistingsdb (
                      classlistingsdb_id INT4 NOT NULL AUTO_INCREMENT,
                      class_id INT4 NOT NULL,
                      listingsdb_id INT4 NOT NULL,
                      PRIMARY KEY(classlistingsdb_id)
                    );';
                    $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'class (
                      class_id INT4 NOT NULL AUTO_INCREMENT,
                      class_name CHAR VARYING(80) NOT NULL,
                      class_rank INT2 NOT NULL,
                      PRIMARY KEY(class_id)
                    );';
                }
                foreach ($sql_insert as $elementContents) {
                    $recordSet = $conn->Execute($elementContents);
                    if ($recordSet === false) {
                        if ($_SESSION['devel_mode'] == 'no') {
                            die("<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />");
                        } else {
                            echo "<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />";
                        }
                    }
                }
                $sql_insert = [];
                echo 'Starting Property Class Conversion.<br />';
                // Get current Listing Types
                $sql = 'SELECT listingsformelements_field_elements FROM ' . $config['table_prefix'] . "listingsformelements WHERE listingsformelements_field_name = 'type'";
                $recordSet = $conn->execute($sql);
                if ($recordSet === false) {
                    if ($_SESSION['devel_mode'] == 'no') {
                        die('FATAL ERROR IN CLASS CONVERSION');
                    } else {
                        echo 'FATAL ERROR IN CLASS CONVERSION';
                    }
                }
                $i = 1;
                $class_names = explode('||', $recordSet->fields('listingsformelements_field_elements'));
                foreach ($class_names as $class_name) {
                    $classes[$i] = $class_name;
                    $sql_insert[] = 'INSERT INTO ' . $config['table_prefix'] . "class VALUES ($i,'$class_name',$i)";
                    $i++;
                    $recordSet->MoveNext();
                }
                // Get a list of all the type fields with field vlaue and listingid
                $sql = 'SELECT listingsdbelements_field_value, listingsdb_id FROM ' . $config['table_prefix'] . "listingsdbelements WHERE listingsdbelements_field_name = 'type'";
                $recordSet = $conn->execute($sql);
                if ($recordSet === false) {
                    if ($_SESSION['devel_mode'] == 'no') {
                        die('FATAL ERROR IN CLASS CONVERSION');
                    } else {
                        echo 'FATAL ERROR IN CLASS CONVERSION';
                    }
                }
                $classes = array_flip($classes);
                while (!$recordSet->EOF) {
                    $listing_id = $recordSet->fields('listingsdb_id');
                    $class_id = $classes[$recordSet->fields('listingsdbelements_field_value')];
                    $sql_insert[] = 'INSERT INTO ' . $config['table_prefix_no_lang'] . "classlistingsdb (class_id,listingsdb_id)VALUES ($class_id,$listing_id)";
                    $recordSet->MoveNext();
                }
                // Add each formelement to each property class.
                // Get a list of all the type fields with field vlaue and listingid
                $sql = 'SELECT listingsformelements_id FROM ' . $config['table_prefix'] . 'listingsformelements';
                $recordSet = $conn->execute($sql);
                if ($recordSet === false) {
                    if ($_SESSION['devel_mode'] == 'no') {
                        die('FATAL ERROR IN CLASS CONVERSION');
                    } else {
                        echo 'FATAL ERROR IN CLASS CONVERSION';
                    }
                }
                while (!$recordSet->EOF) {
                    $listingsformelements_id = $recordSet->fields('listingsformelements_id');
                    foreach ($classes as $class_id) {
                        $sql_insert[] = 'INSERT INTO ' . $config['table_prefix_no_lang'] . "classformelements (class_id,listingsformelements_id)VALUES ($class_id,$listingsformelements_id)";
                    }
                    $recordSet->MoveNext();
                }
                // Delete Old tpe field and all type field elements
                $sql_insert[] = 'DELETE FROM ' . $config['table_prefix'] . "listingsformelements WHERE listingsformelements_field_name = 'type'";
                $sql_insert[] = 'DELETE FROM ' . $config['table_prefix'] . "listingsdbelements WHERE listingsdbelements_field_name = 'type'";
                // Remove fields from control panel that are no longer used.
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_rental_step';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_max_rental_price';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_min_rental_price';
                foreach ($sql_insert as $elementContents) {
                    $recordSet = $conn->Execute($elementContents);
                    if ($recordSet === false) {
                        if ($_SESSION['devel_mode'] == 'no') {
                            die("<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />");
                        } else {
                            echo "<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />";
                        }
                    }
                }
                $sql_insert = [];
                echo 'Adding new user property class permissions and member notify options.<br />';
                // Add User Permissions for Property Class
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_agent_default_edit_property_classes  INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "userdb ADD COLUMN userdb_can_edit_property_classes CHAR(3) NOT NULL DEFAULT 'no'";
                // Add new field for notify member option
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "usersavedsearches ADD COLUMN usersavedsearches_notify CHAR VARYING(3) NOT NULL DEFAULT 'no'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_map_country CHAR VARYING(45) NOT NULL DEFAULT ''";
                // Remove fields no longer needed.
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_phpmyadmin';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_manage_index_permissions';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_show_listedby_admin INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_show_next_prev_listing_page INT2 NOT NULL DEFAULT 0';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.1.0':
            case '2.1.1':
            case '2.1.2':
            case '2.1.3':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_multiple_pclass_selection INT2 NOT NULL DEFAULT 0';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.1.5':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_map_address2 CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_map_address3 CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_seo_default_keywords TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_seo_default_description TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel CHANGE COLUMN controlpanel_site_title controlpanel_seo_default_title TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_seo_listing_title TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_template_listing_sections TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_seo_listing_keywords TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_seo_listing_description TEXT NOT NULL';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.1.6dev':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_agent_template CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'CREATE TABLE  ' . $config['table_prefix_no_lang'] . 'zipdist (
        zipdist_zipcode char(5) NULL,
        zipdist_ziptype char(1) NULL,
        zipdist_cityname CHAR VARYING(64) NULL,
        zipdist_citytype char(1) NULL,
        zipdist_statename CHAR VARYING(64) NULL,
        zipdist_stateabbr char(2) NULL,
        zipdist_areacode char(3) NULL,
        zipdist_latitude decimal(9,6) NULL,
        zipdist_longitude  decimal(9,6) NULL,
        zipdist_id INT4 NOT NULL AUTO_INCREMENT,
        PRIMARY KEY  (zipdist_id)
      );';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsformelements MODIFY COLUMN listingsformelements_search_type CHAR VARYING(50) NOT NULL';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.2.0':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vtour_template CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vtour_width CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vtour_height CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vt_popup_width CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vt_popup_height CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vtour_fov CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_map_address4 CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_zero_price INT2 NOT NULL DEFAULT 0';

                $sql = 'SELECT controlpanel_date_format FROM ' . $config['table_prefix_no_lang'] . 'controlpanel';
                $recordSet = $conn->Execute($sql);
                $date_format = $recordSet->fields('controlpanel_date_format');
                $sql = 'SELECT listingsformelements_field_name FROM ' . $config['table_prefix'] . 'listingsformelements WHERE listingsformelements_field_type = \'date\'';
                $recordSet = $conn->Execute($sql);
                while (!$recordSet->EOF) {
                    $field_name = $recordSet->fields('listingsformelements_field_name');
                    $sql2 = 'SELECT listingsdbelements_field_value, listingsdbelements_id FROM ' . $config['table_prefix'] . 'listingsdbelements WHERE listingsdbelements_field_name = \'' . $field_name . '\'';
                    $recordSet2 = $conn->Execute($sql2);
                    while (!$recordSet2->EOF) {
                        $field_value = $recordSet2->fields('listingsdbelements_field_value');
                        $id = $recordSet2->fields('listingsdbelements_id');
                        if ($date_format == 1) {
                            $format = '%m/%d/%Y';
                        } elseif ($date_format == 2) {
                            $format = '%Y/%d/%m';
                        } elseif ($date_format == 3) {
                            $format = '%d/%m/%Y';
                        }
                        $returnValue = $this->parseDate($field_value, $format);
                        $sql_insert[] = 'UPDATE ' . $config['table_prefix'] . "listingsdbelements SET listingsdbelements_field_value = '" . $returnValue . "' WHERE listingsdbelements_id = " . $id;
                        $recordSet2->MoveNext();
                    }
                    $recordSet->MoveNext();
                }
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.3.0':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vcard_phone CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vcard_fax CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vcard_mobile CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vcard_address CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vcard_city CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vcard_state CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vcard_zip CHAR VARYING(4) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vcard_country CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vcard_notes CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_vcard_url CHAR VARYING(45) NOT NULL DEFAULT ''";
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.3.1':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'pagesmain ADD COLUMN pagesmain_description TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'pagesmain ADD COLUMN pagesmain_keywords TEXT NOT NULL';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.3.2':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsdb CHANGE COLUMN listingsdb_last_modified listingsdb_last_modified DATETIME NOT NULL';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.3.3':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_email_information_to_new_users INT(2) NOT NULL DEFAULT 0';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.3.4':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_demo_mode INT(2) NOT NULL DEFAULT 0';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.3.5':
            case '2.3.6':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_max_search_results INT4 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_feature_list_separator CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_search_list_separator CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_use_email_image_verification INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_rss_title_featured CHAR VARYING(45) NOT NULL DEFAULT 'Featured Listings Feed'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_rss_desc_featured CHAR VARYING(255) NOT NULL DEFAULT 'RSS feed of our featured listings'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_rss_listingdesc_featured CHAR VARYING(255) NOT NULL DEFAULT '<table><tr><td>{image_thumb_fullurl_1}</td><td>{listing_field_full_desc_rawvalue}</td></tr></table>';";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_rss_title_lastmodified CHAR VARYING(45) NOT NULL DEFAULT 'Last Modified Listings Feed'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_rss_desc_lastmodified CHAR VARYING(255) NOT NULL DEFAULT 'RSS feed of our last modified listings'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_rss_listingdesc_lastmodified CHAR VARYING(255) NOT NULL DEFAULT '<table><tr><td>{image_thumb_fullurl_1}</td><td>{listing_field_full_desc_rawvalue}</td></tr></table>';";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_max_listings_upload_height INT4 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_max_user_upload_height INT4 NOT NULL DEFAULT 0';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.4.0':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel CHANGE COLUMN controlpanel_rss_listingdesc_lastmodified controlpanel_rss_listingdesc_lastmodified TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel CHANGE COLUMN controlpanel_rss_listingdesc_featured controlpanel_rss_listingdesc_featured TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_rss_limit_lastmodified INT4 NOT NULL DEFAULT 50';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.4.1':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_thumbnail_height INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_resize_thumb_by CHAR VARYING(20) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_resize_by CHAR VARYING(20) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_charset CHAR VARYING(15) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_wysiwyg_show_edit INT(2) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_textarea_short_chars INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_main_image_display_by CHAR VARYING(20) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_main_image_width INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_main_image_height INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_number_columns INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_rss_limit_featured INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_force_decimals INT(2) NOT NULL';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.4.2':
            case '2.4.3':
            case '2.4.4':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsformelements ADD COLUMN listingsformelements_field_length INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_icon_image_width INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_icon_image_height INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_max_listings_file_uploads INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_max_listings_file_upload_size INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_max_users_file_uploads INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_max_users_file_upload_size INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_allowed_file_upload_extensions CHAR VARYING(255) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_show_file_icon INT(2) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_show_file_size INT(2) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_file_display_option CHAR VARYING(20) NOT NULL';
                $sql_insert[] = 'CREATE TABLE  ' . $config['table_prefix'] . 'listingsfiles (
          listingsfiles_id INT4 NOT NULL AUTO_INCREMENT,
          userdb_id INT4 NOT NULL,
          listingsfiles_caption CHAR VARYING(255) NOT NULL,
          listingsfiles_file_name CHAR VARYING(255) NOT NULL,
          listingsfiles_description TEXT NOT NULL,
          listingsdb_id INT4 NOT NULL,
          listingsfiles_rank INT4 NOT NULL,
          listingsfiles_active CHAR(3) NOT NULL,
          PRIMARY KEY (listingsfiles_id)
        );';
                $sql_insert[] = 'CREATE TABLE  ' . $config['table_prefix'] . 'usersfiles (
          usersfiles_id INT4 NOT NULL AUTO_INCREMENT,
          userdb_id INT4 NOT NULL,
          usersfiles_caption CHAR VARYING(255) NOT NULL,
          usersfiles_file_name CHAR VARYING(255) NOT NULL,
          usersfiles_description TEXT NOT NULL,
          usersfiles_rank INT4 NOT NULL,
          usersfiles_active CHAR(3) NOT NULL,
          PRIMARY KEY (usersfiles_id)
        );';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_include_senders_ip INT(2) NOT NULL ;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_agent_default_havefiles INT(2) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_agent_default_haveuserfiles INT(2) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb ADD COLUMN userdb_can_have_files CHAR(3) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb ADD COLUMN userdb_can_have_user_files CHAR(3) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsformelements ADD COLUMN listingsformelements_tool_tip TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'agentformelements ADD COLUMN agentformelements_tool_tip TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'memberformelements ADD COLUMN memberformelements_tool_tip TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_show_notes_field INT(2) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_disable_referrer_check INT(2) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_seo_url_seperator CHAR VARYING(20) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_search_step_max INT(4) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_special_search_sortby CHAR VARYING(45) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_special_search_sorttype CHAR VARYING(45) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_price_field CHAR VARYING(45) NOT NULL DEFAULT 'price'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_users_per_page INT4 NOT NULL DEFAULT 5';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.5.0':
            case '2.5.1':
            case '2.5.2':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_use_help_link INT2 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_main_admin_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_add_listing_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_edit_listing_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_edit_user_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_user_manager_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_page_editor_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_modify_listing_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_edit_listing_images_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_edit_vtour_images_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_edit_listing_files_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_edit_agent_template_add_field_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_edit_agent_template_field_order_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_edit_member_template_add_field_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_edit_listing_template_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_edit_listing_template_add_field_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_edit_listings_template_field_order_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_edit_listing_template_search_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_edit_listing_template_search_results_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_show_property_classes_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_configure_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_view_log_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_addon_transparentmaps_admin_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_addon_transparentmaps_geocode_all_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_addon_transparentRETS_config_server_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_addon_transparentRETS_config_imports_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_user_template_member_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_user_template_agent_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_modify_property_class_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_insert_property_class_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_addon_IDXManager_config_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_addon_IDXManager_classmanager_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_addon_csvloader_admin_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_edit_member_template_field_order_help_link TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_show_admin_on_agent_list INT(2) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsformelements CHANGE COLUMN listingsformelements_location listingsformelements_location CHAR VARYING(50) NOT NULL';
                $sql_insert[] = 'CREATE INDEX idx_vtourimages_listingsdb_id ON ' . $config['table_prefix'] . 'vtourimages (listingsdb_id);';
                $sql_insert[] = 'CREATE INDEX idx_vtourimages_vtourimages_rank ON ' . $config['table_prefix'] . 'vtourimages (vtourimages_rank);';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.5.3':
            case '2.5.4':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'pagesmain CHANGE COLUMN pagesmain_title pagesmain_title TEXT NOT NULL';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.5.5':
            case '2.5.6':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsdb CHANGE COLUMN listingsdb_title listingsdb_title TEXT NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsimages CHANGE COLUMN listingsimages_file_name listingsimages_file_name CHAR VARYING(255) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsimages CHANGE COLUMN listingsimages_thumb_file_name listingsimages_thumb_file_name CHAR VARYING(255) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userimages CHANGE COLUMN userimages_file_name userimages_file_name CHAR VARYING(255) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userimages CHANGE COLUMN userimages_thumb_file_name userimages_thumb_file_name CHAR VARYING(255) NOT NULL';
                $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'blogmain (
          blogmain_id INT4 NOT NULL AUTO_INCREMENT,
          userdb_id INT4 NOT NULL,
          blogmain_title TEXT NOT NULL,
          blogmain_date CHAR VARYING(20) NOT NULL,
          blogmain_full LONGTEXT NOT NULL,
          blogmain_description LONGTEXT NOT NULL,
          blogmain_keywords LONGTEXT NOT NULL,
          blogmain_published INT4 NOT NULL,
          PRIMARY KEY(blogmain_id)
        );';
                $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . 'blogcomments (
          blogcomments_id INT4 NOT NULL AUTO_INCREMENT,
          blogmain_id INT4 NOT NULL,
          userdb_id INT4 NULL,
          blogcomments_timestamp INT4 NOT NULL,
          blogcomments_text LONGTEXT NOT NULL,
          blogcomments_moderated BOOLEAN,
          PRIMARY KEY(blogcomments_id)
        );';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb ADD COLUMN userdb_rank INT4';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb ADD COLUMN userdb_featuredlistinglimit INT4';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD controlpanel_agent_default_num_featuredlistings INT4 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb ADD COLUMN userdb_can_edit_blog CHAR(3) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb ADD COLUMN userdb_blog_user_type INT4 NOT NULL DEFAULT 1';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb ADD COLUMN userdb_can_manage_addons INT4 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD controlpanel_use_signup_image_verification INT2 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_site_email CHAR VARYING(45) NOT NULL DEFAULT ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_mbstring_enabled INT(2) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD controlpanel_require_email_verification INT2 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb ADD COLUMN userdb_email_verified CHAR(3) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD controlpanel_blog_requires_moderation INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsformelements MODIFY COLUMN `listingsformelements_search_step` VARCHAR(25) DEFAULT NULL';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.5.7':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_maintenance_mode INT(2) NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_notification_last_timestamp TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_notify_listings_template CHAR VARYING(45) NOT NULL DEFAULT 'notify_listings_default.html'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb DROP COLUMN userdb_can_manage_addons';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb ADD COLUMN userdb_can_manage_addons CHAR(3) NOT NULL';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '2.5.8':
                $sql_insert[] = 'CREATE TABLE IF NOT EXISTS `' . $config['table_prefix_no_lang'] . "open-realty_license` ( `license_key` varchar(155) NOT NULL default '', `hash` BLOB );";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_twitter_user CHAR VARYING(45) NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_twitter_password CHAR VARYING(45) NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_twitter_new_listings INT(1) NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_twitter_update_listings INT(1) NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_twitter_new_blog INT(1) NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_blog_pingback_urls LONGTEXT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_blogs_per_page INT4 NOT NULL DEFAULT 10';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_allow_pingbacks INT(1) NOT NULL DEFAULT 1';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_send_url_pingbacks INT(1) NOT NULL DEFAULT 1';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_send_service_pingbacks INT(1) NOT NULL DEFAULT 1';
                $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'blogpingbacks (
          blogpingback_id INT4 NOT NULL AUTO_INCREMENT,
          blogmain_id INT4 NOT NULL,
          blogpingback_timestamp INT4 NOT NULL,
          blogpingback_source CHAR VARYING(2000) NOT NULL,
          blogpingback_dest CHAR VARYING(2000) NOT NULL,
          blogcomments_moderated BOOLEAN,
          PRIMARY KEY(blogpingback_id)
        );';
                $sql_insert[] =  'CREATE TABLE ' . $config['table_prefix'] . 'blogcategory (
                  `category_id` INT4 NOT NULL AUTO_INCREMENT,
                  `category_name` CHAR VARYING(80) NOT NULL,
                  `category_rank` INT2 NOT NULL,
                  PRIMARY KEY(`category_id`)
                );';
                $sql_insert[] =  'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'blogcategory_relation (
                  relation_id INT4 NOT NULL AUTO_INCREMENT,
                  category_id INT4 NOT NULL,
                  blogmain_id INT4 NOT NULL,
                  PRIMARY KEY(relation_id),
                  INDEX blot_cat_rel (category_id,blogmain_id)
                );';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_timezone CHAR VARYING(45) NOT NULL default 'America/New_York'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "userdb MODIFY COLUMN userdb_can_manage_addons CHAR(3) NOT NULL default 'no'";
                // Check if we have the userdb_can_edit_blog column and remove it if we do have it
                $sql = 'SELECT userdb_can_edit_blog FROM ' . $config['table_prefix'] . 'userdb limit 1';
                $recordSet = $conn->execute($sql);
                if ($recordSet != false) {
                    $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb DROP COLUMN userdb_can_edit_blog';
                }
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_default_page CHAR VARYING(45) NOT NULL default 'wysiwyg_page'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'blogcategory ADD COLUMN parent_id INT4 NULL default 0';
                $sql_insert[] =  'CREATE TABLE ' . $config['table_prefix'] . 'blogtags (
                  tag_id INT4 NOT NULL AUTO_INCREMENT,
                  tag_name CHAR VARYING(80) NOT NULL,
                  tag_seoname CHAR VARYING(80) NOT NULL,
                  PRIMARY KEY(tag_id)
                );';
                $sql_insert[] =  'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'blogtag_relation (
                  relation_id INT4 NOT NULL AUTO_INCREMENT,
                  tag_id INT4 NOT NULL,
                  blogmain_id INT4 NOT NULL,
                  PRIMARY KEY(relation_id),
                  INDEX blot_tag_rel (tag_id,blogmain_id)
                );';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "blogcategory ADD COLUMN category_seoname CHAR VARYING(80) NOT NULL default ''";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'pagesmain DROP COLUMN pagesmain_no_visitors;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'pagesmain CHANGE COLUMN  pagesmain_complete pagesmain_published INT2 NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'blogcategory ADD COLUMN category_description LONGTEXT NULL';
                $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'seouri (
                  `action` varchar(25)  NOT NULL,
                  `slug` varchar(25)  NULL,
                  `uri` varchar(255)  NOT NULL,
                  `seouri_id` INT(4)  NOT NULL AUTO_INCREMENT,
                  PRIMARY KEY (`seouri_id`),
                  INDEX `slug_uidx`(`slug`(5))
                )';
                $sql_insert[] = 'DELETE FROM ' . $config['table_prefix_no_lang'] . 'seouri';
                $sql_insert[] = 'INSERT INTO ' . $config['table_prefix_no_lang'] . "seouri (action,slug,uri) VALUES
        ('listing','listing-','{listing_title}-{listing_id}.html'),
        ('page','page-','{page_title}-{page_id}.html'),
        ('agent','agent-','{agent_fname}-{agent_lname}-{agent_id}.html'),
        ('contactagent','contact-agent-','{agent_name}-{agent_id}.html'),
        ('blog','article-','{blog_title}-{blog_id}.html'),
        ('css','css_','{css_name}.css'),
        ('searchpage','','search.html'),
        ('searchresults','','searchresults.html'),
        ('blogindex','','blog.html'),
        ('view_agents','','agents.html'),
        ('index','','index.html'),
        ('member_signup','','member_signup.html'),
        ('agent_signup','','agent_signup.html'),
        ('member_login','','member_login.html'),
        ('view_favorites','','view_favorites.html'),
        ('calculator','','calculator.html'),
        ('saved_searches','','saved_searches.html'),
        ('logout','','logout.html'),
        ('edit_profile','','edit_profile.html'),
        ('listing_image','listing_image_','{image_id}.html'),
        ('rss','rss_','{rss_feed}'),
        ('blog_tag','blog_tag_','{tag_seoname}'),
        ('blog_cat','blog_category_','{cat_seoname}'),
        ('blog_archive','blog/archive/','{archive_date}')";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'pagesmain ADD COLUMN page_seotitle TEXT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'blogmain ADD COLUMN blog_seotitle TEXT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsdb ADD COLUMN listing_seotitle TEXT NULL';
                //Get URL Seperator
                $sql = 'SELECT controlpanel_charset, controlpanel_mbstring_enabled,controlpanel_seo_url_seperator FROM ' . $config['table_prefix_no_lang'] . 'controlpanel';
                $recordSet = $conn->Execute($sql);
                $seo_url_seperator = $recordSet->fields('controlpanel_seo_url_seperator');
                $controlpanel_mbstring_enabled = $recordSet->fields('controlpanel_mbstring_enabled');
                $charset = $recordSet->fields('controlpanel_charset');
                $config['seo_url_seperator'] = $seo_url_seperator;
                $config['charset'] = $charset;
                $config['controlpanel_mbstring_enabled'] = $controlpanel_mbstring_enabled;
                //Make SEO Title for Blogs
                $sql = 'SELECT blogmain_id,blogmain_title FROM ' . $config['table_prefix'] . 'blogmain';
                $recordSet = $conn->Execute($sql);
                while (!$recordSet->EOF) {
                    // phpcs:ignore
                    include_once dirname(__FILE__) . '/../../include/core.inc.php';
                    $page = new page_admin();
                    $blog_id = $recordSet->fields('blogmain_id');
                    $blog_title = $recordSet->fields('blogmain_title');
                    $seotitle = $page->create_seouri($blog_title, false);
                    $sql_insert[] = 'UPDATE ' . $config['table_prefix'] . "blogmain SET blog_seotitle = '" . $seotitle . "' WHERE blogmain_id = " . $blog_id;
                    $recordSet->MoveNext();
                }
                //Make SEO Title for Pages
                $sql = 'SELECT pagesmain_id,pagesmain_title FROM ' . $config['table_prefix'] . 'pagesmain';
                $recordSet = $conn->Execute($sql);
                while (!$recordSet->EOF) {
                    // phpcs:ignore
                    include_once dirname(__FILE__) . '/../../include/core.inc.php';
                    $page = new page_admin();
                    $page_id = $recordSet->fields('pagesmain_id');
                    $page_title = $recordSet->fields('pagesmain_title');
                    $seotitle = $page->create_seouri($page_title, false);
                    $sql_insert[] = 'UPDATE ' . $config['table_prefix'] . "pagesmain SET page_seotitle = '" . $seotitle . "' WHERE pagesmain_id = " . $page_id;
                    $recordSet->MoveNext();
                }
                //Make SEO Title for Listings
                $sql = 'SELECT listingsdb_id,listingsdb_title FROM ' . $config['table_prefix'] . 'listingsdb';
                $recordSet = $conn->Execute($sql);
                while (!$recordSet->EOF) {
                    // phpcs:ignore
                    include_once dirname(__FILE__) . '/../../include/core.inc.php';
                    $page = new page_admin();
                    $listing_id = $recordSet->fields('listingsdb_id');
                    $listing_title = $recordSet->fields('listingsdb_title');
                    $seotitle = $page->create_seouri($listing_title, false);
                    $sql_insert[] = 'UPDATE ' . $config['table_prefix'] . "listingsdb SET listing_seotitle = '" . $seotitle . "' WHERE listingsdb_id = " . $listing_id;
                    $recordSet->MoveNext();
                }
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'blogtags ADD COLUMN tag_description LONGTEXT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_banned_domains_signup LONGTEXT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_banned_ips_signup LONGTEXT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_banned_ips_site LONGTEXT NULL';

                $sql_insert[] = 'RENAME TABLE ' . $config['table_prefix'] . 'vtourimages TO ' . $config['table_prefix'] . 'listingsvtours';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsvtours CHANGE COLUMN vtourimages_id listingsvtours_id INT4 NOT NULL AUTO_INCREMENT';

                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsvtours CHANGE COLUMN vtourimages_caption listingsvtours_caption varchar(255) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsvtours CHANGE COLUMN vtourimages_file_name listingsvtours_file_name varchar(255) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsvtours CHANGE COLUMN vtourimages_thumb_file_name listingsvtours_thumb_file_name varchar(255) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsvtours CHANGE COLUMN vtourimages_description listingsvtours_description text NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsvtours CHANGE COLUMN vtourimages_rank listingsvtours_rank int(11) NOT NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsvtours CHANGE COLUMN vtourimages_active listingsvtours_active char(3) NOT NULL';

                $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . "feedbackdb (
                  `feedbackdb_id` int(11) NOT NULL auto_increment,
                  `userdb_id` int(11) NOT NULL default '0',
                  `feedbackdb_notes` text NOT NULL,
                  `feedbackdb_creation_date` datetime NOT NULL,
                  `feedbackdb_last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
                  `feedbackdb_last_modified_by` int(11) NOT NULL default '0',
                  `listingdb_id` int(11) default NULL,
                  `feedbackdb_status` tinyint(1) NOT NULL default '0',
                  `feedbackdb_priority` varchar(20) NOT NULL default 'Normal',
                  PRIMARY KEY  (`feedbackdb_id`)
                );";
                $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . "feedbackdbelements (
                  `feedbackdbelements_id` int(11) NOT NULL auto_increment,
                  `feedbackdbelements_field_name` varchar(20) NOT NULL default '',
                  `feedbackdbelements_field_value` text NOT NULL,
                  `feedbackdb_id` int(11) NOT NULL default '0',
                  `userdb_id` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`feedbackdbelements_id`)
                );";
                $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . "feedbackformelements (
                  `feedbackformelements_id` int(11) NOT NULL auto_increment,
                  `feedbackformelements_field_type` varchar(20) NOT NULL default '',
                  `feedbackformelements_field_name` varchar(20) NOT NULL default '',
                  `feedbackformelements_field_caption` varchar(80) NOT NULL default '',
                  `feedbackformelements_default_text` text NOT NULL,
                  `feedbackformelements_field_elements` text NOT NULL,
                  `feedbackformelements_rank` int(11) NOT NULL default '0',
                  `feedbackformelements_required` char(3) NOT NULL default 'No',
                  `feedbackformelements_location` varchar(15) NOT NULL default '',
                  `feedbackformelements_display_on_browse` char(3) NOT NULL default 'No',
                  `feedbackformelements_display_priv` INT4 NOT NULL,
                  PRIMARY KEY  (`feedbackformelements_id`)
                );";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_rss_title_blogposts CHAR VARYING(45)';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN   controlpanel_rss_desc_blogposts CHAR VARYING(255)';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_rss_title_blogcomments CHAR VARYING(45)';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_rss_desc_blogcomments CHAR VARYING(255)';

                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_phpmailer INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_mailserver CHAR VARYING(255) NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_mailport INT4 NOT NULL DEFAULT 25';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_mailuser CHAR VARYING(255) NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_mailpass CHAR VARYING(255) NULL';

                $sql_insert[] = 'UPDATE ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_rss_title_blogposts = 'Recent Blog Posts'";
                $sql_insert[] = 'UPDATE ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_rss_desc_blogposts = 'RSS Feed of our Recent Blog Posts'";
                $sql_insert[] = 'UPDATE ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_rss_title_blogcomments = 'Recent Blog Comments'";
                $sql_insert[] = 'UPDATE ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_rss_desc_blogcomments = 'RSS Feed of our Recent Blog Comments'";
                //Set UTF8 Encoding.
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'addons CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'zipdist CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'activitylog CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'agentformelements CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsdb CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsdbelements CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsformelements CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsimages CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsfiles CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'usersfiles CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsvtours CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'memberformelements CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'pagesmain CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdbelements CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userfavoritelistings CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userimages CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'usersavedsearches CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'forgot CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'classformelements CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'classlistingsdb CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'class CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'blogmain CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'blogcomments CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'blogpingbacks CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'blogcategory CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'blogcategory_relation CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'blogtags CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'blogtag_relation CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'seouri CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'feedbackdb CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'feedbackdbelements CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'feedbackformelements CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_agent_default_canManageAddons INT4 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_agent_default_blogUserType INT4 NOT NULL DEFAULT 3';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'blogpingbacks DROP COLUMN blogpingback_dest';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_agent_default_edit_all_leads  INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "userdb ADD COLUMN userdb_can_edit_all_leads CHAR(3) NOT NULL DEFAULT 'no'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'feedbackdb ADD COLUMN `feedbackdb_member_userdb_id` int(11) NOT NULL default 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_agent_default_edit_lead_template  INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "userdb ADD COLUMN userdb_can_edit_lead_template CHAR(3) NOT NULL DEFAULT 'no'";
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.0.0 Beta 1':
                $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . "tracking (
                  `tracking_id` int(11) NOT NULL auto_increment,
                  `tracking_timestamp` int(11) NOT NULL,
                  `userdb_id`  int(11) NOT NULL default '0',
                  `tracking_ip` varchar(20) NOT NULL default '',
                  `tracking_referal` varchar(255) NOT NULL default '',
                  `tracking_link_type` varchar(20) NOT NULL default '',
                  `tracking_link_type_id` int(11) NOT NULL default '0',
                  `tracking_link_url` varchar(255) NOT NULL default '',
                  `tracking_agentstring` varchar(255) NOT NULL default '',
                  `tracking_browser` varchar(15) NOT NULL default '',
                  `tracking_browserversion` varchar(5) NOT NULL default '',
                  `tracking_os` varchar(10) NOT NULL default '',
                  `tracking_country` varchar(3) NOT NULL default '',
                  `tracking_city` varchar(255) NOT NULL default '',
                  `tracking_loadtime` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`tracking_id`),
                  INDEX idx_broswer (tracking_os,tracking_browser,tracking_browserversion),
                  INDEX idx_location (tracking_country,tracking_city),
                  INDEX idx_user (userdb_id),
                  INDEX idx_ip (tracking_ip),
                  INDEX idx_timestamp (tracking_timestamp),
                  INDEX idx_loadtime (tracking_loadtime)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'feedbackformelements DROP feedbackformelements_display_on_browse';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'feedbackformelements DROP feedbackformelements_display_priv';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'feedbackformelements ADD COLUMN feedbackformelements_tool_tip text NOT NULL';
                $sql_insert[] = 'INSERT INTO  ' . $config['table_prefix'] . "feedbackformelements
        (feedbackformelements_field_type,feedbackformelements_field_name,feedbackformelements_field_caption,feedbackformelements_default_text,
         feedbackformelements_field_elements,feedbackformelements_rank,feedbackformelements_required,feedbackformelements_location,feedbackformelements_tool_tip)
        VALUES
        ('select','source','How did you hear about us?','',
        'Referral||Phone Book||TV ad||Radio ad||Newspaper||Search Engine',1,'No','center','')";
                $sql_insert[] = 'INSERT INTO  ' . $config['table_prefix'] . "feedbackformelements
        (feedbackformelements_field_type,feedbackformelements_field_name,feedbackformelements_field_caption,feedbackformelements_default_text,
         feedbackformelements_field_elements,feedbackformelements_rank,feedbackformelements_required,feedbackformelements_location,feedbackformelements_tool_tip)
        VALUES
        ('textarea','comments','Comments, questions or suggestions?','','',2,'No','center','')";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'blogmain ADD COLUMN  blogmain_full_autosave LONGTEXT NULL ';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'pagesmain ADD COLUMN  pagesmain_full_autosave LONGTEXT NULL ';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.0.0 Beta 2':
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_wysiwyg_editor';
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_enable_tracking INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_enable_tracking_crawlers INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_show_agent_no_photo INT2 NOT NULL DEFAULT 1';
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_template_lead_sections TEXT NOT NULL';
                $sql_insert[] = 'UPDATE ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_template_lead_sections = 'headline,top_left,top_right,center,bottom_left,bottom_right'";

                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_use_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_main_admin_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_add_listing_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_edit_listing_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_edit_user_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_user_manager_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_page_editor_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_modify_listing_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_edit_listing_images_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_edit_vtour_images_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_edit_listing_files_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_edit_agent_template_add_field_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_edit_agent_template_field_order_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_edit_member_template_add_field_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_edit_listing_template_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_edit_listing_template_add_field_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_edit_listings_template_field_order_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_edit_listing_template_search_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_edit_listing_template_search_results_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_show_property_classes_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_configure_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_view_log_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_addon_transparentmaps_admin_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_addon_transparentmaps_geocode_all_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_addon_transparentRETS_config_server_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_addon_transparentRETS_config_imports_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_user_template_member_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_user_template_agent_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_modify_property_class_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_insert_property_class_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_addon_IDXManager_config_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_addon_IDXManager_classmanager_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_addon_csvloader_admin_help_link';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_edit_member_template_field_order_help_link';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.0.0 Beta 3':
            case '3.0.0 Beta 4':
            case '3.0.0':
            case '3.0.1':
            case '3.0.2':
            case '3.0.3':
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_apikey CHAR VARYING(255) DEFAULT '" . $this->password_gen(32) . "'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'activitylog CHANGE COLUMN activitylog_action activitylog_action TEXT NULL';

                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.0.4':
                $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . "menu (
                  `menu_id` int(11) NOT NULL auto_increment,
                  `menu_name` varchar(25) NOT NULL default '',
                  PRIMARY KEY  (`menu_id`)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";

                $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix'] . "menu_items (
                  `item_id` int(11) NOT NULL auto_increment,
                  `parent_id` int(11) NOT NULL default '0',
                  `menu_id`  int(11) NOT NULL default '0',
                  `item_name` varchar(25) NOT NULL default '',
                  `item_order`  int(11) NOT NULL default '0',
                  `item_type`  int(11) NOT NULL default '0',
                  `item_value` varchar(255) NOT NULL default '',
                  `item_privilage`  int(11) NOT NULL default '0',
                  PRIMARY KEY  (`item_id`),
                  INDEX idx_menu (menu_id,item_id,parent_id,item_order,item_privilage)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_allow_template_change INT2 NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_allow_language_change INT2 NOT NULL DEFAULT 0';

                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.0.5':
            case '3.0.6':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_twitter_user';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_twitter_password';
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_twitter_auth LONGTEXT NULL';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.0.7':
            case '3.0.8':
            case '3.0.9':
            case '3.0.10':
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_listingimages_slideshow_group_thumb INT4 NOT NULL DEFAULT 4';
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_admin_listing_per_page INT4 NOT NULL DEFAULT 10';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.0.11':
            case '3.0.12':
            case '3.0.12p1':
            case '3.0.13':
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_mobile_template CHAR VARYING(45) NOT NULL DEFAULT 'mobile'";

                $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . "sessions(
                        SessionID VARCHAR(64),
			                  session_da
                        ta TEXT NULL,
			                  expiry INT(11),
			                  expireref VARCHAR(64) DEFAULT '',
			                  PRIMARY KEY (SessionID),
			                  INDEX expiry (expiry)
			               ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_captcha_system CHAR VARYING(45) NOT NULL DEFAULT 'securimage'";
                //Get Rid of Muli Pclass Support
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsdb ADD COLUMN listingsdb_pclass_id INT4 NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_multiple_pclass_selection';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                $sql = 'SELECT listingsdb_id,class_id FROM ' . $config['table_prefix_no_lang'] . 'classlistingsdb';
                $recordSet = $conn->Execute($sql);
                while (!$recordSet->EOF) {
                    $listing_id = $recordSet->fields('listingsdb_id');
                    $class_id = $recordSet->fields('class_id');
                    $sql_insert[] = 'UPDATE ' . $config['table_prefix'] . 'listingsdb SET listingsdb_pclass_id = ' . $class_id . ' WHERE listingsdb_id = ' . $listing_id;
                    $recordSet->MoveNext();
                }
                $sql_insert[] = 'DROP TABLE ' . $config['table_prefix_no_lang'] . 'classlistingsdb';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.1.0aplha1':
            case '3.1.0aplha2':
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_floor_agent CHAR VARYING(255) NULL';
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_floor_agent_last INT4 NULL';
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix'] . 'userdb ADD COLUMN userdb_send_notifications_to_floor INT4 NOT NULL default 0';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.1.0beta1':
                $sql_insert[] = 'CREATE INDEX idx_listingsdb_pclass_id ON ' . $config['table_prefix'] . 'listingsdb (listingsdb_pclass_id)';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'listingsdb DROP INDEX idx_listfieldmashup';
                $this->run_sql($sql_insert, true);
                $sql_insert = [];
                $sql_insert[] = 'CREATE INDEX idx_listfieldmashup  ON ' . $config['table_prefix'] . 'listingsdb (listingsdb_id ,listingsdb_pclass_id,listingsdb_active,userdb_id);';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.1.0beta2':
            case '3.1.0beta3':
            case '3.1.0beta4':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'sessions CHANGE COLUMN session_data session_data LONGTEXT NULL';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.1.0beta5':
            case '3.1.0beta6':
            case '3.1.0':
            case '3.1.1':
            case '3.1.2':
                $sql_insert[] = 'UPDATE ' . $config['table_prefix_no_lang'] . "seouri SET action = 'contact_listing_agent', uri = '{listing_id}/{agent_fname}-{agent_lname}.html' WHERE action = 'contactagent';";
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.1.3':
            case '3.1.4':
            case '3.1.5':
            case '3.1.6':
            case '3.1.7':
            case '3.1.8':
            case '3.1.9':
                $sql = 'SELECT controlpanel_charset FROM ' . $config['table_prefix_no_lang'] . 'controlpanel';
                $recordSet = $conn->Execute($sql);
                $charset = $recordSet->fields('controlpanel_charset');
                $config['charset'] = $charset;
                $sql = 'SELECT pagesmain_full, pagesmain_id FROM ' . $config['table_prefix'] . 'pagesmain';
                $recordSet = $conn->Execute($sql);
                while (!$recordSet->EOF) {
                    $page_id = $recordSet->fields('pagesmain_id');
                    $full = $recordSet->fields('pagesmain_full');
                    $full = html_entity_decode($full, ENT_QUOTES, $config['charset']);
                    $full = html_entity_decode($full, ENT_QUOTES, $config['charset']);
                    //$full = str_replace(array("&lt;", "&gt;", '&amp;', '&#039;', '&quot;','&lt;', '&gt;'), array("<", ">",'&','\'','"','<','>'),$full);
                    $full = $conn->qstr($full);
                    $sql_insert[] = 'UPDATE ' . $config['table_prefix'] . "pagesmain SET pagesmain_full = $full, pagesmain_full_autosave = '' WHERE pagesmain_id = " . $page_id;
                    $recordSet->MoveNext();
                }

                $sql = 'SELECT blogmain_full, blogmain_id FROM ' . $config['table_prefix'] . 'blogmain';
                $recordSet = $conn->Execute($sql);
                while (!$recordSet->EOF) {
                    $page_id = $recordSet->fields('blogmain_id');
                    $full = $recordSet->fields('blogmain_full');
                    $full = html_entity_decode($full, ENT_QUOTES, $config['charset']);
                    $full = html_entity_decode($full, ENT_QUOTES, $config['charset']);
                    //$full = str_replace(array("&lt;", "&gt;", '&amp;', '&#039;', '&quot;','&lt;', '&gt;'), array("<", ">",'&','\'','"','<','>'),$full);
                    $full = $conn->qstr($full);
                    $sql_insert[] = 'UPDATE ' . $config['table_prefix'] . "blogmain SET blogmain_full = $full, blogmain_full_autosave = '' WHERE blogmain_id = " . $page_id;
                    $recordSet->MoveNext();
                }
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.1.10':
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_use_email_image_verification';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.1.11':
                $sql_insert[] = 'ALTER TABLE  ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_contact_template CHAR VARYING(45) NOT NULL';
                $sql_insert[] = 'UPDATE ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_contact_template = 'contact_agent_free.html'";
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.1.12':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_rss_title_latestlisting CHAR VARYING(45) NOT NULL DEFAULT 'Latest Listings Feed'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_rss_desc_latestlisting CHAR VARYING(255) NOT NULL DEFAULT 'RSS feed of our latest listings'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_rss_listingdesc_latestlisting TEXT;';
                $sql_insert[] = 'UPDATE ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_rss_listingdesc_latestlisting = '<table><tr><td>{image_thumb_fullurl_1}</td><td>{listing_field_full_desc_rawvalue}</td></tr></table>';";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_rss_limit_latestlisting INT4 NOT NULL DEFAULT 50';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.1.13':
            case '3.1.14':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_gd_version';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_allowed_upload_types';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_user_jpeg_quality INT4 NOT NULL DEFAULT 75';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_user_resize_img INT4 NOT NULL DEFAULT 1';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_user_resize_by CHAR VARYING(20) NOT NULL DEFAULT 'height'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel ADD COLUMN controlpanel_user_resize_thumb_by CHAR VARYING(20) NOT NULL DEFAULT 'height'";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_user_thumbnail_width INT4 NOT NULL DEFAULT 80';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_user_thumbnail_height INT4 NOT NULL DEFAULT 100';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_num_popular_listings INT4 NOT NULL DEFAULT 4';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_num_random_listings INT4 NOT NULL DEFAULT 4';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_num_latest_listings INT4 NOT NULL DEFAULT 4';
                $this->run_sql($sql_insert);
                $sql_insert = [];
                // no break
            case '3.2.0':
            case '3.2.1':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'menu_items DROP COLUMN item_privilage';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'menu_items ADD COLUMN visible_guest BOOL NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'menu_items ADD COLUMN visible_member BOOL NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'menu_items ADD COLUMN visible_agent BOOL NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'menu_items ADD COLUMN visible_admin BOOL NOT NULL DEFAULT 0';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "menu_items MODIFY COLUMN item_name CHAR VARYING(255) NOT NULL DEFAULT ''";
                $sql_insert[] = 'CREATE INDEX idx_listingsimages_file_name ON ' . $config['table_prefix'] . 'listingsimages(listingsimages_file_name);';
                $sql_insert[] = 'CREATE INDEX idx_listingsfiles_file_name ON ' . $config['table_prefix'] . 'listingsfiles(listingsfiles_file_name);';
                $sql_insert[] = 'CREATE INDEX idx_listingsvtours_file_name ON ' . $config['table_prefix'] . 'listingsvtours(listingsvtours_file_name);';
                $sql_insert[] = 'CREATE INDEX idx_userimages_file_name ON ' . $config['table_prefix'] . 'userimages(userimages_file_name);';
                $sql_insert[] = 'CREATE INDEX idx_usersfiles_file_name ON ' . $config['table_prefix'] . 'usersfiles(usersfiles_file_name);';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "menu_items ADD COLUMN  item_target varchar(255) NOT NULL default '_self';";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . "menu_items ADD COLUMN  item_class varchar(255) NOT NULL default '';";
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'menu_items DROP PRIMARY KEY, ADD PRIMARY KEY(item_id);';
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu (`menu_id`,`menu_name`) VALUES ('1', 'vertical');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu (`menu_id`,`menu_name`) VALUES ('2', 'horizontal');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('1', '2', '1', 'prop_class_search_block', '0', '5', 'pclass_searchlinks_block', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('2', '0', '1', 'Property Classes', '5', '3', '#', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('9', '0', '1', 'View All Listings', '1', '1', 'url_search_results', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('10', '0', '1', 'View Blog', '14', '1', 'url_blog', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('11', '0', '1', 'View Agents', '2', '1', 'url_view_agents', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('12', '0', '1', 'Loan Calculators', '3', '1', 'url_view_calculator', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('32', '0', '2', 'Home', '1', '1', 'url_index', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('33', '0', '2', 'View All Listings', '2', '1', 'url_search_results', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('34', '0', '2', 'Search Listings', '3', '3', '{baseurl}/index.php?action=search_step_2', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('35', '0', '2', 'About Us', '4', '3', '{page_link_3}', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('36', '0', '2', 'Legal', '5', '3', '{page_link_4}', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('37', '0', '2', 'Contact Us', '6', '3', '{page_link_2}', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('38', '0', '1', 'Main Menu', '0', '4', '', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('39', '0', '1', 'Search', '4', '4', '', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('40', '0', '1', 'Your Account', '6', '4', '', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('41', '0', '1', 'Member Signup', '10', '1', 'url_member_signup', '1', '0', '0', '0', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('42', '0', '1', 'Agent Signup', '9', '1', 'url_agent_signup', '1', '0', '0', '0', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('43', '0', '1', 'Member Login', '12', '1', 'url_member_login', '1', '0', '0', '0', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('44', '0', '1', 'Syndication', '17', '4', '', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('45', '61', '1', 'Featured Listings', '0', '1', 'rss_featured', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('46', '61', '1', 'Last Added Listings', '1', '1', 'rss_latestlisting', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('47', '61', '1', 'Last Modified Listings', '2', '1', 'rss_lastmodified', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('48', '61', '1', 'Recent Blog Posts', '3', '1', 'rss_blog_posts', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('50', '61', '1', 'Recent Blog Comments', '4', '1', 'rss_blog_comments', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('52', '63', '1', 'blog_cat_Block', '0', '5', 'blog_category_link_block', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('53', '0', '1', 'Blog', '13', '4', '', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('54', '62', '1', 'blog_archive_block', '0', '5', 'blog_archive_link_block', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('55', '0', '1', 'Recent Posts', '19', '4', '', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('56', '0', '1', 'recent_post_block', '20', '5', 'blog_recent_post_block', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('57', '0', '1', 'Recent Comments', '21', '4', '', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('58', '0', '1', 'recent_comments_block', '22', '5', 'blog_recent_comments_block', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('61', '0', '1', 'RSS Feeds', '18', '3', '#', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('62', '0', '1', 'Archives', '15', '3', '#', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('63', '0', '1', 'Categories', '16', '3', '#', '1', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('83', '0', '1', 'Edit profile', '7', '1', 'url_edit_profile', '0', '1', '0', '0', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('84', '0', '1', 'Logout', '8', '1', 'url_logout', '0', '1', '1', '1', '_self', '');";
                $sql_insert[] = ' INSERT INTO ' . $config['table_prefix'] . "menu_items  (`item_id`,`parent_id`,`menu_id`,`item_name`,`item_order`,`item_type`,`item_value`,`visible_guest`,`visible_member`,`visible_agent`,`visible_admin`,`item_target`,`item_class`) VALUES ('85', '0', '1', 'Agent Login', '11', '1', 'url_agent_login', '1', '0', '0', '0', '_self', '');";
                // no break
            case '3.2.2':
            case '3.2.3':
            case '3.2.4':
            case '3.2.5':
            case '3.2.6':
            case '3.2.7':
            case '3.2.8':
            case '3.2.9':
            case '3.2.10':
            case '3.2.11':
            case '3.2.12':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "sessions 
                        CHANGE COLUMN `SessionID` `sesskey` VARCHAR(64) NOT NULL DEFAULT '',
                        CHANGE COLUMN `session_data` `sessdata` LONGTEXT,
                        ADD COLUMN `created` DATETIME NOT NULL AFTER `expireref`,
                        ADD COLUMN `modified` DATETIME NOT NULL AFTER `created`,
                        ADD COLUMN `expiry2` TIMESTAMP NOT NULL";

                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . 'sessions 
                        SET `expiry2` = FROM_UNIXTIME(`expiry`)';

                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "sessions
                        DROP COLUMN `expiry`,
                        CHANGE `expiry2` `expiry` TIMESTAMP NOT NULL,
                        MODIFY `expireref` VARCHAR(250) DEFAULT '',
                        DROP INDEX `expiry`,
                        DROP PRIMARY KEY, 
                        ADD PRIMARY KEY (sesskey),
                        ADD INDEX sess_expiry(expiry),
                        ADD INDEX sess_expireref(expireref);";

                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel 
                        ADD COLUMN controlpanel_twitter_listing_photo 
                        INT(1) NOT NULL DEFAULT 0  AFTER `controlpanel_twitter_new_blog`';

                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel 
                        ADD COLUMN controlpanel_map_latitude 
                        CHAR VARYING(45) NOT NULL DEFAULT '' AFTER `controlpanel_map_country`";

                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . "controlpanel 
                        ADD COLUMN controlpanel_map_longitude 
                        CHAR VARYING(45) NOT NULL DEFAULT '' AFTER `controlpanel_map_latitude`";
                // no break
            case '3.3':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel  
                        ADD COLUMN `controlpanel_recaptcha_sitekey` CHAR VARYING(255) NULL AFTER `controlpanel_captcha_system`,
                        ADD COLUMN `controlpanel_recaptcha_secretkey` CHAR VARYING(255) NULL AFTER `controlpanel_recaptcha_sitekey`,
                        ADD COLUMN `controlpanel_twitter_consumer_secret` CHAR VARYING(255) NULL AFTER `controlpanel_notify_listings_template`,
                        ADD COLUMN `controlpanel_twitter_consumer_key` CHAR VARYING(255) NULL AFTER `controlpanel_twitter_consumer_secret`';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb 
                        ADD COLUMN `userdb_verification_hash` CHAR VARYING(32) NULL';
                // no break
            case '3.4.0-beta.1':
            case '3.4.0-beta.2':
            case '3.4.0-beta.3':
            case '3.4.0-beta.4':
            case '3.4.0':
            case '3.4.1':
            case '3.4.2':
            case '3.4.3':
            case '3.5.0-beta.1':
            case '3.5.0':
            case '3.5.1':
            case '3.5.2':
                $sql_insert[] = 'UPDATE ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_contact_template = 'contact_agent_default.html'";
                // no break
            case '3.5.3':
            case '3.5.4':
            case '3.5.5':
            case '3.5.6':
            case '3.5.7':
            case '3.5.8':
            case '3.5.9':
            case '3.5.10':
            case '3.6.0-alpha.1':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_wysiwyg_execute_php';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_apikey';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel DROP COLUMN controlpanel_vtour_fov';
                // no break
            case '3.6.0-alpha.2':
            case '3.6.0-beta.1':
            case '3.6.0':
            case '3.6.1':
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_google_client_id CHAR VARYING(255) NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix_no_lang'] . 'controlpanel ADD COLUMN controlpanel_google_client_secret CHAR VARYING(255) NULL';
                $sql_insert[] = 'ALTER TABLE ' . $config['table_prefix'] . 'userdb MODIFY COLUMN userdb_user_password CHAR VARYING(255) NOT NULL';
                $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . 'auth_tokens (
                    token_id INT4 NOT NULL AUTO_INCREMENT,
                    selector CHAR VARYING(255) NOT NULL,
                    validator CHAR VARYING(255) NOT NULL,
                    userdb_id INT4 NOT NULL,
                    expires DATETIME NOT NULL,
                    PRIMARY KEY(token_id)
                  )';
                // no break
            case '3.6.2':
            
            default:
                // Update version
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET  controlpanel_basepath ='" . trim($_SESSION['basepath']) . "', controlpanel_baseurl = '" . trim($_SESSION['baseurl']) . "'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET  controlpanel_version ='" . $this->version_number . "'";
                break;
        }
        $this->run_sql($sql_insert);
        $sql_insert = [];
    }
    public function run_sql($sql_insert = [], $SilentFail = false)
    {
        if (empty($sql_insert)) {
            return;
        }
        // this is the setup for the ADODB library
        // phpcs:ignore
        include_once dirname(__FILE__) . '/../../vendor/adodb/adodb-php/adodb.inc.php';

        $conn = ADONewConnection($_SESSION['db_type']);
        $conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, 'utf8');
        $conn->connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']);
        //$conn->Connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']);

        $config['table_prefix'] = $_SESSION['table_prefix'] . $_SESSION['or_install_lang'] . '_';
        $config['table_prefix_no_lang'] = $_SESSION['table_prefix'];
        foreach ($sql_insert as $elementContents) {
            $recordSet = $conn->Execute($elementContents);
            if ($recordSet === false) {
                if ($_SESSION['devel_mode'] == 'no') {
                    if (!$SilentFail) {
                        die("<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />");
                    }
                } else {
                    echo "<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />";
                }
            }
        }
    }
    public function create_index($old_version)
    {
        // this is the setup for the ADODB library
        // phpcs:ignore
        include_once dirname(__FILE__) . '/../../vendor/adodb/adodb-php/adodb.inc.php';

        $conn = ADONewConnection($_SESSION['db_type']);
        $conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, 'utf8');
        $conn->connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']);

        $config['table_prefix'] = $_SESSION['table_prefix'] . $_SESSION['or_install_lang'] . '_';
        $config['table_prefix_no_lang'] = $_SESSION['table_prefix'];

        $sql_insert = [];
        switch ($old_version) {
            case '2.0 Beta 1':
            case '2.0 Beta 2':
            case '2.0.0':
            case '2.0.1':
            case '2.0.2':
            case '2.0.3':
            case '2.0.4':
            case '2.0.5':
            case '2.0.6':
            case '2.0.7':
            case '2.0.8':
            case '2.1.0':
            case '2.1.1':
            case '2.1.2':
            case '2.1.3':
            case '2.1.4':
            case '2.1.5':
                $sql_insert[] = 'CREATE INDEX idx_classformelements_class_id ON ' . $config['table_prefix_no_lang'] . 'classformelements (class_id);';
                $sql_insert[] = 'CREATE INDEX idx_classformelements_listingsformelements_id ON ' . $config['table_prefix_no_lang'] . 'classformelements (listingsformelements_id);';

                $sql_insert[] = 'CREATE INDEX idx_classlistingsdb_class_id ON ' . $config['table_prefix_no_lang'] . 'classlistingsdb (class_id);';
                $sql_insert[] = 'CREATE INDEX idx_classlistingsdb_listingsdb_id ON ' . $config['table_prefix_no_lang'] . 'classlistingsdb (listingsdb_id);';

                $sql_insert[] = 'CREATE INDEX idx_class_rank ON ' . $config['table_prefix'] . 'class (class_rank);';
                // no break
            case '2.1.6dev':
            case '2.2.0':
            case '2.3.0':
            case '2.3.1':
            case '2.3.2':
            case '2.3.3':
            case '2.3.4':
            case '2.3.5':
            case '2.3.6':
                if ($_SESSION['db_type'] == 'mysqli') {
                    $sql_insert[] = 'CREATE INDEX idx_user_field_value ON ' . $config['table_prefix'] . 'userdbelements (userdbelements_field_value(255));';
                } else {
                    $sql_insert[] = 'CREATE INDEX idx_user_field_value ON ' . $config['table_prefix'] . 'userdbelements (userdbelements_field_value);';
                }
                $sql_insert[] = 'CREATE INDEX idx_user_field_name ON ' . $config['table_prefix'] . 'userdbelements (userdbelements_field_name);';
                $sql_insert[] = 'CREATE INDEX idx_userdb_userid ON ' . $config['table_prefix'] . 'userdbelements (userdb_id);';

                // no break
            case '2.4.0':
            case '2.4.1':
            case '2.4.2':
            case '2.4.3':
            case '2.4.4':
            case '2.5.0':
            case '2.5.1':
            case '2.5.2':
                $sql_insert[] = 'CREATE INDEX idx_agentformelements_field_name ON ' . $config['table_prefix'] . 'agentformelements (agentformelements_field_name);';
                $sql_insert[] = 'CREATE INDEX idx_userimages_userdb_id ON ' . $config['table_prefix'] . 'userimages (userdb_id);';
                $sql_insert[] = 'CREATE INDEX idx_userimages_userimages_rank ON ' . $config['table_prefix'] . 'userimages (userimages_rank);';

                // no break
            case '2.5.5':
                $sql_insert[] = 'CREATE INDEX idx_listfieldmashup  ON ' . $config['table_prefix'] . 'listingsdb (listingsdb_id ,listingsdb_active,userdb_id);';
                $sql_insert[] = 'CREATE INDEX idx_fieldmashup  ON ' . $config['table_prefix'] . 'listingsdbelements (listingsdbelements_field_name,listingsdb_id);';
                $sql_insert[] = 'CREATE INDEX idx_classfieldmashup  ON ' . $config['table_prefix_no_lang'] . 'classlistingsdb (listingsdb_id ,class_id);';

                // no break
            default:
                break;
        }

        foreach ($sql_insert as $elementContents) {
            $recordSet = $conn->Execute($elementContents);
            if ($recordSet === false) {
                if ($_SESSION['devel_mode'] == 'no') {
                    die("<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />");
                } else {
                    echo "<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />";
                }
            }
        }
        $sql_insert = [];
    }
    public function insert_values($old_version)
    {
        $sql_insert = [];
        // this is the setup for the ADODB library
        // phpcs:ignore
        include_once dirname(__FILE__) . '/../../vendor/adodb/adodb-php/adodb.inc.php';

        $conn = ADONewConnection($_SESSION['db_type']);
        $conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, 'utf8');
        $conn->connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']);

        $config['table_prefix'] = $_SESSION['table_prefix'] . $_SESSION['or_install_lang'] . '_';
        $config['table_prefix_no_lang'] = $_SESSION['table_prefix'];
        switch ($old_version) {
            case '2.0 Beta 1':
            case '2.0 Beta 2':
            case '2.0.0':
            case '2.0.1':
            case '2.0.2':
            case '2.0.3':
            case '2.0.4':
            case '2.0.5':
            case '2.0.6':
            case '2.0.7':
            case '2.0.8':
            case '2.1.0':
            case '2.1.1':
            case '2.1.2':
            case '2.1.3':
            case '2.1.4':
            case '2.1.5':
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_template_listing_sections = 'headline,top_left,top_right,center,feature1,feature2,bottom_left,bottom_right'";
                // no break
            case '2.1.6dev':
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_agent_template = 'view_user_default.html'";
                // no break
            case '2.2.0':
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_vtour_template = 'vtour_default.html'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_vtour_width = '400'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_vtour_height = '250'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_vt_popup_width = '800'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_vt_popup_height = '480'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_vtour_fov = '70'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_zero_price = '0'";
                // no break
            case '2.3.6':
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_max_search_results = 0';
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_feature_list_separator = '<br />'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_search_list_separator = '<br />'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_use_email_image_verification = 0';
                // no break
            case '2.4.1':
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_thumbnail_height = '100'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_resize_thumb_by = 'width'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_resize_by = 'width'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_max_listings_upload_height = '700' WHERE controlpanel_max_listings_upload_height = 0";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_max_user_upload_height = '700' WHERE controlpanel_max_user_upload_height = 0";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_charset = 'ISO-8859-1'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_wysiwyg_show_edit = '1'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_textarea_short_chars = '100'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_main_image_display_by = 'width'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_main_image_width = '500'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_main_image_height = '700'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_number_columns = '4'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_rss_limit_featured = '50'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_force_decimals = '0'";
                // no break
            case '2.4.4':
            case '2.4.3':
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_icon_image_width = '16'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_icon_image_height = '16'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_max_listings_file_uploads = '7'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_max_listings_file_upload_size = '2097152'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_allowed_file_upload_extensions = 'jpg,gif,png,pdf,doc,swf,avi,mov,mpg,zip,sbd,stc,std,sti,stw,svw,sxc,sxd,sxg,sxi,sxm'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_show_file_icon = '1'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_show_file_size = '1'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_file_display_option = 'both'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_agent_default_havefiles = '0'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix'] . "userdb SET userdb_can_have_files = 'yes' WHERE userdb_can_have_vtours = 'yes'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix'] . "userdb SET userdb_can_have_files = 'no' WHERE userdb_can_have_vtours = 'no'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_max_users_file_uploads = '7'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_max_users_file_upload_size = '2097152'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_agent_default_havefiles = '0'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix'] . "userdb SET userdb_can_have_user_files = 'yes' WHERE userdb_can_have_vtours = 'yes'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix'] . "userdb SET userdb_can_have_user_files = 'no' WHERE userdb_can_have_vtours = 'no'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_show_notes_field = '1'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_disable_referrer_check = '0'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_seo_url_seperator = ' '";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_search_step_max = '100'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_special_search_sortby = 'none'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_special_search_sorttype = 'DESC'";
                // no break
            case '2.5.0':
            case '2.5.1':
            case '2.5.2':
                /*
                       * $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_use_help_link = '1'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_main_admin_help_link = 'http://wiki.open-realty.org/Admin_guide'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_add_listing_help_link = 'http://wiki.open-realty.org/Admin_create_new_listing'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_edit_listing_help_link = 'http://wiki.open-realty.org/Admin_edit_listings'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_page_editor_help_link = 'http://wiki.open-realty.org/Admin_Page_Editor'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_edit_user_help_link = 'http://wiki.open-realty.org/Admin_Edit_User'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_user_manager_help_link = 'http://wiki.open-realty.org/Admin_user_manager'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_modify_listing_help_link = 'http://wiki.open-realty.org/Admin_modify_listing'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_edit_listing_images_help_link = 'http://wiki.open-realty.org/Admin_edit_images'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_edit_vtour_images_help_link = 'http://wiki.open-realty.org/Admin_edit_vtour'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_edit_listing_files_help_link = 'http://wiki.open-realty.org/Admin_edit_files'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_edit_agent_template_add_field_help_link = 'http://wiki.open-realty.org/Agentmember_template_edit_add_field'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_edit_agent_template_field_order_help_link = 'http://wiki.open-realty.org/Agentmember_template_set_field_order'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_edit_member_template_add_field_help_link = 'http://wiki.open-realty.org/Agentmember_template_edit_add_field'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_edit_listing_template_help_link = 'http://wiki.open-realty.org/Admin_edit_listing_template'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_edit_listing_template_add_field_help_link = 'http://wiki.open-realty.org/Listing_template_edit_add_field'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_edit_listings_template_field_order_help_link = 'http://wiki.open-realty.org/Listing_template_set_field_order'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_edit_listing_template_search_help_link = 'http://wiki.open-realty.org/Listing_template_search_setup'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_edit_listing_template_search_results_help_link = 'http://wiki.open-realty.org/Listing_template_search_results_setup'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_show_property_classes_help_link = 'http://wiki.open-realty.org/Admin_property_classes'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_configure_help_link = 'http://wiki.open-realty.org/Site_Configuration'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_view_log_help_link = 'http://wiki.open-realty.org/Admin_view_site_log'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_addon_transparentmaps_admin_help_link = 'http://wiki.open-realty.org/TransparentMaps_configuration'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_addon_transparentmaps_geocode_all_help_link = 'http://wiki.open-realty.org/TransparentMaps_cron_jobs'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_addon_transparentRETS_config_server_help_link = 'http://wiki.open-realty.org/TransparentRETS_ug_config_server'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_addon_transparentRETS_config_imports_help_link = 'http://wiki.open-realty.org/TransparentRETS_ug_class_settigns'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_user_template_member_help_link = 'http://wiki.open-realty.org/Admin_edit_agent_member_template'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_user_template_agent_help_link = 'http://wiki.open-realty.org/Admin_edit_agent_member_template'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_modify_property_class_help_link = 'http://wiki.open-realty.org/Property_class_insertmodify'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_insert_property_class_help_link = 'http://wiki.open-realty.org/Property_class_insert'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_addon_IDXManager_config_help_link = 'http://wiki.open-realty.org/Addon_IDXManager'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_addon_IDXManager_classmanager_help_link = 'http://wiki.open-realty.org/Addon_IDXManager'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_addon_csvloader_admin_help_link = 'http://wiki.open-realty.org/Addon_csvloader_user_guide'";
                      $sql_insert[] = "UPDATE  " . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_edit_member_template_field_order_help_link = 'http://wiki.open-realty.org/Agentmember_template_set_field_order'";
                      */
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_show_admin_on_agent_list = 0';
                // no break
            case '2.5.6':
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_agent_default_num_featuredlistings = -1';
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix'] . 'userdb SET userdb_rank = 0';
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix'] . 'userdb SET userdb_featuredlistinglimit = -1';
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_use_signup_image_verification = 0';
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_mbstring_enabled = '0'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_require_email_verification = 0';
                // no break
            case '2.5.7':
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix'] . "userdb SET userdb_blog_user_type = 4 WHERE userdb_is_admin = 'yes'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . 'controlpanel SET controlpanel_notification_last_timestamp = CURRENT_TIMESTAMP';
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_charset = 'UTF-8'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix'] . "userdb SET userdb_can_manage_addons = 'yes' WHERE userdb_is_admin = 'yes'";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix'] . "userdb SET userdb_can_manage_addons = 'no' WHERE userdb_is_admin = 'no'";
                // no break
            case '2.5.8':
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_blog_pingback_urls = 'http://blogsearch.google.com/ping/RPC2
http://api.moreover.com/ping
http://ping.weblogalot.com/rpc.php'";
                $sql_insert[] = 'INSERT INTO  ' . $config['table_prefix'] . "blogcategory (`category_id`,`category_name`,`category_seoname`,`category_rank`) VALUES(1,'Default','default','0')";
                $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel SET controlpanel_admin_template = 'OR_small'";

                break;
        }
        foreach ($sql_insert as $elementContents) {
            $recordSet = $conn->Execute($elementContents);
            if ($recordSet === false) {
                if ($_SESSION['devel_mode'] == 'no') {
                    die("<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />");
                } else {
                    echo "<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />";
                }
            }
        }
    }
    public function load_version()
    {
        $this->load_lang($_SESSION['or_install_lang']);
        switch ($_GET['step']) {
            case 'autoupdate':
                $settings = $this->load_prev_settings(true);
                $_SESSION['table_prefix'] = $settings['table_prefix'];
                $_SESSION['db_type'] = $settings['db_type'];
                $_SESSION['db_user'] = $settings['db_user'];
                if ($settings['db_password'] != false) {
                    $_SESSION['db_password'] = $settings['db_password'];
                } else {
                    $_SESSION['db_password'] = '';
                }

                $_SESSION['db_database'] = $settings['db_database'];
                $_SESSION['db_server'] = $settings['db_server'];
                $www = $this->get_base_url();
                $physical = $this->get_base_path();
                $_SESSION['basepath'] = $physical;
                $_SESSION['baseurl'] = $www;
                if (isset($_GET['devel_mode'])) {
                    $_SESSION['devel_mode'] = $_GET['devel_mode'];
                } else {
                    $_SESSION['devel_mode'] = 'no';
                }

                $this->write_config();
                $old_version = $this->get_previous_version();
                if (empty($old_version)) {
                    echo $this->lang['install_get_old_version_error'];
                    break;
                }
                $this->update_tables($old_version);
                $this->create_tables($old_version);
                $this->create_index($old_version);
                $this->insert_values($old_version);
                $this->database_maintenance();
                echo '<br /><strong>' . $this->lang['install_installation_complete'] . '</strong>';
                break;
            case 4:
                $this->load_prev_settings();
                break;
            case 5:
                $_SESSION['table_prefix'] = trim($_POST['table_prefix']);
                $_SESSION['db_type'] = trim($_POST['db_type']);
                $_SESSION['db_user'] = trim($_POST['db_user']);
                $_SESSION['db_password'] = trim($_POST['db_password']);
                $_SESSION['db_database'] = trim($_POST['db_database']);
                $_SESSION['db_server'] = trim($_POST['db_server']);
                $_SESSION['basepath'] = trim($_POST['basepath']);
                $_SESSION['baseurl'] = trim($_POST['baseurl']);
                $_SESSION['devel_mode'] = $_POST['devel_mode'];
                $this->write_config();
                break;
            case 6:
                $old_version = $this->get_previous_version();
                if (empty($old_version)) {
                    echo $this->lang['install_get_old_version_error'];
                    break;
                }
                $this->update_tables($old_version);
                $this->create_tables($old_version);
                $this->create_index($old_version);
                $this->insert_values($old_version);
                $this->database_maintenance();
                echo '<br /><strong>' . $this->lang['install_installation_complete'] . ' <a href="../admin/index.php?action=configure">' . $this->lang['install_configure_installation'] . '</a></strong>';
                break;
        }
    }
    public function parseDate($date, $format)
    {
        //Supported formats
        //%Y - year as a decimal number including the century
        //%m - month as a decimal number (range 01 to 12)
        //%d - day of the month as a decimal number (range 01 to 31)
        //%H - hour as a decimal number using a 24-hour clock (range 00 to 23)
        //%M - minute as a decimal number
        // Builds up date pattern from the given $format, keeping delimiters in place.
        if (!preg_match_all('/%([YmdHMp])([^%])*/', $format, $formatTokens, PREG_SET_ORDER)) {
            return false;
        }
        $datePattern = '';
        foreach ($formatTokens as $formatToken) {
            $delimiter = preg_quote($formatToken[2], '/');
            $datePattern .= '(.*)' . $delimiter;
        }
        // Splits up the given $date
        if (!preg_match('/' . $datePattern . '/', $date, $dateTokens)) {
            return false;
        }
        $dateSegments = [];
        for ($i = 0; $i < count($formatTokens); $i++) {
            $dateSegments[$formatTokens[$i][1]] = $dateTokens[$i + 1];
        }
        // Reformats the given $date into US English date format, suitable for strtotime()
        if ($dateSegments['Y'] && $dateSegments['m'] && $dateSegments['d']) {
            $dateReformated = $dateSegments['Y'] . '-' . $dateSegments['m'] . '-' . $dateSegments['d'];
        } else {
            return false;
        }
        if ($dateSegments['H'] && $dateSegments['M']) {
            $dateReformated .= ' ' . $dateSegments['H'] . ':' . $dateSegments['M'];
        }

        return strtotime($dateReformated);
    }
}
