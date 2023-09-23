<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use PiPHP\GPIO\GPIO;
use PiPHP\GPIO\Pin\PinInterface;

$gpio = new GPIO();

$pinsOut[23] = $gpio->getOutputPin(23);
$pinsOut[27] = $gpio->getOutputPin(27);
$pinsOut[22] = $gpio->getOutputPin(22);


class PortsController extends Controller
{
    public function onLed(): JsonResponse
    {
        global $pinsOut;
        $pinsOut[23]->setValue(PinInterface::VALUE_HIGH);
        return new JsonResponse(['message' => 'Led encendido']);
    }

    public function offLed(): JsonResponse
    {
        global $pinsOut;
        $pinsOut[23]->setValue(PinInterface::VALUE_LOW);
        return new JsonResponse(['message' => 'Led encendido']);
    }
}
