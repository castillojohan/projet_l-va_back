<?php

namespace App\Form;

use App\Entity\CaseFolder;
use App\Entity\Platform;
use App\Entity\Reported;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CaseFoldersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextType::class, [
                "empty_data" => ''
            ])
            ->add('platform', EntityType::class, [
                "class" => Platform::class,
                "empty_data" => '',
                "mapped" => false
            ])
            ->add('reported', EntityType::class, [
                "class" => Reported::class,
                "empty_data" => '',
                
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CaseFolder::class,
        ]);
    }
}
