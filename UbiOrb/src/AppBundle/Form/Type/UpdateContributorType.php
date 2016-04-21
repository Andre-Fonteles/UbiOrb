<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Entity\Contributor;

class UpdateContributorType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('role', 'choice', array(
				'choices'  => array(
						Contributor::ROLE_JOURNALIST => 'form.label.journalist',
						Contributor::ROLE_ADMIN => 'form.label.admin'),
		));
		$builder->add('apply', 'submit', array('label' => 'form.label.apply'));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Entity\Contributor'
		));
	}

	public function getName()
	{
		return 'contributor';
	}
}