<?
if ($_GET["a"]) {
    global $bancodedados;
} else {

    if (false) {
        ?><script type="text/javascript"><?
    }
    ?>

    function validaCron(formulario) {

        var erro_vazio = new Array();
        var erro_invalido = new Array();

        if (!formulario.cro_classe.value) {
            erro_vazio[erro_vazio.length] = "Classe";
        }

        if (!formulario.cro_acao.value) {
            erro_vazio[erro_vazio.length] = "Ação";
        }

        if (formulario.cro_intervalo_minutos.value && !/^[0-9]+$/.test(formulario.cro_intervalo_minutos.value)) {
            erro_invalido[erro_invalido.length] = "Intervalo (minutos)";
        }

        return apresentaErros(erro_vazio, erro_invalido);

    }

    <? if (false) { ?></script><?
    }
}
?>
