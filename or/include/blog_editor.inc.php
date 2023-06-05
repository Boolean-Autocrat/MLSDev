<?php


class blog_editor
{
    public function blog_edit_category_change($blog_id)
    {
        global $config, $jscript, $lang;
        $jscript .=  '{load_css_blog}' . BR;
        //Make Sure Image Folder Exists For this BLog
        if (!file_exists($config['basepath'] . '/images/blog_uploads/' . $blog_id)) {
            mkdir($config['basepath'] . '/images/blog_uploads/' . $blog_id);
        }
        $_SESSION['filemanager_basepath'] = $config['basepath'];
        $_SESSION['filemanager_baseurl'] = $config['baseurl'];
        $_SESSION['filemanager_pathpart'] = '/images/blog_uploads/' . $blog_id . '/';
    }
    public function ajax_general_settings()
    {
        global $config;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        $display = '';
        if ($security) {
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/blog_settings_general.html');
            //Load Jscript
            $yes_no = [0 => 'No', 1 => 'Yes'];

            $page->replace_tag('controlpanel_blogs_per_page', htmlentities($config['blogs_per_page'], ENT_COMPAT, $config['charset']));
            $page->replace_tag('template_url', $config['admin_template_url']);

            $html = $page->get_template_section('blog_requires_moderation_block');
            $html = $page->form_options($yes_no, $config['blog_requires_moderation'], $html);
            $page->replace_template_section('blog_requires_moderation_block', $html);

            $html = $page->get_template_section('allow_pingbacks_block');
            $html = $page->form_options($yes_no, $config['allow_pingbacks'], $html);
            $page->replace_template_section('allow_pingbacks_block', $html);

            $html = $page->get_template_section('send_url_pingbacks_block');
            $html = $page->form_options($yes_no, $config['send_url_pingbacks'], $html);
            $page->replace_template_section('send_url_pingbacks_block', $html);

            $html = $page->get_template_section('send_service_pingbacks_block');
            $html = $page->form_options($yes_no, $config['send_service_pingbacks'], $html);
            $page->replace_template_section('send_service_pingbacks_block', $html);

            $page->replace_tag('controlpanel_blog_pingback_urls', htmlentities($config['blog_pingback_urls'], ENT_COMPAT, $config['charset']));

            $page->replace_permission_tags();
            $page->replace_lang_template_tags(true);
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        }

        return $display;
    }
    public function render_category_datatable_row($html, $category_id, $category_name, $category_seoname, $category_description, $parent_id, $category_rank, $lvl = 0)
    {
        global $config, $misc, $conn;
        // Open Connection to the Control Panel Table

        $new_html = $html;
        $new_html = str_replace('{cat_name}', $category_name, $new_html);
        $new_html = str_replace('{cat_seoname}', $category_seoname, $new_html);
        $new_html = str_replace('{cat_description}', $category_description, $new_html);
        $new_html = str_replace('{cat_parent_id}', $parent_id, $new_html);
        $new_html = str_replace('{cat_id}', $category_id, $new_html);
        $cat_level_indicator = '';
        if ($lvl > 0) {
            $margin = 0;
            if ($lvl > 1) {
                $margincount = $lvl - 1;
                $margin = 16 * $margincount;
            }
            $cat_level_indicator .= '<img style="margin-left:' . $margin . 'px" src="' . $config['admin_template_url'] . '/images/blog_subdir.png">&nbsp;';
        }

        $new_html = str_replace('{cat_level_indicator}', $cat_level_indicator, $new_html);
        //Get Post Count
        $sql = 'SELECT count(blogmain_id) as post_count 
				FROM ' . $config['table_prefix_no_lang'] . 'blogcategory_relation 
				WHERE category_id = ' . $category_id;
        $recordSet2 = $conn->Execute($sql);
        if (!$recordSet2) {
            $misc->log_error($sql);
        }
        $count = $recordSet2->fields('post_count');
        $new_html = str_replace('{cat_post_count}', $count, $new_html);
        //Hide RankUP RankDown icons
        if ($category_id == 1) {
            $new_html = str_replace('{rankup_style}', 'opacity:.3;filter:alpha(opacity=30)', $new_html);
            $new_html = str_replace('{rankdown_style}', 'opacity:.3;filter:alpha(opacity=30)', $new_html);
            $new_html = str_replace('{catdelete_style}', 'opacity:.3;filter:alpha(opacity=30)', $new_html);
            $new_html = preg_replace('/\{rankup_block\}(.*?)\{\/rankup_block\}/', '', $new_html);
            $new_html = preg_replace('/\{rankdown_block\}(.*?)\{\/rankdown_block\}/', '', $new_html);
            $new_html = preg_replace('/\{catdelete_block\}(.*?)\{\/catdelete_block\}/', '', $new_html);
        //
        } else {
            if ($category_rank == 0 || ($parent_id == 0 && $category_rank == 1)) {
                $new_html = str_replace('{rankup_style}', 'opacity:.3;filter:alpha(opacity=30)', $new_html);
                $new_html = preg_replace('/\{rankup_block\}(.*?)\{\/rankup_block\}/', '', $new_html);
            }
            //Get Next Rank For this Category Group.
            $sql = 'SELECT max(category_rank) as max_rank 
					FROM ' . $config['table_prefix'] . 'blogcategory 
					WHERE parent_id = ' . $parent_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $maxrank = $recordSet->fields('max_rank');
            if ($category_rank == $maxrank) {
                $new_html = str_replace('{rankdown_style}', 'opacity:.3;filter:alpha(opacity=30)', $new_html);
                $new_html = preg_replace('/\{rankdown_block\}(.*?)\{\/rankdown_block\}/', '', $new_html);
            }
        }
        $sql = 'SELECT parent_id, category_id, category_name,category_seoname,category_description,category_rank 
				FROM ' . $config['table_prefix'] . 'blogcategory 
				WHERE parent_id = ' . $category_id . ' 
				ORDER BY category_rank ASC';
        $recordSet2 = $conn->Execute($sql);
        if (!$recordSet2) {
            $misc->log_error($sql);
        }

        while (!$recordSet2->EOF) {
            $c_lvl = $lvl + 1;
            $category_name = $recordSet2->fields('category_name');
            $parent_id = $recordSet2->fields('parent_id');
            $category_id = $recordSet2->fields('category_id');
            $category_seoname = $recordSet2->fields('category_seoname');
            $category_description = $recordSet2->fields('category_description');
            $category_rank = $recordSet2->fields('category_rank');
            $new_html  .= $this->render_category_datatable_row($html, $category_id, $category_name, $category_seoname, $category_description, $parent_id, $category_rank, $c_lvl);
            $recordSet2->MoveNext();
        }
        return $new_html;
    }

