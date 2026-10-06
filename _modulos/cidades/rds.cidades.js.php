<?
if ($_GET["a"]) {
    global $bancodedados;
} else {

    if (false) {
        ?><script type="text/javascript"><?
    }
    ?>

    function validaCidade(formulario) {

        var erro_vazio = new Array();
        var erro_invalido = new Array();

        if (!formulario.cid_nome.value) {
            erro_vazio[erro_vazio.length] = "Nome";
        }

        if (!formulario.est_id.value) {
            erro_vazio[erro_vazio.length] = "Estado";
        }

        return apresentaErros(erro_vazio, erro_invalido);

    }

    <? if (false) { ?></script><?
    }
}
?>
