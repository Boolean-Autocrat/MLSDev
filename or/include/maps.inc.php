<?php

class maps
{
    /**
     * maps::create_map_link()
     * This is the function to call to show a map link. It should be called from the listing detail page, or any page where $_GET['listingID'] is set.
     * This function then calls the appropriate make_mapname function as specified in the configuration.
     *
     * @see maps::make_mapquest()
     * @see maps::make_yahoo_us()
     * @return string Return the URL for the map as long as the required fields are filled out, if not it returns a empty string.
     */
    public function create_map_link($url_only = 'no', $rokbox = 'no')
    {
        global $conn, $config, $misc;

        include_once $config['basepath'] . '/include/listing.inc.php';
        $listing_pages = new listing_pages();

        $display = '';
        $address = '';
        $city = '';
        $state = '';
        $zip = '';
        $listingsdb_id = intval($_GET['listingID']);
        $listing_title = urlencode($listing_pages->get_listing_single_value('listingsdb_title', $listingsdb_id));
        $address1 = urlencode($listing_pages->get_listing_single_value($config['map_address'], $listingsdb_id));
        $address2 = urlencode($listing_pages->get_listing_single_value($config['map_address2'], $listingsdb_id));
        $address3 = urlencode($listing_pages->get_listing_single_value($config['map_address3'], $listingsdb_id));
        $address = urlencode($address1) .' '. urlencode($address2) .' '.urlencode($address3);
        $city = urlencode($listing_pages->get_listing_single_value($config['map_city'], $listingsdb_id));
        $state = urlencode($listing_pages->get_listing_single_value($config['map_state'], $listingsdb_id));
        $zip = urlencode($listing_pages->get_listing_single_value($config['map_zip'], $listingsdb_id));
        $country = urlencode($listing_pages->get_listing_single_value($config['map_country'], $listingsdb_id));

        if ($address != '' || $city != '' || $state != '' || $zip != '') {
            $map_type = 'make_' . $config['map_type'];

            $pos = strpos($map_type, 'mapquest');

            $pos2 = strpos($map_type, 'multimap');
            $pos3 = strpos($map_type, 'global_');
            if ($pos3 !== false) {
                if ($pos !== false) {
                    $display = $this->make_mapquest($country, $address, $city, $state, $zip, $listing_title, $url_only, $rokbox);
                } elseif ($pos2 !== false) {
                    $display = $this->make_multimap($country, $address, $city, $state, $zip, $listing_title, $url_only, $rokbox);
                }
            } elseif ($pos !== false) {
                $country = substr($map_type, -2);
                $display = $this->make_mapquest($country, $address, $city, $state, $zip, $listing_title, $url_only, $rokbox);
            } elseif ($pos2 !== false) {
                $country = substr($map_type, -2);
                $display = $this->make_multimap($country, $address, $city, $state, $zip, $listing_title, $url_only, $rokbox);
            } else {
                $display = $this->$map_type($address, $city, $state, $zip, $listing_title, $url_only, $rokbox);
            }
        }
        return $display;
    }

    public function make_mapquest($country, $address, $city, $state, $zip, $listing_title, $url_only, $rokbox)
    {
        // renders a link to yahoo maps on the page
        global $lang;
        $mapquest_string = "country=$country&amp;addtohistory=&amp;address=$address&amp;city=$city&amp;zipcode=$zip";
        if ($url_only == 'no') {
            $display = "<a href=\"http://www.mapquest.com/maps/map.adp?$mapquest_string\" onclick=\"window.open(this.href,'_blank','location=0,status=0,scrollbars=1,toolbar=0,menubar=0,width=800,height=600,noopener,noreferrer');return false\">$lang[map_link]</a>";
        } else {
            $display = "https://www.mapquest.com/maps/map.adp?$mapquest_string";
        }
        return $display;
    } // end makeMapQuestMap

    public function make_yahoo_us($address, $city, $state, $zip, $listing_title, $url_only, $rokbox)
    {
        global $lang;
        $yahoo_string = "Pyt=Tmap&amp;addr=$address&amp;csz=$city,$state,$zip&amp;Get+Map=Get+Map";
        if ($url_only == 'no') {
            $display = "<a href=\"http://maps.yahoo.com/py/maps.py?$yahoo_string\" onclick=\"window.open(this.href,'_blank','location=0,status=0,scrollbars=1,toolbar=0,menubar=0,width=800,height=600,noopener,noreferrer');return false\">$lang[map_link]</a>";
        } else {
            $display = "http://maps.yahoo.com/py/maps.py?$yahoo_string";
        }
        return $display;
    }

    public function make_google_us($address, $city, $state, $zip, $listing_title, $url_only, $rokbox)
    {
        global $lang;
        $google_string = "maps?q=loc:$address%20$city%20$state%20$zip%20($listing_title)";
        if ($url_only == 'no') {
            $display = "<a href=\"https://maps.google.com/$google_string\" onclick=\"window.open(this.href,'_blank','location=0,status=0,scrollbars=1,toolbar=0,menubar=0,width=800,height=600,noopener,noreferrer');return false\">$lang[map_link]</a>";
        } else {
            $display = "http://maps.google.com/$google_string";
        }
        return $display;
    }

    public function make_multimap($country, $address, $city, $state, $zip, $listing_title, $url_only, $rokbox)
    {
        // renders a link to multi map on the page
        global $lang;
        $multimap_string = "&amp;db=$country&amp;addr2=$address&amp;addr3=$city&amp;pc=$zip";
        if ($url_only == 'no') {
            $display = '<a href="https://www.multimap.com/map/places.cgi?client=public'.$multimap_string.'" target="_map">'.$lang['map_link'].'</a>';
        } else {
            $display = 'http://www.multimap.com/map/places.cgi?client=public'.$multimap_string;
        }
        return $display;
    } // end makeMultiMapFRMap
}
