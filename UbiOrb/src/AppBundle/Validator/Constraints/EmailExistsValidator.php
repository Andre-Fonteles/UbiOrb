<?php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;

class EmailExistsValidator extends ConstraintValidator
{
	protected $em;
	
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}
	
	public function validate($value, Constraint $constraint)
	{
		$user = $this->em->getRepository('AppBundle:User')
		->findOneBy(array('email' => $value));
		
		if(!$user) {
			$this->context->addViolation(
					$constraint->message,
					array('%string%' => $value)
			);
		}
	}
	
	
}

