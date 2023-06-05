<?php


use Abraham\TwitterOAuth\TwitterOAuth;

class page
{
    public $page;

    public function output_powered_by()
    {
        header('Content-Type: image/png');
        return base64_decode('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');
    }

    public function load_js($admin_area = false)
    {
        global $lang, $jscript, $config;
        $display = $jscript;
        //Get CSS Tags
        // $css_tags=[];
        // $css_output='';

        // preg_match_all('/{load_css_(.*?)}/',$display,$css_tags);
        // $css_tagname=$css_tags[1];
        // foreach($css_tagname as $id => $tag){
        //     $full_tag = $css_tags[0][$id];
        //     $url = $this->magicURIGenerator('css',$tag,TRUE,$admin_area);
        //     $css_output .= '<link rel="stylesheet" type="text/css" href="'.$url.'" />'."\r\n";
        //     $display = str_replace($full_tag,'',$display);
        // }

        //Do Replacement of Lang tags in jscript. Must be done here as page editor and blog editor load content after these happen.
        $display = $this->replace_lang_template_tags(true, $display);
        //$display = $css_output.$display;
        return $display;
    }

    public function load_ORjs($admin_area = false)
    {
        global $lang, $jscript, $config;
        $display = '';

        $display .= '<!-- begin load_ORjs -->' . "\r\n";
        //compiled/minified confirm delete
        $display .= '<script type="text/javascript">
						function confirmDelete(a){a||(a="{lang_are_you_sure_you_want_to_delete}");return confirm(a)?!0:!1};
					</script>' . "\r\n";
        $display .= '<!-- end load_ORjs -->' . "\r\n";

        $display .= $jscript;
        //Get CSS Tags
        $css_tags = [];
        $css_output = '';

        /*      May need to re-add this, not sure what's using it.
                preg_match_all('/{load_css_(.*?)}/',$display,$css_tags);
                $css_tagname=$css_tags[1];
                foreach($css_tagname as $id => $tag){
                    $full_tag = $css_tags[0][$id];
                    $url = $this->magicURIGenerator('css',$tag,TRUE,$admin_area);
                    $css_output .= '<link rel="stylesheet" type="text/css" href="'.$url.'" />'."\r\n";
                    $display = str_replace($full_tag,'',$display);
                }
        */
        //Do Replacement of Lang tags in jscript. Must be done here as page editor and blog editor load content after these happen.
        $display = $this->replace_lang_template_tags(true, $display);
        $display = $css_output . $display;
        return $display;
    }

