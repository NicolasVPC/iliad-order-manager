# iliad-order-manager
Iliad Order Manager is a system to monitor and manage daily user orders.

## documentation environment
## set up env variables
You need to create two `.env` files inside `/doc`: `db.env` and `wiki.env`

`db.env` </br>
``` env
MARIADB_ROOT_PASSWORD=securerootpassword
MYSQL_DATABASE=iom-wiki
MYSQL_USER=iom-wikijs
MYSQL_PASSWORD=securepassword
```
Choose the values you prefer, just keep in mind they have to match their counterparts in `wiki.env`

`wiki.env`
``` env
ADMIN_EMAIL=example@mail.com
ADMIN_PASS=secureadminpassword
DB_USER=iom-wikijs
DB_PASS=securepassword
DB_NAME=iom-wiki
```

## Run the documentation
The documentation can be up with
``` bash
docker-compose -f ./doc/docker-compose.yaml up -d
```
The backup repository is: https://github.com/NicolasVPC/iom-wikijs-storage
How to setup Git as a backup repository: https://docs.requarks.io/storage/git

## Getting start with the project
> All the commands will be launched from the `/app` folder: `cd ./app`

first of all we need to run `symfony check:requirements` to know what we need to run a Symfony project.

**no PHP binaries detected** </br>
if the output is `no PHP binaries detected`, it means that you don't have php installed on the system, running `sudo apt install php-common libapache2-mod-php php-cli` will fix this issue.

**simplexml_import_dom() must be available** </br>
this error means you must install SimpleXML extension.
``` bash                   
 [ERROR]                                          
 Your system is not ready to run Symfony projects 
                                                  

Fix the following mandatory requirements
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

 * simplexml_import_dom() must be available
   > Install and enable the SimpleXML extension.
```

Running: `sudo apt install php-xml` will fix the issue.

**Optional recommendations to improve your setup** </br>
mb_strlen() should be available
Install and enable the mbstring extension.

intl extension should be available
Install and enable the intl extension (used for validators).

PDO should have some drivers installed (currently available: none)
Install PDO drivers (mandatory for `Doctrine`).
 
`sudo apt install php-mbstring php-intl php8.3-mysql`

### Other tools needed
Doctrine: `composer req symfony/orm-pack` </br>
PhpUnit: `composer require symfony/test-pack --dev` </br>
MakerBundle: `composer require symfony/maker-bundle --dev`

## How to run
set the `.env` file inside `/app`:
``` env
APP_ENV=dev
APP_SECRET=


MYSQL_ROOT_PASSWORD=securerootpassword
MYSQL_USER=tester
MYSQL_PASSWORD=securepassword
MYSQL_DATABASE=app

DATABASE_URL=mysql://${MYSQL_USER}:${MYSQL_PASSWORD:-securepassword}@127.0.0.1:8088/${MYSQL_DATABASE:-app}?serverVersion=${MYSQL_VERSION:-8.4}&charset=${MYSQL_CHARSET:-utf8mb4}

MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
```

set the `db.env` file inside `/app/db`:
> keep in mind that they must match the variables inside `/app/.env`
``` env
MYSQL_ROOT_PASSWORD=securerootpassword
MYSQL_USER=tester
MYSQL_PASSWORD=securepassword
MYSQL_DATABASE=app
```
<>
`docker compose up -d` should start the php and database services.
enable the redis queue: `docker compose exec -d php bin/console messenger:consume redis_queue --time-limit=3600 --memory-limit=128M -vv`