<?php

namespace App\Form;

use App\Entity\Relation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Json;
use function Sodium\add;

class RelationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('data', FileType::class, [
            'label' => 'Subir json',
            'required' => true,
            'mapped' => false,
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'application/json',
                    ],
                    'mimeTypesMessage' => 'Por favor, sube un archivo JSON válido',
                ])
            ]
        ])
        ->add('image', FileType::class, [
            'label' => 'Subir imagen',
            'required' => false,
            'mapped' => false,
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'image/*',
                    ],
                    'mimeTypesMessage' => 'Por favor, sube un archivo de imagen válido',
                ])
            ]
        ]);


            /*
            ->add('title', TextType::class)
            ->add('solutions', ChoiceType::class, [
                'label' => 'Datos en formato JSON',
                'required' => true,
                'attr' => [
                    'rows' => 5,
                ],
                'constraints' => [
                    new Json([
                        'message' => 'El valor ingresado debe ser un JSON válido.',
                    ]),
                ],
                'choices' => ['pepe', 'paco']
            ])
            ->add('other_solutions', TextareaType::class, [
                'label' => 'Otros datos en formato JSON',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                ],
                'constraints' => [
                    new Json([
                        'message' => 'El valor ingresado debe ser un JSON válido.',
                    ]),
                ],
            ])
            ->add('mode', ChoiceType::class, [
                'choices' => [
                    'Completo' => Relation::MODE_COMPLETO,
                    'Individual' => Relation::MODE_INDIVIDUAL,
                ]
            ])
            ->add('image', FileType::class, [
                'label' => 'Subir imagen',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => ['image/*',],
                        'mimeTypesMessage' => 'Por favor, sube un archivo de imagen válido',
                    ])
                ]
            ])
            */
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Relation::class,
        ]);
    }
}
