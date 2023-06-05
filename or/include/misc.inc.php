<?php


use PHPMailer\PHPMailer\Exception;

/**
 * Class misc
 *
 * This is our misc class that contains common functions used by other classes
 */
class Misc
{
    /**
     * Generate a csfr token
     *
     * @return string
     */
    public function generate_csrf_token()
    {
        if (isset($_SESSION['csrf_token'])) {
            return $_SESSION['csrf_token'];
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    /**
     * Validate CSRF token, removes token from session for one time validations.
     *
     * @param $token CSRF Token to validate
     *
     * @return bool
     */
    public function validate_csrf_token($token): bool
    {
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            unset($_SESSION['csrf_token']);
            return true;
        }
        return false;
    }

    /**
     * Validate CSRF token for ajax calls. Doesn't clear token from session allowing multiple ajax calls to use same token.
     *
     * @param $token CSRF Token to validate
     *
     * @return bool
     */
    public function validate_csrf_token_ajax($token): bool
    {
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            return true;
        }
        return false;
    }

    /**
     * Tries to detect if a the user is connecting from a mobile device.
     *
     * @return boolean
     */
    public function detect_mobile_browser(): bool
    {
        global $config;
        include_once $config['basepath'] . '/include/hooks.inc.php';
        $hooks = new hooks();
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $hook_result = $hooks->load('detect_mobile_browser', $_SERVER['HTTP_USER_AGENT']);
            if (is_array($hook_result)) {
                if (isset($hook_result['is_mobile']) && is_bool($hook_result['is_mobile'])) {
                    return  $hook_result['is_mobile'];
                }
            }
        }
        //echo $_SERVER['HTTP_USER_AGENT'];
        $regex_match = '/(nokia|iphone|android(?!(.*xoom\sbuild))|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|';
        $regex_match .= 'htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|';
        $regex_match .= 'blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|';
        $regex_match .= 'symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|';
        $regex_match .= 'jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220|skyfire';
        $regex_match .= ')/i';
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        } elseif (isset($_SERVER['HTTP_PROFILE'])) {
            return true;
        } elseif (isset($_SERVER['HTTP_USER_AGENT'])) {
            return (bool)preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT']));
        } else {
            return false;
        }
    }

    /**
     * Money Formating
     *
     * Formats a number with the international money sign (dollar sign) in the correct place.
     *
     * @param string $number Takes a formated number eg. 1,000.00
     * @return string Returns the number string with the money sign in the correct place. Eg. $1,000.00
     */
    public function money_formats($number)
    {
        global $config;
        switch ($config['money_format']) {
            case '2':
                /* germany, spain -- 123.456,78 */
                $output = $number . $config['money_sign'];
                break;
            case '3':
                /*  honduras -- 123,456.78 */
                $output = $config['money_sign'] . ' ' . $number;
                break;
            default:
                /* usa, uk - $123,345 */
                $output = $config['money_sign'] . $number;
                break;
        }
        return $output;
    }

    /**
     * Internationalizes numbers
     *
     * Internationalizes numbers on the site according to the fomat defined in the
     * $config['number_format_style'])
     *
     * @param float $input
     * @param int $decimals
     * @return string
     */
    public function international_num_format($input, $decimals = 2)
    {
        //
        global $config;

        switch ($config['number_format_style']) {
            case '2': // spain, germany
                if ($config['force_decimals'] == '1') {
                    $output = number_format($input, $decimals, ',', '.');
                } else {
                    $output = $this->formatNumber($input, $decimals, ',', '.');
                }
                break;
            case '3': // estonia
                if ($config['force_decimals'] == '1') {
                    $output = number_format($input, $decimals, '.', ' ');
                } else {
                    $output = $this->formatNumber($input, $decimals, '.', ' ');
                }
                break;
            case '4': // france, norway
                if ($config['force_decimals'] == '1') {
                    $output = number_format($input, $decimals, ',', ' ');
                } else {
                    $output = $this->formatNumber($input, $decimals, ',', ' ');
                }
                break;
            case '5': // switzerland
                if ($config['force_decimals'] == '1') {
                    $output = number_format($input, $decimals, ',', "'");
                } else {
                    $output = $this->formatNumber($input, $decimals, ',', "'");
                }
                break;
            case '6': // kazahistan
                if ($config['force_decimals'] == '1') {
                    $output = number_format($input, $decimals, ',', '.');
                } else {
                    $output = $this->formatNumber($input, $decimals, ',', '.');
                }
                break;
            default:
                if ($config['force_decimals'] == '1') {
                    $output = number_format($input, $decimals, '.', ',');
                } else {
                    $output = $this->formatNumber($input, $decimals, '.', ',');
                }
                break;
        } // end switch
        return $output;
    }

    public function formatNumber($number, $decimals, $dec_point, $thousands_sep)
    {
        //Make sure $number is a double or a numeric value, and user did not provide a string
        if (!is_double($number) && !is_numeric($number)) {
            return '';
        }
        $nocomma = abs($number - floor($number));
        $strnocomma = number_format($nocomma, $decimals, '.', '');
        for ($i = 1; $i <= $decimals; $i++) {
            if (substr($strnocomma, ($i * -1), 1) != '0') {
                break;
            }
        }
        return number_format($number, ($decimals - $i + 1), $dec_point, $thousands_sep);
    }

    /**
     * Recursive directory deletion.
     *
     * https://stackoverflow.com/questions/3338123/how-do-i-recursively-delete-a-directory-and-its-entire-contents-files-sub-dir
     *
     * @return bool
     */
    public function recurseRmdir($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file") && !is_link("$dir/$file")) ? $this->recurseRmdir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    public function getmicrotime()
    {
        [$usec, $sec] = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }

    public function make_db_safe($input, $skipHtmlStrip = false)
    {
        global $config, $conn;
        if ($config['strip_html'] === '1' && $skipHtmlStrip == false) {
            $input = strip_tags($input, $config['allowed_html_tags']);
        }
        $output = $conn->qstr($input);
        return $output;
    } // end make_db_safe

    public function make_db_extra_safe($input)
    {
        global $conn;
        $output = strip_tags($input); // strips out all tags
        $output = $conn->qstr($output);
        $output = trim($output);
        return $output;
    }

    public function make_db_unsafe($input)
    {
        $output = stripslashes($input); // strips out slashes
        $output = str_replace("''", "'", $output); // strips out double quotes from m$ db's
        return $output;
    }

    public function log_error($sql, $handle = 'die')
    {
        // logs SQL errrors for later inspection
        global $config, $conn;
        $message = '';
        $message .= 'Fatal Error triggered by User at IP --> ' . $_SERVER['REMOTE_ADDR'] . ' ON ' . date('F j, Y, g:i:s a') . "\r\n\r\n";
        $message .= 'SQL Error Message: ' . $conn->ErrorMsg() . "\r\n";
        $message .= 'SQL statement that failed below: ' . "\r\n";
        $message .= '---------------------------------------------------------' . "\r\n";
        $message .= $sql . "\r\n";
        $message .= "\r\n" . '---------------------------------------------------------' . "\r\n";
        $message .= "\r\n" . 'ERROR REPORT ' . $_SERVER['SERVER_NAME'] . ': ' . date('F j, Y, g:i:s a') . "\r\n";
        $message .= "\r\n" . '---------------------------------------------------------' . "\r\n";
        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            $message .= 'Server Type: ' . $_SERVER['SERVER_SOFTWARE'] . "\r\n";
        }
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $message .= 'Request Method: ' . $_SERVER['REQUEST_METHOD'] . "\r\n";
        }
        if (isset($_SERVER['QUERY_STRING'])) {
            $message .= 'Query String: ' . $_SERVER['QUERY_STRING'] . "\r\n";
        }
        if (isset($_SERVER['HTTP_REFERER'])) {
            $message .= 'Refereer: ' . $_SERVER['HTTP_REFERER'] . "\r\n";
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $message .= 'User Agent: ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
        }
        if (isset($_SERVER['REQUEST_URI'])) {
            $message .= 'Request URI: ' . $_SERVER['REQUEST_URI'] . "\r\n";
        }

        $message .= 'POST Variables: ' . var_export($_POST, true) . "\r\n";
        $message .= 'GET Variables: ' . var_export($_GET, true) . "\r\n";

        if (isset($config['site_email']) && $config['site_email'] != '') {
            $sender_email = $config['site_email'];
        } else {
            $sender_email = $config['admin_email'];
        }
        $this->send_email($config['admin_name'], $sender_email, $config['admin_email'], $message, 'SQL Error http://' . $_SERVER['SERVER_NAME']);
        if ($handle == '500') {
            header('Location: 500.shtml');
            die;
        }
        if ($handle == 'die') {
            die(nl2br($message));
        }
    }

    public function next_prev($num_rows, $cur_page, $guidestring = '', $template = '', $admin = false)
    {
        global $lang, $config;

        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        if (isset($template) && $template != '') {
            $template_file = 'next_prev_' . $template . '.html';
        } else {
            $template_file = 'next_prev.html';
        }
        if ($admin == true) {
            $page->load_page($config['admin_template_path'] . '/' . $template_file);
        } else {
            $page->load_page($config['template_path'] . '/' . $template_file);
        }
        $guidestring = '';
        $guidestring_no_action = '';
        // Save GET
        foreach ($_GET as $k => $v) {
            if ($v !== '' && $k != 'cur_page' && $k != 'PHPSESSID' && ($k != 'printer_friendly' && $v != false)) {
                if (is_array($v)) {
                    foreach ($v as $vitem) {
                        $guidestring .= '&amp;' . urlencode($k) . '[]=' . urlencode($vitem);
                    }
                } else {
                    $guidestring .= '&amp;' . urlencode($k) . '=' . urlencode($v);
                }
            }
            if ($v !== '' && $k != 'cur_page' && $k != 'PHPSESSID' && $k != 'action' && ($k != 'printer_friendly' && $v != false)) {
                if (is_array($v)) {
                    foreach ($v as $vitem) {
                        $guidestring_no_action .= '&amp;' . urlencode($k) . '[]=' . urlencode($vitem);
                    }
                } else {
                    $guidestring_no_action .= '&amp;' . urlencode($k) . '=' . urlencode($v);
                }
            }
        }
        $page->page = str_replace('{nextprev_guidestring}', $guidestring, $page->page);
        $page->page = str_replace('{nextprev_guidestring_no_action}', $guidestring_no_action, $page->page);
        if ($cur_page == '') {
            $cur_page = 0;
        }
        $page_num = $cur_page + 1;

        $page->page = str_replace('{nextprev_num_rows}', $num_rows, $page->page);
        if ($_GET['action'] == 'view_log') {
            $items_per_page = 25;
            $page->page = str_replace('{nextprev_page_type}', $lang['log'], $page->page);
            $page->page = str_replace('{nextprev_meet_your_search}', $lang['logs_meet_your_search'], $page->page);

            if ($num_rows == 1) {
                $page->page = $page->remove_template_block('!nextprev_num_of_rows_is_1', $page->page);
                $page->page = $page->cleanup_template_block('nextprev_num_of_rows_is_1', $page->page);
            } else {
                $page->page = $page->remove_template_block('nextprev_num_of_rows_is_1', $page->page);
                $page->page = $page->cleanup_template_block('!nextprev_num_of_rows_is_1', $page->page);
            }
        } elseif ($_GET['action'] == 'edit_blog' || $_GET['action'] == 'blog_index') {
            $items_per_page = $config['blogs_per_page'];
            $page->page = str_replace('{nextprev_page_type}', $lang['blog'], $page->page);
            $page->page = str_replace('{nextprev_meet_your_search}', $lang['blogs'], $page->page);
            if ($num_rows == 1) {
                $page->page = $page->remove_template_block('!nextprev_num_of_rows_is_1', $page->page);
                $page->page = $page->cleanup_template_block('nextprev_num_of_rows_is_1', $page->page);
            } else {
                $page->page = $page->remove_template_block('nextprev_num_of_rows_is_1', $page->page);
                $page->page = $page->cleanup_template_block('!nextprev_num_of_rows_is_1', $page->page);
            }
        } elseif ($_GET['action'] == 'view_users') {
            $items_per_page = $config['users_per_page'];
            $page->page = str_replace('{nextprev_page_type}', $lang['agent'], $page->page);
            $page->page = str_replace('{nextprev_meet_your_search}', $lang['agents'], $page->page);
            if ($num_rows == 1) {
                $page->page = $page->remove_template_block('!nextprev_num_of_rows_is_1', $page->page);
                $page->page = $page->cleanup_template_block('nextprev_num_of_rows_is_1', $page->page);
            } else {
                $page->page = $page->remove_template_block('nextprev_num_of_rows_is_1', $page->page);
                $page->page = $page->cleanup_template_block('!nextprev_num_of_rows_is_1', $page->page);
            }
        } else {
            if ($_GET['action'] == 'edit_listings' || $_GET['action'] == 'edit_my_listings') {
                $items_per_page = $config['admin_listing_per_page'];
            } else {
                $items_per_page = $config['listings_per_page'];
            }
            if ($_GET['action'] == 'user_manager') {
                $page->page = str_replace('{nextprev_page_type}', $lang['user_manager_users'], $page->page);
                $page->page = str_replace('{nextprev_meet_your_search}', $lang['user_manager_users'], $page->page);
            } else {
                $page->page = str_replace('{nextprev_page_type}', $lang['listing'], $page->page);
                $page->page = str_replace('{nextprev_meet_your_search}', $lang['listings_meet_your_search'], $page->page);
            }

            if ($num_rows == 1) {
                $page->page = $page->remove_template_block('!nextprev_num_of_rows_is_1', $page->page);
                $page->page = $page->cleanup_template_block('nextprev_num_of_rows_is_1', $page->page);
            } else {
                $page->page = $page->remove_template_block('nextprev_num_of_rows_is_1', $page->page);
                $page->page = $page->cleanup_template_block('!nextprev_num_of_rows_is_1', $page->page);
            }
        }
        $total_num_page = ceil($num_rows / $items_per_page);
        if ($total_num_page == 0) {
            $listing_num_min = 0;
            $listing_num_max = 0;
        } else {
            $listing_num_min = (($cur_page * $items_per_page) + 1);
            if ($page_num == $total_num_page) {
                $listing_num_max = $num_rows;
            } else {
                $listing_num_max = $page_num * $items_per_page;
            }
        }

        $page->page = str_replace('{nextprev_listing_num_min}', $listing_num_min, $page->page);
        $page->page = str_replace('{nextprev_listing_num_max}', $listing_num_max, $page->page);
        $prevpage = $cur_page - 1;
        $nextpage = $cur_page + 1;
        $next10page = $cur_page + 10;
        $prev10page = $cur_page - 10;
        $page->page = str_replace('{nextprev_nextpage}', $nextpage, $page->page);
        $page->page = str_replace('{nextprev_prevpage}', $prevpage, $page->page);
        $page->page = str_replace('{nextprev_next10page}', $next10page, $page->page);
        $page->page = str_replace('{nextprev_prev10page}', $prev10page, $page->page);

        if ($_GET['action'] == 'searchresults') {
            $page->page = $page->cleanup_template_block('nextprev_show_save_search', $page->page);
        } else {
            $page->page = $page->remove_template_block('nextprev_show_save_search', $page->page);
        }
        if ($_GET['action'] == 'searchresults') {
            $page->page = $page->cleanup_template_block('nextprev_show_refine_search', $page->page);
        } else {
            $page->page = $page->remove_template_block('nextprev_show_refine_search', $page->page);
        }
        if ($page_num <= 1) {
            $page->page = $page->cleanup_template_block('nextprev_is_firstpage', $page->page);
            $page->page = $page->remove_template_block('!nextprev_is_firstpage', $page->page);
        }

        if ($page_num > 1) {
            $page->page = $page->cleanup_template_block('!nextprev_is_firstpage', $page->page);
            $page->page = $page->remove_template_block('nextprev_is_firstpage', $page->page);
        }
        // begin 10 page menu selection
        $count = $cur_page;

        //Determine Where to Start the Page Count At
        $count_start = $count - 10;
        if ($count_start < 0) {
            $count_start = 0;
        } else {
            while (!preg_match('/0$/', $count_start)) {
                $count_start++;
            }
        }
        $page_section_part = $page->get_template_section('nextprev_page_section');
        $page_section = '';

        $reverse_count = $count_start;
        while ($count > $count_start) {
            // If the last number is a zero, it's divisible by 10 check it...
            if (preg_match('/0$/', $count)) {
                break;
            }
            $page_section .= $page_section_part;
            $disp_count = ($reverse_count + 1);

            $page_section = str_replace('{nextprev_count}', $reverse_count, $page_section);
            $page_section = str_replace('{nextprev_disp_count}', $disp_count, $page_section);
            $page_section = $page->cleanup_template_block('nextprev_page_other', $page_section);
            $page_section = $page->remove_template_block('nextprev_page_current', $page_section);
            $count--;
            $reverse_count++;
        }
        $count = $cur_page;
        while ($count < $total_num_page) {
            $page_section .= $page_section_part;
            $disp_count = ($count + 1);
            $page_section = str_replace('{nextprev_count}', $count, $page_section);
            $page_section = str_replace('{nextprev_disp_count}', $disp_count, $page_section);
            if ($page_num == $disp_count) {
                // the currently selected page
                $page_section = $page->cleanup_template_block('nextprev_page_current', $page_section);
                $page_section = $page->remove_template_block('nextprev_page_other', $page_section);
            } else {
                $page_section = $page->cleanup_template_block('nextprev_page_other', $page_section);
                $page_section = $page->remove_template_block('nextprev_page_current', $page_section);
            }
            $count++;
            // If the last number is a zero, it's divisible by 10 check it...
            if (substr($count, -1, 1) == '0') {
                break;
            }
        }
        $page->replace_template_section('nextprev_page_section', $page_section);
        if ($page_num >= $total_num_page) {
            $page->page = $page->cleanup_template_block('nextprev_lastpage', $page->page);
            $page->page = $page->remove_template_block('!nextprev_lastpage', $page->page);
        }
        if ($page_num < $total_num_page) {
            $page->page = $page->cleanup_template_block('!nextprev_lastpage', $page->page);
            $page->page = $page->remove_template_block('nextprev_lastpage', $page->page);
        }
        // search buttons
        if ($page_num >= 11) { // previous 10 page
            $page->page = $page->cleanup_template_block('nextprev_prev_100_button', $page->page);
            $page->page = $page->remove_template_block('!nextprev_prev_100_button', $page->page);
        } else {
            $page->page = $page->cleanup_template_block('!nextprev_prev_100_button', $page->page);
            $page->page = $page->remove_template_block('nextprev_prev_100_button', $page->page);
        }
        // Next 100 button
        if (($cur_page < ($total_num_page - 10)) && ($total_num_page > 10)) {
            $page->page = $page->cleanup_template_block('nextprev_next_100_button', $page->page);
            $page->page = $page->remove_template_block('!nextprev_next_100_button', $page->page);
        } else {
            $page->page = $page->cleanup_template_block('!nextprev_next_100_button', $page->page);
            $page->page = $page->remove_template_block('nextprev_next_100_button', $page->page);
        }
        if ($_GET['action'] == 'view_log' && $_SESSION['admin_privs'] == 'yes') {
            $page->page = $page->cleanup_template_block('nextprev_clearlog', $page->page);
        } else {
            $page->page = $page->remove_template_block('nextprev_clearlog', $page->page);
        }
        return $page->page;
    }

    public function referer_check()
    {
        global $config;
        // Make sure data is comming from the site. (Easily faked, but will stop some of the spammers)
        $referers = $config['baseurl'];
        $referers = str_replace('http://', '', $referers);
        $referers = str_replace('https://', '', $referers);
        $referers = str_replace('www.', '', $referers);
        $referers = explode('/', $referers);
        //print_r($referers);
        $found = false;
        if (isset($_SERVER['HTTP_REFERER'])) {
            $temp = explode('/', $_SERVER['HTTP_REFERER']);
            $referer = $temp[2];
            //echo $_SERVER['HTTP_REFERER'];
            if (preg_match('/' . $referers[0] . '/i', $referer)) {
                $found = true;
            }
        } elseif (isset($_SERVER['HTTP_ORIGIN'])) {
            $temp = explode('/', $_SERVER['HTTP_ORIGIN']);
            $referer = $temp[2];
            if (preg_match('/' . $referers[0] . '/i', $referer)) {
                $found = true;
            }
        }
        //echo $referers[0];
        //echo $referer;
        return $found;
    }

    public function send_email($sender, $sender_email, $recipient, $message, $subject, $isHTML = false, $skipRefCheck = false, $replyto = null, $replyto_email = null)
    {
        global $config, $lang;
        if (!defined('OR_DEBUG_MAIL')) {
            define('OR_DEBUG_MAIL', false);
        }
        // Make sure data is comming from the site. (Easily faked, but will stop some of the spammers)
        $referers = $config['baseurl'];
        $referers = str_replace('http://', '', $referers);
        $referers = str_replace('https://', '', $referers);
        $referers = str_replace('www.', '', $referers);
        $referers = explode('/', $referers);
        $found = false;
        if ($skipRefCheck == true) {
            $found = true;
        } elseif (isset($_SERVER['HTTP_REFERER'])) {
            $temp = explode('/', $_SERVER['HTTP_REFERER']);
            $referer = $temp[2];
            if (preg_match('/' . $referers[0] . '/i', $referer)) {
                $found = true;
            }
        }
        if (!$found) {
            $temp = '1' . $lang['email_not_authorized'];
            return $temp;
        } else {
            // First, make sure the form was posted from a browser.
            // For basic web-forms, we don't care about anything
            // other than requests from a browser:
            if (php_sapi_name() != 'cli' && !isset($_SERVER['HTTP_USER_AGENT'])) {
                $temp = '2' . $lang['email_not_authorized'];
                return $temp;
            }
            // Attempt to defend against header injections:
            $badStrings = [
                'Content-Type:',
                'MIME-Version:',
                'Content-Transfer-Encoding:',
                'bcc:',
                'cc:',
            ];
            foreach ($badStrings as $v2) {
                if (strpos($sender, $v2) !== false) {
                    $temp = $lang['email_not_authorized'];
                    return $temp;
                }
                if (strpos($sender_email, $v2) !== false) {
                    $temp = $lang['email_not_authorized'];
                    return $temp;
                }
                if (strpos($recipient, $v2) !== false) {
                    $temp = $lang['email_not_authorized'];
                    return $temp;
                }
                if (strpos($message, $v2) !== false) {
                    $temp = $lang['email_not_authorized'];
                    return $temp;
                }
                if (strpos($subject, $v2) !== false) {
                    $temp = $lang['email_not_authorized'];
                    return $temp;
                }
                if (strpos($replyto, $v2) !== false) {
                    $temp = $lang['email_not_authorized'];
                    return $temp;
                }
                if (strpos($replyto_email, $v2) !== false) {
                    $temp = $lang['email_not_authorized'];
                    return $temp;
                }
            }
            // validate Sender_email as a Spam check
            $valid = $this->validate_email($sender_email);
            if ($valid) {
                //Are we sending via mail() or via phpmailer?
                if ($config['phpmailer'] == 1) {
                    $mail = new PHPMailer\PHPMailer\PHPMailer;

                    $mail->IsSMTP(); // telling the class to use SMTP
                    /* Enable  SMTP debug information for testing
                    $mail->SMTPDebug  = 2;
                    */

                    try {
                        $mail->Host       = $config['mailserver']; // SMTP server
                        if ($config['mailport'] == 465) {
                            $mail->SMTPSecure = 'ssl';                 // sets the prefix to the servier
                        }
                        if ($config['mailport'] == 587) {
                            $mail->SMTPSecure = 'tls';                 // sets the prefix to the servier
                        }
                        $mail->Port       = $config['mailport'];                   // set the SMTP port for the server

                        if (trim($config['mailuser']) !== '') {
                            $mail->SMTPAuth   = true;                  // enable SMTP authentication
                            $mail->Username   = $config['mailuser'];  // GMAIL username
                            $mail->Password   = $config['mailpass'];            // GMAIL password
                        }
                        if ($replyto_email ==  null) {
                            $mail->AddReplyTo($sender_email, $sender);
                        } else {
                            $mail->AddReplyTo($replyto_email, $replyto);
                        }
                        $mail->AddAddress($recipient);
                        $mail->SetFrom($sender_email, $sender);
                        $mail->Subject = $subject;
                        if ($isHTML) {
                            $htmlmessage = $mail->MsgHTML($message);
                            $mail->IsHTML(false);
                            $mail->Body = $htmlmessage;
                            $mail->AltBody = strip_tags($message);
                        } else {
                            $mail->IsHTML(false);
                            $mail->Body = $message;
                        }

                        if ($mail->Send()) {
                            return true;
                        }
                    } catch (Exception $e) {
                        return $e->errorMessage(); //Pretty error messages from PHPMailer
                    } catch (\Exception $e) {
                        return $e->getMessage(); //Boring error messages from anything else!
                    }
                } else {
                    $message = stripslashes($message);
                    $subject = stripslashes($subject);
                    if ($isHTML) {
                        $header = 'Content-Type: text/html; charset=' . $config['charset'] . "\n";
                    } else {
                        $header = 'Content-Type: text/plain; charset=' . $config['charset'] . "\n";
                    }

                    $header .= "Content-Transfer-Encoding: 8bit\n";
                    $header .= 'From: "' . $sender . '" <' . $sender_email . ">\n";
                    if ($replyto_email !=  null) {
                        $header .= 'Reply-To: "' . $replyto . '" <' . $replyto_email . ">\n";
                    }
                    $header .= 'Return-Path: ' . $config['admin_email'] . "\n";
                    $header .= 'X-Sender: <' . $config['admin_email'] . ">\n";
                    $header .= 'X-Mailer: Open-Realty ' . $config['version'] . ' - Installed at ' . $config['baseurl'] . "\n";
                    if (OR_DEBUG_MAIL) {
                        $this->log_action("EMAIL DEBUG - Recipient: $recipient, Subject: $subject, Message: $message, Header: $header");
                        echo 'Recipient: ' . $recipient;
                        echo 'Subject: ' . $subject;
                        echo 'Message: ' . $message;
                        echo 'Header: ' . $header;
                    }
                    $temp = mail($recipient, $subject, $message, $header);
                    if (OR_DEBUG_MAIL) {
                        $this->log_action("EMAIL DEBUG - Temp: $temp");
                        echo 'Temp: ' . $temp;
                    }
                }
            } else {
                $temp = false;
            }
            return $temp;
        }
    }

    public function is_banned_site_ip()
    {
        global $config;
        //Get Users IP
        if (isset($_SERVER['HTTP_X_FORWARD_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARD_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $banned_ips = explode("\n", $config['banned_ips_site']);
        $is_banned_ip = false;
        foreach ($banned_ips as $bi) {
            if (trim($bi) !== '') {
                if (stripos($ip, $bi) === 0) {
                    $is_banned_ip = true;
                }
            }
        }
        if ($is_banned_ip) {
            //Set a cookie to track the banned user
            setcookie('cookban', $ip, time() + 60 * 60 * 24 * 365, '/');
        } else {
            //Check to see if user has a ban cookie
            if (isset($_COOKIE['cookban']) && $_COOKIE['cookban'] != '') {
                //User was banned on a different IP, alert admin
                $old_ip = $_COOKIE['cookban'];
                //See if old_ip is still banned
                $ip_still_banned = false;
                foreach ($banned_ips as $bi) {
                    if (trim($bi) !== '') {
                        if (stripos($old_ip, $bi) === 0) {
                            $ip_still_banned = true;
                        }
                    }
                }
                if ($ip_still_banned) {
                    echo 'IP is still banned';
                    if (!isset($_SESSION['cookalert_sent'])) {
                        $this->send_email($config['admin_email'], $config['admin_email'], $config['admin_email'], 'User origionally banned at IP ' . $old_ip . ' is back using ' . $ip, 'Banned User Has Returned', false, true);
                        $_SESSION['cookalert_sent'] = $ip;
                    } elseif ($_SESSION['cookalert_sent'] != $ip) {
                        $this->send_email($config['admin_email'], $config['admin_email'], $config['admin_email'], 'User origionally banned at IP ' . $old_ip . ' is back using ' . $ip, 'Banned User Has Returned', false, true);
                        $_SESSION['cookalert_sent'] = $ip;
                    }
                } else {
                    echo 'IP is not still banned';
                    //We unbanned their IP, stop tracking.
                    setcookie('cookban', '', time() - (3600 * 25), '/');
                }
            }
        }
        return $is_banned_ip;
    }

    /**
    * Wrapper around setcookie function for better testability
    */

    public function setcookie($name, $value = "", $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false)
    {
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
  
  
    public function log_action($log_action)
    {
        // logs user actions
        global $api;
        $api->load_local_api('log__log_create_entry', ['log_type' => 'MISC', 'log_api_command' => 'MISC', 'log_message' => $log_action]);
    } // end function log_action

    public function os_type()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $OS = 'Windows';
        } else {
            $OS = 'Linux';
        }
        return $OS;
    }

    public function validate_email($email)
    {
        // Presume that the email is invalid
        $valid = false;
        // Validate the syntax
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            [$username, $domaintld] = explode('@', $email);
            $OS = $this->os_type();
            if ($OS == 'Linux') {
                if (checkdnsrr($domaintld, 'MX')) {
                    return true;
                }
            } else {
                return true;
            }
        }
        return $valid;
    }

    public function parseDate($date, $format)
    {
        //Supported formats
        //%Y - year as a decimal number including the century
        //%m - month as a decimal number (range 01 to 12)
        //%d - day of the month as a decimal number (range 01 to 31)
        //%H - hour as a decimal number using a 24-hour clock (range 00 to 23)
        //%M - minute as a decimal number
        // Builds up date pattern from the given $format, keeping delimiters in place.
        if (!preg_match_all('/%([YmdHMp])([^%])*/', $format, $formatTokens, PREG_SET_ORDER)) {
            return false;
        }
        $datePattern = '';
        foreach ($formatTokens as $formatToken) {
            $delimiter = preg_quote($formatToken[2], '/');
            $datePattern .= '(.*)' . $delimiter;
        }
        // Splits up the given $date
        if (!preg_match('/' . $datePattern . '/', $date, $dateTokens)) {
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
            return false;
        }
        if ($dateSegments['H'] && $dateSegments['M']) {
            $dateReformated .= ' ' . $dateSegments['H'] . ':' . $dateSegments['M'];
        }

        return strtotime($dateReformated);
    }

    public function clean_filename($filename)
    {
        //function to clean a filename string so it is a valid filename
        //replaces all characters that are not alphanumeric with the exception of underscore and the . for filename usage
        $realname = preg_replace('/[^a-zA-Z0-9\_\.]/', '', $filename);
        return $realname;
    }

    public function convert_timestamp($timestamp, $time = false)
    {
        global $config;
        switch ($config['date_format']) {
            case 1:
                $format = 'm/d/Y';
                break;
            case 2:
                $format = 'Y/d/m';
                break;
            case 3:
                $format = 'd/m/Y';
                break;
        }
        if ($time === true) {
            $format .= ' h:i A';
        }
        $timestamp = intval($timestamp);
        $date = date($format, $timestamp);
        return $date;
    }

    public function get_url($url, $cache = 0)
    {
        global $config, $misc;
        $cache_file = $config["basepath"] . "/files/download_cache/" . sha1($url) . '.cache';

        if ($cache > 0 && file_exists($cache_file) && (time() - $cache < filemtime($cache_file))) {
            $data = file_get_contents($cache_file);
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            $data = curl_exec($ch);
            $header  = curl_getinfo($ch);
            curl_close($ch);
            if ($header['http_code'] != '200') {
                $misc->log_action('Get URL Failed - ' . $url . ' Status Code: ' . $header['http_code']);
                $misc->log_action(print_r($header, true));
                return false;
            }
            if ($cache > 0) {
                file_put_contents($cache_file, $data);
            }
        }

        return $data;
    }

    //random password generator accepts numerical $length to determine password length.
    public function generatePassword($length = 10)
    {
        $chars = 'abcdefghjklmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * Get Admin status
     *
     * @param  integer $userdb_id
     * @return boolean
     */
    public function get_admin_status($userdb_id)
    {
        global $api;

        $result = $api->load_local_api('user__read', [
            'user_id' => $userdb_id,
            'resource' => 'agent',
            'fields' => ['userdb_is_admin'],
        ]);
        if ($result['error'] === true) {
            return false;
        }
        if ($result['user']['userdb_is_admin'] === 'yes') {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Get Agent status
     *
     * @param  integer $userdb_id
     * @return boolean
     */
    public function get_agent_status($userdb_id)
    {
        global $api;
        $result = $api->load_local_api('user__read', [
            'user_id' => $userdb_id,
            'resource' => 'agent',
            'fields' => ['userdb_is_agent'],
        ]);
        if ($result['error'] === true) {
            return false;
        }
        if ($result['user']['userdb_is_agent'] == 'yes') {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Get active status
     *
     * @param  integer $userdb_id
     * @return boolean
     */
    public function get_active_status($userdb_id)
    {
        global $api;

        $is_agent = $this->get_agent_status($userdb_id);

        if ($is_agent) {
            $res = 'agent';
        } else {
            $res = 'member';
        }

        $result = $api->load_local_api('user__read', [
            'user_id' => $userdb_id,
            'resource' => $res,
            'fields' => ['userdb_active'],
        ]);

        if ($result['user']['userdb_active'] == 'yes') {
            return true;
        } else {
            return false;
        }
    }
}
