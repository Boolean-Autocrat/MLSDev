<?php

// just so we know it is broken
class version extends installer
{
    public function load_prev_settings()
    {
        // Open File
        $old_file = file_get_contents(dirname(__FILE__) . '/../../include/common.php');
        // Get values
        preg_match('/\$db_type = "(.*?)"/', $old_file, $old_db_type);
        preg_match('/\$db_server = "(.*?)"/', $old_file, $old_db_server);
        preg_match('/\$db_database = "(.*?)"/', $old_file, $old_db_name);
        preg_match('/\$db_user = "(.*?)"/', $old_file, $old_db_user);
        preg_match('/\$db_password = "(.*?)"/', $old_file, $old_db_password);
        preg_match('/\$config\["table_prefix_no_lang"\] = "(.*?)"/', $old_file, $old_table_prefix);
        $this->get_new_settings($old_db_type[1], $old_db_server[1], $old_db_name[1], $old_db_user[1], $old_db_password[1], $old_table_prefix[1]);
    }
    public function create_tables()
    {
    }
    public function update_tables()
    {
        // this is the setup for the ADODB library
        $this->set_version();
        include dirname(__FILE__) . '/../../vendor/adodb/adodb-php/adodb.inc.php';

        $conn = ADONewConnection($_SESSION['db_type']);
        $conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, 'utf8');
        $conn->connect($_SESSION['db_server'], $_SESSION['db_user'], $_SESSION['db_password'], $_SESSION['db_database']);

        $config['table_prefix'] = $_SESSION['table_prefix'] . $_SESSION['or_install_lang'] . '_';
        $config['table_prefix_no_lang'] = $_SESSION['table_prefix'];
        $sql_insert[] = 'UPDATE  ' . $config['table_prefix_no_lang'] . "controlpanel 
						SET  controlpanel_basepath ='" . trim($_SESSION['basepath']) . "', 
							controlpanel_baseurl = '" . trim($_SESSION['baseurl']) . "'";
        foreach ($sql_insert as $elementIndexValue => $elementContents) {
            $recordSet = $conn->Execute($elementContents);
            if (!$recordSet) {
                die("<strong><span style=\"red\">ERROR - $elementContents</span></strong><br />");
            }
        }
    }
    public function create_index()
    {
    }
    public function insert_values()
    {
    }
    public function load_version()
    {
        $this->load_lang($_SESSION['or_install_lang']);
        switch ($_GET['step']) {
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
                $this->write_config();
                break;
            case 6:
                $this->update_tables();
                $this->create_tables();
                $this->create_index();
                $this->insert_values();
                echo '<br /><strong>' . $this->lang['install_installation_complete'] . ' <a href="../admin/index.php?action=configure">' . $this->lang['install_configure_installation'] . '</a></strong>';
                break;
        }
    }
}
