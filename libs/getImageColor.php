<?php
/**
 * Class for  Generator Image Color Palette.  
 * This file is class for return  mamimum used colors in image, format return in HTML or RGB format. 
 *
 * PHP versions 4 and 5
 * Licensed under The GPL License
 *
 * Developed By 	Kalpeh Gamit
 * E-mail 			Kalpa.programmer@gmail.com
 *
 * @filesource
 * @copyright     Copyright 2009, InScripter Inc. (http://www.inscripter.com)
 * @link          http://www.inscripter.com
 * @package       PHP
 * @version       GetImageColor-1.0.0
 * @license       http://www.opensource.org/licenses/gpl-2.0.php The GPL License
 */
 
// start of class : Generator Image Color Palette 
class GeneratorImageColorPalette 
{
	// get image color in RGB format function 
	function getImageColor($imageFile_URL, $numColors, $image_granularity = 5)
	{
   		$image_granularity = max(1, abs((int)$image_granularity));
   		$colors = array();
   		//find image size
   		$size = @getimagesize($imageFile_URL);
   		if($size === false)
   		{
      		user_error("Unable to get image size data");
      		return false;
   		}
   		// open image
   		$img = @imagecreatefromjpeg($imageFile_URL);
   		if(!$img)
   		{
   	  		user_error("Unable to open image file");
   		   return false;
   		}
   		
   		// fetch color in RGB format
   		for($x = 0; $x < $size[0]; $x += $image_granularity)
   		{
      		for($y = 0; $y < $size[1]; $y += $image_granularity)
      		{
         		$thisColor = imagecolorat($img, $x, $y);
         		$rgb = imagecolorsforindex($img, $thisColor);
        		$red = round(round(($rgb['red'] / 0x33)) * 0x33);
         		$green = round(round(($rgb['green'] / 0x33)) * 0x33);
         		$blue = round(round(($rgb['blue'] / 0x33)) * 0x33);
         		$thisRGB = sprintf('%02X%02X%02X', $red, $green, $blue);
         		if(array_key_exists($thisRGB, $colors))
         		{
           			 $colors[$thisRGB]++;
         		}
         		else
         		{
           			 $colors[$thisRGB] = 1;
         		}
      		}
   		}
   		arsort($colors);
   		// returns maximum used color of image format like #C0C0C0.
   		//return array_slice(array_keys($colors), 0, $numColors);
      return array_slice($colors, 0, $numColors, true);
	}

	// html color to convert in RGB format color like R(255) G(255) B(255)  
	function getHtml2Rgb($str_color)
	{
    	if ($str_color[0] == '#')
        	$str_color = substr($str_color, 1);

  	  	if (strlen($str_color) == 6)
        	list($r, $g, $b) = array($str_color[0].$str_color[1],
                                 $str_color[2].$str_color[3],
                                 $str_color[4].$str_color[5]);
    	elseif (strlen($str_color) == 3)
        	list($r, $g, $b) = array($str_color[0].$str_color[0], $str_color[1].$str_color[1], $str_color[2].$str_color[2]);
    	else
        	return false;

    	$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
    	$arr_rgb = array($r, $g, $b);
		// Return colors format liek R(255) G(255) B(255)  
    	return $arr_rgb;
	}

// end of class here : Generator Image Color Palette  
}
?>