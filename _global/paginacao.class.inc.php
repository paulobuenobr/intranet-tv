<?

class paginacao {

    var $conteudo;
    
    function __construct($url, $total, $pagina) {
        
        $inicial = (($pagina - 1) * PAG_ITENS) + 1;
        $final = $inicial + PAG_ITENS - 1 <= $total ? $inicial + PAG_ITENS - 1 : $total;
        $paginas_total = ceil($total / PAG_ITENS);
        
        $this->conteudo = "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"margin:1px\"><tr bgcolor=\"#505050\" align=\"center\" class=\"txtBranco11\" style=\"font-size:10px\">";
        $this->conteudo .= "<td align=\"left\" width=\"267\">&nbsp;Registros encontrados: ".number_format($total, null, null, ".").($paginas_total > 1 ? " (Exibindo ".number_format($inicial, null, null, ".")." a ".number_format($final, null, null, ".").")" : "")."</td>";
        $this->conteudo .= "<td align=\"right\">".$this->geraPaginacao($url, $total, $pagina)."&nbsp;</td>";
        $this->conteudo .= "</tr></table>";
        
    }

    function geraPaginacao($url, $total, $pagina) {
        
        $paginas_total = ceil($total / PAG_ITENS);
        $paginas_exibir = 10;
        $paginas_conteudo = array();
        
        if ($paginas_total > 1) {
            
            $pagina_inicial = $this->buscaPaginaInicial($pagina, $paginas_total, $paginas_exibir);
            $pagina_final = ($pagina_inicial + $paginas_exibir - 1 < $paginas_total) ? $pagina_inicial + $paginas_exibir - 1 : $paginas_total;
            
            $paginas_conteudo[] = '<img title="Página Anterior [Ctrl + PageUp]" src="imagens/seta_left.gif" width="4" height="7"'.($pagina == 1 ? '' : ' id="pagina_anterior" style="cursor:pointer" onclick="window.location = \''.$url.'&p='.($pagina - 1).'\'"').'>';
            
            for ($i = $pagina_inicial; $i <= $pagina_final; $i++) {
                if ($i == $pagina) {
                    $paginas_conteudo[] = "<span style=\"color:#232323;font-weight:bold;margin-bottom:3px\">$i</span>";
                } else {
                    $paginas_conteudo[] = "<span style=\"cursor:pointer\" onclick=\"window.location = '".$url.'&p='.($i)."'\">$i</span>";
                }
            }

            $paginas_conteudo[] = '<img title="Próxima Página [Ctrl + PageDown]" src="imagens/seta_right.gif" width="4" height="7"'.($pagina == $paginas_total ? '' : ' id="proxima_pagina" style="cursor:pointer" onclick="window.location = \''.$url.'&p='.($pagina + 1).'\'"').'>';
            
            $paginas_conteudo = implode("&nbsp;|&nbsp;", $paginas_conteudo);
            $paginas_conteudo = '<img title="Primeira Página" src="imagens/seta_left_left.gif" width="7" height="7"'.($pagina == 1 ? '' : ' style="cursor:pointer" onclick="window.location = \''.$url.'&p='.(1).'\'"').'> '.$paginas_conteudo.' <img title="Última Página" src="imagens/seta_right_right.gif" width="7" height="7"'.($pagina == $paginas_total ? '' : ' style="cursor:pointer" onclick="window.location = \''.$url.'&p='.($paginas_total).'\'"').'>';
        
            return $paginas_conteudo;
        
        } else {
            return "";
        }
    }
    
    function buscaPaginaInicial($pagina, $paginas_total, $paginas_exibir) {
        
        if ($pagina <= ceil($paginas_exibir / 2) || $paginas_total <= $paginas_exibir) {
            return 1;
        } else {
            if ($pagina < ($paginas_total - floor($paginas_exibir / 2))) {
                return $pagina - floor($paginas_exibir / 2);
            } else {
                return $paginas_total - floor($paginas_exibir / 2) - 2;
            }
        }
        
    }

}

?>
