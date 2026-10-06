#include <sstream>
#include <iostream>
#include <unistd.h>
#include <errno.h>
#include <set>

using namespace std;

class Funcoes {
  public:
    static set<string> explode(string source, string separator);
    static string trim(string const& source);
    static string tireEspacos(string const& source);
    static string execute(string comando, bool get_erro = false);
    static string int2str(const int& var);
    static int str2int(const string& var);
    static bool inArray(int argc, char** argv, int offset, int valor);
    static bool inArray(int argc, char** argv, int offset, string valor);
};
