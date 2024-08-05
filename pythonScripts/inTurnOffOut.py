#!/usr/bin/env python3

import os
import time

# Funciones para manipular GPIO
def is_exported(pin):
    """Verificar si un pin GPIO ya está exportado."""
    return os.path.exists(f"/sys/class/gpio/gpio{pin}")

def export_pin(pin):
    """Exportar un pin GPIO solo si no está exportado."""
    if not is_exported(pin):
        try:
            with open("/sys/class/gpio/export", "w") as f:
                f.write(str(pin))
            print(f"Pin {pin} exportado.")
        except IOError as e:
            print(f"Error exportando el pin {pin}: {e}")
    else:
        print(f"Pin {pin} ya está exportado.")

def is_direction_set(pin, direction):
    """Verificar si la dirección de un pin GPIO ya está configurada."""
    try:
        with open(f"/sys/class/gpio/gpio{pin}/direction", "r") as f:
            current_direction = f.read().strip()
            return current_direction == direction
    except IOError as e:
        print(f"Error leyendo la dirección del pin {pin}: {e}")
        return False

def set_pin_direction(pin, direction):
    """Configurar la dirección de un pin GPIO solo si no está configurada previamente."""
    if not is_direction_set(pin, direction):
        try:
            with open(f"/sys/class/gpio/gpio{pin}/direction", "w") as f:
                f.write(direction)
            print(f"Pin {pin} configurado como {direction}.")
        except IOError as e:
            print(f"Error configurando la dirección del pin {pin}: {e}")
    else:
        print(f"Pin {pin} ya está configurado como {direction}.")

def write_pin_value(pin, value):
    """Escribir un valor en un pin GPIO."""
    try:
        with open(f"/sys/class/gpio/gpio{pin}/value", "w") as f:
            f.write(str(value))
        print(f"Pin {pin} escrito con valor {value}.")
    except IOError as e:
        print(f"Error escribiendo en el pin {pin}: {e}")

def main():
    pin = 2

    # Exportar el pin si no está exportado
    export_pin(pin)
    time.sleep(0.1)  # Esperar un poco para asegurarse de que el pin se exporta correctamente

    # Configurar el pin como salida si no está configurado previamente
    set_pin_direction(pin, "out")

    # Escribir valor alto en el pin
    write_pin_value(pin, 0)

if __name__ == "__main__":
    main()
