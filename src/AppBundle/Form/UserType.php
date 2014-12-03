<?php

namespace AppBundle\Form;

use AppBundle\Util\GameUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("active", "checkbox")
            ->add("team", "choice", [
                'choices' => [
                    GameUtil::TEAM_HUMAN => 'Human',
                    GameUtil::TEAM_ZOMBIE => 'Zombie'
                ]
            ])
            ->add("clan", "text");
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\User'
        ]);
    }

    public function getName()
    {
        return "user";
    }
}