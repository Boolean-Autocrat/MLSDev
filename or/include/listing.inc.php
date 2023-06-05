<?php

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;

class listing_pages
{
    public function listing_view()
    {
        global $conn, $lang, $config, $meta_canonical, $meta_index, $api;
        include_once $config['basepath'] . '/include/media.inc.php';
        $images = new image_handler();
        $display = '';
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $show_inactive_listing = false;
        $is_admin = $login->verify_priv('Admin');

        if (isset($_GET['listingID']) && $_GET['listingID'] != '' && is_numeric($_GET['listingID'])) {
            $api_result = $api->load_local_api('listing__read', ['listing_id' => intval($_GET['listingID']), 'fields' => ['listingsdb_pclass_id', 'listingsdb_active', 'userdb_id']]);
            if (!$api_result['error']) {
                $class_id = $api_result['listing']['listingsdb_pclass_id'];
                $listingdb_active = $api_result['listing']['listingsdb_active'];
                $listing_agent = $api_result['listing']['userdb_id'];
                if ($is_admin) {
                    $show_inactive_listing = true;
                } else {
                    if (isset($_SESSION['userID']) && $listing_agent == $_SESSION['userID']) {
                        $show_inactive_listing = true;
                    }
                }
                // first, check to see whether the listing is currently active
                if ($listingdb_active == 'yes' || $show_inactive_listing) {
                    include_once $config['basepath'] . '/include/core.inc.php';
                    $page = new page_user();
                    //Lookup Class

                    if (file_exists($config['template_path'] . '/listing_detail_pclass' . $class_id . '.html')) {
                        $page->load_page($config['template_path'] . '/listing_detail_pclass' . $class_id . '.html');
                    } else {
                        $page->load_page($config['template_path'] . '/' . $config['listing_template']);
                    }
                    $page->replace_custom_listing_search_block();
                    $sections = explode(',', $config['template_listing_sections']);
                    foreach ($sections as $section) {
                        $section = trim($section);
                        $replace = $this->renderTemplateArea($section, $_GET['listingID']);
                        $page->replace_tag($section, $replace);
                    }
                    $page->replace_listing_field_tags($_GET['listingID']);

                    // Check to see if listing owner is an admin only.
                    $is_admin = $this->getListingAgentAdminStatus($_GET['listingID']);

                    if ($is_admin == true && $config['show_listedby_admin'] == 0) {
                        $page->page = $page->remove_template_block('show_listed_by_admin', $page->page);
                        $page->page = $page->cleanup_template_block('!show_listed_by_admin', $page->page);
                    } else {
                        $page->page = $page->cleanup_template_block('show_listed_by_admin', $page->page);
                        $page->page = $page->remove_template_block('!show_listed_by_admin', $page->page);
                    }
                    if ($config['show_next_prev_listing_page'] == 1) {
                        $next_prev = $this->listing_next_prev();
                        $page->page = str_replace('{next_prev}', $next_prev, $page->page);
                    } else {
                        $page->page = str_replace('{next_prev}', '', $page->page);
                    }
                    //Show Slideshow
                    if (strpos($page->page, '{slideshow_thumbnail_group_block') !== false) {
                        //old < v3.3 slideshow JS moved to slideshow template
                        //$jscript .= '';
                        $page->page = $images->renderListingsMainImageSlideShow($_GET['listingID'], $page->page);
                    }
                    //Add Canonical Link
                    $meta_canonical = $page->magicURIGenerator('listing', $_GET['listingID'], true);
                    $display .= $page->return_page();
                } else {
                    $meta_index = false;
                    $display .= $lang['this_listing_is_not_active'];
                }
            } else {
                //No listing match, so show the custom not found page template
                include_once $config['basepath'] . '/include/core.inc.php';
                $page = new page_user();
                $page->load_page($config['template_path'] . '/not_found.html');
                $display .= $page->return_page();
                header('HTTP/1.0 404 Not Found');
            }
        }
        return $display;
    }

    public function listing_next_prev()
    {
        global $config, $lang;
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $display = '';
        $listingID = intval($_GET['listingID']);
        if (isset($_SESSION['OR_REFERRER_ACTION'])) {
            //print_r($_SESSION);
            if ($_SESSION['OR_REFERRER_ACTION']  != 'listingqrcode' && $_SESSION['OR_REFERRER_ACTION'] != 'searchresults' && $_SESSION['OR_REFERRER_ACTION'] != 'save_search' && $_SESSION['OR_REFERRER_ACTION'] != 'listingview' && $_SESSION['OR_REFERRER_ACTION'] != 'view_listing_image' && $_SESSION['OR_REFERRER_ACTION'] != 'calculator') {
                unset($_SESSION['results']);
            }
            if (isset($_SESSION['results'])) {
                $url = $_SESSION['searchstring'];
                $cur_page = $_SESSION['cur_page'];
                $url_with_page = $url . '&amp;cur_page=' . $cur_page;
                //$np_listings = $_SESSION['results'];
                //find current posistion in array
                $cur_key = array_search($listingID, $_SESSION['results']);
                $count = count($_SESSION['results']);

                if ($count > 1) {
                    //include_once $config['basepath'] . '/include/core.inc.php';
                    //$page = new page_user();
                    $page->load_page($config['template_path'] . '/listing_next_prev.html');
                    $page->replace_tag('count', $count);
                    if ($cur_key !== 0) {
                        $first_url = $page->magicURIGenerator('listing', $_SESSION['results'][0], true);
                        //Get URL for Previous Listing
                        $previous_url = $page->magicURIGenerator('listing', $_SESSION['results'][$cur_key - 1], true);
                        $page->replace_tag('first_url', $first_url);
                        $page->replace_tag('previous_url', $previous_url);
                        $page->page = $page->cleanup_template_block('!first_listing', $page->page);
                        $page->page = $page->remove_template_block('first_listing', $page->page);
                    } else {
                        $page->page = $page->cleanup_template_block('first_listing', $page->page);
                        $page->page = $page->remove_template_block('!first_listing', $page->page);
                    }
                    if (!empty($url)) {
                        $page->replace_tag('refine_search_args', $url_with_page);
                        $page->replace_tag('save_search_args', $url);
                    } else {
                        $page->replace_tag('refine_search_args', '');
                        $page->replace_tag('save_search_args', '');
                    }
                    //Show Next Last Buttons if we are not on the last Listing
                    $cur_num = $cur_key + 1;
                    $page->replace_tag('cur_num', $cur_num);
                    if ($cur_key != $count - 1) {
                        //Get URL for Next Listing
                        $next_url = $page->magicURIGenerator('listing', $_SESSION['results'][$cur_key + 1], true);
                        //Get URL for Last Listing
                        $last_url = $page->magicURIGenerator('listing', $_SESSION['results'][$count - 1], true);
                        $page->replace_tag('next_url', $next_url);
                        $page->replace_tag('last_url', $last_url);
                        $page->page = $page->cleanup_template_block('!last_listing', $page->page);
                        $page->page = $page->remove_template_block('last_listing', $page->page);
                    } else {
                        $page->page = $page->cleanup_template_block('last_listing', $page->page);
                        $page->page = $page->remove_template_block('!last_listing', $page->page);
                    }
                    $display .= $page->return_page();
                }
            }
        }
        return $display;
    }

