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

        if($options["show_roles"] === true)
        {
            $builder
                ->add("roles", "collection", [
                    "type" => "choice",
                    "options" => [
                        "choices" => [
                            "ROLE_USER" => "User",
                            "ROLE_MOD" => "Moderator",
                            "ROLE_ADMIN" => "Administrator"
                        ]
                    ]
                ]);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\User',
            'show_roles' => false
        ]);
    }

    public function getName()
    {
        return "user";
    }
}