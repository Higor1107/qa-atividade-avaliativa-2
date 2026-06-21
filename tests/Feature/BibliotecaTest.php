<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Biblioteca;
use App\Models\User;

class BibliotecaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pode_listar_bibliotecas()
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 3; $i++) {
            Biblioteca::create([
                'nome' => 'Biblioteca ' . $i,
                'endereco' => 'Rua ' . $i,
                'created_by' => $user->id
            ]);
        }

        $response = $this->get(route('bibliotecas.index'));

        $response->assertStatus(200);
        $response->assertViewHas('bibliotecas');
    }

    public function test_pode_acessar_pagina_de_criacao_de_biblioteca()
    {
        $response = $this->get(route('bibliotecas.create'));

        $response->assertStatus(200);
        $response->assertViewHas('users');
    }

    public function test_pode_criar_biblioteca()
    {
        $user = User::factory()->create();

        $data = [
            'created_by' => $user->id,
            'nome' => 'Biblioteca Central',
            'endereco' => 'Rua Principal, 123'
        ];

        $response = $this->post(route('bibliotecas.store'), $data);

        $response->assertRedirect(route('bibliotecas.index'));
        $response->assertSessionHas('message', 'Biblioteca criada com sucesso');

        $this->assertDatabaseHas('bibliotecas', [
            'nome' => 'Biblioteca Central',
            'endereco' => 'Rua Principal, 123'
        ]);
    }

    public function test_nao_pode_criar_biblioteca_sem_dados_obrigatorios()
    {
        $data = [
            'nome' => null // Provoca erro ao salvar
        ];

        $response = $this->post(route('bibliotecas.store'), $data);

        $response->assertRedirect(route('bibliotecas.new', ['error' => 'Erro ao criar a biblioteca: Verifique as informações enviadas']));
        
        $this->assertDatabaseCount('bibliotecas', 0);
    }

    public function test_pode_editar_biblioteca()
    {
        $user = User::factory()->create();
        $biblioteca = Biblioteca::create([
            'nome' => 'Biblioteca Editavel',
            'endereco' => 'Rua',
            'created_by' => $user->id
        ]);

        $response = $this->get(route('bibliotecas.edit', $biblioteca->id));

        $response->assertStatus(200);
        $response->assertViewHas('biblioteca');
    }

    public function test_retorna_erro_ao_editar_biblioteca_inexistente()
    {
        $response = $this->get(route('bibliotecas.edit', 999));

        $response->assertRedirect(route('bibliotecas.index'));
        $response->assertSessionHas('error', 'Biblioteca não encontrada');
    }

    public function test_pode_atualizar_biblioteca()
    {
        $user = User::factory()->create();
        $biblioteca = Biblioteca::create([
            'nome' => 'Nome Antigo',
            'endereco' => 'Rua',
            'created_by' => $user->id
        ]);

        $data = [
            'nome' => 'Nome Novo',
        ];

        $response = $this->put(route('bibliotecas.update', $biblioteca->id), $data);

        $response->assertRedirect(route('bibliotecas.index'));
        $response->assertSessionHas('message', 'Biblioteca atualizada com sucesso');

        $this->assertDatabaseHas('bibliotecas', [
            'id' => $biblioteca->id,
            'nome' => 'Nome Novo'
        ]);
    }

    public function test_retorna_erro_ao_atualizar_biblioteca_inexistente()
    {
        $data = [
            'nome' => 'Nome Novo',
        ];

        $response = $this->put(route('bibliotecas.update', 999), $data);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Biblioteca não encontrada']);
    }

    public function test_pode_excluir_biblioteca()
    {
        $user = User::factory()->create();
        $biblioteca = Biblioteca::create([
            'nome' => 'Biblioteca a excluir',
            'endereco' => 'Rua',
            'created_by' => $user->id
        ]);

        $response = $this->delete(route('bibliotecas.destroy', $biblioteca->id));

        $response->assertRedirect(route('bibliotecas.index'));
        $response->assertSessionHas('message', 'Biblioteca excluída com sucesso');

        $this->assertDatabaseMissing('bibliotecas', [
            'id' => $biblioteca->id
        ]);
    }

    public function test_retorna_erro_ao_excluir_biblioteca_inexistente()
    {
        $response = $this->delete(route('bibliotecas.destroy', 999));

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Biblioteca não encontrada']);
    }
}
