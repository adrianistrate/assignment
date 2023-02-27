symfony console doctrine:database:drop --force
symfony console doctrine:migrations:migrate --no-interaction
symfony console app:fetch:persons --env=dev -vvv
