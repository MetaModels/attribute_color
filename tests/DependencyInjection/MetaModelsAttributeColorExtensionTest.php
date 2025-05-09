<?php

/**
 * This file is part of MetaModels/attribute_color.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_color
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_color/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeColorBundle\Test\DependencyInjection;

use MetaModels\AttributeColorBundle\Attribute\AttributeTypeFactory;
use MetaModels\AttributeColorBundle\DependencyInjection\MetaModelsAttributeColorExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * This test case test the extension.
 *
 * @covers \MetaModels\AttributeColorBundle\DependencyInjection\MetaModelsAttributeColorExtension
 */
class MetaModelsAttributeColorExtensionTest extends TestCase
{
    /**
     * Test that extension can be instantiated.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $extension = new MetaModelsAttributeColorExtension();

        $this->assertInstanceOf(MetaModelsAttributeColorExtension::class, $extension);
        $this->assertInstanceOf(ExtensionInterface::class, $extension);
    }

    /**
     * Test that the services are loaded.
     *
     * @return void
     */
    public function testFactoryIsRegistered(): void
    {
        $container = new ContainerBuilder();

        $extension = new MetaModelsAttributeColorExtension();
        $extension->load([], $container);

        self::assertTrue($container->hasDefinition('metamodels.attribute_color.factory'));
        $definition = $container->getDefinition('metamodels.attribute_color.factory');
        self::assertCount(1, $definition->getTag('metamodels.attribute_factory'));
    }
}
