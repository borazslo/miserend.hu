<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\ChurchHolder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChurchHolder>
 *
 * @method ChurchHolder|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChurchHolder|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChurchHolder[]    findAll()
 * @method ChurchHolder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChurchHolderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChurchHolder::class);
    }

    public function save(ChurchHolder $church, bool $save = false): void
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
