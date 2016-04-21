<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use AppBundle\Service\ThumbnailGenerator;

/**
 * @ORM\Entity
 */
class Contributor
{
	const ROLE_ADMIN = 'admin';
	const ROLE_JOURNALIST = 'journalist';
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string", length=20, nullable=false)
	 */
	private $role;
	
	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
	 **/
	protected $user;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Newspaper", inversedBy="contributors")
	 * @ORM\JoinColumn(name="newspaper_id", referencedColumnName="id", nullable=false)
	 */
	protected $newspaper;
	
	/**
	 * Set id
	 *
	 * @param string $id
	 * @return Contributor
	 */
	public function setId($id)
	{
		$this->id = $id;
	
		return $this;
	}
	
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return Contributor
     */
    public function setRole($role)
    {
    	if(($role != $this::ROLE_ADMIN) && ($role != $this::ROLE_JOURNALIST)) {
    		throw new \Exception("Invalid role. Use Contributor::ROLE_ADMIN or Contributor::ROLE_JOURNALIST.");
    	}
    	
    	$this->role = $role;
    
    	return $this;
    }
    
    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
    	return $this->role;
    }
    
    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return User
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
    	$this->user = $user;
    
    	return $this;
    }
    
    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
    	return $this->user;
    }
    
    /**
    * Set newspaper
    *
    * @param \AppBundle\Entity\Newspaper $newspaper
    * @return Contributor
    */
    public function setNewspaper(\AppBundle\Entity\Newspaper $newspaper = null) {
    	$this->newspaper = $newspaper;
    
    	return $this;
    }
    
    /**
     * Get newspaper
     *
     * @return \AppBundle\Entity\Newspaper
     */
    public function getNewspaper() {
    	return $this->newspaper;
    }
    
}
