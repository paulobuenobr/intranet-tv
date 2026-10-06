<?
if ($_GET["a"]) {
    global $bancodedados;
} else {

    if (false) {
        ?><script type="text/javascript"><?
    }
    ?>

    function validaCatalogoTv(formulario) {

        var erro_vazio = new Array();
        var erro_invalido = new Array();

        if (!formulario.codigo.value) {
            erro_vazio[erro_vazio.length] = "Código";
        }

        if (!formulario.nome.value) {
            erro_vazio[erro_vazio.length] = "Nome";
        }

        if (!formulario.nome_catalogo.value) {
            erro_vazio[erro_vazio.length] = "Nome Catálogo";
        }

        return apresentaErros(erro_vazio, erro_invalido);

    }

    <? if (false) { ?></script><?
    }
}
?>
