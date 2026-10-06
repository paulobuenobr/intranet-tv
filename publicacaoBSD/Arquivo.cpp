#include "Interface.h"
#include "Funcoes.h"
#include "Arquivo.h"

Arquivo::Arquivo(string arquivoConfiguracao) {
  ifstream publicacao_cfg;
  publicacao_cfg.open(arquivoConfiguracao.c_str());
  if (!publicacao_cfg) {
    Interface::exibaMensagem("[Erro] Arquivo '" + arquivoConfiguracao + "' nao encontrado");
    exit(1);
  }
  string linha;
  Ponto* ponto;
  bool interfacesDefinidas = false;
  while (getline(publicacao_cfg, linha)) {
    if (!Funcoes::trim(linha).length()) continue;
    if (Funcoes::tireEspacos(linha).substr(0, 19) == "#!caminho_arquivos=") {
      caminhoArquivos = Funcoes::tireEspacos(linha.substr(linha.find_first_of('=') + 1));
      continue;
    } else if (Funcoes::tireEspacos(linha).substr(0, 16) == "#!interface_wan=") {
      interfaceWAN = Funcoes::tireEspacos(linha.substr(linha.find_first_of('=') + 1));
      continue;
    } else if (Funcoes::tireEspacos(linha).substr(0, 15) == "#!bloqueio_tcp=") {
      bloqueioTCP = Funcoes::tireEspacos(linha.substr(linha.find_first_of('=') + 1));
      continue;
    } else if (Funcoes::tireEspacos(linha).substr(0, 15) == "#!bloqueio_udp=") {
      bloqueioUDP = Funcoes::tireEspacos(linha.substr(linha.find_first_of('=') + 1));
      continue;
    } else if (Funcoes::tireEspacos(linha).substr(0, 18) == "#!limite_conexoes=") {
      limiteConexoes = Funcoes::str2int(linha.substr(linha.find_first_of('=') + 1));
      continue;
    } else if (Funcoes::tireEspacos(linha).substr(0, 6) == "#!nat=") {
      nat = Funcoes::str2int(linha.substr(linha.find_first_of('=') + 1));
      continue;
    } else if (Funcoes::tireEspacos(linha).substr(0, 13) == "#!interfaces=") {
      interfaces = Funcoes::explode(linha.substr(linha.find_first_of('=') + 1), ",");
      interfacesDefinidas = true;
      continue;
    }
    if (Funcoes::trim(linha.substr(0, linha.find('#') + 1)) == "#") continue;
    if (Funcoes::trim(linha.substr(0, linha.find(';') + 1)) == ";") continue;
    ponto = new Ponto(linha);
    if (ponto->ehPontoValido()) {
      pontos.push_back(ponto);
      velocidadesDownload.insert(ponto->informeVelocidadeDownload());
      velocidadesUpload.insert(ponto->informeVelocidadeUpload());
      if (!interfacesDefinidas) 
        interfaces.insert(ponto->informeInterfaceUpload());
    } else
      delete ponto;
  }
  publicacao_cfg.close();
}

