import RPi.GPIO as GPIO
import time

GPIO.setmode(GPIO.BCM)

GPIO.setup(17, GPIO.OUT)

try:
    while True:
        # Encender el LED
        GPIO.output(17, True)
        print("LED ON")
        time.sleep(1)  # Espera 1 segundo

        # Apagar el LED
        GPIO.output(17, False)
        print("LED OFF")
        time.sleep(1)  # Espera 1 segundo

except KeyboardInterrupt:
    GPIO.cleanup()
    print("Programa terminado.")
