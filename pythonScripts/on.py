#!/usr/bin/env python3

import os
import time

def export_pin(pin):
    """Exportar un pin GPIO."""
    try:
        with open("/sys/class/gpio/export", "w") as f:
            f.write(str(pin))
    except IOError as e:
        if "Device or resource busy" in str(e):
            pass  # El pin ya puede estar exportado
        else:
            raise e

def set_pin_direction(pin, direction):
    """Configurar la dirección de un pin GPIO."""
    with open(f"/sys/class/gpio/gpio{pin}/direction", "w") as f:
        f.write(direction)

def set_pin_value(pin, value):
    """Configurar el valor de un pin GPIO."""
    with open(f"/sys/class/gpio/gpio{pin}/value", "w") as f:
        f.write(str(value))

def turn_on_pins(pins_file):
    # Leer los pines desde el archivo
    with open(pins_file, "r") as f:
        pins = [int(line.strip()) for line in f if line.strip().isdigit()]

    for pin in pins:
        try:
            export_pin(pin)
            time.sleep(0.1)  # Esperar un poco para asegurarse de que el pin se exporta
            set_pin_direction(pin, "out")
            print(f"Turning on GPIO pin {pin}...")
            set_pin_value(pin, 1)
        except Exception as e:
            print(f"Error handling pin {pin}: {e}")

if __name__ == "__main__":
    import argparse

    parser = argparse.ArgumentParser(description='Script para encender todos los pines listados en un archivo.')
    parser.add_argument('pins_file', type=str, help='El archivo con la lista de pines GPIO.')

    args = parser.parse_args()
    turn_on_pins(args.pins_file)

