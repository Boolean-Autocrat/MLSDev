<?php
/**
 * This is the Page  API, it contains all api calls for creating and deleting
 * Page Editor Pages.
 *
 * @package Open-Realty
 * @subpackage API
 **/

class page_api
{
    /**
     * This API Command creates Page Editor pages.
     * @param array $data $data expects an array containing the following array keys.
     *
     *  $data['pagesmain_title'] - TEXT - REQUIRED - The Friendly Title of your Page, e.g.: "About us".
     *  $data['pagesmain_full'] - TEXT - OPTIONAL - The HTML content (markup) to be used for the Page".
     *  $data['pagesmain_published'] - INT - OPTIONAL - The numeric publish status of the Page. 0 = Draft  1= Live. Default value is: 0/Draft
     *  $data['pagesmain_description'] TEXT - OPTIONAL - HTML Meta Description. A 160 character or less summary of the page contents.
     *  $data['pagesmain_keywords'] TEXT - OPTIONAL - Comma delimited list of HTML Meta Keywords.
     *
     * @return array
     *
     */
    public function create($data)
    {
        global $conn, $config, $misc, $lapi, $lang;
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->verify_priv('editpages');
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure/no permission'];
        }

        $save_pagesmain_full = "''";
        $save_pagesmain_published ='';
        $save_pagesmain_description ='';
        $save_pagesmain_keywords ='';

        extract($data, EXTR_SKIP || EXTR_REFS, '');

        // this field is required, everything else is optional
        if (!isset($pagesmain_title) || !is_string($pagesmain_title) || trim($pagesmain_title) == '') {
            return ['error' => true, 'error_msg' => 'pagesmain_title: correct_parameter_not_passed'];
        } else {
            $save_pagesmain_title = $misc->make_db_safe($pagesmain_title);
        }

        if (isset($pagesmain_full) && !empty($pagesmain_full)) {
            $save_full_xhtml = $pagesmain_full;
            //Replace Paths with template tags
            $save_full_xhtml = str_replace($config['template_url'], '{template_url}', $save_full_xhtml);
            $save_full_xhtml = str_replace($config['baseurl'], '{baseurl}', $save_full_xhtml);
            $save_pagesmain_full = $conn->qstr($save_full_xhtml);
        }
        if (isset($pagesmain_published) && !empty($pagesmain_published)) {
            $save_pagesmain_published = intval($pagesmain_published);
        }
        if (isset($pagesmain_description) && !empty($pagesmain_description)) {
            $save_pagesmain_description =  $misc->make_db_safe($pagesmain_description);
        }
        if (isset($pagesmain_keywords) && !empty($pagesmain_keywords)) {
            $save_pagesmain_keywords =  $misc->make_db_safe($pagesmain_keywords);
        }

