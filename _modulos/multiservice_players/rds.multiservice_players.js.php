<?
if ($_GET["a"]) {
    global $bancodedados;
} else {

    if (false) {
        ?><script type="text/javascript"><? }
    ?>

    function validaPlayer(formulario) {

        var erro_vazio = new Array();
        var erro_invalido = new Array();

        if (!formulario.nome.value) {
            erro_vazio[erro_vazio.length] = "Nome";
        }

        return apresentaErros(erro_vazio, erro_invalido);

    }

    <? if (false) { ?></script><?
    }
}
?>
