<?php

namespace App\Entity;

use App\Repository\RelationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RelationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Relation
{
    const MODE_COMPLETO = 'completo';
    const MODE_INDIVIDUAL = 'individual';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private array $solutions = [];

    #[ORM\Column(nullable: true)]
    private array $other_solutions = [];

    #[ORM\Column(length: 16)]
    private ?string $mode = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private array $json = [];


    public function __construct(?string $json = null)
    {
        $this->uuid = Uuid::v4()->toBase32();

        if ($json) {
            $this->fromJson($json);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        if ($this->uuid) {
            throw new \Exception('can-not-modify-uuid-once-setted');
        }
        $this->uuid = $uuid;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSolutions(): array
    {
        return $this->solutions;
    }
    public function getOptions(): array
    {
        $rdo = [];

        foreach ($this->getSolutions() as $solution) {
            foreach ($solution['option'] as $option) {
                $rdo[] = $option;
            }
        }

        return $rdo;
    }

    public function setSolutions(array $solutions): self
    {
        $this->solutions = $solutions;

        return $this;
    }

    public function getOtherSolutions(): array
    {
        return $this->other_solutions;
    }

    public function setOtherSolutions(?array $other_solutions): self
    {
        $this->other_solutions = $other_solutions;

        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getJson(): array
    {
        return $this->json;
    }

    public function setJson(array $json): self
    {
        $this->json = $json;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public static function formatTo64(string $img): string
    {
        return base64_encode(file_get_contents($img));
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->getUuid(),
            'title' => $this->getTitle(),
            'solutions' => $this->getSolutions(),
            'other_solutions' => $this->getOtherSolutions(),
            'mode' => $this->getMode(),
            'image' => $this->getImage()
        ];
    }


    public function fromArray(array $data)
    {
        // hacer comprobaciones de keys y values
        $this->existKey($data);

        $this->setTitle($data['title']);
        $this->setSolutions($data['solutions']);
        $this->setOtherSolutions($data['other_solutions']);
        $this->setMode($data['mode']);
    }

    /**
     * @throws \Exception
     */
    private function existKey(array $data): void
    {
        $keys = ["title","solutions","other_solutions","mode"];

        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \Exception('from-array-no-%key%-key');
                //throw new \Exception(sprintf('from-array-no-%s-key', $key));
            }
        }

/*
        if (!array_key_exists("title", $data)) {
            throw new \Exception('from-array-no-title-key');
        }*/
        if (!array_key_exists("solutions", $data)) {
            throw new \Exception('from-array-no-title-key');
        } else {
            //$this->
        }

        /*
        if (!array_key_exists("other_solutions", $data)) {
            throw new \Exception('from-array-no-title-key');
        }
        if (!array_key_exists("mode", $data)) {
            throw new \Exception('from-array-no-title-key');
        }
*/
    }

    private function validateValues(array $data): void
    {
        if (count($data['solutions']) == 0) {
            throw new \Exception('must-have-at-least-one-solution');
        }

        if (!is_string($data['title']) || empty($data['title'])) {
            throw new \Exception('incorrect-title');
        }

        if (!is_string($data['mode']) || empty($data['mode'])) {
            throw new \Exception('empty-mode');
        }
        if (!($data['mode'] == self::MODE_COMPLETO || $data['mode'] == self::MODE_INDIVIDUAL)) {
            throw new \Exception('mode-format-not-correct');
        }

        foreach ($data['solutions'] as $key => $solution) {
            if (!is_string($solution["nombre"]) || empty($solution["nombre"])) {
                throw new \Exception('empty-solution-at:'. $key);
            }

            foreach ($solution['valores'] as $valor) {
                if (!is_string($valor) || empty($valor)) {
                    throw new \Exception('empty-solution-at:'. $solution["nombre"]);
                }
            }
        }
    }


    public function TransformSolutions($data) {

        $rdo['solutions'] = [];

        foreach ($data as $solution) {

            $sol = ['id' => $solution['id'], 'category' => $solution['category'],
                'options' => $solution['options']];

            if ($solution['image']) {

                $newFilename = date('d-m-Y-H-i') . '_' . uniqid() . '.' . $solution['image']->guessExtension();

                $solution['image']->move(
                    getParameter('uploads_directory').'/category',
                    $newFilename
                );

                $ruta = $this->getParameter('uploads_directory') . '/category/' . $newFilename;
                $sol['image'] = Relation::formatTo64($ruta);
            }

            $rdo['solutions'][] = $sol;
        }
    }




    public function fromJson(string $json): void
    {
        $this->fromArray(json_decode($json, true));
    }


    private function getAllSolShuffled(): array
    {
        $rdo = $this->getOptions();

        foreach ($this->getOtherSolutions() as $other) {
            $rdo[] = $other;
        }

        shuffle($rdo);

        return $rdo;
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if (!$this->uuid) {
            $this->setUuid((Uuid::v4()->toBase32()));
        }

        if(!$this->createdAt) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }

    #[ORM\PreFlush]
    public function preFlush(): void
    {
        $this->setUpdatedAt(new \DateTime('now'));
    }

}
