<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label'       => 'Nom',
                'constraints' => [new NotBlank()],
                'attr'        => ['class' => 'form-control form-control-sm'],
            ])
            ->add('price', NumberType::class, [
                'label'       => 'Prix',
                'scale'       => 2,
                'constraints' => [new NotBlank(), new Positive()],
                'attr'        => ['class' => 'form-control form-control-sm'],
            ])
            ->add('featured', CheckboxType::class, [
                'label'    => 'Mis en avant',
                'required' => false,
                'attr'     => ['class' => 'form-check-input'],
            ])
            ->add('stockXs', IntegerType::class, [
                'label' => 'Stock XS',
                'attr'  => ['class' => 'form-control form-control-sm', 'min' => 0],
            ])
            ->add('stockS', IntegerType::class, [
                'label' => 'Stock S',
                'attr'  => ['class' => 'form-control form-control-sm', 'min' => 0],
            ])
            ->add('stockM', IntegerType::class, [
                'label' => 'Stock M',
                'attr'  => ['class' => 'form-control form-control-sm', 'min' => 0],
            ])
            ->add('stockL', IntegerType::class, [
                'label' => 'Stock L',
                'attr'  => ['class' => 'form-control form-control-sm', 'min' => 0],
            ])
            ->add('stockXl', IntegerType::class, [
                'label' => 'Stock XL',
                'attr'  => ['class' => 'form-control form-control-sm', 'min' => 0],
            ])
            ->add('imageFile', FileType::class, [
                'label'    => 'Image',
                'mapped'   => false,
                'required' => $options['require_image'],
                'constraints' => $options['require_image'] ? [
                    new File(['mimeTypes' => ['image/jpeg', 'image/png', 'image/webp']]),
                ] : [],
                'attr' => ['class' => 'form-control form-control-sm'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => Product::class,
            'require_image' => false,
        ]);
    }
}
