# First step

Build docker containers

```bash
docker-compose -f docker-compose.yml -f docker-compose.override.yml up
```

# Second step

Set proper permissions for scripts

```bash
chmod 755 scripts/in-php.sh
chmod 755 scripts/restart-test.sh
```

# Third step

Get in container

```bash
./scripts/in-php.sh
```

# Fourth step

While in the container, create database, run migrations and fetch all persons

```bash
./scripts/restart-test.sh
```

# Thirst step

Consume all messages

```bash
symfony console messenger:consume async-json --env=dev -vvv
```
