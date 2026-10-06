<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

require_once '../config/config_api.php';

session_start();

$myIP = '192.168.125.71';//'127.0.0.1   192.168.125.71';

if ( $myIP != '' && $_SERVER['REMOTE_ADDR'] == $myIP ) {
    //$formPostDefault = array('keyapi'=>'738341c5d2c46520ed6fca5670bc6733092b712c','login'=>'ole','pass'=>'oletelecom');
    $formPostDefault = array('keyapi'=>'2b70b7c75f0c77f711a93dcc97b93014012b5c04','login'=>'mdconnect','pass'=>'b0050a95ca99289bd51b');
}

$data_atualizacao = '17/05/2023';
$historico_atualizacao = array(
    array('15/10/2021','Alterações em <b>Inserir um Novo Contrato</b> - '.$_SERVER['REMOTE_ADDR'],
        array(
            'No plano Mobile, os parâmetros id_modelo[] e mac[] não deverão ser informados (Caso o faça, serão simplesmente ignoradas)',
            'Inserido o parâmetro email_usuario, que será obrigatório quando for plano Mobile'
        )
    ),
    array('15/10/2021','Alterações resultado <b>Listagem de Contrato do Cliente</b>',
        array(
            'Adicionado no retorno o parametro box, que se refere ao número de BOX que o plano permite, 0 caracteriza como Mobile.'
        )
    ),
    array('15/10/2021','Alteração em <b>Visualizar Termo de Adesão do Contrato</b>',
        array(
            'Liberado a visualização de contratos ainda não ativados.'
        )
    ),
    array(
        '26/10/2021','<b>Envio de Documento</b> ao contrato',
        array(
            'Envio do documento PDF, referênte do contrato assinado.'           
        )
    ),
    array(
        '08/11/2021','Validações em <b>Inserir um Novo Contrato</b>',
        array(
            'Ajustes nas validações dos dados cadastrais (id_plano_principal e id_plano_adicional), parando a execução caso algo não esteja de acordo. Além de reparos de certos erros internos.'
        )
    ),
    array(
        '14/12/2021','Listagem dos Boletos por Contrato',
        array(
            'Adicionado a busca de boletos por um contrato.'
        )
    ),
    array(
        '31/05/2022','Inserir Contrato',
        array(
            'Adicionado o parâmetro "id_contrato_origem", para o cliente informar o ID do contrato gerado pelo ERP de origem. Sendo obrigado apenas para algumas plataformas ERP.'
        )
    ),
    array(
        '01/08/2022','Bloqueio/Desbloqueio de Contrato',
        array(
            'Bloqueio: Agora poderá ser inserido o <b>Bloqueio por Inadimplência</b> ou <b>Bloqueio a Pedido do Cliente</b> a um contrato através do parametro <i>motivo_suspensao</i>',
            'Desbloqueio: Poderá ser desbloqueado os dois tipos (<b>Bloqueio por Inadimplência</b> ou <b>Bloqueio a Pedido do Cliente</b>)'
        )
    ),
    array(
        '22/08/2022','Codificação de Caracteres',
        array(
            'Validação da padronização da comunicação para trabalhar sempre com a codificação UTF-8 dos dados enviados'
        )
    ),
    array(
        '23/03/2023','Bloqueio de contrato',
        array(
            'Adicionado ao efetuar um bloqueio com sucesso, o retorno do ID do bloqueio gerado (<b>id_bloqueio</b>).'
        )
    ),
    array(
        '04/04/2023','Ajustes nos planos',
        array(
            'Ajustado a listem de planos, para retornar somente os planos que o parceiro pode comercializar.',
            'Ajustado a inserção de um contrato, para validar somente os planos que o parceiro pode comercializar'
        )
    )
    ,
    array(
        '06/04/2023','Implementação',
        array(
            'Adicionado o método <b>pontosregistrados</b> para obter todos os pontos registrados do cliente, inclusive com seu status (Online/Offline)'
        )
    ),
    array(
        '17/05/2023','Ajustes - Substituir Modelo/Equipamento',
        array(
            'Na substituição de equipamento, agora é possivel também substituir por um sem MAC, desde que o contrato já tenha email_usuario definido. Ou seja, o contrato é liberado por login/senha.'
        )
    ),
    array(
        '02/10/2025','Ajustes - contratos/alterarusuario',
        array(
            'Inserido novo parâmetro "email_antigo", para buscar dados além do número do contrato.'
        )
    ),
    array(
        '28/08/2026','Ajustes - contratos/recuperarsenha',
        array(
            'Inserido novo método "recuperarsenha", para recuperar a senha de um usuário.'
        )
    ),
    array(
        '28/08/2026','Ajustes - clientes/inserir',
        array(
            'Alterado o novo parâmetro "email[]", tornando-o obrigatório, sendo que é obrigatório informar pelo menos um e-mail de contato.'
        )
    )
    
);

