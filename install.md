# How to setup Laravel project using Caddy in Debian OS.

# Server information
IP `172.20.0.73`
URL: https://plg.parknjetseatac.com

## Login into the server
1. Connect Your Open VPN
2. Enter Command From Terminal `ssh root@172.20.0.73`;

## Install Php 8.2 fpm without appache2
`sudo dpkg -l | grep php | tee packages.txt`
`sudo apt install apt-transport-https lsb-release ca-certificates wget -y`
`sudo wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg` 
`sudo sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'`
`sudo apt update`
`sudo apt install php8.2 php8.2-cli php8.2-{bz2,curl,mbstring,intl}`
`sudo apt install php8.2-fpm`

## Install php extenstions for Laravel
`sudo apt install openssl php8.2-bcmath php8.2-curl php8.2-mbstring php8.2-mysql php8.2-tokenizer php8.2-xml php8.2-zip`

## Install Caddy
`sudo apt install -y debian-keyring debian-archive-keyring apt-transport-https`
`curl -1sLf 'https://dl.cloudsmith.io/public/caddy/testing/gpg.key' | sudo gpg --dearmor -o /usr/share/keyrings/caddy-testing-archive-keyring.gpg`
`curl -1sLf 'https://dl.cloudsmith.io/public/caddy/testing/debian.deb.txt' | sudo tee /etc/apt/sources.list.d/caddy-testing.list`
`sudo apt update`
`sudo apt install caddy`

## Install composser
Flow these command or read this blog for install composer https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-debian-11

`cd ~`
`sudo apt update`
`curl -sS https://getcomposer.org/installer -o composer-setup.php`
HASH=`curl -sS https://composer.github.io/installer.sig`
`echo $HASH`

You will see Output
55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae

`php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"`

Output
Installer verified

`sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer`

Output
All settings correct for using Composer
Downloading...

Composer (version 2.3.10) successfully installed to: /usr/local/bin/composer
Use it: php /usr/local/bin/composer

Finaly run `composer`;

## Clone the repository
Go to var/www/html and clone repository
Run: `cd /var/www/html`;
Run: `git clone https://gitlab.com/techcliqs/parknjet_reservation.git`

## Setup Caddyfile
Open caddy file: `nano /etc/caddy/Caddyfile`
Put this code into the file:

:80 {
    root * /var/www/html/parknjet_reservation/public
    file_server
    encode zstd gzip
    php_fastcgi unix//run/php/php8.2-fpm.sock
}

## GO to project directory
`cd /var/www/html/parknjet_reservation`
`composer install`

Note: if you are using vite in plg server. Recomanded dont use vite in plg or production server.
Build the project in local and upload it into the server.
`npm install`
`npm install vite@latest`
`npm run dev` or `npm run build`

## Link the storage file
`php artisan storage:link`

## Update .env for plg server
APP_URL=https://plg.parknjetseatac.com/
ASSET_URL=https://plg.parknjetseatac.com/

## Give permission to all folder
`chown -R www-data:www-data *`

## Reload caddy
`systemctl reload caddy`

# Done! Visite the website: https://plg.parknjetseatac.com