<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfectionSpread
 *
 * @ORM\Table(name="infection_spread")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\InfectionSpreadRepository")
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
     * @var bool
     *
     * @ORM\Column(name="hasLocation", type="boolean")
     */
    private $hasLocation = false;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float")
     */
    private $latitude = null;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float")
     */
    private $longitude = null;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="zombie", referencedColumnId="id", onDelete="CASCADE")
     */
    private $zombie;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="human", referencedColumnId="id", onDelete="CASCADE")
     */
    private $human;

    /**
     * @var array
     *
     * @ORM\Column(name="killstreaks", type="array")
     */
    private $killstreaks = array();

    public function __construct()
    {
        $this->time = new \DateTime();
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

    /**
     * Set killstreaks
     *
     * @param array $killstreaks
     * @return InfectionSpread
     */
    public function setKillstreaks($killstreaks)
    {
        $this->killstreaks = $killstreaks;

        return $this;
    }

    /**
     * Get killstreaks
     *
     * @return array 
     */
    public function getKillstreaks()
    {
        return $this->killstreaks;
    }
}
