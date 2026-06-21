<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Livro;
use App\Models\Autor;

class LivroTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pode_listar_livros()
    {
        $autor = Autor::create([
            'nome' => 'Autor Teste',
            'nacionalidade' => 'Brasileiro'
        ]);
        for ($i = 0; $i < 3; $i++) {
            Livro::create([
                'autor_id' => $autor->id,
                'titulo' => 'Livro ' . $i,
                'isbn' => '123-456-' . $i,
                'data_publicacao' => '2023-01-01'
            ]);
        }

        $response = $this->get(route('livros.index'));

        $response->assertStatus(200);
        $response->assertViewHas('livros');
    }

    public function test_pode_acessar_pagina_de_criacao_de_livro()
    {
        $response = $this->get(route('livros.create'));

        $response->assertStatus(200);
        $response->assertViewHas('autores');
    }

    public function test_pode_criar_livro()
    {
        $autor = Autor::create([
            'nome' => 'J. R. R. Tolkien',
            'nacionalidade' => 'Britânico'
        ]);

        $data = [
            'autor_id' => $autor->id,
            'titulo' => 'O Senhor dos Anéis',
            'isbn' => '978-3-16-148410-0',
            'data_publicacao' => '1954-07-29'
        ];

        $response = $this->post(route('livros.store'), $data);

        $response->assertRedirect(route('livros.index'));

        $this->assertDatabaseHas('livros', [
            'titulo' => 'O Senhor dos Anéis',
            'isbn' => '978-3-16-148410-0'
        ]);
    }

    public function test_pode_exibir_livro_especifico()
    {
        $autor = Autor::create([
            'nome' => 'Autor Teste',
            'nacionalidade' => 'Br'
        ]);
        $livro = Livro::create([
            'autor_id' => $autor->id,
            'titulo' => 'Livro Show',
            'isbn' => '999',
            'data_publicacao' => '2023-01-01'
        ]);

        $response = $this->get(route('livros.show', $livro->id));

        $response->assertStatus(200);
        $response->assertViewHas('livro');
    }

    public function test_retorna_404_ao_exibir_livro_inexistente()
    {
        $response = $this->get(route('livros.show', 999));

        $response->assertStatus(404);
    }

    public function test_pode_editar_livro()
    {
        $autor = Autor::create([
            'nome' => 'Autor Edit',
            'nacionalidade' => 'Br'
        ]);
        $livro = Livro::create([
            'autor_id' => $autor->id,
            'titulo' => 'Livro Edit',
            'isbn' => '888',
            'data_publicacao' => '2023-01-01'
        ]);

        $response = $this->get(route('livros.edit', $livro->id));

        $response->assertStatus(200);
        $response->assertViewHas('livro');
        $response->assertViewHas('autores');
    }

    public function test_pode_atualizar_livro()
    {
        $autor = Autor::create([
            'nome' => 'Autor Update',
            'nacionalidade' => 'Br'
        ]);
        $livro = Livro::create([
            'autor_id' => $autor->id,
            'titulo' => 'Título Antigo',
            'isbn' => '777',
            'data_publicacao' => '2023-01-01'
        ]);

        $data = [
            'autor_id' => $livro->autor_id,
            'titulo' => 'Título Novo',
            'isbn' => $livro->isbn,
            'data_publicacao' => $livro->data_publicacao
        ];

        $response = $this->put(route('livros.update', $livro->id), $data);

        $response->assertRedirect(route('livros.index'));

        $this->assertDatabaseHas('livros', [
            'id' => $livro->id,
            'titulo' => 'Título Novo'
        ]);
    }

    public function test_pode_excluir_livro()
    {
        $autor = Autor::create([
            'nome' => 'Autor Delete',
            'nacionalidade' => 'Br'
        ]);
        $livro = Livro::create([
            'autor_id' => $autor->id,
            'titulo' => 'Livro Delete',
            'isbn' => '666',
            'data_publicacao' => '2023-01-01'
        ]);

        $response = $this->delete(route('livros.destroy', $livro->id));

        $response->assertRedirect(route('livros.index'));

        $this->assertDatabaseMissing('livros', [
            'id' => $livro->id
        ]);
    }
}
