<?php declare(strict_types=1);

namespace Gos\Bundle\WebSocketBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

final class ServerPushHandlerCompilerPass implements CompilerPassInterface
{
    /**
     * @throws InvalidArgumentException if the service tag is missing required attributes
     */
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('gos_web_socket.registry.server_push_handler')) {
            $registryDefinition = $container->getDefinition('gos_web_socket.registry.server_push_handler');

            foreach ($container->findTaggedServiceIds('gos_web_socket.push_handler') as $id => $attributes) {
                if (!isset($attributes[0]['alias'])) {
                    throw new InvalidArgumentException(sprintf('Service "%s" must define the "alias" attribute on "gos_web_socket.push_handler" tags.', $id));
                }

                $pusherDefinition = $container->getDefinition($id);
                $pusherDefinition->addMethodCall('setName', [$attributes[0]['alias']]);

                $registryDefinition->addMethodCall('addPushHandler', [new Reference($id)]);
            }
        }
    }
}
