install:
	composer install
	npm install
	cp .env.example .env
	php artisan key:generate

serve:
	php artisan serve

migrate:
	php artisan migrate

fresh:
	php artisan migrate:fresh --seed

test:
	php artisan test

clear:
	php artisan optimize:clear
