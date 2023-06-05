<?php

/**
 * This is the Blog  API, it contains all api calls for creating and retrieving Blog data.
 *
 * @package Open-Realty
 * @subpackage API
 **/
class blog_api
{
    //password field removed from list.
    protected $OR_INT_FIELDS = ['blogmain_id','userdb_id','blogmain_title','blog_seotitle','blogmain_date','blogmain_full','blogmain_description','blogmain_keywords','blogmain_published'];

    /*
        protected $read_filter = array(
            'fields' =>array(
                ‘filter’ => FILTER_SANITIZE_STRING
            )
        );

        protected $read_filter = array(
            'blogmain_id' => FILTER_VALIDATE_INT,
            'userdb_id' => FILTER_VALIDATE_INT,
            'blogmain_title' => FILTER_SANITIZE_STRING,
            'blog_seotitle' => FILTER_SANITIZE_STRING,
            'blogmain_date' => FILTER_VALIDATE_INT,
            'blogmain_full' => FILTER_SANITIZE_STRING,
            'blogmain_description' => FILTER_SANITIZE_STRING,
            'blogmain_keywords' => FILTER_SANITIZE_STRING,
            'blogmain_published' => FILTER_VALIDATE_INT
        );

    */

    /**
     * This API Command searches the users
     * @param array $data $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['parameters'] - This is a REQUIRED array of the fields and the values we are searching for.</li>
     *      <li>$data['sortby'] - This is an optional array of fields to sort by.</li>
     **     <li>$data['sorttype'] - This is an optional array of sort types (ASC/DESC) to sort the sortby fields by.</li>
     *      <li>$data['offset'] - This is an optional integer of the number of users to offset the search by. To use offset you must set a limit.</li>
     *      <li>$data['limit'] - This is an optional integer of the number of users to limit the search by. 0 or unset will return all users.</li>
     *      <li>$data['count_only'] - This is an optional integer flag 1/0, where 1 returns a record count only, defaults to 0 if not set. Usefull if doing limit/offset search for pagenation to get the inital full record count..</li>
     *  </ul>
     * @return array  - Array retruned will contain the following paramaters.
     *  [error] = true/FASLE
     *  [user_count] = Number of records found, if using a limit this is only the number of records that match your current limit/offset results.
     *  [users] = Array of user_ids.
     *  [info] = The info array contains benchmark information on the search, including process_time, query_time, and total_time
     *  [sortby] = Contains an array of fields that were used to sort the search results. Note if you are doing a CountONly search the sort is not actually used as this would just slow down the query.
     *  [sorttype] = Contains an array of the sorttype (ASC/DESC) used on the sortby fields.
    *   ['limit'] - INT - The numeric limit being imposed on the results.
    *   ['resource'] - TEXT - The resource the search was made against, 'agent' or 'member'
     **/
    public function search($data)
    {
        $DEBUG_SQL = false;
        global $conn, $lapi, $config, $misc, $lang, $db_type;

        $start_time = $misc ->getmicrotime();
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed
        if (!isset($parameters) || !is_array($parameters)) {
            return ['error' => true, 'error_msg' => 'parameters: correct_parameter_not_passed'];
        }
        if (isset($sortby)&& !is_array($sortby)) {
            return ['error' => true, 'error_msg' => 'sortby: correct_parameter_not_passed'];
        }
        if (isset($sorttype)&& !is_array($sorttype)) {
            return ['error' => true, 'error_msg' => 'sorttype: correct_parameter_not_passed'];
        }
        if (isset($offset) && !is_numeric($offset)) {
            return ['error' => true, 'error_msg' => 'offset: correct_parameter_not_passed'];
        }

        if (isset($limit) && !is_numeric($limit)) {
            return ['error' => true, 'error_msg' => 'limit: correct_parameter_not_passed'];
        }

        if (isset($count_only)&& $count_only ==1) {
            $count_only=true;
        } else {
            $count_only=false;
        }
        $searchresultSQL = '';
        // Set Default Search Options
        $imageonly = false;
        $tablelist = [];
        $tablelist_fullname = [];
        $string_where_clause='';
        $string_where_clause_nosort='';
        $login_status = $login->verify_priv('can_access_blog_manager');

        if ($login_status !== true || !isset($parameters['blogmain_published'])) {
            //If we are not an agent only show active agents, or if user did not specify show only actives by default.
            $parameters['blogmain_published']=1;
        }

        //check to see teh publishing status of this user
        $pub_status = $this->get_publisher_status($_SESSION['userID']);

        if ($pub_status === false || $pub_status === true && $_SESSION['userID'] !=1) {
            $admin_not_agent = true;
        } else {
            $admin_not_agent = false;
        }

        //Loop through search paramaters
        foreach ($parameters as $k => $v) {
            if (is_null($v) || $v == '') {
                unset($parameters[$k]);
                continue;
            }

            //Search blog by blogmain_id
            if ($k == 'blogmain_id') {
                $blogmain_id = explode(',', $v);
                $i = 0;
                if ($searchresultSQL != '') {
                    $searchresultSQL .= ' AND ';
                }
                foreach ($blogmain_id as $id) {
                    $id = intval($id);
                    if ($i == 0) {
                        $searchresultSQL .= '((' . $config['table_prefix'] . 'blogmain.blogmain_id = ' . $id . ')';
                    } else {
                        $searchresultSQL .= ' OR (' . $config['table_prefix'] . 'blogmain.blogmain_id = ' . $id . ')';
                    }
                    $i++;
                }
                $searchresultSQL .= ')';
            } elseif ($k == 'userdb_id') {
                if ($v != '' && $v != 'any') {
                    if (is_array($v)) {
                        $sstring = '';
                        foreach ($v as $u) {
                            $u = intval($u);
                            if (empty($sstring)) {
                                $sstring .=  $config['table_prefix'] . 'blogmain.userdb_id = '.$u;
                            } else {
                                $sstring .=  ' OR ' . $config['table_prefix'] . 'blogmain.userdb_id = '.$u;
                            }
                        }
                        if ($searchresultSQL != '') {
                            $searchresultSQL .= ' AND ';
                        }
                        $searchresultSQL .=  '(' . $sstring. ')';
                    } else {
                        $sql_v = intval($v);
                        if ($searchresultSQL != '') {
                            $searchresultSQL .= ' AND ';
                        }
                        $searchresultSQL .= '(' . $config['table_prefix'] . 'blogmain.userdb_id = ' . $sql_v . ')';
                    }
                }
            } elseif ($k == 'blogmain_title') {
                $safe_v = '%'.$conn->addQ($v).'%';
                if ($string_where_clause != '') {
                    $string_where_clause .= ' AND ';
                }
                if ($string_where_clause_nosort != '') {
                    $string_where_clause_nosort .= ' AND ';
                }
                $string_where_clause .= '(' . $config['table_prefix'] . 'blogmain.blogmain_title LIKE \''.$safe_v.'\')';
                $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'blogmain.blogmain_title LIKE \''.$safe_v.'\')';
            } elseif ($k == 'blogmain_full') {
                $safe_v = strip_tags($v);
                $safe_v = '%'.$conn->addQ($v).'%';
                if ($string_where_clause != '') {
                    $string_where_clause .= ' AND ';
                }
                if ($string_where_clause_nosort != '') {
                    $string_where_clause_nosort .= ' AND ';
                }
                $string_where_clause .= '(' . $config['table_prefix'] . 'blogmain.blogmain_full LIKE \''.$safe_v.'\')';
                $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'blogmain.blogmain_full LIKE \''.$safe_v.'\')';
            } elseif ($k == 'blogmain_description') {
                $safe_v = '%'.$conn->addQ($v).'%';
                if ($string_where_clause != '') {
                    $string_where_clause .= ' AND ';
                }
                if ($string_where_clause_nosort != '') {
                    $string_where_clause_nosort .= ' AND ';
                }
                $string_where_clause .= '(' . $config['table_prefix'] . 'blogmain.blogmain_description LIKE \''.$safe_v.'\')';
                $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'blogmain.blogmain_description LIKE \''.$safe_v.'\')';
            } elseif ($k == 'blogmain_keywords') {
                $safe_v = strip_tags($safe_v);
                $safe_v = '%'.$conn->addQ($v).'%';
                if ($string_where_clause != '') {
                    $string_where_clause .= ' AND ';
                }
                if ($string_where_clause_nosort != '') {
                    $string_where_clause_nosort .= ' AND ';
                }
                $string_where_clause .= '(' . $config['table_prefix'] . 'blogmain.blogmain_keywords LIKE \''.$safe_v.'\')';
                $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'blogmain.blogmain_keywords LIKE \''.$safe_v.'\')';
            } elseif ($k == 'blogmain_published') {
                if ($string_where_clause != '') {
                    $string_where_clause .= ' AND ';
                }
                if ($string_where_clause_nosort != '') {
                    $string_where_clause_nosort .= ' AND ';
                }
                // Get any blog regardless of publish status
                if ($v == 'any') {
                    $string_where_clause .= '(' . $config['table_prefix'] . 'blogmain.blogmain_published = \'0\' 
												OR ' . $config['table_prefix'] . 'blogmain.blogmain_published = \'1\' 
												OR ' . $config['table_prefix'] . 'blogmain.blogmain_published = \'2\'
											)';
                    $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'blogmain.blogmain_published = \'0\' 
														OR ' . $config['table_prefix'] . 'blogmain.blogmain_published = \'1\' 
														OR ' . $config['table_prefix'] . 'blogmain.blogmain_published = \'2\'
													)';
                }
                //Draft
                elseif ($v == 0) {
                    $string_where_clause .= '(' . $config['table_prefix'] . 'blogmain.blogmain_published = \'0\')';
                    $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'blogmain.blogmain_published = \'0\')';
                }
                //Live
                elseif ($v == 1) {
                    $string_where_clause .= '(' . $config['table_prefix'] . 'blogmain.blogmain_published = \'1\')';
                    $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'blogmain.blogmain_published = \'1\')';
                }
                // Pending Review
                elseif ($v == 2) {
                    $string_where_clause .= '(' . $config['table_prefix'] . 'blogmain.blogmain_published = \'2\')';
                    $string_where_clause_nosort .= '(' . $config['table_prefix'] . 'blogmain.blogmain_published = \'2\')';
                } else {
                    $string_where_clause .= '(' . $config['table_prefix'] . 'blogmain.blogmain_published = \'1\')';
                    $string_where_clause_nosort  .= '(' . $config['table_prefix'] . 'blogmain.blogmain_published = \'1\')';
                }
            }

