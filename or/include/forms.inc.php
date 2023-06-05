<?php


class forms
{
    /**
     * ****************************************************************************\
     * renderFormElement($field_type, $field_name, $field_value,              *
     *                  $field_caption, $default_text, $required, $field_elements)*
     * \****************************************************************************
     */
    public function renderFormElement($field_type, $field_name, $field_value, $field_caption, $default_text, $required, $field_elements, $field_length = '', $tool_tip = '')
    {
        // handles the rendering of already filled in user forms
        global $lang, $config, $jscript;
        $field_value_raw = $field_value;
        $field_value_array = [];
        if (is_array($field_value)) {
            foreach ($field_value as $x => $v) {
                $field_value_array[$v] = htmlentities($v, ENT_COMPAT, $config['charset']);
            }
            $field_value = $field_value_array;
        } else {
            $field_value = htmlentities($field_value, ENT_COMPAT, $config['charset']);
        }
        if (!is_array($field_elements)) {
            $field_elements = explode('||', $field_elements);
        }
        sort($field_elements);
        $display = '';
        $markup = '';
        $markup2 = '';
        $validate = [];
        $required_html = '';
        if ($required == 'Yes') {
            $required_html = 'required';
        }
        $max_lenth_html = '';
        if ($field_length != '' && $field_length != 0) {
            $$max_lenth_html .= 'maxlength="' . $field_length . '" ';
        }
        switch ($field_type) {
            case 'lat':
            case 'long':
            case 'text': // handles text input boxes
                $display .= '<div class="input-group input-group-static mb-2">
                <label for="' . $field_name . '"
                  >' . $field_caption . '</label
                >
                <input
                  type="text"
                  name="' . $field_name . '"
                  id="' . $field_name . '"
                  value="' . $field_value . '"
                  class="form-control"
                  ' . $required_html . '
                  ' . $max_lenth_html . '
                />
              </div>';
                if ($tool_tip != '') {
                    $display .= '		<a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="$tool_tip"><i class="material-icons">help_outline</i></a>';
                }
                break;
            case 'date':
                // HTML5 Date Fields always pass data in yyyy-mm-dd format
                if ($field_value != '') {
                    $field_value = date('Y-m-d', intval($field_value));
                }
                $display .= '<div class="input-group input-group-static mb-2">
                <label for="' . $field_name . '"
                  >' . $field_caption . '</label
                >
                <input
                  type="date"
                  name="' . $field_name . '"
                  id="' . $field_name . '"
                  value="' . $field_value . '"
                  class="form-control"
                  ' . $required_html . '
                  ' . $max_lenth_html . '
                />
              </div>';
                if ($tool_tip != '') {
                    $display .= '		<a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="$tool_tip"><i class="material-icons">help_outline</i></a>';
                }
                break;

            case 'textarea': // handles textarea input
                $display .= '<div class="input-group input-group-static mb-2">
              <label for="' . $field_name . '"
                >' . $field_caption . '</label
              >
              <textarea
                name="' . $field_name . '"
                id="' . $field_name . '"
                cols="80"
                class="form-control"
                ' . $required_html . '
              >' . $field_value . '</textarea>
            </div>';
                if ($tool_tip != '') {
                    $display .= '		<a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="$tool_tip"><i class="material-icons">help_outline</i></a>';
                }
                break;

            case 'select':
                // handles single item select boxes
                $display .= '<div class="input-group input-group-static">
              <label for="' . $field_name . '" class="ms-0"
                >' . $field_caption . '</label
              >
              <select
                name="' . $field_name . '"
                class="form-control"
                id="' . $field_name . '"
              >';

                foreach ($field_elements as $list_item) {
                    $html_list_item = htmlentities($list_item, ENT_COMPAT, $config['charset']);
                    $display .= '			<option value="' . $html_list_item . '"';
                    if ($list_item == $field_value_raw || $list_item == "{lang_$field_value_raw}") {
                        $display .= ' selected ';
                    }
                    $display .= '>' . $list_item . '
                    </option>';
                }
                $display .= '
                </select>
              </div>';
                if ($tool_tip != '') {
                    $display .= '		<a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="$tool_tip"><i class="material-icons">help_outline</i></a>';
                }
                break;
            case 'select-multiple': // handles multiple item select boxes
                // handles single item select boxes
                $display .= '<div class="input-group input-group-static">
              <label for="' . $field_name . '" class="ms-0"
                >' . $field_caption . '</label
              >
              <select
                name="' . $field_name . '"
                class="form-control"
                id="' . $field_name . '"
                multiple
              >';
                foreach ($field_elements as $list_item) {
                    $html_list_item = htmlentities($list_item, ENT_COMPAT, $config['charset']);
                    $display .= '			<option value="' . $html_list_item . '"';
                    if ($list_item == $field_value_raw || $list_item == "{lang_$field_value_raw}") {
                        $display .= ' selected ';
                    }
                    $display .= '>' . $list_item . '
                    </option>';
                }
                $display .= '
                </select>
              </div>';
                if ($tool_tip != '') {
                    $display .= '		<a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="$tool_tip"><i class="material-icons">help_outline</i></a>';
                }
                break;

            case 'divider': // handles dividers in forms
                $display .= '<div class="input-group input-group-static mb-2">
                  <label for="' . $field_name . '"
                    >' . $field_caption . '</label
                  >
                  <div class="field_element"></div>
                </div>';
                break;

            case 'price': // handles price input
                $display .= '<div class="input-group input-group-static mb-2">
              <label for="' . $field_name . '"
                >' . $field_caption . '</label
              >
              <span class="input-group-text">' . $config['money_sign'] . '</span>
              <input
                type="number"
                name="' . $field_name . '"
                id="' . $field_name . '"
                value="' . $field_value . '"
                class="form-control"
                ' . $required_html . '
                ' . $max_lenth_html . '
              />
            </div>';
                if ($tool_tip != '') {
                    $display .= '		<a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="$tool_tip"><i class="material-icons">help_outline</i></a>';
                }
                break;

            case 'url': // handles url input fields
                $display .= '<div class="input-group input-group-static mb-2">
              <label for="' . $field_name . '"
                >' . $field_caption . '</label
              >
              <input
                type="url"
                name="' . $field_name . '"
                id="' . $field_name . '"
                value="' . $field_value . '"
                class="form-control"
                ' . $required_html . '
                ' . $max_lenth_html . '
              />
            </div>';
                if ($tool_tip != '') {
                    $display .= '		<a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="$tool_tip"><i class="material-icons">help_outline</i></a>';
                }
                break;

            case 'email': // handles email input
                $display .= '<div class="input-group input-group-static mb-2">
                <label for="' . $field_name . '"
                  >' . $field_caption . '</label
                >
                <input
                  type="email"
                  name="' . $field_name . '"
                  id="' . $field_name . '"
                  value="' . $field_value . '"
                  class="form-control"
                  ' . $required_html . '
                  ' . $max_lenth_html . '
                />
              </div>';
                if ($tool_tip != '') {
                    $display .= '		<a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="$tool_tip"><i class="material-icons">help_outline</i></a>';
                }
                break;

            case 'checkbox': // handles checkboxes
                if ($required != 'Yes') {
                    $display .= '		<input type="hidden" value="" name="' . $field_name . '[]" />';
                }
                if (!is_array($field_value_raw)) {
                    $field_value_raw = explode('||', $field_value_raw);
                }
                sort($field_value_raw);
                $count = 1;
                $display .= '<div class="input-group input-group-static mb-2">
              <label for="'.$field_name . '[]">' . $field_caption . '</label>
            </div>';
                foreach ($field_elements as $feature_list_Value => $feature_list_item) {
                    $html_feature_list_item = htmlentities($feature_list_item, ENT_COMPAT, $config['charset']);
                    $checked_html = '';
                    foreach ($field_value_raw as $field_value_list_item) {
                        if ($field_value_list_item == $feature_list_item || $field_value_list_item == "{lang_$feature_list_item}") {
                            $checked_html = ' checked ';
                        } // end if
                    } // end while
                    $display .= '<div class="form-check">
                <input class="form-check-input" type="checkbox" value="' . $html_feature_list_item . '" name="' . $field_name . '[]" id="' . $field_name . $count . '" ' . $checked_html . ' ' . $required_html . '>
                <label class="custom-control-label" for="' . $field_name . $count . '">' . $html_feature_list_item . '</label>
                </div>';
                    $count++;
                } // end while
                if ($tool_tip != '') {
                    $display .= '		<a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="$tool_tip"><i class="material-icons">help_outline</i></a>';
                }
                break;

            case 'option': // handles options

                if ($required != 'Yes') {
                    $display .= '		<input type="hidden" value="" name="' . $field_name . '[]" />';
                }
                if (!is_array($field_value_raw)) {
                    $field_value_raw = explode('||', $field_value_raw);
                }
                sort($field_value_raw);
                $count = 1;
                $display .= '<div class="input-group input-group-static mb-2">
              <label >' . $field_caption . '</label>
            </div>';
                foreach ($field_elements as $feature_list_Value => $feature_list_item) {
                    $html_feature_list_item = htmlentities($feature_list_item, ENT_COMPAT, $config['charset']);
                    $checked_html = '';
                    foreach ($field_value_raw as $field_value_list_item) {
                        if ($field_value_list_item == $feature_list_item || $field_value_list_item == "{lang_$feature_list_item}") {
                            $checked_html = ' checked ';
                        } // end if
                    } // end while
                    $display .= '<div class="form-check">
                <input class="form-check-input" type="radio" value="' . $html_feature_list_item . '" name="' . $field_name . '" id="' . $field_name . $count . '" ' . $checked_html . ' ' . $required_html . '>
                <label class="custom-control-label" for="' . $field_name . $count . '">' . $html_feature_list_item . '</label>
                </div>';
                    $count++;
                } // end while
                if ($tool_tip != '') {
                    $display .= '		<a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="$tool_tip"><i class="material-icons">help_outline</i></a>';
                }
                break;
            case 'number':
            case 'decimal':
                $display .= '<div class="input-group input-group-static mb-2">
                  <label for="' . $field_name . '"
                    >' . $field_caption . '</label
                  >
                  <input
                    type="number"
                    name="' . $field_name . '"
                    id="' . $field_name . '"
                    value="' . $field_value . '"
                    class="form-control"
                    ' . $required_html . '
                    ' . $max_lenth_html . '
                  />
                </div>';
                if ($tool_tip != '') {
                    $display .= '		<a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="$tool_tip"><i class="material-icons">help_outline</i></a>';
                }
                break;

            case 'submit': // handles submit buttons
                $display .= '<div class="input-group input-group-static mb-2">
								<input class="btn btn-primary" type="submit" value="' . $field_value . '" />
							</div>' . BR;
                break;

            default: // the catch all... mostly for errors and whatnot
                $display .= '<div>no handler yet</div>' . BR;
        } // end switch statement
        return $display;
    } // end renderExistingUserFormElement function

