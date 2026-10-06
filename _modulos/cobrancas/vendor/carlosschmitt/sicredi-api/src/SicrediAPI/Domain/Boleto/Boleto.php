<?php

namespace SicrediAPI\Domain\Boleto;

use DateTime;

class Boleto {

    private $tipoCobranca;
    private $beneficiarioFinalCode;
    private $beneficiarioFinal;
    private $pagador;
    private $especieDocumento;
    private $nossoNumero;
    private $seuNumero;
    private $dataVencimento;
    private $dataDeEmissao;
    private $valor;
    private $carteira;
    private $status;
    private $descontoConfiguracao;
    private $desconto;
    private $jurosConfiguracao;
    private $juros;
    private $mensagens;
    private $informativo;
    private $informacaoDePagamento;
    private $liquidacao;
    private $erro;

    public function __construct(BeneficiarioFinal $beneficiarioFinal = null, Pagador $pagador, float $valor, string $tipoCobranca = null, int $beneficiarioFinalCode = null,
            string $especieDocumento = null, string $seuNumero, DateTime $dataVencimento, DateTime $dataDeEmissao = null, string $nossoNumero = null,
            DescontoConfiguracao $descontoConfiguracao = null, JurosConfiguracao $jurosConfiguracao = null, Mensagens $mensagens = null, Informativo $informativo = null,$erro = null
    ) {

        $this->tipoCobranca = $tipoCobranca;
        $this->beneficiarioFinalCode = $beneficiarioFinalCode;
        $this->beneficiarioFinal = $beneficiarioFinal;
        $this->pagador = $pagador;
        $this->especieDocumento = $especieDocumento;
        $this->nossoNumero = $nossoNumero;
        $this->seuNumero = $seuNumero;
        $this->dataVencimento = $dataVencimento;
        $this->valor = $valor;
        $this->dataDeEmissao = $dataDeEmissao;
        $this->erro = $erro;

        // If no issued date is provided, use today
        if (empty($dataDeEmissao)) {
            $dataDeEmissao = new DateTime();
        }

        // Due date must be greater or equal than today
        if ($dataVencimento < $dataDeEmissao) {
            throw new \InvalidArgumentException("A data de vencimento deve ser maior ou igual a hoje");
        }

        // Amount must be greater than 0
        if ($valor <= 0) {
            throw new \InvalidArgumentException("O valor deve ser maior que 0");
        }

        if (strlen($seuNumero) > 10) {
            throw new \InvalidArgumentException("Seu número deve ter no máximo 10 caracteres");
        }

        if (isset($beneficiarioFinal) && $beneficiarioFinal->getDocumento() === $pagador->getDocumento()) {
            throw new \InvalidArgumentException("O documento do beneficiário deve ser diferente do documento do beneficiário");
        }

        if (!empty($beneficiarioFinalCode) && strlen($beneficiarioFinalCode) !== 5) {
            throw new \InvalidArgumentException("O código do beneficiário deve ter 5 dígitos");
        }

        if (!empty($descontoConfiguracao)) {
            $this->setDesconto($descontoConfiguracao);
        }

        if (!empty($jurosConfiguracao)) {
            $this->setJuros($jurosConfiguracao);
        }

        if (!empty($mensagens)) {
            $this->setMensagens($mensagens);
        }

        if (!empty($informativo)) {
            $this->setInformativo($informativo);
        }
       
        if (!empty($parcelasCanceladas)) {
            $this->setParcelasCanceladas($parcelasCanceladas);
        }

        return $this;
    }

    public function getErro() {
        return $this->erro;
    }

    public function setErro($erro) {
        $this->erro = $erro;
    }

    public function getTipoCobranca() {
        return $this->tipoCobranca;
    }

    public function getBeneficiarioFinalCode() {
        return $this->beneficiarioFinalCode;
    }

    public function getBeneficiarioFinal() {
        return $this->beneficiarioFinal;
    }

    public function getPagador(): Pagador {
        return $this->pagador;
    }

    public function getEspecieDocumento() {
        return $this->especieDocumento;
    }

    public function getNossoNumero() {
        return $this->nossoNumero;
    }

    public function setNossoNumero($nossoNumero) {
        $this->nossoNumero = $nossoNumero;
    }

    public function getSeuNumero() {
        return $this->seuNumero;
    }

    public function getDataVencimento() {
        return $this->dataVencimento;
    }

    public function getValor() {
        return $this->valor;
    }

    public function getMensagens() {
        return $this->mensagens;
    }

    public function getInformativo() {
        return $this->informativo;
    }

    public function setDesconto(DescontoConfiguracao $descontoConfiguracao) {
        $descontoConfiguracao->validarDataVencimento($this->dataVencimento);
        $descontoConfiguracao->validarValor($this->valor);
        $this->desconto = $descontoConfiguracao;
    }

    public function getDesconto() {
        return $this->desconto;
    }

    public function setJuros(JurosConfiguracao $jurosConfiguracao) {
        $jurosConfiguracao->validarValorDoJuros($this->valor);
        $this->juros = $jurosConfiguracao;
    }

    public function getJuros() {
        return $this->juros;
    }

    public function setMensagens(Mensagens $mensagens) {
        $this->mensagens = $mensagens;
    }

    public function setInformativo(Informativo $informativoss) {
        $this->informativo = $informativoss;
    }

    public function getInformacaoDePagamento() {
        return $this->informacaoDePagamento;
    }

    public function setInformacaoDePagamento(InformacaoDePagamento $informacaoDePagamento) {
        $this->informacaoDePagamento = $informacaoDePagamento;
    }

    public function setSeuNumero($seuNumero) {
        $this->seuNumero = $seuNumero;
    }

    public function getDataDeEmissao() {
        return $this->dataDeEmissao;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setCarteira($carteira) {
        $this->carteira = $carteira;
    }

    public function getCarteira() {
        return $this->carteira;
    }

    public function getLiquidacao() {
        return $this->liquidacao;
    }

    public function setLiquidacao(Liquidacao $liquidacao) {
        $this->liquidacao = $liquidacao;
    }
}
