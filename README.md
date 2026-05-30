## Vehicle Soft-Hold Service

This service provides an API to place a soft-hold on a vehicle for a specified duration. It is designed to be used in a car rental application, allowing customers to reserve a vehicle while they complete their booking process. 

The assignment can be found [here](https://github.com/bas-world/senior-developer-challenge-vehicle-hold-service).

[![Example](/docs/assets/example.png)](/docs/assets/example.png)

## Local Development

To run the service locally: 

```
git clone https://github.com/Baspa/senior-developer-challenge-vehicle-hold-service.git
cd senior-developer-challenge-vehicle-hold-service
docker compose up --build
```

This will start the service on `http://localhost:8080`.

## Quality Checks

The project ships with a few tools to keep the codebase healthy. Run them locally after `composer install`, or inside the running container with `docker compose exec app <command>`.

Run the test suite (Pest):

```
composer test
# or directly
./vendor/bin/pest
```

Run static analysis (Larastan, level 7):

```
./vendor/bin/phpstan analyse
```
