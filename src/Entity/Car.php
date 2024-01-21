<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\CarReviewsController;
use App\Repository\CarRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CarRepository::class)]
#[ApiResource(
    description: 'A car entity to store car specifications',
    normalizationContext: ['groups' => ['car.read']],
    denormalizationContext: ['groups' => ['car.write']],
    operations: [
        new GetCollection(
            name: 'latest_top_rated_car_reviews',
            uriTemplate: '/cars/{id}/reviews/latest-top-rated',
            controller: CarReviewsController::class,
            description: 'Retrives top 5 reviews of a car that its starRating is above 6',
        ),
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete()
    ]
)]
#[UniqueConstraint(fields: ['brand', 'model', 'color'])]
#[UniqueEntity(['brand', 'model', 'color'])]
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['car.read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[NotBlank]
    #[Groups(['car.read', 'car.write', 'review.read'])]
    private ?string $brand = null;

    #[ORM\Column(length: 255)]
    #[NotBlank]
    #[Groups(['car.read', 'car.write', 'review.read'])]
    private ?string $model = null;

    #[ORM\Column(length: 255)]
    #[NotBlank]
    #[Groups(['car.read', 'car.write', 'review.read'])]
    private ?string $color = null;

    #[ORM\OneToMany(
        mappedBy: 'car',
        targetEntity: Review::class,
        cascade: ["persist", "remove"]
    )]
    #[Groups(['car.read'])]
    private Collection $reviews;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

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
            $review->setCar($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getCar() === $this) {
                $review->setCar(null);
            }
        }

        return $this;
    }
}
