<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street',TextType::class,[
        'label' => ' ',
        'required' => true,
        'attr' => [
            'placeholder' =>
                'Entrez la rue...',
            'class' => 'form-control mb-3 ',
        ]
    ])
            ->add('zipCode',TextType::class,[
                'label' => ' ',
                'required' => true,
                'attr' => [
                    'placeholder' =>
                        'Entrez le code...',
                    'class' => 'form-control mb-3 ',
                ]
            ])
            ->add('city', TextType::class,[
                'label' => ' ',
                'required' => true,
                'attr' => [
                    'placeholder' =>
                        'Entrez la ville...',
                    'class' => 'form-control mb-3 ',
                ]
            ])
            ->add('country', TextType::class,[
                'label' => ' ',
                'required' => true,
                'attr' => [
                    'placeholder' =>
                        'Entrez le pays...',
                    'class' => 'form-control mb-3 ',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => Address::class,
        ]);
    }
}
