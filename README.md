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
sudo apt install php libapache2-mod-php -y

# Instala SQLite
sudo apt install sqlite3 php-sqlite3 -y

# Habilita el módulo PHP en Apache
sudo a2enmod php8.1
sudo systemctl restart apache2

# Instala Python y RPi.GPIO para GPIOs
sudo apt install python3 python3-pip -y
sudo pip3 install RPi.GPIO

echo "Instalación completada. Apache, PHP, SQLite y RPi.GPIO están configurados."
-------------------------------------------------------------------------------------------

