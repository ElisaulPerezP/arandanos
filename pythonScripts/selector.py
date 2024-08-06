#!/usr/bin/env python3

import os
import time
import requests
import argparse
from threading import Thread
import signal

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

def set_pin_value(pin, value):
    """Configurar el valor de un pin GPIO."""
    with open(f"/sys/class/gpio/gpio{pin}/value", "w") as f:
        f.write(value)

def check_pin_value(pin):
    """Leer el valor de un pin GPIO."""
    with open(f"/sys/class/gpio/gpio{pin}/value", "r") as f:
        return f.read().strip()

# Funciones para interactuar con la API
def report_status(url, status_message):
    payload = {'status': status_message}
    try:
        response = requests.post(url, json=payload)
        if response.status_code == 200:
            print(f"Estado reportado exitosamente: {status_message}")
        else:
            print(f"Error al reportar el estado: {response.status_code}")
    except Exception as e:
        print(f"Excepción al reportar el estado: {e}")

def get_selector_command(url):
    try:
        response = requests.get(url)
        if response.status_code == 200:
            return response.json()
        else:
            print(f"Error al obtener el comando: {response.status_code}")
            return None
    except Exception as e:
        print(f"Excepción al obtener el comando: {e}")
        return None

# Funciones auxiliares
def load_pins_from_file(filename):
    pins = {}
    with open(filename, 'r') as f:
        for line in f:
            if ':' in line:
                name, pin = line.strip().split(':')
                pins[name] = int(pin)
            else:
                print(f"Línea inválida en el archivo de configuración: {line.strip()}")
    return pins

def main(output_file, output_neg_file, selector_url, estado_url, apagado_url):
    # Cargar pines desde archivos
    output_pins = load_pins_from_file(output_file)
    output_neg_pins = load_pins_from_file(output_neg_file)

    all_pins = {**output_pins, **output_neg_pins}

    # Exportar y configurar los pines
    for pin in all_pins.values():
        export_pin(pin)
        set_pin_direction(pin, "out")

    # Apagar todas las electrovalvulas al inicio
    for pin in output_pins.values():
        set_pin_value(pin, "0")
    for pin in output_neg_pins.values():
        set_pin_value(pin, "1")

    # Variables de control
    stop_threads = False

    # Función para manejar comandos desde la API
    def handle_commands():
        nonlocal stop_threads
        while not stop_threads:
            command = get_selector_command(selector_url)
            if command:
                for action in command.get('actions', []):
                    action_parts = action.split(':')
                    action_type = action_parts[0]  # on or off
                    pin_name = action_parts[1]    # valvula1, valvula2, etc.
                    if pin_name in output_pins:
                        if action_type == 'on':
                            set_pin_value(output_pins[pin_name], "1")
                        elif action_type == 'off':
                            set_pin_value(output_pins[pin_name], "0")
                    elif pin_name in output_neg_pins:
                        if action_type == 'on':
                            set_pin_value(output_neg_pins[pin_name], "0")
                        elif action_type == 'off':
                            set_pin_value(output_neg_pins[pin_name], "1")
            time.sleep(10)

    # Función para reportar estado a la API
    def report_state():
        nonlocal stop_threads
        while not stop_threads:
            status_message = {}
            for name, pin in output_pins.items():
                status_message[name] = 'encendida' if check_pin_value(pin) == "1" else 'apagada'
            for name, pin in output_neg_pins.items():
                status_message[name] = 'encendida' if check_pin_value(pin) == "0" else 'apagada'
            report_status(estado_url, status_message)
            time.sleep(10)

    # Iniciar hilos
    command_thread = Thread(target=handle_commands)
    state_thread = Thread(target=report_state)
    command_thread.start()
    state_thread.start()

    def handle_signal(signum, frame):
        nonlocal stop_threads
        stop_threads = True
        command_thread.join()
        state_thread.join()
        for pin in output_pins.values():
            set_pin_value(pin, "0")
        for pin in output_neg_pins.values():
            set_pin_value(pin, "1")
        for pin in all_pins.values():
            unexport_pin(pin)
        report_status(apagado_url, 'Apagado con exito')
        print(f"Proceso terminado con la señal {signum}")
        exit(0)

    # Registrar los manejadores de señales
    signal.signal(signal.SIGINT, handle_signal)
    signal.signal(signal.SIGTERM, handle_signal)

    try:
        # Esperar a que se interrumpa el script
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        handle_signal(signal.SIGINT, None)

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Script para manejar las electrovalvulas automáticamente.')
    parser.add_argument('output_file', type=str, help='Archivo de configuración de electrovalvulas (lógica positiva).')
    parser.add_argument('output_neg_file', type=str, help='Archivo de configuración de electrovalvulas (lógica negativa).')
    parser.add_argument('selector_url', type=str, help='URL del endpoint para obtener comandos de selección.')
    parser.add_argument('estado_url', type=str, help='URL del endpoint para reportar estado.')
    parser.add_argument('apagado_url', type=str, help='URL del endpoint para reportar apagado.')

    args = parser.parse_args()
    main(args.output_file, args.output_neg_file, args.selector_url, args.estado_url, args.apagado_url)
