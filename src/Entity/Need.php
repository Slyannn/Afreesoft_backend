<?php

namespace App\Entity;

use App\Repository\NeedRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NeedRepository::class)]
class Need
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Organism::class, mappedBy: 'services')]
    private Collection $organisms;

    public function __construct()
    {
        $this->organisms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Organism>
     */
    public function getOrganisms(): Collection
    {
        return $this->organisms;
    }

    public function addOrganism(Organism $organism): static
    {
        if (!$this->organisms->contains($organism)) {
            $this->organisms->add($organism);
            $organism->addService($this);
        }

        return $this;
    }

    public function removeOrganism(Organism $organism): static
    {
        if ($this->organisms->removeElement($organism)) {
            $organism->removeService($this);
        }

        return $this;
    }

}
