<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Range;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ApiResource(
    description: 'A car review entity to store car reviews',
    normalizationContext: ['groups' => ['review.read']]
)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['review.read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Range(
        min: 1,
        max: 10,
        notInRangeMessage: 'starRating must be between {{ min }} and {{ max }}.')]
    #[NotNull]
    #[Groups(['review.read', 'car.read'])]
    private ?int $starRating = null;

    #[ORM\Column(length: 255)]
    #[NotBlank]
    #[Groups(['review.read', 'car.read'])]
    private ?string $reviewText = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['review.read'])]
    private ?Car $car = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStarRating(): ?int
    {
        return $this->starRating;
    }

    public function setStarRating(int $starRating): static
    {
        $this->starRating = $starRating;

        return $this;
    }

    public function getReviewText(): ?string
    {
        return $this->reviewText;
    }

    public function setReviewText(string $reviewText): static
    {
        $this->reviewText = $reviewText;

        return $this;
    }

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): static
    {
        $this->car = $car;

        return $this;
    }
}
