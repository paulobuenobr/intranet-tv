# Login NFS-e

Módulo CRUD para a tabela `tab_notas_login` (credenciais usadas na emissão de NFS-e São José).

## Arquivos

```
_modulos/notas_login/
  notas_login.class.inc.php
  notas_login.adicionar.inc.php
  notas_login.editar.inc.php
  notas_login.ver.inc.php
  notas_login.remover.inc.php
  README.md
```

## Tabela

Criar no banco (se ainda não existir):

```sql
CREATE TABLE tab_notas_login (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  login VARCHAR(155) NULL,
  senha VARCHAR(155) NULL,
  status INT NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
);
```

| Campo    | Descrição                         |
|----------|-----------------------------------|
| `id`     | ID (PK)                           |
| `login`  | Usuário do webservice NFS-e       |
| `senha`  | Senha do webservice NFS-e         |
| `status` | 1 = Ativo, 0 = Inativo            |

## Registro do módulo e ações

Use o utilitário `__util/verifica_modulo.php` para inserir em `tab_modulos` e `tab_acoes`.

### Só módulo + ações CRUD

```
http://localhost/intranet/intranet-tv/__util/verifica_modulo.php?modulo=notas_login
```

Produção:

```
https://oletv.oletelecom.com.br/__util/verifica_modulo.php?modulo=notas_login
```

Ações criadas automaticamente:

- `listar`
- `adicionar`
- `editar`
- `ver`
- `remover`

### Com menu

```
http://localhost/intranet/intranet-tv/__util/verifica_modulo.php?modulo=notas_login&menu=sim&menu_nome=Login NFS-e&menu_pai=1
```

Parâmetros:

| Parâmetro   | Descrição                         |
|-------------|-----------------------------------|
| `modulo`    | Nome do módulo (obrigatório)      |
| `menu=sim`  | Cria menu em `tab_menus`          |
| `menu_nome` | Nome exibido no menu              |
| `menu_pai`  | ID do menu pai (`men_id`)         |

## Após registrar

1. Liberar permissões do grupo em `rel_gru_aco_tempermissao` (ou pela tela de grupos).
2. Acessar: `/?m=notas_login&a=listar`

## Regras

- Na **edição**, senha em branco mantém a senha atual.
- Ao gravar com `status = 1`, os demais registros são desativados (`status = 0`), pois `NotaSJ` usa `WHERE status = 1 LIMIT 1`.
- A senha não é exibida em texto na listagem nem na visualização.
- Consumido em `_modulos/nfse/sjsc/NotaSJ.php` para autenticar no webservice da NFS-e.
