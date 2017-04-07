<?php
	class Watermark
	{
		public function Watermark()
		{
			
		}
		
		public function do_watermark($src_image, $watermark_image, $padding_bottom = 10, $padding_right = 10, $out_file = false)
		{
			$watermark = imagecreatefrompng($watermark_image);
			//$watermark = imagecreatefromgif($watermark_image);
			$watermark_width = imagesx($watermark);
			$watermark_height = imagesy($watermark);
			$image = imagecreatetruecolor($watermark_width, $watermark_height);
			$image = imagecreatefromjpeg($src_image);
			$size = getimagesize($src_image);
			
//			$dest_x = $size[0] - $watermark_width - $padding_right;
//			$dest_y = $size[1] - $watermark_height - $padding_bottom;
                        // adding watermark at the center of image
                        $dest_x = ($size[0] / 2) - ($watermark_width / 2);
			$dest_y = ($size[1] / 2) - ($watermark_height / 2);
			
			//imagecopymerge($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $opacity);
			imagecopy($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height);
			//imagecopyresampled($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $size[0], $size[1]);
			
			if($out_file === false)
			{
				header('Content-type: image/jpeg');
				imagejpeg($image);
			}
			else
			{
				imagejpeg($image, $out_file, 90);
			}
			
			imagedestroy($image);
			imagedestroy($watermark);
		}
	}
	
?>
