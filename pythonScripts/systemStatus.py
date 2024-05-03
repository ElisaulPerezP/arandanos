import RPi.GPIO as GPIO
import requests
import time
import random

# Configuración inicial de GPIO
GPIO.setmode(GPIO.BCM)  # Usa el número de pin Broadcom SOC
GPIO.setwarnings(False)  # Desactiva las advertencias

# Definición de los pines de los GPIOs
pins = [17, 27, 22, 10, 9, 11, 5, 6, 13, 19, 26, 3, 4]

# Configuración de los pines como entrada
for pin in pins:
    GPIO.setup(pin, GPIO.IN)

def post_estado():
    url = "http://127.0.0.1:8000/api/estados"

    # Leer el estado de los GPIOs
    data = {
        'solenoide_1': GPIO.input(29),
        'solenoide_2': GPIO.input(27),
        'solenoide_3': GPIO.input(22),
        'solenoide_4': GPIO.input(10),
        'solenoide_5': GPIO.input(9),
        'solenoide_6': GPIO.input(11),
        'solenoide_7': GPIO.input(5),
        'solenoide_8': GPIO.input(6),
        'solenoide_9': GPIO.input(13),
        'solenoide_10': GPIO.input(19),
        'solenoide_11': False,  # Valor fijo hasta la expansión futura
        'solenoide_12': False,  # Valor fijo hasta la expansión futura
        'bomba_1': GPIO.input(26),
        'bomba_2': GPIO.input(3),
        'bomba_fertilizante': GPIO.input(4),
    }

    headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }

    # Enviar la solicitud POST con los datos
    response = requests.post(url, json=data, headers=headers)
    print(response.text)

if __name__ == "__main__":
    while True:
        post_estado()
        time.sleep(10)  # Espera 10 segundos antes de enviar el próximo estado
