# Sicredi API

Este pacote fornece um cliente PHP para interação com a API do Sicredi. Ele permite que os desenvolvedores integrem facilmente o Sicredi às suas aplicações PHP. O cliente oferece suporte a uma ampla gama de recursos dentro da API Charges, incluindo criação e consulta de boletos, geração de boletos e recuperação de relatórios de pagamento.

O pacote é construído sobre o cliente HTTP Guzzle e fornece uma API simples e orientada a objetos para interagir com a API Sicredi. Também inclui documentação abrangente e exemplos para ajudar os desenvolvedores a começar rapidamente.

Embora o pacote atualmente suporte apenas a API para Boletos, ele foi projetado tendo em mente a extensibilidade e pode ser facilmente estendido para suportar funcionalidades adicionais conforme necessário.


## Instalação

Primeiro, você precisará instalar o [Composer](http://getcomposer.org/) seguindo as instruções do site.

Depois, basta executar o seguinte comando:

```sh
compositor requer carlinhoanc/sicredi-api
```

## Uso

Depois de instalá-lo, você pode seguir as seguintes etapas para criar um novo Boleto:

1. Crie uma nova instância de Cliente: Crie uma nova instância de Cliente passando como argumentos sua chave API do SICREDI, número da cooperativa, número da postagem e número do beneficiário. Por exemplo:

```php
$client = new \SicrediAPI\Client(
    $_ENV['SICREDI_API_KEY'],
    $_ENV['SICREDI_COOPERATIVE'],
    $_ENV['SICREDI_POST'],
    $_ENV['SICREDI_BENEFICIARY'],
    new \GuzzleHttp\Client(), true);
```
Este exemplo usa variáveis de ambiente, mas fique à vontade para usar qualquer método de sua preferência para passar os parâmetros.

2. Chame o método authenticate() na instância Cliente para autenticar na API do Sicredi, passando como argumentos seu nome de usuário e senha da API do Sicredi.

3. Obtenha o cliente do recurso Boleto da instância Client chamando o método boleto(). Por exemplo:

```php
$client->authenticate($_ENV['SICREDI_USERNAME'], $_ENV['SICREDI_PASSWORD']);

$boletoClient = $client->boleto();
```

4. Crie uma nova instância de Boleto passando os parâmetros necessários, como informações do beneficiário, informações do beneficiário, valor e data de vencimento. Por exemplo:

```php
$boleto = new \SicrediAPI\Domain\Boleto\Boleto(
    (new Beneficiary(
        'Jose da Silva',
        '86049253099',
        'person'
    )),
    (new Payee(
        'Maria de Lurdes',
        '50581718054',
        'person'
    )),
    100.00,
    'DM',
    12345,
    'RECIBO',
    '999999',
    new DateTime('2023-12-31')
);
```

5. Chame o método create() no cliente de recurso Boleto para criar o Boleto. Por exemplo:

```php
$boletoClient->create($boleto);
```

6. Armazene e/ou faça qualquer outra coisa que você precise fazer com as informações do Boleto retornadas pelo método create().

## Validação e Produção


Antes de utilizar a API Cobrança do Sicredi, siga as etapas sequenciais de validação e produção contidas no manual anexo,

Em resumo, o processo de validação inclui as seguintes etapas:

1. Acesse o Portal do Desenvolvedor
2. Crie um APP para Sandbox
3. Solicite o token da API Sandbox
4. Teste as URLs disponíveis no ambiente de Homologação
5. Crie um APP para Produção
6. Solicite o token da API de produção
7. Teste as URLs disponíveis no ambiente de Produção
8. Implante sua solução

Para criar o APP Sandbox, o desenvolvedor deve acessar o [Portal do Desenvolvedor Sicredi](https://developer.sicredi.com.br/), criar uma conta ou fazer login e criar um novo aplicativo com o prefixo "API Cobrança < Cooperativa> <Código do Beneficiário> Sandbox”.

Após a criação do APP Sandbox, o desenvolvedor deverá solicitar o Sandbox API Token através do menu ‘Suporte’ > ‘Abrir Chamado’ no Portal do Desenvolvedor. Selecione a opção adequada e preencha o nome do APP criado anteriormente. O API Token será gerado dentro de alguns dias e poderá ser encontrado no menu ‘Minhas Apps’, nos detalhes da respectiva aplicação.
The URLs available for testing can be found in the API manual.

Para o processo de produção, repita os mesmos passos de criação do APP, mas com um nome diferente, como “API Cobrança <Cooperativa> <Código do Beneficiário> Produção”. Após criar o APP, solicite o API Token para Produção, seguindo os mesmos passos do Sandbox.

Vale lembrar que, em todas as operações, o token de autenticação recebido deverá ser informado no cabeçalho `x-api-key` da requisição. Este token é diferente para cada ambiente.

Caso tenha alguma dúvida, entre em contato com os canais de suporte listados no manual anexo, ou acesse o [Portal do Desenvolvedor](https://developer.sicredi.com.br/).