$categorias = array();

if ($_SESSION["administrador"]) {
	$categorias[] = array(
		'nome'=>'Acesso restrito',
		'classe'=>array(
			array(
				'url'=>'contratos/alterarsenha',
				'title'=>'Método para alterar senha de um contrato',
				'get'=>array(
					array('id_contrato','inteiro','ID do Contrato')
				),
				'form'=>array(
					array('email_usuario','string','E-mail válido do usuário (Obrigatório)'),
					array('senha','string','Senha para acesso (Obrigatório, minino 6 caracteres)')
				),
			),
		)
	);
}

$categorias[] = array(
	'nome'=>'Clientes',
	'classe'=>array(
		array(
			'url'=>'clientes/listar',
			'title'=>'Listagem de Clientes (Resumida)',
			'form'=>array()
		),
		array(
			'url'=>'clientes/buscacpfcnpj',
			'get'=>array(
				array('cpf_cnpj','string','CPF/CNPJ (Formato: 999.999.999-99)')
			),
			'title'=>'Buscar Lista por CPF/CNPJ',
			'form'=>array()
		),
		array(
			'url'=>'clientes/buscadados',
			'get'=>array(
				array('id_cliente','Inteiro','ID do Cliente')
			),
			'title'=>'Buscar Dados Completo do Cliente',
			'form'=>array()
		),
		array(
			'url'=>'clientes/inserir',
			'title'=>'Inserir Novo Cliente',
			'form'=>array(
				array('nome','string','Nome do cliente - Não pode conter números ou caracteres especiais e precisar conter duas palavras (Obrigatório)'),
				array('tipo_pessoa','string (1)','Tipo de Pessoa - (Obrigatório)', TIPOS_CLIENTE),
				array('nome_fantasia','string',' Nome fantasia - (Obrigatório p/Pessoa Júridica)'),
				array('cpf_cnpj','string (14 ou 19)','CPF: 999.999.999-99 ou CNPJ: 99.999.999/9999-99 - (Obrigatório)'),
				array('inscricao_estadual','string','Inscrição Estadual - (Opcional)'),
				array('data_nascimento','string (10)','Data Nascimento: dd/mm/aaaa - (Obrigatório p/Pessoa Física)'),
				array('endereco_cep','string (9)','CEP: 99999-999 - (Obrigatório)'),
				array('endereco_logradouro','string','Rua, avenida, travessa... - (Obrigatório, apenas para cidades com CEP único)'),
				array('endereco_numero','string','Número do endereço - (Opcional)'),
				array('endereco_bairro','string','Nome do bairro - (Obrigatório, apenas para cidades com CEP único)'),
				array('endereco_ponto_referencia','string','Ponto de referência do endereço - (Opcional)'),
				array('endereco_complemento_tipo','string (1)','Tipo do complemento - (Opcional)', TIPOS_COMPLEMENTO),
				array('endereco_complemento_valor','string','Detalhe do complemento - (Opcional)'),
				array('contato','string','Nome do contato - (Opcional)'),
				array('telefone_ddd[]','int (2)','DDD do telefone: 99 - (Opcional)'),
				array('telefone_numero[]','string (10)','Número do telefone: 99999-9999 - (Opcional)'),
				array('telefone_ramal[]','string','Ramal do telefone - (Opcional)'),
				array('telefone_tipo[]','int (1)','Tipo do telefone - (Opcional)', TIPOS_TELEFONE),
				array('email[]','string','E-mail de contato - (Obrigatório)'),
				array('dia_vencimento','int (2)','Dia vencimento do boleto - (Obrigatório)', DIAS_VENCIMENTO),
				array('endereco_cobranca','boolean','Endereço p/cobrança - (Obrigatório)', array('true'=>'Mesmo endereço','false'=>'Endereço diferente')),
				array('cobranca_cep','string (9)','CEP para cobrança: 99999-999 - (Obrigatório p/endereco_cobranca = false)'),
				array('cobranca_logradouro','string','Rua, avenida, travessa... p/cobrança - (Obrigatório p/endereco_cobranca = false)'),
				array('cobranca_numero','string','Número do endereço p/cobrança - (Obrigatório p/endereco_cobranca = false)'),
				array('cobranca_bairro','string','Nome do bairro p/cobrança - (Obrigatório p/endereco_cobranca = false)'),
				array('cobranca_complemento_tipo','string (1)','Tipo do complemento p/cobrança - ', TIPOS_COMPLEMENTO),
				array('cobranca_complemento_valor','string','Detalhe do complemento p/cobrança'),
				array('contato','string','Pelo menos um contato deve ser informado, seja telefone ou e-mail - (Obrigatório)'),
			),
			'obs'=>'Os dados do endereço de cobrança (cobranca_cep, cobranca_logradouro, cobranca_bairro, cobranca_numero, cobranca_complemento_tipo e cobranca_complemento_valor) só precisa ser informado, caso o parâmetro "endereco_cobranca" for informado como "false" (Endereço diferente).<br><br>'.
				   'Os parametros de telefone e e-mail, do qual possui as "[]", podem ser repetidos a quantidade que forem necessárias, ou seja, o cliente pode ter mais de um telefone/email para contato.'
		),
		array(
			'url'=>'clientes/alterar',
			'get'=>array(
				array('id_cliente','Inteiro','ID do cliente')
			),
			'title'=>'Alterar Cliente',
			'form'=>array(
				array('nome','string','Nome do cliente - Não pode conter números ou caracteres especiais e precisar conter duas palavras (Obrigatório)'),
				array('tipo_pessoa','string (1)','Tipo de Pessoa - (Obrigatório)', TIPOS_CLIENTE),
				array('nome_fantasia','string',' Nome fantasia - (Obrigatório p/Pessoa Júridica)'),
				array('cpf_cnpj','string (14 ou 19)','CPF: 999.999.999-99 ou CNPJ: 99.999.999/9999-99 - (Obrigatório)'),
				array('inscricao_estadual','string','Inscrição Estadual - (Opcional)'),
				array('data_nascimento','string (10)','Data Nascimento: dd/mm/aaaa - (Obrigatório p/Pessoa Física)'),
				array('endereco_cep','string (9)','CEP: 99999-999 - (Obrigatório)'),
				array('endereco_logradouro','string','Rua, avenida, travessa... - (Obrigatório, apenas para cidades com CEP único)'),
				array('endereco_numero','string','Número do endereço - (Opcional)'),
				array('endereco_bairro','string','Nome do bairro - (Obrigatório, apenas para cidades com CEP único)'),
				array('endereco_ponto_referencia','string','Ponto de referência do endereço - (Opcional)'),
				array('endereco_complemento_tipo','string (1)','Tipo do complemento - (Opcional)', TIPOS_COMPLEMENTO),
				array('endereco_complemento_valor','string','Detalhe do complemento - (Opcional)'),
				array('contato','string','Nome do contato - (Opcional)'),
				array('telefone_ddd[]','int (2)','DDD do telefone: 99 - (Opcional)'),
				array('telefone_numero[]','string (10)','Número do telefone: 99999-9999 - (Opcional)'),
				array('telefone_ramal[]','string','Ramal do telefone - (Opcional)'),
				array('telefone_tipo[]','int (1)','Tipo do telefone - (Opcional)', TIPOS_TELEFONE),
				array('email[]','string','E-mail de contato - (Opcional)'),
				array('dia_vencimento','int (2)','Dia vencimento do boleto - (Obrigatório)', DIAS_VENCIMENTO),
				array('endereco_cobranca','boolean','Endereço p/cobrança - (Obrigatório)', array('true'=>'Mesmo endereço','false'=>'Endereço diferente')),
				array('cobranca_cep','string (9)','CEP para cobrança: 99999-999 - (Obrigatório p/endereco_cobranca = false)'),
				array('cobranca_logradouro','string','Rua, avenida, travessa... p/cobrança - (Obrigatório p/endereco_cobranca = false)'),
				array('cobranca_numero','string','Número do endereço p/cobrança - (Obrigatório p/endereco_cobranca = false)'),
				array('cobranca_bairro','string','Nome do bairro p/cobrança - (Obrigatório p/endereco_cobranca = false)'),
				array('cobranca_complemento_tipo','string (1)','Tipo do complemento p/cobrança - ', TIPOS_COMPLEMENTO),
				array('cobranca_complemento_valor','string','Detalhe do complemento p/cobrança'),
				array('contato','string','Pelo menos um contato deve ser informado, seja telefone ou e-mail - (Obrigatório)'),
			),
			'obs'=>'Os dados do endereço de cobrança (cobranca_cep, cobranca_logradouro, cobranca_bairro, cobranca_numero, cobranca_complemento_tipo e cobranca_complemento_valor) só precisa ser informado, caso o parâmetro "endereco_cobranca" for informado como "false" (Endereço diferente)<br><br>'.
			'Os parametros de telefone e e-mail, do qual possui as "[]", podem ser repetidos a quantidade que forem necessárias, ou seja, o cliente pode ter mais de um telefone/email para contato'
		)
	),
);

