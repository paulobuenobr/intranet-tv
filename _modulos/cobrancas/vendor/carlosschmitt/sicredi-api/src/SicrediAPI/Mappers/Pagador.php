<?php

namespace SicrediAPI\Mappers;

use SicrediAPI\Domain\Boleto\Pagador as PagadorDomain;

class Pagador
{
    private $pagador;

    public function __construct(PagadorDomain $pagador)
    {
        $this->pagador = $pagador;
    }

    private function getTipoDePessoa()
    {
        return $this->pagador->getTipoPessoa() == PagadorDomain::PESSOA_FISICA ? 'PESSOA_FISICA' : 'PESSOA_JURIDICA';
    }

    public function toArray()
    {
        $payee = [
            'nome' => $this->pagador->getNome(),
            'documento' => $this->pagador->getDocumento(),
            'tipoPessoa' => $this->getTipoDePessoa(),
            'endereco' => $this->pagador->getLogradouro(),
            'cidade' => $this->pagador->getCidade(),
            'uf' => $this->pagador->getStatus(),
            'cep' => $this->pagador->getCep(),
            'telefone' => $this->pagador->getTelefone(),
            'email' => $this->pagador->getEmail()
        ];

        return array_filter($payee, function ($value) {
            return !empty($value);
        });
    }
}
