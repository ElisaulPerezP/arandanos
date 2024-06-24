#!/usr/bin/env python3

import os

# Ruta al directorio GPIO
gpio_dir = "/sys/class/gpio"

# Recorrer todos los directorios gpioN en /sys/class/gpio
for gpio in os.listdir(gpio_dir):
    if gpio.startswith("gpio"):
        pin = gpio.replace("gpio", "")
        try:
            with open(f"/sys/class/gpio/unexport", "w") as f:
                f.write(pin)
            print(f"Desexportado GPIO pin {pin}")
        except IOError as e:
            print(f"Error desexportando GPIO pin {pin}: {e}")

print("Todos los pines desexportados.")