    public function render_tag_datatable_row($html, $tag_id, $tag_name, $tag_seoname, $tag_description)
    {
        global $config, $conn, $misc;

        // Open Connection to the Control Panel Table
        $new_html = $html;
        $new_html = str_replace('{tag_name}', $tag_name, $new_html);
        $new_html = str_replace('{tag_seoname}', $tag_seoname, $new_html);
        $new_html = str_replace('{tag_description}', $tag_description, $new_html);
        $new_html = str_replace('{tag_id}', $tag_id, $new_html);

        //Get Post Count
        $sql = 'SELECT count(blogmain_id) as post_count 
				FROM ' . $config['table_prefix_no_lang'] . 'blogtag_relation 
				WHERE tag_id = ' . $tag_id;
        $recordSet2 = $conn->Execute($sql);
        if (!$recordSet2) {
            $misc->log_error($sql);
        }
        $count = $recordSet2->fields('post_count');
        $new_html = str_replace('{tag_post_count}', $count, $new_html);
        //Hide RankUP RankDown icons

        return $new_html;
    }

    public function ajax_general_categories()
    {
        global $conn, $lang, $config;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        if (!$security) {
            $security = $login->verify_priv('is_blog_editor');
        }
        $display = '';
        if ($security) {
            // Open Connection to the Control Panel Table
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            include_once $config['basepath'] . '/include/blog_functions.inc.php';
            $blog_functions = new blog_functions();

            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/blog_settings_categories.html');

            $html = $page->get_template_section('category_display_block');
            $new_html = '';
            $sql = 'SELECT parent_id, category_id, category_name,category_seoname,category_description,category_rank 
					FROM ' . $config['table_prefix'] . 'blogcategory 
					WHERE parent_id = 0 
					ORDER BY category_rank ASC';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $categories = [];
            while (!$recordSet->EOF) {
                $category_name = $recordSet->fields('category_name');
                $parent_id = $recordSet->fields('parent_id');
                $category_id = $recordSet->fields('category_id');
                $category_seoname = $recordSet->fields('category_seoname');
                $category_description = $recordSet->fields('category_description');
                $category_rank = $recordSet->fields('category_rank');
                $new_html  .= $this->render_category_datatable_row($html, $category_id, $category_name, $category_seoname, $category_description, $parent_id, $category_rank);
                $recordSet->MoveNext();
            }

            $page->replace_template_section('category_display_block', $new_html);
            //Render Parent ID Block
            /*
             {parent_id_block}
                <option value="{value}"{selected}>{text}</option>
                {/parent_id_block}
                */
            //Populate the Add Category Parent Category Select
            $dumby_categories[0] = $lang['blog_top_level_parent_category'];
            $cat_html = $page->get_template_section('parent_id_block');
            $categories = $blog_functions->get_blog_categories();
            $cat_html_replace = '';
            //<input type="checkbox" name="cat_id" value="{blog_category_id}" /> {blog_category_name}
            foreach ($dumby_categories as $cat_id => $cat_name) {
                $cat_html_replace .= $this->render_category_options($cat_html, $cat_name, $cat_id, 0, $categories);
            }
            $page->replace_template_section('parent_id_block', $cat_html_replace);
            $page->replace_permission_tags();
            $page->replace_lang_template_tags(true);
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        }

        return $display;
    }

