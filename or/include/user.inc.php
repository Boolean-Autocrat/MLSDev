<?php

use Sabre\VObject;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;

class user
{
    /**
     *
     * @param  $type
     * @return
     */

    public function view_users()
    {
        global $conn, $config, $agent_id, $misc;

        include_once dirname(__FILE__) . '/core.inc.php';
        $user_section = '';
        $page = new page_user();
        $page->load_page($config['template_path'] . '/view_users_default.html');
        $addons = $page->load_addons();
        $addon_fields = $page->get_addon_template_field_list($addons);

        if ($config['show_admin_on_agent_list'] == 0) {
            $options = 'userdb_is_agent = \'yes\'';
        } else {
            $options = '(userdb_is_agent = \'yes\' or userdb_is_admin = \'yes\')';
        }
        //Get User Count
        $sql = 'SELECT count(userdb_id) as user_count
		FROM ' . $config['table_prefix'] . 'userdb
		WHERE ' . $options . ' and userdb_active = \'yes\' and userdb_rank > 0
		ORDER BY userdb_rank,userdb_user_name';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $num_rows = $recordSet->fields('user_count');

        $sql = 'SELECT userdb_id
				FROM ' . $config['table_prefix'] . 'userdb
				WHERE ' . $options . ' and userdb_active = \'yes\'  and userdb_rank > 0
				ORDER BY userdb_rank, userdb_user_name';
        //Handle Pagnation
        if (!isset($_GET['cur_page'])) {
            $_GET['cur_page'] = 0;
        }
        $limit_str = intval($_GET['cur_page']) * $config['users_per_page'];

        $next_prev = $misc->next_prev($num_rows, intval($_GET['cur_page']), ''); // put in the next/previous stuff
        $recordSet = $conn->SelectLimit($sql, $config['users_per_page'], $limit_str);

        if (!$recordSet) {
            $misc->log_error($sql);
        }
        while (!$recordSet->EOF) {
            $agent_id = $recordSet->fields('userdb_id');
            $user_section .= $page->get_template_section('user_block');
            $user_section = $page->replace_user_field_tags($agent_id, $user_section, 'agent');
            $user_section = $page->parse_addon_tags($user_section, $addon_fields);
            $recordSet->MoveNext();
        }

        $page->replace_template_section('user_block', $user_section);
        $page->page = str_replace('{next_prev}', $next_prev, $page->page);
        return $page->page;
    }

    /**
     * View User
     *
     * @param   (none)
     * @return  string $display
     */
    public function view_user()
    {
        global $lang, $config, $misc;
        $display = '';
        $userdb_id = intval($_GET['user']);

        if ($userdb_id != '' && $userdb_id > 0) {
            $is_agent = $misc->get_agent_status($userdb_id);
            $is_admin = $misc->get_admin_status($userdb_id);

            if (($is_agent === true) || ($is_admin == true && $config['show_listedby_admin'] == 1) ||  ($is_admin == true && $config['show_admin_on_agent_list'] == 1)) {
                include_once dirname(__FILE__) . '/core.inc.php';
                $page = new page_user();
                include_once dirname(__FILE__) . '/media.inc.php';
                $image_handler = new image_handler();
                $file_handler = new file_handler();
                $page->load_page($config['template_path'] . '/' . $config['agent_template']);
                //Replace Tags
                $page->replace_user_field_tags($userdb_id, '', 'user');
                //TODO: Move other user related tags to the core replace_user_field_tag function
                $page->page = str_replace('{user_images_thumbnails}', $image_handler->renderUserImages($userdb_id), $page->page);
                $page->page = str_replace('{user_vcard_link}', $this->vcard_agent_link($userdb_id), $page->page);
                $page->page = str_replace('{user_listings_list}', $this->userListings($userdb_id), $page->page);

                $page->page = preg_replace_callback(
                    '/{user_listings_list_([0-9]*)}/is',
                    function ($matches) {
                        global $config, $userdb_id;
                        include_once $config['basepath'] . '/include/user.inc.php';
                        $u = new user();
                        $tag = $u->userListings($userdb_id, $matches[1]);
                        return $tag;
                    },
                    $page->page
                );

                $page->page = str_replace('{user_hit_count}', $this->userHitcount($userdb_id), $page->page);
                $page->page = str_replace('{user_listings_link}', $this->userListingsLink($userdb_id), $page->page);
                $page->page = str_replace('{files_user_horizontal}', $file_handler->render_templated_files($userdb_id, 'user', 'horizontal'), $page->page);
                $page->page = str_replace('{files_user_vertical}', $file_handler->render_templated_files($userdb_id, 'user', 'vertical'), $page->page);
                $page->page = str_replace('{user_files_select}', $file_handler->render_files_select($userdb_id, 'user'), $page->page);

                $display = $page->page;
            } else {
                $display = $lang['user_manager_invalid_user_id'];
            }
        }
        return $display;
    }

