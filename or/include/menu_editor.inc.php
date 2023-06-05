<?php

class menu_editor
{
    public function ajax_get_menus()
    {
        global $api;
        $api_result = $api->load_local_api('menu__metadata', []);
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($api_result);
    }

    public function ajax_get_menu_items($menu_id)
    {
        global $api, $lang, $misc;
        if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
        }
        $api_result = $api->load_local_api('menu__read', ['menu_id' => $menu_id]);
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($api_result);
    }

    public function ajax_get_menu_item_details($item_id)
    {
        global $api;
        $api_result = $api->load_local_api('menu__item_details', ['item_id' => $item_id]);
        return json_encode($api_result);
    }

    public function ajax_set_menu_order($menu_id, $menu_items)
    {
        global $api;
        $api_result = $api->load_local_api('menu__set_menu_order', ['menu_id' => $menu_id, 'menu_items' => $menu_items]);
        return json_encode($api_result);
    }

    public function ajax_save_menu_item($item_id, $item_name, $item_type, $item_value, $item_target, $item_class, $visible_guest, $visible_member, $visible_agent, $visible_admin)
    {
        global $api;
        $api_result = $api->load_local_api('menu__save_menu_item', [
            'item_id' => $item_id, 'item_name' => $item_name, 'item_type' => $item_type, 'item_value' => $item_value,
            'item_target' => $item_target, 'item_class' => $item_class, 'visible_guest' => $visible_guest, 'visible_member' => $visible_member, 'visible_agent' => $visible_agent, 'visible_admin' => $visible_admin,
        ]);
        return json_encode($api_result);
    }

    public function ajax_add_menu_item($menu_id, $item_name, $parent_id)
    {
        global $api, $lang, $misc;
        if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
        }
        $api_result = $api->load_local_api('menu__add_menu_item', ['menu_id' => $menu_id, 'item_name' => $item_name, 'parent_id' => $parent_id]);
        return json_encode($api_result);
    }

    public function ajax_delete_menu_item($item_id)
    {
        global $api;
        $api_result = $api->load_local_api('menu__delete_menu_item', ['item_id' => $item_id]);
        return json_encode($api_result);
    }

    public function ajax_delete_menu($menu_id)
    {
        global $api;
        $api_result = $api->load_local_api('menu__delete', ['menu_id' => $menu_id]);
        return json_encode($api_result);
    }

    public function ajax_create_menu($menu_name)
    {
        global $api, $lang, $misc;
        if (!isset($_POST['token']) || !$misc->validate_csrf_token_ajax($_POST['token'])) {
            header('Content-type: application/json');
            return json_encode(['error' => '1', 'error_msg' => $lang['invalid_csrf_token']]);
        }
        $api_result = $api->load_local_api('menu__create', ['menu_name' => $menu_name]);
        return json_encode($api_result);
    }

    public function render_menu($menu_id)
    {
        global $config, $jscript, $lang, $api;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $api_result = $api->load_local_api('menu__read', ['menu_id' => $menu_id]);
        if ($api_result['error']) {
            return $api_result['error_msg'];
        }
        $display = '';
        //$display .= '<pre>'.print_r($api_result['menu'],TRUE).'</pre>';
        $display .= '<ul class="or_menu" id="or_menu_' . $menu_id . '">' . $this->build_menu_html($api_result['menu'], 0) . '</ul>';
        return $display;
    }

    private function build_menu_html($menuarray, $parent_id)
    {
        global $config;
        include_once $config['basepath'] . '/include/core.inc.php';
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $page = new page_user();
        $menu = '';
        if (isset($menuarray[$parent_id])) {
            foreach ($menuarray[$parent_id] as $order => $menu_item) {
                $item_id = $menu_item['item_id'];
                $item_name = $menu_item['item_name'];
                $item_type = $menu_item['item_type'];
                $item_target = $menu_item['item_target'];
                $item_value = $menu_item['item_value'];
                $item_class = $menu_item['item_class'];
                $item_types = [1 => 'action', 2 => 'page', 3 => 'custom', 4 => 'divider', '5' => 'block', 6 => 'blog'];
                //Get Users permission Levels
                $is_agent = $login_status = $login->verify_priv('Agent');
                $is_admin = $login_status = $login->verify_priv('Admin');
                $is_member = $login_status = $login->verify_priv('Member');
                //Get Items Visible States
                $visible_guest = $menu_item['visible_guest'];
                $visible_member = $menu_item['visible_member'];
                $visible_agent = $menu_item['visible_agent'];
                $visible_admin = $menu_item['visible_admin'];
                $visible = false;
                if (!$is_member && !$is_agent && !$is_admin && $visible_guest) {
                    $visible = true;
                } elseif ($is_member && !$is_agent && !$is_admin && $visible_member) {
                    $visible = true;
                } elseif ($is_agent && !$is_admin && $visible_agent) {
                    $visible = true;
                } elseif ($is_admin && $visible_admin) {
                    $visible = true;
                }
                if (!$visible) {
                    continue;
                }
                //Build URL..
                $url = '';
                switch ($item_type) {
                    case 1:
                        //Action
                        if ($item_value != '') {
                            $url = '{' . $item_value . '}';
                        } else {
                            $url = '#';
                        }
                        break;
                    case 2:
                        //Page
                        if ($item_value > 0) {
                            $url = '{page_link_' . intval($item_value) . '}';
                        } else {
                            $url = '#';
                        }
                        break;
                    case 6:
                        //Page
                        if ($item_value > 0) {
                            $url = '{blog_link_' . intval($item_value) . '}';
                        } else {
                            $url = '#';
                        }
                        break;
                    case 3:
                        //Custom
                        $url = $item_value;
                        break;
                    case 4:
                        //Divider
                        $item_type = $menu_item['item_type'];
                        if (isset($menuarray[$item_id])) {
                            $menu .= '<li class="or_menu_item or_menu_parent or_menu_item_type_' . $item_types[$item_type] . '" id="or_menu_item_' . $item_id . '">' . $item_name . '<ul>';
                            $menu .= $this->build_menu_html($menuarray, $item_id);
                            $menu .= '</ul></li>';
                        } else {
                            $menu .= '<li class="or_menu_item or_menu_item_type_' . $item_types[$item_type] . '" id="or_menu_item_' . $item_id . '">' . $item_name . '</li>';
                        }
                        continue (2);
                    case 5:
                        switch ($item_value) {
                            case 'blog_recent_comments_block':
                                $menu .= '{blog_recent_comments_block}
                                    <li class="or_menu_item or_menu_item_type_' . $item_types[$item_type] . '"><a href="{blog_recent_comments_url}" class="' . $item_class . '" title="{blog_recent_comments_title}">{blog_recent_comments_title}</a></li>
                                    {/blog_recent_comments_block}';
                                break;
                            case 'blog_recent_post_block':
                                $menu .= '{blog_recent_post_block}
                                <li class="or_menu_item or_menu_item_type_' . $item_types[$item_type] . '"><a href="{blog_recent_post_url}" class="' . $item_class . '" title="{blog_recent_post_title}">{blog_recent_post_title}</a></li>
                                {/blog_recent_post_block}';
                                break;
                            case 'blog_archive_link_block':
                                $menu .= '{blog_archive_link_block}
                                    <li class="or_menu_item or_menu_item_type_' . $item_types[$item_type] . '"><a href="{blog_archive_url}" class="' . $item_class . '" title="{blog_archive_title}">{blog_archive_title}</a></li>
                                {/blog_archive_link_block}';
                                break;
                            case 'blog_category_link_block':
                                $menu .= '{blog_category_link_block}
                                    <li class="or_menu_item or_menu_item_type_' . $item_types[$item_type] . '"><a href="{blog_cat_url}" class="' . $item_class . '" title="{blog_cat_title}">{blog_cat_title}</a></li>
                                {/blog_category_link_block}';
                                break;
                            case 'pclass_searchlinks_block':
                                global $api;
                                $class_metadata = $api->load_local_api('pclass__metadata', []);
                                $keys = array_keys($class_metadata['metadata']);
                                if (is_array($keys)) {
                                    foreach ($keys as $class_id) {
                                        $menu .= '<li class="or_menu_item or_menu_item_type_' . $item_types[$item_type] . '"><a href="' . $config['baseurl'] . '/index.php?action=search_step_2&amp;pclass%5B%5D=' . $class_id . '" class="' . $item_class . '" title="' . $class_metadata['metadata'][$class_id]['name'] . '">' . $class_metadata['metadata'][$class_id]['name'] . '</a></li>' . BR;
                                    }
                                }
                                break;
                            default:
                                break;
                        }
                        continue (2);
                }
                $item_type = $menu_item['item_type'];
                if (isset($menuarray[$item_id])) {
                    $menu .= '<li class="or_menu_item or_menu_parent or_menu_item_type_' . $item_types[$item_type] . '" id="or_menu_item_' . $item_id . '"><a href="' . $url . '" title="' . $item_name . '" class="' . $item_class . '" target="' . $item_target . '">' . $item_name . '</a><ul>';
                    $menu .= $this->build_menu_html($menuarray, $item_id);
                    $menu .= '</ul></li>';
                } else {
                    $menu .= '<li class="or_menu_item or_menu_item_type_' . $item_types[$item_type] . '" id="or_menu_item_' . $item_id . '"><a href="' . $url . '" title="' . $item_name . '" class="' . $item_class . '"  target="' . $item_target . '">' . $item_name . '</a></li>';
                }
            }
        }

        $page->page = $menu;
        $page->replace_urls();
        $page->replace_blog_template_tags();
        $page->auto_replace_tags();
        $page->replace_lang_template_tags();
        $menu = $page->return_page();

        return $menu;
    }

    public function show_editor()
    {
        global $config, $lang;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('editpages');
        $display = '';
        if ($security === true) {
            global $misc, $jscript;

            $jscript .= '';
            $jscript .= '
			';

            //Load the Core Template
            include_once $config['basepath'] . '/include/core.inc.php';

            $page = new page_admin();

            //Load Template File
            $page->load_page($config['admin_template_path'] . '/menu_editor_index.html');

            //We are done finish output
            $page->replace_lang_template_tags(true);
            $page->replace_permission_tags();
            $page->auto_replace_tags('', true);
            $display .= $page->return_page();
        } else {
            $display = '{lang_emenu_permission_denied}';
        }

        return $display;
    }
}
