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

def get_pin_value(pin):
    """Leer el valor de un pin GPIO."""
    with open(f"/sys/class/gpio/gpio{pin}/value", "r") as f:
        return f.read().strip()

def set_pin_value(pin, value):
    """Configurar el valor de un pin GPIO."""
    with open(f"/sys/class/gpio/gpio{pin}/value", "w") as f:
        f.write(str(value))

def toggle_pin(pin):
    """Exportar, leer y cambiar el estado de un pin GPIO, luego desexportar."""
    # Exportar y configurar el pin GPIO
    export_pin(pin)
    time.sleep(0.1)  # Esperar un poco para asegurarse de que el pin se exporta
    set_pin_direction(pin, "out")

    try:
        # Leer el estado actual del pin
        current_value = get_pin_value(pin)
        print(f"El estado actual del pin GPIO {pin} es: {current_value}")

        # Cambiar el estado del pin
        new_value = "0" if current_value == "1" else "1"
        set_pin_value(pin, new_value)
        print(f"El nuevo estado del pin GPIO {pin} es: {new_value}")

    except Exception as e:
        print(f"Error handling pin {pin}: {e}")

    finally:
        # Desexportar el pin GPIO
        unexport_pin(pin)
        print(f"Pin {pin} desexportado y programa terminado.")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Script para exportar, leer y cambiar el estado de un pin GPIO.')
    parser.add_argument('pin', type=int, help='El pin GPIO a manejar.')

    args = parser.parse_args()
    toggle_pin(args.pin)

