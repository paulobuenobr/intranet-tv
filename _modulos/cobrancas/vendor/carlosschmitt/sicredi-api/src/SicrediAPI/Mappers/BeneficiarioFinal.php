<?php

namespace SicrediAPI\Mappers;

use SicrediAPI\Domain\Boleto\BeneficiarioFinal as BeneficiarioFinalDomain;

class BeneficiarioFinal
{
    private $beneficiarioFinal;

    public function __construct(BeneficiarioFinalDomain $beneficiarioFinal)
    {
        $this->beneficiarioFinal = $beneficiarioFinal;
    }

    private function getTipoPessoa()
    {
        return $this->beneficiarioFinal->getTipoPessoa() == BeneficiarioFinalDomain::PESSOA_FISICA ? 'PESSOA_FISICA' : 'PESSOA_JURIDICA';
    }

    public function toArray()
    {
        $beneficiarioFinal = [
            'documento' => $this->beneficiarioFinal->getDocumento(),
            'tipoPessoa' => $this->getTipoPessoa(),
            'nome' => $this->beneficiarioFinal->getNome(),
            'endereco' => $this->beneficiarioFinal->getLogradouro(),
            'cidade' => $this->beneficiarioFinal->getCidade(),
            'complemento' => $this->beneficiarioFinal->getComplemento(),
            'numero' => $this->beneficiarioFinal->getNumero(),
            'uf' => $this->beneficiarioFinal->getStatus(),
            'cep' => $this->beneficiarioFinal->getCep(),
            'telefone' => $this->beneficiarioFinal->getTelefone(),
            'email' => $this->beneficiarioFinal->getEmail()
        ];

        return array_filter($beneficiarioFinal, function ($value) {
            return !empty($value);
        });
    }
}
