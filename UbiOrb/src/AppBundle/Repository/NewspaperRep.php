<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\AppBundle;

class NewspaperRep extends EntityRepository
{
	public function findByUserContributor($user) {
		$qb = $this->getEntityManager()->createQuery(
				"SELECT np FROM AppBundle:Newspaper np " .
				" JOIN np.contributors a WHERE a.user = :userId " .
				" ORDER BY np.id DESC "
		)->setParameters(array('userId' => $user->getId())
		);
	
		return $qb->getResult();
	}
	
}