<?php

namespace MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Factory\ModelFactory;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\UserHelper;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\CoreBundle\Service\FlashBag;
use Mautic\CoreBundle\Translation\Translator;
use Mautic\FormBundle\Helper\FormFieldHelper;
use Mautic\LeadBundle\Controller\ListController;
use Mautic\LeadBundle\Event\LeadListQueryBuilderGeneratedEvent;
use Mautic\LeadBundle\LeadEvents;
use MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Controller\CustomListController;
use MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Model\CustomLeadListModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Mautic\LeadBundle\Segment\Stat\SegmentDependencies;
use Mautic\LeadBundle\Segment\Stat\SegmentCampaignShare;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Mautic\LeadBundle\Event\LeadListEvent;


class LeadListSubscriber implements EventSubscriberInterface
{
    public const FILTER_PRE_LOAD = [
        'exclude_unsubscribed' => [
            "glue" => "and",
            "operator" => "=",
            "properties" => [
              "filter" => "0"
            ],
            "field" => "dnc_unsubscribed",
            "type" => "boolean",
            "object" => "lead",
        ],
        'exclude_bounces' => [
            "glue" => "and",
            "operator" => "=",
            "properties" => [
                "filter" => "0"
            ],
            "field" => "dnc_bounced",
            "type" => "boolean",
            "object" => "lead",
          ],
        'exclude_contacts_no_email' => [
            "glue" => "and",
            "operator" => "!empty",
            "field" => "email",
            "type" => "email",
            "object" => "lead",
        ],
    ];

    public function __construct(
        private CustomListController $customListController,
        private SegmentDependencies $segmentDependencies,
        private SegmentCampaignShare $segmentCampaignShare,
        private RequestStack $requestStack,
        private CustomLeadListModel $customLeadListModel,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
//            KernelEvents::REQUEST  => ['leadListEditShow', 0],
//            LeadEvents::LIST_PRE_SAVE => ['filterPreLoad', 0],
            LeadEvents::LIST_FILTERS_QUERYBUILDER_GENERATED => ['filterPreLoad', 0],
        ];
    }

    public function filterPreLoad(LeadListQueryBuilderGeneratedEvent $event): void
    {
        $segment = $event->getSegment();

        $customFormEntity = $this->customLeadListModel->getRepository()->findOneBy(['leadList' => $segment]);
        if($customFormEntity->getExcludeBounces() || $customFormEntity->getExcludeUnsubscribed() ) {
            $event->getQueryBuilder()->leftJoin(
                'l',
                MAUTIC_TABLE_PREFIX.'lead_donotcontact',
                'dnc',
                'dnc.lead_id = l.id'
            );
        }
        if ($customFormEntity->getExcludeBounces()) {
            $event->getQueryBuilder()->andWhere(
                $event->getQueryBuilder()->expr()->orX(
                    $event->getQueryBuilder()->expr()->isNull('dnc.reason'),
                    $event->getQueryBuilder()->expr()->neq('dnc.reason', 2)
                )
            );
        }

        if ($customFormEntity->getExcludeUnsubscribed()) {
            $event->getQueryBuilder()->andWhere(
                $event->getQueryBuilder()->expr()->orX(
                    $event->getQueryBuilder()->expr()->isNull('dnc.reason'),
                    $event->getQueryBuilder()->expr()->neq('dnc.reason', 1)
                )
            );
        }
        if ($customFormEntity->getExcludeContactsNoEmail()) {
            $event->getQueryBuilder()->andWhere(
                $event->getQueryBuilder()->expr()->isNotNull('l.email')
            );
        }

    }

    public function leadListEditShow(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        $route = $request->attributes->get('_route');
        $objectId = $request->attributes->get('objectId');
        $objectAction = $request->attributes->get('objectAction');
        $objectActionValid = [
            'new',
            'edit',
        ];
//        dd($request->attributes->all());
//        if (
//            $route === 'mautic_segment_action' && $objectAction === 'new'
//        ) {
//            $response = $this->customListController->newAction(
//                $request,
//                $this->segmentDependencies,
//                $this->segmentCampaignShare
//            );
//            $event->setResponse($response);
//        }
//        dump('aaa');
        if (
            $route === 'mautic_segment_action'
            && in_array($objectAction,$objectActionValid)
            && $request->isMethod('POST')
        ) {


        }
    }
}