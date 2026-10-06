#include "Ponto.h"
#include "Interface.h"
#include "Funcoes.h"

Ponto::Ponto(int vRegra) {
  buscaPontoRegra(vRegra);
}

Ponto::Ponto(string linha) {
  processeLinha(linha);
}

Ponto::Ponto(string vEnderecoMAC, string vInterfaceDownload, string vInterfaceUpload, int vVelocidadeDownload, int vVelocidadeUpload, int vRegra) {
  enderecoMAC = vEnderecoMAC;
  interfaceDownload = vInterfaceDownload;
  interfaceUpload = vInterfaceUpload;
  velocidadeDownload = vVelocidadeDownload;
  velocidadeUpload = vVelocidadeUpload;
  regra = vRegra;
  empresarial = 0;
  status = 1;
}

string Ponto::informeEnderecoMAC() {
  return enderecoMAC;
}

string Ponto::informeInterfaceDownload() {
  return interfaceDownload;
}

string Ponto::informeInterfaceUpload() {
  return interfaceUpload;
}

string Ponto::informeComentario() {
  return comentario;
}

int Ponto::informeVelocidadeUpload() {
  return velocidadeUpload;
}

int Ponto::informeVelocidadeDownload() {
  return velocidadeDownload;
}

int Ponto::informeEmpresarial() {
  return empresarial;
}

int Ponto::informeStatus() {
  return status;
}

int Ponto::informeRegra() {
  return regra;
}

int Ponto::ehPontoValido() {
  return enderecoMAC.length() == 17;
}

int Ponto::publique() {
  // Remove regra
  Funcoes::execute("/sbin/ipfw delete " + Funcoes::int2str(regra));
  if (status) {
    // Adicionar Velocidades não aplicadas
    string regras = Funcoes::execute("/sbin/ipfw show 45000-58000 | grep pipe | awk '{print $1}'");
    if (velocidadeUpload >= 0 && regras.find(Funcoes::int2str(velocidadeUpload + 45000)) == string::npos) {
      Funcoes::execute("/sbin/ipfw pipe " + Funcoes::int2str(velocidadeUpload) + " config mask src-ip 0x00ffffff bw " + Funcoes::int2str(velocidadeUpload) + "Kbit/s");
      Funcoes::execute("/sbin/ipfw -q add " + Funcoes::int2str(velocidadeUpload + 45000) + " pipe " + Funcoes::int2str(velocidadeUpload) + " ip from any to any MAC any any in");
      Funcoes::execute("/sbin/ipfw -q add " + Funcoes::int2str(velocidadeUpload + 45005) + " skipto 59000 all from any to any MAC any any in");
    }
    if (velocidadeDownload >= 0 && regras.find(Funcoes::int2str(velocidadeDownload + 45001)) == string::npos) {
      Funcoes::execute("/sbin/ipfw pipe " + Funcoes::int2str(velocidadeDownload + 1) + " config mask dst-ip 0x00ffffff bw " + Funcoes::int2str(velocidadeDownload) + "Kbit/s");
      Funcoes::execute("/sbin/ipfw -q add " + Funcoes::int2str(velocidadeDownload + 45001) + " pipe " + Funcoes::int2str(velocidadeDownload + 1) + " ip from any to any MAC any any out");
      Funcoes::execute("/sbin/ipfw -q add " + Funcoes::int2str(velocidadeDownload + 45005) + " skipto 59000 all from any to any MAC any any out");
    }
    // Adicionar bloqueio MACs em interfaces não aplicados
    regras = Funcoes::execute("/sbin/ipfw show 44000 | awk '{print $15}'");
    if (regras.find(interfaceUpload) == string::npos)
      Funcoes::execute("/sbin/ipfw -q add 44000 deny all from any to any MAC any any in via " + interfaceUpload);
    // Adicionar regra do ponto
    if (velocidadeDownload >= 0)
      Funcoes::execute("/sbin/ipfw -q add " + Funcoes::int2str(regra) + " set 10 skipto " + Funcoes::int2str(velocidadeDownload + 45001) + " ip from any to any MAC " + enderecoMAC + " any out" + (interfaceDownload != "" ? " via " + interfaceDownload : ""));
    else
      Funcoes::execute("/sbin/ipfw -q add " + Funcoes::int2str(regra) + " set 10 skipto 65530 ip from any to any MAC " + enderecoMAC + " any out" + (interfaceDownload != "" ? " via " + interfaceDownload : ""));
    if (velocidadeUpload >= 0)
      Funcoes::execute("/sbin/ipfw -q add " + Funcoes::int2str(regra) + " set 10 skipto " + Funcoes::int2str(velocidadeUpload + 45000) + " ip from any to any MAC any " + enderecoMAC + " in" + (interfaceUpload != "" ? " via " + interfaceUpload : ""));
    else
      Funcoes::execute("/sbin/ipfw -q add " + Funcoes::int2str(regra) + " set 10 skipto 65530 ip from any to any MAC any " + enderecoMAC + " in" + (interfaceUpload != "" ? " via " + interfaceUpload : ""));
    if (empresarial) {
      Funcoes::execute("/sbin/ipfw -q add 60500 set 10 skipto 62000 all from any to any MAC " + enderecoMAC + " any out" + (interfaceDownload != "" ? " via " + interfaceDownload : ""));
      Funcoes::execute("/sbin/ipfw -q add 60500 set 10 skipto 62000 all from any to any MAC any " + enderecoMAC + " in" + (interfaceUpload != "" ? " via " + interfaceUpload : ""));
    }
  }
  return 1;
}

