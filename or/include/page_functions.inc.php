<?php


class page_functions
{
    public function ajax_get_pages()
    {
        global $conn, $config, $misc;

        $sql = 'SELECT pagesmain_id, pagesmain_title 
				FROM ' . $config['table_prefix'] . 'pagesmain';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $pages = [];
        while (!$recordSet->EOF) {
            $pages[$recordSet->fields('pagesmain_id')] = $recordSet->fields('pagesmain_title');
            $recordSet->Movenext();
        }
        return json_encode(['error' => false, 'pages' => $pages]);
    }

    public function ajax_update_page_post_autosave()
    {
        global $conn, $lang, $config;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $display = '';
        $security = $login->verify_priv('editpages');
        if ($security === true) {
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // Do we need to save?
            if (isset($_POST['ta']) && isset($_POST['pageID'])) {
                if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
                }
                // Save page now
                $pageID = intval($_POST['pageID']);
                $save_full = $_POST['ta'];
                $save_full = $conn->qstr($save_full);

                $sql = 'UPDATE ' . $config['table_prefix'] . "pagesmain 
						SET pagesmain_full_autosave = " . $save_full . " 
						WHERE pagesmain_id = " . $pageID . '';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                } else {
                    header('Content-type: application/json');
                    return json_encode(['error' => '0', 'page_id' => $pageID]);
                }
            }
        }
    }

    public function ajax_get_page_post()
    {
        global $conn, $lang, $config, $api;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $display = '';
        $security = $login->verify_priv('editpages');
        if ($security === true) {
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // Do we need to save?
            if (isset($_POST['pageID'])) {
                // Save page now
                $pageID = intval($_POST['pageID']);

                $sql = 'SELECT * FROM ' . $config['table_prefix'] . "pagesmain 
						WHERE pagesmain_id = $pageID";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                if ($recordSet->RecordCount() == 0) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => 'Page Not Found']);
                }
                $id  = $recordSet->fields('pagesmain_id');
                $title = $recordSet->fields('pagesmain_title');
                $date = $recordSet->fields('pagesmain_date');
                $full = $recordSet->fields('pagesmain_full');
                $published = $recordSet->fields('pagesmain_published');
                $description = $recordSet->fields('pagesmain_description');
                $keywords = $recordSet->fields('pagesmain_keywords');
                $full_autosave = $recordSet->fields('pagesmain_full_autosave');
                $seotitle = $recordSet->fields('page_seotitle');
                header('Content-type: application/json');

                $full = str_replace('{template_url}', $config['template_url'], $full);
                $full = str_replace('{baseurl}', $config['baseurl'], $full);

                return json_encode([
                    'error' => '0',
                    'id' => $id,
                    'title' => $title,
                    'date' => $date,
                    'full' => $full,
                    'published' => $published,
                    'description' => $description,
                    'keywords' => $keywords,
                    'full_autosave' => $full_autosave,
                    'seotitle' => $seotitle,
                ]);
            }
        }
    }

    public function ajax_update_page_post()
    {
        global $conn, $lang, $config, $api;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $display = '';
        $security = $login->verify_priv('editpages');
        if ($security === true) {
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // Do we need to save?
            if (isset($_POST['ta']) && isset($_POST['pageID']) && isset($_POST['description']) && isset($_POST['title']) && isset($_POST['keywords'])) {
                if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
                }
                // Save page now
                $pageID = intval($_POST['pageID']);
                $save_full_xhtml = $_POST['ta'];
                //Replace Paths with template tags
                $save_full_xhtml = str_replace($config['template_url'], '{template_url}', $save_full_xhtml);
                $save_full_xhtml = str_replace($config['baseurl'], '{baseurl}', $save_full_xhtml);
                $save_full_xhtml = $conn->qstr($save_full_xhtml);
                $save_description = $misc->make_db_safe($_POST['description']);
                $title = trim($_POST['title']);
                if ($title == '') {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $lang['page_title_not_blank']]);
                }
                $save_title = $misc->make_db_safe($title);
                //Make sure Blog Title is unique.
                $sql = 'SELECT pagesmain_id FROM ' . $config['table_prefix'] . "pagesmain 
						WHERE STRCMP(pagesmain_title,$save_title) = 0 
						AND pagesmain_id <> $pageID";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                if ($recordSet->RecordCount() > 0) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $lang['page_title_not_unique']]);
                }
                $save_keywords = $misc->make_db_safe($_POST['keywords']);

                $seotitle = trim($_POST['seotitle']);
                if ($seotitle == '') {
                    $seotitle = $page->create_seouri($_POST['title'], false);
                }
                $sql_seotitle = $misc->make_db_safe($seotitle);
                //Verify the SEO title is unique
                $sql = 'SELECT pagesmain_id FROM ' . $config['table_prefix'] . 'pagesmain 
						WHERE page_seotitle = ' . $sql_seotitle . ' 
						AND pagesmain_id <> ' . $pageID;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                if ($recordSet->RecordCount() > 0) {
                    $seotitle =  $page->create_seouri($seotitle . '-' . $pageID, false);
                    $sql_seotitle =  $misc->make_db_safe($seotitle);
                }
                if (isset($_POST['status'])) {
                    $save_status = intval($_POST['status']);
                } else {
                    $save_status = null;
                }
                $current_status = $this->get_page_status($pageID);

                //See if this is a new publish.
                //Send Twitter
                if ($save_status == 1 && $current_status != 1) {
                    if ($config['twitter_new_page'] == 1) {
                        include_once $config['basepath'] . '/include/social.inc.php';
                        $social = new social();
                        $twitter_url = ' ' . $config['baseurl'] . '/b/' . $pageID;
                        $twitter_title = $_POST['title'];
                        if (strlen($twitter_url) + strlen($twitter_title) > 140) {
                            $twitter_title = substr($twitter_title, 0, 137 - strlen($twitter_url)) . '...';
                        }
                        $twitter_post = $twitter_title . $twitter_url;
                        $api->load_local_api('twitter__post', ['message' => $twitter_post]);
                    }
                }

                if ($save_status !== null) {
                    $status_sql = ', pagesmain_published = ' . $save_status;
                }
                $sql = 'UPDATE ' . $config['table_prefix'] . 'pagesmain 
						SET page_seotitle = ' . $sql_seotitle . ", pagesmain_full = " . $save_full_xhtml . ", pagesmain_title = " . $save_title . ', pagesmain_description = ' . $save_description . ', pagesmain_keywords = ' . $save_keywords . $status_sql . ' 
						WHERE pagesmain_id = ' . $pageID . '';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                } else {
                    header('Content-type: application/json');
                    return json_encode(['error' => '0', 'page_id' => $pageID, 'seotitle' => $seotitle]);
                }
            }
        }
    }

    public function ajax_delete_page_post()
    {
        global $conn, $lang, $config;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $display = '';
        $security = $login->verify_priv('editpages');
        //$page_user_type = intval($_SESSION['page_user_type']);
        if ($security === true) {
            global $misc;
            // Do we need to save?
            if (isset($_POST['pageID'])) {
                // Save page now
                $pageID = intval($_POST['pageID']);
                //Don't allow deletion of Page 1 as it is the index page
                if ($pageID == 1) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $lang['page_1_can_not_delete']]);
                }
                $sql = 'DELETE FROM ' . $config['table_prefix'] . 'pagesmain  
						WHERE pagesmain_id = ' . $pageID . '';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                } else {
                    header('Content-type: application/json');
                    return json_encode(['error' => '0', 'page_id' => $pageID]);
                }
            }
        }
    }

    public function ajax_create_page_post()
    {
        global $conn, $lang, $config;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('editpages');
        $display = '';
        $page_saved = false;
        if ($security === true) {
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // Do we need to save?
            if (isset($_POST['title'])) {
                if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
                }
                // Save page now
                $title = trim($_POST['title']);
                $save_title = $misc->make_db_safe($title);
                //Make sure page Title is unique.
                $sql = 'SELECT pagesmain_id FROM ' . $config['table_prefix'] . "pagesmain 
						WHERE STRCMP(pagesmain_title,$save_title) = 0";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                if ($recordSet->RecordCount() > 0) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => "$lang[page_title_not_unique]"]);
                }
                $userdb_id = $misc->make_db_safe($_SESSION['userID']);
                //Generate seo URL
                $seotitle = $page->create_seouri($title, false);
                $sql_seotitle = $misc->make_db_safe($seotitle);

                $sql = 'INSERT INTO ' . $config['table_prefix'] . "pagesmain
				(pagesmain_full,pagesmain_title,pagesmain_date,pagesmain_published,pagesmain_description,pagesmain_keywords,page_seotitle)
				VALUES ('',$save_title," . strtotime('now') . ",0,'',''," . $sql_seotitle . ')';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $page_id = $conn->Insert_ID();
                //Verify the SEO title is unique
                $sql = 'SELECT pagesmain_id FROM ' . $config['table_prefix'] . 'pagesmain 
						WHERE page_seotitle = ' . $sql_seotitle . ' 
						AND pagesmain_id <> ' . $page_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                if ($recordSet->RecordCount() > 0) {
                    $seotitle =  $page->create_seouri($title . '-' . $page_id, false);
                    $sql_seotitle =  $misc->make_db_safe($seotitle);
                    $sql = 'UPDATE ' . $config['table_prefix'] . "pagesmain 
							SET page_seotitle = $sql_seotitle 
							WHERE pagesmain_id = " . $page_id;
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'id' => $page_id, 'title' => "$title"]);
            }
        }
    }

    public function get_recent_page_posts($count = 5)
    {
        global $conn, $config, $misc;

        $sql = 'SELECT pagesmain_id, pagesmain_title 
				FROM ' . $config['table_prefix'] . "pagesmain 
				WHERE pagesmain_published = '1' 
				ORDER BY pagesmain_date DESC LIMIT " . intval($count);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $posts = [];
        while (!$recordSet->EOF) {
            $posts[$recordSet->fields('pagesmain_id')] = $recordSet->fields('pagesmain_title');
            $recordSet->MoveNext();
        }
        return $posts;
    }

    public function create_page_seoname($title)
    {
        global $config;
        $slug = strtolower($title);
        $slug = trim($slug);
        $slug = preg_replace('/[^a-zA-Z0-9 ]/', '', $slug);
        $slug = str_replace(' ', $config['seo_url_seperator'], $slug);
        return $slug;
    }

    public function get_page_url($page_id)
    {
        global $conn, $config;
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $page_id = intval($page_id);
        //Get Title
        $article_url = $page->magicURIGenerator('page', $page_id, true);
        return $article_url;
    }

    // TODO consolidate all these get_page_XXX functions below

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

    public function get_page_seotitle($page_id)
    {
        global $conn, $config, $misc;

        $page_id = intval($page_id);
        $sql = 'SELECT page_seotitle 
				FROM ' . $config['table_prefix'] . 'pagesmain 
				WHERE pagesmain_id=' . $page_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $title = $recordSet->fields('page_seotitle');
        return $title;
    }

    public function get_page_status($page_id)
    {
        global $conn, $config, $misc;

        $page_id = intval($page_id);
        $page_id = $misc->make_db_safe($page_id);
        $sql = 'SELECT pagesmain_published 
				FROM ' . $config['table_prefix'] . 'pagesmain 
				WHERE pagesmain_id=' . $page_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $status = $recordSet->fields('pagesmain_published');
        return $status;
    }

    public function get_page_date($page_id)
    {
        global $conn, $config, $misc;

        $page_id = intval($page_id);
        $sql = 'SELECT pagesmain_date 
				FROM ' . $config['table_prefix'] . 'pagesmain 
				WHERE pagesmain_id=' . $page_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $date = $recordSet->fields('pagesmain_date');
        $date = $misc->convert_timestamp($date, true);
        return $date;
    }

    public function get_page_description($page_id)
    {
        global $conn, $config, $misc;

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
    }

    public function get_page_keywords($page_id)
    {
        global $conn, $config, $misc;

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
    }
}
