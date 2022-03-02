<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures {

    public function load(ObjectManager $objectManager) {
        $faker = Factory::create('fr_FR');

    }
}