<?php

namespace Hvz\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mission
 *
 * @ORM\Table(name="missions")
 * @ORM\Entity(repositoryClass="Hvz\GameBundle\Entity\MissionRepository")
 */
class Mission
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
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text", nullable=true)
     */
    private $body;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="postdate", type="datetime")
     */
    private $postdate;

    /**
     * @var integer
     *
     * @ORM\Column(name="team", type="smallint")
     */
    private $team;

    /**
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="missions")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $game;

    public function __construct()
    {
        $this->postdate = date_create("now");
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
     * Set title
     *
     * @param string $title
     * @return Mission
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Mission
     */
    public function setBody($body)
    {
        $this->body = $body;
    
        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set postdate
     *
     * @param \DateTime $postdate
     * @return Mission
     */
    public function setPostdate($postdate)
    {
        $this->postdate = $postdate;
    
        return $this;
    }

    /**
     * Get postdate
     *
     * @return \DateTime 
     */
    public function getPostdate()
    {
        return $this->postdate;
    }

    /**
     * Set team
     *
     * @param integer $team
     * @return Mission
     */
    public function setTeam($team)
    {
        $this->team = $team;
    
        return $this;
    }

    /**
     * Get team
     *
     * @return integer 
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set game
     *
     * @param \Hvz\GameBundle\Entity\Game $game
     * @return Mission
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
}