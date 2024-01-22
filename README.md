## Description

- `Car` and `Review` entities implemented for database object mapping.
- `CarRepository` and `ReviewRepository` added for handling database queries.
- `#[ApiResource]` added to `Car` and `Review` entities for integrating with ApiPlatform.
- Input validations like `#[UniqueEntity]`, `#[NotBlank]` and `#[NotNull]` added to entities.
- `#[Groups]` added to entities to have control over properties read and write permissions for ApiPlatform.
- `CarReviewsController` added for getting five latest top-rated reviews of a car.
- Custom operation `latest_top_rated_car_reviews` added to `Car` entity for integrating to ApiPlatform.
- `CarTest` and `ReviewTest` added to handle APIs integration tests.
- `car.yaml` and `review.yaml` added for data fixtures.


## How to use:
**For initializing the project:**
- run `docker compose up --build -d`.
- run `docker exec -it php composer install`.
- run `docker exec -it php symfony console doctrine:schema:create`.
- run `docker exec -it php symfony console doctrine:migration:migrate`.
- run `docker exec -it php symfony console hautelook:fixtures:load` to load some sample data.

Open `localhost:8081/api` for using documentations and making requests to resources.

**For running integration tests:**
- run `docker exec -it php symfony console --env=test doctrine:schema:create` 
- run `docker exec -it php symfony console --env=test doctrine:migration:migrate` 
- run `docker exec -it php php bin/phpunit` 
