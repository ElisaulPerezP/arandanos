#!/usr/bin/env python3

import os
import time
import requests
import argparse
from threading import Thread
import signal

# Configuración del tiempo de espera en segundos
TIMEOUT = 5  # Tiempo de espera total de 2 segundos

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

def set_pin_value(pin, value, api_error_url):
    """Configurar el valor de un pin GPIO."""
    try:
        with open(f"/sys/class/gpio/gpio{pin}/value", "w") as f:
            f.write(str(value))
            print(f"Pin {pin} escrito con valor {value}.")
    except IOError as e:
        report_error(api_error_url, f"Error configurando el valor del pin {pin}: {e}")

def check_pin_value(pin, api_error_url):
    """Leer el valor de un pin GPIO."""
    try:
        with open(f"/sys/class/gpio/gpio{pin}/value", "r") as f:
            return f.read().strip()
    except IOError as e:
        report_error(api_error_url, f"Error leyendo el valor del pin {pin}: {e}")
        return None

# Funciones para interactuar con la API
def report_status(url, status_message, api_error_url):
    payload = status_message
    try:
        response = requests.post(url, json=payload, timeout=TIMEOUT)
        if response.status_code == 200:
            print(f"Estado reportado exitosamente: {status_message}")
            return True
        else:
            report_error(api_error_url, f"Error al reportar el estado: {response.status_code}")
            return False
    except requests.Timeout:
        report_error(api_error_url, "Timeout al reportar el estado.")
        return False
    except Exception as e:
        report_error(api_error_url, f"Excepción al reportar el estado: {e}")
        return False

def get_selector_command(url, api_error_url):
    try:
        response = requests.get(url, timeout=TIMEOUT)
        if response.status_code == 200:
            return response.json()
        else:
            report_error(api_error_url, f"Error al obtener el comando: {response.status_code}")
            return None
    except requests.Timeout:
        report_error(api_error_url, "Timeout al obtener el comando.")
        return None
    except Exception as e:
        report_error(api_error_url, f"Excepción al obtener el comando: {e}")
        return None

def report_shutdown(url, status_message, api_error_url):
    payload = status_message
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
    payload = {
        'script_name': 'tanques.py',
        'error_message': error_message,
        'timestamp': time.strftime("%Y-%m-%dT%H:%M:%S")
    }
    try:
        response = requests.post(url, json=payload, timeout=TIMEOUT)
        if response.status_code != 200:
            print(f"Error al reportar el error: {response.status_code}")
    except Exception as e:
        print(f"Excepción al reportar el error: {e}")

# Funciones auxiliares
def load_pins_from_file(filename):
    pins = {}
    with open(filename, 'r') as f:
        for line in f:
            if ':' in line:
                name, pin = line.strip().split(':')
                pins[name] = int(pin)
            else:
                # Omitir líneas vacías o mal formateadas
                continue
    return pins

def main(input_file, output_file, output_neg_file, selector_url, estado_url, apagado_url, api_error_url):
    # Cargar pines desde archivos
    output_pins = load_pins_from_file(output_file)
    output_neg_pins = load_pins_from_file(output_neg_file)
    input_pins = load_pins_from_file(input_file)

    all_pins = {**output_pins, **output_neg_pins, **input_pins}

    # Exportar y configurar los pines
    for pin in all_pins.values():
        export_pin(pin, api_error_url)
        set_pin_direction(pin, "out" if pin in output_pins.values() or pin in output_neg_pins.values() else "in", api_error_url)

    # Apagar todas las electrovalvulas al inicio
    for pin in output_pins.values():
        set_pin_value(pin, "0", api_error_url)
    for pin in output_neg_pins.values():
        set_pin_value(pin, "1", api_error_url)

    # Variables de control
    stop_threads = False

    # Función para manejar comandos desde la API
    def handle_commands():
        nonlocal stop_threads
        while not stop_threads:
            current_time = time.localtime()
            current_second = current_time.tm_sec

            if current_second in [22, 58]:
                command = get_selector_command(selector_url, api_error_url)
                if command:
                    action = command.get('command')
                    if action == "llenar":
                        print("Comando 'llenar' recibido, encendiendo electrovalvulas...")
                        # Encender las electrovalvulas
                        for pin in output_pins.values():
                            set_pin_value(pin, "1", api_error_url)
                        for pin in output_neg_pins.values():
                            set_pin_value(pin, "0", api_error_url)

                        # Monitorear los sensores de nivel
                        while not all(check_pin_value(pin, api_error_url) == "1" for pin in input_pins.values()):
                            time.sleep(1)

                        # Apagar las electrovalvulas cuando los tanques estén llenos
                        for pin in output_pins.values():
                            set_pin_value(pin, "0", api_error_url)
                        for pin in output_neg_pins.values():
                            set_pin_value(pin, "1", api_error_url)

                        # Reportar el estado
                        status_message = {name: 'lleno' for name in input_pins.keys()}
                        report_status(estado_url, status_message, api_error_url)
                        print("Tanques llenos, electrovalvulas apagadas.")
                    elif action == "esperar":
                        print("Comando 'esperar' recibido, en espera de nuevas instrucciones.")
                    else:
                        report_error(api_error_url, f"Comando desconocido: {action}")

            # Espera hasta el próximo segundo
            time.sleep(1 - time.time() % 1)

    # Función para reportar estado a la API
    def report_state():
        nonlocal stop_threads
        while not stop_threads:
            current_time = time.localtime()
            current_second = current_time.tm_sec

            if current_second in [56]:
                status_message = {}
                for name, pin in output_pins.items():
                    status_message[name] = 'encendida' if check_pin_value(pin, api_error_url) == "1" else 'apagada'
                for name, pin in output_neg_pins.items():
                    status_message[name] = 'encendida' if check_pin_value(pin, api_error_url) == "0" else 'apagada'
                report_status(estado_url, status_message, api_error_url)

            # Espera hasta el próximo segundo
            time.sleep(1 - time.time() % 1)

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
            set_pin_value(pin, "0", api_error_url)
        for pin in output_neg_pins.values():
            set_pin_value(pin, "1", api_error_url)
        for pin in all_pins.values():
            unexport_pin(pin, api_error_url)
        report_shutdown(apagado_url, 'Apagado con exito', api_error_url)
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
    parser = argparse.ArgumentParser(description='Script para el llenado de tanques automáticamente.')
    parser.add_argument('input_file', type=str, help='Archivo de configuración de sensores de nivel.')
    parser.add_argument('output_file', type=str, help='Archivo de configuración de electrovalvulas (lógica positiva).')
    parser.add_argument('output_neg_file', type=str, help='Archivo de configuración de electrovalvulas (lógica negativa).')
    parser.add_argument('selector_url', type=str, help='URL del endpoint para obtener comandos de selección.')
    parser.add_argument('estado_url', type=str, help='URL del endpoint para reportar estado.')
    parser.add_argument('apagado_url', type=str, help='URL del endpoint para reportar apagado.')
    parser.add_argument('api_error_url', type=str, help='URL del endpoint para reportar errores.')

    args = parser.parse_args()
    main(args.input_file, args.output_file, args.output_neg_file, args.selector_url, args.estado_url, args.apagado_url, args.api_error_url)