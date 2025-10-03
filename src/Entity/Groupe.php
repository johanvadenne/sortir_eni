<?php

namespace App\Entity;

use App\Repository\GroupeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GroupeRepository::class)]
#[ORM\Index(name: 'idx_groupe_nom', columns: ['nom'])]
#[ORM\Index(name: 'idx_groupe_actif', columns: ['actif'])]
#[UniqueEntity(fields: ['nom'], message: 'Ce nom de groupe est déjà utilisé.')]
class Groupe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le nom du groupe est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le nom du groupe doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom du groupe ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(
        max: 500,
        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $actif = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\ManyToOne(inversedBy: 'groupesCrees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $createur = null;

    #[ORM\ManyToMany(targetEntity: Participant::class, inversedBy: 'groupes')]
    #[ORM\JoinTable(name: 'groupe_participants')]
    private Collection $participants;

    #[ORM\OneToMany(mappedBy: 'groupe', targetEntity: Sortie::class)]
    private Collection $sorties;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->sorties = new ArrayCollection();
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
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

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getCreateur(): ?Participant
    {
        return $this->createur;
    }

    public function setCreateur(?Participant $createur): static
    {
        $this->createur = $createur;
        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }
        return $this;
    }

    public function removeParticipant(Participant $participant): static
    {
        $this->participants->removeElement($participant);
        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): static
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties->add($sorty);
            $sorty->setGroupe($this);
        }
        return $this;
    }

    public function removeSorty(Sortie $sorty): static
    {
        if ($this->sorties->removeElement($sorty)) {
            // Définir le côté propriétaire à null si nécessaire
            if ($sorty->getGroupe() === $this) {
                $sorty->setGroupe(null);
            }
        }
        return $this;
    }

    /**
     * Vérifie si un participant est membre du groupe
     */
    public function isMembre(Participant $participant): bool
    {
        return $this->participants->contains($participant);
    }

    /**
     * Vérifie si un participant peut gérer le groupe (créateur ou admin)
     */
    public function canGérer(Participant $participant): bool
    {
        return $this->createur === $participant || $participant->isAdministrateur();
    }

    /**
     * Retourne le nombre de participants dans le groupe
     */
    public function getNbParticipants(): int
    {
        return $this->participants->count();
    }

    /**
     * Retourne le nombre de sorties du groupe
     */
    public function getNbSorties(): int
    {
        return $this->sorties->count();
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}
