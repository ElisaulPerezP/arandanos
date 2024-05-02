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

