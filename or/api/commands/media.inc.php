<?php

/**
 * This File Contains the Media Class API Commands
 * @package Open-Realty
 * @subpackage API
 * @copyright 2002 - 2017

 * @link http://www.open-realty.com Open-Realty
 */

/**
 * This is the media API, it contains all api calls for setting and retrieving media.
 *
 * @package Open-Realty
 * @subpackage API
 **/
class media_api
{
    private $media_types = ['listingsimages', 'userimages', 'listingsfiles', 'usersfiles', 'listingsvtours'];

    /**
     * @param unknown_type $url
     * @return string|mixed
     * @access private
     */
    private function get_url($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $data = curl_exec($ch);
        $header  = curl_getinfo($ch);
        curl_close($ch);
        if ($header['http_code'] != '200') {
            return false;
        }
        return $data;
    }
    /**
     * @param unknown_type $input_file_name
     * @param unknown_type $input_file_path
     * @param unknown_type $output_path
     * @return Ambigous <string, unknown>
     * @access private
     */
    private function make_thumb_gd($input_file_name, $input_file_path, $media_type)
    {
        // makes a thumbnail using the GD library
        global $config;
        $quality = $config['jpeg_quality']; // jpeg quality -- set in common.php)
        $output_path = $input_file_path;

        if ($media_type == 'listingsimages' || $media_type == 'listingsvtours') {
            $max_width = $config['thumbnail_width'];
            $max_height = $config['thumbnail_height'];
            $resize_by = $config['resize_thumb_by'];
        } else {
            $max_width = $config['user_thumbnail_width'];
            $max_height = $config['user_thumbnail_height'];
            $resize_by = $config['user_resize_thumb_by'];
        }
        // Specify your file details
        $current_file = $input_file_path . '/' . $input_file_name;

        // Get the current info on the file
        $imagedata = getimagesize($current_file);
        $imagewidth = $imagedata[0];
        $imageheight = $imagedata[1];
        $imagetype = $imagedata[2];
        if ($resize_by == 'width') {
            $shrinkage = $imagewidth / $max_width;
            $new_img_width = $max_width;
            $new_img_height = round($imageheight / $shrinkage);
        } elseif ($resize_by == 'height') {
            $shrinkage = $imageheight / $max_height;
            $new_img_height = $max_height;
            $new_img_width = round($imagewidth / $shrinkage);
        } elseif ($resize_by == 'both') {
            $new_img_width = $max_width;
            $new_img_height = $max_height;
        } elseif ($resize_by == 'bestfit') {
            $shrinkage_width = $imagewidth / $max_width;
            $shrinkage_height = $imageheight / $max_height;
            $shrinkage = max($shrinkage_width, $shrinkage_height);
            $new_img_height = round($imageheight / $shrinkage);
            $new_img_width = round($imagewidth / $shrinkage);
        }
        // type definitions
        // 1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP
        // 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order)
        // 9 = JPC, 10 = JP2, 11 = JPX
        $thumb_name = $input_file_name; //by default
        // the GD library, which this uses, can only resize GIF, JPG and PNG
        if ($imagetype == 1) {
            // it's a GIF
            // see if GIF support is enabled
            if (imagetypes() & IMG_GIF) {
                $src_img = imagecreatefromgif($current_file);
                $dst_img = imageCreateTrueColor($new_img_width, $new_img_height);
                // copy the original image info into the new image with new dimensions
                // checking to see which function is available
                ImageCopyResampled($dst_img, $src_img, 0, 0, 0, 0, $new_img_width, $new_img_height, $imagewidth, $imageheight);
                $thumb_name = 'thumb_' . "$input_file_name";
                imagegif($dst_img, "$output_path/$thumb_name");
                @chmod("$output_path/$thumb_name", 0777);
                imagedestroy($src_img);
                imagedestroy($dst_img);
            } // end if GIF support is enabled
        } // end if $imagetype == 1
        elseif ($imagetype == 2) {
            // it's a JPG
            $src_img = imagecreatefromjpeg($current_file);
            $dst_img = imageCreateTrueColor($new_img_width, $new_img_height);
            // copy the original image info into the new image with new dimensions
            ImageCopyResampled($dst_img, $src_img, 0, 0, 0, 0, $new_img_width, $new_img_height, $imagewidth, $imageheight);
            $thumb_name = 'thumb_' . "$input_file_name";
            imagejpeg($dst_img, "$output_path/$thumb_name", $quality);
            @chmod("$output_path/$thumb_name", 0777);
            imagedestroy($src_img);
            imagedestroy($dst_img);
        } // end if $imagetype == 2
        elseif ($imagetype == 3) {
            // it's a PNG
            $src_img = imagecreatefrompng($current_file);
            $dst_img = imageCreateTrueColor($new_img_width, $new_img_height);
            ImageCopyResampled($dst_img, $src_img, 0, 0, 0, 0, $new_img_width, $new_img_height, $imagewidth, $imageheight);
            $thumb_name = 'thumb_' . "$input_file_name";
            imagepng($dst_img, "$output_path/$thumb_name");
            @chmod("$output_path/$thumb_name", 0777);
            imagedestroy($src_img);
            imagedestroy($dst_img);
        } // end if $imagetype == 3
        return $thumb_name;
    } // end function make_thumb_gd
    /**
     * @param unknown_type $input_file_name
     * @param unknown_type $input_file_path
     * @param unknown_type $type
     * @access private
     */
    private function resize_img_gd($input_file_name, $input_file_path, $media_type)
    {
        // resizes image using the GD library
        global $config;
        $quality = $config['jpeg_quality']; // jpeg quality -- set in common.php
        // Specify your file details
        $current_file = $input_file_path . '/' . $input_file_name;

        if ($media_type == 'listingsimages') {
            $max_width = $config['max_listings_upload_width'];
            $max_height = $config['max_listings_upload_height'];
            $resize_by = $config['resize_by'];
        } else {
            $max_width = $config['max_user_upload_width'];
            $max_height = $config['max_user_upload_height'];
            $resize_by = $config['user_resize_by'];
        }

        // Get the current info on the file
        $imagedata = getimagesize($current_file);

        $imagewidth = $imagedata[0];
        $imageheight = $imagedata[1];
        $imagetype = $imagedata[2];

        // if this is a .jpg see if it has an orientation if so, and it's not normal,
        // swap the width/height values
        if ($imagetype == 2) {
            $exif = exif_read_data($current_file);

            if (!empty($exif['Orientation']) && $exif['Orientation'] != 1) {
                $imagewidth = $imagedata[1];
                $imageheight = $imagedata[0];
            }
        }

        if ($resize_by == 'width') {
            $shrinkage = $imagewidth / $max_width;
            $new_img_width = $max_width;
            $new_img_height = round($imageheight / $shrinkage);
        } elseif ($resize_by == 'height') {
            $shrinkage = $imageheight / $max_height;
            $new_img_height = $max_height;
            $new_img_width = round($imagewidth / $shrinkage);
        } elseif ($resize_by == 'both') {
            $new_img_width = $max_width;
            $new_img_height = $max_height;
        } elseif ($resize_by == 'bestfit') {
            $shrinkage_width = $imagewidth / $max_width;
            $shrinkage_height = $imageheight / $max_height;
            $shrinkage = max($shrinkage_width, $shrinkage_height);
            $new_img_height = round($imageheight / $shrinkage);
            $new_img_width = round($imagewidth / $shrinkage);
        }
        // type definitions
        // 1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP
        // 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order)
        // 9 = JPC, 10 = JP2, 11 = JPX
        $img_name = $input_file_name; //by default
        // the GD library, which this uses, can only resize GIF, JPG and PNG
        if ($imagetype == 1) {
            // it's a GIF
            // see if GIF support is enabled
            if (imagetypes() & IMG_GIF) {
                $src_img = imagecreatefromgif($current_file);
                $dst_img = imageCreateTrueColor($new_img_width, $new_img_height);
                // copy the original image info into the new image with new dimensions
                ImageCopyResampled($dst_img, $src_img, 0, 0, 0, 0, $new_img_width, $new_img_height, $imagewidth, $imageheight);
                $thumb_name = 'thumb_' . "$input_file_name";
                imagegif($dst_img, "$input_file_path/$img_name");
                imagedestroy($src_img);
                imagedestroy($dst_img);
            } //end if GIF support is enabled
        } // end if $imagetype == 1
        elseif ($imagetype == 2) {
            // it's a JPG
            $src_img = imagecreatefromjpeg($current_file);

            //if the .jpg has a EXIF orientation set, rotate it back to normal
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $src_img = imagerotate($src_img, 180, 0);
                        break;
                    case 6:
                        $src_img = imagerotate($src_img, -90, 0);
                        break;
                    case 8:
                        $src_img = imagerotate($src_img, 90, 0);
                        break;
                }
            }

