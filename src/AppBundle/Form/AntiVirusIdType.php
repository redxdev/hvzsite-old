<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AntiVirusIdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("idString", "text", [
                "label" => "Antivirus",
                "read_only" => true,
                "max_length" => 8
            ])
            ->add("description", "textarea", ["required" => false])
            ->add("active", "checkbox", ["required" => false]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\AntiVirusId'
        ]);
    }

    public function getName()
    {
        return "antivirus";
    }
}