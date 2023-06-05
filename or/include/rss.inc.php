<?php

class rss
{
    public function build_listing_sql()
    {
        global $config;
        $sql='SELECT listingsdb_id,listingsdb_last_modified,listingsdb_pclass_id FROM ' . $config['table_prefix'].'listingsdb WHERE ';
        //Allow Filtering by agent ID
        if (isset($_GET['agent_id'])) {
            if (!is_array($_GET['agent_id'])) {
                $id = $_GET['agent_id'];
                unset($_GET['agent_id']);
                $_GET['agent_id'][]=$id;
            }
            $aidset =false;
            foreach ($_GET['agent_id'] as $aid) {
                if (is_numeric($aid)) {
                    if ($aidset) {
                        $sql .= ' AND userdb_id = '.$aid;
                    } else {
                        $sql .= ' userdb_id = '.$aid;
                    }
                    $aidset=true;
                }
            }
            if ($aidset) {
                $sql .= ' AND ';
            }
        }
        return $sql;
    }

    public function rss_view($option)
    {
        global $conn, $lang, $config, $misc;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        include_once $config['basepath'] . '/include/user.inc.php';
        $userclass=new user();
        $display = '';

        //Decide with RSS feed to show
        switch ($option) {
            case 'featured':
                $sql = $this->build_listing_sql();
                if (intval($config['rss_limit_featured']) > 0) {
                    $sql.=' listingsdb_featured = \'yes\' AND listingsdb_active = \'yes\' LIMIT 0, '.intval($config['rss_limit_featured']);
                } else {
                    $sql.=' listingsdb_featured = \'yes\' AND listingsdb_active = \'yes\' ';
                }
                $rsslink = $config['baseurl'].'/index.php?action=rss_featured_listings';
                $rsstitle = $config['rss_title_featured'];
                $rssdesc = $config['rss_desc_featured'];
                $rsslistingdesc = $config['rss_listingdesc_featured'];
                break;

            case 'lastmodified':
                $sql = $this->build_listing_sql();
                if (intval($config['rss_limit_lastmodified']) > 0) {
                    $sql.=' listingsdb_active = \'yes\' ORDER BY listingsdb_last_modified DESC LIMIT 0, '.intval($config['rss_limit_lastmodified']);
                } else {
                    $sql.=' listingsdb_active = \'yes\' ORDER BY listingsdb_last_modified DESC';
                }
                $rsslink = $config['baseurl'].'/index.php?action=rss_lastmodified_listings';
                $rsstitle = $config['rss_title_lastmodified'];
                $rssdesc = $config['rss_desc_lastmodified'];
                $rsslistingdesc = $config['rss_listingdesc_lastmodified'];
                break;

            case 'latestlisting':
                $sql = $this->build_listing_sql();
                if (intval($config['rss_limit_latestlisting']) > 0) {
                    $sql.=' listingsdb_active = \'yes\' ORDER BY listingsdb_creation_date DESC LIMIT 0, '.intval($config['rss_limit_latestlisting']);
                } else {
                    $sql.=' listingsdb_active = \'yes\' ORDER BY listingsdb_creation_date DESC';
                }
                $rsslink = $page->magicURIGenerator('rss', 'latestlisting', true);
                $rsstitle = $config['rss_title_latestlisting'];
                $rssdesc = $config['rss_desc_latestlisting'];
                $rsslistingdesc = $config['rss_listingdesc_latestlisting'];
                break;

            case 'blog_posts':
                $sql = 'SELECT blogmain_id, blogmain_title, blogmain_date, blogmain_full FROM ' . $config['table_prefix'].'blogmain WHERE blogmain_published = 1 ORDER BY blogmain_date DESC';
                $rsslink = $page->magicURIGenerator('rss', 'blog_posts', true);
                $rsstitle = $config['rss_title_blogposts'];
                $rssdesc = $config['rss_desc_blogposts'];
                break;

            case 'blog_comments':
                $sql = 'SELECT bc.blogmain_id, blogmain_title, blogmain_date, blogmain_full,blogcomments_id, bc.userdb_id, blogcomments_timestamp,blogcomments_text FROM ' . $config['table_prefix'].'blogmain as bm INNER JOIN ' . $config['table_prefix'].'blogcomments as bc ON bm.blogmain_id = bc.blogmain_id WHERE blogmain_published = 1 AND blogcomments_moderated = 1  ORDER BY blogcomments_timestamp DESC';
                $rsslink = $page->magicURIGenerator('rss', 'blog_comments', true);
                $rsstitle = $config['rss_title_blogcomments'];
                $rssdesc = $config['rss_desc_blogcomments'];
                break;
        }

        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        //Get RSS Template
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $page->load_page($config['template_path'] . '/rss.html', false);
        $page->replace_tag('rss_webroot', $rsslink);
        $page->replace_tag('rss_description', $rssdesc);
        $page->replace_tag('rss_title', $rsstitle);

        switch ($option) {
            case 'featured':
            case 'lastmodified':
            case 'latestlisting':
                $page->replace_tag('rss_item_description', $rsslistingdesc);
                $page->replace_tag('item_link', '{fulllink_to_listing}');
                $page->replace_tag('rss_item_title', '{listing_title}');
                $listing_template=$page->get_template_section('rss_item_block');
                $completed_listing_template='';
                while (!$recordSet->EOF) {
                    // first, check to see whether the listing is currently active
                    $class = $recordSet->fields('listingsdb_pclass_id');
                    $completed_listing_template .= $page->replace_listing_field_tags($recordSet->fields('listingsdb_id'), $listing_template, true);
                    $completed_listing_template = str_replace('{rss_item_guid}', base64_encode($recordSet->fields('listingsdb_id').'-'.$recordSet->fields('listingsdb_last_modified')), $completed_listing_template);
                    $completed_listing_template = str_replace('{rss_item_modified_date}', date('D, d M Y H:i:s O', strtotime($recordSet->fields('listingsdb_last_modified'))), $completed_listing_template);
                    $recordSet->MoveNext();
                }
                $page->replace_template_section('rss_item_block', $completed_listing_template);
                break;

            case 'blog_comments':
                //$sql = 'SELECT blogmain_id, blogmain_title, blogmain_date, blogmain_full,blogcomments_id, bc.userdb_id, blogcomments_timestamp,blog_comments_text FROM ' . $config['table_prefix'].'blogmain as bm INNER JOIN ' . $config['table_prefix'].'blogcomments as bc ON bm.blogmain_id = bc.blogmain_id WHERE blogmain_published = 1 AND blog_comments_moderated = 1';
                $blogpost_template=$page->get_template_section('rss_item_block');
                $completed_listing_template='';
                while (!$recordSet->EOF) {
                    $blog_id = $recordSet->fields('blogmain_id');
                    $comment_id = $recordSet->fields('blogcomments_id');
                    $blog_url = $page->magicURIGenerator('blog_comment', $comment_id, true);
                    $blog_title = $recordSet->fields('blogmain_title');
                    $comment_author_id = $recordSet->fields('userdb_id');
                    //Lookup Comment Author..
                    $author_type=$userclass->get_user_type($comment_author_id);
                    if ($author_type=='member') {
                        $author_display=$userclass->get_user_single_item('userdb_user_name', $comment_author_id);
                    } else {
                        $author_display=$userclass->get_user_single_item('userdb_user_last_name', $comment_author_id).', '.$userclass->get_user_single_item('userdb_user_first_name', $comment_author_id);
                    }

                    $completed_listing_template .= str_replace('{item_link}', $blog_url, $blogpost_template);
                    $completed_listing_template = str_replace('{rss_item_title}', 'Comment on '.$blog_title.' by '.$author_display, $completed_listing_template);
                    //Get BLog Summary
                    $text = html_entity_decode($recordSet->fields('blogcomments_text'), ENT_NOQUOTES, $config['charset']);
                    //Start Replacing Tags

                    $completed_listing_template = str_replace('{rss_item_description}', $text, $completed_listing_template);
                    //$completed_listing_template .= $page->replace_listing_field_tags($recordSet->fields('listingsdb_id'),$listing_template,TRUE);
                    $completed_listing_template = str_replace('{rss_item_guid}', base64_encode($blog_id.'-'.$recordSet->fields('blogmain_date')), $completed_listing_template);
                    $completed_listing_template = str_replace('{rss_item_modified_date}', date('D, d M Y H:i:s O', $recordSet->fields('blogmain_date')), $completed_listing_template);
                    $recordSet->MoveNext();
                }
                $page->replace_template_section('rss_item_block', $completed_listing_template);
                break;

            case 'blog_posts':
                $blogpost_template=$page->get_template_section('rss_item_block');
                $completed_listing_template='';
                while (!$recordSet->EOF) {
                    $blog_id = $recordSet->fields('blogmain_id');
                    $blog_url = $page->magicURIGenerator('blog', $blog_id, true);
                    $blog_title = $recordSet->fields('blogmain_title');

                    $completed_listing_template .= str_replace('{item_link}', $blog_url, $blogpost_template);
                    $completed_listing_template = str_replace('{rss_item_title}', $blog_title, $completed_listing_template);
                    //Get BLog Summary
                    $full = html_entity_decode($recordSet->fields('blogmain_full'), ENT_NOQUOTES, $config['charset']);
                    //Start Replacing Tags

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

                    $completed_listing_template = str_replace('{rss_item_description}', $summary, $completed_listing_template);
                    //$completed_listing_template .= $page->replace_listing_field_tags($recordSet->fields('listingsdb_id'),$listing_template,TRUE);
                    $completed_listing_template = str_replace('{rss_item_guid}', base64_encode($blog_id.'-'.$recordSet->fields('blogmain_date')), $completed_listing_template);
                    $completed_listing_template = str_replace('{rss_item_modified_date}', date('D, d M Y H:i:s O', $recordSet->fields('blogmain_date')), $completed_listing_template);
                    $recordSet->MoveNext();
                }
                $page->replace_template_section('rss_item_block', $completed_listing_template);
                break;
        }
        $display=$page->return_page();
        return $display;
    }
}
