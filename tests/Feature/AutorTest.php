<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\Autor;

class AutorTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    public function test_pode_listar_autores()
    {
        $this->withoutExceptionHandling();
        for ($i = 0; $i < 3; $i++) {
            Autor::create([
                'nome' => 'Autor ' . $i,
                'nacionalidade' => 'Brasileiro'
            ]);
        }

        $response = $this->get(route('autores.index'));

        $response->assertStatus(200);
        $response->assertViewHas('autores');
    }

    public function test_pode_acessar_pagina_de_criacao_de_autor()
    {
        $response = $this->get(route('autores.create'));

        $response->assertStatus(200);
    }

    public function test_pode_criar_autor()
    {
        $data = [
            'nome' => 'J. R. R. Tolkien',
            'nacionalidade' => 'Britânico',
            'data_nascimento' => '1892-01-03'
        ];

        $response = $this->post(route('autores.store'), $data);

        $response->assertRedirect(route('autores.index'));
        $response->assertSessionHas('success', 'Autor criado com sucesso.');

        $this->assertDatabaseHas('autores', [
            'nome' => 'J. R. R. Tolkien'
        ]);
    }

    public function test_nao_pode_criar_autor_sem_nome()
    {
        $data = [
            'nome' => '',
            'nacionalidade' => 'Britânico',
        ];

        $response = $this->post(route('autores.store'), $data);

        $response->assertSessionHasErrors(['nome']);
        $this->assertDatabaseCount('autores', 0);
    }

    public function test_pode_editar_autor()
    {
        $autor = Autor::create([
            'nome' => 'Autor Editavel',
            'nacionalidade' => 'Brasileiro'
        ]);

        $response = $this->get(route('autores.edit', $autor->id));

        $response->assertStatus(200);
        $response->assertViewHas('autor');
    }

    public function test_pode_atualizar_autor()
    {
        $autor = Autor::create([
            'nome' => 'Nome Antigo',
            'nacionalidade' => 'Antiga'
        ]);

        $data = [
            'nome' => 'Nome Novo',
            'nacionalidade' => 'Brasileiro'
        ];

        $response = $this->put(route('autores.update', $autor->id), $data);

        $response->assertRedirect(route('autores.index'));
        $response->assertSessionHas('success', 'Autor atualizado com sucesso.');

        $this->assertDatabaseHas('autores', [
            'id' => $autor->id,
            'nome' => 'Nome Novo',
            'nacionalidade' => 'Brasileiro'
        ]);
    }

    public function test_retorna_404_ao_atualizar_autor_inexistente()
    {
        $data = [
            'nome' => 'Nome Novo',
        ];

        $response = $this->put(route('autores.update', 999), $data);

        $response->assertStatus(404);
    }
}
