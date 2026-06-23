<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\User;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pode_listar_usuarios()
    {
        User::factory()->count(3)->create();

        $response = $this->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertViewHas('users');
    }

    public function test_pode_exibir_usuario_especifico()
    {
        $user = User::factory()->create();

        $response = $this->get(route('users.show', $user->id));

        $response->assertStatus(200);
        $response->assertViewHas('user');
    }

    public function test_retorna_erro_ao_exibir_usuario_inexistente()
    {
        $response = $this->get(route('users.show', 999));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error', 'Usuário não encontrado');
    }

    public function test_pode_acessar_pagina_de_criacao_de_usuario()
    {
        $response = $this->get(route('users.create'));

        $response->assertStatus(200);
    }

    public function test_pode_criar_usuario()
    {
        $data = [
            'name' => 'Novo Usuário',
            'email' => 'novo@email.com',
            'password' => 'senha123'
        ];

        $response = $this->post(route('users.store'), $data);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('message', 'Usuário criado com sucesso');

        $this->assertDatabaseHas('users', [
            'name' => 'Novo Usuário',
            'email' => 'novo@email.com'
        ]);
    }

    public function test_nao_pode_criar_usuario_com_email_duplicado()
    {
        User::factory()->create(['email' => 'duplicado@email.com']);

        $data = [
            'name' => 'Outro Usuário',
            'email' => 'duplicado@email.com', // erro do banco de dados (unique constraint)
            'password' => 'senha123'
        ];

        $response = $this->post(route('users.store'), $data);

        $response->assertRedirect(route('users.create'));
        $response->assertSessionHas('error', 'Erro ao criar o usuário: Verifique as informações enviadas');
    }

    public function test_pode_editar_usuario()
    {
        $user = User::factory()->create();

        $response = $this->get(route('users.edit', $user->id));

        $response->assertStatus(200);
        $response->assertViewHas('user');
    }

    public function test_pode_atualizar_usuario()
    {
        $user = User::factory()->create([
            'name' => 'Nome Antigo'
        ]);

        $data = [
            'name' => 'Nome Novo',
            'email' => $user->email,
            'role' => 'admin'
        ];

        $response = $this->put(route('users.update', $user->id), $data);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('message', 'Usuário atualizado com sucesso');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nome Novo',
            'role' => 'admin'
        ]);
    }

    public function test_pode_excluir_usuario()
    {
        $user = User::factory()->create();

        $response = $this->delete(route('users.destroy', $user->id));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('message', 'Usuário excluído com sucesso');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }
}
