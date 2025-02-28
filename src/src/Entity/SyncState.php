<?php

namespace App\Entity;

use App\Repository\SyncStateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SyncStateRepository::class)]
class SyncState
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $syncInProgress = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isSyncInProgress(): ?bool
    {
        return $this->syncInProgress;
    }

    public function setSyncInProgress(bool $syncInProgress): static
    {
        $this->syncInProgress = $syncInProgress;

        return $this;
    }
}
