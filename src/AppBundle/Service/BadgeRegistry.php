<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

use AppBundle\Entity\User;

class BadgeRegistry
{
    private $entityManager;

    private $registry = [];

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        $this->registerBadges();
    }

    public function addBadge(User $user, $badgeId, $flush = true)
    {
        if(!$this->badgeExists($badgeId))
        {
            throw new \InvalidArgumentException("Unknown badge id " . $badgeId);
        }

        $badges = $user->getBadges();
        $badges[] = $badgeId;
        $user->setBadges($badges);

        if($flush)
            $this->entityManager->flush();
    }

    public function getBadge($id)
    {
        if(!$this->badgeExists($id))
        {
            throw new \InvalidArgumentException("Unknown badge id " . $id);
        }

        return $this->registry[$id];
    }

    public function getBadges(User $user)
    {
        $badgeIds = $user->getBadges();
        if($badgeIds === null)
            return [];

        $badges = [];
        foreach($badgeIds as $id)
        {
            $badges[] = $this->getBadge($id);
        }

        return $badges;
    }

    public function getRegistry()
    {
        return $this->registry;
    }

    public function badgeExists($id)
    {
        return array_key_exists($id, $this->registry);
    }

    /**
     * @param $id string internal badge id
     * @param $name string human-readable name
     * @param $description string human-readable description
     * @param $imgPath string path relative to public/images/badges
     * @param $access string role required to give a badge (use INTERNAL to not allow)
     * @throws \DuplicateKeyException
     */
    public function registerBadge($id, $name, $description, $imgPath, $access = 'ROLE_ADMIN')
    {
        if($this->badgeExists($id))
        {
            throw new \DuplicateKeyException("Duplicate badge id " . $id);
        }

        $this->registry[$id] = [
            'id' => $id,
            'name' => $name,
            'image' => $imgPath,
            'description' => $description,
            'access' => $access
        ];
    }

    public function registerBadges()
    {
        // example
        /*$this->registerBadge(
            'internal-id',
            'Readable Name',
            'Description',
            'image relative to web/assets/images/badges/',
            'required role to give (INTERNAL, ROLE_MOD, or ROLE_ADMIN), defaults to ROLE_ADMIN'
        );*/

        // twinkie
        $this->registerBadge(
            'twinkie',
            'Twinkie!',
            'Received a twinkie from a moderator',
            'twinkie.png',
            'ROLE_MOD'
        );

        // win midday mission
        $this->registerBadge(
            'midday',
            'Win a Midday',
            'Win a midday mission',
            'sun.png',
            'ROLE_ADMIN'
        );

        // #Selfie
        $this->registerBadge(
            'selfie',
            '#Selfie',
            'Take a selfie with 3 admins and 3 mods',
            'selfie.png',
            'ROLE_ADMIN'
        );

        // mod squad
        $this->registerBadge(
            'mod-squad',
            'Mod Squad',
            'I am a MODERATOR!',
            'mod.png',
            'ROLE_ADMIN'
        );

        // admin
        $this->registerBadge(
            'admin-squad',
            'Yellow',
            'I am an ADMIN',
            'yellow.png',
            'ROLE_ADMIN'
        );

        // chair ;_;
        $this->registerBadge(
            'chair-squad',
            'Chair',
            'I am a chair ;_;',
            'chair.png',
            'ROLE_ADMIN'
        );

        // infection badge
        $this->registerBadge(
            'infected',
            'Infected',
            'Died in the zombie apocalypse',
            'infected.png',
            'INTERNAL'
        );

        // OZ badge
        $this->registerBadge(
            'oz',
            'OZ',
            'Patient Zero',
            'infected.png',
            'INTERNAL'
        );

        // used an AV
        $this->registerBadge(
            'used-av',
            'Used AV',
            'Used an AV code to become human again',
            'antivirus.png',
            'INTERNAL'
        );

        // caught a human between 6 and 8 AM
        $this->registerBadge(
            'early-bird',
            'Early Bird',
            'Caught a human between 6 and 8 AM',
            'earlybird.png',
            'INTERNAL'
        );

        // died between 11 PM and midnight
        $this->registerBadge(
            'mission-aint-over',
            'Mission ain\'t Over',
            'Died between 11 PM and Midnight',
            'mission-aint-over.png',
            'INTERNAL'
        );

        // died on sunday night
        $this->registerBadge(
            'bad-start',
            'Bad Start',
            'Died on Sunday night',
            'badstart.png',
            'INTERNAL'
        );

        // died from thursday onward
        $this->registerBadge(
            'so-close',
            'So Close',
            'Died from Thursday onward',
            'close.png',
            'INTERNAL'
        );

        // killed a human within an hour of becoming a zombie
        $this->registerBadge(
            'quick-turnaround',
            'Quick Turnaround',
            'Killed a human within an hour of becoming a zombie',
            'clock.png',
            'INTERNAL'
        );

        // killstreaks
        $this->registerBadge(
            'streak-2',
            'Double Kill',
            '2 kills within an hour',
            'streak-2.png',
            'INTERNAL'
        );

        $this->registerBadge(
            'streak-3',
            'Triple Kill',
            '3 kills within an hour',
            'streak-3.png',
            'INTERNAL'
        );

        $this->registerBadge(
            'streak-4',
            'Overkill',
            '4 kills within an hour',
            'streak-4.png',
            'INTERNAL'
        );

        $this->registerBadge(
            'streak-5',
            'Killtacular',
            '5 kills within an hour',
            'streak-5.png',
            'INTERNAL'
        );

        $this->registerBadge(
            'streak-6',
            'Killtrocity',
            '6 kills within an hour',
            'streak-6.png',
            'INTERNAL'
        );

        $this->registerBadge(
            'streak-7',
            'Killmanjaro',
            '7 kills within an hour',
            'streak-7.png',
            'INTERNAL'
        );

        $this->registerBadge(
            'streak-8',
            'Killtastrophy',
            '8 kills within an hour',
            'streak-8.png',
            'INTERNAL'
        );

        $this->registerBadge(
            'streak-9',
            'Killpocalypse',
            '9 kills within an hour',
            'streak-9.png',
            'INTERNAL'
        );

        $this->registerBadge(
            'streak-10',
            'Killionare',
            '10 kills within an hour',
            'streak-10.png',
            'INTERNAL'
        );
    }
}