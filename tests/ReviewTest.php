<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Car;
use App\Entity\Review;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class ReviewTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    /**
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/api/reviews');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Review',
            '@id'      => '/api/reviews',
            '@type'    => 'hydra:Collection',
        ]);
        $this->assertCount(20, $response->toArray()['hydra:member']);
    }

    /**
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGet(): void
    {
        $reviewId = (static::getContainer()->get('doctrine')->getRepository(Review::class)->findOne())[0]['id'];

        static::createClient()->request('GET', '/api/reviews/' . $reviewId);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Review',
            '@id'      => '/api/reviews/' . $reviewId,
            '@type'    => 'Review',
            'id'       => $reviewId,
            'car'      => [
                '@type' => 'Car'
            ]
        ]);
    }

    /**
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetNotFound(): void
    {
        $reviewId = (static::getContainer()->get('doctrine')->getRepository(Review::class)->findOne('DESC'))[0]['id'];

        static::createClient()->request('GET', '/api/reviews/' . $reviewId + 1);

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@id'    => '/api/errors/404',
            '@type'  => 'hydra:Error',
            'title'  => 'An error occurred',
            'detail' => 'Not Found',
        ]);
    }

    /**
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreate(): void
    {
        $carId = (static::getContainer()->get('doctrine')->getRepository(Car::class)->findOne())[0]['id'];

        static::createClient()->request('POST', '/api/reviews', [
            'json' => [
                'starRating' => 9,
                'reviewText' => 'This is awesome!',
                'car'        => '/api/cars/' . $carId,
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context'   => '/api/contexts/Review',
            '@type'      => 'Review',
            'starRating' => 9,
            'reviewText' => 'This is awesome!',
            'car'        => [
                '@id' => '/api/cars/' . $carId
            ]

        ]);
    }

    /**
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateReviewWithEmptyReviewTest(): void
    {
        static::createClient()->request('POST', '/api/reviews', [
            'json' => [
                'starRating' => 9,
                'reviewText' => '',
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@type'       => 'ConstraintViolationList',
            'detail'      => 'reviewText: This value should not be blank.',
            'hydra:title' => 'An error occurred',
        ]);
    }

    /**
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateReviewWithMissingReviewText(): void
    {
        static::createClient()->request('POST', '/api/reviews', [
            'json' => [
                'starRating' => 9,
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@type'       => 'ConstraintViolationList',
            'detail'      => 'reviewText: This value should not be blank.',
            'hydra:title' => 'An error occurred',
        ]);
    }

    /**
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateReviewWithIntegerReviewText(): void
    {
        static::createClient()->request('POST', '/api/reviews', [
            'json' => [
                'reviewText' => 1,
            ]
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@id'    => '/api/errors/400',
            '@type'  => 'hydra:Error',
            'title'  => 'An error occurred',
            'detail' => "The type of the \"reviewText\" attribute must be \"string\", \"integer\" given.",
        ]);
    }

    /**
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateReviewWithStarRatingGreaterThanTen(): void
    {
        static::createClient()->request('POST', '/api/reviews', [
            'json' => [
                'starRating' => 11,
                'reviewText' => 'This is awesome!',
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@type'       => 'ConstraintViolationList',
            'detail'      => 'starRating: starRating must be between 1 and 10.',
            'hydra:title' => 'An error occurred',
        ]);
    }

    /**
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatch(): void
    {
        $reviewId = (static::getContainer()->get('doctrine')->getRepository(Review::class)->findOne())[0]['id'];

        static::createClient()->request('PATCH', '/api/reviews/' . $reviewId, [
            'json'    => [
                'starRating' => 3,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id'        => '/api/reviews/' . $reviewId,
            'id'         => $reviewId,
            'starRating' => 3,
        ]);
    }

    /**
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPut(): void
    {
        $reviewId = (static::getContainer()->get('doctrine')->getRepository(Review::class)->findOne())[0]['id'];
        $carId = (static::getContainer()->get('doctrine')->getRepository(Car::class)->findOne())[0]['id'];

        static::createClient()->request('PUT', '/api/reviews/' . $reviewId, [
            'json' => [
                'starRating' => 9,
                'reviewText' => 'This is awesome!',
                'car'        => '/api/cars/' . $carId
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context'   => '/api/contexts/Review',
            '@type'      => 'Review',
            'starRating' => 9,
            'reviewText' => 'This is awesome!',
            'car'        => [
                '@id' => '/api/cars/' . $carId
            ]
        ]);
    }

    /**
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDelete(): void
    {
        $reviewId = (static::getContainer()->get('doctrine')->getRepository(Review::class)->findOne())[0]['id'];

        static::createClient()->request('DELETE', '/api/reviews/' . $reviewId);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Review::class)->findOneBy(['id' => $reviewId])
        );
    }
}
