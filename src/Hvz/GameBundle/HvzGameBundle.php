<?php

namespace Hvz\GameBundle;

use Hvz\GameBundle\DependencyInjection\Security\Factory\GoogleOAuthFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class HvzGameBundle extends Bundle
{
	// TODO: Move to service
	public static function generateNavigation($page_id, $router, $securityContext)
	{
		$navigation = array(
			"links" => array(
				array("id" => "status", "icon" => "info", "label" => "Status", "link" => $router->generate('hvz_index')),
				array("id" => "rules", "icon" => "list", "label" => "Rules", "link" => $router->generate('hvz_rules')),
				array("id" => "players", "icon" => "filter", "label" => "Players", "link" => $router->generate('hvz_players')),
				array("id" => "tags", "icon" => "label", "label" => "Tags", "link" => $router->generate('hvz_tags')),
				array("id" => "register-tag", "icon" => "label-outline", "label" => "Register Tag", "link" => $router->generate('hvz_register_tag'))
			),
			"selected" => -1
		);

		if($securityContext->isGranted("ROLE_USER"))
		{
			$navigation["links"][] = array("id" => "missions", "icon" => "drive-form", "label" => "Missions", "link" => $router->generate('hvz_missions'));
		}

		if($securityContext->isGranted("ROLE_ADMIN"))
		{
			$navigation["admin"] = array(
				array("id" => "games", "icon" => "", "label" => "Games", "link" => $router->generate('hvz_admin_games')),
				array("id" => "users", "icon" => "", "label" => "Users", "link" => $router->generate('hvz_admin_users')),
				array("id" => "profiles", "icon" => "", "label" => "Profiles", "link" => $router->generate('hvz_admin_profiles')),
				array("id" => "missions", "icon" => "", "label" => "Missions", "link" => $router->generate('hvz_admin_missions')),
				array("id" => "rules", "icon" => "", "label" => "Rules", "link" => $router->generate('hvz_admin_rules'))
			);
		}

		foreach($navigation["links"] as $k => $v)
		{
			if($v["id"] == $page_id) {
				$navigation["selected"] = $k;
				break;
			}
		}

		return $navigation;
	}

	public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new GoogleOAuthFactory());
    }
}
