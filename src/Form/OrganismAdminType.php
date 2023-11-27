<?php

namespace App\Form;

use App\Entity\OrganismAdmin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class OrganismAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('logo', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2000k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/jpe',
                            'image/png'

                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image File',
                    ])
                ],
                //class
                'attr' => [
                    'class' => 'form-control mb-3 '
                    ]
            ])
            ->add('name', TextType::class,[
                'label' => ' ',
                'required' => true,
                'attr' => [
                    'placeholder' =>
                        'Entrez le nom de l\'organisme...',
                    'class' => 'form-control mb-3 ',
                ]
            ])
            ->add('email', EmailType::class,[
                'label' => ' ',
                'required' => true,
                'attr' => [
                    'placeholder' =>
                        'Entrez l\' email...',
                    'class' => 'form-control mb-3 ',
                ]
            ])
            ->add('description',  TextareaType::class, [
                'label' => false,
                'required' => false,
                'constraints' => [new Length(['min' => 2])],
                'attr' => [
                    'class' => 'form-control mb-3',
                    'rows' => '5',
                    'placeholder' => 'Entrez une description...'
                ]
            ])
            ->add('address', AddressType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            //add Address fpr OrganismAdminType
            'data_class' => OrganismAdmin::class,

        ]);
    }
}
