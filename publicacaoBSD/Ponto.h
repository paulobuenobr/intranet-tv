#define REGRA_INICIAL_CLIENTES 10010
#define REGRA_INICIAL_EMPRESA 10000

#include <fstream>

using namespace std;

class Ponto {
  string enderecoMAC;
  string interfaceDownload;
  string interfaceUpload;
  string comentario;
  int velocidadeDownload;
  int velocidadeUpload;
  int empresarial;
  int status;
  int regra;
  public:
    Ponto(int vRegra);
    Ponto(string linha);
    Ponto(string vEnderecoMAC, string vInterfaceDownload, string vInterfaceUpload, int vVelocidadeDownload, int vVelocidadeUpload, int vRegra);
    string informeEnderecoMAC();
    string informeInterfaceDownload();
    string informeInterfaceUpload();
    string informeComentario();
    int informeVelocidadeDownload();
    int informeVelocidadeUpload();
    int informeEmpresarial();
    int informeStatus();
    int informeRegra();
    int ehPontoValido();
    int publique();
    void agendeRemocao(int regra, int duracao);
  private:
    void buscaPontoRegra(int vRegra);
    int processeLinha(string linha);
};
