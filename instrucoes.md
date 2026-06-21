Atividade Avaliativa 2 - Testes de Integração

Objetivo
Desenvolver testes de integração para uma API e configurar a execução automática desses testes utilizando GitHub Actions a cada pull request.

Instruções

Faça o fork do repositório: https://github.com/guilherme-ferraz/qa-atividade-avaliativa-2
Não trabalhe diretamente na branch `master`, crie uma nova branch para suas alterações no código, e quando precisar fazer pull request, faça para a branch `develop`
Implemente testes de integração para os principais endpoints da aplicação.
Utilize ferramentas de IA (como Copilot, Claude Code, Gemini ou similares) como apoio no desenvolvimento dos testes. O foco da atividade não é apenas gerar código, mas definir uma boa cobertura de cenários.
Configure um workflow no GitHub Actions para executar automaticamente os testes a cada pull request.

Sugestões:

Os testes devem cobrir, sempre que aplicável:
Cenários válidos (operações bem-sucedidas de CRUD)
Validações de entrada (dados inválidos ou ausentes)
Respostas da API (códigos HTTP adequados, como 200, 201, 400, 404, 422)
Regras de negócio implementadas
Garantia de que dados inválidos não sejam persistidos no banco
Situações que possam causar regressões


Data de Entrega: 23/06
Entregáveis:
Adicionar `guilherme-ferraz` ao repositório github
Um arquivo doc ou readme no próprio repositório descrevendo o que foi testado e identificação dos problemas encontrados nos testes que ainda estiverem falhando.
Apresentar no laboratório a execução dos testes e da cobertura de código testado.

Comandos para iniciar/resetar o projeto:

cp .env-example .env - configuração inicial das variáveis de ambiente da aplicação
DB_CONNECTION=sqlite

composer update --no-cache - instalar dependências

php artisan key:generate - gerar chave aleatória para variável de ambiente APP_KEY=

php artisan migrate - executar alterações no banco
php artisan migrate:rollback - retroceder alterações no banco

php artisan db:seed - popular o banco de dados com registros default/teste

php artisan serve - executar a aplicação

php artisan test - executar todos os testes
php artisan test --filter NomeDoTeste - executar teste específico

Puxar alterações do repositório base (fork)

Para pegar as alterações novas, acesse o próprio repositório e verifique se aparece uma mensagem como na imagem abaixo, em seguida basta clicar em `Sync fork`


Ao clicar em `Sync Fork`, vai abrir um pop-up como na figura a seguir, basta clicar em `Update branch`, isso vai mesclar master com master, como cada aluno está trabalhando em branchs distintas no próprio repositório, ainda será necessário mesclar a master local com a branch em questão, para isso abra o terminal, faça o checkout para a branch que estiver trabalhando, e em seguida puxe as alterações da master com o comando `git merge master`.



O esperado é que sejam recebidas novas alterações com novos CRUDs do repositório base.
Se ocorrer conflito ou não for possível seguir o passo a passo por algum outro motivo, não deixe de me procurar no laboratório para resolvermos juntos.

Mesclar alterações com a sua branch após o Sync Fork
git checkout <Nome da Sua Branch>
git merge master

Configurar ambiente:
cp .env-example .env
Configurar conexão mysql no arquivo .env
Executar docker compose up
Entrar no container:
docker exec -it app_laravel bash
