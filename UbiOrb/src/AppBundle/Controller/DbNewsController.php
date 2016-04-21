<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\User;
use AppBundle\Form\Type\RecoverPasswordType;
use Symfony\Component\Security\Core\SecurityContextInterface;
use AppBundle\Form\Type\UserType;
use Symfony\Component\Security\Core\Util\SecureRandom;
use AppBundle\Entity\EmailConfCode;
use AppBundle\Entity\ResetPassCode;
use AppBundle\Form\Type\ResetPasswordType;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use AppBundle\Form\Type\ChangePasswordType;
use AppBundle\Entity\ChangePassword;
use AppBundle\Entity\News;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\Type\CreateNewsType;
use AppBundle\Entity\Figure;
use AppBundle\Form\Type\UpdateNewsType;
use AppBundle\Entity\Newspaper;

class DbNewsController extends Controller
{
	public function createNewsAction(Request $request, $newspaperId)
	{
		$news = new News();
		$npCriteria = array();
		
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Newspaper');
		
		if($newspaperId) {
			$npCriteria['id'] = $newspaperId;
	    	$newspaper = $repository->findOneBy($npCriteria);
		} else {
			$newspapers = $repository->findByUserContributor($this->getUser());
			
			if($newspapers && count($newspapers) > 0) {
				$newspaper = array_shift($newspapers);
			} else {
				$newspaper = null;
			}
		}
		
		
    	if(!$newspaper) {
    		$translator = $this->container->get('translator');
    		
    		$request->getSession()->getFlashBag()->add(
    				'notice',
    				$translator->trans('label.noNewspapers')
    		);
    		
    		return $this->redirect($this->generateUrl('create_newspaper'));
    	}
    	
    	$news->setNewspaper($newspaper);
    	
    	// Get list of available newspapers to publish
    	$newspapers = $this->getDoctrine()->getRepository('AppBundle:Newspaper')
    	->findByUserContributor($this->getUser());
    	
		$form = $this->createForm(new CreateNewsType($newspapers), $news);
		$form->handleRequest($request);
		 
		if ($form->isValid()) {
			// Configure new object with 
			$news = $form->getData();
			
			// Make sure the user didn't hacked the newspaperId
			if(!$news->canBeCreatedBy($this->getUser())) {
				throw new AccessDeniedException();
			}
			
			// This form always returns a object Figure 
			// inside the news (even when there is no file Uploaded).
			if($news->getFigure()->getFile() == null) {
				$news->setFigure(null);
			} else {
				$news->getFigure()->setOwner($this->getUser());
			}

			$purifier = $this->container->get('exercise_html_purifier.default');
	    	$purifier->config->set('HTML.SafeIframe', true);
	    	$purifier->config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%'); //allow YouTube and Vimeo
			
			// Configure new news
			$news->setAuthor($this->getUser());
			$news->setPublishDate();
			$news->setDraft(false);
			$news->setContent($purifier->purify($news->getContent()));
				
			// Creates a slugfied url
			$slugTitle = $this->get('slugfier')->slugfy($news->getTitle());
			$news->setSlugTitle($slugTitle);
			
			// Create a new published news in the database
			$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:News');
			$repository->createPublishedNews($news);
	
			return $this->redirect($this->generateUrl('show_news', 
					array('id' => $news->getId(), 'slug' => $news->getSlugTitle(), 'subdomain' => $news->getNewspaper()->getDomain()))
			);
		}
		
		return $this->render('news/create-news.html.twig',
				array('form' => $form->createView())
		);
	}
	
	public function updateNewsAction(Request $request, $id = null)
	{
		if($id) {
	    	$news = $this->getDoctrine()
	    	->getRepository('AppBundle:News')
	    	->findOneBy(array('id' => $id));
		}
		
		if($news) {
	    	
	    	$form = $this->createForm(new UpdateNewsType(), $news,
	    			array('action' => $this->generateUrl('update_news', 
	    					array('id' => $news->getId()))));
	    	
	    	$form->handleRequest($request);
	    	
	    	if ($form->isValid()) {
		    	// Check if the user has permission
		    	if (!($news->canBeUpdatedBy($this->getUser()))) {
		    		throw new AccessDeniedException();
		    	}
		    	
		    	$purifier = $this->container->get('exercise_html_purifier.default');
		    	$purifier->config->set('HTML.SafeIframe', true);
		    	$purifier->config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%'); //allow YouTube and Vimeo
		    	
		    	$news->setContent($purifier->purify($news->getContent()));
	    		
	    		// Update news in the database
				$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:News');
				$repository->updateNews($news);
				
				$translator = $this->container->get('translator');
				
				// TODO : pass link to see the news
				$request->getSession()->getFlashBag()->add(
						'notice',
						$translator->trans('label.success.updateNews')
				);
				
				return $this->redirect($this->generateUrl('admin_newspaper_news', 
						array('newspaperId' => $news->getNewspaper()->getId())));
	    	}
	    	
	    	return $this->render('news/update-news.html.twig',
	    			array('form' => $form->createView())
	    	);
    	}
        throw $this->createNotFoundException();
	}
	
	
    public function deleteNewsAction(Request $request, $id = null)
    {
    	if($id) {
	    	$news = $this->getDoctrine()->getRepository('AppBundle:News')->find($id);
    	}
	    	
    	if($news) {
    		$form = $this->createFormBuilder($news)
    			->add('delete', 'submit', array('label' => 'form.label.delete'))
    			->getForm();
    		$form->handleRequest($request);
    		
    		if($form->isValid()) {
    			// Check if the user has permission
    			if (!($news->canBeDeletedBy($this->getUser()))) {
    				throw new AccessDeniedException();
    			}
    			
    			// Delete news in the database
    			$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:News');
    			$repository->deleteNews($news);
    			
    			$translator = $this->container->get('translator');

    			$request->getSession()->getFlashBag()->add(
    					'notice',
    					$translator->trans('label.success.deleteNews')
    			);
    			
				return $this->redirect($this->generateUrl('admin_newspaper_news', 
						array('newspaperId' => $news->getNewspaper()->getId())));
    		}
    		
    		return $this->render('news/delete-news.html.twig',
    				array('form' => $form->createView()));
    	}
    	
    	throw $this->createNotFoundException();
    }
    
    public function listNewsAction(Request $request, $offset, $limit)
    {
    	if($offset<0) {
    		$offset = 0;
    	}
    	
    	$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:News');
    	$newsList = $repository->findBy(array("author" => $this->getUser()), array('publishDate' => 'DESC'), $limit, $offset);
    	$total = $repository->countNewsByUser($this->getUser()->getId());
    	return $this->render('news/list-news.html.twig', array(
    			'newsList' => $newsList,
    			'total' => $total,
    			'offset' => $offset,
    			'limit' => $limit,
    	));
    }
    
    public function adminNewspaperNewsAction(Request $request, $newspaperId, $offset, $limit)
    {
    	if($offset<0) {
    		$offset = 0;
    	}

    	$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Newspaper');
    	$newspaper = $repository->findOneBy(array("id" => $newspaperId));
    	
    	$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:News');
    	$newsList = $repository->findBy(array("newspaper" => $newspaperId), array('publishDate' => 'DESC'), $limit, $offset);
    	
    	$total = $repository->countNewsByNewspaper($newspaper->getId());
    	
    	return $this->render('dashboard/admin-newspaper-news.html.twig', array(
    			'newsList' => $newsList,
    			'total' => $total,
    			'offset' => $offset,
    			'limit' => $limit,
    			'newspaper' => $newspaper
    	));
    }
}
