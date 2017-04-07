<?php

require_once '../config.php';
require_once '../module/de_grocery.class.php';
include_once('uploadImage.php');

$uploadImage    = new uploadImage();
$imgPath        = "";
$bid            = trim($_POST['m']);
$delImages      = $_POST['hdnDelImg_' . $_POST['currenttab']];
$uploadedImages = $_POST['hdnimagepath_' . $_POST['currenttab']];
$username       = $_POST['userID'];
$imgsizecode    = $_POST['itmsizecode_' . $_POST['currenttab']];
$item_id        = trim($_POST['hdnItemcode']);
$ipc            = trim($_POST['editItemIPC']);
$updateip       = trim($_POST['userIP']);
$hdnupldby      = trim($_POST['userID']);


/*switch($bid)
{
case 128: $groceryOrPharmacy    =   "grocery/dev";
break;
case 32768: $groceryOrPharmacy  =    "pharmacy/dev";
break;
case 16: $groceryOrPharmacy     =    "wine/dev";
break;
}

switch($bid)
{
case 128: $groceryOrPharmacy    =   "grocery";
break;
case 32768: $groceryOrPharmacy  =    "pharmacy";
break;
case 16: $groceryOrPharmacy     =    "wine";
break;
}*/
$groceryOrPharmacy = AMAZON_IMG_FOLDER;
$file              = $_FILES;

if ($file != '') {
    foreach ($file as $imageToUpload) {
        
        $rdmString   = $uploadImage->generateRandomString($length = 5);
        $photosURL   = $imageToUpload['tmp_name'];
        $photoDet    = array();
        $imgPathData = $uploadImage->doUploadImages($item_id, $photosURL, $photoDet, $rdmString, $groceryOrPharmacy);
        $img_valid   = $imgPathData['error_code'] == 0 ? 1 : 0;
        if ($img_valid == 1) {
            $updateImgPath .= $imgPathData['display_image'] . ",";
        } else {
            $error_ImgPath .= $imgPathData['display_image'] . ",";
        }
        
    }
} else {
    $updateImgPath = '';
}
$uploadedImages = $uploadedImages == '' ? rtrim($updateImgPath, ",") : $uploadedImages . "," . $updateImgPath;
$uploadedImages = rtrim($uploadedImages, ",");

//code to compare two arrays (deleted and existing image array)
if ($delImages != '') {
    $imgArray      = explode(",", $uploadedImages);
    $delArray      = explode(",", $delImages);
    $imgDiffArray  = array_diff($imgArray, $delArray);
    $imgDiff       = trim(implode(",", $imgDiffArray));
    $updateImgPath = rtrim($imgDiff, ",");
    
}
//code ends

else {
    $updateImgPath = rtrim($uploadedImages, ",");
}
$updateImgPath = rtrim($updateImgPath, ",");
//update image path to api
$gryobj   = new degroceryClass();
$response = $gryobj->updateImagePath($bid, $item_id, $updateImgPath, $username, $imgsizecode, $ipc, '');

json_encode($response);
?>
