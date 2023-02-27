<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['person:read']]),
        new GetCollection(normalizationContext: ['groups' => ['person:read:collection']])
    ]
)]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[ORM\Column(length: 255)]
    private ?string $politicalGroup = null;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Contact::class)]
    private Collection $contacts;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
    }

    #[Groups(['person:read', 'person:read:collection'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(['person:read', 'person:read:collection'])]
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    #[Groups(['person:read', 'person:read:collection'])]
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    #[Groups(['person:read', 'person:read:collection'])]
    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    #[Groups(['person:read', 'person:read:collection'])]
    public function getPoliticalGroup(): ?string
    {
        return $this->politicalGroup;
    }

    public function setPoliticalGroup(string $politicalGroup): self
    {
        $this->politicalGroup = $politicalGroup;

        return $this;
    }

    /**
     * @return Collection<int, Contact>
     */
    #[Groups(['person:read'])]
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
            $contact->setPerson($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getPerson() === $this) {
                $contact->setPerson(null);
            }
        }

        return $this;
    }
}
