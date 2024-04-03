<?php

namespace App\Entity;

use App\Repository\OrganismAdminRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrganismAdminRepository::class)]
class OrganismAdmin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $logo = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'organismAdmins')]
    private ?Address $address = null;

    #[ORM\ManyToMany(targetEntity: Need::class, inversedBy: 'organismAdmins')]
    private Collection $services;

    #[ORM\OneToOne(inversedBy: 'organismAdmin', cascade: ['persist', 'remove'])]
    private ?Organism $profile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $organismEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createAt;


    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->createAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): static
    {
        $this->logo = $logo;

        return $this;
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


    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, Need>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Need $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
        }

        return $this;
    }

    public function removeService(Need $service): static
    {
        $this->services->removeElement($service);

        return $this;
    }

    public function getProfile(): ?Organism
    {
        return $this->profile;
    }

    public function setProfile(?Organism $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    public function getOrganismEmail(): ?string
    {
        return $this->organismEmail;
    }

    public function setOrganismEmail(?string $organismEmail): static
    {
        $this->organismEmail = $organismEmail;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }



    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }


}
