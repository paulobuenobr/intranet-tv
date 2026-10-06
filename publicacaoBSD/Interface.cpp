#include "Interface.h"

int Interface::exibaAjuda() {
  cout << endl;
  cout << " Sintaxe: publicacaoBSD [-d] [-c arquivo_configuracao] -[hvafgpl] [parametros]" << "\n";
  cout << "\n";
  cout << "   h: ajuda" << "\n";
  cout << "   v: exibe versao" << "\n";
  cout << "   d: reinicia DHCP" << "\n";
  cout << "   a: publica todos os pontos" << "\n";
  cout << "   f: executa o firewall" << "\n";
  cout << "   g: gera arquivos" << "\n";
  cout << "   p: publica ponto(s) [regra(s) ...]" << "\n";
  cout << "   i: publica interface(s) [interface(s) ...]" << "\n";
  cout << "   l: libera ponto [mac velocidade_download velocidade_upload duracao]" << "\n";
  cout << "   c: define arquivo de configuracao" << "\n";
  cout << "\n";
  return 0;
}

int Interface::exibaVersao() {
  cout << "\n";
  cout << " ---------------------------------" << "\n";
  cout << "  Publicacao FreeBSD  Versao  1.1"  << "\n";
  cout << "  OLE Telecom         " << __DATE__ << "\n";
  cout << " ---------------------------------" << "\n";
  cout << "\n";
  return 0;
}

int Interface::exibaErroSintaxe() {
  cerr << "[Erro] Sintaxe incorreta. Digite 'publicacaoBSD -h' para ajuda." << "\n";
  cerr << "\n";
  return 1;
}

int Interface::exibaErro(string erro) {
  cerr << erro << "\n";
  cerr << "\n";
  return 0;
}

void Interface::exibaMensagem(string mensagem) {
  cout << mensagem << "\n";
}

void Interface::exibaMensagem(int mensagem) {
  cout << mensagem << "\n";
}
