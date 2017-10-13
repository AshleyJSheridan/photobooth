#!/usr/bin/env php
<?php
class filter
{
	const WIDTH = 800;
	const HEIGHT = 600;
	private $photo;

	public function __construct($img_path)
	{
		$this->photo = $this->load_image($img_path);
	}

	public function apply_filter($filter)
	{
		if(method_exists($this, $filter))
		{
			$filtered_image = $this->{$filter}();
			
			$photo_filename = date("ymdhis");
			imagejpeg($filtered_image, "images/$photo_filename.jpg");
		}
		else
			die("filter $filter does not exist");
	}

	private function pumpkins()
	{
		$base = $this->photo;
		$pumpkins = $this->load_image("filter_layers/pumpkins.png");
		
		imagecopyresampled($base, $pumpkins, 0, 0, 0, 0, $this::WIDTH, $this::HEIGHT, $this::WIDTH, $this::HEIGHT);

		return $base;
	}
	
	private function negate()
	{
		$base = $this->get_blank_base();
		
		imagecopyresampled($base, $this->photo, 0, 0, 0, 0, $this::WIDTH, $this::HEIGHT, $this::WIDTH, $this::HEIGHT);
		imagefilter($base, IMG_FILTER_NEGATE);
		
		return $base;
	}
	
	private function edge()
	{
		$base = $this->get_blank_base();
		
		imagecopyresampled($base, $this->photo, 0, 0, 0, 0, $this::WIDTH, $this::HEIGHT, $this::WIDTH, $this::HEIGHT);
		imagefilter($base, IMG_FILTER_EDGEDETECT);
		imagefilter($base, IMG_FILTER_CONTRAST, 10);
		imagefilter($base, IMG_FILTER_BRIGHTNESS, -50);
		
		return $base;
	}
	
	private function tv()
	{
		$base = $this->get_blank_base();
		$tv = $this->load_image("filter_layers/tv.png");
		$rotated  = imagerotate($this->photo, 3, 0);
		
		imagefilter($rotated, IMG_FILTER_GRAYSCALE);
		imagecopyresampled($base, $rotated, $this::WIDTH * 0.18 , $this::HEIGHT * 0.128, 0, 0, $this::WIDTH / 1.5, $this::HEIGHT / 1.5, $this::WIDTH, $this::HEIGHT);
		imagecopyresampled($base, $tv, 0, 0, 0, 0, $this::WIDTH, $this::HEIGHT, $this::WIDTH, $this::HEIGHT);
		
		return $base;
	}
	
	private function mirror()
	{
		$base = $this->get_blank_base();
		$mirror = $this->load_image("filter_layers/mirror.png");
		
		imagefilter($this->photo, IMG_FILTER_BRIGHTNESS, -100);
		imagecopyresampled($base, $this->photo, $this::WIDTH * 0.1 , $this::HEIGHT * 0.2, 0, 0, $this::WIDTH / 1.5, $this::HEIGHT / 1.5, $this::WIDTH, $this::HEIGHT);
		imagecopyresampled($base, $mirror, 0, 0, 0, 0, $this::WIDTH, $this::HEIGHT, $this::WIDTH, $this::HEIGHT);
		
		return $base;
	}
	
	private function pumpkin_mouth()
	{
		$base = $this->get_blank_base();
		$mouth = $this->load_image("filter_layers/pumpkin.png");
		
		imagefilter($this->photo, IMG_FILTER_BRIGHTNESS, -100);
		imagecopyresampled($base, $this->photo, $this::WIDTH * 0.17 , $this::HEIGHT * 0.3, 0, 0, $this::WIDTH / 1.5, $this::HEIGHT / 1.6, $this::WIDTH, $this::HEIGHT);
		imagecopyresampled($base, $mouth, 0, 0, 0, 0, $this::WIDTH, $this::HEIGHT, $this::WIDTH, $this::HEIGHT);
		
		return $base;
	}
	
	private function party()
	{
		$base = $this->photo;
		$party = $this->load_image("filter_layers/party.png");
		
		imagecopyresampled($base, $party, 0, 0, 0, 0, $this::WIDTH, $this::HEIGHT, $this::WIDTH, $this::HEIGHT);
		
		return $base;
	}
	
