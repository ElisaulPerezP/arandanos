#!/usr/bin/env python3

import os
import time
import select
import argparse

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

def set_pin_edge(pin, edge):
    """Configurar el edge de un pin GPIO."""
    with open(f"/sys/class/gpio/gpio{pin}/edge", "w") as f:
        f.write(edge)

def enviar_notificacion(cultivo_id):
    import http.client
    import json

    conn = http.client.HTTPConnection("localhost")
    headers = {'Content-type': 'application/json'}
    payload = json.dumps({'cultivo_id': cultivo_id})

    try:
        conn.request("POST", "/api/stop", payload, headers)
        response = conn.getresponse()
        data = response.read()
        if response.status == 200:
            print(f"Notificación enviada exitosamente para cultivo_id: {cultivo_id}")
        else:
            print(f"Error al enviar notificación: {response.status} - {data.decode('utf-8')}")
    except Exception as e:
        print(f"Error al conectarse al endpoint: {e}")
    finally:
        conn.close()

def main(event_pin, cultivo_id):
    # Exportar y configurar el pin de evento
    export_pin(event_pin)
    time.sleep(0.1)  # Esperar un poco para asegurarse de que el pin se exporta
    set_pin_direction(event_pin, "in")
    set_pin_edge(event_pin, "rising")  # Configurar el edge para rising

    # Abrir el archivo de valor del pin
    value_fd = os.open(f"/sys/class/gpio/gpio{event_pin}/value", os.O_RDONLY | os.O_NONBLOCK)

    # Crear un poller para detectar eventos en el archivo
    poller = select.poll()
    poller.register(value_fd, select.POLLPRI)

    try:
        print(f"Monitoring GPIO pin {event_pin} for rising edge events...")
        while True:
            events = poller.poll(1000)  # Esperar hasta 1000 ms por un evento
            if events:
                os.lseek(value_fd, 0, os.SEEK_SET)  # Resetear el puntero del archivo al inicio
                os.read(value_fd, 1024).strip()  # Leer el valor (aunque no lo usemos)
                print(f"Evento rising detectado en el pin {event_pin}")
                enviar_notificacion(cultivo_id)
    except KeyboardInterrupt:
        print("Saliendo...")
    finally:
        # Limpiar configuración de GPIO
        unexport_pin(event_pin)
        os.close(value_fd)
        print(f"Pin {event_pin} desexportado y programa terminado.")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Script para manejar eventos GPIO.')
    parser.add_argument('event_pin', type=int, help='El pin GPIO para detección de eventos.')
    parser.add_argument('cultivo_id', type=int, help='El ID del cultivo asociado al evento.')

    args = parser.parse_args()
    main(args.event_pin, args.cultivo_id)
