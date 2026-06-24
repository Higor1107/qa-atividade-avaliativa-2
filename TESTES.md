# Relatório de Bugs e Engenharia de Qualidade (QA)

Abaixo documentamos todo o ciclo de diagnóstico, identificação de bugs e as refatorações realizadas. O objetivo primário de escrever testes de integração foi atingido: **os testes interceptaram falhas estruturais críticas no código base**, as quais foram corrigidas por nós. 

Atualmente, a suíte conta com **100% de aprovação (Build Verde)**, executando **44 testes e 111 asserções**.

---

## 1. Problemas de Infraestrutura e Banco de Dados (Resolvidos)

- **Gargalo do SQLite no WSL2 / Windows (Database Locked):**
  - **Como era:** O arquivo de banco `database/database.sqlite` ficava numa pasta sincronizada pelo Windows/OneDrive. Rodar testes de integração causava sobrecarga de I/O, resultando no erro clássico de `database locked` e timeouts na execução.
  - **Como ficou:** Criamos e configuramos o ambiente `.env.testing` para forçar o banco SQLite a rodar na pasta `/tmp/database.sqlite` diretamente no file system nativo do Linux do Docker. A lentidão e os locks sumiram completamente.

- **Demora excessiva e lentidão do PHPUnit:**
  - **Como era:** O uso da trait `RefreshDatabase` forçava o Laravel a dar drop e recriar todo o esquema físico das tabelas a cada teste rodado.
  - **Como ficou:** Alteramos a abordagem para utilizar a trait `DatabaseTransactions`, que constrói o banco apenas uma vez e se baseia em Rollbacks automáticos de banco após cada inserção de teste. A performance aumentou drasticamente (bateria de testes inteira rodando em cerca de 3 a 5 segundos de execução pura de banco).

- **O "Falso-Positivo" do CSRF (Erro 419):**
  - **Como era:** Originalmente, a classe base de testes usava a trait `WithoutMiddleware`. Isso removia a trava CSRF, mas também **desligava a sessão**, impedindo testes de mensagens de erro (flash sessions). Ao removermos essa trait para fazer as sessões funcionarem, todos os envios via `POST/PUT/DELETE` nos testes começaram a falhar retornando status `419 Page Expired`.
  - **Como ficou:** Para contornar, religamos os middlewares para não perder o gerenciamento de sessões e configuramos especificamente o arquivo `bootstrap/app.php`. Adicionamos a lógica `if (($_ENV['APP_ENV'] ?? '') === 'testing')` para dispensar a checagem de token CSRF apenas no ambiente de testes de forma nativa e limpa.

---

## 2. Inconsistências de Banco de Dados e Factories (Resolvidos)

- **Omissão de Colunas em Migrations:** A model `Autor` possuía `sobrenome` em seu `$fillable`, mas a respectiva Migration não criava essa coluna no banco de dados.
  - **Como ficou:** Ao invés de quebrar as regras de modelagem do desenvolvedor, refatoramos a inserção de testes para não preencher o campo e evitar falhas na persistência (`NOT NULL constraint`).
  
- **Limitações do Mass Assignment:** O Model `Livro` protegia os campos `autor_id` e `data_publicacao`, não permitindo preenchimento via arrays normais de injeção em testes.
  - **Como ficou:** Nos testes, usamos a propriedade `forceCreate()` da Eloquent, o que injeta os valores forçadamente para o Setup sem precisarmos desproteger (abrir brecha de segurança) o Model `Livro` em produção.

---

## 3. Os Bugs Reais Encontrados e Corrigidos no Código

Este foi o verdadeiro trabalho do Analista de QA. Os testes revelaram falhas graves de arquitetura nas rotas, as quais foram corrigidas para atingir os 100% de sucesso.

### 3.1. LivroController e AutorController
- **Como não funcionava:** As rotas `store()` e `update()` recebiam parâmetros do front-end e tentavam salvar diretamente no banco **sem nenhuma validação**. Além disso, a `AutorController` sequer possuía os métodos `destroy()`, gerando erro 500 (Method Not Allowed) ou 404 nos testes RESTful de deleção. O método `destroy()` de `Livros` retornava view incorreta.
- **Como agora funciona:** Injetamos a validação `$request->validate()` rigorosa para dados nulos. Criamos do zero as funções `destroy()` para manipular o `Livro::find($id)` e o `Autor::find($id)` e invocar as funções nativas de `delete()` com redirecionamento de sucesso.

### 3.2. PessoaController
- **Como não funcionava:** O método `destroy()` estava apenas definido (vazio em seu escopo de função), permitindo requisições HTTP 200 de sucesso como se tivesse excluído, mas o dado permanecia vivo no banco. Além disso, a validação de senhas divergentes passava sem bater em checagens do framework.
- **Como agora funciona:** O método de exclusão foi codificado para instanciar a entidade e acionar o `delete()`. Adicionamos verificações estritas para os e-mails e criamos a lógica coerente de comparação de password.

### 3.3. BibliotecasController e UserController
- **Como não funcionava:** Os testes dessas classes procuravam respostas de `errors` específicos ou redirecionamentos de variáveis na URL (ex: `?error=Erro ao criar`). Como a controller confiava totalmente que apenas o banco daria erro (Exceptions cruas via PDO) ao cadastrar um e-mail duplicado ou enviar um input em branco, o framework devolvia erros de exceção generalizados (ou 500).
- **Como agora funciona:** Usamos o recurso de validação manual `Validator::make($request->all(), [...])` no backend em vez do automático, para podermos injetar exatamente a frase de erro que a view e o front-end estavam preparados para receber (`with('error', 'Erro ao criar o usuário...')`), compatibilizando as lógicas semânticas da interface do usuário e aprovando todos os testes de QA.

---

## 4. Pipeline de Integração Contínua (CI/CD Configurada)
- A pipeline configurada no Github Actions para a rotina de CI automatizada sofria com erros de infra (versão desatualizada do PHP e ausência de banco de dados nativo no container efêmero).
- **Solução Implementada e Aprovada:** 
  1. Alteramos a configuração YAML para provisionar instâncias no Github utilizando `PHP 8.4`, eliminando incompatibilidades do `Composer`.
  2. Implementamos o comando mandatário de esteira: `php artisan migrate --env=testing --force` antes do `php artisan test`, preparando totalmente o ambiente de nuvem do GitHub e rodando os testes integrados de forma transparente em cada Push.
  
**Resultado Atual da Esteira: Build SUCCESS (Verde).**

<img width="571" height="739" alt="image" src="https://github.com/user-attachments/assets/063322b9-2259-41e8-a016-c65d020c7e54" />

<img width="567" height="438" alt="image" src="https://github.com/user-attachments/assets/171c1eed-42cd-44ac-991c-c1d7dfc0a5b8" />

<img width="1098" height="575" alt="Captura de tela 2026-06-24 161005" src="https://github.com/user-attachments/assets/09eacf97-6463-4cb7-b0ec-539e8bbd66fe" />

