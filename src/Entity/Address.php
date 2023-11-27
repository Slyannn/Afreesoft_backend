<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $street = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    private ?string $zipCode = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;


    #[ORM\OneToMany(mappedBy: 'address', targetEntity: OrganismAdmin::class)]
    private Collection $organismAdmins;

    public function __construct()
    {
        $this->organismAdmins = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }


    /**
     * @return Collection<int, OrganismAdmin>
     */
    public function getOrganismAdmins(): Collection
    {
        return $this->organismAdmins;
    }

    public function addOrganismAdmin(OrganismAdmin $organismAdmin): static
    {
        if (!$this->organismAdmins->contains($organismAdmin)) {
            $this->organismAdmins->add($organismAdmin);
            $organismAdmin->setAddress($this);
        }

        return $this;
    }

    public function removeOrganismAdmin(OrganismAdmin $organismAdmin): static
    {
        if ($this->organismAdmins->removeElement($organismAdmin)) {
            // set the owning side to null (unless already changed)
            if ($organismAdmin->getAddress() === $this) {
                $organismAdmin->setAddress(null);
            }
        }

        return $this;
    }
}
