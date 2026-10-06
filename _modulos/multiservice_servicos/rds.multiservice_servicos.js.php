<?
if ($_GET["a"]) {
    global $bancodedados;
} else {

    if (false) {
        ?><script type="text/javascript"><? }
    ?>

    function validaServico(formulario) {

        var erro_vazio = new Array();
        var erro_invalido = new Array();

        if (!formulario.nome.value) {
            erro_vazio[erro_vazio.length] = "Nome";
        }

        if (!formulario.player.value) {
            erro_vazio[erro_vazio.length] = "Player";
        }

        if (!formulario.codigo.value) {
            erro_vazio[erro_vazio.length] = "Cóodigo";
        }

        return apresentaErros(erro_vazio, erro_invalido);

    }

    <? if (false) { ?></script><?
    }
}
?>