        //Make sure page Title is unique.
        $sql = 'SELECT pagesmain_id FROM ' . $config['table_prefix'] . "pagesmain 
				WHERE STRCMP(pagesmain_title, $save_pagesmain_title) = 0";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->page->create','log_message'=>'DB Error: '.$error]);
            return ['error' => true,'error_msg'=>'DB Error: '.$error."\r\n".'SQL: '.$sql];
        }
        if ($recordSet->RecordCount() >0) {
            header('Content-type: application/json');
            //return json_encode(array('error' => "1",'error_msg' => "$lang[page_title_not_unique]"));
            return ['error' => true, 'error_msg' => 'pagesmain_id: '.$lang['page_title_not_unique'].''];
        }

        //Generate seo URL
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        $seotitle = $page->create_seouri($pagesmain_title, false);
        $sql_seotitle = $misc->make_db_safe($seotitle);

        $sql = 'INSERT INTO ' . $config['table_prefix'] . "pagesmain
		                (pagesmain_full, pagesmain_title, pagesmain_date, pagesmain_published, pagesmain_description, pagesmain_keywords, page_seotitle)
		        VALUES (".$save_pagesmain_full.", $save_pagesmain_title, ".strtotime('now').", $save_pagesmain_published, $save_pagesmain_description, $save_pagesmain_keywords, $sql_seotitle)";
        //('',$save_title," . strtotime("now") . ",0,'','',".$sql_seotitle.")";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->page->create','log_message'=>'DB Error: '.$error]);
            return ['error' => true,'error_msg'=>'DB Error: '.$error."\r\n".'SQL: '.$sql];
        }

        $pagesmain_id = $conn->Insert_ID();

        if (!file_exists($config['basepath'] . '/images/page_upload/'.$pagesmain_id)) {
            mkdir($config['basepath'] . '/images/page_upload/'.$pagesmain_id);
        }

        //Verify the SEO title is unique
        $sql = 'SELECT pagesmain_id FROM '. $config['table_prefix'] . 'pagesmain 
				WHERE page_seotitle = '.$sql_seotitle.' 
				AND pagesmain_id <> '.$pagesmain_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->page->create','log_message'=>'DB Error: '.$error]);
            return ['error' => true,'error_msg'=>'DB Error: '.$error."\r\n".'SQL: '.$sql];
        }

        if ($recordSet->RecordCount() >0) {
            $seotitle =  $page->create_seouri($title.'-'.$pagesmain_id, false);
            $sql_seotitle =  $misc->make_db_safe($seotitle);
            $sql = 'UPDATE ' . $config['table_prefix'] . "pagesmain 
					SET page_seotitle = $sql_seotitle 
					WHERE pagesmain_id = ".$pagesmain_id;
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->page->create','log_message'=>'DB Error: '.$error]);
                return ['error' => true,'error_msg'=>'DB Error: '.$error."\r\n".'SQL: '.$sql];
            }
        }

        $lapi->load_local_api('log__log_create_entry', ['log_type'=>'INFO','log_api_command'=>'api->page->create','log_message'=>'Page Created: '.$pagesmain_title.'('.$pagesmain_id.')']);
        return ['error' => false, 'pagesmain_id' => $pagesmain_id];
    }

    /**
     * This API Command deletes listings.
     * @param array $data $data expects an array containing the following array key.
     *
     * $data['pagesmain_id'] - INT - REQUIRED - Listing ID to delete
     *
     */
    public function delete($data)
    {
        global $conn, $config, $lapi;

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        if (!isset($pagesmain_id) || !is_numeric($pagesmain_id)) {
            return ['error' => true, 'error_msg' => 'pagesmain_id: correct_parameter_not_passed'];
        }

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $display='';

        $login_status = $login->verify_priv('editpages');
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure/no permission'];
        }
        //Don't allow deletion of Page 1 as it is the index page
        if ($pagesmain_id == 1) {
            return ['error' => true, "error_msg' => $lang[page_1_can_not_delete]"];
            //return json_encode(array('error' => "1",'error_msg' => $lang['page_1_can_not_delete']));
        }
        $sql = 'DELETE FROM ' . $config['table_prefix'] . 'pagesmain  
				WHERE pagesmain_id = ' . $pagesmain_id . '';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->page->delete','log_message'=>'DB Error: '.$error]);
            return ['error' => true,'error_msg'=>'DB Error: '.$error."\r\n".'SQL: '.$sql];
        } else {
            //get any files
            $dir = $config['basepath'].'/images/page_upload/'.$pagesmain_id;
            if ($dir == $config['basepath']) {
                return ['error' => false, 'error_msg'=> "$pagesmain_id No folder present"];
            //return json_encode(array('error' => "1",'blog_id' => $pagesmain_id .'No folder present'));
            } else {
                if (!$misc->recurseRmdir($dir)) {
                    $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->blog->delete','log_message'=>'Unable To Delete Media Folder: '.$dir . ' For Page: '.$pagesmain_id ]);
                    return ['error' => true,'error_msg'=>'Unable to delete media folder'];
                }
            }
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'INFO','log_api_command'=>'api->page->delete','log_message'=>'Page Deleted: '.$pagesmain_id]);
            return ['error' => false, 'pagesmain_id' => $pagesmain_id];
        }
    }

    public function update($data)
    {
        global $conn, $config, $lapi;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $display='';

        $login_status = $login->verify_priv('editpages');
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure/no permission'];
        }

        global $misc, $lang;

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        if (!isset($pagesmain_id) || !is_numeric($pagesmain_id)) {
            return ['error' => true, 'error_msg' => 'pagesmain_id: correct_parameter_not_passed'];
        }
        if (!isset($pagesmain_id) && !is_numeric($pagesmain_id)) {
            return ['error' => true, 'error_msg' => 'pagesmain_title: correct_parameter_not_passed'];
        } else {
            $pagesmain_id = intval($pagesmain_id);
        }

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();

        if (isset($pagesmain_title) && !empty($pagesmain_title)) {
            $sql_pagesmain_title = intval($pagesmain_title);

            //Make sure Page Title is unique.
            $sql = 'SELECT pagesmain_id FROM ' . $config['table_prefix'] . "pagesmain 
					WHERE STRCMP(pagesmain_title, $sql_pagesmain_title) = 0 
					AND pagesmain_id <> $pagesmain_id";
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $error = $conn->ErrorMsg();
                $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->page->update','log_message'=>'DB Error: '.$error]);
                return ['error' => true,'error_msg'=>'DB Error: '.$error."\r\n".'SQL: '.$sql];
            }
            if ($recordSet->RecordCount() >0) {
                return ['error' => true, 'error_msg' => 'pagesmain_title: '.$lang['page_title_not_unique'].''];
            } else {
                $sql_fields['pagesmain_title'] = $misc->make_db_safe($pagesmain_title);
                $page_seotitle = $page->create_seouri($pagesmain_title, false);
                $sql_fields['page_seotitle'] = $misc->make_db_safe($page_seotitle);
            }
        }
        if (isset($pagesmain_full) && !empty($pagesmain_full)) {
            $save_full_xhtml = $pagesmain_full;
            //Replace Paths with template tags
            $save_full_xhtml = str_replace($config['template_url'], '{template_url}', $save_full_xhtml);
            $save_full_xhtml = str_replace($config['baseurl'], '{baseurl}', $save_full_xhtml);
            $save_pagesmain_full = $conn->qstr($save_full_xhtml);
            $sql_fields['pagesmain_full'] = $save_pagesmain_full;
        }
        if (isset($pagesmain_published) && !empty($pagesmain_published)) {
            $publish_status = $pagesmain_published;
            $sql_fields['pagesmain_published'] = intval($pagesmain_published);
        }
        if (isset($pagesmain_description) && !empty($pagesmain_description)) {
            $sql_fields['pagesmain_description'] = $misc->make_db_safe($pagesmain_description);
        }
        if (isset($pagesmain_keywords) && !empty($pagesmain_keywords)) {
            $sql_fields['pagesmain_keywords'] = $misc->make_db_safe($pagesmain_keywords);
        }
        if (isset($page_seotitle) && !empty($page_seotitle)) {
            $sql_page_seotitle = $misc->make_db_safe($page_seotitle);
            $sql_fields['page_seotitle'] = $sql_page_seotitle;
        }
        //Verify the SEO title is unique.
        $sql = 'SELECT pagesmain_id FROM '. $config['table_prefix'] . 'pagesmain 
				WHERE page_seotitle = '.$sql_page_seotitle.' 
				AND pagesmain_id <> '.$pagesmain_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->page->update','log_message'=>'DB Error: '.$error]);
            return ['error' => true,'error_msg'=>'DB Error: '.$error."\r\n".'SQL: '.$sql];
        }
        if ($recordSet->RecordCount() > 0) {
            $seotitle =  $page->create_seouri($page_seotitle.'-'.$pagesmain_id, false);
            $sql_fields['page_seotitle'] = $misc->make_db_safe($seotitle);
        }
        foreach ($sql_fields as $field => $value) {
            $sql_a[] = $field.' = '.$value;
        }

        $sql = 'UPDATE ' . $config['table_prefix'] . 'pagesmain 
				SET '.implode(',', $sql_a).' 
				WHERE pagesmain_id = ' . $pagesmain_id . '';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $error = $conn->ErrorMsg();
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'CRIT','log_api_command'=>'api->page->update','log_message'=>'DB Error: '.$error]);
            return ['error' => true,'error_msg'=>'DB Error: '.$error."\r\n".'SQL: '.$sql];
        } else {
            $lapi->load_local_api('log__log_create_entry', ['log_type'=>'INFO','log_api_command'=>'api->page->update','log_message'=>'Page Updated: '.$pagesmain_title.'('.$pagesmain_id.')']);
            return ['error' => false, 'pagesmain_id' => $pagesmain_id];
        }
    }
}
