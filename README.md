# Arandanos: Aplicación IoT para Gestión de Cultivos

## Descripción

**Arandanos** es una aplicación de tipo IoT (Internet of Things) que permite a un agricultor gestionar los aspectos más importantes de un cultivo, tales como:

- Irrigación
- Fertilización
- Asignación de tareas al personal
- Ficha técnica de cada planta
- Historial sanitario de cada planta
- Historial de aplicaciones de agroinsumos
- Manejo de stock de bodega
- Estadísticas de producción

Inicialmente implementado para **Arandanos de mi Pueblo**, está optimizada para la gestión de cultivos de arándanos. Sin embargo, puede ser usado sin ninguna adaptación para la gestión de todo tipo de cultivo que implemente sistemas de microrriego.

## Hardware Compatible

**Arandanos** está diseñado para controlar el siguiente hardware:

1. Sistema de control de Arandanos (hardware de desarrollo propio)
2. Botón de parada de emergencia (hardware genérico)
3. Electrovalvula de llenado de tanques (hardware genérico)
4. Sensores de nivel de tanques (2 unidades, hardware genérico)
5. Medidores de flujo (2 unidades, hardware genérico)
6. Inyectores de fertilizante líquido (2 unidades, hardware genérico)
7. Bomba principal de propulsión de agua (hardware genérico)
8. Bomba de respaldo (hardware genérico)
9. Electrovalvulas de cultivo (8 unidades, hardware genérico)

## Beneficios

**Arandanos** dota a un productor con el mecanismo para realizar irrigaciones en sectores específicos de su cultivo, permitiendo:

- Entregar la responsabilidad de la fertilización y la irrigación a un sistema autónomo.
- Manejo y supervisión directa por el productor o su agrónomo de confianza.
- Evitar la incidencia de la intervención de terceros en este aspecto crítico de un cultivo.
- Gestionar de forma sistematizada los aspectos administrativos generales de campo relacionados con el negocio.

