<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
    	$appLocales = explode('|', $this->container->getParameter('app_locales'));
		$preferedLanguage = $this->get('request')->getPreferredLanguage($appLocales);
			
    	// Check if user is logged in and fully authenticated (remember me won't work)
    	if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
    		return $this->redirect($this->generateUrl('dashboard', array("_locale" => $preferedLanguage)));
    	}
		    	
		return $this->redirect($this->generateUrl('home', array("_locale" => $preferedLanguage)));
    }
	
	public function homeAction(Request $request)
    {
    	return $this->render('main/index.html.twig');
    }
    
    public function termsAction(Request $request)
    {
    	return $this->render('other/terms.html.twig');
    }
}
