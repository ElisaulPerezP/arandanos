#!/usr/bin/env python3

import os
import time
import select

# Configurar el pin
PIN = 15  # Define el pin GPIO 15 (BCM 15)
gpio_path = f"/sys/class/gpio/gpio{PIN}"

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

# Exportar y configurar el pin
export_pin(PIN)
time.sleep(0.1)  # Esperar un poco para asegurarse de que el pin se exporta
set_pin_direction(PIN, "in")
set_pin_edge(PIN, "rising")  # Configurar el edge para rising, puedes cambiar a falling o both

# Abrir el archivo de valor del pin
value_fd = os.open(f"/sys/class/gpio/gpio{PIN}/value", os.O_RDONLY | os.O_NONBLOCK)

# Crear un poller para detectar eventos en el archivo
poller = select.poll()
poller.register(value_fd, select.POLLPRI)

# Inicializar el contador
event_count = 0

try:
    print("Esperando eventos... Presiona Ctrl+C para salir.")
    while True:
        events = poller.poll()  # Espera indefinidamente por un evento
        if events:
            os.lseek(value_fd, 0, os.SEEK_SET)  # Resetear el puntero del archivo al inicio
            os.read(value_fd, 1024).strip()  # Leer el valor (aunque no lo usemos)
            event_count += 1  # Incrementar el contador de eventos
except KeyboardInterrupt:
    pass  # Solo pasar, el código de limpieza se ejecutará en finally
finally:
    # Limpiar configuración de GPIO
    unexport_pin(PIN)
    os.close(value_fd)
    print(f"Pin {PIN} desexportado y programa terminado.")
    # Presentar el total de eventos detectados
    print(f"Total de eventos detectados: {event_count}")