$categorias[] = array(
	'nome'=>'Boletos',
	'classe'=>array(
		array(
			'url'=>'boletos/listar',
			'get'=>array(
				array('id_cliente','inteiro','ID do cliente desejado')
			),
			'title'=>'Listagem dos Boletos por Cliente',
			'form'=>array()
		),
		
		array(
			'url'=>'boletos/buscacpfcnpj',
			'get'=>array(
				array('cpf_cnpj','string','CPF/CNPJ (Formato: 999.999.999-99)'),
				array('status','string','Status do Boleto (Aberto ou Pago) <br>Vazio para todos')
			),
			'title'=>'Listagem dos Boletos por CPF/CNPJ',
			'form'=>array()
		),
		
		array(
			'url'=>'boletos/buscacontrato',
			'get'=>array(
				array('id_contrato','inteiro','ID do contrato de um cliente')
			),
			'title'=>'Listagem dos Boletos por Contrato',
			'form'=>array()
		),
		
		array(
			'url'=>'boletos/visualizar',
			'get'=>array(
				array('id_boleto','inteiro','ID do boleto')
			),
			'title'=>'Visualização de um Boleto',
			'form'=>array(),
			'obs'=>'Em caso de sucesso (retorno_status=true), o parametro conteúdo retornado na requisição, é uma string Base 64 do PDF'
		),
		array(
			'url'=>'boletos/baixa',
			'get'=>array(
				array('id_boleto','inteiro','ID do boleto')
			),
			'title'=>'Baixa (quitar) um Boleto do Cliente',
			'form'=>array(
				array('data_pagamento','data BR','Data do pagamento: dd/mm/aaaa - (Obrigatório)'),
				array('valor_pago','moeda BR','Total recebido do boleto (valor + juros): Formato: 9.999,90 - (Obrigatório) '),
				array('comentario','texto','Comentário que deseja vincular ao pagamento - (Opcional)'),
			)
		)
	),
);

