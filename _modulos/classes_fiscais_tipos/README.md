# Classes Fiscais Tipos

Módulo CRUD para a tabela `tab_classes_fiscais_tipos`.

## Arquivos

```
_modulos/classes_fiscais_tipos/
  classes_fiscais_tipos.class.inc.php
  classes_fiscais_tipos.adicionar.inc.php
  classes_fiscais_tipos.editar.inc.php
  classes_fiscais_tipos.ver.inc.php
  classes_fiscais_tipos.remover.inc.php
  README.md
```

## Registro do módulo e ações

Use o utilitário `__util/verifica_modulo.php` para inserir em `tab_modulos` e `tab_acoes`.

### Só módulo + ações CRUD

```
http://localhost/intranet/intranet-tv/__util/verifica_modulo.php?modulo=classes_fiscais_tipos
```

Produção:

```
https://oletv.oletelecom.com.br/__util/verifica_modulo.php?modulo=classes_fiscais_tipos
```

Ações criadas automaticamente:

- `listar`
- `adicionar`
- `editar`
- `ver`
- `remover`

### Com menu

```
http://localhost/intranet/intranet-tv/__util/verifica_modulo.php?modulo=classes_fiscais_tipos&menu=sim&menu_nome=Classes_Fiscais_Tipos&menu_pai=1
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
2. Acessar: `/?m=classes_fiscais_tipos&a=listar`

## Regras

- **Remover** só para usuário administrador (`usu_administrador`).
- `cft_sigla` é única (`UNIQUE KEY`).
- `cft_id` é `AUTO_INCREMENT`.
- `emp_id` referencia `tab_empresas` (opcional).

## Tabela

`tab_classes_fiscais_tipos`

| Campo                    | Descrição                |
|--------------------------|--------------------------|
| `cft_id`                 | ID (PK, AUTO_INCREMENT)  |
| `cft_sigla`              | Sigla (única, 3 chars)   |
| `cft_descricao`          | Descrição                |
| `cft_tipo`               | SCM / SVA                |
| `cft_tributacao`         | Tributação               |
| `cft_icms`               | Alíquota ICMS            |
| `cft_codigo`             | Código                   |
| `cft_natureza_pf_sc`     | Natureza PF SC           |
| `cft_natureza_pj_sc`     | Natureza PJ SC           |
| `cft_natureza_pf_outros` | Natureza PF Outros       |
| `cft_natureza_pj_outros` | Natureza PJ Outros       |
| `emp_id`                 | Empresa (FK opcional)    |
| `cft_gerar_nota`         | Gerar nota (0/1)         |
