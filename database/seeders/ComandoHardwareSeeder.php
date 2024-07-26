<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ComandoHardware;

class ComandoHardwareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Comandos para s1
        ComandoHardware::create(['sistema' => 's1', 'comando' => 'llenar']);
        ComandoHardware::create(['sistema' => 's1', 'comando' => 'esperar']);

        // Comandos para s2
        for ($i = 1; $i <= 13; $i++) {
            ComandoHardware::create(['sistema' => 's2', 'comando' => "on:valvula$i"]);
            ComandoHardware::create(['sistema' => 's2', 'comando' => "off:valvula$i"]);
        }

        // Comandos para s3
        $combinaciones = [
            ['pump1:on', 'pump2:on'],
            ['pump1:on', 'pump2:off'],
            ['pump1:off', 'pump2:on'],
            ['pump1:off', 'pump2:off'],
        ];

        foreach ($combinaciones as $comando) {
            ComandoHardware::create([
                'sistema' => 's3',
                'comando' => json_encode(['actions' => $comando]),
            ]);
        }

        // Comandos para s4
        $actions = [];
        $pump_states = ['on', 'off'];

        foreach ($pump_states as $state1) {
            foreach ($pump_states as $state2) {
                if ($state1 === 'off') {
                    $actions[] = ["pump1:$state1:1", "pump2:$state2:1"];
                } else {
                    for ($i = 1; $i <= 100; $i++) {
                        if ($state2 === 'off') {
                            $actions[] = ["pump1:$state1:$i", "pump2:$state2:1"];
                        } else {
                            for ($j = 1; $j <= 100; $j++) {
                                $actions[] = ["pump1:$state1:$i", "pump2:$state2:$j"];
                            }
                        }
                    }
                }
            }
        }

        foreach ($actions as $action) {
            ComandoHardware::create([
                'sistema' => 's4',
                'comando' => json_encode(['actions' => $action]),
            ]);
        }
    }
}