## Índice
1. [Introducción](#introducción)
2. [Instalación y Preparación del Sistema](#instalación-y-preparación-del-sistema)
3. [Guía del Usuario](#guía-del-usuario)
4. [Documentación Técnica](#documentación-técnica)
5. [Arquitectura](#arquitectura)
6. [Requisitos](#requisitos)
7. [Pruebas](#pruebas)
8. [Mantenimiento](#mantenimiento)
9. [API](#api)
10. [Seguridad](#seguridad)
11. [Legal](#legal)

## Introducción

**Arandanos** es una aplicación web desarrollada en PHP mediante el uso de Laravel, servida por Apache y ejecutada en una Raspberry Pi Zero 2W con Ubuntu Server 22.04 LTS. La aplicación implementa cinco scripts de Python para el manejo del hardware GPIO de la Raspberry Pi, los cuales se comunican vía API con la aplicación web. A su vez, la aplicación se comunica a través de internet con la aplicación de gestión en línea del cultivo, alojada en la nube.

### Requisitos

Para un uso satisfactorio de esta aplicación, se necesita:
- [Hardare compatible](#hardware-compatible).
- Acceso a la aplicación de gestión en línea del cultivo, en la nube.
- Acceso a wifi con internet en el lugar de la instalacion del hardware

### Funcionalidades

**Arandanos** permite:

- **Agendar Irrigaciones**: Programar irrigaciones del cultivo en zonas específicas, permitiendo la inyección de dos tipos diferentes de fertilizantes. Esto incluye el control de la cantidad de agua y de fertilizante para cada evento de irrigación en cada zona.
  
- **Comprobar el Estado del Sistema**: Consultar el estado del sistema tanto localmente como en línea, permitiendo conocer desde cualquier lugar si se ha presentado algún tipo de error, si hay tareas próximas, y qué tareas se están llevando a cabo en el cultivo, entre otros aspectos.
  
- **Recepción de Reportes de Errores**: Recibir reportes de errores en la aplicación en la nube, tales como falta de agua en los tanques, fugas críticas, fugas leves, filtros obstruidos, entre otros posibles errores.

- **Acceso a Registros del Cultivo**: Acceder a los registros del cultivo tanto localmente como en línea, incluyendo irrigaciones efectuadas, cantidad de agua utilizada, cantidad de fertilizante aplicado, tareas realizadas, tareas pendientes, bitácora de trabajadores, avisos de stock, entre otros.


## Instalación y Preparación del Sistema
### Instalación

Para el personal tecnico:
La instalacion del sofware en la placa raspberry requiere de configuraciones especificas que permiten correr este robusto sistema en una placa con prestasiones minimas como la raspberry pi zero 2w. El equipo de desarroyo se disculpa de antemano por no usar sistemas de dokerizacion, pero dadas las caracteristicas del hardware, el uso de contenedores no es adecuado, cualquier inconveniente favor comunicarlo al equipo de desarroyo. 

los siguientes son los pasos a seguir para tener la placa configurada adecuadamente con la aplicacion corriendo en ella:

#### Crear el archivo de script:

```bash
nano setup.sh
```
**En el archivo escribir:**

```bash

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

```
#### Hacerlo ejectuable
```bash
chmod +x setup.sh
```

#### Ejecutarlo
```bash
./setup.sh
```


#### Clonar desde github

Una vez preparado el sistema debe clonar este repocitorio de guit, generando una clave ssh
recuerde, el comando para generarlo es:
---

```bash

ssh-keygen -t rsa -C "escriba aqui algun comentario"

```

le sera pedido un nombre, sugerencia use sshArandanosRaspberry.

hecho eso, ve la clave en consola:

```bash
cat sshArandanosRaspberry
```
copiela y añadala a las claves de desarroyo de github.

inicie un agente ssh con el comando
```bash
eval "$(ssh-agent -s)"
```
e inscriba la clave con:
```bash
ssh-add /direccion/a/la/clave
```

ahora clone el repositorio.
```bash
git clone /direccion/del/repositorio/en/github
```
---------------------------------------------------------------------------------------------

ingrese a la carpeta del proyecto y lance el comando:
```bash
composer install
```
eso instalara todas las dependencias php del proyecto, y luego lance el comando:

```bash
npm install
```

```bash
eso instalara todas las dependencias js del proyecto.
```

---------------------------------------------------------------------------------------------

genere la base de datos:

```bash
touch database/arandanos.sqlite
```


copiar el archivo de configuracion de entorno

```bash
cp .env.example .env
```

generar la clave de la aplicacion:

```bash
php artisan key:generate
```

migrar la base de datos:

```bash
php artisan migrate
```

-------------------------------------------------------------------------------------------

ahora debes configurar apache.
crea el archivo de configuracion del sitio arandanos:

```bash
sudo nano /etc/apache2/sites-available/arandanos.conf
```

arandanos.config debe tener este contenido:

```bash
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
```


habilita el modulo rewrite:

```bash
sudo a2enmod rewrite
```

habilita el archivo de configuracion:

```bash
sudo a2ensite arandanos.conf
```

deshabilita el sitio por defecto de apache:

```bash
sudo a2dissite 000-default.conf
```

reinicia apache:

```bash
sudo systemctl restart apache2
```


edita el archivo sudo nano /etc/hosts

añadiendo la sieguiente linea: 
(en la .x.x debe colocar la ip de la raspberry en la red local.)

```bash
192.168.x.x  arandanos.local www.arandanos.local
```



para finalizar debe otorgar los permisos y propiedad a apache:

```bash
sudo chown -R www-data:www-data /var/www/arandanos/storage
sudo chown -R www-data:www-data /var/www/arandanos/bootstrap/cache
sudo chmod -R 775 /var/www/arandanos/storage
sudo chmod -R 775 /var/www/arandanos/bootstrap/cache
sudo chown -R www-data:www-data /var/www/arandanos/database
sudo chmod 664 /var/www/arandanos/database/arandanos.sqlite
sudo chmod 775 /var/www/arandanos/database
sudo systemctl restart apache2
```

# Colas y trabajadores
en el archivo .env:

```dotenv
QUEUE_CONNECTION=database
```

correr el comando que lanza el  trabajador:

```bash
php artisan queue:work
```

inscribir el crontab del sistema:

```bash
crontab -e
```

en el archivo que aparece integrar esto como una nueva linea:

```crontab
* * * * * cd /var/www/arandanos && php artisan schedule:run >> /dev/null 2>&1
```

crear el servicio para que se ejecute el worker recien inicie el sistema

```bash
sudo nano /etc/systemd/system/laravel-worker.service
```

en el archivo escribir:

```service
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
```


luego recargar el demonio:

```bash
sudo systemctl daemon-reload
```

iniciar el servicio:
```bash
sudo systemctl start laravel-worker
```

por ultimo, habilitar el servicio:
```bash
sudo systemctl enable laravel-worker
```

opcional, revisar el servicio:
```bash
sudo systemctl status laravel-worker
```

cambiar la propiedad de la carpeta de sistema sys/class/gpio
```bash
sudo chown -R www-data:www-data /sys/class/gpio
sudo chmod -R 777 /sys/class/gpio
```

entregar los permisos de gpio a www-data:
abrir el archivo con el siguiente comando:

```bash
sudo nano /etc/udev/rules.d/99-gpio.rules
```

reemplazar su contenido con estas lineas:

```rules
SUBSYSTEM=="gpio", KERNEL=="gpio*", ACTION=="add", RUN+="/bin/chown www-data:gpio /sys/class/gpio/export /sys/class/gpio/unexport"
SUBSYSTEM=="gpio", KERNEL=="gpio*", ACTION=="add", RUN+="/bin/chmod 770 /sys/class/gpio/export /sys/class/gpio/unexport"
SUBSYSTEM=="gpio", KERNEL=="gpio*", ACTION=="add", RUN+="/bin/sh -c 'chown www-data:gpio /sys/class/gpio/gpio*/direction /sys/class/gpio/gpio*/value'"
SUBSYSTEM=="gpio", KERNEL=="gpio*", ACTION=="add", RUN+="/bin/sh -c 'chmod 770 /sys/class/gpio/gpio*/direction /sys/class/gpio/gpio*/value'"
```
Por ultimo, actualizar las reglas. 

```bash
sudo udevadm control --reload-rules
sudo udevadm trigger
```

## Guía del Usuario
Nota para el usuario:
No se recomienda ni se autoriza al usuario para la intervencion de este sofware. EL hardware adquirido tendra instalada la aplicacion, con sus funionalidades testeadas. Por tanto, el unico requisito  que se debe cumplir para que el sistema inicie, es que el usuario tenga configurado su wifi con el nombre y la contraseña que reportó al personal que configuro su sustema. de no ser asi, el aparato no podra conectarse y el usuario no podra menejar el aparato de ninguna manera. Si el usuario pretende cambiar la contraseña o nombre de su red wifi, se recomienda contactar con el personal de **Arandanos** para realizar las actualizaciones pertinentes.

### Uso
**En la nuve**
paso 1: Registrese en la aplicacion de gestion en linea del cultivo. 
paso 2: Comuniquese con el personal de administracion para generar su role de productor. 
paso 3: en el menu de navegacion valla a cultivos, en esta ventana de clic a Crear Nuevo Cultivo
paso 4: cree el cultivo y acceda a ver detalles. alli podra gestionar todos los aspectos del cultivo en la nuve. debe proceder conectando el hardware a su cuenta en la nuve, de la siguiente manera. 

**En local**
Paso 1: conectese a la misma red wifi que reporto al personal tecnico para la preparacion del dispositivo
paso 2: abra un navegador web y escriba arandanos.local
paso 3: registrese en la parte superior de la pantalla, en el vinculo Registro.
paso 4: ingrese con su usuario y contraseña. 
paso 5: inscrba un cultivo con las credenciales entregadas por el personal de mantenimiento de la aplicacion. 
paso 6: navegue la aplicacion, cualquier error sera reportado como un mensaje en la parte superior del navegador, si aparece un error, no dude en consultar al personal de la aplicaicon. 


### Preguntas Frecuentes (FAQ)
- **Como puedo agendar un riego de mi cultivo?**: [Respuesta a la pregunta 1]
- **Como puedo cancelar una agenda de riego?**: [Respuesta a la pregunta 2]
- **Como puedo apagar mi sistema?**: [Respuesta a la pregunta 2]
- **Como sé si se efectuo el riego?**: [Respuesta a la pregunta 2]
- **Como veo los errores del sistema?**: [Respuesta a la pregunta 2]
- **Mi sistema no inicia, como puedo iniciarlo?**: [Respuesta a la pregunta 2]
- **No encuentor mi sistema al acceder a arandanos.local, que pasa?**: [Respuesta a la pregunta 2]
- **El sistema no sicnroniza con la nuve, que hago?**: [Respuesta a la pregunta 2]


## Documentación Técnica
### Estructura del Proyecto
La aplicacion se compone de un conjunto de scripts de base que son lanzados por la aplicacion al iniciarse el sistema, pueden ser detneidos tambien por la aplicacion. los scripts son reportados a continuacion con sus posibles argumentos. tenga en cuenta que las direcciones api son locales pues corren sobre el mismo sitema que la aplicacion princial.
```bash
stopManual.py /home/elisaul/ws/arandanos/pythonScripts/input_pins_file_stop.txt http://127.0.0.1:8001/api/stop, 
```
Este script gestiona el pin gpio con el numero reportado en el archivo **input_pins_file_stop.txt** al precesntarse un evento de tipo rising en ese pin, se consulta la direccion api reportada como segundo argumento, alli se presenta la parada para ser tramitada por la aplicacion principal. 

```bash
flujo.py /home/elisaul/ws/arandanos/pythonScripts/input_pins_file_flujo.txt http://127.0.0.1:8001/api/flujo/conteo http://127.0.0.1:8001/api/flujo/apagado
```
Este script gestiona el pin GPIO con el número reportado en el archivo input_pins_file_flujo.txt. Al presentarse un evento de tipo rising en ese pin, el script incrementa un contador y cada 0.5 segundos reporta el conteo a la dirección API indicada como segundo argumento. Cuando el script se apaga, envía una notificación a la dirección API reportada como tercer argumento.

```bash
impulsores.py /home/elisaul/ws/arandanos/pythonScripts/output_pins_file_impulsores.txt /home/elisaul/ws/arandanos/pythonScripts/output_neg_pins_file_impulsores.txt http://127.0.0.1:8001/api/impulsores http://127.0.0.1:8001/api/impulsores/estado http://127.0.0.1:8001/api/impulsores/apagado
```
Este script gestiona los pines GPIO reportados en los archivos output_pins_file_bomba.txt (lógica positiva) y output_neg_pins_file_bomba.txt (lógica negativa). El script recibe comandos de la API para encender o apagar las bombas y reporta su estado cada 10 segundos a la dirección API indicada. Al apagarse, envía una notificación a la dirección API reportada como quinto argumento.

```bash
inyectores.py /home/elisaul/ws/arandanos/pythonScripts/output_pins_file_inyectores.txt /home/elisaul/ws/arandanos/pythonScripts/output_neg_pins_file_inyectores.txt http://127.0.0.1:8001/api/inyectores http://127.0.0.1:8001/api
 inyectores/estado http://127.0.0.1:8001/api/inyectores/apagado
```
Este script gestiona los pines GPIO reportados en los archivos output_pins_file_inyectores.txt (lógica positiva) y output_neg_pins_file_inyectores.txt (lógica negativa). El script recibe comandos de la API para encender o apagar los inyectores y controlar el ciclo de trabajo PWM de los inyectores. Reporta su estado cada 10 segundos a la dirección API indicada. Al apagarse, envía una notificación a la dirección API reportada como quinto argumento.

```bash
tanques.py /home/elisaul/ws/arandanos/pythonScripts/input_pins_file_tanques.txt /home/elisaul/ws/arandanos/pythonScripts/output_pins_file_tanques.txt /home/elisaul/ws/arandanos/pythonScripts/output_neg_pins_file_tanques.txt http://127.0.0.1:8001/api/tanques http://127.0.0.1:8001/api/tanques/estado http://127.0.0.1:8001/api/tanques/apagado
```
Este script gestiona los pines GPIO reportados en los archivos input_pins_file_llenado.txt (sensores de nivel), output_pins_file_llenado.txt (electroválvulas de lógica positiva) y output_neg_pins_file_llenado.txt (electroválvulas de lógica negativa). El script recibe comandos de la API para llenar o esperar, y reporta su estado cada 10 segundos a la dirección API indicada. Al apagarse, envía una notificación a la dirección API reportada como sexto argumento.


```bash
selector.py /home/elisaul/ws/arandanos/pythonScripts/output_pins_file_selector.txt /home/elisaul/ws/arandanos/pythonScripts/output_neg_pins_file_selector.txt http://127.0.0.1:8001/api/selector http://127.0.0.1:8001/api/selector/estado http://127.0.0.1:8001/api/selector/apagado'
```
Este script gestiona los pines GPIO reportados en los archivos output_pins_file_electrovalvulas.txt (lógica positiva) y output_neg_pins_file_electrovalvulas.txt (lógica negativa). El script recibe comandos de la API para encender o apagar las electrovalvulas y reporta su estado cada 10 segundos a la dirección API indicada. Al apagarse, envía una notificación a la dirección API reportada como quinto argumento.

```bash
stopTotal.py /home/elisaul/ws/arandanos/pythonScripts/pins.txt /home/elisaul/ws/arandanos/pythonScripts/pinsNegativ.txt'
```
Este script gestiona los pines GPIO reportados en los archivos pins.txt (pines con lógica positiva) y pinsNegativ.txt (pines con lógica negativa). El script exporta, configura y enciende los pines listados en los archivos. Los pines con lógica positiva se encienden configurándolos a 1, mientras que los pines con lógica negativa se encienden configurándolos a 0.

**Manejo del sistema**
El modelo EstadoSistema alberga el estado actual del sistema. solo debe haber UNA entrada en esa tabla, correspondiente al estado actual del sistema. 

sus campos fillables son:
        's0_id',
        's1_id',
        's2_id',
        's3_id',
        's4_id',
        's5_id',
los cuales son id que apuntan al la tabla respectiva de cada sistema, anotando el estado actual. asi pues, si el estado de s0 es la entrada de la tabla s0 apuntada por la tabla estadoSistema. si se hace una anotacion nueva en s0 debe actualizarse estadoSistema para reflejar el cambio. asi, cualquier script subsistema que requiera del estado de s0 tendra una vision actualizada de s0 al consultar la tabla estadoSistema. 

las responsabilidades de accion sobre el modelo estadoSistema se asignan asi:

#### s0 Sistema de stop
 Sera generada por la funcion reportStop en el controlador ApiController, en cuyo caso asignara el estado como 
 payload = {'estado': 'Parada activada','sensor3': value }

de encontrarse el estado actual 'Parada activada' se guardará 'Parada desactivada', y viseversa.  en ambos casos se acompaña la creacion del s0 con la actualizacion en la tabla de estado y la emicion del evento de inicio o detencion, 

#### s1 sistema de tanques
Sus entradas seran generadas por dos funcions en el controlador ApiController, reportTanquesState y reportTanquesShutdown, estas funciones guardan en base de datos el estado completo de los pines del sistema, o el estado a false, o inactivo. 
Por otra parte, los comandos asociados a s1 en la base de datos, se iran modificando por parte de un job, hardware, del que se hablara mas adelante. 

#### s2 sistema de electrovalvulas
Sus entradas serán generadas por dos funciones en el controlador ApiController: reportState y reportShutdown. Estas funciones guardan en la base de datos el estado completo de los pines del sistema, o el estado a false (inactivo). Además, los comandos asociados a s2 en la base de datos se pueden obtener a través de la función getSelectorCommand.

#### s3 Sistema de bombas
Sus entradas serán generadas por dos funciones en el controlador ApiController: reportImpulsoresState y reportImpulsoresShutdown. Estas funciones guardan en la base de datos el estado completo de los pines del sistema, o el estado a false (inactivo). Los comandos asociados a s3 en la base de datos se pueden obtener a través de la función getImpulsoresCommand.

#### s4 Sistema de inyeccion de fertilizante
Sus entradas serán generadas por dos funciones en el controlador ApiController: reportInyectoresState y reportInyectoresShutdown. Estas funciones guardan en la base de datos el estado completo de los pines del sistema, o el estado a false (inactivo). Los comandos asociados a s4 en la base de datos se pueden obtener a través de la función

#### s5 Sistema de sensado de flujo
Sus entradas serán generadas por dos funciones en el controlador ApiController: reportFlujoConteo y reportFlujoApagado. Estas funciones guardan en la base de datos el conteo de flujo del sistema, o el estado a false (inactivo).




### Comentarios en el Código
```python
# Ejemplo de comentario en el código
def ejemplo_funcion():
    """
    Esta función hace algo importante
    """
    pass
```

### Guía del Desarrollador
[Instrucciones para los desarrolladores que deseen contribuir al proyecto]

## Arquitectura
### Diagramas de Arquitectura
![Diagrama de Arquitectura](path/to/diagram.png)

### Especificaciones de Diseño
[Detalles sobre las decisiones de diseño y patrones utilizados]

## Requisitos
### Requisitos Funcionales
1. **Requisito Funcional 1**: [Descripción del requisito]
2. **Requisito Funcional 2**: [Descripción del requisito]

### Requisitos No Funcionales
1. **Requisito No Funcional 1**: [Descripción del requisito]
2. **Requisito No Funcional 2**: [Descripción del requisito]

## Pruebas
### Plan de Pruebas
[Descripción de la estrategia de pruebas]

### Casos de Prueba
```markdown
# Caso de Prueba 1
- **Descripción**: [Descripción del caso de prueba]
- **Pasos**: [Pasos para ejecutar la prueba]
- **Resultado Esperado**: [Resultado esperado]
```

## Mantenimiento
[Instrucciones para el mantenimiento de la aplicación, incluyendo actualizaciones, backups, y restauraciones]

### Registro de Cambios (Changelog)
[Historial de cambios realizados en la aplicación, incluyendo nuevas funcionalidades, mejoras y correcciones de errores]

## API
### Referencia de API
[Detalles sobre las interfaces de programación (API) expuestas por la aplicación, incluyendo endpoints, métodos, parámetros, y ejemplos de uso]

### Guía de Integración
[Instrucciones sobre cómo integrar la aplicación con otros sistemas o servicios, incluyendo ejemplos de código y mejores prácticas]

## Seguridad
### Políticas de Seguridad
[Normas y procedimientos para garantizar la seguridad de la aplicación y los datos que maneja]

### Guía de Cumplimiento
[Información sobre cómo la aplicación cumple con las normativas y estándares relevantes (e.g., GDPR, HIPAA)]

## Legal
### Términos y Condiciones
[Condiciones de uso de la aplicación, responsabilidades y limitaciones]

### Política de Privacidad
[Descripción de cómo se recopilan, usan, almacenan y protegen los datos de los usuarios]



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

------------------------------------------------------------------------
cambiar la propiedad de la carpeta de sistema sys/class/gpio

sudo chown -R www-data:www-data /sys/class/gpio
sudo chmod -R 777 /sys/class/gpio

entregar los permisos de gpio a www-data:
abrir el archivo con el siguiente comando:

sudo nano /etc/udev/rules.d/99-gpio.rules

reemplazar su contenido con estas lineas:

SUBSYSTEM=="gpio", KERNEL=="gpio*", ACTION=="add", RUN+="/bin/chown www-data:gpio /sys/class/gpio/export /sys/class/gpio/unexport"
SUBSYSTEM=="gpio", KERNEL=="gpio*", ACTION=="add", RUN+="/bin/chmod 770 /sys/class/gpio/export /sys/class/gpio/unexport"
SUBSYSTEM=="gpio", KERNEL=="gpio*", ACTION=="add", RUN+="/bin/sh -c 'chown www-data:gpio /sys/class/gpio/gpio*/direction /sys/class/gpio/gpio*/value'"
SUBSYSTEM=="gpio", KERNEL=="gpio*", ACTION=="add", RUN+="/bin/sh -c 'chmod 770 /sys/class/gpio/gpio*/direction /sys/class/gpio/gpio*/value'"

sudo udevadm control --reload-rules
sudo udevadm trigger
