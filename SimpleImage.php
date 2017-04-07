<?php
error_reporting(0);
class SimpleImage
{
    var $image;
    var $image_type;
    
    function load($filename)
    {
        $image_info       = getimagesize($filename);
        $this->image_type = $image_info[2];
        if ($this->image_type == IMAGETYPE_JPEG) {
            $this->image = imagecreatefromjpeg($filename);
        } elseif ($this->image_type == IMAGETYPE_GIF) {
            $this->image = imagecreatefromgif($filename);
        } elseif ($this->image_type == IMAGETYPE_PNG) {
            $img         = imagecreatefrompng($filename);
            $this->image = imagejpeg($img, $filename, 100);
            
        }
    }
    function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 90, $permissions = null)
    //$compression=75
    {
        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image, $filename, $compression);
        } elseif ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image, $filename);
        } elseif ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image, $filename);
        }
        if ($permissions != null) {
            chmod($filename, $permissions);
        }
    }
    function output($image_type = IMAGETYPE_JPEG)
    {
        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image);
        } elseif ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image);
        } elseif ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image);
        }
    }
    function getWidth()
    {
        return imagesx($this->image);
    }
    function getHeight()
    {
        return imagesy($this->image);
    }
    function resizeToHeight($height)
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }
    function resizeToWidth($width)
    {
        $ratio  = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width, $height);
    }
    function scale($scale)
    {
        $width  = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100;
        $this->resize($width, $height);
    }
    function resize($width, $height, $original_width, $original_height, $change_ratio = '')
    {
        if (!$change_ratio == 1) {
            $new_height = ceil(($original_height / $original_width) * $width);
            
            if ($new_height >= $height) {
                
                $new_height   =   $height;
                $new_width  =   ceil(($original_width/$original_height)*$height);
		if($new_width>=$width){
			$new_width	=	$width;
		}else{
			$new_width	=	$new_width;
		}

            }else{
                
                $new_width  =   $width;

            }    
}else{
         
            $new_height  =   ceil(($original_height/$original_width)*$width);
       
            if($new_height>=$height){
                
                $new_height   =   $height;
                $new_width  =   ceil(($original_width/$original_height)*$height);

            }else{
                
                $new_width  =   $width;

            }     
}

      $new_image = imagecreatetruecolor($new_width, $new_height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $new_width, $new_height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;   
   }  
   
   function validate_image($filename){
       //echo $filename;echo "---inside--";
       $re  ='';
       $jpg_extn_array =   array("jpg","jpeg","JPG","JPEG");
       $png_extn_array =   array("png","PNG");
       
       
       $pathinfo = pathinfo($filename);
       $img_extn  =   $pathinfo['extension'];
       
       
       if(in_array($img_extn,$jpg_extn_array)){
           
           //check jpg corruption
            $re =   imagecreatefromjpeg($filename);
            if($re){
                //image not corrupted
                return 1;
            }else{
                //image corrupted
                return 0;
            }           
          
       }
       else if(in_array($img_extn,$png_extn_array)){
           //check jpg corruption
           
            $re =   imagecreatefrompng($filename);
            if($re){
                //image not corrupted
                return 1;
            }else{
                //image corrupted
                return 0;
            }                                         
       }
       else{
           //corrupt or not of jpg,png type
           return 0;
       }
       
   }


}
?>
