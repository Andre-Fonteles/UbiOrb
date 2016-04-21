<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use AppBundle\Service\ThumbnailGenerator;

/**
 * @ORM\Entity
 */
class ContributorInvitation
{
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
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\Length(max = 255)
     * @Assert\NotBlank(message = "not.blank.associateInvitation.email", groups={"Default"})
     * @Assert\Email(message = "invalid.email", groups={"Default"})
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=300, nullable=true)
     * @Assert\Length(max = 300)
     */
    protected $customMessage;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="inviter_id", referencedColumnName="id", nullable=false)
     */
    protected $inviter;
    
    /**
     * @ORM\ManyToOne(targetEntity="Newspaper")
     * @ORM\JoinColumn(name="newspaper_id", referencedColumnName="id", nullable=false)
     */
    protected $newspaper;
    
	/**
	 * Set id
	 *
	 * @param string $id
	 * @return Associated
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
     * @return Associated
     */
    public function setRole($role)
    {
    	if(($role != Contributor::ROLE_ADMIN) && ($role != Contributor::ROLE_JOURNALIST)) {
    		throw new \Exception("Invalid role. Use Associated::ROLE_ADMIN or Associated::ROLE_JOURNALIST.");
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
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Set customMessage
     *
     * @param string $email
     * @return User
     */
    public function setCustomMessage($customMessage)
    {
    	$this->customMessage = $customMessage;
    
    	return $this;
    }
    
    /**
     * Get customMessage
     *
     * @return string
     */
    public function getCustomMessage()
    {
    	return $this->customMessage;
    }
    
    /**
     * Set inviter
     *
     * @param \AppBundle\Entity\User $inviter
     * @return ContributorInvitation
     */
    public function setInviter(\AppBundle\Entity\User $inviter) {
    	$this->inviter = $inviter;
    
    	return $this;
    }
    
    /**
     * Get inviter
     *
     * @return \AppBundle\Entity\User
     */
    public function getInviter() {
    	return $this->inviter;
    }
    
    /**
     * Set newspaper
     *
     * @param \AppBundle\Entity\Newspaper $newspaper
     * @return ContributorInvitation
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
