#!/usr/bin/env python3

import os
import time
import select
import requests
import argparse
from threading import Thread

# Funciones para manipular GPIO
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

def read_pin_value(pin):
    """Leer el valor de un pin GPIO."""
    with open(f"/sys/class/gpio/gpio{pin}/value", "r") as f:
        return f.read().strip()

# Funciones para interactuar con la API
def report_stop(url, value):
    payload = {'estado': 'Parada activada', 'sensor3': value}
    try:
        response = requests.post(url, json=payload)
        if response.status_code == 200:
            print(f"Parada reportada exitosamente.")
        else:
            print(f"Error al reportar la parada: {response.status_code}")
    except Exception as e:
        print(f"Excepción al reportar la parada: {e}")

def load_first_pin_from_file(filename):
    with open(filename, 'r') as f:
        line = f.readline()
        name, pin = line.strip().split(':')
        return {name: int(pin)}

def main(input_file, stop_url):
    # Cargar el primer pin desde el archivo
    stop_pin = load_first_pin_from_file(input_file)
    name, pin = next(iter(stop_pin.items()))

    # Exportar y configurar el pin
    export_pin(pin)
    set_pin_direction(pin, "in")
    set_pin_edge(pin, "rising")

    # Variables de control
    stop_threads = False

    # Función para manejar el botón de parada
    def handle_stop():
        nonlocal stop_threads
        # Abrir el archivo de valor del pin
        value_fd = os.open(f"/sys/class/gpio/gpio{pin}/value", os.O_RDONLY | os.O_NONBLOCK)
        poller = select.poll()
        poller.register(value_fd, select.POLLPRI)

        while not stop_threads:
            events = poller.poll(1000)  # Esperar hasta 1 segundo por un evento
            if events:
                os.lseek(value_fd, 0, os.SEEK_SET)  # Resetear el puntero del archivo al inicio
                value = os.read(value_fd, 1024).strip()  # Leer el valor
                if value == b'1':
                    print(f"Evento detectado en el pin {pin}: valor {value.decode()}")
                    report_stop(stop_url, value.decode())
                    stop_threads = True

        os.close(value_fd)

    # Iniciar hilo
    stop_thread = Thread(target=handle_stop)
    stop_thread.start()

    try:
        # Esperar a que se interrumpa el script
        while not stop_threads:
            time.sleep(1)
    except KeyboardInterrupt:
        stop_threads = True
        stop_thread.join()
    finally:
        # Desexportar el pin
        unexport_pin(pin)
        print(f"Pin {pin} desexportado y programa terminado.")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Script para manejar el botón de parada.')
    parser.add_argument('input_file', type=str, help='Archivo de configuración del botón de parada.')
    parser.add_argument('stop_url', type=str, help='URL del endpoint para reportar la parada.')

    args = parser.parse_args()
    main(args.input_file, args.stop_url)
