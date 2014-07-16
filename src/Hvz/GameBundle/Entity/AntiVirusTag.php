<?php

namespace Hvz\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AntiVirusTag
 *
 * @ORM\Table(name="antivirus_tags")
 * @ORM\Entity(repositoryClass="Hvz\GameBundle\Entity\AntiVirusTagRepository")
 */
class AntiVirusTag
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="tag", type="string", length=8)
     */
    private $tag;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    public function __construct($tag)
    {
        $this->tag = $tag;
        $this->active = true;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tag
     *
     * @param string $tag
     * @return AntiVirusTag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return AntiVirusTag
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }
}
