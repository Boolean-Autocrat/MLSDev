<?php


class page_editor
{
    public function page_edit_index()
    {
        global $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('editpages');
        $display = '';

        if ($security === true) {
            global $conn, $misc;

            //Load the Core Template
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            include_once $config['basepath'] . '/include/page_functions.inc.php';
            $page_functions = new page_functions();
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/page_edit_index.html');


            //What Access Rights does user have to pages? Access page Manager means they are at least a contributor.
            /*//page Permissions
* 1 - Subscriber - A subscriber can read posts, comment on posts.
* 2 - Contributor - A contributor can post and manage their own post but they cannot publish the posts. An administrator
must first approve the post before it can be published.
* 3 - Author - The Author role allows someone to publish and manage posts. They can only manage their own posts, no one
else’s.
* 4 - Editor - An editor can publish posts. They can also manage and edit other users posts. If you are looking for
someone to edit your posts, you would assign the Editor role to that person.
*/
            //$page_user_type = intval($_SESSION['page_user_type']);
            $page_user_id = intval($_SESSION['userID']);

            if ($config['demo_mode'] == 1 && $_SESSION['admin_privs'] != 'yes') {
                $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
            } else {
                if (isset($_POST['delete'])) {
                    if (isset($_POST['pageID']) && $_POST['pageID'] != 0) {
                        // Delete page
                        $pageID = intval($_POST['pageID']);
                        $sql = 'DELETE FROM ' . $config['table_prefix'] . 'pagesmain WHERE pagesmain_id = ' . $pageID;
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                        $page_deleted = true;
                        $_POST['pageID'] = '';
                    }
                }
            }

            //Replace Status Counts
            //{page_edit_status_all_count}
            $sql = 'SELECT count(pagesmain_id) as pagecount FROM ' . $config['table_prefix'] . 'pagesmain';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $count_all = $recordSet->fields('pagecount');

            $sql = 'SELECT count(pagesmain_id) as pagecount FROM ' . $config['table_prefix'] . 'pagesmain WHERE pagesmain_published
= 1';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $count_published = $recordSet->fields('pagecount');

            $sql = 'SELECT count(pagesmain_id) as pagecount FROM ' . $config['table_prefix'] . 'pagesmain WHERE pagesmain_published
= 0';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $count_draft = $recordSet->fields('pagecount');

            $sql = 'SELECT count(pagesmain_id) as pagecount FROM ' . $config['table_prefix'] . 'pagesmain WHERE pagesmain_published
= 2';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $count_review = $recordSet->fields('pagecount');

            $page->replace_tag('page_edit_status_all_count', $count_all);
            $page->replace_tag('page_edit_status_published_count', $count_published);
            $page->replace_tag('page_edit_status_draft_count', $count_draft);
            $page->replace_tag('page_edit_status_review_count', $count_review);
            //Get Status
            $statusSQL = '';
            if (isset($_GET['status']) && $_GET['status'] == 'Published') {
                $statusSQL = 'pagesmain_published = 1';
            } elseif (isset($_GET['status']) && $_GET['status'] == 'Draft') {
                $statusSQL = 'pagesmain_published = 0';
            } elseif (isset($_GET['status']) && $_GET['status'] == 'Review') {
                $statusSQL = 'pagesmain_published = 2';
            }

            //Show page List
            if (!empty($statusSQL)) {
                $sql = 'SELECT pagesmain_title, pagesmain_id, pagesmain_date, pagesmain_published, pagesmain_keywords
FROM ' . $config['table_prefix'] . 'pagesmain
WHERE ' . $statusSQL;
            } else {
                $sql = 'SELECT pagesmain_title, pagesmain_id, pagesmain_date, pagesmain_published, pagesmain_keywords
FROM ' . $config['table_prefix'] . 'pagesmain';
            }

            //Load Record Set
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            //Handle Next prev
            $num_rows = $recordSet->RecordCount();
            if (!isset($_GET['cur_page'])) {
                $_GET['cur_page'] = 0;
            }

            $limit_str = $_GET['cur_page'] * $config['listings_per_page'];
            $recordSet = $conn->SelectLimit($sql, $config['listings_per_page'], $limit_str);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $page_edit_template = '';

            while (!$recordSet->EOF) {
                $page_edit_template .= $page->get_template_section('page_edit_item_block');
                //echo $page_edit_template;
                $title = $recordSet->fields('pagesmain_title');
                $pagesmain_id = $recordSet->fields('pagesmain_id');
                $keywords = $recordSet->fields('pagesmain_keywords');
                $page_date = $recordSet->fields('pagesmain_date');
                $page_published = $recordSet->fields('pagesmain_published');
                $page_date = $misc->convert_timestamp($page_date, true);
                //Get Author
                include_once $config['basepath'] . '/include/user.inc.php';
                $user = new user();

                $page_edit_template = $page->parse_template_section($page_edit_template, 'page_edit_item_title', $title);
                $page_edit_template = $page->parse_template_section($page_edit_template, 'page_edit_item_id', $pagesmain_id);
                $page_edit_template = $page->parse_template_section($page_edit_template, 'page_edit_item_date', $page_date);
                $page_edit_template = $page->parse_template_section($page_edit_template, 'page_edit_item_keywords', $keywords);

                switch ($page_published) {
                    case 0:
                        $page_edit_template = $page->parse_template_section(
                            $page_edit_template,
                            'page_edit_item_published',
                            $lang['page_draft']
                        );
                        break;
                    case 1:
                        $page_edit_template = $page->parse_template_section(
                            $page_edit_template,
                            'page_edit_item_published',
                            $lang['page_published']
                        );

                        break;
                    case 2:
                        $page_edit_template = $page->parse_template_section(
                            $page_edit_template,
                            'page_edit_item_published',
                            $lang['page_review']
                        );
                        break;
                }
                $recordSet->MoveNext();
            }
            //Next Prev
            $next_prev = $misc->next_prev($num_rows, $_GET['cur_page'], '', 'page', true);
            $page->replace_tag('next_prev', $next_prev);
            $page->replace_template_section('page_edit_item_block', $page_edit_template);
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        } else {
            $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }

        return $display;
    }

