<?php

namespace Hvz\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlayerTag
 *
 * @ORM\Table(name="player_id_tags")
 * @ORM\Entity(repositoryClass="Hvz\GameBundle\Entity\PlayerTagRepository")
 */
class PlayerTag
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
     * @ORM\Column(name="tag", type="string", length=8, unique=true)
     */
    private $tag;

    /**
     * @ORM\ManyToOne(targetEntity="Profile", inversedBy="idTags")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $profile;

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
     * @return PlayerTag
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
     * @return PlayerTag
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

    /**
     * Set profile
     *
     * @param \Hvz\GameBundle\Entity\Profile $profile
     * @return PlayerTag
     */
    public function setProfile(\Hvz\GameBundle\Entity\Profile $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \Hvz\GameBundle\Entity\Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }
}
