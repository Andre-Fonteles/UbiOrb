<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\Container;
use AppBundle\Entity\News;

class NewspaperTools
{
	/**
	 * Get a list of regular news and a list of headlines and balance it
	 * in order to display into a newspaper
	 */
	public function balanceNews(&$regularNews, &$headlines) {
		$rCount = count($regularNews);
		$hCount = count($headlines);
		
		if($rCount == 0 && $hCount == 0) {
			return;
		}
		
		if($rCount+1 > $hCount) {
			while ($rCount+1 > $hCount && $rCount != 1) {
				array_unshift($headlines, array_pop($regularNews));
				$rCount--;
				$hCount++;
			}
		} else {
			while ($hCount > $rCount+1) {
				array_push($regularNews, array_shift($headlines));
				$rCount++;
				$hCount--;
			}
		}
	}
}
