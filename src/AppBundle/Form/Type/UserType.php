<?php

namespace AppBundle\Form\Type;

use AppBundle\Util\GameUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options["show_id_info"] === true)
        {
            $builder
                ->add("email", "email", ["required" => true])
                ->add("fullname", "text", ["required" => true, "label" => "Name"]);
        }

        $builder
            ->add("active", "checkbox", ["required" => false])
            ->add("printed", "checkbox", ["required" => false])
            ->add("team", "choice", [
                'choices' => [
                    GameUtil::TEAM_HUMAN => 'Human',
                    GameUtil::TEAM_ZOMBIE => 'Zombie'
                ]
            ])
            ->add("clan", "text", ["required" => false]);

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
                ])
                ->add("apiEnabled", "checkbox", ["required" => false, "label" => "API Key Enabled"])
                ->add("apiFails", "integer", ["required" => false, "label" => "API Failures"])
                ->add("maxApiFails", "integer", ["required" => false, "label" => "Maximum API Failures"]);
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