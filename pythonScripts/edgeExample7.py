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

def main(event_pin, control_pin, max_count, max_time):
    # Exportar y configurar los pines
    export_pin(event_pin)
    export_pin(control_pin)
    time.sleep(0.1)  # Esperar un poco para asegurarse de que los pines se exportan
    set_pin_direction(event_pin, "in")
    set_pin_edge(event_pin, "rising")  # Configurar el edge para rising, puedes cambiar a falling o both
    set_pin_direction(control_pin, "out")  # Configurar el pin de control como salida

    # Abrir el archivo de valor del pin
    value_fd = os.open(f"/sys/class/gpio/gpio{event_pin}/value", os.O_RDONLY | os.O_NONBLOCK)

    # Crear un poller para detectar eventos en el archivo
    poller = select.poll()
    poller.register(value_fd, select.POLLPRI)

    # Inicializar el contador y el tiempo de inicio
    event_count = 0
    start_time = time.time()

    try:
        print("Esperando eventos... Presiona Ctrl+C para salir.")
        while event_count < max_count:
            # Verificar el tiempo transcurrido
            elapsed_time = time.time() - start_time
            if elapsed_time > max_time:
                print(f"Tiempo máximo de ejecución de {max_time} segundos alcanzado.")
                break

            events = poller.poll(1000)  # Esperar hasta 1000 ms por un evento
            if events:
                os.lseek(value_fd, 0, os.SEEK_SET)  # Resetear el puntero del archivo al inicio
                os.read(value_fd, 1024).strip()  # Leer el valor (aunque no lo usemos)
                event_count += 1  # Incrementar el contador de eventos
                
                # Controlar el pin de control basado en el contador de eventos
                if event_count % 5 == 0:
                    set_pin_value(control_pin, "0")  # Apagar el pin
                else:
                    set_pin_value(control_pin, "1")  # Encender el pin
    except KeyboardInterrupt:
        pass  # Solo pasar, el código de limpieza se ejecutará en finally
    finally:
        # Limpiar configuración de GPIO
        unexport_pin(event_pin)
        unexport_pin(control_pin)
        os.close(value_fd)
        print(f"Pin {event_pin} y {control_pin} desexportados y programa terminado.")
        # Presentar el total de eventos detectados
        print(f"Total de eventos detectados: {event_count}")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Script para manejar eventos GPIO.')
    parser.add_argument('event_pin', type=int, help='El pin GPIO para detección de eventos.')
    parser.add_argument('control_pin', type=int, help='El pin GPIO para control.')
    parser.add_argument('max_count', type=int, help='El número máximo de cuentas de eventos antes de terminar el programa.')
    parser.add_argument('max_time', type=int, help='El número máximo de segundos que puede pasar en ejecución antes de terminar el programa.')

    args = parser.parse_args()
    main(args.event_pin, args.control_pin, args.max_count, args.max_time)

