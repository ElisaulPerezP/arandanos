import json

# Crear un diccionario con una respuesta válida
response_data = {
    "message": {
        "Solenoid 1": "CERRADO",
        "Solenoid 2": "CERRADO",
        "Solenoid 3": "ABIERTO",
        "Solenoid 4": "ABIERTO",
        "Solenoid 5": "ABIERTO",
        "Solenoid 6": "ABIERTO",
        "Solenoid 7": "ABIERTO",
        "Solenoid 8": "ABIERTO",
        "Solenoid 9": "ABIERTO",
        "Solenoid 10": "ABIERTO",
        "pump 1": "ENCENDIDO",
        "pump 2": "APAGADO"
    },
    "status": "OK"
}

# Convertir el diccionario en una cadena JSON
json_response = json.dumps(response_data)

# Imprimir la cadena JSON (esto será la salida que PHP capturará)
print(json_response)
