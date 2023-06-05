<?php


class blog_display
{
    public function display_blog_index()
    {
        global $conn, $config, $misc, $meta_index,$blog_id;

        //Load the Core Template
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $addons = $page->load_addons();
        $addon_fields = $page->get_addon_template_field_list($addons);
        include_once $config['basepath'] . '/include/blog_functions.inc.php';
        $blog_functions = new blog_functions();
        // Make Sure we passed the PageID
        $display = '';
        if (isset($_GET['tag_id'])) {
            //Getting only Post that have a tag in them.
            $tag_sql=' AND blogmain_id IN (SELECT blogmain_id FROM '.$config['table_prefix_no_lang'].'blogtag_relation WHERE tag_id = '.intval($_GET['tag_id']).') ';
            //Add No index metatag.
            $meta_index=false;
        } else {
            $tag_sql='';
        }

        if (isset($_GET['cat_id'])) {
            //Getting only Post that have a tag in them.
            $cat_sql=' AND blogmain_id IN (SELECT blogmain_id FROM '.$config['table_prefix_no_lang'].'blogcategory_relation WHERE category_id = '.intval($_GET['cat_id']).') ';
            //Add No index metatag.
            $meta_index=false;
        } else {
            $cat_sql='';
        }

        //Deal with Dates
        if (isset($_GET['year']) && isset($_GET['month'])) {
            //First Day of Month
            $first_day = strtotime('01 '.$_GET['month'].' '.$_GET['year']);
            $month_number = date('n', $first_day);
            $month_number++;
            $last_day = strtotime($_GET['year'].'-'.$month_number.'-00 23:59:59');
            if ($first_day == '' || $last_day =='') {
                $date_sql='';
                $meta_index=false;
            } else {
                $date_sql=' AND (blogmain_date >= '.$first_day.' AND blogmain_date <= '.$last_day.') ';
                //Add No index metatag.
                $meta_index=false;
            }
        } else {
            $date_sql='';
        }
        //Get Number of blog posts.
        $sql = 'SELECT blogmain_id FROM ' . $config['table_prefix'] . "blogmain WHERE blogmain_published = 1 $tag_sql $cat_sql $date_sql ORDER BY blogmain_date DESC";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $num_rows = $recordSet->RecordCount();

        $sql = 'SELECT blogmain_full,blogmain_id,userdb_id 
				FROM ' . $config['table_prefix'] . "blogmain 
				WHERE blogmain_published = 1 $tag_sql $cat_sql $date_sql 
				ORDER BY blogmain_date DESC";

        if (!isset($_GET['cur_page'])) {
            $_GET['cur_page']=0;
        }
        $limit_str = intval($_GET['cur_page']) * $config['blogs_per_page'];
        $next_prev = $misc->next_prev($num_rows, intval($_GET['cur_page']), '');
        $next_prev_bottom = $misc->next_prev($num_rows, intval($_GET['cur_page']), '', 'bottom');
        $recordSet = $conn->SelectLimit($sql, $config['blogs_per_page'], $limit_str);
        if (!$recordSet) {
            $misc->log_error($sql);
        }

        $page->load_page($config['template_path'] . '/blog_index.html');
        //Display Tag HEader if needed.
        if (isset($_GET['cat_id'])) {
            //Get Tag
            $cat_name = $blog_functions->get_blog_category_name(intval($_GET['cat_id']));
            $cat_description = $blog_functions->get_blog_category_description(intval($_GET['cat_id']));
            $page->page = $page->parse_template_section($page->page, 'cat_name', htmlentities($cat_name, ENT_COMPAT, $config['charset']));
            $page->page = $page->parse_template_section($page->page, 'cat_description', htmlentities($cat_description, ENT_COMPAT, $config['charset']));
            $page->page = $page->cleanup_template_block('cat', $page->page);
        } else {
            $page->page = $page->remove_template_block('cat', $page->page);
        }
        //Display Tag HEader if needed.
        if (isset($_GET['tag_id'])) {
            //Get Tag
            $tag_name = $blog_functions->get_tag_name(intval($_GET['tag_id']));
            $tag_description = $blog_functions->get_tag_description(intval($_GET['tag_id']));
            $page->page = $page->parse_template_section($page->page, 'tag_name', htmlentities($tag_name, ENT_COMPAT, $config['charset']));
            $page->page = $page->parse_template_section($page->page, 'tag_description', htmlentities($tag_description, ENT_COMPAT, $config['charset']));
            $page->page = $page->cleanup_template_block('tag', $page->page);
        } else {
            $page->page = $page->remove_template_block('tag', $page->page);
        }
        $blog_entry_template = '';
        while (!$recordSet->EOF) {
            $blog_entry_template .= $page->get_template_section('blog_entry_block');
            $blog_author_id = $recordSet->fields('userdb_id');
            //Get Fields
            $blog_id = $recordSet->fields('blogmain_id');
            $full = $recordSet->fields('blogmain_full');
            //Start Replacing Tags
            $blog_title = $blog_functions->get_blog_title($blog_id);
            $blog_entry_template = $page->parse_template_section($blog_entry_template, 'blog_title', $blog_title);

            //Deal with Code Tags
            preg_match_all('/<code>(.*?)<\/code>/is', $full, $code_tags);
            //echo '<pre>'.print_r($code_tags).'</pre>';
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

            //Handle blog_listing_# blocks
            preg_match_all('/{(blog_listing_[\d]*)}/m', $full, $matches);
            $blog_listings=array_unique($matches[1]);
            foreach ($blog_listings as $blog_listing) {
                $listing_template = $page->get_template_section($blog_listing, $full);
                //Skip incomplete blogs, tags will just be stripped
                if ($listing_template==false) {
                    continue;
                }
                //Get Listing ID
                preg_match('/blog_listing_([\d]*)/', $blog_listing, $id_match);
                $listing_id = $id_match[1];
                $listing_template = $page->replace_listing_field_tags($listing_id, $listing_template);
                $full = $page->replace_template_section($blog_listing, $listing_template, $full);
            }
            $summary_endpos = strpos($full, '<hr');
            if ($summary_endpos!==false) {
                $summary=substr($full, 0, $summary_endpos);
            } else {
                $summary=$full;
            }
            $blog_entry_template = $page->parse_template_section($blog_entry_template, 'blog_id', $blog_id);

            $blog_entry_template = $page->parse_template_section($blog_entry_template, 'blog_summary', $summary);
            $blog_author=$blog_functions->get_blog_author($blog_id);

            $blog_entry_template = $page->replace_user_field_tags($blog_author_id, $blog_entry_template, 'blog_author');
            $blog_entry_template = $page->parse_template_section($blog_entry_template, 'blog_author', $blog_author);
            $blog_comment_count=$blog_functions->get_blog_comment_count($blog_id);
            $blog_entry_template = $page->parse_template_section($blog_entry_template, 'blog_comment_count', $blog_comment_count);
            $blog_date_posted=$blog_functions->get_blog_date($blog_id);
            $blog_entry_template = $page->parse_template_section($blog_entry_template, 'blog_date_posted', $blog_date_posted);
            $article_url = $page->magicURIGenerator('blog', $blog_id, true);
            $blog_entry_template = $page->parse_template_section($blog_entry_template, 'blog_link_article', $article_url);

            $blog_entry_template = $page->parse_addon_tags($blog_entry_template, $addon_fields);
            $recordSet->MoveNext();
        }
        $page->replace_blog_template_tags();
        $page->replace_template_section('blog_entry_block', $blog_entry_template);
        $page->replace_permission_tags();
        $page->cleanup_template_sections($next_prev, $next_prev_bottom);

        $display .= $page->return_page();
        return $display;
    }

