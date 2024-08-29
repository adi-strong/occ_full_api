<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

trait IsDeletedTrait
{
    #[ORM\Column]
    private ?bool $isDeleted = false;

    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setDeleted(bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
}
