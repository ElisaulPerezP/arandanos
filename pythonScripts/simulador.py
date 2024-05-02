import requests
import time
import random

def post_estado():
    url = "http://127.0.0.1:8000/api/estados"  

    data = {
        'solenoide_1': random.choice([True, False]),
        'solenoide_2': random.choice([True, False]),
        'solenoide_3': random.choice([True, False]),
        'solenoide_4': random.choice([True, False]),
        'solenoide_5': random.choice([True, False]),
        'solenoide_6': random.choice([True, False]),
        'solenoide_7': random.choice([True, False]),
        'solenoide_8': random.choice([True, False]),
        'solenoide_9': random.choice([True, False]),
        'solenoide_10': random.choice([True, False]),
        'solenoide_11': random.choice([True, False]),
        'solenoide_12': random.choice([True, False]),
        'bomba_1': random.choice([True, False]),
        'bomba_2': random.choice([True, False]),
        'bomba_fertilizante': random.choice([True, False]),
    }

    headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }

    response = requests.post(url, json=data, headers=headers)

    print(response.text)


if __name__ == "__main__":
    while True:
        post_estado()
        time.sleep(10)