	private function autumn()
	{
		$base = $this->photo;
		$autumn = $this->load_image("filter_layers/autumn.png");
		
		imagecopyresampled($base, $autumn, 0, 0, 0, 0, $this::WIDTH, $this::HEIGHT, $this::WIDTH, $this::HEIGHT);
		
		return $base;
	}
	
	private function sepia()
	{
		$base = $this->photo;
		
		imagefilter($base, IMG_FILTER_GRAYSCALE);
		imagefilter($base, IMG_FILTER_BRIGHTNESS, -30);
		imagefilter($base, IMG_FILTER_COLORIZE, 90, 60, 40);
		
		$base = $this->vignette($base);
		imagefilter($base, IMG_FILTER_GAUSSIAN_BLUR);
		
		return $base;
	}
	
	
	private function vignette($img)
	{
		$sharp = 0.4; // 0 - 10 smaller is sharper,
		$level = 0.7; // 0 - 1 smaller is brighter
		$skip_pixels = 2;
		
		for($x = 0; $x < $this::WIDTH; $x += $skip_pixels)
		{
			for($y = 0; $y < $this::HEIGHT; $y += $skip_pixels)
			{
				$rgb = imagecolorat($this->photo, $x, $y);

				$red = (($rgb >> 16) & 0xFF);
				$green = (($rgb >> 8) & 0xFF);
				$blue = ($rgb & 0xFF);

				/*$l = sin(M_PI / $this::WIDTH * $x) * sin(M_PI / $this::HEIGHT * $y);
				$l = pow($l, $sharp);
				$l = 1 - $level * (1 - $l);*/
				$l = $this->get_vignette_colour_modifier($x, $y, $sharp, $level);

				$red *= $l;
				$green *= $l;
				$blue *= $l;
				
				$color = imagecolorallocate($img, $red, $green, $blue);

				imagesetpixel($img, $x, $y, $color);
			}
		}
		return $img;
	}
	
	private function get_vignette_colour_modifier($x, $y, $sharpness, $brightness)
	{
		$l = sin(M_PI / $this::WIDTH * $x) * sin(M_PI / $this::HEIGHT * $y);
		$l = pow($l, $sharpness);
		$l = 1 - $brightness * (1 - $l);
		
		return $l;
	}
	
	private function oil()
	{
		$strength = 3;
		$diff = 10;
		$brushsize = 7;
		$brushdiff = 3;
	
		for ($x = 0; $x < $this::WIDTH; $x += $strength)
		{
			for ($y = 0; $y < $this::HEIGHT; $y += $strength)
			{
				if(rand(0, $strength) < 2)
				{
					$rgb = imagecolorat($this->photo, $x, $y);
					$modifier = $this->get_rand($diff);

					$red = (($rgb >> 16) & 0xFF) + $modifier;
					$green = (($rgb >> 8) & 0xFF) + $modifier;
					$blue = ($rgb & 0xFF) + $modifier;

					$red = $this->constrain_colour_value($red);
					$green = $this->constrain_colour_value($green);
					$blue = $this->constrain_colour_value($blue);
					
					$brushdiffx = $this->get_rand($brushdiff);
					$brushdiffy = $this->get_rand($brushdiff);
					
					$colour = imagecolorallocate($this->photo, $red, $green, $blue);
					imagefilledellipse($this->photo, $x + $brushdiffx, $y + $brushdiffy, $brushsize, $brushsize, $colour);
				}
			}
		}
		
		return $this->photo;
	}
	
	private function load_image($filename)
	{
		if(file_exists($filename))
		{
			$extension = substr($filename, -3);

			switch($extension)
			{
				case 'png':
					$image = imagecreatefrompng($filename);
					break;
				case 'gif':
					$image = imagecreatefromgif($filename);
					break;
				default:
					$image = imagecreatefromjpeg($filename);
					break;
			}

			return $image;
		}
		else
			die("file $filename doesn't exist");
	}
	
	private function get_rand($range)
	{
		return rand($range * -1, $range);
	}
	
	private function get_blank_base()
	{
		return imagecreatetruecolor($this::WIDTH, $this::HEIGHT);
	}
	
	private function constrain_colour_value($colour, $low = 0, $high = 255)
	{
		if($colour < $low)
			$colour = $low;

		if($colour > $high)
			$colour = $high;

		return $colour;
	}
}

$filter = new filter($argv[1]);
$filter->apply_filter('edge');


