<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Integration\Internal\Framework\Module\Configuration\Dao;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ModuleConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ShopConfiguration;
use OxidEsales\EshopCommunity\Tests\ContainerTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ModuleConfigurationDaoTest extends TestCase
{
    use ContainerTrait;

    protected function setUp(): void
    {
        $this->prepareProjectConfiguration();

        parent::setUp();
    }

    public function testSaving()
    {
        $moduleConfiguration = new ModuleConfiguration();
        $moduleConfiguration
            ->setId('testId')
            ->setModuleSource('test');

        $dao = $this->get(ModuleConfigurationDaoInterface::class);
        $dao->save($moduleConfiguration, 1);

        $this->assertEquals(
            $moduleConfiguration,
            $dao->get('testId', 1)
        );
    }

    private function prepareProjectConfiguration()
    {
        $this->get(ShopConfigurationDaoInterface::class)->save(
            new ShopConfiguration(),
            1
        );
    }
}
