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

def read_pins_from_file(pins_file):
    """Leer los números de los pines desde un archivo."""
    with open(pins_file, "r") as f:
        pins = [int(line.strip()) for line in f if line.strip().isdigit()]
    return pins

def signal_handler(sig, frame):
    """Manejar la señal de interrupción (Ctrl+C)."""
    print("\nDesexportando pines y mostrando los resultados...")
    for pin in pins:
        unexport_pin(pin)
    print(f"Eventos contados: {event_counts}")
    sys.exit(0)

if __name__ == "__main__":
    import argparse

    parser = argparse.ArgumentParser(description='Script para exportar pines GPIO, contar eventos en borde ascendente y mostrar los resultados al terminar.')
    parser.add_argument('pins_file', type=str, help='El archivo con la lista de pines GPIO.')

    args = parser.parse_args()

    pins = read_pins_from_file(args.pins_file)
    event_counts = {pin: 0 for pin in pins}

    # Exportar y configurar los pines
    for pin in pins:
        export_pin(pin)
        set_pin_direction(pin, "in")
        set_pin_edge(pin, "rising")

    # Registrar el manejador de señales
    signal.signal(signal.SIGINT, signal_handler)

    # Abrir los archivos de valor de los pines y configurar el poller
    value_fds = {}
    poller = select.poll()
    for pin in pins:
        value_fd = os.open(f"/sys/class/gpio/gpio{pin}/value", os.O_RDONLY | os.O_NONBLOCK)
        value_fds[pin] = value_fd
        poller.register(value_fd, select.POLLPRI)

    try:
        print("Esperando eventos... Presiona Ctrl+C para mostrar los resultados y salir.")
        while True:
            events = poller.poll(1000)  # Esperar hasta 1000 ms por un evento
            for fd, _ in events:
                for pin, value_fd in value_fds.items():
                    if fd == value_fd:
                        os.lseek(value_fd, 0, os.SEEK_SET)  # Resetear el puntero del archivo al inicio
                        os.read(value_fd, 1024).strip()  # Leer el valor (aunque no lo usemos)
                        event_counts[pin] += 1  # Incrementar el contador de eventos
    except KeyboardInterrupt:
        pass  # Esto se maneja con el signal handler
    finally:
        signal_handler(None, None)

