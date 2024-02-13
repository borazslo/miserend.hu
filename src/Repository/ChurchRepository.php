<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Church;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @extends ServiceEntityRepository<Church>
 *
 * @method Church|null find($id, $lockMode = null, $lockVersion = null)
 * @method Church|null findOneBy(array $criteria, array $orderBy = null)
 * @method Church[]    findAll()
 * @method Church[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChurchRepository extends ServiceEntityRepository
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        ManagerRegistry $registry)
    {
        parent::__construct($registry, Church::class);
    }

    /**
     * @todo ha a user admin / feltolto akkor lathatlja akkor is ha nincs jovahagyva
     */
    public function findOnePublicChurch(int $churchId): ?Church
    {
        return $this->createQueryBuilder('church')
            ->where('church.id = :church_id AND church.moderation = :accepted AND (church.deletedAt IS NULL OR church.deletedAt > :now)') // todo kiszedni ha mar a softdelete filter bekerul
            ->setParameter('now', new \DateTime())
            ->setParameter('accepted', Church::MODERATION_ACCEPTED)
            ->setParameter('church_id', $churchId)
            ->leftJoin('church.holder', 'holder')
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @return array<int, Church>
     */
    public function findMostFavorite(int $amount = 10): array
    {
        $churches = $this->createQueryBuilder('church')
            ->select('church as church_entity, COUNT(church.id) as total')
            ->join('church.usersWhoFavored', 'users')
            ->orderBy('total', 'DESC')
            ->groupBy('users.id')
            ->setMaxResults($amount)
            ->getQuery()->getResult();

        if (\count($churches) === 0) {
            return [];
        }

        return array_map(function ($row) {
            return $row['church_entity'];
        }, $churches);
    }

    public function generateSlug(Church $church, bool $save = true): void
    {
        $slug = $this->slugger->slug($church->getName());
        $church->setSlug(strtolower($slug));

        if ($save) {
            $this->_em->flush();
        }
    }

    /**
     * @param User $user
     * @return array<Church>
     */
    public function findFavoriteChurches(User $user): array
    {
        return $this->createQueryBuilder('church')
            ->join('church.usersWhoFavored', 'user')
            ->where('user = :user')
            ->setParameter('user', $user)
            ->orderBy('church.name')
            ->getQuery()->getResult();
    }

    public function save(Church $church, bool $save = false): void
    {
        $this->_em->persist($church);

        if ($save) {
            $this->_em->flush();
        }
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
