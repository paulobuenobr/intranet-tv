#include <iostream>
#include "Arquivo.h"

class Publicacao {
  public:
    Publicacao(string vArquivoConfiguracao);
    int publiqueRegras(int argc, char** argv, bool reiniciarDHCP);
    int publiqueInterfaces(int argc, char** argv, bool reiniciarDHCP);
    int liberePonto(int argc, char** argv, bool reiniciarDHCP);
    int publiquePontos(bool reiniciarDHCP);
    int executeFirewall(bool reiniciarDHCP);
    int gereArquivos(bool reiniciarDHCP);
    int reinicieDHCP();
  private:
    string arquivoConfiguracao;
    int publiqueRegra(int vRegra);
    int buscaRegraLivre();
};
