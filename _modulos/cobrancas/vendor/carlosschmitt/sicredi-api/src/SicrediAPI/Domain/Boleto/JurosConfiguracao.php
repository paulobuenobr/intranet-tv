<?php

namespace SicrediAPI\Domain\Boleto;

use DateTime;

class JurosConfiguracao {

    public const PERCENTUAL = 'PERCENTUAL';
    public const VALOR = 'VALOR';

    private $diasProtestoAuto;
    private $diasNegativacaoAuto;
    private $validadeAposVencimento;
    private $tipoDeJuros;
    private $juros;
    private $multa;

    public function __construct(string $tipodeJuros = null, float $juros = null, float $multa = null, int $diasProtestoAuto = null,
            int $diasNegativacaoAuto = null, int $validadeAposVencimento = null) {

        // Cannot use daysAutoProtestAfter with daysAutoNegativateAfter
        if (!empty($diasProtestoAuto) && !empty($diasNegativacaoAuto)) {
            throw new \InvalidArgumentException("Auto Protest e Auto Negativate não podem ser definidos ao mesmo tempo");
        }

        // $interestType must be percentage or value
        if (!empty($tipodeJuros) && !in_array($tipodeJuros, [self::PERCENTUAL, self::VALOR])) {
            throw new \InvalidArgumentException("O tipo de juros deve ser porcentagem ou valor");
        }

        // If interestType is set, interestAmount must be set
        if (!empty($tipodeJuros) && empty($juros)) {
            throw new \InvalidArgumentException("O valor dos juros deve ser definido quando o tipo de juros for definido");
        }

        // If interestAmount is set, interestType must be set
        if (empty($tipodeJuros) && !empty($juros)) {
            throw new \InvalidArgumentException("O tipo de juros deve ser definido quando o valor dos juros for definido");
        }

        // If penaltyAmount is set, it must be between 0 and 100
        if (!empty($multa) && ($multa < 0 || $multa > 100)) {
            throw new \InvalidArgumentException("O valor da multa deve estar entre 0 e 100");
        }

        $this->assertPositive($diasProtestoAuto, 'Dias de protesto automático', true);
        $this->assertPositive($diasNegativacaoAuto, 'Dias de negação automática', true);
        $this->assertPositive($validadeAposVencimento, 'Dias válidos após a data de vencimento', true);

        $this->assertPositive($juros, 'Montante de juros');
        $this->assertPositive($multa, 'Valor da multa');

        $this->diasProtestoAuto = $diasProtestoAuto;
        $this->diasNegativacaoAuto = $diasNegativacaoAuto;
        $this->validadeAposVencimento = $validadeAposVencimento;
        $this->tipoDeJuros = $tipodeJuros;
        $this->juros = $juros;
        $this->multa = $multa;
    }

    private function assertPositive($field, $fieldName, $forceInteger = false) {
        if (empty($field)) {
            return;
        }

        if ($field <= 0 || $field > 99) {
            throw new \InvalidArgumentException("{$fieldName} deve estar entre 1 e 99");
        }

        if ($forceInteger && !is_int($field)) {
            throw new \InvalidArgumentException("{$fieldName} deve ser um número inteiro");
        }
    }

    public function validarValorDoJuros(float $valor) {

        if ($this->tipoDeJuros == self::VALOR && $this->juros >= $valor) {
            throw new \InvalidArgumentException("O valor dos juros deve ser menor que o valor");
        }

        return true;
    }

    public function getDiasProtestoAuto() {
        return $this->diasProtestoAuto;
    }

    public function getDiasNegativacaoAuto() {
        return $this->diasNegativacaoAuto;
    }

    public function getValidadeAposVencimento() {
        return $this->validadeAposVencimento;
    }

    public function getTipoDeJuros() {
        return $this->tipoDeJuros;
    }

    public function getJuros() {
        return $this->juros;
    }

    public function getMulta() {
        return $this->multa;
    }
}
