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
        $this->activePlugin();

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

//        $form = $crawler->filter('form[name=leadlist]')->form();
//        $form    = $crawler->selectButton('leadlist_buttons_apply')->form();
//        dd($form->getPhpValues(),'AAAA');
//        $data = $form->getPhpValues();
//        $data['custom_leadlist[exclude_unsubscribed]'] = true;
//        $data['custom_leadlist[exclude_bounces]'] = 0;
//        $form->setValues($data);
//        $crawler = $this->client->submit($form);
//        $customLeadListModel = $this->getContainer()->get('mautic.lenonleitefiltervalidemailrecipient.model.customleadlist');
//        assert($customLeadListModel instanceof \MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Model\CustomLeadListModel);
//        $customLeadList = $customLeadListModel->getRepository()->findBy(['leadList' => $segment]);
//        dd($customLeadList);

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
        $this->client->request('GET', '/s/plugins/reload');
        $this->useCleanupRollback = false;
        $this->setUpSymfony($this->configParams);
    }
}