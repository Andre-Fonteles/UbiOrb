<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ResetPassCodeRep extends EntityRepository
{
	public function deleteAllByUserId($user) {
		$this->getEntityManager()->createQueryBuilder()
		->delete('AppBundle:ResetPassCode', 'c')
		->where('c.user = :user_id')
		->setParameter('user_id', $user->getId())
		->getQuery()
		->execute();
	}
}