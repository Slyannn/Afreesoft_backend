<?php

namespace App\Form;

use App\Entity\Organism;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class OrganismType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('logo', FileType::class, [
        'label' => false,
        'mapped' => false,
        'required' => true,
        'constraints' => [
            new File([
                'mimeTypes' => [
                    'image/jpeg',
                    'image/jpg',
                    'image/jpe',
                    'image/png'

                ],
                'mimeTypesMessage' => 'Please upload a valid Image File',
            ])
        ],])
            ->add('name')
            ->add('description')
            ->add('certificate', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/jpe',
                            'image/png',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image File',
                    ])
                ],])
            ->add('user')
            ->add('services')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Organism::class,
        ]);
    }
}
