<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\File;
use App\Entity\Post;
use Faker;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    public function load(ObjectManager $manager)
    {

        $faker = Faker\Factory::create('fr_FR');

        $users = [
            ['email' => 'user@user.com', 'pass' => 'user'],
            ['email' => 'user@user1.com', 'pass' => 'user1'],
            ['email' => 'user@user2.com', 'pass' => 'user2'],
        ];
        $userS = [];
        for ($i = 0; $i < 3; $i++) {

            $photo = new File();
            $photo->setName($faker->imageUrl(640, 480, 'city'));

            $userS[$i] =  new User();
            $userS[$i]->setEmail($users[$i]['email'])
                ->setPassword($this->passwordEncoder->encodePassword($userS[$i], $users[$i]['pass']))
                ->setFirstName($faker->firstName)
                ->setName($faker->lastName)
                ->setPhone($faker->e164PhoneNumber)
                ->setLocation($faker->country)
                ->setAddress($faker->address)
                ->setPhoto($photo);

            $manager->persist($photo);
            $manager->persist($userS[$i]);
        }


        $posts = [];
        for ($i = 0; $i < 10; $i++) {

            $image = new File();
            $image->setName($faker->imageUrl(640, 480, 'technics'));

            $posts[$i] = new Post();

            $posts[$i]->setTitle($faker->catchPhrase)
                ->setDescription($faker->text(2000))
                ->setImage($image)
                ->setUser($userS[array_rand($userS, 1)]);

            $manager->persist($image);
            $manager->persist($posts[$i]);
        }


        for ($i = 0; $i < 50; $i++) {

            $comment = new Comment();

            $comment->setContent($faker->text(200))
                ->setPost($posts[array_rand($posts, 1)])
                ->setUser($userS[array_rand($userS, 1)]);

            $manager->persist($comment);
        }

        $manager->flush();
    }
}
