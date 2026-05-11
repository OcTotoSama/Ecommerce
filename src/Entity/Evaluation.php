<?php

namespace App\Entity;

use App\Repository\EvaluationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_user_produit', columns: ['user_id', 'produit_id'])]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column(type: 'smallint')]
    private int $note;

    public function getId(): ?int { return $this->id; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $u): static { $this->user = $u; return $this; }
    public function getProduit(): ?Produit { return $this->produit; }
    public function setProduit(?Produit $p): static { $this->produit = $p; return $this; }
    public function getNote(): int { return $this->note; }
    public function setNote(int $n): static { $this->note = max(1, min(5, $n)); return $this; }
}