<?php

class cron_notificacao_cobranca {

    function cron_notificacao_cobranca($bancodedados) {

        $this->bancodedados = $bancodedados;
    }

    function enviaCobrancas() {

        $enviados1 = $enviados = 0;
        $erros = array();
        $sql = 'SELECT * FROM tab_dias_notificar WHERE status IS TRUE AND tipo = "notificacao_cobranca"';
        $query = $this->bancodedados->query($sql);
        while (($dia = $this->bancodedados->resultadoArray($query)) != false) {

            $enviados1++;
            $day = "{$dia['dias']} day";
            $diaSql = date('Y-m-d', strtotime($day));
            $pai = "SELECT 
                            coa_id AS id , coa_parametros AS parametros
                    FROM
                            tab_cobrancas
                            INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
                    WHERE
                            tab_cobrancas_formatos.cft_id IN (2, 3) AND
                            tab_cobrancas.cot_id = 1 AND
                            tab_cobrancas.coa_data_vencimento = '$diaSql' AND
                            tab_cobrancas.coa_pai IS NULL AND
                            (SELECT COUNT(*) FROM tab_cobrancas filha WHERE filha.coa_pai = tab_cobrancas.coa_id) = 0";
            $erros[$enviados1][0] = $pai;
            $queryPai = $this->bancodedados->query($pai);
            while (($dados = $this->bancodedados->resultadoArray($queryPai)) != false) {
                $this->enviar($dados, $dia);
                $enviados++;
            }

            $filho = "SELECT
                            coa_id AS id , coa_parametros AS parametros
                      FROM
                            tab_cobrancas
                      WHERE
                            coa_id IN (
                                SELECT
                                    MAX(tab_cobrancas.coa_id)
                                FROM
                                    tab_cobrancas
                                    INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
                                WHERE
                                    tab_cobrancas_formatos.cft_id IN (2, 3) AND
                                    tab_cobrancas.cot_id = 1 AND
                                    tab_cobrancas.coa_data_vencimento = '$diaSql' AND
                                    tab_cobrancas.coa_pai IS NOT NULL
                                GROUP BY
                                    tab_cobrancas.coa_pai
                                )";
            $erros[$enviados1][1] = $filho;
            $queryFilho = $this->bancodedados->query($filho);
            while (($dados = $this->bancodedados->resultadoArray($queryFilho)) != false) {
                $this->enviar($dados, $dia);
                $enviados++;
            }
        }

        // echo $enviados . '--' . $enviados1 . "<pre>";
        // var_dump($erros);
    }

    function enviar($dados, $dia) {

        $cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);
        $cobrancas->buscaDadosItem($dados["id"]);
        $parametros = unserialize($dados["parametros"]);
        $emails = $cobrancas->buscaContatosEmails($cobrancas->dados_item["cliente_id"]);
        $conteudo = str_replace('[NOME_DO_CLIENTE]', $cobrancas->dados_item["cliente_nome"], $dia['conteudo']);
        if (count($emails) > 0) {
            if (empty($parametros["boleto"])) {
                if ($this->enviaBoleto($dados["id"], $cobrancas->dados_item["cliente_nome"], $emails, $conteudo, null, false) === true) {
                    
                }
            } else {
                if ($this->enviaBoleto($dados["id"], $cobrancas->dados_item["cliente_nome"], $emails, $conteudo, null, true) === true) {
                    
                }
            }
            sleep(1);
        }
    }

    function loginEmail($tipo = 'boleto') {
            
            $sql = "SELECT * FROM tab_email_smtp where ativo IS TRUE AND tipo LIKE '$tipo' LIMIT 1";
            $query = $this->bancodedados->query($sql);

            return $this->bancodedados->resultadoArray($query);
    }
    
    function enviaBoleto($id, $nome, $emails = null, $comentario = null, $assunto = null, $enviarAnexo = true) {

        require_once "_global/PHPMailer/PHPMailerAutoload.php";

        $mail = new PHPMailer();
        $mail->isSMTP();
        $dadosEmail = $this->loginEmail();
        $mail->Host =  $dadosEmail['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $dadosEmail['usuario'];
        $mail->Password = $dadosEmail['senha'];
        $mail->SMTPSecure = $dadosEmail['seguranca'];
        $mail->Port = $dadosEmail['porta'];
        $mail->From = $dadosEmail['usuario'];
        $mail->FromName = $dadosEmail['de'];

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->addReplyTo("boleto@oletv.net.br", "Olé TV");
        $mail->Subject = "Boleto Online";
        if ($assunto) {
            $mail->Subject = $assunto;
        }

        $mail->isHTML(true);

        for ($i = 0; $i < count($emails); $i++) {
            $mail->addAddress($emails[$i], $nome);
            //$mail->addBCC("rodrigo.oletvtelecom@gmail.com", "Rodrigo");
            //$mail->addBCC("carlos.schmitt.1978@gmail.com", "Carlos R.S.");
        }

        $mail->Body = $comentario;
        if ($enviarAnexo) {

            $boletos_pdf = controle::instanciaObjeto("cobrancas.boletos_pdf", $this->bancodedados);
            $boletos_pdf->gerarBoleto($id);
            $conteudo = $boletos_pdf->conteudo();
            $mail->AddStringAttachment($conteudo, "Boleto {$id}.pdf", "base64", "application/pdf");
        }

        if ($mail->send()) {

            return true;
        } else {

            return false;
        }

        return false;
    }
}
