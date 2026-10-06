# Ajustes Financeiros Categorias

Módulo CRUD para a tabela `tab_ajustes_financeiros_categorias`.

## Arquivos

```
_modulos/ajustes_financeiros_categorias/
  ajustes_financeiros_categorias.class.inc.php
  ajustes_financeiros_categorias.adicionar.inc.php
  ajustes_financeiros_categorias.editar.inc.php
  ajustes_financeiros_categorias.ver.inc.php
  ajustes_financeiros_categorias.remover.inc.php
  README.md
```

## Registro do módulo e ações

Use o utilitário `__util/verifica_modulo.php` para inserir em `tab_modulos` e `tab_acoes`.

### Só módulo + ações CRUD

```
http://localhost/intranet/intranet-tv/__util/verifica_modulo.php?modulo=ajustes_financeiros_categorias
```

Produção:

```
https://oletv.oletelecom.com.br/__util/verifica_modulo.php?modulo=ajustes_financeiros_categorias
```

Ações criadas automaticamente:

- `listar`
- `adicionar`
- `editar`
- `ver`
- `remover`

### Com menu

```
http://localhost/intranet/intranet-tv/__util/verifica_modulo.php?modulo=ajustes_financeiros_categorias&menu=sim&menu_nome=Categorias Ajustes Financeiros&menu_pai=1
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
2. Acessar: `/?m=ajustes_financeiros_categorias&a=listar`

## Regras

- **Remover** só para usuário administrador (`usu_administrador`).
- Campos NFS-e (`codigo_servico_id`, `cnae_id`, `aliquota`, `issRetido`, `nbs`) são gravados quando `gerar_nfse = 1`; caso contrário ficam `NULL`.
- A coluna usada no banco é `nbs` (não `nds`).

## Tabela

`tab_ajustes_financeiros_categorias`

| Campo               | Descrição              |
|---------------------|------------------------|
| `afc_id`            | ID (PK)                |
| `afc_nome`          | Nome                   |
| `afc_acrescimo`     | Acréscimo (0/1)        |
| `afc_decrescimo`    | Decréscimo (0/1)       |
| `afc_bonificacao`   | Bonificação (0/1)      |
| `afc_solicitar_nf`  | Solicitar NF (0/1)     |
| `afc_disponivel`    | Disponível (0/1)       |
| `custom`            | Tipo Custom (0/1)      |
| `gerar_nfse`        | Gerar NFS-e (0/1)      |
| `codigo_servico_id` | FK `tab_notas_servicos`|
| `cnae_id`           | FK `tab_notas_cnae`    |
| `aliquota`          | Alíquota               |
| `issRetido`         | ISS retido             |
| `nbs`               | Código NBS             |
