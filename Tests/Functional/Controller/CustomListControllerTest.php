<?php

namespace MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Tests\Functional\Controller;

use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Mautic\LeadBundle\Entity\LeadList;
use Mautic\PluginBundle\Entity\Integration;
use Mautic\PluginBundle\Entity\Plugin;
use MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Integration\LenonLeiteFilterValidEmailRecipientIntegration;

class CustomListControllerTest extends MauticMysqlTestCase
{

    public function setUp(): void
    {
        parent::setUp();

    }

    public function testNewSegmentActionWithoutActive(): void
    {
        $this->activePlugin(false);
        $this->client->request('GET', '/s/segments/new');
        $this->assertStringNotContainsString('Exclude Unsubscribed', $this->client->getResponse()->getContent());
    }

    public function testNewSegmentActionWithActive(): void
    {
        $this->activePlugin();
        $this->client->request('GET', '/s/segments/new');
        $this->assertStringContainsString('Exclude Unsubscribed', $this->client->getResponse()->getContent());
    }

    public function testNewSegmentAndEditActionWithActive(): void
    {

        $this->activePlugin();
        $filter = [[
            'glue'     => 'and',
            'field'    => 'email',
            'object'   => 'lead',
            'type'     => 'email',
            'operator' => '!empty',
            'display'  => '',
        ]];

        $segment = $this->saveSegment('Test', 'test', $filter);
        $crawler = $this->client->request('GET', '/s/segments/edit/' . $segment->getId());
        $this->assertStringContainsString('Exclude Unsubscribed', $this->client->getResponse()->getContent());

    }

    private function saveSegment(string $name, string $alias, array $filters = [], LeadList $segment = null): LeadList
    {
        $segment ??= new LeadList();
        $segment->setName($name)->setAlias($alias)->setFilters($filters);
        $listModel = $this->getContainer()->get('mautic.lead.model.list');
        $listModel->saveEntity($segment);

        return $segment;
    }


    public function activePlugin($isPublished = true): void
    {
        $this->client->request('GET', '/s/plugins/reload');
        $integration = $this->em->getRepository(Integration::class)->findOneBy(['name' => LenonLeiteFilterValidEmailRecipientIntegration::INTEGRATION_NAME]);
        if (empty($integration)) {
            $plugin      = $this->em->getRepository(Plugin::class)->findOneBy(['bundle' => 'LenonLeiteFilterValidEmailRecipientBundle']);
            $integration = new Integration();
            $integration->setName('LenonLeiteFilterValidEmailRecipient');
            $integration->setPlugin($plugin);
        }
        $integration->setIsPublished($isPublished);
        $this->em->persist($integration);
        $this->em->flush();

        $this->useCleanupRollback = false;
        $this->setUpSymfony($this->configParams);
    }
}