            $dst_img = imageCreateTrueColor($new_img_width, $new_img_height);
            // copy the original image info into the new image with new dimensions
            // checking to see which function is available
            ImageCopyResampled($dst_img, $src_img, 0, 0, 0, 0, $new_img_width, $new_img_height, $imagewidth, $imageheight);
            $img_name = "$input_file_name";
            imagejpeg($dst_img, "$input_file_path/$img_name", $quality);
            imagedestroy($src_img);
            imagedestroy($dst_img);
        } // end if $imagetype == 2
        elseif ($imagetype == 3) {
            // it's a PNG
            $src_img = imagecreatefrompng($current_file);
            $dst_img = imageCreateTrueColor($new_img_width, $new_img_height);
            ImageCopyResampled($dst_img, $src_img, 0, 0, 0, 0, $new_img_width, $new_img_height, $imagewidth, $imageheight);
            $img_name = "$input_file_name";
            imagepng($dst_img, "$input_file_path/$img_name");
            imagedestroy($src_img);
            imagedestroy($dst_img);
        } // end if $imagetype == 3
    } // end function resize_img_gd
    /**
     * @param unknown_type $input_file_name
     * @param unknown_type $input_file_path
     * @param unknown_type $type
     * @access private
     */
    private function resize_img_imagemagick($input_file_name, $input_file_path, $media_type)
    {
        // resizes image using ImageMagick
        global $config;
        // Specify your file details
        $current_file = $input_file_path . '/' . $input_file_name;
        if ($media_type == 'listingsimages') {
            $max_width = $config['max_listings_upload_width'];
            $max_height = $config['max_listings_upload_height'];
            $resize_by = $config['resize_by'];
        } else {
            $max_width = $config['max_user_upload_width'];
            $max_height = $config['max_user_upload_height'];
            $resize_by = $config['user_resize_by'];
        }
        // Get the current info on the file
        $imagedata = getimagesize($current_file);
        $imagewidth = $imagedata[0];
        $imageheight = $imagedata[1];

        if ($resize_by == 'width') {
            $shrinkage = $imagewidth / $max_width;
            $new_img_width = $max_width;
            $new_img_height = round($imageheight / $shrinkage);
        } elseif ($resize_by == 'height') {
            $shrinkage = $imageheight / $max_height;
            $new_img_height = $max_height;
            $new_img_width = round($imagewidth / $shrinkage);
        } elseif ($resize_by == 'both') {
            $new_img_width = $max_width;
            $new_img_height = $max_height;
        } elseif ($resize_by == 'bestfit') {
            $shrinkage_width = $imagewidth / $max_width;
            $shrinkage_height = $imageheight / $max_height;
            $shrinkage = max($shrinkage_width, $shrinkage_height);
            $new_img_height = round($imageheight / $shrinkage);
            $new_img_width = round($imagewidth / $shrinkage);
        }
        // $image_base = explode('.', $current_file);
        // This part gets the new thumbnail name
        // $image_basename = $image_base[0];
        // $image_ext = $image_base[1];
        $path = $config['path_to_imagemagick'];
        $debug_path = '"' . $path . '" -geometry ' . $new_img_width . 'x' . $new_img_height . ' "' . $current_file . '" current_file';
        // Convert the file
        $debug = exec($debug_path);
    } // end function resize_img_imagemagick
    /**
     * @param unknown_type $input_file_name
     * @param unknown_type $input_file_path
     * @return string
     * @access private
     */
    private function make_thumb_imagemagick($input_file_name, $input_file_path, $media_type)
    {
        // makes a thumbnail using ImageMagick
        global $config;
        // Specify your file details
        $current_file = $input_file_path . '/' . $input_file_name;
        if ($media_type == 'listingsimages' || $media_type == 'listingsvtours') {
            $max_width = $config['thumbnail_width'];
            $max_height = $config['thumbnail_height'];
            $resize_by = $config['resize_thumb_by'];
        } else {
            $max_width = $config['user_thumbnail_width'];
            $max_height = $config['user_thumbnail_height'];
            $resize_by = $config['user_resize_thumb_by'];
        }

        // Get the current info on the file
        $imagedata = getimagesize($current_file);
        $imagewidth = $imagedata[0];
        $imageheight = $imagedata[1];

        if ($resize_by == 'width') {
            $shrinkage = $imagewidth / $max_width;
            $new_img_width = $max_width;
            $new_img_height = round($imageheight / $shrinkage);
        } elseif ($resize_by == 'height') {
            $shrinkage = $imageheight / $max_height;
            $new_img_height = $max_height;
            $new_img_width = round($imagewidth / $shrinkage);
        } elseif ($resize_by == 'both') {
            $new_img_width = $max_width;
            $new_img_height = $max_height;
        } elseif ($resize_by == 'bestfit') {
            $shrinkage_width = $imagewidth / $max_width;
            $shrinkage_height = $imageheight / $max_height;
            $shrinkage = max($shrinkage_width, $shrinkage_height);
            $new_img_height = round($imageheight / $shrinkage);
            $new_img_width = round($imagewidth / $shrinkage);
        }

        // $image_base = explode('.', $current_file);
        // This part gets the new thumbnail name
        // $image_basename = $image_base[0];
        // $image_ext = $image_base[1];
        $thumb_name = $input_file_path . '/thumb_' . $input_file_name;
        $thumb_name2 = 'thumb_' . $input_file_name;
        $path = $config['path_to_imagemagick'];
        // Convert the file
        $debug_path = '"' . $path . '" -geometry ' . $new_img_width . 'x' . $new_img_height . ' "' . $current_file . '" "' . $thumb_name . '"';
        $debug = exec($debug_path);
        @chmod("$input_file_path/$thumb_name", 0777);
        return $thumb_name2;
    } // end function make_thumb

    /**
     * @param unknown_type $media_id
     * @param unknown_type $media_type
     * @param unknown_type $parent_id
     * @return string|Ambigous <string, number>
     * @access private
     */
    private function media_permission_check($media_id = 0, $media_type = '', $parent_id = 0)
    {
        global $conn, $lang, $config, $misc;

        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $has_permission = false;
        $parent_id = intval($parent_id);
        $media_type = $media_type;
        //Make sure Media Type is valid
        if (!in_array($media_type, $this->media_types)) {
            return false;
        }

        switch ($media_type) {
            case 'listingsimages':
                include_once $config['basepath'] . '/include/listing.inc.php';
                $listing_pages = new listing_pages();
                if ($media_id != 0 && $parent_id == 0) {
                    $sql = 'SELECT listingsdb_id 
							FROM ' . $config['table_prefix'] . "$media_type 
							WHERE ( " . $media_type . "_id = $media_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $parent_id = $recordSet->fields('listingsdb_id');
                }
                //Get Listing owner
                $listing_agent_id = $listing_pages->get_listing_agent_value('userdb_id', $parent_id);
                //Make sure we can Edit this lisitng
                if ($_SESSION['userID'] != $listing_agent_id) {
                    $security = $login->verify_priv('edit_all_listings');
                    if ($security === true) {
                        $has_permission = $listing_agent_id;
                    }
                } else {
                    $has_permission = $listing_agent_id;
                }
                break;

            case 'userimages':
                if ($media_id != 0 && $parent_id == 0) {
                    $sql = 'SELECT userdb_id 
							FROM ' . $config['table_prefix'] . "$media_type 
							WHERE ( " . $media_type . "_id = $media_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $parent_id = $recordSet->fields('userdb_id');
                }
                if ($_SESSION['userID'] != $parent_id) {
                    $security = $login->verify_priv('edit_all_users');
                    if ($security === true) {
                        $has_permission = $parent_id;
                    }
                } else {
                    $has_permission = $parent_id;
                }

                break;
            case 'listingsvtours':
                include_once $config['basepath'] . '/include/listing.inc.php';
                $listing_pages = new listing_pages();
                if ($media_id != 0 && $parent_id == 0) {
                    $sql = 'SELECT listingsdb_id 
							FROM ' . $config['table_prefix'] . "$media_type 
							WHERE ( " . $media_type . "_id = $media_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $parent_id = $recordSet->fields('listingsdb_id');
                }
                //Get Listing owner
                $listing_agent_id = $listing_pages->get_listing_agent_value('userdb_id', $parent_id);
                //Make sure we can Edit this lisitng
                if ($_SESSION['userID'] != $listing_agent_id) {
                    if ($_SESSION['edit_all_listings'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                        $has_permission = $listing_agent_id;
                    }
                } else {
                    $has_permission = $listing_agent_id;
                }
                break;
            case 'usersfiles':
                if ($media_id != 0 && $parent_id == 0) {
                    $sql = 'SELECT userdb_id 
							FROM ' . $config['table_prefix'] . "$media_type 
							WHERE ( " . $media_type . "_id = $media_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $parent_id = $recordSet->fields('userdb_id');
                }

                //Make sure we can Edit this lisitng
                if ($_SESSION['userID'] != $parent_id) {
                    if ($_SESSION['edit_all_users'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                        $has_permission = $parent_id;
                    }
                } else {
                    $has_permission = $parent_id;
                }
                break;
            case 'listingsfiles':
                include_once $config['basepath'] . '/include/listing.inc.php';
                $listing_pages = new listing_pages();
                if ($media_id != 0 && $parent_id == 0) {
                    $sql = 'SELECT listingsdb_id 
							FROM ' . $config['table_prefix'] . "$media_type 
							WHERE ( " . $media_type . "_id = $media_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $parent_id = $recordSet->fields('listingsdb_id');
                }
                //Get Listing owner
                $listing_agent_id = $listing_pages->get_listing_agent_value('userdb_id', $parent_id);
                //Make sure we can Edit this lisitng
                if ($_SESSION['userID'] != $listing_agent_id) {
                    if ($_SESSION['edit_all_listings'] == 'yes' || $_SESSION['admin_privs'] == 'yes') {
                        $has_permission = $listing_agent_id;
                    }
                } else {
                    $has_permission = $listing_agent_id;
                }
                break;
        }
        return $has_permission;
    }

    /**
     * This function create a new property class.
     *
     * @param array $data Data array should contain the following elements.
     *  <ul>
     *      <li>$data['media_parent_id'] - This should be either the User ID or Listing ID that the media is associated with.</li>
     *      <li>$data['media_type'] - Type of Media ('listingsimages','listingsfiles','listingsvtours','userimages','usersfiles')</li>
     *
     *      <li>$data['media_data'] - This should be array like follows, with the key being the FILENAME for the media file, example  "greenhouse.jpg".</li>
     *      <li>$data['media_data']['FILENAME']['caption'] = 'You Caption goes here"  - OPTIONAL used to set the media caption.</li>
     *      <li>$data['media_data']['FILENAME']['description'] = 'You description goes here"  - OPTIONAL used to set the media description.</li>
     *      <li>$data['media_data']['FILENAME']['data'] = BINARY / ULR  - This should be either the BINARY data for this media or a URL to the media.</li>
     *      <li>$data['media_data']['FILENAME']['remote'] = TRUE /FALSE  - OPTIONAL If set to true the media will only be linked to and not downloaded. Only applies with the data command above contains a URL</li>
     *      <li>$data['media_data']['FILENAME']['rank'] - OPTIONAL INT- Ordering rank of the media. If left empty the media will be the next avaliable rank (last). </li>
     *  </ul>
     *
     * @return array
     */
    public function create($data)
    {
        global $conn, $config, $lapi, $lang, $misc;
        extract($data, EXTR_SKIP || EXTR_REFS, '');
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $login_status = $login->verify_priv('Agent');
        if ($login_status !== true) {
            return ['error' => true, 'error_msg' => 'Login Failure'];
        }
        //Check that required settings were passed
        if (!isset($media_type) || !in_array($media_type, $this->media_types)) {
            return ['error' => true, 'error_msg' => 'correct_parameter_not_passed'];
        }
        if (!isset($media_data) || !is_array($media_data)) {
            return ['error' => true, 'error_msg' => 'correct_parameter_not_passed'];
        }
        if (!isset($media_parent_id) || !is_numeric($media_parent_id)) {
            return ['error' => true, 'error_msg' => 'media_parent_id: correct_parameter_not_passed'];
        }

        $owner = $this->media_permission_check(0, $media_type, $media_parent_id);
        if ($owner != false) {
            $allow_resize = true;
            switch ($media_type) {
                case 'listingsimages':
                    $sql = 'SELECT count(' . $media_type . '_id) as num_images 
							FROM ' . $config['table_prefix'] . $media_type . " 
							WHERE (listingsdb_id = $media_parent_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $num_files = $recordSet->fields('num_images');
                    $avaliable_files = $config['max_listings_uploads'] - $num_files;
                    $max_file_size = $config['max_listings_upload_size'];
                    $max_width = $config['max_listings_upload_width'];
                    $max_height = $config['max_listings_upload_height'];
                    $resize_by = $config['resize_by'];
                    if ($config['resize_img'] == 1) {
                        $allow_resize = true;
                    }
                    $allowed_extensions = $config['allowed_upload_extensions'];
                    break;
                case 'userimages':
                    $sql = 'SELECT count(' . $media_type . '_id) as num_images 
							FROM ' . $config['table_prefix'] . $media_type . " 
							WHERE (userdb_id = $owner)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $num_files = $recordSet->fields('num_images');
                    $avaliable_files = $config['max_user_uploads'] - $num_files;
                    $max_file_size = $config['max_user_upload_size'];
                    $max_width = $config['max_user_upload_width'];
                    $max_height = $config['max_user_upload_height'];
                    $allowed_extensions = $config['allowed_upload_extensions'];
                    $resize_by = $config['user_resize_by'];
                    if ($config['user_resize_img'] == 1) {
                        $allow_resize = true;
                    }
                    break;
                case 'listingsvtours':
                    $sql = 'SELECT count(' . $media_type . '_id) as num_images 
							FROM ' . $config['table_prefix'] . $media_type . " 
							WHERE (listingsdb_id = $media_parent_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $num_files = $recordSet->fields('num_images');
                    $avaliable_files = $config['max_vtour_uploads'] - $num_files;
                    $max_file_size = $config['max_vtour_upload_size'];
                    $max_width = $config['max_vtour_upload_width'];
                    $max_height = 0;
                    $allow_resize = false;
                    $allowed_extensions = $config['allowed_upload_extensions'];
                    break;
                case 'listingsfiles':
                    $sql = 'SELECT count(' . $media_type . '_id) as num_files 
							FROM ' . $config['table_prefix'] . '' . $media_type . " 
							WHERE (listingsdb_id = $media_parent_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $num_files = $recordSet->fields('num_files');
                    $avaliable_files = $config['max_listings_file_uploads'] - $num_files;
                    $max_file_size = $config['max_listings_file_upload_size'];
                    $max_width = 0;
                    $max_height = 0;
                    $allow_resize = false;
                    $allowed_extensions = $config['allowed_file_upload_extensions'];
                    break;
                case 'usersfiles':
                    $sql = 'SELECT count(' . $media_type . '_id) as num_images 
							FROM ' . $config['table_prefix'] . $media_type . " 
							WHERE (userdb_id = $media_parent_id)";
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $misc->log_error($sql);
                    }
                    $num_files = $recordSet->fields('num_images');
                    $avaliable_files = $config['max_users_file_uploads'] - $num_files;
                    $max_file_size = $config['max_users_file_upload_size'];
                    $max_width = 0;
                    $max_height = 0;
                    $allow_resize = false;
                    $allowed_extensions = $config['allowed_file_upload_extensions'];
                    break;
            }
            //Get Multiple URLS at the same time to speed up curl requests..
            //print_r($media_data);
            $image_import_count = 0;
            $master = curl_multi_init();
            $curl_arr = [];
            foreach ($media_data as $media_name => $media_info) {
                if ($image_import_count == $avaliable_files) {
                    continue;
                }
                if ((strpos($media_info['data'], 'http://') === 0 || strpos($media_info['data'], 'https://') === 0 || strpos($media_info['data'], '//') === 0) && (!isset($media_info['remote']) || $media_info['remote'] == false)) {
                    $curl_arr[$media_name] = curl_init();
                    curl_setopt($curl_arr[$media_name], CURLOPT_URL, $media_info['data']);
                    curl_setopt($curl_arr[$media_name], CURLOPT_ENCODING, 'gzip');
                    curl_setopt($curl_arr[$media_name], CURLOPT_TIMEOUT, 20);
                    curl_setopt($curl_arr[$media_name], CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl_arr[$media_name], CURLOPT_SSL_VERIFYPEER, 1);
                    curl_setopt($curl_arr[$media_name], CURLOPT_SSL_VERIFYHOST, 2);
                    curl_multi_add_handle($master, $curl_arr[$media_name]);
                }
                $image_import_count++;
            }
            do {
                curl_multi_exec($master, $running);
            } while ($running > 0);
            foreach ($curl_arr as $media_name => $curlobj) {
                $media_data[$media_name]['data'] = curl_multi_getcontent($curl_arr[$media_name]);
                curl_multi_remove_handle($master, $curl_arr[$media_name]);
            }
            curl_multi_close($master);
            //Keep Track of number of files added.
            $image_import_count = 0;
            $media_response = [];
            $media_error = [];
            //print_r($media_data);
            foreach ($media_data as $media_name => $media_info) {
                //Set Error to True by default
                $media_error[$media_name] = true;
                //Make sure we are not out of allowed media
                if ($image_import_count == $avaliable_files) {
                    $media_response[$media_name] = $lang['media_limit_exceeded'];
                    continue;
                }
                $save_file = false;

                if (!isset($media_info['data'])) {
                    //Skip Bad Media Object
                    $media_response[$media_name] = $lang['media_bad_object'];
                    continue;
                } else {
                    //Figure out if this is an URL or Binary Data
                    if (strpos($media_info['data'], 'http://') !== 0 && strpos($media_info['data'], 'https://') !== 0 && strpos($media_info['data'], '//') !== 0) {
                        //We have Binary Data
                        $filename = $media_name;
                        $save_file = true;
                    } else {
                        //We have a URL check to see if we are importing or linking to this URL.
                        if (isset($media_info['remote']) && $media_info['remote'] == true) {
                            $filename = $media_info['data'];
                        } else {
                            $save_file = true;
                            //Determine filename from the URL
                            $filename = $media_name;
                        }
                    }
                    //Ok we have the file name, check to see if it is already uploaded.
                    $save_name = $misc->make_db_safe($filename);
                    if ($media_type == 'listingsimages') {
                        $sql = 'SELECT listingsimages_file_name FROM ' . $config['table_prefix'] . "listingsimages WHERE listingsimages_file_name = $save_name";
                    } elseif ($media_type == 'listingsvtours') {
                        $sql = 'SELECT listingsvtours_file_name FROM ' . $config['table_prefix'] . "listingsvtours WHERE listingsvtours_file_name = $save_name";
                    } elseif ($media_type == 'userimages') {
                        $sql = 'SELECT userimages_file_name FROM ' . $config['table_prefix'] . "userimages WHERE userimages_file_name = $save_name";
                    } elseif ($media_type == 'listingsfiles') {
                        $sql = 'SELECT listingsfiles_file_name FROM ' . $config['table_prefix'] . "listingsfiles WHERE listingsfiles_file_name = $save_name";
                    } elseif ($media_type == 'usersfiles') {
                        $sql = 'SELECT usersfiles_file_name FROM ' . $config['table_prefix'] . "usersfiles WHERE usersfiles_file_name = $save_name";
                    }
                    $recordSet = $conn->Execute($sql);
                    if (!$recordSet) {
                        $error = $conn->ErrorMsg();
                        $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->create', 'log_message' => 'DB Error: ' . $error]);
                        return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                    }
                    $num = $recordSet->RecordCount();
                    if ($num > 0) {
                        $media_response[$media_name] = $lang['media_object_exists'];
                        continue;
                    }
                    if ($save_file == true) {
                        //This is a saftey in case the multi curl get failed..
                        if (strpos($media_info['data'], 'http://') === 0 || strpos($media_info['data'], 'https://') === 0 || strpos($media_info['data'], '//') === 0) {
                            $file_data = $this->get_url($media_info['data']);
                            if ($file_data == false) {
                                $media_response[$media_name] = $lang['media_object_invalid_url'];
                                continue;
                            }
                        } else {
                            $file_data = $media_info['data'];
                        }

                        $file_data = $media_info['data'];

                        $temp_file = tempnam(sys_get_temp_dir(), $filename);
                        $fp = fopen($temp_file, 'wb');
                        fwrite($fp, $file_data);
                        fclose($fp);

                        $extension = strtolower(substr(strrchr($filename, '.'), 1));
                        $filesize = filesize($temp_file);
                        // check file extensions
                        if (!in_array($extension, explode(',', $allowed_extensions))) {
                            $media_response[$media_name] = $lang['upload_invalid_extension'];
                            unlink($temp_file);
                            continue;
                        }
                        // check size

                        if ($max_file_size != 0 && $filesize > $max_file_size) {
                            $media_response[$media_name] = $lang['upload_too_large'];
                            unlink($temp_file);
                            continue;
                        }
                        //Test Image Height & Width Restrictions
                        // check width & height
                        if ($max_width !== 0 && $max_height !== 0) {
                            $imagedata = GetImageSize($temp_file);
                            $imagewidth = $imagedata[0];
                            $imageheight = $imagedata[1];
                            if ($allow_resize == true) {
                                $shrinkage = 1;
                                // Figure out what the sizes are going to be AFTER resizing the images to know if we should allow the upload or not
                                if ($resize_by == 'width') {
                                    if ($imagewidth > $max_width) {
                                        $shrinkage = $imagewidth / $max_width;
                                    }
                                    $new_img_width = $max_width;
                                    $new_img_height = round($imageheight / $shrinkage);
                                    if ($new_img_height > $max_height) {
                                        $media_response[$media_name] = $lang['upload_too_high'];
                                        unlink($temp_file);
                                        continue;
                                    }
                                } elseif ($resize_by == 'height') {
                                    if ($imageheight > $max_height) {
                                        $shrinkage = $imageheight / $max_height;
                                    }
                                    $new_img_height = $max_height;
                                    $new_img_width = round($imagewidth / $shrinkage);
                                    if ($new_img_width > $max_width) {
                                        $media_response[$media_name] = $lang['upload_too_wide'];
                                        unlink($temp_file);
                                        continue;
                                    }
                                } elseif ($resize_by == 'both') {
                                } elseif ($resize_by == 'bestfit') {
                                }
                            } else {
                                if ($max_width != 0 && $imagewidth > $max_width) {
                                    $media_response[$media_name] = $lang['upload_too_wide'];
                                    unlink($temp_file);
                                    continue;
                                }
                                if ($max_height != 0) {
                                    if ($imageheight > $max_height) {
                                        $media_response[$media_name] = $lang['upload_too_high'];
                                        unlink($temp_file);
                                        continue;
                                    }
                                }
                            }
                        }
                    }
                    switch ($media_type) {
                        case 'listingsimages':
                            $thumb_name = $save_name; // by default -- no difference... unless...
                            if ($save_file == true) {
                                if (!@rename($temp_file, $config['listings_upload_path'] . '/' . $filename)) {
                                    if (copy($temp_file, $config['listings_upload_path'] . '/' . $filename)) {
                                        unlink($temp_file);
                                    } else {
                                        unlink($temp_file);
                                        $media_response[$media_name] .= $lang['media_object_rename_failed'] . ' ' . $temp_file . ' -> ' . $config['listings_upload_path'] . '/' . $filename;
                                        continue 2;
                                    }
                                }
                                if ($allow_resize && ($imagewidth > $max_width || $imageheight > $max_height)) {
                                    // if the option to resize the images on upload is activated...
                                    $resize_img = 'resize_img_' . $config['thumbnail_prog'];
                                    $img_name = $this->$resize_img($filename, $config['listings_upload_path'], $media_type);
                                } // end if $config[resize_img] === "1"
                                if ($config['make_thumbnail'] == '1') {
                                    // if the option to make a thumbnail is activated...
                                    $make_thumb = 'make_thumb_' . $config['thumbnail_prog'];
                                    $thumb_name = $misc->make_db_safe($this->$make_thumb($filename, $config['listings_upload_path'], $media_type));
                                } // end if $config[make_thumbnail] === "1"
                            }
                            // Get Max Image Rank
                            if (!isset($media_info['rank'])) {
                                $sql = 'SELECT MAX(listingsimages_rank) AS max_rank 
										FROM ' . $config['table_prefix'] . "listingsimages 
										WHERE (listingsdb_id = $media_parent_id)";
                                $recordSet = $conn->Execute($sql);
                                if (!$recordSet) {
                                    $error = $conn->ErrorMsg();
                                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->create', 'log_message' => 'DB Error: ' . $error]);
                                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                                }
                                $rank = $recordSet->fields('max_rank');
                                $rank++;
                            } else {
                                $rank = intval($media_info['rank']);
                            }
                            //Deal with Caption and Description.
                            $caption = $misc->make_db_safe('');
                            $description = $misc->make_db_safe('');
                            if (isset($media_info['caption'])) {
                                $caption = $misc->make_db_safe($media_info['caption']);
                            }
                            if (isset($media_info['description'])) {
                                $description = $misc->make_db_safe($media_info['description']);
                            }
                            $sql = 'INSERT INTO ' . $config['table_prefix'] . "listingsimages (
										listingsdb_id, 
										userdb_id, 
										listingsimages_file_name, 
										listingsimages_thumb_file_name,
										listingsimages_rank,
										listingsimages_caption,
										listingsimages_description,
										listingsimages_active
									) 
									VALUES (
										$media_parent_id, 
										$owner, 
										$save_name, 
										$thumb_name,
										$rank,
										$caption,
										$description,
										'yes'
									)";
                            $recordSet = $conn->Execute($sql);
                            if (!$recordSet) {
                                $error = $conn->ErrorMsg();
                                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->create', 'log_message' => 'DB Error: ' . $error]);
                                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                            }
                            //$lapi->load_local_api('log__log_create_entry',array('log_type'=>'CRIT','log_api_command'=>'api->media->create','log_message'=>"$lang[log_uploaded_listing_image] $filename"));
                            $media_response[$media_name] = "$lang[log_uploaded_listing_image] $filename";
                            $media_error[$media_name] = false;
                            @chmod("$config[listings_upload_path]/$filename", 0777);
                            break;
                        case 'userimages':
                            $thumb_name = $save_name; // by default -- no difference... unless...
                            if ($save_file == true) {
                                if (!@rename($temp_file, $config['user_upload_path'] . '/' . $filename)) {
                                    if (copy($temp_file, $config['user_upload_path'] . '/' . $filename)) {
                                        unlink($temp_file);
                                    } else {
                                        unlink($temp_file);
                                        $media_response[$media_name] .= $lang['media_object_rename_failed'] . ' ' . $temp_file . ' -> ' . $config['user_upload_path'] . '/' . $filename;
                                        continue 2;
                                    }
                                }
                                if ($allow_resize && ($imagewidth > $max_width || $imageheight > $max_height)) {
                                    // if the option to resize the images on upload is activated...
                                    $resize_img = 'resize_img_' . $config['thumbnail_prog'];
                                    $img_name = $this->$resize_img($filename, $config['user_upload_path'], $media_type);
                                } // end if $config[resize_img] === "1"
                                if ($config['make_thumbnail'] == '1') {
                                    // if the option to make a thumbnail is activated...
                                    $make_thumb = 'make_thumb_' . $config['thumbnail_prog'];
                                    $thumb_name = $misc->make_db_safe($this->$make_thumb($filename, $config['user_upload_path'], $media_type));
                                } // end if $config[make_thumbnail] === "1"
                            }
                            // Get Max Image Rank
                            if (!isset($media_info['rank'])) {
                                $sql = 'SELECT MAX(userimages_rank) AS max_rank 
										FROM ' . $config['table_prefix'] . "userimages 
										WHERE (userdb_id = $media_parent_id)";
                                $recordSet = $conn->Execute($sql);
                                if (!$recordSet) {
                                    $error = $conn->ErrorMsg();
                                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->create', 'log_message' => 'DB Error: ' . $error]);
                                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                                }
                                $rank = $recordSet->fields('max_rank');
                                $rank++;
                            } else {
                                $rank = intval($media_info['rank']);
                            }
                            //Deal with Caption and Description.
                            $caption = $misc->make_db_safe('');
                            $description = $misc->make_db_safe('');
                            if (isset($media_info['caption'])) {
                                $caption = $misc->make_db_safe($media_info['caption']);
                            }
                            if (isset($media_info['description'])) {
                                $description = $misc->make_db_safe($media_info['description']);
                            }
                            $sql = 'INSERT INTO ' . $config['table_prefix'] . "userimages (
										userdb_id, 
										userimages_file_name, 
										userimages_thumb_file_name,
										userimages_rank,
										userimages_caption,
										userimages_description,
										userimages_active
									) 
									VALUES (
										$media_parent_id, 
										$save_name, 
										$thumb_name,
										$rank,
										$caption,
										$description,
										'yes'
									)";
                            $recordSet = $conn->Execute($sql);
                            if (!$recordSet) {
                                $error = $conn->ErrorMsg();
                                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->create', 'log_message' => 'DB Error: ' . $error]);
                                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                            }
                            //$lapi->load_local_api('log__log_create_entry',array('log_type'=>'CRIT','log_api_command'=>'api->media->create','log_message'=>"$lang[log_uploaded_listing_image] $filename"));
                            $media_response[$media_name] = "$lang[log_uploaded_user_image] $filename";
                            $media_error[$media_name] = false;
                            @chmod("$config[user_upload_path]/$filename", 0777);
                            break;
                        case 'listingsfiles':
                            $uploadpath = $config['listings_file_upload_path'] . '/' . $media_parent_id;
                            @mkdir($uploadpath);
                            if ($save_file == true) {
                                if (!@rename($temp_file, $uploadpath . '/' . $filename)) {
                                    if (copy($temp_file, $uploadpath . '/' . $filename)) {
                                        unlink($temp_file);
                                    } else {
                                        unlink($temp_file);
                                        $media_response[$media_name] .= $lang['media_object_rename_failed'] . ' ' . $temp_file . ' -> ' . $uploadpath . '/' . $filename;
                                        continue 2;
                                    }
                                }
                            }
                            // Get Max Files Rank
                            if (!isset($media_info['rank'])) {
                                $sql = 'SELECT MAX(listingsfiles_rank) 
										AS max_rank 
										FROM ' . $config['table_prefix'] . "listingsfiles 
										WHERE (listingsdb_id = $media_parent_id)";
                                $recordSet = $conn->Execute($sql);
                                if (!$recordSet) {
                                    $error = $conn->ErrorMsg();
                                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->create', 'log_message' => 'DB Error: ' . $error]);
                                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                                }
                                $rank = $recordSet->fields('max_rank');
                                $rank++;
                            } else {
                                $rank = intval($media_info['rank']);
                            }
                            //Deal with Caption and Description.
                            $caption = $misc->make_db_safe('');
                            $description = $misc->make_db_safe('');
                            if (isset($media_info['caption'])) {
                                $caption = $misc->make_db_safe($media_info['caption']);
                            }
                            if (isset($media_info['description'])) {
                                $description = $misc->make_db_safe($media_info['description']);
                            }
                            $sql = 'INSERT INTO ' . $config['table_prefix'] . "listingsfiles (
										listingsdb_id, 
										userdb_id, 
										listingsfiles_file_name, 
										listingsfiles_rank,
										listingsfiles_caption,
										listingsfiles_description,
										listingsfiles_active
									) 
									VALUES (
										$media_parent_id,
										$owner, 
										$save_name,
										$rank,
										$caption,
										$description,
										'yes'
									)";
                            $recordSet = $conn->Execute($sql);
                            if (!$recordSet) {
                                $error = $conn->ErrorMsg();
                                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->create', 'log_message' => 'DB Error: ' . $error]);
                                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                            }
                            //$lapi->load_local_api('log__log_create_entry',array('log_type'=>'CRIT','log_api_command'=>'api->media->create','log_message'=>"$lang[log_uploaded_listing_image] $filename"));
                            $media_response[$media_name] = "$uploadpath $filename";
                            $media_error[$media_name] = false;
                            @chmod("$uploadpath/$filename", 0777);
                            break;
                        case 'usersfiles':
                            $uploadpath = $config['users_file_upload_path'] . '/' . $media_parent_id;
                            @mkdir($uploadpath);
                            if ($save_file == true) {
                                if (!@rename($temp_file, $uploadpath . '/' . $filename)) {
                                    if (copy($temp_file, $uploadpath . '/' . $filename)) {
                                        unlink($temp_file);
                                    } else {
                                        unlink($temp_file);
                                        $media_response[$media_name] .= $lang['media_object_rename_failed'] . ' ' . $temp_file . ' -> ' . $uploadpath . '/' . $filename;
                                        continue 2;
                                    }
                                }
                            }
                            // Get Max Image Rank
                            if (!isset($media_info['rank'])) {
                                $sql = 'SELECT MAX(usersfiles_rank) 
										AS max_rank 
										FROM ' . $config['table_prefix'] . "usersfiles 
										WHERE (userdb_id = $media_parent_id)";
                                $recordSet = $conn->Execute($sql);
                                if (!$recordSet) {
                                    $error = $conn->ErrorMsg();
                                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->create', 'log_message' => 'DB Error: ' . $error]);
                                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                                }
                                $rank = $recordSet->fields('max_rank');
                                $rank++;
                            } else {
                                $rank = intval($media_info['rank']);
                            }
                            //Deal with Caption and Description.
                            $caption = $misc->make_db_safe('');
                            $description = $misc->make_db_safe('');
                            if (isset($media_info['caption'])) {
                                $caption = $misc->make_db_safe($media_info['caption']);
                            }
                            if (isset($media_info['description'])) {
                                $description = $misc->make_db_safe($media_info['description']);
                            }
                            $sql = 'INSERT INTO ' . $config['table_prefix'] . "usersfiles (userdb_id, usersfiles_file_name, usersfiles_rank,usersfiles_caption,usersfiles_description,usersfiles_active) 
									VALUES ($media_parent_id, $save_name,$rank,$caption,$description,'yes')";
                            $recordSet = $conn->Execute($sql);
                            if (!$recordSet) {
                                $error = $conn->ErrorMsg();
                                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->create', 'log_message' => 'DB Error: ' . $error]);
                                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                            }
                            //$lapi->load_local_api('log__log_create_entry',array('log_type'=>'CRIT','log_api_command'=>'api->media->create','log_message'=>"$lang[log_uploaded_listing_image] $filename"));
                            $media_response[$media_name] = "$uploadpath $filename";
                            $media_error[$media_name] = false;
                            @chmod("$uploadpath/$filename", 0777);
                            break;
                        case 'listingsvtours':
                            $thumb_name = $save_name; // by default -- no difference... unless...
                            if ($save_file == true) {
                                if (!@rename($temp_file, $config['vtour_upload_path'] . '/' . $filename)) {
                                    if (copy($temp_file, $config['vtour_upload_path'] . '/' . $filename)) {
                                        unlink($temp_file);
                                    } else {
                                        unlink($temp_file);
                                        $media_response[$media_name] .= $lang['media_object_rename_failed'] . ' ' . $temp_file . ' -> ' . $config['vtour_upload_path'] . '/' . $filename;
                                        continue 2;
                                    }
                                }
                                // if ($config['make_thumbnail'] == '1') {
                                //     // if the option to make a thumbnail is activated...
                                //     $make_thumb = 'make_thumb_' . $config['thumbnail_prog'];
                                //     $thumb_name = $misc->make_db_safe($this->$make_thumb($filename, $config['vtour_upload_path'], $media_type));
                                // } // end if $config[make_thumbnail] === "1"
                            }
                            // Get Max Image Rank
                            if (!isset($media_info['rank'])) {
                                $sql = 'SELECT MAX(listingsvtours_rank) 
										AS max_rank FROM ' . $config['table_prefix'] . "listingsvtours 
										WHERE (listingsdb_id = $media_parent_id)";
                                $recordSet = $conn->Execute($sql);
                                if (!$recordSet) {
                                    $error = $conn->ErrorMsg();
                                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->create', 'log_message' => 'DB Error: ' . $error]);
                                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                                }
                                $rank = $recordSet->fields('max_rank');
                                $rank++;
                            } else {
                                $rank = intval($media_info['rank']);
                            }
                            //Deal with Caption and Description.
                            $caption = $misc->make_db_safe('');
                            $description = $misc->make_db_safe('');
                            if (isset($media_info['caption'])) {
                                $caption = $misc->make_db_safe($media_info['caption']);
                            }
                            if (isset($media_info['description'])) {
                                $description = $misc->make_db_safe($media_info['description']);
                            }
                            $sql = 'INSERT INTO ' . $config['table_prefix'] . "listingsvtours (listingsdb_id, userdb_id, listingsvtours_file_name, listingsvtours_thumb_file_name,listingsvtours_rank,listingsvtours_caption,listingsvtours_description,listingsvtours_active) 
									VALUES ($media_parent_id, $owner, $save_name, $thumb_name,$rank,$caption,$description,'yes')";
                            $recordSet = $conn->Execute($sql);
                            if (!$recordSet) {
                                $error = $conn->ErrorMsg();
                                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->create', 'log_message' => 'DB Error: ' . $error]);
                                return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                            }
                            //$lapi->load_local_api('log__log_create_entry',array('log_type'=>'CRIT','log_api_command'=>'api->media->create','log_message'=>"$lang[log_uploaded_listing_image] $filename"));
                            $media_response[$media_name] = "$lang[log_uploaded_listing_image] $filename";
                            $media_error[$media_name] = false;
                            @chmod("$config[vtour_upload_path]/$filename", 0777);
                            break;
                    }

                    $mediainfo = [];
                    $mediainfo['file_name'] = $filename;
                    $mediainfo['media_type'] = $media_type;
                    $mediainfo['media_parent_id'] = $media_parent_id;

                    //Call after_new_media hoook
                    include_once $config['basepath'] . '/include/hooks.inc.php';
                    $hooks = new hooks();
                    $hooks->load('after_new_media', $mediainfo);

                    $image_import_count++;
                }
            }
            return ['error' => false, 'media_response' => $media_response, 'media_error' => $media_error];
        } else {
            return ['error' => true, 'error_msg' => 'permission_denied'];
        }
    }

    /**
     * This function reads media information (get's photos, files, etc). (Avaliable in 3.0.13)
     * userimages $media_type added 1/07/2017
     * Example
     * <code>
     * //Call the API and Get all Images for Listing 4
     * $api_result = $api->load_local_api('media__read',array('media_type'=>'listingsimages','media_parent_id'=>4,'media_output'=>'URL'));
     * if($api_result['error']){
     *  //If an error occurs die and show the error msg;
     *  die($api_result['error_msg']);
     * }
     * //No error so display the thumbnail images that were returned.
     * foreach($api_result['media_object'] as $obj){
     * echo '<img src="'.$obj['thumb_file_src'].'" width="'.$obj['thumb_width'].'" height="'.$obj['thumb_height'].'"/>
     * }
     * </code>
     *
     * @param array $data Data array should contain the following elements.
     *  <ul>
     *      <li>$data['media_parent_id'] - This should be either the User ID or Listing ID that the media is associated with.</li>
     *      <li>$data['media_type'] - Type of Media ('listingsimages','listingsfiles','listingsvtours','userimages','usersfiles') (Only listingsimages and userimages is avaliable currently)</li>
     *      <li>$data['media_output'] - DATA/URL. DATA will return the raw binary image, instead of the URL. You may not get all images if using DATA mode, as remote images will not be returned</li>
     *  </ul>
     *
     * @return array
     */
    public function read($data)
    {
        global $conn, $config, $lapi, $lang;
        extract($data, EXTR_SKIP || EXTR_REFS, '');
        $media_output_types = ['DATA', 'URL'];
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $agent_status = $login->verify_priv('Agent');

        //Check that required settings were passed
        if (!isset($media_type) || !in_array($media_type, $this->media_types)) {
            return ['error' => true, 'error_msg' => 'correct_parameter_not_passed - media_type'];
        }
        if (!isset($media_output) || !in_array($media_output, $media_output_types)) {
            return ['error' => true, 'error_msg' => 'correct_parameter_not_passed - media_output'];
        }
        if (!isset($media_parent_id) || !is_numeric($media_parent_id)) {
            return ['error' => true, 'error_msg' => 'media_parent_id: correct_parameter_not_passed - media_parent_id'];
        }

        //optional
        if (isset($media_limit) && is_numeric($media_limit) && $media_limit != 0) {
            $LIMITstr = "LIMIT $media_limit";
        } else {
            $LIMITstr = '';
        }

        $media_object = [];
        $media_count = 0;
        switch ($media_type) {
            case 'listingsimages':
                if ($agent_status) {
                    $sql = 'SELECT listingsimages_id, listingsimages_caption, listingsimages_description, listingsimages_file_name, listingsimages_thumb_file_name,listingsimages_rank 
							FROM ' . $config['table_prefix'] . "listingsimages 
							WHERE (listingsdb_id = $media_parent_id) ORDER BY listingsimages_rank
							$LIMITstr";
                } else {
                    $sql = 'SELECT listingsimages_id, listingsimages_caption, listingsimages_description, listingsimages_file_name, listingsimages_thumb_file_name,listingsimages_rank 
							FROM ' . $config['table_prefix'] . 'listingsimages 
							LEFT JOIN ' . $config['table_prefix'] . 'listingsdb 
							ON ' . $config['table_prefix'] . 'listingsimages.listingsdb_id = ' . $config['table_prefix'] . 'listingsdb.listingsdb_id 
							WHERE ' . $config['table_prefix'] . "listingsimages.listingsdb_id = $media_parent_id AND listingsdb_active = 'yes'  
							ORDER BY listingsimages_rank
							$LIMITstr";
                }

                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->read', 'log_message' => 'DB Error: ' . $error . ' Full SQL: ' . $sql]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }
                $media_count = $recordSet->RecordCount();
                while (!$recordSet->EOF) {
                    $this_media = [];
                    $this_media['media_id'] = $recordSet->fields('listingsimages_id');
                    $this_media['media_rank'] = $recordSet->fields('listingsimages_rank');
                    $this_media['caption'] = $recordSet->fields('listingsimages_caption');
                    $this_media['description'] = $recordSet->fields('listingsimages_description');
                    $this_media['thumb_file_name'] = $recordSet->fields('listingsimages_thumb_file_name');
                    $this_media['file_name'] = $recordSet->fields('listingsimages_file_name');
                    $image_id = $recordSet->fields('listingsimages_id');
                    //Deal with Remote Images
                    if (strpos($this_media['thumb_file_name'], 'http://') === 0 || strpos($this_media['thumb_file_name'], 'https://') === 0 || strpos($this_media['thumb_file_name'], '//') === 0) {
                        if ($media_output == 'URL') {
                            $this_media['thumb_file_src'] = $recordSet->fields('listingsimages_thumb_file_name');
                            $this_media['file_src'] = $recordSet->fields('listingsimages_file_name');
                            $this_media['remote'] = true;
                        }
                    } else {
                        if (file_exists($config['listings_upload_path'] . '/' . $this_media['thumb_file_name'])) {
                            $thumb_imagedata = GetImageSize($config['listings_upload_path'] . '/' . $this_media['thumb_file_name']);
                            $thumb_imagewidth = $thumb_imagedata[0];
                            $this_media['thumb_width'] = $thumb_imagewidth;
                            $thumb_imageheight = $thumb_imagedata[1];
                            $this_media['thumb_height'] = $thumb_imageheight;
                            if ($media_output == 'URL') {
                                $this_media['thumb_file_src'] = $config['listings_view_images_path'] . '/' . $this_media['thumb_file_name'];
                            } else {
                                $this_media['thumb_file_src'] = file_get_contents($config['listings_upload_path'] . '/' . $this_media['thumb_file_name']);
                            }
                        }
                        if (file_exists($config['listings_upload_path'] . '/' . $this_media['file_name'])) {
                            $imagedata = GetImageSize($config['listings_upload_path'] . '/' . $this_media['file_name']);
                            $imagewidth = $imagedata[0];
                            $this_media['file_width'] = $imagewidth;
                            $imageheight = $imagedata[1];
                            $this_media['file_height'] = $imageheight;
                            if ($media_output == 'URL') {
                                $this_media['file_src'] = $config['listings_view_images_path'] . '/' . $this_media['file_name'];
                            } else {
                                $this_media['file_src'] = file_get_contents($config['listings_upload_path'] . '/' . $this_media['file_name']);
                            }
                        }
                        $this_media['remote'] = false;
                    }
                    $media_object[] = $this_media;
                    $recordSet->MoveNext();
                } // end while
                break;

            case 'listingsfiles':
                if ($agent_status) {
                    $sql = 'SELECT listingsfiles_id, listingsfiles_caption, listingsfiles_description, listingsfiles_file_name, listingsfiles_rank 
							FROM ' . $config['table_prefix'] . "listingsfiles 
							WHERE (listingsdb_id = $media_parent_id) ORDER BY listingsfiles_rank
							$LIMITstr";
                } else {
                    $sql = 'SELECT listingsfiles_id, listingsfiles_caption, listingsfiles_description, listingsfiles_file_name, listingsfiles_rank 
							FROM ' . $config['table_prefix'] . 'listingsfiles 
							LEFT JOIN ' . $config['table_prefix'] . 'listingsdb 
							ON ' . $config['table_prefix'] . 'listingsfiles.listingsdb_id = ' . $config['table_prefix'] . 'listingsdb.listingsdb_id 
							WHERE ' . $config['table_prefix'] . "listingsfiles.listingsdb_id = $media_parent_id AND listingsdb_active = 'yes'  
							ORDER BY listingsfiles_rank
							$LIMITstr";
                }

                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->read', 'log_message' => 'DB Error: ' . $error . ' Full SQL: ' . $sql]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }
                $media_count = $recordSet->RecordCount();
                while (!$recordSet->EOF) {
                    $this_media = [];
                    $this_media['media_id'] = $recordSet->fields('listingsfiles_id');
                    $this_media['media_rank'] = $recordSet->fields('listingsfiles_rank');
                    $this_media['caption'] = $recordSet->fields('listingsfiles_caption');
                    $this_media['description'] = $recordSet->fields('listingsfiles_description');
                    $this_media['file_name'] = $recordSet->fields('listingsfiles_file_name');
                    $file_id = $recordSet->fields('listingsfiles_id');

                    //Deal with Remote files
                    if (strpos($this_media['file_name'], 'http://') === 0 || strpos($this_media['file_name'], 'https://') === 0 || strpos($this_media['file_name'], '//') === 0) {
                        if ($media_output == 'URL') {
                            $this_media['file_src'] = $recordSet->fields('listingsfiles_file_name');
                        }
                    }
                    if ($media_output == 'URL') {
                        $this_media['file_src'] = $config['listings_view_file_path'] . '/' . $media_parent_id . '/' . $this_media['file_name'];
                        if (file_exists($this_media['file_src'])) {
                            $this_media['file_size'] = filesize($this_media['file_src']);
                        }
                    } else {
                        $this_media['thumb_file_src'] = file_get_contents($config['listings_file_upload_path'] . '/' . $this_media['file_name']);
                    }

                    $media_object[] = $this_media;
                    $recordSet->MoveNext();
                } // end while
                break;

            case 'userimages':
                if ($agent_status) {
                    $sql = 'SELECT userimages_id, userimages_caption, userimages_description, userimages_file_name, userimages_thumb_file_name,userimages_rank 
							FROM ' . $config['table_prefix'] . "userimages 
							WHERE (userdb_id = $media_parent_id) ORDER BY userimages_rank
							$LIMITstr";
                } else {
                    $sql = 'SELECT userimages_id, userimages_caption, userimages_description, userimages_file_name, userimages_thumb_file_name,userimages_rank 
							FROM ' . $config['table_prefix'] . 'userimages 
							LEFT JOIN ' . $config['table_prefix'] . 'userdb 
							ON ' . $config['table_prefix'] . 'userimages.userdb_id = ' . $config['table_prefix'] . 'userdb.userdb_id 
							WHERE ' . $config['table_prefix'] . "userimages.userdb_id = $media_parent_id AND userdb_active = 'yes'  
							ORDER BY userimages_rank
							$LIMITstr";
                    ;
                }

                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->read', 'log_message' => 'DB Error: ' . $error . ' Full SQL: ' . $sql]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }
                $media_count = $recordSet->RecordCount();
                while (!$recordSet->EOF) {
                    $this_media = [];
                    $this_media['media_id'] = $recordSet->fields('userimages_id');
                    $this_media['media_rank'] = $recordSet->fields('userimages_rank');
                    $this_media['caption'] = $recordSet->fields('userimages_caption');
                    $this_media['description'] = $recordSet->fields('userimages_description');
                    $this_media['thumb_file_name'] = $recordSet->fields('userimages_thumb_file_name');
                    $this_media['file_name'] = $recordSet->fields('userimages_file_name');
                    $image_id = $recordSet->fields('userimages_id');
                    //Deal with Remote Images
                    if (strpos($this_media['thumb_file_name'], 'http://') === 0 || strpos($this_media['thumb_file_name'], 'https://') === 0 || strpos($this_media['thumb_file_name'], '//') === 0) {
                        if ($media_output == 'URL') {
                            $this_media['thumb_file_src'] = $recordSet->fields('userimages_thumb_file_name');
                            $this_media['file_src'] = $recordSet->fields('userimages_file_name');
                        }
                    } else {
                        if (file_exists($config['user_upload_path'] . '/' . $this_media['thumb_file_name'])) {
                            $thumb_imagedata = GetImageSize($config['user_upload_path'] . '/' . $this_media['thumb_file_name']);
                            $thumb_imagewidth = $thumb_imagedata[0];
                            $this_media['thumb_width'] = $thumb_imagewidth;
                            $thumb_imageheight = $thumb_imagedata[1];
                            $this_media['thumb_height'] = $thumb_imageheight;
                            if ($media_output == 'URL') {
                                $this_media['thumb_file_src'] = $config['user_view_images_path'] . '/' . $this_media['thumb_file_name'];
                                if (file_exists($this_media['thumb_file_src'])) {
                                    $this_media['thumb_file_size'] = filesize($this_media['thumb_file_src']);
                                }
                            } else {
                                $this_media['thumb_file_src'] = file_get_contents($config['user_upload_path'] . '/' . $this_media['thumb_file_name']);
                            }
                        }
                        if (file_exists($config['user_upload_path'] . '/' . $this_media['file_name'])) {
                            $imagedata = GetImageSize($config['user_upload_path'] . '/' . $this_media['file_name']);
                            $imagewidth = $imagedata[0];
                            $this_media['file_width'] = $imagewidth;
                            $imageheight = $imagedata[1];
                            $this_media['file_height'] = $imageheight;
                            if ($media_output == 'URL') {
                                $this_media['file_src'] = $config['user_view_images_path'] . '/' . $this_media['file_name'];
                                if (file_exists($this_media['file_src'])) {
                                    $this_media['file_size'] = filesize($this_media['file_src']);
                                }
                            } else {
                                $this_media['file_src'] = file_get_contents($config['user_upload_path'] . '/' . $this_media['file_name']);
                            }
                        }
                    }
                    $media_object[] = $this_media;
                    $recordSet->MoveNext();
                } // end while
                break;

            case 'usersfiles':
                if ($agent_status) {
                    $sql = 'SELECT usersfiles_id, usersfiles_caption, usersfiles_description, usersfiles_file_name, usersfiles_rank 
							FROM ' . $config['table_prefix'] . "usersfiles 
							WHERE (userdb_id = $media_parent_id) ORDER BY usersfiles_rank
							$LIMITstr";
                } else {
                    $sql = 'SELECT usersfiles_id, usersfiles_caption, usersfiles_description, usersfiles_file_name, usersfiles_rank 
							FROM ' . $config['table_prefix'] . 'usersfiles 
							LEFT JOIN ' . $config['table_prefix'] . 'listingsdb 
							ON ' . $config['table_prefix'] . 'usersfiles.userdb_id = ' . $config['table_prefix'] . 'listingsdb.userdb_id 
							WHERE ' . $config['table_prefix'] . "usersfiles.userdb_id = $media_parent_id AND userdb_active = 'yes'  
							ORDER BY usersfiles_rank
							$LIMITstr";
                }

                $recordSet = $conn->Execute($sql);
                if (!$recordSet) {
                    $error = $conn->ErrorMsg();
                    $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->read', 'log_message' => 'DB Error: ' . $error . ' Full SQL: ' . $sql]);
                    return ['error' => true, 'error_msg' => 'DB Error: ' . $error . "\r\n" . 'SQL: ' . $sql];
                }
                $media_count = $recordSet->RecordCount();
                while (!$recordSet->EOF) {
                    $this_media = [];
                    $this_media['media_id'] = $recordSet->fields('usersfiles_id');
                    $this_media['media_rank'] = $recordSet->fields('usersfiles_rank');
                    $this_media['caption'] = $recordSet->fields('usersfiles_caption');
                    $this_media['description'] = $recordSet->fields('usersfiles_description');
                    $this_media['file_name'] = $recordSet->fields('usersfiles_file_name');
                    $file_id = $recordSet->fields('usersfiles_id');

                    //Deal with Remote files
                    if (strpos($this_media['file_name'], 'http://') === 0 || strpos($this_media['file_name'], 'https://') === 0 || strpos($this_media['file_name'], '//') === 0) {
                        if ($media_output == 'URL') {
                            $this_media['file_src'] = $recordSet->fields('usersfiles_file_name');
                        }
                    }
                    if ($media_output == 'URL') {
                        $this_media['file_src'] = $config['users_view_file_path'] . '/' . $media_parent_id . '/' . $this_media['file_name'];
                        if (file_exists($this_media['file_src'])) {
                            $this_media['file_size'] = filesize($this_media['file_src']);
                        }
                    } else {
                        $this_media['file_src'] = file_get_contents($config['users_file_upload_path'] . '/' . $this_media['file_name']);
                    }
                    $media_object[] = $this_media;
                    $recordSet->MoveNext();
                } // end while
                break;
        }
        return ['error' => false, 'media_object' => $media_object, 'media_count' => $media_count];
    }

    public function delete($data)
    {
        global $conn, $config, $lapi, $lang, $misc;

        extract($data, EXTR_SKIP || EXTR_REFS, '');
        $media_output_types = ['DATA', 'URL'];
        include_once $config['basepath'] . '/include/login.inc.php';
        $login = new login();
        $agent_status = $login->verify_priv('Agent');

        //Check that required settings were passed
        if (!isset($media_type) || !in_array($media_type, $this->media_types)) {
            return ['error' => true, 'error_msg' => 'correct_parameter_not_passed'];
        }
        if (!isset($media_parent_id) || !is_numeric($media_parent_id)) {
            return ['error' => true, 'error_msg' => 'media_parent_id: correct_parameter_not_passed'];
        }
        if (!isset($media_object_id) || (!is_numeric($media_object_id) && $media_object_id != '*')) {
            return ['error' => true, 'error_msg' => 'media_object_id: correct_parameter_not_passed'];
        }
        if (isset($or_int_disable_log) && !is_bool($or_int_disable_log)) {
            return ['error' => true, 'error_msg' => 'or_int_disable_log: correct_parameter_not_passed'];
        }
        $owner = $this->media_permission_check(0, $media_type, $media_parent_id);
        if ($owner != false) {
            switch ($media_type) {
                case 'listingsimages':
                    $has_thumb = true;
                    $folderpresent = false;
                    $path = $config['listings_upload_path'];
                    $foriegnkey = 'listingsdb_id';
                    break;
                case 'userimages':
                    $has_thumb = true;
                    $folderpresent = false;
                    $path = $config['user_upload_path'];
                    $foriegnkey = 'userdb_id';
                    break;
                case 'listingsvtours':
                    $has_thumb = true;
                    $folderpresent = false;
                    $path = $config['vtour_upload_path'];
                    $foriegnkey = 'listingsdb_id';
                    break;
                case 'listingsfiles':
                    $has_thumb = false;
                    $path = $config['listings_file_upload_path'] . '/' . $media_parent_id;
                    $foriegnkey = 'listingsdb_id';
                    break;
                case 'usersfiles':
                    $has_thumb = false;
                    $path = $config['users_file_upload_path'] . '/' . $media_parent_id;
                    $foriegnkey = 'userdb_id';
                    break;
            }
            if ($has_thumb) {
                if ($media_object_id == '*') {
                    $sql = 'SELECT ' . $media_type . '_file_name, ' . $media_type . '_thumb_file_name 
							FROM ' . $config['table_prefix'] . $media_type . " 
							WHERE $foriegnkey = $media_parent_id";
                } else {
                    $sql = 'SELECT ' . $media_type . '_file_name, ' . $media_type . '_thumb_file_name 
							FROM ' . $config['table_prefix'] . $media_type . " 
							WHERE $foriegnkey = $media_parent_id 
							AND " . $media_type . "_id = $media_object_id";
                }
            } else {
                if ($media_object_id == '*') {
                    $sql = 'SELECT ' . $media_type . '_file_name 
							FROM ' . $config['table_prefix'] . $media_type . " 
							WHERE $foriegnkey = $media_parent_id";
                    //we just deleted everything, so no need to keep the associated /media_parent_id/ folder.
                    $folderpresent = true;
                } else {
                    $sql = 'SELECT ' . $media_type . '_file_name 
							FROM ' . $config['table_prefix'] . $media_type . " 
							WHERE $foriegnkey = $media_parent_id AND " . $media_type . "_id = $media_object_id";
                }
            }

            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $mediainfo = [];
            //setup the vars we need to send to the hook
            $mediainfo['media_type'] = $media_type;
            $mediainfo['media_parent_id'] = $media_parent_id;

            while (!$recordSet->EOF) {
                $file_name = $recordSet->fields($media_type . '_file_name');
                if ($has_thumb) {
                    $thumb_file_name = $recordSet->fields($media_type . '_thumb_file_name');
                }

                $mediainfo['file_name'] = $file_name;

                //call the before_delete hook
                include_once $config['basepath'] . '/include/hooks.inc.php';
                $hooks = new hooks();
                $hooks->load('before_media_delete', $mediainfo);

                //Delete Full Photo
                if (strpos($file_name, 'https://') !== 0 && strpos($file_name, 'http://') !== 0 && strpos($file_name, '//') !== 0) {
                    try {
                        unlink($path . '/' . $file_name);
                    } catch (Exception $e) {
                        $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->delete', 'log_message' => 'missing file: ' . $file_name . ' ' . $e]);
                    }
                }

                // Delete Thumbnail
                if ($has_thumb && (strpos($thumb_file_name, 'http://') !== 0 && strpos($thumb_file_name, 'https://') !== 0 && strpos($thumb_file_name, '//') !== 0)) {
                    try {
                        if ($file_name != $thumb_file_name) {
                            unlink($path . '/' . $thumb_file_name);
                        }
                    } catch (Exception $e) {
                        $lapi->load_local_api('log__log_create_entry', ['log_type' => 'CRIT', 'log_api_command' => 'api->media->delete', 'log_message' => 'missing file: ' . $thumb_file_name . ' ' . $e]);
                    }
                }

                $recordSet->MoveNext();
            } // end while
            //nuke the /media_parent_id/ folder if there was one
            if ($folderpresent && $recordSet->RecordCount() > 0) {
                if (file_exists($path) === true) {
                    rmdir($path);
                }
            }

            // delete from the db
            if ($media_object_id == '*') {
                $sql = 'DELETE FROM ' . $config['table_prefix'] . $media_type . " 
						WHERE $foriegnkey = $media_parent_id";
            } else {
                $sql = 'DELETE FROM ' . $config['table_prefix'] . $media_type . " 
						WHERE $foriegnkey = $media_parent_id AND " . $media_type . "_id = $media_object_id";
            }
            $recordSet = $conn->Execute($sql);
            if (!$recordSet) {
                $misc->log_error($sql);
            }

            $admin_status = $login->verify_priv('Admin');
            if ($admin_status == false || !isset($or_int_disable_log) || $or_int_disable_log == false) {
                $lapi->load_local_api('log__log_create_entry', ['log_type' => 'INFO', 'log_api_command' => 'api->media->delete', 'log_message' => $lang['log_deleted_listing_image'] . ' ' . $media_type . ' - ' . $media_parent_id . ' - ' . $media_object_id]);
            }

            include_once $config['basepath'] . '/include/hooks.inc.php';
            $hooks = new hooks();
            $hooks->load('after_media_delete', $mediainfo);

            if ($media_object_id == '*') {
                $itemtype = 'ALL';
            } else {
                $itemtype = $media_object_id;
            }

            return [
                'error' => false,
                'status_msg' => 'Deleted ' . $itemtype,
                'media_parent_id' => $media_parent_id,
                'media_type' => $media_type,
            ];
        } else {
            return ['error' => true, 'error_msg' => 'permission denied: not media owner (API) - ' . $media_type . ' ' . $media_parent_id];
        }
    }
}
