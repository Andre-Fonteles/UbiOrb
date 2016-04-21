<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @UniqueEntity(fields={"name", "newspaper"}, message="already.registered.category")
 */
class Category
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="string", length=20)
	 * @Assert\Length(max = 20, groups={"Default"})
	 * @Assert\NotBlank(message = "not.blank.category.name", groups={"Default"})
	 */
    private $name;
    
	/**
	 * @ORM\Column(type="integer", nullable=false)
	 */
	protected $position = 0;
        
    /**
     * @ORM\ManyToOne(targetEntity="Newspaper", inversedBy="categories")
     * @ORM\JoinColumn(name="newspaper_id", referencedColumnName="id", nullable=false)
     */
    protected $newspaper;
    
    /**
     * Set id
     *
     * @param string $id
     * @return Category
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
     * Set name
     *
     * @param string $name
     * @return Category
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
     * Set newspaper
     *
     * @param \AppBundle\Entity\Newspaper $newspaper
     * @return Category
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
    
    /**
     * Set position
     *
     * @param integer $position
     * @return Category
     */
    public function setPosition($position) {
    	$this->position = $position;
    
    	return $this;
    }
    
    /**
     * Get position
     *
     * @return integer position
     */
    public function getPosition() {
    	return $this->position;
    }
}
