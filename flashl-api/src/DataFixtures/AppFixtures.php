<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        // We create 2 user, an admin and a regular user

        // Admin user
        $admin = new User();
        $admin->setEmail('seb@iamseb.dev');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->encoder->hashPassword(
            $admin,
            'password'
        ));
        $admin->setIsVerified(true);
        $admin->setApiToken('apiToken');
        $manager->persist($admin);

        // Regular user
        $user = new User();
        $user->setEmail('daemon@iamseb.dev');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->encoder->hashPassword(
            $user,
            'password'
        ));
        $user->setIsVerified(true);
        $user->setApiToken('apiToken');
        $manager->persist($user);

        $manager->flush();
    }
}
