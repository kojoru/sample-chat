# sample-chat
I try to write a simple polling chat in modern php without any frameworks

Running it
----------

1. Clone the repository.

2. Install [composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) and
   [docker-compose](https://docs.docker.com/compose/install/#install-compose)

3. Run ``` composer install --ignore-platform-reqs ``` to install the dependencies.

4. Linux (e. g. DigitalOcean's composer droplet) prevents php-fpm's user from writing the database so
   ``` chown www-data database ``` if that happens.

5. Run ``` docker-compose up ``` in the folder. It will install everything it needs to run the app in two docker 
   containers and start hosting the result on http://localhost:8081 (Port can be changed in ```docker-compose.yml```,
   e. g. ``` sed -i 's/8081/80/' docker-compose.yml ```)
