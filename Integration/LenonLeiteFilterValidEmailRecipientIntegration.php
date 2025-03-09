<?php
declare(strict_types=1);

namespace MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Integration;

use Mautic\IntegrationsBundle\Integration\BasicIntegration;
use Mautic\IntegrationsBundle\Integration\ConfigurationTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\BasicInterface;

class LenonLeiteFilterValidEmailRecipientIntegration extends BasicIntegration implements BasicInterface
{
    use ConfigurationTrait;

    public const INTEGRATION_NAME = 'lenonleitefiltervalidemailrecipient';
    public const DISPLAY_NAME     = 'Add options to filter valid email recipients at segment level';

    public function getName(): string
    {
        return self::INTEGRATION_NAME;
    }

    public function getDisplayName(): string
    {
        return self::DISPLAY_NAME;
    }

    public function getIcon(): string
    {
        return 'plugins/LenonLeiteFilterValidEmailRecipientBundle/Assets/img/icon.png';
    }
}