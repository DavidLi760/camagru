SERVICE=web

build:
	docker-compose build

up:
	docker-compose up -d

up-logs:
	docker-compose up

down:
	docker-compose down

logs:
	docker-compose logs -f $(SERVICE)

clean:
	docker-compose down -v --rmi all

