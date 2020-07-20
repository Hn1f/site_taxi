<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',TextType::class,[
                'label'=>'Titre'
            ])
            ->add('paragraphe', TextareaType::class,[
                'label'=> "Contenu de l'article", 
            ])
            ->add('image',FileType::class,[
                'label'=>'Image', 
                'mapped'=> false, 
                'required'=> false,
                'constraints'=> [
                    new File([
                        'maxSize' => '40000k', 
                        'mimeTypes' => [
                            'image/png', 
                            'image/jpeg',   
                        ],
                        'mimeTypesMessage'=>"Merci d'envoyer une image dans un format jpeg, jpg ou png s'il vous plait",  
                    ])
                ], 
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
