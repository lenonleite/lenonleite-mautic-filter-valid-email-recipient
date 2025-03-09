<?php

namespace MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Model;

use Mautic\ApiBundle\Entity\oAuth2\Client;
use Mautic\ApiBundle\Form\Type\ClientType;
use Mautic\CoreBundle\Model\FormModel;
use Mautic\LeadBundle\Entity\Company;
use MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Entity\CustomLeadList;
use MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Form\Type\CustomLeadListType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class CustomLeadListModel extends FormModel
{

    public function getRepository()
    {
        return $this->em->getRepository(CustomLeadList::class);
    }

    /**
     * @throws MethodNotAllowedHttpException
     */
    public function createForm($entity, FormFactoryInterface $formFactory, $action = null, $options = []): \Symfony\Component\Form\FormInterface
    {
        if (!$entity instanceof CustomLeadList) {
            throw new MethodNotAllowedHttpException(['CustomLeadList']);
        }

        $params['action'] = '';

        return $formFactory->create(CustomLeadListType::class, $entity, $params);
    }

    public function getEntity($id = null): CustomLeadList
    {
        if (null === $id) {
            return new CustomLeadList();
        }

        return parent::getEntity($id);
    }


}