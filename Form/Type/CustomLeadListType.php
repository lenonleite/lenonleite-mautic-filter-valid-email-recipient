<?php

namespace MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Mautic\LeadBundle\Form\Type\ListType;
use Mautic\LeadBundle\Model\ListModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\EventListener\LeadListSubscriber;

class CustomLeadListType extends AbstractType
{
    public function __construct(
        protected FormFactoryInterface $formFactory,
        private RequestStack $requestStack,
        private ListModel $leadList
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

//        dump($this->requestStack->getCurrentRequest()->attributes->get('objectId'));
//        $listId = $this->requestStack->getCurrentRequest()->attributes->get('objectId');
//        if ($listId) {
//            $leadList = $this->leadList->getEntity($listId);
//            $filters = $leadList->getFilters();
//            foreach ($filters as $filter) {
//                foreach (LeadListSubscriber::FILTER_PRE_LOAD as $key =>$preLoadFilter) {
//                    unset($preLoadFilter['properties']);
//                    if (array_diff_assoc($preLoadFilter,$filter) == []) {
//                        $options['data'][$key] = true;
//                    }
//                }
//            }
//        }

//        dd($options);
//        dump($options['data'],(isset($options['data']['exclude_unsubscribed'])) ? $options['data']['exclude_unsubscribed'] : false);
        $builder->add(
            'exclude_unsubscribed',
            YesNoButtonGroupType::class,
            [
                'label'      => 'mautic.lead.list.form.exclude_unsubscribed',
                'attr'       => [
                    'tooltip' => 'mautic.lead.list.form.exclude_unsubscribed.tooltip',
                ],
//                'data'  => (!isset($options['data']['exclude_unsubscribed'])) ? false : $options['data']['exclude_unsubscribed'],
            ]
        );

        $builder->add(
            'exclude_bounces',
            YesNoButtonGroupType::class,
            [
                'label'      => 'mautic.lead.list.form.exclude_bounces',
                'attr'       => [
                    'tooltip' => 'mautic.lead.list.form.exclude_bounces.tooltip',
                ],
//                'data'  => (!isset($options['data']['exclude_bounces'])) ? false : $options['data']['exclude_bounces'],
            ]
        );

        $builder->add(
            'exclude_contacts_no_email',
            YesNoButtonGroupType::class,
            [
                'label'      => 'mautic.lead.list.form.exclude_contacts_no_email',
                'attr'       => [
                    'tooltip' => 'mautic.lead.list.form.exclude_contacts_no_email.tooltip',
                ],
//                'data'  => (!isset($options['data']['exclude_contacts_no_email'])) ? false : $options['data']['exclude_contacts_no_email'],
            ]
        );

    }

//    public function configureOptions(OptionsResolver $resolver): void
//    {
//        $resolver->setDefaults([
//            'data_class' => CustomListType::class,
//        ]);
//    }

//    public function getParent(): string
//    {
//        // Return the original form type class
//        return ListType::class;
//    }

    public function getBlockPrefix()
    {
        return 'custom_leadlist';
    }

}