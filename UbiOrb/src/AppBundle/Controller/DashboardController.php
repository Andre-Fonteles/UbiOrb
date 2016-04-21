<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Entity\Contributor;
use AppBundle\Form\Type\InviteContributorType;
use AppBundle\Entity\ContributorInvitation;

class DashboardController extends Controller
{
    public function indexAction(Request $request)
    {
    	$newspapers = $this->getDoctrine()->getRepository('AppBundle:Newspaper')
    	->findByUserContributor($this->getUser());
    	
    	$invitations = $this->getDoctrine()->getRepository('AppBundle:ContributorInvitation')
    	->findBy(array('email' => $this->getUser()->getEmail()));
    	 
        return $this->render('dashboard/index.html.twig', array(
        		"newspapers" => $newspapers, 
        		"invitations" => $invitations
        ));
    }
    
    public function adminNewspaperAction(Request $request, $id)
    {
    	$newspaper = $this->getDoctrine()->getRepository('AppBundle:Newspaper')->findOneBy(array('id' => $id));
    	 
    	return $this->render('dashboard/admin-newspaper.html.twig', array("newspaper" => $newspaper));
    }
    
    public function adminContributorsAction(Request $request, $id)
    {
    	$newspaper = $this->getDoctrine()->getRepository('AppBundle:Newspaper')->findOneBy(array('id' => $id));
    	 
    	if(!$newspaper->isContributor($this->getUser())) {
    		throw new AccessDeniedException();
    	}
    	
    	return $this->render('dashboard/admin-contributors.html.twig', array("newspaper" => $newspaper));
    }
    
    public function inviteContributorAction(Request $request, $id)
    {
    	$newspaper = $this->getDoctrine()->getRepository('AppBundle:Newspaper')->findOneBy(array('id' => $id));
    	
    	if(!$newspaper->hasRole($this->getUser(), Contributor::ROLE_ADMIN)) {
    		throw new AccessDeniedException();
    	}
    	
    	$form = $this->createForm(new InviteContributorType(), new ContributorInvitation());
    	$form->handleRequest($request);
    	
    	if ($form->isValid()) {
    		$contributorInvitation = $form->getData();
    		$translator = $this->container->get('translator');
    		
    		if($newspaper->isContributorByEmail($contributorInvitation->getEmail())) {
    			$request->getSession()->getFlashBag()->add(
    					'notice',
    					$translator->trans('label.alreadyContributor')
    			);
    			
    			return $this->render('dashboard/invite-contributor.html.twig', array(
    					"form" => $form->createView(),
    					"newspaper" => $newspaper
    			));
    		}
    			
    		$contributorInvitation->setInviter($this->getUser());
    		$contributorInvitation->setNewspaper($newspaper);
    		
    		$em = $this->getDoctrine()->getManager();

    		$oldInvitation = $this->getDoctrine()->getRepository('AppBundle:ContributorInvitation')
    		->findOneBy(array('email' => $contributorInvitation->getEmail(), 'newspaper' => $newspaper->getId()));
    		
    		if($oldInvitation) {
    			$em->remove($oldInvitation);
    		}
    		
    		$em->persist($contributorInvitation);
    		$em->flush();
    		
    		$this->get('email_manager')->sendContributorInvitation($contributorInvitation);
    		
    		
    		$request->getSession()->getFlashBag()->add(
    				'notice',
    				$translator->trans('A invitation has been sent to %email%.', 
    						array('%email%' => $contributorInvitation->getEmail()))
    		);
    		 
    		return $this->redirect($this->generateUrl(
    				'dashboard_admin_contributors', 
    				array('id' => $newspaper->getId())
    		));
    	}
    	
    	return $this->render('dashboard/invite-contributor.html.twig', array(
    			"form" => $form->createView(), 
    			"newspaper" => $newspaper
    	));
    }
    
    public function answerContributeInvitationAction(Request $request, $invitationId, $answer)
    {
    	$invitation = $this->getDoctrine()->getRepository('AppBundle:ContributorInvitation')
    	->findOneBy(array('id' => $invitationId));
    	
		if($invitation->getEmail() == $this->getUser()->getEmail()) {
			$em = $this->getDoctrine()->getManager();

			if($answer == "true") {
				$contributor = new Contributor();
				$contributor->setRole($invitation->getRole());
				$contributor->setUser($this->getUser());
				$contributor->setNewspaper($invitation->getNewspaper());
				$invitation->getNewspaper()->getContributors()->add($contributor);
				$em->persist($invitation->getNewspaper());
			}
			
			$em->remove($invitation);
			$em->flush();
			return $this->redirect($this->generateUrl('dashboard'));
    	}
    	
    	throw $this->createNotFoundException();
    }
}
