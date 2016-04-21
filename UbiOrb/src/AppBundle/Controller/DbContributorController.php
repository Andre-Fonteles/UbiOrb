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
use AppBundle\Form\Type\UpdateContributorType;

class DbContributorController extends Controller
{
	public function updateContributorAction(Request $request, $id = null)
	{
		$contributor = $this->getDoctrine()->getRepository('AppBundle:Contributor')
		->findOneBy(array('id' => $id));
		
		if(!$contributor) {
			throw $this->createNotFoundException();
		}
		
		$form = $this->createForm(new UpdateContributorType(), $contributor);
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$contributor = $form->getData();
			$newspaper = $contributor->getNewspaper();
			
			if(!($newspaper->hasRole($this->getUser(), Contributor::ROLE_ADMIN)) || 
					$this->getUser()->getId() == $contributor->getUser()->getId()) {
				throw new AccessDeniedException();
			}
			
			// No one can edit the owner of the paper
			if($contributor->getUser()->getId() == $newspaper->getOwner()->getId()) {
				throw new AccessDeniedException();
			}

			$em = $this->getDoctrine()->getManager();
			$em->persist($contributor);
			$em->flush();
			
			$translator = $this->container->get('translator');
			$request->getSession()->getFlashBag()->add(
					'notice',
					$translator->trans('label.success.updateContributor', array(
							"%email%" => $contributor->getUser()->getEmail()
							
					))
			);
			
			return $this->redirect($this->generateUrl('dashboard_admin_contributors', 
					array('id' => $newspaper->getId())
			));
		}
		
		return $this->render('contributor/update-contributor.html.twig', array(
				'form' => $form->createView(),
		));
	}
	
    public function deleteContributorAction(Request $request, $id = null)
    {
    	$contributor = $this->getDoctrine()->getRepository('AppBundle:Contributor')
		->findOneBy(array('id' => $id));
    	
    	if(!$contributor) {
    		throw $this->createNotFoundException();
    	}
    	
    	$form = $this->createFormBuilder($contributor)
    	->add('delete', 'submit', array('label' => 'form.label.delete'))
    	->getForm();
    	$form->handleRequest($request);
		
		if ($form->isValid()) {
			$contributor = $form->getData();
			$newspaper = $contributor->getNewspaper();
			
			if(!($newspaper->hasRole($this->getUser(), Contributor::ROLE_ADMIN)) || 
					$this->getUser()->getId() == $contributor->getUser()->getId()) {
				throw new AccessDeniedException();
			}
			
			// No one can delete the owner of the paper
			if($contributor->getUser()->getId() == $newspaper->getOwner()->getId()) {
				throw new AccessDeniedException();
			}

			$em = $this->getDoctrine()->getManager();
			$em->remove($contributor);
			$em->flush();
			
			$translator = $this->container->get('translator');
			$request->getSession()->getFlashBag()->add(
					'notice',
					$translator->trans('label.success.deleteContributor', array(
							"%email%" => $contributor->getUser()->getEmail()
								
					))
			);
				
			return $this->redirect($this->generateUrl('dashboard_admin_contributors',
					array('id' => $newspaper->getId())
			));
		}
		
		return $this->render('contributor/delete-contributor.html.twig', array(
				'form' => $form->createView(),
		));
    }
}
