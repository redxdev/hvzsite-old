<?php

namespace AppBundle\Form;

use AppBundle\Util\GameUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", "text")
            ->add("team", "choice", [
                'choices' => [
                    GameUtil::TEAM_HUMAN => 'Human',
                    GameUtil::TEAM_ZOMBIE => 'Zombie'
                ]
            ])
            ->add("postDate", "datetime")
            ->add("body", "textarea");
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Mission'
        ]);
    }

    public function getName()
    {
        return "mission";
    }
}