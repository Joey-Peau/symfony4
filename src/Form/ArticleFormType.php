<?php

namespace App\Form;


use App\Entity\Article;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleFormType extends AbstractType
{


    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Article|null $article */
        $article = $options['data'] ?? null;
        $isEdit = $article && $article->getId();
        $location = $article ? $article->getLocation() : null;
        $builder
            ->add(
                'title',
                TextType::class,
                [
                    'help' => "Choose something catchty!",
                ]
            )
            ->add(
                'content',
                null,
                [
                    'rows' => 15,
                ]
            )
            ->add(
                'author',
                UserSelectTextType::class,
                [
                    'disabled' => $isEdit,
                ]
            )
            ->add(
                'location',
                ChoiceType::class,
                [
                    'placeholder' => 'Choose a location',
                    'choices'     => [
                        'The Solar System'   => 'solar_system',
                        'Near a star'        => 'star',
                        'Interstellar Space' => 'interstellar_space',
                    ],
                    "required"    => false,
                ]
            );
        if ($location) {
            $builder->add(
                'specificLocationName',
                ChoiceType::class,
                [
                    'placeholder' => 'Where exactly?',
                    'choices'     => $this->getLocationNameChoices($location),
                    'required'    => false,
                ]
            );
        }

        if ($options['include_published_at']) {
            $builder->add(
                'publishedAt',
                null,
                [
                    'widget' => 'single_text',
                ]
            );
        }

        $builder->get("location")->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $this->setupSpecificLocationNameField(
                    $form->getParent(),
                    $form->getData()
                );
            }
        );
    }

    private function getLocationNameChoices(string $location)
    {
        $planets = [
            'Mercury',
            'Venus',
            'Earth',
            'Mars',
            'Jupiter',
            'Saturn',
            'Uranus',
            'Neptune',
        ];
        $stars = [
            'Polaris',
            'Sirius',
            'Alpha Centauari A',
            'Alpha Centauari B',
            'Betelgeuse',
            'Rigel',
            'Other',
        ];
        $locationNameChoices = [
            'solar_system'       => array_combine($planets, $planets),
            'star'               => array_combine($stars, $stars),
            'interstellar_space' => null,
        ];

        return $locationNameChoices[$location];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'           => Article::class,
                'include_published_at' => false,
            ]
        );
    }

    private function setupSpecificLocationNameField(FormInterface $form, ?string $location)
    {
        if (null === $location) {
            $form->remove('specificLocationName');

            return;
        }
        $choices = $this->getLocationNameChoices($location);

        if (null === $choices) {
            $form->remove('specificLocationName');

            return;
        }

        $form->add(
            'specificLocationName',
            ChoiceType::class,
            [
                'placeholder' => 'Where exactly?',
                'choices'     => $choices,
                'required'    => false,
            ]
        );
    }


}