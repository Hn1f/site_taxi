<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class,[
                'attr'=>['placeholder'=>' Entrez votre adresse email']
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent être identique.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe', 'attr' =>['placeholder'=>'Entrez votre mot de passe']],
                'second_options' => ['label' => 'Confirmer mot de passe','attr' =>['placeholder'=>'Confirmez votre mot de passe']],
                
            ])
            ->add('firstname',TextType::class,[
            'label'=>'Prenom',
            'attr'=>['placeholder'=>'Entrez votre prénom'] 
        ])
            ->add('lastName',TextType::class,[
            'label'=>'Nom',
            'attr'=>['placeholder'=>'Entrez votre nom'] 
        ])
            ->add('adresse',TextType::class,[
                'label'=>'adresse',
                'attr'=>['placeholder'=>'Entrez votre adresse']
            ])
            ->add('zipCode',TextType::class,[
                'label'=>'Code postale',
                'attr'=>['placeholder'=>'Entrez votre code postale'] 
            ])
            ->add('phone',Teltype::class,[
                'label'=>'Téléphone',
                'attr'=>['placeholder'=>'Entrez votre numéro de téléphone'] 
            ])
            ->add('birth', BirthdayType::class,[
                'label'=>'Date de naissance',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
