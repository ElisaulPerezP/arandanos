#!/usr/bin/env python3

import os
import time
import select
import signal
import sys

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

def set_pin_edge(pin, edge):
    """Configurar el edge de un pin GPIO."""
    with open(f"/sys/class/gpio/gpio{pin}/edge", "w") as f:
        f.write(edge)

def set_pin_value(pin, value):
    """Configurar el valor de un pin GPIO."""
    with open(f"/sys/class/gpio/gpio{pin}/value", "w") as f:
        f.write(str(value))

def read_pin_from_file(pin_file):
    """Leer el número del pin desde un archivo."""
    with open(pin_file, "r") as f:
        pin = int(f.read().strip())
    return pin

def signal_handler(sig, frame):
    """Manejar la señal de interrupción (Ctrl+C)."""
    print("\nDesexportando pines y mostrando el resultado...")
    set_pin_value(bomba_pin, 0)  # Apagar la bomba
    unexport_pin(fluxometro_pin)
    unexport_pin(bomba_pin)
    print(f"Eventos contados: {event_count}")
    sys.exit(0)

if __name__ == "__main__":
    import argparse

    parser = argparse.ArgumentParser(description='Script para controlar una bomba basada en eventos de un fluxómetro.')
    parser.add_argument('fluxometro_file', type=str, help='El archivo con el pin del fluxómetro.')
    parser.add_argument('bomba_file', type=str, help='El archivo con el pin de la bomba.')
    parser.add_argument('numero_de_cuentas', type=int, help='El número de cuentas para detener la bomba.')

    args = parser.parse_args()

    fluxometro_pin = read_pin_from_file(args.fluxometro_file)
    bomba_pin = read_pin_from_file(args.bomba_file)
    numero_de_cuentas = args.numero_de_cuentas

    # Exportar y configurar los pines
    export_pin(fluxometro_pin)
    export_pin(bomba_pin)
    set_pin_direction(fluxometro_pin, "in")
    set_pin_edge(fluxometro_pin, "rising")
    set_pin_direction(bomba_pin, "out")

    # Registrar el manejador de señales
    signal.signal(signal.SIGINT, signal_handler)

    # Abrir el archivo de valor del fluxómetro y configurar el poller
    value_fd = os.open(f"/sys/class/gpio/gpio{fluxometro_pin}/value", os.O_RDONLY | os.O_NONBLOCK)
    poller = select.poll()
    poller.register(value_fd, select.POLLPRI)

    # Encender la bomba
    set_pin_value(bomba_pin, 1)

    event_count = 0

    try:
        print("Esperando eventos... Presiona Ctrl+C para mostrar el resultado y salir.")
        while event_count < numero_de_cuentas:
            events = poller.poll(1000)  # Esperar hasta 1000 ms por un evento
            if events:
                os.lseek(value_fd, 0, os.SEEK_SET)  # Resetear el puntero del archivo al inicio
                os.read(value_fd, 1024).strip()  # Leer el valor (aunque no lo usemos)
                event_count += 1  # Incrementar el contador de eventos
    except KeyboardInterrupt:
        pass  # Esto se maneja con el signal handler
    finally:
        signal_handler(None, None)

