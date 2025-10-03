<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
#[ORM\Index(name: 'idx_sortie_etat', columns: ['etat_id'])]
#[ORM\Index(name: 'idx_sortie_date_debut', columns: ['date_heure_debut'])]
#[ORM\Index(name: 'idx_sortie_date_limite', columns: ['date_limite_inscription'])]
#[ORM\Index(name: 'idx_sortie_lieu', columns: ['lieu_id'])]
#[ORM\Index(name: 'idx_sortie_organisateur', columns: ['organisateur_id'])]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan('now', message: 'La date de début doit être dans le futur')]
    private ?\DateTimeInterface $dateHeureDebut = null;

    #[ORM\Column(nullable: true)]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\LessThan(propertyPath: 'dateHeureDebut', message: 'La date limite d\'inscription doit être antérieure à la date de début')]
    private ?\DateTimeInterface $dateLimiteInscription = null;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(1, message: 'Le nombre maximum d\'inscriptions doit être au moins 1')]
    private ?int $nbInscriptionsMax = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $infosSortie = null;

    #[ORM\Column(length: 250, nullable: true)]
    private ?string $urlPhoto = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lieu $lieu = null;

    #[ORM\ManyToOne(inversedBy: 'sortiesOrganisees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $organisateur = null;

    #[ORM\OneToMany(mappedBy: 'sortie', targetEntity: Inscription::class)]
    private Collection $inscriptions;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Groupe $groupe = null;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
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

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): static
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): static
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(?string $infosSortie): static
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getUrlPhoto(): ?string
    {
        return $this->urlPhoto;
    }

    public function setUrlPhoto(?string $urlPhoto): static
    {
        $this->urlPhoto = $urlPhoto;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getEtatLibelle(): ?string
    {
        return $this->etat ? $this->etat->getLibelle() : null;
    }

    public function setEtatLibelle(?string $etatLibelle): static
    {
        // Cette méthode est utilisée par le workflow Symfony
        // Elle permet de synchroniser l'état de la sortie avec l'entité Etat
        if ($etatLibelle) {
            // La logique de synchronisation est gérée par SortieStateService
            // Cette méthode est appelée automatiquement par le workflow
        }

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Participant $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    /**
     * @return Collection<int, Inscription>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setSortie($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            // Définir le côté propriétaire à null si nécessaire
            if ($inscription->getSortie() === $this) {
                $inscription->setSortie(null);
            }
        }

        return $this;
    }

    /**
     * Retourne le nombre d'inscriptions actuelles
     */
    public function getNbInscriptionsActuelles(): int
    {
        return $this->inscriptions->count();
    }

    /**
     * Vérifie si la sortie est complète
     */
    public function isComplete(): bool
    {
        return $this->getNbInscriptionsActuelles() >= $this->nbInscriptionsMax;
    }

    /**
     * Vérifie si les inscriptions sont encore ouvertes
     */
    public function isInscriptionOuverte(): bool
    {
        return $this->dateLimiteInscription > new \DateTime() && !$this->isComplete();
    }

    public function getGroupe(): ?Groupe
    {
        return $this->groupe;
    }

    public function setGroupe(?Groupe $groupe): static
    {
        $this->groupe = $groupe;
        return $this;
    }

    /**
     * Vérifie si la sortie est privée (restreinte à un groupe)
     */
    public function isPrivee(): bool
    {
        return $this->groupe !== null;
    }

    /**
     * Vérifie si un participant peut voir cette sortie
     */
    public function canVoir(Participant $participant): bool
    {
        // Si la sortie n'est pas privée, tout le monde peut la voir
        if (!$this->isPrivee()) {
            return true;
        }

        // Si la sortie est privée, seuls les membres du groupe peuvent la voir
        return $this->groupe->isMembre($participant) || $participant->isAdministrateur();
    }

    /**
     * Vérifie si un participant peut s'inscrire à cette sortie
     */
    public function canSInscrire(Participant $participant): bool
    {
        // Vérifier d'abord les conditions de base
        if (!$this->isInscriptionOuverte()) {
            return false;
        }

        // Vérifier si le participant peut voir la sortie
        if (!$this->canVoir($participant)) {
            return false;
        }

        // Vérifier si le participant n'est pas déjà inscrit
        foreach ($this->inscriptions as $inscription) {
            if ($inscription->getParticipant() === $participant) {
                return false;
            }
        }

        return true;
    }
}