$categorias[] = array(
	'nome'=>'Contratos/Assinatura',
	'classe'=>array(
		array(
			'url'=>'planos',
			'title'=>'Listagem de Planos',
			'form'=>array()
		),
		array(
			'url'=>'equipamentos',
			'title'=>'Listagem de Modelos/Equipamento',
			'form'=>array()
		),
		array(
			'url'=>'contratos/listar',
			'get'=>array(
				array('id_cliente','inteiro','ID do cliente')
			),
			'title'=>'Listagem de Contratos do Cliente',
			'form'=>array()
		),
		array(
			'url'=>'contratos/alterarusuario',
			'get'=>array(
				array('id_contrato','inteiro','ID do contrato')
			),
			'title'=>'Alterar/Criar um usuário/senha',
			'form'=>array(
				array('email_usuario','texto','E-mail válido do usuário (Obrigatório)'),
				array('email_antigo','texto','E-mail antigo do usuário (Não obrigatório)')
			)
		),
		array(
			'url'=>'contratos/recuperarsenha',
			'get'=>array(
				array('id_contrato','inteiro','ID do contrato')
			),
			'title'=>'Recuperar senha do usuário',
			'form'=>array(
				array('email_usuario','texto','E-mail válido do usuário (Obrigatório)')
			)
		),
		array(
			'url'=>'contratos/inserir',
			'title'=>'Inserir um Novo Contrato',
			'form'=>array(
				array('id_cliente','inteiro','ID do Cliente (Obrigatório)'),
				array('id_contrato_origem', 'string', 'ID do contrato no ERP externo de origem (Obrigatório para alguns ERP de origem)'),
				array('id_plano_principal','inteiro','ID do plano principal: disponível pelo /planos tipo=Principal (Obrigatório)'),
				array('id_modelo[]','inteiro','ID modelo do equipamento: disponível pelo /equipamentos (Obrigatório)'),
				array('mac[]','string','Endereço MAC do equipamento (Obrigatório)'),
				array('id_plano_adicional[]','inteiro','ID do plano adicional: disponível pelo /planos tipo=Adicional (Opcional)'),
				array('id_modelo[]','inteiro','ID modelo do equipamento: disponível pelo /equipamentos (Obrigatório quando não for plano Mobile)'),
				array('mac[]','string','Endereço MAC do equipamento. Ex: XXXXXXXXXXXX (Obrigatório quando não for plano Mobile)'),
				array('email_usuario','string','E-mail que será usado para login no mobile (obrigatório quando plano Mobile)')
				
			),
			'obs'=>'Os campos com colchetes são arrays, ou seja, podem se repetir.<br><br>O primeiro valor dos parâmetros id_modelo[] e mac[] equivale aos dados do equipamento do id_plano_principal. (Se não for plano Mobile).<br><br>'.
			'O id_plano_adicional[], refere ao adicionais do Plano Principal (id_plano_principal), que poderá ser:<br>'.
			'- Um Pacote Especiais (Á la carte, ex: HBO,  Sex Privé), nesses casos não possuem id_modelo e mac vinculado;<br>'.
			'- Um Ponto Adicional (quando não for plano Mobile), que nesses casos, deverá ser informado os dados do equipamento (id_modelo e mac).<br><br>'.

			'No caso de plano Mobile:<br>'.
			'- Deverá ser informado apenas o id_plano_principal sem os parametros id_modelo[] e mac[];<br>'.
			'- Poderá ter um Pacote Especiais (Á la carte, ex: HBO,  Sex Privé), nesses casos não possuem id_modelo e mac vinculado;<br>'.
			'- Necessita informar o email_usuario (será enviado para o e-mail as instruções para gerar a senha).<br><br>'.
			
			'O email_usuario será Obrigatório quando o Plano Principal for plano Mobile, caso contrário será opcional.'
			
		),
		array(
			'url'=>'contratos/enviardocumentacao',
			'title'=>'Enviar documento assinado',
			'get'=>array(
				array('id_cliente','inteiro','ID do Cliente'),
				array('id_contrato','inteiro','ID do Contrato (Válido e Ativo)'),
			),
			'form'=>array(
				array('nome','string','Nome do arquivo PDF'),
				array('conteudo','string','conteúdo do arquivo PDF em Base64'),
				
			),
			'obs'=>''
		),
		array(
			'url'=>'contratos/listarbloqueios',
			'get'=>array(
				array('id_contrato','inteiro','ID do Contrato'),
				array('ativos','boolean','Listar apenas bloqueios ativos (true ou false)')
			),
			'title'=>'Listagem de Bloqueios em Contrato do Cliente'
		),
		array(
			'url'=>'contratos/bloqueio',
			'get'=>array(
				array('id_contrato','inteiro','ID do Contrato')
			),
			'form'=>array(
				array('data_encerramento','String (10)','Data para encerramento do bloqueio (dd/mm/aaaa) - opcional'),
				array('motivo_suspensao', 'inteiro', 'Id do motivo da suspenção', [1=>'Bloqueio por Inadimplência', 2=>'Bloqueio a Pedido do Cliente'])
			),
			'title'=>'Adicionar Bloqueio a um Contrato do Cliente',
			//'obs'=>'O prazo máximo de duração desse bloqueio é limitado a 1 (um) mês'
		),
		array(
			'url'=>'contratos/desbloqueio',
			'get'=>array(
				array('id_contrato','inteiro','ID do Contrato'),
				array('id_bloqueio','inteiro','ID do Bloqueio')
			),
			'title'=>'Desbloqueio de um Contrato do Cliente',
			'obs'=>'Somente é permitido o cancelamento de bloqueio dos tipos "<b>Bloqueio por Inadimplência</b>" ou "<b>Bloqueio a Pedido do Cliente</b>".<br>'
		),
		array(
			'url'=>'contratos/pontosregistrados',
			'get'=>array(
				array('id_contrato','inteiro','ID do Contrato')
			),
			'title'=>'Pontos registrados de um contrato',
			'obs'=>'Através desse método, é possivel ver todos os pontos do cliente, inclusive se esta Online ou Offline'
		),
		array(
			'url'=>'contratos/mac',
			'get'=>array(
				array('id_contrato','inteiro','ID do contrato'),
				array('id_registro_equipamento','inteiro','ID do equipamento a ser substituído')
			),
			'title'=>'Substituir Modelo/Equipamento (Alterar MAC)',
			'form'=>array(
				array('id_modelo','inteiro','ID modelo do equipamento: disponível pelo /equipamentos (Obrigatório)'),
				array('mac','string','Endereço MAC do equipamento (Obrigatório)')
			)
		),
		array(
			'url'=>'contratos/termo',
			'get'=>array(
				array('id_contrato','inteiro','ID do contrato')
			),
			'title'=>'Visualizar Termo de Adesão do Contrato',
			'form'=>array(),
			'obs'=>'Em caso de sucesso (retorno_status=true), o parametro conteudo retornando na requisição, é uma string Base 64 do PDF'
		),
		array(
			'url'=>'contratos/cancelar',
			'get'=>array(
				array('id_contrato','inteiro','ID do contrato (Obrigatório)')
			),
			'title'=>'Cancelar Contrato',
			'form'=>array()
		),
		
	),
);


