<?php

namespace App\DataFixtures;


use App\Entity\Admin;
use App\Entity\Need;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher  )
    {
    }


    public function load(ObjectManager $manager)
    {
        $admin = new Admin();
        $admin->setEmail('admin@test.fr');
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'admin'));
        $admin->setFirstName('Afreesoft');
        $admin->setLastName('Admin');
        $admin->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin);
        $manager->flush();

        $need = new Need();
        $need->setName('Santé');
        $manager->persist($need);
        $manager->flush();

    }
}