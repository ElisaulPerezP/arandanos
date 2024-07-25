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

def unexport_pin(pin):
    """Desexportar un pin GPIO."""
    try:
        with open("/sys/class/gpio/unexport", "w") as f:
            f.write(str(pin))
    except IOError as e:
        print(f"Error: {e}")

def set_pin_direction(pin, direction):
    """Configurar la dirección de un pin GPIO."""
    with open(f"/sys/class/gpio/gpio{pin}/direction", "w") as f:
        f.write(direction)

def read_pin_value(pin):
    """Leer el valor de un pin GPIO."""
    with open(f"/sys/class/gpio/gpio{pin}/value", "r") as f:
        return f.read().strip()

def read_pins_from_file(pins_file):
    """Leer los números de los pines desde un archivo."""
    with open(pins_file, "r") as f:
        pins = [line.strip() for line in f if line.strip().isdigit()]
    return pins

def display_pin_values(pins):
    """Mostrar los valores de los pines en pantalla."""
    for pin in pins:
        value = read_pin_value(pin)
        print(f"Pin {pin}: {value}")
    print("----------------------")

if __name__ == "__main__":
    import argparse
    import signal
    import sys

    def signal_handler(sig, frame):
        print("\nDesexportando pines y terminando programa...")
        for pin in pins:
            unexport_pin(pin)
        sys.exit(0)

    signal.signal(signal.SIGINT, signal_handler)

    parser = argparse.ArgumentParser(description='Script para exportar, leer y mostrar el estado de pines GPIO desde un archivo cada 5 segundos.')
    parser.add_argument('pins_file', type=str, help='El archivo con la lista de pines GPIO.')

    args = parser.parse_args()

    pins = read_pins_from_file(args.pins_file)

    # Exportar y configurar los pines
    for pin in pins:
        export_pin(pin)
        set_pin_direction(pin, "in")

    try:
        while True:
            display_pin_values(pins)
            time.sleep(1)
    except KeyboardInterrupt:
        pass  # Esto se maneja con el signal handler
    finally:
        print("\nDesexportando pines y terminando programa...")
        for pin in pins:
            unexport_pin(pin)

