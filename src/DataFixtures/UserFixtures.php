<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{

    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
        {
             $this->passwordEncoder = $passwordEncoder;
         }


    public function load(ObjectManager $manager)
    {
        
        $user = new User();
        $user->setEmail("fatou2@cyberdine.fr");
        $user->setUsername("Fatou");
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordEncoder->encodePassword($user,'Repasser@1234'));
        $manager->persist($user);


        $manager->flush();
    }
}