            // creation date related searches
            //  date_equals is already handled via 'blogmain_date' => xxx
            elseif ($k == 'blog_creation_date_greater') {
                $safe_v = intval($v);
                if ($safe_v>0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    //$safe_v = $conn->DBDate($v);
                    $searchresultSQL .= ' blogmain_date > '.$safe_v;
                }
            } elseif ($k == 'blog_creation_date_less') {
                $safe_v = intval($v);
                if ($safe_v>0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    //$safe_v =$conn->DBDate($v);
                    $searchresultSQL .= ' blogmain_date < '.$safe_v;
                }
            } elseif ($k == 'blog_creation_date_equal_days') {
                $safe_v = intval($v);
                if ($safe_v>0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    //$time = mktime(0, 0, 0, date("m")  , date("d")-intval($v), date("Y"));
                    //$safe_v = $conn->DBTimeStamp($time);
                    $searchresultSQL .= ' blogmain_date = '.$safe_v;
                }
            } elseif ($k == 'blog_creation_date_greater_days') {
                $safe_v = intval($v);
                if ($v>0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    //$time = mktime(0, 0, 0, date("m")  , date("d")-intval($v), date("Y"));
                    //$safe_v = $conn->DBTimeStamp($time);;
                    $searchresultSQL .= ' blogmain_date > '.$safe_v;
                }
            //userdb_last_modified
            } elseif ($k == 'blog_creation_date_less_days') {
                $safe_v = intval($v);
                if ($v>0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    //$time = mktime(0, 0, 0, date("m")  , date("d")-intval($v), date("Y"));
                    //$safe_v = $conn->DBTimeStamp($time);
                    $searchresultSQL .= ' blogmain_date < '.$safe_v;
                }
            //userdb_last_modified
            } elseif ($k == 'blog_categories') {
                $use = false;
                $comma_separated = implode(' ', $v);
                if (trim($comma_separated) != '') {
                    $use = true;
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                }
                if ($use === true) {
                    $safe_k = 'category_id';
                    $searchresultSQL .= ' (';
                    $vitem_count = 0;
                    foreach ($v as $vitem) {
                        $safe_vitem = $misc->make_db_safe($vitem);
                        if ($vitem != '') {
                            if ($vitem_count != 0) {
                                $searchresultSQL .= " OR `$safe_k` = $safe_vitem";
                            } else {
                                $searchresultSQL .= " `$safe_k` = $safe_vitem";
                            }
                            $vitem_count++;
                        }
                    }
                    $searchresultSQL .= ')';
                    $tablelist[] = $config['table_prefix_no_lang'] . 'blogcategory_relation';
                }
            } elseif ($k == 'blog_post_tags') {
                $use = false;
                $comma_separated = implode(' ', $v);
                if (trim($comma_separated) != '') {
                    $use = true;
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                }
                if ($use === true) {
                    $safe_k = 'tag_id';
                    $searchresultSQL .= ' (';
                    $vitem_count = 0;
                    foreach ($v as $vitem) {
                        $safe_vitem = $misc->make_db_safe($vitem);
                        if ($vitem != '') {
                            if ($vitem_count != 0) {
                                $searchresultSQL .= " OR `$safe_k` = $safe_vitem";
                            } else {
                                $searchresultSQL .= " `$safe_k` = $safe_vitem";
                            }
                            $vitem_count++;
                        }
                    }
                    $searchresultSQL .= ')';
                    $tablelist[] = $config['table_prefix_no_lang'] . 'blogtag_relation';
                }
            }

