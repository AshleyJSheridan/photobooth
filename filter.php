#!/usr/bin/env php
<?php
class filter
{
	const WIDTH = 800;
	const HEIGHT = 600;
	private $photo;

	public function __construct($img_path)
	{
		$this->load_image($img_path);
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
			echo "filter $filter does not exist";
	}
	
	private function load_image($img_path)
	{
		if(file_exists($img_path))
		{
			$this->photo = imagecreatefromjpeg($img_path);
		}
		else
		{
			echo "file $img_path doesn't exist";
		}
	}
	
	private function pumpkins()
	{
		$base = imagecreatetruecolor($this::WIDTH, $this::HEIGHT);
		$pumpkins = imagecreatefrompng("filter_layers/pumpkins.png");
		
		imagecopyresampled($base, $this->photo, 0, 0, 0, 0, $this::WIDTH, $this::HEIGHT, $this::WIDTH, $this::HEIGHT);
		imagecopyresampled($base, $pumpkins, 0, 0, 0, 0, $this::WIDTH, $this::HEIGHT, $this::WIDTH, $this::HEIGHT);

		return $base;
	}
	
	private function negate()
	{
		$base = imagecreatetruecolor($this::WIDTH, $this::HEIGHT);
		
		imagecopyresampled($base, $this->photo, 0, 0, 0, 0, $this::WIDTH, $this::HEIGHT, $this::WIDTH, $this::HEIGHT);
		imagefilter($base, IMG_FILTER_NEGATE);
		
		return $base;
	}
	
	private function tv()
	{
		$base = imagecreatetruecolor($this::WIDTH, $this::HEIGHT);
		$tv = imagecreatefrompng("filter_layers/tv.png");
		$rotated  = imagerotate($this->photo, 3, 0);
		
		imagefilter($rotated, IMG_FILTER_GRAYSCALE);
		imagecopyresampled($base, $rotated, $this::WIDTH * 0.18 , $this::HEIGHT * 0.128, 0, 0, $this::WIDTH / 1.5, $this::HEIGHT / 1.5, $this::WIDTH, $this::HEIGHT);
		imagecopyresampled($base, $tv, 0, 0, 0, 0, $this::WIDTH, $this::HEIGHT, $this::WIDTH, $this::HEIGHT);
		
		return $base;
	}
	
	private function mirror()
	{
		$base = imagecreatetruecolor($this::WIDTH, $this::HEIGHT);
		$mirror = imagecreatefrompng("filter_layers/mirror.png");
		
		imagefilter($this->photo, IMG_FILTER_BRIGHTNESS, -100);
		imagecopyresampled($base, $this->photo, $this::WIDTH * 0.1 , $this::HEIGHT * 0.2, 0, 0, $this::WIDTH / 1.5, $this::HEIGHT / 1.5, $this::WIDTH, $this::HEIGHT);
		imagecopyresampled($base, $mirror, 0, 0, 0, 0, $this::WIDTH, $this::HEIGHT, $this::WIDTH, $this::HEIGHT);
		
		return $base;
	}
}

$filter = new filter($argv[1]);
$filter->apply_filter('mirror');