    /**
     * User Listings
     *
     * @param   integer $userdb_id
     *          integer $limit
     * @return  string $display
     */
    public function userListings($userdb_id, $limit = 50)
    {
        global $api, $lang;

        include_once dirname(__FILE__) . '/core.inc.php';
        $page = new page_user();
        $display = '';
        $userdb_id = intval($userdb_id);
        $display .= '<strong>' . $lang['users_other_listings'] . '</strong>';

        $result = $api->load_local_api('listing__search', [
            'parameters' => [
                'listingsdb_active' => 'yes',
                'user_ID' => $userdb_id,
            ],
            'sortby' => ['listingsdb_id'],
            'sorttype' => ['ASC'],
            'limit' => $limit,
            'offset' => 0,
        ]);

        if ($result['listing_count'] > 0) {
            foreach ($result['listings'] as $listingsdb_id) {
                $api_read = $api->load_local_api('listing__read', ['listing_id' => $listingsdb_id, 'fields' => ['listingsdb_title']]);
                if (!$api_read['error']) {
                    $listingsdb_title = $api_read['listing']['listingsdb_title'];
                }
                $url = $page->magicURIGenerator('listing', $listingsdb_id, true);
                $display .= '<li> <a href="' . $url . '">' . $listingsdb_title . '</a></li>';
            }
        }
        $display .= '</ul>';

        return $display;
    } // end function userListings

    /**
     * User Hit count
     *
     * @param   integer $userdb_id
     * @return  string $display
     */
    public function userHitcount($userdb_id)
    {
        // hit counter for user listings
        global $api, $lang, $config, $conn, $misc;

        $display = '';
        $hit_count = '';
        $userdb_id = intval($userdb_id);

        $result = $api->load_local_api('user__read', [
            'user_id' => $userdb_id,
            'resource' => 'agent',
            'fields' => [
                'userdb_hit_count',
            ],
        ]);
        if (!$result['error']) {
            $hit_count = $result['user']['userdb_hit_count'];
            $hit_count = $hit_count + 1;

            /*Can't use this
            $update = $api->load_local_api('user__update',array(
                'user_id' => $userdb_id,
                'user_details'=>array(
                'hit_count' => $hit_count
                )
            ));
            */

            $sql = 'UPDATE ' . $config['table_prefix'] . 'userdb
				SET userdb_hit_count=userdb_hit_count+1
				WHERE userdb_id=' . $userdb_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
        }

        $display .= $lang['this_user_has_been_viewed'] . ' <strong>' . $hit_count . '</strong> ' . $lang['times'];

        return $display;
    }

    /**
     * Get user single item (replaces several get_user_xx() functions)
     *
     * @param   string $field_name (e.g. userdb_user_first_name)
     *          integer $userdb_id
     * @return  string $display
     */
    public function get_user_single_item($field_name, $userdb_id)
    {
        global $conn, $config, $misc, $api;

        $display = '';
        $userdb_id = intval($userdb_id);

        $is_agent = $misc->get_agent_status($userdb_id);
        $is_admin = $misc->get_admin_status($userdb_id);

        if ($is_agent === true || $is_admin === true) {
            $resource = 'agent';
        } else {
            $resource = 'member';
        }

        $result_data = $api->load_local_api('user__read', [
            'user_id' => $userdb_id,
            'resource' => $resource,
            'fields' => [
                $field_name,
            ],
        ]);
        if (!$result_data['error']) {
            $display .= $result_data['user'][$field_name];
        }

        return $display;
    }

