#include <vector>
#include <set>
#include "Ponto.h"

using namespace std;

class Arquivo {
  vector<Ponto*> pontos;
  set<string> interfaces;
  set<int> velocidadesDownload;
  set<int> velocidadesUpload;
  string caminhoArquivos;
  string interfaceWAN;
  string bloqueioTCP;
  string bloqueioUDP;
  int limiteConexoes;
  int nat;
  public:
    Arquivo(string vArquivoConfiguracao);
    void gerePublicacao();
    void gereClientes();
    void publique();
    void publiqueRegras(int argc, char** argv);
    void publiqueInterfaces(int argc, char** argv);
};
