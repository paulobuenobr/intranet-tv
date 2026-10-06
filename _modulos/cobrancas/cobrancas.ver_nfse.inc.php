<script type="text/javascript" src="rds.js.php?m=cobrancas"></script>

<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
    <tr>
        <td>
            <table width="100%" border="0" cellspacing="1" cellpadding="3">
                <tr bgcolor="#E8E8E8" class="txtAzulEscuro11 d-none-print">
                    <td width="100%">
                        <strong>&nbsp;Nota:</strong>
                    </td>
                </tr>

                <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                    <td class="td_nota">
                        [nota]
                    </td>
                </tr>

                <tr bgcolor="#E8E8E8" class="txtAzulEscuro11 d-none-print">
                    <td height="40" colspan="2" align="center">[botoes]</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<script type="text/javascript">
    if (window.opener && window.opener.buscarNotas) {
        window.opener.buscarNotas([cobranca]);
    }
</script>

<style>
    @media print {
        title,
        .d-none-print ,
        .txtBranco11 {
            display: none;
        }
    }
    td.td_nota div {
        padding: 0 !important;
    }
</style>