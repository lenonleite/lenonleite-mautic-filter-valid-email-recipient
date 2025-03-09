<?php
return [
    'name'        => 'Lenon Leite',
    'description' => 'This plugin will give possibility to filter valid email recipients at segment level',
    'version'     => '1.0.0',
    'author'      => 'Lenon Leite',
    'routes' => [
        'main' => [
            'mautic_segment_index' => [
                'path'       => '/segments/{page}',
                'controller' => 'MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Controller\CustomListController::indexAction',
            ],
            'mautic_segment_action' => [
                'path'       => '/segments/{objectAction}/{objectId}',
                'controller' => 'MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Controller\CustomListController::executeAction',
            ],
        ],

    ],
    'services'    => [
        'integrations' => [
            'mautic.integration.lenonleitefiltervalidemailrecipient' => [
                'class' => \MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Integration\LenonLeiteFilterValidEmailRecipientIntegration::class,
                'tags'  => [
                    'mautic.integration',
                    'mautic.basic_integration',
                ],
            ],
            'mautic.integration.lenonleitefiltervalidemailrecipient.configuration' => [
                'class' => \MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Integration\Support\ConfigSupport::class,
                'tags'  => [
                    'mautic.config_integration',
                ],
            ],
            'mautic.integration.lenonleitefiltervalidemailrecipient.config' => [
                'class' => \MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Integration\Config::class,
                'tags'  => [
                    'mautic.integrations.helper',
                ],
                'arguments' => [
                    'mautic.integrations.helper',
                ],
            ],
        ],

//        'forms' => [
//            'mautic.form.type.customlist' => [
//                'class' => \MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Form\Type\CustomListType::class,
//                'tags'  => [
//                    'form.type',
//                ],
//                'arguments' => [
//                    'form.factory',
//                    'request_stack',
//                ],
//            ],
//        ],
//        'other' => [
//            'mautic.lenonleitefiltervalidemailrecipient.twig.extension' => [
//                'class' => \MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Twig\AddFormTypeExtension::class,
//                'tags'  => [
//                    'twig.extension',
//                ],
//                'arguments' => [
//                    'form.factory',
//                    'request',
//                ],
//            ],
//        ]
    ],
];