    public function validateForm($db_to_validate, $pclass = [])
    {
        // Validates the info being put into the system
        global $conn, $lang, $config, $misc;

        $pass_the_form = 'Yes';
        // this stuff is input that's already been dealt with
        // check to if the form should be passed
        $sql = 'SELECT ' . $db_to_validate . '_required, ' . $db_to_validate . '_field_type, ' . $db_to_validate . '_field_name 
				FROM ' . $config['table_prefix'] . $db_to_validate;
        if (count($pclass) > 0 && $db_to_validate == 'listingsformelements') {
            $sql .= ' WHERE listingsformelements_id IN (
							SELECT listingsformelements_id 
							FROM ' . $config['table_prefix_no_lang'] . 'classformelements 
							WHERE class_id IN (' . implode(',', $pclass) . ')
						)';
        }
        $recordSet = $conn->Execute($sql);
        if (!$recordSet) {
            $misc->log_error($sql);
        }
        while (!$recordSet->EOF) {
            $required = $recordSet->fields($db_to_validate . '_required');
            $field_type = $recordSet->fields($db_to_validate . '_field_type');
            $field_name = $recordSet->fields($db_to_validate . '_field_name');
            if ($required == 'Yes') {
                if (!isset($_POST[$field_name]) || (is_array($_POST[$field_name]) && count($_POST[$field_name]) == 0) || (!is_array($_POST[$field_name]) && trim($_POST[$field_name]) == '')) {
                    $pass_the_form = 'No';
                    $error[$field_name] = 'REQUIRED';
                }
            } // end if
            if ($field_type == 'number' && isset($_POST[$field_name]) && !is_numeric($_POST[$field_name]) && $_POST[$field_name] != '') {
                $pass_the_form = 'No';
                $error[$field_name] = 'TYPE';
            }
            $recordSet->MoveNext();
        }
        if ($pass_the_form == 'Yes') {
            return $pass_the_form;
        } else {
            return $error;
        }
    } // end function validateForm
}
