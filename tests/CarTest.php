<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Car;
use App\Entity\Review;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class CarTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/api/cars');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Car',
            '@id' => '/api/cars',
            '@type' => 'hydra:Collection',
        ]);
        $this->assertMatchesResourceCollectionJsonSchema(Car::class);
        $this->assertCount(5, $response->toArray()['hydra:member']);
    }

    public function testGet(): void
    {
        $carId = (static::getContainer()->get('doctrine')->getRepository(Car::class)->findOne())[0]['id'];
        
        static::createClient()->request('GET', '/api/cars/' . $carId);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Car',
            '@id' => '/api/cars/' . $carId,
            '@type' => 'Car',
            'id' => $carId,
        ]);
        $this->assertMatchesResourceItemJsonSchema(Car::class);
    }

    public function testGetNotFound(): void
    {
        $carId = (static::getContainer()->get('doctrine')->getRepository(Car::class)->findOne('DESC'))[0]['id'];
        static::createClient()->request('GET', '/api/cars/' . $carId + 1);

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
        static::createClient()->request('POST', '/api/cars', [
            'json' => [
                'brand' => 'BMW',
                'model' => 'X6',
                'color' => 'Black',
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Car',
            '@type' => 'Car',
            'brand' => 'BMW',
            'model' => 'X6',
            'color' => 'Black',
            'reviews' => [],
        ]);
        $this->assertMatchesResourceItemJsonSchema(Car::class);
    }

    public function testCreateCarWithEmptyBrand(): void
    {
        static::createClient()->request('POST', '/api/cars', [
            'json' => [
                'brand' => "",
                'model' => 'X6',
                'color' => 'Black',
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@type' => 'ConstraintViolationList',
            'detail' => 'brand: This value should not be blank.',
            'hydra:title' => 'An error occurred',
        ]);
    }

    public function testCreateCarWithMissingModelAndColor(): void
    {
        static::createClient()->request('POST', '/api/cars', [
            'json' => [
                'brand' => "BMW",
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@type' => 'ConstraintViolationList',
            'detail' => 'model: This value should not be blank.
color: This value should not be blank.',
            'hydra:title' => 'An error occurred',
        ]);
    }

    public function testCreateCarWithIntegerBrand(): void
    {
        static::createClient()->request('POST', '/api/cars', [
            'json' => [
                'brand' => 1,
            ]
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@id' => '/api/errors/400',
            '@type' => 'hydra:Error',
            'title' => 'An error occurred',
            'detail' => "The type of the \"brand\" attribute must be \"string\", \"integer\" given.",
        ]);
    }

    public function testPatch(): void
    {
        $carId = (static::getContainer()->get('doctrine')->getRepository(Car::class)->findOne())[0]['id'];

        static::createClient()->request('PATCH', '/api/cars/' . $carId, [
            'json' => [
                'color' => 'Blue',
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => '/api/cars/' . $carId,
            'id' => $carId,
            'color' => 'Blue',
        ]);
    }

    public function testPut(): void
    {
        $carId = (static::getContainer()->get('doctrine')->getRepository(Car::class)->findOne())[0]['id'];

        static::createClient()->request('PUT', '/api/cars/' . $carId, [
            'json' => [
                'brand' => 'BMW',
                'model' => 'X6',
                'color' => 'Black',
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Car',
            '@type' => 'Car',
            'brand' => 'BMW',
            'model' => 'X6',
            'color' => 'Black',
            'reviews' => [],
        ]);
    }

    public function testDelete(): void
    {
        $carId = (static::getContainer()->get('doctrine')->getRepository(Car::class)->findOne())[0]['id'];
        static::createClient()->request('DELETE', '/api/cars/' . $carId);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Car::class)->findOneBy(['id' => $carId])
        );
    }

    public function testGetLatestTopRatedCarReviews(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $car = new Car();
        $car->setBrand('BMW');
        $car->setModel('x6');
        $car->setColor('Black');

        $review1 = new Review();
        $review1->setStarRating(5);
        $review1->setReviewText('Great car!');
        $review1->setCar($car); 

        $review2 = new Review();
        $review2->setStarRating(9);
        $review2->setReviewText('Fantastic car!');
        $review2->setCar($car); 

        $entityManager->persist($car);
        $entityManager->persist($review1);
        $entityManager->persist($review2);
        $entityManager->flush();


        $client->request('GET', "/api/cars/{$car->getId()}/reviews/latest-top-rated");
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $this->assertJsonContains([
            [
                'starRating' => 9,
                'reviewText' => 'Fantastic car!'
            ]
        ]);
    }

    public function testGetLatestTopRatedCarReviewsNotFound(): void
    {
        $carId = (static::getContainer()->get('doctrine')->getRepository(Car::class)->findOne('DESC'))[0]['id'] + 1;

        static::createClient()->request('GET', "/api/cars/{$carId}/reviews/latest-top-rated");

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $this->assertJsonContains([
            'error' => 'no reviews found'
        ]);
    }
}
