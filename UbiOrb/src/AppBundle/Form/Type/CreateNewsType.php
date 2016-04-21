<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;
use AppBundle\Entity\Newspaper;
use AppBundle\Entity\News;

class CreateNewsType extends AbstractType
{
	private $newspapers;
	
	public function __construct($newspapers) {
		$this->newspapers = $newspapers;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('title', 'text', array('label' => 'form.label.title', 'max_length' => 75));
		$builder->add('resume', 'textarea', array('label' => 'form.label.resume', 'max_length' => 160));
		$builder->add('headline', 'checkbox', array('label' => 'form.label.headline', 'required' => false));
		$builder->add('newspaper', 'entity', array(
				'class' => 'AppBundle\Entity\Newspaper',
				'property' => 'name',
				'choices' => $this->newspapers
		));
		$builder->add('figure', new FigureType(), array(
				'label' => 'form.label.mainPicture',
		        'data_class' => 'AppBundle\Entity\Figure'
		));
		$builder->add('publish', 'submit', array(
				'label' => 'form.label.publish',
				'validation_groups' => array('Default', 'publish')
		));	
		$builder->add('content', 'textarea', array('label' => 'form.label.content', 
				'attr' => array('style' => 'visibility: hidden; position: absolute')));
		$builder->add('tags', 'text', array('label' => 'form.label.tags', 'max_length' => 70));
		
		$formModifier = function (FormInterface $form, Newspaper $newspaper = null) {
			if($newspaper instanceof Newspaper) {
				$categories = null === $newspaper ? array() : $newspaper->getCategories();
				
				$form->add('categories', 'entity', array(
						'class' => 'AppBundle\Entity\Category',
						'property' => 'name',
						'multiple' => true,
						'expanded' => true,
						'choices' => $categories
				));
			}
		};
		
		$builder->addEventListener(
				FormEvents::PRE_SET_DATA,
				function (FormEvent $event) use ($formModifier) {
					// this would be your entity, i.e. SportMeetup
					$news = $event->getData();
		
					$formModifier($event->getForm(), $news->getNewspaper());
				}
		);
		
		$builder->get('newspaper')->addEventListener(
				FormEvents::POST_SUBMIT,
				function (FormEvent $event) use ($formModifier) {
					// It's important here to fetch $event->getForm()->getData(), as
					// $event->getData() will get you the client data (that is, the ID)
					$newspaper = $event->getForm()->getData();
		
					// since we've added the listener to the child, we'll have to pass on
					// the parent to the callback functions!
					$formModifier($event->getForm()->getParent(), $newspaper);
				}
		);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Entity\News',
				'cascade_validation' => true
		));
	}

	public function getName()
	{
		return 'news';
	}
}