    /**
     * Get user type
     *
     * @param   integer $userdb_id
     * @return  string (admin/agent/member)
     */
    public function get_user_type($user)
    {
        global $misc;

        $userdb_id = intval($user);
        $is_agent = $misc->get_agent_status($userdb_id);
        $is_admin = $misc->get_admin_status($userdb_id);

        if ($is_admin) {
            return 'admin';
        } elseif ($is_agent) {
            return 'agent';
        }
        return 'member';
    }

    /**
     * Contact agent link
     *
     * @param  integer $userdb_id
     * @return string $display
     */
    public function contact_agent_link($userdb_id)
    {
        global $config, $lang;
        $userID = intval($userdb_id);
        $display = '<a href="' . $config['baseurl'] . '/index.php?action=contact_agent&amp;agent_id=' . $userdb_id . '">' . $lang['contact_agent'] . '</a>';
        return $display;
    }

    public function renderUserInfo($user)
    {
        global $conn, $config, $misc;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $display = '';
        $formDB = $this->determine_user_formtype($user);
        $user = intval($user);
        $priv_sql = '';

        if ($formDB == 'agentformelements') {
            //Check Users Permissions.
            $display_agent = $login->verify_priv('Agent');
            $display_member = $login->verify_priv('Member');
            if ($display_agent == true) {
                $priv_sql = 'AND ' . $formDB . '_display_priv <= 2 ';
            } elseif ($display_member == true) {
                $priv_sql = 'AND ' . $formDB . '_display_priv <= 1 ';
            } else {
                $priv_sql = 'AND ' . $formDB . '_display_priv = 0 ';
            }
        }
        $sql = 'SELECT userdbelements_field_value, ' . $formDB . '_field_type, ' . $formDB . '_field_caption
				FROM ' . $config['table_prefix'] . 'userdbelements, ' . $config['table_prefix'] . $formDB . '
				WHERE ((userdb_id = ' . $user . ')
				AND (userdbelements_field_name = ' . $formDB . '_field_name)) ' . $priv_sql . '
				ORDER BY ' . $formDB . '_rank ASC';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        while (!$recordSet->EOF) {
            $field_value = $recordSet->fields('userdbelements_field_value');
            $field_type = $recordSet->fields($formDB . '_field_type');
            $field_caption = $recordSet->fields($formDB . '_field_caption');
            if ($field_value != '') {
                if ($field_type == 'select-multiple' or $field_type == 'option' or $field_type == 'checkbox') {
                    // handle field types with multiple options
                    $display .= '<br /><strong>' . $field_caption . '</strong>: <br />';
                    $feature_index_list = explode('||', $field_value);
                    foreach ($feature_index_list as $feature_list_item) {
                        $display .= $feature_list_item;
                        $display .= $config['feature_list_separator'];
                    } // end while
                } // end if field type is a multiple typ
                elseif ($field_type == 'price') {
                    $money_amount = $misc->international_num_format($field_value);
                    $display .= '<strong>' . $field_caption . '</strong>: ' . $misc->money_formats($money_amount) . '<br />';
                } // end elseif
                elseif ($field_type == 'number') {
                    $display .= '<strong>' . $field_caption . '</strong>: ' . $misc->international_num_format($field_value) . '<br />';
                } // end elseif
                elseif ($field_type == 'url') {
                    if ($formDB == 'agentformelements') {
                        $display .= '<strong>' . $field_caption . '</strong>: <a href="' . $field_value . '" onclick="window.open(this.href,\'_blank\',\'location=1,resizable=1,status=1,scrollbars=1,toolbar=1,menubar=1,noopener,noreferrer\');return false">' . $field_value . '</a><br />';
                    } else {
                        $display .= '<strong>' . $field_caption . '</strong>: <a href="' . $field_value . '" onclick="window.open(this.href,\'_blank\',\'location=1,resizable=1,status=1,scrollbars=1,toolbar=1,menubar=1,noopener,noreferrer\');return false" rel="nofollow">' . $field_value . '</a><br />';
                    }
                } elseif ($field_type == 'email') {
                    $display .= '<strong>' . $field_caption . '</strong>: <a href="mailto:' . $field_value . '">' . $field_value . '</a><br />';
                } elseif ($field_type == 'date') {
                    if ($config['date_format'] == 1) {
                        $format = 'm/d/Y';
                    } elseif ($config['date_format'] == 2) {
                        $format = 'Y/d/m';
                    } elseif ($config['date_format'] == 3) {
                        $format = 'd/m/Y';
                    }
                    $field_value = date($format, intval($field_value));
                    $display .= '<strong>' . $field_caption . '</strong>: ' . $field_value . '<br />';
                } else {
                    if ($config['add_linefeeds'] === '1') {
                        $field_value = nl2br($field_value); //replace returns with <br />
                    } // end if
                    $display .= '<strong>' . $field_caption . '</strong>: ' . $field_value . '<br />';
                } // end else
            } // end if
            $recordSet->MoveNext();
        } // end while
        return $display;
    } // end renderUserInfo

    /**
     * Determine user form type
     *
     * @param  integer $userdb_id
     * @return string $formDB
     */
    public function determine_user_formtype($userdb_id)
    {
        global $conn, $config, $misc;

        $userID = intval($userdb_id);
        $is_agent = $misc->get_agent_status($userdb_id);
        $is_admin = $misc->get_admin_status($userdb_id);
        if ($is_agent === true || $is_admin === true) {
            $formDB = 'agentformelements';
        } else {
            $formDB = 'memberformelements';
        }
        return $formDB;
    }

    public function renderSingleListingItem($userID, $name, $display_type = 'both')
    {
        // Display_type - Sets what should be returned.
        // both - Displays both the caption and the formated value
        // value - Displays just the formated value
        // rawvalue - Displays just the raw value
        // caption - Displays only the captions
        global $conn, $config, $misc;

        $display = '';
        $formDB = $this->determine_user_formtype($userID); //agentformelements, memberformelements
        $userID = intval($userID);
        $name = $misc->make_db_safe($name);
        $user_type = $this->get_user_type($userID);
        $sql = 'SELECT userdbelements_field_value, ' . $formDB . '_id, ' . $formDB . '_field_type, ' . $formDB . '_field_caption
				FROM ' . $config['table_prefix'] . 'userdbelements, ' . $config['table_prefix'] . $formDB . '
				WHERE ((userdb_id = ' . $userID . ')
				AND (' . $formDB . '_field_name = userdbelements_field_name)
				AND (userdbelements_field_name = ' . $name . '))';

        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        while (!$recordSet->EOF) {
            $field_value = $recordSet->fields('userdbelements_field_value');
            $field_type = $recordSet->fields($formDB . '_field_type');
            $form_elements_id = $recordSet->fields($formDB . '_id');
            if (!isset($_SESSION['users_lang'])) {
                // Hold empty string for translation fields, as we are workgin with teh default lang
                $field_caption = $recordSet->fields($formDB . '_field_caption');
            } else {
                $lang_sql = 'SELECT ' . $formDB . '_field_caption
							FROM ' . $config['lang_table_prefix'] . $formDB . '
							WHERE ' . $formDB . '_id = ' . $form_elements_id;
                $lang_recordSet = $conn->Execute($lang_sql);
                if (!$lang_recordSet) {
                    $misc->log_error($lang_sql);
                }
                $field_caption = $lang_recordSet($formDB . '_field_caption');
            }

            if ($field_value != '') {
                if ($display_type === 'both' || $display_type === 'caption') {
                    $display .= '<span class="field_caption">' . $field_caption . '</span>';
                }
                if ($display_type == 'both') {
                    $display .= ':&nbsp;';
                }
                if ($display_type === 'both' || $display_type === 'value') {
                    if ($field_type == 'select-multiple' or $field_type == 'option' or $field_type == 'checkbox') {
                        // handle field types with multiple options
                        $feature_index_list = explode('||', $field_value);
                        sort($feature_index_list);
                        foreach ($feature_index_list as $feature_list_item) {
                            $display .= '<br />' . $feature_list_item;
                        } // end while
                    } // end if field type is a multiple type
                    elseif ($field_type == 'price') {
                        $money_amount = $misc->international_num_format($field_value, $config['number_decimals_price_fields']);
                        $display .= $misc->money_formats($money_amount);
                    } // end elseif
                    elseif ($field_type == 'number') {
                        $display .= $misc->international_num_format($field_value, $config['number_decimals_number_fields']);
                    } // end elseif
                    elseif ($field_type == 'url') {
                        if ($user_type == 'member') {
                            $display .= '<a href="' . $field_value . '" onclick="window.open(this.href,\'_blank\',\'location=1,resizable=1,status=1,scrollbars=1,toolbar=1,menubar=1,noopener,noreferrer\');return false" rel="nofollow">' . $field_value . '</a>';
                        } else {
                            $display .= '<a href="' . $field_value . '" onclick="window.open(this.href,\'_blank\',\'location=1,resizable=1,status=1,scrollbars=1,toolbar=1,menubar=1,noopener,noreferrer\');return false">' . $field_value . '</a>';
                        }
                    } elseif ($field_type == 'email') {
                        $display .= '<a href="mailto:' . $field_value . '">' . $field_value . '</a>';
                    } elseif ($field_type == 'text' or $field_type == 'textarea') {
                        if ($config['add_linefeeds'] === '1') {
                            $field_value = nl2br($field_value); //replace returns with <br />
                        } // end if
                        $display .= $field_value;
                    } elseif ($field_type == 'date') {
                        if ($config['date_format'] == 1) {
                            $format = 'm/d/Y';
                        } elseif ($config['date_format'] == 2) {
                            $format = 'Y/d/m';
                        } elseif ($config['date_format'] == 3) {
                            $format = 'd/m/Y';
                        }
                        $field_value = date($format, $field_value);
                        $display .= $field_value;
                    } else {
                        $display .= $field_value;
                    } // end else
                }
                if ($display_type === 'rawvalue') {
                    $display .= $field_value;
                }
            } // end if
            $recordSet->MoveNext();
        } // end while
        return $display;
    } // end renderSingleListingItem

    public function vcard_agent_link($user)
    {
        global $lang;
        $display = '';
        $display .= '<a href="index.php?action=create_vcard&amp;user=' . $user . '">' . $lang['vcard_link_text'] . '</a>';
        return $display;
    }

    public function create_vcard($user)
    {
        global $config, $conn, $misc;

        // define vcard
        $vcard = new VObject\Component\VCard();

        $first = $this->get_user_single_item('userdb_user_first_name', $user);
        $last = $this->get_user_single_item('userdb_user_last_name', $user);
        $additional = '';
        $prefix = '';
        $suffix = '';


        $vcard->add('N', [$last, $first, $additional, $prefix, $suffix]);

        $sql = 'SELECT userdb_emailaddress FROM ' . $config['lang_table_prefix'] . 'userdb WHERE userdb_id=' . $user;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $email = $recordSet->fields('userdb_emailaddress');
        $vcard->add('EMAIL', $email);
        $sql = 'SELECT userdbelements_field_name,userdbelements_field_value
                    FROM ' . $config['lang_table_prefix'] . 'userdbelements
                    WHERE userdb_id=' . $user;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $address = '';
        $city = '';
        $state = '';
        $zip = '';
        $country = '';

        while (!$recordSet->EOF) {
            if ($recordSet->fields('userdbelements_field_name') == $config['vcard_phone']) {
                $phone = $recordSet->fields('userdbelements_field_value');
                $vcard->add('TEL', $phone, ['type' => 'WORK;VOICE']);
            } elseif ($recordSet->fields('userdbelements_field_name') == $config['vcard_fax']) {
                $fax = $recordSet->fields('userdbelements_field_value');
                $vcard->add('TEL', $fax, ['type' => 'WORK;FAX']);
            } elseif ($recordSet->fields('userdbelements_field_name') == $config['vcard_mobile']) {
                $mobile = $recordSet->fields('userdbelements_field_value');
                $vcard->add('TEL', $mobile, ['type' => 'WORK;CELL']);
            } elseif ($recordSet->fields('userdbelements_field_name') == $config['vcard_notes']) {
                $notes = $recordSet->fields('userdbelements_field_value');
                $vcard->add('NOTE', $notes);
            } elseif ($recordSet->fields('userdbelements_field_name') == $config['vcard_url']) {
                $url = $recordSet->fields('userdbelements_field_value');
                $vcard->add('URL', $url, ['type' => 'WORK']);
            } elseif ($recordSet->fields('userdbelements_field_name') == $config['vcard_address']) {
                $address = $recordSet->fields('userdbelements_field_value');
            } elseif ($recordSet->fields('userdbelements_field_name') == $config['vcard_city']) {
                $city = $recordSet->fields('userdbelements_field_value');
            } elseif ($recordSet->fields('userdbelements_field_name') == $config['vcard_state']) {
                $state = $recordSet->fields('userdbelements_field_value');
            } elseif ($recordSet->fields('userdbelements_field_name') == $config['vcard_zip']) {
                $zip = $recordSet->fields('userdbelements_field_value');
            } elseif ($recordSet->fields('userdbelements_field_name') == $config['vcard_country']) {
                $country = $recordSet->fields('userdbelements_field_value');
            }
            $recordSet->MoveNext();
        }
        if ($address != '' || $city != '' || $state != '' || $zip != '' || $country != '') {
            $addr_string = implode(";", array_filter([$address, $city, $state, $zip, $country]));
            $vcard->add('ADR', ['', '', $address, $city, $state, $zip, $country], ['type' => 'WORK']);
        }

        $output = $vcard->serialize();
        //echo $output;
        $filename = $last . "_" . $first . ".vcf";
        Header('Content-Disposition: attachment; filename=' . $filename);
        Header('Content-Length: ' . strlen($output) . '');
        Header('Connection: close');
        Header('Content-Type: text/x-vCard; name=' . $filename);
    }

    /**
     * user listings link
     *
     * @param  integer $userdb_id
     * @return string $display
     */
    public function userListingsLink($userdb_id)
    {
        global $lang, $config;
        $display = '';
        $display .= '<a href="' . $config['baseurl'] . '/index.php?action=searchresults&amp;user_ID=' . intval($userdb_id) . '">' . $lang['user_listings_link_text'] . '</a>';
        return $display;
    }

    /**
     * QR Code link
     *
     * @param  integer $userdb_id
     * @return string $display
     */
    public function qr_code_link($userdb_id)
    {
        global $config;
        $user = intval($userdb_id);
        $display = $config['baseurl'] . '/index.php?action=userqrcode&amp;user_id=' . $userdb_id;
        return $display;
    }

    /**
     * QR Code
     *
     * @param  integer $userdb_id
     * @return
     */
    public function qr_code($userdb_id)
    {
        global $config;
        $user = intval($userdb_id);
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $writer = new PngWriter();
        $userURL = $page->magicURIGenerator('agent', $userdb_id, true);
        $qrCode = QrCode::create($userURL)
            ->setEncoding(new Encoding('UTF-8'));
        $result = $writer->write($qrCode);
        header('Content-Type: ' . $result->getMimeType());
        echo $result->getString();
    }


    /**
     * Get user reg info
     *
     * @param  integer $userdb_id
     * @return array $reg_info
     * @keys userdb_user_name, userdb_user_name, userdb_user_last_name, userdb_emailaddress
     */
    public function get_user_reg_info($userdb_id)
    {
        // grabs the main info for a given user
        global $conn, $config, $misc;

        $user = intval($userdb_id);
        $sql = 'SELECT userdb_user_name, userdb_user_first_name, userdb_user_last_name, userdb_emailaddress
				FROM ' . $config['table_prefix'] . 'userdb
				WHERE (userdb_id = ' . $user . ')';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }

        $reg_info = [];
        // get main listings data
        $reg_info['user_name'] = $recordSet->fields('userdb_user_name');
        $reg_info['first_name'] = $recordSet->fields('userdb_user_first_name');
        $reg_info['last_name'] = $recordSet->fields('userdb_user_last_name');
        $reg_info['emailaddress'] = $recordSet->fields('userdb_emailaddress');

        return $reg_info;
    }


    /******************* DEPRECATED BELOW *************************************/

    /**
     * Get user name (deprecated)
     *
     * @param  integer $userdb_id
     * @return string $display
     */
    public function get_user_name($userdb_id)
    {
        // grabs the main info for a given user
        global $conn, $config, $misc;
        $display = '';

        $user = intval($userdb_id);
        $sql = 'SELECT userdb_user_name
				FROM ' . $config['table_prefix'] . 'userdb
				WHERE (userdb_id = ' . $userdb_id . ')';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        // get main listings data
        $name = '';
        while (!$recordSet->EOF) {
            $name = $recordSet->fields('userdb_user_name');
            $recordSet->MoveNext();
        } // end while
        $display .= $name;
        return $display;
    }

    /**
     * Get user email (deprecated)
     *
     * @param  integer $userdb_id
     * @return string $email
     */
    public function get_user_email($userdb_id)
    {
        // grabs the main info for a given user
        global $conn, $config, $misc, $api;

        $email = '';
        $user = intval($userdb_id);

        $is_agent = $misc->get_agent_status($userdb_id);
        $is_admin = $misc->get_admin_status($userdb_id);

        if ($is_agent === true || $is_admin === true) {
            $resource = 'agent';
        } else {
            $resource = 'member';
        }

        $result_data = $api->load_local_api('user__read', [
            'user_id' => $userdb_id,
            'resource' => $resource,
            'fields' => [
                'userdb_emailaddress',
            ],
        ]);
        if (!$result_data['error']) {
            $email .= $result_data['user']['userdb_emailaddress'];
        }

        return $email;
    }

    /**
     * Get user first name (deprecated)
     *
     * @param  integer $userdb_id
     * @return string $display
     */
    public function get_user_first_name($userdb_id)
    {
        global $conn, $config, $misc, $api;

        $display = '';
        $user = intval($userdb_id);

        $is_agent = $misc->get_agent_status($userdb_id);
        $is_admin = $misc->get_admin_status($userdb_id);

        if ($is_agent === true || $is_admin === true) {
            $resource = 'agent';
        } else {
            $resource = 'member';
        }

        $result_data = $api->load_local_api('user__read', [
            'user_id' => $userdb_id,
            'resource' => $resource,
            'fields' => [
                'userdb_user_first_name',
            ],
        ]);
        if (!$result_data['error']) {
            $display .= $result_data['user']['userdb_user_first_name'];
        }

        return $display;
    }

    /**
     * Get user last name (deprecated)
     *
     * @param  integer $userdb_id
     * @return string $display
     */
    public function get_user_last_name($userdb_id)
    {
        // grabs the main info for a given user
        global $conn, $config, $misc, $api;

        $display = '';
        $user = intval($userdb_id);
        $is_agent = $misc->get_agent_status($userdb_id);
        $is_admin = $misc->get_admin_status($userdb_id);

        if ($is_agent === true || $is_admin === true) {
            $resource = 'agent';
        } else {
            $resource = 'member';
        }

        $result_data = $api->load_local_api('user__read', [
            'user_id' => $userdb_id,
            'resource' => $resource,
            'fields' => [
                'userdb_user_last_name',
            ],
        ]);
        if (!$result_data['error']) {
            $display .= $result_data['user']['userdb_user_last_name'];
        }

        return $display;
    }
}
