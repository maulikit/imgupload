<?php

include_once('SimpleImage.php');
include_once('class.watermark.php');

require_once 'sdk/sdk.class.php';
require_once 'sdk/services/s3.class.php';

class uploadImage {

    public function validateImage($Files, $itemid) {

        $allowedExts = array(
            "jpeg",
            "jpg"
        );
        $temp = explode(".", $Files["ImageFile" . $itemid]["name"]);
        $retVal = array(
            "Error Code" => array(),
            "Error Msg" => array()
        );
        $extension = end($temp);
        if ((($Files["ImageFile" . $itemid]["type"] == "image/gif") || ($Files["ImageFile" . $itemid]["type"] == "image/jpeg") || ($Files["ImageFile" . $itemid]["type"] == "image/jpg") || ($Files["ImageFile" . $itemid]["type"] == "image/png")) && ($Files["ImageFile" . $itemid]["size"] < 524288) && in_array($extension, $allowedExts)) {
            if ($Files["file"]["error"] > 0) {
                $retVal['Error Code'] = 1;
                $retVal['Error Msg'] = $Files["ImageFile" . $itemid]["error"];
                return $retVal;
            } else {
                if (file_exists("upload/" . $Files["file"]["name"])) {
                    $retVal['Error Code'] = 1;
                    $retVal['Error Msg'] = $Files["ImageFile" . $itemid]["name"] . " already exists";
                    return $retVal;
                } else {
                    $retVal['Error Code'] = 0;
                    $retVal['Error Msg'] = " Valid File";
                    return $retVal;
                }
            }
        } else {
            $retVal['Error Code'] = 1;
            $retVal['Error Msg'] = $Files["ImageFile" . $itemid]["name"] . " is an Invalid File";
            return $retVal;
        }
    }

