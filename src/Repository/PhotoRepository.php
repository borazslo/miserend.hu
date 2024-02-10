<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Photo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    public function findRandomPhoto(): ?Photo
    {
        // TODO: Van, hogy a random képhez nem is tartozik templom. Valami régi hiba miatt.
        return $this->createQueryBuilder('photo')
            ->join('photo.church', 'church')
            ->orderBy('RAND()')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    public function save(Photo $photo, bool $save = false): void
    {
        $this->_em->persist($photo);

        if ($save) {
            $this->_em->flush();
        }
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
