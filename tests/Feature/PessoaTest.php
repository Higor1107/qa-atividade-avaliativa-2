<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\Pessoa;

class PessoaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pode_listar_pessoas()
    {
        Pessoa::factory()->count(3)->create();

        $response = $this->get(route('pessoas.index'));

        $response->assertStatus(200);
        $response->assertViewHas('pessoas');
    }

    public function test_pode_acessar_pagina_de_criacao_de_pessoa()
    {
        $response = $this->get(route('pessoas.create'));

        $response->assertStatus(200);
    }

    public function test_pode_criar_pessoa_com_senhas_iguais()
    {
        $data = [
            'name' => 'Pessoa Teste',
            'email' => 'pessoa@teste.com',
            'telefone' => '11999999999',
            'matricula' => '123456',
            'password' => 'senha123',
            'confirmPassword' => 'senha123'
        ];

        $response = $this->post(route('pessoas.store'), $data);

        $response->assertRedirect(route('pessoas.index'));
        $response->assertSessionHas('message', 'Pessoa criada com sucesso!');

        $this->assertDatabaseHas('pessoas', [
            'name' => 'Pessoa Teste',
            'email' => 'pessoa@teste.com',
            'matricula' => '123456'
        ]);
    }

    public function test_nao_pode_criar_pessoa_com_senhas_diferentes()
    {
        $data = [
            'name' => 'Pessoa Teste',
            'email' => 'pessoa@teste.com',
            'telefone' => '11999999999',
            'matricula' => '123456',
            'password' => 'senha123',
            'confirmPassword' => 'senhaDiferente'
        ];

        $response = $this->post(route('pessoas.store'), $data);

        $response->assertSessionHas('error', 'As senhas não coincidem!');
        
        $this->assertDatabaseMissing('pessoas', [
            'email' => 'pessoa@teste.com'
        ]);
    }

    public function test_pode_editar_pessoa()
    {
        $pessoa = Pessoa::factory()->create();

        $response = $this->get(route('pessoas.edit', $pessoa->id));

        $response->assertStatus(200);
        $response->assertViewHas('pessoa');
    }

    public function test_retorna_erro_ao_editar_pessoa_inexistente()
    {
        $response = $this->get(route('pessoas.edit', 999));

        $response->assertRedirect(route('pessoas.index'));
        $response->assertSessionHas('error', 'Pessoa não encontrada');
    }

    public function test_pode_atualizar_pessoa()
    {
        $pessoa = Pessoa::factory()->create([
            'name' => 'Nome Antigo'
        ]);

        $data = [
            'name' => 'Nome Novo',
            'email' => $pessoa->email,
            'telefone' => $pessoa->telefone,
            'matricula' => $pessoa->matricula,
            'password' => 'novasenha',
            'confirmPassword' => 'novasenha'
        ];

        $response = $this->put(route('pessoas.update', $pessoa->id), $data);

        $response->assertRedirect(route('pessoas.index'));
        $response->assertSessionHas('message', 'Pessoa atualizada com sucesso!');

        $this->assertDatabaseHas('pessoas', [
            'id' => $pessoa->id,
            'name' => 'Nome Novo'
        ]);
    }

    public function test_erro_ao_atualizar_pessoa_com_senhas_diferentes()
    {
        $pessoa = Pessoa::factory()->create([
            'name' => 'Nome Antigo'
        ]);

        $data = [
            'name' => 'Nome Novo',
            'email' => $pessoa->email,
            'telefone' => $pessoa->telefone,
            'matricula' => $pessoa->matricula,
            'password' => 'novasenha',
            'confirmPassword' => 'senhadiferente'
        ];

        $response = $this->put(route('pessoas.update', $pessoa->id), $data);

        $response->assertSessionHas('error', 'As senhas não coincidem!');

        // Garante que não atualizou o nome
        $this->assertDatabaseHas('pessoas', [
            'id' => $pessoa->id,
            'name' => 'Nome Antigo'
        ]);
    }
}
