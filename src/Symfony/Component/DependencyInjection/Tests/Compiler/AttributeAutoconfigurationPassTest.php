<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Tests\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\AttributeAutoconfigurationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * @requires PHP 8
 */
class AttributeAutoconfigurationPassTest extends TestCase
{
    public function testProcessAddsNoEmptyInstanceofConditionals()
    {
        $container = new ContainerBuilder();
        $container->registerAttributeForAutoconfiguration(AsTaggedItem::class, static function () {});
        $container->register('foo', \stdClass::class)
            ->setAutoconfigured(true)
        ;

        (new AttributeAutoconfigurationPass())->process($container);

        $this->assertSame([], $container->getDefinition('foo')->getInstanceofConditionals());
    }

    public function testAttributeConfiguratorCallableMissingType()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/Parameter "\$reflector" in callable passed to registerAttributeForAutoconfiguration\(\) in .* on line "\d+" should have a type\. Use one or more of the following types: \\\ReflectionClass|\\\ReflectionMethod|\\\ReflectionProperty|\\\ReflectionParameter\./');
        $container = new ContainerBuilder();
        $container->registerAttributeForAutoconfiguration(AsTaggedItem::class, static function (ChildDefinition $definition, AsTaggedItem $attribute, $reflector) {});
        $container->register('foo', \stdClass::class)
            ->setAutoconfigured(true)
        ;

        (new AttributeAutoconfigurationPass())->process($container);
    }

    public function testAttributeConfiguratorCallableReflectorType()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/Parameter "\$reflector" in callable passed to registerAttributeForAutoconfiguration\(\) in .* on line "\d+" cannot be \\\Reflector but should be one of \\\ReflectionClass|\\\ReflectionMethod|\\\ReflectionProperty|\\\ReflectionParameter or a union of multiple\./');
        $container = new ContainerBuilder();
        $container->registerAttributeForAutoconfiguration(AsTaggedItem::class, static function (ChildDefinition $definition, AsTaggedItem $attribute, \Reflector $reflector) {});
        $container->register('foo', \stdClass::class)
            ->setAutoconfigured(true)
        ;

        (new AttributeAutoconfigurationPass())->process($container);
    }
}
