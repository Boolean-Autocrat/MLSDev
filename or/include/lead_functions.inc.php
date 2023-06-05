<?php

class lead_functions
{
    public function updateFeedbackData($new_feedback_id, $user)
    {
        // UPDATES THE FEEDBACK INFORMATION
        global $conn, $config, $misc;

        $sql_feedback_id = $misc->make_db_safe($new_feedback_id);

        $sql = 'DELETE FROM ' . $config['table_prefix'] . "feedbackdbelements
				WHERE feedbackdb_id = $sql_feedback_id";
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $misc->log_error($sql);
        }

        global $_POST;
        reset($_POST);

        foreach ($_POST as $ElementIndexValue => $ElementContents) {
            // first, ignore all the stuff that's been taken care of above

            if ($ElementIndexValue == 'listingID') {
                // do nothing
            }
            if ($ElementIndexValue == 'notes') {
                // do nothing
            } elseif ($ElementIndexValue == 'formaction') {
                // do nothing
            } elseif ($ElementIndexValue == 'captcha_code') {
                // do nothing
            } elseif (preg_match('/asmSelect[0-9]+/i', $ElementIndexValue)) {
                // do nothing
            } elseif ($ElementIndexValue == 'PHPSESSID') {
                // do nothing
            } elseif ($ElementIndexValue == 'edit') {
                // do nothing
            } elseif ($ElementIndexValue == 'edit_expiration') {
                // do nothing
            } elseif ($ElementIndexValue == 'user') {
                // do nothing
            } elseif (is_array($ElementContents)) {
                // deal with checkboxes & multiple selects elements
                $feature_insert = '';
                foreach ($ElementContents as $featureValue => $feature_item) {
                    $feature_insert = "$feature_insert||$feature_item";
                } // end while

                // now remove the first two characters
                $feature_insert_length = strlen($feature_insert);
                $feature_insert_length = $feature_insert_length - 2;
                $feature_insert = substr($feature_insert, 2, $feature_insert_length);
                $sql_ElementIndexValue = $misc->make_db_safe($ElementIndexValue);
                $sql_feature_insert = $misc->make_db_safe($feature_insert);
                $sql_owner = $misc->make_db_safe($user);
                $sql_feedback_id = $misc->make_db_safe($new_feedback_id);

                $sql = 'INSERT INTO ' . $config['table_prefix'] . 'feedbackdbelements (feedbackdbelements_field_name, feedbackdbelements_field_value, feedbackdb_id, userdb_id)
                		VALUES (' . $sql_ElementIndexValue . ', ' . $sql_feature_insert . ', ' . $sql_feedback_id . ', ' . $sql_owner . ')';
                $recordSet = $conn->Execute($sql);

                if (!$recordSet) {
                    $misc->log_error($sql);
                }
            } // end elseif
            else {
                // process the form
                $sql_ElementIndexValue = $misc->make_db_safe($ElementIndexValue);
                $sql_ElementContents = $misc->make_db_safe($ElementContents);
                $sql_owner = $misc->make_db_safe($user);
                $sql_feedback_id = $misc->make_db_safe($new_feedback_id);

                $sql = 'INSERT INTO ' . $config['table_prefix'] . "feedbackdbelements (feedbackdbelements_field_name, feedbackdbelements_field_value, feedbackdb_id, userdb_id)
						VALUES ($sql_ElementIndexValue, $sql_ElementContents, $sql_feedback_id, $sql_owner)";
                $recordSet = $conn->Execute($sql);

                if (!$recordSet) {
                    $misc->log_error($sql);
                }
            } // end else
        } // end while

