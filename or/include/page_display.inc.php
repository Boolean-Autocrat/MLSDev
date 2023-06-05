<?php

class page_display
{
    public function display()
    {
        global $conn, $config, $lang, $misc, $meta_canonical;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        // Make Sure we passed the PageID
        $display = '';
        if (!isset($_GET['PageID'])) {
            $display .= 'ERROR. PageID not sent';
        }
        $page_id = intval($_GET['PageID']);
        $display .= '<div class="page_display">';
        $sql = 'SELECT pagesmain_full,pagesmain_id,pagesmain_published 
				FROM ' . $config['table_prefix'] . 'pagesmain 
				WHERE pagesmain_id=' . $page_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $full = $recordSet->fields('pagesmain_full');

        //Deal with Code Tags
        preg_match_all('/<code>(.*?)<\/code>/is', $full, $code_tags);
        if (isset($code_tags[1])) {
            foreach ($code_tags[1] as $x => $tag) {
                $new_tag = str_replace('{', '&#123;', $tag);
                $new_tag = str_replace('}', '&#125;', $new_tag);
                $new_tag = str_replace('>', '&gt;', $new_tag);
                $new_tag = str_replace('<', '&lt;', $new_tag);
                $code_tags[1][$x] = $new_tag;
            }
            foreach ($code_tags[0] as $x => $tag) {
                $full = str_replace($tag, '<code>'.$code_tags[1][$x].'</code>', $full);
            }
        }
        $id = $recordSet->fields('pagesmain_id');
        $status = $recordSet->fields('pagesmain_published');
        if ($status !=1) {
            return $lang['listing_editor_permission_denied'];
        }

        $display .= $full;
        // Allow Admin To Edit #
        if (((isset($_SESSION['editpages']) && $_SESSION['editpages'] == 'yes') || (isset($_SESSION['editpages']) && $_SESSION['admin_privs'] == 'yes')) && $config['wysiwyg_show_edit'] == 1) {
            $display .= '<br />';
            $display .= "<a href=\"$config[baseurl]/admin/index.php?action=edit_page_post&amp;id=$id\">$lang[edit_html_from_site]</a>";
        }

        //Add Canonical Link
        $meta_canonical = $page->magicURIGenerator('page', $page_id, true);
        $display .= '</div>' ;
        // parse page for template varibales
        include_once $config['basepath'] . '/include/core.inc.php';
        $template = new page_user();
        $template->page = $display;
        $template->replace_tags(['templated_search_form', 'featured_listings_horizontal', 'featured_listings_vertical', 'company_name', 'link_printer_friendly']);
        $page->replace_search_field_tags();
        $display = $template->return_page();
        return $display;
    }

    public function show_page_notfound()
    {
        global $config;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $page->load_page($config['template_path'].'/not_found.html');
        header('HTTP/1.0 404 Not Found');
        return $page->return_page();
    }

    //consolidate these 3 functions?
    public function get_page_title($page_id)
    {
        global $conn, $config, $misc;

        $page_id = intval($page_id);
        $sql = 'SELECT pagesmain_title 
				FROM ' . $config['table_prefix'] . 'pagesmain 
				WHERE pagesmain_id=' . $page_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $title = $recordSet->fields('pagesmain_title');
        return $title;
    }

    public function get_page_description($page_id)
    {
        global $conn, $config, $misc;

        if (isset($_GET['PageID'])) {
            $page_id = intval($page_id);
            $sql = 'SELECT pagesmain_description 
					FROM ' . $config['table_prefix'] . 'pagesmain
					WHERE pagesmain_id=' . $page_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $description = $recordSet->fields('pagesmain_description');
            return $description;
        } else {
            return '';
        }
    }

    public function get_page_keywords($page_id)
    {
        global $conn, $config, $misc;

        if (isset($_GET['PageID'])) {
            $page_id = intval($page_id);
            $sql = 'SELECT pagesmain_keywords 
					FROM ' . $config['table_prefix'] . 'pagesmain 
					WHERE pagesmain_id=' . $page_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $keywords = $recordSet->fields('pagesmain_keywords');
            return $keywords;
        } else {
            return '';
        }
    }
} //End page_display Class
