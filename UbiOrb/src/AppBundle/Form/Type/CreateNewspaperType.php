<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreateNewspaperType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('name', 'text', array('label' => 'form.label.name', 'max_length' => 30));
		$builder->add('domain', 'text', array('label' => 'form.label.domain', 'max_length' => 30));
		$builder->add('categories', 'collection', array(
				'type' => new CategoryType(),
				'allow_add' => true,
				'allow_delete' => true,
				'by_reference' => false
		));
		
		$builder->add('create', 'submit', array('label' => 'form.label.create'));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Entity\Newspaper',
				'cascade_validation' => true
		));
	}

	public function getName()
	{
		return 'newspaper';
	}
}