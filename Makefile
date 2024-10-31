CONTAINER_NAME=arcadyan-payment-service-api

git-hooks:
	@cp tools/pre-commit.sh .git/hooks/pre-commit
	@chmod +x .git/hooks/pre-commit

check-create-network:
	@chmod +x check-create-network.sh
	./check-create-network.sh arcadyan

install:
	make check-create-network
	make force-recreate
	make composer-install
	make migrate
	make git-hooks

up:
	make check-create-network
	docker-compose up -d

down:
	docker-compose down

bash:
	make up
	docker exec -it $(CONTAINER_NAME) sh

build:
	docker-compose build

force-recreate:
	docker-compose up -d --force-recreate --build

composer-install:
	make up
	docker exec $(CONTAINER_NAME) composer install --no-interaction --no-scripts

migrate:
	make up
	docker exec $(CONTAINER_NAME) php artisan migrate --seed

migrate-fresh:
	make up
	docker exec $(CONTAINER_NAME) php artisan migrate:fresh
	docker exec $(CONTAINER_NAME) php artisan migrate --seed

test:
ifdef FILTER
	make up
	#make clear
	docker exec -t $(CONTAINER_NAME) composer unit-test -- --filter="$(FILTER)"
else
	make up
	#make clear
	docker exec -t $(CONTAINER_NAME) composer unit-test
endif

logs:
	make up
	docker-compose logs --follow

clear:
	make up
	docker exec $(CONTAINER_NAME) sh -c "php artisan optimize:clear"

coverage-html:
	make up
	#make clear
	docker exec -t $(CONTAINER_NAME) composer test-coverage-html

format:
	make up
	docker exec -t $(CONTAINER_NAME) composer lint-fix
