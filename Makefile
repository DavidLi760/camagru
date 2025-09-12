SERVICE=php
PORT=8080

all: up setup

up:
	docker-compose up -d --build

setup:
	docker-compose exec $(SERVICE) php config/setup.php

stop:
	docker-compose down

re: stop up

