<?php

namespace Hvz\GameBundle\Services;

class NavigationService
{
	protected $router;
	protected $securityContext;

	public function __construct($router, $securityContext)
	{
		$this->router = $router;
		$this->securityContext = $securityContext;
	}

	public function generate($page_id)
	{
		$navigation = array(
			"links" => array(
				array(
					"id" => "status",
					"icon" => "info",
					"label" => "Status",
					"link" => $this->router->generate('hvz_index')),
				array(
					"id" => "rules",
					"icon" => "list",
					"label" => "Rules",
					"link" => $this->router->generate('hvz_rules')),
				array(
					"id" => "video",
					"icon" => "drive-video",
					"label" => "Video + Maps",
					"link" => $this->router->generate('hvz_video')),
				array(
					"id" => "players",
					"icon" => "filter",
					"label" => "Players",
					"link" => $this->router->generate('hvz_players')),
				array(
					"id" => "tags",
					"icon" => "label",
					"label" => "Tags",
					"link" => $this->router->generate('hvz_tags')),
				array(
					"id" => "register-tag",
					"icon" => "label-outline",
					"label" => "Register Tag",
					"link" => $this->router->generate('hvz_register_tag'))
			),
			"selected" => -1
		);

		if($this->securityContext->isGranted("ROLE_USER"))
		{
			$navigation["links"][] = array(
				"id" => "missions",
				"icon" => "drive-form",
				"label" => "Missions",
				"link" => $this->router->generate('hvz_missions'));
		}

		if($this->securityContext->isGranted("ROLE_MOD"))
		{
			$navigation["admin"] = array();

			$navigation["admin"][] = array(
				"id" => "games",
				"icon" => "",
				"label" => "Games",
				"link" => $this->router->generate('hvz_admin_games')
			);

			$navigation["admin"][] = array(
				"id" => "users",
				"icon" => "",
				"label" => "Users",
				"link" => $this->router->generate('hvz_admin_users')
			);

			$navigation["admin"][] = array(
				"id" => "profiles",
				"icon" => "",
				"label" => "Profiles",
				"link" => $this->router->generate('hvz_admin_profiles')
			);

			$navigation["admin"][] = array(
				"id" => "missions",
				"icon" => "",
				"label" => "Missions",
				"link" => $this->router->generate('hvz_admin_missions')
			);

			if($this->securityContext->isGranted("ROLE_ADMIN"))
			{
				$navigation["admin"][] = array(
					"id" => "rules",
					"icon" => "",
					"label" => "Rules",
					"link" => $this->router->generate('hvz_admin_rules')
				);
			}
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
}
