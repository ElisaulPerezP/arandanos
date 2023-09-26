<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Redirect;


class PortsController extends Controller
{
    public function onLed(): void
    {
        $scriptPath = '/var/www/onLed.py';
        $command = "sudo python3 $scriptPath";
        shell_exec($command);
    }

    public function offLed(): void
    {
        $scriptPath = '/var/www/offLed.py';
        $command = "sudo python3 $scriptPath";
        shell_exec($command);

    }
}
