<?php


class blog_functions
{
    public function ajax_get_blogs()
    {
        global $conn, $config, $misc;
        $sql = 'SELECT blogmain_id, blogmain_title 
				FROM ' . $config['table_prefix'] . 'blogmain';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $blogs = [];
        while (!$recordSet->EOF) {
            $blogs[$recordSet->fields('blogmain_id')] = $recordSet->fields('blogmain_title');
            $recordSet->Movenext();
        }
        return json_encode(['error' => false, 'blogs' => $blogs]);
    }

    public function ajax_get_blog_post()
    {
        global $lang, $config;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $display = '';
        $security = $login->verify_priv('can_access_blog_manager');

        if ($security === true) {
            global $conn, $api, $misc;

            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // Do we need to save?
            if (isset($_POST['blogID'])) {
                // Save page now
                $pageID = intval($_POST['blogID']);

                $sql = 'SELECT * FROM ' . $config['table_prefix'] . "blogmain 
						WHERE blogmain_id = $pageID";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                if ($recordSet->RecordCount() == 0) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => 'Page Not Found']);
                }
                $id  = $recordSet->fields('blogmain_id');
                $title = $recordSet->fields('blogmain_title');
                $date = $recordSet->fields('blogmain_date');
                $summary = $recordSet->fields('blogmain_summary');
                $full = $recordSet->fields('blogmain_full');
                $published = $recordSet->fields('blogmain_published');
                $description = $recordSet->fields('blogmain_description');
                $keywords = $recordSet->fields('blogmain_keywords');
                $full_autosave = $recordSet->fields('blogmain_full_autosave');
                $seotitle = $recordSet->fields('blog_seotitle');
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
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['blog_permission_denied']]);
        }
    }

    public function ajax_update_blog_post_autosave()
    {
        global $conn, $lang, $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);
        $blog_user_id = intval($_SESSION['userID']);

        if ($security === true) {
            global $conn, $misc;

            // Do we need to save?
            if (isset($_POST['ta']) && isset($_POST['blogID'])) {
                // Save blog now
                $blogID = intval($_POST['blogID']);
                //Verify Blog Owner
                $sql = 'SELECT userdb_id 
						FROM ' . $config['table_prefix'] . 'blogmain 
						WHERE blogmain_id = ' . $blogID;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $blog_owner = intval($recordSet->fields('userdb_id'));
                //Make sure user is and editor or the blog owner.
                if ($blog_owner != $blog_user_id && $blog_user_type != 4) {
                    //Throw Error
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $lang['blog_permission_denied']]);
                }

                $save_full = $_POST['ta'];
                $save_full = $conn->addQ($save_full);
                
                $sql = 'UPDATE ' . $config['table_prefix'] . "blogmain 
						SET blogmain_full_autosave = '$save_full_xhtml' 
						WHERE blogmain_id = " . $blogID . '';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                } else {
                    header('Content-type: application/json');
                    return json_encode(['error' => '0', 'blog_id' => $blogID]);
                }
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['blog_permission_denied']]);
        }
    }

    public function ajax_update_blog_post()
    {
        global $lang, $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $display = '';
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);
        $blog_user_id = intval($_SESSION['userID']);

        if ($security === true) {
            global $conn, $misc, $api;

            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // Do we need to save?
            if (isset($_POST['ta']) && isset($_POST['blogID']) && isset($_POST['description']) && isset($_POST['title']) && isset($_POST['keywords']) && isset($_POST['seotitle'])) {
                // Save blog now
                $blogID = intval($_POST['blogID']);
                //Verify Blog Owner
                $sql = 'SELECT userdb_id FROM ' . $config['table_prefix'] . 'blogmain WHERE blogmain_id = ' . $blogID;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $blog_owner = intval($recordSet->fields('userdb_id'));
                //Make sure user is and editor or the blog owner.
                if ($blog_owner != $blog_user_id && $blog_user_type != 4) {
                    //Throw Error
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $lang['blog_permission_denied']]);
                }

                $save_full = $_POST['ta'];
                //Replace Paths with template tags
                $save_full_xhtml = str_replace($config['template_url'], '{template_url}', $save_full);
                $save_full_xhtml = str_replace($config['baseurl'], '{baseurl}', $save_full_xhtml);
                $save_full_xhtml = $conn->addQ($save_full_xhtml);
                $save_description = $misc->make_db_safe($_POST['description']);
                $title = trim($_POST['title']);
                if ($title == '') {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $lang['blog_title_not_blank']]);
                }
                $save_title = $misc->make_db_safe($title);
                //Make sure Blog Title is unique.
                $sql = 'SELECT blogmain_id 
						FROM ' . $config['table_prefix'] . "blogmain 
						WHERE STRCMP(blogmain_title,$save_title) = 0 
						AND blogmain_id <> $blogID";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                if ($recordSet->RecordCount() > 0) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => "$lang[blog_title_not_unique]"]);
                }

                $save_keywords = $misc->make_db_safe($_POST['keywords']);
                $seotitle = trim($_POST['seotitle']);
                if ($seotitle == '') {
                    $seotitle = $page->create_seouri($_POST['title'], false);
                }
                $sql_seotitle = $misc->make_db_safe($seotitle);
                //Verify the SEO title is unique
                $sql = 'SELECT blogmain_id 
						FROM ' . $config['table_prefix'] . 'blogmain 
						WHERE blog_seotitle = ' . $sql_seotitle . ' 
						AND blogmain_id <> ' . $blogID;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                if ($recordSet->RecordCount() > 0) {
                    $seotitle =  $page->create_seouri($seotitle . '-' . $blogID, false);
                    $sql_seotitle =  $misc->make_db_safe($seotitle);
                }
                if (isset($_POST['status'])) {
                    $save_status = intval($_POST['status']);
                } else {
                    $save_status = null;
                }
                $current_status = $this->get_blog_status($blogID);
                // Check to see if user can publish a blog
                if ($blog_user_type == 2 && $save_status == 1) {
                    //Throw Error
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $lang['blog_permission_denied']]);
                }

                if ($save_status !== null) {
                    $status_sql = ', blogmain_published = ' . $save_status;
                }
                $sql = 'UPDATE ' . $config['table_prefix'] . 'blogmain 
						SET blog_seotitle = ' . $sql_seotitle . ", blogmain_full_autosave = '', blogmain_full = '" . $save_full_xhtml . "', blogmain_title = " . $save_title . ', blogmain_description = ' . $save_description . ', blogmain_keywords = ' . $save_keywords . $status_sql . ' 
						WHERE blogmain_id = ' . $blogID . '';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                } else {
                    //See if this is a new publish.
                    //Send Twitter and PingBacks
                    if ($save_status == 1 && $current_status != 1) {
                        if ($config['twitter_new_blog'] == 1) {
                            include_once $config['basepath'] . '/include/social.inc.php';
                            $social = new social();
                            $twitter_url = ' ' . $config['baseurl'] . '/b/' . $blogID;
                            $twitter_title = $_POST['title'];
                            if (strlen($twitter_url) + strlen($twitter_title) > 140) {
                                $twitter_title = substr($twitter_title, 0, 137 - strlen($twitter_url)) . '...';
                            }
                            $twitter_post = $twitter_title . $twitter_url;
                            $api->load_local_api('twitter__post', ['message' => $twitter_post]);
                        }
                        //Pingbacks
                        $article_url = $page->magicURIGenerator('blog', $blogID, true);
                        $pingback_service_status = '';
                        if ($config['send_service_pingbacks'] == 1) {
                            $pingback_service_status .= $this->pingback_services($article_url);
                        }
                        //Do Additional Pingback based on article
                        $pingback_url_status = '';
                        if ($config['send_url_pingbacks'] == 1) {
                            $pingback_url_status .= $this->pingback_urls($article_url, $save_full);
                        } else {
                            $pingback_url_status = 'Skipping based config';
                        }
                    } else {
                        $pingback_url_status = 'Not Run';
                    }
                    header('Content-type: application/json');
                    return json_encode(['error' => '0', 'blog_id' => $blogID, 'seotitle' => $seotitle, 'pingback_url_status' => $pingback_url_status]);
                }
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['blog_permission_denied']]);
        }
    }

    public function ajax_delete_blog_post()
    {
        global $config;

        //TODO: Fix Security Check
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            global $conn, $misc, $api;

            // Do we need to save?
            if (isset($_POST['blogID'])) {
                // Save blog now
                $blogID = intval($_POST['blogID']);

                // Check to see if user can delete this post
                /*
                if ($blog_user_type==2 && $save_status == 1){
                //Throw Error
                header('Content-type: application/json');
                return json_encode(array('error' => "1",'error_msg' => $lang['blog_permission_denied']));
                }
                */
                $sql = 'DELETE FROM ' . $config['table_prefix'] . 'blogmain  WHERE blogmain_id = ' . $blogID . '';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . 'blogtag_relation  WHERE blogmain_id = ' . $blogID . '';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . 'blogcategory_relation  WHERE blogmain_id = ' . $blogID . '';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }

                $dir = $config['basepath'] . '/images/blog_uploads/' . $blogID;
                if ($dir == $config['basepath']) {
                    return json_encode(['error' => '1', 'blog_id' => $blogID . 'No folder present']);
                } else {
                    if (!$misc->recurseRmdir($dir)) {
                        return json_encode(['error' => '1', 'blog_id' => $blogID . 'Unable to delete media folder']);
                    }
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'blog_id' => $blogID]);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['blog_permission_denied']]);
        }
    }

    public function ajax_add_assigned_blog_tag_byid()
    {
        global $lang, $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            global $conn, $misc;
            // Do we need to save?
            if (isset($_POST['tag_id'])) {
                $tag_id = intval($_POST['tag_id']);
                $blogID = intval($_SESSION['blogID']);
                //Make Sure Tag is not already assigned
                $sql = 'SELECT relation_id 
						FROM ' . $config['table_prefix_no_lang'] . 'blogtag_relation 
						WHERE tag_id = ' . $tag_id . ' 
						AND blogmain_id = ' . $blogID;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                //Assign Tag
                if ($recordSet->RecordCount() > 0) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $lang['blog_tag_already_assigned']]);
                }
                $sql = 'INSERT INTO ' . $config['table_prefix_no_lang'] . "blogtag_relation
						(tag_id,blogmain_id)
						VALUES ($tag_id,$blogID)";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                //Get Tag Name for json return
                $sql = 'SELECT tag_name FROM ' . $config['table_prefix'] . 'blogtags WHERE tag_id = ' . $tag_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $tag_name = $recordSet->fields('tag_name');
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'tag_id' => $tag_id, 'tag_name' => $tag_name]);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['blog_permission_denied']]);
        }
    }

    public function ajax_remove_assigned_blog_tag()
    {
        global $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            global $conn, $misc;

            // Do we need to save?
            if (isset($_POST['tag_id'])) {
                $tag_id = intval($_POST['tag_id']);
                $blogID = intval($_SESSION['blogID']);
                $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . 'blogtag_relation 
				WHERE tag_id = ' . $tag_id . ' 
				AND blogmain_id = ' . $blogID;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'tag_id' => $tag_id]);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['blog_permission_denied']]);
        }
    }

    public function ajax_create_blog_tag()
    {
        global  $lang, $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            global $conn, $misc;

            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
            }
            // Do we need to save?
            if (isset($_POST['title'])) {
                // Save blog now
                $title = trim($_POST['title']);
                $seoname = $page->create_seouri($title, false);
                $save_seoname =  $misc->make_db_safe($seoname);
                $save_title = $misc->make_db_safe($title);
                //Make sure Blog Title is unique.
                $sql = 'SELECT tag_id FROM ' . $config['table_prefix'] . "blogtags WHERE STRCMP(tag_name,$save_title) = 0";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                //Tag Already Exists just assign it
                if ($recordSet->RecordCount() > 0) {
                    $tag_id = $recordSet->fields('tag_id');
                } else {
                    $sql = 'INSERT INTO ' . $config['table_prefix'] . "blogtags
					(tag_name,tag_seoname)
					VALUES ($save_title,$save_seoname)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $tag_id = $conn->Insert_ID();
                }
                $blogID = intval($_SESSION['blogID']);
                //Make sure Blog Title is unique.
                $sql = 'SELECT relation_id FROM ' . $config['table_prefix_no_lang'] . 'blogtag_relation WHERE tag_id = ' . $tag_id . ' AND blogmain_id = ' . $blogID;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                //Tag Already Exists just assign it
                if ($recordSet->RecordCount() > 0) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $lang['blog_tag_already_assigned']]);
                } else {
                    $sql = 'INSERT INTO ' . $config['table_prefix_no_lang'] . "blogtag_relation
						(tag_id,blogmain_id)
						VALUES ($tag_id,$blogID)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $rel_id = $conn->Insert_ID();
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'tag_id' => $tag_id, 'tag_name' => "$title", 'tag_relid' => "$rel_id", 'tag_seoname' => "$seoname"]);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['blog_permission_denied']]);
        }
    }

    public function ajax_create_blog_tag_noassignment()
    {
        global  $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            global $conn, $misc;

            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // Do we need to save?
            if (isset($_POST['title']) && isset($_POST['seoname']) && isset($_POST['description'])) {
                // Save blog now
                $title = trim($_POST['title']);
                $seoname = trim($_POST['seoname']);
                $description = trim($_POST['description']);
                if ($seoname == '') {
                    $seoname = $page->create_seouri($title, false);
                }
                $save_seoname =  $misc->make_db_safe($seoname);
                $save_title = $misc->make_db_safe($title);
                $save_description = $misc->make_db_safe($description);

                //Make sure Blog Title is unique.
                $sql = 'SELECT tag_id FROM ' . $config['table_prefix'] . "blogtags WHERE STRCMP(tag_name,$save_title) = 0";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                //Tag Already Exists just assign it
                if ($recordSet->RecordCount() > 0) {
                    $tag_id = $recordSet->fields('tag_id');
                } else {
                    $sql = 'INSERT INTO ' . $config['table_prefix'] . "blogtags
					(tag_name,tag_seoname,tag_description)
					VALUES ($save_title,$save_seoname,$save_description)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $tag_id = $conn->Insert_ID();
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'tag_id' => $tag_id, 'tag_name' => "$title", 'tag_seoname' => "$seoname"]);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['blog_permission_denied']]);
        }
    }

    public function ajax_get_category_info()
    {
        global $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            global $conn, $misc;

            // Do we need to save?
            if (isset($_POST['category_id'])) {
                $cat_id = intval($_POST['category_id']);
                $sql = 'SELECT parent_id, category_id, category_name, category_seoname, category_description 
						FROM ' . $config['table_prefix'] . 'blogcategory 
						WHERE category_id = ' . $cat_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                while (!$recordSet->EOF) {
                    $parent_id = $recordSet->fields('parent_id');
                    $cat_id = $recordSet->fields('category_id');
                    $cat_name = $recordSet->fields('category_name');
                    $cat_seoname = $recordSet->fields('category_seoname');
                    $cat_description = $recordSet->fields('category_description');
                    $recordSet->MoveNext();
                    header('Content-type: application/json');
                    return json_encode(['error' => '0', 'category_id' => $cat_id, 'category_name' => $cat_name, 'category_seoname' => $cat_seoname, 'category_description' => $cat_description, 'parent_id' => $parent_id]);
                }
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => 'No Such Category Found']);
            }
        } else {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['blog_permission_denied']]);
        }
    }

    public function get_tag_seoname($tag_id)
    {
        global $conn, $config, $misc;

        $sql = 'SELECT tag_seoname FROM ' . $config['table_prefix'] . 'blogtags WHERE tag_id = ' . $tag_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $seoname = $recordSet->fields('tag_seoname');
        return $seoname;
    }

    public function get_tag_name($tag_id)
    {
        global $conn, $config, $misc;

        $sql = 'SELECT tag_name 
				FROM ' . $config['table_prefix'] . 'blogtags 
				WHERE tag_id = ' . $tag_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $seoname = $recordSet->fields('tag_name');
        return $seoname;
    }

    public function get_tag_description($tag_id)
    {
        global $conn, $config, $misc;

        $sql = 'SELECT tag_description 
				FROM ' . $config['table_prefix'] . 'blogtags 
				WHERE tag_id = ' . $tag_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $seoname = $recordSet->fields('tag_description');
        return $seoname;
    }

    public function ajax_get_tag_info()
    {
        global $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            global  $conn, $misc;

            // Do we need to save?
            if (isset($_POST['tag_id'])) {
                $cat_id = intval($_POST['tag_id']);
                $sql = 'SELECT  tag_id, tag_name, tag_seoname, tag_description 
						FROM ' . $config['table_prefix'] . 'blogtags 
						WHERE tag_id = ' . $cat_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                while (!$recordSet->EOF) {
                    $cat_id = $recordSet->fields('tag_id');
                    $cat_name = $recordSet->fields('tag_name');
                    $cat_seoname = $recordSet->fields('tag_seoname');
                    $cat_description = $recordSet->fields('tag_description');
                    $recordSet->MoveNext();
                    header('Content-type: application/json');
                    return json_encode(['error' => '0', 'tag_id' => $cat_id, 'tag_name' => $cat_name, 'tag_seoname' => $cat_seoname, 'tag_description' => $cat_description]);
                }
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => 'No Such tag Found']);
            }
        }
    }

    public function ajax_update_category_info()
    {
        global $config, $conn, $misc, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
            }

            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // Do we need to save?
            if (isset($_POST['category_id']) && isset($_POST['title']) && isset($_POST['seoname']) && isset($_POST['description']) && isset($_POST['parent'])) {
                $cat_id = intval($_POST['category_id']);
                $parent_id = intval($_POST['parent']);
                //Prevent Cat From being it's own parent
                if ($parent_id == $cat_id) {
                    $parent_id = 0;
                }
                $name = strip_tags(trim($_POST['title']));
                $seoname = strip_tags(trim($_POST['seoname']));
                $description = trim($_POST['description']);
                if (trim($seoname) != '') {
                    $seoname = trim($seoname);
                } else {
                    $seoname = $page->create_seouri($name, false);
                }

                $name = $misc->make_db_safe($name);
                $seoname = $misc->make_db_safe($seoname);
                $description = $misc->make_db_safe($description);
                $sql = 'UPDATE ' . $config['table_prefix'] . "blogcategory 
						SET parent_id = $parent_id, category_id = $cat_id, category_name = $name, category_seoname = $seoname, category_description = $description 
						WHERE category_id = " . $cat_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'cat_id' => $cat_id]);
            }
        }
    }

    public function ajax_update_tag_info()
    {
        global $config, $conn, $misc, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
            }

            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // Do we need to save?
            if (isset($_POST['tag_id']) && isset($_POST['title']) && isset($_POST['seoname']) && isset($_POST['description'])) {
                $tag_id = intval($_POST['tag_id']);
                $name = strip_tags(trim($_POST['title']));
                $seoname = strip_tags(trim($_POST['seoname']));
                $description = trim($_POST['description']);
                if (trim($seoname) != '') {
                    $seoname = trim($seoname);
                } else {
                    $seoname = $page->create_seouri($seoname, false);
                }

                $name = $misc->make_db_safe($name);
                $seoname = $misc->make_db_safe($seoname);
                $description = $misc->make_db_safe($description);
                $sql = 'UPDATE ' . $config['table_prefix'] . "blogtags SET  tag_id = $tag_id, tag_name = $name, tag_seoname = $seoname, tag_description = $description WHERE tag_id = " . $tag_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'tag_id' => $tag_id]);
            }
        }
    }

    public function ajax_delete_blog_category()
    {
        global $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            global $conn, $misc;

            // Do we need to save?
            if (isset($_POST['category_id'])) {
                $cat_id = intval($_POST['category_id']);
                //Delete BLog Category Relations
                $sql = 'DELETE FROM '   . $config['table_prefix_no_lang'] . 'blogcategory_relation 
						WHERE category_id = ' . $cat_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }

                //Look For Blogs with no categories
                $sql = 'SELECT blogmain_id FROM ' . $config['table_prefix'] . 'blogmain WHERE blogmain_id NOT IN (SELECT DISTINCT(blogmain_id) FROM ' . $config['table_prefix_no_lang'] . 'blogcategory_relation)';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }

                while (!$recordSet->EOF) {
                    $fix_id = $recordSet->fields('blogmain_id');
                    $sql = 'INSERT INTO '   . $config['table_prefix_no_lang'] . 'blogcategory_relation (blogmain_id,category_id) VALUES (' . $fix_id . ',1)';
                    $recordSet2 = $conn->Execute($sql);
                    if (!$recordSet2) {
                        $misc->log_error($sql);
                    }
                    $recordSet->MoveNext();
                }

                $sql = 'UPDATE ' . $config['table_prefix'] . 'blogcategory 
						SET parent_id = 0 
						WHERE parent_id = ' . $cat_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $sql = 'DELETE FROM ' . $config['table_prefix'] . 'blogcategory 
						WHERE category_id = ' . $cat_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'error_msg' => '']);
            } else {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => 'No category_id']);
            }
        }
    }

    public function ajax_delete_blog_tag()
    {
        global $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            global $conn, $misc;

            // Do we need to save?
            if (isset($_POST['tag_id'])) {
                $tag_id = intval($_POST['tag_id']);
                //Delete BLog tag Relations
                $sql = 'DELETE FROM '   . $config['table_prefix_no_lang'] . 'blogtag_relation 
						WHERE tag_id = ' . $tag_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $sql = 'DELETE FROM ' . $config['table_prefix'] . 'blogtags 
						WHERE tag_id = ' . $tag_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                header('Content-type: applitagion/json');
                return json_encode(['error' => '0', 'error_msg' => '']);
            } else {
                header('Content-type: applitagion/json');
                return json_encode(['error' => '1', 'error_msg' => 'No tag_id']);
            }
        }
    }

    public function ajax_rankdown_blog_category()
    {
        global $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            global $conn, $misc;

            // Do we need to save?
            if (isset($_POST['category_id'])) {
                $cat_id = intval($_POST['category_id']);
                //Get Category Rank
                $sql = 'SELECT category_rank,parent_id 
						FROM ' . $config['table_prefix'] . 'blogcategory 
						WHERE category_id = ' . $cat_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $category_rank = $recordSet->fields('category_rank');
                $parent_id = $recordSet->fields('parent_id');
                //Get Next Rank For this Category Group.
                $sql = 'SELECT max(category_rank) as maxrank 
						FROM ' . $config['table_prefix'] . 'blogcategory 
						WHERE parent_id = ' . $parent_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $rank = $recordSet->fields('max_rank');
                if ($category_rank == $rank) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => 'Max Can Not Move Down']);
                }
                $new_rank = $category_rank + 1;
                $sql = 'UPDATE ' . $config['table_prefix'] . "blogcategory 
						SET category_rank = $category_rank 
						WHERE category_rank = $new_rank 
						AND parent_id = $parent_id";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $sql = 'UPDATE ' . $config['table_prefix'] . "blogcategory 
						SET category_rank = $new_rank WHERE category_id = $cat_id";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'error_msg' => '']);
            }
        }
    }

    public function ajax_rankup_blog_category()
    {
        global $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            global $conn, $misc;

            // Do we need to save?
            if (isset($_POST['category_id'])) {
                $cat_id = intval($_POST['category_id']);
                //Get Category Rank
                $sql = 'SELECT category_rank,parent_id 
						FROM ' . $config['table_prefix'] . 'blogcategory 
						WHERE category_id = ' . $cat_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $category_rank = $recordSet->fields('category_rank');
                $parent_id = $recordSet->fields('parent_id');
                if ($category_rank == 0) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => 'Rank 0 Can Not Move Up']);
                }
                $new_rank = $category_rank - 1;
                $sql = 'UPDATE ' . $config['table_prefix'] . "blogcategory 
						SET category_rank = $category_rank 
						WHERE category_rank = $new_rank 
						AND parent_id = $parent_id";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $sql = 'UPDATE ' . $config['table_prefix'] . "blogcategory 
						SET category_rank = $new_rank 
						WHERE category_id = $cat_id";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'error_msg' => '']);
            }
        }
    }

    public function ajax_create_blog_category()
    {
        global $lang, $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            global $conn, $misc;

            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
                header('Content-type: application/json');
                return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
            }

            // Do we need to save?
            if (isset($_POST['title'])) {
                // Save blog now
                if (!isset($_POST['parent'])) {
                    $parent = 0;
                } else {
                    $parent = intval($_POST['parent']);
                }
                if ($blog_user_type == 4 || $_SESSION['admin_privs'] == 'yes') {
                    if (isset($_POST['seoname']) && trim($_POST['seoname']) != '') {
                        $seotitle = trim($_POST['seoname']);
                    } else {
                        $seotitle = $page->create_seouri($_POST['title'], false);
                    }
                    if (isset($_POST['description'])) {
                        $cat_desc = trim($_POST['description']);
                    } else {
                        $cat_desc = '';
                    }
                } else {
                    $cat_desc = '';
                    $seotitle = $page->create_seouri($_POST['title'], false);
                }
                //Get Next Rank For this Category Group.
                $sql = 'SELECT max(category_rank) as maxrank,count(category_id) as post_count 
						FROM ' . $config['table_prefix'] . 'blogcategory 
						WHERE parent_id = ' . $parent;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $rank = $recordSet->fields('maxrank');
                $post_count = $recordSet->fields('post_count');
                if ($post_count == 0) {
                    $rank = 0;
                } else {
                    $rank++;
                }
                $title = trim($_POST['title']);

                $save_title = $misc->make_db_safe($title);
                $sql_seotitle = $misc->make_db_safe($seotitle);
                $save_description = $misc->make_db_safe($cat_desc);
                //Make sure Blog Title is unique.
                $sql = 'SELECT category_id 
						FROM ' . $config['table_prefix'] . "blogcategory 
						WHERE STRCMP(category_name,$save_title) = 0";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                if ($recordSet->RecordCount() > 0) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => "$lang[blog_category_not_unique]"]);
                }
                $sql = 'INSERT INTO ' . $config['table_prefix'] . "blogcategory	
						(category_name,category_seoname,category_description,category_rank,parent_id)
						VALUES ($save_title,$sql_seotitle,$save_description,$rank,$parent)";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $blog_id = $conn->Insert_ID();
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'cat_id' => $blog_id, 'cat_name' => "$title", 'cat_rank' => "$rank", 'cat_parent' => "$parent"]);
            }
        }
    }

    public function ajax_create_blog_post()
    {
        global $lang, $config, $conn, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            // Do we need to save?
            if (isset($_POST['title'])) {
                if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
                }
                // Save blog now
                $title = trim($_POST['title']);
                $save_title = $misc->make_db_safe($title);
                //Make sure Blog Title is unique.
                $sql = 'SELECT blogmain_id 
						FROM ' . $config['table_prefix'] . "blogmain 
						WHERE STRCMP(blogmain_title,$save_title) = 0";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                if ($recordSet->RecordCount() > 0) {
                    header('Content-type: application/json');
                    return json_encode(['error' => '1', 'error_msg' => "$lang[blog_title_not_unique]"]);
                }
                $userdb_id = $misc->make_db_safe($_SESSION['userID']);
                //Generate seo URL
                $seotitle = $page->create_seouri($title, false);
                $sql_seotitle = $misc->make_db_safe($seotitle);

                $sql = 'INSERT INTO ' . $config['table_prefix'] . "blogmain
				(userdb_id,blogmain_full,blogmain_title,blogmain_date,blogmain_published,blogmain_description,blogmain_keywords,blog_seotitle)
				VALUES ($userdb_id,'',$save_title," . strtotime('now') . ",0,'',''," . $sql_seotitle . ')';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $blog_id = $conn->Insert_ID();
                $sql = 'INSERT INTO ' . $config['table_prefix_no_lang'] . "blogcategory_relation
				(category_id,blogmain_id)
				VALUES (1,$blog_id)";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                //Verify the SEO title is unique
                $sql = 'SELECT blogmain_id 
						FROM ' . $config['table_prefix'] . 'blogmain 
						WHERE blog_seotitle = ' . $sql_seotitle . ' 
						AND blogmain_id <> ' . $blog_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                if ($recordSet->RecordCount() > 0) {
                    $seotitle =  $page->create_seouri($title . '-' . $blog_id, false);
                    $sql_seotitle =  $misc->make_db_safe($seotitle);
                    $sql = 'UPDATE ' . $config['table_prefix'] . "blogmain 
							SET blog_seotitle = $sql_seotitle 
							WHERE blogmain_id = " . $blog_id;
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                }
                header('Content-type: application/json');
                return json_encode(['error' => '0', 'id' => $blog_id, 'title' => "$title"]);
            }
        }
    }

    public function ajax_set_blog_cat()
    {
        global $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $blog_user_type = intval($_SESSION['blog_user_type']);

        if ($security === true) {
            global $conn, $misc;

            // Do we need to save?
            if (isset($_POST['blog_id']) && isset($_POST['cat_id'])) {
                // Save blog now
                $blog_id = intval($_POST['blog_id']);
                $cat_id = intval($_POST['cat_id']);
                $status = intval($_POST['status']);
                if ($status == 1) {
                    $sql = 'SELECT category_id 
							FROM '    . $config['table_prefix_no_lang'] . 'blogcategory_relation 
							WHERE blogmain_id = ' . $blog_id . ' 
							AND category_id =' . $cat_id;
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    if ($recordSet->RecordCount() == 0) {
                        $sql = 'INSERT INTO '   . $config['table_prefix_no_lang'] . 'blogcategory_relation 
								(blogmain_id,category_id) 
								VALUES (' . $blog_id . ',' . $cat_id . ')';
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                    }
                } else {
                    $sql = 'DELETE FROM '   . $config['table_prefix_no_lang'] . 'blogcategory_relation 
							WHERE blogmain_id = ' . $blog_id . ' 
							AND category_id = ' . $cat_id;
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                }
                header('Content-type: application/json');
                return json_encode(['blog_id' => $blog_id, 'cat_id' => $cat_id, 'status' => $status]);
            }
        }
    }

    public function get_blog_tag_assignment($blog_id)
    {
        global $conn, $config, $misc;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        //Get Min/Max population.
        $sql = 'SELECT count(tag_id) as population 
				FROM ' . $config['table_prefix_no_lang'] . 'blogtag_relation 
				GROUP BY tag_id 
				ORDER BY population';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $min_count = $recordSet->fields('population');
        $recordSet->MoveLast();
        $max_count = $recordSet->fields('population');

        $sql = 'SELECT ' . $config['table_prefix_no_lang'] . 'blogtag_relation.tag_id,tag_name,tag_seoname 
				FROM ' . $config['table_prefix_no_lang'] . 'blogtag_relation 
				LEFT JOIN ' . $config['table_prefix'] . 'blogtags 
				ON ' . $config['table_prefix_no_lang'] . 'blogtag_relation.tag_id = ' . $config['table_prefix'] . 'blogtags.tag_id 
				WHERE blogmain_id = ' . intval($blog_id);
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $misc->log_error($sql);
        }
        //weight = (Math.log(occurencesOfCurrentTag)-Math.log(minOccurs))/(Math.log(maxOccurs)-Math.log(minOccurs));
        //fontSizeOfCurrentTag = minFontSize + Math.round((maxFontSize-minFontSize)*weight);

        $assigned = [];
        while (!$recordSet->EOF) {
            $tag_id = $recordSet->fields('tag_id');
            $tag_name = $recordSet->fields('tag_name');
            $tag_seoname = $recordSet->fields('tag_seoname');
            //Get Tag LInk
            $tag_link = $page->magicURIGenerator('blog_tag', $tag_id, true);
            //Get Tag Population
            $sql = 'SELECT count(tag_id) as population 
					FROM ' . $config['table_prefix_no_lang'] . "blogtag_relation 
					WHERE tag_id = '" . $tag_id . "' 
					GROUP BY tag_id";
            $recordSet2 = $conn->Execute($sql);
            if (!$recordSet2) {
                $misc->log_error($sql);
            }
            $tag_population = $recordSet2->fields('population');
            if ($min_count == $max_count) {
                $font_size = 8;
            } else {
                $weight = (log($tag_population) - log($min_count)) / (log($max_count) - log($min_count));
                $font_size = 8 + round((22 - 8) * $weight);
            }
            $assigned[$tag_id] = ['tag_name' => $tag_name, 'tag_seoname' => $tag_seoname, 'tag_link' => $tag_link, 'tag_fontsize' => $font_size];
            $recordSet->MoveNext();
        }
        return $assigned;
    }

    public function get_blog_categories_assignment($blog_id)
    {
        global $conn, $config, $misc;

        $sql = 'SELECT category_id 
				FROM ' . $config['table_prefix_no_lang'] . 'blogcategory_relation 
				WHERE blogmain_id = ' . intval($blog_id);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $assigned = [];
        while (!$recordSet->EOF) {
            $assigned[] = $recordSet->fields('category_id');
            $recordSet->MoveNext();
        }
        return $assigned;
    }

    public function get_blog_categories_assignment_names($blog_id)
    {
        global $conn, $config, $misc;

        $blog_id = intval($blog_id);
        $sql = 'SELECT ' . $config['table_prefix'] . 'blogcategory.category_id, category_name 
				FROM ' . $config['table_prefix_no_lang'] . 'blogcategory_relation, ' . $config['table_prefix'] . 'blogcategory 
				WHERE ' . $config['table_prefix_no_lang'] . 'blogcategory_relation.category_id =  ' . $config['table_prefix'] . 'blogcategory.category_id 
				AND blogmain_id = ' . intval($blog_id);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $assigned = [];
        while (!$recordSet->EOF) {
            $assigned[$recordSet->fields('category_id')] = $recordSet->fields('category_name');
            $recordSet->MoveNext();
        }
        return $assigned;
    }

    public function get_blog_categories_assignment_seonames($blog_id)
    {
        global $conn, $config, $misc;

        $sql = 'SELECT category_seoname 
				FROM ' . $config['table_prefix_no_lang'] . 'blogcategory_relation, ' . $config['table_prefix'] . 'blogcategory 
				WHERE ' . $config['table_prefix_no_lang'] . 'blogcategory_relation.category_id =  ' . $config['table_prefix'] . 'blogcategory.category_id AND blogmain_id = ' . intval($blog_id);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $assigned = [];
        while (!$recordSet->EOF) {
            $assigned[] = $recordSet->fields('category_seoname');
            $recordSet->MoveNext();
        }
        return $assigned;
    }

    public function get_blog_category_parent($cat_id)
    {
        global $config, $conn, $misc;

        $sql = 'SELECT parent_id 
				FROM ' . $config['table_prefix'] . 'blogcategory 
				WHERE category_id = ' . intval($cat_id);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $parent_id = $recordSet->fields('parent_id');
        return $parent_id;
    }

    public function get_blog_category_seoname($cat_id)
    {
        global $conn, $config, $misc;

        $sql = 'SELECT category_seoname FROM ' . $config['table_prefix'] . 'blogcategory WHERE category_id = ' . intval($cat_id);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $cat_name = $recordSet->fields('category_seoname');
        return $cat_name;
    }

    public function get_blog_category_name($cat_id)
    {
        global $conn, $config, $misc;

        $sql = 'SELECT category_name 
				FROM ' . $config['table_prefix'] . 'blogcategory 
				WHERE category_id = ' . intval($cat_id);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $cat_name = false;
        while (!$recordSet->EOF) {
            $cat_name = $recordSet->fields('category_name');
            $recordSet->MoveNext();
        }
        return $cat_name;
    }

    public function get_blog_category_description($cat_id)
    {
        global $conn, $config, $misc;

        $sql = 'SELECT category_description 
				FROM ' . $config['table_prefix'] . 'blogcategory 
				WHERE category_id = ' . intval($cat_id);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $cat_name = false;
        while (!$recordSet->EOF) {
            $cat_name = $recordSet->fields('category_description');
            $recordSet->MoveNext();
        }
        return $cat_name;
    }

    public function get_blog_tags()
    {
        global $conn, $config, $misc;

        $sql = 'SELECT tag_id, tag_name 
				FROM ' . $config['table_prefix'] . 'blogtags';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $tags = [];
        while (!$recordSet->EOF) {
            $tags[$recordSet->fields('tag_id')] = $recordSet->fields('tag_name');
            $recordSet->MoveNext();
        }
        return $tags;
    }

    public function get_blog_categories_flat()
    {
        global $conn, $config, $misc;

        $sql = 'SELECT category_id, category_name 
				FROM ' . $config['table_prefix'] . 'blogcategory 
				ORDER BY category_rank';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $categories = [];
        while (!$recordSet->EOF) {
            $categories[$recordSet->fields('category_id')] = $recordSet->fields('category_name');
            $recordSet->MoveNext();
        }
        return $categories;
    }

    public function get_blog_categories()
    {
        global $conn, $config, $misc;

        $sql = 'SELECT parent_id, category_id, category_name 
				FROM ' . $config['table_prefix'] . 'blogcategory';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $categories = [];
        while (!$recordSet->EOF) {
            $categories[$recordSet->fields('parent_id')][$recordSet->fields('category_id')] = $recordSet->fields('category_name');
            $recordSet->MoveNext();
        }
        return $categories;
    }

    public function get_category_description($cat_id)
    {
        global $conn, $config, $misc;

        $sql = 'SELECT category_description 
				FROM ' . $config['table_prefix'] . 'blogcategory 
				WHERE category_id = ' . intval($cat_id);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $description = $recordSet->fields('category_description');
        return $description;
    }

    public function get_category_keywords($cat_id)
    {
        global $conn, $config, $misc;

        //gets the keywords from all the blog articles on the current page
        //then returns the 6 most used
        $sql = 'SELECT blogmain_keywords 
				FROM ' . $config['table_prefix'] . 'blogmain 
				WHERE blogmain_published = 1
				AND blogmain_id IN (
					SELECT blogmain_id 
					FROM ' . $config['table_prefix_no_lang'] . 'blogcategory_relation 
					WHERE category_id = ' . intval($cat_id) . ')
				ORDER BY blogmain_date DESC';
        if (!isset($_GET['cur_page'])) {
            $_GET['cur_page'] = 0;
        }
        $limit_str = intval($_GET['cur_page']) * $config['blogs_per_page'];
        $recordSet = $conn->SelectLimit($sql, $config['blogs_per_page'], $limit_str);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $keywords = '';
        $count = 0;
        while (!$recordSet->EOF) {
            if ($recordSet->fields('blogmain_keywords') != '') {
                if ($count == 0) {
                    $keywords .= rtrim($recordSet->fields('blogmain_keywords'), ',');
                } else {
                    $keywords .= ',' . rtrim($recordSet->fields('blogmain_keywords'), ',');
                }
                $count++;
            }
            $recordSet->MoveNext();
        }
        $keywords_arr = array_map('trim', explode(',', $keywords));
        //limit this to 6 keywords
        $keywords_arr = array_slice(array_count_values($keywords_arr), 0, 5);
        $keywords = implode(', ', array_keys($keywords_arr));
        return $keywords;
    }

    public function get_blog_populartags()
    {
        global $conn, $config, $misc;

        //Get Min/Max population.
        $sql = 'SELECT count(tag_id) as population 
				FROM ' . $config['table_prefix_no_lang'] . 'blogtag_relation 
				GROUP BY tag_id ORDER BY population';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $min_count = $recordSet->fields('population');
        $recordSet->MoveLast();
        $max_count = $recordSet->fields('population');

        $sql = 'SELECT ' . $config['table_prefix'] . 'blogtags.tag_id, tag_name, count(' . $config['table_prefix_no_lang'] . 'blogtag_relation.tag_id) as population 
				FROM ' . $config['table_prefix'] . 'blogtags 
				LEFT JOIN ' . $config['table_prefix_no_lang'] . 'blogtag_relation 
				ON ' . $config['table_prefix'] . 'blogtags.tag_id = ' . $config['table_prefix_no_lang'] . 'blogtag_relation.tag_id 
				GROUP BY tag_id 
				ORDER BY population DESC LIMIT 50';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $tags = [];
        while (!$recordSet->EOF) {
            $tag_id = $recordSet->fields('tag_id');
            $tag_name = $recordSet->fields('tag_name');
            //Get Tag Population
            $sql = 'SELECT count(tag_id) as population 
					FROM ' . $config['table_prefix_no_lang'] . "blogtag_relation 
					WHERE tag_id = '" . $tag_id . "' GROUP BY tag_id";
            $recordSet2 = $conn->Execute($sql);
            if (!$recordSet2) {
                $misc->log_error($sql);
            }
            $tag_population = $recordSet2->fields('population');
            if ($min_count == $max_count) {
                $font_size = 8;
            } else {
                $weight = (log($tag_population) - log($min_count)) / (log($max_count) - log($min_count));
                $font_size = 8 + round((22 - 8) * $weight);
            }
            $tags[$tag_id] = ['tag_name' => $tag_name, 'tag_fontsize' => $font_size];
            $recordSet->MoveNext();
        }
        return $tags;
    }

    public function get_blog_popularcategories()
    {
        global $conn, $config, $misc;

        $sql = 'SELECT ' . $config['table_prefix'] . 'blogcategory.category_id, category_name, count(' . $config['table_prefix_no_lang'] . 'blogcategory_relation.category_id) as population 
				FROM ' . $config['table_prefix'] . 'blogcategory 
				LEFT JOIN ' . $config['table_prefix_no_lang'] . 'blogcategory_relation 
				ON ' . $config['table_prefix'] . 'blogcategory.category_id = ' . $config['table_prefix_no_lang'] . 'blogcategory_relation.category_id 
				GROUP BY category_id 
				ORDER BY population DESC LIMIT 5';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $categories = [];
        while (!$recordSet->EOF) {
            $categories[$recordSet->fields('category_id')] = $recordSet->fields('category_name');
            $recordSet->MoveNext();
        }
        return $categories;
    }

    public function get_recent_blog_posts($count = 5)
    {
        global $conn, $config, $misc;

        $sql = 'SELECT blogmain_id, blogmain_title 
				FROM ' . $config['table_prefix'] . "blogmain 
				WHERE blogmain_published = '1' 
				ORDER BY blogmain_date DESC LIMIT " . intval($count);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $posts = [];
        while (!$recordSet->EOF) {
            $posts[$recordSet->fields('blogmain_id')] = $recordSet->fields('blogmain_title');
            $recordSet->MoveNext();
        }
        return $posts;
    }

    public function get_recent_blog_comments($count = 5)
    {
        global $conn, $config, $misc;

        $count = intval($count);
        $sql = 'SELECT blogcomments_id, blogcomments_text,blogmain_id,userdb_id 
				FROM ' . $config['table_prefix'] . "blogcomments 
				WHERE blogcomments_moderated = '1' 
				ORDER BY blogcomments_timestamp 
				DESC LIMIT " . intval($count);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $comments = [];
        while (!$recordSet->EOF) {
            $comments[$recordSet->fields('blogcomments_id')] = ['text' => $recordSet->fields('blogcomments_text'), 'blog_id' => $recordSet->fields('blogmain_id'), 'userdb_id' => $recordSet->fields('userdb_id')];
            $recordSet->MoveNext();
        }
        return $comments;
    }

    public function get_archive_list()
    {
        global $conn, $config, $misc;

        $sql = 'SELECT blogmain_date 
				FROM ' . $config['table_prefix'] . 'blogmain 
				WHERE blogmain_published = 1 
				ORDER BY blogmain_date DESC';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $archive = [];
        while (!$recordSet->EOF) {
            if (count($archive) >= 10) {
                break;
            }
            $date = $recordSet->fields('blogmain_date');
            $date = date('Y/F', $date);
            $date = strtolower($date);
            $archive[$date] = $date;
            $recordSet->MoveNext();
        }
        return $archive;
    }

    public function get_blog_title($blog_id)
    {
        global $conn, $config, $misc;

        $blog_id = intval($blog_id);
        $sql = 'SELECT blogmain_title 
				FROM ' . $config['table_prefix'] . 'blogmain 
				WHERE blogmain_id=' . $blog_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $title = $recordSet->fields('blogmain_title');
        return $title;
    }

    public function get_blog_seotitle($blog_id)
    {
        global $conn, $config, $misc;

        $blog_id = intval($blog_id);
        $sql = 'SELECT blog_seotitle 
				FROM ' . $config['table_prefix'] . 'blogmain 
				WHERE blogmain_id=' . $blog_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $title = $recordSet->fields('blog_seotitle');
        return $title;
    }

    public function get_blog_status($blog_id)
    {
        global $conn, $config, $misc;

        $blog_id = intval($blog_id);
        $blog_id = $misc->make_db_safe($blog_id);
        $sql = 'SELECT blogmain_published 
				FROM ' . $config['table_prefix'] . 'blogmain 
				WHERE blogmain_id=' . $blog_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $status = $recordSet->fields('blogmain_published');
        return $status;
    }

    public function get_blog_date($blog_id)
    {
        global $conn, $config, $misc;

        $blog_id = intval($blog_id);
        $blog_id = $misc->make_db_safe($blog_id);
        $sql = 'SELECT blogmain_date 
				FROM ' . $config['table_prefix'] . 'blogmain 
				WHERE blogmain_id=' . $blog_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $date = $recordSet->fields('blogmain_date');
        $date = $misc->convert_timestamp($date, true);
        return $date;
    }

    public function get_blog_comment_count($blog_id)
    {
        global $conn, $config, $misc;

        $blog_id = intval($blog_id);
        $blog_id = intval($blog_id);
        $sql = 'SELECT count(blogcomments_id) as commentcount 
				FROM ' . $config['table_prefix'] . 'blogcomments WHERE blogmain_id=' . $blog_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $count = $recordSet->fields('commentcount');
        return $count;
    }

    public function get_blog_id_from_comment_id($comment_id)
    {
        global $conn, $config, $misc;

        $comment_id = intval($comment_id);
        $sql = 'SELECT blogmain_id 
				FROM ' . $config['table_prefix'] . 'blogcomments 
				WHERE blogcomments_id=' . $comment_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $blogmain_id = $recordSet->fields('blogmain_id');
        return $blogmain_id;
    }

    public function get_blog_author_id($blog_id)
    {
        global $conn, $config, $misc;

        $blog_id = intval($blog_id);
        $sql = 'SELECT userdb_id 
				FROM ' . $config['table_prefix'] . 'blogmain 
				WHERE blogmain_id=' . $blog_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $userid = $recordSet->fields('userdb_id');
        return $userid;
    }

    public function get_blog_author($blog_id)
    {
        global $conn, $config, $misc;

        $blog_id = intval($blog_id);
        $sql = 'SELECT userdb_id 
				FROM ' . $config['table_prefix'] . 'blogmain 
				WHERE blogmain_id=' . $blog_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $userid = $recordSet->fields('userdb_id');
        include_once $config['basepath'] . '/include/user.inc.php';
        $user = new user();
        $name = $user->get_user_single_item('userdb_user_first_name', $userid) . ' ' . $user->get_user_single_item('userdb_user_last_name', $userid);
        return $name;
    }

    public function get_blog_description($blog_id)
    {
        global $conn, $config, $misc;

        $blog_id = intval($blog_id);
        $sql = 'SELECT blogmain_description 
				FROM ' . $config['table_prefix'] . 'blogmain 
				WHERE blogmain_id=' . $blog_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $description = $recordSet->fields('blogmain_description');
        return $description;
    }

    public function get_blog_keywords($blog_id)
    {
        global $conn, $config, $misc;

        $blog_id = intval($blog_id);
        $sql = 'SELECT blogmain_keywords 
				FROM ' . $config['table_prefix'] . 'blogmain 
				WHERE blogmain_id=' . $blog_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $keywords = $recordSet->fields('blogmain_keywords');
        return $keywords;
    }

    public function pingback_url_test($url)
    {
        global $config;

        $agent = 'Open-Realty Pingback Agent (' . $config['baseurl'] . ')';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $buffer = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch), '<br />';
        }
        echo $url . ': ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . '</br >';
        echo '<pre>' . $buffer . '</pre><br />';
        curl_close($ch);
    }

    public function pingback_urls($myURL, $text)
    {
        global $config, $misc;
        ;

        $display = '';
        preg_match_all('/(href)=[\'"]?([^\'" >]+)[\'" >]/im', $text, $matches);
        $unique_link = array_unique($matches[2]);
        foreach ($unique_link as $mURL) {
            $display .= 'Checking ' . $mURL . "\n";
            $agent = 'Open-Realty Pingback Agent (' . $config['baseurl'] . ')';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $mURL);
            curl_setopt($ch, CURLOPT_USERAGENT, $agent);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            $buffer = curl_exec($ch);
            $curl_info = curl_getinfo($ch);
            curl_close($ch);
            $header_size = $curl_info['header_size'];
            $header = substr($buffer, 0, $header_size);
            $body = substr($buffer, $header_size + 1);
            $h_array = explode("\n", $header);
            $pingback_url = false;
            //Look at header first.
            foreach ($h_array as $header) {
                $display .= 'Checking Header "' . $header . "\"\n";
                if (stripos($header, 'X-Pingback: ') === 0) {
                    $display .= 'Found X-Pingback header';
                    $pingback_url = trim(substr($header, 12));
                }
            }
            //If no header look for <link>
            if ($pingback_url == false) {
                $link_found = preg_match('/<link rel="pingback" href="([^"]+)\s?/?>/i', $body, $link_matches);
                if ($link_found == 1) {
                    $pingback_url = html_entity_decode($link_matches[1]);
                }
            }
            //If we have a pingback URL ping it.
            if ($pingback_url !== false) {
                $misc->log_action('Sending Pingback to ' . $pingback_url);
                $display .= 'Found it sending pickback request for ' . $pingback_url . "\n";
                $result = $this->doPingback($myURL, $pingback_url, $mURL);
                if ($result !== true) {
                    $misc->log_action('Pingback failed:' . $result);
                    $display .= $result . "\n";
                }
            }
        }
        return $display;
    }

    public function pingback_services($myurl)
    {
        global $config;
        $servicelist = $config['pingback_services'];
        $service_urls = explode("\n", $servicelist);
        foreach ($service_urls as $url) {
            $result = $this->doPingback($myurl, $url);
        }
        return $result;
    }

    public function doPingback($myURL, $pingURL, $linkURL = '')
    {
        global $config;

        include_once $config['basepath'] . '/vendor/phpxmlrpc/phpxmlrpc/lib/xmlrpc.inc';
        $url = trim($pingURL);
        //Check if path is a full url
        if (strpos($url, 'http://') !== false || strpos($url, 'https://') !== false) {
            $udata = parse_url($url);
            if (isset($udata['port'])) {
                $port = intval($udata['port']);
            } else {
                $port = 80;
            }
            $server = $udata['host'];
            $path = $udata['path'];
            if (isset($udata['query'])) {
                $path .= $udata['query'];
            }
            $xmlrpc_client = new xmlrpc_client($path, $server, $port);
            //$xmlrpc_client->setDebug(1); //this will print all the responses as they come back
            if ($linkURL != '') {
                $xmlrpc_message = new xmlrpcmsg('pingback.ping', [new xmlrpcval($myURL), new xmlrpcval($linkURL)]);
            } else {
                $xmlrpc_message = new xmlrpcmsg('pingback.ping', [new xmlrpcval($myURL), new xmlrpcval($url)]);
            }

            $xmlrpc_response = $xmlrpc_client->send($xmlrpc_message);
            if ($xmlrpc_response->faultCode() == 0) {
                return true;
            } else {
                return $xmlrpc_response->faultString();
            }
        }
    }

    public function ajax_wpinject_run()
    {
        global $config, $conn, $misc, $lang;

        include_once $config['basepath'] . '/include/blog_functions.inc.php';
        $blog_functions = new blog_functions();
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        include_once $config['basepath'] . '/include/user_manager.inc.php';
        $user_manager = new user_managment();
        $display = '';

        if (!isset($_POST['token']) || !$misc->validate_csrf_token($_POST['token'])) {
            $display .= '<span class="redtext">' . $lang['invalid_csrf_token'] . '</span>' . BR;
            return $display;
        }

        // $_FILES['uploadedfile']['name']);
        // $_FILES['uploadedfile']['tmp_name']
        if (!isset($_FILES['uploadedfile'])) {
            return $display;
        }
        $display .= 'File "' . $_FILES['uploadedfile']['name'] . '" uploaded sucessfully.<br />';
        $xml = simplexml_load_file($_FILES['uploadedfile']['tmp_name'], 'SimpleXMLElement', LIBXML_NOCDATA);

        //Load User List
        $user_email_list = [];
        $user_name_list = [];
        $sql = 'SELECT userdb_id,userdb_emailaddress,userdb_user_name
FROM ' . $config['table_prefix'] . 'userdb';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        // get main listings data
        while (!$recordSet->EOF) {
            $user_email = $recordSet->fields('userdb_emailaddress');
            $user_name = $recordSet->fields('userdb_user_name');
            $user_id = $recordSet->fields('userdb_id');
            $user_email_list[$user_id] = $user_email;
            $user_name_list[$user_id] = $user_name;

            $recordSet->MoveNext();
        } // end while
        //Get The Channel Properties, as they hold categories and slugs
        $wp_channel = (array)$xml->{'channel'}->children('http://wordpress.org/export/1.2/');
        if (empty($wp_channel)) {
            $wp_channel = (array)$xml->{'channel'}->children('http://wordpress.org/export/1.1/');
        }
        if (empty($wp_channel)) {
            $wp_channel = (array)$xml->{'channel'}->children('http://wordpress.org/export/1.0/');
        }

        $cat_obj = $wp_channel['category'];

        if (isset($cat_obj->{'category_nicename'})) {
            $cat_obj = [0 => $cat_obj];
        }


        $display .= 'Getting Category List from Open-Realty<br />';
        $or_cats = $blog_functions->get_blog_categories_flat();
        $display .= 'Importing Wordpress Categories.<br />';
        $skiped_id = [];

        foreach ($cat_obj as $cat_id => $wpCat) {
            $cat_slug = (string)$wpCat->{'category_nicename'};
            $cat_parent = (string)$wpCat->{'category_parent'};
            $cat_name = (string)$wpCat->{'cat_name'};
            $cat_desc = (string)$wpCat->{'category_description'};

            //Check if category exists
            if (in_array($cat_name, $or_cats)) {
                $display .= 'Category ' . $cat_name . ' already exists.<br />';
            } else {
                //Make Sure Parent Category exists.
                if ($cat_parent != '' && !in_array($cat_parent, $or_cats)) {
                    $display .= '<span class="redtext">Category ' . $cat_name . ' can not be imported into parent category ' . $cat_parent .
    ' becuase it does not exist, importing as a top level category.</span>' . BR;
                    $parent_id = 0;
                } else {
                    $parent_id = array_search($cat_name, $or_cats);
                    $parent_id = intval($parent_id);
                }
                //Get Next Rank For this Category Group.
                $sql = 'SELECT max(category_rank) as maxrank,count(category_id) as post_count
FROM ' . $config['table_prefix'] . 'blogcategory
WHERE parent_id = ' . $parent_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $rank = $recordSet->fields('maxrank');
                $post_count = $recordSet->fields('post_count');
                if ($post_count == 0) {
                    $rank = 0;
                } else {
                    $rank++;
                }
                $sql = 'INSERT INTO ' . $config['table_prefix'] . "blogcategory
(category_name,category_seoname,category_description,category_rank,parent_id)
VALUES (" . $misc->make_db_safe($cat_name) . "," . $misc->make_db_safe($cat_name) . "," . $misc->make_db_safe($cat_name)
. ",$rank,$parent_id)";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $blog_id = $conn->Insert_ID();
                //Add New Category to list
                $or_cats[$blog_id] = $cat_name;
                $display .= 'Category ' . $cat_name . ' created.<br />';
            }
        }
        //Load Tags
        if (isset($wp_channel['tag'])) {
            $tag_obj = $wp_channel['tag'];
        } else {
            $tag_obj = [];
        }


        $display .= 'Getting Taq List from Open-Realty<br />';
        $or_tags = $blog_functions->get_blog_tags();
        $display .= 'Importing Wordpress Tags.<br />';
        $skiped_id = [];
        foreach ($tag_obj as $tag_id => $wpTag) {
            $tag_slug = (string)$wpTag->{'tag_slug'};
            $tag_name = (string)$wpTag->{'tag_name'};

            //Check if category exists
            if (in_array($tag_name, $or_tags)) {
                $display .= 'Tag ' . $tag_name . ' already exists.<br />';
            } else {
                $sql = 'INSERT INTO ' . $config['table_prefix'] . "blogtags
(tag_name,tag_seoname,tag_description)
VALUES (" . $misc->make_db_safe($tag_name) . "," . $misc->make_db_safe($tag_slug) . ",'')";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $blog_id = $conn->Insert_ID();
                //Add New Category to list
                $or_tags[$blog_id] = $tag_name;
                $display .= 'Tag ' . $tag_name . ' created.<br />';
            }
        }

        foreach ($xml->{'channel'}->{'item'} as $data) {
            /* xmlns:excerpt="http://wordpress.org/export/1.0/excerpt/"
            xmlns:content="http://purl.org/rss/1.0/modules/content/"
            xmlns:wfw="http://wellformedweb.org/CommentAPI/"
            xmlns:dc="http://purl.org/dc/elements/1.1/"
            xmlns:wp="http://wordpress.org/export/1.0/"
            */
            $ns_content = $data->children('http://purl.org/rss/1.0/modules/content/');
            $dc_content = $data->children('http://purl.org/dc/elements/1.1/');
            $wp_content = $data->children('http://wordpress.org/export/1.0/');
            if (empty($wp_content)) {
                $wp_content = $data->children('http://wordpress.org/export/1.2/');
            }


            $blog_title = (string)$data->{'title'};
            $blog_pubdate = $data->{'pubDate'};
            $blog_pubdate = strtotime($blog_pubdate);
            $blog_author = (string)$dc_content->{'creator'};
            $blog_content = (string)$ns_content->{'encoded'};
            $blog_content = nl2br($blog_content);
            $blog_type = (string)$wp_content->{'post_type'};
            $blog_status = (string)$wp_content->{'status'};
            $blog_metakeywords = '';
            $blog_metadescription = '';
            $blog_cateories = [];
            $blog_tags = [];
            $category = $data->{'category'};
            foreach ($category as $id => $tmpobj) {
                if (isset($tmpobj->attributes()->domain)) {
                    $cat_tag = $tmpobj->attributes()->domain;
                    $cat_tag_name = (string)$tmpobj->{0};
                    if ($cat_tag == 'category') {
                        //See if this category exists
                        if (in_array($cat_tag_name, $or_cats)) {
                            $cat_id = array_search($cat_tag_name, $or_cats);
                            $blog_cateories[$cat_id] = $cat_id;
                        }
                    }
                    if ($cat_tag == 'tag') {
                        //See if this category exists
                        if (in_array($cat_tag_name, $or_tags)) {
                            $tag_id = array_search($cat_tag_name, $or_tags);
                            $blog_tags[$tag_id] = $tag_id;
                        }
                    }
                }
            }
            //Make sure at least one category is selected
            if (count($blog_cateories) == 0) {
                $blog_cateories[1] = 1;
            }

            //Get Metadata
            $metadata = $wp_content->{'postmeta'};
            foreach ($metadata as $id => $tmpobj) {
                $key = (string)$tmpobj->{'meta_key'};
                $value = (string)$tmpobj->{'meta_value'};
                if ($key == '_aioseop_description') {
                    $blog_metadescription = $value;
                } elseif ($key == '_aioseop_keywords') {
                    $blog_metakeywords = $value;
                }
            }

            //INSERT BLog POSTS
            //Insert WYSIWYG Pages
            if ($blog_type == 'page') {
                //Make sure blog post does not yet exsists.
                $sql_blog_title = $misc->make_db_safe($blog_title);
                $sql_blog_content = $conn->addQ($blog_content);
                $sql_blog_metadescription = $misc->make_db_safe($blog_metadescription);
                $sql_blog_metakeywords = $misc->make_db_safe($blog_metakeywords);
                $seotitle = $page->create_seouri($blog_title, false);
                $sql_seotitle = $misc->make_db_safe($seotitle);
                $sql_blog_status = 0;
                switch ($blog_status) {
                    case 'publish':
                        $sql_blog_status = 1;
                        break;
                    case 'draft':
                        $sql_blog_status = 0;
                        break;
                    case 'review':
                        $sql_blog_status = 2;
                        break;
                }

                $sql = 'SELECT pagesmain_id FROM ' . $config['table_prefix'] . "pagesmain WHERE pagesmain_title = $sql_blog_title";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                if ($recordSet->RecordCount() == 0) {
                    //Add Blog
                    $sql = 'INSERT INTO ' . $config['table_prefix'] . "pagesmain (pagesmain_title,pagesmain_date,pagesmain_full,
pagesmain_description,pagesmain_keywords,pagesmain_published,page_seotitle)
VALUES ($sql_blog_title,$blog_pubdate,'$sql_blog_content',
$sql_blog_metadescription,$sql_blog_metakeywords,$sql_blog_status,$sql_seotitle)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $blog_id = $conn->Insert_ID();

                    $display .= 'WYSIWYG PAGE ' . htmlentities($blog_title, ENT_COMPAT, $config['charset']) . ' created.<br />';
                } else {
                    $display .= 'WYSIWYG PAGE ' . htmlentities($blog_title, ENT_COMPAT, $config['charset']) . ' already exists.<br />';
                }
            } elseif ($blog_type == 'post') {
                //Make sure blog post does not yet exsists.
                $sql_blog_title = $misc->make_db_safe($blog_title);
                $sql_blog_content = $conn->addQ($blog_content);
                $sql_blog_metadescription = $misc->make_db_safe($blog_metadescription);
                $sql_blog_metakeywords = $misc->make_db_safe($blog_metakeywords);
                $seotitle = $page->create_seouri($blog_title, false);
                $sql_seotitle = $misc->make_db_safe($seotitle);
                $sql_blog_status = 0;
                switch ($blog_status) {
                    case 'publish':
                        $sql_blog_status = 1;
                        break;
                    case 'draft':
                        $sql_blog_status = 0;
                        break;
                    case 'review':
                        $sql_blog_status = 2;
                        break;
                }

                $sql = 'SELECT blogmain_id
FROM ' . $config['table_prefix'] . "blogmain
WHERE blogmain_title = $sql_blog_title";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }

                if ($recordSet->RecordCount() == 0) {
                    //Add Blog
                    //TODO: Change Insert to map wp blog users to OR Agents.
                    $sql = 'INSERT INTO ' . $config['table_prefix'] . "blogmain
(userdb_id,blogmain_title,blogmain_date,blogmain_full,blogmain_description,blogmain_keywords,blogmain_published,blog_seotitle)
VALUES
(1,$sql_blog_title,$blog_pubdate,'$sql_blog_content',$sql_blog_metadescription,$sql_blog_metakeywords,$sql_blog_status,$sql_seotitle)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $blog_id = $conn->Insert_ID();
                    //Assign Categories
                    foreach ($blog_cateories as $cid) {
                        $sql = 'INSERT INTO ' . $config['table_prefix_no_lang'] . "blogcategory_relation
(category_id,blogmain_id)
VALUES ($cid,$blog_id)";
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                    }
                    foreach ($blog_tags as $tid) {
                        $sql = 'INSERT INTO ' . $config['table_prefix_no_lang'] . "blogtag_relation
(tag_id,blogmain_id)
VALUES ($tid,$blog_id)";
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                    }
                    //Start Importing COmmnets & Pingbacks
                    if (isset($wp_content->{'comment'})) {
                        $comments = $wp_content->{'comment'};
                        foreach ($comments as $comment) {
                            $comment_name = (string)$comment->{'comment_author'};
                            $comment_name_email = (string)$comment->{'comment_author_email'};
                            $comment_url = (string)$comment->{'comment_author_url'};
                            $comment_content = (string)$comment->{'comment_content'};
                            $comment_approved = (string)$comment->{'comment_approved'};
                            $comment_date = (string)$comment->{'comment_date'};
                            $comment_date = strtotime($comment_date);
                            $comment_type = (string)$comment->{'comment_type'};

                            if ($comment_type == 'pingback') {
                                $display .= 'Found Pingback from URL: ' . $comment_url . '<br />';
                            } else {
                                //Get the user
                                //$user_email_list=array();
                                //$user_name_list=array();
                                $user_search = array_search($comment_name_email, $user_email_list);
                                if ($user_search === false) {
                                    //No User found with that email
                                    //Create a user
                                    $_POST['user_first_name'] = 'WP IMPORT';
                                    $_POST['user_last_name'] = $comment_name;
                                    $safe_user_name = preg_replace('/[^a-zA-Z0-9]/', '', $comment_name);
                                    $_POST['edit_user_name'] = 'wp' . $safe_user_name;
                                    $_POST['user_email'] = $comment_name_email;
                                    $rand_pass = $user_manager->generatePassword();
                                    $_POST['edit_user_pass'] = $rand_pass;
                                    $_POST['edit_user_pass2'] = $rand_pass;
                                    $_POST['edit_active'] = 'yes';
                                    $_POST['edit_isAgent'] = 'no';
                                    $_POST['edit_isAdmin'] = 'no';
                                    $user_search = $user_manager->create_user();
                                    if (!is_numeric($user_search)) {
                                        $display .= $user_search;
                                    }
                                }
                                //See if the comment already exists..
                                $comment_user_id = intval($user_search);
                                $comment_approved = intval($comment_approved);
                                $safe_comment_content = $misc->make_db_safe($comment_content);
                                $sql = 'SELECT blogcomments_id
FROM ' . $config['table_prefix'] . "blogcomments
WHERE userdb_id = $comment_user_id
AND blogcomments_timestamp = $comment_date
AND blogcomments_text = $safe_comment_content
AND blogmain_id = $blog_id";
                                $recordSet = $conn->Execute($sql);
                                if (!$recordSet) {
                                    $misc->log_error($sql);
                                }
                                if ($recordSet->RecordCount() == 0) {
                                    $sql = 'INSERT INTO ' . $config['table_prefix'] . "blogcomments
(userdb_id,blogcomments_timestamp,blogcomments_text,blogmain_id,blogcomments_moderated)
VALUES ($comment_user_id, $comment_date, $safe_comment_content,$blog_id,$comment_approved)";
                                    $recordSet = $conn->Execute($sql);
                                    if (!$recordSet) {
                                        $misc->log_error($sql);
                                    }
                                    $display .= 'Blog Comment from ' . $comment_name_email . ' created.<br />';
                                } else {
                                    $display .= 'Blog Comment from ' . $comment_name_email . ' already exists.<br />';
                                }
                            }
                        }
                    }
                    //END Importing COmmnets & Pingbacks

                    $display .= 'Blog Post ' . htmlentities($blog_title, ENT_COMPAT, $config['charset']) . ' created.<br />';
                } else {
                    $blog_id = $recordSet->fields('blogmain_id');
                    $display .= 'Blog Post ' . htmlentities($blog_title, ENT_COMPAT, $config['charset']) . ' already exists.<br />';
                    //Start Importing COmmnets & Pingbacks
                    if (isset($wp_content->{'comment'})) {
                        $comments = $wp_content->{'comment'};
                        foreach ($comments as $comment) {
                            $comment_name = (string)$comment->{'comment_author'};
                            $comment_name_email = (string)$comment->{'comment_author_email'};
                            $comment_url = (string)$comment->{'comment_author_url'};
                            $comment_content = (string)$comment->{'comment_content'};
                            $comment_approved = (string)$comment->{'comment_approved'};
                            $comment_date = (string)$comment->{'comment_date'};
                            $comment_date = strtotime($comment_date);
                            $comment_type = (string)$comment->{'comment_type'};
                            $safe_comment_url = $misc->make_db_safe($comment_url);
                            $comment_approved = intval($comment_approved);

                            if ($comment_type == 'pingback' || $comment_type = 'trackback') {
                                $sql = 'SELECT blogpingback_id FROM ' . $config['table_prefix_no_lang'] . "blogpingbacks
WHERE blogpingback_source = '$safe_comment_url'
AND blogmain_id = $blog_id;";
                                $recordSet = $conn->Execute($sql);
                                if (!$recordSet) {
                                    $misc->log_error($sql);
                                }

                                if ($recordSet->RecordCount() > 0) { # Pingback already registered
                                    $display .= 'Blog Pingback from ' . $comment_url . ' already exists.<br />';
                                } else {
                                    $sql = 'INSERT INTO ' . $config['table_prefix_no_lang'] . "blogpingbacks
(blogpingback_source,blogmain_id,blogpingback_timestamp,blogcomments_moderated)
VALUES ($safe_comment_url,$blog_id,$comment_date,$comment_approved);";
                                    $recordSet = $conn->Execute($sql);
                                    if (!$recordSet) {
                                        $misc->log_error($sql);
                                    }
                                    $display .= 'Blog Pingback from ' . $comment_url . ' created.<br />';
                                }
                                /*

                                */
                            } else {
                                //Get the user
                                //$user_email_list=array();
                                //$user_name_list=array();
                                $user_search = array_search($comment_name_email, $user_email_list);
                                if ($user_search === false) {
                                    //No User found with that email
                                    //Create a user
                                    $_POST['user_first_name'] = 'WP IMPORT';
                                    $_POST['user_last_name'] = $comment_name;
                                    $safe_user_name = preg_replace('/[^a-zA-Z0-9]/', '', $comment_name);
                                    $_POST['edit_user_name'] = 'wp' . $safe_user_name;
                                    $_POST['user_email'] = $comment_name_email;
                                    $rand_pass = $user_manager->generatePassword();
                                    $_POST['edit_user_pass'] = $rand_pass;
                                    $_POST['edit_user_pass2'] = $rand_pass;
                                    $user_search = $user_manager->create_user();
                                    if (!is_numeric($user_search)) {
                                        $display .= $user_search;
                                    }
                                }
                                //See if the comment already exists..
                                $comment_user_id = intval($user_search);

                                $safe_comment_content = $misc->make_db_safe($comment_content);
                                $sql = 'SELECT blogcomments_id FROM ' . $config['table_prefix'] . "blogcomments WHERE userdb_id = $comment_user_id AND
blogcomments_timestamp = $comment_date AND blogcomments_text = '$safe_comment_content' AND blogmain_id = $blog_id";
                                $recordSet = $conn->Execute($sql);
                                if (!$recordSet) {
                                    $misc->log_error($sql);
                                }
                                if ($recordSet->RecordCount() == 0) {
                                    $sql = 'INSERT INTO ' . $config['table_prefix'] . "blogcomments
(userdb_id,blogcomments_timestamp,blogcomments_text,blogmain_id,blogcomments_moderated) VALUES
($comment_user_id, $comment_date, $safe_comment_content,$blog_id,$comment_approved)";
                                    $recordSet = $conn->Execute($sql);
                                    if (!$recordSet) {
                                        $misc->log_error($sql);
                                    }
                                    $display .= 'Blog Comment from ' . $comment_name_email . ' created.<br />';
                                } else {
                                    $display .= 'Blog Comment from ' . $comment_name_email . ' already exists.<br />';
                                }
                            }
                        }
                    }
                    //END Importing COmmnets & Pingbacks
                }
            }
        }
        return $display;
    }
}