        return 'success';
    }

    public function renderFeedbackTemplateArea($templateArea, $feedback_id)
    {
        global $conn, $config, $misc;
        $output = '';
        // renders all the elements in a given template area on the feedback pages
        $feedback_id = intval($feedback_id);
        $templateArea = $misc->make_db_extra_safe($templateArea);
        $sql = 'SELECT dbe.field_value, fe.field_type, fe.field_caption, fe.location
        		FROM ' .  $config['table_prefix']  . 'feedbackdbelements dbe, ' .  $config['table_prefix']  . "feedbackformelements fe
        		WHERE ((dbe.feedback_id = $feedback_id)
        		AND (fe.field_name = dbe.field_name)
        		AND (fe.location = $templateArea))
        		ORDER BY fe.rank ASC";
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $misc->log_error($sql);
        }

        while (!$recordSet->EOF) {
            $field_value = $recordSet->fields('field_value');
            $field_type = $recordSet->fields('field_type');
            $field_caption = $recordSet->fields('field_caption');
            $location = $recordSet->fields('location');

            if ($field_value != '') {
                if ($field_type == 'select-multiple' or $field_type == 'option' or $field_type == 'checkbox') {
                    // handle field types with multiple options
                    $output = "<span class=\"feedback_caption\">$field_caption</span><br />";
                    $feature_index_list = explode('||', $field_value);

                    foreach ($feature_index_list as $feature_list_key => $feature_list_value) {
                        $safe_feature_list_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                        $output .= "<span class=\"feedback_data\">$safe_feature_list_value</span><br />";
                    } // end while
                } // end if field type is a multiple type
                elseif ($field_type == 'price') {
                    $money_amount = $misc->international_num_format($field_value);
                    $output .= "<br /><span class=\"feedback_caption\">$field_caption:</span> " . $misc->money_formats($money_amount);
                } // end elseif
                elseif ($field_type == 'number') {
                    $output .= "<br /><span class=\"feedback_caption\">$field_caption:</span> " . $misc->international_num_format($field_value, 0);
                } // end elseif
                elseif ($field_type == 'url') {
                    $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                    $output .= "<span class=\"feedback_caption\">$field_caption:</span> <a href=\"$field_value\" target=\"_new\"><span class=\"feedback_data\">$field_value</span></a><br />";
                } elseif ($field_type == 'email') {
                    $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                    $output .= "<span class=\"feedback_caption\">$field_caption:</span> <a href=\"mailto:$field_value\"><span class=\"feedback_data\">$field_value</span></a><br />";
                } elseif ($field_type == 'text' or $field_type == 'textarea') {
                    $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                    if ($config['add_linefeeds'] == 'yes') {
                        $field_value = nl2br($field_value); //replace returns with <br />
                    } // end if
                    if ($location == 'top_left' or $location == 'top_right' or $location == 'bottom_left' or $location == 'bottom_right' or $location == 'feature1' or $location == 'feature2') {
                        $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                        $output .= "<span class=\"feedback_caption\">$field_caption:</span><span class=\"feedback_data\">$field_value</span><br />\n";
                    } //end if
                } else {
                    $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                    $output .= "<span class=\"feedback_caption\">$field_caption</span>: <span class=\"feedback_data\">$field_value</span><br />\n";
                } // end else
            } // end if ($field_value != "")
            $recordSet->MoveNext();
        } // end while
        return $output;
    }

    public function getMainFeedbackData($feedbackID)
    {
        // get the main data for a given feedback
        global $conn, $config, $misc;
        $feedbackID = $misc->make_db_extra_safe($feedbackID);

        $sql = 'SELECT ' . $config['table_prefix'] . 'feedbackdb.user_id, ' . $config['table_prefix'] . 'UserDB.user_name, ' .  $config['table_prefix']  . 'feedbackdb.last_modified
        		FROM ' .  $config['table_prefix']  . 'feedbackdb, ' .  $config['table_prefix']  . 'UserDB
        		WHERE ((' .  $config['table_prefix']  . "feedbackdb.ID = $feedbackID)
        		AND (" . $config['table_prefix'] . 'UserDB.ID = ' .  $config['table_prefix']  . 'feedbackdb.user_id))';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }

        // get main feedbacks data
        while (!$recordSet->EOF) {
            $feedback_user_id = $recordSet->fields('user_id');
            $feedback_user_name = $recordSet->fields('user_name');
            $feedback_last_modified = $recordSet->UserTimeStamp($recordSet->fields('last_modified'), 'D M j G:i:s T Y');

            $recordSet->MoveNext();
            $output = "<form><input type=\"hidden\" name=\"feedback_last_modified\" value=\"$feedback_last_modified\" /></form>";
        } // end while
    }

    public function renderFeedbackTemplateAreaNoCaption($templateArea, $feedbackID)
    {
        // renders all the elements in a given template area on the feedback pages
        // without captions
        global $conn, $config, $misc;
        $output = '';
        $feedbackID = $misc->make_db_extra_safe($feedbackID);
        $templateArea = $misc->make_db_extra_safe($templateArea);

        $sql = 'SELECT ' .  $config['table_prefix']  . 'feedbackdbelements.field_value, ' .  $config['table_prefix']  . 'feedbackformelements.field_type, ' .  $config['table_prefix']  . 'feedbackformelements.field_caption
        		FROM ' .  $config['table_prefix']  . 'feedbackdbelements, ' .  $config['table_prefix']  . 'feedbackformelements
        		WHERE ((' .  $config['table_prefix']  . "feedbackdbelements.feedback_id = $feedbackID)
        		AND (" .  $config['table_prefix']  . 'feedbackformelements.field_name = ' .  $config['table_prefix']  . 'feedbackdbelements.field_name) AND (' .  $config['table_prefix']  . "feedbackformelements.location = $templateArea))
        		ORDER BY " .  $config['table_prefix']  . 'feedbackformelements.rank ASC';
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $misc->log_error($sql);
        }

        while (!$recordSet->EOF) {
            $field_value = $recordSet->fields('field_value');
            $field_type = $recordSet->fields('field_type');
            $field_caption = $recordSet->fields('field_caption');

            if ($field_value != '') {
                if ($field_type == 'select-multiple' or $field_type == 'option' or $field_type == 'checkbox') {
                    // handle field types with multiple options
                    $feature_index_list = explode('||', $field_value);
                    foreach ($feature_index_list as $feature_list_key => $feature_list_value) {
                        $safe_feature_list_value = htmlentities($feature_list_value, ENT_NOQUOTES, $config['charset']);
                        $output .= "$feature_list_value<br />";
                    } // end while
                } // end if field type is a multiple type
                elseif ($field_type == 'price') {
                    $money_amount = $misc->international_num_format($field_value);
                    $output .= "<br /><strong>$field_caption</strong>: " . $misc->money_formats($money_amount);
                } // end elseif
                elseif ($field_type == 'number') {
                    $output .= "<br /><strong>$field_caption</strong>: " . $misc->international_num_format($field_value, 0);
                } // end elseif
                elseif ($field_type == 'url') {
                    $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                    $output .= "<br /><a href=\"$field_value\" target=\"_new\">$field_value</a>";
                } elseif ($field_type == 'email') {
                    $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                    $output .= "<br /><a href=\"mailto:$field_value\">$field_value</a>";
                } elseif ($field_type == 'text' or $field_type == 'textarea') {
                    $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                    if ($config['add_linefeeds'] == 'yes') {
                        $field_value = nl2br($field_value); //replace returns with <br />
                    } // end if
                    $output .= "<br />$field_value";
                } else {
                    $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                    $output .= "<br />$field_value";
                } // end else
            } // end if ($field_value != "")

            $recordSet->MoveNext();
        } // end while
        return $output;
    }

    public function contactAgentEmailDropdown()
    {
        //Email to Agent Pulldown  Checks to see if this was just a plain request
        //for the contact form or if the request came from one of the Agent's listing
        //pages or from the feedback viewer

        global $conn, $config, $misc;
        $output='';

        if (isset($_GET['userID'])) {
            $user_id = $_GET['userID'];
        } else {
            $user_id = '';
        }

        if (isset($_GET['listingID']) && !empty($_GET['listingID'])) {
            $lid = intval($_GET['listingID']);
        } else {
            $lid = '';
        }

        if (isset($_GET['feedback_id']) && !empty($_GET['feedback_id'])) {
            $fid = intval($_GET['feedback_id']);
        } else {
            $fid = '';
        }

        // $output = '<form id="change_agent" method="post" action="#">
        // 			<div>
        // 			<input type="hidden" name="feedback_id" value="{feedback_id}" />
        // 			<input type="hidden" name="change_agent" value="change_agent" />
        // 			<select name="user" id="user">';
        // $output .= "\n";

        if (!empty($lid)) {
            $sql = 'SELECT userdb_id
        			FROM ' .  $config['table_prefix']  . "listingsdb
        			WHERE listingsdb_id = '" . $lid . "'";
            $recordSet = $conn->Execute($sql);

            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $agent = $recordSet->fields('userdb_id');

            $sql = 'SELECT userdb_user_first_name,  userdb_user_last_name
        			FROM ' .  $config['table_prefix']  . 'userdb
        			WHERE userdb_id = ' . $agent . '';
            $recordSet = $conn->Execute($sql);

            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $first_name = $recordSet->fields('userdb_user_first_name');
            $last_name = $recordSet->fields('userdb_user_last_name');
            $user_name = $first_name . ' ' . $last_name;

            $userID = $recordSet->fields('userdb_id');

            $output .= '<option value="' . $agent . '">' . $user_name . '</option>';
            $output .= "\n";
            $output .= '</select>';
            $output .= "\n";
        } elseif (!empty($fid)) {
            $feedback_id = $_GET['feedback_id'];

            $sql = 'SELECT userdb_id
        			FROM ' . $config['table_prefix'] . "feedbackdb
        			WHERE feedbackdb_id = '" . $feedback_id . "' ";
            $recordSet = $conn->Execute($sql);

            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $user_id = $recordSet->fields('userdb_id');

            // setup the Gen delivery option.
            $output .= '<option value="1">General delivery</option>';
            $output .= "\n";

            // Get the rest of the Agents name
            $sql = 'SELECT userdb_user_first_name,  userdb_user_last_name, userdb_id
        			FROM ' . $config['table_prefix'] . "userdb
        			WHERE userdb_is_agent = 'yes' ";
            $recordSet = $conn->Execute($sql);

            if (!$recordSet) {
                $misc->log_error($sql);
            }

            // get main users data
            while (!$recordSet->EOF) {
                $agent = $recordSet->fields('userdb_id');
                $first_name = $recordSet->fields('userdb_user_first_name');
                $last_name = $recordSet->fields('userdb_user_last_name');
                $user_name = $first_name . ' ' . $last_name;

                if (!empty($user_id) && $user_id == $agent) {
                    $output .= '<option value="' . $agent . '" selected="selected" >' . $user_name . '</option>';
                    $output .= "\n";
                } else {
                    $output .= '<option value="' . $agent . '">' . $user_name . '</option>';
                    $output .= "\n";
                }

                $recordSet->MoveNext();
            } //end while
            $output .= "\n</select>\n";
            $agent = '';
        } else {
            // setup the Gen delivery option.
            $output .= '<option value="1">General delivery</option>';
            $output .= "\n";

            // Get the rest of the Agents name
            $sql = 'SELECT userdb_user_first_name,  userdb_user_last_name, userdb_id
        			FROM ' . $config['table_prefix'] . "userdb
        			WHERE userdb_is_agent = 'yes' ";
            $recordSet = $conn->Execute($sql);

            if (!$recordSet) {
                $misc->log_error($sql);
            }

            // get main users data
            while (!$recordSet->EOF) {
                $agent = $recordSet->fields('userdb_id');
                $first_name = $recordSet->fields('userdb_user_first_name');
                $last_name = $recordSet->fields('userdb_user_last_name');
                $user_name = $first_name . ' ' . $last_name;

                if (!empty($user_id) && $user_id == $agent) {
                    $output .= '<option value="' . $agent . '" selected="selected" >' . $user_name . '</option>';
                    $output .= "\n";
                } else {
                    $output .= '<option value="' . $agent . '">' . $user_name . '</option>';
                    $output .= "\n";
                }

                $recordSet->MoveNext();
            } //end while
            $output .= "\n</select>\n";
            $agent = '';
        }
        $output .= '</div></form>';
        return $output;
    }

    public function getFormAgentSelect()
    {
        //Email to Agent Pulldown  Checks to see if this was just a plain request
        //for the contact form or if the request came from one of the Agent's listing
        //pages or from the feedback viewer

        global $conn, $config, $misc;

        if (isset($_GET['agent_id'])) {
            $user_id = $_GET['agent_id'];
        } else {
            $user_id = '';
        }

        if (isset($_GET['listingID']) && !empty($_GET['listingID'])) {
            $lid = intval($_GET['listingID']);
        } else {
            $lid = '';
        }

        $output = '<select name="user_id" id="user_id">';

        if (!empty($lid)) {
            $sql = 'SELECT userdb_id
        			FROM ' .  $config['table_prefix']  . "listingsdb
        			WHERE listingsdb_id = '" . $lid . "'";
            $recordSet = $conn->Execute($sql);

            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $user_id = $recordSet->fields('userdb_id');

            $sql = 'SELECT userdb_user_first_name,  userdb_user_last_name
        			FROM ' .  $config['table_prefix']  . 'userdb
        			WHERE userdb_id = ' . $user_id . '';
            $recordSet = $conn->Execute($sql);

            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $first_name = $recordSet->fields('userdb_user_first_name');
            $last_name = $recordSet->fields('userdb_user_last_name');
            $user_name = $first_name . ' ' . $last_name;

            $output .= "\n";
            $output .= '<option value="' . $user_id . '">' . $user_name . '</option>';
            $output .= "\n";
            $output .= '</select>';
            $output .= "\n";
        } else {
            // setup the Gen delivery option.
            $output .= '<option value="1">General delivery</option>';
            $output .= "\n";

            // Get the rest of the Agents name
            $sql = 'SELECT userdb_user_first_name,  userdb_user_last_name, userdb_id
        			FROM ' . $config['table_prefix'] . "userdb
        			WHERE userdb_is_agent = 'yes' ";
            $recordSet = $conn->Execute($sql);

            if (!$recordSet) {
                $misc->log_error($sql);
            }

            // get main users data
            while (!$recordSet->EOF) {
                $agent = $recordSet->fields('userdb_id');
                $first_name = $recordSet->fields('userdb_user_first_name');
                $last_name = $recordSet->fields('userdb_user_last_name');
                $user_name = $first_name . ' ' . $last_name;

                if (!empty($user_id) && $user_id == $agent) {
                    $output .= '<option value="' . $agent . '" selected="selected" >' . $user_name . '</option>';
                    $output .= "\n";
                } else {
                    $output .= '<option value="' . $agent . '">' . $user_name . '</option>';
                    $output .= "\n";
                }

                $recordSet->MoveNext();
            } //end while
            $output .= "\n</select>\n";
            $agent = '';
        }
        return $output;
    }

    public function getFeedbackModData($feedbackID, $datetype)
    {
        // get the main data for a given feedback
        global $conn, $config, $misc;
        include_once $config['basepath'] . '/include/user.inc.php';
        $user = new user();

        if ($datetype == 'created') {
            $select_txt = 'feedbackdb_creation_date';
        } elseif ($datetype == 'modified') {
            $select_txt = 'feedbackdb_last_modified';
        } elseif ($datetype == 'modifiedby') {
            $select_txt = 'feedbackdb_last_modified_by';
        } else {
            $select_txt = '';
        }

        $feedbackID = $misc->make_db_extra_safe($feedbackID);

        $sql = 'SELECT ' . $select_txt . '
        		FROM ' .  $config['table_prefix']  . 'feedbackdb
        		WHERE feedbackdb_id = ' . $feedbackID . '';
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }

        // get last_modified date

        if ($datetype == 'modifiedby') {
            $user_id = $recordSet->fields($select_txt);

            if ($user_id == 0) {
                $feedback_last_modified = 'Unmodified';
            } else {
                $feedback_last_modified = $user->get_user_single_item('userdb_user_first_name', $user_id) . ', ' . $user->get_user_single_item('userdb_user_last_name', $user_id);
            }
        } else {
            $feedback_last_modified = $recordSet->UserTimeStamp($recordSet->fields($select_txt), 'm-d-Y g:ia');
        }

        return $feedback_last_modified;
    }

    public function agent_id_to_fullname($user_id)
    {
        global $conn, $config, $misc;

        $sql = 'SELECT userdb_user_first_name, userdb_user_last_name
			FROM ' .  $config['table_prefix'] . 'userdb
			WHERE (userdb_id = ' . $user_id . ')';
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $misc->log_error($sql);
        }

        $agentfname = $recordSet->fields('userdb_user_first_name');
        $agentlname = $recordSet->fields('userdb_user_last_name');
        $full_name =  $agentfname . ' ' . $agentlname;

        return $full_name;
    }

    public function set_notes($feedback_id, $notes)
    {
        global $conn, $config, $misc;

        $feedback_id = intval($feedback_id);
        $sql_notes = $misc->make_db_safe($notes);
        $sql = 'UPDATE  ' .  $config['table_prefix']  . 'feedbackdb
        		SET feedbackdb_notes = ' . $sql_notes . '
        		WHERE feedbackdb_id = ' . intval($feedback_id) . '';
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $misc->log_error($sql);
        }
        return true;
    }

    public function set_feedback_mods($feedback_id)
    {
        global $conn, $config, $misc;

        $sql_user_id = intval($_SESSION['userID']);
        $sql_feedback_id = intval($feedback_id);
        $sql = 'UPDATE  ' .  $config['table_prefix']  . 'feedbackdb
        		SET feedbackdb_last_modified_by = ' . $sql_user_id . ', feedbackdb_last_modified = ' . $conn->DBTimeStamp(time()) . '
        		WHERE feedbackdb_id = ' . $sql_feedback_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        return true;
    }

    public function set_feedback_status($feedback_id, $status)
    {
        global $conn, $config, $misc;

        $sql_feedback_id = intval($feedback_id);
        $sql_status = intval($status);
        $sql = 'UPDATE  ' .  $config['table_prefix']  . 'feedbackdb
        		SET feedbackdb_status = ' . $sql_status . '
        		WHERE feedbackdb_id = ' . $sql_feedback_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        return true;
    }

    public function set_feedback_priority($feedback_id, $priority)
    {
        global $conn, $config, $misc;

        $sql_feedback_id = intval($feedback_id);
        $sql_priority = $misc->make_db_safe($priority);
        $sql = 'UPDATE  ' .  $config['table_prefix']  . 'feedbackdb
        		SET feedbackdb_priority = ' . $sql_priority . '
        		WHERE feedbackdb_id = ' . $sql_feedback_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        return true;
    }

    public function get_leadmanager_priority($feedback_id, $template)
    {
        global $conn, $config, $misc;
        $page = new page_admin();

        if (is_numeric($feedback_id)) {
            $feedback_id = intval($feedback_id);
            $sql = 'SELECT feedbackdb_priority
        			FROM ' .  $config['table_prefix']  . 'feedbackdb
        			WHERE feedbackdb_id = ' . $feedback_id;

            $recordSet = $conn->Execute($sql);

            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $priority = $recordSet->fields('feedbackdb_priority');
            $priority_options = ['Low' => 'Low', 'Normal' => 'Normal', 'Urgent' => 'Urgent'];

            $html = $page->get_template_section('leadmanager_priority_block', $template);
            $html = $page->form_options($priority_options, $priority, $html);
            $template = $page->replace_template_section('leadmanager_priority_block', $html, $template);
        }
        return $template;
    }



    public function get_feedback_owner($feedback_id)
    {
        global $conn, $config, $misc;

        $sql_feedback_id = intval($feedback_id);
        $sql = 'SELECT userdb_id
        		FROM ' .  $config['table_prefix']  . "feedbackdb
        		WHERE feedbackdb_id = $sql_feedback_id";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $owner = $recordSet->fields('userdb_id');
        return $owner;
    }

    public function get_feedback_formelements()
    {
        global $conn, $config, $misc, $jscript;

        include_once $config['basepath'] . '/include/forms.inc.php';
        $forms = new forms();

        $output = '';

        //Builds the form  on the add_feedback page
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $sql = 'SELECT feedbackformelements_id, feedbackformelements_field_type, feedbackformelements_field_name, feedbackformelements_field_caption, feedbackformelements_default_text, feedbackformelements_field_elements, feedbackformelements_rank, feedbackformelements_required
    	FROM ' . $config['table_prefix'] . 'feedbackformelements
       	ORDER BY feedbackformelements_rank, feedbackformelements_field_name';
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $misc->log_error($sql);
        }

        while (!$recordSet->EOF) {
            $id = $recordSet->fields('feedbackformelements_id');
            $field_type = $recordSet->fields('feedbackformelements_field_type');
            $field_name = $recordSet->fields('feedbackformelements_field_name');
            $field_caption = $recordSet->fields('feedbackformelements_field_caption');
            $default_text = $recordSet->fields('feedbackformelements_default_text');
            $field_elements = $recordSet->fields('feedbackformelements_field_elements');
            $rank = $recordSet->fields('feedbackformelements_rank');
            $required = $recordSet->fields('feedbackformelements_required');
            $field_type = $field_type;
            $field_name = $field_name;
            $field_caption = $field_caption;
            $default_text = $default_text;
            $field_elements = $field_elements;
            $required = $required;

            //renderFormElement($field_type, $field_name, $field_value, $field_caption, $default_text, $required, $field_elements, $field_length='', $tool_tip='')
            $field_value = '';
            //print_r($_POST);
            if (isset($_POST[$field_name])) {
                $field_value = htmlentities($_POST[$field_name], ENT_COMPAT, $config['charset']);
            }
            $output .= $forms->renderFormElement($field_type, $field_name, $field_value, $field_caption, $default_text, $required, $field_elements);

            $recordSet->MoveNext();
        } // end while

        return $output;
    }

    public function renderSingleFeedbackItem($feedbackID, $name, $display_type = 'both')
    {
        // renders a single item on the view leads page
        // includes the caption
        global $conn, $config, $misc, $lang;
        $display = '';
        $feedbackID = intval($feedbackID);
        $name = $misc->make_db_extra_safe($name);
        $sql = 'SELECT feedbackdbelements_field_value, feedbackformelements_field_type, feedbackformelements_field_caption
				FROM ' .  $config['table_prefix']  . 'feedbackdbelements, ' .  $config['table_prefix']  . "feedbackformelements
				WHERE ((feedbackdb_id = $feedbackID)
				AND (feedbackformelements_field_name = feedbackdbelements_field_name)
				AND (feedbackdbelements_field_name = " . $name . '))';
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $misc->log_error($sql);
        }

        while (!$recordSet->EOF) {
            $field_value = $recordSet->fields('feedbackdbelements_field_value');
            $field_type = $recordSet->fields('feedbackformelements_field_type');
            $field_caption = $recordSet->fields('feedbackformelements_field_caption');

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
                                $display .= htmlentities($feature_list_item, ENT_NOQUOTES, $config['charset']);
                                $display .= $config['feature_list_separator'];
                                $l++;
                            } else {
                                $display .= htmlentities($feature_list_item, ENT_NOQUOTES, $config['charset']);
                            }
                        } // end while
                    } // end if field type is a multiple type
                    elseif ($field_type == 'price') {
                        $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                        $money_amount = $misc->international_num_format($field_value, $config['number_decimals_price_fields']);
                        $display .= $misc->money_formats($money_amount);
                    } // end elseif
                    elseif ($field_type == 'number') {
                        $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                        $display .= $misc->international_num_format($field_value, $config['number_decimals_number_fields']);
                    } // end elseif
                    elseif ($field_type == 'url') {
                        $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                        $display .= "<a href=\"$field_value\" onclick=\"window.open(this.href,'_blank','location=1,resizable=1,status=1,scrollbars=1,toolbar=1,menubar=1,noopener,noreferrer');return false\">$field_value</a>";
                    } elseif ($field_type == 'email') {
                        $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                        $display .= "<a href=\"mailto:$field_value\">$field_value</a>";
                    } elseif ($field_type == 'text' or $field_type == 'textarea') {
                        $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                        if ($config['add_linefeeds'] === '1') {
                            $field_value = nl2br($field_value); //replace returns with <br />
                        } // end if
                        $display .= $field_value;
                    } elseif ($field_type == 'date') {
                        $field_value = $misc->convert_timestamp($field_value);
                        $display .= "<br />$field_value";
                    } else {
                        $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                        $display .= $field_value;
                    } // end else
                }
                if ($display_type === 'rawvalue') {
                    $display .= $field_value;
                }
            } else {
                if ($field_type == 'price' && $display_type !== 'rawvalue' && $config['zero_price'] == '1') {
                    $display .= $lang['call_for_price'] . '<br />';
                } // end if
            } // end else
            $recordSet->MoveNext();
        } // end while
        return $display;
    }

    public function send_agent_lead_assigned_notice($feedback_id, $modified_by)
    {
        global $conn, $config, $misc;

        include_once $config['basepath'] . '/include/user.inc.php';
        $user = new user();
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $page->load_page($config['admin_template_path'] . '/email/lead_assigned.html');
        $sql_feedback_id = intval($feedback_id);
        $lead_url = $config['baseurl'] . '/admin/index.php?action=leadmanager_my_feedback_edit&feedback_id=' . $feedback_id;
        $via = $_SERVER['REMOTE_ADDR'] . ' -- ' . date('F j, Y, g:i:s a');
        //Lookup Lead Information
        $sql = 'SELECT listingdb_id, userdb_id, feedbackdb_member_userdb_id FROM ' . $config['table_prefix'] . 'feedbackdb WHERE feedbackdb_id = ' . $sql_feedback_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $agent_id = $recordSet->fields('userdb_id');
        $agent_email = $user->get_user_single_item('userdb_emailaddress', $agent_id);
        $member_id = $recordSet->fields('feedbackdb_member_userdb_id');
        $listing_id = $recordSet->fields('listingdb_id');

        $page->replace_tag('lead_id', $sql_feedback_id);
        $page->replace_tag('lead_url', $lead_url);
        $page->replace_tag('via', $via);

        if ($listing_id > 0) {
            $page->replace_listing_field_tags($listing_id);
            $page->page = $page->remove_template_block('agent_contact', $page->page);
            $page->page = $page->cleanup_template_block('listing_contact', $page->page);
        } else {
            $page->page = $page->remove_template_block('listing_contact', $page->page);
            $page->page = $page->cleanup_template_block('agent_contact', $page->page);
        }
        $page->replace_lead_field_tags($sql_feedback_id);
        $page->replace_user_field_tags($member_id);
        $page->replace_user_field_tags($modified_by, '', 'assigned_by');
        $subject = $page->get_template_section('subject_block');
        $page->page = $page->remove_template_block('subject', $page->page);
        $page->auto_replace_tags();
        $message = $page->return_page();
        if (isset($config['site_email']) && $config['site_email'] != '') {
            $sender_email = $config['site_email'];
        } else {
            $sender_email = $config['admin_email'];
        }
        $misc->send_email($config['admin_name'], $sender_email, $agent_email, $message, $subject, true);
    }

    public function send_agent_feedback_notice($feedback_id)
    {
        // UPDATES THE FEEDBACK INFORMATION
        global $conn, $config, $misc;

        include_once $config['basepath'] . '/include/user.inc.php';
        $user = new user();
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $page->load_page($config['admin_template_path'] . '/email/agent_lead_notification.html');
        $sql_feedback_id = intval($feedback_id);
        $lead_url = $config['baseurl'] . '/admin/index.php?action=leadmanager_my_feedback_edit&feedback_id=' . $sql_feedback_id;
        $via = $_SERVER['REMOTE_ADDR'] . ' -- ' . date('F j, Y, g:i:s a');
        //Lookup Lead Information
        $sql = 'SELECT listingdb_id, userdb_id, feedbackdb_member_userdb_id 
				FROM ' . $config['table_prefix'] . 'feedbackdb 
				WHERE feedbackdb_id = ' . $sql_feedback_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $agent_id = $recordSet->fields('userdb_id');
        $agent_email = $user->get_user_single_item('userdb_emailaddress', $agent_id);
        $member_id = $recordSet->fields('feedbackdb_member_userdb_id');
        $listing_id = $recordSet->fields('listingdb_id');

        $page->replace_tag('lead_id', $sql_feedback_id);
        $page->replace_tag('lead_url', $lead_url);
        $page->replace_tag('via', $via);
        if ($listing_id > 0) {
            $page->replace_listing_field_tags($listing_id);
            $page->page = $page->remove_template_block('agent_contact', $page->page);
            $page->page = $page->cleanup_template_block('listing_contact', $page->page);
        } else {
            $page->page = $page->remove_template_block('listing_contact', $page->page);
            $page->page = $page->cleanup_template_block('agent_contact', $page->page);
        }
        $page->replace_lead_field_tags($sql_feedback_id);
        $page->replace_user_field_tags($member_id);
        //Get Member Email and First and Last Name.
        $member_email = $user->get_user_single_item('userdb_emailaddress', $member_id);
        $member_name = $user->get_user_single_item('userdb_user_last_name', $member_id) . ', ' . $user->get_user_single_item('userdb_user_first_name', $member_id);

        $subject = $page->get_template_section('subject_block');
        $page->page = $page->remove_template_block('subject', $page->page);
        $page->auto_replace_tags();
        $message = $page->return_page();
        if (isset($config['site_email']) && $config['site_email'] != '') {
            $sender_email = $config['site_email'];
        } else {
            $sender_email = $config['admin_email'];
        }
        $misc->send_email($config['admin_name'], $sender_email, $agent_email, $message, $subject, true, false, $member_name, $member_email);
        return 'success';
    }

    public function send_user_feedback_notice($feedback_id)
    {
        // UPDATES THE FEEDBACK INFORMATION
        global $conn, $config, $misc;

        include_once $config['basepath'] . '/include/user.inc.php';
        $user = new user();
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        $page->load_page($config['admin_template_path'] . '/email/user_lead_notification.html');
        $sql_feedback_id = intval($feedback_id);
        $via = $_SERVER['REMOTE_ADDR'] . ' -- ' . date('F j, Y, g:i:s a');
        //Lookup Lead Information
        $sql = 'SELECT listingdb_id, userdb_id, feedbackdb_member_userdb_id 
				FROM ' . $config['table_prefix'] . 'feedbackdb 
				WHERE feedbackdb_id = ' . $sql_feedback_id;
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $agent_id = $recordSet->fields('userdb_id');

        $member_id = $recordSet->fields('feedbackdb_member_userdb_id');
        $member_email = $user->get_user_single_item('userdb_emailaddress', $member_id);
        $listing_id = $recordSet->fields('listingdb_id');

        $page->replace_tag('lead_id', $sql_feedback_id);
        $page->replace_tag('via', $via);

        if ($listing_id > 0) {
            $page->replace_listing_field_tags($listing_id);
            $page->page = $page->remove_template_block('agent_contact', $page->page);
            $page->page = $page->cleanup_template_block('listing_contact', $page->page);
        } else {
            $page->page = $page->remove_template_block('listing_contact', $page->page);
            $page->page = $page->cleanup_template_block('agent_contact', $page->page);
        }
        $page->replace_lead_field_tags($sql_feedback_id);
        $page->replace_user_field_tags($member_id);
        $page->replace_user_field_tags($agent_id, '', 'agent');
        $subject = $page->get_template_section('subject_block');
        $page->page = $page->remove_template_block('subject', $page->page);
        $page->auto_replace_tags();
        $message = $page->return_page();
        if (isset($config['site_email']) && $config['site_email'] != '') {
            $sender_email = $config['site_email'];
        } else {
            $sender_email = $config['admin_email'];
        }
        $misc->send_email($config['admin_name'], $sender_email, $member_email, $message, $subject, true);
        return 'success';
    }

    public function get_feedback_field($current)
    {
        global $conn, $config, $misc, $jscript;

        if ($current == '') {
            $selected = '';
        } else {
            $selected = 'selected';
        }

        $jscript .= '

		<script type="text/javascript">
			$(document).ready(function() {
				$("#field_selection_choice").change(function(){
					$("#field_selection").submit();
				});
			});
		</script>
		';
        $output = '<div class="select_lead_field_box">
						<div class="select_lead_field_text">Select field to modify --&gt;</div>

				<div style="float: left; padding: 3px;">
				<form id="field_selection" method="get" action="' . $_SERVER['PHP_SELF'] . '?" >
					<input type="hidden" name="action" value="leadmanager_form_edit" />
					<select id="field_selection_choice" name="ID">
						<option value="" selected="selected">-Add New Field-</option>';
        $output .= "\n";

        $sql = 'SELECT feedbackformelements_id, feedbackformelements_field_caption, feedbackformelements_rank
			FROM ' . $config['table_prefix'] . 'feedbackformelements
			ORDER BY feedbackformelements_rank';
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $misc->log_error($sql);
        }

        while (!$recordSet->EOF) {
            $ID = $recordSet->fields('feedbackformelements_id');
            $field_caption = $recordSet->fields('feedbackformelements_field_caption');
            $rank = $recordSet->fields('feedbackformelements_rank');

            if ($current == $ID) {
                $name = $field_caption;
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }

            $output .= '<option value="' . $ID . '" ' . $selected . '>(' . $rank . ') ' . $field_caption . '</option>';
            $output .= "\n";

            $recordSet->MoveNext();
        }

        $output .= '</select>
				</form>

				</div>';

        if ($current != '') {
            $output .= '<div class="select_lead_field_text"><span class="weight_600">Editing: </span> ' . $name . '</div>';
        } else {
            $output .= '<div class="select_lead_field_text weight_600">Create New</div>';
        }

        $output .= '</div>
		<div class="clear"></div>';

        return $output;
    }

    public function feedback_example()
    {
        global $conn, $config, $misc;
        include_once $config['basepath'] . '/include/forms.inc.php';
        $forms = new forms();
        $output = '<div class="user_preview">
						<span class="user_preview_title">Simple preview of Lead form</span>';

        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $sql = 'SELECT feedbackformelements_id, feedbackformelements_field_type, feedbackformelements_field_name, feedbackformelements_field_caption,
						feedbackformelements_default_text, feedbackformelements_field_elements, feedbackformelements_rank, feedbackformelements_required
				FROM ' . $config['table_prefix'] . 'feedbackformelements
				ORDER BY feedbackformelements_rank, feedbackformelements_field_name';
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $misc->log_error($sql);
        }

        while (!$recordSet->EOF) {
            $ID = $recordSet->fields('feedbackformelements_id');
            $field_type = $recordSet->fields('feedbackformelements_field_type');
            $field_name = $recordSet->fields('feedbackformelements_field_name');
            $field_caption = $recordSet->fields('feedbackformelements_field_caption');
            $default_text = $recordSet->fields('feedbackformelements_default_text');
            $field_elements = $recordSet->fields('feedbackformelements_field_elements');
            $rank = $recordSet->fields('feedbackformelements_rank');
            $required = $recordSet->fields('feedbackformelements_required');

            $field_type = $field_type;
            $field_name = $field_name;
            $field_caption = $field_caption;
            $default_text = $default_text;
            $field_elements = $field_elements;
            $required = $required;

            $output .= $forms->renderFormElement($field_type, $field_name, '', $field_caption, $default_text, $required, $field_elements);

            $recordSet->MoveNext();
        } // end while

        $output .= '<div class="clear"></div>
		</div>';

        return $output;
    }

    public function reload_page($url, $message, $delay)
    {
        echo '<meta http-equiv="Refresh" content="' . $delay . '; url=' . $url . '">';
        if (!empty($message)) {
            echo $message;
        }
    }

    public function renderTemplateArea($templateArea, $feedbackID)
    {
        // renders all the elements in a given template area on the listing pages
        global $conn, $config, $lang, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $feedbackID = intval($feedbackID);
        $templateArea = $misc->make_db_extra_safe($templateArea);
        $sql = 'SELECT feedbackdbelements_field_value, feedbackformelements_id, feedbackformelements_field_type,
				feedbackformelements_field_caption
				FROM ' . $config['table_prefix'] . 'feedbackdbelements, ' . $config['table_prefix'] . 'feedbackformelements
				WHERE ((' . $config['table_prefix'] . "feedbackdbelements.feedbackdb_id = $feedbackID)
				AND (feedbackformelements_field_name = feedbackdbelements_field_name)
				AND (feedbackformelements_location = $templateArea))
				ORDER BY feedbackformelements_rank ASC";
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        $display = '';
        while (!$recordSet->EOF) {
            $form_elements_id = $recordSet->fields('feedbackformelements_id');
            $field_type = $recordSet->fields('feedbackformelements_field_type');

            $field_value = $recordSet->fields('feedbackdbelements_field_value');
            if (!isset($_SESSION['users_lang'])) {
                // Hold empty string for translation fields, as we are workgin with teh default lang
                $field_caption = $recordSet->fields('feedbackformelements_field_caption');
            } else {
                $lang_sql = 'SELECT feedbackformelements_field_caption
				FROM ' . $config['lang_table_prefix'] . "feedbackformelements
				WHERE feedbackformelements_id = $form_elements_id";
                $lang_recordSet = $conn->Execute($lang_sql);
                if ($lang_recordSet === false) {
                    $misc->log_error($lang_sql);
                }
                $field_caption = $lang_recordSet->fields('feedbackformelements_field_caption');
            }

            $display_status = true;
            if ($display_status === true) {
                if ($field_type == 'divider') {
                    $display .= '<br /><span class="normal_caption">$field_caption</span>';
                } elseif ($field_value != '') {
                    if ($field_type == 'select-multiple' or $field_type == 'option' or $field_type == 'checkbox') {
                        // handle field types with multiple options
                        //$display .= "<br /><strong>$field_caption</strong><br />";
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
                                $feature_list_item = htmlentities($feature_list_item, ENT_NOQUOTES, $config['charset']);
                                $display .= $feature_list_item;
                                $display .= $config['feature_list_separator'];
                                $display .= '</li>';
                                $l++;
                            } else {
                                $display .= '<li>';
                                $feature_list_item = htmlentities($feature_list_item, ENT_NOQUOTES, $config['charset']);
                                $display .= $feature_list_item;
                                $display .= '</li>';
                            }
                        } // end while
                        $display .= '</ul>';
                        $display .= '</div>';
                    } // end if field type is a multiple type
                    elseif ($field_type == 'price') {
                        $money_amount = $misc->international_num_format($field_value, $config['number_decimals_price_fields']);
                        $display .= "<strong>$field_caption</strong>: " . $misc->money_formats($money_amount);
                    } // end elseif
                    elseif ($field_type == 'number') {
                        $display .= "<strong>$field_caption</strong>: " . $misc->international_num_format($field_value, $config['number_decimals_number_fields']);
                    } // end elseif
                    elseif ($field_type == 'url') {
                        $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                        $display .= "<strong>$field_caption</strong>: <a href=\"$field_value\" onclick=\"window.open(this.href,'_blank','location=1,resizable=1,status=1,scrollbars=1,toolbar=1,menubar=1,noopener,noreferrer');return false\">$field_value</a>";
                    } elseif ($field_type == 'email') {
                        $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                        $display .= "<strong>$field_caption</strong>: <a href=\"mailto:$field_value\">$field_value</a>";
                    } elseif ($field_type == 'text' or $field_type == 'textarea') {
                        $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                        if ($config['add_linefeeds'] === '1') {
                            $field_value = nl2br($field_value); //replace returns with <br />
                        } // end if
                        $display .= "<strong>$field_caption</strong>: $field_value";
                    } elseif ($field_type == 'date') {
                        if ($config['date_format'] == 1) {
                            $format = 'm/d/Y';
                        } elseif ($config['date_format'] == 2) {
                            $format = 'Y/d/m';
                        } elseif ($config['date_format'] == 3) {
                            $format = 'd/m/Y';
                        }
                        $field_value = date($format, "$field_value");
                        $display .= "<strong>$field_caption</strong>: $field_value";
                    } else {
                        $field_value = htmlentities($field_value, ENT_NOQUOTES, $config['charset']);
                        $display .= "<strong>$field_caption</strong>: $field_value";
                    } // end else
                    $display .= '<br />';
                } else {
                    if ($field_type == 'price' && $config['zero_price'] == '1') {
                        $display .= "<strong>$field_caption</strong>: " . $lang['call_for_price'] . '<br />';
                    } // end if
                } // end else
            }
            $recordSet->MoveNext();
        } // end while
        return $display;
    }

    public function edit_form_preview()
    {
        global $conn, $config, $misc;
        include_once $config['basepath'] . '/include/forms.inc.php';
        $forms = new forms();
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_admin();
        $page->load_page($config['admin_template_path'] . '/lead_forms_preview.html');

        //Load Template Area From Config
        $sections = explode(',', $config['template_lead_sections']);
        $template_holder = ['misc_hold' => ''];
        foreach ($sections as $section) {
            if (strpos($page->page, $section) !== false) {
                $template_holder[$section] = '';
            }
        }

        $sql = 'SELECT feedbackformelements_field_type, feedbackformelements_field_name, feedbackformelements_field_caption,
				feedbackformelements_default_text, feedbackformelements_field_elements, feedbackformelements_required, feedbackformelements_tool_tip,
				feedbackformelements_location
				FROM ' . $config['table_prefix'] . 'feedbackformelements
				ORDER BY feedbackformelements_rank, feedbackformelements_field_name';
        $recordSet = $conn->Execute($sql);

        if (!$recordSet) {
            $misc->log_error($sql);
        }
        while (!$recordSet->EOF) {
            $field_type = $recordSet->fields('feedbackformelements_field_type');
            $field_name = $recordSet->fields('feedbackformelements_field_name');
            $field_caption = $recordSet->fields('feedbackformelements_field_caption');
            $default_text = $recordSet->fields('feedbackformelements_default_text');
            $field_elements = $recordSet->fields('feedbackformelements_field_elements');
            $required = $recordSet->fields('feedbackformelements_required');
            $tool_tip = $recordSet->fields('feedbackformelements_tool_tip');
            $location = $recordSet->fields('feedbackformelements_location');

            $field_type = $field_type;
            $field_name = $field_name;
            $field_caption = $field_caption;
            $default_text = $default_text;
            $field_elements = $field_elements;
            $required = $required;
            $location = $location;

            //$output .= $forms->renderFormElement($field_type, $field_name, '', $field_caption, $default_text, $required,$field_elements);

            $field = $forms->renderFormElement($field_type, $field_name, '', $field_caption, $default_text, $required, $field_elements, $field_length = '', $tool_tip);

            if (array_key_exists($location, $template_holder) == true) {
                $template_holder[$location] .= $field;
            } else {
                $template_holder['misc_hold'] .= $field;
            }

            $recordSet->MoveNext();
        } // end while

        foreach ($template_holder as $tag => $value) {
            $page->page = str_replace('{' . $tag . '}', $value, $page->page);
        }

        $page->replace_lang_template_tags(true);
        $page->replace_permission_tags();
        $page->auto_replace_tags('', true);
        $output = $page->return_page();
        return $output;
    }
}
