<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
    const TEAM_HUMAN = 0;

    const TEAM_ZOMBIE = 1;

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
    private $numberTagged;

    /**
     * @var string
     *
     * @ORM\Column(name="clan", type="string", length=255)
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
     * @ORM\Column(name="avatar_path", length=255, nullable=true)
     */
    private $avatarPath;

    private $avatarFile;

    public function __construct()
    {
        $this->roles = array("ROLE_USER");
        $this->active = false;
        $this->team = User::TEAM_HUMAN;
        $this->numberTagged = 0;
        $this->signupDate = new \DateTime();
        $this->clan = "";
        $this->badges = array();
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
     * Set numberTagged
     *
     * @param integer $numberTagged
     * @return User
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
}
