<?php

class multilingual
{
    // Multilingual Add-on
    public function selector_language_template()
    {
        global $config, $lang;
        $display = '';
        $display .= '<form id="selector_language_template" action="" method="post">';
        $display .= '<fieldset>';
        $display .= '<select id="selected_language_template" name="selected_language_template" onchange="this.form.submit();">';
        $template_directory_path = $config['basepath'] . '/template';
        $open_template_directory = opendir($template_directory_path) or die('fail to open');
        while (false !== ($langdir = readdir($open_template_directory))) {
            if ($langdir != '.' && $langdir != '..' && $langdir != '.svn') {
                $language_file = $config['basepath'] . '/addons/multilingual/language/' . $langdir . '/lang.inc.php';
                if (is_dir("$template_directory_path/$langdir") && file_exists($language_file)) {
                    $display .= '<option value="' . $langdir . '"';
                    if ($_SESSION['language_template'] == $langdir) {
                        $display .= ' selected="selected"';
                    }
                    $display .= '>' . $lang[$langdir] . '</option>';
                }
            }
        }
        $display .= '</select>';
        $display .= '</fieldset>';
        $display .= '</form>';
        closedir($open_template_directory);
        return $display;
    }

    public $lang_names = [];
    public function multilingual_select()
    {
        global $config, $lang;
        $guidestring = '';
        foreach ($_GET as $k => $v) {
            if ($k != 'PHPSESSID' && $v) {
                if (is_array($v)) {
                    foreach ($v as $vitem) {
                        $guidestring .= urlencode("$k") . '[]=' . urlencode("$vitem") . '&amp;';
                    }
                } else {
                    $guidestring .= urlencode("$k") . '=' . urlencode("$v") . '&amp;';
                }
            }
        }
        $display = '';
        $display .= '<form class="multilingual_form" id="lang_select" method="post" action="index.php?' . $guidestring . '"><div style="display:inline;">';
        foreach ($_POST as $k => $v) {
            if ($k != 'user_name' && $k != 'user_pass' && $k != 'ta' && $k != 'title') {
                if (is_array($v)) {
                    foreach ($v as $vitem) {
                        $display .= '<input type="hidden" name="' . $k . '[]" value="' . $vitem . '" />';
                    }
                } else {
                    $display .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
                }
            }
        }
        $display .= $lang['language'];
        $display .= '<select class="multilingual_select" name="select_users_lang" onchange="document.getElementById(\'lang_select\').submit()">';
        // Get List of active languages
        $configured_langs = explode(',', $config['configured_langs']);
        $selected_lang = '';
        if (!isset($_SESSION['users_lang'])) {
            $selected_lang = $config['lang'];
        } else {
            $selected_lang = $_SESSION['users_lang'];
        }
        foreach ($configured_langs as $langs) {
            if ($langs == $selected_lang) {
                $display .= '<option value="' . $langs . '" selected="selected">' . $lang['multilingual_' . $langs] . '</option>';
            } else {
                $display .= '<option value="' . $langs . '">' . $lang['multilingual_' . $langs] . '</option>';
            }
        }
        $display .= '</select>';
        $display .= '<input type="hidden" name="lang_change" value="yes" />';
        $display .= '</div></form>';
        return $display;
    }
    public function setup_additional_language($language)
    {
        // echo 'Setup '.$language;
        global $config, $conn;
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . $language . '_listingsdb (
				listingsdb_id INT4 NOT NULL AUTO_INCREMENT,
				listingsdb_title CHAR VARYING(80) NOT NULL,
				listingsdb_notes TEXT NOT NULL,
				PRIMARY KEY(listingsdb_id)
				);';
        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . $language . '_listingsdbelements (
				listingsdbelements_id INT4 NOT NULL AUTO_INCREMENT,
				listingsdbelements_field_value TEXT NOT NULL,
				PRIMARY KEY(listingsdbelements_id)
				);';

        $sql_insert[] = 'CREATE TABLE ' . $config['table_prefix_no_lang'] . $language . '_listingsformelements (
				listingsformelements_id INT4 NOT NULL AUTO_INCREMENT,
				listingsformelements_field_caption CHAR VARYING(80) NOT NULL,
				listingsformelements_default_text TEXT NOT NULL,
				listingsformelements_field_elements TEXT NOT NULL,
				listingsformelements_search_label CHAR VARYING(50) NULL,
				PRIMARY KEY(listingsformelements_id)
				);';
        foreach ($sql_insert as $elementIndexValue => $elementContents) {
            $recordSet = $conn->Execute($elementContents);
            if ($recordSet === false) {
                die("<strong><span style=\"red\">ERROR - $elementContents</span></strong>");
            }
        }
    }
    public function remove_additional_language($language)
    {
        // echo 'Remove '.$language;
        global $config, $conn;
        $sql_insert[] = 'DROP TABLE ' . $config['table_prefix_no_lang'] . $language . '_listingsdb';
        $sql_insert[] = 'DROP TABLE ' . $config['table_prefix_no_lang'] . $language . '_listingsdbelements';
        $sql_insert[] = 'DROP TABLE ' . $config['table_prefix_no_lang'] . $language . '_listingsformelements';
        foreach ($sql_insert as $elementIndexValue => $elementContents) {
            $recordSet = $conn->Execute($elementContents);
            if ($recordSet === false) {
                die("<strong><span style=\"red\">ERROR - $elementContents</span></strong>");
            }
        }
    }
}
