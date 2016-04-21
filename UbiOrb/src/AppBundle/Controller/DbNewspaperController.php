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
use AppBundle\Entity\Figure;
use AppBundle\Form\Type\UpdateNewsType;
use AppBundle\Entity\Category;
use AppBundle\Entity\Newspaper;
use AppBundle\Form\Type\CategoryType;
use AppBundle\Form\Type\CreateNewspaperType;
use AppBundle\Entity\Contributor;
use AppBundle\Form\Type\UpdateNewspaperType;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Service\NewspaperTools;

class DbNewspaperController extends Controller
{
	public function createNewspaperAction(Request $request)
	{
		$newspaper = new Newspaper();
		
		$form = $this->createForm(new CreateNewspaperType(), $newspaper);
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$newspaper = $form->getData();
			$newspaper->setOwner($this->getUser());
			
			$contributor = new Contributor();
			$contributor->setUser($this->getUser());
			$contributor->setRole(Contributor::ROLE_ADMIN);
			$contributor->setNewspaper($newspaper);
			$newspaper->getContributors()->add($contributor);
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($newspaper);
			$em->flush();
			
			return $this->redirect($this->generateUrl('dashboard'));
		}
		
		return $this->render('newspaper/create-newspaper.html.twig', array(
				'form' => $form->createView(),
		));
	}
	
	public function updateNewspaperAction(Request $request, $id = null)
	{
		$newspaper = $this->getDoctrine()
    	->getRepository('AppBundle:Newspaper')
    	->findOneBy(array('id' => $id));
		
		if(!$newspaper) {
			throw $this->createNotFoundException();
		}
		
		// Create an ArrayCollection of the current Categories objects in the database
		$originalCategories = new ArrayCollection();
		foreach ($newspaper->getCategories() as $c) {
			$originalCategories->add($c);
		}
		
		$form = $this->createForm(new UpdateNewspaperType(), $newspaper);
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$newspaper = $form->getData();
			
			if(!$newspaper->hasRole($this->getUser(), Contributor::ROLE_ADMIN)) {
				throw new AccessDeniedException();
			}
				
			$em = $this->getDoctrine()->getManager();
			// Delete removed categories
			foreach ($originalCategories as $category) {
				if(!$newspaper->getCategoryByName($category->getName())) {
					$em->remove($category);
				}
			}
			$em->persist($newspaper);
			$em->flush();
			
			// Configure success flash message
			$translator = $this->container->get('translator');
			$request->getSession()->getFlashBag()->add(
					'notice',
					$translator->trans('label.success.updateNewspaper', array("%newspaperName%" => $newspaper->getName()))
			);
			
			return $this->redirect($this->generateUrl('dashboard'));
		}
		
		return $this->render('newspaper/update-newspaper.html.twig', array(
				'form' => $form->createView(),
		));
	}
	
	public function orderCategoriesAction(Request $request, $id = null)
	{
		$categoriesJson = $this->get('request')->request->get('categoriesJson');
		
		$newspaper = $this->getDoctrine()
		->getRepository('AppBundle:Newspaper')
		->findOneBy(array('id' => $id));
		
		if(!$newspaper) {
			throw $this->createNotFoundException();
		}
		if(!$newspaper->hasRole($this->getUser(), Contributor::ROLE_ADMIN)) {
			throw new AccessDeniedException();
		}
		
		if($categoriesJson) {
			$categoriesArray = json_decode($categoriesJson, true);
				
			foreach ($categoriesArray as $category) {
				$newspaper->getCategoryByName($category['name'])
				->setPosition($category['position']);
			}
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($newspaper);
			$em->flush();
			
			// Configure success flash message
			$translator = $this->container->get('translator');
			$request->getSession()->getFlashBag()->add(
					'notice',
					$translator->trans('label.success.updateCategoryOrder', array("%newspaperName%" => $newspaper->getName()))
			);
			
			return new Response($this->generateUrl('dashboard'));
		}
	
		return $this->render('newspaper/order-categories.html.twig', array(
				'newspaper' => $newspaper,
		));
	}
	
    public function deleteNewspaperAction(Request $request, $id = null)
    {
    	$newspaper = $this->getDoctrine()
    	->getRepository('AppBundle:Newspaper')
    	->findOneBy(array('id' => $id));
    	
    	if($newspaper) {
	    	$form = $this->createFormBuilder($newspaper)
	    	->add('delete', 'submit', array('label' => 'form.label.delete'))
	    	->getForm();
	    	$form->handleRequest($request);
	    	
	    	if($form->isValid()) {
		    	if($newspaper->getOwner()->getId() != $this->getUser()->getId()) {
		    		throw new AccessDeniedException();
		    	}
		    	
		    	// Delete all data related to Newspaper
		    	$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:News');
		    	$repository->deleteNewsByNewspaper($newspaper->getId());
		    	
		    	$em = $this->getDoctrine()->getManager();
	    		$em->remove($newspaper);
	    		foreach ($newspaper->getContributors() as $c) {
	    			$em->remove($c);
	    		}
	    		foreach ($newspaper->getCategories() as $c) {
	    			$em->remove($c);
	    		}
	    		$em->flush();
	    		 
	    		$translator = $this->container->get('translator');
	    		$request->getSession()->getFlashBag()->add(
	    				'notice',
	    				$translator->trans('label.success.deleteNewspaper', array("%newspaperName%" => $newspaper->getName()))
	    		);
	    		
	    		return $this->redirect($this->generateUrl('dashboard'));
	    	}
    	
    		return $this->render('newspaper/delete-newspaper.html.twig',
    				array('form' => $form->createView()));
    	}
    	 
    	throw $this->createNotFoundException();
    }
    
    public function quitNewspaperAction(Request $request, $id = null)
    {
    	$newspaper = $this->getDoctrine()
    	->getRepository('AppBundle:Newspaper')
    	->findOneBy(array('id' => $id));
    	 
    	if($newspaper) {
    		$form = $this->createFormBuilder($newspaper)
    		->add('quit', 'submit', array('label' => 'form.label.quit'))
    		->getForm();
    		$form->handleRequest($request);
    
    		if($form->isValid()) {
    			if($newspaper->getOwner()->getId() == $this->getUser()->getId()) {
    				throw new AccessDeniedException();
    			}

    			$contributors = $newspaper->getContributors();
    			$contributor = null;
    			
    			foreach ($contributors as $c) {
    				if($c->getUser()->getId() == $this->getUser()->getId()) {
    					$contributor = $c;
    					break;
    				}
    			}
    			
    			if($contributor) {
	    			$em = $this->getDoctrine()->getManager();
	    			$contributors->removeElement($contributor);
	    			$em->remove($contributor);
	    			$em->persist($newspaper);
	    			$em->flush();
	    			
	    			$translator = $this->container->get('translator');
	    			$request->getSession()->getFlashBag()->add(
	    					'notice',
	    					$translator->trans('label.success.quitNewspaper', array("%newspaperName%" => $newspaper->getName()))
	    			);
	    	   
	    			return $this->redirect($this->generateUrl('dashboard'));
    			}		
    		}
    		 
    		return $this->render('newspaper/quit-newspaper.html.twig',
    				array('form' => $form->createView()));
    	}
    
    	throw $this->createNotFoundException();
    }
}
