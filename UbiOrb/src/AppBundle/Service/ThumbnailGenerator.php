<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\Container;

class ThumbnailGenerator
{
	public function generate($source_image_path, $thumbnail_image_path, $maxWidth, $maxHeight)
	{
		list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
		switch ($source_image_type) {
			case IMAGETYPE_GIF:
				$source_gd_image = imagecreatefromgif($source_image_path);
				break;
			case IMAGETYPE_JPEG:
				$source_gd_image = imagecreatefromjpeg($source_image_path);
				break;
			case IMAGETYPE_PNG:
				$source_gd_image = imagecreatefrompng($source_image_path);
				break;
		}
		if ($source_gd_image === false) {
			return false;
		}
		$source_aspect_ratio = $source_image_width / $source_image_height;
		$thumbnail_aspect_ratio = $maxWidth / $maxHeight;
		if ($source_image_width <= $maxWidth && $source_image_height <= $maxHeight) {
			$thumbnail_image_width = $source_image_width;
			$thumbnail_image_height = $source_image_height;
		} elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
			$thumbnail_image_width = (int) ($maxHeight * $source_aspect_ratio);
			$thumbnail_image_height = $maxHeight;
		} else {
			$thumbnail_image_width = $maxWidth;
			$thumbnail_image_height = (int) ($maxWidth / $source_aspect_ratio);
		}
		$thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
		imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
		imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
		imagedestroy($source_gd_image);
		imagedestroy($thumbnail_gd_image);
		return true;
	}
}
