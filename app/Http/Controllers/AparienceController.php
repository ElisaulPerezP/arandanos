<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use App\Models\Cultivo;

class AparienceController extends Controller
{
    public function showDashboard()
    {
            // Obtiene el único cultivo registrado
            $cultivo = Cultivo::first();

            // Pasa el cultivo a la vista del dashboard
            return view('dashboard', compact('cultivo'));
    }
}