void Arquivo::gerePublicacao() {
  ofstream arquivo(string(caminhoArquivos + "/publicacao").c_str());
  if (!arquivo) {
    Interface::exibaMensagem("[Erro] Nao foi possivel criar o arquivo 'publicacao'");
    exit(1);
  }
  arquivo << "#!/bin/sh" << "\n";
  arquivo << "\n";
  arquivo << "/sbin/ipfw -q add 100 skipto 65530 all from any to any MAC any any out via " << interfaceWAN << "\n";
  arquivo << "/sbin/ipfw -q add 100 skipto 65530 all from any to any MAC any any in via " << interfaceWAN << "\n";
  arquivo << "\n";
  if (bloqueioTCP.length() || bloqueioUDP.length()) {
    arquivo << "###############################" << "\n";
    arquivo << "###    BLOQUEIO DE VIRUS    ###\n";
    arquivo << "###############################\n";
    arquivo << "\n";
    if (bloqueioTCP.length())
      arquivo << "/sbin/ipfw -q add 400 deny tcp from any to any dst-port " << bloqueioTCP << " in\n";
    if (bloqueioUDP.length())
      arquivo << "/sbin/ipfw -q add 400 deny udp from any to any dst-port " << bloqueioUDP << " in\n";
  }
  arquivo << "\n";
  arquivo << "###############################\n";
  arquivo << "###  VELOCIDADES (UPLOAD)   ###\n";
  arquivo << "###############################\n";
  arquivo << "\n";
  for (set<int>::iterator i = velocidadesUpload.begin(); i != velocidadesUpload.end(); i++ ) { 
    arquivo << "### " << *i << " Kbps\n";
    arquivo << "/sbin/ipfw pipe " << *i << " config mask src-ip 0x00ffffff bw " << *i << "Kbit/s\n";
    arquivo << "/sbin/ipfw -q add " << *i + 45000 << " pipe " << *i << " ip from any to any MAC any any in\n";
    arquivo << "/sbin/ipfw -q add " << *i + 45005 << " skipto 59000 all from any to any MAC any any in\n";
    arquivo << "\n";
  }
  arquivo << "###############################\n";
  arquivo << "###  VELOCIDADES (DOWNLOAD) ###\n";
  arquivo << "###############################\n";
  arquivo << "\n";
  for (set<int>::iterator i = velocidadesDownload.begin(); i != velocidadesDownload.end(); i++ ) { 
    arquivo << "### " << *i << " Kbps\n";
    arquivo << "/sbin/ipfw pipe " << *i + 1 << " config mask dst-ip 0x00ffffff bw " << *i << "Kbit/s\n";
    arquivo << "/sbin/ipfw -q add " << *i + 45001 << " pipe " << *i + 1 << " ip from any to any MAC any any out\n";
    arquivo << "/sbin/ipfw -q add " << *i + 45005 << " skipto 59000 all from any to any MAC any any out\n";
    arquivo << "\n";
  }
  if (nat && interfaceWAN != "") {
    arquivo << "###############################\n";
    arquivo << "###           NAT           ###\n";
    arquivo << "###############################\n";
    arquivo << "\n";
    arquivo << "/sbin/ipfw nat 1 config if " << interfaceWAN << " same_ports unreg_only reset\n";
    arquivo << "/sbin/ipfw -q add 60000 nat 1 ip from any to any not layer2 via " << interfaceWAN << "\n";
    arquivo << "\n";
  }
  arquivo << "###############################\n";
  arquivo << "###   LIMITE DE CONEXOES    ###\n";
  arquivo << "###############################\n";
  arquivo << "\n";
  arquivo << "/sbin/ipfw -q add 61000 allow ip from any to any MAC any any in limit src-addr " << limiteConexoes << "\n";
  arquivo << "/sbin/ipfw -q add 61000 allow ip from any to any MAC any any out limit dst-addr " << limiteConexoes << "\n";
  arquivo << "\n";
  arquivo << "###############################\n";
  arquivo << "### EXECUTA REGRAS CLIENTES ###\n";
  arquivo << "###############################\n";
  arquivo << "\n";
  arquivo << "sh " << caminhoArquivos << "/clientes\n";
  arquivo << "\n";
  arquivo << "### FIM\n";
  arquivo.close();  
}

void Arquivo::gereClientes() {
  ofstream arquivo(string(caminhoArquivos + "/clientes").c_str());
  if (!arquivo) {
    Interface::exibaMensagem("[Erro] Nao foi possivel criar o arquivo 'clientes'");
    exit(1);
  }
  arquivo << "#!/bin/sh\n";
  arquivo << "\n";
  arquivo << "###############################\n";
  arquivo << "###  DELETA REGRAS ANTIGAS  ###\n";
  arquivo << "###############################\n";
  arquivo << "\n";
  arquivo << "/sbin/ipfw delete 44000\n";
  arquivo << "/sbin/ipfw delete set 10\n";
  arquivo << "\n";
  arquivo << "###############################\n";
  arquivo << "### CLIENTES X VELOCIDADES  ###\n";
  arquivo << "###############################\n";
  arquivo << "\n";
  for (int i = 0; i < pontos.size(); i++) {
    arquivo << "### " << (pontos.at(i)->informeStatus() ? "" : "[Bloqueado] ") << pontos.at(i)->informeComentario() << "\n";
    arquivo << (pontos.at(i)->informeStatus() ? "" : "### ") << "/sbin/ipfw -q add " << pontos.at(i)->informeRegra() << " set 10 skipto " << pontos.at(i)->informeVelocidadeDownload() + 45001 << " ip from any to any MAC " << pontos.at(i)->informeEnderecoMAC() << " any out via " << pontos.at(i)->informeInterfaceDownload() << "\n";
    arquivo << (pontos.at(i)->informeStatus() ? "" : "### ") << "/sbin/ipfw -q add " << pontos.at(i)->informeRegra() << " set 10 skipto " << pontos.at(i)->informeVelocidadeUpload() + 45000 << " ip from any to any MAC any " << pontos.at(i)->informeEnderecoMAC() << " in via " << pontos.at(i)->informeInterfaceUpload() << "\n";
    if (pontos.at(i)->informeEmpresarial()) {
      arquivo << (pontos.at(i)->informeStatus() ? "" : "### ") << "/sbin/ipfw -q add 60500 set 10 skipto 62000 all from any to any MAC " << pontos.at(i)->informeEnderecoMAC() << " any out via " << pontos.at(i)->informeInterfaceDownload() << "\n";
      arquivo << (pontos.at(i)->informeStatus() ? "" : "### ") << "/sbin/ipfw -q add 60500 set 10 skipto 62000 all from any to any MAC any " << pontos.at(i)->informeEnderecoMAC() << " in via " << pontos.at(i)->informeInterfaceUpload() << "\n";
    }
    arquivo << "\n";
  }
  arquivo << "###############################\n";
  arquivo << "###  BLOQUEIA DEMAIS MAC'S  ###\n";
  arquivo << "###############################\n";
  arquivo << "\n";
  for (set<string>::iterator i = interfaces.begin(); i != interfaces.end(); i++)
    arquivo << "/sbin/ipfw -q add 44000 deny all from any to any MAC any any in via " << *i << "\n";
  arquivo << "\n";
  arquivo << "### FIM\n";
  arquivo.close();
}

