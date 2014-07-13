<?php

namespace Hvz\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Game
 *
 * @ORM\Table(name="games")
 * @ORM\Entity(repositoryClass="Hvz\GameBundle\Entity\GameRepository")
 */
class Game
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
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime")
     */
    private $endDate;

    /**
     * @ORM\OneToMany(targetEntity="Profile", mappedBy="game", cascade={"remove"}, orphanRemoval=true)
     */
    private $profiles;

    /**
     * @ORM\OneToMany(targetEntity="Mission", mappedBy="game", cascade={"remove"}, orphanRemoval=true)
     */
    private $missions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->profiles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __tostring()
    {
        return $this->getStartDate()->format('Y D M j h:i:s A');
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
     * Add profiles
     *
     * @param \Hvz\GameBundle\Entity\Profile $profiles
     * @return Game
     */
    public function addProfile(\Hvz\GameBundle\Entity\Profile $profiles)
    {
        $this->profiles[] = $profiles;
    
        return $this;
    }

    /**
     * Remove profiles
     *
     * @param \Hvz\GameBundle\Entity\Profile $profiles
     */
    public function removeProfile(\Hvz\GameBundle\Entity\Profile $profiles)
    {
        $this->profiles->removeElement($profiles);
    }

    /**
     * Get profiles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Game
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    
        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Game
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    
        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Add missions
     *
     * @param \Hvz\GameBundle\Entity\Mission $missions
     * @return Game
     */
    public function addMission(\Hvz\GameBundle\Entity\Mission $missions)
    {
        $this->missions[] = $missions;
    
        return $this;
    }

    /**
     * Remove missions
     *
     * @param \Hvz\GameBundle\Entity\Mission $missions
     */
    public function removeMission(\Hvz\GameBundle\Entity\Mission $missions)
    {
        $this->missions->removeElement($missions);
    }

    /**
     * Get missions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMissions()
    {
        return $this->missions;
    }
}