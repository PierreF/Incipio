<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\SuiviBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubCcType extends DocTypeType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        DocTypeType::buildForm($builder, $options);
        // aucun champ propre a CC
    }

    public function getName()
    {
        return 'mgate_suivibundle_subcctype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'mgate\SuiviBundle\Entity\Cc',
            'prospect' => '',
        ));
    }
}
