<?php
// src/Hvz/GameBundle/DependencyInjection/Security/Factory/GoogleOAuthFactory.php
namespace Hvz\GameBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

class GoogleOAuthFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.google_oauth.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('google_oauth.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
        ;

        $listenerId = 'security.authentication.listener.google_oauth.'.$id;
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('google_oauth.security.authentication.listener'));

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'google_oauth';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
}