void Arquivo::publique() {
  // Adicionar velocidades novas
  string regras = Funcoes::execute("/sbin/ipfw show 45000-58000 | grep pipe | awk '{print $1}'");
  for (set<int>::iterator i = velocidadesUpload.begin(); i != velocidadesUpload.end(); i++) {
    if (regras.find(Funcoes::int2str(*i + 45000)) == string::npos) {
      Funcoes::execute("/sbin/ipfw pipe " + Funcoes::int2str(*i) + " config mask src-ip 0x00ffffff bw " + Funcoes::int2str(*i) + "Kbit/s");
      Funcoes::execute("/sbin/ipfw -q add " + Funcoes::int2str(*i + 45000) + " pipe " + Funcoes::int2str(*i) + " ip from any to any MAC any any in");
      Funcoes::execute("/sbin/ipfw -q add " + Funcoes::int2str(*i + 45005) + " skipto 59000 all from any to any MAC any any in");
    }
  }
  for (set<int>::iterator i = velocidadesDownload.begin(); i != velocidadesDownload.end(); i++) {
    if (regras.find(Funcoes::int2str(*i + 45001)) == string::npos) {
      Funcoes::execute("/sbin/ipfw pipe " + Funcoes::int2str(*i + 1) + " config mask dst-ip 0x00ffffff bw " + Funcoes::int2str(*i) + "Kbit/s");
      Funcoes::execute("/sbin/ipfw -q add " + Funcoes::int2str(*i + 45001) + " pipe " + Funcoes::int2str(*i + 1) + " ip from any to any MAC any any out");
      Funcoes::execute("/sbin/ipfw -q add " + Funcoes::int2str(*i + 45005) + " skipto 59000 all from any to any MAC any any out");
    }
  }
  // Adicionar bloqueio MACs de interfaces novas
  regras = Funcoes::execute("/sbin/ipfw show 44000 | awk '{print $15}'");
  for (set<string>::iterator i = interfaces.begin(); i != interfaces.end(); i++) {
    if (regras.find(*i) == string::npos) {
      Funcoes::execute("/sbin/ipfw -q add 44000 deny all from any to any MAC any any in via " + *i);
    }
  }
  // Verifica/Adiciona NAT
  if (nat && interfaceWAN != "") {
    regras = Funcoes::execute("/sbin/ipfw show 60000");
    if (Funcoes::trim(regras).length() == 0 || Funcoes::trim(regras).find("does not exist") != string::npos) {
      Funcoes::execute("/sbin/ipfw nat 1 config if " + interfaceWAN + " same_ports unreg_only reset");
      Funcoes::execute("/sbin/ipfw -q add 60000 nat 1 ip from any to any not layer2 via " + interfaceWAN);
    }
  }
  // Atualiza limite conexões
  regras = Funcoes::execute("/sbin/ipfw show 61000 | grep limit | awk '{print $1}'");
  if (Funcoes::trim(regras).length() == 0) {
    Funcoes::execute("/sbin/ipfw -q add 61000 allow ip from any to any MAC any any in limit src-addr " + Funcoes::int2str(limiteConexoes));
    Funcoes::execute("/sbin/ipfw -q add 61000 allow ip from any to any MAC any any out limit dst-addr " + Funcoes::int2str(limiteConexoes));
  }
  // Executa arquivo 'clientes'
  Funcoes::execute("sh " + caminhoArquivos + "/clientes");
}

