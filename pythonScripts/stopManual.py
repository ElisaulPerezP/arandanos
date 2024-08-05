#!/usr/bin/env python3

import os
import time
import select
import requests
import argparse
import signal
from threading import Thread

# Configuración del tiempo de espera en segundos
TIMEOUT = 2  # Tiempo de espera total de 2 segundos

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
        except IOError as e:
            if "Device or resource busy" in str(e):
                pass  # El pin ya puede estar exportado
            else:
                raise e

def unexport_pin(pin, api_error_url):
    """Desexportar un pin GPIO."""
    try:
        with open("/sys/class/gpio/unexport", "w") as f:
            f.write(str(pin))
    except IOError as e:
        report_error(api_error_url, f"Error desexportando el pin {pin}: {e}")

def is_direction_set(pin, direction, api_error_url):
    """Verificar si la dirección de un pin GPIO ya está configurada."""
    try:
        with open(f"/sys/class/gpio/gpio{pin}/direction", "r") as f:
            current_direction = f.read().strip()
            return current_direction == direction
    except IOError as e:
        report_error(api_error_url, f"Error leyendo la dirección del pin {pin}: {e}")
        return False

def set_pin_direction(pin, direction, api_error_url):
    """Configurar la dirección de un pin GPIO solo si no está configurada previamente."""
    if not is_direction_set(pin, direction, api_error_url):
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

def read_pin_value(pin, api_error_url):
    """Leer el valor de un pin GPIO."""
    try:
        with open(f"/sys/class/gpio/gpio{pin}/value", "r") as f:
            return f.read().strip()
    except IOError as e:
        report_error(api_error_url, f"Error leyendo el valor del pin {pin}: {e}")
        return None

# Funciones para interactuar con la API
def report_stop(api_error_url, value):
    payload = {'estado': 'Parada activada', 'sensor3': value}
    try:
        response = requests.post(api_error_url, json=payload, timeout=TIMEOUT)
        if response.status_code == 200:
            print(f"Parada reportada exitosamente.")
        else:
            report_error(api_error_url, f"Error al reportar la parada: {response.status_code}")
    except requests.Timeout:
        report_error(api_error_url, "Timeout al reportar la parada.")
    except requests.RequestException as e:
        report_error(api_error_url, f"Excepción al reportar la parada: {e}")

def report_error(url, error_message):
    payload = {'error': error_message}
    try:
        response = requests.post(url, json=payload, timeout=TIMEOUT)
        if response.status_code != 200:
            print(f"Error al reportar el error: {response.status_code}")
    except requests.Timeout:
        print("Timeout al reportar el error.")
    except requests.RequestException as e:
        print(f"Excepción al reportar el error: {e}")

def load_first_pin_from_file(filename):
    with open(filename, 'r') as f:
        line = f.readline()
        name, pin = line.strip().split(':')
        return {name: int(pin)}

def main(input_file, stop_url, api_error_url):
    # Cargar el primer pin desde el archivo
    stop_pin = load_first_pin_from_file(input_file)
    name, pin = next(iter(stop_pin.items()))

    # Exportar y configurar el pin
    export_pin(pin)
    set_pin_direction(pin, "in", api_error_url)
    set_pin_edge(pin, "rising", api_error_url)

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
                try:
                    value = os.read(value_fd, 1024).strip()  # Leer el valor
                    if value == b'1':
                        report_stop(stop_url, value.decode())
                        stop_threads = True
                except OSError as e:
                    if e.errno == 19:
                        report_error(api_error_url, "Dispositivo no disponible, probablemente desexportado.")
                        break
                    else:
                        raise

        os.close(value_fd)

    # Manejadores de señal
    def signal_handler(signum, frame):
        nonlocal stop_threads
        stop_threads = True

    signal.signal(signal.SIGINT, signal_handler)  # Ctrl+C
    signal.signal(signal.SIGTERM, signal_handler)  # Kill

    # Iniciar hilo
    stop_thread = Thread(target=handle_stop)
    stop_thread.start()

    try:
        # Esperar a que se interrumpa el script
        while not stop_threads:
            time.sleep(1)
    except KeyboardInterrupt:
        signal_handler(signal.SIGINT, None)
    finally:
        # Desexportar el pin
        unexport_pin(pin, api_error_url)
        time.sleep(1)  # Esperar un poco antes de terminar
        print(f"Pin {pin} desexportado y programa terminado.")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Script para manejar el botón de parada.')
    parser.add_argument('input_file', type=str, help='Archivo de configuración del botón de parada.')
    parser.add_argument('stop_url', type=str, help='URL del endpoint para reportar la parada.')
    parser.add_argument('api_error_url', type=str, help='URL del endpoint para reportar errores.')

    args = parser.parse_args()
    main(args.input_file, args.stop_url, args.api_error_url)
