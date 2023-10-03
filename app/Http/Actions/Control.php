<?php

namespace App\Http\Actions;

use Symfony\Component\HttpFoundation\JsonResponse;
use function Laravel\Prompts\select;
use function Symfony\Component\String\s;

class Control
{
    public function adecuadorDeRespuesta($respuesta)
    {
        $buttons = include('../config/buttons.php');

        // Define un arreglo de mapeo entre solenoides y botones
        $solenoidToButtonMapOff = [
            'Solenoid 1' => 'off_1_2',
            'Solenoid 2' => 'off_1_2',
            'Solenoid 3' => 'off_3_4',
            'Solenoid 4' => 'off_3_4',
            'Solenoid 5' => 'off_5_6',
            'Solenoid 6' => 'off_5_6',
            'Solenoid 7' => 'off_7_8',
            'Solenoid 8' => 'off_7_8',
            'Solenoid 9' => 'off_9_10',
            'Solenoid 10' => 'off_9_10',
        ];

        // Itera a través del arreglo de mapeo y elimina botones según el estado de los solenoides
        foreach ($solenoidToButtonMapOff as $solenoid => $buttonKey) {
            //dd(json_decode($respuesta));
            if ($respuesta->message->{$solenoid} === 'CERRADO') {
                unset($buttons['buttons'][$buttonKey]);
            }
        }

        $solenoidToButtonMapOn = [
            'Solenoid 1' => 'on_1_2',
            'Solenoid 2' => 'on_1_2',
            'Solenoid 3' => 'on_3_4',
            'Solenoid 4' => 'on_3_4',
            'Solenoid 5' => 'on_5_6',
            'Solenoid 6' => 'on_5_6',
            'Solenoid 7' => 'on_7_8',
            'Solenoid 8' => 'on_7_8',
            'Solenoid 9' => 'on_9_10',
            'Solenoid 10' => 'on_9_10',
        ];

        foreach ($solenoidToButtonMapOn as $solenoid => $buttonKey) {
            if ($respuesta->message->{$solenoid} === 'ABIERTO') {
                unset($buttons['buttons'][$buttonKey]);
            }
        }

        if ($respuesta->message->{'pump 1'} === 'ENCENDIDO') {
            unset($buttons['buttons']['on_bomba_1']);
        }
         if ($respuesta->message->{'pump 2'} === 'ENCENDIDO') {
             unset($buttons['buttons']['on_bomba_2']);
         }
        if ($respuesta->message->{'pump 1'} === 'APAGADO') {
            unset($buttons['buttons']['off_bomba_1']);
        }
         if ($respuesta->message->{'pump 2'} === 'APAGADO') {
             unset($buttons['buttons']['off_bomba_2']);
         }
        return $buttons;
    }
        /*
        $buttons = include('buttons.php');
        if (($respuesta['message']['Solenoid 1'] == 'CERRADO') or ($respuesta['message']['Solenoid 2'] == 'CERRADO')) {
            unset($buttons['off_1_2']);
        };
        if (($respuesta['message']['Solenoid 3'] === 'CERRADO') or ($respuesta['message']['Solenoid 4'] === 'CERRADO')) {
            unset($buttons['off_3_4']);
        };
        if (($respuesta['message']['Solenoid 5'] === 'CERRADO') or ($respuesta['message']['Solenoid 6'] === 'CERRADO')) {
            unset($buttons['off_5_6']);
        };
        if (($respuesta['message']['Solenoid 7'] === 'CERRADO') or ($respuesta['message']['Solenoid 8'] === 'CERRADO')) {
            unset($buttons['off_7_8']);
        };
        if (($respuesta['message']['Solenoid 9'] === 'CERRADO') or ($respuesta['message']['Solenoid 10'] === 'CERRADO')) {
            unset($buttons['off_9_10']);
        };
        if (($respuesta['message']['Solenoid 1'] === 'ABIERTO') or ($respuesta['message']['Solenoid 2'] === 'ABIERTO')) {
            unset($buttons['on_1_2']);
        };
        if (($respuesta['message']['Solenoid 3'] === 'ABIERTO') or ($respuesta['message']['Solenoid 4'] === 'ABIERTO')) {
            unset($buttons['on_3_4']);
        };
        if (($respuesta['message']['Solenoid 5'] === 'ABIERTO') or ($respuesta['message']['Solenoid 6'] === 'ABIERTO')) {
            unset($buttons['on_5_6']);
        };
        if (($respuesta['message']['Solenoid 7'] === 'ABIERTO') or ($respuesta['message']['Solenoid 8'] === 'ABIERTO')) {
            unset($buttons['on_7_8']);
        };
        if (($respuesta['message']['Solenoid 9'] === 'ABIERTO') or ($respuesta['message']['Solenoid 10'] === 'ABIERTO')) {
            unset($buttons['on_9_10']);
        };
        return [
            'buttons' => $buttons,
        ];
    }*/
    public function stopAction()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['stop'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function startAction()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['start'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function onLedAction()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['onLed'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }

    public function offLedAction()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['offLed'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function on_bomba_1Action()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['on_bomba_1'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function off_bomba_1Action()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['off_bomba_1'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function on_bomba_2Action()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['on_bomba_2'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function off_bomba_2Action()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['off_bomba_2'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function on_1_2Action()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['on_1_2'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function on_3_4Action()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['on_3_4'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function on_5_6Action()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['on_5_6'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function on_7_8Action()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['on_7_8'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function on_9_10Action()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['on_9_10'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function off_1_2Action()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['off_1_2'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function off_3_4Action()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['off_3_4'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function off_5_6Action()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['off_5_6'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function off_7_8Action()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['off_7_8'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }
    public function off_9_10Action()
    {
        $scripts = include('../config/scripts.php');
        $command = "python3 " . $scripts['scripts']['off_9_10'];
        shell_exec($command);
        $respuesta = shell_exec("python3 " . $scripts['scripts']['status']);
        return self::adecuadorDeRespuesta(json_decode($respuesta));
    }

}
