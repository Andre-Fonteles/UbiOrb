<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChangePasswordType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('oldPassword', 'password', array('label' => 'form.label.currentPassword', 'max_length' => 255));
		$builder->add('newPassword', 'repeated', array(
				'first_name'  => 'first',
				'second_name' => 'second',
				'type' 		  => 'password',
				'first_options'  => array('label' => 'form.label.newPassword', 'max_length' => 255),
				'second_options' => array('label' => 'form.label.repeatNewPassword', 'max_length' => 255),
				'invalid_message' => 'not.match.password'
		));
		$builder->add('change', 'submit', array('label' => 'label.changePassword'));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Entity\ChangePassword'
		));
	}

	public function getName()
	{
		return 'change_password';
	}
}