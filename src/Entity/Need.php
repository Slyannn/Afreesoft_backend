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

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Student::class, mappedBy: 'needs')]
    private Collection $students;


    #[ORM\ManyToMany(targetEntity: OrganismAdmin::class, mappedBy: 'services')]
    private Collection $organismAdmins;

    public function __construct()
    {
        $this->students = new ArrayCollection();
        $this->organismAdmins = new ArrayCollection();
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
     * @return Collection<int, Student>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(Student $student): static
    {
        if (!$this->students->contains($student)) {
            $this->students->add($student);
            $student->addNeed($this);
        }

        return $this;
    }

    public function removeStudent(Student $student): static
    {
        if ($this->students->removeElement($student)) {
            $student->removeNeed($this);
        }

        return $this;
    }

    //toString
    public function __toString(): string
    {
        //all attributes
        return $this->name;

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
            $organismAdmin->addService($this);
        }

        return $this;
    }

    public function removeOrganismAdmin(OrganismAdmin $organismAdmin): static
    {
        if ($this->organismAdmins->removeElement($organismAdmin)) {
            $organismAdmin->removeService($this);
        }

        return $this;
    }


}
