<?php


namespace App\CompilerPass;


use App\Taxes\TaxCalculator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TaxInterceptorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(TaxCalculator::class)) {
            return;
        }
        $definition = $container->findDefinition(TaxCalculator::class);
        $taggedServices = $container->findTaggedServiceIds('taxes.interceptor');
        foreach ($taggedServices as $id => $tags) {
                $definition->addMethodCall('addInterceptor', [new Reference($id)]);
        }
    }


}