<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Diocese;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Diocese>
 *
 * @method Diocese|null find($id, $lockMode = null, $lockVersion = null)
 * @method Diocese|null findOneBy(array $criteria, array $orderBy = null)
 * @method Diocese[]    findAll()
 * @method Diocese[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DioceseRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, Diocese::class);
    }

    public function createAllRecordQueryBuilder(string $alias = 'diocese', string $indexBy = null): QueryBuilder
    {
        return $this->createQueryBuilder($alias, $indexBy)
            ->select('partial diocese.{id, name}')
            ->where('diocese.status = \'i\'')
            ->orderBy('diocese.recordOrder');
    }

    public function save(Diocese $diocese, bool $flush = false): void
    {
        $this->_em->persist($diocese);

        if ($flush) {
            $this->_em->flush();
        }
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
