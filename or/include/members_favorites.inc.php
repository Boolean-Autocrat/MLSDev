<?php


global $config;
class membersfavorites
{
    public function delete_favorites()
    {
        global $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $display = '';
        $security = $login->loginCheck('Member');
        if ($security === true) {
            global $lang, $conn, $misc;
            if (!isset($_GET['listingID'])) {
                $display .= '<a href="' . $config['baseurl'] . '/index.php">' . $lang['perhaps_you_were_looking_something_else'] . '</a>';
            } elseif ($_GET['listingID'] == '') {
                $display .= '<a href="' . $config['baseurl'] . '/index.php">' . $lang['perhaps_you_were_looking_something_else'] . '</a>';
            } else {
                $userID = $misc->make_db_safe($_SESSION['userID']);
                $listingID = $misc->make_db_safe($_GET['listingID']);
                $sql = 'DELETE FROM ' . $config['table_prefix'] . "userfavoritelistings WHERE userdb_id = $userID AND listingsdb_id = $listingID";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $display .= '<div class="deletedfromfavorite">' . $lang['listing_deleted_from_favorites'].'</div>';
                $display .= $this->view_favorites();
            }
        } else {
            return $security;
        }
        return $display;
    }

    public function addtofavorites()
    {
        global $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->loginCheck('Member');
        if ($security === true) {
            global $lang, $conn, $misc;
            ob_start();
            $display = '';
            if ($_GET['listingID'] == '') {
                $display .= '<a href="' . $config['baseurl'] . '/index.php">' . $lang['perhaps_you_were_looking_something_else'] . '</a>';
            } else {
                $userID = intval($_SESSION['userID']);
                $listingID = intval($_GET['listingID']);
                $sql = 'SELECT * FROM ' . $config['table_prefix'] . "userfavoritelistings 
						WHERE userdb_id = $userID 
						AND listingsdb_id = $listingID";
                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $misc->log_error($sql);
                }
                $num_columns = $recordSet->RecordCount();
                if ($num_columns == 0) {
                    $sql = 'INSERT INTO ' . $config['table_prefix'] . "userfavoritelistings (userdb_id, listingsdb_id) 
							VALUES ($userID, $listingID)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    echo '<div class="addedtofavorite">' . $lang['listing_added_to_favorites'].'</div>';
                } else {
                    echo '<div class="alreadyaddedtofavorite">' . $lang['listing_already_in_favorites'].'</div>';
                }
            }
            include_once dirname(__FILE__) . '/listing.inc.php';
            $listing_pages = new listing_pages();
            echo $listing_pages->listing_view();
            $display = ob_get_contents();
            ob_end_clean();
            return $display;
        } else {
            return $security;
        }
    }

    public function view_favorites()
    {
        global $config;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $security = $login->loginCheck('Member');
        if ($security === true) {
            global $misc, $lang, $conn;
            $display = '';
            $display .= '<h3>' . $lang['favorite_listings'] . '</h3>';
            $userID = intval($_SESSION['userID']);
            $sql = 'SELECT listingsdb_id 
					FROM ' . $config['table_prefix'] . "userfavoritelistings 
					WHERE userdb_id = $userID";
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }
            $num_columns = $recordSet->RecordCount();
            if ($num_columns == 0) {
                $display .= $lang['no_listing_in_favorites'] . '<br /><br />';
            } else {
                $recordNum = 0;
                $listings = '';
                while (!$recordSet->EOF) {
                    if ($recordNum == 0) {
                        $listings .= $recordSet->fields('listingsdb_id');
                    } else {
                        $listings .= ',' . $recordSet->fields('listingsdb_id');
                    }
                    $recordNum++;
                    $recordSet->MoveNext();
                }
                $_GET['listing_id'] = $listings;
                include_once $config['basepath'] . '/include/search.inc.php';
                $search = new search_page();
                $display .= $search->search_results();
            } // End else
            return $display;
        } else {
            return $security;
        }
    }
} // End cladd membersfavorites extends members
