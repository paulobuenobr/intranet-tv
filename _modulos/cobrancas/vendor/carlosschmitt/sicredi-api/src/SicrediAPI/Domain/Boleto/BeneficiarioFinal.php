<?php

namespace SicrediAPI\Domain\Boleto;

class BeneficiarioFinal {

    public const PESSOA_FISICA = 'PESSOA_FISICA';
    public const PESSOA_JURIDICA = 'PESSOA_JURIDICA';

    private $documento;
    private $tipoPessoa;
    private $nome;
    private $codigo;
    private $logradouro;
    private $cidade;
    private $complemento;
    private $numero;
    private $status;
    private $cep;
    private $telefone;
    private $email;

    public function __construct(string $nome, string $documento, string $tipoPessoa = null, string $codigo = null, string $logradouro = null, string $cidade = null, string $complemento = null, string $numero = null, string $state = null, string $cep = null, string $telefone = null, string $email = null) {

        if (!empty($tipoPessoa) && !in_array($tipoPessoa, [self::PESSOA_FISICA, self::PESSOA_JURIDICA])) {
            throw new \InvalidArgumentException("O tipo de pessoa deve ser 'PESSOA_FISICA' ou 'PESSOA_JURIDICA'");
        }

        if (!empty($state) && !\SicrediAPI\Helper::valid_brazilian_state($state)) {
            throw new \InvalidArgumentException("UF deve ser a abreviatura de um estado brasileiro");
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("E-mail deve ser válido");
        }

        $this->documento = \SicrediAPI\Helper::filter_only_numbers($documento);
        $this->tipoPessoa = $tipoPessoa;
        $this->nome = $nome;
        $this->codigo = $codigo;
        $this->logradouro = $logradouro;
        $this->cidade = $cidade;
        $this->complemento = $complemento;
        $this->numero = $numero;
        $this->status = $state;
        $this->cep = \SicrediAPI\Helper::filter_only_numbers($cep);
        $this->telefone = \SicrediAPI\Helper::filter_only_numbers($telefone);
        $this->email = $email;
    }

    public function getDocumento() {
        return $this->documento;
    }

    public function getTipoPessoa() {
        return $this->tipoPessoa;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getCodigo() {
        return $this->codigo;
    }

    public function getLogradouro() {
        return $this->logradouro;
    }

    public function getCidade() {
        return $this->cidade;
    }

    public function getComplemento() {
        return $this->complemento;
    }

    public function getNumero() {
        return $this->numero;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getCep() {
        return $this->cep;
    }

    public function getTelefone() {
        return $this->telefone;
    }

    public function getEmail() {
        return $this->email;
    }
}
