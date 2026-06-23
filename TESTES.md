# Relatório de Bugs e QA

Durante a análise e implementação dos testes de integração, os seguintes bugs e inconsistências foram identificados e (onde possível) corrigidos nos testes ou na infraestrutura:

## 1. Problemas de Infraestrutura e Banco de Dados
- **Lock do SQLite no Docker/OneDrive:** A configuração original usava o banco físico `database/database.sqlite` na pasta montada (syncada com OneDrive). Isso causava locks do sistema operacional e resultava em timeouts de 30s no PHP/PDO. **Correção:** O `.env.testing` foi modificado para criar e usar `/tmp/database.sqlite` dentro do container, fora do volume montado, além de alterar os drivers globais de sessão, cache e fila para `array`/`file`/`sync`.
- **Lentidão em ambiente Windows Docker com RefreshDatabase:** A trait `RefreshDatabase` causava muita latência e travamentos no PHP 8.4 apagando e reconstruindo views via Laravel, além da sobrecarga do autoloader na montagem WSL2. **Correção:** Alterado de `RefreshDatabase` para `DatabaseTransactions` nas classes de teste e rodado os comandos de otimização de cache/boot do Laravel.

## 2. Inconsistências de Banco de Dados e Factories
- **Ausência do Campo "sobrenome":** A model `Autor` referia-se a uma coluna `sobrenome` no array `$fillable`, e os testes originais (`AutorTest.php` e `LivroTest.php`) tentavam inserir esse dado. No entanto, a migration de autores não criava a coluna. **Registro:** Os testes foram refatorados para omitir o `sobrenome` para não quebrar a inserção via Eloquent.
- **Factories Faltantes:** Não havia classes de Factory implementadas para `Autor`, `Livro` ou `Biblioteca`. As chamadas a `Autor::factory()` travavam a execução lançando erro de classe inexistente. **Registro:** Modificado as chamadas para `Model::create([])` manuais ou Factories válidas como `UserFactory` e `PessoaFactory`.

## 3. Bugs nas Controllers (Foco dos Testes de QA)
Segundo os requisitos de QA, identificamos os seguintes bugs intencionais nas controllers:

- **AutorController:**
  - Não possui o método `destroy()`, causando quebras na tentativa de exclusão na rota RESTful padrão.
  - O método `create()` retorna a view diretamente sem a variável `autores` (que era checada pela asserção `assertViewHas('autores')` original).

- **LivroController:**
  - O método `store()` e `update()` **não possuem nenhuma validação** (ausência de `$request->validate(...)`), permitindo a persistência de registros vazios ou inválidos, e falhando caso os testes assumam que ele barraria inputs incorretos.
  - Estes mesmos métodos redirecionam para a `index` sem retornar a flash session `message` ou `success` padrão, falhando a verificação de `$response->assertSessionHas('message')`.

- **PessoaController:**
  - O método `store()` faz a validação de confirmação de senha `password !== confirmPassword` mas não faz uso das validações built-in do Laravel para outros campos (e-mail vazio, por exemplo).
  - O método `destroy()` está **completamente vazio**, não excluindo os registros.

- **UserController:**
  - Não realiza validação de campos (nome, e-mail) no seu método `store` ou `update`. E-mails duplicados são rejeitados por falha direta no PDO (`QueryException`) em vez de Validação elegante.

## 4. Pipeline de CI/CD
- O arquivo `.github/workflows/tests.yml` estava configurado para PHP `8.2`, enquanto o projeto requeria PHP `8.4`. **Correção:** Versão ajustada para `8.4` no step `shivammathur/setup-php`.
- O GitHub Actions estava falhando por tentar rodar testes sem que o banco de dados tivesse sido criado ou migrado. **Correção:** Adicionado o comando `php artisan migrate --env=testing --force` no pipeline e forçado a variável `DB_DATABASE=/tmp/database.sqlite`.

## 5. Refinamentos Baseados em Code Review (Melhores Práticas)
- **Mass Assignment no Model Livro:** O model `Livro` não tinha as propriedades `autor_id` e `data_publicacao` na variável `$fillable`, impedindo a inserção de dados no setup dos testes (erro de "NOT NULL constraint"). **Correção:** Substituído `Livro::create()` por `Livro::forceCreate()` na classe `LivroTest` para contornar a restrição sem alterar o model original.
- **Middleware Global:** O uso da trait `WithoutMiddleware` na classe base `TestCase.php` mascarava falhas de segurança de rotas globalmente. **Correção:** A trait foi removida da classe base e aplicada apenas pontualmente nas classes de teste específicas onde a proteção CSRF quebrava as chamadas da API (`LivroTest`, `AutorTest`, etc).
- **Limpeza de Repositório:** Relatórios dinâmicos (`public/coverage/`) e scripts de debug (`test-key.php`) não devem ser versionados. **Correção:** Esses arquivos foram removidos da tracking do Git e adicionados ao `.gitignore`.
- **Rota Inexistente:** Um teste do `BibliotecaTest` chamava erroneamente `route('bibliotecas.new')`. **Correção:** Atualizado para `route('bibliotecas.create')`.

<img width="619" height="615" alt="image" src="https://github.com/user-attachments/assets/2ade1a09-7ea2-4a2e-a7f4-4f9fb168f75c" />

<img width="615" height="605" alt="image" src="https://github.com/user-attachments/assets/a147ad84-719e-4d3f-806b-166e4c3ef22e" />

<img width="634" height="333" alt="image" src="https://github.com/user-attachments/assets/7c69b24d-b8e0-4d08-9fb1-54339d45be3f" />

<img width="1546" height="733" alt="image" src="https://github.com/user-attachments/assets/da2a14ac-7759-425f-9e26-4238daa40901" />
<img width="981" height="288" alt="image" src="https://github.com/user-attachments/assets/b199347b-c0b7-415c-9ef6-bb3bf6c0db3d" />

<img width="1540" height="770" alt="image" src="https://github.com/user-attachments/assets/83b97f62-fd00-4a68-8f6a-b932617eaa17" />

<img width="532" height="339" alt="image" src="https://github.com/user-attachments/assets/7ee5a278-e44d-4713-90f8-6056b771d039" />

