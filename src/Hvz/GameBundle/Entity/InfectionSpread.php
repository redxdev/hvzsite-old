<?php

namespace Hvz\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfectionSpread
 *
 * @ORM\Table(name="infections")
 * @ORM\Entity(repositoryClass="Hvz\GameBundle\Entity\InfectionSpreadRepository")
 */
class InfectionSpread
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
     * @ORM\Column(name="time", type="datetime")
     */
    private $time;

    /**
     * @ORM\ManyToOne(targetEntity="Profile")
     * @ORM\JoinColumn(name="zombie_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $zombie;

	/**
	 * @var float
	 *
	 * @ORM\Column(name="latitude", type="float", nullable=true)
	 */
	private $latitude = -1;

	/**
	 * @var float
	 *
	 * @ORM\Column(name="longitude", type="float", nullable=true)
	 */
	private $longitude = -1;

    /**
     * @ORM\ManyToOne(targetEntity="Profile")
     * @ORM\JoinColumn(name="victim_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $victim;

    /**
     * @ORM\ManyToOne(targetEntity="Game")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $game;

    public function __construct()
    {
        $this->time = date_create("now");
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
     * Set time
     *
     * @param \DateTime $time
     * @return InfectionSpread
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set zombie
     *
     * @param \Hvz\GameBundle\Entity\user $zombie
     * @return InfectionSpread
     */
    public function setZombie(\Hvz\GameBundle\Entity\Profile $zombie = null)
    {
        $this->zombie = $zombie;

        return $this;
    }

    /**
     * Get zombie
     *
     * @return \Hvz\GameBundle\Entity\user
     */
    public function getZombie()
    {
        return $this->zombie;
    }

    /**
     * Set victim
     *
     * @param \Hvz\GameBundle\Entity\user $victim
     * @return InfectionSpread
     */
    public function setVictim(\Hvz\GameBundle\Entity\Profile $victim = null)
    {
        $this->victim = $victim;

        return $this;
    }

    /**
     * Get victim
     *
     * @return \Hvz\GameBundle\Entity\user
     */
    public function getVictim()
    {
        return $this->victim;
    }

    /**
     * Set game
     *
     * @param \Hvz\GameBundle\Entity\Game $game
     * @return InfectionSpread
     */
    public function setGame(\Hvz\GameBundle\Entity\Game $game = null)
    {
        $this->game = $game;

        return $this;
    }

    /**
     * Get game
     *
     * @return \Hvz\GameBundle\Entity\Game
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     * @return InfectionSpread
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     * @return InfectionSpread
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
}
