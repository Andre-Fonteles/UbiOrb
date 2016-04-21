<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NewspaperRep")
 * @UniqueEntity(fields="name", message="already.registered.newpapper.name")
 * @UniqueEntity(fields="domain", message="already.registered.domain")
 */
class Newspaper
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string", length=30, nullable=true)
	 * @Assert\Length(max = 30, groups={"Default", "publish"})
	 * @Assert\NotBlank(message = "not.blank.newspaper.name", groups={"Default"})
	 */
    protected $name;
    
    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\Length(max = 30, groups={"Default", "publish"})
     * @Assert\NotBlank(message = "not.blank.domain", groups={"Default"})
     * @Assert\Regex(pattern="/^[a-zA-Z0-9]*$/", message = "invalid.domain", groups={"Default"})
     */
    protected $domain;
    
    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="newspaper", cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $categories;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
     **/
    protected $owner;
    
    /**
	 * @ORM\OneToMany(targetEntity="Contributor", mappedBy="newspaper", cascade={"persist"})
	 */
    protected $contributors;
    
    public function __construct()
    {
    	$this->contributors = new ArrayCollection();
    	$this->categories = new ArrayCollection();
    }
    
    
    /**
     * Set id
     *
     * @param string $id
     * @return Newspaper
     */
    public function setId($id)
    {
    	$this->id = $id;
    
    	return $id;
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
     * Set name
     *
     * @param string $name
     * @return Newspaper
     */
    public function setName($name)
    {
    	$this->name = $name;
    
    	return $this;
    }
    
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
    	return $this->name;
    }
    
    /**
     * Set domain
     *
     * @param string $domain
     * @return Newspaper
     */
    public function setDomain($domain)
    {
    	$this->domain = $domain;
    
    	return $this;
    }
    
    /**
     * Get domain
     *
     * @return string
     */
    public function getDomain()
    {
    	return $this->domain;
    }
    
    /**
     * Get categories
     *
     * @return ArrayCollection $categories
     */
    public function getCategories()
    {
    	return $this->categories;
    }
    
    /**
     * Set categories
     *
     * @param ArrayCollection $categories
     * @return Newspaper
     */
    public function setCategories($categories)
    {
    	// Put all categories to lower case with a capital first letter
    	foreach ($categories as $c)
    	{
    		$c->setName(ucwords(mb_strtolower($c->getName(), mb_detect_encoding($c->getName()))));
    		$c->setNewspaper($this);
    	}
    	
    	$this->categories = $categories;
    
    	return $this;
    }
    
    public function addCategory(Category $category)
    {
    	// Put category to lower case with a capital first letter
    	$category->setName(ucwords(mb_strtolower($category->getName(), mb_detect_encoding($category->getName()))));
    	$category->setNewspaper($this);
    	
    	$this->categories->add($category);
    }
    
    public function removeCategory(Category $category)
    {
    	$category->setNewspaper(null);
    	$this->categories->removeElement($category);
    }
    
    /**
     * Set owner
     *
     * @param \AppBundle\Entity\User $owner
     * @return Newspaper
     */
    public function setOwner(\AppBundle\Entity\User $owner = null)
    {
    	$this->owner = $owner;
    
    	return $this;
    }
    
    /**
     * Get owner
     *
     * @return \AppBundle\Entity\User
     */
    public function getOwner()
    {
    	return $this->owner;
    }
    
    /**
     * Set Contributor
     *
     * @param ArrayCollection $contributors
     * @return Newspaper
     */
    public function setContributors($contributors = null)
    {
    	$this->contributors = $contributors;
    
    	return $this;
    }
    
    /**
     * Get contributors
     *
     * @return ArrayCollection
     */
    public function getContributors()
    {
    	return $this->contributors;
    }
    
    public function getCategoryByName($categoryName) {
    	foreach ($this->categories as $category)
    	{
    		if($category->getName() == $categoryName) {
				return $category;
    		}
    	}
    	return null;
    }
    
    public function hasRole($user, $role) {
    	foreach ($this->contributors as $a)
    	{
    		if($a->getUser()->getId() == $user->getId()) {
    			if($a->getRole() == $role) {
    				return true;
    			} else {
    				return false;
    			}
    		}
    	}
    	return false;
    }
    
    public function isContributor($user) {
    	foreach ($this->contributors as $a)
    	{
    		if($a->getUser()->getId() == $user->getId()) {
    			return true;
    		}
    	}
    	return false;
    }
    
    public function isContributorByEmail($email) {
    	foreach ($this->contributors as $a)
    	{
    		if($a->getUser()->getEmail() == $email) {
    			return true;
    		}
    	}
    	return false;
    }
    
    public function __toString() {
    	return "Newspaper";
    }
}
