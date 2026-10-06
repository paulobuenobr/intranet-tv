#!/bin/bash
#
# Edi7iWebCli.sh
#
# Script de configuracao para inclusao de uma conta (apenas)
# em sistemas padrao POSIX via console
# 
# Versao inicial: Rodrigo F. Valentim
# Data: 19/02/2015
# 
# Obs.:	Script desenvolvido para ter compatibilidade com BASH 3
# 	e superiores, visando assim compatibilidade com todos os
#	sistemas do Padrao POSIX (HP-UX, Linux, Mac OS, SOLARIS...)

echo "Iniciando a configuracao do Edi7WebCli"

check_config_file_exist() {
	if ! [ -f $CONFIG_FILE ] ; then
	   echo "File $FILE does not exists"
		echo >&2 "ERRO: Instalacao incompleta, faltando arquivo: $CONFIG_FILE";
		exit 1;
	fi
}

is_in_system() {
	if ! command -v $1 > /dev/null ; then {
		echo >&2 "ERRO: Necessario o comando $1, mas ele nao esta instalado, cancelando a configuracao.";
		exit 1;
	} fi
}

is_in_system 'basename'
is_in_system 'chmod'
is_in_system 'dirname'
is_in_system 'printf'
is_in_system 'pwd'
is_in_system 'sed'

LOCAL_DIR=`pwd`
BASE_DIR=`basename $LOCAL_DIR`
MSG_TIMEOUT="ERRO: Tempo ocioso durante a configuracao, tente novamente"

if [ "$BASE_DIR" == "bin" ] || [ "$BASE_DIR" == "cfg" ] || [ "$BASE_DIR" == "log" ] ; then
	APP_DIR=`dirname $LOCAL_DIR`
else
	APP_DIR=$LOCAL_DIR
fi

CONFIG_DIR="$APP_DIR/cfg"
CONFIG_FILE="$CONFIG_DIR/webcli.xml"
CONFIG_REF="$CONFIG_DIR/setup.ref"

if ! [[ -d "$CONFIG_DIR" ]] ; then
	echo >&2 "ERRO: Instalacao incompleta, faltando o diretorio: $CONFIG_DIR.";
	exit 1;
fi

if ! [[ -w "$CONFIG_DIR" ]] ; then
	echo >&2 "ERRO: Usuario sem permissao de escrita no diretorio: $CONFIG_DIR.";
	exit 1;
fi

if [[ -a "$CONFIG_FILE" ]] ; then
	while true ; do
		if ! read -r -t 30 -p "AVISO: Arquivo de configuracao ja existe, deseja remover o arquivo atual e realizar nova configuracao? [s/N] " response ; then
			echo $'\n'$MSG_TIMEOUT
			exit 1;
		fi

		if [ "$response" == "n" ] || [ "$response" == "N" ] || [ -z "$response" ] ; then
			echo "Configuracao cancelada"
			exit 1;
		elif [ "$response" == "s" ] || [ "$response" == "S" ] ; then
			break;
		fi
	done
fi

cliente=""
produto=""
tipo=""
senha=""
proxyServidor=""
proxyPorta=""
proxyUsuario=""
proxySenha=""
dirUpload=""
dirDownload=""
dirEnviados=""
operacao=""
modo=""
especial=""

echo "*** Entre com os dados conforme solicitado ***"

