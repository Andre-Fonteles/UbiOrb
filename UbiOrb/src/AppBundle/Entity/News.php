<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NewsRep")
 * @ORM\HasLifecycleCallbacks
 */
class News {
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string", length=75, nullable=true)
	 * @Assert\Length(max = 75, groups={"Default", "publish"})
	 * @Assert\NotBlank(message = "not.blank.title", groups={"publish"})
	 */
	protected $title;
	
	/**
	 * @ORM\Column(type="string", length=80, nullable=true)
	 */
	protected $slugTitle;
	
	/**
	 * @ORM\Column(type="string", length=160, nullable=true)
	 * @Assert\Length(max = 160, groups={"Default", "publish"})
	 * @Assert\NotBlank(message = "not.blank.resume", groups={"publish"})
	 */
	protected $resume;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":false})
	 */
	protected $headline = false;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @Assert\Length(max = 20000, groups={"Default", "publish"})
	 * @Assert\NotBlank(message = "not.blank.content", groups={"publish"})
	 */
	protected $content;
	
	/**
	 * @ORM\Column(type="string", length=70, nullable=true)
	 * @Assert\Length(max = 70, groups={"Default", "publish"})
	 * @Assert\NotBlank(message = "not.blank.tags", groups={"publish"})
	 */
	protected $tags;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $numberOfViews = 0;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":true})
	 */
	protected $draft = true;
	
	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="author_id", referencedColumnName="id", nullable=false)
	 */
	protected $author;
	
	/**
	 * @ORM\OneToOne(targetEntity="Figure")
	 * @ORM\JoinColumn(name="figure_id", referencedColumnName="id", nullable=true)
	 */
	protected $figure;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Newspaper")
	 * @ORM\JoinColumn(name="newspaper_id", referencedColumnName="id", nullable=false)
	 */
	protected $newspaper;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $updateDate;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $publishDate;
	
	/**
	 * @ORM\ManyToMany(targetEntity="Category", cascade={"persist"})
	 */
	protected $categories;
	
	public function __construct() {
		$this->categories = new ArrayCollection ();
	}
	
	/**
	 * Set id
	 *
	 * @param string $id        	
	 * @return News
	 */
	public function setId($id) {
		$this->id = $id;
		
		return $this;
	}
	
	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Set title
	 *
	 * @param string $title        	
	 * @return News
	 */
	public function setTitle($title) {
		$this->title = $title;
		
		return $this;
	}
	
	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Set slugTitle
	 *
	 * @param string $slugTitle        	
	 * @return News
	 */
	public function setSlugTitle($slugTitle) {
		$this->slugTitle = $slugTitle;
		
		return $this;
	}
	
	/**
	 * Get slugTitle
	 *
	 * @return string
	 */
	public function getSlugTitle() {
		return $this->slugTitle;
	}
	
	/**
	 * Set resume
	 *
	 * @param string $resume        	
	 * @return News
	 */
	public function setResume($resume) {
		$this->resume = $resume;
		
		return $this;
	}
	
	/**
	 * Get resume
	 *
	 * @return string
	 */
	public function getResume() {
		return $this->resume;
	}
	
	/**
	 * Set headline
	 *
	 * @param boolean $headline        	
	 * @return News
	 */
	public function setHeadline($headline) {
		$this->headline = $headline;
		
		return $this;
	}
	
	/**
	 * Get headline
	 *
	 * @return boolean
	 */
	public function isHeadline() {
		return $this->headline;
	}
	
	/**
	 * Set content
	 *
	 * @param string $content        	
	 * @return News
	 */
	public function setContent($content) {
		$this->content = $content;
		
		return $this;
	}
	
	/**
	 * Get content
	 *
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}
	
	/**
	 * Set tags
	 *
	 * @param string $tags        	
	 * @return News
	 */
	public function setTags($tags) {
		$this->tags = $tags;
		
		return $this;
	}
	
	/**
	 * Get tags
	 *
	 * @return string
	 */
	public function getTags() {
		return $this->tags;
	}
	
	/**
	 * Set numberOfViews
	 *
	 * @param integer $numberOfViews        	
	 * @return News
	 */
	public function setNumberOfViews($numberOfViews) {
		$this->numberOfViews = $numberOfViews;
		
		return $this;
	}
	
	/**
	 * Increment numberOfViews by 1
	 */
	public function incrementViews() {
		$this->numberOfViews ++;
	}
	
	/**
	 * Get numberOfViews
	 *
	 * @return integer
	 */
	public function getNumberOfViews() {
		return $this->numberOfViews;
	}
	
	/**
	 * Set draft
	 *
	 * @param boolean $draft        	
	 * @return News
	 */
	public function setDraft($draft) {
		$this->draft = $draft;
		
		return $this;
	}
	
	/**
	 * Get draft
	 *
	 * @return boolean
	 */
	public function isDraft() {
		return $this->draft;
	}
	
	/**
	 * Set updateDate
	 *
	 * @return News
	 */
	public function setUpdateDate() {
		$this->updateDate = new \DateTime ( "now" );
		return $this;
	}
	
	/**
	 * Get updateDate
	 *
	 * @return \DateTime
	 */
	public function getUpdateDate() {
		return $this->updateDate;
	}
	
	/**
	 * Set publishDate
	 *
	 * @return News
	 */
	public function setPublishDate() {
		$this->publishDate = new \DateTime ( "now" );
		
		return $this;
	}
	
	/**
	 * Get publishDate
	 *
	 * @return \DateTime
	 */
	public function getPublishDate() {
		return $this->publishDate;
	}
	
	/**
	 * Set author
	 *
	 * @param \AppBundle\Entity\User $author        	
	 * @return News
	 */
	public function setAuthor(\AppBundle\Entity\User $author) {
		$this->author = $author;
		
		return $this;
	}
	
	/**
	 * Get author
	 *
	 * @return \AppBundle\Entity\User
	 */
	public function getAuthor() {
		return $this->author;
	}
	
	/**
	 * Set figure
	 *
	 * @param \AppBundle\Entity\Figure $figure        	
	 * @return News
	 */
	public function setFigure(\AppBundle\Entity\Figure $figure = null) {
		$this->figure = $figure;
		
		return $this;
	}
	
	/**
	 * Get figure
	 *
	 * @return \AppBundle\Entity\Figure
	 */
	public function getFigure() {
		return $this->figure;
	}
	
	/**
	 * Set newspaper
	 *
	 * @param \AppBundle\Entity\Newspaper $newspaper        	
	 * @return News
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
	 * Get categories
	 *
	 * @return ArrayCollection $categories
	 */
	public function getCategories() {
		return $this->categories;
	}
	
	/**
	 * Set categories
	 *
	 * @param ArrayCollection $categories        	
	 * @return News
	 */
	public function setCategories($categories) {
		$this->categories = $categories;
		
		return $this;
	}
	
	/* ---------------------------- */
	/* Methods for permission check */
	/* ---------------------------- */
	
	public function canBeCreatedBy($user) {
		if($this->newspaper->isContributor($user)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function canBeUpdatedBy($user) {
		if($this->author->getId() == $user->getId() && $this->newspaper->isContributor($user)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function canBeDeletedBy($user) {
		if($this->author->getId() == $user->getId() && $this->newspaper->isContributor($user) || $this->newspaper->hasRole($user, Contributor::ROLE_ADMIN)) {
			return true;
		} else {
			return false;
		}
	}
}
