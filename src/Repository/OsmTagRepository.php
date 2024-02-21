<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Church;
use App\Entity\OsmTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OsmTag>
 *
 * @method OsmTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method OsmTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method OsmTag[]    findAll()
 * @method OsmTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OsmTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OsmTag::class);
    }

    /**
     * @return array{
     *     "wheelchair"?: OsmTag,
     *     "toilets:wheelchair"?: OsmTag,
     *     "hearing_loop"?: OsmTag,
     *     "wheelchair:description"?: OsmTag,
     *     "disabled:description"?: OsmTag,
     * }|array<OsmTag>
     */
    public function findTagsWithChurch(Church $church, bool $nameAsKey = false): array
    {
        /** @var array<int, OsmTag> $tags */
        $tags = $this->createQueryBuilder('osm_tag')
            ->select('osm_tag')
            ->where('osm_tag.osmId = :osm_id AND osm_tag.osmType = :osm_type')
            ->setParameter('osm_id', $church->getOsmId())
            ->setParameter('osm_type', $church->getOsmType())
            ->getQuery()->getResult();

        if ($nameAsKey === false) {
            return $tags;
        }

        $buffer = [];
        foreach ($tags as $tag) {
            $buffer[$tag->getName()] = $tag;
        }

        return $buffer;
    }

    public function save(OsmTag $osmTag, bool $save = false): void
    {
        $this->_em->persist($osmTag);

        if ($save) {
            $this->_em->flush();
        }
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
