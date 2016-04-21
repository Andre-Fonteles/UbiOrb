<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\Container;

class Slugfier
{
	/**
	 * Transforms a free text input by the user in a url friendly string
	 * If there is any non latin characters, it generates a dandom unic string
	 */
	function slugfy($str, $replace=array(), $delimiter='-') {
		// If ther are non western characters, then juts generates 
		if(preg_match('/[^\\p{Common}\\p{Latin}]/u', $str)) {
			$clean = sha1(uniqid(mt_rand(), true));
			return $clean; 
		}
		
		if( !empty($replace) ) {
			$str = str_replace((array)$replace, ' ', $str);
		}
	
		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
	
		return $clean;
	}
}
