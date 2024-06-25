import RPi.GPIO as GPIO
import requests
import sys
import time

if len(sys.argv) != 3:
    print("Uso: python monitor_gpio.py <GPIO_PIN> <CULTIVO_ID>")
    sys.exit(1)

GPIO_PIN = int(sys.argv[1])
CULTIVO_ID = sys.argv[2]
API_ENDPOINT = "http://localhost/api/stop"

def enviar_notificacion():
    payload = {'cultivo_id': CULTIVO_ID}
    try:
        response = requests.post(API_ENDPOINT, json=payload)
        if response.status_code == 200:
            print(f"Notificación enviada exitosamente para cultivo_id: {CULTIVO_ID}")
        else:
            print(f"Error al enviar notificación: {response.status_code} - {response.text}")
    except Exception as e:
        print(f"Error al conectarse al endpoint: {e}")

def evento_rising(channel):
    print(f"Evento rising detectado en el pin {channel}")
    enviar_notificacion()

GPIO.setmode(GPIO.BCM)
GPIO.setup(GPIO_PIN, GPIO.IN, pull_up_down=GPIO.PUD_UP)
GPIO.add_event_detect(GPIO_PIN, GPIO.RISING, callback=evento_rising, bouncetime=300)

try:
    print(f"Monitoring GPIO pin {GPIO_PIN} for rising edge events...")
    while True:
        time.sleep(1)
except KeyboardInterrupt:
    print("Saliendo...")
finally:
    GPIO.cleanup()
