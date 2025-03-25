<?php

namespace App\DataFixtures;

use App\Entity\Subscription;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SubscriptionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $subscriptions = [
            ['id' => 1, 'name' => 'Standard', 'max_pdf' => 3, 'price' => 0],
            ['id' => 2, 'name' => 'Expert', 'max_pdf' => 6, 'price' => 4.99],
            ['id' => 3, 'name' => 'Premium', 'max_pdf' => 10, 'price' => 9.99],
        ];

        foreach ($subscriptions as $data) {
            $subscription = new Subscription();
            $subscription->setName($data['name']);
            $subscription->setMaxPdf($data['max_pdf']);
            $subscription->setPrice($data['price']);
            $manager->persist($subscription);
        }

        $manager->flush();
    }
}
