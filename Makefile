export NAME = example-service
export TAG ?= local
BUILD_ENV ?= local
BUILD_ARGS := --pull --build-arg BUILD_ENV=${BUILD_ENV}

ifeq ($(shell uname -s), Linux)
BUILD_ARGS += --build-arg USER_ID=$(shell id -u) --build-arg GROUP_ID=$(shell id -g)
endif

default:
	cp -n .env.example .env || true
	cd api; composer install
	make migrate-local-database

build:
	docker-compose -f docker-compose.${BUILD_ENV}.yml build ${BUILD_ARGS}

up:
	docker-compose -f docker-compose.${BUILD_ENV}.yml up

upd:
	docker-compose -f docker-compose.${BUILD_ENV}.yml up -d
	make -s port

down:
	docker-compose -f docker-compose.${BUILD_ENV}.yml down

port:
	URL=`docker-compose -f docker-compose.${BUILD_ENV}.yml port api 80`;\
	echo "\033[0;33mUrl: http://$${URL}\nPort: $${URL#*:}\033[0m"

migrate-local-database:
	php api/artisan migrate:fresh --database=default --seed --seeder=DefaultDatabaseSeeder
	php api/artisan migrate:fresh --database=legacy --seed --seeder=LegacyDatabaseSeeder --path=database/migrations/legacy
