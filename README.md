# API de Gerenciamento de Consultas

Uma API RESTful construída com Laravel para gerenciar consultas e serviços adicionais, utilizando cache com Redis, filas para notificações e uma suíte de testes completa com Pest.

## Requisitos

- Docker
- Composer

## Instalação e Configuração

Siga os passos abaixo para configurar o ambiente de desenvolvimento.

1.  **Clonar o Repositório**
    ```bash
    git clone git@github.com:montanhes/consultas_webjasper.git
    cd consultas_webjasper
    ```

2.  **Instalar Dependências**
    ```bash
    composer install
    ```

3.  **Configurar Variáveis de Ambiente**
    Copie o arquivo de exemplo `.env.example` para um novo arquivo chamado `.env`.
      ```bash
      cp .env.example .env
      ```
    Abra o arquivo `.env` e configure as variáveis necessárias, especialmente as de e-mail (Mailtrap), conforme detalhado na seção "Variáveis de Ambiente" abaixo.

4.  **Iniciar os Containers Docker**
    O projeto utiliza Laravel Sail para gerenciar o ambiente Docker.
      ```bash
      sail up -d
      ```

5.  **Gerar a Chave da Aplicação**
    ```bash
    sail artisan key:generate
    ```

6.  **Executar as Migrations e Seeders**
    Este comando irá criar a estrutura do banco de dados e popular com dados iniciais (usuário de teste e serviços).
      ```bash
      sail artisan migrate:fresh --seed
      ```

## Usuário de Teste

Ao executar o comando `migrate:fresh --seed`, um usuário de teste é criado com as seguintes credenciais, que podem ser usadas para fazer login e obter um token de autenticação:

- **E-mail:** `test@example.com`
- **Senha:** `password`

## Executando a Aplicação

Após o `sail up`, a aplicação estará disponível em `http://localhost`.

- **API Base URL:** `http://localhost/api`
- **Documentação da API (Scramble):** `http://localhost/docs/api`

### Fila de Tarefas (Queue Worker)

Para que as notificações por e-mail sejam processadas, você precisa iniciar um "worker" de fila em um terminal separado:

```bash
sail artisan queue:work
```

## Executando os Testes

Para rodar a suíte de testes completa (Pest), execute o comando:

```bash
sail test
```

## Variáveis de Ambiente (.env)

As seguintes variáveis no arquivo `.env` são importantes para a configuração do projeto:

| Variável              | Descrição                                                                                             | Valor Padrão (Recomendado) |
| --------------------- | ----------------------------------------------------------------------------------------------------- | -------------------------- |
| `DB_CONNECTION`       | Conexão de banco de dados. O Sail gerencia isso.                                                      | `mysql`                    |
| `DB_HOST`             | Host do banco de dados. O Sail gerencia isso.                                                         | `mysql`                    |
| `CACHE_DRIVER`        | Driver de cache. Deve ser `redis` para usar o cache de listagens.                                     | `redis`                    |
| `QUEUE_CONNECTION`    | Driver da fila. Deve ser `redis` para processar jobs em segundo plano.                                | `redis`                    |
| `REDIS_HOST`          | Host do Redis. O Sail gerencia isso.                                                                  | `redis`                    |
| `MAIL_MAILER`         | Driver de e-mail.                                                                                     | `smtp`                     |
| `MAIL_HOST`           | Host do seu serviço de SMTP.                                                                          | `smtp.mailtrap.io`         |
| `MAIL_PORT`           | Porta do seu serviço de SMTP.                                                                         | `2525`                     |
| `MAIL_USERNAME`       | **(Requer Ação)** Seu nome de usuário do Mailtrap.                                                    | `your_mailtrap_username`   |
| `MAIL_PASSWORD`       | **(Requer Ação)** Sua senha do Mailtrap.                                                              | `your_mailtrap_password`   |
| `MAIL_ENCRYPTION`     | Criptografia para o SMTP.                                                                             | `tls`                      |
| `MAIL_FROM_ADDRESS`   | E-mail remetente padrão.                                                                              | `"hello@example.com"`      |
