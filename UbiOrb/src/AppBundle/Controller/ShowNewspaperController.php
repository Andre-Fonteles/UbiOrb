<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Service\NewspaperTools;

class ShowNewspaperController extends Controller
{
	public function indexAction(Request $request, $subdomain)
	{
		$appLocales = explode('|', $this->container->getParameter('app_locales'));
		$preferedLanguage = $this->get('request')->getPreferredLanguage($appLocales);
		
		return $this->redirect($this->generateUrl('show_newspaper', array("_locale" => $preferedLanguage, "subdomain" => $subdomain)));
	}
	
	public function showNewsAction(Request $request, $id, $slug, $subdomain)
	{
		$newspaper = $this->getDoctrine()
		->getRepository('AppBundle:Newspaper')
		->findOneBy(array('domain' => $subdomain));
		
		if(!$id || !$slug || !$newspaper) {
			throw $this->createNotFoundException();
		}
		
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:News');
		$news = $repository->findOneBy(array("id" => $id, "slugTitle" => $slug));
		
		if($news->getNewspaper()->getId() != $newspaper->getId()) {
			throw $this->createNotFoundException();
		}
		
		$news->incrementViews();
		$em = $this->getDoctrine()->getManager();
		$em->persist($news);
		$em->flush();
		 
		return $this->render('news/show-news.html.twig', array(
				'news' => $news
		));
	}
	
	public function showNewspaperAction(Request $request, $category = null, $subdomain)
	{
		$newspaper = $this->getDoctrine()
		->getRepository('AppBundle:Newspaper')
		->findOneBy(array('domain' => $subdomain));
		 
		if(!$newspaper) {
			throw $this->createNotFoundException();
		}
		 
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:News');
		 
		$offset = 0;
		$limitHeadLines = 6;
		$limitRegularNews = 4;
		 
		if(!$category) {
			$olderHealdineNews = $repository->findBy(array(
					"headline" => true,
					"newspaper" => $newspaper->getId()
			), array('publishDate' => 'DESC'), $limitHeadLines, $offset);
	
			$regularNews = $repository->findBy(array(
					"headline" => false,
					"newspaper" => $newspaper->getId()
			), array('publishDate' => 'DESC'), $limitRegularNews, $offset);
		} else {
			$olderHealdineNews = $repository->getNewsByNewspaperAndCategory($newspaper->getId(), $category, true, $offset, $limitHeadLines);
			$regularNews = $repository->getNewsByNewspaperAndCategory($newspaper->getId(), $category, false, $offset, $limitRegularNews);
		}
		 
		$headlineNews = array_shift($olderHealdineNews);
		 
		 
		if(!$headlineNews) {
			$headlineNews = array_shift($regularNews);
		}
		 
		$newspaperTools = (new NewspaperTools());
		$newspaperTools->balanceNews($regularNews, $olderHealdineNews);
		 
		return $this->render('newspaper/show/index.html.twig', array(
				'headlineNews' => $headlineNews,
				'regularNews' => $regularNews,
				'olderHealdineNews' => $olderHealdineNews,
				'newspaper' => $newspaper,
				'category' => $category
		));
	}
	
	public function listAllNewsAction(Request $request, $offset, $limit, $subdomain)
	{
		$newspaper = $this->getDoctrine()
		->getRepository('AppBundle:Newspaper')
		->findOneBy(array('domain' => $subdomain));
	
		
		if(!$newspaper) {
			throw $this->createNotFoundException();
		}
		 
		if($offset<0) {
			$offset = 0;
		}
	
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:News');
		$newsList = $repository->findBy(array("newspaper" => $newspaper->getId()), array('publishDate' => 'DESC'), $limit, $offset);
		$total = $repository->countNewsByNewspaper($newspaper->getId());
	
		return $this->render('newspaper/show/all-news.html.twig', array(
				'newsList' => $newsList,
				'total' => $total,
				'offset' => $offset,
				'limit' => $limit,
				'newspaper' => $newspaper
		));
	}
}