void Ponto::agendeRemocao(int regra, int duracao) {
  Funcoes::execute(string("echo '/sbin/ipfw delete " + Funcoes::int2str(regra) + "' | at now + " + Funcoes::int2str(duracao) + " minutes").c_str());
}

void Ponto::buscaPontoRegra(int vRegra) {
  ifstream publicacao_cfg;
  publicacao_cfg.open("publicacaoBSD.cfg");
  if (!publicacao_cfg) {
    Interface::exibaMensagem("[Erro] Arquivo 'publicacaoBSD.cfg' nao encontrado");
    exit(1);
  }
  string linha;
  int encontrato = 0;
  while (getline(publicacao_cfg, linha)) {
    if (!Funcoes::trim(linha).length()) continue;
    if (Funcoes::trim(linha.substr(0, linha.find('#') + 1)) == "#") continue;
    if (Funcoes::trim(linha.substr(0, linha.find(';') + 1)) == ";") continue;
    if (Funcoes::str2int(Funcoes::trim(linha.substr(0, linha.find(';')))) == vRegra) {
      processeLinha(linha);
      encontrato = 1;
      break;
    }
  }
  publicacao_cfg.close();
  if (!encontrato) {
    Interface::exibaMensagem("[Erro] regra '" + Funcoes::int2str(vRegra) + "' nao encontrada");
    exit(1);
  }
}

int Ponto::processeLinha(string linha) {
  int indice = 0;
  int posicao = linha.find(';') + 1;
  regra = REGRA_INICIAL_CLIENTES + Funcoes::str2int(Funcoes::trim(linha.substr(0, linha.find(';'))));
  while (linha.find(';', posicao) != string::npos) {
    switch (indice) {
      case 0: enderecoMAC = Funcoes::trim(linha.substr(posicao, linha.find(';', posicao) - posicao));
      case 1: status = Funcoes::str2int(Funcoes::trim(linha.substr(posicao, linha.find(';', posicao) - posicao)));
      case 2: empresarial = Funcoes::str2int(Funcoes::trim(linha.substr(posicao, linha.find(';', posicao) - posicao)));
      case 3: interfaceDownload = Funcoes::trim(linha.substr(posicao, linha.find(';', posicao) - posicao));
      case 4: interfaceUpload = Funcoes::trim(linha.substr(posicao, linha.find(';', posicao) - posicao));
      case 5: velocidadeDownload = Funcoes::str2int(Funcoes::trim(linha.substr(posicao, linha.find(';', posicao) - posicao)));
      case 6: velocidadeUpload = Funcoes::str2int(Funcoes::trim(linha.substr(posicao, linha.find(';', posicao) - posicao)));
    }
    posicao = linha.find(';', posicao) + 1;
    indice++;
  }
  comentario = Funcoes::trim(linha.substr(posicao));
}
