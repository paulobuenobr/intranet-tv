#include <iostream>

using namespace std;

class Interface {
  public:
    static int exibaAjuda();
    static int exibaVersao();
    static int exibaErroSintaxe();
    static int exibaErro(string erro);
    static void exibaMensagem(string mensagem);
    static void exibaMensagem(int mensagem);
};
