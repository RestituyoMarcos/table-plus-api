<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No se encontró ningún usuario, No se pudo crear la tarea.');
            return;
        }

        $tasks = [
            ['title' => 'Revisar informe de ventas trimestral', 'description' => 'Analizar las cifras y preparar un resumen para la reunión.'],
            ['title' => 'Planificar campaña de marketing Q3', 'description' => 'Definir objetivos, presupuesto y canales para el próximo trimestre.'],
            ['title' => 'Actualizar dependencias del proyecto', 'description' => 'Ejecutar composer update y npm update, y verificar la compatibilidad.'],
            ['title' => 'Contactar al proveedor de hosting', 'description' => 'Consultar sobre las opciones de escalabilidad del servidor actual.'],
            ['title' => 'Diseñar mockups para la nueva App', 'description' => 'Crear los diseños de las pantallas principales en Figma.'],
            ['title' => 'Escribir el post del blog de esta semana', 'description' => 'Tema: "5 tips para mejorar la productividad en Laravel".'],
            ['title' => 'Preparar la presentación para el cliente', 'description' => 'Resumir los avances del sprint y los próximos pasos.'],
            ['title' => 'Hacer backup de la base de datos', 'description' => 'Realizar un backup completo de la base de datos de producción.'],
            ['title' => 'Entrevistar candidatos para el puesto de frontend', 'description' => 'Revisar perfiles y realizar las entrevistas técnicas.'],
            ['title' => 'Configurar el entorno de staging', 'description' => 'Clonar el entorno de producción para pruebas finales.'],
        ];

        foreach ($tasks as $taskData) {
            Task::create([
                'user_id' => $users->random()->id,
                'title' => $taskData['title'],
                'description' => $taskData['description'],
                'status' => ['pending', 'completed'][array_rand(['pending', 'completed'])],
                'due_date' => Carbon::now()->addDays(rand(1, 30)),
            ]);
        }
    }
}
