<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use AppBundle\Service\ThumbnailGenerator;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Figure
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @Assert\Image(
	 * 	maxSize="7M",
	 *  mimeTypes = {"image/png", "image/jpeg", "image/jpg"},
	 *  mimeTypesMessage = "invalid.imageType",
	 *  maxSizeMessage= "invalid.fileSize")
	 */
	private $file;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	private $path;

	/**
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	private $thumbPath;
	
	/**
	 * @ORM\Column(type="boolean", nullable=false, options={"default":false})
	 */
	private $landscape;
	
	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
	 **/
	protected $owner;
	
	private $maxWidth = 930;
	private $maxHeight = 525;
	
	private $maxThumbWidth = 300;
	private $maxThumbHeight = 120;
	
	/**
	 * Set id
	 *
	 * @param string $id
	 * @return Figure
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
     * Set path
     *
     * @param string $path
     * @return Figure
     */
    public function setPath($path)
    {
    	$this->path = $path;
    
    	return $this;
    }
    
    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
    	return $this->path;
    }
    
    /**
     * Set thumbPath
     *
     * @param string $thumbPath
     * @return Figure
     */
    public function setThumbPath($thumbPath)
    {
    	$this->thumbPath = $thumbPath;
    
    	return $this;
    }
    
    /**
     * Get thumbPath
     *
     * @return string
     */
    public function getThumbPath()
    {
    	return $this->thumbPath;
    }
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
	public function setFile(UploadedFile $file)
    {
    	if($file) {
	    	list($imageWidth, $imageHeight) = getimagesize($file->getPathname());
	    	if($imageHeight >= $imageWidth) {
	    		$this->landscape = false;
	    	} else {
	    		$this->landscape = true;
	    	}
    	}
        $this->file = $file;
    }
    
    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
    	return $this->file;
    }
    
    /**
     * Sets landscape.
     *
     * @param boolean $landscape
     */
    public function setLandscape($landscape)
    {
    	$this->landscape = $landscape;
    }
    
    /**
     * Get landscape.
     *
     * @return boolean
     */
    public function isLandscape()
    {
    	return $this->landscape;
    }
    
    /**
     * Set owner
     *
     * @param \AppBundle\Entity\User $owner
     * @return Figure
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
     * @ORM\PreUpdate()
     */
    public function onUpdate() {
    	// This entity can not be updated because it 
    	// represents a image in the file system 
    	// that can only either be created or deleted
		throw new \Exception("Figure entity can not be updated on database.");    	
    }
        
    /**
     * @ORM\PrePersist()
     */
    public function createImagesFromFile()
    {
    	if (null === $this->getFile()) {
    		throw new \Exception("Figure entity can not be created without a file.");
    	}

    	// if there is an error when creating the images, an exception will
    	// be automatically thrown by move(). This will properly prevent
    	// the entity from being persisted to the database on error
    	$filename = sha1(uniqid(mt_rand(), true));
    	$this->path = $filename.'.'.$this->getFile()->guessExtension();
    	$this->thumbPath = $filename.'thumb.'.$this->getFile()->guessExtension();
    	
    	$thumbnailGen = new ThumbnailGenerator();
    	$thumbnailGen->generate($this->getFile()->getPathname(), $this->getWebPath(), $this->maxWidth, $this->maxHeight);
    	$thumbnailGen->generate($this->getFile()->getPathname(), $this->getWebThumbPath(), $this->maxThumbWidth, $this->maxThumbHeight);
    	unlink($this->getFile()->getPathname());
    	$this->file = null;
    	
    }

    public function getAbsolutePath()
    {
    // If the Figure object was created but not persisted,
    	// then there is no path
    	if($this->path) {
	    	return $this->getUploadDir().'/'.$this->path;
    	}
    	return null;
    }
    
    public function getWebPath()
    {
    	// If the Figure object was created but not persisted,
    	// then there is no path
    	if($this->path) {
	    	return $this->getUploadDir().'/'.$this->path;
    	}
    	return null;
    }
    
    public function getAbsoluteThumbPath()
    {
    // If the Figure object was created but not persisted,
    	// then there is no path
    	if($this->thumbPath) {
	    	return $this->getUploadDir().'/'.$this->thumbPath;
    	}
    	return null;
    }
    
    public function getWebThumbPath()
    {
    // If the Figure object was created but not persisted,
    	// then there is no path
    	if($this->thumbPath) {
	    	return $this->getUploadDir().'/'.$this->thumbPath;
    	}
    	return null;
    }
    
    public function getUploadRootDir()
    {
    	// the absolute directory path where uploaded
    	// documents should be saved
    	return __DIR__.'/../../../web/'.$this->getUploadDir();
    }
    
 	public function getUploadDir()
    {
    	// get rid of the __DIR__ so it doesn't screw up
    	// when displaying uploaded doc/image in the view.
    	return 'u/image';
    }
    
    /* -- ATTENTION -- */
    // These temporary paths used delete images files must be set
    // in the PreRemove event to be used on PosRemove.
    // This is a Work around to deal with a symfony bug that 
    // wouldn't allow to get the path in the PostRemove,
    // but would rather thow a Exception saying "Entity not found"
	// TODO: Reflect about a more elegant alternative
	
    private $toDeleteImg;
    private $toDeleteThumbImg;
    
    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
    	$this->toDeleteImg = $this->getAbsolutePath();
    	$this->toDeleteThumbImg = $this->getAbsoluteThumbPath();
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
    	// Delete image from the file system if the entity
    	// is deleted from database
    	if ($this->toDeleteImg) {
    		unlink($this->toDeleteImg);
    	}
    	 
    	if($this->toDeleteThumbImg) {
    		unlink($this->toDeleteThumbImg);
    	}
    }
}
