CONTAINER=camagru_php

all: setup start

setup:
	docker exec -it $(CONTAINER) php config/setup.php

start:
	docker exec -d $(CONTAINER) php -S 0.0.0.0:$(PORT) -t /app/public

stop:
	docker stop $(CONTAINER)

trdysty: stop start
