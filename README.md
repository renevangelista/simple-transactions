# Simple Transactions

### Requisitos
* PHP >= 7.3

### Instalação

* Clonar o Projeto

* Copie o arquivo `.env.example` para um novo arquivo denominado `.env`</cod>

* O próximo paso é definir a chave do projeto com uma string aleatória usando o seguinte comando.
    ```
    php artisan key:generate
    ```

* Rode o comando abaixo para baixar as dependencias do php.
    ```
    composer install
    ```

* Crie o banco e adicionar configurações do banco no `.env`

* Rodar o comando abaixo para criar as tabelas
    ```
    php artisan migrate
    ```

### Projeto de Software

A arquitetura de software do Simple Transactions é organizado em três camadas, como mostra a figura abaixo.

![](/project_architecture.png)

Para organizar a camada de Interface com o Usuário e sua comunicação com a Camada de Lógica de Negócio, é usado o padrão MVC (Model-View-Controller).
Assim, a camada de interface com o usuário contém tanto classes desempenhando o papel de visão, quanto classes desempenhando o papel de controlador.
Todas as classes do pacote dessa camada que desempenha papel de controladores devem ser nomeadas terminando com o sufixo **Controller**.

Para organizar a camada de Lógica de Negócio, é usado o padrão arquitetônico Camada de Serviço, o qual considera dois tipos de lógico de negócio:
a Lógica de Domínio, que trata das classes de domínio e são agrupadas no componente Model, e a Lógica de Aplicação, que se refere à lógica de negócio e tratada pelo Componente Service.
Uma vez que as classes do pacote service capturam a lógica de aplicação, elas devem ser nomeadas terminando com o sufixo **Service**.

Por fim, a camada de Gerência de Dados é organizada seguindo o padrão Repositório.
Objetos dessa camada podem ser acessados por objetos do pacote Service.
Como os objetos do pacote Service são os responsáveis pela lógica de aplicação, é natural que os mesmos solicitem serviços de persistência.
