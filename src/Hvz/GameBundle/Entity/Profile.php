<?php

namespace Hvz\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Profile
 *
 * @ORM\Table(name="profiles")
 * @ORM\Entity(repositoryClass="Hvz\GameBundle\Entity\ProfileRepository")
 */
class Profile
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="profiles")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="profiles")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $game;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="date")
     */
    private $creationDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var integer
     *
     * @ORM\Column(name="team", type="smallint")
     */
    private $team;

    /**
     * @var string
     *
     * @ORM\Column(name="tagId", type="string", length=8)
     */
    private $tagId;

    /**
     * @ORM\OneToMany(targetEntity="PlayerTag", mappedBy="profile", cascade={"remove"}, orphanRemoval=true)
     */
    private $idTags;

    /**
     * @var integer
     *
     * @ORM\Column(name="numberTagged", type="integer")
     */
    private $numberTagged = 0;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="clan", type="text")
	 */
	private $clan = "";

    /**
     * @var array
     *
     * @ORM\Column(name="badges", type="array")
     */
    private $badges;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar_path", length=255, nullable=true)
     */
    private $avatarPath;

    private $avatarFile;

    /**
     * Constructor
     */
    public function __construct($tag)
    {
        $this->idTags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->active = false;
        $this->team = User::TEAM_HUMAN;
        $this->tagId = $tag;
        $this->numberTagged = 0;
        $this->creationDate = new \DateTime();
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
     * @return Profile
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
     * Set team
     *
     * @param integer $team
     * @return Profile
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
     * Set tagId
     *
     * @param string $tagId
     * @return Profile
     */
    public function setTagId($tagId)
    {
        $this->tagId = $tagId;

        return $this;
    }

    /**
     * Get tagId
     *
     * @return string
     */
    public function getTagId()
    {
        return $this->tagId;
    }

    /**
     * Set numberTagged
     *
     * @param integer $numberTagged
     * @return Profile
     */
    public function setNumberTagged($numberTagged)
    {
        $this->numberTagged = $numberTagged;

        return $this;
    }

    /**
     * Get numberTagged
     *
     * @return integer
     */
    public function getNumberTagged()
    {
        return $this->numberTagged;
    }

    /**
     * Set user
     *
     * @param \Hvz\GameBundle\Entity\User $user
     * @return Profile
     */
    public function setUser(\Hvz\GameBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Hvz\GameBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set game
     *
     * @param \Hvz\GameBundle\Entity\Game $game
     * @return Profile
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
     * Add idTags
     *
     * @param \Hvz\GameBundle\Entity\PlayerTag $idTags
     * @return Profile
     */
    public function addIdTag(\Hvz\GameBundle\Entity\PlayerTag $idTags)
    {
        $this->idTags[] = $idTags;

        return $this;
    }

    /**
     * Remove idTags
     *
     * @param \Hvz\GameBundle\Entity\PlayerTag $idTags
     */
    public function removeIdTag(\Hvz\GameBundle\Entity\PlayerTag $idTags)
    {
        $this->idTags->removeElement($idTags);
    }

    /**
     * Get idTags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIdTags()
    {
        return $this->idTags;
    }

    /**
     * Set clan
     *
     * @param string $clan
     * @return Profile
     */
    public function setClan($clan)
    {
        $this->clan = $clan;

        return $this;
    }

    /**
     * Get clan
     *
     * @return string
     */
    public function getClan()
    {
        return $this->clan;
    }

    /**
     * Set badges
     *
     * @param array $badges
     * @return Profile
     */
    public function setBadges($badges)
    {
        $this->badges = $badges;

        return $this;
    }

    /**
     * Get badges
     *
     * @return array
     */
    public function getBadges()
    {
        return $this->badges;
    }

    /**
     * Set avatarPath
     *
     * @param string $avatarPath
     * @return Profile
     */
    public function setAvatarPath($avatarPath)
    {
        $this->avatarPath = $avatarPath;

        return $this;
    }

    /**
     * Get avatarPath
     *
     * @return string
     */
    public function getAvatarPath()
    {
        return $this->avatarPath;
    }

    public function getAbsoluteAvatarPath()
    {
        return null === $this->avatarPath ? null : $this->getAvatarUploadRootDir() . '/' . $this->avatarPath;
    }

    public function getWebAvatarPath()
    {
        return null === $this->avatarPath ? null : $this->getAvatarUploadDir() . '/' . $this->avatarPath;
    }

    public function getAvatarUploadRootDir()
    {
        return __DIR__ . '/../../../../web/' . $this->getAvatarUploadDir();
    }

    public function getAvatarUploadDir()
    {
        return 'uploads/avatars';
    }

    public function setAvatarFile(UploadedFile $file)
    {
        $this->avatarFile = $file;
    }

    public function getAvatarFile()
    {
        return $this->avatarFile;
    }

    public function uploadAvatar()
    {
        if(null === $this->getAvatarFile())
        {
            return;
        }

        $filename = null;
        while (true) {
            $filename = uniqid('hvz_avatar', true) . '.jpg';
            if(!file_exists($this->getAvatarUploadRootDir() . '/' . $filename))
                break;
        }

        $this->getAvatarFile()->move(
            $this->getAvatarUploadRootDir(),
            $filename
        );

        $this->avatarPath = $filename;

        $this->avatarFile = null;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return Profile
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }
}
