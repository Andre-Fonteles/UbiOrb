<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ResetPassCode
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ResetPassCodeRep")
 */
class ResetPassCode
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="User")
     **/
    protected $user;
    
    /**
     * @ORM\Column(type="string", unique=true, length=32)
     */
    protected $code;
    
    /**
     * Constructor that a new code automatically
     */
    public function __construct($user) {
    	$this->user = $user;
    	$this->code = md5(uniqid(rand(), true));
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
     * Set user
     *
     * @param User $user
     * @return ResetPassCode
     */
    public function setUser($user)
    {
    	$this->user = $user;
    
    	return $this;
    }
    
    /**
     * Get user
     *
     * @return user
     */
    public function getUser()
    {
    	return $this->user;
    }
    
    /**
     * Set code
     *
     * @param string $code
     * @return ResetPassCode
     */
    public function setCode($code)
    {
    	$this->code = $code;
    
    	return $this;
    }
    
    /**
     * Get code
     *
     * @return code
     */
    public function getCode()
    {
    	return $this->code;
    }
}
