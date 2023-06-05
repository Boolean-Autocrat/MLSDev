<?php

global $config;
class search_page
{
    public function browse_all_listings_pclass_link()
    {
        global $conn, $config, $lang, $api, $misc;

        if (!isset($_GET['pclass']) || $_GET['pclass'][0] == '') {
            return $this->browse_all_listings_link();
        }
        $class_sql = '';
        $class_url = '';
        foreach ($_GET['pclass'] as $x => $y) {
            $_GET['pclass'][$x] = intval($y);
        }
        foreach ($_GET['pclass'] as $class) {
            $class_url .= '&amp;pclass%5B%5D=' . $class;
        }
        $class_sql = implode(',', $_GET['pclass']);
        $url = '<a href="' . $config['baseurl'] . '/index.php?action=searchresults' . $class_url . '">' . $lang['browse_all_listings_in_pclass'];
        if ($config['configured_show_count'] == 1) {
            $result = $api->load_local_api('listing__search', ['parameters' => $_GET, 'limit' => 0, 'offset' => 0, 'count_only' => 1]);

            $num_listings = $result['listing_count'];
            $display = $url . ' (' . $num_listings . ')</a>';
        } else {
            $display = $url . '</a>';
        }
        return $display;
    }
    public function browse_all_listings_link()
    {
        global $conn, $config, $lang, $api, $misc;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $url = $page->magicURIGenerator('searchresults', null, true);
        $url = '<a href="' . $url . '">' . $lang['browse_all_listings'];

        if ($config['configured_show_count'] == 1) {
            $result = $api->load_local_api('listing__search', ['parameters' => $_GET, 'limit' => 0, 'offset' => 0, 'count_only' => 1]);
            $num_listings = $result['listing_count'];
            $display = $url . ' (' . $num_listings . ')</a>';
        } else {
            $display = $url . '</a>';
        }
        return $display;
    } // end function browse_all_listings
    public function create_search_page_logic()
    {
        global $conn, $config, $api;

        // First find out how many property classes exist.
        $result = $api->load_local_api('pclass__metadata', []);
        if ($result['error']) {
            $api->load_local_api('log__log_create_entry', [
                'log_type' => 'CRIT',
                'log_api_command' => 'api->pclass->metadata',
                'log_message' => 'Error: No property classes found',
            ]);
        } elseif ($result['metadata'] > 1) {
            // Multiple Classes Exist show new search page.
            return $this->create_class_searchpage();
        } else {
            return $this->create_searchpage();
        }
    }
    public function create_class_searchpage()
    {
        global $config, $conn, $jscript, $api;

        $result = $api->load_local_api('pclass__metadata', []);
        if ($result['error']) {
            $api->load_local_api('log__log_create_entry', [
                'log_type' => 'CRIT',
                'log_api_command' => 'api->pclass->metadata',
                'log_message' => 'Error: No property classes found',
            ]);
        } else {
            $class_count = count($result['metadata']);
            $class_checkbox = '';
            $x = 1;
            $keys = array_keys($result['metadata']);

            if (is_array($keys)) {
                foreach ($keys as $class_id) {
                    $class_name = $result['metadata'][$class_id]['name'];
                    $class_checkbox .= '<input name="pclass[]" value="' . $class_id . '" type="checkbox" id="class' . $x . '" onclick="SearchClassUnCheckALL()" />
										<label>' . $class_name . '</label>
										<br />';
                    $x++;
                }
            }
        }

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $page->load_page($config['template_path'] . '/search_class_default.html');
        $page->page = str_replace('{property_class_checkboxes}', $class_checkbox, $page->page);
        // Set the JS
        $jscript .= '<script type="text/javascript">' . "\r\n";
        $jscript .= 'function SearchClassCheckALL() {
			for (var j = 1; j <= ' . $class_count . '; j++) {
				box = eval("document.getElementById(\'class\'+j)");
				if (document.getElementById("class0").checked == true) {
					if (box.checked == true) box.checked = false;
				}
			}
		}';
        $jscript .= 'function SearchClassUnCheckALL() {
			if (document.getElementById("class0").checked == true) {
				 document.getElementById("class0").checked = false;
			}
		}';
        $jscript .= "</script>\r\n";
        return $page->page;
    }
    public function create_searchpage($template_tag = false, $no_results = false)
    {
        global $config, $conn, $jscript, $api;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user;
        // Determine if we are searching for a particular property class
        $class_sql = '';
        if (!isset($_GET['pclass']) || !is_array($_GET['pclass'])) {
            $class_array = [];
        } else {
            $class_array = array_unique($_GET['pclass']);
        }
        //echo '<pre>'.print_r($class_array,TRUE).'</pre>';
        $result = $api->load_local_api('fields__metadata', ['resource' => 'listing', 'searchable_only' => true, 'class' => $class_array]);
        //echo '<pre>'.print_r($result,TRUE).'</pre>';
        $fielddata_ouput = '';
        if ($result['error'] !== true) {
            foreach ($result['fields'] as $field_id => $field_array) {
                $fielddata_ouput .= $this->searchbox_render($field_array, $class_array);
            }
        }

        if (isset($_GET['pclass'][0]) && !isset($_GET['pclass'][1])) {
            $class = $_GET['pclass'][0];
            if (file_exists($config['template_path'] . '/search_page_class_' . $class . '.html')) {
                $page->load_page($config['template_path'] . '/search_page_class_' . $class . '.html');
            } else {
                $page->load_page($config['template_path'] . '/search_page_default.html');
            }
        } else {
            $page->load_page($config['template_path'] . '/search_page_default.html');
        }
        if ($template_tag == true) {
            $page->page = $page->get_template_section('templated_search_form_block');
        } else {
            $page->page = $page->cleanup_template_block('templated_search_form', $page->page);
        }
        $class_inputs = '';
        if (isset($_GET['pclass']) && is_array($_GET['pclass'])) {
            foreach ($_GET['pclass'] as $class) {
                $class_inputs .= '<input type="hidden" name="pclass[]" value="' . $class . '" />';
            }
        }
        if ($class_inputs == '') {
            $class_inputs .= '<input type="hidden" name="pclass[]" value="" />';
        }

        $page->page = str_replace('{search_type}', $class_inputs, $page->page);
        $page->replace_tags(['featured_listings_horizontal', 'featured_listings_vertical']);

        $page->page = $page->parse_template_section($page->page, 'browse_all_listings', $this->browse_all_listings_link());
        $page->page = $page->parse_template_section($page->page, 'browse_all_listings_pclass', $this->browse_all_listings_pclass_link());

        $page->page = $page->parse_template_section($page->page, 'search_fields', $fielddata_ouput);
        $page->page = $page->parse_template_section($page->page, 'agent_searchbox', $this->searchbox_agentdropdown());
        $page->page = $page->parse_template_section($page->page, 'searchbox_created_in_last_days', $this->searchbox_created_in_last_days());

        $page->page = $page->parse_template_section($page->page, 'lat_long_dist_search', $this->searchbox_latlongdist());
        $page->page = $page->parse_template_section($page->page, 'postalcode_dist_search', $this->searchbox_postaldist());
        $page->page = $page->parse_template_section($page->page, 'city_dist_search', $this->searchbox_citydist());
        $page->replace_search_field_tags();

        $ImagesOnlySet = '';
        if (isset($_GET['imagesOnly']) && $_GET['imagesOnly'] == 'yes') {
            $ImagesOnlySet = 'checked="checked"';
        }
        $page->page = $page->parse_template_section($page->page, 'show_only_with_images', '<input type="checkbox" name="imagesOnly" ' . $ImagesOnlySet . ' value="yes" />');
        $VtourOnlySet = '';
        if (isset($_GET['vtoursOnly']) && $_GET['vtoursOnly'] == 'yes') {
            $VtourOnlySet = 'checked="checked"';
        }
        $page->page = $page->parse_template_section($page->page, 'show_only_with_vtours', '<input type="checkbox" name="vtoursOnly" ' . $VtourOnlySet . ' value="yes" />');
        if (isset($_GET['searchtext']) && $_GET['searchtext'] != '') {
            $page->page = $page->parse_template_section($page->page, 'full_text_search', '<input type="text" name="searchtext" value="' . htmlentities($_GET['searchtext'], ENT_COMPAT, $config['charset']) . '" />');
        } else {
            $page->page = $page->parse_template_section($page->page, 'full_text_search', '<input type="text" name="searchtext" />');
        }

        if ($no_results == false) {
            $page->replace_template_section('no_search_results_block', '');
            $page->page = $page->cleanup_template_block('!no_search_results', $page->page);
        } else {
            @header('HTTP/1.1 404 Not Found');
            @header('Status: 404 Not Found');
            $page->page = $page->cleanup_template_block('no_search_results', $page->page);
            $page->page = $page->remove_template_block('!no_search_results', $page->page);
            // Generate a Saved search link
            $guidestring_no_action = '';
            foreach ($_GET as $k => $v) {
                if ($v != '' && $k != 'cur_page' && $k != 'PHPSESSID' && $k != 'action' && $k != 'printer_friendly' && $k != 'template') {
                    if (is_array($v)) {
                        foreach ($v as $vitem) {
                            $guidestring_no_action .= '&amp;' . urlencode($k) . '[]=' . urlencode($vitem);
                        }
                    } else {
                        $guidestring_no_action .= '&amp;' . urlencode($k) . '=' . urlencode($v);
                    }
                }
            }
            $save_search_link = 'index.php?action=save_search' . $guidestring_no_action;
            $page->page = $page->parse_template_section($page->page, 'save_search_link', $save_search_link);
        }
        return $page->page;
    } //End Function create_searchpage

    /**
     * **************************************************************************\
     * Open-Realty - search_results Function                                     *
     * --------------------------------------------                              *
     *   This is the search_results function.                                    *
     * also now a function called search_results_old                             *
     * \**************************************************************************
     */
    public function search_results($return_ids_only = false)
    {
        $DEBUG_SQL = false;
        global $config, $conn, $lang, $misc, $current_ID, $db_type, $api;

        include_once $config['basepath'] . '/include/core.inc.php';
        include_once $config['basepath'] . '/include/listing.inc.php';
        $page = new page();
        $listing_pages = new listing_pages();

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        // Load any addons
        $addons = $page->load_addons();
        $guidestring = '';
        $guidestring_with_sort = '';

        foreach ($_GET as $k => $v) {
            if ($v != '' && $k != 'listingID' && $k != 'cur_page' && $k != 'action' && $k != 'PHPSESSID' && $k != 'sortby' && $k != 'sorttype' && $k != 'printer_friendly' && $k != 'template' && $k != 'x' && $k != 'y') {
                if (is_array($v)) {
                    foreach ($v as $vitem) {
                        $guidestring .= '&amp;' . urlencode($k) . '[]=' . urlencode($vitem);
                    }
                } else {
                    $guidestring .= '&amp;' . urlencode($k) . '=' . urlencode($v);
                }
            }
            if ($k == 'sortby') {
                $guidestring_with_sort = '$k=$v';
            } elseif ($k == 'sorttype') {
                $guidestring_with_sort = '$k=$v&amp;';
            }
        }
        $display = '';

        //Do Search to get total record count, no need pass in soring information
        $result = $api->load_local_api('listing__search', ['parameters' => $_GET, 'limit' => 0, 'offset' => 0, 'count_only' => 1]);
        //API_DEBUG
        //echo '<pre>'.print_r($result['info'],TRUE)."</pre>\r\n";
        //See if we have sorting information and if not set NULL variables.
        if (!isset($_GET['sortby'])) {
            $_GET['sortby'] = [];
        } elseif (!is_array($_GET['sortby'])) {
            $_GET['sortby'] = [$_GET['sortby']];
        }
        if (!isset($_GET['sorttype'])) {
            $_GET['sorttype'] = [];
        } elseif (!is_array($_GET['sorttype'])) {
            $_GET['sorttype'] = [$_GET['sorttype']];
        }
        $sortby_array = [];
        $sorttype_array = [];
        //Deal with System Defined Sorting (Site Config)
        if ($config['special_sortby'] != 'none') {
            $sortby_array[] = $config['special_sortby'];
            $sorttype_array[] = $config['special_sorttype'];
        }

        if (!isset($_GET['sortby']) || empty($_GET['sortby'])) {
            $sortby_array[] = $config['sortby'];
        } else {
            $sortby_array = array_merge($sortby_array, $_GET['sortby']);
        }
        if (!isset($_GET['sorttype']) || empty($_GET['sorttype'])) {
            $sorttype_array[] = $config['sorttype'];
        } else {
            $sorttype_array = array_merge($sorttype_array, $_GET['sorttype']);
        }
        // Load the templste
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        if (isset($_GET['pclass']) && count($_GET['pclass']) == 1 && file_exists($config['template_path'] . '/search_results_class_' . $_GET['pclass'][0] . '.html')) {
            $page->load_page($config['template_path'] . '/search_results_class_' . $_GET['pclass'][0] . '.html');
        } else {
            $page->load_page($config['template_path'] . '/' . $config['search_result_template']);
        }
        // Get header section
        $header_section = $page->get_template_section('search_result_header');
        $search_result = '';
        // Ok we have the header section now get the result section
        $search_result_section = $page->get_template_section('search_result_dataset');

        // Get the number of rows(records) we have.
        $num_rows =  $result['listing_count'];
        if ($return_ids_only === true) {
            $result = $api->load_local_api('listing__search', ['parameters' => $_GET, 'limit' => 0, 'offset' => 0, 'sortby' => $sortby_array, 'sorttype' => $sorttype_array]);
            return $result['listings'];
        } elseif ($return_ids_only === 'perpage') {
            if (!isset($_GET['cur_page'])) {
                $_GET['cur_page'] = 0;
            }
            $num_records = $config['listings_per_page'];
            $limit_str = intval($_GET['cur_page']) * $config['listings_per_page'];
            $result = $api->load_local_api('listing__search', ['parameters' => $_GET, 'limit' => $num_records, 'offset' => $limit_str, 'sortby' => $sortby_array, 'sorttype' => $sorttype_array]);
            return $result['listings'];
        } else {
            if ($num_rows > 0) {
                $guidestring_no_action = '';
                foreach ($_GET as $k => $v) {
                    if ($v != '' && $k != 'cur_page' && $k != 'PHPSESSID' && $k != 'action' && $k != 'printer_friendly' && $k != 'template') {
                        if (is_array($v)) {
                            foreach ($v as $vitem) {
                                $guidestring_no_action .= '&amp;' . urlencode($k) . '[]=' . urlencode($vitem);
                            }
                        } else {
                            $guidestring_no_action .= '&amp;' . urlencode($k) . '=' . urlencode($v);
                        }
                    }
                }
                if (!isset($_GET['cur_page'])) {
                    $_GET['cur_page'] = 0;
                }
                // build the string to select a certain number of listings per page
                $limit_str = intval($_GET['cur_page']) * $config['listings_per_page'];
                $num_records = $config['listings_per_page'];
                $some_num = intval($_GET['cur_page']) + 1;
                $this_page_max = $some_num * $config['listings_per_page'];
                // Check if we're setting a maximum number of search results
                if ($config['max_search_results'] > 0) {
                    // Check if we've reached the max number of listings setting.
                    if ($this_page_max > $config['max_search_results']) {
                        $num_records = $this_page_max - $config['max_search_results'];
                    }
                    // Failsafe check in case the max search results was set lower than the listings per page setting.
                    if ($config['max_search_results'] < $config['listings_per_page']) {
                        $num_records = $config['max_search_results'];
                    }
                    // Adjust the $num_rows for the next_prev function to show at the max the max results setting
                    if ($num_rows > $config['max_search_results']) {
                        $num_rows = $config['max_search_results'];
                    }
                }
                if ($config['show_next_prev_listing_page'] == 1) {
                    $lnp_limit = 0;
                    if ($config['max_search_results'] > 0) {
                        $lnp_limit = $config['max_search_results'];
                    }
                    $result = $api->load_local_api('listing__search', ['parameters' => $_GET, 'limit' => $lnp_limit, 'offset' => 0, 'sortby' => $sortby_array, 'sorttype' => $sorttype_array]);
                    $newurl = '';
                    foreach ($_GET as $k => $v) {
                        if ($v && $k != 'cur_page' && $k != 'PHPSESSID' && $k != 'action') {
                            if (is_array($v)) {
                                foreach ($v as $vitem) {
                                    $newurl .= '&amp;' . urlencode($k) . '[]=' . urlencode($vitem);
                                }
                            } else {
                                $newurl .= '&amp;' . urlencode($k) . '=' . urlencode($v);
                            }
                        }
                    }

                    $_SESSION['results'] = $result['listings'];
                    //$_SESSION['titles'] = array();
                    unset($result);
                    $_SESSION['cur_page'] = intval($_GET['cur_page']);
                    $_SESSION['searchstring'] = $newurl;
                    $_SESSION['count'] = $num_rows;
                    // ************added for next prev navigation***********
                }
                // Store the next_prev code as a variable to place in the template
                $next_prev = $misc->next_prev($num_rows, intval($_GET['cur_page']), $guidestring_with_sort);
                $next_prev_bottom = $misc->next_prev($num_rows, intval($_GET['cur_page']), $guidestring_with_sort, 'bottom');

                $result = $api->load_local_api('listing__search', ['parameters' => $_GET, 'limit' => $num_records, 'offset' => $limit_str, 'sortby' => $sortby_array, 'sorttype' => $sorttype_array]);
                //API_DEBUG
                //echo '<pre>'.print_r($result,TRUE)."</pre>\r\n";
                $sresults = $result['listings'];

                //$sresults = array_slice($result['listings'],$limit_str,$num_records);
                if (isset($_GET['pclass']) && is_array($_GET['pclass'])) {
                    $pclass_string = implode(',', $_GET['pclass']);
                    if ($pclass_string != '') {
                        $pclass_where = ' AND class_id IN (' . implode(',', $_GET['pclass']) . ') AND '
                            . $config['table_prefix'] . 'listingsformelements.listingsformelements_id = ' . $config['table_prefix_no_lang'] . 'classformelements.listingsformelements_id ';
                        $pclass_from = ', ' . $config['table_prefix_no_lang'] . 'classformelements';
                    } else {
                        $pclass_where = '';
                        $pclass_from = '';
                    }
                } else {
                    $pclass_where = '';
                    $pclass_from = '';
                }
                // Get the the fields marked as browseable.
                $sql = 'SELECT ' . $config['table_prefix'] . 'listingsformelements.listingsformelements_id, listingsformelements_field_caption, listingsformelements_field_name,
						listingsformelements_display_priv, listingsformelements_search_result_rank
						FROM ' . $config['table_prefix'] . 'listingsformelements' . $pclass_from . '
						WHERE (listingsformelements_display_on_browse = \'Yes\')
						AND (listingsformelements_field_type <> \'textarea\')
						' . $pclass_where . '
						ORDER BY listingsformelements_search_result_rank';
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    echo $sql;
                }
                $num_columns = $recordSet->RecordCount();
                // Get header_title
                $field_caption = $lang['title'];
                $field_name = 'listingsdb_title';
                $sorttypestring = '';
                $sort_type_count = 0;
                $sortby = '';
                $reverse_sort = 'ASC';

                $sortby_array = $result['sortby'];
                $sorttype_array = $result['sorttype'];
                //End of section to be replaced by API output
                foreach ($sortby_array as $sortby) {
                    if ($sortby == $field_name) {
                        if (!isset($sorttype_array[$sort_type_count]) || $sorttype_array[$sort_type_count] == 'DESC') {
                            $reverse_sort = 'ASC';
                        } else {
                            $reverse_sort = 'DESC';
                        }
                        $sorttypestring = 'sorttype=' . $reverse_sort;
                    }
                    $sort_type_count++;
                }
                if ($sorttypestring == '') {
                    $sorttypestring = 'sorttype=ASC';
                }
                // This is header_title it is the lang variable for title
                $header_title = '<a class="sort_' . $reverse_sort . '" href="index.php?action=searchresults&amp;sortby=' . $field_name . '&amp;' . $sorttypestring . $guidestring . '"rel="nofollow">' . $field_caption . '</a>';
                $header_title_no_sort = $field_caption;

                // Get header_title
                $field_caption = $lang['header_pclass'];
                $field_name = 'pclass';
                $sorttypestring = '';
                $sort_type_count = 0;
                $reverse_sort = 'ASC';
                foreach ($sortby_array as $sortby) {
                    if ($sortby == $field_name) {
                        if (!isset($sorttype_array[$sort_type_count]) || $sorttype_array[$sort_type_count] == 'DESC') {
                            $reverse_sort = 'ASC';
                        } else {
                            $reverse_sort = 'DESC';
                        }
                        $sorttypestring = 'sorttype=' . $reverse_sort;
                    }
                    $sort_type_count++;
                }
                if ($sorttypestring == '') {
                    $sorttypestring = 'sorttype=ASC';
                }
                // This is header_title it is the lang variable for title
                $header_pclass = '<a class="sort_' . $reverse_sort . '" href="index.php?action=searchresults&amp;sortby=' . $field_name . '&amp;' . $sorttypestring . $guidestring . '" rel="nofollow">' . $field_caption . '</a>';
                $header_pclass_no_sort = $field_caption;

                $field = [];
                $field_no_sort = [];
                while (!$recordSet->EOF) {
                    $x = $recordSet->fields('listingsformelements_search_result_rank');
                    // Check for Translations if needed
                    if (!isset($_SESSION['users_lang'])) {
                        $field_caption = $recordSet->fields('listingsformelements_field_caption');
                    } else {
                        $listingsformelements_id = $recordSet->fields('listingsformelements_id');
                        $lang_sql = 'SELECT listingsformelements_field_caption
									FROM ' . $config['lang_table_prefix'] . 'listingsformelements
									WHERE listingsformelements_id = ' . $listingsformelements_id;
                        $lang_recordSet = $conn->Execute($lang_sql);
                        if (!$lang_recordSet) {
                            $misc->log_error($lang_sql);
                        }
                        if ($DEBUG_SQL) {
                            echo '<strong>ML: Field Caption SQL:</strong> ' . $lang_sql . '<br />';
                        }
                        $field_caption = $lang_recordSet->fields('listingsformelements_field_caption');
                    }

                    $field_name = $recordSet->fields('listingsformelements_field_name');
                    $display_priv = $recordSet->fields('listingsformelements_display_priv');
                    $display_status = false;
                    if ($display_priv == 1) {
                        $display_status = $login->verify_priv('Member');
                    } elseif ($display_priv == 2) {
                        $display_status = $login->verify_priv('Agent');
                    } elseif ($display_priv == 3) {
                        $display_status = $login->verify_priv('Admin');
                    } else {
                        $display_status = true;
                    }
                    if ($display_status === true) {
                        $sorttypestring = '';
                        $sort_type_count = 0;
                        $reverse_sort = 'ASC';
                        foreach ($sortby_array as $sortby) {
                            if ($sortby == $field_name) {
                                if (!isset($sorttype_array[$sort_type_count]) || $sorttype_array[$sort_type_count] == 'DESC') {
                                    $reverse_sort = 'ASC';
                                } else {
                                    $reverse_sort = 'DESC';
                                }
                                $sorttypestring = 'sorttype=' . $reverse_sort;
                            }
                            $sort_type_count++;
                        }
                        if ($sorttypestring == '') {
                            $sorttypestring = 'sorttype=ASC';
                        }
                        $field[$x] = '<a class="sort_' . $reverse_sort . '" href="index.php?action=searchresults&amp;sortby=' . $field_name . '&amp;' . $sorttypestring . $guidestring . '" rel="nofollow">' . $field_caption . '</a>';
                        $field_no_sort[$x] = $field_caption;
                    }
                    $recordSet->MoveNext();
                } // end while
                // We have all the header information so we can now parse that section
                $header_section = $page->parse_template_section($header_section, 'header_title', $header_title);
                $header_section = $page->parse_template_section($header_section, 'header_title_no_sort', $header_title_no_sort);
                $header_section = $page->parse_template_section($header_section, 'header_pclass', $header_pclass);
                $header_section = $page->parse_template_section($header_section, 'header_pclass_no_sort', $header_pclass_no_sort);
                foreach ($field as $x => $f) {
                    $header_section = $page->parse_template_section($header_section, 'header_' . $x, $f);
                }
                foreach ($field_no_sort as $x => $f) {
                    $header_section = $page->parse_template_section($header_section, 'header_' . $x . '_no_sort', $f);
                }
                // We have the title now we need the image
                $num_columns = $num_columns + 1; // add one for the image
                $count = 0;
                foreach ($sresults as $current_ID) {
                    // Start a new section for each listing.
                    $search_result .= $search_result_section;
                    // alternate the colors
                    if ($count == 0) {
                        $count = $count + 1;
                    } else {
                        $count = 0;
                    }
                    $url = $page->magicURIGenerator('listing', $current_ID, true);
                    $url = '<a href="' . $url . '">';
                    // Insert the title as we grabbed it earlier
                    $search_result = $page->parse_template_section($search_result, 'listingid', $current_ID);
                    $search_result = $page->replace_listing_field_tags($current_ID, $search_result);
                    //get distance for postal code distance searches
                    if (isset($_GET['postalcode_dist_dist'])) {
                        $sql3 = 'SELECT listingsdbelements_field_value FROM ' . $config['table_prefix'] . 'listingsdbelements 
								WHERE ((listingsdb_id = ' . $current_ID . ') 
								AND (listingsdbelements_field_name = \'' . $config['map_zip'] . '\'))';
                        $recordSet3 = $conn->Execute($sql3);
                        $sql4 = 'SELECT zipdist_latitude, zipdist_longitude 
								FROM ' . $config['table_prefix_no_lang'] . 'zipdist 
								WHERE zipdist_zipcode =' . $recordSet3->fields('listingsdbelements_field_value');
                        $recordSet4 = $conn->Execute($sql4);
                        $postalcode_distance = round($this->calculate_mileage($postalcode_dist_lat, $recordSet4->fields('zipdist_latitude'), $postalcode_dist_long, $recordSet4->fields('zipdist_longitude')), 2) . ' ' . $lang['postalcode_miles_away'];
                        $search_result = $page->parse_template_section($search_result, 'postalcode_search_distance', $postalcode_distance);
                    }
                    // grab the rest of the listing's data
                    $sql2 = 'SELECT listingsdbelements_field_value, listingsformelements_field_type,listingsformelements_field_caption, listingsformelements_display_priv, listingsformelements_search_result_rank
							FROM ' . $config['table_prefix'] . 'listingsdbelements, ' . $config['table_prefix'] . 'listingsformelements
							' . $pclass_from . ' WHERE ((listingsdb_id = ' . $current_ID . ')
							AND (listingsformelements_display_on_browse = \'Yes\')
							AND (listingsdbelements_field_name = listingsformelements_field_name))
							' . $pclass_where . ' ORDER BY listingsformelements_search_result_rank';
                    $recordSet2 = $conn->Execute($sql2);
                    if ($DEBUG_SQL) {
                        echo '<strong>Listing Data:</strong> ' . $sql2 . '<br />';
                    }
                    if (!$recordSet2) {
                        $misc->log_error($sql2);
                    }
                    $field = [];
                    $field_captions = [];
                    $textarea = [];
                    $textarea_captions = [];
                    while (!$recordSet2->EOF) {
                        $field_value = $recordSet2->fields('listingsdbelements_field_value');
                        $field_caption = $recordSet2->fields('listingsformelements_field_caption');
                        $field_type = $recordSet2->fields('listingsformelements_field_type');
                        $display_priv = $recordSet2->fields('listingsformelements_display_priv');
                        $x = $recordSet2->fields('listingsformelements_search_result_rank');
                        $display_status = false;
                        if ($display_priv == 1) {
                            $display_status = $login->verify_priv('Member');
                        } elseif ($display_priv == 2) {
                            $display_status = $login->verify_priv('Agent');
                        } elseif ($display_priv == 3) {
                            $display_status = $login->verify_priv('Admin');
                        } else {
                            $display_status = true;
                        }
                        if ($display_status === true) {
                            switch ($field_type) {
                                case 'textarea':
                                    $textarea_captions[$x] = html_entity_decode($field_caption, ENT_COMPAT, $config['charset']);
                                    if ($config['add_linefeeds'] === '1') {
                                        $textarea[$x] = nl2br($field_value);
                                    } else {
                                        $textarea[$x] = $field_value;
                                    }
                                    break;
                                case 'select-multiple':
                                case 'option':
                                case 'checkbox':
                                    $field_captions[$x] = html_entity_decode($field_caption, ENT_COMPAT, $config['charset']);
                                    // handle field types with multiple options
                                    $feature_index_list = explode('||', $field_value);
                                    $field[$x] = '';
                                    foreach ($feature_index_list as $feature_list_item) {
                                        $field[$x] .= $feature_list_item;
                                        $field[$x] .= $config['feature_list_separator'];
                                    }
                                    break;
                                case 'price':
                                    $field_captions[$x] = html_entity_decode($field_caption, ENT_COMPAT, $config['charset']);
                                    $sql3 = 'SELECT listingsdbelements_field_value
											FROM ' . $config['table_prefix'] . 'listingsdbelements
											WHERE ((listingsdb_id = ' . $current_ID . ')
											AND (listingsdbelements_field_name = \'status\'))';
                                    $recordSet3 = $conn->Execute($sql3);
                                    if (!$recordSet3) {
                                        $misc->log_error($sql3);
                                    }
                                    if ($DEBUG_SQL) {
                                        echo '<strong>Status Lookup for price field:</strong> ' . $sql3 . '<br />';
                                    }
                                    $status = $recordSet3->fields('listingsdbelements_field_value');
                                    $recordSet3->Close();
                                    if ($field_value == '' && $config['zero_price'] == '1') {
                                        $money_amount = $misc->international_num_format($field_value, $config['number_decimals_price_fields']);
                                        if ($status == 'Sold') {
                                            $field[$x] = '<span style="text-decoration: line-through">';
                                            $field[$x] .= '</span><br /><span style="color:red;"><strong>' . $lang['mark_as_sold'] . '</strong></span>';
                                        } elseif ($status == 'Pending') {
                                            $field[$x] .= '<br /><span style="color:green;"><strong>' . $lang['mark_as_pending'] . '</strong></span>';
                                        } else {
                                            $field[$x] = $lang['call_for_price'];
                                        }
                                    } else {
                                        $money_amount = $misc->international_num_format($field_value, $config['number_decimals_price_fields']);
                                        if ($status == 'Sold') {
                                            $field[$x] = '<span style="text-decoration: line-through">';
                                            $field[$x] .= $misc->money_formats($money_amount);
                                            $field[$x] .= '</span><br /><span style="color:red;"><strong>' . $lang['mark_as_sold'] . '</strong></span>';
                                        } elseif ($status == 'Pending') {
                                            $field[$x] = $misc->money_formats($money_amount);
                                            $field[$x] .= '<br /><span style="color:green;"><strong>' . $lang['mark_as_pending'] . '</strong></span>';
                                        } else {
                                            $field[$x] = $misc->money_formats($money_amount);
                                        }
                                    } // end else
                                    break;
                                case 'number':
                                    $field_captions[$x] = html_entity_decode($field_caption, ENT_COMPAT, $config['charset']);
                                    $field[$x] = $misc->international_num_format($field_value, $config['number_decimals_number_fields']);
                                    break;
                                case 'url':
                                    $field_captions[$x] = html_entity_decode($field_caption, ENT_COMPAT, $config['charset']);
                                    $field[$x] = '<a href="' . $field_value . '" target="_blank">' . $field_value . '</a>';
                                    break;
                                case 'email':
                                    $field_captions[$x] = html_entity_decode($field_caption, ENT_COMPAT, $config['charset']);
                                    $field[$x] = '<a href="mailto:' . $field_value . '">' . $field_value . '</a>';
                                    break;
                                case 'date':
                                    $field_captions[$x] = html_entity_decode($field_caption, ENT_COMPAT, $config['charset']);
                                    if ($config['date_format'] == 1) {
                                        $format = 'm/d/Y';
                                    } elseif ($config['date_format'] == 2) {
                                        $format = 'Y/d/m';
                                    } elseif ($config['date_format'] == 3) {
                                        $format = 'd/m/Y';
                                    }
                                    if ($field_value > 0) {
                                        $field_value = date($format, $field_value);
                                    }
                                    $field[$x] = $field_value;
                                    break;
                                default:
                                    $field_captions[$x] = html_entity_decode($field_caption, ENT_COMPAT, $config['charset']);
                                    $field[$x] = $field_value;
                                    break;
                            } // end switch
                        }
                        $recordSet2->MoveNext();
                    } // end while
                    foreach ($field as $x => $f) {
                        if ($f != '') {
                            $search_result = $page->parse_template_section($search_result, 'field_' . $x, $f);
                        } else {
                            $search_result = $page->remove_template_block('field_' . $x, $search_result);
                        }
                    }
                    foreach ($field_captions as $x => $f) {
                        if ($f != '') {
                            $search_result = $page->parse_template_section($search_result, 'field_caption_' . $x, $f);
                        }
                    }
                    //Cleanup Field and Field_captions
                    $search_result = preg_replace('/{field_(\d+)}/', '', $search_result);
                    $search_result = preg_replace('/{field_caption_(\d+)}/', '', $search_result);
                    //Form URLS for TextArea
                    $url = $page->magicURIGenerator('listing', $current_ID, true);

                    $preview = '... <a class="more_info" href="' . $url . '">' . $lang['more_info'] . '</a>';

                    foreach ($textarea as $x => $f) {
                        // Normal Textarea
                        $search_result = $page->parse_template_section($search_result, 'textarea_' . $x, $f);
                        // Short textarea of first number of characters defined in site config with link to the listing
                        $p = substr(strip_tags($f), 0, $config['textarea_short_chars']);
                        $p = substr($p, 0, strrpos($p, ' '));
                        $search_result = $page->parse_template_section($search_result, 'textarea_' . $x . '_short', $p . '' . $preview);
                    }
                    foreach ($textarea_captions as $x => $f) {
                        if ($f != '') {
                            $search_result = $page->parse_template_section($search_result, 'textarea_caption_' . $x, $f);
                        }
                    }
                    //Cleanup Textareas
                    $search_result = preg_replace('/{textarea_(.*?)_short}/', '', $search_result);
                    $search_result = preg_replace('/{textarea_caption_(\d+)}/', '', $search_result);
                    $search_result = preg_replace('/{textarea_(.*?)}/', '', $search_result);
                    // Show Vtour indicator Image if vtour exists
                    include_once $config['basepath'] . '/include/media.inc.php';
                    $vtour_handler = new vtour_handler();
                    $vtour_link = $vtour_handler->rendervtourlink($current_ID, true);
                    $search_result = $page->parse_template_section($search_result, 'vtour_button', $vtour_link);
                    // Show Creation Date
                    $get_creation_date = $listing_pages->get_creation_date($current_ID);
                    $search_result = $page->parse_template_section($search_result, 'get_creation_date', $get_creation_date);

                    // Show Featured

                    $get_featured = $listing_pages->get_featured($current_ID, 'no');
                    $search_result = $page->parse_template_section($search_result, 'get_featured', $get_featured);
                    // Show Featured Raw

                    $get_featured_raw = $listing_pages->get_featured($current_ID, 'yes');
                    $search_result = $page->parse_template_section($search_result, 'get_featured_raw', $get_featured_raw);
                    // Show Modified Date

                    $get_modified_date = $listing_pages->get_modified_date($current_ID);
                    $search_result = $page->parse_template_section($search_result, 'get_modified_date', $get_modified_date);
                    // Start {isfavorite} search result template section tag
                    if (isset($_SESSION['userID'])) {
                        $userID = $misc->make_db_safe($_SESSION['userID']);
                        $sql1 = 'SELECT listingsdb_id
								FROM ' . $config['table_prefix'] . 'userfavoritelistings
								WHERE ((listingsdb_id = ' . $current_ID . ')
								AND (userdb_id= ' . $userID . '))';
                        $recordSet1 = $conn->Execute($sql1);
                        if ($recordSet1 === false) {
                            $misc->log_error($sql1);
                        }
                        $favorite_listingsdb_id = $recordSet1->fields('listingsdb_id');
                        if ($favorite_listingsdb_id !== $current_ID) {
                            $isfavorite = 'no';
                            $search_result = $page->parse_template_section($search_result, 'isfavorite', $isfavorite);
                        } else {
                            $isfavorite = 'yes';
                            $search_result = $page->parse_template_section($search_result, 'isfavorite', $isfavorite);
                        }
                    }
                    // End {isfavorite} search result template section tag
                    // Show Delete From Favorites Link if needed
                    $delete_from_fav = '';
                    if (isset($_SESSION['userID'])) {
                        $userID = $misc->make_db_safe($_SESSION['userID']);
                        $sql = 'SELECT listingsdb_id FROM ' . $config['table_prefix'] . 'userfavoritelistings
								WHERE ((listingsdb_id = ' . $current_ID . ')
								AND (userdb_id=' . $userID . '))';
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                        if ($DEBUG_SQL) {
                            echo '<strong>Delete Favorite Lookup:</strong> ' . $sql . '<br />';
                        }
                        $num_rows = $recordSet->RecordCount();
                        if ($num_rows > 0) {
                            $delete_from_fav = '<a href="index.php?action=delete_favorites&amp;listingID=' . $current_ID . '" onclick="return confirmDelete()">' . $lang['delete_from_favorites'] . '</a>';
                        }
                    }
                    // Instert link into section
                    $search_result = $page->parse_template_section($search_result, 'delete_from_favorite', $delete_from_fav);
                    //Show Add To Favorites
                    $link_add_favorites = '';
                    if (isset($_SESSION['userID'])) {
                        $userID = $misc->make_db_safe($_SESSION['userID']);
                        $sql = 'SELECT listingsdb_id
								FROM ' . $config['table_prefix'] . 'userfavoritelistings
								WHERE ((listingsdb_id = ' . $current_ID . ') AND (userdb_id= ' . $userID . '))';
                        $recordSet = $conn->Execute($sql);
                        if (!$recordSet) {
                            $misc->log_error($sql);
                        }
                        if ($DEBUG_SQL) {
                            echo '<strong>Add Favorite Lookup:</strong> ' . $sql . '<br />';
                        }
                        $num_rows = $recordSet->RecordCount();
                        if ($num_rows == 0) {
                            $link_add_favorites = $listing_pages->create_add_favorite_link();
                        }
                    } else {
                        $link_add_favorites = $listing_pages->create_add_favorite_link();
                    }
                    // Instert link into section
                    $search_result = $page->parse_template_section($search_result, 'link_add_favorites', $link_add_favorites);
                    //Add Link to Favorites
                    $link_add_favorites_url = $listing_pages->create_add_favorite_link('yes');
                    $search_result = $page->parse_template_section($search_result, 'link_add_favorites_url', $link_add_favorites_url);

                    // Insert row number
                    $search_result = $page->parse_template_section($search_result, 'row_num_even_odd', $count);
                    //$resultRecordSet->MoveNext();
                    // Replace Edit Listing links

                    $edit_link = $listing_pages->edit_listing_link();
                    $search_result = $page->parse_template_section($search_result, 'link_edit_listing', $edit_link);
                    $edit_link = $listing_pages->edit_listing_link('yes');
                    $search_result = $page->parse_template_section($search_result, 'link_edit_listing_url', $edit_link);
                    // Replace addon fields.
                    $addon_fields = $page->get_addon_template_field_list($addons);
                    $search_result = $page->parse_addon_tags($search_result, $addon_fields);
                    $search_result = $page->cleanup_images($search_result);
                } // end while
                $page->replace_template_section('search_result_header', $header_section);
                $page->replace_template_section('search_result_dataset', $search_result);

                $save_search_link = $config['baseurl'] . '/index.php?action=save_search' . $guidestring_no_action;
                $page->page = $page->parse_template_section($page->page, 'save_search_link', $save_search_link);
                $page->replace_permission_tags();
                $page->cleanup_template_sections($next_prev, $next_prev_bottom);
                $display = $page->return_page();
            } // end if
            else {
                if (!isset($_GET['cur_page'])) {
                    $_GET['cur_page'] = 0;
                }
                // This search has no results. Display an error message and the search page again.
                $display .= $this->create_searchpage(false, true);
            }

            return $display;
        }
    } //End Function search_results
    public function searchbox_latlongdist()
    {
        global $lang;
        // start the row
        $display = '';
        $display .= '<fieldset class="or_latlongdist_fieldset"><legend class="or_latlongdist_legend">' . $lang['distance_from_lat_log'] . '</legend>';
        $display .= '<label class="searchpage_field_caption">' . $lang['lat'] . '</label><input type="text" name="latlong_dist_lat" /><br />';
        $display .= '<label class="searchpage_field_caption">' . $lang['long'] . '</label><input type="text" name="latlong_dist_long" /><br />';
        $display .= '<label class="searchpage_field_caption">' . $lang['distance'] . '</label><input type="text" name="latlong_dist_dist" />' . $lang['miles'] . '<br />';
        $display .= '</fieldset>';
        return $display;
    }
    public function searchbox_postaldist()
    {
        global  $lang;
        // start the row
        $display = '';
        $display .= '<fieldset class="or_postaldist_fieldset"><legend class="or_postaldist_legend">' . $lang['distance_from_zip'] . '</legend>';
        $display .= '<label class="searchpage_field_caption">' . $lang['postal_code'] . '</label><input type="text" name="postalcode_dist_code" /><br />';
        $display .= '<label class="searchpage_field_caption">' . $lang['distance'] . '</label><input type="text" name="postalcode_dist_dist" />' . $lang['miles'] . '<br />';
        $display .= '</fieldset>';

        return $display;
    }
    public function searchbox_citydist()
    {
        global  $lang;
        // start the row
        $display = '';
        $display .= '<fieldset class="or_citydist_fieldset"><legend class="or_citydist_legend">' . $lang['distance_from_city'] . '</legend>';
        $display .= '<label class="searchpage_field_caption">' . $lang['city'] . '</label><input type="text" name="city_dist_code" /><br />';
        $display .= '<label class="searchpage_field_caption">' . $lang['distance'] . '</label><input type="text" name="city_dist_dist" />' . $lang['miles'] . '<br />';
        $display .= '</fieldset>';
        return $display;
    }
    public function searchbox_agentdropdown()
    {
        global $conn, $config, $lang, $api;

        // start the row
        $display = '';
        $display .= '<label>' . $lang['search_by_agent'] . '</label>';
        $display .= '<select name="user_ID">';
        $display .= '<option value="">' . $lang['Any_Agent'] . '</option>';

        //get a list of active Agent ID#s sorted by rank
        $result = $api->load_local_api('user__search', [
            'parameters' => [
                'userdb_active' => 'yes',
                'userdb_is_agent' => 'yes',
            ],
            'resource' => 'agent',
            'sortby' => ['userdb_rank'],
        ]);

        // get the firstname, lastname
        if (!$result['error']) {
            foreach ($result['users'] as $user_id) {
                $agent_details = $api->load_local_api('user__read', [
                    'user_id' => $user_id,
                    'resource' => 'agent',
                    'fields' => [
                        'userdb_user_first_name',
                        'userdb_user_last_name',
                    ],
                ]);
                if (!$agent_details['error']) {
                    $user_name = "{$agent_details['user']['userdb_user_first_name']}, {$agent_details['user']['userdb_user_last_name']}";
                    $display .= '<option value="' . $user_id . '">' . $user_name . '</option>';
                }
            }
        }

        $display .= '</select><br />';
        return $display;
    }

    public function searchbox_created_in_last_days()
    {
        global $conn, $config, $lang, $misc;

        $display = '';
        $display .= '<label class="searchpage_field_caption">' . $lang['search_listing_created_in_last_days'] . '</label> ';
        $display .= '<input type="text" name="listingsdb_creation_date_greater_days" />';
        $display .= '<br />';
        return $display;
    }

    public function searchbox_render($field_id, $pclass = [], $render_parts = 'both')
    {
        global $conn, $config, $lang, $api, $misc;

        $display = '';
        $time = $misc->getmicrotime();
        $class_sql = '';
        $class_sql_array = [];
        foreach ($pclass as $class_id) {
            if ($class_id > 0) {
                $class_sql_array[] .= $config['table_prefix'] . 'listingsdb.listingsdb_pclass_id = ' . intval($class_id);
            }
        }
        if (count($class_sql_array) > 0) {
            $class_sql = ' AND (' . implode(' OR ', $class_sql_array) . ' ) ';
        }
        //See if we got passed the ID or field object
        if (is_numeric($field_id)) {
            $result = $api->load_local_api('fields__metadata', ['resource' => 'listing', 'searchable_only' => true, 'class' => $pclass]);
            foreach ($result['fields'] as $my_field_id => $field_array) {
                if ($field_id == $my_field_id) {
                    $field_id = $field_array;
                    break;
                }
            }
        }
        if (!is_array($field_id)) {
            return;
        }

        $browse_caption = $field_id['search_label'];
        $searchbox_type = $field_id['search_type'];
        $browse_field_name = $field_id['field_name'];
        $field_type = $field_id['field_type'];

        //$result = $api->load_local_api('fields__values',array('field_name'=>$browse_field_name,'field_type'=>$field_type));

        //Deal with Date Formats
        $dateFormat = false;
        if ($field_type == 'date') {
            $dateFormat = true;
        }
        //Get Date Format Settins
        if ($config['date_format'] == 1) {
            $format = 'm/d/Y';
        } elseif ($config['date_format'] == 2) {
            $format = 'Y/d/m';
        } elseif ($config['date_format'] == 3) {
            $format = 'd/m/Y';
        }
        $display .= '<div class="searchpage_field_wrapper">';
        switch ($searchbox_type) {
            case 'ptext':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $display .= '<input name="' . $browse_field_name . '[]" type="text"';
                    if (isset($_GET[$browse_field_name][0]) && $_GET[$browse_field_name][0] != '') {
                        $f = htmlspecialchars($_GET[$browse_field_name][0], ENT_COMPAT, $config['charset']);
                        $display .= 'value="' . $f . '"';
                    }
                    $display .= ' />';
                }
                break;
            case 'pulldown':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $display .= '<select name="' . $browse_field_name . '"><option value="">' . $lang['all'] . '</option>';
                    $result = $api->load_local_api('fields__values', ['field_name' => $browse_field_name, 'field_type' => $field_type, 'pclass' => $pclass]);
                    foreach ($result['field_values'] as $field_output) {
                        $selected = '';
                        if (isset($_GET[$browse_field_name]) && $_GET[$browse_field_name] == $field_output) {
                            $selected = 'selected="selected"';
                        }
                        $num_type = '';
                        if ($config['configured_show_count'] == 1) {
                            $num_type = $result['field_counts'][$field_output];
                            $num_type = '(' . $num_type . ')';
                        }
                        if ($dateFormat == true) {
                            $display .= '<option value="' . $field_output . '" ' . $selected . '>' . date($format, $field_output) . ' ' . $num_type . '</option>';
                        } else {
                            if ($field_type == 'number') {
                                $field_display = $misc->international_num_format($field_output, $config['number_decimals_number_fields']);
                                $display .= '<option value="' . $field_output . '" ' . $selected . '>' . $field_display . ' ' . $num_type . '</option>';
                            } else {
                                $display .= '<option value="' . $field_output . '" ' . $selected . '>' . $field_output . ' ' . $num_type . '</option>';
                            }
                        }
                    } // end while
                    $display .= '</select>';
                }
                break;
            case 'null_checkbox':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $display .= '<td align="left">';
                    $num_type = '';

                    $setvalue = '';
                    if (isset($_GET[$browse_field_name . '-NULL']) && $_GET[$browse_field_name . '-NULL'] == 1) {
                        $setvalue = 'checked="checked"';
                    }
                    $display .= '<input type="checkbox" name="' . $browse_field_name . '-NULL" ' . $setvalue . ' value="1" />' . $browse_field_name . ' ' . $lang['null_search'] . ' ' . $num_type;
                }
                break;
            case 'notnull_checkbox':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $num_type = '';
                    $setvalue = '';
                    if (isset($_GET[$browse_field_name . '-NOTNULL']) && $_GET[$browse_field_name . '-NOTNULL'] == 1) {
                        $setvalue = 'checked="checked"';
                    }
                    $display .= '<input type="checkbox" name="' . $browse_field_name . '-NOTNULL" ' . $setvalue . ' value="1" />' . $browse_field_name . ' ' . $lang['notnull_search'] . ' ' . $num_type;
                }
                break;
            case 'select':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $display .= '<select name="' . $browse_field_name . '[]" multiple="multiple">';
                    $selected = '';
                    if (isset($_GET[$browse_field_name]) && is_array($_GET[$browse_field_name])) {
                        if (in_array('', $_GET[$browse_field_name])) {
                            $selected = 'selected="selected"';
                        }
                    }
                    $display .= '<option value="" ' . $selected . '>' . $lang['all'] . '</option>';
                    $result = $api->load_local_api('fields__values', ['field_name' => $browse_field_name, 'field_type' => $field_type, 'pclass' => $pclass]);
                    foreach ($result['field_values'] as $field_output) {
                        $selected = '';
                        if (isset($_GET[$browse_field_name]) && is_array($_GET[$browse_field_name])) {
                            if (in_array($field_output, $_GET[$browse_field_name])) {
                                $selected = 'selected="selected"';
                            }
                        }
                        $num_type = '';
                        if ($config['configured_show_count'] == 1) {
                            $num_type = $result['field_counts'][$field_output];
                            $num_type = '(' . $num_type . ')';
                        }
                        if ($dateFormat == true) {
                            $display .= '<option value="' . $field_output . '" ' . $selected . '>' . date($format, $field_output) . ' ' . $num_type . '</option>';
                        } else {
                            if ($field_type == 'number') {
                                $field_display = $misc->international_num_format($field_output, $config['number_decimals_number_fields']);
                                $display .= '<option value="' . $field_output . '" ' . $selected . '>' . $field_display . ' ' . $num_type . '</option>';
                            } else {
                                $display .= '<option value="' . $field_output . '" ' . $selected . '>' . $field_output . ' ' . $num_type . '</option>';
                            }
                        }
                    } // end while
                    $display .= '</select>';
                }
                break;
            case 'select_or':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $display .= '<select name="' . $browse_field_name . '_or[]" multiple="multiple">';
                    $selected = '';
                    if (isset($_GET[$browse_field_name]) && is_array($_GET[$browse_field_name])) {
                        if (in_array('', $_GET[$browse_field_name])) {
                            $selected = 'selected="selected"';
                        }
                    }
                    $display .= '<option value="" ' . $selected . '>' . $lang['all'] . '</option>';
                    $result = $api->load_local_api('fields__values', ['field_name' => $browse_field_name, 'field_type' => $field_type, 'pclass' => $pclass]);
                    foreach ($result['field_values'] as $field_output) {
                        $selected = '';
                        if (isset($_GET[$browse_field_name]) && is_array($_GET[$browse_field_name])) {
                            if (in_array($field_output, $_GET[$browse_field_name])) {
                                $selected = 'selected="selected"';
                            }
                        }
                        $num_type = '';
                        if ($config['configured_show_count'] == 1) {
                            $num_type = $result['field_counts'][$field_output];
                            $num_type = '(' . $num_type . ')';
                        }
                        if ($dateFormat == true) {
                            $display .= '<option value="' . $field_output . '" ' . $selected . '>' . date($format, $field_output) . ' ' . $num_type . '</option>';
                        } else {
                            if ($field_type == 'number') {
                                $field_display = $misc->international_num_format($field_output, $config['number_decimals_number_fields']);
                                $display .= '<option value="' . $field_output . '" ' . $selected . '>' . $field_display . ' ' . $num_type . '</option>';
                            } else {
                                $display .= '<option value="' . $field_output . '" ' . $selected . '>' . $field_output . ' ' . $num_type . '</option>';
                            }
                        }
                    } // end while
                    $display .= '</select>';
                }
                break;
            case 'checkbox':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $result = $api->load_local_api('fields__values', ['field_name' => $browse_field_name, 'field_type' => $field_type, 'pclass' => $pclass]);

                    //echo '<pre>'.print_r($result,TRUE).'</pre>';
                    foreach ($result['field_values'] as $field_output) {
                        $selected = '';
                        if (isset($_GET[$browse_field_name]) && is_array($_GET[$browse_field_name])) {
                            if (in_array($field_output, $_GET[$browse_field_name])) {
                                $selected = 'checked="checked"';
                            }
                        }
                        $num_type = '';
                        if ($config['configured_show_count'] == 1) {
                            $num_type = $result['field_counts'][$field_output];
                            $num_type = '(' . $num_type . ')';
                        }
                        if ($dateFormat == true) {
                            $display .= '<input type="checkbox" name="' . $browse_field_name . '[]" value="' . $field_output . '" ' . $selected . ' />' . date($format, $field_output) . ' ' . $num_type . '';
                            $display .= $config['search_list_separator'];
                        } else {
                            if ($field_type == 'number') {
                                $field_display = $misc->international_num_format($field_output, $config['number_decimals_number_fields']);
                                $display .= '<input type="checkbox" name="' . $browse_field_name . '[]" value="' . $field_output . '" ' . $selected . ' />' . $field_display . ' ' . $num_type . '';
                                $display .= $config['search_list_separator'];
                            } else {
                                $display .= '<input type="checkbox" name="' . $browse_field_name . '[]" value="' . $field_output . '" ' . $selected . ' />' . $field_output . ' ' . $num_type . '';
                                $display .= $config['search_list_separator'];
                            }
                        }
                    } // end while
                }
                break;
            case 'checkbox_or':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $result = $api->load_local_api('fields__values', ['field_name' => $browse_field_name, 'field_type' => $field_type, 'pclass' => $pclass]);
                    foreach ($result['field_values'] as $field_output) {
                        $selected = '';
                        if (isset($_GET[$browse_field_name]) && is_array($_GET[$browse_field_name])) {
                            if (in_array($field_output, $_GET[$browse_field_name])) {
                                $selected = 'checked="checked"';
                            }
                        }
                        $num_type = '';
                        if ($config['configured_show_count'] == 1) {
                            $num_type = $result['field_counts'][$field_output];
                            $num_type = '(' . $num_type . ')';
                        }
                        if ($dateFormat == true) {
                            $display .= '<input type="checkbox" name="' . $browse_field_name . '_or[]" value="' . $field_output . '" ' . $selected . ' />' . date($format, $field_output) . ' ' . $num_type . '';
                            $display .= $config['search_list_separator'];
                        } else {
                            if ($field_type == 'number') {
                                $field_display = $misc->international_num_format($field_output, $config['number_decimals_number_fields']);
                                $display .= '<input type="checkbox" name="' . $browse_field_name . '_or[]" value="' . $field_output . '" ' . $selected . ' />' . $field_display . ' ' . $num_type . '';
                                $display .= $config['search_list_separator'];
                            } else {
                                $display .= '<input type="checkbox" name="' . $browse_field_name . '_or[]" value="' . $field_output . '" ' . $selected . ' />' . $field_output . ' ' . $num_type . '';
                                $display .= $config['search_list_separator'];
                            }
                        }
                    } // end while
                }
                break;
            case 'option':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $result = $api->load_local_api('fields__values', ['field_name' => $browse_field_name, 'field_type' => $field_type, 'pclass' => $pclass]);
                    foreach ($result['field_values'] as $field_output) {
                        $selected = '';
                        if (isset($_GET[$browse_field_name]) && $_GET[$browse_field_name] == $field_output) {
                            $selected = 'checked="checked"';
                        }
                        $num_type = '';
                        if ($config['configured_show_count'] == 1) {
                            $num_type = $result['field_counts'][$field_output];
                            $num_type = '(' . $num_type . ')';
                        }
                        if ($dateFormat == true) {
                            $display .= '<input type="radio" name="' . $browse_field_name . '" value="' . $field_output . '" ' . $selected . ' />' . date($format, $field_output) . ' ' . $num_type . '';
                            $display .= $config['search_list_separator'];
                        } else {
                            if ($field_type == 'number') {
                                $field_display = $misc->international_num_format($field_output, $config['number_decimals_number_fields']);
                                $display .= '<input type="radio" name="' . $browse_field_name . '" value="' . $field_output . '" ' . $selected . ' />' . $field_display . ' ' . $num_type . '';
                                $display .= $config['search_list_separator'];
                            } else {
                                $display .= '<input type="radio" name="' . $browse_field_name . '" value="' . $field_output . '" ' . $selected . ' />' . $field_output . ' ' . $num_type . '';
                                $display .= $config['search_list_separator'];
                            }
                        }
                    } // end while
                    if (count($result['field_values']) == 0) {
                        $display .= $config['search_list_separator'];
                    }
                }
                break;
            case 'optionlist':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $display .= '<select name="' . $browse_field_name . '[]" multiple="multiple" size="6">';

                    $r = $field_id['field_elements'];
                    sort($r);
                    foreach ($r as $f) {
                        $selected = '';
                        if (isset($_GET[$browse_field_name]) && is_array($_GET[$browse_field_name])) {
                            if (in_array($f, $_GET[$browse_field_name])) {
                                $selected = 'selected="selected"';
                            }
                        }
                        $f = htmlspecialchars($f, ENT_COMPAT, $config['charset']);
                        $display .= '<option value="' . $f . '" ' . $selected . '>' . $f . '</option>';
                    }
                    $display .= '</select>';
                }
                break;
            case 'optionlist_or':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $display .= '<select name="' . $browse_field_name . '_or[]" multiple="multiple" size="6">';
                    $r = $field_id['field_elements'];
                    sort($r);
                    foreach ($r as $f) {
                        $selected = '';
                        if (isset($_GET[$browse_field_name]) && is_array($_GET[$browse_field_name])) {
                            if (in_array($f, $_GET[$browse_field_name])) {
                                $selected = 'selected="selected"';
                            }
                        }
                        $f = htmlspecialchars($f, ENT_COMPAT, $config['charset']);
                        $display .= '<option value="' . $f . '" ' . $selected . '>' . $f . '</option>';
                    }
                    $display .= '</select>';
                }
                break;
            case 'fcheckbox':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $r = $field_id['field_elements'];
                    sort($r);
                    foreach ($r as $f) {
                        $selected = '';
                        if (isset($_GET[$browse_field_name]) && is_array($_GET[$browse_field_name])) {
                            if (in_array($f, $_GET[$browse_field_name])) {
                                $selected = 'checked="checked"';
                            }
                        }
                        $f = htmlspecialchars($f, ENT_COMPAT, $config['charset']);
                        $display .= '<input type="checkbox" name="' . $browse_field_name . '[]" value="' . $f . '" ' . $selected . ' />' . $f . '';
                        $display .= $config['search_list_separator'];
                    }
                }
                break;
            case 'fcheckbox_or':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $r = $field_id['field_elements'];
                    sort($r);
                    foreach ($r as $f) {
                        $selected = '';
                        if (isset($_GET[$browse_field_name]) && is_array($_GET[$browse_field_name])) {
                            if (in_array($f, $_GET[$browse_field_name])) {
                                $selected = 'checked="checked"';
                            }
                        }
                        $f = htmlspecialchars($f, ENT_COMPAT, $config['charset']);
                        $display .= '<input type="checkbox" name="' . $browse_field_name . '_or[]" value="' . $f . '" ' . $selected . ' />' . $f . '';
                        $display .= $config['search_list_separator'];
                    }
                }
                break;
            case 'fpulldown':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $display .= '<select name="' . $browse_field_name . '"><option value="">' . $lang['all'] . '</option>';
                    $r = $field_id['field_elements'];
                    sort($r);
                    foreach ($r as $f) {
                        $selected = '';
                        if (isset($_GET[$browse_field_name]) && $_GET[$browse_field_name] == $f) {
                            $selected = 'selected="selected"';
                        }
                        $f = htmlspecialchars($f, ENT_COMPAT, $config['charset']);
                        $display .= '<option value="' . $f . '" ' . $selected . '>' . $f . '</option>';
                    }
                    $display .= '</select>';
                }
                break;
            case 'daterange':
                if ($render_parts != 'element') {
                    $display .= '<label  class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $setvalue = '';
                    if (isset($_GET[$browse_field_name . '-mindate']) && $_GET[$browse_field_name . '-mindate'] != '') {
                        $f = htmlspecialchars($_GET[$browse_field_name . '-mindate'], ENT_COMPAT, $config['charset']);
                        $setvalue = 'value="' . $f . '"';
                    }
                    $display .= $lang['from'] . ' <input type="text" name="' . $browse_field_name . '-mindate" ' . $setvalue . '  class="{curley_open}validate:{curley_open}ordate:' . $config['date_format'] . '{curley_close}{curley_close}" /> (' . $config['date_format_long'] . ')<br />';
                    $setvalue = '';
                    if (isset($_GET[$browse_field_name . '-maxdate']) && $_GET[$browse_field_name . '-maxdate'] != '') {
                        $f = htmlspecialchars($_GET[$browse_field_name . '-maxdate'], ENT_COMPAT, $config['charset']);
                        $setvalue = 'value="' . $f . '"';
                    }
                    $display .= $lang['to'] . ' <input type="text" name="' . $browse_field_name . '-maxdate" ' . $setvalue . '  class="{curley_open}validate:{curley_open}ordate:' . $config['date_format'] . '{curley_close}{curley_close}" /> (' . $config['date_format_long'] . ')';
                }
                break;
            case 'singledate':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    $setvalue = '';
                    if (isset($_GET[$browse_field_name . '-date']) && $_GET[$browse_field_name . '-date'] != '') {
                        $f = htmlspecialchars($_GET[$browse_field_name . '-date'], ENT_COMPAT, $config['charset']);
                        $setvalue = 'value="' . $f . '"';
                    }
                    $display .= ' <input type="text" name="' . $browse_field_name . '-date" ' . $setvalue . ' class="{curley_open}validate:{curley_open}ordate:' . $config['date_format'] . '{curley_close}{curley_close}" /> (' . $config['date_format_long'] . ')';
                }
                break;
            case 'minmax':
                if ($render_parts != 'element') {
                    $display .= '<label class="searchpage_field_caption">' . $browse_caption . '</label>';
                }
                if ($render_parts != 'label') {
                    // Get max, min and step
                    $step = $field_id['search_step'];
                    ;
                    //Manual Step Values
                    if (strpos($step, '|') !== false) {
                        $step_array = explode('|', $step);
                        if (!isset($step_array[0]) || !isset($step_array[1])) {
                            //Bad Step Array Fail
                            exit;
                        }
                        $min = intval($step_array[0]);
                        $max = intval($step_array[1]);
                        if (isset($step_array[2])) {
                            $step = intval($step_array[2]);
                        } else {
                            $step = 0;
                        }
                    } else {
                        $field_list = $config['table_prefix'] . 'listingsdbelements, ' . $config['table_prefix'] . 'listingsdb WHERE
								' . $config['table_prefix'] . 'listingsdbelements.listingsdb_id = ' . $config['table_prefix'] . 'listingsdb.listingsdb_id';

                        global $db_type;
                        if (strpos($db_type, 'mysql') !== false || strpos($db_type, 'pdo') !== false) {
                            if ($field_type == 'decimal') {
                                $minmax_rs = $conn->Execute('SELECT max(listingsdbelements_field_value+0) as max, min(listingsdbelements_field_value+0) as min
													FROM ' . $field_list . '
													AND listingsdbelements_field_name = \'' . $browse_field_name . '\'' . $class_sql);
                                $max = $minmax_rs->fields('max');
                                $min = $minmax_rs->fields('min');
                            } else {
                                $minmax_rs = $conn->Execute('SELECT max(CAST(listingsdbelements_field_value as signed)) as max,
														min(CAST(listingsdbelements_field_value as signed)) as min
													FROM ' . $field_list . '
													AND listingsdbelements_field_name = \'' . $browse_field_name . '\'' . $class_sql);
                                $max = $minmax_rs->fields('max');
                                $min = $minmax_rs->fields('min');

                                if ($field_type == 'price') {
                                    $min = substr_replace($min, '000', -3);
                                }
                            }
                        } else {
                            if ($field_type == 'decimal') {
                                $max = $conn->Execute('SELECT max(listingsdbelements_field_value+0) As max
													FROM ' . $field_list . '
													AND listingsdbelements_field_name = \'' . $browse_field_name . '\'' . $class_sql);
                                $max = $max->fields('max');
                                $min = $conn->Execute('SELECT min(listingsdbelements_field_value+0) as min
													FROM ' . $field_list . '
													AND listingsdbelements_field_name = \'' . $browse_field_name . '\'' . $class_sql);
                                $min = $min->fields('min');
                            } else {
                                $max = $conn->Execute('SELECT max(CAST(listingsdbelements_field_value as int4)) as max
													FROM ' . $field_list . '
													AND listingsdbelements_field_name = \'' . $browse_field_name . '\'' . $class_sql);
                                $max = $max->fields('max');
                                $min = $conn->Execute('SELECT min(CAST(listingsdbelements_field_value as int4)) as min
													FROM ' . $field_list . '
													AND listingsdbelements_field_name = \'' . $browse_field_name . '\'' . $class_sql);
                                $min = $min->fields('min');
                                if ($field_type == 'price') {
                                    $min = substr_replace($min, '000', -3);
                                }
                            }
                        }
                    }

                    if ($step == 0) {
                        if ($max > $min) {
                            $step = ceil(($max - $min) / 10);
                        } else {
                            $step = ceil($max / 10);
                        }
                    }
                    if ($config['search_step_max'] >= '1') {
                        $step_val = (($max - $min) / $config['search_step_max']);
                        if ($step_val > $step) {
                            $step = $step_val;
                        }
                    }

                    $display .= '<select name="' . $browse_field_name . '-min">' . "\n";
                    $options = '<option value="">' . $lang['all'] . '</option>' . "\n";
                    if ($field_type == 'price') {
                        $i = $min;
                        while ($i < $max) {
                            $z = $misc->international_num_format($i, $config['number_decimals_price_fields']);
                            $z = $misc->money_formats($z);
                            $selected = '';
                            if (isset($_GET[$browse_field_name . '-min']) && $_GET[$browse_field_name . '-min'] == $i) {
                                $selected = 'selected="selected"';
                            }
                            $options .= '<option value="' . $i . '" ' . $selected . '>' . $z . '</option>';
                            $i += $step;
                        }
                        $z = $misc->international_num_format($max, $config['number_decimals_price_fields']);
                        $z = $misc->money_formats($z);
                        $selected = '';
                        if (isset($_GET[$browse_field_name . '-min']) && $_GET[$browse_field_name . '-min'] == $i) {
                            $selected = 'selected="selected"';
                        }
                        $options .= '<option value="' . $max . '" ' . $selected . '>' . $z . '</option>';
                    } else {
                        $i = $min;
                        while ($i < $max) {
                            $selected = '';
                            if (isset($_GET[$browse_field_name . '-min']) && $_GET[$browse_field_name . '-min'] == $i) {
                                $selected = 'selected="selected"';
                            }
                            $options .= '<option ' . $selected . '>' . $i . '</option>';
                            $i += $step;
                        }
                        $selected = '';
                        if (isset($_GET[$browse_field_name . '-min']) && $_GET[$browse_field_name . '-min'] == $max) {
                            $selected = 'selected="selected"';
                        }
                        $options .= '<option ' . $selected . '>' . $max . '</option>';
                    }
                    $options .= '</select>';
                    $display .= $options . ' ' . $lang['to'] . ' ';

                    $options = '<option value="">' . $lang['all'] . '</option>' . "\n";
                    if ($field_type == 'price') {
                        $i = $min;

                        while ($i < $max) {
                            $z = $misc->international_num_format($i, $config['number_decimals_price_fields']);
                            $z = $misc->money_formats($z);
                            $selected = '';

                            if (isset($_GET[$browse_field_name . '-max']) && $_GET[$browse_field_name . '-max'] == $i) {
                                $selected = 'selected="selected"';
                            }
                            $options .= '<option value="' . $i . '" ' . $selected . '>' . $z . '</option>';
                            $i += $step;
                        }
                        $z = $misc->international_num_format($max, $config['number_decimals_price_fields']);
                        $z = $misc->money_formats($z);
                        $selected = '';
                        if (isset($_GET[$browse_field_name . '-max']) && $_GET[$browse_field_name . '-max'] == $max) {
                            $selected = 'selected="selected"';
                        }
                        $options .= '<option value="' . $max . '" ' . $selected . '>' . $z . '</option>';
                    } else {
                        $i = $min;
                        while ($i < $max) {
                            $selected = '';
                            if (isset($_GET[$browse_field_name . '-max']) && $_GET[$browse_field_name . '-max'] == $i) {
                                $selected = 'selected="selected"';
                            }
                            $options .= '<option ' . $selected . '>' . $i . '</option>';
                            $i += $step;
                        }
                        $selected = '';
                        if (isset($_GET[$browse_field_name . '-max']) && $_GET[$browse_field_name . '-max'] == $max) {
                            $selected = 'selected="selected"';
                        }
                        $options .= '<option ' . $selected . '>' . $max . '</option>';
                    }
                    $options .= '</select>';
                    $display .= '<select name="' . $browse_field_name . '-max">' . $options;
                }
                break;
        } // End switch ($searchbox_type)
        $display .= '</div>';
        $time2 = $misc->getmicrotime();
        $render_time = sprintf('%.3f', $time2 - $time);
        $display .= "\r\n" . '<!--Search Box ' . $browse_field_name . ' Render Time ' . $render_time . ' -->' . "\r\n";
        //$display .= "\r\n".'Search Box '.$browse_field_name.' Render Time '.$render_time.''."\r\n";
        return $display;
    }

    public function calculate_mileage($lat1, $lat2, $lon1, $lon2)
    {
        // used internally, this function actually performs that calculation to
        // determine the mileage between 2 points defined by lattitude and
        // longitude coordinates.  This calculation is based on the code found
        // at http://www.cryptnet.net/fsp/zipdy/

        // Convert lattitude/longitude (degrees) to radians for calculations
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // Find the deltas
        $delta_lat = $lat2 - $lat1;
        $delta_lon = $lon2 - $lon1;

        // Find the Great Circle distance
        $temp = pow(sin($delta_lat / 2.0), 2) + cos($lat1) * cos($lat2) * pow(sin($delta_lon / 2.0), 2);
        $distance = 3956 * 2 * atan2(sqrt($temp), sqrt(1 - $temp));

        return $distance;
    }
} // End Class
