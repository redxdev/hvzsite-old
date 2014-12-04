<?php

namespace AppBundle\Form;

use AppBundle\Util\GameUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RulesetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", "text")
            ->add("position", "integer")
            ->add("body", "textarea");
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Ruleset'
        ]);
    }

    public function getName()
    {
        return "ruleset";
    }
}