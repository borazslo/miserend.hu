<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Random\Randomizer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, User::class);
    }

    public function migrateOldRolesToNew(User $user): void
    {
        $newRoles = ['ROLE_USER'];
        $oldRoles = explode('-', $user->getJogok());
        if (\in_array('miserend', $oldRoles)) {
            $newRoles[] = 'ROLE_CHURCH_ADMIN';
        }

        if (\in_array('user', $oldRoles)) {
            $newRoles[] = 'ROLE_USER_ADMIN';
        }

        $user->setRoles($newRoles);
    }

    public function initPasswordChange(User $user, int $deadlineInDays = 2, bool $save = false): void
    {
        $randomizer = new Randomizer();
        $passwordChangeHash = bin2hex($randomizer->getBytes(16));

        $user->setPasswordChangeHash($passwordChangeHash);
        $user->setPasswordChangeDeadline(new \DateTimeImmutable($deadlineInDays * 3600 * 24));

        if ($save) {
            $this->_em->flush();
        }
    }

    public function save(User $user, bool $flush = false): void
    {
        $user->setCreatedAt(new \DateTimeImmutable());
        $this->_em->persist($user);

        if ($flush) {
            $this->_em->flush();
        }
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
