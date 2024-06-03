<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash as FacadesHash;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_task()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password')
        ]);

        // Autenticar al usuario y obtener el token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('access_token');

        $this->assertNotEmpty($token);

        // Intentar crear una tarea con el token
        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'description' => 'This is a test task description.'
        ], [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Task registered successfully']);

        // Verificar que la tarea se ha creado en la base de datos
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'description' => 'This is a test task description.',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    /** @test */
    public function it_updates_a_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        // Autenticar al usuario y obtener el token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('access_token');

        $this->assertNotEmpty($token);

        // Actualizar la tarea
        $response = $this->putJson('/api/tasks/' . $task->id, [
            'title' => 'Updated Task',
            'description' => 'Updated description.'
        ], [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200);
        // Volver a cargar la tarea desde la base de datos
      //  $updatedTask = Task::find($task->id);

        // // Verificar que la tarea se haya actualizado correctamente en la base de datos
        // $this->assertEquals('Updated Task', $updatedTask->title);
        // $this->assertEquals('Updated description.', $updatedTask->description);
        // $this->assertDatabaseHas('tasks', [
        //     'id' => $task->id,
        //     'title' => 'Updated Task',
        //     'description' => 'Updated description.',
        // ]);
    }



    /** @test */
    public function it_deletes_a_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        // Autenticar al usuario y obtener el token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('access_token');

        $this->assertNotEmpty($token);

        // Eliminar la tarea
        $response = $this->deleteJson('/api/tasks/' . $task->id, [], [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200);
        $this->assertDeleted($task);
    }


     /** @test */
    public function it_fetches_all_tasks_for_a_user()
    {
        $user = User::factory()->create();
        $tasks = Task::factory()->count(3)->create(['user_id' => $user->id]);

        // Autenticar al usuario y obtener el token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('access_token');

        $this->assertNotEmpty($token);

        // // Obtener todas las tareas del usuario
        $response = $this->getJson('/api/tasks', [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'tasks'); 
    }


    /** @test */
    public function it_shows_task_details()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        // Autenticar al usuario y obtener el token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('access_token');

        $this->assertNotEmpty($token);

        // Obtener detalles de la tarea
        $response = $this->getJson('/api/tasks/' . $task->id, [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'user_id' => $task->user_id,
                'created_at' => $task->created_at->toISOString(),
                'updated_at' => $task->updated_at->toISOString()
            ]
        ]);
        
    }
}
