#include "Funcoes.h"
#include "Interface.h"
#include "Publicacao.h"

using namespace std;

Publicacao::Publicacao(string vArquivoConfiguracao) {
  arquivoConfiguracao = vArquivoConfiguracao;
}

int Publicacao::publiqueRegras(int argc, char** argv, bool reiniciarDHCP) {
  if (argc < 3)
    return Interface::exibaErroSintaxe();
  Arquivo arquivosPublicacao(arquivoConfiguracao);
  arquivosPublicacao.gerePublicacao();
  arquivosPublicacao.gereClientes();
  arquivosPublicacao.publiqueRegras(argc, argv);
  if (reiniciarDHCP)
    reinicieDHCP();
  return 0;
}

int Publicacao::publiqueInterfaces(int argc, char** argv, bool reiniciarDHCP) {
  if (argc < 3)
    return Interface::exibaErroSintaxe();
  Arquivo arquivosPublicacao(arquivoConfiguracao);
  arquivosPublicacao.gerePublicacao();
  arquivosPublicacao.gereClientes();
  arquivosPublicacao.publiqueInterfaces(argc, argv);
  if (reiniciarDHCP)
    reinicieDHCP();
  return 0;
}

int Publicacao::liberePonto(int argc, char** argv, bool reiniciarDHCP) {
  if (argc < 6)
    return Interface::exibaErroSintaxe();
  // Parametros
  string enderecoMAC = string(argv[2]);
  int velocidadeDownload = Funcoes::str2int(string(argv[3]));
  int velocidadeUpload = Funcoes::str2int(string(argv[4]));
  int duracao = Funcoes::str2int(string(argv[5]));
  // Verifica se já está liberado
  if (Funcoes::trim(Funcoes::execute("/sbin/ipfw show 10000-10010 | grep " + enderecoMAC + " | awk '{print $1}' | uniq")).length()) {
    Interface::exibaMensagem("[Erro] MAC '" + enderecoMAC + "' ja esta publicado");
    exit(1);
  }
  // Publica ponto
  Ponto umPonto(enderecoMAC, "", "", velocidadeDownload, velocidadeUpload, buscaRegraLivre());
  if (!umPonto.ehPontoValido()) {
    Interface::exibaMensagem("[Erro] Dados invalidos");
    exit(1);
  }
  umPonto.publique();
  // Agenda remoção
  if (duracao) 
    umPonto.agendeRemocao(umPonto.informeRegra(), duracao);
  Interface::exibaMensagem("[OK] MAC '" + enderecoMAC + "' publicado com sucesso");
  if (reiniciarDHCP)
    reinicieDHCP();
  return 0;
}

int Publicacao::buscaRegraLivre() {
  string::size_type index;
  int regra = 0, regraLivre = 0;
  char const* delims = " \t\r\n";
  string regras = Funcoes::trim(Funcoes::execute("/sbin/ipfw show 10000-10010 | awk '{print $1}' | uniq"));
  while (regras.length() > 0) {
    if ((index = regras.find_first_of(delims)) == string::npos)
      index = regras.length() - 1;
    regra = Funcoes::str2int(regras.substr(0, index + 1));
    if (regra > regraLivre)
      regraLivre = regra;
    regras.erase(0, index + 1);
    regras = Funcoes::trim(regras);
  }
  regra = (regra == 0) ? REGRA_INICIAL_EMPRESA : regra + 1;
  if (regra > 10010) {
    Interface::exibaMensagem("[Erro] Nenhuma regra disponivel");
    exit(1);
  }
  return regra;
}

int Publicacao::publiquePontos(bool reiniciarDHCP) {
  Arquivo arquivosPublicacao(arquivoConfiguracao);
  arquivosPublicacao.gerePublicacao();
  arquivosPublicacao.gereClientes();
  arquivosPublicacao.publique();
  if (reiniciarDHCP)
    reinicieDHCP();
  return 0;
}

int Publicacao::executeFirewall(bool reiniciarDHCP) {
  Arquivo arquivosPublicacao(arquivoConfiguracao);
  arquivosPublicacao.gerePublicacao();
  arquivosPublicacao.gereClientes();
  Funcoes::execute("/usr/local/etc/rc.d/firewall.sh");
  if (reiniciarDHCP)
    reinicieDHCP();
  return 0;
}

int Publicacao::gereArquivos(bool reiniciarDHCP) {
  Arquivo arquivosPublicacao(arquivoConfiguracao);
  arquivosPublicacao.gerePublicacao();
  arquivosPublicacao.gereClientes();
  if (reiniciarDHCP)
    reinicieDHCP();
  return 0;
}

int Publicacao::reinicieDHCP() {
  Funcoes::execute("/usr/local/etc/rc.d/isc-dhcpd restart");
  return 0;
}
