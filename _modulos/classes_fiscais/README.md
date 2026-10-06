# Classes Fiscais

Módulo CRUD para a tabela `tab_classes_fiscais`.

## Arquivos

```
_modulos/classes_fiscais/
  classes_fiscais.class.inc.php
  classes_fiscais.adicionar.inc.php
  classes_fiscais.editar.inc.php
  classes_fiscais.ver.inc.php
  classes_fiscais.remover.inc.php
  README.md
```

## Registro do módulo e ações

Use o utilitário `__util/verifica_modulo.php` para inserir em `tab_modulos` e `tab_acoes`.

### Só módulo + ações CRUD

```
http://localhost/intranet/intranet-tv/__util/verifica_modulo.php?modulo=classes_fiscais
```

Produção:

```
https://oletv.oletelecom.com.br/__util/verifica_modulo.php?modulo=classes_fiscais
```

Ações criadas automaticamente:

- `listar`
- `adicionar`
- `editar`
- `ver`
- `remover`

### Com menu

```
http://localhost/intranet/intranet-tv/__util/verifica_modulo.php?modulo=classes_fiscais&menu=sim&menu_nome=Classes Fiscais&menu_pai=1
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
2. Acessar: `/?m=classes_fiscais&a=listar`

## Regras

- **Remover** só para usuário administrador (`usu_administrador`).
- `clf_id` é `AUTO_INCREMENT`.
- `cft_id` é obrigatório (FK para `tab_classes_fiscais_tipos`).
- `pla_id`, `pls_id` e `afc_id` são opcionais.

## Tabela

`tab_classes_fiscais`

| Campo    | Descrição                                      |
|----------|------------------------------------------------|
| `clf_id` | ID (PK, AUTO_INCREMENT)                        |
| `clf_taxa` | Taxa                                         |
| `pla_id` | Plano (`tab_planos`)                           |
| `pls_id` | Serviço (`tab_planos_servicos`)                |
| `afc_id` | Categoria ajuste (`tab_ajustes_financeiros_categorias`) |
| `cft_id` | Tipo fiscal (`tab_classes_fiscais_tipos`)      |
