#!/usr/bin/env python3

import os
import time
import select
import requests
import argparse
from threading import Thread

# Configuración del tiempo de espera en segundos
TIMEOUT = 2  # Tiempo de espera total de 2 segundos

# Funciones para manipular GPIO
def export_pin(pin, api_error_url):
    """Exportar un pin GPIO."""
    try:
        with open("/sys/class/gpio/export", "w") as f:
            f.write(str(pin))
    except IOError as e:
        if "Device or resource busy" in str(e):
            pass  # El pin ya puede estar exportado
        else:
            report_error(api_error_url, f"Error exportando el pin {pin}: {e}")

def unexport_pin(pin, api_error_url):
    """Desexportar un pin GPIO."""
    try:
        with open("/sys/class/gpio/unexport", "w") as f:
            f.write(str(pin))
    except IOError as e:
        report_error(api_error_url, f"Error desexportando el pin {pin}: {e}")

def set_pin_direction(pin, direction, api_error_url):
    """Configurar la dirección de un pin GPIO."""
    try:
        with open(f"/sys/class/gpio/gpio{pin}/direction", "w") as f:
            f.write(direction)
    except IOError as e:
        report_error(api_error_url, f"Error configurando la dirección del pin {pin}: {e}")

def set_pin_edge(pin, edge, api_error_url):
    """Configurar el edge de un pin GPIO."""
    try:
        with open(f"/sys/class/gpio/gpio{pin}/edge", "w") as f:
            f.write(edge)
    except IOError as e:
        report_error(api_error_url, f"Error configurando el edge del pin {pin}: {e}")

# Funciones para interactuar con la API
def report_count(url, counts, api_error_url):
    payload = counts
    try:
        response = requests.post(url, json=payload, timeout=TIMEOUT)
        if response.status_code == 200:
            print(f"Conteo reportado exitosamente: {counts}")
            return True
        else:
            report_error(api_error_url, f"Error al reportar el conteo: {response.status_code}")
            return False
    except requests.Timeout:
        report_error(api_error_url, "Timeout al reportar el conteo.")
        return False
    except Exception as e:
        report_error(api_error_url, f"Excepción al reportar el conteo: {e}")
        return False

def report_stop(url, status_message, api_error_url):
    payload = {'status': status_message}
    try:
        response = requests.post(url, json=payload, timeout=TIMEOUT)
        if response.status_code == 200:
            print(f"Apagado reportado exitosamente: {status_message}")
            return True
        else:
            report_error(api_error_url, f"Error al reportar el apagado: {response.status_code}")
            return False
    except requests.Timeout:
        report_error(api_error_url, "Timeout al reportar el apagado.")
        return False
    except Exception as e:
        report_error(api_error_url, f"Excepción al reportar el apagado: {e}")
        return False

def report_error(url, error_message):
    payload = {'error': error_message}
    try:
        response = requests.post(url, json=payload, timeout=TIMEOUT)
        if response.status_code != 200:
            print(f"Error al reportar el error: {response.status_code}")
    except Exception as e:
        print(f"Excepción al reportar el error: {e}")

def load_pins_from_file(filename):
    sensors = {}
    with open(filename, 'r') as f:
        for line in f:
            name, pin = line.strip().split(':')
            sensors[name] = int(pin)
    return sensors

def main(input_file, post_url, stop_url, api_error_url):
    # Cargar los pines desde el archivo
    sensors = load_pins_from_file(input_file)

    # Exportar y configurar los pines
    for pin in sensors.values():
        export_pin(pin, api_error_url)
        set_pin_direction(pin, "in", api_error_url)
        set_pin_edge(pin, "rising", api_error_url)

    # Variables de control
    stop_threads = False
    counts = {name: 0 for name in sensors.keys()}

    # Función para manejar el conteo de pulsos
    def handle_counts(sensor_name, pin):
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
                counts[sensor_name] += 1  # Incrementar el contador de eventos

        os.close(value_fd)

    # Función para reportar el conteo a la API
    def report_state():
        nonlocal stop_threads
        while not stop_threads:
            time.sleep(10)
            current_counts = counts.copy()
            if report_count(post_url, current_counts, api_error_url):
                for name in counts:
                    counts[name] = 0

    # Iniciar hilos para cada sensor
    threads = []
    for name, pin in sensors.items():
        thread = Thread(target=handle_counts, args=(name, pin))
        threads.append(thread)
        thread.start()

    state_thread = Thread(target=report_state)
    state_thread.start()

    try:
        # Esperar a que se interrumpa el script
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        stop_threads = True
        for thread in threads:
            thread.join()
        state_thread.join()
    finally:
        # Desexportar los pines
        for pin in sensors.values():
            unexport_pin(pin, api_error_url)
        # Reportar apagado
        report_stop(stop_url, 'Apagado con exito', api_error_url)

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Script para manejar sensores de flujo automáticamente.')
    parser.add_argument('input_file', type=str, help='Archivo de configuración de sensores de flujo.')
    parser.add_argument('post_url', type=str, help='URL del endpoint para reportar el conteo.')
    parser.add_argument('stop_url', type=str, help='URL del endpoint para reportar apagado.')
    parser.add_argument('api_error_url', type=str, help='URL del endpoint para reportar errores.')

    args = parser.parse_args()
    main(args.input_file, args.post_url, args.stop_url, args.api_error_url)
