<?php

namespace MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\EventListener;

use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CustomFormSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

//        $otherFormData = $form->getParent()->get('leadlist')->getData();
        $otherFormData = $form->getParent();
//        dd($otherFormData);

        $form->add('exclude_unsubscribed', YesNoButtonGroupType::class, [
            'label' => 'mautic.lead.list.form.exclude_unsubscribed',
            'attr' => [
                'tooltip' => 'mautic.lead.list.form.exclude_unsubscribed.tooltip',
            ],
//            'data' => (!isset($data['exclude_unsubscribed'])) ? false : $data['exclude_unsubscribed'],
        ]);
        $form->add('exclude_bounces', YesNoButtonGroupType::class, [
            'label' => 'mautic.lead.list.form.exclude_bounces',
            'attr' => [
                'tooltip' => 'mautic.lead.list.form.exclude_bounces.tooltip',
            ],
//            'data' => (!isset($data['exclude_bounces'])) ? false : $data['exclude_bounces'],
        ]);
        $form->add('exclude_contacts_no_email', YesNoButtonGroupType::class, [
            'label' => 'mautic.lead.list.form.exclude_contacts_no_email',
            'attr' => [
                'tooltip' => 'mautic.lead.list.form.exclude_contacts_no_email.tooltip',
            ],
//            'data' => (!isset($data['exclude_contacts_no_email'])) ? false : $data['exclude_contacts_no_email'],
        ]);
    }
}