/*

if ( $myIP != '' && $_SERVER['REMOTE_ADDR'] == $myIP ) {
    
    array_push($categorias,
        array(
         'nome'=>'Ficara oculto',
         'classe'=>array(
         array(
         'url'=>'parceiro/autenticacao',
         'title'=>'Valida autenticação do Parceiro',
         'form'=>array()
         ),
         array(
         'url'=>'parceiro/verificacaocidade',
         'title'=>'Verifica a autorização com base no CEP da cidade para o Parceiro',
         'form'=>array(
         array('cep','string','CEP (Formato: 99999-999)'),
         array('logradoudo','string',''),
         array('bairro','string',''),
         //array('cidade','string',''),
         //array('uf','string','UF (Sigla)'),
         ),
         ),
         array(
         'url'=>'parceiro/verificaCidadeParceiro',
         'title'=>'Verifica a autorização do Parceiro para inserir um CEP',
         'form'=>array(
         array('cep','string','CEP (Formato: 99999-999)'),
         ),
         ),
             
         array(
         'url'=>'parceiro/emailcontratoativo',
         'title'=>'Verifica se o e-mail já está em um contrato ativo',
         'form'=>array(
         array('email','string',''),
         ),
         )
             
         )
            
         )
    );
    
    
}
*/

// Montagem do HTML com base da array "$categorias" acima