while [ -z "$cliente" ] ; do
	if ! read -r -t 30 -p "Cliente: " cliente ; then
		echo $'\n'$MSG_TIMEOUT
		exit 1;
	fi

	if [ ${#cliente} != 7 ] ; then
		echo $'\nCliente deve conter 7 caracteres'
		cliente=""
	fi
done

while [ -z "$produto" ] ; do
	if ! read -r -t 30 -p "Produto: " produto ; then
		echo $'\n'$MSG_TIMEOUT
		exit 1;
	fi

	if [ ${#produto} != 3 ] ; then
		echo $'\nProduto deve conter 3 caracteres'
		produto=""
	fi
done

while [ -z "$tipo" ] ; do
	if ! read -r -t 30 -p "Tipo (Teste/Producao): [t/P] " tipo ; then
		echo $'\n'$MSG_TIMEOUT
		exit 1;
	fi

	if [ -z "$tipo" ] ; then
		tipo="P";
	elif [ "$tipo" != "t" ] && [ "$tipo" != "T" ] && [ "$tipo" != "p" ] && [ "$tipo" != "P" ] ; then
		tipo=""
	fi
done

while [ -z "$senha" ] ; do
	printf "Senha: "
	while IFS= read -p "$prompt" -r -s -n 1 char
	do
		if [[ $char == $'\0' ]] ; then
			if [ -z "$senha" ] ; then
				printf  $'\n'
			fi

			break
		fi

		prompt='*'
		senha="${senha}${char}"
		char=""
	done
done

response=""
echo ""
while [ -z "$response" ] ; do
	if ! read -r -t 30 -p "Configurar servidor proxy? [s/N] " response ; then
		echo $'\n'$MSG_TIMEOUT
		exit 1;
	fi

	if [ "$response" == "n" ] || [ "$response" == "N" ] || [ -z "$response" ] ; then
		response="N";
	elif [ "$response" == "s" ] || [ "$response" == "S" ] ; then
		response="S"
	fi
done

if [ "$response" == "S" ] ; then
	while [ -z "$proxyServidor" ] ; do
		if ! read -r -t 30 -p "Endereco do servidor proxy: " proxyServidor ; then
			echo $'\n'$MSG_TIMEOUT
			exit 1;
		fi
	done

	while [ -z "$proxyPorta" ] ; do
		if ! read -r -t 30 -p "Porta do servidor proxy: " proxyPorta ; then
			echo $'\n'$MSG_TIMEOUT
			exit 1;
		fi

		if [ -z "${proxyPorta##*[!0-9]*}" ] ; then
				echo $'\nPorta do servidor proxy deve ser numerico'
				proxyPorta=""
		fi

		if [ ${#proxyPorta} -ge 6 ] ; then
			echo $'\nPorta do servidor proxy deve conter no maximo 5 numeros'
			proxyPorta=""
			continue
		fi

	done

	if ! read -r -t 30 -p "Usuario do servidor proxy: " proxyUsuario ; then
		echo $'\n'$MSG_TIMEOUT
		exit 1;
	fi

	prompt=""
	printf "Senha do usuario do servidor proxy: "
	while IFS= read -p "$prompt" -r -s -n 1 char
	do
		if [[ $char == $'\0' ]] ; then
			break
		fi

		prompt='*'
		proxySenha="${proxySenha}${char}"
	done

	echo ""
fi

cliente=`echo ${cliente^^}`
produto=`echo ${produto^^}`
tipo=`echo ${tipo^^}`

while [ -z "$operacao" ] ; do
	if ! read -r -t 30 -p "Operacao (Normal/reTransmissao): [N/t] " operacao ; then
		echo $'\n'$MSG_TIMEOUT
		exit 1;
	fi

	if [ -z "$operacao" ] ; then
		operacao="T";
	elif [ "$operacao" != "t" ] && [ "$operacao" != "T" ] && [ "$operacao" != "n" ] && [ "$operacao" != "N" ] ; then
		operacao=""
	fi
done

while [ -z "$especial" ] ; do
	if ! read -r -t 30 -p "Arquivo especial: [s/N] " especial ; then
		echo $'\n'$MSG_TIMEOUT
		exit 1;
	fi

	if [ -z "$especial" ] ; then
		especial="N";
	elif [ "$especial" != "n" ] && [ "$especial" != "N" ] && [ "$especial" != "s" ] && [ "$especial" != "S" ] ; then
		especial=""
	fi
done

operacao=`echo ${operacao^^}`
especial=`echo ${especial^^}`

if [ "$especial" == "S" ] ; then
	especial="1"

	while [ -z "$modo" ] ; do
		if ! read -r -t 30 -p "Modo (Texto/Binario): [T/b] " modo ; then
			echo $'\n'$MSG_TIMEOUT
			exit 1;
		fi

		if [ -z "$modo" ] ; then
			modo="T";
		elif [ "$modo" != "t" ] && [ "$modo" != "T" ] && [ "$modo" != "b" ] && [ "$modo" != "B" ] ; then
			modo=""
		fi
	done
else
	especial="0"
	modo="T"
fi

modo=`echo ${modo^^}`

CLIENTE_KEY="$cliente-$produto-$tipo"
CLIENTE_DIR="$APP_DIR/$CLIENTE_KEY"

while [ -z "$dirUpload" ] ; do
	if ! read -r -t 60 -p "Diretorio e filtro de arquivos para upload (remessa): [$CLIENTE_DIR/enviar/*] " dirUpload ; then
		echo $'\n'$MSG_TIMEOUT
		exit 1;
	fi

	if [ -z "$dirUpload" ] ; then
		dirUpload="$CLIENTE_DIR/enviar/*"
	fi

	mkdir -p "$dirUpload"

	if ! [[ -d "$dirUpload" ]] ; then
		echo >&2 "ERRO: Nao foi possivel criar o diretorio: $dirUpload";
		dirUpload=""	
	fi
done

while [ -z "$dirDownload" ] ; do
	if ! read -r -t 60 -p "Diretorio de arquivos para download (retorno): [$CLIENTE_DIR/recepcao] " dirDownload ; then
		echo $'\n'$MSG_TIMEOUT
		exit 1;
	fi

	if [ -z "$dirDownload" ] ; then
		dirDownload="$CLIENTE_DIR/recepcao"
	fi

	mkdir -p "$dirDownload"

	if ! [[ -d "$dirDownload" ]] ; then
		echo >&2 "ERRO: Nao foi possivel criar o diretorio: $dirDownload";
		dirDownload=""	
	fi
done

while [ -z "$dirEnviados" ] ; do
	if ! read -r -t 60 -p "Diretorio de arquivos enviados: [$CLIENTE_DIR/enviados] " dirEnviados ; then
		echo $'\n'$MSG_TIMEOUT
		exit 1;
	fi

	if [ -z "$dirEnviados" ] ; then
		dirEnviados="$CLIENTE_DIR/enviados"
	fi

	mkdir -p "$dirEnviados"

	if ! [[ -d "$dirEnviados" ]] ; then
		echo >&2 "ERRO: Nao foi possivel criar o diretorio: $dirEnviados";
		dirEnviados=""	
	fi
done

dirUpload=$(echo $dirUpload | sed 's_/_\\/_g')
dirDownload=$(echo $dirDownload | sed 's_/_\\/_g')
dirEnviados=$(echo $dirEnviados | sed 's_/_\\/_g')

sed -e "s/7COMMKEY/${CLIENTE_KEY}/" -e "s/7COMMCLIENTE/${cliente}/" -e "s/7COMMPRODUTO/${produto}/" -e "s/7COMMTIPO/${tipo}/" -e "s/7COMMSENHA/${senha}/" -e "s/7COMMPROXYSERVIDOR/${proxyServidor}/" -e "s/7COMMPROXYPORTA/${proxyPorta}/" -e "s/7COMMPROXYUSUARIO/${proxyUsuario}/" -e "s/7COMMPROXYSENHA/${proxySenha}/" -e 's/7COMMDIRUPLOAD/'"${dirUpload}"'/' -e 's/7COMMDIRDOWNLOAD/'"${dirDownload}"'/' -e 's/7COMMDIRENVIADOS/'"${dirEnviados}"'/' -e "s/7COMMOPERACAO/${operacao}/" -e "s/7COMMMODO/${modo}/" -e "s/7COMMESPECIAL/${especial}/" $CONFIG_REF  > $CONFIG_FILE 

echo "java -cp \"Edi7WebCli.jar\" webclicfg.Edi7WebCli.Main -c$cliente -p$produto -t$tipo" > $APP_DIR/bin/$CLIENTE_KEY.sh
chmod +x $APP_DIR/bin/$CLIENTE_KEY.sh

