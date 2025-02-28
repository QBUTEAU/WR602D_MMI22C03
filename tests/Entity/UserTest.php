<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetterAndSetter()
    {
        // Création d'une instance de l'entité User
        $user = new User();

        // Définition de données de test
        $email = 'test@test.com';
        $password = 'password123';
        $firstname = 'Quentin';
        $lastname = 'BUTEAU';

        // Utilisation des setters
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);

        // Vérification des getters
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals($firstname, $user->getFirstname());
        $this->assertEquals($lastname, $user->getLastname());
    }
}
