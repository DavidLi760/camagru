SERVICE=php
PORT=8080

all: up

up:
	docker-compose up -d --build

setup:
	rm -rf ./config/camagru.sqlite
	docker-compose exec $(SERVICE) php config/setup.php

stop:
	docker-compose down

re: stop up setup

