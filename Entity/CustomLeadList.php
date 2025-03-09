<?php

namespace MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Entity;

use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\CommonEntity;
use Doctrine\ORM\Mapping as ORM;
use Mautic\LeadBundle\Entity\LeadList;
use Mautic\LeadBundle\Entity\TagRepository;
use MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Form\Type\CustomLeadListType;

class CustomLeadList extends CommonEntity
{
    public const TABLE_NAME = 'custom_lead_list';

    private LeadList $leadList;

    private $id;

    private bool $excludeUnsubscribed;

    private bool $excludeBounces;

    private bool $excludeContactsNoEmail;


    public static function loadMetadata(ORM\ClassMetadata $metadata): void
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable(self::TABLE_NAME)
            ->setCustomRepositoryClass(CustomLeadListRepository::class);

        $builder->addId();

        $builder->createOneToOne('leadList', LeadList::class)
//            ->inversedBy('customLeadList')
            ->addJoinColumn('leadlist_id', 'id', true, false, 'CASCADE')
            ->build();

//        $builder->addField('exclude_unsubscribed', 'boolean', ['default' => 0]);

//        $builder->createField('excludeUnsubscribed', 'boolean')
//            ->columnName('exclude_unsubscribed')
//            ->build();

        $builder->addField(
            'excludeUnsubscribed',
            'boolean',
            [
                'columnName' => 'exclude_unsubscribed',
                'options'    => [
                    'unsigned' => false,
                ],
            ]
        );

        $builder->addField(
            'excludeBounces',
            'boolean',
            [
                'columnName' => 'exclude_bounces',
                'options'    => [
                    'unsigned' => false,
                ],
            ]
        );

        $builder->addField(
            'excludeContactsNoEmail',
            'boolean',
            [
                'columnName' => 'exclude_contacts_no_email',
                'options'    => [
                    'unsigned' => false,
                ],
            ]
        );


    }

    public function getLeadList(): ?LeadList
    {
        return $this->leadList;
    }

    public function setLeadList(LeadList $leadList): void
    {
        $this->leadList = $leadList;
    }

    public function getExcludeUnsubscribed(): bool
    {
        return $this->excludeUnsubscribed;
    }

    public function setExcludeUnsubscribed(bool $excludeUnsubscribed): void
    {
        $this->excludeUnsubscribed = $excludeUnsubscribed;
    }

    public function getExcludeBounces(): bool
    {
        return $this->excludeBounces;
    }

    public function setExcludeBounces(bool $excludeBounces): void
    {
        $this->excludeBounces = $excludeBounces;
    }

    public function getExcludeContactsNoEmail(): bool
    {
        return $this->excludeContactsNoEmail;
    }

    public function setExcludeContactsNoEmail(bool $excludeContactsNoEmail): void
    {
        $this->excludeContactsNoEmail = $excludeContactsNoEmail;
    }

    public function getId()
    {
        return $this->id;
    }

}