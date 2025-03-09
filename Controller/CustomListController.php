<?php

namespace MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Controller;


use Doctrine\ORM\EntityNotFoundException;
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
use Mautic\LeadBundle\Entity\LeadList;
use Mautic\LeadBundle\Model\ListModel;
use Mautic\LeadBundle\Security\Permissions\LeadPermissions;
use Mautic\LeadBundle\Segment\Stat\SegmentCampaignShare;
use Mautic\LeadBundle\Segment\Stat\SegmentDependencies;
use MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Integration\Config;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Mautic\FormBundle\Controller\FormController;
use Symfony\Component\HttpFoundation\RequestStack;
use MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Form\Type\CustomLeadListType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Model\CustomLeadListModel;

class CustomListController extends ListController
{
    public function __construct(
        protected FormFactoryInterface     $formFactory,
        protected FormFieldHelper          $fieldHelper,
        private ManagerRegistry            $managerRegistry,
        protected MauticFactory            $factory,
        protected ModelFactory             $modelFactory,
        private UserHelper                 $userHelper,
        protected CoreParametersHelper     $coreParametersHelper,
        protected EventDispatcherInterface $dispatcher,
        protected Translator               $translator,
        private FlashBag                   $flashBag,
        private RequestStack               $requestStack,
        protected ?CorePermissions         $security,
        private Config                     $config,
        private ListModel                  $listModel,
        protected CustomLeadListModel        $customLeadListModel


    )
    {
        parent::__construct(
            $formFactory,
            $fieldHelper,
            $managerRegistry,
            $factory,
            $modelFactory,
            $userHelper,
            $coreParametersHelper,
            $dispatcher,
            $translator,
            $flashBag,
            $requestStack,
            $security
        );
    }

