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

def main(numero_camellon, numero_bomba, fluxometro, cuentas_flujo, numero_bomba_fertilizante, concentracion_fertilizante, numero_sensor_nivel, numero_maximo_minutos):
    # Exportar y configurar los pines
    export_pin(numero_camellon)
    export_pin(numero_bomba)
    export_pin(fluxometro)
    export_pin(numero_bomba_fertilizante)
    export_pin(numero_sensor_nivel)

    time.sleep(0.1)  # Esperar un poco para asegurarse de que los pines se exportan
    set_pin_direction(numero_camellon, "out")
    set_pin_direction(numero_bomba, "out")
    set_pin_direction(fluxometro, "in")
    set_pin_edge(fluxometro, "rising")
    set_pin_direction(numero_bomba_fertilizante, "out")
    set_pin_direction(numero_sensor_nivel, "in")

    # Comprobar si el sensor de nivel está en 1
    with open(f"/sys/class/gpio/gpio{numero_sensor_nivel}/value", "r") as f:
        if f.read().strip() != "1":
            print("Error: El tanque no está lleno.")
            unexport_pin(numero_camellon)
            unexport_pin(numero_bomba)
            unexport_pin(fluxometro)
            unexport_pin(numero_bomba_fertilizante)
            unexport_pin(numero_sensor_nivel)
            return

    # Abrir el archivo de valor del fluxómetro
    value_fd = os.open(f"/sys/class/gpio/gpio{fluxometro}/value", os.O_RDONLY | os.O_NONBLOCK)

    # Crear un poller para detectar eventos en el archivo
    poller = select.poll()
    poller.register(value_fd, select.POLLPRI)

    # Inicializar el contador y el tiempo de inicio
    event_count = 0
    start_time = time.time()

    # Encender la válvula y la bomba
    set_pin_value(numero_camellon, "1")
    set_pin_value(numero_bomba, "1")

    try:
        print("Esperando eventos... Presiona Ctrl+C para salir.")
        while event_count < cuentas_flujo:
            # Verificar el tiempo transcurrido
            elapsed_time = time.time() - start_time
            if elapsed_time > numero_maximo_minutos * 60:
                print(f"Tiempo máximo de ejecución de {numero_maximo_minutos} minutos alcanzado.")
                break

            events = poller.poll(1000)  # Esperar hasta 1000 ms por un evento
            if events:
                os.lseek(value_fd, 0, os.SEEK_SET)  # Resetear el puntero del archivo al inicio
                os.read(value_fd, 1024).strip()  # Leer el valor (aunque no lo usemos)
                event_count += 1  # Incrementar el contador de eventos

            # Control de la bomba de fertilizante basado en la concentración
            if concentracion_fertilizante > 0:
                cycle_time = 10
                on_time = (concentracion_fertilizante / 100) * cycle_time
                off_time = cycle_time - on_time
                set_pin_value(numero_bomba_fertilizante, "1")
                time.sleep(on_time)
                set_pin_value(numero_bomba_fertilizante, "0")
                time.sleep(off_time)
    except KeyboardInterrupt:
        pass  # Solo pasar, el código de limpieza se ejecutará en finally
    finally:
        # Apagar todo y limpiar
        set_pin_value(numero_camellon, "0")
        set_pin_value(numero_bomba, "0")
        set_pin_value(numero_bomba_fertilizante, "0")
        unexport_pin(numero_camellon)
        unexport_pin(numero_bomba)
        unexport_pin(fluxometro)
        unexport_pin(numero_bomba_fertilizante)
        unexport_pin(numero_sensor_nivel)
        os.close(value_fd)
        print(f"Pin {numero_camellon}, {numero_bomba}, {fluxometro}, {numero_bomba_fertilizante} y {numero_sensor_nivel} desexportados y programa terminado.")
        print(f"Total de eventos detectados: {event_count}")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Script para manejar riego y fertilización automática.')
    parser.add_argument('NumeroDeCamellon', type=int, help='El pin GPIO para la electrovalvula del camellon.')
    parser.add_argument('NumeroDeLaBomba', type=int, help='El pin GPIO para la bomba de agua.')
    parser.add_argument('Fluxometro', type=int, help='El pin GPIO para el fluxometro.')
    parser.add_argument('CuentasDeFlujo', type=int, help='El número de eventos que el poller debe contar del fluxómetro antes de detener el script.')
    parser.add_argument('NumeroDeLaBombaFertilizante', type=int, help='El pin GPIO para la bomba de fertilizante.')
    parser.add_argument('ConcentracionDeFertilizante', type=int, help='Concentración de fertilizante (0-100).')
    parser.add_argument('NumeroDelSensorDeNivel', type=int, help='El pin GPIO para el sensor de nivel del tanque.')
    parser.add_argument('NumeroMaximoDeMinutos', type=int, help='El tiempo máximo en minutos que el script puede estar en ejecución.')

    args = parser.parse_args()
    main(args.NumeroDeCamellon, args.NumeroDeLaBomba, args.Fluxometro, args.CuentasDeFlujo, args.NumeroDeLaBombaFertilizante, args.ConcentracionDeFertilizante, args.NumeroDelSensorDeNivel, args.NumeroMaximoDeMinutos)
