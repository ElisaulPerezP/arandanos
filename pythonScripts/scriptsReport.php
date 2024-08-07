<?php
return array (
  'scriptsDisponibles' => '/var/www/arandanos/pythonScripts/stopManual.py, 
                           /var/www/arandanos/pythonScripts/flujo.py,
                           /var/www/arandanos/pythonScripts/impulsores.py, 
                           /var/www/arandanos/pythonScripts/inyectores.py, 
                           /var/www/arandanos/pythonScripts/tanques.py, 
                           /var/www/arandanos/pythonScripts/selector.py, 
                           /var/www/arandanos/pythonScripts/stopTotal.py',

  'scriptsEjecutandose' => '',

  'scritpsDeBase' => '/var/www/arandanos/pythonScripts/stopManual.py 
                      /var/www/arandanos/pythonScripts/input_pins_file_stop.txt 
                      http://127.0.0.1/api/stop http://127.0.0.1/api/error, 
                      /var/www/arandanos/pythonScripts/flujo.py 
                      /var/www/arandanos/pythonScripts/input_pins_file_flujo.txt
                      http://127.0.0.1/api/flujo/conteo http://127.0.0.1/api/flujo/apagado
                      http://127.0.0.1/api/error,

                      /var/www/arandanos/pythonScripts/impulsores.py
                      /var/www/arandanos/pythonScripts/output_pins_file_impulsores.txt
                      /var/www/arandanos/pythonScripts/output_neg_pins_file_impulsores.txt
                      http://127.0.0.1/api/impulsores
                      http://127.0.0.1/api/impulsores/estado
                      http://127.0.0.1/api/impulsores/apagado
                      http://127.0.0.1/api/error,

                      /var/www/arandanos/pythonScripts/inyectores.py
                      /var/www/arandanos/pythonScripts/output_pins_file_inyectores.txt
                      /var/www/arandanos/pythonScripts/output_neg_pins_file_inyectores.txt
                      http://127.0.0.1/api/inyectores http://127.0.0.1/api/inyectores/estado
                      http://127.0.0.1/api/inyectores/apagado
                      http://127.0.0.1/api/error,

                      /var/www/arandanos/pythonScripts/tanques.py
                      /var/www/arandanos/pythonScripts/input_pins_file_tanques.txt
                      /var/www/arandanos/pythonScripts/output_pins_file_tanques.txt
                      /var/www/arandanos/pythonScripts/output_neg_pins_file_tanques.txt
                      http://127.0.0.1/api/tanques http://127.0.0.1/api/tanques/estado
                      http://127.0.0.1/api/tanques/apagado
                      http://127.0.0.1/api/error,

                      /var/www/arandanos/pythonScripts/selector.py
                      /var/www/arandanos/pythonScripts/output_pins_file_selector.txt
                      /var/www/arandanos/pythonScripts/output_neg_pins_file_selector.txt
                      http://127.0.0.1/api/selector http://127.0.0.1/api/selector/estado
                      http://127.0.0.1/api/selector/apagado
                      http://127.0.0.1/api/error',

'scriptStopTotal' => '/var/www/arandanos/pythonScripts/stopTotal.py
                      /var/www/arandanos/pythonScripts/pins.txt
                      /var/www/arandanos/pythonScripts/pinsNegativ.txt',
);