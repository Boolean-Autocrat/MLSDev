<?php

/**
 * This File Contains the Listing API Commands
 * @package Open-Realty
 * @subpackage API
 * @author Ryan C. Bonham
 * @copyright 2010

 * @link http://www.open-realty.com Open-Realty
 */

/**
 * This is the listings API, it contains all api calls for creating and retrieving listing data.
 *
 * @package Open-Realty
 * @subpackage API
 **/
class listing_api
{
    protected $OR_INT_FIELDS = ['listingsdb_id', 'listingsdb_pclass_id', 'userdb_id', 'listingsdb_title', 'listingsdb_expiration', 'listingsdb_notes', 'listingsdb_creation_date', 'listingsdb_last_modified', 'listingsdb_hit_count', 'listingsdb_featured', 'listingsdb_active', 'listingsdb_mlsexport', 'listing_seotitle'];
    /**
     * This API Command searches the listings
     * @param array $data $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['parameters'] - This is a REQUIRED array of the fields and the values we are searching for.</li>
     *      <li>$data['sortby'] - This is an optional array of fields to sort by.</li>
     **     <li>$data['sorttype'] - This is an optional array of sort types (ASC/DESC) to sort the sortby fields by.</li>
     *      <li>$data['offset'] - This is an optional integer of the number of listings to offset the search by. To use offset you must set a limit.</li>
     *      <li>$data['limit'] - This is an optional integer of the number of listings to limit the search by. 0 or unset will return all listings.</li>
     *      <li>$data['count_only'] - This is an optional integer flag 1/0, where 1 returns a record count only, defaults to 0 if not set. Usefull if doing limit/offset search for pagenation to get the inital full record count..</li>
     *  </ul>
     * @return array  - Array retruned will contain the following paramaters.
     *  [error] = TRUE/FASLE
     *  [listing_count] = Number of records found, if using a limit this is only the number of records that match your current limit/offset results.
     *  [listings] = Array of listing IDs.
     *  [info] = The info array contains benchmark information on the search, including process_time, query_time, and total_time
     *  [sortby] = Contains an array of fields that were used to sort the search results. Note if you are doing a CountONly search the sort is not actually used as this would just slow down the query.
     *  [sorttype] = Contains an array of the sorttype (ASC/DESC) used on the sortby fields.
     **/
    public function search($data)
    {
        $DEBUG_SQL = false;
        global $conn, $lapi, $config, $lang, $db_type, $misc;

        $start_time = $misc->getmicrotime();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed
        if (!isset($parameters) || !is_array($parameters)) {
            return ['error' => true, 'error_msg' => 'parameters: correct_parameter_not_passed'];
        }
        if (isset($sortby) && !is_array($sortby)) {
            return ['error' => true, 'error_msg' => 'sortby: correct_parameter_not_passed'];
        }
        if (isset($sorttype) && !is_array($sorttype)) {
            return ['error' => true, 'error_msg' => 'sorttype: correct_parameter_not_passed'];
        }
        if (isset($offset) && !is_numeric($offset)) {
            return ['error' => true, 'error_msg' => 'offset: correct_parameter_not_passed'];
        }
        if (isset($limit) && !is_numeric($limit)) {
            return ['error' => true, 'error_msg' => 'limit: correct_parameter_not_passed'];
        }
        if (isset($count_only) && $count_only == 1) {
            $count_only = true;
        } else {
            $count_only = false;
        }
        if (!isset($limit)) {
            $limit=0;
        }
        $searchresultSQL = '';
        // Set Default Search Options
        $imageonly = false;
        $vtoursonly = false;
        $tablelist = [];
        $tablelist_fullname = [];
        $postalcode_dist_lat = '';
        $postalcode_dist_long = '';
        $postalcode_dist_dist = '';
        $latlong_dist_lat = '';
        $latlong_dist_long = '';
        $latlong_dist_dist = '';
        $city_dist_lat = '';
        $city_dist_long = '';
        $city_dist_dist = '';
        $login_status = $login->verify_priv('Agent');
        $string_where_clause = '';
        $string_where_clause_nosort = '';
        if ($login_status !== true || !isset($parameters['listingsdb_active'])) {
            //If we are not an agent only show active listings, or if user did not specify show only actives by default.
            $parameters['listingsdb_active'] = 'yes';
        }
        if ($login_status !== true && $config['use_expiration'] == 1) {
            $parameters['listingsdb_expiration_greater'] = time();
            unset($parameters['listingsdb_expiration_less']);
        }
        //Loop through search paramaters
        foreach ($parameters as $k => $v) {
            //Search Listings By Agent
            if ($k == 'user_ID') {
                if ($v != '' && $v != 'Any Agent') {
                    if (is_array($v)) {
                        $sstring = '';
                        foreach ($v as $u) {
                            $u = intval($u);
                            if (empty($sstring)) {
                                $sstring .=  $config['table_prefix'] . 'listingsdb.userdb_id = ' . $u;
                            } else {
                                $sstring .=  ' OR ' . $config['table_prefix'] . 'listingsdb.userdb_id = ' . $u;
                            }
                        }
                        if ($searchresultSQL != '') {
                            $searchresultSQL .= ' AND ';
                        }
                        $searchresultSQL .=  '(' . $sstring . ')';
                    } else {
                        $sql_v = intval($v);
                        if ($searchresultSQL != '') {
                            $searchresultSQL .= ' AND ';
                        }
                        $searchresultSQL .= '(' . $config['table_prefix'] . 'listingsdb.userdb_id = ' . $sql_v . ')';
                    }
                }
            } elseif ($k == 'listingsdb_active') {
                if ($string_where_clause != '') {
                    $string_where_clause .= ' AND ';
                }
                if ($string_where_clause_nosort != '') {
                    $string_where_clause_nosort .= ' AND ';
                }
                if ($v == 'no') {
                    $string_where_clause .= '(' . $config['table_prefix'] . 'listingsdb.listingsdb_active = \'no\')';
                    $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'listingsdb.listingsdb_active = \'no\')';
                } elseif ($v == 'any') {
                    $string_where_clause .= '(' . $config['table_prefix'] . 'listingsdb.listingsdb_active = \'yes\' or ' . $config['table_prefix'] . 'listingsdb.listingsdb_active = \'no\')';
                    $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'listingsdb.listingsdb_active = \'yes\' or ' . $config['table_prefix'] . 'listingsdb.listingsdb_active = \'no\')';
                } else {
                    $string_where_clause .= '(' . $config['table_prefix'] . 'listingsdb.listingsdb_active = \'yes\')';
                    $string_where_clause_nosort  .= '(' . $config['table_prefix'] . 'listingsdb.listingsdb_active = \'yes\')';
                }
            } elseif ($k == 'listingsdb_expiration_greater') {
                if ($string_where_clause != '') {
                    $string_where_clause .= ' AND ';
                }
                if ($string_where_clause_nosort != '') {
                    $string_where_clause_nosort .= ' AND ';
                }
                $string_where_clause .= ' (listingsdb_expiration > ' . $conn->DBDate($v) . ')';
                $string_where_clause_nosort .= ' (listingsdb_expiration > ' . $conn->DBDate($v) . ')';
            } elseif ($k == 'listingsdb_expiration_less') {
                if ($string_where_clause != '') {
                    $string_where_clause .= ' AND ';
                }
                if ($string_where_clause_nosort != '') {
                    $string_where_clause_nosort .= ' AND ';
                }
                $string_where_clause .= ' (listingsdb_expiration < ' . $conn->DBDate($v) . ')';
                $string_where_clause_nosort .= ' (listingsdb_expiration < ' . $conn->DBDate($v) . ')';
            } elseif ($k == 'featuredOnly') {
                if ($v == 'yes') {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $searchresultSQL = $searchresultSQL . '(' . $config['table_prefix'] . 'listingsdb.listingsdb_featured = \'yes\')';
                }
            } elseif ($k == 'pclass') {
                $class_sql = '';
                foreach ($v as $class) {
                    // Ignore non numberic values
                    if (is_numeric($class)) {
                        if (!empty($class_sql)) {
                            $class_sql .= ' OR ';
                        }
                        $class_sql .= $config['table_prefix'] . 'listingsdb.listingsdb_pclass_id = ' . $class;
                    }
                }
                if (!empty($class_sql)) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $searchresultSQL .= '(' . $class_sql . ')';
                    //$searchresultSQL = $searchresultSQL . '(' . $class_sql . ') AND ' . $config['table_prefix_no_lang'] . 'classlistingsdb.listingsdb_id = ' . $config['table_prefix'] . 'listingsdb.listingsdb_id';
                    //$tablelist_fullname[] = $config['table_prefix_no_lang'] . 'classlistingsdb';
                }
            } elseif ($k == 'listing_id') {
                $listing_id = explode(',', $v);
                $i = 0;
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
                foreach ($listing_id as $id) {
                    $id = intval($id);
                    if ($i == 0) {
                        $searchresultSQL .= '((' . $config['table_prefix'] . 'listingsdb.listingsdb_id = ' . $id . ')';
                    } else {
                        $searchresultSQL .= ' OR (' . $config['table_prefix'] . 'listingsdb.listingsdb_id = ' . $id . ')';
                    }
                    $i++;
                }
                $searchresultSQL .= ')';
            } elseif ($k == 'imagesOnly') {
                // Grab only listings with images if that is what we need.
                if ($v == 'yes') {
                    $imageonly = true;
                }
            } elseif ($k == 'vtoursOnly') {
                // Grab only listings with images if that is what we need.
                if ($v == 'yes') {
                    $vtoursonly = true;
                }
            } elseif ($k == 'listingsdb_title') {
                $safe_v = '%' . $conn->addQ($v) . '%';
                if ($string_where_clause != '') {
                    $string_where_clause .= ' AND ';
                }
                if ($string_where_clause_nosort != '') {
                    $string_where_clause_nosort .= ' AND ';
                }
                $string_where_clause .= '(' . $config['table_prefix'] . 'listingsdb.listingsdb_title LIKE \'' . $safe_v . '\')';
                $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'listingsdb.listingsdb_title LIKE \'' . $safe_v . '\')';
            } elseif ($k == 'listing_last_modified_equal') {
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
                $safe_v = $conn->DBTimeStamp($v);
                $searchresultSQL .= ' listingsdb_last_modified = ' . $safe_v;
            //listingsdb_last_modified
            } elseif ($k == 'listing_last_modified_greater') {
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
                $safe_v = $conn->DBTimeStamp($v);
                $searchresultSQL .= ' listingsdb_last_modified > ' . $safe_v;
            //listingsdb_last_modified
            } elseif ($k == 'listing_last_modified_less') {
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
                $safe_v = $conn->DBTimeStamp($v);
                $searchresultSQL .= ' listingsdb_last_modified < ' . $safe_v;
            //listingsdb_last_modified
            } elseif ($k == 'listingsdb_creation_date_equal') {
                //$v = intval($v);
                if ($v > 0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $safe_v = $conn->DBDate($v);
                    $searchresultSQL .= ' listingsdb_creation_date = ' . $safe_v;
                    //listingsdb_last_modified
                }
            } elseif ($k == 'listingsdb_creation_date_greater') {
                if ($v > 0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $safe_v = $conn->DBDate($v);
                    $searchresultSQL .= ' listingsdb_creation_date > ' . $safe_v;
                    //listingsdb_last_modified
                }
            } elseif ($k == 'listingsdb_creation_date_less') {
                //$v = intval($v);
                if ($v > 0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $safe_v = $conn->DBDate($v);
                    $searchresultSQL .= ' listingsdb_creation_date < ' . $safe_v;
                    //listingsdb_last_modified
                }
            } elseif ($k == 'listingsdb_creation_date_equal_days') {
                $v = intval($v);
                if ($v > 0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $time = mktime(0, 0, 0, date('m'), date('d') - intval($v), date('Y'));
                    $safe_v = $conn->DBTimeStamp($time);
                    $searchresultSQL .= ' listingsdb_creation_date = ' . $safe_v;
                    //listingsdb_last_modified
                }
            } elseif ($k == 'listingsdb_creation_date_greater_days') {
                $v = intval($v);
                if ($v > 0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $time = mktime(0, 0, 0, date('m'), date('d') - intval($v), date('Y'));
                    $safe_v = $conn->DBTimeStamp($time);
                    ;
                    $searchresultSQL .= ' listingsdb_creation_date > ' . $safe_v;
                }
            //listingsdb_last_modified
            } elseif ($k == 'listingsdb_creation_date_less_days') {
                $v = intval($v);
                if ($v > 0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $time = mktime(0, 0, 0, date('m'), date('d') - intval($v), date('Y'));
                    $safe_v = $conn->DBTimeStamp($time);
                    $searchresultSQL .= ' listingsdb_creation_date < ' . $safe_v;
                }
            //listingsdb_last_modified
            } elseif ($k == 'latlong_dist_lat' || $k == 'latlong_dist_long' || $k == 'latlong_dist_dist' && $v != '') {
                switch ($k) {
                    case 'latlong_dist_lat':
                        $latlong_dist_lat = $v;
                        break;
                    case 'latlong_dist_long':
                        $latlong_dist_long = $v;
                        break;
                    case 'latlong_dist_dist':
                        $latlong_dist_dist = $v;
                        break;
                }
            } elseif ($k == 'postalcode_dist_code' && $v != '') {
                $postalcode = $misc->make_db_safe($v);
                $sql = 'SELECT zipdist_latitude, zipdist_longitude 
						FROM ' . $config['table_prefix_no_lang'] . 'zipdist 
						WHERE zipdist_zipcode = ' . $postalcode;
                $postalcode_recordSet = $conn->Execute($sql);
                if (!$postalcode_recordSet) {
                    $misc->log_error($sql);
                }
                $postalcode_dist_lat = floatval($postalcode_recordSet->fields('zipdist_latitude'));
                $postalcode_dist_long = floatval($postalcode_recordSet->fields('zipdist_longitude'));
            } elseif ($k == 'postalcode_dist_dist' && $v != '') {
                $postalcode_dist_dist = floatval($v);
            } elseif ($k == 'city_dist_code' && $v != '') {
                $city = $misc->make_db_safe($v);
                $sql = 'SELECT zipdist_latitude, zipdist_longitude FROM ' . $config['table_prefix_no_lang'] . 'zipdist WHERE zipdist_cityname = ' . $city;
                $city_recordSet = $conn->Execute($sql);
                if ($city_recordSet === false) {
                    $misc->log_error($sql);
                }
                $city_dist_lat = floatval($city_recordSet->fields('zipdist_latitude'));
                $city_dist_long = floatval($city_recordSet->fields('zipdist_longitude'));
            } elseif ($k == 'city_dist_dist' && $v != '') {
                $city_dist_dist = $v;
            } elseif (
                $v != '' && $k != 'listingID' && $k != 'postalcode_dist_code' && $k != 'postalcode_dist_dist' && $k != 'city_dist_code' && $k != 'city_dist_dist'
                && $k != 'latlong_dist_lat' && $k != 'latlong_dist_long' && $k != 'latlong_dist_dist' && $k != 'cur_page' && $k != 'action' && $k != 'PHPSESSID'
                && $k != 'sortby' && $k != 'sorttype' && $k != 'printer_friendly' && $k != 'template' && $k != 'pclass' && $k != 'listing_last_modified_less'
                && $k != 'listing_last_modified_equal' && $k != 'listing_last_modified_greater' && $k != 'listingsdb_creation_date_equal'
                && $k != 'listingsdb_creation_date_greater' && $k != 'listingsdb_creation_date_less' && $k != 'listingsdb_creation_date_equal_days'
                && $k != 'listingsdb_creation_date_greater_days' && $k != 'listingsdb_creation_date_less_days' && $k != 'x' && $k != 'y' && $k != 'listingsdb_expiration_greater'
                && $k != 'listingsdb_expiration_less' && $k != 'listingsdb_active' && $k != 'listingsdb_title' && $k != 'popup'
            ) {
                if (!is_array($v)) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }

                    //Handle NULL/NOTNULL Searches
                    if (substr($k, -5) == '-NULL' && $v == '1') {
                        $subk = substr($k, 0, -5);
                        $searchresultSQL .= "(`$subk`.listingsdbelements_field_name = '$subk' AND (`$subk`.listingsdbelements_field_value IS NULL OR `$subk`.listingsdbelements_field_value = ''))";
                        if (!in_array($subk, $tablelist)) {
                            $tablelist[] = $subk;
                        }
                    } elseif (substr($k, -8) == '-NOTNULL' && $v == '1') {
                        $subk = substr($k, 0, -8);
                        $searchresultSQL .= "(`$subk`.listingsdbelements_field_name = '$subk' AND (`$subk`.listingsdbelements_field_value IS NOT NULL  AND `$subk`.listingsdbelements_field_value <> ''))";
                        if (!in_array($subk, $tablelist)) {
                            $tablelist[] = $subk;
                        }
                    }
                    //Handle Min/Max Searches
                    elseif (substr($k, -4) == '-max') {
                        $subk = $conn->addQ(substr($k, 0, -4));
                        $safe_subk =  $misc->make_db_safe(substr($k, 0, -4));
                        $safe_v = $misc->make_db_safe($v);
                        $sql_file_type = 'SELECT listingsformelements_field_type FROM ' . $config['table_prefix'] . 'listingsformelements WHERE listingsformelements_field_name = ' . $safe_subk;
                        $recordSet_field_type = $conn->Execute($sql_file_type);
                        if (!$recordSet_field_type) {
                            $error = $conn->ErrorMsg();
                            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->search', 'log_message' => 'DB Error: ' . $error]);
                            return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                        }
                        $field_type = $recordSet_field_type->fields('listingsformelements_field_type');
                        if ($db_type == 'mysql' || $db_type == 'mysqli' || $db_type == 'pdo') {
                            if ($field_type == 'lat' || $field_type == 'long') {
                                $searchresultSQL .= "(`$subk`.listingsdbelements_field_name = $safe_subk AND CAST(`$subk`.listingsdbelements_field_value as DECIMAL(13,7)) <= $safe_v)";
                            } elseif ($field_type == 'decimal') {
                                $searchresultSQL .= "(`$subk`.listingsdbelements_field_name = $safe_subk AND CAST(`$subk`.listingsdbelements_field_value as DECIMAL(64,6)) <='$safe_v)";
                            } else {
                                $searchresultSQL .= "(`$subk`.listingsdbelements_field_name = $safe_subk AND CAST(`$subk`.listingsdbelements_field_value as signed) <= $safe_v)";
                            }
                        } else {
                            $searchresultSQL .= "(`$subk`.listingsdbelements_field_name = $safe_subk AND CAST(`$subk`.listingsdbelements_field_value as int4) <= $safe_v)";
                        }

                        if (!in_array($subk, $tablelist)) {
                            $tablelist[] = $subk;
                        }
                    } elseif (substr($k, -4) == '-min') {
                        $subk = $conn->addQ(substr($k, 0, -4));
                        $safe_subk =  $misc->make_db_safe(substr($k, 0, -4));
                        $safe_v = $misc->make_db_safe($v);
                        $sql_file_type = 'SELECT listingsformelements_field_type 
										FROM ' . $config['table_prefix'] . 'listingsformelements 
										WHERE listingsformelements_field_name = ' . $safe_subk;
                        $recordSet_field_type = $conn->Execute($sql_file_type);
                        if (!$recordSet_field_type) {
                            $error = $conn->ErrorMsg();
                            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->search', 'log_message' => 'DB Error: ' . $error]);
                            return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                        }
                        $field_type = $recordSet_field_type->fields('listingsformelements_field_type');
                        if ($db_type == 'mysql' || $db_type == 'mysqli' || $db_type == 'pdo') {
                            if ($field_type == 'lat' || $field_type == 'long') {
                                $searchresultSQL .= "(`$subk`.listingsdbelements_field_name = $safe_subk AND CAST(`$subk`.listingsdbelements_field_value as DECIMAL(13,7)) >= $safe_v)";
                            } elseif ($field_type == 'decimal') {
                                $searchresultSQL .= "(`$subk`.listingsdbelements_field_name = $safe_subk AND CAST(`$subk`.listingsdbelements_field_value as DECIMAL(64,6)) >= $safe_v)";
                            } else {
                                $searchresultSQL .= "(`$subk`.listingsdbelements_field_name = $safe_subk AND CAST(`$subk`.listingsdbelements_field_value as signed) >= $safe_v)";
                            }
                        } else {
                            $searchresultSQL .= "(`$subk`.listingsdbelements_field_name = $safe_subk AND CAST(`$subk`.listingsdbelements_field_value as int4) >= $safe_v)";
                        }
                        if (!in_array($subk, $tablelist)) {
                            $tablelist[] = $subk;
                        }
                    } elseif (substr($k, -8) == '-maxdate') {
                        if ($config['date_format'] == 1) {
                            $format = '%m/%d/%Y';
                        } elseif ($config['date_format'] == 2) {
                            $format = '%Y/%d/%m';
                        } elseif ($config['date_format'] == 3) {
                            $format = '%d/%m/%Y';
                        }
                        $v = $misc->parseDate($v, $format);
                        $safe_v = $misc->make_db_safe($v);
                        $subk = $conn->addQ(urldecode(substr($k, 0, -8)));
                        $safe_subk = $misc->make_db_safe(urldecode(substr($k, 0, -8)));
                        $searchresultSQL .= "(`$subk`.listingsdbelements_field_name = $safe_subk AND `$subk`.listingsdbelements_field_value <= $safe_v)";
                        if (!in_array($subk, $tablelist)) {
                            $tablelist[] = $subk;
                        }
                    } elseif (substr($k, -8) == '-mindate') {
                        if ($config['date_format'] == 1) {
                            $format = '%m/%d/%Y';
                        } elseif ($config['date_format'] == 2) {
                            $format = '%Y/%d/%m';
                        } elseif ($config['date_format'] == 3) {
                            $format = '%d/%m/%Y';
                        }
                        $v = $misc->parseDate($v, $format);
                        $subk = urldecode(substr($k, 0, -8));
                        $searchresultSQL .= "(`$subk`.listingsdbelements_field_name = $safe_subk AND `$subk`.listingsdbelements_field_value >= $safe_v)";
                        if (!in_array($subk, $tablelist)) {
                            $tablelist[] = $subk;
                        }
                    } elseif (substr($k, -5) == '-date') {
                        if ($config['date_format'] == 1) {
                            $format = '%m/%d/%Y';
                        } elseif ($config['date_format'] == 2) {
                            $format = '%Y/%d/%m';
                        } elseif ($config['date_format'] == 3) {
                            $format = '%d/%m/%Y';
                        }
                        $v = $misc->parseDate($v, $format);
                        $subk = urldecode(substr($k, 0, -5));
                        $searchresultSQL .= "(`$subk`.listingsdbelements_field_name = $safe_subk AND `$subk`.listingsdbelements_field_value = $safe_v)";
                        if (!in_array($subk, $tablelist)) {
                            $tablelist[] = $subk;
                        }
                    } elseif ($k == 'searchtext') {
                        $safe_v = $conn->addQ($v);
                        $searchresultSQL .= "((`$k`.listingsdbelements_field_value like '%$safe_v%') OR (listingsdb_title like '%$safe_v%'))";
                        $tablelist[] = $k;
                    } else {
                        $safe_k = $conn->addQ($k);
                        $safe_v = $conn->addQ($v);
                        $searchresultSQL .= "(`$safe_k`.listingsdbelements_field_name = '$safe_k' AND `$safe_k`.listingsdbelements_field_value = '$safe_v')";
                        $tablelist[] = $safe_k;
                    }
                } else {
                    // Make Sure Array is not empty
                    $use = false;
                    $comma_separated = implode(' ', $v);
                    if (trim($comma_separated) != '') {
                        $use = true;
                        if ($searchresultSQL != '') {
                            $searchresultSQL .= ' AND ';
                        }
                    }
                    if ($use === true) {
                        if (substr($k, -3) == '_or') {
                            $k = substr($k, 0, strlen($k) - 3);
                            $safe_k = $conn->addQ($k);
                            $searchresultSQL .= "(`$safe_k`.listingsdbelements_field_name = '$safe_k' AND (";
                            $vitem_count = 0;
                            foreach ($v as $vitem) {
                                $safe_vitem = $conn->addQ($vitem);
                                if ($vitem != '') {
                                    if ($vitem_count != 0) {
                                        $searchresultSQL .= " OR `$safe_k`.listingsdbelements_field_value LIKE '%$safe_vitem%'";
                                    } else {
                                        $searchresultSQL .= " `$safe_k`.listingsdbelements_field_value LIKE '%$safe_vitem%'";
                                    }
                                    $vitem_count++;
                                }
                            }
                            $searchresultSQL .= '))';
                            $tablelist[] = $safe_k;
                        } else {
                            $safe_k = $conn->addQ($k);
                            $searchresultSQL .= "(`$safe_k`.listingsdbelements_field_name = '$safe_k' AND (";
                            $vitem_count = 0;
                            foreach ($v as $vitem) {
                                $safe_vitem = $conn->addQ($vitem);
                                if ($vitem != '') {
                                    if ($vitem_count != 0) {
                                        $searchresultSQL .= " AND `$safe_k`.listingsdbelements_field_value LIKE '%$safe_vitem%'";
                                    } else {
                                        $searchresultSQL .= " `$safe_k`.listingsdbelements_field_value LIKE '%$safe_vitem%'";
                                    }
                                    $vitem_count++;
                                }
                            }
                            $searchresultSQL .= '))';
                            $tablelist[] = $safe_k;
                        }
                    }
                }
            }
        }
        if ($postalcode_dist_lat != '' && $postalcode_dist_long != '' && $postalcode_dist_dist != '') {
            $sql = 'SELECT zipdist_zipcode
					FROM ' . $config['table_prefix_no_lang'] . 'zipdist
					WHERE (POW((69.1*(zipdist_longitude-"' . $postalcode_dist_long . '")*cos(' . $postalcode_dist_lat . '/57.3)),"2")+POW((69.1*(zipdist_latitude-"' . $postalcode_dist_lat . '")),"2"))<(' . $postalcode_dist_dist . '*' . $postalcode_dist_dist . ') ';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $zipcodes = [];
            while (!$recordSet->EOF) {
                $zipcodes[] = $recordSet->fields('zipdist_zipcode');
                $recordSet->MoveNext();
            }
            $pc_field_name = $config['map_zip'];
            // Build Search Query
            // Make Sure Array is not empty
            $use = false;
            $comma_separated = implode(' ', $zipcodes);
            if (trim($comma_separated) != '') {
                $use = true;
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
            }
            if ($use === true) {
                $searchresultSQL .= "(`$pc_field_name`.listingsdbelements_field_name = '$pc_field_name' AND (";
                $vitem_count = 0;
                foreach ($zipcodes as $vitem) {
                    $safe_vitem = $misc->make_db_safe($vitem);
                    if ($vitem != '') {
                        if ($vitem_count != 0) {
                            $searchresultSQL .= " OR `$pc_field_name`.listingsdbelements_field_value = $safe_vitem";
                        } else {
                            $searchresultSQL .= " `$pc_field_name`.listingsdbelements_field_value = $safe_vitem";
                        }
                        $vitem_count++;
                    }
                }
                $searchresultSQL .= '))';
                $tablelist[] = $pc_field_name;
            }
        }
        if ($city_dist_lat != '' && $city_dist_long != '' && $city_dist_dist != '') {
            $sql = "SELECT zipdist_zipcode FROM $config[table_prefix_no_lang]zipdist WHERE (POW((69.1*(zipdist_longitude-\"$city_dist_long\")*cos($city_dist_lat/57.3)),\"2\")+POW((69.1*(zipdist_latitude-\"$city_dist_lat\")),\"2\"))<($city_dist_dist*$city_dist_dist) ";
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $zipcodes = [];
            while (!$recordSet->EOF) {
                $zipcodes[] = $recordSet->fields('zipdist_zipcode');
                $recordSet->MoveNext();
            }
            $pc_field_name = $config['map_zip'];
            // Build Search Query
            // Make Sure Array is not empty
            $use = false;
            $comma_separated = implode(' ', $zipcodes);
            if (trim($comma_separated) != '') {
                $use = true;
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
            }
            if ($use === true) {
                $searchresultSQL .= "(`$pc_field_name`.listingsdbelements_field_name = '$pc_field_name' AND (";
                $vitem_count = 0;
                foreach ($zipcodes as $vitem) {
                    $safe_vitem = $misc->make_db_safe($vitem);
                    if ($vitem != '') {
                        if ($vitem_count != 0) {
                            $searchresultSQL .= " OR `$pc_field_name`.listingsdbelements_field_value = $safe_vitem";
                        } else {
                            $searchresultSQL .= " `$pc_field_name`.listingsdbelements_field_value = $safe_vitem";
                        }
                        $vitem_count++;
                    }
                }
                $searchresultSQL .= '))';
                $tablelist[] = $pc_field_name;
            }
        }
        //Lat Long Distance
        if ($latlong_dist_lat != '' && $latlong_dist_long != '' && $latlong_dist_dist != '') {
            /*
             max_lon = lon1 + arcsin(sin(D/R)/cos(lat1))
             min_lon = lon1 - arcsin(sin(D/R)/cos(lat1))
             max_lat = lat1 + (180/pi)(D/R)
             min_lat = lat1 - (180/pi)(D/R)
             $max_long = $latlong_dist_long + asin(sin($latlong_dist_dist/3956)/cos($latlong_dist_lat));
             $min_long = $latlong_dist_long - asin(sin($latlong_dist_dist/3956)/cos($latlong_dist_lat));
             $max_lat = $latlong_dist_lat + (180/pi())*($latlong_dist_dist/3956);
             $min_lat = $latlong_dist_lat - (180/pi())*($latlong_dist_dist/3956);
             /*
             Latitude:
             Apparently a degree of latitude expressed in miles does
             vary slighty by latitude

             (http://www.ncgia.ucsb.edu/education/curricula/giscc/units/u014/tables/table01.html)
             but for our purposes, I suggest we use 1 degree latitude

             = 69 miles.



             Longitude:
             This is more tricky one since it varies by latitude
             (http://www.ncgia.ucsb.edu/education/curricula/giscc/units/u014/tables/table02.html).
             The

             simplest formula seems to be:
             1 degree longitude expressed in miles = cos (latitude) *
             69.17 miles
             */
            //Get Correct Milage for ong based on lat.
            $cos_long = 69.17;
            if ($latlong_dist_lat >= 10) {
                $cos_long = 68.13;
            }
            if ($latlong_dist_lat >= 20) {
                $cos_long = 65.03;
            }
            if ($latlong_dist_lat >= 30) {
                $cos_long = 59.95;
            }
            if ($latlong_dist_lat >= 40) {
                $cos_long = 53.06;
            }
            if ($latlong_dist_lat >= 50) {
                $cos_long = 44.55;
            }
            if ($latlong_dist_lat >= 60) {
                $cos_long = 34.67;
            }
            if ($latlong_dist_lat >= 70) {
                $cos_long = 23.73;
            }
            if ($latlong_dist_lat >= 80) {
                $cos_long = 12.05;
            }
            if ($latlong_dist_lat >= 90) {
                $cos_long = 0;
            }
            $max_long = $latlong_dist_long + $latlong_dist_dist / (cos(deg2rad($latlong_dist_lat)) * $cos_long);
            $min_long = $latlong_dist_long - $latlong_dist_dist / (cos(deg2rad($latlong_dist_lat)) * $cos_long);
            $max_lat = $latlong_dist_lat + $latlong_dist_dist / 69;
            $min_lat = $latlong_dist_lat - $latlong_dist_dist / 69;
            //
            if ($max_lat < $min_lat) {
                $max_lat2 = $min_lat;
                $min_lat = $max_lat;
                $max_lat = $max_lat2;
            }
            if ($max_long < $min_long) {
                $max_long2 = $min_long;
                $min_long = $max_long;
                $max_long = $max_long2;
            }
            // Lat and Long Fields
            $sql = 'SELECT listingsformelements_field_name
					FROM ' . $config['table_prefix'] . 'listingsformelements
					WHERE listingsformelements_field_type  = \'lat\'';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $lat_field = $recordSet->fields('listingsformelements_field_name');
            $sql = 'SELECT listingsformelements_field_name
					FROM ' . $config['table_prefix'] . 'listingsformelements
					WHERE listingsformelements_field_type  = \'long\'';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $long_field =  $recordSet->fields('listingsformelements_field_name');
            if ($lat_field != '' & $long_field != '') {
                $tablelist[] = $lat_field;
                $tablelist[] = $long_field;
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
                $searchresultSQL .= "(`$lat_field`.listingsdbelements_field_name = '$lat_field' AND `$lat_field`.listingsdbelements_field_value+0 <= '$max_lat')";
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
                $searchresultSQL .= "(`$lat_field`.listingsdbelements_field_name = '$lat_field' AND `$lat_field`.listingsdbelements_field_value+0 >= '$min_lat')";
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
                $searchresultSQL .= "(`$long_field`.listingsdbelements_field_name = '$long_field' AND `$long_field`.listingsdbelements_field_value+0 <= '$max_long')";
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
                $searchresultSQL .= "(`$long_field`.listingsdbelements_field_name = '$long_field' AND `$long_field`.listingsdbelements_field_value+0 >= '$min_long')";
            }
        }
        // Handle Sorting
        // sort the listings
        // this is the main SQL that grabs the listings
        // basic sort by title..
        $group_order_text = '';
        $sortby_array = [];
        $sorttype_array = [];
        //Set array
        if (isset($sortby) && !empty($sortby)) {
            $sortby_array = $sortby;
        }
        if (isset($sorttype) && !empty($sorttype)) {
            $sorttype_array = $sorttype;
        }
        $sql_sort_type = '';
        $sort_text = '';
        $order_text = '';
        $group_order_text = '';
        $tablelist_nosort = $tablelist;
        $sort_count = count($sortby_array);
        for ($x = 0; $x < $sort_count; $x++) {
            if (!isset($sorttype_array[$x])) {
                $sorttype_array[$x] = '';
            } elseif ($sorttype_array[$x] != 'ASC' && $sorttype_array[$x] != 'DESC') {
                $sorttype_array[$x] = '';
            }
            if ($sortby_array[$x] == 'listingsdb_id') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY listingsdb_id ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',listingsdb_id ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'listingsdb_title') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY listingsdb_title ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',listingsdb_title ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'listingsdb_hit_count') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY listingsdb_hit_count ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',listingsdb_hit_count ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'random') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY rand() ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',rand() ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'listingsdb_featured') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY listingsdb_featured ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',listingsdb_featured ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'listingsdb_last_modified') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY listingsdb_last_modified ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',listingsdb_last_modified ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'pclass') {
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
                //$searchresultSQL .=  $config['table_prefix_no_lang'] . 'classlistingsdb.listingsdb_id = ' . $config['table_prefix'] . 'listingsdb.listingsdb_id AND '. $config['table_prefix_no_lang'] . 'classlistingsdb.class_id = '.$config['table_prefix'].'class.class_id ';
                //$tablelist_fullname[] = $config['table_prefix_no_lang'] . 'classlistingsdb';
                //$tablelist_fullname[] = $config['table_prefix'].'class';
                if ($x == 0) {
                    $order_text .= 'ORDER BY ' . $config['table_prefix'] . 'listingsdb.listingsdb_pclass_id ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',' . $config['table_prefix'] . 'listingsdb.listingsdb_pclass_id ' . $sorttype_array[$x];
                }
            } else {
                // Check if field is a number or price field and cast the order.
                $sort_by_field = $misc->make_db_safe($sortby_array[$x]);
                $sql_sort_type = 'SELECT listingsformelements_field_type FROM ' . $config['table_prefix'] . 'listingsformelements WHERE listingsformelements_field_name = ' . $sort_by_field;
                $recordSet_sort_type = $conn->Execute($sql_sort_type);
                if (!$recordSet_sort_type) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->search', 'log_message' => 'DB Error: ' . $error]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }
                $field_type = $recordSet_sort_type->fields('listingsformelements_field_type');
                if ($field_type == 'price' || $field_type == 'number') {
                    $tablelist[] = 'sort' . $x;
                    $sort_text .= 'AND (sort' . $x . '.listingsdbelements_field_name = ' . $sort_by_field . ') ';
                    if ($db_type == 'mysql' || $db_type == 'mysqli' || $db_type == 'pdo') {
                        if ($x == 0) {
                            $order_text .= ' ORDER BY CAST(sort' . $x . '.listingsdbelements_field_value as signed) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.listingsdbelements_field_value';
                        } else {
                            $order_text .= ',CAST(sort' . $x . '.listingsdbelements_field_value as signed) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.listingsdbelements_field_value';
                        }
                    } else {
                        if ($x == 0) {
                            $order_text .= ' ORDER BY CAST(sort' . $x . '.listingsdbelements_field_value as int4) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.listingsdbelements_field_value';
                        } else {
                            $order_text .= ',CAST(sort' . $x . '.listingsdbelements_field_value as int4) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.listingsdbelements_field_value';
                        }
                    }
                } elseif ($field_type == 'lat' || $field_type == 'long') {
                    $tablelist[] = 'sort' . $x;
                    $sort_text .= 'AND (sort' . $x . '.listingsdbelements_field_name = ' . $sort_by_field . ') ';
                    if ($db_type == 'mysql' || $db_type == 'mysqli' || $db_type == 'pdo') {
                        if ($x == 0) {
                            $order_text .= ' ORDER BY CAST(sort' . $x . '.listingsdbelements_field_value as decimal(13,7)) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.listingsdbelements_field_value';
                        } else {
                            $order_text .= ',CAST(sort' . $x . '.listingsdbelements_field_value as signed) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.listingsdbelements_field_value';
                        }
                    } else {
                        if ($x == 0) {
                            $order_text .= ' ORDER BY CAST(sort' . $x . '.listingsdbelements_field_value as int4) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.listingsdbelements_field_value';
                        } else {
                            $order_text .= ',CAST(sort' . $x . '.listingsdbelements_field_value as int4) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.listingsdbelements_field_value';
                        }
                    }
                } elseif ($field_type == 'decimal') {
                    $tablelist[] = 'sort' . $x;
                    $sort_text .= 'AND (sort' . $x . '.listingsdbelements_field_name = ' . $sort_by_field . ') ';
                    if ($db_type == 'mysql' || $db_type == 'mysqli' || $db_type == 'pdo') {
                        if ($x == 0) {
                            $order_text .= ' ORDER BY CAST(sort' . $x . '.listingsdbelements_field_value as decimal(64,6)) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.listingsdbelements_field_value';
                        } else {
                            $order_text .= ',CAST(sort' . $x . '.listingsdbelements_field_value as signed) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.listingsdbelements_field_value';
                        }
                    } else {
                        if ($x == 0) {
                            $order_text .= ' ORDER BY CAST(sort' . $x . '.listingsdbelements_field_value as int4) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.listingsdbelements_field_value';
                        } else {
                            $order_text .= ',CAST(sort' . $x . '.listingsdbelements_field_value as int4) ' . $sorttype_array[$x];
                            $group_order_text .= ',sort' . $x . '.listingsdbelements_field_value';
                        }
                    }
                } else {
                    $tablelist[] = 'sort' . $x;
                    $sort_text .= 'AND (sort' . $x . '.listingsdbelements_field_name = ' . $sort_by_field . ') ';
                    if ($x == 0) {
                        $order_text .= ' ORDER BY sort' . $x . '.listingsdbelements_field_value ' . $sorttype_array[$x];
                    } else {
                        $order_text .= ', sort' . $x . '.listingsdbelements_field_value ' . $sorttype_array[$x];
                    }
                    $group_order_text .= ',sort' . $x . '.listingsdbelements_field_value';
                }
            }
        }
        $group_order_text = $group_order_text . ' ' . $order_text;

        if ($imageonly == true || $vtoursonly == true) {
            $order_text = 'GROUP BY ' . $config['table_prefix'] . 'listingsdb.listingsdb_id, ' . $config['table_prefix'] . 'listingsdb.listingsdb_title ' . $group_order_text;
        }
        if ($DEBUG_SQL) {
            echo '<strong>Sort Type SQL:</strong> ' . $sql_sort_type . '<br />';
            echo '<strong>Sort Text:</strong> ' . $sort_text . '<br />';
            echo '<strong>Order Text:</strong> ' . $order_text . '<br />';
        }

        //$guidestring_with_sort = $guidestring_with_sort . $guidestring;
        // End of Sort
        $arrayLength = count($tablelist);
        if ($DEBUG_SQL) {
            echo '<strong>Table List Array Length:</strong> ' . $arrayLength . '<br />';
        }
        $string_table_list = '';
        for ($i = 0; $i < $arrayLength; $i++) {
            $string_table_list .= ' ,' . $config['table_prefix'] . 'listingsdbelements `' . $tablelist[$i] . '`';
        }
        $arrayLength = count($tablelist_nosort);
        $string_table_list_no_sort = '';
        for ($i = 0; $i < $arrayLength; $i++) {
            $string_table_list_no_sort .= ' ,' . $config['table_prefix'] . 'listingsdbelements `' . $tablelist[$i] . '`';
        }
        $arrayLength = count($tablelist_fullname);
        if ($DEBUG_SQL) {
            echo '<strong>Table List Array Length:</strong> ' . $arrayLength . '<br />';
        }
        for ($i = 0; $i < $arrayLength; $i++) {
            $string_table_list .= ' ,' . $tablelist_fullname[$i];
            $string_table_list_no_sort .= ' ,' . $tablelist_fullname[$i];
        }

        if ($DEBUG_SQL) {
            echo '<strong>Table List String:</strong> ' . $string_table_list . '<br />';
        }
        $arrayLength = count($tablelist);
        for ($i = 0; $i < $arrayLength; $i++) {
            if ($string_where_clause != '') {
                $string_where_clause .= ' AND ';
            }
            $string_where_clause .= ' (' . $config['table_prefix'] . 'listingsdb.listingsdb_id = `' . $tablelist[$i] . '`.listingsdb_id)';
        }
        $arrayLength = count($tablelist_nosort);
        for ($i = 0; $i < $arrayLength; $i++) {
            if ($string_where_clause_nosort != '') {
                $string_where_clause_nosort .= ' AND ';
            }
            $string_where_clause_nosort .= ' (' . $config['table_prefix'] . 'listingsdb.listingsdb_id = `' . $tablelist[$i] . '`.listingsdb_id)';
        }

        if ($imageonly) {
            $searchSQL = 'SELECT distinct(' . $config['table_prefix'] . 'listingsdb.listingsdb_id), ' . $config['table_prefix'] . 'listingsdb.userdb_id,
						' . $config['table_prefix'] . 'listingsdb.listingsdb_title FROM ' . $config['table_prefix'] . 'listingsdb,
						' . $config['table_prefix'] . 'listingsimages ' . $string_table_list . '
						WHERE ' . $string_where_clause . '
						AND (' . $config['table_prefix'] . 'listingsimages.listingsdb_id = ' . $config['table_prefix'] . 'listingsdb.listingsdb_id)';

            $searchSQLCount = 'SELECT COUNT(distinct(' . $config['table_prefix'] . 'listingsdb.listingsdb_id)) as total_listings
						FROM ' . $config['table_prefix'] . 'listingsdb, ' . $config['table_prefix'] . 'listingsimages ' . $string_table_list_no_sort . '
						WHERE ' . $string_where_clause_nosort . '
						AND (' . $config['table_prefix'] . 'listingsimages.listingsdb_id = ' . $config['table_prefix'] . 'listingsdb.listingsdb_id)';
        } elseif ($vtoursonly) {
            $searchSQL = 'SELECT distinct(' . $config['table_prefix'] . 'listingsdb.listingsdb_id), ' . $config['table_prefix'] . 'listingsdb.userdb_id,
						' . $config['table_prefix'] . 'listingsdb.listingsdb_title
						FROM ' . $config['table_prefix'] . 'listingsdb, ' . $config['table_prefix'] . 'listingsvtours ' . $string_table_list . '
						WHERE ' . $string_where_clause . '
						AND (' . $config['table_prefix'] . 'listingsvtours.listingsdb_id = ' . $config['table_prefix'] . 'listingsdb.listingsdb_id)';

            $searchSQLCount = 'SELECT COUNT(distinct(' . $config['table_prefix'] . 'listingsdb.listingsdb_id)) as total_listings
						FROM ' . $config['table_prefix'] . 'listingsdb, ' . $config['table_prefix'] . 'listingsvtours ' . $string_table_list_no_sort . '
						WHERE ' . $string_where_clause_nosort . '
						AND (' . $config['table_prefix'] . 'listingsvtours.listingsdb_id = ' . $config['table_prefix'] . 'listingsdb.listingsdb_id) ';
        } else {
            $searchSQL = 'SELECT distinct(' . $config['table_prefix'] . 'listingsdb.listingsdb_id)
						FROM ' . $config['table_prefix'] . 'listingsdb ' . $string_table_list . '
						WHERE ' . $string_where_clause;
            $searchSQLCount = 'SELECT COUNT(distinct(' . $config['table_prefix'] . 'listingsdb.listingsdb_id)) as total_listings
						FROM ' . $config['table_prefix'] . 'listingsdb ' . $string_table_list_no_sort . '
						WHERE ' . $string_where_clause_nosort;
            /*$searchSQL = 'SELECT distinct(' . $config['table_prefix'] . 'listingsdb.listingsdb_id), ' . $config['table_prefix'] . 'listingsdb.userdb_id,
             ' . $config['table_prefix'] . 'listingsdb.listingsdb_title
             FROM ' . $config['table_prefix'] . 'listingsdb ' . $string_table_list . '
             WHERE (listingsdb_active = \'yes\') ' . $string_where_clause;
             $searchSQLCount = 'SELECT COUNT(distinct(' . $config['table_prefix'] . 'listingsdb.listingsdb_id)) as total_listings
             FROM ' . $config['table_prefix'] . 'listingsdb ' . $string_table_list_no_sort . '
             WHERE (listingsdb_active = \'yes\') ' . $string_where_clause_nosort;
             */
        }
        if ($searchresultSQL != '') {
            $searchSQL .= ' AND ' . $searchresultSQL;
            $searchSQLCount .= ' AND ' . $searchresultSQL;
        }

        $sql = $searchSQL . ' ' . $sort_text . ' ' . $order_text;
        if ($count_only) {
            $sql = $searchSQLCount;
        }
        //$searchSQLCount = $searchSQLCount;
        // We now have a complete SQL Query. Now grab the results
        $process_time = $misc->getmicrotime();
        //  echo 'Limit: '.$limit;
        //  echo 'Offset: '.$offset;
        if ($limit > 0) {
            $recordSet = $conn->SelectLimit($sql, $limit, $offset);
        } else {
            $recordSet = $conn->Execute($sql);
        }
        //$recordSet = $conn->GetAll($sql);
        $query_time = $misc->getmicrotime();
        $query_time = $query_time - $process_time;
        $process_time = $process_time - $start_time;
        if ($DEBUG_SQL) {
            echo '<strong>Search Query:</strong> ' . $sql . '<br />';
        }
        if (!$recordSet) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->search', 'log_message' => 'DB Error: ' . $error . ' Full SQL: ' . $sql]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
        }
        $listings_found = [];
        //print_r($recordSet);
        /*
        foreach($recordSet as $row){
        //print_r($row);die;
        $listings_found[]=$row['listingsdb_id'];
        }
        $listing_count=count($listings_found);
        */
        if ($count_only) {
            $listing_count = $recordSet->fields('total_listings');
        } else {
            $listing_count = $recordSet->RecordCount();
        }
        if (!$count_only) {
            while (!$recordSet->EOF) {
                $listings_found[] = $recordSet->fields('listingsdb_id');
                $recordSet->MoveNext();
            }
        }
        $total_time = $misc->getmicrotime();
        $total_time = $total_time - $start_time;
        $info['process_time'] = sprintf('%.3f', $process_time);
        $info['query_time'] = sprintf('%.3f', $query_time);
        $info['total_time'] = sprintf('%.3f', $total_time);
        return ['error' => false, 'listing_count' => $listing_count, 'listings' => $listings_found, 'info' => $info, 'sortby' => $sortby_array, 'sorttype' => $sorttype_array, 'limit' => $limit];
    }

    private function convert_date($date, $format)
    {
        global $config;
        //Supported formats
        //%Y - year as a decimal number including the century
        //%m - month as a decimal number (range 01 to 12)
        //%d - day of the month as a decimal number (range 01 to 31)
        //%H - hour as a decimal number using a 24-hour clock (range 00 to 23)
        //%M - minute as a decimal number
        // Builds up date pattern from the given $format, keeping delimiters in place.
        //echo 'Date: "'.$date.'"';
        //echo 'Format "'.$format.'"';
        if (!preg_match_all('/%([YmdHMp])([^%])*/', $format, $formatTokens, PREG_SET_ORDER)) {
            //return 'BAD FORMAT';
            return false;
        }
        $datePattern = '';
        //echo '<pre>'.print_r($formatTokens,TRUE).'</pre>';
        foreach ($formatTokens as $formatToken) {
            if (isset($formatToken[2])) {
                $delimiter = preg_quote($formatToken[2], '/');
            } else {
                $delimiter = '';
            }

            $datePattern .= '(.*)' . $delimiter;
        }
        // Splits up the given $date
        if (!preg_match('/' . $datePattern . '/', $date, $dateTokens)) {
            //return 'BAD SPLIT';
            return false;
        }
        $dateSegments = [];
        $formatTokenCount = count($formatTokens);
        for ($i = 0; $i < $formatTokenCount; $i++) {
            $dateSegments[$formatTokens[$i][1]] = $dateTokens[$i + 1];
        }
        // Reformats the given $date into US English date format, suitable for strtotime()
        if ($dateSegments['Y'] && $dateSegments['m'] && $dateSegments['d']) {
            $dateReformated = $dateSegments['Y'] . '-' . $dateSegments['m'] . '-' . $dateSegments['d'];
        } else {
            //return 'BAD DATE';
            return false;
        }
        if (isset($dateSegments['H']) && isset($dateSegments['M'])) {
            $dateReformated .= ' ' . $dateSegments['H'] . ':' . $dateSegments['M'];
        }

        return strtotime($dateReformated);
    }

    public function get_statistics($data)
    {
        global $conn, $lapi, $config, $lang, $misc;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed
        $class_sql = '';
        $pclass_list = [];
        if (isset($pclass) && !is_array($pclass)) {
            return ['error' => true, 'error_msg' => 'class_id: correct_parameter_not_passed'];
        } elseif (isset($pclass) && is_array($pclass)) {
            foreach ($pclass as $class_id) {
                if ($class_id > 0) {
                    $class_sql .= ' AND ' . $config['table_prefix'] . 'listingsdb.listingsdb_pclass_id = ' . intval($class_id);
                    $pclass_list[] = $class_id;
                }
            }
        }
        if (!isset($function) || !in_array($function, ['max', 'min', 'avg', 'median', 'mode', 'range'])) {
            return ['error' => true, 'error_msg' => 'function: correct_parameter_not_passed'];
        }
        if (!isset($field_name)) {
            return ['error' => true, 'error_msg' => 'field_name: correct_parameter_not_passed'];
        }
        $numformat = false;
        if (isset($format) && is_bool($format)) {
            $numformat = $format;
        }
        //Get Field List
        $field_list = $lapi->load_local_api('fields__metadata', ['resource' => 'listing', 'class' => $pclass_list]);
        if ($field_list['error']) {
            return ['error' => true, 'error_msg' => $field_list['error_msg']];
        }
        $field_found = false;
        foreach ($field_list['fields'] as $field) {
            $fname = $field['field_name'];
            if ($field_name == $fname) {
                $browse_field_name = $misc->make_db_safe($fname);
                $field_found = true;
                $field_type = $field['field_type'];
                if ($field_type == 'decimal') {
                    $field_sql = 'listingsdbelements_field_value+0';
                } else {
                    $field_sql = 'CAST(listingsdbelements_field_value as signed)';
                }
                break;
            }
        }
        $value = null;
        if ($field_found) {
            $field_list = $config['table_prefix'] . 'listingsdbelements, ' . $config['table_prefix'] . 'listingsdb WHERE
			' . $config['table_prefix'] . 'listingsdbelements.listingsdb_id = ' . $config['table_prefix'] . 'listingsdb.listingsdb_id';
            switch ($function) {
                case 'max':
                case 'min':
                    $minmax_rs = $conn->Execute('SELECT max(' . $field_sql . ') as max, min(' . $field_sql . ') as min
							FROM ' . $field_list . '
							AND listingsdbelements_field_name = ' . $browse_field_name . '' . $class_sql);
                    $max = $minmax_rs->fields('max');
                    $min = $minmax_rs->fields('min');
                    if ($numformat) {
                        if ($field_type == 'price') {
                            $max = $misc->money_formats($misc->international_num_format($max, $config['number_decimals_price_fields']));
                            $min = $misc->money_formats($misc->international_num_format($min, $config['number_decimals_price_fields']));
                        } elseif ($field_type == 'number') {
                            $max = $misc->international_num_format($max, $config['number_decimals_number_fields']);
                            $min = $misc->international_num_format($min, $config['number_decimals_number_fields']);
                        }
                    }
                    return ['error' => false, 'min' => $min, 'max' => $max];

                case 'avg':
                    $minmax_rs = $conn->Execute('SELECT AVG(' . $field_sql . ') as avg
							FROM ' . $field_list . '
							AND listingsdbelements_field_name = ' . $browse_field_name . '' . $class_sql);
                    $avg = $minmax_rs->fields('avg');
                    if ($numformat) {
                        if ($field_type == 'price') {
                            $avg = $misc->money_formats($misc->international_num_format($avg, $config['number_decimals_price_fields']));
                        } elseif ($field_type == 'number') {
                            $avg = $misc->international_num_format($avg, $config['number_decimals_number_fields']);
                        }
                    }
                    return ['error' => false, 'avg' => $avg];
                case 'median':
                    $minmax_rs = $conn->Execute('SELECT COUNT(' . $field_sql . ') as count
					FROM ' . $field_list . '
					AND listingsdbelements_field_name = ' . $browse_field_name . '' . $class_sql);
                    $count = $minmax_rs->fields('count');
                    $minmax_rs = $conn->Execute('SELECT ' . $field_sql . ' as fvalue
							FROM ' . $field_list . '
							AND listingsdbelements_field_name = ' . $browse_field_name . '' . $class_sql);
                    $varray = [];
                    while (!$minmax_rs->EOF) {
                        $varray[] = $minmax_rs->fields('fvalue');
                        $minmax_rs->MoveNext();
                    }
                    if (empty($varray)) {
                        $median = 0;
                    } else {
                        $middle = round($count / 2);
                        $middle--;
                        $median = $varray[$middle];
                    }

                    if ($numformat) {
                        if ($field_type == 'price') {
                            $median = $misc->money_formats($misc->international_num_format($median, $config['number_decimals_price_fields']));
                        } elseif ($field_type == 'number') {
                            $median = $misc->international_num_format($median, $config['number_decimals_number_fields']);
                        }
                    }

                    return ['error' => false, 'median' => $median];
            }
        } else {
            return ['error' => true, 'error_msg' => 'field not found'];
        }
    }

    /**
     * This API Command creates listings.
     * @param array $data $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['class_id'] - This should be an integer of the class_id that this listing is assigned to.</li>
     *      <li>$data['listing_details'] - This should be an array containg the following three settings.</li>
     *      <li>$data['listing_details']['title'] - Required. This is the title for the listing.</li>
     *      <li>$data['listing_details']['seotitle'] - Optional. If not set the API will create a seo friendly title based on the title supplied. The API will ensure teh seotitle is unique, so the seotitle you supply will be modified if needed..</li>
     *      <li>$data['listing_details']['notes'] - Options - notes about this listing, only visible to admin and agents.</li>
     *      <li>$data['listing_details']['featured'] - Required Boolean - Is this a featured listings. TRUE/FALSE</li>
     *      <li>$data['listing_details']['active'] - - Required Boolean - Is this a active listings. TRUE/FALSE</li>
     *      <li>$data['listing_agents'] - This should be an array of up to agent ids. This sets the listing agent ID, the primary listing agent ID must be key 0.
     *      <code>$data['listing_agents'][0]=5; //This lising belongs to agent 5. All other keys are currently ignored.</code></li>
     *      <li>$data['listing_fields'] - This should be an array of the actual listing data. The array keys should be the field name and the array values should be the field values. Only valid fields will be used, other data will be dropped.
     *      <code>$data['listing_fields'] =array('mls_id' => 126,'address'=>'126 E Buttler Ave');  // This example defines a field value of 126 for a field called mls_id and a value of "126 E Buttler Ave" for the address field.</code></li>
     *      <li>$data['listing_media'] - Currently not used and MUST be an empty array.</li>
     *  </ul>
     * @return array
     *
     */
    public function create($data)
    {
        global $conn, $lapi, $config, $lang, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->verify_priv('Agent');
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure'];
        }
        extract($data, EXTR_SKIP || EXTR_REFS, '');

        //Check that required settings were passed
        if (!isset($class_id) || !is_numeric($class_id)) {
            return ['error' => true, 'error_msg' => 'class_id: correct_parameter_not_passed'];
        }
        if (!isset($listing_details) || !is_array($listing_details)) {
            return ['error' => true, 'error_msg' => 'listing_details: correct_parameter_not_passed'];
        }
        if (!isset($listing_details['title']) || empty($listing_details['title'])) {
            return ['error' => true, 'error_msg' => '$listing_details[title]: correct_parameter_not_passed'];
        }
        if (!isset($listing_details['featured']) || !is_bool($listing_details['featured'])) {
            return ['error' => true, 'error_msg' => '$listing_details[featured]: correct_parameter_not_passed'];
        }
        if (!isset($listing_details['active']) || !is_bool($listing_details['active'])) {
            return ['error' => true, 'error_msg' => '$listing_details[active]: correct_parameter_not_passed'];
        }

        if (!isset($listing_agents) || !is_array($listing_agents) || count($listing_agents) < 1) {
            return ['error' => true, 'error_msg' => 'listing_agents: correct_parameter_not_passed'];
        }
        if (!isset($listing_fields) || !is_array($listing_fields)) {
            return ['error' => true, 'error_msg' => 'listing_fields: correct_parameter_not_passed'];
        }
        if (isset($or_int_disable_log) && !is_bool($or_int_disable_log)) {
            return ['error' => true, 'error_msg' => 'or_int_disable_log: correct_parameter_not_passed'];
        }
        //Allow override of date format set in OR.
        //%Y/%d/%m
        if (!isset($or_date_format)) {
            $or_date_format = $config['date_to_timestamp'];
        }
        //Strip Tags from Titles
        if (isset($listing_details['title'])) {
            $listing_details['title'] = strip_tags($listing_details['title']);
        }
        if (isset($listing_details['seotitle'])) {
            $listing_details['seotitle'] = strip_tags($listing_details['seotitle']);
        }

        //Ok we have all the needed variables so now we bulid the listings.
        $listing_details['creation_date'] = time();
        $listing_details['last_modified'] = time();
        $listing_details['expiration'] = time() + ($config['days_until_listings_expire'] * 86400);

        if (!isset($listing_details['seotitle'])) {
            //Make SEO Title
            if ($config['controlpanel_mbstring_enabled'] == 0) {
                // MBSTRING NOT ENABLED
                $listing_details['seotitle'] = strtolower($listing_details['title']);
            } else {
                $listing_details['seotitle'] = mb_convert_case($listing_details['title'], MB_CASE_LOWER, $config['charset']);
            }
            $listing_details['seotitle'] = trim($listing_details['seotitle']);
            $listing_details['seotitle'] = preg_replace('/[\~`!@#\$%^*\(\)\+=\"\':;\[\]\{\}|\\\?\<\>,\.\/]/', '', $listing_details['seotitle']);
            $listing_details['seotitle'] = str_replace(' ', $config['seo_url_seperator'], $listing_details['seotitle']);
            $listing_details['seotitle'] = preg_replace('/[\-]+/', '-', $listing_details['seotitle']);
        }
        //Verify Permissions
        // Check Number of Listings User has
        $listing_agent = intval($listing_agents[0]);
        //Verify we have permissions to add a listing
        if ($_SESSION['userID'] == $listing_agent) {
            $security = $login->verify_priv('Agent');
            if ($security !== true) {
                return ['error' => true, 'error_msg' => 'Permission Denied (agent)'];
            }
        } else {
            //Check tha we can add listing for other users
            $security = $login->verify_priv('edit_all_listings');
            if ($security !== true) {
                return ['error' => true, 'error_msg' => 'Permission Denied (edit_all_listings)'];
            }
        }

        $sql = 'SELECT count(listingsdb_id) as listing_count FROM ' . $config['table_prefix'] . 'listingsdb WHERE userdb_id = ' . $listing_agent;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->create', 'log_message' => 'DB Error: ' . $error]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
        }
        $listing_count = $recordSet->fields('listing_count');
        // Get User Listing Limit
        $sql = 'SELECT userdb_limit_listings FROM ' . $config['table_prefix'] . 'userdb WHERE userdb_id = ' . $listing_agent;

        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->create', 'log_message' => 'DB Error: ' . $error]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
        }
        $listing_limit = $recordSet->fields('userdb_limit_listings');

        //Ok Decide if user can have more listings
        if (($listing_count >= $listing_limit) && ($listing_limit != '-1')) {
            return ['error' => true, 'error_msg' => 'Listing Limit Reached'];
        } else {
            $sql_title = $misc->make_db_safe($listing_details['title']);
            $sql_seotitle = $misc->make_db_safe($listing_details['seotitle']);
            $sql_notes = $misc->make_db_safe($listing_details['notes']);
            $class_id = intval($class_id);
            if ($listing_details['featured']) {
                $featured = $misc->make_db_safe('yes');
            } else {
                $featured = $misc->make_db_safe('no');
            }
            if ($listing_details['active']) {
                $active = $misc->make_db_safe('yes');
            } else {
                $active = $misc->make_db_safe('no');
            }

            //INSERT LISTING DETAILS
            $sql = 'INSERT INTO ' . $config['table_prefix'] . 'listingsdb
				(listingsdb_title,listing_seotitle,listingsdb_expiration,listingsdb_notes,listingsdb_hit_count,
				listingsdb_featured,listingsdb_active,listingsdb_creation_date,listingsdb_last_modified,userdb_id,listingsdb_pclass_id)
				VALUES' . "($sql_title,$sql_seotitle," . $conn->DBTimeStamp($listing_details['expiration']) . ",$sql_notes,0,
				$featured,$active," . $conn->DBDate($listing_details['creation_date']) . ',' . $conn->DBTimeStamp($listing_details['last_modified']) . ",$listing_agent,$class_id);";
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->create', 'log_message' => 'DB Error: ' . $error]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
            }

            $listing_id = $conn->Insert_ID();
            //Make sure title is unique
            $sql = 'SELECT listingsdb_id 
					FROM ' . $config['table_prefix'] . 'listingsdb 
					WHERE listing_seotitle = ' . $sql_seotitle;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->create', 'log_message' => 'DB Error: ' . $error]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
            }

            if ($recordSet->RecordCount() > 1) {
                $listing_details['seotitle'] = $listing_details['seotitle'] . '-' . $listing_id;
                $save_seotitle =  $misc->make_db_safe($listing_details['seotitle']);
                $sql = 'UPDATE ' . $config['table_prefix'] . "listingsdb 
						SET listing_seotitle = $save_seotitle 
						WHERE listingsdb_id = " . $listing_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->create', 'log_message' => 'DB Error: ' . $error]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }
            }

            // INSERT Listing Fields
            $sql = 'SELECT listingsformelements_default_text, listingsformelements_field_name,lfe.listingsformelements_id,listingsformelements_field_type, listingsformelements_field_elements
					FROM  ' . $config['table_prefix'] . 'listingsformelements as lfe 
					LEFT JOIN ' . $config['table_prefix_no_lang'] . 'classformelements  as cfe
					ON lfe.listingsformelements_id = cfe.listingsformelements_id
					WHERE class_id = ' . $class_id;

            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->create', 'log_message' => 'DB Error: ' . $error]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
            }
            //Verify Listing Fields passed via API exist in the class that the listing is being inserted into.
            $insert_listing_fields = [];
            while (!$recordSet->EOF) {
                $name = $recordSet->fields('listingsformelements_field_name');
                $id = $recordSet->fields('listingsformelements_id');
                $data_type = $recordSet->fields('listingsformelements_field_type');
                $data_elements = $recordSet->fields('listingsformelements_field_elements');
                $default_text = $recordSet->fields('listingsformelements_default_text');
                if (array_key_exists($name, $listing_fields)) {
                    switch ($data_type) {
                        case 'number':
                            if ($listing_fields[$name] === '') {
                                $insert_listing_fields[$name] = null;
                            } else {
                                if (is_numeric($listing_fields[$name])) {
                                    $insert_listing_fields[$name] = $listing_fields[$name];
                                } else {
                                    $price = str_replace(',', '', $listing_fields[$name]);
                                    $insert_listing_fields[$name] = intval($price);
                                }
                            }
                            break;
                        case 'decimal':
                        case 'price':
                            if ($listing_fields[$name] === '') {
                                $insert_listing_fields[$name] = null;
                            } else {
                                $price = str_replace(',', '', $listing_fields[$name]);
                                $insert_listing_fields[$name] = (float)$price;
                            }
                            break;
                        case 'date':
                            if ($listing_fields[$name] === '') {
                                $insert_listing_fields[$name] = null;
                            } else {
                                $insert_listing_fields[$name] = $this->convert_date($listing_fields[$name], $or_date_format);
                            }
                            break;
                        case 'select':
                        case 'select-multiple':
                        case 'option':
                        case 'checkbox':
                            //This is a lookup field. Make sure values passed are allowed by the system.
                            //Get Array of allowed data elements
                            $data_elements_array = explode('||', $data_elements);
                            //Get array of passed data eleements
                            if (!is_array($listing_fields[$name])) {
                                $t_value = $listing_fields[$name];
                                unset($listing_fields[$name]);
                                $listing_fields[$name][] = $t_value;
                            }
                            $good_elements = [];
                            foreach ($listing_fields[$name] as $fvalue) {
                                if (in_array($fvalue, $data_elements_array) && !in_array($fvalue, $good_elements)) {
                                    $good_elements[] = $fvalue;
                                }
                            }
                            $insert_listing_fields[$name] = $good_elements;
                            break;
                        default:
                            $insert_listing_fields[$name] = $listing_fields[$name];
                            break;
                    }
                } else {
                    if ($default_text != '') {
                        $insert_listing_fields[$name] = $default_text;
                    } else {
                        $insert_listing_fields[$name] = '';
                    }
                }

                $recordSet->Movenext();
            }
            $sql = 'INSERT INTO ' . $config['table_prefix'] . 'listingsdbelements (listingsdbelements_field_name, listingsdbelements_field_value, listingsdb_id, userdb_id) VALUES ';
            $sql2 = [];
            foreach ($insert_listing_fields as $name => $value) {
                $sql_name = $misc->make_db_safe($name);
                if (is_array($value)) {
                    $sql_value = $misc->make_db_safe(implode('||', $value));
                } else {
                    $sql_value = $misc->make_db_safe($value);
                }
                $sql2[] = "($sql_name, $sql_value, $listing_id, $listing_agent)";
            }
            if (count($sql2) > 0) {
                $sql .= implode(',', $sql2);
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->create', 'log_message' => 'DB Error: ' . $error]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }
            }

            //Call after_new_listing hoook
            include_once $config['basepath'] . '/include/hooks.inc.php';
            $hooks = new hooks();
            $hooks->load('after_new_listing', $listing_id);

            //Deal with Hooks and social sites
            if ($listing_details['active']) {
                $hooks->load('after_actived_listing', $listing_id);
            }

            // Only used when creation is done via the API. The add listing
            // feature in OR creates an inactive listing and used the the update
            // method to send the tweet when the status is changed from inactive
            // to active
            if ($config['twitter_new_listings'] == 1 && $listing_details['active']) {
                $media = '';
                if ($config['twitter_listing_photo'] == 1) {
                    $result = $lapi->load_local_api('media__read', [
                        'media_type' => 'listingsimages',
                        'media_parent_id' => $listing_id,
                        'media_output' => 'URL',
                    ]);
                    if ($result['error']) {
                        //If an error occurs die and show the error msg;
                        die($result['error_msg']);
                    }
                    if ($result['media_count'] > 0) {
                        $media = $result['media_object'][0]['file_name'];
                        $media_remote = $result['media_object'][0]['remote'];
                    }
                }

                $twitter_url = ' ' . $config['baseurl'] . '/l/' . $listing_id;
                $twitter_title = $listing_details['title'];
                if (strlen($twitter_url) + strlen($twitter_title) > 140) {
                    $twitter_title = substr($twitter_title, 0, 137 - strlen($twitter_url)) . '...';
                }
                $twitter_post = $twitter_title . $twitter_url;
                $lapi->load_local_api('twitter__post', ['message' => $twitter_post, 'media' => $media, 'media_remote' => $media_remote]);
            }

            //Email Notification
            if ($config['email_notification_of_new_listings'] === '1') {
                global $misc;

                include_once $config['basepath'] . '/include/core.inc.php';
                $page = new page_user();
                include_once $config['basepath'] . '/include/listing.inc.php';
                $listing_pages = new listing_pages();
                $page->load_page($config['admin_template_path'] . '/email/new_listing_notification.html');
                $remote_ip = $_SERVER['REMOTE_ADDR'];
                $timestamp = date('F j, Y, g:i:s a');
                $page->replace_tag('user_ip', $remote_ip);
                $page->replace_tag('notification_time', $timestamp);
                $page->replace_listing_field_tags($listing_id);
                $page->replace_lang_template_tags();
                $page->replace_tags(['company_logo', 'baseurl', 'template_url']);
                $subject = $page->get_template_section('subject_block');
                $page->page = $page->remove_template_block('subject', $page->page);
                $page->auto_replace_tags();
                $message = $page->return_page();
                if (isset($config['site_email']) && $config['site_email'] != '') {
                    $sender_email = $config['site_email'];
                } else {
                    $sender_email = $config['admin_email'];
                }
                $listing_agent_email = $listing_pages->get_listing_agent_value('userdb_emailaddress', $listing_id);
                $listing_agent_name = $listing_pages->get_listing_agent_value('userdb_user_last_name', $listing_id) . ', ' . $listing_pages->get_listing_agent_value('userdb_user_first_name', $listing_id);
                ;
                $sent = $misc->send_email($config['admin_name'], $sender_email, $config['admin_email'], $message, $subject, true, false, $listing_agent_name, $listing_agent_email);
            } // end if
            // ta da! we're done...
            $admin_status = $login->verify_priv('Admin');
            if ($admin_status == false || !isset($or_int_disable_log) || $or_int_disable_log == false) {
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->create', 'log_message' => $lang['log_created_listing'] . ' ' . $listing_id . ' by ' . $_SESSION['username']]);
            }
            return ['error' => false, 'listing_id' => $listing_id];
        }
    }
    /**
     * This API Command reads a listing
     * @param array $data $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['listing_id'] - This is the Listing ID that we are updating.</li>
     *      <li>$data['fields'] - This is an optional array of fields to retrieve, if left empty or not passed all fields will be retrieved.</li>
     *  </ul>
     * @return array
     **/
    public function read($data)
    {
        global $conn, $lapi, $config, $lang;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_is_agent = $login->verify_priv('Agent');

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed
        if (!isset($listing_id) || !is_numeric($listing_id)) {
            return ['error' => true, 'error_msg' => 'listing_id: correct_parameter_not_passed'];
        }
        if (isset($fields) && !is_array($fields)) {
            return ['error' => true, 'error_msg' => 'fields: correct_parameter_not_passed'];
        }
        //This will hold our listing data
        $listing_data = [];
        //If no fields were passed make an empty array to save checking for if !isset later
        if (!isset($fields)) {
            $fields = [];
        }
        //
        $sql = 'SELECT listingsdb_active,listingsdb_expiration FROM ' . $config['table_prefix'] . 'listingsdb WHERE listingsdb_id = ' . $listing_id;
        //TODO: Move this active check up higher to ensure listing is active no matter if we ask for one field or all fields on a listing
        if (!$login_is_agent) {
            $sql .= ' AND listingsdb_active = \'yes\'';
            if ($config['use_expiration'] == 1) {
                $sql .= ' AND listingsdb_expiration > ' . $conn->DBDate(time());
            }
        }
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->read', 'log_message' => 'DB Error: ' . $error]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
        }

        if ($recordSet->RecordCount() == 0) {
            return ['error' => true, 'error_msg' => 'Listing does Not exist or you do not have permission'];
        }

        //Get Base Listing Information
        if (empty($fields)) {
            $sql = 'SELECT ' . implode(',', $this->OR_INT_FIELDS) . ' FROM ' . $config['table_prefix'] . 'listingsdb WHERE listingsdb_id = ' . $listing_id;

            $recordSet = $conn->Execute($sql);
            if ($recordSet === false) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->read', 'log_message' => 'DB Error: ' . $error]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
            }

            if ($recordSet->RecordCount() == 1) {
                foreach ($this->OR_INT_FIELDS as $field) {
                    $listing_data[$field] = $recordSet->fields($field);
                }
            }
            $pclass_list = [$listing_data['listingsdb_pclass_id']];
            $field_list = $lapi->load_local_api('fields__metadata', ['resource' => 'listing', 'class' => $pclass_list]);
            //echo '<pre>'.print_r($field_list,TRUE).'</pre>';
            //Verify Listing Fields passed via API exist in the class that the listing is being inserted into.
            $allowed_fields = [];
            $allowed_fields_values = [];
            $allowed_fields_type = [];
            foreach ($field_list['fields'] as $field) {
                $field_id = $field['field_id'];
                $field_name = $field['field_name'];
                $field_elements = $field['field_elements'];
                $ftype = $field['field_type'];
                //Make sure field does not have same name as a core field for saftey
                if (!in_array($field_name, $this->OR_INT_FIELDS)) {
                    $allowed_fields[$field_id] = $field_name;
                    if ($ftype == 'select' || $ftype == 'select-multiple' || $ftype == 'option' || $ftype == 'checkbox') {
                        $allowed_fields_values[$field_name] = $field_elements;
                        $allowed_fields_type[$field_name] = $ftype;
                    }
                }
            }
            //Get The fields
            $sql = 'SELECT listingsdbelements_field_name, listingsdbelements_field_value FROM ' . $config['table_prefix'] . 'listingsdbelements WHERE listingsdb_id =' . $listing_id;
            $recordSet = $conn->Execute($sql);
            if ($recordSet === false) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->create', 'log_message' => 'DB Error: ' . $error]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
            }
            while (!$recordSet->EOF) {
                $field_name = $recordSet->fields('listingsdbelements_field_name');
                $field_value = $recordSet->fields('listingsdbelements_field_value');

                if (in_array($field_name, $allowed_fields)) {
                    //See if this is a lookup
                    if (isset($allowed_fields_values[$field_name])) {
                        if ($allowed_fields_type[$field_name] == 'select' || $allowed_fields_type[$field_name] == 'option') {
                            if (in_array($field_value, $allowed_fields_values[$field_name])) {
                                $listing_data[$field_name] = $field_value;
                            } else {
                                $listing_data[$field_name] = '';
                            }
                        } else {
                            $field_values = explode('||', $field_value);
                            $real_values = array_intersect($allowed_fields_values[$field_name], $field_values);
                            $listing_data[$field_name] = $real_values;
                        }
                    } else {
                        $listing_data[$field_name] = $field_value;
                    }
                }

                $recordSet->MoveNext();
            }
        } else {
            $core_fields = array_intersect($this->OR_INT_FIELDS, $fields);
            $noncore_fields = array_diff($fields, $this->OR_INT_FIELDS);
            if (!empty($core_fields)) {
                $sql = 'SELECT ' . implode(',', $core_fields) . ' FROM ' . $config['table_prefix'] . 'listingsdb WHERE listingsdb_id = ' . $listing_id;

                $recordSet = $conn->Execute($sql);
                if ($recordSet === false) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->read', 'log_message' => 'DB Error: ' . $error]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }

                if ($recordSet->RecordCount() == 1) {
                    foreach ($core_fields as $field) {
                        $listing_data[$field] = $recordSet->fields($field);
                    }
                }
            }
            //Ok we have the core fields, figure out what property class this listing is in.
            if (!empty($noncore_fields)) {
                $sql = 'SELECT listingsdb_pclass_id FROM ' . $config['table_prefix'] . 'listingsdb WHERE listingsdb_id = ' . $listing_id;
                $recordSet = $conn->Execute($sql);
                $pclass_list = [$recordSet->fields('listingsdb_pclass_id')];
                $field_list = $lapi->load_local_api('fields__metadata', ['resource' => 'listing', 'class' => $pclass_list]);
                //Verify Listing Fields passed via API exist in the class that the listing is being inserted into.
                $allowed_fields = [];
                $allowed_fields_values = [];
                $allowed_fields_type = [];
                foreach ($field_list['fields'] as $field) {
                    $field_id = $field['field_id'];
                    $field_name = $field['field_name'];
                    $field_elements = $field['field_elements'];
                    $ftype = $field['field_type'];
                    //Make sure field does not have same name as a core field for saftey
                    if (in_array($field_name, $noncore_fields)) {
                        $allowed_fields[$field_id] = $field_name;
                        if ($ftype == 'select' || $ftype == 'select-multiple' || $ftype == 'option' || $ftype == 'checkbox') {
                            $allowed_fields_values[$field_name] = $field_elements;
                            $allowed_fields_type[$field_name] = $ftype;
                        }
                    }
                    $recordSet->MoveNext();
                }
                //Get The fields
                $sql = 'SELECT listingsdbelements_field_name, listingsdbelements_field_value FROM ' . $config['table_prefix'] . 'listingsdbelements WHERE listingsdb_id =' . $listing_id;
                $recordSet = $conn->Execute($sql);
                if ($recordSet === false) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->create', 'log_message' => 'DB Error: ' . $error]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }
                while (!$recordSet->EOF) {
                    $field_name = $recordSet->fields('listingsdbelements_field_name');
                    $field_value = $recordSet->fields('listingsdbelements_field_value');
                    if (in_array($field_name, $allowed_fields)) {
                        //See if this is a lookup
                        if (isset($allowed_fields_values[$field_name])) {
                            if ($allowed_fields_type[$field_name] == 'select' || $allowed_fields_type[$field_name] == 'option') {
                                if (in_array($field_value, $allowed_fields_values[$field_name])) {
                                    $listing_data[$field_name] = $field_value;
                                } else {
                                    $listing_data[$field_name] = '';
                                }
                            } else {
                                $field_values = explode('||', $field_value);
                                $real_values = array_intersect($allowed_fields_values[$field_name], $field_values);
                                $listing_data[$field_name] = $real_values;
                            }
                        } else {
                            $listing_data[$field_name] = $field_value;
                        }
                    }

                    $recordSet->MoveNext();
                }
            }
        }
        return ['error' => false, 'listing' => $listing_data];
    }

    /**
     * This API Command updates listings.
     * @param array $data $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['listing_id'] - This is the Listing ID that we are updating.</li>
     *      <li>$data['class_id'] - This should be an integer of the class_id that this listing is currently assigned to.</li>
     *      <li>$data['listing_details'] - This should be an array containg the following three settings.</li>
     *      <li>$data['listing_details']['title'] - This is the listing title. Set only if you want to change the existing title.</li>
     *      <li>$data['listing_details']['seotitle'] - This is the listing title. Set only if you want to change the existing title, or set to AUTO to have the system generate a new SEO title based on the title you have updated.</li>
     *      <li>$data['listing_details']['featured'] - Set if this a featured listings, only set if you need to change. TRUE/FALSE</li>
     *      <li>$data['listing_details']['active'] -Set if this a active listings, only set if you need to change. TRUE/FALSE</li>
     *      <li>$data['listing_agents'] - This should be an array of up to agent ids. This sets the listing agent ID, the primary listing agent ID must be key 0.
     *      s<code>$data['listing_agents'][0]=5; //This lising belongs to agent 5. All other keys are currently ignored.</code>
     *      <li>$data['listing_fields'] - This should be an array of the actual listing data. The array keys should be the field name and the array values should be the field values. Only valid fields will be used, other data will be dropped.
     *      <code>$data['listing_fields'] =array('mls_id' => 126,'address'=>'126 E Buttler Ave');  // This defines a field value of 126 for a field called mls_id and a value of "126 E Buttler Ave" for the address field.</code></li>
     *  </ul>
     * @return array
     *
     */
    public function update($data)
    {
        global $conn, $lapi, $config, $lang, $misc;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->verify_priv('Agent');
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure'];
        }
        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed
        if (!isset($listing_id) || !is_numeric($listing_id)) {
            return ['error' => true, 'error_msg' => 'listing_id: correct_parameter_not_passed'];
        }
        if (isset($listing_fields) && (!isset($class_id) || !is_numeric($class_id))) {
            return ['error' => true, 'error_msg' => 'class_id: correct_parameter_not_passed'];
        }
        if (isset($listing_details) && !is_array($listing_details)) {
            return ['error' => true, 'error_msg' => 'listing_details: correct_parameter_not_passed'];
        }
        if (isset($listing_details['title']) && empty($listing_details['title'])) {
            return ['error' => true, 'error_msg' => '$listing_details[title]: correct_parameter_not_passed'];
        }
        if (isset($listing_details['featured']) && !is_bool($listing_details['featured'])) {
            return ['error' => true, 'error_msg' => '$listing_details[featured]: correct_parameter_not_passed'];
        }
        if (isset($listing_details['active']) && !is_bool($listing_details['active'])) {
            return ['error' => true, 'error_msg' => '$listing_details[active]: correct_parameter_not_passed'];
        }

        if (isset($listing_agents) && (!is_array($listing_agents) || count($listing_agents) < 1)) {
            return ['error' => true, 'error_msg' => 'listing_agents: correct_parameter_not_passed'];
        }
        if (isset($listing_fields) && !is_array($listing_fields)) {
            return ['error' => true, 'error_msg' => 'listing_fields: correct_parameter_not_passed'];
        }
        //echo '<pre>Listing Field Array: '.print_r($listing_fields,TRUE).'</pre>';
        if (isset($or_int_disable_log) && !is_bool($or_int_disable_log)) {
            return ['error' => true, 'error_msg' => 'or_int_disable_log: correct_parameter_not_passed'];
        }
        //Allow override of date format set in OR.
        //%Y/%d/%m
        if (!isset($or_date_format)) {
            $or_date_format = $config['date_to_timestamp'];
        }
        //Strip Tags from Titles
        if (isset($listing_details['title'])) {
            $listing_details['title'] = strip_tags($listing_details['title']);
        }
        if (isset($listing_details['seotitle'])) {
            $listing_details['seotitle'] = strip_tags($listing_details['seotitle']);
        }
        
        //See if the listing is currently active.
        $api_result = $lapi->load_local_api('listing__read', ['listing_id' => $listing_id, 'fields' => ['listingsdb_active']]);
        if (!$api_result['error']) {
            $oldstatus = $api_result['listing']['listingsdb_active'];
        }
        //Ok we have all the needed variables so now we build the listings.
        $listing_details['last_modified'] = time();
        if (isset($listing_details['title']) && isset($listing_details['seotitle']) && $listing_details['seotitle'] == 'AUTO') {
            //Make SEO Title
            if ($config['controlpanel_mbstring_enabled'] == 0) {
                // MBSTRING NOT ENABLED
                $listing_details['seotitle'] = strtolower($listing_details['title']);
            } else {
                $listing_details['seotitle'] = mb_convert_case($listing_details['title'], MB_CASE_LOWER, $config['charset']);
            }
            $listing_details['seotitle'] = trim($listing_details['seotitle']);
            $listing_details['seotitle'] = preg_replace('/[\~`!@#\$%^*\(\)\+=\"\':;\[\]\{\}|\\\?\<\>,\.\/]/', '', $listing_details['seotitle']);
            $listing_details['seotitle'] = str_replace(' ', $config['seo_url_seperator'], $listing_details['seotitle']);
            $listing_details['seotitle'] = preg_replace('/[\-]+/', '-', $listing_details['seotitle']);
        }
        $listing_id = intval($listing_id);
        //Do Permission Checks
        #TODO: Remove Call to deprecated API function
        $agent_result = $lapi->load_local_api('listing__retrieve_agents', ['listing_id' => $listing_id]);
        if ($agent_result['error'] == true) {
            return ['error' => true, 'error_msg' => 'Permission Denied (agent lookup failed)'];
        } else {
            $existing_listing_agents = $agent_result['listing_agents'];
        }
        $listing_agent = intval($existing_listing_agents[0]);
        //Verify we have permissions to add a listing
        if ($_SESSION['userID'] == $listing_agent) {
            $security = $login->verify_priv('Agent');
            if ($security !== true) {
                return ['error' => true, 'error_msg' => 'Permission Denied (agent)'];
            }
        } else {
            //Check tha we can add listing for other users
            $security = $login->verify_priv('edit_all_listings');
            if ($security !== true) {
                return ['error' => true, 'error_msg' => 'Permission Denied (edit_all_listings)'];
            }
        }
        $sql_fields = [];
        $can_edit_all_listings = $login->verify_priv('edit_all_listings');
        if ($can_edit_all_listings == true) {
            if (isset($listing_agents) && $listing_agents[0] > 0) {
                $sql_fields['userdb_id']  = intval($listing_agents[0]);
            }
        } else {
            $sql_fields['userdb_id'] = $listing_agent;
        }
       
        if (isset($listing_details['title'])) {
            $sql_fields['listingsdb_title'] = $misc->make_db_safe($listing_details['title']);
        }
        if (isset($listing_details['seotitle'])) {
            $sql_fields['listing_seotitle'] = $misc->make_db_safe($listing_details['seotitle']);
        }
        if (isset($listing_details['notes'])) {
            $sql_fields['listingsdb_notes'] = $misc->make_db_safe($listing_details['notes']);
        }

        if (isset($listing_details['featured'])) {
            if ($listing_details['featured']) {
                $sql_fields['listingsdb_featured'] = $misc->make_db_safe('yes');
            } else {
                $sql_fields['listingsdb_featured'] = $misc->make_db_safe('no');
            }
        }
        if (isset($listing_details['active'])) {
            if ($listing_details['active']) {
                $sql_fields['listingsdb_active'] = $misc->make_db_safe('yes');
            } else {
                $sql_fields['listingsdb_active'] = $misc->make_db_safe('no');
            }
        }
       
        if (isset($listing_details['hit_count'])) {
            $sql_fields['listingsdb_hit_count'] = intval($listing_details['hit_count']);
        }

        $sql_a = [];
        foreach ($sql_fields as $field => $value) {
            $sql_a[] = $field . ' = ' . $value;
        }
        $exp_status = $login->verify_priv('CanEditExpiration');
        if ($exp_status) {
            if (isset($listing_details['expiration'])) {
                $sql_a[] = 'listingsdb_expiration = ' . $conn->DBDate($this->convert_date($listing_details['expiration'], $config['date_to_timestamp']));
            }
        }
        $sql_a[]  = 'listingsdb_last_modified = ' . $conn->DBTimeStamp($listing_details['last_modified']);

        $sql = 'UPDATE ' . $config['table_prefix'] . 'listingsdb SET ' . implode(',', $sql_a) . ' WHERE listingsdb_id = ' . $listing_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->update', 'log_message' => 'DB Error: ' . $error]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
        }

        if (isset($sql_fields['listing_seotitle'])) {
            //Make sure title is unique
            $sql = 'SELECT listingsdb_id 
					FROM ' . $config['table_prefix'] . 'listingsdb 
					WHERE listing_seotitle = ' . $sql_fields['listing_seotitle'] . '';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->create', 'log_message' => 'DB Error: ' . $error]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
            }
            if ($recordSet->RecordCount() > 1) {
                $listing_details['seotitle'] = $listing_details['seotitle'] . '-' . $listing_id;
                $sql_fields['listing_seotitle'] =  $misc->make_db_safe($listing_details['seotitle']);
                $sql = 'UPDATE ' . $config['table_prefix'] . 'listingsdb 
						SET listing_seotitle = ' . $sql_fields['listing_seotitle'] . ' 
						WHERE listingsdb_id = ' . $listing_id;
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->create', 'log_message' => 'DB Error: ' . $error]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }
            }
        }

        //Deal with Listing Fields
        if (isset($listing_fields)) {
            $class_id = intval($class_id);
            //Get List of Fields for this property class

            $sql = 'SELECT listingsformelements_field_name,lfe.listingsformelements_id,listingsformelements_field_type, listingsformelements_field_elements
						FROM  ' . $config['table_prefix'] . 'listingsformelements as lfe LEFT JOIN ' . $config['table_prefix_no_lang'] . 'classformelements  as cfe
						ON lfe.listingsformelements_id = cfe.listingsformelements_id
						WHERE class_id = ' . $class_id;

            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->create', 'log_message' => 'DB Error: ' . $error]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
            }
            //Verify Listing Fields passed via API exist in the class that the listing is being inserted into.
            $insert_listing_fields = [];
            //print_r($listing_fields);
            while (!$recordSet->EOF) {
                $name = $recordSet->fields('listingsformelements_field_name');
                if (array_key_exists($name, $listing_fields)) {
                    $id = $recordSet->fields('listingsformelements_id');
                    $data_type = $recordSet->fields('listingsformelements_field_type');
                    $data_elements = $recordSet->fields('listingsformelements_field_elements');
                    switch ($data_type) {
                        case 'number':
                            if ($listing_fields[$name] === '') {
                                $insert_listing_fields[$name] = null;
                            } else {
                                if (is_numeric($listing_fields[$name])) {
                                    $insert_listing_fields[$name] = $listing_fields[$name];
                                } else {
                                    $price = str_replace(',', '', $listing_fields[$name]);
                                    $insert_listing_fields[$name] = intval($price);
                                }
                            }
                            break;
                        case 'decimal':
                        case 'price':
                            if ($listing_fields[$name] === '') {
                                $insert_listing_fields[$name] = null;
                            } else {
                                $price = str_replace(',', '', $listing_fields[$name]);
                                $insert_listing_fields[$name] = (float)$price;
                            }
                            break;
                        case 'date':
                            if ($listing_fields[$name] === '') {
                                $insert_listing_fields[$name] = null;
                            } else {
                                $insert_listing_fields[$name] = $this->convert_date($listing_fields[$name], $or_date_format);
                            }
                            break;
                        case 'select':
                        case 'select-multiple':
                        case 'option':
                        case 'checkbox':
                            //This is a lookup field. Make sure values passed are allowed by the system.
                            //Get Array of allowed data elements
                            $data_elements_array = explode('||', $data_elements);
                            //echo '<pre> Data Elements: '.print_r($data_elements_array,TRUE).'</pre>';
                            //Get array of passed data eleements
                            if (!is_array($listing_fields[$name])) {
                                $t_value = $listing_fields[$name];
                                unset($listing_fields[$name]);
                                $listing_fields[$name][] = $t_value;
                            }
                            //echo '<pre> Field Elements: '.print_r($listing_fields[$name],TRUE).'</pre>';
                            $good_elements = [];
                            foreach ($listing_fields[$name] as $fvalue) {
                                if (in_array($fvalue, $data_elements_array) && !in_array($fvalue, $good_elements)) {
                                    $good_elements[] = $fvalue;
                                }
                            }
                            //echo '<pre> Good Elements: '.print_r($good_elements,TRUE).'</pre>';
                            $insert_listing_fields[$name] = $good_elements;
                            break;
                        default:
                            $insert_listing_fields[$name] = $listing_fields[$name];
                            break;
                    }
                }
                $recordSet->Movenext();
            }
            //print_r($insert_listing_fields);
            foreach ($insert_listing_fields as $name => $value) {
                $sql_name = $misc->make_db_safe($name);
                if (is_array($value)) {
                    $sql_value = $misc->make_db_safe(implode('||', $value));
                } else {
                    $sql_value = $misc->make_db_safe($value);
                }
                $sql = 'UPDATE ' . $config['table_prefix'] . "listingsdbelements 
						SET listingsdbelements_field_value = $sql_value 
						WHERE listingsdbelements_field_name = $sql_name AND listingsdb_id = $listing_id";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->create', 'log_message' => 'DB Error: ' . $error]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }
            }
        }
        //Deal with Hooks and social sites
        if (isset($listing_details['active']) && $listing_details['active'] && $oldstatus == 'no') {
            include_once $config['basepath'] . '/include/hooks.inc.php';
            $hooks = new hooks();
            $hooks->load('after_actived_listing', $listing_id);
        }
        $seoresult = $lapi->load_local_api('listing__read', ['listing_id' => $listing_id, 'fields' => ['listingsdb_title']]);
        $twitter_title = $seoresult['listing']['listingsdb_title'];

        $media = '';
        $media_remote = '';
        if ($config['twitter_listing_photo'] == 1) {
            $result = $lapi->load_local_api('media__read', [
                'media_type' => 'listingsimages',
                'media_parent_id' => $listing_id,
                'media_output' => 'URL',
            ]);
            if ($result['error']) {
                //If an error occurs die and show the error msg;
                die($result['error_msg']);
            }
            if ($result['media_count'] > 0) {
                $media = $result['media_object'][0]['file_name'];
                $media_remote = $result['media_object'][0]['remote'];
            }
        }

        // The add listing feature in OR creates an inactive listing via the
        // create method and uses the update method to send the tweet when the
        // status is changed from inactive to active.
        if (isset($listing_details['active']) && $listing_details['active'] && $oldstatus == 'no') {
            //treat this as being a new listing
            if ($config['twitter_new_listings'] == 1) {
                $twitter_url = ' ' . $config['baseurl'] . '/l/' . $listing_id;
                $twitter_title = $listing_details['title'];
                if (strlen($twitter_url) + strlen($twitter_title) > 140) {
                    $twitter_title = substr($twitter_title, 0, 137 - strlen($twitter_url)) . '...';
                }
                $twitter_post = $twitter_title . $twitter_url;
                $lapi->load_local_api('twitter__post', ['message' => $twitter_post, 'media' => $media, 'media_remote' => $media_remote]);
            }
        } elseif (isset($listing_details['active']) && $listing_details['active']) {
            //listing was updated and tweet updates is set.
            if ($config['twitter_update_listings'] == 1) {
                $twitter_url = ' ' . $config['baseurl'] . '/l/' . $listing_id;
                if (strlen($twitter_url) + strlen($twitter_title) > 140) {
                    $twitter_title = substr($twitter_title, 0, 137 - strlen($twitter_url)) . '...';
                }
                $twitter_post = $twitter_title . $twitter_url;
                $lapi->load_local_api('twitter__post', ['message' => $twitter_post, 'media' => $media, 'media_remote' => $media_remote]);
            }
        }
        // ta da! we're done...
        $admin_status = $login->verify_priv('Admin');
        if ($admin_status == false || !isset($or_int_disable_log) || $or_int_disable_log == false) {
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->update', 'log_message' => $lang['log_updated_listing'] . ' ' . $listing_id . ' by ' . $_SESSION['username']]);
        }
        //call the changed listing change hook
        include_once $config['basepath'] . '/include/hooks.inc.php';
        $hooks = new hooks();
        $hooks->load('after_listing_change', $listing_id);
        return ['error' => false, 'listing_id' => $listing_id];
    }
    /**
     * This API Command is deprecated and should not be used. Command be removed in 3.1
     *
     * @deprecated
     * @param array $data $data Get Listing Agent ID for a listing
     *  <ul>
     *      <li>$data['listing_id'] - Number - Listing ID to get agent for</li>
     *  </ul>
     *
     */
    public function retrieve_agents($data)
    {
        global $conn, $lapi, $config, $lang;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->verify_priv('Agent');
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure'];
        }
        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed
        if (!isset($listing_id) || !is_numeric($listing_id)) {
            return ['error' => true, 'error_msg' => 'listing_id: correct_parameter_not_passed'];
        }
        $sql = 'SELECT ' . $config['table_prefix'] . "listingsdb.userdb_id
				FROM $config[table_prefix]listingsdb
				WHERE listingsdb_id = $listing_id";
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->get_listingagent', 'log_message' => 'DB Error: ' . $error]);
            return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
        }
        // get main listings data
        $display = '';
        $listing_agents[0] = intval($recordSet->fields('userdb_id'));
        ;
        return ['error' => false, 'listing_agents' => $listing_agents];
    }
    /**
     * This API Command deletes listings.
     * @param array $data $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['listing_id'] - Number - Listing ID to delete</li>
     *  <ul>
     */
    public function delete($data)
    {
        global $conn, $lapi, $config, $lang;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->verify_priv('Agent');
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure'];
        }
        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed
        if (!isset($listing_id) || !is_numeric($listing_id)) {
            return ['error' => true, 'error_msg' => 'listing_id: correct_parameter_not_passed'];
        }

        //Check permissions
        #TODO: Remove Call to deprecated API function
        $agent_result = $lapi->load_local_api('listing__retrieve_agents', ['listing_id' => $listing_id]);
        if ($agent_result['error'] == true) {
            return ['error' => true, 'error_msg' => 'Permission Denied (agent lookup failed)'];
        } else {
            $existing_listing_agents = $agent_result['listing_agents'];
        }
        $listing_agent = intval($existing_listing_agents[0]);
        //Verify we have permissions to add a listing
        if ($_SESSION['userID'] == $listing_agent) {
            $security = $login->verify_priv('Agent');
            if ($security !== true) {
                return ['error' => true, 'error_msg' => 'Permission Denied (agent)'];
            }
        } else {
            //Check tha we can add listing for other users
            $security = $login->verify_priv('edit_all_listings');
            if ($security !== true) {
                return ['error' => true, 'error_msg' => 'Permission Denied (edit_all_listings)'];
            }
        }

        $display = '';

        //call the deleted listing plugin function
        include_once $config['basepath'] . '/include/hooks.inc.php';
        $hooks = new hooks();
        $hooks->load('before_listing_delete', $listing_id);
        // now get all the images associated with an listing
        $result = $lapi->load_local_api('media__delete', ['media_type' => 'listingsimages', 'media_parent_id' => $listing_id, 'media_object_id' => '*', 'or_int_disable_log' => true]);
        if ($result['error'] == true) {
            return $result;
        }

        // now get all the vtours associated with an listing
        $result = $lapi->load_local_api('media__delete', ['media_type' => 'listingsvtours', 'media_parent_id' => $listing_id, 'media_object_id' => '*', 'or_int_disable_log' => true]);
        if ($result['error'] == true) {
            return $result;
        }

        // now get all the vtours associated with an listing
        $result = $lapi->load_local_api('media__delete', ['media_type' => 'listingsfiles', 'media_parent_id' => $listing_id, 'media_object_id' => '*', 'or_int_disable_log' => true]);
        if ($result['error'] == true) {
            return $result;
        }

        // delete a listing
        $configured_langs = explode(',', $config['configured_langs']);
        foreach ($configured_langs as $configured_lang) {
            $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . $configured_lang . "_listingsdb WHERE listingsdb_id = $listing_id";

            $recordSet = $conn->Execute($sql);
            if ($recordSet === false) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->delete', 'log_message' => 'DB Error: ' . $error]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
            }
            $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . $configured_lang . "_listingsdbelements WHERE listingsdb_id = $listing_id";

            $recordSet = $conn->Execute($sql);
            if ($recordSet === false) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->delete', 'log_message' => 'DB Error: ' . $error]);
                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
            }
        }

        // Delete from favorites
        $sql = 'DELETE FROM ' . $config['table_prefix'] . "userfavoritelistings WHERE listingsdb_id = $listing_id";
        $recordSet = $conn->Execute($sql);
        if ($recordSet === false) {
            $error = $conn->ErrorMsg();

            return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
        }
        // ta da! we're done...
        $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->listing->delete', 'log_message' => $lang['log_deleted_listing'] . ' ' . $listing_id . ' by ' . $_SESSION['username']]);
        //call the deleted listing plugin function
        include_once $config['basepath'] . '/include/hooks.inc.php';
        $hooks = new hooks();
        $hooks->load('after_listing_delete', $listing_id);
        return ['error' => false, 'listing_id' => $listing_id];
    }
}
