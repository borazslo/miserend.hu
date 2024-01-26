<?php

namespace App\Repository;

use App\Entity\Church;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChurchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
            ->getQuery()->getOneOrNullResult();
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