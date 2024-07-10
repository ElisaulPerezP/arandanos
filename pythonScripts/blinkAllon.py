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

def blink_pin(pin, frequency, duration):
    """Hacer parpadear un pin GPIO."""
    # Exportar y configurar el pin GPIO
    export_pin(pin)
    time.sleep(0.1)  # Esperar un poco para asegurarse de que el pin se exporta
    set_pin_direction(pin, "out")

    period = 1.0 / frequency
    half_period = period / 2
    end_time = time.time() + duration

    try:
        print(f"Blinking GPIO pin {pin} at {frequency} Hz...")
        while time.time() < end_time:
            set_pin_value(pin, 0)  # Lógica negada: 0 enciende
            time.sleep(half_period)
            set_pin_value(pin, 1)  # Lógica negada: 1 apaga
            time.sleep(half_period)
    except KeyboardInterrupt:
        print("Saliendo...")
    finally:
        # Dejar el pin encendido (lógica negada: 0) y desexportar
        set_pin_value(pin, 0)  # Encender el pin antes de desexportar
        unexport_pin(pin)
        print(f"Pin {pin} desexportado y programa terminado.")

def main(pins_file, frequency, duration):
    # Leer los pines desde el archivo
    with open(pins_file, "r") as f:
        pins = [int(line.strip()) for line in f if line.strip().isdigit()]

    for pin in pins:
        blink_pin(pin, frequency, duration)

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Script para hacer parpadear un LED en varios pines.')
    parser.add_argument('pins_file', type=str, help='El archivo con la lista de pines GPIO.')
    parser.add_argument('frequency', type=float, help='La frecuencia de parpadeo en Hz.')
    parser.add_argument('duration', type=float, help='La duración del parpadeo en segundos.')

    args = parser.parse_args()
    main(args.pins_file, args.frequency, args.duration)

