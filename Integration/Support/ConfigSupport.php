<?php

declare(strict_types=1);

namespace MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Integration\Support;

use Mautic\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use MauticPlugin\LenonLeiteFilterValidEmailRecipientBundle\Integration\LenonLeiteFilterValidEmailRecipientIntegration;

class ConfigSupport extends LenonLeiteFilterValidEmailRecipientIntegration implements ConfigFormInterface
{
    use DefaultConfigFormTrait;
}