    public function renderSingleListingItem($listingsdb_id, $name, $display_type = 'both')
    {
        // Display_type - Sets what should be returned.
        // both - Displays both the caption and the formated value
        // value - Displays just the formated value
        // rawvalue - Displays just the raw value
        // caption - Displays only the captions
        global $conn, $config, $lang, $misc;

        $display = '';
        $listingsdb_id = intval($listingsdb_id);

        if ($listingsdb_id !== 0) {
            $name = $misc->make_db_extra_safe($name);
            $sql = 'SELECT listingsdbelements_field_value, listingsformelements_id, listingsformelements_field_type, listingsformelements_field_caption
					FROM ' . $config['table_prefix'] . 'listingsdbelements, ' . $config['table_prefix'] . "listingsformelements
					WHERE ((listingsdb_id = $listingsdb_id) AND (listingsformelements_field_name = listingsdbelements_field_name)
					AND (listingsdbelements_field_name = $name))";
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            while (!$recordSet->EOF) {
                $field_value = $recordSet->fields('listingsdbelements_field_value');
                $field_type = $recordSet->fields('listingsformelements_field_type');
                $form_elements_id = $recordSet->fields('listingsformelements_id');
                if (!isset($_SESSION['users_lang'])) {
                    // Hold empty string for translation fields, as we are workgin with teh default lang
                    $field_caption = $recordSet->fields('listingsformelements_field_caption');
                } else {
                    $lang_sql = 'SELECT listingsformelements_field_caption
								FROM ' . $config['lang_table_prefix'] . 'listingsformelements
								WHERE listingsformelements_id = ' . $form_elements_id;
                    $lang_recordSet = $conn->Execute($lang_sql);
                    if (!$lang_recordSet) {
                        $misc->log_error($lang_sql);
                    }
                    $field_caption = $lang_recordSet->fields('listingsformelements_field_caption');
                }
                if ($field_type == 'divider') {
                    $display .= "<br /><strong>$field_caption</strong>";
                } elseif ($field_value != '') {
                    if ($display_type === 'both' || $display_type === 'caption') {
                        $display .= '<span class="field_caption">' . $field_caption . '</span>';
                    }
                    if ($display_type == 'both') {
                        $display .= ':&nbsp;';
                    }
                    if ($display_type === 'both' || $display_type === 'value') {
                        if ($field_type == 'select-multiple' or $field_type == 'option' or $field_type == 'checkbox') {
                            // handle field types with multiple options
                            // $display .= "<br /><strong>$field_caption</strong>";
                            $feature_index_list = explode('||', $field_value);
                            sort($feature_index_list);
                            $list_count = count($feature_index_list);
                            $l = 1;
                            foreach ($feature_index_list as $feature_list_item) {
                                if ($l < $list_count) {
                                    $display .= $feature_list_item;
                                    $display .= $config['feature_list_separator'];
                                    $l++;
                                } else {
                                    $display .= $feature_list_item;
                                }
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
                            $display .= '<a href="' . $field_value . '" onclick="window.open(this.href,\'_blank\',\'location=1,resizable=1,status=1,scrollbars=1,toolbar=1,menubar=1,noopener,noreferrer\');return false">' . $field_value . '</a>';
                        } elseif ($field_type == 'email') {
                            $display .= '<a href="mailto:' . $field_value . '">' . $field_value . '</a>';
                        } elseif ($field_type == 'text' or $field_type == 'textarea') {
                            if ($config['add_linefeeds'] === '1') {
                                $field_value = nl2br($field_value); //replace returns with <br />
                            } // end if
                            $display .= $field_value;
                        } elseif ($field_type == 'date') {
                            $field_value = $misc->convert_timestamp($field_value);
                            $display .= $field_value;
                        } else {
                            $display .= $field_value;
                        } // end else
                    }
                    if ($display_type === 'rawvalue') {
                        $display .= $field_value;
                    }
                } else {
                    if ($field_type == 'price' && $display_type !== 'rawvalue' && $config['zero_price'] == '1') {
                        $display .= $lang['call_for_price'];
                    } // end if
                } // end else
                $recordSet->MoveNext();
            } // end while
            return $display;
        } else {
            return '';
        }
    }

    public function renderTemplateArea($templateArea, $listingID)
    {
        // renders all the elements in a given template area on the listing pages
        global $conn, $config, $lang, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $listingID = intval($listingID);
        $templateArea = $misc->make_db_extra_safe($templateArea);
        $sql = 'SELECT listingsdbelements_field_value, listingsformelements_id, listingsformelements_field_type, listingsformelements_field_caption,listingsformelements_display_priv
				FROM ' . $config['table_prefix'] . 'listingsdbelements, ' . $config['table_prefix'] . 'listingsformelements
				WHERE ((' . $config['table_prefix'] . "listingsdbelements.listingsdb_id = $listingID)
				AND (listingsformelements_field_name = listingsdbelements_field_name)
				AND (listingsformelements_location = $templateArea))
				ORDER BY listingsformelements_rank ASC";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $display = '';
        while (!$recordSet->EOF) {
            $form_elements_id = $recordSet->fields('listingsformelements_id');
            $field_type = $recordSet->fields('listingsformelements_field_type');
            $display_priv = $recordSet->fields('listingsformelements_display_priv');
            $field_value = $recordSet->fields('listingsdbelements_field_value');
            if (!isset($_SESSION['users_lang'])) {
                // Hold empty string for translation fields, as we are workgin with teh default lang
                $field_caption = $recordSet->fields('listingsformelements_field_caption');
            } else {
                $lang_sql = 'SELECT listingsformelements_field_caption
							FROM ' . $config['lang_table_prefix'] . "listingsformelements
							WHERE listingsformelements_id = $form_elements_id";
                $lang_recordSet = $conn->Execute($lang_sql);
                if (!$lang_recordSet) {
                    $misc->log_error($lang_sql);
                }
                $field_caption = $lang_recordSet->fields('listingsformelements_field_caption');
            }
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
                if ($field_type == 'divider') {
                    $display .= '<br /><strong>' . $field_caption . '</strong>';
                } elseif ($field_value != '') {
                    if ($field_type == 'select-multiple' or $field_type == 'option' or $field_type == 'checkbox') {
                        // handle field types with multiple options
                        $display .= '<div class="multiple_options_caption">' . $field_caption . '</div>';
                        $feature_index_list = explode('||', $field_value);
                        sort($feature_index_list);
                        $list_count = count($feature_index_list);
                        $l = 1;
                        $display .= '<div class="multiple_options">';
                        $display .= '<ul>';
                        foreach ($feature_index_list as $feature_list_item) {
                            if ($l < $list_count) {
                                $display .= '<li>';
                                $display .= $feature_list_item;
                                $display .= $config['feature_list_separator'];
                                $display .= '</li>';
                                $l++;
                            } else {
                                $display .= '<li>';
                                $display .= $feature_list_item;
                                $display .= '</li>';
                            }
                        } // end while
                        $display .= '</ul>';
                        $display .= '</div>';
                        $display .= '<div class="clear"></div>' . BR;
                    } // end if field type is a multiple type
                    elseif ($field_type == 'price') {
                        $money_amount = $misc->international_num_format($field_value, $config['number_decimals_price_fields']);
                        $display .= "<strong>$field_caption</strong>: " . $misc->money_formats($money_amount) . BR;
                    } // end elseif
                    elseif ($field_type == 'number') {
                        $display .= "<strong>$field_caption</strong>: " . $misc->international_num_format($field_value, $config['number_decimals_number_fields']) . BR;
                    } // end elseif
                    elseif ($field_type == 'url') {
                        $display .= "<strong>$field_caption</strong>: <a href=\"$field_value\" onclick=\"window.open(this.href,'_blank','location=1,resizable=1,status=1,scrollbars=1,toolbar=1,menubar=1,noopener,noreferrer');return false\">$field_value</a>" . BR;
                    } elseif ($field_type == 'email') {
                        $display .= "<strong>$field_caption</strong>: <a href=\"mailto:$field_value\">$field_value</a>" . BR;
                    } elseif ($field_type == 'text' or $field_type == 'textarea') {
                        if ($config['add_linefeeds'] === '1') {
                            $field_value = nl2br($field_value); //replace returns with <br />
                        } // end if
                        $display .= '<strong>' . $field_caption . '</strong>: ' . $field_value . BR;
                    } elseif ($field_type == 'date') {
                        if ($config['date_format'] == 1) {
                            $format = 'm/d/Y';
                        } elseif ($config['date_format'] == 2) {
                            $format = 'Y/d/m';
                        } elseif ($config['date_format'] == 3) {
                            $format = 'd/m/Y';
                        }
                        $field_value = date($format, $field_value);
                        $display .= '<strong>' . $field_caption . '</strong>: ' . $field_value . BR;
                    } else {
                        $display .= '<strong>' . $field_caption . '</strong>: ' . $field_value . BR;
                    } // end else
                    $display .= '<br />';
                } else {
                    if ($field_type == 'price' && $config['zero_price'] == '1') {
                        $display .= '<strong>' . $field_caption . '</strong>: ' . $lang['call_for_price'] . '<br />' . BR;
                    } // end if
                } // end else
            }
            $recordSet->MoveNext();
        } // end while
        return $display;
    }

    public function renderTemplateAreaNoCaption($templateArea, $listingID)
    {
        // renders all the elements in a given template area on the listing pages
        // this time without the corresponding captions
        global $conn, $config, $lang, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $listingID = intval($listingID);
        $templateArea = $misc->make_db_extra_safe($templateArea);
        $sql = 'SELECT listingsdbelements_field_value, listingsformelements_field_type, listingsformelements_field_caption, listingsformelements_display_priv
				FROM ' . $config['table_prefix'] . 'listingsdbelements, ' . $config['table_prefix'] . 'listingsformelements
				WHERE ((' . $config['table_prefix'] . "listingsdbelements.listingsdb_id = $listingID)
				AND (listingsformelements_field_name = listingsdbelements_field_name)
				AND (listingsformelements_location = $templateArea))
				ORDER BY listingsformelements_rank ASC";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $display = '';
        while (!$recordSet->EOF) {
            $field_value = $recordSet->fields('listingsdbelements_field_value');
            $field_type = $recordSet->fields('listingsformelements_field_type');
            $field_caption = $recordSet->fields('listingsformelements_field_caption');
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
                if ($field_value != '') {
                    if ($field_type == 'select-multiple' or $field_type == 'option' or $field_type == 'checkbox') {
                        // handle field types with multiple options
                        $feature_index_list = explode('||', $field_value);
                        sort($feature_index_list);
                        $list_count = count($feature_index_list);
                        $l = 1;
                        foreach ($feature_index_list as $feature_list_item) {
                            if ($l < $list_count) {
                                $display .= $feature_list_item;
                                $display .= $config['feature_list_separator'];
                                $l++;
                            } else {
                                $display .= $feature_list_item;
                            }
                        } // end while
                    } // end if field type is a multiple type
                    elseif ($field_type == 'price') {
                        $money_amount = $misc->international_num_format($field_value, $config['number_decimals_price_fields']);
                        $display .= '<strong>' . $field_caption . '</strong>: ' . $misc->money_formats($money_amount);
                    } // end elseif
                    elseif ($field_type == 'number') {
                        $display .= "<strong>$field_caption</strong>: " . $misc->international_num_format($field_value, $config['number_decimals_number_fields']);
                    } // end elseif
                    elseif ($field_type == 'url') {
                        $display .= "<a href=\"$field_value\" onclick=\"window.open(this.href,'_blank','location=1,resizable=1,status=1,scrollbars=1,toolbar=1,menubar=1,noopener,noreferrer');return false\">$field_value</a>";
                    } elseif ($field_type == 'email') {
                        $display .= "<a href=\"mailto:$field_value\">$field_value</a>";
                    } elseif ($field_type == 'text' or $field_type == 'textarea') {
                        if ($config['add_linefeeds'] === '1') {
                            $field_value = nl2br($field_value); //replace returns with <br />
                        } // end if
                        $display .= $field_value;
                    } elseif ($field_type == 'Date') {
                        $field_value = $misc->convert_timestamp($field_value);
                        $display .= $field_value;
                    } else {
                        $display .= $field_value;
                    } // end else
                    $display .= '<br />';
                } else {
                    if ($field_type == 'price' && $config['zero_price'] == '1') {
                        $display .= $lang['call_for_price'] . '<br />';
                    } // end if
                } // end else
            }
            $recordSet->MoveNext();
        } // end while
        return $display;
    }

    public function renderFeaturedListingsVertical($num_of_listings = 0, $random = false, $pclass = '', $latest = false, $popular = false)
    {
        return $this->renderFeaturedListings($num_of_listings, 'vertical', $random, $pclass, $latest, $popular);
    }

    public function renderFeaturedListingsHorizontal($num_of_listings = 0, $random = false, $pclass = '', $latest = false, $popular = false)
    {
        return $this->renderFeaturedListings($num_of_listings, 'horizontal', $random, $pclass, $latest, $popular);
    }

    public function renderFeaturedListings($num_of_listings = 0, $template_name = '', $random = false, $pclass = '', $latest = false, $popular = false)
    {
        global $conn, $config, $current_ID, $api, $misc;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();

        $display = '';
        //If We have a $current_ID save it
        $old_current_ID = '';
        if ($current_ID != '') {
            $old_current_ID = $current_ID;
        }
        //Get the number of listing to display by default, unless user specified an override in the template file.
        if ($num_of_listings == 0) {
            $num_of_listings = $config['num_featured_listings'];
        }
        $ARGS = [];
        $SORTBY = [];
        $SORTTYPE = [];
        if ($latest == true) {
            $SORTBY[] = 'listingsdb_id';
            $SORTTYPE[] = 'DESC';
        }
        if ($popular == true) {
            $SORTBY[] = 'listingsdb_hit_count';
            $SORTTYPE[]  = 'DESC';
        }
        if (($random == true) || ($latest == true) || ($popular == true)) {
            $SORTBY[] = 'random';
        } else {
            $SORTBY[] = 'random';
            $ARGS['featuredOnly'] = 'yes';
        }
        $pclass_int = 0;
        if ($pclass != '' && is_numeric($pclass)) {
            $pclass_int = intval($pclass);
            $ARGS['pclass'][] = $pclass;
        }
        $result = $api->load_local_api('listing__search', ['parameters' => $ARGS, 'sortby' => $SORTBY, 'sorttype' => $SORTTYPE, 'limit' => $num_of_listings, 'offset' => 0, 'count_only' => 0]);
        //echo '<pre>'.print_r($result,TRUE).'</pre>';
        $returned_num_listings = $result['listing_count'];
        if ($returned_num_listings >= 1) {
            //Load the Featured Listing Template specified in the Site Config unless a template was specified in the calling template tag.
            if ($template_name == '') {
                $page->load_page($config['template_path'] . '/' . $config['featured_listing_template']);
            } else {
                if ($random == true) {
                    if ($pclass_int > 0) {
                        if (file_exists($config['template_path'] . '/random_listing_' . $template_name . '_' . $pclass_int . '.html')) {
                            $page->load_page($config['template_path'] . '/random_listing_' . $template_name . '_' . $pclass_int . '.html');
                        } else {
                            $page->load_page($config['template_path'] . '/random_listing_' . $template_name . '.html');
                        }
                    } else {
                        $page->load_page($config['template_path'] . '/random_listing_' . $template_name . '.html');
                    }
                    $page->load_page($config['template_path'] . '/random_listing_' . $template_name . '.html');
                } elseif ($latest == true) {
                    if ($pclass != '' && is_numeric($pclass)) {
                        if (file_exists($config['template_path'] . '/latest_listing_' . $template_name . '_' . $pclass . '.html')) {
                            $page->load_page($config['template_path'] . '/latest_listing_' . $template_name . '_' . $pclass . '.html');
                        } else {
                            $page->load_page($config['template_path'] . '/latest_listing_' . $template_name . '.html');
                        }
                    } else {
                        $page->load_page($config['template_path'] . '/latest_listing_' . $template_name . '.html');
                    }
                    $page->load_page($config['template_path'] . '/latest_listing_' . $template_name . '.html');
                } elseif ($popular == true) {
                    if ($pclass != '' && is_numeric($pclass)) {
                        if (file_exists($config['template_path'] . '/popular_listing_' . $template_name . '_' . $pclass . '.html')) {
                            $page->load_page($config['template_path'] . '/popular_listing_' . $template_name . '_' . $pclass . '.html');
                        } else {
                            $page->load_page($config['template_path'] . '/popular_listing_' . $template_name . '.html');
                        }
                    } else {
                        $page->load_page($config['template_path'] . '/popular_listing_' . $template_name . '.html');
                    }
                    $page->load_page($config['template_path'] . '/popular_listing_' . $template_name . '.html');
                } else {
                    if ($pclass != '' && is_numeric($pclass)) {
                        if (file_exists($config['template_path'] . '/featured_listing_' . $template_name . '_' . $pclass . '.html')) {
                            $page->load_page($config['template_path'] . '/featured_listing_' . $template_name . '_' . $pclass . '.html');
                        } else {
                            $page->load_page($config['template_path'] . '/featured_listing_' . $template_name . '.html');
                        }
                    } else {
                        $page->load_page($config['template_path'] . '/featured_listing_' . $template_name . '.html');
                    }
                }
            }

            $page->replace_custom_listing_search_block();
            // Determine if the template uses rows.
            // First item in array is the row conent second item is the number of block per block row
            $featured_template_row = $page->get_template_section_row('featured_listing_block_row');
            if (is_array($featured_template_row)) {
                $row = $featured_template_row[0];
                $col_count = $featured_template_row[1];
                $user_rows = true;
                $x = 1;
                //Create an empty array to hold the row conents
                $new_row_data = [];
            } else {
                $user_rows = false;
            }
            $featured_template_section = '';
            foreach ($result['listings'] as $listing) {
                if ($user_rows == true && $x > $col_count) {
                    //We are at then end of a row. Save the template section as a new row.
                    $new_row_data[] = $page->replace_template_section('featured_listing_block', $featured_template_section, $row);
                    //$new_row_data[] = $featured_template_section;
                    $featured_template_section = $page->get_template_section('featured_listing_block');
                    $x = 1;
                } else {
                    $featured_template_section .= $page->get_template_section('featured_listing_block');
                }

                $current_ID = $listing;
                $featured_url = $page->magicURIGenerator('listing', $current_ID, true);
                $featured_template_section = $page->replace_listing_field_tags($current_ID, $featured_template_section);
                $featured_template_section = $page->parse_template_section($featured_template_section, 'featured_url', $featured_url);
                $featured_template_section = $page->parse_template_section($featured_template_section, 'listingid', $current_ID);
                // Start {isfavorite} featured template section tag
                if (isset($_SESSION['userID'])) {
                    $userID = intval($_SESSION['userID']);
                    $sql1 = 'SELECT listingsdb_id 
							FROM ' . $config['table_prefix'] . "userfavoritelistings 
							WHERE ((listingsdb_id = $current_ID) 
							AND (userdb_id=$userID))";
                    $recordSet1 = $conn->Execute($sql1);
                    if ($recordSet1 === false) {
                        $misc->log_error($sql1);
                    }
                    $favorite_listingsdb_id = $recordSet1->fields('listingsdb_id');
                    if ($favorite_listingsdb_id !== $current_ID) {
                        $isfavorite = 'no';
                        $featured_template_section = $page->parse_template_section($featured_template_section, 'isfavorite', $isfavorite);
                    } else {
                        $isfavorite = 'yes';
                        $featured_template_section = $page->parse_template_section($featured_template_section, 'isfavorite', $isfavorite);
                    }
                }
                // End {isfavorite} featured template section tag
                // Setup Image Tags
                $sql2 = 'SELECT listingsimages_thumb_file_name,listingsimages_file_name 
						FROM ' . $config['table_prefix'] . "listingsimages 
						WHERE (listingsdb_id = $current_ID) 
						ORDER BY listingsimages_rank";
                $recordSet2 = $conn->SelectLimit($sql2, 1, 0);
                if ($recordSet2 === false) {
                    $misc->log_error($sql2);
                }
                if ($recordSet2->RecordCount() > 0) {
                    $thumb_file_name = $recordSet2->fields('listingsimages_thumb_file_name');
                    $file_name = $recordSet2->fields('listingsimages_file_name');
                    if (strpos($thumb_file_name, 'http://') === 0 || strpos($thumb_file_name, 'https://') === 0 || strpos($thumb_file_name, '//') === 0) {
                        $featured_thumb_src = $thumb_file_name;
                        $featured_thumb_width = $config['thumbnail_width'];
                        $featured_thumb_height = $config['thumbnail_height'];
                        $featured_width =  $config['main_image_width'];
                        $featured_height = $config['main_image_height'];
                        $featured_src = $file_name;
                    } elseif ($thumb_file_name != '' && file_exists($config['listings_upload_path'] . '/' . $thumb_file_name)) {
                        // gotta grab the thumbnail image size
                        $imagedata = GetImageSize("$config[listings_upload_path]/$thumb_file_name");
                        $imagewidth = $imagedata[0];
                        $imageheight = $imagedata[1];
                        $shrinkage = $config['thumbnail_width'] / $imagewidth;
                        $featured_thumb_width = $imagewidth * $shrinkage;
                        $featured_thumb_height = $imageheight * $shrinkage;
                        $featured_thumb_src = $config['listings_view_images_path'] . '/' . $thumb_file_name;
                        // gotta grab the thumbnail image size
                        $imagedata = GetImageSize("$config[listings_upload_path]/$file_name");
                        $imagewidth = $imagedata[0];
                        $imageheight = $imagedata[1];
                        $featured_width = $imagewidth;
                        $featured_height = $imageheight;
                        $featured_src = $config['listings_view_images_path'] . '/' . $file_name;
                    } else {
                        if ($config['show_no_photo'] == 1) {
                            $imagedata = GetImageSize('images/nophoto.gif');
                            $imagewidth = $imagedata[0];
                            $imageheight = $imagedata[1];
                            $shrinkage = $config['thumbnail_width'] / $imagewidth;
                            $featured_thumb_width = $imagewidth * $shrinkage;
                            $featured_thumb_height = $imageheight * $shrinkage;
                            $featured_thumb_src = $config['baseurl'] . '/images/nophoto.gif';
                            $featured_width = $featured_thumb_width;
                            $featured_height = $featured_thumb_height;
                            $featured_src = $config['baseurl'] . '/images/nophoto.gif';
                        } else {
                            $featured_thumb_width = '';
                            $featured_thumb_height = '';
                            $featured_thumb_src = '';
                            $featured_width = '';
                            $featured_height = '';
                            $featured_src = '';
                        }
                    }
                } else {
                    if ($config['show_no_photo'] == 1) {
                        $imagedata = GetImageSize('images/nophoto.gif');
                        $imagewidth = $imagedata[0];
                        $imageheight = $imagedata[1];
                        $shrinkage = $config['thumbnail_width'] / $imagewidth;
                        $featured_thumb_width = $imagewidth * $shrinkage;
                        $featured_thumb_height = $imageheight * $shrinkage;
                        $featured_thumb_src = $config['baseurl'] . '/images/nophoto.gif';
                        $featured_width = $featured_thumb_width;
                        $featured_height = $featured_thumb_height;
                        $featured_src = $config['baseurl'] . '/images/nophoto.gif';
                    } else {
                        $featured_thumb_width = '';
                        $featured_thumb_height = '';
                        $featured_thumb_src = '';
                        $featured_width = '';
                        $featured_height = '';
                        $featured_src = '';
                    }
                }
                if (!empty($featured_thumb_src)) {
                    $featured_template_section = $page->parse_template_section($featured_template_section, 'featured_thumb_src', $featured_thumb_src);
                    $featured_template_section = $page->parse_template_section($featured_template_section, 'featured_thumb_height', $featured_thumb_height);
                    $featured_template_section = $page->parse_template_section($featured_template_section, 'featured_thumb_width', $featured_thumb_width);
                    $featured_template_section = $page->cleanup_template_block('featured_img', $featured_template_section);
                } else {
                    $featured_template_section = $page->remove_template_block('featured_img', $featured_template_section);
                }
                if (!empty($featured_src)) {
                    $featured_template_section = $page->parse_template_section($featured_template_section, 'featured_large_src', $featured_src);
                    $featured_template_section = $page->parse_template_section($featured_template_section, 'featured_large_height', $featured_height);
                    $featured_template_section = $page->parse_template_section($featured_template_section, 'featured_large_width', $featured_width);
                    $featured_template_section = $page->cleanup_template_block('featured_img_large', $featured_template_section);
                } else {
                    $featured_template_section = $page->remove_template_block('featured_img_large', $featured_template_section);
                }
                if ($user_rows == true) {
                    $x++;
                }
            }
            if ($user_rows == true) {
                $featured_template_section = $page->cleanup_template_block('featured_listing', $featured_template_section);
                $new_row_data[] = $page->replace_template_section('featured_listing_block', $featured_template_section, $row);
                $replace_row = '';
                foreach ($new_row_data as $rows) {
                    $replace_row .= $rows;
                }
                $page->replace_template_section_row('featured_listing_block_row', $replace_row);
            } else {
                $page->replace_template_section('featured_listing_block', $featured_template_section);
            }
            $page->replace_permission_tags();
            $page->auto_replace_tags();
            $display .= $page->return_page();
        }
        $current_ID = '';
        if ($old_current_ID != '') {
            $current_ID = $old_current_ID;
        }
        return $display;
    }

    /**
     * get pclass (name)
     *
     * @param   integer $listingsdb_id
     * @return  string $listingsdb_creation_date
     */
    public function get_pclass($listingsdb_id)
    {
        global $api;

        $class_name = '';
        $pclass_id = $this->get_listing_single_value('listingsdb_pclass_id', $listingsdb_id);

        $api_result = $api->load_local_api('pclass__read', ['class_id' => intval($pclass_id)]);
        if ($api_result['error']) {
            die($api_result['error_msg']);
        }
        $class_name = $api_result['class_name'];
        return $class_name;
    }

    /**
     * get creation date
     *
     * @param   integer $listingsdb_id
     * @return  string $listingsdb_creation_date
     */
    public function get_creation_date($listingsdb_id)
    {
        global $config;
        $listingsdb_id = intval($listingsdb_id);
        $listingsdb_creation_date = $this->get_listing_single_value('listingsdb_creation_date', $listingsdb_id);
        $listingsdb_creation_date = date($config['date_format_timestamp'], strtotime($listingsdb_creation_date));
        return $listingsdb_creation_date;
    }

    /**
     * get Modified date
     *
     * @param   integer $listingsdb_id
     * @return  string $listingsdb_last_modified
     */
    public function get_modified_date($listingsdb_id)
    {
        global $config;
        $listingsdb_id = intval($listingsdb_id);
        $listingsdb_last_modified = $this->get_listing_single_value('listingsdb_last_modified', $listingsdb_id);
        $listingsdb_last_modified = date($config['date_format_timestamp'], strtotime($listingsdb_last_modified));
        return $listingsdb_last_modified;
    }

    /**
     * get listing seotitle
     *
     * @param   integer $listingsdb_id
     * @return  string $listing_seotitle
     */
    public function get_listing_seotitle($listingsdb_id)
    {
        $listing_seotitle = $this->get_listing_single_value('listing_seotitle', $listingsdb_id);
        return $listing_seotitle;
    }

    /**
     * get Agent Listings Link
     *
     * @param   integer $listingsdb_id
     * @return  string $display
     */
    public function getAgentListingsLink($listingsdb_id)
    {
        global $conn, $config, $lang;
        // get the main data for a given listing
        $listingsdb_id = intval($listingsdb_id);
        $userdb_id = $this->get_listing_agent_value('userdb_id', $listingsdb_id);
        $display = '<a href="' . $config['baseurl'] . '/index.php?action=searchresults&amp;user_ID=' . $userdb_id . '">' . $lang['user_listings_link_text'] . '</a>';
        return $display;
    }

    /**
     * get Listing Agent Thumbnail
     *
     * @param   integer $listingsdb_id
     * @return  array $listing_agent_thumbnail
     */
    public function getListingAgentThumbnail($listingsdb_id)
    {
        global $conn, $lang, $config, $api;

        $listingsdb_id = intval($listingsdb_id);
        $listing_agent_thumbnail = [];
        if ($listingsdb_id !== 0) {
            $userdb_id = $this->get_listing_single_value('userdb_id', $listingsdb_id);

            $result = $api->load_local_api('media__read', [
                'media_type' => 'userimages',
                'media_parent_id' => $userdb_id,
                'media_output' => 'URL',
            ]);
            if ($result['error']) {
                die($result['error_msg']);
            }
            foreach ($result['media_object'] as $obj) {
                $thumb_file_name = $obj['thumb_file_name'];
                $caption =  $obj['caption'];
                $listing_agent_thumbnail[] = '<img src="' . $config['user_view_images_path'] . '/' . $thumb_file_name . '" alt="' . $caption . '" />';
            } // end while
        } else {
            $listing_agent_thumbnail[0] = '<img src="' . $config['baseurl'] . '/images/nophoto.gif" alt="' . $lang['no_photo'] . '" />';
        }
        return $listing_agent_thumbnail;
    }

    /**
     * get Listing Agent Admin Status (replaces several get_Listing_xx() functions)
     *
     * @param   integer $listingsdb_id
     * @return  boolean
     */
    public function getListingAgentAdminStatus($listingsdb_id)
    {
        global $config, $misc, $api;

        $listingsdb_id = intval($listingsdb_id);

        $result = $api->load_local_api('listing__read', ['listing_id' => $listingsdb_id, 'fields' => ['userdb_id']]);
        if (!$result['error']) {
            $userdb_id = $result['listing']['userdb_id'];
        }

        $is_admin = $misc->get_admin_status($userdb_id);
        $is_agent = $misc->get_agent_status($userdb_id);

        if ($is_admin == 'yes' && $is_agent == 'no') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * pclass link
     *
     * @param   integer $listingsdb_id
     * @return  string $pclass_link
     */
    public function pclass_link($listingsdb_id)
    {
        global $misc, $api;

        $pclass_link = '';
        $api_result = $api->load_local_api('listing__read', ['listing_id' => intval($listingsdb_id), 'fields' => ['listingsdb_pclass_id']]);
        if (!$api_result['error']) {
            $class_id = $api_result['listing']['listingsdb_pclass_id'];

            $api_result = $api->load_local_api('pclass__read', ['class_id' => intval($class_id)]);
            if ($api_result['error']) {
                //If an error occurs die and show the error msg;
                die($api_result['error_msg']);
            }
            $class_name = $api_result['class_name'];
            $pclass_link .= '<a href="index.php?action=searchresults&amp;pclass[]=' . $class_id . '" title="' . $class_name . '">' . $class_name . '</a>' . "\r\n";
        }
        return $pclass_link;
    }

    /**
     * create create yahoo school link link
     * (Now greatschools.org)
     * @param   string $url_only
     * @return  string $display
     */
    public function create_yahoo_school_link($url_only = 'no')
    {
        global $conn, $config, $lang, $misc;

        $display = '';
        $sql_listingID = intval($_GET['listingID']);

        $city_field = $config['map_city'];
        $state_field = $config['map_state'];
        $zip_field = $config['map_zip'];

        $sql_city_field = $misc->make_db_safe($city_field);
        $sql = 'SELECT listingsdbelements_field_value, listingsformelements_field_type, listingsformelements_field_caption 
				FROM ' . $config['table_prefix'] . 'listingsdbelements, ' . $config['table_prefix'] . 'listingsformelements 
				WHERE ((' . $config['table_prefix'] . "listingsdbelements.listingsdb_id = $sql_listingID) 
				AND (listingsformelements_field_name = listingsdbelements_field_name) 
				AND (listingsdbelements_field_name = $sql_city_field))";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $city = '';
        while (!$recordSet->EOF) {
            $city = $recordSet->fields('listingsdbelements_field_value');
            $recordSet->MoveNext();
        } // end while
        //Get State

        $sql_state_field = $misc->make_db_safe($state_field);
        $sql = 'SELECT listingsdbelements_field_value, listingsformelements_field_type, listingsformelements_field_caption 
				FROM ' . $config['table_prefix'] . 'listingsdbelements, ' . $config['table_prefix'] . 'listingsformelements 
				WHERE ((' . $config['table_prefix'] . "listingsdbelements.listingsdb_id = $sql_listingID) 
				AND (listingsformelements_field_name = listingsdbelements_field_name) 
				AND (listingsdbelements_field_name = $sql_state_field))";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $state = '';
        while (!$recordSet->EOF) {
            $state = $recordSet->fields('listingsdbelements_field_value');
            $recordSet->MoveNext();
        } // end while

        //Get Zip
        $sql_zip_field = $misc->make_db_safe($zip_field);
        $sql = 'SELECT listingsdbelements_field_value, listingsformelements_field_type, listingsformelements_field_caption 
				FROM ' . $config['table_prefix'] . 'listingsdbelements, ' . $config['table_prefix'] . 'listingsformelements 
				WHERE ((' . $config['table_prefix'] . "listingsdbelements.listingsdb_id = $sql_listingID) 
				AND (listingsformelements_field_name = listingsdbelements_field_name) 
				AND (listingsdbelements_field_name = $sql_zip_field))";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $zip = '';
        while (!$recordSet->EOF) {
            $zip = $recordSet->fields('listingsdbelements_field_value');
            $recordSet->MoveNext();
        } // end while
        //Build URL
        if ($city != '' && ($state != '' || $zip != '')) {
            if ($url_only == 'no') {
                $display = '<a href="https://www.greatschools.org/search/search.page?locationType=postal_code&amp;zipCode=' . $zip . '&amp;city=' . $city . '&amp;state=' . $state . '" onclick="window.open(this.href,\'_school\',\'location=0,status=0,scrollbars=1,toolbar=0,menubar=0,resizable=1,noopener,noreferrer\');return false">' . $lang['school_profile'] . '</a>';
            } else {
                $display = 'https://www.greatschools.org/cgi-bin/byaddr/pa?biid=&amp;zip=' . $zip . '&amp;city' . $city . '&amp;stateselect=' . $state;
            }
        }
        return $display;
    }


    /**
     * create bestplaces neighborhood link
     *
     * @param   string $url_only
     * @return  string $display
     */
    public function create_bestplaces_neighborhood_link($url_only = 'no')
    {
        global $conn, $config, $lang, $misc;

        $listingsdb_id = intval($_GET['listingID']);
        $display = '';
        $city = '';
        $zip = '';
        $city = $this->get_listing_single_value($config['map_city'], $listingsdb_id);
        $zip = $this->get_listing_single_value($config['map_zip'], $listingsdb_id);

        if ($zip != '') {
            if ($url_only == 'no') {
                $display = '<a href="https://www.bestplaces.net/search/?q=' . $zip . '" onclick="window.open(this.href,\'_neighborhood\',\'location=0,status=0,scrollbars=1,toolbar=0,menubar=0,resizable=1,noopener,noreferrer\');return false">' . $lang['neighborhood_profile'] . '</a>';
            } else {
                $display = 'https://www.bestplaces.net/search/?q=' . $zip;
            }
        } elseif ($city != '') {
            if ($url_only == 'no') {
                $display = '<a href="https://www.bestplaces.net/search/?q=' . $city . '" onclick="window.open(this.href,\'_neighborhood\',\'location=0,status=0,scrollbars=1,toolbar=0,menubar=0,resizable=1,noopener,noreferrer\');return false">' . $lang['neighborhood_profile'] . '</a>';
            } else {
                $display = 'https://www.bestplaces.net/search/?q=' . $city;
            }
        }
        return $display;
    }

    /**
     * create printer friendly link
     *
     * @param   string $url_only
     * @return  string $display
     */
    public function create_email_friend_link($url_only = 'no')
    {
        global $lang, $config;

        if (isset($_GET['listingID'])) {
            $listingsdb_id = intval($_GET['listingID']);
        }
        if ($url_only == 'no') {
            $display = '<a href="' . $config['baseurl'] . '/index.php?action=contact_friend&amp;popup=yes&amp;listing_id=' . $listingsdb_id . '" onclick="window.open(this.href,\'_blank\',\'location=0,status=0,scrollbars=1,toolbar=0,menubar=0,width=500,height=520\');return false">' . $lang['email_listing_link'] . '</a>';
        } else {
            $display = $config['baseurl'] . '/index.php?action=contact_friend&amp;popup=yes&amp;listing_id=' . $listingsdb_id . '';
        }
        return $display;
    }

    /**
     * create printer friendly link
     *
     * @param   string $url_only
     * @return  string $display
     */
    public function create_printer_friendly_link($url_only = 'no')
    {
        global $lang, $config;
        if (isset($_GET['listingID'])) {
            $listingsdb_id = intval($_GET['listingID']);
            if ($url_only == 'no') {
                $display = '<a href="' . $config['baseurl'] . '/index.php?action=listingview&amp;listingID=' . $listingsdb_id . '&amp;printer_friendly=yes" rel="nofollow">' . $lang['printer_version_link'] . '</a>';
            } else {
                $display = $config['baseurl'] . '/index.php?action=listingview&amp;listingID=' . $listingsdb_id . '&amp;printer_friendly=yes';
            }
        } else {
            // Save GET
            $guidestring = '';
            foreach ($_GET as $k => $v) {
                if ($v && $k != 'PHPSESSID' && $k != 'printer_friendly') {
                    if (is_array($v)) {
                        foreach ($v as $vitem) {
                            $guidestring .= '&amp;' . urlencode($k) . '[]=' . urlencode($vitem);
                        }
                    } else {
                        $guidestring .= '&amp;' . urlencode($k) . '=' . urlencode($v);
                    }
                }
            }
            if ($url_only == 'no') {
                $display = '<a href="' . $config['baseurl'] . '/index.php?' . $guidestring . '&amp;printer_friendly=yes" rel="nofollow">' . $lang['printer_version_link'] . '</a>';
            } else {
                $display = $config['baseurl'] . '/index.php?' . $guidestring . '&amp;printer_friendly=yes';
            }
        }
        return $display;
    }

    /**
     * create calc link
     *
     * @param   string $url_only
     * @return  string $display
     */
    public function create_calc_link($url_only = 'no')
    {
        global $lang, $config;
        if ($url_only == 'no') {
            if (isset($_GET['listingID'])) {
                $listingsdb_id = intval($_GET['listingID']);
                $display = '<a href="' . $config['baseurl'] . '/index.php?action=calculator&amp;popup=yes&amp;price=' . $this->renderSingleListingItem($listingsdb_id, $config['price_field'], 'rawvalue') . '" onclick="window.open(this.href,\'_blank\',\'location=0,status=0,scrollbars=1,toolbar=0,menubar=0,width=620,height=560\');return false" rel="nofollow">' . $lang['mortgage_calculator_link'] . '</a>';
            } else {
                $display = '<a href="' . $config['baseurl'] . '/index.php?action=calculator&amp;popup=yes" onclick="window.open(this.href,\'_blank\',\'location=0,status=0,scrollbars=1,toolbar=0,menubar=0,width=620,height=560\');return false" rel="nofollow">' . $lang['mortgage_calculator_link'] . '</a>';
            }
        } else {
            if (isset($_GET['listingID'])) {
                $listingsdb_id = intval($_GET['listingID']);
                $display = $config['baseurl'] . '/index.php?action=calculator&amp;popup=yes&amp;price=' . $this->renderSingleListingItem($listingsdb_id, $config['price_field'], 'rawvalue');
            } else {
                $display = $config['baseurl'] . '/index.php?action=calculator&amp;popup=yes';
            }
        }
        return $display;
    }

    /**
     * create add favorite link
     *
     * @param   string $url_only
     * @return  string $display
     */
    public function create_add_favorite_link($url_only = 'no')
    {
        global $lang, $current_ID, $config;

        if ($current_ID != '') {
            $_GET['listingID'] = intval($current_ID);
        }
        if ($url_only == 'no') {
            $listingsdb_id = intval($_GET['listingID']);
            $display = '<a href="' . $config['baseurl'] . '/index.php?action=addtofavorites&amp;listingID=' . $listingsdb_id . '" rel="nofollow">' . $lang['add_favorites_link'] . '</a>';
        } else {
            $listingsdb_id = intval($_GET['listingID']);
            $display = $config['baseurl'] . '/index.php?action=addtofavorites&amp;listingID=' . $listingsdb_id . '';
        }
        return $display;
    }

    /**
     * qr code link
     *
     * @param   integer $listingsdb_id
     * @return  string $display
     */
    public function qr_code_link($listingsdb_id)
    {
        global $config;
        $listingsdb_id = intval($listingsdb_id);
        $link = $config['baseurl'] . '/index.php?action=listingqrcode&listing_id=' . $listingsdb_id;
        return $link;
    }

    /**
     * qr code (generate)
     *
     * @param   integer $listingsdb_id
     * @return  string $display
     */
    public function qr_code($listingsdb_id)
    {
        global $config;
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $writer = new PngWriter();
        $listingsdb_id = intval($listingsdb_id);
        $listingURL = $page->magicURIGenerator('listing', $listingsdb_id, true);
        $qrCode = QrCode::create($listingURL)
            ->setEncoding(new Encoding('UTF-8'));
        $result = $writer->write($qrCode);
        header('Content-Type: ' . $result->getMimeType());
        echo $result->getString();
    }

    /**
     * contact agent link
     *
     * @param   string $url_only
     * @return  string $display
     */
    public function contact_agent_link($url_only = 'no')
    {
        global $config, $lang;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $listingsdb_id = intval($_GET['listingID']);
        $url = $page->magicURIGenerator('contact_listing_agent', $listingsdb_id, true);
        if ($url_only == 'no') {
            $display = '<a href="' . $url . '" >' . $lang['contact_agent'] . '</a>';
        } else {
            $display = $url;
        }
        return $display;
    }

    /**
     * edit listing link
     *
     * @param   string $url_only
     * @return  string $display
     */
    public function edit_listing_link($url_only = 'no')
    {
        global $lang, $config, $current_ID;
        $display = '';
        //Get the listing ID
        if ($current_ID != '') {
            $_GET['listingID'] = $current_ID;
        }
        if (isset($_GET['listingID'])) {
            $listingsdb_id = intval($_GET['listingID']);

            $listingagentid = $this->get_listing_agent_value('userdb_id', $listingsdb_id);

            if (isset($_SESSION['userID'])) {
                $userid = $_SESSION['userID'];
                if ((isset($_SESSION['edit_all_listings']) && $_SESSION['edit_all_listings'] == 'yes') || (isset($_SESSION['admin_privs']) && $_SESSION['admin_privs'] == 'yes') || (isset($_SESSION['isAgent']) && $_SESSION['isAgent'] == 'yes' && ($listingagentid == $userid))) {
                    $edit_link = $config['baseurl'] . '/admin/index.php?action=edit_listing&amp;edit=' . $listingsdb_id;
                } else {
                    return;
                }
                if ($url_only == 'yes') {
                    $display = $edit_link;
                } else {
                    $display = '<a href="' . $edit_link . '">' . $lang['edit_listing'] . '</a>';
                }
            }
        }
        return $display;
    }

    /**
     * get featured (status)
     *
     * @param   integer $listingsdb_id
     * @return  integer $featured
     */
    public function get_featured($listing_id, $raw)
    {
        $listingsdb_id = intval($listing_id);
        $featured = $this->get_listing_single_value('listingsdb_featured', $listingsdb_id);
        if ($raw == 'no') {
            if ($featured == 'yes') {
                $featured = 'featured';
            } else {
                $featured = '';
            }
        }
        return $featured;
    }

    /**
     * Get listing single value (replaces several get_user_xx() functions)
     *
     * @param   string $field_name (e.g. listingsdb_pclass_id)
     *          integer $listingsdb_id
     * @return  string $display
     */
    public function get_listing_single_value($field_name, $listingsdb_id)
    {
        global $misc, $api;

        $display = '';
        $listingsdb_id = intval($listingsdb_id);
        if ($listingsdb_id !== 0 && $field_name !== '') {
            $result = $api->load_local_api('listing__read', [
                'listing_id' => $listingsdb_id,
                'fields' => [
                    $field_name,
                ],
            ]);
            if (!$result['error']) {
                if (isset($result['listing']) && isset($result['listing'][$field_name])) {
                    $display .= $result['listing'][$field_name];
                }
            }
            return $display;
        } else {
            return '';
        }
    }

    /**
     * Get listing agent value (replaces several get_Listing_xx() functions)
     *
     * @param   string $field_name (e.g. userdb_user_first_name)
     *          integer $userdb_id
     * @return  string $display
     */
    public function get_listing_agent_value($field_name, $listingsdb_id)
    {
        global $api;

        $display = '';
        $listingsdb_id = intval($listingsdb_id);

        $result = $api->load_local_api('listing__read', ['listing_id' => $listingsdb_id, 'fields' => ['userdb_id']]);
        if (!$result['error']) {
            $userdb_id = $result['listing']['userdb_id'];
            //if we just wanted the userdb_id return it now
            if ($field_name == 'userdb_id') {
                return $userdb_id;
            }

            $result_data = $api->load_local_api('user__read', ['user_id' => $userdb_id, 'resource' => 'agent', 'fields' => [$field_name]]);
            if (!$result_data['error']) {
                $display = $result_data['user'][$field_name];
            }
        }

        return $display;
    }

    /**
     * Listing Hit count
     *
     * @param   integer $listingsdb_id
     * @return  integer $hit_count
     */
    public function hitcount($listingsdb_id)
    {
        // hit counter for user listings
        global $api, $lang, $config, $conn, $misc;

        $display = '';
        $hit_count = '';
        $listingsdb_id = intval($listingsdb_id);

        $result = $api->load_local_api('listing__read', [
            'listing_id' => $listingsdb_id,
            'fields' => [
                'listingsdb_hit_count',
                'listingsdb_pclass_id',
            ],
        ]);
        if (!$result['error']) {
            $pclass_id = $result['listing']['listingsdb_pclass_id'];
            $hit_count = $result['listing']['listingsdb_hit_count'];
            $hit_count = $hit_count + 1;

            /* can't use this, site visitors have no permission to update listing data
            $update = $api->load_local_api('listing__update',array(
                'class_id'=> $pclass_id,
                'listing_id' => $listingsdb_id,
                'listing_details'=>array(
                    'hit_count' => $hit_count
                )
            ));
            */
            $sql = 'UPDATE ' . $config['table_prefix'] . 'listingsdb
				SET 
                listingsdb_hit_count=listingsdb_hit_count+1
				WHERE listingsdb_id=' . $listingsdb_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
        }
        return $hit_count;
    }


    /******************* DEPRECATED BELOW *************************************/

    /**
     * get get_pclass_id  (deprecated)
     *
     * @param   integer $listingsdb_id
     * @return  string $pclass_id
     */
    public function get_pclass_id($listingsdb_id)
    {
        $pclass_id = $this->get_listing_single_value('listingsdb_pclass_id', $listingsdb_id);
        return $pclass_id;
    }

    /**
     * Get Title (deprecated)
     *
     * @param   integer $listing_id
     * @return  string $listingsdb_title
     */
    public function get_title($listingsdb_id)
    {
        $listingsdb_title = $this->get_listing_single_value('listingsdb_title', $listingsdb_id);
        return $listingsdb_title;
    }

    /**
     * Get Listing Agent First Name (deprecated)
     *
     * @param   integer $listing_id
     * @return  string $display
     */
    public function getListingAgentFirstName($listingID)
    {
        global $conn, $config, $misc;

        $listingID = intval($listingID);
        $sql = 'SELECT userdb_user_first_name
				FROM ' . $config['table_prefix'] . 'listingsdb, ' . $config['table_prefix'] . "userdb
				WHERE ((listingsdb_id = $listingID)
				AND (" . $config['table_prefix'] . 'userdb.userdb_id = ' . $config['table_prefix'] . 'listingsdb.userdb_id))';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        // get main listings data
        $display = '';
        while (!$recordSet->EOF) {
            $listing_user_name = $recordSet->fields('userdb_user_first_name');
            $recordSet->MoveNext();
        } // end while
        $display .= $listing_user_name;
        return $display;
    }

    /**
     * Get Listing Agent Last Name (deprecated)
     *
     * @param   integer $listing_id
     * @return  string $display
     */
    public function getListingAgentLastName($listingID)
    {
        global $conn, $config, $misc;

        // get the main data for a given listing
        $listingID = intval($listingID);
        $sql = 'SELECT userdb_user_last_name
				FROM ' . $config['table_prefix'] . 'listingsdb, ' . $config['table_prefix'] . "userdb
				WHERE ((listingsdb_id = $listingID)
				AND (" . $config['table_prefix'] . 'userdb.userdb_id = ' . $config['table_prefix'] . 'listingsdb.userdb_id))';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        // get main listings data
        $display = '';
        while (!$recordSet->EOF) {
            $listing_user_name = $recordSet->fields('userdb_user_last_name');
            $recordSet->MoveNext();
        } // end while
        $display .= $listing_user_name;
        return $display;
    }

    /**
     * Get Listing Agent (deprecated)
     *
     * @param   integer $listing_id
     * @return  string $display
     */
    public function getListingAgent($listingID)
    {
        global $conn, $config, $misc;

        // get the main data for a given listing
        $listingID = intval($listingID);
        $sql = 'SELECT userdb_user_name
				FROM ' . $config['table_prefix'] . 'listingsdb, ' . $config['table_prefix'] . "userdb
				WHERE ((listingsdb_id = $listingID)
				AND (" . $config['table_prefix'] . 'userdb.userdb_id = ' . $config['table_prefix'] . 'listingsdb.userdb_id))';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        // get main listings data
        $display = '';
        while (!$recordSet->EOF) {
            $listing_user_name = $recordSet->fields('userdb_user_name');
            $recordSet->MoveNext();
        } // end while
        $display .= $listing_user_name;
        return $display;
    }

    /**
     * Get Listing Agent ID (deprecated)
     *
     * @param   integer $listing_id
     * @return  string $display
     */
    public function getListingAgentID($listing_id)
    {
        global $conn, $config, $misc;

        // get the main data for a given listing
        $listingID = intval($listing_id);
        //$listingID = mysql_real_escape_string($listing_id);
        $sql = 'SELECT ' . $config['table_prefix'] . "listingsdb.userdb_id
				FROM $config[table_prefix]listingsdb
				WHERE listingsdb_id = $listingID";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        // get main listings data
        $display = '';
        $listing_user_ID = '';
        while (!$recordSet->EOF) {
            $listing_user_ID = $recordSet->fields('userdb_id');
            $recordSet->MoveNext();
        } // end while
        $display .= $listing_user_ID;
        return $display;
    }

    /**
     * Get Listing Agent Link (deprecated)
     *
     * @param   integer $listingsdb_id
     * @return  string $display
     */
    public function getListingAgentLink($listingsdb_id)
    {
        global $conn, $config, $misc;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $listingsdb_id = intval($listingsdb_id);
        $userdb_id = $this->get_listing_agent_value('userdb_id', $listingsdb_id);
        $display .= $page->magicURIGenerator('view_agents', $userdb_id, true);
        return $display;
    }

    /**
     * Get listing email (deprecated)
     *
     * @param   integer $listingsdb_id
     *          boolean $value_only
     * @return  string $display
     */
    public function getListingEmail($listingsdb_id, $value_only = false)
    {
        global $lang, $config, $misc, $api;

        $listingsdb_id = intval($listingsdb_id);

        $result = $api->load_local_api('listing__read', ['listing_id' => $listingsdb_id, 'fields' => ['userdb_id']]);
        if (!$result['error']) {
            $userdb_id = $result['listing']['userdb_id'];

            $result_data = $api->load_local_api('user__read', ['user_id' => $userdb_id, 'resource' => 'agent', 'fields' => ['userdb_emailaddress']]);
            if (!$result_data['error']) {
                $agent_email = $result_data['user']['userdb_emailaddress'];
            }
        }

        if ($value_only === true) {
            $display = $agent_email;
        } else {
            $display = '<strong>' . $lang['user_email'] . ':</strong> <a href="mailto:' . $agent_email . '">' . $agent_email . '</a><br />';
        }

        return $display;
    }
}
