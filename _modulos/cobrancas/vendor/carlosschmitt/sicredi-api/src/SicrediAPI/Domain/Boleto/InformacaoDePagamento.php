<?php

namespace SicrediAPI\Domain\Boleto;

class InformacaoDePagamento {

    private $txid;
    private $qrCode;
    private $linhaDigitavel;
    private $codigoBarras;
    private $nossoNumero;

    public function __construct($txid, $qrCode, $linhaDigitavel, $codigoBarras, $nossoNumero) {
        
        $this->txid = $txid;
        $this->qrCode = $qrCode;
        $this->linhaDigitavel = $linhaDigitavel;
        $this->codigoBarras = $codigoBarras;
        $this->nossoNumero = $nossoNumero;
    }

    public function getTxid() {
        return $this->txid;
    }

    public function getQrCode() {
        return $this->qrCode;
    }

    public function getLinhaDigitavel() {
        return $this->linhaDigitavel;
    }

    public function getCodigoBarras() {
        return $this->codigoBarras;
    }

    public function getNossoNumero() {
        return $this->nossoNumero;
    }

    public static function fromArray($data) {
        return new InformacaoDePagamento(
                $data['txid'] ?? $data['txId'],
                $data['qrCode'] ?? $data['codigoQrCode'],
                $data['linhaDigitavel'],
                $data['codigoBarras'],
                $data['nossoNumero']
        );
    }
}
