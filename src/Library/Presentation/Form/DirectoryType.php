<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Form;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class DirectoryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('exclude_directories', []);
        $resolver->setAllowedTypes('exclude_directories', Directory::class . '[]');
    }

    /** @inheritDoc */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'title',
            TextType::class,
            [
                'label' => 'Titel',
                'translation_domain' => false,
                'constraints' => [new NotBlank()],
            ],
        );

        $builder->add(
            'parent',
            DirectoryChoiceType::class,
            [
                'label' => 'In Verzeichnis ...',
                'exclude_directories' => $options['exclude_directories'],
                'translation_domain' => false,
                'constraints' => [new NotNull()],
            ],
        );
    }
}
