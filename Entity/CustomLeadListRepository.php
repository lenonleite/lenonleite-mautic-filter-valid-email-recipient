<?php

namespace MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;
use Mautic\FormBundle\Entity\FormRepository;

class CustomLeadListRepository extends FormRepository
{
    public function getLeadListIds(): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c.id')
            ->from(CustomLeadList::class, 'c');

        return array_column($qb->getQuery()->getArrayResult(), 'id');
    }
}