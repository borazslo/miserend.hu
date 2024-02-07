<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Repository\PhotoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhotoRepository::class)]
#[ORM\Table(name: 'photos')]
class Photo
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue('AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'filename', type: Types::STRING, length: 100, nullable: false)]
    private ?string $filename = null;

    #[ORM\Column(name: 'title', type: Types::STRING, length: 250, nullable: false)]
    private ?string $title = null;

    #[ORM\Column(name: 'weight', type: Types::INTEGER, nullable: false)]
    private ?int $weight = null;

    #[ORM\Column(name: 'flag', type: Types::STRING, length: 32, nullable: false)]
    private ?string $flag = null;

    #[ORM\Column(name: 'height', type: Types::INTEGER, nullable: true)]
    private ?int $height = null;

    #[ORM\Column(name: 'width', type: Types::INTEGER, nullable: true)]
    private ?int $width = null;

    #[ORM\ManyToOne(targetEntity: Church::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(name: 'church_id', referencedColumnName: 'id', nullable: false)]
    private ?Church $church = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): void
    {
        $this->filename = $filename;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): void
    {
        $this->weight = $weight;
    }

    public function getFlag(): ?string
    {
        return $this->flag;
    }

    public function setFlag(?string $flag): void
    {
        $this->flag = $flag;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): void
    {
        $this->height = $height;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    public function getChurch(): ?Church
    {
        return $this->church;
    }

    /*
     *
    <field name="createdAt" type="datetime" column="created_at" nullable="true"/>
    <field name="updatedAt" type="datetime" column="updated_at" nullable="true"/>
  </entity>
*/
}
