<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Utilisateur admin
        $admin = new User();
        $admin->setName('Admin Stubborn');
        $admin->setEmail('admin@stubborn.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin1234!'));
        $admin->setIsVerified(true);
        $manager->persist($admin);

        // Utilisateur client de test
        $client = new User();
        $client->setName('John Doe');
        $client->setEmail('john@example.com');
        $client->setRoles([]);
        $client->setPassword($this->passwordHasher->hashPassword($client, 'Client1234!'));
        $client->setDeliveryAddress('8 rue du test, 75000 Paris');
        $client->setIsVerified(true);
        $manager->persist($client);

        // Produits Stubborn (** = mis en avant)
        $products = [
            ['name' => 'Blackbelt',   'price' => '29.90', 'image' => '1.jpeg',  'featured' => true],
            ['name' => 'BlueBelt',    'price' => '29.90', 'image' => '2.jpeg',  'featured' => false],
            ['name' => 'Street',      'price' => '34.50', 'image' => '3.jpeg',  'featured' => false],
            ['name' => 'Pokeball',    'price' => '45.00', 'image' => '4.jpeg',  'featured' => true],
            ['name' => 'PinkLady',    'price' => '29.90', 'image' => '5.jpeg',  'featured' => false],
            ['name' => 'Snow',        'price' => '32.00', 'image' => '6.jpeg',  'featured' => false],
            ['name' => 'Greyback',    'price' => '28.50', 'image' => '7.jpeg',  'featured' => false],
            ['name' => 'BlueCloud',   'price' => '45.00', 'image' => '8.jpeg',  'featured' => false],
            ['name' => 'BornInUsa',   'price' => '59.90', 'image' => '9.jpeg',  'featured' => true],
            ['name' => 'GreenSchool', 'price' => '42.20', 'image' => '10.jpeg', 'featured' => false],
        ];

        foreach ($products as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setPrice($data['price']);
            $product->setImage($data['image']);
            $product->setFeatured($data['featured']);
            $product->setStockXs(5);
            $product->setStockS(5);
            $product->setStockM(5);
            $product->setStockL(5);
            $product->setStockXl(5);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
