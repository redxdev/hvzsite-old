<?php

namespace Hvz\GameBundle;

use Hvz\GameBundle\DependencyInjection\Security\Factory\GoogleOAuthFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class HvzGameBundle extends Bundle
{
	public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new GoogleOAuthFactory());
    }
}
