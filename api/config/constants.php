<?php

// Tipos de Clientes
    $this->conectaDB();
    $queryTpCliente = $this->db->query("SELECT * FROM tab_clientes_tipos");
    $tiposClientes = array();
    while (($v = $this->db->resultadoArray($queryTpCliente)) != false) {
        $tiposClientes[$v['clt_id']] = $v['clt_nome'];
    }
    define('TIPOS_CLIENTE', $tiposClientes);
    
    
    
