<?php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EmailExists extends Constraint
{
	public $message = 'Sorry, but we couldn\'t find a user with that email.';
	
	public function validatedBy()
	{
		return 'email_exists';
	}
}