    /**
     * **************************************************************************\
     * function page_edit() - Display's the page editor *
     * \**************************************************************************
     */
    public function page_edit()
    {
        global $config, $lang, $conn, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('editpages');
        $display = '';
        $page_saved = false;
        $page_deleted = false;

        if ($security === true) {
            //Load the Core Template
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            include_once $config['basepath'] . '/include/page_functions.inc.php';
            $page_functions = new page_functions();
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/page_edit_post.html');
            //$page_user_type = intval($_SESSION['page_user_type']);
            $page_user_id = intval($_SESSION['userID']);
            $html = '';
            if (isset($_GET['id'])) {
                // Save pageID to Session for Image Upload Plugin
                $_SESSION['pageID'] = intval($_GET['id']);
                $pageID = intval($_GET['id']);

                //Make Sure Image Folder Exists For this page
                if (!file_exists($config['basepath'] . '/images/page_upload/' . $pageID)) {
                    mkdir($config['basepath'] . '/images/page_upload/' . $pageID);
                }
                $_SESSION['filemanager_basepath'] = $config['basepath'];
                $_SESSION['filemanager_baseurl'] = $config['baseurl'];
                $_SESSION['filemanager_pathpart'] = '/images/page_upload/' . $pageID . '/';

                $page->replace_tag('page_id', $pageID);
                $sql = 'SELECT pagesmain_published FROM ' . $config['table_prefix'] . 'pagesmain WHERE pagesmain_id = ' . $pageID;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                //Deal with Template Tag
                //$html = str_replace('{template_url}',$config['template_url'],$html);
                $published = intval($recordSet->fields('pagesmain_published'));

                //No Page is Dirty on load.
                $page->replace_tag('page_revert_button_state', 'display:none;');
                $page->replace_tag('page_published_lang', $lang['page_draft']);
                //Show page Categories.
                //Load JS to Handle Cat Changes.
                //$this->page_edit_category_change($pageID);
                $article_url = $page_functions->get_page_url($pageID);
                $page->replace_tag('page_article_url', $article_url);
            }
            $status_html = $page->get_template_section('page_status_option_block');
            $status_html_replace = '';
            //Build Draft Option
            $status_html_replace .= $status_html;
            if ($published == 0) {
                $status_html_replace = $page->parse_template_section(
                    $status_html_replace,
                    'page_status_selected',
                    'selected="selected"'
                );
            } else {
                $status_html_replace = $page->parse_template_section($status_html_replace, 'page_status_selected', '');
            }
            $status_html_replace = $page->parse_template_section($status_html_replace, 'page_status_value', '0');
            $status_html_replace = $page->parse_template_section($status_html_replace, 'page_status_text', $lang['page_draft']);
            //Build Review Option
            $status_html_replace .= $status_html;
            if ($published == 2) {
                $status_html_replace = $page->parse_template_section(
                    $status_html_replace,
                    'page_status_selected',
                    'selected="selected"'
                );
            } else {
                $status_html_replace = $page->parse_template_section($status_html_replace, 'page_status_selected', '');
            }
            $status_html_replace = $page->parse_template_section($status_html_replace, 'page_status_value', '2');
            $status_html_replace = $page->parse_template_section($status_html_replace, 'page_status_text', $lang['page_review']);
            //Build Published Option
            $status_html_replace .= $status_html;
            if ($published == 1) {
                $status_html_replace = $page->parse_template_section(
                    $status_html_replace,
                    'page_status_selected',
                    'selected="selected"'
                );
            } else {
                $status_html_replace = $page->parse_template_section($status_html_replace, 'page_status_selected', '');
            }
            $status_html_replace = $page->parse_template_section($status_html_replace, 'page_status_value', '1');
            $status_html_replace = $page->parse_template_section($status_html_replace, 'page_status_text', $lang['page_published']);
            $page->replace_template_section('page_status_option_block', $status_html_replace);
            //Show Link to page Manager
            $page->replace_tag('page_manager_url', 'index.php?action=edit_page');
            $page->replace_tag('page_edit_action', 'index.php?action=edit_page_post');

            if ($config['demo_mode'] == 1 && $_SESSION['admin_privs'] != 'yes') {
                $page->page = $page->remove_template_block('page_update', $page->page);
                $page->page = $page->remove_template_block('page_delete', $page->page);
            } else {
                $page->page = $page->cleanup_template_block('page_update', $page->page);
                $page->page = $page->cleanup_template_block('page_delete', $page->page);
            }
            $page->replace_permission_tags();

            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        } else {
            $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return $display;
    }
}
