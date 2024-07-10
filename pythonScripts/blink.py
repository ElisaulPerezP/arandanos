#!/usr/bin/env python3

import os
import time
import argparse

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

def set_pin_value(pin, value):
    """Configurar el valor de un pin GPIO."""
    with open(f"/sys/class/gpio/gpio{pin}/value", "w") as f:
        f.write(str(value))

def main(pin, frequency):
    # Exportar y configurar el pin GPIO
    export_pin(pin)
    time.sleep(0.1)  # Esperar un poco para asegurarse de que el pin se exporta
    set_pin_direction(pin, "out")

    period = 1.0 / frequency
    half_period = period / 2

    try:
        print(f"Blinking GPIO pin {pin} at {frequency} Hz...")
        while True:
            set_pin_value(pin, 1)
            time.sleep(half_period)
            set_pin_value(pin, 0)
            time.sleep(half_period)
    except KeyboardInterrupt:
        print("Saliendo...")
    finally:
        # Limpiar configuración de GPIO
        unexport_pin(pin)
        print(f"Pin {pin} desexportado y programa terminado.")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Script para hacer parpadear un LED.')
    parser.add_argument('pin', type=int, help='El pin GPIO para hacer parpadear el LED.')
    parser.add_argument('frequency', type=float, help='La frecuencia de parpadeo en Hz.')

    args = parser.parse_args()
    main(args.pin, args.frequency)
