<?php

namespace App\Tests\Unit\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

class UserRepositoryTest extends TestCase
{
    public function testInitPasswordChange(): void
    {
        $clock = new MockClock('2023-06-02T06:15:20+00:00');
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $repository = new UserRepository($clock, $managerRegistryMock);

        $user = new User();

        $repository->initPasswordChange($user);

        $this->assertNotNull($user->getPasswordChangeHash());
        $this->assertTrue(strlen($user->getPasswordChangeHash()) > 10);

        $this->assertNotNull($user->getPasswordChangeDeadline());
        $this->assertSame('2023-06-04T06:15:20+00:00', $user->getPasswordChangeDeadline()->format('c'));
    }

    public function testRoleMigrator(): void
    {
        $clock = new MockClock();
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $repository = new UserRepository($clock, $managerRegistryMock);

        $user = new User();
        $user->setJogok('miserend');
        $repository->migrateOldRolesToNew($user);
        $this->assertSame(['ROLE_USER', 'ROLE_CHURCH_ADMIN'], $user->getRoles());

        $user = new User();
        $user->setJogok('user');
        $repository->migrateOldRolesToNew($user);
        $this->assertSame(['ROLE_USER', 'ROLE_USER_ADMIN'], $user->getRoles());

        $user = new User();
        $user->setJogok('miserend-user');
        $repository->migrateOldRolesToNew($user);
        $this->assertSame(['ROLE_USER', 'ROLE_CHURCH_ADMIN', 'ROLE_USER_ADMIN'], $user->getRoles());
    }
}
