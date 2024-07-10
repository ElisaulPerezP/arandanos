#!/usr/bin/env python3

import subprocess
import time
import os

def run_script(script_name, pins_file):
    """Ejecutar un script con el archivo pins.txt como argumento y esperar a que termine."""
    script_path = os.path.join(os.path.dirname(__file__), script_name)
    pins_file_path = os.path.join(os.path.dirname(__file__), pins_file)
    try:
        subprocess.run(["python3", script_path, pins_file_path], check=True)
    except subprocess.CalledProcessError as e:
        print(f"Error al ejecutar el script {script_name}: {e}")

def main(delay):
    """Ejecutar on.py y off.py en un ciclo con un retraso entre ellos."""
    while True:
        print("Ejecutando on.py...")
        run_script("on.py", "pins.txt")
        print("Esperando...")
        time.sleep(delay)

        print("Ejecutando off.py...")
        run_script("off.py", "pins.txt")
        print("Esperando...")
        time.sleep(delay)

if __name__ == "__main__":
    delay = 0 # El retraso en segundos entre la ejecución de los scripts
    main(delay)

