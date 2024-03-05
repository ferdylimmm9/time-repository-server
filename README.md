## Get Started

### Make sure you have install php, composer, dan docker.
### Command need to run

1. copy `.env.example` to `.env` and do adjustment if needed
2. run `docker compose up` to run docker
3. run in new terminal `docker exec -it time-repository-laravel.test-1 bash`
4. run `composer install` to install package
5. run `php artisan key:generate` to generate or rotate application key
6. run `php artisan migrate` or `php artisan migrate:fresh` to reset the DB
7. run `php artisan passport:keys --force` to generate oauth2 keys
8. run `php artisan passport:client --password --provider users` to support oauth2 client on `User` and fill it at .env
9. run `php artisan db:seed` if any (OPTIONAL)
10. rum in new terminal `.vendor/bin/sail down` for closing docker.

### Things to aware

Always make sure to run `php artisan config:cache` if your `.env` has changed
or just run `php artisan config:clear` to avoid using cached value on development
in case you forgetful or heavily adjusting
