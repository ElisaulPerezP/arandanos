#!/usr/bin/env python3

import subprocess
import time
import signal

def run_script(duration):
    """Ejecuta edgeExample3.py por una duración específica y retorna el conteo de eventos."""
    print(f"Ejecutando edgeExample3.py por {duration} segundos...")
    
    # Ejecutar edgeExample3.py
    try:
        process = subprocess.Popen(['sudo', './edgeExample3.py'], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
    except Exception as e:
        print(f"Error al iniciar edgeExample3.py: {e}")
        return 0
    
    print(f"Proceso iniciado con PID: {process.pid}")
    
    # Esperar la duración especificada
    time.sleep(duration)
    
    print(f"Enviando señal SIGINT al proceso PID: {process.pid}")
    
    # Enviar señal SIGINT para detener el proceso
    try:
        process.send_signal(signal.SIGINT)
    except Exception as e:
        print(f"Error al enviar SIGINT: {e}")
        process.kill()
        return 0
    
    # Leer la salida del proceso con un tiempo de espera para evitar bloqueo indefinido
    try:
        stdout, stderr = process.communicate(timeout=5)
        print(f"Proceso PID: {process.pid} terminado")
    except subprocess.TimeoutExpired:
        print(f"El proceso PID: {process.pid} no terminó en el tiempo esperado. Terminando proceso...")
        process.kill()
        stdout, stderr = process.communicate()
    
    # Depurar la salida
    print("Salida del script:")
    print(stdout)
    print("Errores del script (si hay):")
    print(stderr)

    # Buscar la línea que contiene el total de eventos detectados
    for line in stdout.split('\n'):
        if "Total de eventos detectados:" in line:
            try:
                count = int(line.split(':')[-1].strip())
                print(f"Eventos detectados: {count}")
                return count
            except ValueError:
                print("Error al convertir el conteo de eventos a un entero.")
    
    print("No se encontraron eventos detectados")
    return 0

# Ejecutar edgeExample3.py tres veces con diferentes duraciones
total_count = 0

print("Ejecutando edgeExample3.py por 5 segundos (primera vez)...")
count1 = run_script(5)
print(f"Eventos detectados en la primera ejecución: {count1}")
total_count += count1

print("Ejecutando edgeExample3.py por 5 segundos (segunda vez)...")
count2 = run_script(5)
print(f"Eventos detectados en la segunda ejecución: {count2}")
total_count += count2

print("Ejecutando edgeExample3.py por 15 segundos (tercera vez)...")
count3 = run_script(15)
print(f"Eventos detectados en la tercera ejecución: {count3}")
total_count += count3

print(f"Suma total de eventos detectados: {total_count}")

