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

# Funciones para interactuar con la API
def report_count(url, counts):
    payload = counts
    try:
        response = requests.post(url, json=payload)
        if response.status_code == 200:
            print(f"Conteo reportado exitosamente: {counts}")
        else:
            print(f"Error al reportar el conteo: {response.status_code}")
    except Exception as e:
        print(f"Excepción al reportar el conteo: {e}")

def load_first_pin_from_file(filename):
    with open(filename, 'r') as f:
        line = f.readline()
        name, pin = line.strip().split(':')
        return {name: int(pin)}

def main(input_file, post_url, stop_url):
    # Cargar el primer pin desde el archivo
    sensor_pin = load_first_pin_from_file(input_file)
    name, pin = next(iter(sensor_pin.items()))

    # Exportar y configurar el pin
    export_pin(pin)
    set_pin_direction(pin, "in")
    set_pin_edge(pin, "rising")

    # Variables de control
    stop_threads = False
    counts = {name: 0}

    # Función para manejar el conteo de pulsos
    def handle_counts():
        nonlocal stop_threads
        # Abrir el archivo de valor del pin
        value_fd = os.open(f"/sys/class/gpio/gpio{pin}/value", os.O_RDONLY | os.O_NONBLOCK)
        poller = select.poll()
        poller.register(value_fd, select.POLLPRI)
        
        while not stop_threads:
            events = poller.poll(1)  # Esperar hasta 1 µs por un evento
            if events:
                os.lseek(value_fd, 0, os.SEEK_SET)  # Resetear el puntero del archivo al inicio
                os.read(value_fd, 1024).strip()  # Leer el valor (aunque no lo usemos)
                counts[name] += 1  # Incrementar el contador de eventos
        
        os.close(value_fd)

    # Función para reportar el conteo a la API
    def report_state():
        nonlocal stop_threads
        while not stop_threads:
            time.sleep(0.5)
            current_counts = counts.copy()
            report_count(post_url, current_counts)
            counts[name] = 0

    # Iniciar hilos
    count_thread = Thread(target=handle_counts)
    state_thread = Thread(target=report_state)
    count_thread.start()
    state_thread.start()

    try:
        # Esperar a que se interrumpa el script
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        stop_threads = True
        count_thread.join()
        state_thread.join()
    finally:
        # Desexportar el pin
        unexport_pin(pin)
        # Reportar apagado
        report_count(stop_url, {'status': 'Apagado con exito'})

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Script para manejar sensores de flujo automáticamente.')
    parser.add_argument('input_file', type=str, help='Archivo de configuración de sensores de flujo.')
    parser.add_argument('post_url', type=str, help='URL del endpoint para reportar el conteo.')
    parser.add_argument('stop_url', type=str, help='URL del endpoint para reportar apagado.')

    args = parser.parse_args()
    main(args.input_file, args.post_url, args.stop_url)
