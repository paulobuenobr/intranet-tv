#include "Funcoes.h"

#define FATAL_MSG(msg) \
        cout << msg \
             << " : " \
             << (strerror (errno)) \
             << " [ " \
             << __FILE__ \
             << ", #" \
             << __LINE__ \
             << " ]" \
             << endl

set<string> Funcoes::explode(string source, string separator) {
  set<string> retorno;
  int iPos = source.find(separator, 0);
  int iPit = separator.length();
  while (iPos > -1) {
    if (iPos != 0)
      retorno.insert(source.substr(0, iPos));
    source.erase(0, iPos + iPit);
    iPos = source.find(separator, 0);
  }
  if (source != "")
    retorno.insert(source);
  return retorno;
}

string Funcoes::trim(string const& source) {
  char const* delims = " \t\r\n";
  string result(source);
  string::size_type index = result.find_last_not_of(delims);
  if (index != string::npos)
    result.erase(++index);
  index = result.find_first_not_of(delims);
  if (index != string::npos)
    result.erase(0, index);
  else
    result.erase();
  return result;
}

string Funcoes::tireEspacos(string const& source) {
  char const* delims = " \t\r\n";
  string::size_type index;
  string result(source);
  while ((index = result.find_first_of(delims)) != string::npos) {
    result.erase(index, 1);
  }
  return result;
}

string Funcoes::execute(string comando, bool get_erro) {
  string retorno;
  char buf[BUFSIZ];
  FILE *ptr;
  FILE *err;
  const int FD_STDERR = 2;
  int fd_errfile;
  int fd_new_errfile;
  int fd_save_error;
  // Redireciona stderr to errfile
  if ((err = tmpfile()) == NULL)
    FATAL_MSG("[Erro] Cannot execute tmpfile()");
  fd_errfile = fileno(err);
  // redirect error to file
  if ((fd_save_error = dup(FD_STDERR)) == -1)
    FATAL_MSG("[Erro] Cannot execute dup");
  // Fecha tela
  if (close(FD_STDERR) == -1)
    FATAL_MSG("[Erro] Cannot close descriptor");
  if ((fd_new_errfile = dup(fd_errfile)) == -1)
    FATAL_MSG("[Erro] Cannot execute dup");
  // Pega stdout
  if ((ptr = popen(comando.c_str(), "r")) != NULL)
    while (fgets(buf, BUFSIZ, ptr) != NULL)
      retorno += buf;
  pclose(ptr);
  // Fecha tela
  if (close(FD_STDERR) == -1)
    FATAL_MSG("[Erro] Cannot close descriptor");
  if ((fd_new_errfile = dup(fd_errfile)) == -1)
    FATAL_MSG("[Erro] Cannot execute dup");
  // Pega erro
  if (close(FD_STDERR) == -1)
    FATAL_MSG("[Erro] Cannot close descriptor");  
  if (dup(fd_save_error) == -1)
    FATAL_MSG("[Erro] Cannot execute dup");
  if (close(fd_save_error) == -1)
    FATAL_MSG ("[Erro] Cannot close descriptor");
  // Pega stderr
  if (get_erro) {
    rewind(err);
    while (fgets(buf, BUFSIZ, err)) 
      retorno += buf;
  }
  fclose(err);
  return retorno;
}

int Funcoes::str2int(const string& var) {
  stringstream ss(var);
  int n;
  ss >> n;
  return n;
}

string Funcoes::int2str(const int& var) {
  ostringstream tmp;
  tmp << var;
  return tmp.str();
}

bool Funcoes::inArray(int argc, char** argv, int offset, int valor) {
  for (int i = offset; i < argc; i++)
    if (str2int(string(argv[i])) == valor)
      return true;
  return false;
}

bool Funcoes::inArray(int argc, char** argv, int offset, string valor) {
  for (int i = offset; i < argc; i++)
    if (string(argv[i]) == valor)
      return true;
  return false;
}
