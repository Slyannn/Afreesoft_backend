<?php

namespace App\Entity;

use App\Repository\OrganismRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrganismRepository::class)]
class Organism
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $certificate = null;

    #[ORM\OneToOne(inversedBy: 'organism')]
    private ?User $user = null;

    #[ORM\OneToOne(mappedBy: 'profile', cascade: ['persist', 'remove'])]
    private ?OrganismAdmin $organismAdmin = null;

    #[ORM\Column]
    private ?bool $enable = false;

    #[ORM\OneToMany(mappedBy: 'organism', targetEntity: Review::class)]
    private Collection $reviews;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getCertificate(): ?string
    {
        return $this->certificate;
    }

    public function setCertificate(string $certificate): static
    {
        $this->certificate = $certificate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getOrganismAdmin(): ?OrganismAdmin
    {
        return $this->organismAdmin;
    }

    public function setOrganismAdmin(?OrganismAdmin $organismAdmin): static
    {
        // unset the owning side of the relation if necessary
        if ($organismAdmin === null && $this->organismAdmin !== null) {
            $this->organismAdmin->setProfile(null);
        }

        // set the owning side of the relation if necessary
        if ($organismAdmin !== null && $organismAdmin->getProfile() !== $this) {
            $organismAdmin->setProfile($this);
        }

        $this->organismAdmin = $organismAdmin;

        return $this;
    }


    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setOrganism($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getOrganism() === $this) {
                $review->setOrganism(null);
            }
        }

        return $this;
    }
}