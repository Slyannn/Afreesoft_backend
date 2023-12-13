<?php

namespace App\Form;

use App\Entity\Need;
use App\Entity\OrganismAdmin;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            //Services from Entity Need
            ->add('services', EntityType::class, [
                'label' => 'Add Services',
                'required' => true,
                'class' => Need::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'm-2 form-check form-check-inline',
                ]
            ])

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
            ->add('organismEmail', EmailType::class,[
                'label' => ' ',
                'required' => true,
                'attr' => [
                    'placeholder' =>
                        'Entrez l\' email de votre organisme...',
                    'class' => 'form-control mb-3 ',
                ]
            ])
            ->add('phone', TextType::class,[
                'label' => ' ',
                'required' => true,
                'attr' => [
                    'placeholder' =>
                        'Entrez le numéro de téléphone de votre organisme...',
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
