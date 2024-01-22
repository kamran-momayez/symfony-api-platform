<?php

namespace App\Controller;

use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;


class CarReviewsController extends AbstractController
{
    /**
     * @param int              $id
     * @param ReviewRepository $reviewRepository
     * @return JsonResponse
     */
    public function __invoke(int $id, ReviewRepository $reviewRepository): JsonResponse
    {
        $result = $reviewRepository->findLatestTopRatedReviewsForCar($id);

        return !$result ? new JsonResponse(['error' => 'no reviews found'], 404) : new JsonResponse($result);
    }
}
