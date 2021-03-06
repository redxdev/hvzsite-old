<?php

namespace AppBundle\Entity;

use AppBundle\Service\GameAuthentication;
use AppBundle\Util\GameUtil;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
 */
class User implements UserInterface, EquatableInterface, \Serializable
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
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="apikey", type="string", length=32)
     */
    private $apiKey;

    /**
     * @var boolean
     *
     * @ORM\Column(name="api_enabled", type="boolean")
     */
    private $apiEnabled;

    /**
     * @var integer
     *
     * @ORM\Column(name="api_fails", type="integer")
     */
    private $apiFails;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_api_fails", type="integer")
     */
    private $maxApiFails;

    /**
     * @var string
     *
     * @ORM\Column(name="fullname", type="string", length=255)
     */
    private $fullname;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="signupDate", type="datetime")
     */
    private $signupDate;

    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="array")
     */
    private $roles;

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
     * @ORM\Column(name="zombieId", type="string", length=8)
     */
    private $zombieId;

    /**
     * @ORM\OneToMany(targetEntity="HumanId", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $humanIds;

    /**
     * @var integer
     *
     * @ORM\Column(name="humansTagged", type="integer")
     */
    private $humansTagged;

    /**
     * @var string
     *
     * @ORM\Column(name="clan", type="string", length=32, nullable=true)
     */
    private $clan;

    /**
     * @var array
     *
     * @ORM\Column(name="badges", type="array")
     */
    private $badges;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar_path", type="string", length=255, nullable=true)
     */
    private $avatarPath;

    /**
     * @var boolean
     *
     * @ORM\Column(name="printed", type="boolean")
     */
    private $printed;

    private $avatarFile;

    public function __construct()
    {
        $this->roles = array("ROLE_USER");
        $this->active = false;
        $this->team = GameUtil::TEAM_HUMAN;
        $this->humansTagged = 0;
        $this->signupDate = new \DateTime();
        $this->clan = "";
        $this->badges = array();
        $this->printed = false;
        $this->apiEnabled = true;
        $this->apiFails = 0;
        $this->maxApiFails = GameAuthentication::DEFAULT_MAX_API_FAILURES;
    }

    public function eraseCredentials()
    {
    }

    public function serialize()
    {
        return serialize(array(
            $this->id
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->id
            ) = unserialize($serialized);
    }

    public function isEqualTo(UserInterface $user)
    {
        if($user instanceof User)
        {
            return $this->id == $user->getId();
        }

        return false;
    }

    public function setUsername($username)
    {
        $this->setEmail($username);
    }

    public function getUsername()
    {
        return $this->getEmail();
    }

    public function setSalt($salt)
    {
        return $this;
    }

    public function getSalt()
    {
        return null;
    }

    public function setPassword($password)
    {
        return $this;
    }

    public function getPassword()
    {
        return null;
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
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set fullname
     *
     * @param string $fullname
     * @return User
     */
    public function setFullname($fullname)
    {
        $this->fullname = $fullname;

        return $this;
    }

    /**
     * Get fullname
     *
     * @return string 
     */
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * Set signupDate
     *
     * @param \DateTime $signupDate
     * @return User
     */
    public function setSignupDate($signupDate)
    {
        $this->signupDate = $signupDate;

        return $this;
    }

    /**
     * Get signupDate
     *
     * @return \DateTime 
     */
    public function getSignupDate()
    {
        return $this->signupDate;
    }

    /**
     * Set roles
     *
     * @param array $roles
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array 
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return User
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
     * @return User
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
     * Set zombieId
     *
     * @param string $zombieId
     * @return User
     */
    public function setZombieId($zombieId)
    {
        $this->zombieId = $zombieId;

        return $this;
    }

    /**
     * Get zombieId
     *
     * @return string 
     */
    public function getZombieId()
    {
        return $this->zombieId;
    }

    /**
     * Set humansTagged
     *
     * @param integer $humansTagged
     * @return User
     */
    public function setHumansTagged($humansTagged)
    {
        $this->humansTagged = $humansTagged;

        return $this;
    }

    /**
     * Get humansTagged
     *
     * @return integer 
     */
    public function getHumansTagged()
    {
        return $this->humansTagged;
    }

    /**
     * Add humanIds
     *
     * @param \AppBundle\Entity\HumanId $humanIds
     * @return User
     */
    public function addHumanId(\AppBundle\Entity\HumanId $humanIds)
    {
        $this->humanIds[] = $humanIds;

        return $this;
    }

    /**
     * Remove humanIds
     *
     * @param \AppBundle\Entity\HumanId $humanIds
     */
    public function removeHumanId(\AppBundle\Entity\HumanId $humanIds)
    {
        $this->humanIds->removeElement($humanIds);
    }

    /**
     * Get humanIds
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHumanIds()
    {
        return $this->humanIds;
    }

    /**
     * Set clan
     *
     * @param string $clan
     * @return User
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
     * @return User
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
     * @return User
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
        return __DIR__ . '/../../../web/' . $this->getAvatarUploadDir();
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
     * Set printed
     *
     * @param boolean $printed
     * @return User
     */
    public function setPrinted($printed)
    {
        $this->printed = $printed;

        return $this;
    }

    /**
     * Get printed
     *
     * @return boolean 
     */
    public function getPrinted()
    {
        return $this->printed;
    }

    /**
     * Set apiKey
     *
     * @param string $apiKey
     * @return User
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string 
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set apiEnabled
     *
     * @param boolean $apiEnabled
     * @return User
     */
    public function setApiEnabled($apiEnabled)
    {
        $this->apiEnabled = $apiEnabled;

        return $this;
    }

    /**
     * Get apiEnabled
     *
     * @return boolean 
     */
    public function getApiEnabled()
    {
        return $this->apiEnabled;
    }

    /**
     * Set apiFails
     *
     * @param integer $apiFails
     * @return User
     */
    public function setApiFails($apiFails)
    {
        $this->apiFails = $apiFails;

        return $this;
    }

    /**
     * Get apiFails
     *
     * @return integer 
     */
    public function getApiFails()
    {
        return $this->apiFails;
    }

    /**
     * Set maxApiFails
     *
     * @param integer $maxApiFails
     * @return User
     */
    public function setMaxApiFails($maxApiFails)
    {
        $this->maxApiFails = $maxApiFails;

        return $this;
    }

    /**
     * Get maxApiFails
     *
     * @return integer 
     */
    public function getMaxApiFails()
    {
        return $this->maxApiFails;
    }
}