void Arquivo::publiqueRegras(int argc, char** argv) {
  bool existeRegra;
  for (int i = 2; i < argc; i++) {
    existeRegra = false;
    for (int j = 0; j < pontos.size(); j++)
      if (Funcoes::str2int(argv[i]) + REGRA_INICIAL_CLIENTES == pontos.at(j)->informeRegra()) {
        existeRegra = true;
        break;
      }
    if (!existeRegra)
      Interface::exibaMensagem("[Erro] Regra '" + string(argv[i]) + "' nao encontrada");
  }
  bool existeEmpresarial = false;
  int pontos_processados = 0;
  for (int i = 0; i < pontos.size(); i++)
    if (Funcoes::inArray(argc, argv, 2, pontos.at(i)->informeRegra() - REGRA_INICIAL_CLIENTES)) {
      if (!existeEmpresarial && (pontos.at(i)->informeEmpresarial() || Funcoes::trim(Funcoes::execute("ipfw show 60500 | grep -i '" + pontos.at(i)->informeEnderecoMAC() + "' | awk '{print $1}' | uniq")) == "60500")) {
        Funcoes::execute("/sbin/ipfw delete 61000");
        Funcoes::execute("/sbin/ipfw delete 60500");
        existeEmpresarial = true;
      }
      pontos.at(i)->publique();
      Interface::exibaMensagem("[OK] Regra '" + Funcoes::int2str(pontos.at(i)->informeRegra() - REGRA_INICIAL_CLIENTES) + "' publicada com sucesso");
      pontos_processados++;
      if (pontos_processados == argc - 2)
        break;
    }      
  if (existeEmpresarial) {
    for (int i = 0; i < pontos.size(); i++)
      if (pontos.at(i)->informeEmpresarial() && !Funcoes::inArray(argc, argv, 2, pontos.at(i)->informeRegra() - REGRA_INICIAL_CLIENTES))
        pontos.at(i)->publique();
    Funcoes::execute("/sbin/ipfw -q add 61000 allow ip from any to any MAC any any in limit src-addr " + Funcoes::int2str(limiteConexoes));
    Funcoes::execute("/sbin/ipfw -q add 61000 allow ip from any to any MAC any any out limit dst-addr " + Funcoes::int2str(limiteConexoes));
  }
}

void Arquivo::publiqueInterfaces(int argc, char** argv) {
  bool existeInterface;
  for (int i = 2; i < argc; i++) {
    existeInterface = false;
    for (int j = 0; j < pontos.size(); j++)
      if (string(argv[i]) == pontos.at(j)->informeInterfaceDownload()) {
        existeInterface = true;
        break;
      }
    if (!existeInterface)
      Interface::exibaMensagem("[Erro] Interface '" + string(argv[i]) + "' nao encontrada ou sem pontos para publicar");
  }
  bool existeEmpresarial = false;
  set<string> interfacesPublicadas;
  for (int i = 0; i < pontos.size(); i++) {
    if (Funcoes::inArray(argc, argv, 2, pontos.at(i)->informeInterfaceDownload())) {
      if (!existeEmpresarial && (pontos.at(i)->informeEmpresarial() || Funcoes::trim(Funcoes::execute("ipfw show 60500 | grep -i '" + pontos.at(i)->informeEnderecoMAC() + "' | awk '{print $1}' | uniq")) == "60500")) {
        Funcoes::execute("/sbin/ipfw delete 61000");
        Funcoes::execute("/sbin/ipfw delete 60500");
        existeEmpresarial = true;
      }
      pontos.at(i)->publique();
      interfacesPublicadas.insert(pontos.at(i)->informeInterfaceDownload());
    }
  }
  for (set<string>::iterator i = interfacesPublicadas.begin(); i != interfacesPublicadas.end(); i++)
    Interface::exibaMensagem("[OK] Interface '" + *i + "' publicada com sucesso");
  if (existeEmpresarial) {
    for (int i = 0; i < pontos.size(); i++)
      if (pontos.at(i)->informeEmpresarial() && !Funcoes::inArray(argc, argv, 2, pontos.at(i)->informeInterfaceDownload()))
        pontos.at(i)->publique();
    Funcoes::execute("/sbin/ipfw -q add 61000 allow ip from any to any MAC any any in limit src-addr " + Funcoes::int2str(limiteConexoes));
    Funcoes::execute("/sbin/ipfw -q add 61000 allow ip from any to any MAC any any out limit dst-addr " + Funcoes::int2str(limiteConexoes));
  }  
}