$temp = file_get_contents('html/index.html');
$temp = str_replace('{data_atualizacao}', $data_atualizacao, $temp);

$historico = '';
foreach ($historico_atualizacao AS $dados) {
    $historico .= '<li><b>'.$dados[0].'</b> - '.$dados[1];
    if (isset($dados[2])) {
        $historico .= '<ul style="font-size: 0.9rem;">';
        foreach ($dados[2] AS $detalhe) {
            $historico .= '<li>'.$detalhe.'</li>';
        }
        $historico .= '</ul>';
    }
    $historico .= '</li>';
}

$temp = str_replace('{historico}', $historico, $temp);



$txtcateg = '';

foreach ($categorias AS $categ) {
    $tmpcateg = file_get_contents('html/categorias.html');
    $tmpcateg = str_replace('{nmCategoria}', $categ['nome'], $tmpcateg);
    
    $txtacoes = '';
    foreach ($categ['classe'] AS $acao) {
        $tmpacoes = file_get_contents('html/acoes.html');
        $tmpacoes = str_replace('{titulo}', $acao['title'], $tmpacoes);
        $tmpacoes = str_replace('{apihost}', API_HOST, $tmpacoes);
        $tmpacoes = str_replace('{url}', $acao['url'], $tmpacoes);
        $tmpacoes = str_replace('{nmurl}', str_replace('/','_', $acao['url']), $tmpacoes);
        
        // Verifica parametros GET
        if (isset($acao['get'])) {
            $parametroGet  = '<p class="tituloparametro">parâmetos via GET (url)</p>';
            $parametroGet .= '<ul>';
            $urlget = '';
            $fieldget = '';
            foreach ($acao['get'] AS $vget) {
                $parametroGet .= '<li>'.$vget[0].': <strong>'.$vget[1].'</strong> - <em>'.$vget[2].'</em>.</li>';
                $urlget .= '/{'.$vget[0].'}';
                if ($fieldget != '') {
                    $fieldget .= '<div class="input-group-prepend">';
                    $fieldget .= '  <span class="input-group-text" id="basic-addon1">/</span>';
                    $fieldget .= '</div>';
                }
                $fieldget .= '<input type="text" class="form-control fieldget'.str_replace('/','_', $acao['url']).'" placeholder="'.$vget[2].'" aria-label="'.$vget[2].'" aria-describedby="basic-addon1">';
            }
            $parametroGet .= '</ul>';
            $tmpacoes = str_replace('{isget}', '/', $tmpacoes);
            $tmpacoes = str_replace('{urlget}', $urlget, $tmpacoes);
            $tmpacoes = str_replace('{fieldget}', $fieldget, $tmpacoes);
            $tmpacoes = str_replace('{parametroget}', $parametroGet, $tmpacoes);
        }
        else {
            $tmpacoes = str_replace('{isget}', '', $tmpacoes);
            $tmpacoes = str_replace('{urlget}', '', $tmpacoes);
            $tmpacoes = str_replace('{fieldget}', '', $tmpacoes);
            $tmpacoes = str_replace('{parametroget}', '', $tmpacoes);
        }
        
        if (isset($acao['obs'])) {
            $tmpacoes = str_replace('{obs}', '<p class="tituloparametro"><b>Observações</b></p><p><em>'.$acao['obs'].'</em></p>', $tmpacoes);
        }
        else {
            $tmpacoes = str_replace('{obs}', '', $tmpacoes);
        }
        
        // Verifica parametros POST
        if (isset($acao['form'])) {
            $fieldpost = '';
            $infpost = '';
            foreach ($acao['form'] AS $vpost) {
                $infpost .= '<li>'.$vpost[0].': <strong>'.$vpost[1].'</strong> - <em>'.$vpost[2];
                if (isset($vpost[3])) {
                    $infpost .= '<br><ul>';
                    foreach ($vpost[3] as $koption => $voption) {
                        $infpost .= '<li>'.$koption.' = '.$voption.'</li>';
                    }
                    $infpost .= '</ul>';
                }
                $infpost .= '</em></li>';
                
                $fieldpost .= '<div class="form-group row">';
                $fieldpost .= ' <label for="login" class="col-sm-3 col-form-label col-form-label-sm">'.$vpost[0].'</label>';
                $fieldpost .= ' <div class="col-sm-9">';
                if (isset($vpost[3])) {
                    $fieldpost .= '  <select name="'.$vpost[0].'" id="'.$vpost[0].'" class="form-control form-control-sm">';
                    $fieldpost .= '    <option value="">'.$vpost[2].'</option>';
                    foreach ($vpost[3] as $koption => $voption) {
                        $fieldpost .= '    <option value="'.$koption.'">'.$voption.'</option>';
                    }
                    $fieldpost .= '  </select>';
                }
                else {
                    $fieldpost .= '  <input type="text" class="form-control form-control-sm" id="'.$vpost[0].'" name="'.$vpost[0].'" value="" placeholder="'.$vpost[2].'">';
                }
                $fieldpost .= ' </div>';
                $fieldpost .= '</div>';
            }
            $tmpacoes = str_replace('{infposts}', $infpost, $tmpacoes);
            $tmpacoes = str_replace('{fieldpost}', '<hr>'.$fieldpost, $tmpacoes);
        }
        else {
            $tmpacoes = str_replace('{infposts}', '', $tmpacoes);
            $tmpacoes = str_replace('{fieldpost}', '', $tmpacoes);
        }
        
        
        $tmpacoes = str_replace('{examplecode}', @file_get_contents('modelcodes/'.str_replace('/','_', $acao['url'])), $tmpacoes);
        
        // set dafault dados
        $tmpacoes = str_replace('{keyapi}', @$formPostDefault['keyapi'], $tmpacoes);
        $tmpacoes = str_replace('{login}', @$formPostDefault['login'], $tmpacoes);
        $tmpacoes = str_replace('{pass}', @$formPostDefault['pass'], $tmpacoes);
        
        
        $txtacoes .= $tmpacoes;
    }
    $tmpcateg = str_replace('{acoes}', $txtacoes, $tmpcateg);
    
    
    $txtcateg .= $tmpcateg;
}

$temp = str_replace('{categorias}', $txtcateg, $temp);




echo $temp;