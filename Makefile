SERVICE=php

all: up

up:
	docker-compose up -d --build

setup:
	rm -rf ./config/camagru.sqlite
	rm -rf public/uploads/*
	docker-compose exec $(SERVICE) php config/setup.php

stop:
	docker-compose down -v --remove-orphans

re: stop up

