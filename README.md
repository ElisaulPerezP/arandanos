archivo de configuracion para una raspberry pi zero 2w con ubuntu server 22.04.4 instalado.


-------------------------------------------------------------------------------------------
#!/bin/bash

# Detiene la ejecución si ocurre un error
set -e

# Actualiza los paquetes del sistema
sudo apt update
sudo apt upgrade -y

# Instala Apache
sudo apt install apache2 -y

# Instala PHP
sudo apt install php libapache2-mod-php php-xml php-dom php-curl php-zip -y

# Instala herramientas de descompresión
sudo apt install unzip p7zip-full -y

# Instala SQLite
sudo apt install sqlite3 php-sqlite3 -y

# Habilita el módulo PHP en Apache
sudo a2enmod php8.1
sudo systemctl restart apache2

# Instala Python y RPi.GPIO para GPIOs
sudo apt install python3 python3-pip -y
sudo pip3 install RPi.GPIO

# Instalar Composer
EXPECTED_CHECKSUM="$(wget -q -O - https://composer.github.io/installer.sig)"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]
then
    >&2 echo 'ERROR: Invalid installer checksum'
    rm composer-setup.php
    exit 1
fi

sudo php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# Instalar nvm, npm y Node.js
wget -qO- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.1/install.sh | bash

# Esta línea asume que el script de nvm se añadirá automáticamente al .bashrc o .zshrc
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
nvm install node # Esto instala la última versión de Node.js y npm

echo "Instalación completada. Apache, PHP, SQLite, RPi.GPIO, Composer, nvm y npm están configurados."

-------------------------------------------------------------------------------------------

una vez preparado el sistema debe clonar este repocitorio de guit, generando una clave ssh
recuerde, el comando para generarlo es:
ssh-keygen -t rsa -C "escriba aqui algun comentario"
le sera pedido un nombre, sugerencia use sshArandanosRaspberry.
hecho eso ve la clave en consola:
cat sshArandanosRaspberry
copiela y añadala a las claves de desarroyo de github.
inicie un agente ssh con el comando
eval "$(ssh-agent -s)"
e inscriba la clave con ssh-add /direccion/a/la/clave
ahora clone el repositorio.
git clone /direccion/del/repositorio/en/github

---------------------------------------------------------------------------------------------

ingrese a la carpeta del proyecto y lance el comando
composer install
y luego lance el comando
npm install
---------------------------------------------------------------------------------------------

genere la base de datos:
touch database/arandanos.sqlite

copiar el archivo de configuracion de entorno
cp .env.example .env

generar la clave de la aplicacion:
php artisan key:generate

migrar la base de datos:
php artisan migrate

-------------------------------------------------------------------------------------------

ahora debes configurar apache.
crea el archivo de configuracion del sitio arandanos:
sudo nano /etc/apache2/sites-available/arandanos.conf



arandanos.config debe tener este contenido:
----------------------------------------------------------------------------------------------
<VirtualHost *:80>
    ServerName arandanos.local
    ServerAlias www.arandanos.local
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/arandanos/public

    <Directory /var/www/arandanos/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
-----------------------------------------------------------------------------------------------

habilita el modulo rewrite:
sudo a2enmod rewrite

habilita el archivo de configuracion:
sudo a2ensite arandanos.conf

deshabilita el sitio por defecto de apache:
sudo a2dissite 000-default.conf

reinicia apache:
sudo systemctl restart apache2
---------------------------------------------------------------------------------------------

edita el archivo sudo nano /etc/hosts

añadiendo la linea:

192.168.x.x  arandanos.local www.arandanos.local
en la .x.x debe colocar la ip de la raspberry en la red local.
---------------------------------------------------------------------------------------------

para finalizar debe otorgar los permisos y propiedad a apache:
sudo chown -R www-data:www-data /var/www/arandanos/storage
sudo chown -R www-data:www-data /var/www/arandanos/bootstrap/cache
sudo chmod -R 775 /var/www/arandanos/storage
sudo chmod -R 775 /var/www/arandanos/bootstrap/cache
sudo chown -R www-data:www-data /var/www/arandanos/database
sudo chmod 664 /var/www/arandanos/database/arandanos.sqlite
sudo chmod 775 /var/www/arandanos/database
sudo systemctl restart apache2

-----------------------------------------------------------------------------------------------
# Colas y trabajadores
en el archivo .env:

QUEUE_CONNECTION=database

correr el comando que lanza el  trabajador:

php artisan queue:work

inscribir el crontab del sistema:

crontab -e

en el archivo que aparece integrar esto como una nueva linea:

* * * * * cd /var/www/arandanos && php artisan schedule:run >> /dev/null 2>&1

----------------------------------------------------------------------------

crear el servicio para que se ejecute el worker recien inicie el sistema

sudo nano /etc/systemd/system/laravel-worker.service

en el archivo escribir:
[Unit]
Description=Laravel Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/arandanos/artisan queue:work --tries=3

[Install]
WantedBy=multi-user.target


luego recargar el demonio de maxwel:
sudo systemctl daemon-reload

iniciar el servicio:
sudo systemctl start laravel-worker

por ultimo, habilitar el servicio:
sudo systemctl enable laravel-worker

opcional, revisar el servicio:
sudo systemctl status laravel-worker

