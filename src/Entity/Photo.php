<?php

namespace App\Entity;

use App\Repository\PhotoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string|null $filename
     */
    public function setFilename(?string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int|null
     */
    public function getWeight(): ?int
    {
        return $this->weight;
    }

    /**
     * @param int|null $weight
     */
    public function setWeight(?int $weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @return string|null
     */
    public function getFlag(): ?string
    {
        return $this->flag;
    }

    /**
     * @param string|null $flag
     */
    public function setFlag(?string $flag): void
    {
        $this->flag = $flag;
    }

    /**
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @param int|null $height
     */
    public function setHeight(?int $height): void
    {
        $this->height = $height;
    }

    /**
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @param int|null $width
     */
    public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    /**
     * @return Church|null
     */
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
