<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use AppBundle\Entity\News;

class UpdateNewsType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('title', 'text', array('label' => 'form.label.title', 'max_length' => 75));
		$builder->add('resume', 'textarea', array('label' => 'form.label.resume', 'max_length' => 160));
		$builder->add('headline', 'checkbox', array('label' => 'form.label.headline', 'required' => false));
		$builder->add('tags', 'text', array('label' => 'form.label.tags', 'max_length' => 70));
		$builder->add('content', 'textarea', array('label' => 'form.label.content', 
				'attr' => array('style' => 'visibility: hidden; position: absolute')));
		$builder->add('apply', 'submit', array(
				'label' => 'form.label.apply',
				'validation_groups' => array('Default', 'publish')
		));
		
		$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder)
		{
			$form = $event->getForm();
			$data = $event->getData();
		
			/* Check we're looking at the right data/form */
			if ($data instanceof News)
			{
				$newspaper = $data->getNewspaper();
				$categories = null === $newspaper ? array() : $newspaper->getCategories();
				
				$form->add('categories', 'entity', array(
						'class' => 'AppBundle\Entity\Category',
						'property' => 'name',
						'multiple' => true,
						'expanded' => true,
						'choices' => $categories
				));
			}
		});
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Entity\News'
		));
	}

	public function getName()
	{
		return 'news';
	}
}