    public function display()
    {
        global $conn, $config, $lang, $misc, $jscript, $meta_canonical;

        include_once $config['basepath'] . '/include/user.inc.php';
        $userclass=new user();
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        include_once $config['basepath'] . '/include/blog_functions.inc.php';
        $blog_functions = new blog_functions();
        //Load Template
        $page->load_page($config['template_path'] . '/blog_article.html');
        // Make Sure we passed the PageID
        $display = '';
        if (!isset($_GET['ArticleID']) && intval($_GET['ArticleID'])<=0) {
            $display .= 'ERROR. PageID not sent';
        } else {
            $blog_id = intval($_GET['ArticleID']);
            //Add Pingback headers
            header('X-Pingback: '.$config['baseurl'].'/pingback.php');
            $jscript .= '<link rel="pingback" href="'.$config['baseurl'].'/pingback.php" />';
            //Check if we posted a comment.
            if (isset($_SESSION['userID']) && $_SESSION['userID']>0 && isset($_POST['comment_text']) && strlen($_POST['comment_text']) > 0) {
                include_once $config['basepath'] . '/include/blog_editor.inc.php';
                $blog_comment = $misc->make_db_safe(strip_tags($_POST['comment_text']));
                if ($config['blog_requires_moderation']==1) {
                    if (isset($_SESSION['blog_user_type']) && $_SESSION['blog_user_type'] == 4) {
                        $moderated=1;
                    } else {
                        $moderated=0;
                    }
                } else {
                    $moderated=1;
                }
                $sql = 'INSERT INTO ' . $config['table_prefix'] . 'blogcomments (userdb_id,blogcomments_timestamp,blogcomments_text,blogmain_id,blogcomments_moderated) 
						VALUES ('.intval($_SESSION['userID']).','.time().",$blog_comment,$blog_id,$moderated);";

                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $comment_id = $conn->Insert_ID();
                if ($moderated==0) {
                    $page->page = $page->cleanup_template_block('comment_moderated', $page->page);
                }
                include_once $config['basepath'] . '/include/hooks.inc.php';
                $hooks = new hooks();
                $hooks->load('after_new_blog_comment', $comment_id);
            }
            $page->page = $page->remove_template_block('comment_moderated', $page->page);

            //$display .= '<div class="page_display">';
            $sql = 'SELECT userdb_id,blogmain_full,blogmain_id,blogmain_published 
					FROM ' . $config['table_prefix'] . 'blogmain 
					WHERE blogmain_id=' . $blog_id;
            $recordSet = $conn->Execute($sql);
            if ($recordSet === false) {
                $misc->log_error($sql);
            }
            //$full = html_entity_decode($recordSet->fields('blogmain_full'), ENT_NOQUOTES, $config['charset']);
            $full = $recordSet->fields('blogmain_full');
            $full=preg_replace('/\<hr.*?\>/', '', $full, 1);

            //Deal with Code Tags
            preg_match_all('/<code>(.*?)<\/code>/is', $full, $code_tags);
            //echo '<pre>'.print_r($code_tags).'</pre>';
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

            //Handle blog_listing_# blocks
            preg_match_all('/{(blog_listing_[\d]*)}/m', $full, $matches);
            $blog_listings=array_unique($matches[1]);
            foreach ($blog_listings as $blog_listing) {
                $listing_template = $page->get_template_section($blog_listing, $full);
                //Skip incomplete blogs, tags will just be stripped
                if ($listing_template==false) {
                    continue;
                }
                //Get Listing ID
                preg_match('/blog_listing_([\d]*)/', $blog_listing, $id_match);
                $listing_id = $id_match[1];
                $listing_template = $page->replace_listing_field_tags($listing_id, $listing_template);
                $full = $page->replace_template_section($blog_listing, $listing_template, $full);
            }
            $id = $recordSet->fields('blogmain_id');
            $status = $recordSet->fields('blogmain_published');
            $blog_author_id = $recordSet->fields('userdb_id');
            if ($status !=1) {
                if (!isset($_SESSION['blog_user_type']) || $_SESSION['blog_user_type'] < 4) {
                    return $lang['listing_editor_permission_denied'];
                }
            }

            //Start Replacing Tags

            $page->page = $page->parse_template_section($page->page, 'blog_id', $id);

            $blog_title = $blog_functions->get_blog_title($id);
            $page->page = $page->parse_template_section($page->page, 'blog_title', $blog_title);
            $page->replace_user_field_tags($blog_author_id, '', 'blog_author');
            $blog_comment_count=$blog_functions->get_blog_comment_count($id);
            $page->page = $page->parse_template_section($page->page, 'blog_comment_count', $blog_comment_count);
            $blog_date_posted=$blog_functions->get_blog_date($id);
            $page->page = $page->parse_template_section($page->page, 'blog_date_posted', $blog_date_posted);
            $page->page = $page->parse_template_section($page->page, 'blog_full_article', $full);

            //Show Blog Categories
            $assigned_cats = $blog_functions->get_blog_categories_assignment_names($id);
            $cat_html = $page->get_template_section('category_block');
            $cat_html_replace ='';
            //<input type="checkbox" name="cat_id" value="{blog_category_id}" /> {blog_category_name}
            foreach ($assigned_cats as $cat_id => $cat_name) {
                $cat_html_replace = $page->cleanup_template_block('category_delimiter', $cat_html_replace);
                $cat_html_replace.= $cat_html;
                $cat_url = $page->magicURIGenerator('blog_cat', $cat_id, true);
                $cat_html_replace = $page->parse_template_section($cat_html_replace, 'category_url', $cat_url);
                $cat_html_replace = $page->parse_template_section($cat_html_replace, 'category_name', htmlentities($cat_name, ENT_COMPAT, $config['charset']));
            }
            $cat_html_replace = $page->remove_template_block('category_delimiter', $cat_html_replace);
            $page->replace_template_section('category_block', $cat_html_replace);

            // Allow Admin To Edit #
            if ((isset($_SESSION['can_access_blog_manager']) || $_SESSION['admin_privs'] == 'yes') && $config['wysiwyg_show_edit'] == 1) {
                $admin_edit_link = $config['baseurl'].'/admin/index.php?action=edit_blog_post&amp;id='.$id;
                $page->page = $page->parse_template_section($page->page, 'admin_edit_link', $admin_edit_link);
                $page->page = $page->cleanup_template_block('admin_edit_link', $page->page);
            } else {
                $page->page = $page->remove_template_block('admin_edit_link', $page->page);
            }

            //Tag Cloud
            $assigned_tags = $blog_functions->get_blog_tag_assignment($id);
            $tag_html = $page->get_template_section('blog_tag_cloud_block');
            $tag_html_replace ='';
            //<input type="checkbox" name="cat_id" value="{blog_category_id}" /> {blog_category_name}
            foreach ($assigned_tags as $tag_info) {
                $tag_html_replace.= $tag_html;
                $tag_html_replace = $page->parse_template_section($tag_html_replace, 'tag_name', $tag_info['tag_name']);
                ;
                $tag_html_replace = $page->parse_template_section($tag_html_replace, 'tag_link', $tag_info['tag_link']);
                ;
                $tag_html_replace = $page->parse_template_section($tag_html_replace, 'tag_fontsize', $tag_info['tag_fontsize']);
                ;
            }
            $page->replace_template_section('blog_tag_cloud_block', $tag_html_replace);

            //Deal with Comments
            $sql = 'SELECT userdb_id,blogcomments_timestamp,blogcomments_text,blogcomments_id FROM ' . $config['table_prefix'] . 'blogcomments WHERE blogmain_id = '.$id.' AND blogcomments_moderated = 1 ORDER BY blogcomments_timestamp ASC;';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $blog_comment_template = '';
            while (!$recordSet->EOF) {
                //Load DB Values
                $comment_author_id=$recordSet->fields('userdb_id');
                $comment_id=$recordSet->fields('blogcomments_id');
                $blogcomments_timestamp=$recordSet->fields('blogcomments_timestamp');
                $blogcomments_text=html_entity_decode($recordSet->fields('blogcomments_text'), ENT_NOQUOTES, $config['charset']);
                //Load Template Block
                $blog_comment_template .= $page->get_template_section('blog_article_comment_item_block');
                //Lookup Blog Author..
                $author_type=$userclass->get_user_type($comment_author_id);
                if ($author_type=='member') {
                    $author_display=$userclass->get_user_single_item('userdb_user_name', $comment_author_id);
                } else {
                    $author_display=$userclass->get_user_single_item('userdb_user_first_name', $comment_author_id).' '.$userclass->get_user_single_item('userdb_user_last_name', $comment_author_id);
                }
                $blog_comment_template = $page->parse_template_section($blog_comment_template, 'blog_comment_author', $author_display);
                $blog_comment_date_posted=$misc->convert_timestamp($blogcomments_timestamp, true);
                $blog_comment_template = $page->parse_template_section($blog_comment_template, 'blog_comment_date_posted', $blog_comment_date_posted);
                $blog_comment_template = $page->parse_template_section($blog_comment_template, 'blog_comment_text', $blogcomments_text);
                $blog_comment_template = $page->parse_template_section($blog_comment_template, 'blog_comment_id', $comment_id);

                $recordSet->MoveNext();
            }
            $page->replace_template_section('blog_article_comment_item_block', $blog_comment_template);

            //Render Add New Comment

            $article_url = $page->magicURIGenerator('blog', $id, true);
            $page->page = $page->parse_template_section($page->page, 'blog_comments_post_url', $article_url);

            //Add Canonical Link
            $meta_canonical = $page->magicURIGenerator('blog', $id, true);

            //Render Page Out
            //$page->replace_tags(array('templated_search_form', 'featured_listings_horizontal', 'featured_listings_vertical', 'company_name', 'link_printer_friendly'));
            $page->replace_search_field_tags();
            $page->replace_permission_tags();

            $display .= $page->return_page();
        }
        return $display;
    } // End page_display()
} //End page_display Class
