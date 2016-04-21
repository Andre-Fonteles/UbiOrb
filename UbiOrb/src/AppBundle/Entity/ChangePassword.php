<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class ChangePassword
{
	/**
	 * @SecurityAssert\UserPassword(
	 *     message = "wrong.password"
	 * )
	 */
	protected $oldPassword;
	
	/**
	 * @Assert\NotBlank(message = "not.blank.password")
	 * @Assert\Regex(
     *	pattern="/[0-9]/",
     *	match=true,
     *	message="atLeast.digit.password"
     * )
     * @Assert\Regex(
     *	pattern="/[A-z]|[a-z]/",
     *	match=true,
     *	message="atLeast.letter.password"
     * )
    * @Assert\Length(
    * 	max = 255, 
    * 	min = 6,
    * 	minMessage="min.length.password"
    * )
    */
	protected $newPassword;
	
	/**
	 * Set oldPassword
	 *
	 * @param string $oldPassword
	 * @return User
	 */
	public function setOldPassword($oldPassword)
	{
		$this->oldPassword = $oldPassword;
	
		return $this;
	}
	
	/**
	 * Get oldPassword
	 *
	 * @return string
	 */
	public function getOldPassword()
	{
		return $this->oldPassword;
	}
	
	/**
	 * Set newPassword
	 *
	 * @param string $newPassword
	 * @return User
	 */
	public function setNewPassword($newPassword)
	{
		$this->newPassword = $newPassword;
	
		return $this;
	}
	
	/**
	 * Get newPassword
	 *
	 * @return string
	 */
	public function getNewPassword()
	{
		return $this->newPassword;
	}
}
