<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Review;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class ReviewTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/api/reviews');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Review',
            '@id' => '/api/reviews',
            '@type' => 'hydra:Collection',
        ]);
        $this->assertCount(20, $response->toArray()['hydra:member']);
    }

    public function testGet(): void
    {
        static::createClient()->request('GET', '/api/reviews/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Review',
            '@id' => '/api/reviews/1',
            '@type' => 'Review',
            'id' => 1,
            'car' => [
                '@type' => 'Car'
            ]
        ]);
    }

    public function testGetNotFound(): void
    {
        static::createClient()->request('GET', '/api/reviews/21');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@id' => '/api/errors/404',
            '@type' => 'hydra:Error',
            'title' => 'An error occurred',
            'detail' => 'Not Found',
        ]);
    }

    public function testCreate(): void
    {
        static::createClient()->request('POST', '/api/reviews', [
            'json' => [
                'starRating' => 9,
                'reviewText' => 'This is awesome!',
                'car' => '/api/cars/1',
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Review',
            '@type' => 'Review',
            'starRating' => 9,
            'reviewText' => 'This is awesome!',
            'car' => [
                '@id' => '/api/cars/1'
            ]

        ]);
    }

    public function testCreateReviewWithEmptyReviewTest(): void
    {
        static::createClient()->request('POST', '/api/reviews', [
            'json' => [
                'starRating' => 9,
                'reviewText' => '',
                'car' => '/api/cars/1',
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@type' => 'ConstraintViolationList',
            'detail' => 'reviewText: This value should not be blank.',
            'hydra:title' => 'An error occurred',
        ]);
    }

    public function testCreateReviewWithMissingReviewText(): void
    {
        static::createClient()->request('POST', '/api/reviews', [
            'json' => [
                'starRating' => 9,
                'car' => '/api/cars/1',
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@type' => 'ConstraintViolationList',
            'detail' => 'reviewText: This value should not be blank.',
            'hydra:title' => 'An error occurred',
        ]);
    }

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
            '@id' => '/api/errors/400',
            '@type' => 'hydra:Error',
            'title' => 'An error occurred',
            'detail' => "The type of the \"reviewText\" attribute must be \"string\", \"integer\" given.",
        ]);
    }

    public function testCreateReviewWithStarRatingGreaterThanTen(): void
    {
        static::createClient()->request('POST', '/api/reviews', [
            'json' => [
                'starRating' => 11,
                'reviewText' => 'This is awesome!',
                'car' => '/api/cars/1',
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@type' => 'ConstraintViolationList',
            'detail' => 'starRating: starRating must be between 1 and 10.',
            'hydra:title' => 'An error occurred',
        ]);
    }

    public function testPatch(): void
    {
        static::createClient()->request('PATCH', '/api/reviews/1', [
            'json' => [
                'starRating' => 3,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => '/api/reviews/1',
            'id' => 1,
            'starRating' => 3,
        ]);
    }

    public function testPut(): void
    {
        static::createClient()->request('PUT', '/api/reviews/1', [
            'json' => [
                'starRating' => 9,
                'reviewText' => 'This is awesome!',
                'car' => '/api/cars/1'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Review',
            '@type' => 'Review',
            'starRating' => 9,
            'reviewText' => 'This is awesome!',
            'car' => [
                '@id' => '/api/cars/1'
            ]
        ]);
    }

    public function testDelete(): void
    {
        static::createClient()->request('DELETE', '/api/reviews/1');

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Review::class)->findOneBy(['id' => 1])
        );
    }
}
