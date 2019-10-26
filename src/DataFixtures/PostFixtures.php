<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

class PostFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        $faker = Faker\Factory::create('fr_FR');


        for ($i = 0; $i < 50; $i++) {
            $post = new Post();
            $post->setTitle($faker->catchPhrase);
            $post->setDescription($faker->text(2000));
            $post->setImage($faker->imageUrl(640, 480, 'technics'));
            $manager->persist($post);
        }
        $manager->flush();
    }
}
