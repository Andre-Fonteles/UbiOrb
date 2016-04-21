<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * @ORM\Entity
 * @UniqueEntity(fields="email", message="already.registered.email")
 */
class User implements AdvancedUserInterface, \Serializable
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Length(max = 255)
     * @Assert\NotBlank(message = "not.blank.email", groups={"Default", "recover-password"})
     * @Assert\Email(message = "invalid.email", groups={"Default", "recover-password"})
     * @AppAssert\EmailExists(message = "notFound.email", groups={"recover-password"})
     */
    protected $email;
	
    /**
    * @ORM\Column(type="string", length=255)
    * @Assert\NotBlank(message = "not.blank.password", groups={"Default", "reset-password"})
    * @Assert\Regex(
     *	pattern="/[0-9]/",
     *	match=true,
     *	message="atLeast.digit.password",
     *	groups={"Default", "reset-password"}
     * )
     * @Assert\Regex(
     *	pattern="/[A-z]|[a-z]/",
     *	match=true,
     *	message="atLeast.letter.password",
     *	groups={"Default", "reset-password"}
     * )
    * @Assert\Length(
    * 	max = 255, 
    * 	min = 6,
    * 	minMessage="min.length.password",
    *	groups={"Default", "reset-password"}
    * )
    */
    protected $password;
    
    /**
     * @ORM\Column(type="string", length=60)
     * @Assert\Length(max = 60)
     * @Assert\NotBlank(message = "not.blank.firstName")
     */
    protected $firstName;
    
    /**
     * @ORM\Column(type="string", length=60)
     * @Assert\Length(max = 60)
     * @Assert\NotBlank(message = "not.blank.lastName")
     */
    protected $lastName;
        
    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    protected $confirmedEmail = false;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $active = false;
    
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
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set active
     *
     * @param string $active
     * @return User
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }
    
    /**
     * Set confirmedEmail
     *
     * @param boolean $confirmedEmail
     * @return User
     */
    public function setConfirmedEmail($confirmedEmail)
    {
    	$this->confirmedEmail = $confirmedEmail;
    
    	return $this;
    }
    
    /**
     * Get confirmedEmail
     *
     * @return string
     */
    public function getConfirmedEmail()
    {
    	return $this->confirmedEmail;
    }
    
    /** @see \Serializable::serialize() */
    public function serialize()
    {
    	return serialize(array(
    			$this->id,
    			$this->email,
    			$this->password,
    	));
    }
    
    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
    	list (
    			$this->id,
    			$this->email,
    			$this->password,
    	) = unserialize($serialized);
    }
    
    public function getSalt()
    {
    	return null;
    }
    
    public function getRoles()
    {
    	return array('ROLE_USER');
    }
    
    public function eraseCredentials()
    {
    }
    
    public function getUsername()
    {
    	return $this->email;
    }
    
    public function isAccountNonExpired()
    {
    	return true;
    }
    
    public function isAccountNonLocked()
    {
    	return $this->active;
    }
    
    public function isCredentialsNonExpired()
    {
    	return true;
    }
    
    public function isEnabled()
    {
    	return $this->active;
    }
}
