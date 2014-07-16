<?php

namespace Hvz\GameBundle\Services;

class TagGeneratorService
{
	protected $entityManager;

	public function __construct($entityManager)
	{
		$this->entityManager = $entityManager;
	}

	public function generate()
	{
		$str = '';
		$charset = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$count = strlen($charset) - 1;

		for($i = 0; $i < 8; $i++)
		{
			$str .= $charset[mt_rand(0, $count)];
		}

		while($this->isDuplicateTagId($str)
			|| $this->isDuplicatePlayerTag($str)
			|| $this->isDuplicateAntiVirusTag($str))
		{
			$str = '';

			for($i = 0; $i < 8; $i++)
			{
				$str .= $charset[mt_rand(0, $count)];
			}
		}

		return $str;
	}

	protected function isDuplicateTagId($id)
	{
		$query = $this->entityManager->createQueryBuilder()
			->select("count(tag.id)")
			->from("HvzGameBundle:PlayerTag", 'tag')
			->where("tag.tag = :t")
			->setParameter("t", $id)
			->getQuery();

		return $query->getSingleScalarResult() != 0;
	}

	protected function isDuplicatePlayerTag($id)
	{
		$query = $this->entityManager->createQueryBuilder()
			->select("count(profile.id)")
			->from("HvzGameBundle:Profile", "profile")
			->where("profile.tagId = :t")
			->setParameter("t", $id)
			->getQuery();

		return $query->getSingleScalarResult() != 0;
	}

	protected function isDuplicateAntiVirusTag($id)
	{
		return false; //TODO
	}
}
