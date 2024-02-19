<?php

namespace App\Tests\DatabaseMigrationTests;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->repository = static::$kernel->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
    }

    public function testUserLoad(): void
    {
        $user = $this->repository->find(1);

        $this->assertSame(1, $user->getId());
    }
}