    public function magicURIGenerator($action, $id = null, $full = false, $admin = false)
    {
        global $config, $conn, $misc;

        $url = '';
        $SEOURI_COMPLETE = false;
        //Handle Link Generation
        //BLog COmment is spectial as we really link to the blog page and just add a bookmark to that URL.
        if ($action == 'blog_comment') {
            $sql_action = 'blog';
        } else {
            $sql_action = $action;
        }
        $slug = $config['uri_parts'][$sql_action]['slug'];
        $uri = $config['uri_parts'][$sql_action]['uri'];

        switch ($action) {
            case 'listing':
                global $url_listing_id;
                $url_listing_id = intval($id);
                include_once $config['basepath'] . '/include/listing.inc.php';
                $listing_pages = new listing_pages();
                if ($config['url_style'] != '1') {
                    //Make sure uri contains listing_title or listing_id tag.
                    if (strpos($uri, '{listing_id}') !== false) {
                        $uri = str_replace('{listing_id}', urlencode($url_listing_id), $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    if (strpos($uri, '{listing_seotitle}') !== false) {
                        $title = $listing_pages->get_listing_seotitle($url_listing_id);
                        $uri = str_replace('{listing_seotitle}', urlencode($title), $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    preg_match_all('/({.*?})/', $uri, $urimatches);
                    foreach ($urimatches[0] as $ltag) {
                        $value = $this->replace_listing_field_tags($url_listing_id, $ltag, false, true);
                        $value = $this->create_seouri($value);
                        $uri = str_replace($ltag, urlencode($value), $uri);
                    }

                    $url = $slug . $uri;
                    //Make sure we encode any %2F in the URL as %252F for apache.
                    $url = str_replace('%2F', '%252F', $url);
                }
                if ($config['url_style'] == '1' || $SEOURI_COMPLETE == false) {
                    $url = 'index.php?action=listingview&amp;listingID=' . $url_listing_id;
                }
                break;
            case 'blog_archive':
                include_once $config['basepath'] . '/include/blog_functions.inc.php';
                $blog_functions = new blog_functions();
                $date = $id;
                $date_array = explode('/', $date);
                $year = $date_array[0];
                $month = $date_array[1];
                if ($config['url_style'] != '1') {
                    //Make sure uri contains listing_title or listing_id tag.
                    if (strpos($uri, '{archive_date}') !== false) {
                        $uri = str_replace('{archive_date}', $date, $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    $url = $slug . $uri;
                }
                if ($config['url_style'] == '1' || $SEOURI_COMPLETE == false) {
                    $url = 'index.php?action=blog_index&amp;year=' . $year . '&amp;month=' . $month;
                }
                break;
            case 'blog_cat':
                include_once $config['basepath'] . '/include/blog_functions.inc.php';
                $blog_functions = new blog_functions();
                $cat_id = intval($id);
                if ($config['url_style'] != '1') {
                    //Make sure uri contains listing_title or listing_id tag.
                    if (strpos($uri, '{cat_id}') !== false) {
                        $uri = str_replace('{cat_id}', $cat_id, $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    if (strpos($uri, '{cat_seoname}') !== false) {
                        $title = $blog_functions->get_blog_category_seoname($cat_id);
                        $uri = str_replace('{cat_seoname}', urlencode($title), $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    if (strpos($uri, '{cat_parent_seoname}') !== false) {
                        $parent_id = $blog_functions->get_blog_category_parent($cat_id);
                        $title = $blog_functions->get_blog_category_seoname($parent_id);
                        $uri = str_replace('{cat_parent_seoname}', urlencode($title), $uri);
                    }
                    $url = $slug . $uri;
                }
                if ($config['url_style'] == '1' || $SEOURI_COMPLETE == false) {
                    $url = 'index.php?action=blog_index&amp;cat_id=' . $cat_id;
                }
                break;
            case 'blog':
                include_once $config['basepath'] . '/include/blog_functions.inc.php';
                $blog_functions = new blog_functions();
                $blog_id = intval($id);
                if ($config['url_style'] != '1') {
                    //Make sure uri contains listing_title or listing_id tag.
                    if (strpos($uri, '{blog_id}') !== false) {
                        $uri = str_replace('{blog_id}', $blog_id, $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    if (strpos($uri, '{blog_title}') !== false) {
                        $title = $blog_functions->get_blog_title($blog_id);
                        $title = $this->create_seouri($title);
                        $uri = str_replace('{blog_title}', urlencode($title), $uri);
                    }
                    if (strpos($uri, '{blog_seotitle}') !== false) {
                        $title = $blog_functions->get_blog_seotitle($blog_id);
                        $uri = str_replace('{blog_seotitle}', urlencode($title), $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    if (strpos($uri, '{category_path}') !== false) {
                        $cats = $blog_functions->get_blog_categories_assignment_seonames($blog_id);
                        array_walk($cats, 'urlencode');
                        $cat_url = implode('/', $cats);
                        $uri = str_replace('{category_path}', $cat_url, $uri);
                    }
                    $url = $slug . $uri;
                }
                if ($config['url_style'] == '1' || $SEOURI_COMPLETE == false) {
                    $url = 'index.php?action=blog_view_article&amp;ArticleID=' . $blog_id;
                }
                break;
            case 'blog_comment':
                include_once $config['basepath'] . '/include/blog_functions.inc.php';
                $blog_functions = new blog_functions();
                $blogcomment_id = intval($id);
                //Get BLog ID
                $blog_id = $blog_functions->get_blog_id_from_comment_id($blogcomment_id);
                if ($config['url_style'] != '1') {
                    //Make sure uri contains listing_title or listing_id tag.
                    if (strpos($uri, '{blog_id}') !== false) {
                        $uri = str_replace('{blog_id}', $blog_id, $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    if (strpos($uri, '{blog_title}') !== false) {
                        $title = $blog_functions->get_blog_title($blog_id);
                        $title = $this->create_seouri($title);
                        $uri = str_replace('{blog_title}', urlencode($title), $uri);
                    }
                    if (strpos($uri, '{blog_seotitle}') !== false) {
                        $title = $blog_functions->get_blog_seotitle($blog_id);
                        $uri = str_replace('{blog_seotitle}', urlencode($title), $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    if (strpos($uri, '{category_path}') !== false) {
                        $cats = $blog_functions->get_blog_categories_assignment_seonames($blog_id);
                        array_walk($cats, 'urlencode');
                        $cat_url = implode('/', $cats);
                        $uri = str_replace('{category_path}', $cat_url, $uri);
                    }
                    $url = $slug . $uri;
                }
                if ($config['url_style'] == '1' || $SEOURI_COMPLETE == false) {
                    $url = 'index.php?action=blog_view_article&amp;ArticleID=' . $blog_id;
                }
                $url .= '#' . $blogcomment_id;
                break;
            case 'page':
                include_once $config['basepath'] . '/include/page_functions.inc.php';
                $page_functions = new page_functions();
                $page_id = intval($id);
                if ($config['url_style'] != '1') {
                    //Make sure uri contains listing_title or listing_id tag.
                    if (strpos($uri, '{page_id}') !== false) {
                        $uri = str_replace('{page_id}', $page_id, $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    if (strpos($uri, '{page_title}') !== false) {
                        $title = $page_functions->get_page_title($page_id);
                        $title = $this->create_seouri($title);
                        $uri = str_replace('{page_title}', urlencode($title), $uri);
                    }
                    if (strpos($uri, '{page_seotitle}') !== false) {
                        $title = $page_functions->get_page_seotitle($page_id);
                        $uri = str_replace('{page_seotitle}', urlencode($title), $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    $url = $slug . $uri;
                }
                if ($config['url_style'] == '1' || $SEOURI_COMPLETE == false) {
                    $url = 'index.php?action=page_display&amp;PageID=' . $page_id;
                }
                break;
            case 'agent':
                include_once $config['basepath'] . '/include/user.inc.php';
                $user = new user();
                $agent_id = intval($id);
                if ($config['url_style'] != '1') {
                    //Make sure uri contains listing_title or listing_id tag.
                    if (strpos($uri, '{agent_id}') !== false) {
                        $uri = str_replace('{agent_id}', $agent_id, $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    if (strpos($uri, '{agent_fname}') !== false) {
                        $title = $user->get_user_single_item('userdb_user_first_name', $agent_id);
                        $title = $this->create_seouri($title);
                        $uri = str_replace('{agent_fname}', urlencode($title), $uri);
                    }
                    if (strpos($uri, '{agent_lname}') !== false) {
                        $title = $user->get_user_single_item('userdb_user_last_name', $agent_id);
                        $title = $this->create_seouri($title);
                        $uri = str_replace('{agent_lname}', urlencode($title), $uri);
                    }
                    $url = $slug . $uri;
                }
                if ($config['url_style'] == '1' || $SEOURI_COMPLETE == false) {
                    $url = 'index.php?action=view_user&amp;user=' . $id;
                }
                break;
            case 'listing_image':
                $image_id = intval($id);
                include_once $config['basepath'] . '/include/listing.inc.php';
                $listing_pages = new listing_pages();
                if ($config['url_style'] != '1') {
                    //Make sure uri contains listing_title or listing_id tag.
                    if (strpos($uri, '{image_id}') !== false) {
                        $uri = str_replace('{image_id}', $image_id, $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    $sql = 'SELECT listingsdb_id 
							FROM ' . $config['table_prefix'] . "listingsimages 
							WHERE ( listingsimages_id = $image_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $parent_id = $recordSet->fields('listingsdb_id');
                    if (strpos($uri, '{listing_seotitle}') !== false) {
                        $title = $listing_pages->get_listing_seotitle($parent_id);
                        $uri = str_replace('{listing_seotitle}', urlencode($title), $uri);
                    }
                    preg_match_all('/({.*?})/', $uri, $urimatches);
                    foreach ($urimatches[0] as $ltag) {
                        $value = $this->replace_listing_field_tags($parent_id, $ltag, false, true);
                        $value = $this->create_seouri($value);
                        $uri = str_replace($ltag, urlencode($value), $uri);
                    }
                    $url = $slug . $uri;
                }
                if ($config['url_style'] == '1' || $SEOURI_COMPLETE == false) {
                    $url = 'index.php?action=view_listing_image&amp;image_id=' . $image_id;
                }
                break;
            case 'css':
                $css_name = $id;
                //echo $css_name ." - magicURIGenerator<br /> ";
                if ($config['url_style'] != '1') {
                    //Make sure uri contains listing_title or listing_id tag.
                    if (strpos($uri, '{css_name}') !== false) {
                        $uri = str_replace('{css_name}', urlencode($css_name), $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    $url = $slug . $uri;
                }
                if ($config['url_style'] == '1' || $SEOURI_COMPLETE == false) {
                    $url = 'index.php?action=load_css&amp;css_file=' . $css_name;
                }
                break;
            case 'rss':
                $rss_name = $id;
                if ($config['url_style'] != '1') {
                    //Make sure uri contains listing_title or listing_id tag.
                    if (strpos($uri, '{rss_feed}') !== false) {
                        $uri = str_replace('{rss_feed}', urlencode($rss_name), $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    $url = $slug . $uri;
                }
                if ($config['url_style'] == '1' || $SEOURI_COMPLETE == false) {
                    $url = 'index.php?action=show_rss&amp;rss_feed=' . $rss_name;
                }
                break;
            case 'blog_tag':
                include_once $config['basepath'] . '/include/blog_functions.inc.php';
                $blog_functions = new blog_functions();
                $tag_id = intval($id);
                if ($config['url_style'] != '1') {
                    //Make sure uri contains listing_title or listing_id tag.
                    if (strpos($uri, '{tag_seoname}') !== false) {
                        $title = $blog_functions->get_tag_seoname($tag_id);
                        $uri = str_replace('{tag_seoname}', urlencode($title), $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    $url = $slug . $uri;
                }
                if ($config['url_style'] == '1' || $SEOURI_COMPLETE == false) {
                    $url = 'index.php?action=blog_index&amp;tag_id=' . $id;
                }
                break;
            case 'view_agents':
                if ($config['url_style'] != '1') {
                    $url = $slug . $uri;
                } else {
                    $url = 'index.php?action=view_users';
                }
                break;
            case 'searchpage':
                if ($config['url_style'] != '1') {
                    $url = $slug . $uri;
                } else {
                    $url = 'index.php?action=searchpage';
                }
                break;
            case 'blogindex':
                if ($config['url_style'] != '1') {
                    $url = $slug . $uri;
                } else {
                    $url = 'index.php?action=blog_index';
                }
                break;
            case 'searchresults':
                if ($config['url_style'] != '1') {
                    $url = $slug . $uri;
                } else {
                    $url = 'index.php?action=searchresults';
                }
                break;
            case 'index':
                if ($config['url_style'] != '1') {
                    $url = $slug . $uri;
                } else {
                    $url = 'index.php?action=index';
                }
                break;
            case 'member_signup':
                if ($config['url_style'] != '1') {
                    $url = $slug . $uri;
                } else {
                    $url = 'index.php?action=signup&amp;type=member';
                }
                break;
            case 'agent_signup':
                if ($config['url_style'] != '1') {
                    $url = $slug . $uri;
                } else {
                    $url = 'index.php?action=signup&amp;type=agent';
                }
                break;
            case 'member_login':
                if ($config['url_style'] != '1') {
                    $url = $slug . $uri;
                } else {
                    $url = 'index.php?action=member_login';
                }
                break;
            case 'view_favorites':
                if ($config['url_style'] != '1') {
                    $url = $slug . $uri;
                } else {
                    $url = 'index.php?action=view_favorites';
                }
                break;
            case 'calculator':
                if ($config['url_style'] != '1') {
                    $url = $slug . $uri;
                } else {
                    $url = 'index.php?action=calculator&amp;popup=yes';
                }
                break;
            case 'saved_searches':
                if ($config['url_style'] != '1') {
                    $url = $slug . $uri;
                } else {
                    $url = 'index.php?action=view_saved_searches';
                }
                break;
            case 'contact_listing_agent':
                $url_listing_id = intval($id);
                if ($config['url_style'] != '1') {
                    //Get Agent IF
                    include_once $config['basepath'] . '/include/user.inc.php';
                    $user = new user();
                    include_once $config['basepath'] . '/include/listing.inc.php';
                    $listing = new listing_pages();
                    $agent_id = $listing->get_listing_agent_value('userdb_id', $url_listing_id);
                    //Make sure uri contains listing_title or listing_id tag.
                    if (strpos($uri, '{agent_id}') !== false) {
                        $uri = str_replace('{agent_id}', $agent_id, $uri);
                    }
                    if (strpos($uri, '{listing_id}') !== false) {
                        $uri = str_replace('{listing_id}', $url_listing_id, $uri);
                        $SEOURI_COMPLETE = true;
                    }
                    if (strpos($uri, '{agent_fname}') !== false) {
                        $title = $user->get_user_single_item('userdb_user_first_name', $agent_id);
                        $title = $this->create_seouri($title);
                        $uri = str_replace('{agent_fname}', $title, $uri);
                    }
                    if (strpos($uri, '{agent_lname}') !== false) {
                        $title = $user->get_user_single_item('userdb_user_last_name', $agent_id);
                        $title = $this->create_seouri($title);
                        $uri = str_replace('{agent_lname}', $title, $uri);
                    }
                    $url = $slug . $uri;
                    //$url = 'index.php?action=contact_agent&listing_id='.$url_listing_id;
                }
                if ($config['url_style'] == '1' || $SEOURI_COMPLETE == false) {
                    $url = 'index.php?action=contact_agent&amp;listing_id=' . $url_listing_id;
                }
                break;
            case 'logout':
                if ($config['url_style'] != '1') {
                    $url = $slug . $uri;
                } else {
                    $url = 'index.php?action=logout';
                }
                break;
            case 'edit_profile':
                if (isset($_SESSION['userID'])) {
                    if ($config['url_style'] != '1') {
                        $url = $slug . $uri;
                    } else {
                        $url = 'index.php?action=edit_profile&amp;user_id=' . intval($_SESSION['userID']);
                    }
                } else {
                    $url = '';
                }
                break;
            default:
                $url = 'BAD URL';
                break;
        }
        if ($full == true) {
            if ($admin == true) {
                $url = $config['baseurl'] . '/admin/' . $url;
            } else {
                $url = $config['baseurl'] . '/' . $url;
            }
        }
        return $url;
    }

    public function create_seouri($title, $urlencode = true)
    {
        global $config;
        if ($config['controlpanel_mbstring_enabled'] == 0) {
            // MBSTRING NOT ENABLED
            $uri = strtolower($title);
        } else {
            $uri = mb_convert_case($title, MB_CASE_LOWER, $config['charset']);
        }
        $uri = trim($uri);
        $uri = preg_replace('/[\~`!@#\$%^*\(\)\+=\"\':;\[\]\{\}|\\\?\<\>,\.]/', '', $uri);
        $uri = str_replace(' ', $config['seo_url_seperator'], $uri);
        $uri = preg_replace('/[\-]+/', '-', $uri);
        if ($urlencode) {
            $uri = urlencode($uri);
        }
        return $uri;
    }

    public function str_replace_once($str_pattern, $str_replacement, $string)
    {
        if (strpos($string, $str_pattern) !== false) {
            $occurrence = strpos($string, $str_pattern);
            return substr_replace($string, $str_replacement, strpos($string, $str_pattern), strlen($str_pattern));
        }

        return $string;
    }

    public function set_session_referrer()
    {
        if ($_GET['action'] == 'powered_by' || $_GET['action'] == 'load_css') {
            return;
        }
        if (isset($_SESSION['OR_THIS_ACTION'])) {
            $_SESSION['OR_REFERRER_ACTION'] = $_SESSION['OR_THIS_ACTION'];
        } else {
            $_SESSION['OR_REFERRER_ACTION'] = '';
        }
        $_SESSION['OR_THIS_ACTION'] = $_GET['action'];
    }

    public function magicURIParser($admin = false, $url = '')
    {
        global $config, $conn, $misc;

        if (php_sapi_name() == 'cli') {
            return;
        }
        //Determine Path to index.php based on baseurl
        if ($admin == true) {
            $baseurlparsed = parse_url($config['baseurl'] . '/admin');
        } else {
            $baseurlparsed = parse_url($config['baseurl']);
        }

        if (isset($baseurlparsed['path'])) {
            $orpath = $baseurlparsed['path'];
        } else {
            $orpath = '';
        }
        if ($url == '') {
            if ($orpath != '') {
                $SEO_URI = $this->str_replace_once($orpath, '', $_SERVER['REQUEST_URI']);
            } else {
                $SEO_URI = $_SERVER['REQUEST_URI'];
            }
        } else {
            if ($orpath != '') {
                $SEO_URI = $this->str_replace_once($orpath, '', $url);
            } else {
                $SEO_URI = $url;
            }
        }
        $SEO_URI = ltrim($SEO_URI, '/');
        //echo "SEO_URI: $SEO_URI";
        //Remove Template switch code
        $SEO_URI = preg_replace('/[?|&]select_users_template=(.*)[^&]/', '', $SEO_URI);
        if ($SEO_URI == '' || $SEO_URI == 'index.php') {
            $_GET['action'] = 'index';
            $this->set_session_referrer();
            return;
        }
        //Load seouri table
        $sql = 'SELECT action,slug,uri FROM ' . $config['table_prefix_no_lang'] . 'seouri';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        while (!$recordSet->EOF) {
            $slug = $recordSet->fields('slug');
            $uri = $recordSet->fields('uri');
            $action = $recordSet->fields('action');

            //Handle Listing Pages
            if ($action == 'listing' && ($slug == '' || strpos($SEO_URI, $slug) === 0 || strpos($SEO_URI, 'l/') === 0)) {
                //This is a listing.
                //Handle Listing URLS for Twitter
                if (strpos($SEO_URI, 'l/') === 0) {
                    //Ok we have the listing ID
                    preg_match('/l\/([0-9]*)/', $SEO_URI, $matches);
                    $blog_id = $matches[1];
                    if ($blog_id > 0) {
                        $_GET['action'] = 'listingview';
                        $_GET['listingID'] = $blog_id;
                        $this->set_session_referrer();
                        return;
                    }
                }
                //Figure out if the $action has a listing ID or listing seotitle {listing_seotitle} {listing_id}
                if (strpos($uri, '{listing_id}') !== false) {
                    //Ok we have the listing I
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{listing_id}', '([0-9]*)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*', $preg_search);
                    //echo 'SEO URI: '.$SEO_URI.'<br />';
                    //echo 'PREG : '.$preg_search.'<br />';
                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    if (isset($matches[1])) {
                        $listing_id = $matches[1];
                        $sql = 'SELECT listingsdb_id 
								FROM ' . $config['table_prefix'] . 'listingsdb 
								WHERE listingsdb_id = ' . $listing_id;
                        $recordsetA = $conn->Execute($sql);
                        if (!$recordsetA) {
                            $misc->log_error($sql);
                        }
                        //echo 'RC: '.$recordsetA->RecordCount();
                        if ($recordsetA->RecordCount() == 1) {
                            $_GET['action'] = 'listingview';
                            $_GET['listingID'] = $listing_id;
                            $this->set_session_referrer();
                            return;
                        }
                    }
                }
                if (strpos($uri, 'listing_seotitle') !== false) {
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{listing_seotitle}', '(.*?)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*?', $preg_search);
                    //echo 'SEO URI: '.$SEO_URI.'<br />';
                    //echo 'PREG : '.$preg_search.'<br />';
                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    $listing_seotitle = rawurldecode($matches[1]);
                    //echo 'Blog SEO Title: '.$blog_seotitle.'<br />';
                    //Lookup SEO Title
                    $sql = 'SELECT listingsdb_id 
							FROM ' . $config['table_prefix'] . 'listingsdb 
							WHERE listing_seotitle = ' . $misc->make_db_safe($listing_seotitle);
                    $recordsetA = $conn->Execute($sql);
                    if (!$recordsetA) {
                        $misc->log_error($sql);
                    }
                    //echo 'RC: '.$recordsetA->RecordCount();
                    if ($recordsetA->RecordCount() == 1) {
                        $listing_id = $recordsetA->fields('listingsdb_id');
                        $_GET['action'] = 'listingview';
                        $_GET['listingID'] = $listing_id;
                        $this->set_session_referrer();
                        return;
                    }
                    //blog_seotitle
                }
            }
            //Handle Contact Agent page
            if ($action == 'contact_listing_agent' && ($slug == '' || strpos($SEO_URI, $slug) === 0)) {
                if (strpos($uri, '{listing_id}') !== false) {
                    //Ok we have the listing ID
                    $_GET['action'] = 'contact_agent';
                    //$url = 'index.php?action=contact_agent&listing_id='.$url_listing_id;
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{listing_id}', '([0-9]*)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*?', $preg_search);
                    //echo 'SEO URI: '.$SEO_URI.'<br />';
                    //echo 'PREG : '.$preg_search.'<br />';
                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    $listing_id = $matches[1];
                    $_GET['listing_id'] = $listing_id;
                    $this->set_session_referrer();
                    return;
                }
            }
            if ($action == 'blog' && ($slug == '' || strpos($SEO_URI, $slug) === 0 || strpos($SEO_URI, 'b/') === 0)) {
                //Handle Blog URLS for Twitter
                if (strpos($SEO_URI, 'b/') === 0) {
                    //Ok we have the listing ID
                    $blog_id = $matches[1];
                    if ($blog_id > 0) {
                        $_GET['action'] = 'blog_view_article';
                        $_GET['ArticleID'] = $blog_id;
                        $this->set_session_referrer();
                        return;
                    }
                }
                if (strpos($uri, '{blog_id}') !== false) {
                    //Ok we have the listing ID
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{blog_id}', '([0-9]*)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*?', $preg_search);
                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    $blog_id = $matches[1];
                    if ($blog_id > 0) {
                        $_GET['action'] = 'blog_view_article';
                        $_GET['ArticleID'] = $blog_id;
                        $this->set_session_referrer();
                        return;
                    }
                }
                if (strpos($uri, 'blog_seotitle') !== false) {
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{blog_seotitle}', '(.*?)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*?', $preg_search);
                    //echo 'SEO URI: '.$SEO_URI.'<br />';
                    //echo 'PREG : '.$preg_search.'<br />';
                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    if (isset($matches[1])) {
                        $blog_seotitle = rawurldecode($matches[1]);
                        //echo 'Blog SEO Title: '.$blog_seotitle.'<br />';
                        //Lookup SEO Title
                        $sql = 'SELECT blogmain_id FROM ' . $config['table_prefix'] . 'blogmain WHERE blog_seotitle = ' . $misc->make_db_safe($blog_seotitle);
                        $recordsetA = $conn->Execute($sql);
                        if (!$recordsetA) {
                            $misc->log_error($sql);
                        }
                        //echo 'RC: '.$recordsetA->RecordCount();
                        if ($recordsetA->RecordCount() == 1) {
                            $blog_id = $recordsetA->fields('blogmain_id');
                            $_GET['action'] = 'blog_view_article';
                            $_GET['ArticleID'] = $blog_id;
                            $this->set_session_referrer();
                            return;
                        }
                    }

                    //blog_seotitle
                }
            }
            if ($action == 'blog_archive' && ($slug == '' || strpos($SEO_URI, $slug) === 0)) {
                if (strpos($uri, '{archive_date}') !== false) {
                    //Ok we have the listing ID
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{archive_date}', '(.*)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*?', $preg_search);
                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    $date = $matches[1];
                    if ($date != '') {
                        $date_array = explode('/', $date);
                        $year = $date_array[0];
                        $month = $date_array[1];
                        $_GET['action'] = 'blog_index';
                        $_GET['year'] = $year;
                        $_GET['month'] = $month;
                        $this->set_session_referrer();
                        return;
                    }
                }
            }
            if ($action == 'page' && ($slug == '' || strpos($SEO_URI, $slug) === 0)) {
                if (strpos($uri, '{page_id}') !== false) {
                    //Ok we have the listing ID
                    $_GET['action'] = 'page_display';
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{page_id}', '([0-9]*)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*?', $preg_search);
                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    $blog_id = $matches[1];
                    if ($blog_id > 0) {
                        $_GET['action'] = 'page_display';
                        $_GET['PageID'] = $blog_id;
                        $this->set_session_referrer();
                        return;
                    }
                }
                if (strpos($uri, 'page_seotitle') !== false) {
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{page_seotitle}', '(.*?)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*?', $preg_search);
                    if ($preg_search == '(.*?)') {
                        //If SEO title is the only thing used in the URL
                        $preg_search = '(.*)';
                    }
                    //echo 'SEO URI: '.$SEO_URI.'<br />';
                    //echo 'PREG : '.$preg_search.'<br />';
                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    if (count($matches) > 1) {
                        $page_seotitle = rawurldecode($matches[1]);
                        //echo 'Page SEO Title: '.$page_seotitle.'<br />';
                        //Lookup SEO Title
                        $sql = 'SELECT pagesmain_id FROM ' . $config['table_prefix'] . 'pagesmain WHERE page_seotitle = ' . $misc->make_db_safe($page_seotitle);
                        $recordsetA = $conn->Execute($sql);
                        if (!$recordsetA) {
                            $misc->log_error($sql);
                        }
                        //echo 'RC: '.$recordsetA->RecordCount();
                        if ($recordsetA->RecordCount() == 1) {
                            $page_id = $recordsetA->fields('pagesmain_id');
                            $_GET['action'] = 'page_display';
                            $_GET['PageID'] = $page_id;
                            $this->set_session_referrer();
                            return;
                        }
                    } else {
                        //  $misc->log_error('No page found for seo title match'. $preg_search);
                    }
                    //blog_seotitle
                }
            }
            if ($action == 'agent' && ($slug == '' || strpos($SEO_URI, $slug) === 0)) {
                if (strpos($uri, '{agent_id}') !== false) {
                    //Ok we have the listing ID
                    $_GET['action'] = 'view_user';
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{agent_id}', '([0-9]*)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*?', $preg_search);
                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    $user_id = $matches[1];
                    $_GET['user'] = $user_id;
                    $this->set_session_referrer();
                    return;
                }
            }
            if ($action == 'blog_tag' && ($slug == '' || strpos($SEO_URI, $slug) === 0)) {
                if (strpos($uri, 'tag_seoname') !== false) {
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{tag_seoname}', '(.*)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*?', $preg_search);

                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    $tag_seotitle = rawurldecode($matches[1]);
                    //Lookup SEO Title
                    $sql = 'SELECT tag_id FROM ' . $config['table_prefix'] . 'blogtags WHERE tag_seoname = ' . $misc->make_db_safe($tag_seotitle);
                    $recordsetA = $conn->Execute($sql);
                    if (!$recordsetA) {
                        $misc->log_error($sql);
                    }
                    if ($recordsetA->RecordCount() == 1) {
                        $tag_id = $recordsetA->fields('tag_id');
                        $_GET['action'] = 'blog_index';
                        $_GET['tag_id'] = $tag_id;
                        $this->set_session_referrer();
                        return;
                    }
                }
            }
            if ($action == 'blog_cat' && ($slug == '' || strpos($SEO_URI, $slug) === 0)) {
                if (strpos($uri, 'cat_id') !== false) {
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{cat_id}', '([0-9]*)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*?', $preg_search);
                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    $cat_id = rawurldecode($matches[1]);
                    $_GET['action'] = 'blog_index';
                    $_GET['cat_id'] = $cat_id;
                    $this->set_session_referrer();
                    return;
                }
                if (strpos($uri, 'cat_seoname') !== false) {
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{cat_seoname}', '(.*)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*?', $preg_search);
                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    $cat_seotitle = rawurldecode($matches[1]);
                    //Lookup SEO Title
                    $sql = 'SELECT category_id FROM ' . $config['table_prefix'] . 'blogcategory WHERE category_seoname = ' . $misc->make_db_safe($cat_seotitle);
                    $recordsetA = $conn->Execute($sql);
                    if (!$recordsetA) {
                        $misc->log_error($sql);
                    }
                    if ($recordsetA->RecordCount() == 1) {
                        $cat_id = $recordsetA->fields('category_id');
                        $_GET['action'] = 'blog_index';
                        $_GET['cat_id'] = $cat_id;
                        $this->set_session_referrer();
                        return;
                    }
                }
            }
            if ($action == 'listing_image' && ($slug == '' || strpos($SEO_URI, $slug) === 0)) {
                if (strpos($uri, '{image_id}') !== false) {
                    //Ok we have the listing ID
                    $_GET['action'] = 'view_listing_image';
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{image_id}', '([0-9]*)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*?', $preg_search);
                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    $image_id = $matches[1];
                    $_GET['image_id'] = $image_id;
                    $this->set_session_referrer();
                    return;
                }
            }
            if ($action == 'css' && ($slug == '' || strpos($SEO_URI, $slug) === 0)) {
                if (strpos($uri, '{css_name}') !== false) {
                    //Ok we have the listing ID
                    $_GET['action'] = 'load_css';
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{css_name}', '(.*?)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*?', $preg_search);
                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    $css_name = $matches[1];
                    $_GET['css_file'] = $css_name;
                    return;
                }
            }
            if ($action == 'rss' && ($slug == '' || strpos($SEO_URI, $slug) === 0)) {
                if (strpos($uri, '{rss_feed}') !== false) {
                    //Ok we have the listing ID
                    $_GET['action'] = 'show_rss';
                    $preg_search = $slug . $uri;
                    $preg_search = preg_replace('/[\\\+\*\?\[\^\]\$\(\)\=\!\<\>\|\:\-\/\.]/', '\\\${0}', $preg_search);
                    $preg_search = str_replace('{rss_feed}', '(.*)', $preg_search);
                    $preg_search = preg_replace('/\{(.*?)\}/', '.*?', $preg_search);
                    preg_match('/' . $preg_search . '/', $SEO_URI, $matches);
                    $rss_feed = $matches[1];
                    $_GET['rss_feed'] = $rss_feed;
                    $this->set_session_referrer();
                    return;
                }
            }
            //
            if ($action == 'searchpage' && $SEO_URI == $slug . $uri) {
                $_GET['action'] = 'searchpage';
                $this->set_session_referrer();
                return;
            }
            if ($action == 'index' && $SEO_URI == $slug . $uri) {
                $_GET['action'] = 'index';
                $this->set_session_referrer();
                return;
            }
            if ($action == 'searchresults' && $SEO_URI == $slug . $uri) {
                $_GET['action'] = 'searchresults';
                $this->set_session_referrer();
                return;
            }
            if ($action == 'blogindex' && $SEO_URI == $slug . $uri) {
                $_GET['action'] = 'blog_index';
                $this->set_session_referrer();
                return;
            }
            if ($action == 'view_agents' && $SEO_URI == $slug . $uri) {
                $_GET['action'] = 'view_users';
                $this->set_session_referrer();
                return;
            }
            if ($action == 'logout' && $SEO_URI == $slug . $uri) {
                $_GET['action'] = 'logout';
                $this->set_session_referrer();
                return;
            }
            if ($action == 'member_signup' && $SEO_URI == $slug . $uri) {
                $_GET['action'] = 'signup';
                $_GET['type'] = 'member';
                $this->set_session_referrer();
                return;
            }
            if ($action == 'agent_signup' && $SEO_URI == $slug . $uri) {
                $_GET['action'] = 'signup';
                $_GET['type'] = 'agent';
                $this->set_session_referrer();
                return;
            }
            if ($action == 'member_login' && $SEO_URI == $slug . $uri) {
                $_GET['action'] = 'member_login';
                $this->set_session_referrer();
                return;
            }
            if ($action == 'view_favorites' && $SEO_URI == $slug . $uri) {
                $_GET['action'] = 'view_favorites';
                $this->set_session_referrer();
                return;
            }
            if ($action == 'calculator' && $SEO_URI == $slug . $uri) {
                $_GET['action'] = 'calculator';
                $_GET['popup'] = 'yes';
                $this->set_session_referrer();
                return;
            }
            if ($action == 'saved_searches' && $SEO_URI == $slug . $uri) {
                $_GET['action'] = 'view_saved_searches';
                $this->set_session_referrer();
                return;
            }
            if ($action == 'edit_profile' && $SEO_URI == $slug . $uri) {
                if (isset($_SESSION['userID'])) {
                    $_GET['action'] = 'edit_profile';
                    $_GET['user_id'] = intval($_SESSION['userID']);
                    $this->set_session_referrer();
                    return;
                }
            }
            $recordSet->MoveNext();
        }
        if (!isset($_GET['action'])) {
            $_GET['action'] = 'notfound';
        }
    }

    public function auto_replace_tags($section = '', $admin = false)
    {
        if ($section == '') {
            $section = $this->page;
        }
        if ($admin == true) {
            //We need to skip tags in forms in the admin or controlpanel, etc will not render correctly.
            $section = preg_replace('/<form.*?form>/si', '', $section);
            preg_match_all('/{(?!lang_)(.\S*?)}/i', $section, $tags_found);
            $tags_found = $tags_found[1];
            $tags_found[] = 'csrf_token';
            $tags_special = ['content', 'site_title'];
            $tags_found = array_diff($tags_found, $tags_special);
            foreach ($tags_found as $x => $y) {
                if (strpos($y, 'load_') === 0 || strpos($y, 'check_') === 0 || strpos($y, '/check_') === 0 || strpos($y, '!check_') === 0 || strpos($y, '/!check_') === 0) {
                    unset($tags_found[$x]);
                }
            }
        } else {
            preg_match_all('/{(?!lang_|\/)(.\S*?)}/i', $section, $tags_found);
            $tags_found = $tags_found[1];
            $tags_special = ['content', 'site_title'];
            $tags_found = array_diff($tags_found, $tags_special);
            foreach ($tags_found as $x => $y) {
                if (strpos($y, 'load_') === 0 || strpos($y, 'validate:{') === 0) {
                    unset($tags_found[$x]);
                }
            }
        }
        $tags_found = array_unique($tags_found);
        $this->replace_tags($tags_found);
    }

    public function replace_tags($tags = [])
    {
        return;
    }

    public function get_addon_template_field_list($addons)
    {
        global $config;
        $template_list = [];
        foreach ($addons as $addon) {
            $addon_file = $config['basepath'] . '/addons/' . $addon . '/addon.inc.php';
            if (file_exists($addon_file)) {
                include_once $addon_file;
                $function_name = $addon . '_load_template';
                $addon_fields = $function_name();
                if (is_array($addon_fields)) {
                    $template_list = array_merge($template_list, $addon_fields);
                }
            }
        }
        return $template_list;
    }

    public function load_addons()
    {
        global $config;
        // Get Addon List
        $dir = 0;
        $options = [];
        if ($handle = opendir($config['basepath'] . '/addons')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..' && $file != 'CVS' && $file != '.svn') {
                    if (is_dir($config['basepath'] . '/addons/' . $file)) {
                        $options[$file] = $file;
                        $dir++;
                    }
                }
            }
            closedir($handle);
        }
        return $options;
    }

    public function replace_blog_template_tags()
    {
        global $config, $lang;
        include_once $config['basepath'] . '/include/blog_functions.inc.php';
        $blog_functions = new blog_functions();
        include_once $config['basepath'] . '/include/user.inc.php';
        $userclass = new user();
        //Deal with Blog Archive Links
        $archives = $blog_functions->get_archive_list();
        $html = $this->get_template_section('blog_archive_link_block');
        //Get Latest Blog Posts
        $new_html = '';
        foreach ($archives as $date) {
            $new_html .= $html;
            $date_array = explode('/', $date);
            $year = $date_array[0];
            $month = $date_array[1];
            $display_month = $lang[$month];
            $new_html = str_replace('{blog_archive_title}', htmlentities($display_month . ' ' . $year), $new_html);
            $url = $this->magicURIGenerator('blog_archive', $date, true);
            $new_html = str_replace('{blog_archive_url}', $url, $new_html);
        }
        $this->replace_template_section('blog_archive_link_block', $new_html);
        //Display Blog Cat Link
        $cats = $blog_functions->get_blog_categories_flat();
        $html = $this->get_template_section('blog_category_link_block');
        //Get Latest Blog Posts
        $new_html = '';
        foreach ($cats as $id => $title) {
            $new_html .= $html;
            $new_html = str_replace('{blog_cat_title}', htmlentities($title, ENT_QUOTES, 'UTF-8'), $new_html);
            $url = $this->magicURIGenerator('blog_cat', $id, true);
            $new_html = str_replace('{blog_cat_url}', $url, $new_html);
        }
        $this->replace_template_section('blog_category_link_block', $new_html);

        $html = $this->get_template_section('blog_recent_post_block');
        //Get Latest Blog Posts
        $posts = $blog_functions->get_recent_blog_posts();
        $new_html = '';
        foreach ($posts as $id => $title) {
            $new_html .= $html;
            $new_html = str_replace('{blog_id}', $id, $new_html);
            $new_html = str_replace('{blog_recent_post_title}', htmlentities($title, ENT_QUOTES, 'UTF-8'), $new_html);
            $url = $this->magicURIGenerator('blog', $id, true);
            $new_html = str_replace('{blog_recent_post_url}', $url, $new_html);
        }
        $this->replace_template_section('blog_recent_post_block', $new_html);
        //blog_recent_comments_block
        $html = $this->get_template_section('blog_recent_comments_block');
        //Get Latest Blog Posts
        $comments = $blog_functions->get_recent_blog_comments();
        $new_html = '';
        foreach ($comments as $id => $comment_array) {
            $new_html .= $html;
            $title = $comment_array['text'];
            $blog_id = $comment_array['blog_id'];
            $user_id = $comment_array['userdb_id'];
            $blog_title = $blog_functions->get_blog_title($blog_id);

            $author_type = $userclass->get_user_type($user_id);
            if ($author_type == 'member') {
                $author_display = $userclass->get_user_single_item('userdb_user_name', $user_id);
            } else {
                $author_display = $userclass->get_user_single_item('userdb_user_first_name', $user_id) . ' ' . $userclass->get_user_single_item('userdb_user_last_name', $user_id);
            }
            $new_html = str_replace('{blog_recent_comments_title}', htmlentities($author_display . ' - ' . $blog_title, ENT_QUOTES, 'UTF-8'), $new_html);

            $url = $this->magicURIGenerator('blog', $blog_id, true);
            $url = $url . '#comment' . $id;
            $new_html = str_replace('{blog_recent_comments_url}', $url, $new_html);
        }
        $this->replace_template_section('blog_recent_comments_block', $new_html);
    }

    // This function should be called first, it checks that the page exsists and sets
    // up the page variable for the other functions
    // DEPRECATE
    public function load_page($template = '', $parse = false, $poweredby_check = false)
    {
        global $config;
        $file = false;
        $admin = false;
        if (strpos($template, $config['admin_template_path']) !== false) {
            $file = str_replace($config['admin_template_path'], '', $template);
            $admin = true;
        } elseif (strpos($template, $config['template_path']) !== false) {
            $file = str_replace($config['template_path'], '', $template);
        }
        if ($file === false) {
            die('Load Page (' . $admin . ') ' . htmlentities($template) . ' not found');
        } else {
            $this->load_file($file, $admin, $parse, $poweredby_check);
        }
    }

    public function load_addon_file($addon_name, $file)
    {
        global $config, $jscript;
        if (preg_match('/[^A-Za-z0-9_\.\-\/]/', $file) && strpos($file, '..') !== false) {
            //File name contains non alphanum chars die to prevent file system attacks.
            die('Load File: File Security Error');
        }
        if (preg_match('/[^A-Za-z0-9_\.\-\/]/', $addon_name) && strpos($addon_name, '..') !== false) {
            //File name contains non alphanum chars die to prevent file system attacks.
            die('Load File: Addon Security Error');
        }
        $my_file = $config['basepath'] . '/addons/' . $addon_name . '/template/' . $file;
        if (!file_exists($my_file)) {
            die('Template file ' . htmlentities($my_file) . ' not found');
        }
        $this->page = file_get_contents($my_file);
    }

    public function load_file($file, $admin = false, $parse = false, $poweredby_check = false)
    {
        global $config, $jscript, $jscript_last;
        if (preg_match('/[^A-Za-z0-9_\.\-\/]/', $file) && strpos($file, '..') !== false) {
            //File name contains non alphanum chars die to prevent file system attacks.
            die('Load File: File Security Error');
        }
        //Determine file path.
        if ($admin) {
            if (file_exists($config['admin_template_path'] . '/' . $file)) {
                $my_file = $config['admin_template_path'] . '/' . $file;
            } elseif (file_exists($config['basepath'] . '/admin/template/default/' . $file)) {
                $my_file = $config['basepath'] . '/admin/template/default/' . $file;
            } else {
                die('Admin Template file ' . htmlentities($file) . ' not found');
            }
        } else {
            if (file_exists($config['template_path'] . '/' . $file)) {
                $my_file = $config['template_path'] . '/' . $file;
            } elseif (file_exists($config['basepath'] . '/template/default/' . $file)) {
                $my_file = $config['basepath'] . '/template/default/' . $file;
            } else {
                die('Template file ' . htmlentities($file) . ' not found');
            }
        }

        if ($parse == false) {
            $this->page = file_get_contents($my_file);
        } else {
            $this->page = $this->parse($my_file);
        }
    }

    // This function allows us to parse the file allowing us to have php directives
    public function parse($file)
    {
        ob_start();
        include_once $file;
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

    public function get_template_section_row($section_name)
    {
        if (!empty($section_name)) {
            $section = '/{' . $section_name . ' repeat="([0-9]{1,3})"}(.*?){\/' . $section_name . '}/is';
            $section_results = [];
            preg_match($section, $this->page, $section_results);
            if (isset($section_results[1])) {
                return [$section_results[2], $section_results[1]];
            }
        }
    }

    public function get_template_section($section_name, $page = '')
    {
        if (!empty($section_name)) {
            $section = '/{' . $section_name . '}(.*?){\/' . $section_name . '}/is';
            $section_results = [];
            if ($page == '') {
                $page = $this->page;
            }
            preg_match($section, $page, $section_results);
            if (isset($section_results[1])) {
                return $section_results[1];
            }
            return false;
        }
    }

    public function cleanup_template_block($block, $section)
    {
        $section = str_replace('{' . $block . '_block}', '', $section);
        $section = str_replace('{/' . $block . '_block}', '', $section);
        return $section;
    }

    public function remove_template_block($block, $section)
    {
        $find_block = '/{' . $block . '_block}(.*?){\/' . $block . '_block}/is';
        $section = preg_replace($find_block, '', $section);
        return $section;
    }

    public function form_options($options, $selected_value, $template_section)
    {
        $html_replace = '';
        foreach ($options as $value => $text) {
            //Yes Option
            $html_replace .= $template_section;
            if (is_array($selected_value)) {
                if (in_array($value, $selected_value)) {
                    $html_replace = str_replace('{selected}', 'selected="selected"', $html_replace);
                } else {
                    $html_replace = str_replace('{selected}', '', $html_replace);
                }
            } else {
                if ($selected_value == $value) {
                    $html_replace = str_replace('{selected}', 'selected="selected"', $html_replace);
                } else {
                    $html_replace = str_replace('{selected}', '', $html_replace);
                }
            }
            $html_replace = str_replace('{value}', $value, $html_replace);
            $html_replace = str_replace('{text}', $text, $html_replace);
        }
        return $html_replace;
    }

    // Use replace_tag_safe or replace_tag_unsafe instead
    // @deprecated
    public function replace_tag($tag, $replacement)
    {
        $this->page = str_replace('{' . $tag . '}', $replacement, $this->page);
    }
    //Does not sanitize before doing replacement, use with caution will allow html injection
    public function replace_tag_unsafe($tag, $replacement)
    {
        $this->page = str_replace('{' . $tag . '}', $replacement, $this->page);
    }
    //Replaces tag with sanitized version of replacement
    public function replace_tag_safe($tag, $replacement, $page = '')
    {
        if ($page == '') {
            $this->page = str_replace('{' . $tag . '}', htmlentities($replacement), $this->page);
        } else {
            return str_replace('{' . $tag . '}', htmlentities($replacement), $page);
        }
    }

    public function parse_template_section($section_as_variable, $field, $value)
    {
        $section_as_variable = str_replace('{' . $field . '}', $value, $section_as_variable);
        $section_as_variable = $this->cleanup_template_block($field, $section_as_variable);
        return $section_as_variable;
    }

    public function replace_template_section($section_name, $replacement, $page = '')
    {
        $section = '/{' . $section_name . '}(.*?){\/' . $section_name . '}/is';
        $replacement = str_replace('$', '\$', $replacement);
        if ($page == '') {
            $this->page = preg_replace($section, $replacement, $this->page);
        } else {
            $page = preg_replace($section, $replacement, $page);
            return $page;
        }
    }

    public function replace_template_section_row($section_name, $replacement)
    {
        $section = '/{' . $section_name . ' (.*?)}(.*?){\/' . $section_name . '}/is';
        $replacement = str_replace('$', '\$', $replacement);
        $this->page = preg_replace($section, $replacement, $this->page);
    }

    // This function is used to cleanup any indivdual image or thumbnail tags on the search
    //result page that were not filled with data. It should be run after every data row.
    public function cleanup_images($section)
    {
        $section = preg_replace('/{(.*?)image_(.*?)}/', '', $section);
        $section = preg_replace('/{listing_agent_thumbnail_(.*?)}/', '', $section);
        return $section;
    }

    public function output_page()
    {
        print $this->page;
    }

    // This function returns the results for sub templates
    public function return_page()
    {
        return $this->page;
    }

    public function replace_current_user_tags()
    {
        global $config;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->verify_priv('Member');
        if ($login_status) {
            include_once $config['basepath'] . '/include/user.inc.php';
            $user = new user();
            $user_id = intval($_SESSION['userID']);
            $this->page = str_replace('{current_user_id}', $user_id, $this->page);
            $this->page = str_replace('{current_user_first_name}', $user->get_user_single_item('userdb_user_first_name', $user_id), $this->page);
            $this->page = str_replace('{current_user_last_name}', $user->get_user_single_item('userdb_user_last_name', $user_id), $this->page);
        }
    }

    public function replace_permission_tags()
    {
        global $config, $misc;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        //Check to see if this si a mobile template
        $safe_action = "index";
        if (isset($_GET['action'])) {
            $safe_action = preg_replace("/[^[:alnum:][:space:]_\-]+/", "", $_GET['action']);
        }
        //Check for this action
        // Use pregreplace to removed {check_agent} tags and content between them
        $this->page = preg_replace('/{!check_action_' . $safe_action . '}(.*?){\/!check_action_' . $safe_action . '}/is', '', $this->page);
        $this->page = str_replace('{check_action_' . $safe_action . '}', '', $this->page);
        $this->page = str_replace('{/check_action_' . $safe_action . '}', '', $this->page);

        //Clear other actions
        // Use pregreplace to removed {check_agent} tags and content between them
        // Use strreplace to remove {check_agent} tags and leave the content.
        $this->page = preg_replace('/{check_action_.*?}(.*?){\/check_action_.*?}/is', '', $this->page);
        $this->page = preg_replace('/{!check_action_.*?}/', '', $this->page);
        $this->page = preg_replace('/{\/!check_action_.*?}/', '', $this->page);

        if ($config['allow_agent_signup'] == 'yes') {
            $this->page = preg_replace('/{!check_allow_agent_signup}(.*?){\/!check_allow_agent_signup}/is', '', $this->page);
            $this->page = str_replace('{check_allow_agent_signup}', '', $this->page);
            $this->page = str_replace('{/check_allow_agent_signup}', '', $this->page);
        } else {
            $this->page = preg_replace('/{check_allow_agent_signup}(.*?){\/check_allow_agent_signup}/is', '', $this->page);
            $this->page = str_replace('{!check_allow_agent_signup}', '', $this->page);
            $this->page = str_replace('{/!check_allow_agent_signup}', '', $this->page);
        }

        $is_mobile = $misc->detect_mobile_browser();
        if ($is_mobile !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_mobile}(.*?){\/check_mobile}/is', '', $this->page);
            $this->page = str_replace('{!check_mobile}', '', $this->page);
            $this->page = str_replace('{/!check_mobile}', '', $this->page);
        } else {
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = preg_replace('/{!check_mobile}(.*?){\/!check_mobile}/is', '', $this->page);
            $this->page = str_replace('{check_mobile}', '', $this->page);
            $this->page = str_replace('{/check_mobile}', '', $this->page);
        }
        // Check for tags: Admin, Agent, canEditForms, canViewLogs, editpages, havevtours
        $login_status = $login->verify_priv('Agent');
        if ($login_status !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_agent}(.*?){\/check_agent}/is', '', $this->page);
            $this->page = str_replace('{!check_agent}', '', $this->page);
            $this->page = str_replace('{/!check_agent}', '', $this->page);
        } else {
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = preg_replace('/{!check_agent}(.*?){\/!check_agent}/is', '', $this->page);
            $this->page = str_replace('{check_agent}', '', $this->page);
            $this->page = str_replace('{/check_agent}', '', $this->page);
        }
        $login_status = $login->verify_priv('Member');
        if ($login_status !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_member}(.*?){\/check_member}/is', '', $this->page);
            $this->page = str_replace('{!check_member}', '', $this->page);
            $this->page = str_replace('{/!check_member}', '', $this->page);
            $this->page = str_replace('{check_guest}', '', $this->page);
            $this->page = str_replace('{/check_guest}', '', $this->page);
        } else {
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = preg_replace('/{!check_member}(.*?){\/!check_member}/is', '', $this->page);
            $this->page = str_replace('{check_member}', '', $this->page);
            $this->page = str_replace('{/check_member}', '', $this->page);
            $this->page = preg_replace('/{check_guest}(.*?){\/check_guest}/is', '', $this->page);
        }
        $login_status = $login->verify_priv('Admin');
        if ($login_status !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_moderate_listings}(.*?){\/check_moderate_listings}/is', '', $this->page);
            $this->page = str_replace('{!check_moderate_listings}', '', $this->page);
            $this->page = str_replace('{/!check_moderate_listings}', '', $this->page);
            $this->page = str_replace('{!check_admin}', '', $this->page);
            $this->page = str_replace('{/!check_admin}', '', $this->page);
            $this->page = preg_replace('/{check_admin}(.*?){\/check_admin}/is', '', $this->page);
        } else {
            if ($config['moderate_listings'] === '1') {
                $this->page = str_replace('{check_moderate_listings}', '', $this->page);
                $this->page = str_replace('{/check_moderate_listings}', '', $this->page);
                $this->page = preg_replace('/{!check_moderate_listings}(.*?){\/!check_moderate_listings}/is', '', $this->page);
            } else {
                $this->page = str_replace('{!check_moderate_listings}', '', $this->page);
                $this->page = str_replace('{/!check_moderate_listings}', '', $this->page);
                $this->page = preg_replace('/{check_moderate_listings}(.*?){\/check_moderate_listings}/is', '', $this->page);
            }
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = str_replace('{check_admin}', '', $this->page);
            $this->page = str_replace('{/check_admin}', '', $this->page);
            $this->page = preg_replace('/{!check_admin}(.*?){\/!check_admin}/is', '', $this->page);
        }
        $login_status = $login->verify_priv('edit_site_config');
        if ($login_status !== true) {
            $this->page = preg_replace('/{check_edit_site_config}(.*?){\/check_edit_site_config}/is', '', $this->page);
            $this->page = str_replace('{!check_edit_site_config}', '', $this->page);
            $this->page = str_replace('{/!check_edit_site_config}', '', $this->page);
        } else {
            $this->page = str_replace('{check_edit_site_config}', '', $this->page);
            $this->page = str_replace('{/check_edit_site_config}', '', $this->page);
            $this->page = preg_replace('/{!check_edit_site_config}(.*?){\/!check_edit_site_config}/is', '', $this->page);
        }
        $login_status = $login->verify_priv('edit_member_template');
        if ($login_status !== true) {
            $this->page = preg_replace('/{check_edit_member_template}(.*?){\/check_edit_member_template}/is', '', $this->page);
            $this->page = str_replace('{!check_edit_member_template}', '', $this->page);
            $this->page = str_replace('{/!check_edit_member_template}', '', $this->page);
        } else {
            $this->page = str_replace('{check_edit_member_template}', '', $this->page);
            $this->page = str_replace('{/check_edit_member_template}', '', $this->page);
            $this->page = preg_replace('/{!check_edit_member_template}(.*?){\/!check_edit_member_template}/is', '', $this->page);
        }
        $login_status = $login->verify_priv('edit_agent_template');
        if ($login_status !== true) {
            $this->page = preg_replace('/{check_edit_agent_template}(.*?){\/check_edit_agent_template}/is', '', $this->page);
            $this->page = str_replace('{!check_edit_agent_template}', '', $this->page);
            $this->page = str_replace('{/!check_edit_agent_template}', '', $this->page);
        } else {
            $this->page = str_replace('{check_edit_agent_template}', '', $this->page);
            $this->page = str_replace('{/check_edit_agent_template}', '', $this->page);
            $this->page = preg_replace('/{!check_edit_agent_template}(.*?){\/!check_edit_agent_template}/is', '', $this->page);
        }
        $login_status = $login->verify_priv('edit_listing_template');
        if ($login_status !== true) {
            $this->page = preg_replace('/{check_edit_listing_template}(.*?){\/check_edit_listing_template}/is', '', $this->page);
            $this->page = str_replace('{!check_edit_listing_template}', '', $this->page);
            $this->page = str_replace('{/!check_edit_listing_template}', '', $this->page);
        } else {
            $this->page = str_replace('{check_edit_listing_template}', '', $this->page);
            $this->page = str_replace('{/check_edit_listing_template}', '', $this->page);
            $this->page = preg_replace('/{!check_edit_listing_template}(.*?){\/!check_edit_listing_template}/is', '', $this->page);
        }
        $login_status = $login->verify_priv('canViewLogs');
        if ($login_status !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_view_logs}(.*?){\/check_view_logs}/is', '', $this->page);
            $this->page = str_replace('{!check_view_logs}', '', $this->page);
            $this->page = str_replace('{/!check_view_logs}', '', $this->page);
        } else {
            $this->page = preg_replace('/{!check_view_logs}(.*?){\/!check_view_logs}/is', '', $this->page);
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = str_replace('{check_view_logs}', '', $this->page);
            $this->page = str_replace('{/check_view_logs}', '', $this->page);
        }
        $login_status = $login->verify_priv('editpages');
        if ($login_status !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_edit_pages}(.*?){\/check_edit_pages}/is', '', $this->page);
            $this->page = str_replace('{!check_edit_pages}', '', $this->page);
            $this->page = str_replace('{/!check_edit_pages}', '', $this->page);
        } else {
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = str_replace('{check_edit_pages}', '', $this->page);
            $this->page = str_replace('{/check_edit_pages}', '', $this->page);
            $this->page = preg_replace('/{!check_edit_pages}(.*?){\/!check_edit_pages}/is', '', $this->page);
        }
        $login_status = $login->verify_priv('edit_all_listings');
        if ($login_status !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_edit_all_listings}(.*?){\/check_edit_all_listings}/is', '', $this->page);
            $this->page = str_replace('{!check_edit_all_listings}', '', $this->page);
            $this->page = str_replace('{/!check_edit_all_listings}', '', $this->page);
        } else {
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = str_replace('{check_edit_all_listings}', '', $this->page);
            $this->page = str_replace('{/check_edit_all_listings}', '', $this->page);
            $this->page = preg_replace('/{!check_edit_all_listings}(.*?){\/!check_edit_all_listings}/is', '', $this->page);
        }
        $login_status = $login->verify_priv('edit_all_users');
        if ($login_status !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_edit_all_users}(.*?){\/check_edit_all_users}/is', '', $this->page);
            $this->page = str_replace('{!check_edit_all_users}', '', $this->page);
            $this->page = str_replace('{/!check_edit_all_users}', '', $this->page);
        } else {
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = str_replace('{check_edit_all_users}', '', $this->page);
            $this->page = str_replace('{/check_edit_all_users}', '', $this->page);
            $this->page = preg_replace('/{!check_edit_all_users}(.*?){\/!check_edit_all_users}/is', '', $this->page);
        }
        $login_status = $login->verify_priv('edit_property_classes');
        if ($login_status !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_edit_listing_classes}(.*?){\/check_edit_listing_classes}/is', '', $this->page);
            $this->page = str_replace('{!check_edit_listing_classes}', '', $this->page);
            $this->page = str_replace('{/!check_edit_listing_classes}', '', $this->page);
        } else {
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = str_replace('{check_edit_listing_classes}', '', $this->page);
            $this->page = str_replace('{/check_edit_listing_classes}', '', $this->page);
            $this->page = preg_replace('/{!check_edit_listing_classes}(.*?){\/!check_edit_listing_classes}/is', '', $this->page);
        }
        $login_status = $login->verify_priv('havevtours');
        if ($login_status !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_have_vtours}(.*?){\/check_have_vtours}/is', '', $this->page);
            $this->page = str_replace('{!check_have_vtours}', '', $this->page);
            $this->page = str_replace('{/!check_have_vtours}', '', $this->page);
        } else {
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = preg_replace('/{!check_have_vtours}(.*?){\/!check_have_vtours}/is', '', $this->page);
            $this->page = str_replace('{check_have_vtours}', '', $this->page);
            $this->page = str_replace('{/check_have_vtours}', '', $this->page);
        }
        $login_status = $login->verify_priv('havefiles');
        if ($login_status !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_have_files}(.*?){\/check_have_files}/is', '', $this->page);
            $this->page = str_replace('{!check_have_files}', '', $this->page);
            $this->page = str_replace('{/!check_have_files}', '', $this->page);
        } else {
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = preg_replace('/{!check_have_files}(.*?){\/!check_have_files}/is', '', $this->page);
            $this->page = str_replace('{check_have_files}', '', $this->page);
            $this->page = str_replace('{/check_have_files}', '', $this->page);
        }
        if (isset($_GET['printer_friendly']) && $_GET['printer_friendly'] == 'yes') {
            $this->page = preg_replace('/{hide_printer_friendly}(.*?){\/hide_printer_friendly}/is', '', $this->page);
            $this->page = str_replace('{show_printer_friendly}', '', $this->page);
            $this->page = str_replace('{/show_printer_friendly}', '', $this->page);
        } else {
            $this->page = preg_replace('/{show_printer_friendly}(.*?){\/show_printer_friendly}/is', '', $this->page);
            $this->page = str_replace('{hide_printer_friendly}', '', $this->page);
            $this->page = str_replace('{/hide_printer_friendly}', '', $this->page);
        }
        $login_status = $login->verify_priv('can_manage_addons');
        if ($login_status !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_addon_manager}(.*?){\/check_addon_manager}/is', '', $this->page);
            $this->page = str_replace('{!check_addon_manager}', '', $this->page);
            $this->page = str_replace('{/!check_addon_manager}', '', $this->page);
        } else {
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = preg_replace('/{!check_addon_manager}(.*?){\/!check_addon_manager}/is', '', $this->page);
            $this->page = str_replace('{check_addon_manager}', '', $this->page);
            $this->page = str_replace('{/check_addon_manager}', '', $this->page);
        }
        //can_access_blog_manager
        $login_status = $login->verify_priv('can_access_blog_manager');
        if ($login_status !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_access_blog_manager}(.*?){\/check_access_blog_manager}/is', '', $this->page);
            $this->page = str_replace('{!check_access_blog_manager}', '', $this->page);
            $this->page = str_replace('{/!check_access_blog_manager}', '', $this->page);
        } else {
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = preg_replace('/{!check_access_blog_manager}(.*?){\/!check_access_blog_manager}/is', '', $this->page);
            $this->page = str_replace('{check_access_blog_manager}', '', $this->page);
            $this->page = str_replace('{/check_access_blog_manager}', '', $this->page);
        }
        $login_status = $login->verify_priv('is_blog_editor');
        if ($login_status !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_is_blog_editor}(.*?){\/check_is_blog_editor}/is', '', $this->page);
            $this->page = str_replace('{!check_is_blog_editor}', '', $this->page);
            $this->page = str_replace('{/!check_is_blog_editor}', '', $this->page);
        } else {
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = preg_replace('/{!check_is_blog_editor}(.*?){\/!check_is_blog_editor}/is', '', $this->page);
            $this->page = str_replace('{check_is_blog_editor}', '', $this->page);
            $this->page = str_replace('{/check_is_blog_editor}', '', $this->page);
        }
        $login_status = $login->verify_priv('edit_all_leads');
        if ($login_status !== true) {
            // Use pregreplace to removed {check_agent} tags and content between them
            $this->page = preg_replace('/{check_edit_all_leads}(.*?){\/check_edit_all_leads}/is', '', $this->page);
            $this->page = str_replace('{!check_edit_all_leads}', '', $this->page);
            $this->page = str_replace('{/!check_edit_all_leads}', '', $this->page);
        } else {
            // Use strreplace to remove {check_agent} tags and leave the content.
            $this->page = str_replace('{check_edit_all_leads}', '', $this->page);
            $this->page = str_replace('{/check_edit_all_leads}', '', $this->page);
            $this->page = preg_replace('/{!check_edit_all_leads}(.*?){\/!check_edit_all_leads}/is', '', $this->page);
        }
        $login_status = $login->verify_priv('edit_lead_template');
        if ($login_status !== true) {
            $this->page = preg_replace('/{check_edit_lead_template}(.*?){\/check_edit_lead_template}/is', '', $this->page);
            $this->page = str_replace('{!check_edit_lead_template}', '', $this->page);
            $this->page = str_replace('{/!check_edit_lead_template}', '', $this->page);
        } else {
            $this->page = str_replace('{check_edit_lead_template}', '', $this->page);
            $this->page = str_replace('{/check_edit_lead_template}', '', $this->page);
            $this->page = preg_replace('/{!check_edit_lead_template}(.*?){\/!check_edit_lead_template}/is', '', $this->page);
        }
    }

    public function replace_urls()
    {
        global $config;
        //New Style URL
        $this->page = preg_replace_callback(
            '/{blog_link_([0-9]*)}/is',
            function ($matches) {
                global $config;
                include_once $config['basepath'] . '/include/core.inc.php';
                $page = new page_user();
                $title = $page->magicURIGenerator('blog', $matches[1], true);
                return $title;
            },
            $this->page
        );

        $this->page = preg_replace_callback(
            '/{page_link_([0-9]*)}/is',
            function ($matches) {
                global $config;
                include_once $config['basepath'] . '/include/core.inc.php';
                $page = new page_user();
                $title = $page->magicURIGenerator('page', $matches[1], true);
                return $title;
            },
            $this->page
        );

        $this->page = preg_replace_callback(
            '/{rss_([[a-zA-Z0-9_-]*)}/is',
            function ($matches) {
                global $config;
                include_once $config['basepath'] . '/include/core.inc.php';
                $page = new page_user();
                $title = $page->magicURIGenerator('rss', $matches[1], true);
                return $title;
            },
            $this->page
        );

        $this->page = str_replace('{url_view_agents}', $this->magicURIGenerator('view_agents', null, true), $this->page);
        $this->page = str_replace('{url_search}', $this->magicURIGenerator('searchpage', null, true), $this->page);
        $this->page = str_replace('{url_blog}', $this->magicURIGenerator('blogindex', null, true), $this->page);
        $this->page = str_replace('{url_index}', $this->magicURIGenerator('index', null, true), $this->page);
        $this->page = str_replace('{url_search_results}', $this->magicURIGenerator('searchresults', null, true), $this->page);
        $this->page = str_replace('{url_logout}', $this->magicURIGenerator('logout', null, true), $this->page);
        $this->page = str_replace('{url_member_signup}', $this->magicURIGenerator('member_signup', null, true), $this->page);
        $this->page = str_replace('{url_agent_signup}', $this->magicURIGenerator('agent_signup', null, true), $this->page);
        $this->page = str_replace('{url_member_login}', $this->magicURIGenerator('member_login', null, true), $this->page);
        $this->page = str_replace('{url_view_favorites}', $this->magicURIGenerator('view_favorites', null, true), $this->page);
        $this->page = str_replace('{url_view_calculator}', $this->magicURIGenerator('calculator', null, true), $this->page);
        $this->page = str_replace('{url_view_saved_searches}', $this->magicURIGenerator('saved_searches', null, true), $this->page);
        $this->page = str_replace('{url_edit_profile}', $this->magicURIGenerator('edit_profile', null, true), $this->page);
        //Old Style URLs
        $this->page = preg_replace('/{url_search_class_(.*?)}/is', $config['baseurl'] . '/index.php?action=search_step_2&amp;pclass[]=$1', $this->page);
        $this->page = preg_replace('/{url_searchresults_class_(.*?)}/is', $config['baseurl'] . '/index.php?action=searchresults&amp;pclass[]=$1', $this->page);
        $this->page = str_replace('{url_agent_login}', $config['baseurl'] . '/admin/index.php', $this->page);
    }

    public function replace_meta_template_tags()
    {
        global $config, $lang, $meta_follow, $meta_index, $meta_canonical;
        include_once $config['basepath'] . '/include/hooks.inc.php';
        $hooks = new hooks();

        $title = $config['seo_default_title'];
        $description = $config['seo_default_description'];
        $keywords = $config['seo_default_keywords'];

        if ((isset($_GET['listingID'])) && ($_GET['action'] != 'searchresults')) {
            $listing_keywords = $this->replace_listing_field_tags($_GET['listingID'], $config['seo_listing_keywords']);
            $keywords = strip_tags(str_replace(["\r\n", "\r", "\n", '||'], ['', '', '', ','], $listing_keywords));
            $listing_description = $this->replace_listing_field_tags($_GET['listingID'], $config['seo_listing_description']);
            $description = strip_tags(str_replace(["\r\n", "\r", "\n", '||'], ['', '', '', ','], $listing_description));
            $listing_title = $this->replace_listing_field_tags($_GET['listingID'], $config['seo_listing_title']);
            $title = strip_tags(str_replace(["\r\n", "\r", "\n", '||'], ['', '', '', ','], $listing_title));
        } elseif ($_GET['action'] == 'view_users') {
            $title = $config['seo_default_title'] . ' - ' . $lang['menu_view_agents'];
        } elseif ($_GET['action'] == 'view_listing_image') {
            if (isset($_GET['image_id'])) {
                include_once $config['basepath'] . '/include/media.inc.php';
                $media_handler = new media_handler();
                $title = $config['seo_default_title'] . ' - ' . $media_handler->get_media_caption('listingsimages', $_GET['image_id']);
            }
        } elseif (isset($_GET['PageID'])) {
            include_once $config['basepath'] . '/include/page_display.inc.php';
            $page_display = new page_display();
            $title = $page_display->get_page_title($_GET['PageID']);

            $page_description = $page_display->get_page_description($_GET['PageID']);
            if ($page_description != '') {
                $description = $page_description;
            }
            $page_keywords = $page_display->get_page_keywords($_GET['PageID']);
            if ($page_keywords != '') {
                $keywords = $page_keywords;
            }
        } elseif (isset($_GET['cat_id'])) {
            include_once $config['basepath'] . '/include/blog_functions.inc.php';
            $blog_functions = new blog_functions();
            $title = $blog_functions->get_blog_category_name($_GET['cat_id']);
            $cat_description = $blog_functions->get_category_description($_GET['cat_id']);

            if ($cat_description != '') {
                $description = $cat_description;
            }
            $cat_keywords = $blog_functions->get_category_keywords($_GET['cat_id']);
            if ($cat_keywords != '') {
                $keywords = $cat_keywords;
            }
        } elseif (isset($_GET['ArticleID'])) {
            include_once $config['basepath'] . '/include/blog_functions.inc.php';
            $blog_functions = new blog_functions();

            $title = $blog_functions->get_blog_title($_GET['ArticleID']);
            $blog_description = $blog_functions->get_blog_description($_GET['ArticleID']);
            if ($blog_description != '') {
                $description = $blog_description;
            }
            $blog_keywords = $blog_functions->get_blog_keywords($_GET['ArticleID']);
            if ($blog_keywords != '') {
                $keywords = $blog_keywords;
            }
        }
        $hook_result = $hooks->load('replace_meta_template_tags', $_GET['action']);
        if (is_array($hook_result)) {
            if (isset($hook_result['keywords'])) {
                $keywords = $hook_result['keywords'];
            }
            if (isset($hook_result['description'])) {
                $description = $hook_result['description'];
            }
            if (isset($hook_result['title'])) {
                $title = $hook_result['title'];
            }
        }
        $this->page = str_replace('{load_meta_keywords}', '<meta name="keywords" content="' . $keywords . '" />', $this->page);
        $this->page = str_replace('{load_meta_description}', '<meta name="description" content="' . $description . '" />', $this->page);
        $this->page = str_replace('{load_meta_keywords_raw}', $keywords, $this->page);
        $this->page = str_replace('{load_meta_description_raw}', $description, $this->page);

        $description_short = substr(strip_tags($description), 0, 160);
        $description_short = substr($description_short, 0, strrpos($description_short, ' '));

        $this->page = str_replace('{load_meta_description_short_raw}', $description_short, $this->page);
        $this->page = str_replace('{load_meta_description_short}', '<meta name="description" content="' . $description_short . '" />', $this->page);

        if ($meta_canonical != null) {
            $this->page = $this->cleanup_template_block('meta_canonical', $this->page);
            $this->replace_tag('canonical_link', $meta_canonical);
        } else {
            $this->page = $this->remove_template_block('meta_canonical', $this->page);
        }
        if ($meta_follow == true) {
            $this->page = str_replace('{meta_follow}', 'follow', $this->page);
        } else {
            $this->page = str_replace('{meta_follow}', 'nofollow', $this->page);
        }
        if ($meta_index == true) {
            $this->page = str_replace('{meta_index}', 'index', $this->page);
        } else {
            $this->page = str_replace('{meta_index}', 'noindex', $this->page);
        }
        $this->page = str_replace('{site_title}', $title, $this->page);
    }

    public function replace_css_template_tags($admin = false, $temp_section = '')
    {
        global $admin;
        $do_section = false;
        if ($temp_section == '') {
            $do_section = true;
            $temp_section = $this->page;
        }

        $temp_section = preg_replace_callback(
            '/{load_css_(.*?)}/',
            function ($matches) {
                global $config, $admin;
                include_once $config['basepath'] . '/include/core.inc.php';
                $page = new page_user();
                $url = $page->magicURIGenerator('css', $matches['1'], true, $admin);
                return '<link rel="stylesheet" type="text/css" href="' . $url . '" />';
            },
            $temp_section
        );

        if ($do_section == true) {
            $this->page = $temp_section;
        } else {
            return $temp_section;
        }
    }

    public function replace_lang_template_tags($admin = false, $temp_section = '')
    {
        global $lang;
        if ($temp_section == '') {
            $this->page = preg_replace_callback(
                '/{lang_(.*?)}/is',
                function ($matches) {
                    global $lang;
                    if (isset($lang[$matches[1]])) {
                        return $lang[$matches[1]];
                    } else {
                        return 'UNDEFINED_LANG_KEY';
                    }
                },
                $this->page
            );
        } else {
            $temp_section = preg_replace_callback(
                '/{lang_(.*?)}/is',
                function ($matches) {
                    global $lang;
                    return $lang[$matches[1]];
                },
                $temp_section
            );
            return $temp_section;
        }
    }

    public function parse_addon_tags($section_as_variable, $fields)
    {
        if ($section_as_variable == '') {
            $section_as_variable = $this->page;
        }
        //print_r($fields);
        //echo $section_as_variable;
        foreach ($fields as $field) {
            global $config;
            if ($field == '') {
                continue;
            }
            // Make sure that the tag is in the section
            if (strpos($section_as_variable, '{' . $field . '}') !== false) {
                //echo 'Field Found: '.$field;
                $addon_name = [];
                if (preg_match('/^addon_(.\S*?)_.*/', $field, $addon_name)) {
                    include_once $config['basepath'] . '/addons/' . $addon_name[1] . '/addon.inc.php';
                    $function_name = $addon_name[1] . '_run_template_user_fields';
                    $value = $function_name($field);
                    $section_as_variable = str_replace('{' . $field . '}', $value, $section_as_variable);
                }
            }
        }
        return $section_as_variable;
    }

    public function cleanup_template_sections($next_prev = '', $next_prev_bottom = '')
    {
        // Insert Next Prev where needed
        $section = '{next_prev}';
        $this->page = str_replace($section, $next_prev, $this->page);
        $section = '{next_prev_bottom}';
        $this->page = str_replace($section, $next_prev_bottom, $this->page);
        // Renmove any unused blocks
        $section = '/{(.*?)_block}.*?{\/\1_block}/is';
        $this->page = preg_replace($section, '', $this->page);
    }

    public function render_mobile_template_tag()
    {
        global $config, $lang, $misc;
        $display = '';
        if ($config['allow_template_change'] == 1) {
            if ($config['full_template'] != $config['mobile_template']) {
                //echo $config["template"];
                //echo  $config["mobile_template"];
                if ($config['template'] == $config['mobile_template']) {
                    $display = '<form name="or_mobile_template_switch" action="#" method="post" id="or_mobile_template_switch">
                    <input type="hidden" name="token" value="' . $misc->generate_csrf_token() . '" />
                    <input type="hidden" name="select_users_template" value="' . $config['full_template'] . '" />
                    </form> <a href="#" rel="nofollow" onclick="$(\'#or_mobile_template_switch\').submit(); return false;">' . $lang['display_full_site'] . '</a>';
                } else {
                    $display = '<form name="or_mobile_template_switch" action="#" method="post" id="or_mobile_template_switch">
                    <input type="hidden" name="token" value="' . $misc->generate_csrf_token() . '" />
                    <input type="hidden" name="select_users_template" value="' . $config['mobile_template'] . '" /></form> <a href="#" rel="nofollow" onclick="$(\'#or_mobile_template_switch\').submit(); return false;">' . $lang['display_mobile_site'] . '</a>';
                }
            }
        }
        return $display;
    }

    public function replace_search_field_tags($tempate_section = '')
    {
        global $config;
        if ($tempate_section != '') {
            $tsection = true;
        } else {
            $tempate_section = $this->page;
            $tsection = false;
        }
        // Handle Caption Only
        $tempate_section = preg_replace_callback(
            '/{search_field_([^{}]*?)_element}/',
            function ($matches) {
                global $config, $conn, $misc, $lang;
                include_once $config['basepath'] . '/include/search.inc.php';
                $search_page = new search_page();
                $sql_field_name = $misc->make_db_safe($matches[1]);
                //Get Field caption, and searchtype
                $sql = 'SELECT  listingsformelements_id 
						FROM ' . $config['table_prefix'] . "listingsformelements 
						WHERE listingsformelements_field_name = $sql_field_name";
                $rs = $conn->Execute($sql);
                if (!$rs) {
                    $misc->log_error($sql);
                }
                $field_id = $rs->fields('listingsformelements_id');
                if (isset($_GET['pclass'])) {
                    return $search_page->searchbox_render($field_id, $_GET['pclass'], 'element');
                } else {
                    return $search_page->searchbox_render($field_id, [], 'element');
                }
            },
            $tempate_section
        );
        $tempate_section = preg_replace_callback(
            '/{search_field_([^{}]*?)_label}/',
            function ($matches) {
                global $config, $conn, $misc, $lang;
                include_once $config['basepath'] . '/include/search.inc.php';
                $search_page = new search_page();
                $sql_field_name = $misc->make_db_safe($matches[1]);
                //Get Field caption, and searchtype
                $sql = 'SELECT  listingsformelements_id 
						FROM ' . $config['table_prefix'] . "listingsformelements 
						WHERE listingsformelements_field_name = $sql_field_name";
                $rs = $conn->Execute($sql);
                if (!$rs) {
                    $misc->log_error($sql);
                }
                $field_id = $rs->fields('listingsformelements_id');
                if (isset($_GET['pclass'])) {
                    return $search_page->searchbox_render($field_id, $_GET['pclass'], 'label');
                } else {
                    return $search_page->searchbox_render($field_id, [], 'label');
                }
            },
            $tempate_section
        );
        // End of Search Tag Replacement

        if ($tsection === true) {
            return $tempate_section;
        } else {
            $this->page = $tempate_section;
        }
    }

    public function replace_user_field_tags($user_id, $template_section = '', $tag_prefix = 'member')
    {
        global $misc;

        if ($template_section != '') {
            $tsection = true;
        } else {
            $template_section = $this->page;
            $tsection = false;
        }
        if (is_numeric($user_id)) {
            global $lang, $config, $conn, $api, $or_user_id;

            include_once $config['basepath'] . '/include/user.inc.php';
            $user = new user();
            $or_user_id = intval($user_id);

            //Deal with Field Block Tags
            //New listing_agent_field_****_block tag handler for 2.4.1
            $laf_blocks = [];
            preg_match_all('/{' . $tag_prefix . '_field_([^{}]*?)_block}/', $template_section, $laf_blocks);
            if (count($laf_blocks) > 1) {
                foreach ($laf_blocks[1] as $block) {
                    $value = $user->renderSingleListingItem($or_user_id, $block, 'rawvalue');
                    if ($value == '') {
                        $template_section = preg_replace('/{' . $tag_prefix . '_field_' . $block . '_block}(.*?){\/' . $tag_prefix . '_field_' . $block . '_block}/is', '', $template_section);
                    } else {
                        $template_section = str_replace('{' . $tag_prefix . '_field_' . $block . '_block}', '', $template_section);
                        $template_section = str_replace('{/' . $tag_prefix . '_field_' . $block . '_block}', '', $template_section);
                    }
                }
            } // Replace listing_agent tags

            // Handle Caption Only
            preg_match_all('/{' . $tag_prefix . '_field_([^{}]*?)_caption}/', $template_section, $fieldmatches);
            foreach ($fieldmatches[1] as $ftag) {
                $value = $user->renderSingleListingItem($or_user_id, $ftag, 'caption');
                $template_section = str_replace('{' . $tag_prefix . '_field_' . $ftag . '_caption}', $value, $template_section);
            }

            // Handle Value Only
            preg_match_all('/{' . $tag_prefix . '_field_([^{}]*?)_value}/', $template_section, $fieldmatches);
            foreach ($fieldmatches[1] as $ftag) {
                $value = $user->renderSingleListingItem($or_user_id, $ftag, 'value');
                $template_section = str_replace('{' . $tag_prefix . '_field_' . $ftag . '_value}', $value, $template_section);
            }

            // Handle Raw Value
            preg_match_all('/{' . $tag_prefix . '_field_([^{}]*?)_rawvalue}/', $template_section, $fieldmatches);
            foreach ($fieldmatches[1] as $ftag) {
                $value = $user->renderSingleListingItem($or_user_id, $ftag, 'rawvalue');
                $template_section = str_replace('{' . $tag_prefix . '_field_' . $ftag . '_rawvalue}', $value, $template_section);
            }
            // Handle Both Caption and Value

            preg_match_all('/{' . $tag_prefix . '_field_([^{}]*?)}/', $template_section, $fieldmatches);
            foreach ($fieldmatches[1] as $ftag) {
                $value = $user->renderSingleListingItem($or_user_id, $ftag);
                $template_section = str_replace('{' . $tag_prefix . '_field_' . $ftag . '}', $value, $template_section);
            }

            $reg_info = $user->get_user_reg_info($or_user_id);
            $user_name = $reg_info['user_name'];
            $user_first_name = $reg_info['first_name'];
            $user_last_name = $reg_info['last_name'];
            $user_email = $reg_info['emailaddress'];

            $user_info = $user->renderUserInfo($or_user_id);
            $user_link = $this->magicURIGenerator('agent', $or_user_id, true);
            $user_contact_link = $user->contact_agent_link($or_user_id);
            $template_section = str_replace('{' . $tag_prefix . '_first_name}', $user_first_name, $template_section);
            $template_section = str_replace('{' . $tag_prefix . '_last_name}', $user_last_name, $template_section);
            $template_section = str_replace('{' . $tag_prefix . '_name}', $user_name, $template_section);
            $template_section = str_replace('{' . $tag_prefix . '_id}', $or_user_id, $template_section);
            $template_section = str_replace('{' . $tag_prefix . '_email}', $user_email, $template_section);
            $template_section = str_replace('{' . $tag_prefix . '_display_info}', $user_info, $template_section);
            $template_section = str_replace('{' . $tag_prefix . '_link}', $user_link, $template_section);
            $template_section = str_replace('{' . $tag_prefix . '_contact_link}', $user_contact_link, $template_section);

            //Deal with QR Code
            $value = $user->qr_code_link($or_user_id);
            $template_section = str_replace('{' . $tag_prefix . '_qr_code_link}', $value, $template_section);

            $result = $api->load_local_api('media__read', [
                'media_type' => 'userimages',
                'media_parent_id' => $or_user_id,
                'media_output' => 'URL',
            ]);
            if ($result['error']) {
                die($result['error_msg']);
            }

            $num_images = $result['media_count'];

            if ($num_images == 0) {
                if ($config['show_agent_no_photo'] == 1) {
                    $agent_image = '<img src="' . $config['baseurl'] . '/images/nophotobig.gif" alt="' . $lang['no_photo'] . '" />';
                    $raw_agent_image = $config['baseurl'] . '/images/nophotobig.gif';
                    $thumb_agent_image = '<img src="' . $config['baseurl'] . '/images/nophoto.gif" alt="' . $lang['no_photo'] . '" />';
                    $thumb_raw_agent_image = $config['baseurl'] . '/images/nophoto.gif';
                } else {
                    $agent_image = '';
                    $raw_agent_image = '';
                    $thumb_agent_image = '';
                    $thumb_raw_agent_image = '';
                }
                $template_section = $this->parse_template_section($template_section, $tag_prefix . '_image_thumb_1', $thumb_agent_image);
                $template_section = $this->parse_template_section($template_section, 'raw_' . $tag_prefix . '_image_thumb_1', $thumb_raw_agent_image);
                $template_section = $this->parse_template_section($template_section, $tag_prefix . '_image_full_1', $agent_image);
                $template_section = $this->parse_template_section($template_section, 'raw_' . $tag_prefix . '_image_full_1', $raw_agent_image);
            }
            $x = 1;

            //extract the names from the API media object
            foreach ($result['media_object'] as $obj) {
                if (
                    strpos($template_section, '{' . $tag_prefix . '_image_thumb_' . $x . '}') === false &&
                    strpos($template_section, '{raw_' . $tag_prefix . '_image_thumb_' . $x . '}') === false &&
                    strpos($template_section, '{' . $tag_prefix . '_image_full_' . $x . '}') === false &&
                    strpos($template_section, '{raw_' . $tag_prefix . '_image_full_' . $x . '}') === false
                ) {
                    $x++;
                    continue;
                }
                $thumb_file_name = $obj['thumb_file_name'];
                $full_file_name = $obj['file_name'];
                $imagedata = GetImageSize($config['user_upload_path'] . '/' . $full_file_name);
                $imagewidth = $imagedata[0];
                $imageheight = $imagedata[1];
                $max_width = $config['main_image_width'];
                $max_height = $config['main_image_height'];
                $resize_by = $config['resize_by'];
                $shrinkage = 1;
                if (($max_width == $imagewidth) || ($max_height == $imageheight)) {
                    $display_width = $imagewidth;
                    $display_height = $imageheight;
                } else {
                    if ($resize_by == 'width') {
                        $shrinkage = $imagewidth / $max_width;
                        $display_width = $max_width;
                        $display_height = round($imageheight / $shrinkage);
                    } elseif ($resize_by == 'height') {
                        $shrinkage = $imageheight / $max_height;
                        $display_height = $max_height;
                        $display_width = round($imagewidth / $shrinkage);
                    } elseif ($resize_by == 'both') {
                        $display_width = $max_width;
                        $display_height = $max_height;
                    } elseif ($resize_by == 'bestfit') {
                        $shrinkage_width = $imagewidth / $max_width;
                        $shrinkage_height = $imageheight / $max_height;
                        $shrinkage = max($shrinkage_width, $shrinkage_height);
                        $display_height = round($imageheight / $shrinkage);
                        $display_width = round($imagewidth / $shrinkage);
                    }
                }
                // Thumbnail Image Sizes
                $thumb_imagedata = GetImageSize($config['user_upload_path'] . '/' . $thumb_file_name);
                $thumb_imagewidth = $thumb_imagedata[0];
                $thumb_imageheight = $thumb_imagedata[1];
                $thumb_max_width = $config['thumbnail_width'];
                $thumb_max_height = $config['thumbnail_height'];
                $resize_thumb_by = $config['resize_thumb_by'];
                $shrinkage = 1;
                if (($thumb_max_width == $thumb_imagewidth) || ($thumb_max_height == $thumb_imageheight)) {
                    $thumb_displaywidth = $thumb_imagewidth;
                    $thumb_displayheight = $thumb_imageheight;
                } else {
                    if ($resize_thumb_by == 'width') {
                        $shrinkage = $thumb_imagewidth / $thumb_max_width;
                        $thumb_displaywidth = $thumb_max_width;
                        $thumb_displayheight = round($thumb_imageheight / $shrinkage);
                    } elseif ($resize_thumb_by == 'height') {
                        $shrinkage = $thumb_imageheight / $thumb_max_height;
                        $thumb_displayheight = $thumb_max_height;
                        $thumb_displaywidth = round($thumb_imagewidth / $shrinkage);
                    } elseif ($resize_thumb_by == 'both') {
                        $thumb_displayheight = $thumb_max_height;
                        $thumb_displaywidth = $thumb_max_width;
                    }
                }

                $agent_image = '<img src="' . $config['user_view_images_path'] . '/' . $full_file_name . '" height="' . $display_height . '" width="' . $display_width . '" alt="' . $full_file_name . '" />';
                $raw_agent_image = $config['user_view_images_path'] . '/' . $full_file_name;
                $thumb_agent_image = '<img src="' . $config['user_view_images_path'] . '/' . $thumb_file_name . '" height="' . $thumb_displayheight . '" width="' . $thumb_displaywidth . '" alt="' . $thumb_file_name . '" />';
                $thumb_raw_agent_image = $config['user_view_images_path'] . '/' . $thumb_file_name;
                // We have the image so insert it into the section.
                $template_section = $this->parse_template_section($template_section, $tag_prefix . '_image_thumb_' . $x, $thumb_agent_image);
                $template_section = $this->parse_template_section($template_section, 'raw_' . $tag_prefix . '_image_thumb_' . $x, $thumb_raw_agent_image);
                $template_section = $this->parse_template_section($template_section, $tag_prefix . '_image_full_' . $x, $agent_image);
                $template_section = $this->parse_template_section($template_section, 'raw_' . $tag_prefix . '_image_full_' . $x, $raw_agent_image);
                $x++;
            }

            $template_section = preg_replace('{' . $tag_prefix . '_image_thumb_(.*?)}', '', $template_section);
            $template_section = preg_replace('{raw_' . $tag_prefix . '_image_thumb_(.*?)}', '', $template_section);
            // End of Listing Tag Replacement
        }
        if ($tsection === true) {
            return $template_section;
        } else {
            $this->page = $template_section;
        }
    }

    public function replace_lead_field_tags($lead_id, $tempate_section = '')
    {
        if (is_numeric($lead_id)) {
            global $lang, $config, $conn, $or_lead_id;
            $or_lead_id = $lead_id;
            if ($tempate_section != '') {
                $tsection = true;
            } else {
                $tempate_section = $this->page;
                $tsection = false;
            }

            // Handle Caption Only
            $tempate_section = preg_replace_callback(
                '/{lead_field_([^{}]*?)_caption}/',
                function ($matches) {
                    global $config, $or_lead_id, $lang;
                    include_once $config['basepath'] . '/include/lead_functions.inc.php';
                    $lead_functions = new lead_functions();
                    return $lead_functions->renderSingleFeedbackItem($or_lead_id, $matches[1], 'caption');
                },
                $tempate_section
            );

            // Handle Value Only
            $tempate_section = preg_replace_callback(
                '/{lead_field_([^{}]*?)_value}/',
                function ($matches) {
                    global $config, $or_lead_id, $lang;
                    include_once $config['basepath'] . '/include/lead_functions.inc.php';
                    $lead_functions = new lead_functions();
                    return $lead_functions->renderSingleFeedbackItem($or_lead_id, $matches[1], 'value');
                },
                $tempate_section
            );

            // Handle Raw Value
            $tempate_section = preg_replace_callback(
                '/{lead_field_([^{}]*?)_rawvalue}/',
                function ($matches) {
                    global $config, $or_lead_id, $lang;
                    include_once $config['basepath'] . '/include/lead_functions.inc.php';
                    $lead_functions = new lead_functions();
                    return $lead_functions->renderSingleFeedbackItem($or_lead_id, $matches[1], 'rawvalue');
                },
                $tempate_section
            );

            // Handle Both Caption and Value
            $tempate_section = preg_replace_callback(
                '/{lead_field_([^{}]*?)}/',
                function ($matches) {
                    global $config, $or_lead_id, $lang;
                    include_once $config['basepath'] . '/include/lead_functions.inc.php';
                    $lead_functions = new lead_functions();
                    return $lead_functions->renderSingleFeedbackItem($or_lead_id, $matches[1]);
                },
                $tempate_section
            );

            //Handle All Lead Fields..
            if (strpos($tempate_section, '{display_all_lead_fields}') !== false) {
                include_once $config['basepath'] . '/include/lead_functions.inc.php';
                $lead_functions = new lead_functions();
                $sql = 'SELECT feedbackformelements_field_name
				FROM ' . $config['table_prefix'] . 'feedbackformelements
				ORDER BY feedbackformelements_rank';
                $recordSet = $conn->Execute($sql);

                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $all_lead_display = '';
                while (!$recordSet->EOF) {
                    $fname = $recordSet->fields('feedbackformelements_field_name');
                    $my_data = $lead_functions->renderSingleFeedbackItem($or_lead_id, $fname);
                    if ($my_data != '') {
                        $all_lead_display .= $my_data . '<br />';
                    }
                    $recordSet->MoveNext();
                }
                $tempate_section = str_replace('{display_all_lead_fields}', $all_lead_display, $tempate_section);
            }

            // End of Listing Tag Replacement
            if ($tsection === true) {
                return $tempate_section;
            } else {
                $this->page = $tempate_section;
            }
        }
    }

    public function replace_foreach_pclass_block($tempate_section = '')
    {
        global $lang, $api, $config;
        if ($tempate_section != '') {
            $tsection = true;
        } else {
            $tempate_section = $this->page;
            $tsection = false;
        }
        $pclass_api = $api->load_local_api('pclass__metadata', []);
        if ($pclass_api['error']) {
            die($pclass_api['error_msg']);
        }
        preg_match_all('/\{foreach_pclass_block\}(.*?)\{\/foreach_pclass_block\}/s', $tempate_section, $matches);
        //print_r($matches);
        foreach ($matches[1] as $x => $foreach_pclass_block) {
            $foreach_pclass_block_result = '';
            foreach ($pclass_api['metadata'] as $id => $parray) {
                $class_name = $parray['name'];
                $foreach_pclass_block_result .= $foreach_pclass_block;
                $foreach_pclass_block_result = str_replace('{pclass_id}', $id, $foreach_pclass_block_result);
                $foreach_pclass_block_result = str_replace('{pclass_name}', htmlentities($class_name, ENT_COMPAT, $config['charset']), $foreach_pclass_block_result);
            }
            $tempate_section = str_replace($matches[0][$x], $foreach_pclass_block_result, $tempate_section);
        }
        if ($tsection === true) {
            return $tempate_section;
        } else {
            $this->page = $tempate_section;
        }
    }

    public function replace_if_addon_block($tempate_section = '')
    {
        global $lang, $api, $config, $conn, $misc;
        if ($tempate_section != '') {
            $tsection = true;
        } else {
            $tempate_section = $this->page;
            $tsection = false;
        }

        $sql = 'SELECT addons_name
				FROM ' . $config['table_prefix_no_lang'] . 'addons;';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        while (!$recordSet->EOF) {
            $addons[] = $recordSet->fields('addons_name');
            $recordSet->MoveNext();
        }

        preg_match_all('/\{if_addon_([^{}]*?)\}(.*?)\{\/if_addon_([^{}]*?)\}/is', $tempate_section, $matches);

        foreach ($matches[1] as $x => $each_if_addon) {
            // if this add-on is in the DB strip the
            if (in_array($each_if_addon, $addons)) {
                $each_if_addon_result = '';
                $each_if_addon_result = $matches[0][$x];

                $each_if_addon_result = str_replace('{if_addon_' . $each_if_addon . '}', '', $each_if_addon_result);
                $each_if_addon_result = str_replace('{/if_addon_' . $each_if_addon . '}', '', $each_if_addon_result);
                $tempate_section = str_replace($matches[0][$x], $each_if_addon_result, $tempate_section);
            } else {
                $tempate_section = str_replace($matches[0][$x], '', $tempate_section);
            }
        }

        if ($tsection === true) {
            return $tempate_section;
        } else {
            $this->page = $tempate_section;
        }
    }

    public function replace_custom_agent_search_block($tempate_section = '')
    {
        global $lang, $api;
        if ($tempate_section != '') {
            $tsection = true;
        } else {
            $tempate_section = $this->page;
            $tsection = false;
        }

        preg_match_all('/\{agents_custom_search_result_block((\s\$.*?=["|\'].*?["|\'])*)\}(.*?)\{\/agents_custom_search_result_block\}/s', $tempate_section, $tag_matches);

        if (isset($tag_matches[0])) {
            foreach ($tag_matches[0] as $tag_count => $full_section) {
                $arglist = $tag_matches[1][$tag_count];
                $agent_template = $tag_matches[3][$tag_count];
                if ($full_section == '') {
                    return;
                }
                if ($arglist != '') {
                    preg_match_all('/(\$(.*?)=["|\'](.*?)["|\'])/s', $arglist, $matches);
                }
                $LIMIT = '10';
                $ARGS = [];
                $SORTBY = [];
                $SORTTYPE = [];
                foreach ($matches[2] as $x => $argname) {
                    if ($argname == 'limit') {
                        $LIMIT = intval($matches[3][$x]);
                    }
                    if ($argname == 'args') {
                        $arg_array = $matches[3][$x];
                        $arg_array = html_entity_decode($arg_array);
                        $arg_array = explode('&', $arg_array);
                        foreach ($arg_array as $myarg) {
                            $my_parts = explode('=', $myarg);
                            if (isset($_GET['user'])) {
                                $my_parts[1] = $this->replace_user_field_tags($_GET['user'], $my_parts[1], 'agent');
                            }
                            if (substr($my_parts[0], -2) == '[]') {
                                $ARGS[substr($my_parts[0], 0, -2)][] = $my_parts[1];
                            } else {
                                $ARGS[$my_parts[0]] = $my_parts[1];
                            }
                        }
                    }
                    if ($argname == 'sortby') {
                        $SORTBY = $matches[3][$x];
                        $SORTBY = explode(',', $SORTBY);
                    }
                    if ($argname == 'sorttype') {
                        $SORTTYPE = $matches[3][$x];
                        $SORTTYPE = explode(',', $SORTTYPE);
                    }
                }
                if (empty($SORTBY)) {
                    $SORTBY[] = 'random';
                }
                //get the userdb_id# for our list of agents
                $result = $api->load_local_api('user__search', [
                    'resource' => 'agent',
                    'parameters' => $ARGS,
                    'sortby' => $SORTBY,
                    'sorttype' => $SORTTYPE,
                    'limit' => $LIMIT,
                    'offset' => 0,
                    'count_only' => 0,
                ]);


                $returned_num_listings = $result['user_count'];
                $agent_template_result = '';
                if ($returned_num_listings >= 1) {
                    if (strpos($agent_template, '{custom_search_has_results_block}') !== false) {
                        $found_agent_template = $this->get_template_section('custom_search_has_results_block', $agent_template);
                        foreach ($result['users'] as $user) {
                            $agent_template_result .= $found_agent_template;
                            $agent_template_result = $this->replace_user_field_tags($user, $agent_template_result, 'agent');
                            $agent_template_result = $this->cleanup_images($agent_template_result);
                        }
                        $agent_template_result = str_replace('$', '\$', $agent_template_result);
                        $agent_template = preg_replace('/{custom_search_has_results_block}(.*?){\/custom_search_has_results_block}/is', $agent_template_result, $agent_template);
                        $tempate_section = str_replace($full_section, $agent_template, $tempate_section);
                    } else {
                        foreach ($result['users'] as $user) {
                            $agent_template_result .= $agent_template;
                            $agent_template_result = $this->replace_user_field_tags($user, $agent_template_result, 'agent');
                            $agent_template_result = $this->cleanup_images($agent_template_result);
                        }
                        $tempate_section = str_replace($full_section, $agent_template_result, $tempate_section);
                    }
                } else {
                    $tempate_section = str_replace($full_section, $agent_template_result, $tempate_section);
                }
            }
        }
        //echo $tempate_section;die;
        if ($tsection === true) {
            return $tempate_section;
        } else {
            $this->page = $tempate_section;
        }
    }

    public function replace_custom_listing_search_block($tempate_section = '')
    {
        global $lang, $api;
        if ($tempate_section != '') {
            $tsection = true;
        } else {
            $tempate_section = $this->page;
            $tsection = false;
        }
        preg_match_all('/\{listings_custom_search_result_block((\s\$.*?=["|\'].*?["|\'])*)\}(.*?)\{\/listings_custom_search_result_block\}/s', $tempate_section, $tag_matches);
        if (isset($tag_matches[0])) {
            foreach ($tag_matches[0] as $tag_count => $full_section) {
                $arglist = $tag_matches[1][$tag_count];
                $listing_template = $tag_matches[3][$tag_count];
                if ($full_section == '') {
                    return;
                }
                if ($arglist != '') {
                    preg_match_all('/(\$(.*?)=["|\'](.*?)["|\'])/s', $arglist, $matches);
                }
                $LIMIT = '10';
                $ARGS = [];
                $SORTBY = [];
                $SORTTYPE = [];
                foreach ($matches[2] as $x => $argname) {
                    if ($argname == 'limit') {
                        $LIMIT = intval($matches[3][$x]);
                    }
                    if ($argname == 'args') {
                        $arg_array = $matches[3][$x];
                        $arg_array = html_entity_decode($arg_array);
                        $arg_array = explode('&', $arg_array);
                        foreach ($arg_array as $myarg) {
                            $my_parts = explode('=', $myarg);
                            if (isset($_GET['listingID'])) {
                                $my_parts[1] = $this->replace_listing_field_tags($_GET['listingID'], $my_parts[1], false, true);
                            }
                            if (substr($my_parts[0], -2) == '[]') {
                                $ARGS[substr($my_parts[0], 0, -2)][] = $my_parts[1];
                            } else {
                                $ARGS[$my_parts[0]] = $my_parts[1];
                            }
                        }
                    }
                    if ($argname == 'sortby') {
                        $SORTBY = $matches[3][$x];
                        $SORTBY = explode(',', $SORTBY);
                    }
                    if ($argname == 'sorttype') {
                        $SORTTYPE = $matches[3][$x];
                        $SORTTYPE = explode(',', $SORTTYPE);
                    }
                }
                if (empty($SORTBY)) {
                    $SORTBY[] = 'random';
                }
                $result = $api->load_local_api('listing__search', ['parameters' => $ARGS, 'sortby' => $SORTBY, 'sorttype' => $SORTTYPE, 'limit' => $LIMIT, 'offset' => 0, 'count_only' => 0]);
                $returned_num_listings = $result['listing_count'];
                $listing_template_result = '';
                if ($returned_num_listings >= 1) {
                    if (strpos($listing_template, '{custom_search_has_results_block}') !== false) {
                        $found_listing_template = $this->get_template_section('custom_search_has_results_block', $listing_template);
                        foreach ($result['listings'] as $listing) {
                            $listing_template_result .= $found_listing_template;
                            $listing_template_result = $this->replace_listing_field_tags($listing, $listing_template_result);
                            $listing_template_result = $this->cleanup_images($listing_template_result);
                        }
                        $listing_template_result = str_replace('$', '\$', $listing_template_result);
                        $listing_template = preg_replace('/{custom_search_has_results_block}(.*?){\/custom_search_has_results_block}/is', $listing_template_result, $listing_template);
                        $tempate_section = str_replace($full_section, $listing_template, $tempate_section);
                    } else {
                        foreach ($result['listings'] as $listing) {
                            $listing_template_result .= $listing_template;
                            $listing_template_result = $this->replace_listing_field_tags($listing, $listing_template_result);
                            $listing_template_result = $this->cleanup_images($listing_template_result);
                        }
                        $tempate_section = str_replace($full_section, $listing_template_result, $tempate_section);
                    }
                } else {
                    $tempate_section = str_replace($full_section, $listing_template_result, $tempate_section);
                }
            }
        }
        //echo $tempate_section;die;
        if ($tsection === true) {
            return $tempate_section;
        } else {
            $this->page = $tempate_section;
        }
    }

    public function replace_custom_blog_search_block($tempate_section = '')
    {
        global $lang, $api, $misc, $config;
        if ($tempate_section != '') {
            $tsection = true;
        } else {
            $tempate_section = $this->page;
            $tsection = false;
        }

        preg_match_all('/\{blog_custom_search_result_block((\s\$.*?=["|\'].*?["|\'])*)\}(.*?)\{\/blog_custom_search_result_block\}/s', $tempate_section, $tag_matches);

        if (isset($tag_matches[0])) {
            foreach ($tag_matches[0] as $tag_count => $full_section) {
                $arglist = $tag_matches[1][$tag_count];
                $blog_template = $tag_matches[3][$tag_count];
                if ($full_section == '') {
                    return;
                }
                if ($arglist != '') {
                    preg_match_all('/(\$(.*?)=["|\'](.*?)["|\'])/s', $arglist, $matches);
                }
                $LIMIT = '10';
                $ARGS = [];
                $SORTBY = [];
                $SORTTYPE = [];
                foreach ($matches[2] as $x => $argname) {
                    if ($argname == 'limit') {
                        $LIMIT = intval($matches[3][$x]);
                    }
                    if ($argname == 'args') {
                        $arg_array = $matches[3][$x];
                        $arg_array = html_entity_decode($arg_array);
                        $arg_array = explode('&', $arg_array);
                        foreach ($arg_array as $myarg) {
                            $my_parts = explode('=', $myarg);
                            if (isset($_GET['ArticleID'])) {
                                $my_parts[1] = $this->replace_user_field_tags($_GET['ArticleID'], $my_parts[1], 'agent');
                            }
                            if (substr($my_parts[0], -2) == '[]') {
                                $ARGS[substr($my_parts[0], 0, -2)][] = $my_parts[1];
                            } else {
                                $ARGS[$my_parts[0]] = $my_parts[1];
                            }
                        }
                    }
                    //print_r($ARGS);
                    if ($argname == 'sortby') {
                        $SORTBY = $matches[3][$x];
                        $SORTBY = explode(',', $SORTBY);
                    }
                    if ($argname == 'sorttype') {
                        $SORTTYPE = $matches[3][$x];
                        $SORTTYPE = explode(',', $SORTTYPE);
                    }
                }
                if (empty($SORTBY)) {
                    $SORTBY[] = 'random';
                }
                //get the blogmain_id# for our list of blogs
                $result = $api->load_local_api('blog__search', [
                    'parameters' => $ARGS,
                    'sortby' => $SORTBY,
                    'sorttype' => $SORTTYPE,
                    'limit' => $LIMIT,
                    'offset' => 0,
                    'count_only' => 0,
                ]);

                $returned_num_listings = $result['blog_count'];
                $blog_template_result = '';
                if ($returned_num_listings >= 1) {
                    if (strpos($blog_template, '{custom_search_has_results_block}') !== false) {
                        $found_blog_template = $this->get_template_section('custom_search_has_results_block', $blog_template);
                        foreach ($result['blogs'] as $blog) {
                            $blog_template_result .= $found_blog_template;
                            // Todo: Fix this, blog_author_id is not set.
                            //$blog_template_result = $this->replace_user_field_tags($blog_author_id, $blog_template_result, 'blog_author');
                            $blog_template_result = $this->cleanup_images($blog_template_result);
                        }
                        $blog_template_result = str_replace('$', '\$', $blog_template_result);
                        $blog_template = preg_replace('/{custom_search_has_results_block}(.*?){\/custom_search_has_results_block}/is', $blog_template_result, $blog_template);
                        $tempate_section = str_replace($full_section, $blog_template, $tempate_section);
                    } else {
                        //echo 'I should not be here';

                        foreach ($result['blogs'] as $blog) {
                            $blog_info = $api->load_local_api('blog__read', [
                                'blog_id' => $blog,
                            ]);
                            if ($blog_info['error']) {
                                die($blog_info['error_msg']);
                            }

                            //replace tags
                            $user_first_name = $blog_info['blog']['blog_author_firstname'];
                            $user_last_name = $blog_info['blog']['blog_author_lastname'];
                            $blog_template_result .= $blog_template;
                            $blog_template_result = str_replace('{blog_id}', $blog, $blog_template_result);
                            $blog_template_result = str_replace('{blog_title}', htmlentities($blog_info['blog']['blogmain_title'], ENT_COMPAT, $config['charset']), $blog_template_result);
                            $blog_template_result = str_replace('{blog_author}', htmlentities($user_first_name . ' ' . $user_last_name, ENT_COMPAT, $config['charset']), $blog_template_result);
                            $blog_template_result = str_replace('{blog_date_posted}', $misc->convert_timestamp($blog_info['blog']['blogmain_date'], true), $blog_template_result);
                            $blog_template_result = str_replace('{blog_comment_count}', $blog_info['blog']['blog_comment_count'], $blog_template_result);
                            $blog_template_result = str_replace('{blog_url}', htmlentities($blog_info['blog']['blog_url'], ENT_COMPAT, $config['charset']), $blog_template_result);

                            $summary_endpos = strpos($blog_info['blog']['blogmain_full'], '<hr');
                            if ($summary_endpos !== false) {
                                $summary = substr($blog_info['blog']['blogmain_full'], 0, $summary_endpos);
                            } else {
                                $summary = $blog_info['blog']['blogmain_full'];
                            }
                            $blog_template_result = str_replace('{blog_summary}', $summary, $blog_template_result);

                            $blog_template_result = str_replace('{blog_full_article}', $blog_info['blog']['blogmain_full'], $blog_template_result);

                            if (!empty($blog_info['blog']['blog_post_tags'])) {
                                $btags = '<ul class="btags">';
                                foreach ($blog_info['blog']['blog_post_tags'] as $key => $val) {
                                    $btags .= '<li><a class="btags_link" id="btag_' . $key . '" href="' . htmlentities($val['tag_link']) . '" >' . $val['tag_name'] . '</a></li>';
                                }
                                $btags .= '</ul>';
                                $blog_template_result = str_replace('{blog_post_tags}', $btags, $blog_template_result);
                            }

                            $blog_template_result = $this->cleanup_images($blog_template_result);
                        }
                        $tempate_section = str_replace($full_section, $blog_template_result, $tempate_section);
                    }
                } else {
                    $tempate_section = str_replace($full_section, $blog_template_result, $tempate_section);
                }
            }
        }
        if ($tsection === true) {
            return $tempate_section;
        } else {
            $this->page = $tempate_section;
        }
    }

    public function replace_listing_field_tags($listing_id, $tempate_section = '', $utf8HTML = false, $skipImageTags = false)
    {
        global $config, $conn, $misc, $or_replace_listing_id, $or_replace_listing_owner, $lang;
        if ($tempate_section != '') {
            $tsection = true;
        } else {
            $tempate_section = $this->page;
            $tsection = false;
        }
        if (is_numeric($listing_id) && $listing_id > 0) {
            $or_replace_listing_id = intval($listing_id);
            include_once $config['basepath'] . '/include/listing.inc.php';
            $listing_pages = new listing_pages();
            include_once $config['basepath'] . '/include/media.inc.php';
            if ($utf8HTML) {
                $lf_blocks = [];
                preg_match_all('/{listing_field_([^{}]*?)_block}/', $tempate_section, $lf_blocks);
                include_once $config['basepath'] . '/include/user.inc.php';
                global $or_replace_listing_owner;
                if (count($lf_blocks) > 1) {
                    foreach ($lf_blocks[1] as $block) {
                        $value =  $listing_pages->renderSingleListingItem($or_replace_listing_id, $block, 'rawvalue');
                        if ($value == '') {
                            $tempate_section = preg_replace('/{listing_field_' . $block . '_block}(.*?){\/listing_field_' . $block . '_block}/is', '', $tempate_section);
                            $tempate_section = str_replace('{!listing_field_' . $block . '_block}', '', $tempate_section);
                            $tempate_section = str_replace('{/!listing_field_' . $block . '_block}', '', $tempate_section);
                        } else {
                            $tempate_section = str_replace('{listing_field_' . $block . '_block}', '', $tempate_section);
                            $tempate_section = str_replace('{/listing_field_' . $block . '_block}', '', $tempate_section);
                            $tempate_section = preg_replace('/{!listing_field_' . $block . '_block}(.*?){\/!listing_field_' . $block . '_block}/is', '', $tempate_section);
                        }
                    }
                }
                //Deal with featured listing block
                if (strpos($tempate_section, '{listing_favorite_block}') !== false || strpos($tempate_section, '{!listing_favorite_block}') !== false) {
                    if (isset($_SESSION['userID'])) {
                        $userID = intval($_SESSION['userID']);
                        $sql1 = 'SELECT listingsdb_id
						FROM ' . $config['table_prefix'] . 'userfavoritelistings
						WHERE ((listingsdb_id = ' . $or_replace_listing_id . ')
						AND (userdb_id= ' . $userID . '))';
                        $recordSet1 = $conn->Execute($sql1);
                        if ($recordSet1 === false) {
                            $misc->log_error($sql1);
                        }
                        if ($recordSet1->RecordCount() > 0) {
                            $tempate_section = preg_replace('/{!listing_favorite_block}(.*?){\/!listing_favorite_block}/is', '', $tempate_section);
                            $tempate_section = str_replace('{listing_favorite_block}', '', $tempate_section);
                            $tempate_section = str_replace('{/listing_favorite_block}', '', $tempate_section);
                        } else {
                            $tempate_section = preg_replace('/{listing_favorite_block}(.*?){\/listing_favorite_block}/is', '', $tempate_section);
                            $tempate_section = str_replace('{!listing_favorite_block}', '', $tempate_section);
                            $tempate_section = str_replace('{/!listing_favorite_block}', '', $tempate_section);
                        }
                    } else {
                        $tempate_section = preg_replace('/{listing_favorite_block}(.*?){\/listing_favorite_block}/is', '', $tempate_section);
                        $tempate_section = str_replace('{!listing_favorite_block}', '', $tempate_section);
                        $tempate_section = str_replace('{/!listing_favorite_block}', '', $tempate_section);
                    }
                }
                //Deal with Creation Date and Last Modified Date
                if (strpos($tempate_section, '{listing_creation_date}') !== false || strpos($tempate_section, '{listing_last_modified_date}') !== false) {
                    $sql = 'SELECT listingsdb_creation_date, listingsdb_last_modified
					FROM ' . $config['table_prefix'] . "listingsdb
					WHERE listingsdb_id = $or_replace_listing_id";
                    $recordSet = $conn->Execute($sql);
                    if ($recordSet === false) {
                        $misc->log_error($sql);
                    }
                    $listingsdb_creation_date = $recordSet->UserTimeStamp($recordSet->fields('listingsdb_creation_date'), $config['date_format_timestamp']);
                    $listingsdb_last_modified = $recordSet->UserTimeStamp($recordSet->fields('listingsdb_last_modified'), $config['date_format_timestamp']);
                    $tempate_section = str_replace('{listing_creation_date}', $listingsdb_creation_date, $tempate_section);
                    $tempate_section = str_replace('{listing_last_modified_date}', $listingsdb_last_modified, $tempate_section);
                }
                //End Featured Listing Block

                // Handle Caption Only
                $tempate_section = preg_replace_callback(
                    '/{listing_field_([^{}]*?)_caption}/',
                    function ($matches) {
                        global $config, $or_replace_listing_id, $lang;
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing_pages = new listing_pages();
                        return htmlentities($listing_pages->renderSingleListingItem($or_replace_listing_id, $matches[1], 'caption'), ENT_QUOTES, 'UTF-8');
                    },
                    $tempate_section
                );

                // Handle Value Only
                $tempate_section = preg_replace_callback(
                    '/{listing_field_([^{}]*?)_value}/',
                    function ($matches) {
                        global $config, $or_replace_listing_id, $lang;
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing_pages = new listing_pages();
                        return htmlentities($listing_pages->renderSingleListingItem($or_replace_listing_id, $matches[1], 'value'), ENT_QUOTES, 'UTF-8');
                    },
                    $tempate_section
                );

                //Handle Raw Value
                $tempate_section = preg_replace_callback(
                    '/{listing_field_([^{}]*?)_rawvalue}/',
                    function ($matches) {
                        global $config, $or_replace_listing_id, $lang;
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing_pages = new listing_pages();
                        return htmlentities($listing_pages->renderSingleListingItem($or_replace_listing_id, $matches[1], 'rawvalue'), ENT_QUOTES, 'UTF-8');
                    },
                    $tempate_section
                );

                // Handle Both Caption and Value
                $tempate_section = preg_replace_callback(
                    '/{listing_field_([^{}]*?)}/',
                    function ($matches) {
                        global $config, $or_replace_listing_id, $lang;
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing_pages = new listing_pages();
                        return htmlentities($listing_pages->renderSingleListingItem($or_replace_listing_id, $matches[1]), ENT_QUOTES, 'UTF-8');
                    },
                    $tempate_section
                );

                $value = htmlentities($listing_pages->get_listing_single_value('listingsdb_title', $listing_id), ENT_QUOTES, 'UTF-8');
                $tempate_section = str_replace('{listing_title}', $value, $tempate_section);
                $value = $listing_pages->get_listing_single_value('listingsdb_title', $listing_id);
                if ($config['controlpanel_mbstring_enabled'] == 1) {
                    if (mb_detect_encoding($value) != 'UTF-8') {
                        $value = utf8_encode($value);
                    }
                }
                $tempate_section = str_replace('{rss_listing_title}', $value, $tempate_section);
                $value = htmlentities($listing_pages->get_pclass($listing_id), ENT_QUOTES, 'UTF-8');
                $tempate_section = str_replace('{listing_pclass}', $value, $tempate_section);
                $value = $listing_pages->get_listing_single_value('listingsdb_pclass_id', $listing_id);
                $tempate_section = str_replace('{listing_pclass_id}', $value, $tempate_section);

                //Deal with QR Code
                $value = $listing_pages->qr_code_link($or_replace_listing_id);
                $tempate_section = str_replace('{listing_qr_code_link}', $value, $tempate_section);
                // Get listing owner
                $owner_sql = 'SELECT userdb_id FROM ' . $config['table_prefix'] . 'listingsdb WHERE (listingsdb_id = ' . $or_replace_listing_id . ')';
                $recordSet = $conn->execute($owner_sql);
                $or_replace_listing_owner = $recordSet->fields('userdb_id');

                $tempate_section = $this->replace_user_field_tags($or_replace_listing_owner, $tempate_section, 'listing_agent');

                $value = htmlentities($listing_pages->getAgentListingsLink($listing_id), ENT_QUOTES, 'UTF-8');
                $tempate_section = str_replace('{listing_agent_listings}', $value, $tempate_section);
            } else {
                //Deal with listing field blocks
                $lf_blocks = [];
                preg_match_all('/{listing_field_([^{}]*?)_block}/', $tempate_section, $lf_blocks);
                include_once $config['basepath'] . '/include/user.inc.php';
                global $or_replace_listing_owner;
                if (count($lf_blocks) > 1) {
                    foreach ($lf_blocks[1] as $block) {
                        $value =  $listing_pages->renderSingleListingItem($or_replace_listing_id, $block, 'rawvalue');
                        if ($value == '') {
                            $tempate_section = preg_replace('/{listing_field_' . $block . '_block}(.*?){\/listing_field_' . $block . '_block}/is', '', $tempate_section);
                            $tempate_section = str_replace('{!listing_field_' . $block . '_block}', '', $tempate_section);
                            $tempate_section = str_replace('{/!listing_field_' . $block . '_block}', '', $tempate_section);
                        } else {
                            $tempate_section = str_replace('{listing_field_' . $block . '_block}', '', $tempate_section);
                            $tempate_section = str_replace('{/listing_field_' . $block . '_block}', '', $tempate_section);
                            $tempate_section = preg_replace('/{!listing_field_' . $block . '_block}(.*?){\/!listing_field_' . $block . '_block}/is', '', $tempate_section);
                        }
                    }
                }
                //Deal with featured listing block
                if (strpos($tempate_section, '{listing_favorite_block}') !== false) {
                    if (isset($_SESSION['userID'])) {
                        $userID = intval($_SESSION['userID']);
                        $sql1 = 'SELECT listingsdb_id
						FROM ' . $config['table_prefix'] . 'userfavoritelistings
						WHERE ((listingsdb_id = ' . $or_replace_listing_id . ')
						AND (userdb_id= ' . $userID . '))';
                        $recordSet1 = $conn->Execute($sql1);
                        if ($recordSet1 === false) {
                            $misc->log_error($sql1);
                        }
                        if ($recordSet1->RecordCount() > 0) {
                            $tempate_section = preg_replace('/{!listing_favorite_block}(.*?){\/!listing_favorite_block}/is', '', $tempate_section);
                            $tempate_section = str_replace('{listing_favorite_block}', '', $tempate_section);
                            $tempate_section = str_replace('{/listing_favorite_block}', '', $tempate_section);
                        } else {
                            $tempate_section = preg_replace('/{listing_favorite_block}(.*?){\/listing_favorite_block}/is', '', $tempate_section);
                            $tempate_section = str_replace('{!listing_favorite_block}', '', $tempate_section);
                            $tempate_section = str_replace('{/!listing_favorite_block}', '', $tempate_section);
                        }
                    } else {
                        $tempate_section = preg_replace('/{listing_favorite_block}(.*?){\/listing_favorite_block}/is', '', $tempate_section);
                        $tempate_section = str_replace('{!listing_favorite_block}', '', $tempate_section);
                        $tempate_section = str_replace('{/!listing_favorite_block}', '', $tempate_section);
                    }
                }
                //Deal with Creation Date and Last Modified Date
                if (strpos($tempate_section, '{listing_creation_date}') !== false || strpos($tempate_section, '{listing_last_modified_date}') !== false) {
                    $sql = 'SELECT listingsdb_creation_date, listingsdb_last_modified
					FROM ' . $config['table_prefix'] . "listingsdb
					WHERE listingsdb_id = $or_replace_listing_id";
                    $recordSet = $conn->Execute($sql);
                    if ($recordSet === false) {
                        $misc->log_error($sql);
                    }
                    $listingsdb_creation_date = $recordSet->UserTimeStamp($recordSet->fields('listingsdb_creation_date'), $config['date_format_timestamp']);
                    $listingsdb_last_modified = $recordSet->UserTimeStamp($recordSet->fields('listingsdb_last_modified'), $config['date_format_timestamp']);
                    $tempate_section = str_replace('{listing_creation_date}', $listingsdb_creation_date, $tempate_section);
                    $tempate_section = str_replace('{listing_last_modified_date}', $listingsdb_last_modified, $tempate_section);
                }
                //End Featured Listing Block

                // Handle Caption Only
                preg_match_all('/{listing_field_([^{}]*?)_caption}/', $tempate_section, $fieldmatches);
                foreach ($fieldmatches[1] as $ftag) {
                    //($or_replace_listing_id, $matches[1],\'caption\')
                    $value = $listing_pages->renderSingleListingItem($or_replace_listing_id, $ftag, 'caption');
                    $tempate_section = str_replace('{listing_field_' . $ftag . '_caption}', $value, $tempate_section);
                }

                // Handle Value Only
                preg_match_all('/{listing_field_([^{}]*?)_value}/', $tempate_section, $fieldmatches);
                foreach ($fieldmatches[1] as $ftag) {
                    //($or_replace_listing_id, $matches[1],\'caption\')
                    $value = $listing_pages->renderSingleListingItem($or_replace_listing_id, $ftag, 'value');
                    $tempate_section = str_replace('{listing_field_' . $ftag . '_value}', $value, $tempate_section);
                }

                // Handle Raw Value
                preg_match_all('/{listing_field_([^{}]*?)_rawvalue}/', $tempate_section, $fieldmatches);
                foreach ($fieldmatches[1] as $ftag) {
                    //($or_replace_listing_id, $matches[1],\'caption\')
                    $value = $listing_pages->renderSingleListingItem($or_replace_listing_id, $ftag, 'rawvalue');
                    $tempate_section = str_replace('{listing_field_' . $ftag . '_rawvalue}', $value, $tempate_section);
                }

                // Handle Both Caption and Value
                preg_match_all('/{listing_field_([^{}]*?)}/', $tempate_section, $fieldmatches);
                foreach ($fieldmatches[1] as $ftag) {
                    //($or_replace_listing_id, $matches[1],\'caption\')
                    $value = $listing_pages->renderSingleListingItem($or_replace_listing_id, $ftag);
                    $tempate_section = str_replace('{listing_field_' . $ftag . '}', $value, $tempate_section);
                }
                $value = $listing_pages->get_listing_single_value('listingsdb_title', $listing_id);
                $tempate_section = str_replace('{listing_title}', $value, $tempate_section);
                $value = $listing_pages->get_pclass($listing_id);
                $tempate_section = str_replace('{listing_pclass}', $value, $tempate_section);
                $value = $listing_pages->get_listing_single_value('listingsdb_pclass_id', $listing_id);
                $tempate_section = str_replace('{listing_pclass_id}', $value, $tempate_section);

                //Deal with QR Code
                $value = $listing_pages->qr_code_link($or_replace_listing_id);
                $tempate_section = str_replace('{listing_qr_code_link}', $value, $tempate_section);

                // Get listing owner
                $owner_sql = 'SELECT userdb_id FROM ' . $config['table_prefix'] . 'listingsdb WHERE (listingsdb_id = ' . $or_replace_listing_id . ')';
                $recordSet = $conn->execute($owner_sql);
                $or_replace_listing_owner = $recordSet->fields('userdb_id');

                $tempate_section = $this->replace_user_field_tags($or_replace_listing_owner, $tempate_section, 'listing_agent');

                $value = $listing_pages->getAgentListingsLink($listing_id);
                $tempate_section = str_replace('{listing_agent_listings}', $value, $tempate_section);
            }

            // Listing Images
            if ($skipImageTags == false) {
                //Remove VTour Blocks for Listings that have no vtours.
                $sql2 = 'SELECT listingsvtours_caption, listingsvtours_description, listingsvtours_file_name, listingsvtours_rank 
						FROM ' . $config['table_prefix'] . "listingsvtours 
						WHERE (listingsdb_id = $listing_id) 
						ORDER BY listingsvtours_rank";
                $recordSet2 = $conn->Execute($sql2);
                if ($recordSet2 === false) {
                    $misc->log_error($sql);
                }
                $num_images = $recordSet2->RecordCount();
                if ($num_images == 0) {
                    $tempate_section = $this->remove_template_block('vtour_tab', $tempate_section);
                }
                $url = $this->magicURIGenerator('listing', $listing_id, false);
                $full_url = $this->magicURIGenerator('listing', $listing_id, true);
                $tempate_section = str_replace('{link_to_listing}', $url, $tempate_section);
                $tempate_section = str_replace('{listing_id}', $listing_id, $tempate_section);
                $tempate_section = str_replace('{fulllink_to_listing}', $full_url, $tempate_section);
                $url = '<a href="' . $url . '">';
                $fullurl = '<a href="' . $full_url . '">';
                // grab the listing's image
                $sql2 = 'SELECT listingsimages_id, listingsimages_caption, listingsimages_description, listingsimages_thumb_file_name, listingsimages_file_name FROM ' . $config['table_prefix'] . 'listingsimages WHERE listingsdb_id = ' . $listing_id . ' ORDER BY listingsimages_rank';
                $recordSet2 = $conn->Execute($sql2);
                if (!$recordSet2) {
                    $misc->log_error($sql2);
                }
                $num_images = $recordSet2->RecordCount();
                if ($num_images == 0) {
                    if ($config['show_no_photo'] == 1) {
                        $listing_image = $url . '<img src="' . $config['baseurl'] . '/images/nophotobig.gif" alt="' . $lang['no_photo'] . '" /></a>';
                        $listing_image_full = $fullurl . '<img src="' . $config['baseurl'] . '/images/nophotobig.gif" alt="' . $lang['no_photo'] . '" /></a>';
                        $thumb_image = $url . '<img src="' . $config['baseurl'] . '/images/nophoto.gif" alt="' . $lang['no_photo'] . '" /></a>';
                        $thumb_image_full = $fullurl . '<img src="' . $config['baseurl'] . '/images/nophoto.gif" alt="' . $lang['no_photo'] . '" /></a>';
                        if (isset($_GET['action']) && $_GET['action'] == 'listingview') {
                            $thumb_image = '<img src="' . $config['baseurl'] . '/images/nophoto.gif" alt="' . $lang['no_photo'] . '" />';
                            $thumb_image_full = '<img src="' . $config['baseurl'] . '/images/nophoto.gif" alt="' . $lang['no_photo'] . '" />';
                        }
                        $tempate_section = str_replace('{raw_image_thumb_1}', $config['baseurl'] . '/images/nophoto.gif', $tempate_section);
                        $tempate_section = str_replace('{raw_image_full_1}', $config['baseurl'] . '/images/nophotobig.gif', $tempate_section);
                    } else {
                        $listing_image = '';
                        $listing_image_full = '';
                        $thumb_image = '';
                        $thumb_image_full = '';
                        $tempate_section = str_replace('{raw_image_thumb_1}', '', $tempate_section);
                        $tempate_section = str_replace('{raw_image_full_1}', '', $tempate_section);
                    }
                    $tempate_section = str_replace('{image_caption_1}', '', $tempate_section);
                    $tempate_section = str_replace('{image_description_1}', '', $tempate_section);
                    $tempate_section = str_replace('{image_thumb_1}', $thumb_image, $tempate_section);
                    $tempate_section = str_replace('{image_thumb_fullurl_1}', $thumb_image_full, $tempate_section);
                    $tempate_section = str_replace('{image_full_1}', $listing_image, $tempate_section);
                    $tempate_section = str_replace('{image_full_fullurl_1}', $listing_image_full, $tempate_section);
                    //Deal with image tags beyond imag 1
                    $tempate_section = preg_replace('/{image_caption_\d+}/', '', $tempate_section);
                    $tempate_section = preg_replace('/{image_description_\d+}/', '', $tempate_section);
                    $tempate_section = preg_replace('/{image_thumb_\d+}/', '', $tempate_section);
                    $tempate_section = preg_replace('/{image_thumb_fullurl_\d+}/', '', $tempate_section);
                    $tempate_section = preg_replace('/{image_full_\d+}/', '', $tempate_section);
                    $tempate_section = preg_replace('/{image_full_fullurl_\d+}/', '', $tempate_section);
                }
                $x = 1;
                while (!$recordSet2->EOF) {
                    // Make sure Image tags are used before moving on.
                    if (
                        strpos($tempate_section, '{raw_image_thumb_' . $x . '}') === false &&
                        strpos($tempate_section, '{image_thumb_' . $x . '}') === false &&
                        strpos($tempate_section, '{image_thumb_fullurl_' . $x . '}') === false &&
                        strpos($tempate_section, '{image_full_' . $x . '}') === false &&
                        strpos($tempate_section, '{image_caption_' . $x . '}') === false &&
                        strpos($tempate_section, '{image_description_' . $x . '}') === false &&
                        strpos($tempate_section, '{raw_image_full_' . $x . '}') === false &&
                        strpos($tempate_section, '{image_full_fullurl_' . $x . '}') === false
                    ) {
                        $x++;
                        $recordSet2->MoveNext();
                        continue;
                    }

                    //if we're already on the listing then make the urls goto the view image
                    $listingsimages_id = $recordSet2->fields('listingsimages_id');
                    $image_caption = htmlentities($recordSet2->fields('listingsimages_caption'));
                    $image_description = htmlentities($recordSet2->fields('listingsimages_description'));
                    $tempate_section = str_replace('{image_caption_' . $x . '}', $image_caption, $tempate_section);
                    $tempate_section = str_replace('{image_description_' . $x . '}', $image_description, $tempate_section);
                    $thumb_file_name = $recordSet2->fields('listingsimages_thumb_file_name');
                    $full_file_name = $recordSet2->fields('listingsimages_file_name');
                    $url = $this->magicURIGenerator('listing_image', $listingsimages_id);
                    $fullurl = $this->magicURIGenerator('listing_image', $listingsimages_id, true);
                    $url = '<a href="' . $url . '">';
                    $fullurl = '<a href="' . $fullurl . '">';
                    if (strpos($thumb_file_name, 'http://') === 0 || strpos($thumb_file_name, 'https://') === 0 || strpos($thumb_file_name, '//') === 0) {
                        $listing_image = $url . '<img src="' . $thumb_file_name . '" alt="' . $image_caption . '" width="' . $config['thumbnail_width'] . '" height="' . $config['thumbnail_height'] . '" /></a>';
                        $listing_image_fullurl = $fullurl . '<img src="' . $thumb_file_name . '" alt="' . $image_caption . '" width="' . $config['thumbnail_width'] . '" height="' . $config['thumbnail_height'] . '" /></a>';
                        $tempate_section = str_replace('{raw_image_thumb_' . $x . '}', $thumb_file_name, $tempate_section);

                        $listing_image_full = $url . '<img src="' . $full_file_name . '" alt="' . $image_caption . '" /></a>';
                        $listing_image_full_fullurl = $fullurl . '<img src="' . $full_file_name . '" alt="' . $image_caption . '" /></a>';
                        $tempate_section = str_replace('{raw_image_full_' . $x . '}', $full_file_name, $tempate_section);

                        $tempate_section = str_replace('{image_thumb_' . $x . '}', $listing_image, $tempate_section);
                        $tempate_section = str_replace('{image_thumb_fullurl_' . $x . '}', $listing_image_fullurl, $tempate_section);
                        $tempate_section = str_replace('{image_full_' . $x . '}', $listing_image_full, $tempate_section);
                        $tempate_section = str_replace('{image_full_fullurl_' . $x . '}', $listing_image_full_fullurl, $tempate_section);
                    } else {
                        if ($thumb_file_name != '' && file_exists($config['listings_upload_path'] . '/' . $thumb_file_name)) {
                            // Full Image Sizes
                            $imagedata = GetImageSize($config['listings_upload_path'] . '/' . $full_file_name);
                            $imagewidth = $imagedata[0];
                            $imageheight = $imagedata[1];
                            $max_width = $config['main_image_width'];
                            $max_height = $config['main_image_height'];
                            $resize_by = $config['resize_by'];
                            $shrinkage = 1;
                            if (($max_width == $imagewidth) || ($max_height == $imageheight)) {
                                $display_width = $imagewidth;
                                $display_height = $imageheight;
                            } else {
                                if ($resize_by == 'width') {
                                    $shrinkage = $imagewidth / $max_width;
                                    $display_width = $max_width;
                                    $display_height = round($imageheight / $shrinkage);
                                } elseif ($resize_by == 'height') {
                                    $shrinkage = $imageheight / $max_height;
                                    $display_height = $max_height;
                                    $display_width = round($imagewidth / $shrinkage);
                                } elseif ($resize_by == 'both') {
                                    $display_width = $max_width;
                                    $display_height = $max_height;
                                } elseif ($resize_by == 'bestfit') {
                                    $shrinkage_width = $imagewidth / $max_width;
                                    $shrinkage_height = $imageheight / $max_height;
                                    $shrinkage = max($shrinkage_width, $shrinkage_height);
                                    $display_height = round($imageheight / $shrinkage);
                                    $display_width = round($imagewidth / $shrinkage);
                                }
                            }
                            // Thumbnail Image Sizes
                            $thumb_imagedata = GetImageSize($config['listings_upload_path'] . '/' . $thumb_file_name);
                            $thumb_imagewidth = $thumb_imagedata[0];
                            $thumb_imageheight = $thumb_imagedata[1];
                            $thumb_max_width = $config['thumbnail_width'];
                            $thumb_max_height = $config['thumbnail_height'];
                            $resize_thumb_by = $config['resize_thumb_by'];
                            $shrinkage = 1;
                            if (($thumb_max_width == $thumb_imagewidth) || ($thumb_max_height == $thumb_imageheight)) {
                                $thumb_displaywidth = $thumb_imagewidth;
                                $thumb_displayheight = $thumb_imageheight;
                            } else {
                                if ($resize_thumb_by == 'width') {
                                    $shrinkage = $thumb_imagewidth / $thumb_max_width;
                                    $thumb_displaywidth = $thumb_max_width;
                                    $thumb_displayheight = round($thumb_imageheight / $shrinkage);
                                } elseif ($resize_thumb_by == 'height') {
                                    $shrinkage = $thumb_imageheight / $thumb_max_height;
                                    $thumb_displayheight = $thumb_max_height;
                                    $thumb_displaywidth = round($thumb_imagewidth / $shrinkage);
                                } elseif ($resize_thumb_by == 'both') {
                                    $thumb_displayheight = $thumb_max_height;
                                    $thumb_displaywidth = $thumb_max_width;
                                }
                            }
                            $listing_image = $url . '<img src="' . $config['listings_view_images_path'] . '/' . $thumb_file_name . '" height="' . $thumb_displayheight . '" width="' . $thumb_displaywidth . '" alt="' . $image_caption . '" /></a>';
                            $listing_image_full = $url . '<img src="' . $config['listings_view_images_path'] . '/' . $full_file_name . '" height="' . $display_height . '" width="' . $display_width . '" alt="' . $image_caption . '" /></a>';
                            $listing_image_fullurl = $fullurl . '<img src="' . $config['listings_view_images_path'] . '/' . $thumb_file_name . '" height="' . $thumb_displayheight . '" width="' . $thumb_displaywidth . '" alt="' . $image_caption . '" /></a>';
                            $listing_image_full_fullurl = $fullurl . '<img src="' . $config['listings_view_images_path'] . '/' . $full_file_name . '" height="' . $display_height . '" width="' . $display_width . '" alt="' . $image_caption . '" /></a>';
                            $tempate_section = str_replace('{image_thumb_' . $x . '}', $listing_image, $tempate_section);
                            $tempate_section = str_replace('{raw_image_thumb_' . $x . '}', $config['listings_view_images_path'] . '/' . $thumb_file_name, $tempate_section);
                            $tempate_section = str_replace('{image_thumb_fullurl_' . $x . '}', $listing_image_fullurl, $tempate_section);
                            //Full Image tags
                            $tempate_section = str_replace('{image_full_' . $x . '}', $listing_image_full, $tempate_section);
                            $tempate_section = str_replace('{raw_image_full_' . $x . '}', $config['listings_view_images_path'] . '/' . $full_file_name, $tempate_section);
                            $tempate_section = str_replace('{image_full_fullurl_' . $x . '}', $listing_image_full_fullurl, $tempate_section);
                        } else {
                            if ($config['show_no_photo'] == 1) {
                                $listing_image = $url . '<img src="' . $config['baseurl'] . '/images/nophoto.gif" alt="' . $lang['no_photo'] . '" /></a>';
                                $listing_image_fullurl = $fullurl . '<img src="' . $config['baseurl'] . '/images/nophoto.gif" alt="' . $lang['no_photo'] . '" /></a>';
                                $tempate_section = str_replace('{raw_image_thumb_' . $x . '}', $config['baseurl'] . '/images/nophoto.gif', $tempate_section);
                            } else {
                                $listing_image = '';
                                $listing_image_fullurl = '';
                                $tempate_section = str_replace('{raw_image_thumb_' . $x . '}', '', $tempate_section);
                            }
                            $tempate_section = str_replace('{image_thumb_' . $x . '}', $listing_image, $tempate_section);
                            $tempate_section = str_replace('{image_thumb_fullurl_' . $x . '}', $listing_image_fullurl, $tempate_section);
                            $tempate_section = str_replace('{image_full_' . $x . '}', '', $tempate_section);
                            $tempate_section = str_replace('{raw_image_full_' . $x . '}', '', $tempate_section);
                            $tempate_section = str_replace('{image_full_fullurl_' . $x . '}', '', $tempate_section);
                        }
                    }
                    // We have the image so insert it into the section.
                    $x++;
                    $recordSet2->MoveNext();
                } // end whil
            }
            // End Listing Images
            $value = [];
            $value = $listing_pages->getListingAgentThumbnail($listing_id);
            $x = 0;
            foreach ($value as $y) {
                $tempate_section = str_replace('{listing_agent_thumbnail_' . $x . '}', $y, $tempate_section);
                $x++;
            }
            $tempate_section = preg_replace('/{listing_agent_thumbnail_([^{}]*?)}/', '', $tempate_section);
            // End of Listing Tag Replacement
        }
        if ($tsection === true) {
            return $tempate_section;
        } else {
            $this->page = $tempate_section;
        }
    }
}

class page_user extends page
{
    public function replace_user_action()
    {
        global $lang, $config;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $data = '';
        switch ($_GET['action']) {
            case 'index':
                if ($config['default_page'] == 'wysiwyg_page') {
                    $_GET['PageID'] = 1;
                    include_once $config['basepath'] . '/include/page_display.inc.php';
                    $search = new page_display();
                    $data = $search->display();
                } elseif ($config['default_page'] == 'blog_index') {
                    include_once $config['basepath'] . '/include/blog_display.inc.php';
                    $blog = new blog_display();
                    $data = $blog->display_blog_index();
                }
                break;
            case 'member_login':
                $data = $login->display_login('Member');
                break;
            case 'search_step_2':
                include_once $config['basepath'] . '/include/search.inc.php';
                $search = new search_page();
                $data = $search->create_searchpage();
                break;
            case 'searchpage':
                include_once $config['basepath'] . '/include/search.inc.php';
                $search = new search_page();
                $data = $search->create_search_page_logic();
                break;
            case 'searchresults':
                include_once $config['basepath'] . '/include/search.inc.php';
                $search = new search_page();
                $data = $search->search_results();
                break;
            case 'listingview':
                include_once $config['basepath'] . '/include/listing.inc.php';
                $listing = new listing_pages();
                $data = $listing->listing_view();
                break;
            case 'addtofavorites':
                include_once $config['basepath'] . '/include/members_favorites.inc.php';
                $listing = new membersfavorites();
                $data = $listing->addtofavorites();
                break;
            case 'view_favorites':
                include_once $config['basepath'] . '/include/members_favorites.inc.php';
                $listing = new membersfavorites();
                $data = $listing->view_favorites();
                break;
            case 'view_saved_searches':
                include_once $config['basepath'] . '/include/members_search.inc.php';
                $listing = new memberssearch();
                $data = $listing->view_saved_searches();
                break;
            case 'save_search':
                include_once $config['basepath'] . '/include/members_search.inc.php';
                $listing = new memberssearch();
                $data = $listing->save_search();
                break;
            case 'delete_search':
                include_once $config['basepath'] . '/include/members_search.inc.php';
                $listing = new memberssearch();
                $data = $listing->delete_search();
                break;
            case 'delete_favorites':
                include_once $config['basepath'] . '/include/members_favorites.inc.php';
                $listing = new membersfavorites();
                $data = $listing->delete_favorites();
                break;
            case 'page_display':
                include_once $config['basepath'] . '/include/page_display.inc.php';
                $search = new page_display();
                $data = $search->display();
                break;
            case 'calculator':
                include_once $config['basepath'] . '/include/calculators.inc.php';
                $calc = new calculators();
                $data = $calc->start_calc();
                break;
            case 'view_listing_image':
                include_once $config['basepath'] . '/include/media.inc.php';
                $image = new image_handler();
                $data = $image->view_image('listing');
                break;
            case 'view_user_image':
                include_once $config['basepath'] . '/include/media.inc.php';
                $image = new image_handler();
                $data = $image->view_image('userimage');
                break;
            case 'show_rss':
                include_once $config['basepath'] . '/include/rss.inc.php';
                $rss = new rss();
                $data = $rss->rss_view($_GET['rss_feed']);
                break;
            case 'rss_featured_listings':
                include_once $config['basepath'] . '/include/rss.inc.php';
                $rss = new rss();
                $data = $rss->rss_view('featured');
                break;
            case 'rss_lastmodified_listings':
                include_once $config['basepath'] . '/include/rss.inc.php';
                $rss = new rss();
                $data = $rss->rss_view('lastmodified');
                break;
            case 'view_user':
                include_once $config['basepath'] . '/include/user.inc.php';
                $user = new user();
                $data = $user->view_user();
                break;
            case 'view_users':
                include_once $config['basepath'] . '/include/user.inc.php';
                $user = new user();
                $data = $user->view_users();
                break;
            case 'edit_profile':
                include_once $config['basepath'] . '/include/user_manager.inc.php';
                if (!isset($_GET['user_id'])) {
                    $_GET['user_id'] = 0;
                }
                $user_managment = new user_managment();
                $data = $user_managment->edit_member_profile($_GET['user_id']);
                break;
            case 'signup':
                if (isset($_GET['type'])) {
                    include_once $config['basepath'] . '/include/user_manager.inc.php';
                    $listing = new user_managment();
                    $data = $listing->user_signup($_GET['type']);
                }
                break;
            case 'show_vtour':
                if (isset($_GET['listingID'])) {
                    include_once $config['basepath'] . '/include/media.inc.php';
                    $vtour = new vtour_handler();
                    $data = $vtour->show_vtour($_GET['listingID']);
                } else {
                    $data = 'No Listing ID';
                }
                break;
            case 'contact_friend':
                include_once $config['basepath'] . '/include/contact.inc.php';
                $contact = new contact();
                if (isset($_GET['listing_id'])) {
                    $data = $contact->ContactFriendForm($_GET['listing_id']);
                }
                break;
            case 'contact_agent':
                if ($config['allow_member_signup'] == 1) {
                    include_once $config['basepath'] . '/include/contact.inc.php';
                    $contact = new contact();
                    if (isset($_GET['listing_id'])) {
                        $data = $contact->ContactAgentForm($_GET['listing_id'], 0);
                    } elseif (isset($_GET['agent_id'])) {
                        $data = $contact->ContactAgentForm(0, $_GET['agent_id']);
                    } else {
                        $data = '';
                    }
                } else {
                    $data = '<h3>' . $lang['no_user_signup'] . '</h3>';
                }
                break;
            case 'create_vcard':
                include_once $config['basepath'] . '/include/user.inc.php';
                $user = new user();
                if (isset($_GET['user'])) {
                    $data = $user->create_vcard($_GET['user']);
                }
                break;
            case 'create_download':
                include_once $config['basepath'] . '/include/media.inc.php';
                $files = new file_handler();
                if (isset($_GET['ID']) && isset($_GET['file_id']) && isset($_GET['type'])) {
                    $data = $files->create_download($_GET['ID'], $_GET['file_id'], $_GET['type']);
                } elseif (isset($_POST['ID']) && isset($_POST['file_id']) && isset($_POST['type'])) {
                    $data = $files->create_download($_POST['ID'], $_POST['file_id'], $_POST['type']);
                }
                break;
            case 'blog_index':
                include_once $config['basepath'] . '/include/blog_display.inc.php';
                $blog = new blog_display();
                $data = $blog->display_blog_index();
                break;
            case 'blog_view_article':
                include_once $config['basepath'] . '/include/blog_display.inc.php';
                $blog = new blog_display();
                $data = $blog->display();
                break;
            case 'verify_email':
                include_once $config['basepath'] . '/include/user_manager.inc.php';
                $user_manager = new user_managment();
                $data = $user_manager->verify_email();
                break;
            case 'send_forgot':
                include_once $config['basepath'] . '/include/login.inc.php';
                $data = $login->forgot_password(false);
                break;
            case 'forgot':
                include_once $config['basepath'] . '/include/login.inc.php';
                $data = $login->forgot_password_reset();
                break;
            case 'powered_by':
                return $this->output_powered_by();
                break;
            case 'notfound':
                include_once $config['basepath'] . '/include/page_display.inc.php';
                $search = new page_display();
                $data = $search->show_page_notfound();
                break;
            case 'listingqrcode':
                if (isset($_GET['listing_id'])) {
                    include_once $config['basepath'] . '/include/listing.inc.php';
                    $listing = new listing_pages();
                    $listing_id = intval($_GET['listing_id']);
                    return $listing->qr_code($listing_id);
                }
                break;
            case 'userqrcode':
                if (isset($_GET['user_id'])) {
                    include_once $config['basepath'] . '/include/user.inc.php';
                    $user = new user();
                    $userid = intval($_GET['user_id']);
                    return $user->qr_code($userid);
                }
                break;
            default:
                $addon_name = [];
                if (preg_match('/^addon_(.\S*?)_.*/', $_GET['action'], $addon_name)) {
                    $file = $config['basepath'] . '/addons/' . $addon_name[1] . '/addon.inc.php';
                    if (file_exists($file)) {
                        include_once $file;
                        $function_name = $addon_name[1] . '_run_action_user_template';
                        $data = $function_name();
                    } else {
                        $data = $lang['addon_doesnt_exist'];
                    }
                } else {
                    $data = '';
                }
                break;
        }
        return $data;
    }

    public function replace_tags($tags = [])
    {
        global $config, $api, $misc;
        // Remove tags not found in teh template
        $new_tags = $tags;
        $tags = [];
        foreach ($new_tags as $tag) {
            if (strpos($this->page, '{' . $tag . '}') !== false) {
                $tags[] = $tag;
            }
        }
        unset($new_tags);
        if (count($tags) > 0) {
            foreach ($tags as $tag) {
                $data = '';
                switch ($tag) {
                    case 'content':
                        $data = $this->replace_user_action();
                        break;
                    case 'csrf_token':
                        $data = $misc->generate_csrf_token();
                        break;
                    case (preg_match('/templated_search_form(_[0-9]{1,2}|$)/', $tag) ? $tag : false):
                        $_GET['pclass'] = [];
                        $pclass_val = str_replace('templated_search_form', '', $tag);
                        include_once $config['basepath'] . '/include/search.inc.php';
                        $search = new search_page();
                        $pclass_val = str_replace('_', '', $pclass_val);
                        $_GET['pclass'][0] = $pclass_val;
                        $data = $search->create_searchpage(true, false);
                        break;
                    case 'baseurl':
                        $data = $config['baseurl'];
                        break;
                    case 'template_url':
                        $data = $config['template_url'];
                        break;
                    case 'pclass_link':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->pclass_link($_GET['listingID']);
                        break;
                    case 'addthis_button':
                        global $jscript_last;
                        $jscript_last .= "\r\n" . '<script type="text/javascript" src="https://s7.addthis.com/js/250/addthis_widget.js"></script>';
                        $data = '<div class="addthis_toolbox addthis_default_style"><a href="https://www.addthis.com/bookmark.php?v=250" class="addthis_button_compact">Share</a></div>';
                        break;
                    case 'load_js':
                        $data = $this->load_js();
                        break;
                    case 'load_ORjs':
                        $data = $this->load_ORjs();
                        break;
                    case 'load_js_last':
                        global $jscript_last;
                        $data = $jscript_last;
                        break;
                    case 'mobile_full_template_link':
                        $data = $this->render_mobile_template_tag();
                        break;
                    case 'featured_listings_vertical':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsVertical();
                        break;
                    case 'featured_listings_horizontal':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsHorizontal();
                        break;
                    case 'random_listings_vertical':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsVertical($config['num_random_listings'], true);
                        break;
                    case 'random_listings_horizontal':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsHorizontal($config['num_random_listings'], true);
                        break;
                    case ' s_vertical':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsVertical($config['num_latest_listings'], false, '', true);
                        break;
                    case 'latest_listings_horizontal':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsHorizontal($config['num_latest_listings'], false, '', true);
                        break;
                    case 'popular_listings_vertical':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsVertical($config['num_popular_listings'], false, '', false, true);
                        break;
                    case 'popular_listings_horizontal':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsHorizontal($config['num_popular_listings'], false, '', false, true);
                        break;
                    case (preg_match('/^featured_listings_horizontal_class_([0-9]*)/', $tag, $feat_class) ? $tag : !$tag):
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsHorizontal(0, false, $feat_class[1]);
                        break;
                    case (preg_match('/^featured_listings_vertical_class_([0-9]*)/', $tag, $feat_class) ? $tag : !$tag):
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsVertical(0, false, $feat_class[1]);
                        break;
                    case (preg_match('/^random_listings_horizontal_class_([0-9]*)/', $tag, $feat_class) ? $tag : !$tag):
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsHorizontal($config['num_random_listings'], true, $feat_class[1]);
                        break;
                    case (preg_match('/^random_listings_vertical_class_([0-9]*)/', $tag, $feat_class) ? $tag : !$tag):
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsVertical($config['num_random_listings'], true, $feat_class[1]);
                        break;
                    case (preg_match('/^latest_listings_horizontal_class_([0-9]*)/', $tag, $feat_class) ? $tag : !$tag):
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsHorizontal($config['num_latest_listings'], false, $feat_class[1], true);
                        break;
                    case (preg_match('/^latest_listings_vertical_class_([0-9]*)/', $tag, $feat_class) ? $tag : !$tag):
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsVertical($config['num_latest_listings'], false, $feat_class[1], true);
                        break;
                    case (preg_match('/^popular_listings_horizontal_class_([0-9]*)/', $tag, $feat_class) ? $tag : !$tag):
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsHorizontal($config['num_popular_listings'], false, $feat_class[1], false, true);
                        break;
                    case (preg_match('/^popular_listings_vertical_class_([0-9]*)/', $tag, $feat_class) ? $tag : !$tag):
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderFeaturedListingsVertical($config['num_popular_listings'], false, $feat_class[1], false, true);
                        break;
                    case 'headline':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->renderTemplateAreaNoCaption('headline', $_GET['listingID']);
                        break;
                    case 'listing_images':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $images = new image_handler();
                        $data = $images->renderListingsImages($_GET['listingID'], 'yes');
                        break;
                    case 'listing_images_nocaption':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $images = new image_handler();
                        $data = $images->renderListingsImages($_GET['listingID'], 'no');
                        break;
                    case 'listing_files_select':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $files = new file_handler();
                        $data = $files->render_files_select($_GET['listingID'], 'listing');
                        break;
                    case 'files_listing_vertical':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $files = new file_handler();
                        $data = $files->render_templated_files($_GET['listingID'], 'listing', 'vertical');
                        break;
                    case 'files_listing_horizontal':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $files = new file_handler();
                        $data = $files->render_templated_files($_GET['listingID'], 'listing', 'horizontal');
                        break;
                    case 'link_calc':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->create_calc_link();
                        break;
                    case 'link_calc_url':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->create_calc_link('yes');
                        break;
                    case 'link_add_favorites':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->create_add_favorite_link();
                        break;
                    case 'link_add_favorites_url':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->create_add_favorite_link('yes');
                        break;
                    case 'link_printer_friendly':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->create_printer_friendly_link();
                        break;
                    case 'link_map':
                        include_once $config['basepath'] . '/include/maps.inc.php';
                        $maps = new maps();
                        $data = $maps->create_map_link();
                        break;
                    case 'link_yahoo_school':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->create_yahoo_school_link();
                        break;
                    case 'link_yahoo_neighborhood':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->create_bestplaces_neighborhood_link();
                        break;
                    case 'link_printer_friendly_url':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->create_printer_friendly_link('yes');
                        break;
                    case 'link_email_friend_url':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->create_email_friend_link('yes');
                        break;
                    case 'link_email_friend':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->create_email_friend_link('no');
                        break;
                    case 'link_map_url':
                        include_once $config['basepath'] . '/include/maps.inc.php';
                        $maps = new maps();
                        $data = $maps->create_map_link('yes');
                        break;
                    case 'link_yahoo_school_url':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->create_yahoo_school_link('yes');
                        break;
                    case 'link_yahoo_neighborhood_url':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->create_bestplaces_neighborhood_link('yes');
                        break;
                    case 'contact_agent_link_url':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->contact_agent_link('yes');
                        break;
                    case 'listing_email':
                        //get email address of listing's agent
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->get_listing_agent_value('userdb_emailaddress', $_GET['listingID']);
                        break;
                    case 'hitcount':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->hitcount($_GET['listingID']);
                        break;
                    case 'main_image':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $images = new image_handler();
                        $data = $images->renderListingsMainImage($_GET['listingID'], 'yes', 'no');
                        break;
                    case 'main_image_nodesc':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $images = new image_handler();
                        $data = $images->renderListingsMainImage($_GET['listingID'], 'no', 'no');
                        break;
                    case 'main_image_java':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $images = new image_handler();
                        $data = $images->renderListingsMainImage($_GET['listingID'], 'yes', 'yes');
                        break;
                    case 'main_image_java_nodesc':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $images = new image_handler();
                        $data = $images->renderListingsMainImage($_GET['listingID'], 'no', 'yes');
                        break;
                    case 'listing_images_java':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $images = new image_handler();
                        $data = $images->renderListingsImagesJava($_GET['listingID'], 'no');
                        break;
                    case 'listing_images_java_caption':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $images = new image_handler();
                        $data = $images->renderListingsImagesJava($_GET['listingID'], 'yes');
                        break;
                    case 'listing_images_java_rows':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $images = new image_handler();
                        $data = $images->renderListingsImagesJavaRows($_GET['listingID']);
                        break;
                    case 'listing_images_mouseover_java':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $images = new image_handler();
                        $data = $images->renderListingsImagesJava($_GET['listingID'], 'no', 'yes');
                        break;
                    case 'listing_images_mouseover_java_caption':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $images = new image_handler();
                        $data = $images->renderListingsImagesJava($_GET['listingID'], 'yes', 'yes');
                        break;
                    case 'listing_images_mouseover_java_rows':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $images = new image_handler();
                        $data = $images->renderListingsImagesJavaRows($_GET['listingID'], 'yes');
                        break;
                    case 'vtour_button':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $vtour = new vtour_handler();
                        $data = $vtour->rendervtourlink($_GET['listingID']);
                        break;
                    case 'listingid':
                        $data = $_GET['listingID'];
                        break;
                    case 'get_creation_date':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->get_creation_date($_GET['listingID']);
                        break;
                    case 'get_featured_raw':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->get_featured($_GET['listingID'], 'yes');
                        break;
                    case 'get_featured':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->get_featured($_GET['listingID'], 'no');
                        break;
                    case 'get_modified_date':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->get_modified_date($_GET['listingID']);
                        break;
                    case 'contact_agent_link':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->contact_agent_link();
                        break;
                    case 'select_language':
                        // include_once $config['basepath'] . '/include/multilingual.inc.php';
                        // $multilingual = new multilingual();
                        // $data = $multilingual->multilingual_select();
                        break;
                    case 'company_name':
                        $data = $config['company_name'];
                        break;
                    case 'company_location':
                        $data = $config['company_location'];
                        break;
                    case 'company_logo':
                        $data = $config['company_logo'];
                        break;
                    case 'show_vtour':
                        if (isset($_GET['listingID'])) {
                            include_once $config['basepath'] . '/include/media.inc.php';
                            $vtour = new vtour_handler();
                            $data = $vtour->show_vtour($_GET['listingID'], false);
                        } else {
                            $data = 'No Listing ID';
                        }
                        break;
                    case 'charset':
                        $data = $config['charset'];
                        break;
                    case 'link_edit_listing':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->edit_listing_link();
                        break;
                    case 'link_edit_listing_url':
                        include_once $config['basepath'] . '/include/listing.inc.php';
                        $listing = new listing_pages();
                        $data = $listing->edit_listing_link('yes');
                        break;
                    case 'template_select':
                        $data = $this->template_selector();
                        break;
                    case 'money_sign':
                        $data = $config['money_sign'];
                        break;
                    case 'curley_open':
                        $data = '{';
                        break;
                    case 'curley_close':
                        $data = '}';
                        break;
                    case 'powered_by_tag':
                        $data = '<a href="https://gitlab.com/appsbytherealryanbonham/open-realty" title="Powered By Open-Realty®"><img id="or_poweredby_logo" style="display:block;width:150px;height:39px; clip: auto; clip-path: none; z-index: auto; transform: none;" src="' . $config['baseurl'] . '/index.php?action=powered_by" alt="Powered By Open-Realty®" /></a>';
                        break;
                    case (preg_match('/getvar_([^{}]*?)/', $tag) ? $tag : false):
                        $getvar = str_replace('getvar_', '', $tag);
                        if (isset($_GET[$getvar]) && !empty($_GET[$getvar])) {
                            if (is_array($_GET[$getvar])) {
                                $data = htmlentities($_GET[$getvar][0]);
                            } else {
                                $data = htmlentities($_GET[$getvar]);
                            }
                        } else {
                            $data = '';
                        }
                        break;
                    case (preg_match('/url_blog_cat_([0-9]*)/', $tag) ? $tag : false):
                        $cat_id = str_replace('url_blog_cat_', '', $tag);
                        $data = $this->magicURIGenerator('blog_cat', $cat_id, true);
                        break;
                    case (preg_match('/blog_cat_title_([0-9]*)/', $tag) ? $tag : false):
                        $cat_id = str_replace('blog_cat_title_', '', $tag);
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $blog_functions = new blog_functions();
                        $data = $blog_functions->get_blog_category_name($cat_id);
                        break;
                    case (preg_match('/pclass_name_([0-9]{1,2})/', $tag) ? $tag : false):
                        $pclass = str_replace('pclass_name_', '', $tag);
                        $data = $this->get_pclass_name($pclass);
                        break;
                    case 'pclass_names_ul':
                        global $api;
                        $ret_array = [];
                        $class_metadata = $api->load_local_api('pclass__metadata', $ret_array);
                        $keys = array_keys($class_metadata['metadata']);
                        $output = '<ul>' . BR;
                        if (is_array($keys)) {
                            foreach ($keys as $class_id) {
                                $output .= '<li><a href="#">' . $class_metadata['metadata'][$class_id]['name'] . '</a></li>' . BR;
                            }
                        }
                        $output .= '</ul>' . BR;
                        $data = $output;
                        break;
                    case 'pclass_names_searchlinks':
                        global $config, $api;
                        $ret_array = [];
                        $class_metadata = $api->load_local_api('pclass__metadata', $ret_array);
                        $keys = array_keys($class_metadata['metadata']);
                        $output = '<ul>' . BR;
                        if (is_array($keys)) {
                            foreach ($keys as $class_id) {
                                $output .= '<li><a href="' . $config['baseurl'] . '/index.php?action=search_step_2&amp;pclass%5B%5D=' . $class_id . '" title="' . $class_metadata['metadata'][$class_id]['name'] . '">' . $class_metadata['metadata'][$class_id]['name'] . '</a></li>' . BR;
                            }
                        }
                        $output .= '</ul>' . BR;
                        $data = $output;
                        break;
                    case 'current_language':
                        if (isset($_SESSION['language_template'])) {
                            $data = $_SESSION['language_template'];
                        } else {
                            $data = $config['lang'];
                        }
                        break;
                    case 'active_listing_count':
                        $result = $api->load_local_api('listing__search', ['parameters' => [], 'limit' => 0, 'offset' => 0, 'count_only' => 1]);
                        $data = $result['listing_count'];
                        break;
                    case (preg_match('/^active_listing_count_pclass_([0-9]*)/', $tag, $pclass) ? $tag : !$tag):
                        $result = $api->load_local_api('listing__search', ['parameters' => ['pclass' => [$pclass[1]]], 'limit' => 0, 'offset' => 0, 'count_only' => 1]);
                        $data = $result['listing_count'];
                        break;
                    case (preg_match('/^listing_stat_([a-z]*)_field_([^{}]*?)_value_pclass_([0-9]*)/', $tag, $args) ? $tag : !$tag):
                        $result = $api->load_local_api('listing__get_statistics', ['function' => $args[1], 'field_name' => $args[2], 'pclass' => [$args[3]], 'format' => true]);
                        if (isset($result[$args[1]])) {
                            $data = $result[$args[1]];
                        }
                        break;
                    case (preg_match('/^listing_stat_([a-z]*)_field_([^{}]*?)_value/', $tag, $args) ? $tag : !$tag):
                        $result = $api->load_local_api('listing__get_statistics', ['function' => $args[1], 'field_name' => $args[2], 'format' => true]);
                        if (isset($result[$args[1]])) {
                            $data = $result[$args[1]];
                        }
                        break;
                    case (preg_match('/^render_menu_([0-9]*)/', $tag, $menu) ? $tag : !$tag):
                        include_once $config['basepath'] . '/include/menu_editor.inc.php';
                        $menu_editor = new menu_editor();
                        $data = $menu_editor->render_menu($menu[1]);
                        break;
                    default:
                        if (preg_match('/^addon_(.*?)_.*/', $tag, $addon_name)) {
                            $file = $config['basepath'] . '/addons/' . $addon_name[1] . '/addon.inc.php';
                            if (file_exists($file)) {
                                include_once $file;
                                $function_name = $addon_name[1] . '_run_template_user_fields';
                                $data = $function_name($tag);
                            } else {
                                $data = '';
                            }
                        } else {
                            $data = '';
                        }
                        break;
                }
                $this->page = str_replace('{' . $tag . '}', $data, $this->page);
            }
        }
        unset($tags);
        unset($tag);
    }

    public function template_selector()
    {
        global $config, $misc;
        $display = '';
        if ($config['allow_template_change'] == 1) {
            $display .= '<form class="template_selector" id="template_select" action="" method="post">';
            $display .= '<input type="hidden" name="token" value="' . $misc->generate_csrf_token() . '" />';
            $display .= '<fieldset>';
            $display .= '<select id="select_users_template" name="select_users_template" onchange="this.form.submit();">';

            $template_directory = $config['basepath'] . '/template';
            $template = opendir($template_directory);
            if ($template == false) {
                die('fail to open');
            }
            while (false !== ($file = readdir($template))) {
                if ($file != '.' && $file != '..' && $file != '.svn') {
                    if (is_dir($template_directory . '/' . $file)) {
                        $display .= '<option value="' . $file . '"';
                        if ($config['template'] == $file) {
                            $display .= ' selected="selected"';
                        }
                        $display .= '>' . $file . '</option>';
                    }
                }
            }
            $display .= '</select>';
            $display .= '</fieldset>';
            $display .= '</form>';
            closedir($template);
        }
        return $display;
    }

    public function get_pclass_name($pclass)
    {
        global $conn, $config, $misc;
        $sql = 'SELECT class_name
		FROM ' . $config['table_prefix'] . 'class
		WHERE class_id = ' . intval($pclass);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $pclass_name = $recordSet->fields('class_name');

        return $pclass_name;
    }
}

class page_admin extends page
{
    public function replace_tags($tags = [])
    {
        global $config, $lang, $api, $misc;
        if (count($tags) > 0) {
            // Remove tags not found in teh template
            $new_tags = $tags;
            $tags = [];
            foreach ($new_tags as $tag) {
                if (strpos($this->page, '{' . $tag . '}') !== false) {
                    $tags[] = $tag;
                }
            }
            unset($new_tags);
            foreach ($tags as $tag) {
                switch ($tag) {
                    case 'csrf_token':
                        $data = $misc->generate_csrf_token();
                        break;
                    case 'select_language':
                        // require_once($config['basepath'].'/include/multilingual.inc.php');
                        // $multilingual = new multilingual();
                        // $data = $multilingual->multilingual_select();
                        break;
                    case 'version':
                        $data = $lang['version'] . ' ' . $config['version'];
                        break;
                    case 'company_name':
                        $data = $config['company_name'];
                        break;
                    case 'company_location':
                        $data = $config['company_location'];
                        break;
                    case 'company_logo':
                        $data = $config['company_logo'];
                        break;
                    case 'site_title':
                        $data = $config['seo_default_title'];
                        break;
                    case 'lang_index_home':
                        $data = $lang['index_home'];
                        break;
                    case 'lang_index_admin':
                        $data = $lang['index_admin'];
                        break;
                    case 'lang_index_logout':
                        $data = $lang['index_logout'];
                        break;
                    case 'baseurl':
                        $data = $config['baseurl'];
                        break;
                    case 'general_info':
                        include_once $config['basepath'] . '/include/admin.inc.php';
                        $admin = new general_admin();
                        $data = $admin->general_info();
                        break;
                    case 'openrealty_links':
                        include_once $config['basepath'] . '/include/admin.inc.php';
                        $admin = new general_admin();
                        $data = $admin->openrealty_links();
                        break;
                    case 'addon_links':
                        // Show Addons
                        global $config, $lang;
                        $data = '';
                        $addons = $this->load_addons();
                        include_once $config['basepath'] . '/include/admin.inc.php';
                        $admin = new general_admin();
                        $addon_links = [];
                        //print_r($addons);
                        foreach ($addons as $addon) {
                            //echo 'Loading '.$addon;
                            $addon_link = [];
                            $addon_link = $admin->display_addons($addon);
                            //echo "\r\n Addon Link:".print_r($addon_link,TRUE);
                            if (is_array($addon_link)) {
                                foreach ($addon_link as $link) {
                                    if (trim($link) !== '') {
                                        $addon_links[] = $link;
                                    }
                                }
                            } else {
                                if (trim($addon_link) !== '') {
                                    $addon_links[] = $addon_link;
                                }
                            }
                        }
                        $current_link = 0;
                        $cell_count = 0;
                        $link_count = count($addon_links);
                        while ($current_link < $link_count) {
                            if ($addon_links[$current_link]) {
                                $data .= '<div class="col-xl-2 col-sm-3 mb-4"><div class="card h-100"><div class="card-footer h-100 p-3">
                                <p class="align-bottom">' . $addon_links[$current_link] . '</p></div></div></div>';
                                $cell_count++;
                            }
                            $current_link++;
                        } // while
                        //$data .= '<div class="clear"></div>';
                        break;
                    case 'addon_menu_links':
                        // Show Addons
                        global $config, $lang;
                        $data = '';
                        $addons = $this->load_addons();
                        include_once $config['basepath'] . '/include/admin.inc.php';
                        $admin = new general_admin();
                        $addon_links = [];
                        //print_r($addons);
                        foreach ($addons as $addon) {
                            //echo 'Loading '.$addon;
                            $addon_link = [];
                            $addon_link = $admin->display_addons($addon);
                            //echo "\r\n Addon Link:".print_r($addon_link,TRUE);
                            if (is_array($addon_link)) {
                                foreach ($addon_link as $link) {
                                    if (trim($link) !== '') {
                                        $addon_links[] = $link;
                                    }
                                }
                            } else {
                                if (trim($addon_link) !== '') {
                                    $addon_links[] = $addon_link;
                                }
                            }
                        }
                        $current_link = 0;
                        $cell_count = 0;
                        $link_count = count($addon_links);

                        if ($link_count > 0) {
                            $data .= '';

                            //$data .= '<div id="addon_links">';
                            while ($current_link < $link_count) {
                                if ($addon_links[$current_link]) {
                                    $data .= $addon_links[$current_link];
                                    $cell_count++;
                                }
                                $current_link++;
                            } // while
                            //$data .= "</div>";
                        }
                        break;

                    case 'lang':
                        if (isset($_SESSION['users_lang']) && $_SESSION['users_lang'] != $config['lang']) {
                            $data = $_SESSION['users_lang'];
                        } else {
                            $data = $config['lang'];
                        }
                        break;
                    case 'user_id':
                        if (isset($_SESSION['userID'])) {
                            $data = $_SESSION['userID'];
                        } else {
                            $data = 0;
                        }
                        break;
                    case 'template_url':
                        $data = $config['admin_template_url'];
                        break;
                    case 'load_js':
                        $data = $this->load_js(true);
                        break;
                    case 'load_ORjs':
                        $data = $this->load_ORjs(true);
                        break;
                    case 'load_js_last':
                        global $jscript_last;
                        $data = $jscript_last;
                        break;
                    case 'content':
                        $data = $this->replace_admin_actions();
                        break;
                    case 'charset':
                        $data = $config['charset'];
                        break;
                    case 'help_link':
                        $help_link = $this->get_help_link();
                        $data = '<div class="or_std_button headerlinks"><a href="' . $help_link . '" onclick="window.open(this.href,\'_blank\',\'location=0,status=0,scrollbars=1,toolbar=0,menubar=0,width=500,height=520,resizable=yes,noopener,noreferrer\');return false">' . $lang['index_help'] . '</a></div>';
                        break;
                    case 'curley_open':
                        $data = '{';
                        break;
                    case 'curley_close':
                        $data = '}';
                        break;
                    case 'powered_by_tag':
                        $data = '<a href="https://gitlab.com/appsbytherealryanbonham/open-realty" title="Powered By Open-Realty"><img id="or_poweredby_logo" style="display:block;width:110px;height:29px; clip: auto; clip-path: none; z-index: auto; transform: none;" src="' . $config['baseurl'] . '/index.php?action=powered_by" alt="Powered By Open-Realty®" /></a>';
                        break;
                    case (preg_match('/getvar_([^{}]*?)/', $tag) ? $tag : false):
                        $getvar = str_replace('getvar_', '', $tag);
                        if (isset($_GET[$getvar]) && !empty($_GET[$getvar])) {
                            $data = htmlentities($_GET[$getvar]);
                        } else {
                            $data = '';
                        }
                        break;
                    case 'current_language':
                        if (isset($_SESSION['language_template'])) {
                            $data = $_SESSION['language_template'];
                        } else {
                            $data = $config['lang'];
                        }
                        break;
                    case 'active_listing_count':
                        $result = $api->load_local_api('listing__search', ['parameters' => [], 'limit' => 0, 'offset' => 0, 'count_only' => 1]);
                        $data = $result['listing_count'];
                        break;
                    case (preg_match('/^active_listing_count_pclass_([0-9]*)/', $tag, $pclass) ? $tag : !$tag):
                        $result = $api->load_local_api('listing__search', ['parameters' => ['pclass' => [$pclass[1]]], 'limit' => 0, 'offset' => 0, 'count_only' => 1]);
                        $data = $result['listing_count'];
                        break;
                    case (preg_match('/^listing_stat_([a-z]*)_field_([^{}]*?)_value_pclass_([0-9]*)/', $tag, $args) ? $tag : !$tag):
                        $result = $api->load_local_api('listing__get_statistics', ['function' => $args[1], 'field_name' => $args[2], 'pclass' => [$args[3]], 'format' => true]);
                        if (isset($result[$args[1]])) {
                            $data = $result[$args[1]];
                        }
                        break;
                    case (preg_match('/^listing_stat_([a-z]*)_field_([^{}]*?)_value/', $tag, $args) ? $tag : !$tag):
                        $result = $api->load_local_api('listing__get_statistics', ['function' => $args[1], 'field_name' => $args[2], 'format' => true]);
                        if (isset($result[$args[1]])) {
                            $data = $result[$args[1]];
                        }
                        break;
                    default:
                        if (preg_match('/^addon_(.*?)_.*/', $tag, $addon_name)) {
                            $file = $config['basepath'] . '/addons/' . $addon_name[1] . '/addon.inc.php';
                            if (file_exists($file)) {
                                include_once $file;
                                $function_name = $addon_name[1] . '_run_template_user_fields';
                                $data = $function_name($tag);
                            //echo 'Found addon tag '.print_r($data,TRUE);
                            } else {
                                $data = '';
                            }
                        } else {
                            $data = '';
                        }
                        break;
                }
                $this->page = str_replace('{' . $tag . '}', $data, $this->page);
            }
        }
    }

    public function replace_admin_actions()
    {
        global $config;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->loginCheck('Agent');
        $data = '';
        if ($login_status !== true) {
            // Run theese commands even if not logged in.

            switch ($_GET['action']) {
                case 'send_forgot':
                    include_once $config['basepath'] . '/include/login.inc.php';
                    $data = $login->forgot_password();
                    break;
                case 'forgot':
                    include_once $config['basepath'] . '/include/login.inc.php';
                    $data = $login->forgot_password_reset();
                    break;
                default:
                    $data .= $login_status;
                    break;
            }
        } else {
            switch ($_GET['action']) {
                case 'index':
                    include_once $config['basepath'] . '/include/admin.inc.php';
                    $admin = new general_admin();
                    $data = $admin->index_page();
                    break;
                case 'edit_page':
                    include_once $config['basepath'] . '/include/page_editor.inc.php';
                    $listing = new page_editor();
                    $data = $listing->page_edit_index();
                    break;
                case 'edit_page_post':
                    include_once $config['basepath'] . '/include/page_editor.inc.php';
                    $listing = new page_editor();
                    $data = $listing->page_edit();
                    break;
                case 'edit_my_listings':
                    include_once $config['basepath'] . '/include/listing_editor.inc.php';
                    $listing_editor = new listing_editor();
                    $data = $listing_editor->edit_listings();
                    break;
                case 'edit_listings':
                    include_once $config['basepath'] . '/include/listing_editor.inc.php';
                    $listing_editor = new listing_editor();
                    $data = $listing_editor->edit_listings(false);
                    break;
                case 'configure':
                    include_once $config['basepath'] . '/include/controlpanel.inc.php';
                    $listing_editor = new configurator();
                    $data = $listing_editor->show_configurator();
                    break;
                case 'edit_listing_template':
                    include_once $config['basepath'] . '/include/template_editor.inc.php';
                    $listing = new template_editor();
                    $data = $listing->edit_listing_template();
                    break;
                case 'edit_agent_template_add_field':
                    include_once $config['basepath'] . '/include/template_editor.inc.php';
                    $listing = new template_editor();
                    $data = $listing->add_user_template_field($type = 'agent');
                    break;
                case 'edit_member_template_add_field':
                    include_once $config['basepath'] . '/include/template_editor.inc.php';
                    $listing = new template_editor();
                    $data = $listing->add_user_template_field($type = 'member');
                    break;
                case 'user_manager':
                    include_once $config['basepath'] . '/include/user_manager.inc.php';
                    $user_managment = new user_managment();
                    $data = $user_managment->show_user_manager();
                    break;
                case 'edit_user':
                    include_once $config['basepath'] . '/include/user_manager.inc.php';
                    $user_managment = new user_managment();
                    $data = $user_managment->show_edit_user();
                    break;
                case 'edit_agent_template':
                    $_GET['type'] = "agent";
                    include_once $config['basepath'] . '/include/template_editor.inc.php';
                    $fields = new template_editor();
                    $data = $fields->edit_user_template($_GET['type']);
                    break;
                case 'edit_member_template':
                    $_GET['type'] = "member";
                    include_once $config['basepath'] . '/include/template_editor.inc.php';
                    $fields = new template_editor();
                    $data = $fields->edit_user_template($_GET['type']);
                    break;
                case 'edit_listing_template_add_field':
                    include_once $config['basepath'] . '/include/template_editor.inc.php';
                    $listing = new template_editor();
                    $data = $listing->add_listing_template_field();
                    break;
                case 'view_log':
                    include_once $config['basepath'] . '/include/log.inc.php';
                    $log = new log();
                    $data = $log->view();
                    break;
                case 'clear_log':
                    include_once $config['basepath'] . '/include/log.inc.php';
                    $log = new log();
                    $data = $log->clear_log();
                    break;
                case 'show_property_classes':
                    include_once $config['basepath'] . '/include/propertyclass.inc.php';
                    $propertyclass = new propertyclass();
                    if (isset($_GET['statustext'])) {
                        $statustext = strip_tags($_GET['statustext']);
                        $data = $propertyclass->show_classes($statustext);
                    } else {
                        $data = $propertyclass->show_classes();
                    }


                    break;
                case 'delete_property_class':
                    include_once $config['basepath'] . '/include/propertyclass.inc.php';
                    $propertyclass = new propertyclass();
                    $data = $propertyclass->delete_property_class();
                    break;
                case 'insert_property_class':
                    include_once $config['basepath'] . '/include/propertyclass.inc.php';
                    $propertyclass = new propertyclass();
                    $data = $propertyclass->insert_property_class();
                    break;
                case 'edit_blog':
                    include_once $config['basepath'] . '/include/blog_editor.inc.php';
                    $listing = new blog_editor();
                    $data = $listing->blog_edit_index();
                    break;
                case 'edit_blog_post':
                    include_once $config['basepath'] . '/include/blog_editor.inc.php';
                    $listing = new blog_editor();
                    $data = $listing->blog_edit();
                    break;
                case 'edit_blog_post_comments':
                    include_once $config['basepath'] . '/include/blog_editor.inc.php';
                    $listing = new blog_editor();
                    $data = $listing->edit_post_comments();
                    break;
                case 'addon_manager':
                    include_once $config['basepath'] . '/include/addon_manager.inc.php';
                    $am = new addon_manager();
                    $data = $am->display_addon_manager();
                    break;
                case 'send_notifications':
                    include_once $config['basepath'] . '/include/notification.inc.php';
                    $notify = new notification();
                    $data = $notify->NotifyUsersOfAllNewListings();
                    break;
                case 'edit_listing':
                    include_once $config['basepath'] . '/include/listing_editor.inc.php';
                    $listing_editor = new listing_editor();
                    if (isset($_GET['edit'])) {
                        $data = $listing_editor->display_listing_editor($_GET['edit']);
                    }
                    break;
                case 'leadmanager':
                    include_once $config['basepath'] . '/include/lead_manager.inc.php';
                    $lead_manager = new lead_manager();
                    $data = $lead_manager->show_leads(true);
                    break;
                case 'my_leadmanager':
                    include_once $config['basepath'] . '/include/lead_manager.inc.php';
                    $lead_manager = new lead_manager();
                    $data = $lead_manager->show_leads();
                    break;
                case 'leadmanager_feedback_edit':
                    include_once $config['basepath'] . '/include/lead_manager.inc.php';
                    $lead_manager = new lead_manager();
                    if (isset($_GET['feedback_id'])) {
                        $data = $lead_manager->show_feedback_edit($_GET['feedback_id'], true);
                    }
                    break;
                case 'leadmanager_my_feedback_edit':
                    include_once $config['basepath'] . '/include/lead_manager.inc.php';
                    $lead_manager = new lead_manager();
                    if (isset($_GET['feedback_id'])) {
                        $data = $lead_manager->show_feedback_edit($_GET['feedback_id']);
                    }
                    break;
                case 'leadmanager_form_edit':
                    include_once $config['basepath'] . '/include/lead_manager.inc.php';
                    $lead_manager = new lead_manager();
                    $data = $lead_manager->form_edit();
                    break;
                case 'leadmanager_viewfeedback':
                    include_once $config['basepath'] . '/include/lead_manager.inc.php';
                    $lead_manager = new lead_manager();
                    $data = $lead_manager->feedbackview(true);
                    break;
                case 'leadmanager_my_viewfeedback':
                    include_once $config['basepath'] . '/include/lead_manager.inc.php';
                    $lead_manager = new lead_manager();
                    $data = $lead_manager->feedbackview();
                    break;
                case 'view_statistics':
                    include_once $config['basepath'] . '/include/tracking.inc.php';
                    $tracking = new tracking();
                    $data = $tracking->view_statistics();
                    break;
                case 'clear_statistics_log':
                    include_once $config['basepath'] . '/include/tracking.inc.php';
                    $tracking = new tracking();
                    $data = $tracking->clear_statistics_log();
                    break;
                case 'generate_sitemap':
                    include_once $config['basepath'] . '/include/sitemap.inc.php';
                    $sitemap = new sitemap();
                    $data = $sitemap->generate();
                    break;
                case 'twitterback':
                    include_once $config['basepath'] . '/include/social.inc.php';
                    $social = new social();
                    $data = $social->twitter_callback();
                    break;
                case 'leadmanager_add_lead':
                    include_once $config['basepath'] . '/include/lead_manager.inc.php';
                    $lead_manager = new lead_manager();
                    $data = $lead_manager->show_add_lead();
                    break;
                case 'edit_menu':
                    include_once $config['basepath'] . '/include/menu_editor.inc.php';
                    $menu_editor = new menu_editor();
                    $data = $menu_editor->show_editor();
                    break;
                default:
                    // Handle Addons
                    $addon_name = [];
                    if (preg_match('/^addon_(.\S*?)_.*/', $_GET['action'], $addon_name)) {
                        include_once $config['basepath'] . '/addons/' . $addon_name[1] . '/addon.inc.php';
                        $function_name = $addon_name[1] . '_run_action_admin_template';
                        $data = $function_name();
                    }
            }
        }
        return $data;
    }

    public function get_help_link()
    {
        global $config;
        $data = 'https://docs.transparent-tech.com/open-realty/latest/';
        switch ($_GET['action']) {
            case 'edit_listings':
            case 'edit_my_listings':
                $data .= '#Editing_Listings_7221131551556_18629798622357552';
                break;
            case 'user_manager':
                $data .= '#User_Manager_8304827630047783_';
                break;
            case 'edit_page':
                $data .= '#Open_Realty_Features_For_Desig';
                break;

            case 'edit_user_template':
                $data .= '#Edit_Agent_Member_Templates_97_5827533545880411';
                break;
            case 'edit_listing_template':
                $data .= '#Edit_Agent_Member_Templates_97_5827533545880411';
                break;

            case 'show_property_classes':
                $data .= '#Property_Classes_8368741635892_07854014777818996';
                break;
            case 'configure':
                $data .= '#Property_Classes_8368741635892_07854014777818996';
                break;
            case 'view_log':
                $data .= '#Site_Log_2914440960261112_4846';
                break;
        }
        return $data;
    }
}

class page_user_ajax extends page
{
    public function call_ajax()
    {
        global $config, $lang;
        switch ($_GET['action']) {
            case 'is_member_check':
                if (isset($_POST['email'])) {
                    include_once $config['basepath'] . '/include/login.inc.php';
                    $login = new login();
                    return $login->verify_member_email($_POST['email']);
                }
                break;
            case 'is_member_full_check':
                if (isset($_POST['email']) && isset($_POST['password'])) {
                    include_once $config['basepath'] . '/include/login.inc.php';
                    $login = new login();
                    return $login->ajax_check_member_login($_POST['email'], $_POST['password']);
                }
                break;
            case 'create_member_account':
                if ($config['allow_member_signup'] == 1) {
                    if (isset($_POST['email']) && isset($_POST['fname']) && isset($_POST['lname'])) {
                        include_once $config['basepath'] . '/include/user_manager.inc.php';
                        $user_managment = new user_managment();
                        return $user_managment->ajax_member_creation($_POST['email'], $_POST['fname'], $_POST['lname']);
                    }
                } else {
                    return json_encode(['error' => true, 'error_msg' => $lang['no_user_signup']]);
                }

                break;
            case 'ajax_forgot_password':
                if (isset($_POST['email'])) {
                    include_once $config['basepath'] . '/include/login.inc.php';
                    $login = new login();
                    return $login->ajax_forgot_password(false);
                }
                break;
            default:
                // Handle Addons
                $addon_name = [];
                if (preg_match('/^addon_(.\S*?)_.*/', $_GET['action'], $addon_name)) {
                    include_once $config['basepath'] . '/addons/' . $addon_name[1] . '/addon.inc.php';
                    $function_name = $addon_name[1] . '_run_user_action_ajax';
                    $data = $function_name();
                    return $data;
                }
        }
    }
}

class page_admin_ajax extends page
{
    public function call_ajax()
    {
        global $config, $lang;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->loginCheck('Agent');
        if ($login_status !== true) {
            die('Not Logged In');
        } else {
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'ajax_update_page_post':
                        include_once $config['basepath'] . '/include/page_functions.inc.php';
                        $class = new page_functions();
                        return $class->ajax_update_page_post();
                    case 'ajax_get_blog_post':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_get_blog_post();
                    case 'ajax_get_page_post':
                        include_once $config['basepath'] . '/include/page_functions.inc.php';
                        $class = new page_functions();
                        return $class->ajax_get_page_post();
                    case 'ajax_update_page_post_autosave':
                        include_once $config['basepath'] . '/include/page_functions.inc.php';
                        $class = new page_functions();
                        return $class->ajax_update_page_post_autosave();
                    case 'ajax_delete_page_post':
                        include_once $config['basepath'] . '/include/page_functions.inc.php';
                        $class = new page_functions();
                        return $class->ajax_delete_page_post();
                    case 'ajax_create_page_post':
                        include_once $config['basepath'] . '/include/page_functions.inc.php';
                        $class = new page_functions();
                        return $class->ajax_create_page_post();
                    case 'ajax_create_blog_post':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_create_blog_post();
                    case 'ajax_set_blog_cat':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_set_blog_cat();
                    case 'ajax_create_blog_category':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_create_blog_category();
                    case 'ajax_update_blog_post':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_update_blog_post();
                    case 'ajax_update_blog_post_autosave':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_update_blog_post_autosave();
                    case 'ajax_delete_blog_post':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_delete_blog_post();
                    case 'ajax_show_store_addons':
                        include_once $config['basepath'] . '/include/addon_manager.inc.php';
                        $class = new addon_manager();
                        return $class->ajax_show_store_addons();
                    case 'configure_general':
                        include_once $config['basepath'] . '/include/controlpanel.inc.php';
                        $class = new configurator();
                        return $class->ajax_configure_general();
                    case 'configure_uploads':
                        include_once $config['basepath'] . '/include/controlpanel.inc.php';
                        $class = new configurator();
                        return $class->ajax_configure_uploads();
                    case 'configure_listings':
                        include_once $config['basepath'] . '/include/controlpanel.inc.php';
                        $class = new configurator();
                        return $class->ajax_configure_listings();
                    case 'configure_users':
                        include_once $config['basepath'] . '/include/controlpanel.inc.php';
                        $class = new configurator();
                        return $class->ajax_configure_users();
                    case 'configure_social':
                        include_once $config['basepath'] . '/include/controlpanel.inc.php';
                        $class = new configurator();
                        return $class->ajax_configure_social();
                    case 'configure_seo_links':
                        include_once $config['basepath'] . '/include/controlpanel.inc.php';
                        $class = new configurator();
                        return $class->ajax_configure_seo_links();
                    case 'configure_seo':
                        include_once $config['basepath'] . '/include/controlpanel.inc.php';
                        $class = new configurator();
                        return $class->ajax_configure_seo();
                    case 'update_site_config':
                        include_once $config['basepath'] . '/include/controlpanel.inc.php';
                        $class = new configurator();
                        return $class->ajax_update_site_config();
                    case 'smtp_test':
                        include_once $config['basepath'] . '/include/controlpanel.inc.php';
                        $class = new configurator();
                        return $class->ajax_smtp_test();
                    case 'ajax_export_emails':
                        include_once $config['basepath'] . '/include/controlpanel.inc.php';
                        $class = new configurator();
                        return $class->ajax_export_emails();
                    case 'update_seouris':
                        include_once $config['basepath'] . '/include/controlpanel.inc.php';
                        $class = new configurator();
                        return $class->ajax_update_seouris();
                    case 'configure_templates':
                        include_once $config['basepath'] . '/include/controlpanel.inc.php';
                        $class = new configurator();
                        return $class->ajax_configure_templates();
                    case 'edit_blog_post_categories':
                        include_once $config['basepath'] . '/include/blog_editor.inc.php';
                        $class = new blog_editor();
                        return $class->edit_blog_post_categories();
                    case 'edit_blog_post_tags':
                        include_once $config['basepath'] . '/include/blog_editor.inc.php';
                        $class = new blog_editor();
                        return $class->edit_blog_post_tags();
                    case 'load_most_used_tags':
                        include_once $config['basepath'] . '/include/blog_editor.inc.php';
                        $class = new blog_editor();
                        return $class->edit_blog_post_tags(true);
                    case 'ajax_create_blog_tag':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_create_blog_tag();
                    case 'ajax_remove_assigned_blog_tag':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_remove_assigned_blog_tag();
                    case 'ajax_add_assigned_blog_tag_byid':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_add_assigned_blog_tag_byid();
                    case 'blog_settings_general':
                        include_once $config['basepath'] . '/include/blog_editor.inc.php';
                        $class = new blog_editor();
                        return $class->ajax_general_settings();
                    case 'blog_settings_categories':
                        include_once $config['basepath'] . '/include/blog_editor.inc.php';
                        $class = new blog_editor();
                        return $class->ajax_general_categories();
                    case 'blog_settings_tags':
                        include_once $config['basepath'] . '/include/blog_editor.inc.php';
                        $class = new blog_editor();
                        return $class->ajax_general_tags();
                    case 'ajax_rankup_blog_category':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_rankup_blog_category();
                    case 'ajax_rankdown_blog_category':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_rankdown_blog_category();
                    case 'ajax_delete_blog_category':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_delete_blog_category();
                    case 'ajax_get_category_info':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_get_category_info();
                    case 'ajax_update_category_info':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_update_category_info();
                    case 'ajax_get_listing_field_info':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $class = new template_editor();
                        return $class->ajax_get_listing_field_info();
                    case 'ajax_save_listing_field_order':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $class = new template_editor();
                        return $class->ajax_save_listing_field_order();
                    case 'ajax_update_listing_field':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $class = new template_editor();
                        return $class->ajax_update_listing_field();
                    case 'ajax_add_listing_field':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $class = new template_editor();
                        return $class->ajax_add_listing_field();
                    case 'edit_listing_template_qed':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $class = new template_editor();
                        return $class->edit_listing_template_qed();
                        break;
                    case 'edit_listing_template_spo':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $class = new template_editor();
                        return $class->edit_listing_template_spo();
                        break;
                    case 'edit_listing_template_sro':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $class = new template_editor();
                        return $class->edit_listing_template_sro();
                        break;
                    case 'ajax_insert_listing_field':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $class = new template_editor();
                        return $class->ajax_insert_listing_field();
                        break;
                    case 'ajax_save_listing_search_order':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $class = new template_editor();
                        return $class->ajax_save_listing_search_order('spo');
                    case 'ajax_save_search_result_order':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $class = new template_editor();
                        return $class->ajax_save_listing_search_order('sro');
                    case 'edit_form_qed':
                        include_once $config['basepath'] . '/include/lead_manager.inc.php';
                        $class = new lead_manager();
                        return $class->edit_lead_template_qed();
                        break;
                    case 'edit_form_preview':
                        include_once $config['basepath'] . '/include/lead_functions.inc.php';
                        $class = new lead_functions();
                        return $class->edit_form_preview();
                        break;
                    case 'ajax_add_form_field':
                        include_once $config['basepath'] . '/include/lead_manager.inc.php';
                        $class = new lead_manager();
                        return $class->ajax_add_form_field();
                    case 'ajax_insert_form_field':
                        include_once $config['basepath'] . '/include/lead_manager.inc.php';
                        $class = new lead_manager();
                        return $class->ajax_insert_form_field();
                        break;
                    case 'ajax_get_form_field_info':
                        include_once $config['basepath'] . '/include/lead_manager.inc.php';
                        $class = new lead_manager();
                        return $class->ajax_get_form_field_info();
                    case 'ajax_save_form_field_order':
                        include_once $config['basepath'] . '/include/lead_manager.inc.php';
                        $class = new lead_manager();
                        return $class->ajax_save_form_field_order();
                    case 'ajax_get_tag_info':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_get_tag_info();
                    case 'ajax_update_tag_info':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_update_tag_info();
                    case 'ajax_delete_blog_tag':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_delete_blog_tag();
                    case 'ajax_create_blog_tag_noassignment':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_create_blog_tag_noassignment();
                    case 'ajax_display_upload_media':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $class = new media_handler();
                        if (isset($_GET['edit']) && isset($_GET['media_type'])) {
                            return $class->ajax_display_upload_media($_GET['edit'], $_GET['media_type']);
                        }
                        break;
                    case 'ajax_get_media_info':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $class = new media_handler();
                        if (isset($_GET['media_id']) && isset($_GET['media_type'])) {
                            return $class->ajax_get_media_info($_GET['media_id'], $_GET['media_type']);
                        }
                        break;
                    case 'ajax_upload_media':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $class = new media_handler();
                        return $class->ajax_upload_media();
                        break;
                    case 'ajax_upload_media_JSON':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $class = new media_handler();
                        return $class->ajax_upload_media_JSON();
                        break;
                    case 'ajax_display_listing_images':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $class = new image_handler();
                        if (isset($_GET['listing_id'])) {
                            return $class->ajax_display_listing_images($_GET['listing_id']);
                        }
                        break;
                    case 'ajax_save_media_order':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $class = new media_handler();
                        return $class->ajax_save_media_order();
                        break;
                    case 'ajax_delete_media':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $class = new media_handler();
                        return $class->ajax_delete_media();
                        break;
                    case 'ajax_update_media':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $class = new media_handler();
                        return $class->ajax_update_media();
                        break;
                    case 'ajax_display_listing_vtours':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $class = new vtour_handler();
                        if (isset($_GET['listing_id'])) {
                            return $class->ajax_display_listing_vtours($_GET['listing_id']);
                        }
                        break;
                    case 'ajax_display_listing_files':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $class = new file_handler();
                        if (isset($_GET['listing_id'])) {
                            return $class->ajax_display_listing_files($_GET['listing_id']);
                        }
                        break;
                    case 'ajax_delete_all':
                        //ajax_delete_all&media_object_id=*&media_type=listingsimages&media_parent_id={listing_id}
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $class = new media_handler();
                        if (isset($_GET['media_object_id']) && isset($_GET['media_type']) && isset($_GET['media_parent_id'])) {
                            return $class->ajax_delete_all($_GET['media_type'], $_GET['media_parent_id'], $_GET['media_object_id']);
                        }
                        break;
                    case 'ajax_update_listing_data':
                        include_once $config['basepath'] . '/include/listing_editor.inc.php';
                        $listing_editor = new listing_editor();
                        if (isset($_POST['edit'])) {
                            return $listing_editor->ajax_update_listing_data($_POST['edit']);
                        }
                        break;
                    case 'ajax_display_add_listing':
                        include_once $config['basepath'] . '/include/listing_editor.inc.php';
                        $listing_editor = new listing_editor();
                        return $listing_editor->ajax_display_add_listing();
                        break;
                    case 'ajax_add_listing':
                        include_once $config['basepath'] . '/include/listing_editor.inc.php';
                        $listing_editor = new listing_editor();
                        return $listing_editor->ajax_add_listing();
                        break;
                    case 'ajax_display_user_images':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $class = new image_handler();
                        if (isset($_GET['user_id'])) {
                            return $class->ajax_display_user_images($_GET['user_id']);
                        }
                        break;
                    case 'ajax_display_user_files':
                        include_once $config['basepath'] . '/include/media.inc.php';
                        $class = new file_handler();
                        if (isset($_GET['user_id'])) {
                            return $class->ajax_display_user_files($_GET['user_id']);
                        }
                        break;
                    case 'ajax_update_user_data':
                        include_once $config['basepath'] . '/include/user_manager.inc.php';
                        $user_managment = new user_managment();
                        if (isset($_POST['user_id'])) {
                            return $user_managment->ajax_update_user_data($_POST['user_id']);
                        }
                        break;
                    case 'ajax_delete_user':
                        include_once $config['basepath'] . '/include/user_manager.inc.php';
                        $user_managment = new user_managment();
                        if (isset($_GET['user_id'])) {
                            return $user_managment->ajax_delete_user($_GET['user_id']);
                        }
                        break;
                    case 'ajax_delete_listing':
                        include_once $config['basepath'] . '/include/listing_editor.inc.php';
                        $listing_editor = new listing_editor();
                        if (isset($_GET['listing_id'])) {
                            return $listing_editor->ajax_delete_listing($_GET['listing_id']);
                        }
                        break;
                    case 'ajax_display_add_user':
                        include_once $config['basepath'] . '/include/user_manager.inc.php';
                        $user_managment = new user_managment();
                        return $user_managment->ajax_display_add_user();
                        break;
                    case 'ajax_add_user':
                        include_once $config['basepath'] . '/include/user_manager.inc.php';
                        $user_managment = new user_managment();
                        return $user_managment->ajax_add_user();
                        break;
                    case 'blog_wpinject':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_wpinject_run();
                        break;
                    case 'ajax_save_feedback':
                        include_once $config['basepath'] . '/include/lead_manager.inc.php';
                        $lead_manager = new lead_manager();
                        return $lead_manager->ajax_save_feedback();
                        break;
                    case 'ajax_update_lead_field':
                        include_once $config['basepath'] . '/include/lead_manager.inc.php';
                        $lead_manager = new lead_manager();
                        return $lead_manager->ajax_update_lead_field();
                    case 'ajax_save_feedback_status':
                        include_once $config['basepath'] . '/include/lead_manager.inc.php';
                        $lead_manager = new lead_manager();
                        return $lead_manager->ajax_save_feedback_status();
                        break;
                    case 'ajax_change_lead_agent':
                        include_once $config['basepath'] . '/include/lead_manager.inc.php';
                        $lead_manager = new lead_manager();
                        return $lead_manager->ajax_change_lead_agent();
                        break;
                    case 'ajax_change_lead_status':
                        include_once $config['basepath'] . '/include/lead_manager.inc.php';
                        $lead_manager = new lead_manager();
                        return $lead_manager->ajax_change_lead_status();
                        break;
                    case 'ajax_change_lead_priority':
                        include_once $config['basepath'] . '/include/lead_manager.inc.php';
                        $lead_manager = new lead_manager();
                        return $lead_manager->ajax_change_lead_priority();
                        break;
                    case 'ajax_change_lead_notes':
                        include_once $config['basepath'] . '/include/lead_manager.inc.php';
                        $lead_manager = new lead_manager();
                        return $lead_manager->ajax_change_lead_notes();
                        break;
                    case 'ajax_delete_lead':
                        include_once $config['basepath'] . '/include/lead_manager.inc.php';
                        $lead_manager = new lead_manager();
                        if (isset($_GET['lead_id'])) {
                            return $lead_manager->ajax_delete_lead($_GET['lead_id']);
                        }
                        break;
                    case 'do_upgrade':
                        $login_status = $login->verify_priv('Admin');
                        if ($login_status !== true) {
                            die('You Must Be The Site Admin To Do This');
                        }
                        include_once $config['basepath'] . '/include/admin.inc.php';
                        $class = new general_admin();
                        return $class->do_upgrade();
                        break;
                    case 'twitter_disconnect':
                        include_once $config['basepath'] . '/include/social.inc.php';
                        $social = new social();

                        $login_status = $login->verify_priv('edit_site_config');
                        if ($login_status !== true) {
                            return json_encode(['error' => true, 'error_msg' => 'Permission Denied']);
                        }

                        $connection = new TwitterOAuth($config['twitter_consumer_key'], $config['twitter_consumer_secret']);

                        $exception_message = '';

                        try {
                            /* Get temporary credentials. */
                            $request_token = $connection->oauth('oauth/request_token', ['oauth_callback' => $config['baseurl'] . '/admin/index.php?action=twitterback']);
                        } catch (Exception $e) {
                            $exception_message = $e->getMessage() . "\n";
                        }

                        /* Save temporary credentials to session. */
                        $_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
                        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

                        /* If last connection failed don't display authorization link. */
                        $url = '';
                        switch ($connection->getLastHttpCode()) {
                            case 200:
                                /* Build authorize URL and redirect user to Twitter. */
                                //$url = $connection->getAuthorizeURL($token);
                                $url = $connection->url('oauth/authorize', ['oauth_token' =>  $token]);
                                break;
                            case 415:
                                // Callback not approved. Crap
                                $url = '';
                                break;
                        }
                        if ($url != '') {
                            $url = '<a href="' . $url . '" class="or_std_button">' . $lang['connect_to_twitter'] . '</a>';
                        } else {
                            $url = 'Could not connect to Twitter. Refresh the page or try again later. ' . $exception_message;
                        }
                        return json_encode(['error' => false, 'status' => $social->twitter_disconnect(), 'url' => $url]);
                        break;
                    case 'addlead_lookup_member':
                        if (isset($_GET['term'])) {
                            include_once $config['basepath'] . '/include/user_manager.inc.php';
                            $user_managment = new user_managment();
                            $result = $user_managment->ajax_addlead_lookup_user('no', $_GET['term']);
                            header('Content-type: application/json');
                            return json_encode($result);
                        }

                        break;
                    case 'addlead_lookup_agent':
                        if (isset($_GET['term'])) {
                            include_once $config['basepath'] . '/include/user_manager.inc.php';
                            $user_managment = new user_managment();
                            $result = $user_managment->ajax_addlead_lookup_user('yes', $_GET['term']);
                            header('Content-type: application/json');
                            return json_encode($result);
                        }

                        break;
                    case 'addlead_lookup_listing':
                        if (isset($_GET['term'])) {
                            include_once $config['basepath'] . '/include/listing_editor.inc.php';
                            $listing_editor = new listing_editor();
                            $result = $listing_editor->ajax_addlead_lookup_listing($_GET['term']);
                            header('Content-type: application/json');
                            return json_encode($result);
                        }
                        break;
                    case 'addlead_create_member':
                        if (isset($_POST['email']) && isset($_POST['fname']) && isset($_POST['lname'])) {
                            include_once $config['basepath'] . '/include/user_manager.inc.php';
                            $user_managment = new user_managment();
                            return $user_managment->ajax_member_creation($_POST['email'], $_POST['fname'], $_POST['lname'], false);
                        }
                        break;
                    case 'addlead_create_lead':
                        if (isset($_POST['member_id']) && (isset($_POST['listing_id']) || isset($_POST['agent_id'])) && isset($_POST['notes'])) {
                            include_once $config['basepath'] . '/include/lead_manager.inc.php';
                            $lead_manager = new lead_manager();
                            return json_encode($lead_manager->addlead_create_lead());
                        }
                        break;
                    case 'ajax_make_inactive_listing':
                        if (isset($_GET['listing_id'])) {
                            include_once $config['basepath'] . '/include/listing_editor.inc.php';
                            $listing_editor = new listing_editor();
                            return json_encode($listing_editor->ajax_make_inactive_listing($_GET['listing_id']));
                        }
                        break;
                    case 'ajax_make_active_listing':
                        if (isset($_GET['listing_id'])) {
                            include_once $config['basepath'] . '/include/listing_editor.inc.php';
                            $listing_editor = new listing_editor();
                            return json_encode($listing_editor->ajax_make_active_listing($_GET['listing_id']));
                        }
                        break;
                    case 'ajax_make_unfeatured_listing':
                        if (isset($_GET['listing_id'])) {
                            include_once $config['basepath'] . '/include/listing_editor.inc.php';
                            $listing_editor = new listing_editor();
                            return json_encode($listing_editor->ajax_make_unfeatured_listing($_GET['listing_id']));
                        }
                        break;
                    case 'ajax_make_featured_listing':
                        if (isset($_GET['listing_id'])) {
                            include_once $config['basepath'] . '/include/listing_editor.inc.php';
                            $listing_editor = new listing_editor();
                            return json_encode($listing_editor->ajax_make_featured_listing($_GET['listing_id']));
                        }
                        break;
                    case 'ajax_viewlog_datatable':
                        include_once $config['basepath'] . '/include/log.inc.php';
                        $log = new log();
                        return $log->ajax_viewlog_datatable();
                    case 'ajax_get_menus':
                        include_once $config['basepath'] . '/include/menu_editor.inc.php';
                        $menu_editor = new menu_editor();
                        return $menu_editor->ajax_get_menus();
                    case 'ajax_get_menu_items':
                        if (isset($_POST['menu_selection'])) {
                            include_once $config['basepath'] . '/include/menu_editor.inc.php';
                            $menu_editor = new menu_editor();
                            return $menu_editor->ajax_get_menu_items($_POST['menu_selection']);
                        }
                        break;
                    case 'ajax_get_menu_item_details':
                        if (isset($_GET['item_id'])) {
                            include_once $config['basepath'] . '/include/menu_editor.inc.php';
                            $menu_editor = new menu_editor();
                            return $menu_editor->ajax_get_menu_item_details($_GET['item_id']);
                        }
                        break;
                    case 'ajax_set_menu_order':
                        if (isset($_POST['menu_id']) && isset($_POST['menu_items'])) {
                            include_once $config['basepath'] . '/include/menu_editor.inc.php';
                            $menu_editor = new menu_editor();
                            return $menu_editor->ajax_set_menu_order($_POST['menu_id'], $_POST['menu_items']);
                        }
                        break;
                    case 'ajax_get_pages':
                        include_once $config['basepath'] . '/include/page_functions.inc.php';
                        $class = new page_functions();
                        return $class->ajax_get_pages();
                    case 'ajax_get_blogs':
                        include_once $config['basepath'] . '/include/blog_functions.inc.php';
                        $class = new blog_functions();
                        return $class->ajax_get_blogs();
                    case 'ajax_save_menu_item':
                        if (
                            isset($_POST['item_id']) && isset($_POST['item_name']) && isset($_POST['item_type']) && isset($_POST['item_value']) &&
                            isset($_POST['visible_guest']) && isset($_POST['visible_member']) && isset($_POST['visible_agent']) && isset($_POST['visible_admin'])
                            && isset($_POST['item_target']) && isset($_POST['item_class'])
                        ) {
                            include_once $config['basepath'] . '/include/menu_editor.inc.php';
                            $class = new menu_editor();
                            return $class->ajax_save_menu_item(
                                $_POST['item_id'],
                                $_POST['item_name'],
                                $_POST['item_type'],
                                $_POST['item_value'],
                                $_POST['item_target'],
                                $_POST['item_class'],
                                $_POST['visible_guest'],
                                $_POST['visible_member'],
                                $_POST['visible_agent'],
                                $_POST['visible_admin']
                            );
                        }
                        break;
                    case 'ajax_add_menu_item':
                        if (isset($_POST['item_name']) && isset($_POST['parent_id']) && isset($_POST['menu_id'])) {
                            include_once $config['basepath'] . '/include/menu_editor.inc.php';
                            $class = new menu_editor();
                            return $class->ajax_add_menu_item($_POST['menu_id'], $_POST['item_name'], $_POST['parent_id']);
                        }
                        break;
                    case 'ajax_delete_menu_item':
                        if (isset($_POST['item_id'])) {
                            include_once $config['basepath'] . '/include/menu_editor.inc.php';
                            $class = new menu_editor();
                            return $class->ajax_delete_menu_item($_POST['item_id']);
                        }
                        break;
                    case 'ajax_delete_menu':
                        if (isset($_POST['menu_id'])) {
                            include_once $config['basepath'] . '/include/menu_editor.inc.php';
                            $class = new menu_editor();
                            return $class->ajax_delete_menu($_POST['menu_id']);
                        }
                        break;
                    case 'ajax_create_menu':
                        if (isset($_POST['add_menu_name'])) {
                            include_once $config['basepath'] . '/include/menu_editor.inc.php';
                            $class = new menu_editor();
                            return $class->ajax_create_menu($_POST['add_menu_name']);
                        }
                        break;
                    case 'ajax_leadmanager_datatable':
                        if (isset($_GET['show_all_leads'])) {
                            include_once $config['basepath'] . '/include/lead_manager.inc.php';
                            $lead_manager = new lead_manager();
                            if (isset($_GET['show_all_leads']) && $_GET['show_all_leads'] == 'true') {
                                return $lead_manager->ajax_leadmanager_datatable(true);
                            } else {
                                return $lead_manager->ajax_leadmanager_datatable(false);
                            }
                        }
                        break;
                    case 'ajax_reset_password':
                        include_once $config['basepath'] . '/include/login.inc.php';
                        $login = new login();
                        return $login->ajax_reset_password();
                        break;
                    case 'ajax_change_user_status':
                        if (isset($_POST['user_id']) && is_numeric($_POST['user_id']) && isset($_POST['user_active'])) {
                            include_once $config['basepath'] . '/include/user_manager.inc.php';
                            $user_managment = new user_managment();
                            return $user_managment->change_user_status($_POST['user_id'], $_POST['user_active']);
                        } else {
                            header('Content-type: application/json');
                            return json_encode(['error' => '3', 'error_msg' => $lang['access_denied']]);
                        }
                        break;
                    case 'generate_sitemap':
                        include_once $config['basepath'] . '/include/sitemap.inc.php';
                        $sitemap = new sitemap();
                        $data = $sitemap->generate();
                        return $data;
                        break;
                    case 'send_notifications':
                        include_once $config['basepath'] . '/include/notification.inc.php';
                        $notify = new notification();
                        $data = $notify->NotifyUsersOfAllNewListings();
                        return $data;
                        break;
                    case 'ajax_save_class_rank':
                        include_once $config['basepath'] . '/include/propertyclass.inc.php';
                        $class = new propertyclass();
                        return $class->ajax_save_class_rank();
                        break;
                    case 'ajax_modify_property_class':
                        include_once $config['basepath'] . '/include/propertyclass.inc.php';
                        $class = new propertyclass();
                        return $class->ajax_modify_property_class();
                        break;
                    case 'ajax_save_user_rank':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $sort = new template_editor();
                        return $sort->ajax_save_user_rank();
                        break;
                    case 'ajax_get_user_field_info':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $class = new template_editor();
                        if (isset($_GET['user_type'])) {
                            return $class->ajax_get_user_field_info($_GET['user_type']);
                        } elseif (isset($_POST['user_type'])) {
                            return $class->ajax_get_user_field_info($_POST['user_type']);
                        }
                        break;
                    case 'ajax_update_user_field':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $class = new template_editor();
                        return $class->ajax_update_user_field();
                        break;
                    case 'ajax_add_user_field':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $class = new template_editor();
                        if (isset($_GET['user_type'])) {
                            return $class->ajax_add_user_field($_GET['user_type']);
                        } elseif (isset($_POST['user_type'])) {
                            return $class->ajax_add_user_field($_POST['user_type']);
                        }
                        break;
                    case 'ajax_insert_user_field':
                        include_once $config['basepath'] . '/include/template_editor.inc.php';
                        $class = new template_editor();
                        return $class->ajax_insert_user_field();
                        break;
                    case 'ajax_export_logs':
                        include_once $config['basepath'] . '/include/log.inc.php';
                        $class = new log();
                        return $class->ajax_export_logs();
                        break;

                    default:
                        // Handle Addons
                        $addon_name = [];
                        if (preg_match('/^addon_(.\S*?)_.*/', $_GET['action'], $addon_name)) {
                            include_once $config['basepath'] . '/addons/' . $addon_name[1] . '/addon.inc.php';
                            $function_name = $addon_name[1] . '_run_action_ajax';
                            $data = $function_name();
                            return $data;
                        }
                }
            }
        }
    }
}
