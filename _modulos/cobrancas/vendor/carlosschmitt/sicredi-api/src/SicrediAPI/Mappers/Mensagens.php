<?php

namespace SicrediAPI\Mappers;

class Mensagens
{
    private $mensagens;

    public function __construct($messages)
    {
        $this->mensagens = $messages;
    }

    public function toArray()
    {
        if (empty($this->mensagens)) {
            return [];
        }

        return $this->mensagens;
    }
}
