<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Challenge;
use App\Models\CourseUser;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Crear Roles del Sistema
        $roles = ['admin', 'professor', 'ta', 'moderator', 'support', 'student'];
        foreach ($roles as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role]);
        }

        // 1. Crear Instituciones
        $espol = Institution::create([
            'name' => 'ESPOL',
            'slug' => 'espol',
            'domain' => 'espol.edu.ec',
            'type' => 'university'
        ]);

        $bootcamp = Institution::create([
            'name' => 'ITConsultore Bootcamp',
            'slug' => 'itconsultore',
            'domain' => 'itconsultore.com',
            'type' => 'bootcamp'
        ]);

        // 2. Crear Usuarios de Prueba
        $admin = User::create([
            'name' => 'Administrador Global',
            'email' => 'admin@prolecom.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'institution_id' => $bootcamp->id,
            'xp' => 1000
        ]);
        $admin->assignRole('admin');
        
        $profesor = User::create([
            'name' => 'Profesor Python',
            'email' => 'profesor@espol.edu.ec',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'institution_id' => $espol->id,
            'xp' => 500
        ]);
        $profesor->assignRole('professor');

        $estudiante = User::create([
            'name' => 'Estudiante Autodidacta',
            'email' => 'estudiante@gmail.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'institution_id' => null,
            'xp' => 0
        ]);
        $estudiante->assignRole('student');

        // 3. Crear Curso de Prueba
        $cursoPython = Course::create([
            'title' => 'Fundamentos de Programación en Python',
            'slug' => 'fundamentos-python',
            'description' => 'Curso completo desde cero hasta estructuras de datos.',
            'category' => 'programming',
            'status' => 'public',
            'has_leaderboard' => true,
            'owner_id' => $profesor->id
        ]);

        // 4. Inscribir usuarios al curso
        CourseUser::create([
            'course_id' => $cursoPython->id,
            'user_id' => $profesor->id,
            'role' => 'professor',
            'status' => 'enrolled'
        ]);

        CourseUser::create([
            'course_id' => $cursoPython->id,
            'user_id' => $estudiante->id,
            'role' => 'student',
            'status' => 'enrolled'
        ]);

        // 5. Crear un Módulo
        $modulo1 = Module::create([
            'course_id' => $cursoPython->id,
            'title' => 'Módulo 1: Variables y Tipos de Datos',
            'description' => 'Introducción básica a Python.',
            'order' => 1
        ]);

        // 6. Crear un Reto de Programación
        $reto = Challenge::create([
            'module_id' => $modulo1->id,
            'title' => 'Hola Mundo en Python',
            'description' => 'Escribe un programa que imprima "Hola Mundo".',
            'difficulty' => 'easy',
            'language_id' => 71, // Judge0 ID for Python
            'language_name' => 'Python (3.8.1)',
            'starter_code' => "def main():\n    # Escribe tu código aquí\n    pass\n\nif __name__ == '__main__':\n    main()",
            'points' => 10,
            'status' => 'approved',
            'creator_id' => $profesor->id
        ]);
        
        $reto->testCases()->create([
            'input' => null,
            'expected_output' => "Hola Mundo\n",
            'is_hidden' => false
        ]);
    }
}
