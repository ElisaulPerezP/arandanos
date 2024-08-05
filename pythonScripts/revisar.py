#!/usr/bin/env python3

import os

# Funciones para manipular GPIO
def is_exported(pin):
    """Verificar si un pin GPIO ya está exportado."""
    return os.path.exists(f"/sys/class/gpio/gpio{pin}")

def is_direction_set(pin, direction):
    """Verificar si la dirección de un pin GPIO ya está configurada."""
    try:
        with open(f"/sys/class/gpio/gpio{pin}/direction", "r") as f:
            current_direction = f.read().strip()
            return current_direction == direction
    except IOError as e:
        print(f"Error leyendo la dirección del pin {pin}: {e}")
        return False

def read_pin_value(pin):
    """Leer un valor de un pin GPIO."""
    try:
        with open(f"/sys/class/gpio/gpio{pin}/value", "r") as f:
            return f.read().strip()
    except IOError as e:
        print(f"Error leyendo el valor del pin {pin}: {e}")
        return None

def main():
    pin = 2

    # Verificar si el pin está exportado
    if is_exported(pin):
        # Verificar si el pin está configurado como salida
        if is_direction_set(pin, "out"):
            # Leer el valor del pin
            value = read_pin_value(pin)
            if value is not None:
                print(f"Valor del pin {pin}: {value}")
            else:
                print(f"No se pudo leer el valor del pin {pin}.")
        else:
            print(f"El pin {pin} no está configurado como salida.")
    else:
        print(f"El pin {pin} no está exportado.")

if __name__ == "__main__":
    main()
