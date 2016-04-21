<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\AppBundle;

class NewsRep extends EntityRepository
{
	public function createPublishedNews($news) {
		$em = $this->getEntityManager();

		// Persist a new Figure if it has been uploaded
		if($news->getFigure()) {
			$em->persist($news->getFigure());
		}
		
		$em->persist($news);
		$em->flush();
	}
	
	public function updateNews($news) {
		// Set last update date
		$news->setUpdateDate();
	
		$em = $this->getEntityManager();
		$em->persist($news);
		$em->flush();
	}
	
	public function deleteNews($news) {
		$em = $this->getEntityManager();

		$em->remove($news);
		// Delete a new Figure if it exists
		if($news->getFigure()) {
			$em->remove($news->getFigure());
		}
		
		$em->flush();
	}
	
	public function deleteNewsByNewspaper($newspaperId) {
		$qb = $this->getEntityManager()->createQuery(
				"DELETE AppBundle:News n " . 
				" WHERE n.newspaper = :newspaperId "
				)->setParameters(array('newspaperId' => $newspaperId));
		$qb->execute();
	}
	
	public function countNewsByNewspaper($newspaperId) {
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('count(news)');
		$qb->from('AppBundle:News','news');
		$qb->where('news.newspaper = :newspaperId');
		$qb->setParameters(array('newspaperId' => $newspaperId));
		$count = $qb->getQuery()->getSingleScalarResult();
		return $count;
	}
	
	public function countNewsByUser($userId) {
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('count(news)');
		$qb->from('AppBundle:News','news');
		$qb->where('news.author = :userId');
		$qb->setParameters(array('userId' => $userId));
		$count = $qb->getQuery()->getSingleScalarResult();
		return $count;
	}
	
	public function getNewsByNewspaperAndCategory($newspaperId, $categoryName, $headline, $offset, $limit) {
		$qb = $this->getEntityManager()->createQuery(
				"SELECT n FROM AppBundle:News n " . 
				" JOIN n.categories c WHERE n.newspaper = :newspaperId AND n.headline = :headline" .
				" AND c.id IN (SELECT ca.id FROM AppBundle:Category ca WHERE ca.name = :categoryName) " .
				" ORDER BY n.publishDate DESC "
				)->setParameters(array(
						'newspaperId' => $newspaperId,
						'categoryName' => $categoryName,
						'headline' => $headline,
				))
				->setMaxResults($limit)
       			->setFirstResult($offset);
				
		return $qb->getResult();
	}
}