            //this one is non-funtional. I need to work out the joins if it is even possible.
            elseif ($k == 'blog_comment_count_equal') {
                $safe_v = intval($v);
                if ($safe_v >= 0) {
                    if ($searchresultSQL != '') {
                        $searchresultSQL .= ' AND ';
                    }
                    $safe_k = 'blogcomments_moderated';

                    //$safe_v = $conn->DBDate($v);
                    $searchresultSQL .= " `$safe_k` = '$safe_v'";
                    $tablelist[] = $config['table_prefix'] . 'blogcomments';
                }
            }

            //Anything left must not be one of these.
            elseif ($v != '' && $k != 'cur_page' && $k != 'action' && $k != 'PHPSESSID' && $k != 'sortby' && $k != 'sorttype'
                        && $k != 'printer_friendly' && $k !='template' && $k != 'popup' && $k != 'blogmain_id' && $k != 'userdb_id'
                        && $k != 'blogmain_title' && $k != 'blogmain_title' && $k != 'blog_creation_date_equal' && $k != 'blog_creation_date_greater'
                        && $k != 'blog_creation_date_less' && $k != 'blog_creation_date_equal_days' && $k != 'blog_creation_date_greater_days'
                        && $k != 'blog_creation_date_less_days' && $k != 'blogmain_full' && $k != 'blogmain_description' && $k != 'blogmain_keywords'
                        && $k != 'blogmain_published'
            ) {
            }
        }

        // Handle Sorting
        // sort the users
        // this is the main SQL that grabs the users
        // basic sort by title..
        $group_order_text = '';
        $sortby_array=[];
        $sorttype_array=[];
        //Set array
        if (isset($sortby)&& !empty($sortby)) {
            $sortby_array= $sortby;
        }
        if (isset($sorttype)&& !empty($sorttype)) {
            $sorttype_array= $sorttype;
        }
        $sql_sort_type = '';
        $sort_text = '';
        $order_text = '';
        $group_order_text = '';
        $tablelist_nosort = $tablelist;
        $sort_count = count($sortby_array);
        for ($x = 0; $x < $sort_count; $x++) {
            $sortby_array[$x]=$sortby_array[$x];
            if (!isset($sorttype_array[$x])) {
                $sorttype_array[$x]='';
            } elseif ($sorttype_array[$x] != 'ASC' && $sorttype_array[$x] != 'DESC') {
                $sorttype_array[$x]='';
            }

            if ($sortby_array[$x] == 'blogmain_id') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY blogmain_id ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',blogmain_id ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'userdb_id') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY userdb_id ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',userdb_id ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'blogmain_title') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY blogmain_title ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',blogmain_title ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'blogmain_date') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY blogmain_date ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',blogmain_date ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'blogmain_published') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY blogmain_published ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',blogmain_published ' . $sorttype_array[$x];
                }
            } elseif ($sortby_array[$x] == 'random') {
                if ($x == 0) {
                    $order_text .= 'ORDER BY rand() ' . $sorttype_array[$x];
                } else {
                    $order_text .= ',rand() ' . $sorttype_array[$x];
                }
            }
        }
        $group_order_text = $group_order_text . ' '.$order_text;

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
            $string_table_list .= ' ,'  . $tablelist[$i].'';
        }
        $arrayLength = count($tablelist_nosort);
        $string_table_list_no_sort = '';
        for ($i = 0; $i < $arrayLength; $i++) {
            $string_table_list_no_sort .= ' ,'  . $tablelist[$i].'';
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
            $string_where_clause .= ' (' . $config['table_prefix'] . 'blogmain.blogmain_id = ' . $tablelist[$i] . '.blogmain_id)';
        }
        $arrayLength = count($tablelist_nosort);
        for ($i = 0; $i < $arrayLength; $i++) {
            if ($string_where_clause_nosort != '') {
                $string_where_clause_nosort .= ' AND ';
            }
            $string_where_clause_nosort .= ' (' . $config['table_prefix'] . 'blogmain.blogmain_id = ' . $tablelist[$i] . '.blogmain_id)';
        }

        $searchSQL = 'SELECT distinct(' . $config['table_prefix'] . 'blogmain.blogmain_id)
					FROM ' . $config['table_prefix'] . 'blogmain ' . $string_table_list . '
					WHERE ' . $string_where_clause;

        $searchSQLCount = 'SELECT COUNT(distinct(' . $config['table_prefix'] . 'blogmain.blogmain_id)) as total_blogs
					FROM ' . $config['table_prefix'] . 'blogmain ' . $string_table_list_no_sort . '
					WHERE ' . $string_where_clause_nosort;

        if ($searchresultSQL != '') {
            $searchSQL .= ' AND ' . $searchresultSQL;
            $searchSQLCount .= ' AND ' . $searchresultSQL;
        }

        $sql = $searchSQL.' '.$sort_text.' '.$order_text;
        if ($count_only) {
            $sql = $searchSQLCount;
        }
        //$searchSQLCount = $searchSQLCount;
        // We now have a complete SQL Query. Now grab the results
        $process_time = $misc ->getmicrotime();
        //echo 'Limit: '.$limit .'<br>';
        //echo 'Offset: '.$offset;

        if ($limit > 0) {
            $recordSet = $conn->SelectLimit($sql, $limit, $offset);
        //$recordSet = $conn->Execute($sql . ' LIMIT '.$limit. ' OFFSET ' .$offset);
        } else {
            $recordSet = $conn->Execute($sql);
        }

        $query_time = $misc->getmicrotime();
        $query_time = $query_time - $process_time;
        $process_time = $process_time - $start_time;
        if ($DEBUG_SQL) {
            echo '<strong>Search Query:</strong> ' . $sql . '<br />';
        }
        if (!$recordSet) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->blog->search','log_message'=>'DB Error: '.$error. ' Full SQL: '.$sql]);
            return ['error' => true,'error_msg'=>'DB Error: '.$error."\r\n".'SQL: '.$sql];
        }
        $blogs_found=[];

        if ($count_only) {
            $blog_count = $recordSet->fields('total_blogs');
        } else {
            $blog_count = $recordSet->RecordCount();
        }
        if (!$count_only) {
            while (!$recordSet->EOF) {
                $blogs_found[]= $recordSet->fields('blogmain_id');
                $recordSet->MoveNext();
            }
        }
        $total_time = $misc->getmicrotime();
        $total_time = $total_time - $start_time;
        $info['process_time']=sprintf('%.3f', $process_time);
        $info['query_time']=sprintf('%.3f', $query_time);
        $info['total_time']=sprintf('%.3f', $total_time);
        return [
            'error' => false,
            'blog_count' => $blog_count,
            'blogs'=>$blogs_found,
            'info'=>$info,
            'sortby'=>$sortby_array,
            'sorttype'=>$sorttype_array,
            'limit'=>$limit,
            'offset'=>$offset, ];
    }


    /**
     * This API Command reads user info
     * @param array $data $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['userdb_id'] - This is the Listing ID that we are updating.</li>
     *      <li>$data['resource'] - This is the resource you want to get fields for. Allowed Options are: 'agent' or 'member'</li>
     *      <li>$data['fields'] - This is an optional array of fields to retrieve, if left empty or not passed all fields will be retrieved.</li>
     *  </ul>
     * @return array
     **/
    public function read($data)
    {
        global $conn, $lapi, $config, $lang, $misc, $page;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->verify_priv('can_access_blog_manager');

        extract($data, EXTR_SKIP || EXTR_REFS, '');

        //sanitize $data input
        //$fields = (filter_var_array($fields,  FILTER_SANITIZE_STRING));
        //print_r ($fields);

        //Check that required settings were passed
        if (!isset($blog_id) || !is_numeric($blog_id)) {
            return ['error' => true, 'error_msg' => 'blog_id: correct_parameter_not_passed'];
        }
        if (isset($fields) && !is_array($fields)) {
            return ['error' => true, 'error_msg' => 'fields: correct_parameter_not_passed'];
        }
        //If no fields were passed make an empty array to save checking for if !isset later
        if (!isset($fields)) {
            $fields=[];
        }

        //This array will hold our blog data
        $blog_data=[];

        if (!$security) {
            $suffix_published = ' AND blogmain_published = \'1\'';
            $suffix_moderated = ' AND blogcomments_moderated = \'1\'';
        } else {
            $suffix_published ='';
            $suffix_moderated = '';
        }

        //Get Base user information
        if (empty($fields)) {
            $sql = 'SELECT '.implode(',', $this->OR_INT_FIELDS).' 
					FROM '.$config['table_prefix'].'blogmain 
					WHERE blogmain_id = '.$blog_id .' 
					'.$suffix_published;

            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->blog->read','log_message'=>'DB Error: '.$error]);
                return ['error' => true,'error_msg'=>'DB Error: '.$error."\r\n".'SQL: '.$sql];
            }

            if ($recordSet->RecordCount() == 1) {
                foreach ($this->OR_INT_FIELDS as $field) {
                    $blog_data[$field]=$recordSet->fields($field);
                }
            }

            //Get Name of Blog Author
            $result = $lapi->load_local_api('user__read', [
                'user_id' => $blog_data['userdb_id'],
                'resource' => 'agent',
                'fields' => [
                    'userdb_user_first_name',
                    'userdb_user_last_name',
                ],
            ]);

            $blog_data['blog_author_firstname'] = $result['user']['userdb_user_first_name'];
            $blog_data['blog_author_lastname'] = $result['user']['userdb_user_last_name'];
            $blog_data['blog_categories'] = $this->get_blog_categories_assignment_names($blog_id);
            $blog_data['blog_post_tags'] = $this->get_blog_tag_assignment($blog_id);

            //get comment count and comments
            $sql = 'SELECT userdb_id,blogcomments_timestamp, blogcomments_text, blogcomments_id, blogcomments_moderated 
					FROM ' . $config['table_prefix'] . 'blogcomments 
					WHERE blogmain_id = '.$blog_id. ' '.
                    $suffix_moderated .'
					ORDER BY blogcomments_timestamp ASC;';
            $recordSet = $conn->Execute($sql);
            if ($recordSet === false) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->blog->read','log_message'=>'DB Error: '.$error]);
                return ['error' => true,'error_msg'=>'DB Error: '.$error."\r\n".'SQL: '.$sql];
            }

            $blog_data['blog_url'] = $page->magicURIGenerator('blog', $blog_id, true);
            $blog_data['blog_comment_count'] = $recordSet->RecordCount();

            while (!$recordSet->EOF) {
                $blog_data['blog_comments'][$recordSet->fields('blogcomments_id')]['userdb_id'] = $recordSet->fields('userdb_id');
                $blog_data['blog_comments'][$recordSet->fields('blogcomments_id')]['blogcomments_id'] = $recordSet->fields('blogcomments_id');
                $blog_data['blog_comments'][$recordSet->fields('blogcomments_id')]['blogcomments_timestamp'] = $recordSet->fields('blogcomments_timestamp');
                $blog_data['blog_comments'][$recordSet->fields('blogcomments_id')]['blogcomments_text'] = $recordSet->fields('blogcomments_text');
                $blog_data['blog_comments'][$recordSet->fields('blogcomments_id')]['blogcomments_moderated'] = $recordSet->fields('blogcomments_moderated');
                $recordSet->MoveNext();
            }
        } else {
            $core_fields = array_intersect($this->OR_INT_FIELDS, $fields);
            $noncore_fields = array_diff($fields, $this->OR_INT_FIELDS);

            if (!empty($core_fields)) {
                $sql = 'SELECT '.implode(',', $core_fields).' 
						FROM '.$config['table_prefix'].'blogmain 
						WHERE blogmain_id = '.$blog_id .' '.$suffix;

                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->blog->read','log_message'=>'DB Error: '.$error]);
                    return ['error' => true,'error_msg'=>'DB Error: '.$error."\r\n".'SQL: '.$sql];
                }

                if ($recordSet->RecordCount() == 1) {
                    foreach ($core_fields as $field) {
                        $blog_data[$field]=$recordSet->fields($field);
                    }
                }
            }
            if (!empty($noncore_fields)) {
                if (in_array('blog_url', $noncore_fields)) {
                    $blog_data['blog_url'] = $page->magicURIGenerator('blog', $blog_id, true);
                }
                // if either of these files is set, do stuff.
                if (in_array('blog_comment_count', $noncore_fields) || in_array('blog_comments', $noncore_fields)) {
                    //if (isset ($noncore_fields['blog_comment_count']) || isset ($noncore_fields['comments']) ) {

                    //get comment count and comments
                    $sql = 'SELECT userdb_id,blogcomments_timestamp, blogcomments_text, blogcomments_id, blogcomments_moderated 
							FROM ' . $config['table_prefix'] . 'blogcomments 
							WHERE blogmain_id = '.$blog_id.' 
							AND blogcomments_moderated = 1 
							ORDER BY blogcomments_timestamp ASC;';
                    $recordSet = $conn->Execute($sql);

                    if ($recordSet === false) {
                        $error = $conn->ErrorMsg();
                        $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->blog->read','log_message'=>'DB Error: '.$error]);
                        return ['error' => true,'error_msg'=>'DB Error: '.$error."\r\n".'SQL: '.$sql];
                    }

                    $blog_data['blog_comment_count'] = $recordSet->RecordCount();

                    // if blog_comments is set generate that stuff.
                    if (in_array('blog_comments', $noncore_fields)) {
                        while (!$recordSet->EOF) {
                            $blog_data['blog_comments'][$recordSet->fields('blogcomments_id')]['userdb_id'] = $recordSet->fields('userdb_id');
                            $blog_data['blog_comments'][$recordSet->fields('blogcomments_id')]['blogcomments_id'] = $recordSet->fields('blogcomments_id');
                            $blog_data['blog_comments'][$recordSet->fields('blogcomments_id')]['blogcomments_timestamp'] = $recordSet->fields('blogcomments_timestamp');
                            $blog_data['blog_comments'][$recordSet->fields('blogcomments_id')]['blogcomments_text'] = $recordSet->fields('blogcomments_text');
                            $blog_data['blog_comments'][$recordSet->fields('blogcomments_id')]['blogcomments_moderated'] = $recordSet->fields('blogcomments_moderated');
                            $recordSet->MoveNext();
                        }
                    }
                }
                if (in_array('blog_author_firstname', $noncore_fields) || in_array('blog_author_lastname', $noncore_fields)) {
                    //Get Name of Blog Author
                    $result = $lapi->load_local_api('user__read', [
                        'user_id' => $blog_data['userdb_id'],
                        'resource' => 'agent',
                        'fields' => [
                            'userdb_user_first_name',
                            'userdb_user_last_name',
                        ],
                    ]);

                    if (in_array('blog_author_firstname', $noncore_fields)) {
                        $blog_data['blog_author_firstname'] = $result['user']['userdb_user_first_name'];
                    }
                    if (in_array('blog_author_lastname', $noncore_fields)) {
                        $blog_data['blog_author_lastname'] = $result['user']['userdb_user_last_name'];
                    }
                }
                if (in_array('blog_categories', $noncore_fields)) {
                    $blog_data['blog_categories'] = $this->get_blog_categories_assignment_names($blog_id);
                }
                if (in_array('blog_post_tags', $noncore_fields)) {
                    $blog_data['blog_post_tags'] = $this->get_blog_tag_assignment($blog_id);
                }
            }
        }
        return ['error' => false,'blog'=>$blog_data];
    }

    /**
     * This API Command deletes blog articles.
     * @param array $data expects an array containing the following array keys.
     *  <ul>
     *      <li>$data['blog_id'] - Number - blogmain_id# to delete</li>
     *  <ul>
     */
    public function delete($data)
    {
        global $conn, $config, $lang, $lapi, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->verify_priv('can_access_blog_manager');
        // We may need to work out permissions here, the login_status
        // check above presently allows an Admin, Author, Contributor and Editor to do this.
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure'];
        }

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        //Check that required settings were passed
        if (!isset($blog_id)||!is_numeric($blog_id)) {
            return ['error' => true, 'error_msg' => $blog_id.' Invalid Blog ID'];
        }

        // Set Variable to hold errors
        $errors = '';

        if ($config['demo_mode'] == 1 && $_SESSION['admin_privs'] != 'yes') {
            return ['error' => true, 'error_msg' => $lang['demo_mode'] . ' - ' . 'Permission Denied'];
        }

        if ($login_status === true) {
            $blog_id = intval($blog_id);

            $sql = 'SELECT blogmain_id 
					FROM ' . $config['table_prefix'] . 'blogmain  
					WHERE blogmain_id = ' . $blog_id . '';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            if ($recordSet->RecordCount() == 0) {
                return ['error' => true,'error_msg'=>'Blog Article does not exist'];
            }

            $sql = 'DELETE FROM ' . $config['table_prefix'] . 'blogmain  
					WHERE blogmain_id = ' . $blog_id . '';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . 'blogtag_relation  
					WHERE blogmain_id = ' . $blog_id . '';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $sql = 'DELETE FROM ' . $config['table_prefix_no_lang'] . 'blogcategory_relation  
					WHERE blogmain_id = ' . $blog_id . '';
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            //delete the Blog Media Folder
            $dir = $config['basepath'].'/images/blog_uploads/'.$blog_id;
            if ($dir == $config['basepath']) {
                return ['error' => true,'error_msg'=>'Missing media folder'];
            } else {
                if (!$misc->recurseRmdir($dir)) {
                    $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->blog->delete','log_message'=>'Unable To Delete Media Folder: '.$dir . ' For Blog: '.$blog_id ]);
                    return ['error' => true,'error_msg'=>'Unable to delete media folder'];
                }
            }
        }

        // ta da! we're done...
        $lapi->load_local_api('log__log_create_entry', [
            'log_type'=>'CRIT',
            'log_api_command'=>'api->blog->delete',
            'log_message'=>'Deleted ' . $blog_id.' by '.$_SESSION['username'],
        ]);

        include_once $config['basepath'] . '/include/hooks.inc.php';
        $hooks = new hooks();
        $hooks->load('after_blog_delete', $delete_id);

        return ['error' => false,'blog_id' => $blog_id];
    }

    /*
    *
    *   PRIVATE FUNCTIONS
    */
    private function get_publisher_status($user_id)
    {
        global $lapi;

        $result = $lapi->load_local_api('user__read', [
            'user_id'=>$user_id,
            'resource' => 'agent',
            'fields'=>['userdb_blog_user_type'],
        ]);

        $pub_status = $result['user']['userdb_blog_user_type'];

        if (is_numeric($pub_status)) {
            return $pub_status;
        } else {
            return 0;
        }
    }

    private function get_blog_categories_assignment_names($blog_id)
    {
        global $conn, $config, $misc;

        $sql = 'SELECT ' . $config['table_prefix'] . 'blogcategory.category_id, category_name 
				FROM ' . $config['table_prefix_no_lang'] . 'blogcategory_relation, ' . $config['table_prefix'] . 'blogcategory 
				WHERE ' . $config['table_prefix_no_lang'] . 'blogcategory_relation.category_id =  ' . $config['table_prefix'] . 'blogcategory.category_id 
				AND blogmain_id = '.intval($blog_id);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $assigned=[];
        while (!$recordSet->EOF) {
            $assigned[$recordSet->fields('category_id')]= $recordSet->fields('category_name');
            $recordSet->MoveNext();
        }
        return $assigned;
    }

    private function get_blog_tag_assignment($blog_id)
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

        $sql = 'SELECT ' . $config['table_prefix_no_lang'] . 'blogtag_relation.tag_id, tag_name, tag_seoname, tag_description 
				FROM ' . $config['table_prefix_no_lang'] . 'blogtag_relation 
				LEFT JOIN ' . $config['table_prefix'] . 'blogtags 
				ON ' . $config['table_prefix_no_lang'] . 'blogtag_relation.tag_id = ' . $config['table_prefix'] . 'blogtags.tag_id 
				WHERE blogmain_id = '.intval($blog_id);
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }

        $assigned=[];
        while (!$recordSet->EOF) {
            $tag_id = $recordSet->fields('tag_id');
            $tag_name = $recordSet->fields('tag_name');
            $tag_seoname = $recordSet->fields('tag_seoname');
            $tag_description = $recordSet->fields('tag_description');
            //Get Tag LInk
            $tag_link=$page->magicURIGenerator('blog_tag', $tag_id, true);
            //Get Tag Population
            $assigned[$tag_id]= ['tag_name' => $tag_name,'tag_seoname' => $tag_seoname, 'tag_description' =>  $tag_description, 'tag_link'=>$tag_link];
            $recordSet->MoveNext();
        }
        return $assigned;
    }
}