    public function ajax_general_tags()
    {
        global $conn, $config;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('edit_site_config');
        if (!$security) {
            $security = $login->verify_priv('is_blog_editor');
        }
        $display = '';
        if ($security) {
            // Open Connection to the Control Panel Table
            global $misc;
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();

            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/blog_settings_tags.html');

            $html = $page->get_template_section('tag_display_block');
            $new_html = '';
            $sql = 'SELECT tag_id, tag_name,tag_seoname,tag_description 
					FROM ' . $config['table_prefix'] . 'blogtags 
					ORDER BY tag_name ASC';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            while (!$recordSet->EOF) {
                $tag_name = $recordSet->fields('tag_name');

                $tag_id = $recordSet->fields('tag_id');
                $tag_seoname = $recordSet->fields('tag_seoname');
                $tag_description = $recordSet->fields('tag_description');
                $new_html  .= $this->render_tag_datatable_row($html, $tag_id, $tag_name, $tag_seoname, $tag_description);
                $recordSet->MoveNext();
            }

            $page->replace_template_section('tag_display_block', $new_html);

            $page->replace_permission_tags();
            $page->replace_lang_template_tags(true);
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        }
        return $display;
    }

    public function blog_edit_index()
    {
        global $config;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $display = '';
        if ($security) {
            // include global variables
            global $conn, $lang, $config, $misc;

            //Load the Core Template
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            include_once $config['basepath'] . '/include/blog_functions.inc.php';
            $blog_functions = new blog_functions();
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/blog_edit_index.html');
            //What Access Rights does user have to blogs? Access Blog Manager means they are at least a contributor.
            /*//Blog Permissions
             * 1 - Subscriber - A subscriber can read posts, comment on posts.
             * 2 - Contributor - A contributor can post and manage their own post but they cannot publish the posts. An administrator must first approve the post before it can be published.
             * 3 - Author - The Author role allows someone to publish and manage posts. They can only manage their own posts, no one else’s.
             * 4 - Editor - An editor can publish posts. They can also manage and edit other users posts. If you are looking for someone to edit your posts, you would assign the Editor role to that person.
             */
            $blog_user_type = intval($_SESSION['blog_user_type']);
            $blog_user_id = intval($_SESSION['userID']);
            //TODO: Fix permission here
            if ((($config['demo_mode'] == 1) && ($_SESSION['admin_privs'] != 'yes')) || (($blog_user_type == 2) && ($published == 1))) {
                $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
            } else {
                if (isset($_POST['delete'])) {
                    if (isset($_POST['blogID']) && $_POST['blogID'] != 0) {
                        // Delete blog
                        $blogID = intval($_POST['blogID']);
                        $sql = 'DELETE FROM ' . $config['table_prefix'] . 'blogmain 
								WHERE blogmain_id = ' . $blogID;
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                        $_POST['blogID'] = '';
                    }
                }
            }
            //Replace Status Counts
            //{blog_edit_status_all_count}
            if ($blog_user_type == 4 || $_SESSION['admin_privs'] == 'yes') {
                $sql = 'SELECT count(blogmain_id) as blogcount  
						FROM ' . $config['table_prefix'] . 'blogmain';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $count_all = $recordSet->fields('blogcount');

                $sql = 'SELECT count(blogmain_id) as blogcount  
						FROM ' . $config['table_prefix'] . 'blogmain 
						WHERE blogmain_published = 1';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $count_published = $recordSet->fields('blogcount');

                $sql = 'SELECT count(blogmain_id) as blogcount  
						FROM ' . $config['table_prefix'] . 'blogmain 
						WHERE blogmain_published = 0';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $count_draft = $recordSet->fields('blogcount');

                $sql = 'SELECT count(blogmain_id) as blogcount  
						FROM ' . $config['table_prefix'] . 'blogmain 
						WHERE blogmain_published = 2';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $count_review = $recordSet->fields('blogcount');
            } else {
                $sql = 'SELECT count(blogmain_id) as blogcount  
						FROM ' . $config['table_prefix'] . 'blogmain 
						WHERE userdb_id = ' . $blog_user_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $count_all = $recordSet->fields('blogcount');

                $sql = 'SELECT count(blogmain_id) as blogcount  
						FROM ' . $config['table_prefix'] . 'blogmain 
						WHERE blogmain_published = 1 
						AND userdb_id = ' . $blog_user_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $count_published = $recordSet->fields('blogcount');

                $sql = 'SELECT count(blogmain_id) as blogcount  
						FROM ' . $config['table_prefix'] . 'blogmain 
						WHERE blogmain_published = 0 AND userdb_id = ' . $blog_user_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $count_draft = $recordSet->fields('blogcount');
                $sql = 'SELECT count(blogmain_id) as blogcount  
						FROM ' . $config['table_prefix'] . 'blogmain 
						WHERE blogmain_published = 2 
						AND userdb_id = ' . $blog_user_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $count_review = $recordSet->fields('blogcount');
            }

            $page->replace_tag('blog_edit_status_all_count', $count_all);
            $page->replace_tag('blog_edit_status_published_count', $count_published);
            $page->replace_tag('blog_edit_status_draft_count', $count_draft);
            $page->replace_tag('blog_edit_status_review_count', $count_review);
            //Get Status
            //http://localhost/open-realty/admin/index.php?action=edit_blog&amp;status=Published
            $statusSQL = '';
            if (isset($_GET['status']) && $_GET['status'] == 'Published') {
                $statusSQL = 'blogmain_published = 1';
            } elseif (isset($_GET['status']) && $_GET['status'] == 'Draft') {
                $statusSQL = 'blogmain_published = 0';
            } elseif (isset($_GET['status']) && $_GET['status'] == 'Review') {
                $statusSQL = 'blogmain_published = 2';
            }

            //Show Blog List
            if ($blog_user_type == 4 || $_SESSION['admin_privs'] == 'yes') {
                if (!empty($statusSQL)) {
                    $sql = 'SELECT blogmain_title, blogmain_id, userdb_id, blogmain_date, blogmain_published, blogmain_keywords  
							FROM ' . $config['table_prefix'] . 'blogmain 
							WHERE ' . $statusSQL . ' ORDER BY blogmain_date DESC';
                } else {
                    $sql = 'SELECT blogmain_title, blogmain_id, userdb_id, blogmain_date, blogmain_published, blogmain_keywords  
							FROM ' . $config['table_prefix'] . 'blogmain  
							ORDER BY blogmain_date DESC';
                }
            } else {
                if (!empty($statusSQL)) {
                    $sql = 'SELECT blogmain_title, blogmain_id, userdb_id, blogmain_date, blogmain_published, blogmain_keywords  
							FROM ' . $config['table_prefix'] . 'blogmain 
							WHERE userdb_id = ' . $blog_user_id . ' 
							AND ' . $statusSQL . '  
							ORDER BY blogmain_date DESC';
                } else {
                    $sql = 'SELECT blogmain_title, blogmain_id, userdb_id, blogmain_date, blogmain_published, blogmain_keywords  
							FROM ' . $config['table_prefix'] . 'blogmain 
							WHERE userdb_id = ' . $blog_user_id . ' 
							ORDER BY blogmain_date DESC';
                }
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

            $limit_str = $_GET['cur_page'] * $config['blogs_per_page'];
            $recordSet = $conn->SelectLimit($sql, $config['blogs_per_page'], $limit_str);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $blog_edit_template = '';

            while (!$recordSet->EOF) {
                $blog_edit_template .= $page->get_template_section('blog_edit_item_block');
                //echo $blog_edit_template;
                $title = $recordSet->fields('blogmain_title');
                $blogmain_id = $recordSet->fields('blogmain_id');
                $author_id = $recordSet->fields('userdb_id');
                $keywords = $recordSet->fields('blogmain_keywords');
                $blog_date = $recordSet->fields('blogmain_date');
                $blog_published = $recordSet->fields('blogmain_published');
                $comment_count = $blog_functions->get_blog_comment_count($blogmain_id);
                $blog_date = $misc->convert_timestamp($blog_date, true);
                //Get Author
                include_once $config['basepath'] . '/include/user.inc.php';
                $user = new user();
                $author_name = $user->get_user_single_item('userdb_user_last_name', $author_id) . ', ' . $user->get_user_single_item('userdb_user_first_name', $author_id);

                $blog_edit_template = $page->parse_template_section($blog_edit_template, 'blog_edit_item_title', $title);
                $blog_edit_template = $page->parse_template_section($blog_edit_template, 'blog_edit_item_id', $blogmain_id);
                $blog_edit_template = $page->parse_template_section($blog_edit_template, 'blog_edit_item_commentcount', $comment_count);
                /*<td>{blog_edit_item_author}</td>
                 <td>{blog_edit_item_keywords}</td>
                 <td>{blog_edit_item_commentcount}</td>
                 <td>{blog_edit_item_date}</td>
                 */
                $blog_edit_template = $page->parse_template_section($blog_edit_template, 'blog_edit_item_author', $author_name);
                $blog_edit_template = $page->parse_template_section($blog_edit_template, 'blog_edit_item_date', $blog_date);
                $blog_edit_template = $page->parse_template_section($blog_edit_template, 'blog_edit_item_keywords', $keywords);
                switch ($blog_published) {
                    case 0:
                        $blog_edit_template = $page->parse_template_section($blog_edit_template, 'blog_edit_item_published', $lang['blog_draft']);
                        break;
                    case 1:
                        $blog_edit_template = $page->parse_template_section($blog_edit_template, 'blog_edit_item_published', $lang['blog_published']);

                        break;
                    case 2:
                        $blog_edit_template = $page->parse_template_section($blog_edit_template, 'blog_edit_item_published', $lang['blog_review']);

                        break;
                }

                $recordSet->MoveNext();
            }
            /*
             * td>{blog_edit_item_title}</td>
             <td>{blog_edit_item_author}</td>
             <td>{blog_edit_item_keywords}</td>
             <td>{blog_edit_item_commentcount}</td>
             <td>{blog_edit_item_date}</td>
             */
        }

        //Next Prev
        $next_prev = $misc->next_prev($num_rows, $_GET['cur_page'], '', 'blog', true);
        $page->replace_tag('next_prev', $next_prev);
        $page->replace_template_section('blog_edit_item_block', $blog_edit_template);
        $page->replace_lang_template_tags(true);
        $page->replace_permission_tags();
        $page->auto_replace_tags('', true);
        $display .= $page->return_page();

        return $display;
    }
    public function edit_blog_post_tags($return_most_used_tags_only = false)
    {
        global $lang, $config;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $display = '';
        if ($security) {
            //Load the Core Template
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            include_once $config['basepath'] . '/include/blog_functions.inc.php';
            $blog_functions = new blog_functions();
            $blogID = $_SESSION['blogID'];
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/blog_edit_post_tags.html');
            //Get Assigned Tags
            $assigned_tags = $blog_functions->get_blog_tag_assignment($blogID);
            $tag_html = $page->get_template_section('blog_tags_block');
            $tag_html_replace = '';
            //<input type="checkbox" name="cat_id" value="{blog_category_id}" /> {blog_category_name}
            foreach ($assigned_tags as $tag_id => $tag_info) {
                $tag_html_replace .= $tag_html;
                $tag_html_replace = $page->parse_template_section($tag_html_replace, 'blog_tag_id', $tag_id);
                $tag_html_replace = $page->parse_template_section($tag_html_replace, 'blog_tag_name', $tag_info['tag_name']);
                ;
            }
            $page->replace_template_section('blog_tags_block', $tag_html_replace);

            //Get Popular Tags
            $assigned_tags = $blog_functions->get_blog_populartags($blogID);
            $tag_html = $page->get_template_section('blog_most_used_tags_block');
            $tag_html_replace = '';
            //<input type="checkbox" name="cat_id" value="{blog_category_id}" /> {blog_category_name}
            foreach ($assigned_tags as $tag_id => $tag_array) {
                $tag_name = $tag_array['tag_name'];
                $tag_fontsize = $tag_array['tag_fontsize'];
                $tag_html_replace .= $tag_html;
                $tag_html_replace = $page->parse_template_section($tag_html_replace, 'blog_tag_id', $tag_id);
                $tag_html_replace = $page->parse_template_section($tag_html_replace, 'blog_tag_name', $tag_name);
                $tag_html_replace = $page->parse_template_section($tag_html_replace, 'blog_tag_fontsize', $tag_fontsize);
            }
            $page->replace_template_section('blog_most_used_tags_block', $tag_html_replace);
            if ($return_most_used_tags_only === true) {
                return $tag_html_replace;
            }
            $page->replace_lang_template_tags();
            $page->replace_css_template_tags();
            $page->auto_replace_tags('', true);
            $display = $page->return_page();
        } else {
            $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return $display;
    }
    public function edit_blog_post_categories()
    {
        global $lang, $config;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $display = '';

        if ($security) {
            //Load the Core Template
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            include_once $config['basepath'] . '/include/blog_functions.inc.php';
            $blog_functions = new blog_functions();
            $blogID = $_SESSION['blogID'];
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/blog_edit_post_categories.html');
            $categories = $blog_functions->get_blog_categories();
            $top_cats = $categories[0];
            $cat_html = $page->get_template_section('blog_category_block');
            $assigned_cats = $blog_functions->get_blog_categories_assignment($blogID);
            $cat_html_replace = '';
            //<input type="checkbox" name="cat_id" value="{blog_category_id}" /> {blog_category_name}
            foreach ($top_cats as $cat_id => $cat_name) {
                $cat_html_replace .= $this->render_category_checkbox($cat_html, $cat_name, $cat_id, 0, $categories, $assigned_cats);
            }
            $page->replace_template_section('blog_category_block', $cat_html_replace);
            if (isset($_GET['container']) && $_GET['container'] == 'blog_cats') {
                return $cat_html_replace;
            }

            //Populate the Add Category Parent Category Select
            $dumby_categories[0] = $lang['blog_top_level_parent_category'];
            $cat_html = $page->get_template_section('add_blog_category_parent_block');
            //$assigned_cats = $blog_functions->get_blog_categories_assignment($blogID);
            $cat_html_replace = '';
            //<input type="checkbox" name="cat_id" value="{blog_category_id}" /> {blog_category_name}
            foreach ($dumby_categories as $cat_id => $cat_name) {
                $cat_html_replace .= $this->render_category_options($cat_html, $cat_name, $cat_id, 0, $categories);
            }
            $page->replace_template_section('add_blog_category_parent_block', $cat_html_replace);
            if (isset($_GET['container']) && $_GET['container'] == 'blog_cat_parent') {
                return $cat_html_replace;
            }

            //Show Most popular categories
            $categories = $blog_functions->get_blog_popularcategories();
            $cat_html = $page->get_template_section('blog_popularcategory_block');
            $assigned_cats = $blog_functions->get_blog_categories_assignment($blogID);
            $cat_html_replace = '';
            //<input type="checkbox" name="cat_id" value="{blog_category_id}" /> {blog_category_name}
            foreach ($categories as $cat_id => $cat_name) {
                $cat_html_replace .= $cat_html;
                if (in_array($cat_id, $assigned_cats)) {
                    $cat_html_replace = $page->parse_template_section($cat_html_replace, 'blog_category_checked', 'checked="checked"');
                } else {
                    $cat_html_replace = $page->parse_template_section($cat_html_replace, 'blog_category_checked', '');
                }
                $cat_html_replace = $page->parse_template_section($cat_html_replace, 'blog_category_id', $cat_id);
                $cat_html_replace = $page->parse_template_section($cat_html_replace, 'blog_category_name', $cat_name);
                ;
            }
            $page->replace_template_section('blog_popularcategory_block', $cat_html_replace);

            $page->replace_permission_tags();
            $page->replace_lang_template_tags();
            $page->replace_css_template_tags();
            $page->auto_replace_tags('', true);
            $display = $page->return_page();
        } else {
            $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return $display;
    }
    /**
     * **************************************************************************\
     * function blog_edit() - Display's the blog editor                         *
     * \**************************************************************************
     */
    public function blog_edit()
    {
        global $conn, $lang, $config, $jscript;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $display = '';

        if ($security) {
            global $misc;
            //Load the Core Template
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();

            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/blog_edit_post.html');
            $blog_user_type = intval($_SESSION['blog_user_type']);
            $blog_user_id = intval($_SESSION['userID']);
            $html = '';
            if (isset($_GET['id'])) {
                // Save blogID to Session for Image Upload Plugin
                $_SESSION['blogID'] = intval($_GET['id']);
                $blogID = intval($_GET['id']);
                // Pull the blog from the database
                $page->replace_tag('blog_id', $blogID);
                $sql = 'SELECT userdb_id, blogmain_full, blogmain_full_autosave, blogmain_title, blogmain_description, blogmain_keywords,blogmain_published,blog_seotitle  
						FROM ' . $config['table_prefix'] . 'blogmain 
						WHERE blogmain_id = ' . $blogID;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $blogIsDirty = false;
                if ($recordSet->fields('blogmain_full_autosave') == '') {
                    $html = $recordSet->fields('blogmain_full');
                } else {
                    $html = $recordSet->fields('blogmain_full_autosave');
                    $blogIsDirty = true;
                }
                if ($config['controlpanel_mbstring_enabled'] == 0) {
                    // MBSTRING NOT ENABLED
                    $html = htmlentities($html, ENT_QUOTES, $config['charset'], false);
                } else {
                    // MBSTRING ENABLED
                    $html = mb_convert_encoding($html, 'HTML-ENTITIES', $config['charset']);
                    $html = htmlentities($html, ENT_QUOTES, $config['charset'], false);
                }
                $page->replace_lang_template_tags(true);
                //Deal with Template Tag
                $html = str_replace('{template_url}', $config['template_url'], $html);
                $page->replace_tag('blog_html', $html);
                $title = $recordSet->fields('blogmain_title');
                $description = $recordSet->fields('blogmain_description');
                $published = intval($recordSet->fields('blogmain_published'));
                $blog_owner = intval($recordSet->fields('userdb_id'));
                //Make sure user is and editor or the blog owner.
                if ($blog_owner != $blog_user_id && $blog_user_type != 4) {
                    $display = $lang['listing_editor_permission_denied'] . '<br />';
                    return $display;
                }

                $keywords = $recordSet->fields('blogmain_keywords');
                $seotitle = $recordSet->fields('blog_seotitle');
                $page->replace_tag('blog_title', $title);
                $page->replace_tag('blog_description', $description);
                $page->replace_tag('blog_keywords', $keywords);
                $page->replace_tag('baseurl', $config['baseurl']);
                $page->replace_tag('blog_seotitle', $seotitle);
                if ($blogIsDirty) {
                    $page->replace_tag('blog_revert_button_state', '');
                } else {
                    $page->replace_tag('blog_revert_button_state', 'display:none;');
                }
                //Handle Publish Status
                //$page->replace_tag('blog_published', $published);
                switch ($published) {
                    case 0:
                        $page->replace_tag('blog_published_lang', $lang['blog_draft']);
                        break;
                    case 1:
                        $page->replace_tag('blog_published_lang', $lang['blog_published']);
                        break;
                    case 2:
                        $page->replace_tag('blog_published_lang', $lang['blog_review']);
                        break;
                }
                //Show Blog Status Options
                /*{blog_status_option_block}
                 <option value="{blog_status_value}">{blog_status_text}</option>
                 {/blog_status_option_block}*/
                $status_html = $page->get_template_section('blog_status_option_block');
                $status_html_replace = '';
                //Build Draft Option
                if (($blog_user_type == 2 && $published != 1) || $blog_user_type > 2) {
                    $status_html_replace .= $status_html;
                    if ($published == 0) {
                        $status_html_replace = $page->parse_template_section($status_html_replace, 'blog_status_selected', 'selected="selected"');
                    } else {
                        $status_html_replace = $page->parse_template_section($status_html_replace, 'blog_status_selected', '');
                    }
                    $status_html_replace = $page->parse_template_section($status_html_replace, 'blog_status_value', '0');
                    $status_html_replace = $page->parse_template_section($status_html_replace, 'blog_status_text', $lang['blog_draft']);
                }
                //Build Review Option
                if (($blog_user_type == 2 && $published != 1) || $blog_user_type > 2) {
                    $status_html_replace .= $status_html;
                    if ($published == 2) {
                        $status_html_replace = $page->parse_template_section($status_html_replace, 'blog_status_selected', 'selected="selected"');
                    } else {
                        $status_html_replace = $page->parse_template_section($status_html_replace, 'blog_status_selected', '');
                    }
                    $status_html_replace = $page->parse_template_section($status_html_replace, 'blog_status_value', '2');
                    $status_html_replace = $page->parse_template_section($status_html_replace, 'blog_status_text', $lang['blog_review']);
                }
                //Build Published Option
                if (($blog_user_type == 2 && $published == 1) || $blog_user_type > 2) {
                    $status_html_replace .= $status_html;
                    if ($published == 1) {
                        $status_html_replace = $page->parse_template_section($status_html_replace, 'blog_status_selected', 'selected="selected"');
                    } else {
                        $status_html_replace = $page->parse_template_section($status_html_replace, 'blog_status_selected', '');
                    }
                    $status_html_replace = $page->parse_template_section($status_html_replace, 'blog_status_value', '1');
                    $status_html_replace = $page->parse_template_section($status_html_replace, 'blog_status_text', $lang['blog_published']);
                }
                $page->replace_template_section('blog_status_option_block', $status_html_replace);

                /*//Blog Permissions
                 * 1 - Subscriber - A subscriber can read posts, comment on posts.
                 * 2 - Contributor - A contributor can post and manage their own post but they cannot publish the posts. An administrator must first approve the post before it can be published.
                 * 3 - Author - The Author role allows someone to publish and manage posts. They can only manage their own posts, no one else’s.
                 * 4 - Editor - An editor can publish posts. They can also manage and edit other users posts. If you are looking for someone to edit your posts, you would assign the Editor role to that person.
                 */

                //Show Blog Categories.
                //Load JS to Handle Cat Changes.
                $this->blog_edit_category_change($blogID);

                $article_url = $page->magicURIGenerator('blog', $blogID, true);
                $page->replace_tag('blog_article_url', $article_url);
                $page->replace_tag('blog_article_url_display', urldecode($article_url));
            }
            //Show Link to Blog Manager
            $page->replace_tag('blog_manager_url', 'index.php?action=edit_blog');
            $page->replace_tag('blog_edit_action', 'index.php?action=edit_blog_post');
            //Remove Publish & Delete Buttons for Contributorss on live blogs, as it bypasses editors. Also remove buttons if in Demo Mode.
            if ((($config['demo_mode'] == 1) && ($_SESSION['admin_privs'] != 'yes')) || (($blog_user_type == 2) && ($published == 1))) {
                $page->page = $page->cleanup_template_block('blog_update', $page->page);
                $page->page = $page->remove_template_block('blog_delete', $page->page);
            } else {
                $page->page = $page->cleanup_template_block('blog_update', $page->page);
                $page->page = $page->cleanup_template_block('blog_delete', $page->page);
            }
            $page->replace_permission_tags();

            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        } else {
            $display .= '<div class="error_text">' . $lang['access_denied'] . '</div>';
        }
        return $display;
    }
    public function render_category_checkbox($html, $cat_name, $cat_id, $cat_lvl, $all_categories, $assigned_cats)
    {
        global $config;
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        $cat_lvl_child = $cat_lvl;
        $cat_lvl_child++;
        $cat_html_replace = $html;
        if (in_array($cat_id, $assigned_cats)) {
            $cat_html_replace = $page->parse_template_section($cat_html_replace, 'blog_category_checked', 'checked="checked"');
        } else {
            $cat_html_replace = $page->parse_template_section($cat_html_replace, 'blog_category_checked', '');
        }
        $cat_html_replace = $page->parse_template_section($cat_html_replace, 'blog_category_id', $cat_id);
        $cat_html_replace = $page->parse_template_section($cat_html_replace, 'blog_category_name', $cat_name);
        $cat_html_replace = $page->parse_template_section($cat_html_replace, 'child_lvl', 'c' . intval($cat_lvl));
        $child_cats = [];
        $child_html = '';
        if (isset($all_categories[$cat_id])) {
            $child_cats = $all_categories[$cat_id];
        }
        foreach ($child_cats as $ccat_id => $ccat_name) {
            $child_html .= $this->render_category_checkbox($html, $ccat_name, $ccat_id, $cat_lvl_child, $all_categories, $assigned_cats);
        }
        $cat_html_replace .= $child_html;
        return $cat_html_replace;
    }
    public function render_category_options($html, $cat_name, $cat_id, $cat_lvl, $all_categories)
    {
        global $config;
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        if ($cat_id == 1) {
            return '';
        }
        $cat_lvl_child = $cat_lvl;
        $cat_lvl_child++;
        $cat_html_replace = $html;
        $cat_html_replace = $page->parse_template_section($cat_html_replace, 'blog_category_id', $cat_id);
        $cat_html_replace = $page->parse_template_section($cat_html_replace, 'blog_category_name', $cat_name);
        $child_lvl_html = '';
        for ($x = 0; $x < $cat_lvl; $x++) {
            $child_lvl_html .= '&nbsp;&nbsp;';
        }
        $cat_html_replace = $page->parse_template_section($cat_html_replace, 'child_lvl', $child_lvl_html);
        $child_cats = [];
        $child_html = '';
        if (isset($all_categories[$cat_id])) {
            $child_cats = $all_categories[$cat_id];
        }
        foreach ($child_cats as $ccat_id => $ccat_name) {
            $child_html .= $this->render_category_options($html, $ccat_name, $ccat_id, $cat_lvl_child, $all_categories);
        }
        $cat_html_replace .= $child_html;
        return $cat_html_replace;
    }

    public function edit_post_comments()
    {
        global $conn, $lang, $config, $jscript;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');
        $display = '';
        $blog_user_type = intval($_SESSION['blog_user_type']);
        if ($security) {
            global $misc;
            //Load the Core Template
            include_once $config['basepath'] . '/include/core.inc.php';
            $page = new page_admin();
            include_once $config['basepath'] . '/include/user.inc.php';
            $userclass = new user();
            include_once $config['basepath'] . '/include/blog_functions.inc.php';
            $blog_functions = new blog_functions();
            //Load TEmplate File
            $page->load_page($config['admin_template_path'] . '/blog_edit_comments.html');
            // Do we need to save?
            if (isset($_GET['id'])) {
                $post_id = intval($_GET['id']);
                //Get Blog Post Information
                $blog_title = $blog_functions->get_blog_title($post_id);
                $page->page = $page->parse_template_section($page->page, 'blog_title', $blog_title);
                $blog_author = $blog_functions->get_blog_author($post_id);
                $page->page = $page->parse_template_section($page->page, 'blog_author', $blog_author);
                $blog_date_posted = $blog_functions->get_blog_date($post_id);
                $page->page = $page->parse_template_section($page->page, 'blog_date_posted', $blog_date_posted);
                //Handle any deletions and comment approvals before we load the comments
                if (isset($_GET['caction']) && $_GET['caction'] == 'delete') {
                    if (isset($_GET['cid'])) {
                        $cid = intval($_GET['cid']);
                        //Do permission checks.
                        if ($blog_user_type < 4) {
                            //Throw Error
                            $display .= '<div class="error_message">' . $lang['blog_permission_denied'] . '</div><br />';
                            unset($_GET['caction']);
                            $display .= $this->edit_post_comments();
                            return $display;
                        }
                        //Delete
                        $sql = 'DELETE FROM ' . $config['table_prefix'] . 'blogcomments 
								WHERE blogcomments_id = ' . $cid . ' 
								AND blogmain_id = ' . $post_id;
                        //Load Record Set
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                    }
                }
                if (isset($_GET['caction']) && $_GET['caction'] == 'approve') {
                    if (isset($_GET['cid'])) {
                        $cid = intval($_GET['cid']);
                        //Do permission checks.
                        if ($blog_user_type < 4) {
                            //Throw Error
                            $display .= '<div class="error_message">' . $lang['blog_permission_denied'] . '</div><br />';
                            unset($_GET['caction']);
                            $display .= $this->edit_post_comments();
                            return $display;
                        }
                        //Delete
                        $sql = 'UPDATE ' . $config['table_prefix'] . 'blogcomments 
								SET blogcomments_moderated = 1 
								WHERE blogcomments_id = ' . $cid . ' 
								AND blogmain_id = ' . $post_id;
                        //Load Record Set
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                    }
                }

                //Ok Load the comments.
                $sql = 'SELECT * FROM ' . $config['table_prefix'] . 'blogcomments WHERE blogmain_id = ' . $post_id . ' ORDER BY blogcomments_timestamp ASC';
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

                $limit_str = $_GET['cur_page'] * $config['blogs_per_page'];
                $recordSet = $conn->SelectLimit($sql, $config['blogs_per_page'], $limit_str);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $blog_comment_template = '';
                while (!$recordSet->EOF) {
                    //Load DB Values
                    $comment_author_id = $recordSet->fields('userdb_id');
                    $blogcomments_id = $recordSet->fields('blogcomments_id');
                    $blogcomments_moderated = $recordSet->fields('blogcomments_moderated');
                    $blogcomments_timestamp = $recordSet->fields('blogcomments_timestamp');
                    $blogcomments_text = html_entity_decode($recordSet->fields('blogcomments_text'), ENT_NOQUOTES, $config['charset']);
                    //Load Template Block
                    $blog_comment_template .= $page->get_template_section('blog_article_comment_item_block');
                    //Lookup Blog Author..
                    $author_type = $userclass->get_user_type($comment_author_id);
                    if ($author_type == 'member') {
                        $author_display = $userclass->get_user_single_item('userdb_user_name', $comment_author_id);
                    } else {
                        $author_display = $userclass->get_user_single_item('userdb_user_last_name', $comment_author_id) . ', ' . $userclass->get_user_single_item('userdb_user_first_name', $comment_author_id);
                    }
                    $blog_comment_template = $page->parse_template_section($blog_comment_template, 'blog_comment_author', $author_display);
                    if ($config['date_format'] == 1) {
                        $format = 'm/d/Y';
                    } elseif ($config['date_format'] == 2) {
                        $format = 'Y/d/m';
                    } elseif ($config['date_format'] == 3) {
                        $format = 'd/m/Y';
                    }
                    $blog_comment_date_posted = date($format, "$blogcomments_timestamp");
                    $blog_comment_template = $page->parse_template_section($blog_comment_template, 'blog_comment_date_posted', $blog_comment_date_posted);
                    $blog_comment_template = $page->parse_template_section($blog_comment_template, 'blog_comment_text', $blogcomments_text);
                    //Add Delete COmment Link
                    //{blog_comment_delete_url}
                    $blog_comment_delete_url = 'index.php?action=edit_blog_post_comments&id=' . $post_id . '&caction=delete&cid=' . $blogcomments_id;
                    $blog_comment_template = $page->parse_template_section($blog_comment_template, 'blog_comment_delete_url', $blog_comment_delete_url);
                    $blog_comment_approve_url = 'index.php?action=edit_blog_post_comments&id=' . $post_id . '&caction=approve&cid=' . $blogcomments_id;
                    $blog_comment_template = $page->parse_template_section($blog_comment_template, 'blog_comment_approve_url', $blog_comment_approve_url);
                    //Do Security Checks
                    if ($blog_user_type < 4) {
                        $blog_comment_template = $page->remove_template_block('blog_article_comment_approve', $blog_comment_template);
                        $blog_comment_template = $page->remove_template_block('blog_article_comment_delete', $blog_comment_template);
                    }
                    //Handle Moderation
                    if ($blogcomments_moderated == 1) {
                        $blog_comment_template = $page->remove_template_block('blog_article_comment_approve', $blog_comment_template);
                    } else {
                        $blog_comment_template = $page->cleanup_template_block('blog_article_comment_approve', $blog_comment_template);
                    }

                    $recordSet->MoveNext();
                }
                $page->replace_template_section('blog_article_comment_item_block', $blog_comment_template);
                $next_prev = $misc->next_prev($num_rows, $_GET['cur_page'], '', 'blog', true);
                $page->replace_tag('next_prev', $next_prev);
                $page->replace_permission_tags();
                $page->auto_replace_tags('', true);
                $display .= $page->return_page();
            }
        }
        return $display;
    }
}