    public function generateRandomString($length = 5) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public function doUploadImages($itemid, $photosURL, $photoDet, $rdmString, $groceryOrPharmacy) {



        $upload_date = date("Y-m-d H:i:s");
        $temp_dir = "photo/" . $itemid . "/";
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0777);
        }

        $photo_url_base = "";
        $ftp_base = "";
        $ftp_dir = "";
        $catalogue_dir = $groceryOrPharmacy . "/" . trim($itemid) . "/";
        $photosURLArr = explode(",", $photosURL);

        //$imgOrderArr    = explode(",",$photoDet['imageOrder']);

        foreach ($photosURLArr as $url) {
            $upload_file_name_path = $url;

            //validate corrupted images 
            $resizer = new SimpleImage();
            //$img_true   =   $resizer->validate_image($upload_file_name_path);

            $img_true = 1;
            if ($img_true == 1) {
                //image not corrupted
                $file_info = getimagesize($upload_file_name_path);
                $original_width = $file_info[0];
                $original_height = $file_info[1];

//                if ($file_info && ($original_width >= 0 && $original_height >= 0)) {commenting as req by prajwal
                if ($file_info && ($original_width >= 0 && $original_height >= 0)) {
                    $file_name = $itemid . '_' . $rdmString;
                    $thumb_big = $file_name . ".jpg";
                    $thumb_small = $file_name . "_t" . ".jpg";
                    $thumb_result = $file_name . "_r" . ".jpg";
                    $without_watermark = $file_name . "_w" . ".jpg";
                    $mobile_img = $file_name . "_m" . ".jpg";
                    $auto_img = $file_name . "_a" . ".jpg";
                    $cart_img = $file_name . "_c" . ".jpg";
                    $popup_img = $file_name . "_p" . ".jpg";

                    $watermark = new Watermark();
                    $resizer->load($upload_file_name_path);
                    // 1) Original Image
                    $resizer->load($upload_file_name_path);
                    $resizer->save($temp_dir . $without_watermark);
                    // 2) Zoomed image with watermark
                    if ($original_width >= 720 && $original_height >= 800) {
                        $resizer->load($upload_file_name_path);
                        $resizer->resize(720, 800, $original_width, $original_height);
                        $resizer->save($temp_dir . $thumb_big);
                        $watermark->do_watermark($temp_dir . $thumb_big, "images/water_mark.png", $padding_bottom = 10, $padding_right = 10, $temp_dir . $thumb_big);
                    }
                    // 3) Thumbnail image 
                    $resizer->load($upload_file_name_path);
                    $resizer->resize(180, 200, $original_width, $original_height);
                    $resizer->save($temp_dir . $thumb_small);
                    // 4) Result page image
                    $resizer->load($upload_file_name_path);
                    $resizer->resize(180, 140, $original_width, $original_height, 1);
                    $resizer->save($temp_dir . $thumb_result);
                    // 5) Mobile image
                    $resizer->load($upload_file_name_path);
                    $resizer->resize(108, 120, $original_width, $original_height);
                    $resizer->save($temp_dir . $mobile_img);
                    // 6) Autosuggest image
                    $resizer->load($upload_file_name_path);
                    $resizer->resize(36, 40, $original_width, $original_height);
                    $resizer->save($temp_dir . $auto_img);
                    // 7) Cart image
                    $resizer->load($upload_file_name_path);
                    $resizer->resize(54, 60, $original_width, $original_height);
                    $resizer->save($temp_dir . $cart_img);
                    // 8) popup Image
                    if ($original_height < 450 && $original_width < 450) { // save popup image without resizing
                        $resizer->load($upload_file_name_path);
                        $resizer->save($temp_dir . $popup_img);
                    } else { // resize popup image and then save                                                                        
                        $resizer->load($upload_file_name_path);
                        $resizer->resize(450, 450, $original_width, $original_height);
                        $resizer->save($temp_dir . $popup_img);
                    }                    


                    $s3 = new AmazonS3();
                    $catalogue_url = 'http://img.jdmagicbox.com/';


                    if ($original_width >= 720 && $original_height >= 800) {
                        $s3->batch()->create_object('imgs77710', $catalogue_dir . $thumb_big, array(
                            'fileUpload' => $temp_dir . $thumb_big
                        ));
                    }
                    $s3->batch()->create_object('imgs77710', $catalogue_dir . $mobile_img, array(
                        'fileUpload' => $temp_dir . $mobile_img
                    ));
                    $s3->batch()->create_object('imgs77710', $catalogue_dir . $thumb_result, array(
                        'fileUpload' => $temp_dir . $thumb_result
                    ));
                    $s3->batch()->create_object('imgs77710', $catalogue_dir . $thumb_small, array(
                        'fileUpload' => $temp_dir . $thumb_small
                    ));
                    $s3->batch()->create_object('imgs77710', $catalogue_dir . $without_watermark, array(
                        'fileUpload' => $temp_dir . $without_watermark
                    ));
                    $s3->batch()->create_object('imgs77710', $catalogue_dir . $auto_img, array(
                        'fileUpload' => $temp_dir . $auto_img
                    ));
                    $s3->batch()->create_object('imgs77710', $catalogue_dir . $cart_img, array(
                        'fileUpload' => $temp_dir . $cart_img
                    ));
                    $s3->batch()->create_object('imgs77710', $catalogue_dir . $popup_img, array(
                        'fileUpload' => $temp_dir . $popup_img
                    ));                    
                    $file_upload_response = $s3->batch()->send();

                    //$big_image_url   = $catalogue_url . $catalogue_dir . $thumb_big;
                    echo $small_image_url = $catalogue_url . $catalogue_dir . $thumb_small;
                    //$without_watermark_url = $catalogue_url . $catalogue_dir . $without_watermark;

                    $retval = array();
                    $retval['error_code'] = 0;
                    $retval['display_image'] = $thumb_big;
                    $retval['thumbnail_image'] = $thumb_small;
                    $retval['without_watermark_url'] = $without_watermark;


                    unlink($url);
                    unlink($temp_dir . $thumb_small);
                    if ($original_width >= 720 && $original_height >= 800) {
                        unlink($temp_dir . $thumb_big);
                    }
                    unlink($temp_dir . $mobile_img);
                    unlink($temp_dir . $thumb_result);
                    unlink($temp_dir . $without_watermark);
                    unlink($temp_dir . $auto_img);
                    unlink($temp_dir . $cart_img);
                    unlink($temp_dir . $popup_img);
                    rmdir($temp_dir);
                    return $retval;
                }
            } else {
                //update image is corrupt/truncated
                echo "image is corrupted hence upload failed";
                $retval = array();
                $retval['error_code'] = 1;
                return $retval;
            }
        }
    }

}

//}
?>
