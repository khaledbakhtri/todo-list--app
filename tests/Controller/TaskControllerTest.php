<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class TaskControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        
        // Get entity manager from client container
        $entityManager = $client->getContainer()
            ->get('doctrine')
            ->getManager();
        
        // Create a test user with unique email
        $user = new User();
        $user->setEmail('test_' . uniqid() . '@example.com');
        $user->setFirstName('Test');
        $user->setLastName('User');
        
        // Hash the password
        $passwordHasher = $client->getContainer()->get(UserPasswordHasherInterface::class);
        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        
        $entityManager->persist($user);
        $entityManager->flush();
        
        // Create a task for the user
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setIsDone(false);
        $task->setUser($user);
        
        $entityManager->persist($task);
        $entityManager->flush();
        
        // Log in the user
        $client->loginUser($user);
        
        // Test the index page
        $client->request('GET', '/task');
        
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main h1', 'Mes Tâches');
        self::assertSelectorTextContains('.text-lg', 'Test Task');
        
        // Clean up
        $entityManager->remove($task);
        $entityManager->remove($user);
        $entityManager->flush();
    }
}
