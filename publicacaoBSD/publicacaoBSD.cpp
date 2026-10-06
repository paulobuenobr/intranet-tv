#include "Interface.h"
#include "Publicacao.h"

using namespace std;

int main (int argc, char** argv) {
  string arquivoConfiguracao = "/usr/home/intranet/publicacaoBSD.cfg";
  bool reiniciarDHCP = false;
  if (argc < 2)
    return Interface::exibaErroSintaxe();
  else if (string(argv[1]) == "-h" || string(argv[1]) == "-?")
    return Interface::exibaAjuda();
  else if (string(argv[1]) == "-v")
    return Interface::exibaVersao();
  if (string(argv[1]) == "-d") {
    reiniciarDHCP = true;
    argc -= 1;
    for (int i = 1; i < argc; i++)
      argv[i] = argv[i + 1];
  }
  if (string(argv[1]) == "-c") {
    if (argc < 4 || string(argv[3])[0] != '-')
      return Interface::exibaErroSintaxe();
    arquivoConfiguracao = string(argv[2]);
    argc -= 2;
    for (int i = 1; i < argc; i++)
      argv[i] = argv[i + 2];
  }
  Publicacao publicacaoBSD(arquivoConfiguracao);
  if (string(argv[1]) == "-a")
    return publicacaoBSD.publiquePontos(reiniciarDHCP);
  else if (string(argv[1]) == "-f")
    return publicacaoBSD.executeFirewall(reiniciarDHCP);
  else if (string(argv[1]) == "-g")
    return publicacaoBSD.gereArquivos(reiniciarDHCP);
  else if (string(argv[1]) == "-p")
    return publicacaoBSD.publiqueRegras(argc, argv, reiniciarDHCP);
  else if (string(argv[1]) == "-i")
    return publicacaoBSD.publiqueInterfaces(argc, argv, reiniciarDHCP);
  else if (string(argv[1]) == "-l")
    return publicacaoBSD.liberePonto(argc, argv, reiniciarDHCP);
  else if (reiniciarDHCP)
    return publicacaoBSD.reinicieDHCP();
  else
    return Interface::exibaErroSintaxe();
}
