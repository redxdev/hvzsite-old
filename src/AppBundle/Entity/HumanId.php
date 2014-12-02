<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HumanId
 *
 * @ORM\Table(name="human_ids")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\HumanIdRepository")
 */
class HumanId
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
     * @ORM\Column(name="id_string", type="string", length=8)
     */
    private $idString;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="humanIds")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    public function __construct()
    {
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
     * Set active
     *
     * @param boolean $active
     * @return HumanId
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return HumanId
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set idString
     *
     * @param string $idString
     * @return HumanId
     */
    public function setIdString($idString)
    {
        $this->idString = $idString;

        return $this;
    }

    /**
     * Get idString
     *
     * @return string 
     */
    public function getIdString()
    {
        return $this->idString;
    }
}