    /**
     * Generate's new form and processes post data.
     *
     * @return JsonResponse|RedirectResponse|Response
     */
    public function newAction(Request $request, SegmentDependencies $segmentDependencies, SegmentCampaignShare $segmentCampaignShare)
    {
        if (!$this->security->isGranted(LeadPermissions::LISTS_CREATE)) {
            return $this->accessDenied();
        }

        // retrieve the entity
        $list = new LeadList();
        /** @var ListModel $model */
        $model = $this->listModel;
        // set the page we came from
        $page = $request->getSession()->get('mautic.segment.page', 1);
        // set the return URL for post actions
        $returnUrl = $this->generateUrl('mautic_segment_index', ['page' => $page]);
        $action    = $this->generateUrl('mautic_segment_action', ['objectAction' => 'new']);

        // get the user form factory
        $form = $model->createForm($list, $this->formFactory, $action);

        // /Check for a submitted form and process it
        if ('POST' === $request->getMethod()) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    // form is valid so process the data
                    $list->setDateModified(new \DateTime());
                    $model->saveEntity($list);
                    $this->saveCustomList($list, $request->get('custom_leadlist'));
                    $this->addFlashMessage('mautic.core.notice.created', [
                        '%name%'      => $list->getName().' ('.$list->getAlias().')',
                        '%menu_link%' => 'mautic_segment_index',
                        '%url%'       => $this->generateUrl('mautic_segment_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $list->getId(),
                        ]),
                    ]);
                }
            }

            if ($cancelled || ($valid && $this->getFormButton($form, ['buttons', 'save'])->isClicked())) {
                return $this->postActionRedirect([
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'Mautic\LeadBundle\Controller\ListController::indexAction',
                    'passthroughVars' => [
                        'activeLink'    => '#mautic_segment_index',
                        'mauticContent' => 'leadlist',
                    ],
                ]);
            } elseif ($valid && !$cancelled) {
                return $this->editAction($request, $segmentDependencies, $segmentCampaignShare, $list->getId(), true);
            }
        }

        return $this->delegateView([
            'viewParameters' => [
                'form' => $form->createView(),
                'customForm' => $this->formFactory->create(CustomLeadListType::class)->createView(),
            ],
            'contentTemplate' => '@MauticLead/List/form.html.twig',
            'passthroughVars' => [
                'activeLink'    => '#mautic_segment_index',
                'route'         => $this->generateUrl('mautic_segment_action', ['objectAction' => 'new']),
                'mauticContent' => 'leadlist',
            ],
        ]);
    }

    public function editAction(Request $request, SegmentDependencies $segmentDependencies, SegmentCampaignShare $segmentCampaignShare, $objectId, $ignorePost = false, bool $isNew = false)
    {
//        if (!$this->config->isPublished()) {
//            return parent::editAction($request, $segmentDependencies, $segmentCampaignShare, $objectId, $ignorePost, $isNew);
//        }

        $postActionVars = $this->getPostActionVars($request, $objectId);

        try {
            $segment = $this->getSegment($objectId, LeadPermissions::LISTS_EDIT_OWN, LeadPermissions::LISTS_EDIT_OTHER);

            if ($isNew) {
                $segment->setNew();
            }

            return $this->createSegmentModifyResponse(
                $request,
                $segment,
                $segmentDependencies,
                $segmentCampaignShare,
                $postActionVars,
                $this->generateUrl('mautic_segment_action', ['objectAction' => 'edit', 'objectId' => $objectId]),
                $ignorePost
            );
        } catch (AccessDeniedException) {
            return $this->accessDenied();
        } catch (EntityNotFoundException) {
            return $this->postActionRedirect(
                array_merge($postActionVars, [
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'mautic.lead.list.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ])
            );
        }


    }

    /**
     * Get variables for POST action.
     *
     * @param int|null $objectId
     */
    private function getPostActionVars(Request $request, $objectId = null): array
    {
        // set the return URL
        if ($objectId) {
            $returnUrl       = $this->generateUrl('mautic_segment_action', ['objectAction' => 'view', 'objectId'=> $objectId]);
            $viewParameters  = ['objectAction' => 'view', 'objectId'=> $objectId];
            $contentTemplate = 'Mautic\LeadBundle\Controller\ListController::viewAction';
        } else {
            // set the page we came from
            $page            = $request->getSession()->get('mautic.segment.page', 1);
            $returnUrl       = $this->generateUrl('mautic_segment_index', ['page' => $page]);
            $viewParameters  = ['page' => $page];
            $contentTemplate = 'Mautic\LeadBundle\Controller\ListController::indexAction';
        }

        return [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => $viewParameters,
            'contentTemplate' => $contentTemplate,
            'passthroughVars' => [
                'activeLink'    => '#mautic_segment_index',
                'mauticContent' => 'leadlist',
            ],
        ];
    }

    /**
     * Return segment if exists and user has access.
     *
     * @throws EntityNotFoundException
     * @throws AccessDeniedException
     */
    private function getSegment(int $segmentId, string $ownPermission, string $otherPermission): LeadList
    {
        $segment = $this->getModel('lead.list')->getEntity($segmentId);

        // Check if exists
        if (!$segment instanceof LeadList) {
            throw new EntityNotFoundException(sprintf('Segment with id %d not found.', $segmentId));
        }

        if (!$this->security->hasEntityAccess(
            $ownPermission, $otherPermission, $segment->getCreatedBy()
        )) {
            throw new AccessDeniedException(sprintf('User has not access on segment with id %d', $segmentId));
        }

        return $segment;
    }

    /**
     * Create modifying response for segments - edit/clone.
     *
     * @param string $action
     * @param bool   $ignorePost
     *
     * @return Response
     */
    private function createSegmentModifyResponse(Request $request, LeadList $segment, SegmentDependencies $segmentDependencies, SegmentCampaignShare $segmentCampaignShare, array $postActionVars, $action, $ignorePost)
    {
        /** @var ListModel $segmentModel */
        $segmentModel = $this->getModel('lead.list');

        if ($segmentModel->isLocked($segment)) {
            return $this->isLocked($postActionVars, $segment, 'lead.list');
        }

        $form = $segmentModel->createForm($segment, $this->formFactory, $action);

        // customization LenonLeiteFilterValidEmailRecipientBundle
        if(!empty($segment)){
            $customFormEntity = $this->customLeadListModel->getRepository()->findOneBy(['leadList' => $segment]);
//            dump('aaaaa');
            if(empty($customFormEntity)){
//                dump('bbbbb');
                $customFormEntity = $this->customLeadListModel->getEntity();
            }
        }else{
//            dump('eeeee');
            $customFormEntity = $this->customLeadListModel->getEntity();
        }

//        dd($customFormEntity,$segment);

//        $customForm = $this->customLeadListModel->createForm($customFormEntity,$request->get('custom_leadlist'));
        $customForm = $this->customLeadListModel->createForm($customFormEntity, $this->formFactory,null);
//        dd($customForm);

        // /Check for a submitted form and process it
        if (!$ignorePost && 'POST' === $request->getMethod()) {
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($this->isFormValid($form)) {
                    // form is valid so process the data
                    $segmentModel->saveEntity($segment, $this->getFormButton($form, ['buttons', 'save'])->isClicked());
                    $this->saveCustomList($segment, $request->get('custom_leadlist'),$customFormEntity);

                    $this->addFlashMessage('mautic.core.notice.updated', [
                        '%name%'      => $segment->getName().' ('.$segment->getAlias().')',
                        '%menu_link%' => 'mautic_segment_index',
                        '%url%'       => $this->generateUrl('mautic_segment_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $segment->getId(),
                        ]),
                    ]);

                    if ($form->get('buttons')->get('apply')->isClicked()) {
                        $contentTemplate                     = '@MauticLead/List/form.html.twig';
                        $postActionVars['contentTemplate']   = $contentTemplate;
                        $postActionVars['forwardController'] = false;
                        $postActionVars['returnUrl']         = $this->generateUrl('mautic_segment_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $segment->getId(),
                        ]);

                        $form = $segmentModel->createForm($segment, $this->formFactory, $postActionVars['returnUrl']);

                        $postActionVars['viewParameters'] = [
                            'objectAction' => 'edit',
                            'objectId'     => $segment->getId(),
                            'form'         => $form->createView(),
                            'customForm' => $customForm->createView(),
                        ];

                        return $this->postActionRedirect($postActionVars);
                    } else {
                        return $this->viewAction($request, $segmentDependencies, $segmentCampaignShare, $segment->getId());
                    }
                }
            } else {
                // unlock the entity
                $segmentModel->unlockEntity($segment);
            }

            if ($cancelled) {
                return $this->postActionRedirect($postActionVars);
            }
        } else {
            // lock the entity
            $segmentModel->lockEntity($segment);
        }

        return $this->delegateView([
            'viewParameters' => [
                'form'          => $form->createView(),
                'customForm' => $customForm->createView(),
                'currentListId' => $segment->getId(),
            ],
            'contentTemplate' => '@MauticLead/List/form.html.twig',
            'passthroughVars' => [
                'activeLink'    => '#mautic_segment_index',
                'route'         => $action,
                'mauticContent' => 'leadlist',
            ],
        ]);
    }

    private function saveCustomList($list, $request,$customFormEntity=null): void
    {
        if (empty($list)){
            return;
        }

        if(empty($customFormEntity)){
            $customListEntity = $this->customLeadListModel->getRepository()->findOneBy(['leadList' => $list->getId()]);
            if (empty($customListEntity)) {
                $customListEntity = $this->customLeadListModel->getEntity();
            }
        }else{
            $customListEntity = $customFormEntity;
        }

        $customListEntity->setExcludeUnsubscribed($request['exclude_unsubscribed']??false);
        $customListEntity->setExcludeBounces($request['exclude_bounces']??false);
        $customListEntity->setExcludeContactsNoEmail($request['exclude_contacts_no_email']??false);
        if(!empty($list)){
            $customListEntity->setLeadList($list);
        }
//        dd($customListEntity);
//        dd($customListEntity);
//        $form = $this->customLeadListModel->createForm($customListEntity, $this->formFactory);
//        $form = $this->formFactory->create(CustomLeadListType::class,$request);
//        $isValid = $this->isFormValid($form);
//        if (!$isValid) {
//            return;
//        }

//        dd($customListEntity);
//        $this->customLeadListModel->getRepository()->saveEntity($customListEntity);
        $this->customLeadListModel->saveEntity($customListEntity);
    }

    private function loadCustomList($list,$data)
    {
        $customListEntity = $this->customLeadListModel->getRepository()->findOneBy(['leadList' => $list->getId()]);
        if (!$customListEntity) {
            return;
        }
        $data['custom_leadlist'] = [
            'exclude_unsubscribed' => $customListEntity->getExcludeUnsubscribed(),
            'exclude_bounces' => $customListEntity->getExcludeBounces(),
            'exclude_contacts_no_email' => $customListEntity->getExcludeContactsNoEmail(),
        ];
        return $data;

    }

}