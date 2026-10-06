-- phpMyAdmin SQL Dump
-- version 4.4.15.10
-- https://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: 18-Jul-2023 às 14:12
-- Versão do servidor: 10.3.6-MariaDB
-- PHP Version: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tvintranet_demo`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `intranet_stopwords`
--

CREATE TABLE IF NOT EXISTS `intranet_stopwords` (
  `value` varchar(18) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `log_integracao`
--

CREATE TABLE IF NOT EXISTS `log_integracao` (
  `log_id` mediumint(8) unsigned NOT NULL,
  `modulo` char(20) DEFAULT NULL,
  `acao` char(20) DEFAULT NULL,
  `id` char(10) DEFAULT NULL,
  `usuario` char(10) DEFAULT NULL,
  `datahora` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_ass_cor_estacontido`
--

CREATE TABLE IF NOT EXISTS `rel_ass_cor_estacontido` (
  `ass_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cor_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_cam_cac_contem`
--

CREATE TABLE IF NOT EXISTS `rel_cam_cac_contem` (
  `cam_id` mediumint(8) unsigned NOT NULL,
  `cac_id` mediumint(8) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_cha_caa_contem`
--

CREATE TABLE IF NOT EXISTS `rel_cha_caa_contem` (
  `cha_id` mediumint(8) unsigned NOT NULL,
  `caa_id` smallint(5) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_cha_usu_ehacompanhado`
--

CREATE TABLE IF NOT EXISTS `rel_cha_usu_ehacompanhado` (
  `cha_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_cha_usu_ehfavorito`
--

CREATE TABLE IF NOT EXISTS `rel_cha_usu_ehfavorito` (
  `cha_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_clo_cid_contem`
--

CREATE TABLE IF NOT EXISTS `rel_clo_cid_contem` (
  `clo_id` int(10) unsigned NOT NULL,
  `cid_id` smallint(5) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_coa_coe_estacontido`
--

CREATE TABLE IF NOT EXISTS `rel_coa_coe_estacontido` (
  `coa_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `coe_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_cof_clo_disponivel`
--

CREATE TABLE IF NOT EXISTS `rel_cof_clo_disponivel` (
  `cof_id` tinyint(3) unsigned NOT NULL,
  `clo_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_con_pla_tempermissao`
--

CREATE TABLE IF NOT EXISTS `rel_con_pla_tempermissao` (
  `con_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `pla_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rcp_permissao` tinyint(1) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_cov_cod_estacontido`
--

CREATE TABLE IF NOT EXISTS `rel_cov_cod_estacontido` (
  `cov_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cod_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_dec_cor_estacontido`
--

CREATE TABLE IF NOT EXISTS `rel_dec_cor_estacontido` (
  `dec_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cor_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_equ_cor_comodato`
--

CREATE TABLE IF NOT EXISTS `rel_equ_cor_comodato` (
  `equ_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cor_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_equ_pla_comodato`
--

CREATE TABLE IF NOT EXISTS `rel_equ_pla_comodato` (
  `equ_id` tinyint(3) unsigned NOT NULL,
  `pla_id` smallint(5) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_equ_pls_default`
--

CREATE TABLE IF NOT EXISTS `rel_equ_pls_default` (
  `equ_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `pls_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_equ_pls_disponivel`
--

CREATE TABLE IF NOT EXISTS `rel_equ_pls_disponivel` (
  `equ_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `pls_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_esf_esi_fornece`
--

CREATE TABLE IF NOT EXISTS `rel_esf_esi_fornece` (
  `esf_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `esi_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_fop_cor_estacontido`
--

CREATE TABLE IF NOT EXISTS `rel_fop_cor_estacontido` (
  `fop_id` smallint(5) unsigned NOT NULL,
  `cor_id` mediumint(8) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_gru_aco_tempermissao`
--

CREATE TABLE IF NOT EXISTS `rel_gru_aco_tempermissao` (
  `gru_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `aco_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_saj_ass_contem`
--

CREATE TABLE IF NOT EXISTS `rel_saj_ass_contem` (
  `saj_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ass_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_saj_cau_contem`
--

CREATE TABLE IF NOT EXISTS `rel_saj_cau_contem` (
  `saj_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cau_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_saj_cip_contem`
--

CREATE TABLE IF NOT EXISTS `rel_saj_cip_contem` (
  `saj_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cip_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_saj_clb_contem`
--

CREATE TABLE IF NOT EXISTS `rel_saj_clb_contem` (
  `saj_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `clb_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_saj_cle_contem`
--

CREATE TABLE IF NOT EXISTS `rel_saj_cle_contem` (
  `saj_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cle_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_saj_clh_contem`
--

CREATE TABLE IF NOT EXISTS `rel_saj_clh_contem` (
  `saj_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `clh_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_saj_clp_contem`
--

CREATE TABLE IF NOT EXISTS `rel_saj_clp_contem` (
  `saj_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `clp_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_tar_usu_aguardaciencia`
--

CREATE TABLE IF NOT EXISTS `rel_tar_usu_aguardaciencia` (
  `tar_id` int(11) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_tat_ors_orf_ehautoaprovada`
--

CREATE TABLE IF NOT EXISTS `rel_tat_ors_orf_ehautoaprovada` (
  `tat_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ors_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `orf_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_usu_esg_fazparte`
--

CREATE TABLE IF NOT EXISTS `rel_usu_esg_fazparte` (
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `esg_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_usu_gru_fazparte`
--

CREATE TABLE IF NOT EXISTS `rel_usu_gru_fazparte` (
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `gru_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rel_usu_orf_ors_exerce`
--

CREATE TABLE IF NOT EXISTS `rel_usu_orf_ors_exerce` (
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `ors_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `orf_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_acoes`
--

CREATE TABLE IF NOT EXISTS `tab_acoes` (
  `aco_id` smallint(5) unsigned NOT NULL,
  `aco_nome` char(40) NOT NULL DEFAULT '',
  `aco_descricao` char(45) NOT NULL DEFAULT '',
  `mod_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `act_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_acoes_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_acoes_tipos` (
  `act_id` tinyint(3) unsigned NOT NULL,
  `act_nome` char(6) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_ajustes_financeiros`
--

CREATE TABLE IF NOT EXISTS `tab_ajustes_financeiros` (
  `ajf_id` mediumint(8) unsigned NOT NULL,
  `ajf_pai` mediumint(8) unsigned DEFAULT NULL,
  `ajf_competencia_inicio` date NOT NULL DEFAULT '0000-00-00',
  `ajf_competencia_termino` date DEFAULT NULL,
  `ajf_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ajf_parcelas` tinyint(3) unsigned DEFAULT NULL,
  `ajf_valor` decimal(10,2) unsigned NOT NULL DEFAULT 0.00,
  `ajf_valor_total` mediumint(8) unsigned DEFAULT NULL,
  `ajf_valor_tipo` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `ajf_nf_numero` char(15) DEFAULT NULL,
  `ajf_nf_emissao` date DEFAULT NULL,
  `ajf_nf_valor` mediumint(8) unsigned DEFAULT NULL,
  `con_id` smallint(5) unsigned DEFAULT NULL,
  `cli_id` mediumint(8) unsigned DEFAULT NULL,
  `ass_id` mediumint(8) unsigned DEFAULT NULL,
  `cor_id` mediumint(8) unsigned DEFAULT NULL,
  `afc_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `aft_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `afs_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_ajustes_financeiros_categorias`
--

CREATE TABLE IF NOT EXISTS `tab_ajustes_financeiros_categorias` (
  `afc_id` tinyint(3) unsigned NOT NULL,
  `afc_nome` char(42) NOT NULL DEFAULT '',
  `afc_acrescimo` enum('0','1') NOT NULL DEFAULT '0',
  `afc_decrescimo` enum('0','1') NOT NULL DEFAULT '0',
  `afc_bonificacao` enum('0','1') NOT NULL DEFAULT '0',
  `afc_solicitar_nf` enum('0','1') NOT NULL DEFAULT '0',
  `afc_disponivel` enum('0','1') NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_ajustes_financeiros_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_ajustes_financeiros_comentarios` (
  `afm_id` mediumint(8) unsigned NOT NULL,
  `afm_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `afm_conteudo` text DEFAULT NULL,
  `ajf_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_ajustes_financeiros_status`
--

CREATE TABLE IF NOT EXISTS `tab_ajustes_financeiros_status` (
  `afs_id` tinyint(3) unsigned NOT NULL,
  `afs_nome` char(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_ajustes_financeiros_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_ajustes_financeiros_tipos` (
  `aft_id` tinyint(3) unsigned NOT NULL,
  `aft_nome` char(12) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_assinaturas`
--

CREATE TABLE IF NOT EXISTS `tab_assinaturas` (
  `ass_id` mediumint(8) unsigned NOT NULL,
  `ass_pai` mediumint(8) unsigned DEFAULT NULL,
  `ass_data_cadastro` date DEFAULT NULL,
  `ass_data_fechamento` date DEFAULT NULL,
  `ass_data_confirmacao` date DEFAULT NULL,
  `ass_data_ativacao` date DEFAULT NULL,
  `ass_data_cancelamento` date DEFAULT NULL,
  `ass_instalacao` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ass_mensalidade` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ass_multa` mediumint(8) unsigned DEFAULT NULL,
  `ass_vigencia` tinyint(3) unsigned DEFAULT NULL,
  `ass_parametros` text DEFAULT NULL,
  `ass_migrada` mediumint(8) unsigned DEFAULT NULL,
  `cli_id` mediumint(8) unsigned DEFAULT NULL,
  `pla_id` smallint(5) unsigned DEFAULT NULL,
  `pli_id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `ast_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_assinaturas_status`
--

CREATE TABLE IF NOT EXISTS `tab_assinaturas_status` (
  `ast_id` tinyint(3) unsigned NOT NULL,
  `ast_nome` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_assinaturas_vinculos`
--

CREATE TABLE IF NOT EXISTS `tab_assinaturas_vinculos` (
  `asv_id` mediumint(8) unsigned NOT NULL,
  `ass_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ass_filha` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `pvt_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_atividades`
--

CREATE TABLE IF NOT EXISTS `tab_atividades` (
  `ati_id` int(10) unsigned NOT NULL,
  `ati_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ati_ip` char(15) NOT NULL DEFAULT '',
  `ati_parametro` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `aco_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_bairros`
--

CREATE TABLE IF NOT EXISTS `tab_bairros` (
  `bai_id` smallint(5) unsigned NOT NULL,
  `bai_nome` char(90) NOT NULL DEFAULT '',
  `bai_cep_geral` int(8) unsigned zerofill DEFAULT NULL,
  `cid_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_campanhas`
--

CREATE TABLE IF NOT EXISTS `tab_campanhas` (
  `cam_id` mediumint(8) unsigned NOT NULL,
  `cam_nome` char(45) DEFAULT NULL,
  `cam_data_hora_inicio` datetime DEFAULT NULL,
  `cam_data_hora_termino` datetime DEFAULT NULL,
  `cat_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cas_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_campanhas_contatos`
--

CREATE TABLE IF NOT EXISTS `tab_campanhas_contatos` (
  `cac_id` mediumint(8) unsigned NOT NULL,
  `cac_nome` char(200) DEFAULT NULL,
  `cac_cpf_cnpj` char(20) DEFAULT NULL,
  `cep_id` int(10) unsigned DEFAULT NULL,
  `log_id` mediumint(8) unsigned DEFAULT NULL,
  `bai_id` smallint(5) unsigned DEFAULT NULL,
  `cid_id` smallint(5) unsigned DEFAULT NULL,
  `est_id` tinyint(3) unsigned DEFAULT NULL,
  `cli_id` mediumint(8) unsigned DEFAULT NULL,
  `ccs_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_campanhas_contatos_status`
--

CREATE TABLE IF NOT EXISTS `tab_campanhas_contatos_status` (
  `ccs_id` tinyint(3) unsigned NOT NULL,
  `ccs_nome` char(45) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_campanhas_contatos_telefones`
--

CREATE TABLE IF NOT EXISTS `tab_campanhas_contatos_telefones` (
  `cct_id` mediumint(8) unsigned NOT NULL,
  `cct_numero` char(10) NOT NULL DEFAULT '',
  `cac_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_campanhas_operacoes`
--

CREATE TABLE IF NOT EXISTS `tab_campanhas_operacoes` (
  `cao_id` mediumint(8) unsigned NOT NULL,
  `cao_inicio` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cao_termino` datetime DEFAULT NULL,
  `cam_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_campanhas_operacoes_contatos`
--

CREATE TABLE IF NOT EXISTS `tab_campanhas_operacoes_contatos` (
  `coc_id` int(11) NOT NULL,
  `coc_data_hora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cod_duracao` smallint(5) unsigned NOT NULL DEFAULT 0,
  `cao_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cac_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cct_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cos_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_campanhas_operacoes_contatos_situacoes`
--

CREATE TABLE IF NOT EXISTS `tab_campanhas_operacoes_contatos_situacoes` (
  `cos_id` tinyint(3) unsigned NOT NULL,
  `cos_nome` char(45) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_campanhas_status`
--

CREATE TABLE IF NOT EXISTS `tab_campanhas_status` (
  `cas_id` tinyint(3) unsigned NOT NULL,
  `cas_nome` char(9) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_campanhas_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_campanhas_tipos` (
  `cat_id` tinyint(3) unsigned NOT NULL,
  `cat_nome` char(19) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cancelamentos`
--

CREATE TABLE IF NOT EXISTS `tab_cancelamentos` (
  `can_id` mediumint(8) unsigned NOT NULL,
  `can_inicio` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `can_termino` datetime DEFAULT NULL,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cor_id` mediumint(8) unsigned DEFAULT NULL,
  `cae_id` mediumint(8) unsigned DEFAULT NULL,
  `cao_id` tinyint(3) unsigned DEFAULT NULL,
  `cam_id` tinyint(3) unsigned DEFAULT NULL,
  `cas_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cancelamentos_etapas`
--

CREATE TABLE IF NOT EXISTS `tab_cancelamentos_etapas` (
  `cae_id` mediumint(8) unsigned NOT NULL,
  `cae_inicio` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cae_termino` datetime DEFAULT NULL,
  `cae_parametros` text DEFAULT NULL,
  `can_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `cet_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cen_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ces_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cancelamentos_etapas_niveis`
--

CREATE TABLE IF NOT EXISTS `tab_cancelamentos_etapas_niveis` (
  `cen_id` tinyint(3) unsigned NOT NULL,
  `cen_nome` char(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cancelamentos_etapas_status`
--

CREATE TABLE IF NOT EXISTS `tab_cancelamentos_etapas_status` (
  `ces_id` tinyint(3) unsigned NOT NULL,
  `ces_nome` char(12) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cancelamentos_etapas_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_cancelamentos_etapas_tipos` (
  `cet_id` tinyint(3) unsigned NOT NULL,
  `cet_nome` char(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cancelamentos_motivos`
--

CREATE TABLE IF NOT EXISTS `tab_cancelamentos_motivos` (
  `cam_id` tinyint(3) unsigned NOT NULL,
  `cam_nome` char(60) NOT NULL DEFAULT '',
  `cms_id` tinyint(3) unsigned NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cancelamentos_motivos_status`
--

CREATE TABLE IF NOT EXISTS `tab_cancelamentos_motivos_status` (
  `cms_id` tinyint(3) unsigned NOT NULL,
  `cms_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cancelamentos_origens`
--

CREATE TABLE IF NOT EXISTS `tab_cancelamentos_origens` (
  `cao_id` tinyint(3) unsigned NOT NULL,
  `cao_nome` char(35) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cancelamentos_status`
--

CREATE TABLE IF NOT EXISTS `tab_cancelamentos_status` (
  `cas_id` tinyint(3) unsigned NOT NULL,
  `cas_nome` char(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_central_telefonica_agregados`
--

CREATE TABLE IF NOT EXISTS `tab_central_telefonica_agregados` (
  `ctl_id` int(10) unsigned NOT NULL,
  `ctl_data` date NOT NULL DEFAULT '0000-00-00',
  `ctl_mes` char(7) NOT NULL DEFAULT '',
  `ctl_dia_semana` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ctl_hora` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ctl_setor` char(14) NOT NULL DEFAULT '',
  `ctl_operador` char(10) NOT NULL DEFAULT '',
  `ctl_ligacoes_efetuadas_atendidas` smallint(5) unsigned NOT NULL DEFAULT 0,
  `ctl_ligacoes_efetuadas_perdidas` smallint(5) unsigned NOT NULL DEFAULT 0,
  `ctl_ligacoes_efetuadas_recuperadas` smallint(5) unsigned NOT NULL DEFAULT 0,
  `ctl_ligacoes_recebidas_atendidas` smallint(5) unsigned NOT NULL DEFAULT 0,
  `ctl_ligacoes_recebidas_perdidas` smallint(5) unsigned NOT NULL DEFAULT 0,
  `ctl_ligacoes_recebidas_recuperadas` smallint(5) unsigned NOT NULL DEFAULT 0,
  `ctl_total_duracao_recebidas` int(11) unsigned NOT NULL DEFAULT 0,
  `ctl_total_duracao_efetuadas` int(11) unsigned NOT NULL DEFAULT 0,
  `ctl_total_espera_recebidas` int(11) unsigned NOT NULL DEFAULT 0,
  `ctl_tipo` enum('e','i') DEFAULT NULL COMMENT 'e = externa; i = interna',
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_central_telefonica_ligacoes`
--

CREATE TABLE IF NOT EXISTS `tab_central_telefonica_ligacoes` (
  `ctl_id` int(10) unsigned NOT NULL,
  `ctl_data_hora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ctl_fila` char(30) DEFAULT NULL,
  `ctl_origem` char(25) DEFAULT NULL,
  `ctl_destino` char(25) DEFAULT NULL,
  `ctl_arquivo_gravacao` char(32) DEFAULT NULL,
  `ctl_duracao` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ctl_espera` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cli_id` mediumint(8) unsigned DEFAULT NULL,
  `usu_origem_id` smallint(5) unsigned DEFAULT NULL,
  `usu_destino_id` smallint(5) unsigned DEFAULT NULL,
  `cts_origem_id` tinyint(3) unsigned DEFAULT NULL,
  `cts_destino_id` tinyint(3) unsigned DEFAULT NULL,
  `clt_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cls_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_central_telefonica_ligacoes_situacoes`
--

CREATE TABLE IF NOT EXISTS `tab_central_telefonica_ligacoes_situacoes` (
  `cls_id` tinyint(3) unsigned NOT NULL,
  `cls_nome` char(25) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_central_telefonica_ligacoes_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_central_telefonica_ligacoes_tipos` (
  `clt_id` tinyint(3) unsigned NOT NULL,
  `clt_nome` char(8) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_central_telefonica_setores`
--

CREATE TABLE IF NOT EXISTS `tab_central_telefonica_setores` (
  `cts_id` tinyint(3) unsigned NOT NULL,
  `cts_nome` char(14) NOT NULL DEFAULT '',
  `cts_ramais` char(60) NOT NULL DEFAULT '',
  `cts_agentes` char(60) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_ceps`
--

CREATE TABLE IF NOT EXISTS `tab_ceps` (
  `cep_id` int(8) unsigned zerofill NOT NULL,
  `log_id` mediumint(8) unsigned DEFAULT NULL,
  `bai_id` smallint(5) unsigned DEFAULT NULL,
  `cid_id` smallint(5) unsigned DEFAULT NULL,
  `est_id` tinyint(3) unsigned DEFAULT NULL,
  `cep_exportacao` int(11) NOT NULL DEFAULT 0,
  `cep_zona` char(1) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Acionadores `tab_ceps`
--
DELIMITER $$
CREATE TRIGGER `tab_ceps_update` AFTER UPDATE ON `tab_ceps`
 FOR EACH ROW BEGIN
      IF OLD.est_id != NEW.est_id AND NEW.est_id IS NOT NULL THEN
        UPDATE tab_clientes SET tab_clientes.est_id = NEW.est_id WHERE tab_clientes.cep_id = NEW.cep_id;
        UPDATE tab_clientes_cobranca_dados SET tab_clientes_cobranca_dados.est_id = NEW.est_id WHERE tab_clientes_cobranca_dados.cep_id = NEW.cep_id;
        UPDATE tab_condominios SET tab_condominios.est_id = NEW.est_id WHERE tab_condominios.cep_id = NEW.cep_id;
        UPDATE tab_condominios_blocos SET tab_condominios_blocos.est_id = NEW.est_id WHERE tab_condominios_blocos.cep_id = NEW.cep_id;
      END IF; 
      IF OLD.cid_id != NEW.cid_id AND NEW.cid_id IS NOT NULL THEN
        UPDATE tab_clientes SET tab_clientes.cid_id = NEW.cid_id WHERE tab_clientes.cep_id = NEW.cep_id;
        UPDATE tab_clientes_cobranca_dados SET tab_clientes_cobranca_dados.cid_id = NEW.cid_id WHERE tab_clientes_cobranca_dados.cep_id = NEW.cep_id;
        UPDATE tab_condominios SET tab_condominios.cid_id = NEW.cid_id WHERE tab_condominios.cep_id = NEW.cep_id;
        UPDATE tab_condominios_blocos SET tab_condominios_blocos.cid_id = NEW.cid_id WHERE tab_condominios_blocos.cep_id = NEW.cep_id;
      END IF; 
      IF OLD.bai_id != NEW.bai_id AND NEW.bai_id IS NOT NULL THEN
        UPDATE tab_clientes SET tab_clientes.bai_id = NEW.bai_id WHERE tab_clientes.cep_id = NEW.cep_id;
        UPDATE tab_clientes_cobranca_dados SET tab_clientes_cobranca_dados.bai_id = NEW.bai_id WHERE tab_clientes_cobranca_dados.cep_id = NEW.cep_id;
        UPDATE tab_condominios SET tab_condominios.bai_id = NEW.bai_id WHERE tab_condominios.cep_id = NEW.cep_id;
        UPDATE tab_condominios_blocos SET tab_condominios_blocos.bai_id = NEW.bai_id WHERE tab_condominios_blocos.cep_id = NEW.cep_id;
      END IF; 
      IF OLD.log_id != NEW.log_id AND NEW.log_id IS NOT NULL THEN
        UPDATE tab_clientes SET tab_clientes.log_id = NEW.log_id WHERE tab_clientes.cep_id = NEW.cep_id;
        UPDATE tab_clientes_cobranca_dados SET tab_clientes_cobranca_dados.log_id = NEW.log_id WHERE tab_clientes_cobranca_dados.cep_id = NEW.cep_id;
        UPDATE tab_condominios SET tab_condominios.log_id = NEW.log_id WHERE tab_condominios.cep_id = NEW.cep_id;
        UPDATE tab_condominios_blocos SET tab_condominios_blocos.log_id = NEW.log_id WHERE tab_condominios_blocos.cep_id = NEW.cep_id;
      END IF; 
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_chamados`
--

CREATE TABLE IF NOT EXISTS `tab_chamados` (
  `cha_id` mediumint(8) unsigned NOT NULL,
  `cha_pai` mediumint(8) unsigned DEFAULT NULL,
  `cha_codigo` char(16) DEFAULT NULL,
  `cha_solicitante` char(80) DEFAULT NULL,
  `cha_datahora_abertura` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cha_datahora_fechamento` datetime DEFAULT NULL,
  `cha_datahora_encaminhamento` datetime DEFAULT NULL,
  `cha_datahora_retorno` datetime DEFAULT NULL,
  `cha_aguardando_retorno` tinyint(3) NOT NULL DEFAULT 0,
  `cha_etapa_atual` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cha_andamento` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cha_visualizado` tinyint(3) NOT NULL DEFAULT 0,
  `ors_id` tinyint(3) unsigned DEFAULT NULL,
  `orf_id` tinyint(3) unsigned DEFAULT NULL,
  `usu_id` smallint(5) unsigned DEFAULT NULL,
  `con_id` smallint(5) unsigned DEFAULT NULL,
  `cli_id` mediumint(8) unsigned DEFAULT NULL,
  `vis_id` mediumint(8) unsigned DEFAULT NULL,
  `chc_original` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `chc_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `chp_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `chi_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `chs_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_chamados_acoes`
--

CREATE TABLE IF NOT EXISTS `tab_chamados_acoes` (
  `caa_id` smallint(5) unsigned NOT NULL,
  `caa_nome` char(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_chamados_categorias`
--

CREATE TABLE IF NOT EXISTS `tab_chamados_categorias` (
  `chc_id` mediumint(8) unsigned NOT NULL,
  `chc_pai` mediumint(8) unsigned DEFAULT NULL,
  `chc_nome` char(60) NOT NULL DEFAULT '',
  `chc_restrita` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `vit_id` tinyint(3) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_chamados_categorias_etapas`
--

CREATE TABLE IF NOT EXISTS `tab_chamados_categorias_etapas` (
  `cce_id` smallint(5) unsigned NOT NULL,
  `cce_obrigatoria` tinyint(1) unsigned DEFAULT NULL,
  `cce_multipla` tinyint(1) unsigned DEFAULT NULL,
  `cce_ordem` tinyint(1) unsigned DEFAULT NULL,
  `ors_id` tinyint(3) unsigned DEFAULT NULL,
  `orf_id` tinyint(3) unsigned DEFAULT NULL,
  `cet_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `chc_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_chamados_etapas`
--

CREATE TABLE IF NOT EXISTS `tab_chamados_etapas` (
  `che_id` int(10) unsigned NOT NULL,
  `che_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `che_conteudo` text DEFAULT NULL,
  `che_lida` tinyint(3) NOT NULL DEFAULT 0,
  `cha_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL,
  `cen_id` tinyint(3) unsigned DEFAULT NULL,
  `cet_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `che_pai` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_chamados_etapas_niveis_stress`
--

CREATE TABLE IF NOT EXISTS `tab_chamados_etapas_niveis_stress` (
  `cen_id` tinyint(3) unsigned NOT NULL,
  `cen_nome` char(19) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_chamados_etapas_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_chamados_etapas_tipos` (
  `cet_id` tinyint(3) unsigned NOT NULL,
  `cet_nome` char(42) NOT NULL DEFAULT '',
  `cet_sistema` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `cet_interno` tinyint(3) unsigned NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_chamados_prioridades`
--

CREATE TABLE IF NOT EXISTS `tab_chamados_prioridades` (
  `chp_id` tinyint(3) unsigned NOT NULL,
  `chp_nome` char(11) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_chamados_roteiros`
--

CREATE TABLE IF NOT EXISTS `tab_chamados_roteiros` (
  `chr_id` tinyint(3) unsigned NOT NULL,
  `chr_nome` char(20) NOT NULL DEFAULT '',
  `cre_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_chamados_roteiros_etapas`
--

CREATE TABLE IF NOT EXISTS `tab_chamados_roteiros_etapas` (
  `cre_id` smallint(5) unsigned NOT NULL,
  `cre_procedimento` char(50) NOT NULL DEFAULT '',
  `cre_parametros` text DEFAULT NULL,
  `cre_sim_id` smallint(5) unsigned DEFAULT NULL,
  `cre_nao_id` smallint(5) unsigned DEFAULT NULL,
  `chr_id` tinyint(3) unsigned NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_chamados_situacoes`
--

CREATE TABLE IF NOT EXISTS `tab_chamados_situacoes` (
  `chi_id` tinyint(3) unsigned NOT NULL,
  `chi_nome` char(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_chamados_status`
--

CREATE TABLE IF NOT EXISTS `tab_chamados_status` (
  `chs_id` tinyint(3) unsigned NOT NULL,
  `chs_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_chamados_visualizacoes`
--

CREATE TABLE IF NOT EXISTS `tab_chamados_visualizacoes` (
  `chv_id` mediumint(8) unsigned NOT NULL,
  `chv_instancia` char(32) NOT NULL DEFAULT '',
  `chv_data_visualizacao_inicio` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `chv_data_visualizacao_ultima` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cha_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cidades`
--

CREATE TABLE IF NOT EXISTS `tab_cidades` (
  `cid_id` smallint(5) unsigned NOT NULL,
  `cid_nome` char(64) NOT NULL DEFAULT '',
  `cid_cep_geral` int(8) unsigned zerofill DEFAULT NULL,
  `est_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `clo_id` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_classes_fiscais`
--

CREATE TABLE IF NOT EXISTS `tab_classes_fiscais` (
  `clf_id` tinyint(3) unsigned NOT NULL,
  `clf_taxa` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pla_id` smallint(8) unsigned DEFAULT NULL,
  `pls_id` tinyint(3) unsigned DEFAULT NULL,
  `afc_id` tinyint(3) unsigned DEFAULT NULL,
  `cft_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_classes_fiscais_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_classes_fiscais_tipos` (
  `cft_id` tinyint(3) unsigned NOT NULL,
  `cft_sigla` char(3) NOT NULL DEFAULT '',
  `cft_descricao` char(60) NOT NULL DEFAULT '',
  `cft_tipo` enum('SCM','SVA') NOT NULL DEFAULT 'SCM',
  `cft_tributacao` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `cft_icms` decimal(10,2) unsigned NOT NULL DEFAULT 0.00,
  `cft_codigo` char(4) NOT NULL DEFAULT '',
  `cft_natureza_pf_sc` char(8) NOT NULL DEFAULT '',
  `cft_natureza_pj_sc` char(8) NOT NULL DEFAULT '',
  `cft_natureza_pf_outros` char(8) NOT NULL DEFAULT '',
  `cft_natureza_pj_outros` char(8) NOT NULL DEFAULT '',
  `emp_id` int(11) DEFAULT NULL,
  `cft_gerar_nota` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes`
--

CREATE TABLE IF NOT EXISTS `tab_clientes` (
  `cli_id` mediumint(8) unsigned NOT NULL,
  `cli_pai` mediumint(8) unsigned DEFAULT NULL,
  `cli_nome` char(100) NOT NULL DEFAULT '',
  `cli_nome_fantasia` char(80) DEFAULT NULL,
  `cli_cpf_cnpj` char(20) DEFAULT NULL,
  `cli_inscricao_estadual` char(15) DEFAULT NULL,
  `cli_nascimento` date DEFAULT NULL,
  `cli_complemento` char(105) DEFAULT NULL,
  `cli_ponto_referencia` char(105) NOT NULL DEFAULT '',
  `cli_numero` mediumint(8) unsigned DEFAULT NULL,
  `cli_contato` char(60) DEFAULT NULL,
  `cli_ccd_debito_operacao` char(3) DEFAULT NULL,
  `cli_ccd_debito_agencia` char(15) DEFAULT NULL,
  `cli_ccd_debito_conta` char(15) DEFAULT NULL,
  `cli_ccd_debito_codigo` char(25) DEFAULT NULL,
  `cli_ccd_vencimento` tinyint(3) unsigned DEFAULT 10,
  `cli_bloqueio_dias` tinyint(3) unsigned DEFAULT NULL,
  `cli_bloqueio_dias_customizado` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cli_copia_cobranca` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cli_data_cadastro` datetime DEFAULT NULL,
  `cli_comentarios` text DEFAULT NULL,
  `cof_id` tinyint(3) unsigned DEFAULT NULL,
  `cob_id` smallint(5) unsigned DEFAULT NULL,
  `con_id` smallint(5) unsigned DEFAULT NULL,
  `cep_id` int(11) unsigned DEFAULT NULL,
  `log_id` mediumint(8) unsigned DEFAULT NULL,
  `bai_id` smallint(5) unsigned DEFAULT NULL,
  `cid_id` smallint(5) unsigned DEFAULT NULL,
  `est_id` tinyint(3) unsigned DEFAULT NULL,
  `cld_id` tinyint(3) unsigned DEFAULT NULL,
  `clu_id` tinyint(3) unsigned DEFAULT NULL,
  `clt_id` tinyint(3) unsigned DEFAULT NULL,
  `clo_id` int(10) unsigned DEFAULT NULL,
  `coo_id` tinyint(3) unsigned DEFAULT NULL,
  `usu_id` smallint(5) unsigned DEFAULT NULL,
  `cls_id` tinyint(3) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_autenticacoes`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_autenticacoes` (
  `cau_id` mediumint(8) unsigned NOT NULL,
  `cau_usuario` char(50) NOT NULL DEFAULT '',
  `cau_senha` char(32) NOT NULL DEFAULT '',
  `cau_endereco_ip` char(15) DEFAULT NULL,
  `cau_endereco_mac` char(17) DEFAULT NULL,
  `cau_velocidade_download` int(10) unsigned NOT NULL DEFAULT 0,
  `cau_velocidade_upload` int(10) unsigned NOT NULL DEFAULT 0,
  `cau_ajuste_download` int(10) unsigned DEFAULT NULL,
  `cau_ajuste_upload` int(10) unsigned DEFAULT NULL,
  `cau_pool` enum('pppoe','liberados') NOT NULL DEFAULT 'pppoe',
  `cau_publicado` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cau_data_alteracao_senha` datetime DEFAULT NULL,
  `cau_data_publicacao` datetime DEFAULT NULL,
  `cau_data_alteracao` date DEFAULT NULL,
  `cau_aguardando_verificacao` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cau_ajustado` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cau_autenticador` char(12) DEFAULT NULL,
  `cau_equipamento_ip` char(15) DEFAULT NULL,
  `cau_equipamento_modelo` char(20) DEFAULT NULL,
  `cau_equipamento_serie` char(20) DEFAULT NULL,
  `cau_equipamento_mac` char(17) DEFAULT NULL,
  `cau_equipamento_ssid` char(32) DEFAULT NULL,
  `cau_concentrador_ip` char(15) DEFAULT NULL,
  `cau_concentrador_nome` char(32) DEFAULT NULL,
  `cau_concentrador_atualizacao` datetime DEFAULT NULL,
  `cau_comentario` char(128) DEFAULT NULL,
  `cle_id` mediumint(8) unsigned DEFAULT NULL,
  `ass_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ser_id` smallint(5) unsigned DEFAULT NULL,
  `sei_id` smallint(5) unsigned DEFAULT NULL,
  `ssi_id` smallint(5) unsigned DEFAULT NULL,
  `caa_id` tinyint(3) unsigned DEFAULT NULL,
  `cas_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_autenticacoes_ajustes`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_autenticacoes_ajustes` (
  `caa_id` mediumint(8) unsigned NOT NULL,
  `caa_inicio` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `caa_termino` datetime DEFAULT NULL,
  `cau_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `saj_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_autenticacoes_autenticadores`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_autenticacoes_autenticadores` (
  `caa_id` tinyint(3) unsigned NOT NULL,
  `caa_nome` char(12) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_autenticacoes_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_autenticacoes_comentarios` (
  `cac_id` mediumint(8) unsigned NOT NULL,
  `cac_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cac_conteudo` text DEFAULT NULL,
  `cau_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_autenticacoes_status`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_autenticacoes_status` (
  `cas_id` tinyint(3) unsigned NOT NULL,
  `cas_nome` char(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_basesdedados`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_basesdedados` (
  `clb_id` mediumint(8) unsigned NOT NULL,
  `clb_basededados` char(32) DEFAULT NULL,
  `clb_login` char(32) DEFAULT NULL,
  `clb_capacidade` int(10) unsigned DEFAULT NULL,
  `clb_comentarios` text DEFAULT NULL,
  `ass_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ser_id` smallint(5) unsigned DEFAULT 0,
  `cbt_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cbs_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_basesdedados_status`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_basesdedados_status` (
  `cbs_id` tinyint(3) unsigned NOT NULL,
  `cbs_nome` char(19) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_basesdedados_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_basesdedados_tipos` (
  `cbt_id` tinyint(3) unsigned NOT NULL,
  `cbt_nome` char(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_cobranca_dados`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_cobranca_dados` (
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cob_id` smallint(5) unsigned DEFAULT NULL,
  `con_id` smallint(5) unsigned DEFAULT NULL,
  `cep_id` int(11) unsigned DEFAULT NULL,
  `log_id` mediumint(8) unsigned DEFAULT NULL,
  `bai_id` smallint(5) unsigned DEFAULT NULL,
  `cid_id` smallint(5) unsigned DEFAULT NULL,
  `est_id` tinyint(3) unsigned DEFAULT NULL,
  `ccd_numero` smallint(5) unsigned DEFAULT NULL,
  `ccd_complemento` char(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_comodatos`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_comodatos` (
  `clc_id` mediumint(8) unsigned NOT NULL,
  `clc_substituto_id` mediumint(8) unsigned DEFAULT NULL,
  `clc_mac_address` char(12) DEFAULT NULL,
  `clc_data_inicio` date DEFAULT NULL,
  `clc_data_termino` date DEFAULT NULL,
  `clc_data_retirada` date DEFAULT NULL,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ass_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cor_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `equ_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `eqm_id` mediumint(8) unsigned DEFAULT NULL,
  `ccs_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_comodatos_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_comodatos_comentarios` (
  `ccc_id` mediumint(8) unsigned NOT NULL,
  `ccc_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ccc_conteudo` text DEFAULT NULL,
  `clc_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_comodatos_status`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_comodatos_status` (
  `ccs_id` tinyint(3) unsigned NOT NULL,
  `ccs_nome` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_consultas_credito`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_consultas_credito` (
  `ccc_id` mediumint(8) unsigned NOT NULL,
  `ccc_cpf_cnpj` char(20) NOT NULL DEFAULT '',
  `ccc_data_hora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ccc_conteudo` text DEFAULT NULL,
  `ccc_restricoes` smallint(6) NOT NULL DEFAULT 0,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `cct_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ccs_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_consultas_credito_parametros`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_consultas_credito_parametros` (
  `ccp_nsu` smallint(5) unsigned NOT NULL DEFAULT 0,
  `ccp_servidor_host` char(32) NOT NULL DEFAULT '',
  `ccp_servidor_porta` smallint(11) NOT NULL DEFAULT 0,
  `ccp_cliente_codigo` mediumint(11) NOT NULL DEFAULT 0,
  `ccp_cliente_senha` smallint(11) NOT NULL DEFAULT 0,
  `ccp_cliente_serial` mediumint(11) NOT NULL DEFAULT 0,
  `ccp_cliente_email` char(32) NOT NULL DEFAULT '',
  `ccp_servidor_origem` smallint(11) NOT NULL DEFAULT 0,
  `ccp_criptografia` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_consultas_credito_situacoes`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_consultas_credito_situacoes` (
  `ccs_id` tinyint(3) unsigned NOT NULL,
  `ccs_nome` char(13) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_consultas_credito_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_consultas_credito_tipos` (
  `cct_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cct_nome` char(24) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_contatos_emails`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_contatos_emails` (
  `ccm_id` mediumint(8) unsigned NOT NULL,
  `ccm_usuario` char(32) NOT NULL DEFAULT '',
  `ccm_dominio` char(64) NOT NULL DEFAULT '',
  `ccm_informado` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cca_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_contatos_emails_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_contatos_emails_tipos` (
  `cca_id` tinyint(3) unsigned NOT NULL,
  `cca_nome` char(12) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_contatos_telefones`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_contatos_telefones` (
  `ccl_id` mediumint(8) unsigned NOT NULL,
  `ccl_ddd` char(3) NOT NULL DEFAULT '',
  `ccl_numero` char(10) NOT NULL DEFAULT '',
  `ccl_ramal` char(5) DEFAULT NULL,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ccf_id` tinyint(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_contatos_telefones_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_contatos_telefones_tipos` (
  `ccf_id` tinyint(3) NOT NULL,
  `ccf_nome` char(11) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_emails`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_emails` (
  `cle_id` mediumint(8) unsigned NOT NULL,
  `cle_usuario` char(32) DEFAULT NULL,
  `cle_senha` char(10) NOT NULL DEFAULT '',
  `cle_descricao` char(100) NOT NULL DEFAULT '',
  `cle_capacidade` int(10) unsigned DEFAULT NULL,
  `cle_cancelamento` datetime DEFAULT NULL,
  `cle_atualizacao` datetime DEFAULT NULL,
  `cle_bloqueado` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cle_publicado` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cle_principal` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ass_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `dom_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cet_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ces_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_emails_ajustes`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_emails_ajustes` (
  `cea_id` mediumint(8) unsigned NOT NULL,
  `cea_inicio` datetime DEFAULT NULL,
  `cea_termino` datetime DEFAULT NULL,
  `cle_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `saj_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_emails_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_emails_comentarios` (
  `cec_id` mediumint(8) unsigned NOT NULL,
  `cec_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cec_conteudo` text DEFAULT NULL,
  `cle_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_emails_status`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_emails_status` (
  `ces_id` tinyint(3) unsigned NOT NULL,
  `ces_nome` char(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_emails_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_emails_tipos` (
  `cet_id` tinyint(3) unsigned NOT NULL,
  `cet_nome` char(16) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_hospedagens`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_hospedagens` (
  `clh_id` mediumint(8) unsigned NOT NULL,
  `clh_dominio` varchar(250) DEFAULT NULL,
  `clh_login` varchar(32) DEFAULT NULL,
  `clh_diretorio` varchar(250) DEFAULT NULL,
  `clh_capacidade` bigint(20) DEFAULT NULL,
  `clh_transferencia` bigint(20) DEFAULT NULL,
  `clh_comentarios` text NOT NULL,
  `ass_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ser_id` smallint(5) unsigned DEFAULT 0,
  `chs_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_hospedagens_status`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_hospedagens_status` (
  `chs_id` tinyint(3) unsigned NOT NULL,
  `chs_nome` char(19) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_indicacoes`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_indicacoes` (
  `cld_id` tinyint(3) unsigned NOT NULL,
  `cld_nome` char(36) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1 PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_iptv`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_iptv` (
  `cip_id` mediumint(8) unsigned NOT NULL,
  `cip_pontos` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cip_dispositivos` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cip_gravacao` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cip_pacotes` varchar(100) NOT NULL DEFAULT '',
  `cip_vod_telas` int(11) NOT NULL DEFAULT 0,
  `cip_publicado` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cip_usuario` char(96) DEFAULT NULL,
  `cip_recuperacao_datahora` datetime DEFAULT NULL,
  `cip_recuperacao_hash` char(128) DEFAULT NULL,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cor_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `pla_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `cis_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_iptv_ajustes`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_iptv_ajustes` (
  `cia_id` mediumint(8) unsigned NOT NULL,
  `cia_inicio` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cia_termino` datetime DEFAULT NULL,
  `cip_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `saj_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_iptv_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_iptv_comentarios` (
  `cic_id` mediumint(8) unsigned NOT NULL,
  `cic_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cic_conteudo` text DEFAULT NULL,
  `cip_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_iptv_status`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_iptv_status` (
  `cis_id` tinyint(3) unsigned NOT NULL,
  `cis_nome` char(9) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_negativacoes`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_negativacoes` (
  `cln_id` mediumint(8) unsigned NOT NULL,
  `cln_codigo` char(10) NOT NULL DEFAULT '',
  `cln_cpf_cnpj` char(20) NOT NULL DEFAULT '',
  `cln_nome` char(80) NOT NULL DEFAULT '',
  `cln_inclusao` date NOT NULL,
  `cln_baixa` date DEFAULT NULL,
  `cln_status` enum('Ativa','Baixada','Decursada') NOT NULL DEFAULT 'Ativa',
  `cli_id` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_negativacoes_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_negativacoes_comentarios` (
  `cnc_id` mediumint(8) unsigned NOT NULL,
  `cnc_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cnc_conteudo` text DEFAULT NULL,
  `cln_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_operadoras_origens`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_operadoras_origens` (
  `coo_id` tinyint(3) unsigned NOT NULL,
  `coo_nome` char(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_origens`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_origens` (
  `clo_id` int(10) unsigned NOT NULL,
  `clo_nome` char(64) NOT NULL DEFAULT '',
  `clo_razao_social` varchar(120) NOT NULL DEFAULT '',
  `clo_cnpj` varchar(20) NOT NULL DEFAULT '',
  `clo_numero` varchar(20) NOT NULL DEFAULT '',
  `clo_complemento` varchar(80) NOT NULL DEFAULT '',
  `clo_telefone` varchar(15) NOT NULL DEFAULT '',
  `clo_telefone_suporte` varchar(15) DEFAULT NULL,
  `clo_email` varchar(100) NOT NULL DEFAULT '',
  `clo_bloqueio_dias` tinyint(3) unsigned NOT NULL DEFAULT 15,
  `clo_vendor_id` int(10) unsigned DEFAULT NULL,
  `clo_vendor_lot_id` int(10) unsigned DEFAULT NULL,
  `cep_id` int(10) unsigned DEFAULT NULL,
  `clo_hashapi` varchar(40) NOT NULL,
  `clo_system_rpg` set('IXC Soft','Outros') DEFAULT NULL,
  `clo_login` varchar(30) NOT NULL,
  `clo_pass` char(32) NOT NULL,
  `clo_ip` char(110) DEFAULT NULL,
  `clo_errorapi` tinyint(4) NOT NULL DEFAULT 0,
  `cos_id` int(10) unsigned NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_origens_historicoapi`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_origens_historicoapi` (
  `id` bigint(20) unsigned NOT NULL,
  `requestDate` datetime NOT NULL,
  `requestLogin` varchar(30) NOT NULL,
  `requestIp` char(15) NOT NULL,
  `requestGet` text NOT NULL,
  `requestPost` text NOT NULL,
  `requestResult` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_origens_status`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_origens_status` (
  `cos_id` int(10) unsigned NOT NULL,
  `cos_nome` char(19) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_pontos`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_pontos` (
  `clp_id` mediumint(8) unsigned NOT NULL,
  `clp_ip` char(15) DEFAULT NULL,
  `clp_ip_rede` tinyint(3) unsigned DEFAULT NULL,
  `clp_endereco_ip` tinyint(3) unsigned DEFAULT NULL,
  `clp_endereco_mac` char(17) DEFAULT NULL,
  `clp_velocidade_download` int(10) unsigned NOT NULL DEFAULT 0,
  `clp_velocidade_upload` int(10) unsigned NOT NULL DEFAULT 0,
  `clp_regra` smallint(5) unsigned DEFAULT NULL,
  `clp_publicado` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `clp_ajustado` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `clp_parametros_ajuste` text DEFAULT NULL,
  `clp_comentario` char(128) DEFAULT NULL,
  `ass_id` mediumint(8) unsigned DEFAULT NULL,
  `sei_id` smallint(5) unsigned DEFAULT NULL,
  `cps_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_pontos_ajustes`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_pontos_ajustes` (
  `cpa_id` mediumint(8) unsigned NOT NULL,
  `cpa_inicio` datetime DEFAULT NULL,
  `cpa_termino` datetime DEFAULT NULL,
  `clp_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `saj_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_pontos_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_pontos_comentarios` (
  `cpc_id` mediumint(8) unsigned NOT NULL,
  `cpc_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cpc_conteudo` text DEFAULT NULL,
  `clp_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_pontos_status`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_pontos_status` (
  `cps_id` tinyint(3) unsigned NOT NULL,
  `cps_nome` char(9) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_responsabilidades`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_responsabilidades` (
  `cln_id` mediumint(8) unsigned NOT NULL,
  `cln_inicio` datetime DEFAULT NULL,
  `cln_termino` datetime DEFAULT NULL,
  `cln_parametros` text DEFAULT NULL,
  `cln_lembrete` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cod_id` mediumint(8) unsigned DEFAULT NULL,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `crt_id` tinyint(3) unsigned DEFAULT NULL,
  `cri_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cra_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_responsabilidades_categorias`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_responsabilidades_categorias` (
  `crt_id` tinyint(3) unsigned NOT NULL,
  `crt_nome` char(34) NOT NULL DEFAULT '',
  `crt_parametros` text DEFAULT NULL,
  `crt_disponivel` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_responsabilidades_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_responsabilidades_comentarios` (
  `crc_id` mediumint(8) unsigned NOT NULL,
  `crc_datahora` datetime DEFAULT NULL,
  `crc_conteudo` text DEFAULT NULL,
  `cln_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_responsabilidades_status`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_responsabilidades_status` (
  `cra_id` tinyint(3) unsigned NOT NULL,
  `cra_nome` char(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_responsabilidades_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_responsabilidades_tipos` (
  `cri_id` tinyint(3) unsigned NOT NULL,
  `cri_nome` char(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_retornos`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_retornos` (
  `clr_id` mediumint(8) unsigned NOT NULL,
  `clr_data` date NOT NULL DEFAULT '0000-00-00',
  `clr_abertura` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `clr_fechamento` datetime DEFAULT NULL,
  `clr_comentario` text DEFAULT NULL,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `cee_id` tinyint(3) unsigned DEFAULT NULL,
  `cet_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ces_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_retornos_periodos`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_retornos_periodos` (
  `cee_id` tinyint(3) unsigned NOT NULL,
  `cee_nome` char(5) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_retornos_status`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_retornos_status` (
  `ces_id` tinyint(3) unsigned NOT NULL,
  `ces_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_retornos_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_retornos_tipos` (
  `cet_id` tinyint(3) unsigned NOT NULL,
  `cet_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_situacoes`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_situacoes` (
  `clu_id` tinyint(3) unsigned NOT NULL,
  `clu_nome` char(23) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_status`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_status` (
  `cls_id` tinyint(3) unsigned NOT NULL,
  `cls_nome` char(9) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_telefones`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_telefones` (
  `clt_id` mediumint(8) unsigned NOT NULL,
  `clt_numero` char(8) NOT NULL DEFAULT '',
  `clt_publicado` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ass_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ctt_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cts_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_telefones_recargas`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_telefones_recargas` (
  `ctr_id` mediumint(8) unsigned NOT NULL,
  `ctr_data_recarga` date NOT NULL DEFAULT '0000-00-00',
  `ctr_data_vencimento` date DEFAULT NULL,
  `ctr_valor_recarga` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ctr_valor_saldo_remanescente` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ctr_observacao` char(32) DEFAULT NULL,
  `usu_id` smallint(5) unsigned DEFAULT NULL,
  `clt_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_telefones_status`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_telefones_status` (
  `cts_id` tinyint(3) unsigned NOT NULL,
  `cts_nome` char(9) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_telefones_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_telefones_tipos` (
  `ctt_id` tinyint(3) unsigned NOT NULL,
  `ctt_nome` char(8) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_clientes_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_clientes_tipos` (
  `clt_id` tinyint(3) unsigned NOT NULL,
  `clt_nome` char(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas` (
  `coa_id` mediumint(8) unsigned NOT NULL,
  `coa_pai` mediumint(8) unsigned DEFAULT NULL,
  `coa_grupo_id` mediumint(8) unsigned DEFAULT NULL,
  `coa_codigo` char(32) NOT NULL DEFAULT '',
  `coa_nosso_numero` char(32) NOT NULL DEFAULT '',
  `coa_data_geracao` date NOT NULL DEFAULT '0000-00-00',
  `coa_data_vencimento` date NOT NULL DEFAULT '0000-00-00',
  `coa_data_processamento` date DEFAULT NULL,
  `coa_data_pagamento` date DEFAULT NULL,
  `coa_data_credito` date DEFAULT NULL,
  `coa_data_bloqueio` date DEFAULT NULL,
  `coa_valor_original` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `coa_valor_juro` mediumint(8) unsigned DEFAULT NULL,
  `coa_valor_desconto` mediumint(8) unsigned DEFAULT NULL,
  `coa_valor_acrescimo` mediumint(8) unsigned DEFAULT NULL,
  `coa_valor_decrescimo` mediumint(8) unsigned DEFAULT NULL,
  `coa_valor_bonificacao` mediumint(8) unsigned DEFAULT NULL,
  `coa_valor_total` mediumint(8) unsigned DEFAULT NULL,
  `coa_valor_iof` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `coa_dias_protesto` int(10) unsigned NOT NULL DEFAULT 0,
  `coa_parametros` text DEFAULT NULL,
  `coa_enviada` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `coa_fatura_gerada` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `coa_enviada_correios` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `coa_aguardando_registro` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cli_id` mediumint(8) unsigned DEFAULT NULL,
  `fco_id` mediumint(8) unsigned DEFAULT NULL,
  `fat_id` mediumint(8) unsigned DEFAULT NULL,
  `rec_id` mediumint(8) unsigned DEFAULT NULL,
  `coe_id` smallint(5) unsigned DEFAULT NULL,
  `col_id` smallint(5) unsigned DEFAULT NULL,
  `cof_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cop_id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `cot_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_comentarios` (
  `com_id` int(10) unsigned NOT NULL,
  `com_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `com_conteudo` text DEFAULT NULL,
  `coa_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_detalhes`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_detalhes` (
  `cod_id` int(10) unsigned NOT NULL,
  `cod_competencia_inicio` date DEFAULT NULL,
  `cod_competencia_termino` date DEFAULT NULL,
  `cod_descricao` char(250) NOT NULL DEFAULT '',
  `cod_valor_original` int(11) NOT NULL DEFAULT 0,
  `cod_valor_final` int(11) NOT NULL DEFAULT 0,
  `cod_cancelado` tinyint(3) NOT NULL DEFAULT 0,
  `cod_pai` int(10) unsigned DEFAULT NULL,
  `coa_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ass_id` mediumint(8) unsigned DEFAULT NULL,
  `dec_id` mediumint(8) unsigned DEFAULT NULL,
  `ajf_id` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_formatos`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_formatos` (
  `cof_id` tinyint(3) unsigned NOT NULL,
  `cof_nome` char(42) NOT NULL DEFAULT '',
  `cof_cedente` char(6) DEFAULT NULL,
  `cof_parametros` text DEFAULT NULL,
  `cof_publicavel` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cof_remessa` tinyint(4) DEFAULT NULL,
  `cof_nosso_numero` int(11) NOT NULL DEFAULT 0,
  `cft_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cfs_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_formatos_status`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_formatos_status` (
  `cfs_id` tinyint(3) unsigned NOT NULL,
  `cfs_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_formatos_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_formatos_tipos` (
  `cft_id` tinyint(3) unsigned NOT NULL,
  `cft_nome` char(17) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_lotes`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_lotes` (
  `col_id` smallint(5) unsigned NOT NULL,
  `col_nome` char(80) NOT NULL DEFAULT '',
  `col_parametros` text DEFAULT NULL,
  `col_data_geracao` datetime DEFAULT NULL,
  `col_data_ativacao` datetime DEFAULT NULL,
  `col_vencimento` date DEFAULT NULL,
  `col_sincronizado` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `col_gerado` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cof_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cla_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cop_id` tinyint(3) unsigned NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_lotes_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_lotes_comentarios` (
  `clo_id` mediumint(8) unsigned NOT NULL,
  `clo_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `clo_conteudo` text DEFAULT NULL,
  `col_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_lotes_status`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_lotes_status` (
  `cla_id` tinyint(3) unsigned NOT NULL,
  `cla_nome` char(24) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_periodos`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_periodos` (
  `cop_id` tinyint(3) unsigned NOT NULL,
  `cop_nome` char(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_remessas`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_remessas` (
  `cbr_id` smallint(5) unsigned NOT NULL,
  `cbr_data_hora_geracao` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cbr_nsa` int(6) NOT NULL DEFAULT 0,
  `cof_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_remessas_itens`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_remessas_itens` (
  `cbr_id` smallint(6) unsigned NOT NULL,
  `coa_id` mediumint(8) unsigned NOT NULL,
  `cof_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cri_nosso_numero` char(32) NOT NULL,
  `cri_tipo` enum('REGISTRO','BAIXA') NOT NULL DEFAULT 'REGISTRO',
  `cri_status` enum('PENDENTE','REGISTRADA','BAIXADA','REJEITADA') NOT NULL DEFAULT 'PENDENTE'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_retornos`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_retornos` (
  `coe_id` smallint(5) unsigned NOT NULL,
  `coe_data_geracao` date NOT NULL DEFAULT '0000-00-00',
  `coe_data_publicacao` date NOT NULL DEFAULT '0000-00-00',
  `coe_conteudo` longtext DEFAULT NULL,
  `coe_sequencial` int(11) NOT NULL DEFAULT 0,
  `cof_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_retornos_erros`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_retornos_erros` (
  `cos_id` smallint(5) unsigned NOT NULL,
  `cos_descricao` char(64) NOT NULL DEFAULT '',
  `coa_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `coe_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_retornos_nao_encontradas`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_retornos_nao_encontradas` (
  `crn_id` int(10) unsigned NOT NULL,
  `crn_codigo` char(12) NOT NULL DEFAULT '',
  `crn_data_vencimento` date NOT NULL DEFAULT '0000-00-00',
  `crn_valor_original` mediumint(9) NOT NULL DEFAULT 0,
  `crn_valor_pago` mediumint(9) NOT NULL DEFAULT 0,
  `coe_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `coa_id` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_retornos_registros`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_retornos_registros` (
  `coe_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `coa_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cog_tipo` enum('REGISTRO','REJEICAO','LIQUIDACAO','BAIXA') NOT NULL DEFAULT 'LIQUIDACAO'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_status`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_status` (
  `cot_id` tinyint(3) unsigned NOT NULL,
  `cot_nome` char(36) NOT NULL DEFAULT '',
  `cot_recebido` tinyint(3) unsigned DEFAULT 0,
  `cot_manual` tinyint(3) unsigned DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cobrancas_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_cobrancas_tipos` (
  `cop_id` tinyint(3) unsigned NOT NULL,
  `cop_nome` char(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios`
--

CREATE TABLE IF NOT EXISTS `tab_condominios` (
  `con_id` smallint(5) unsigned NOT NULL,
  `con_pai` smallint(5) unsigned DEFAULT NULL,
  `con_nome` char(60) NOT NULL DEFAULT '',
  `con_data_cadastro` date DEFAULT NULL,
  `con_data_ativacao` date DEFAULT NULL,
  `con_numero` smallint(5) unsigned DEFAULT NULL,
  `con_comentarios` text DEFAULT NULL,
  `con_comentarios_comercial` text DEFAULT NULL,
  `con_velocidade_maxima` int(11) unsigned DEFAULT NULL,
  `cep_id` int(11) unsigned DEFAULT NULL,
  `log_id` mediumint(8) unsigned DEFAULT NULL,
  `bai_id` smallint(5) unsigned DEFAULT NULL,
  `cid_id` smallint(5) unsigned DEFAULT NULL,
  `est_id` tinyint(3) unsigned DEFAULT NULL,
  `usu_id` smallint(5) unsigned DEFAULT NULL,
  `ccl_id` tinyint(3) unsigned DEFAULT NULL,
  `cnt_id` tinyint(3) unsigned DEFAULT NULL,
  `cog_id` tinyint(3) unsigned DEFAULT NULL,
  `coq_id` tinyint(3) unsigned DEFAULT NULL,
  `cit_id` tinyint(3) unsigned NOT NULL DEFAULT 2,
  `coi_id` tinyint(3) unsigned DEFAULT NULL,
  `cos_id` tinyint(3) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_blocos`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_blocos` (
  `cob_id` smallint(5) unsigned NOT NULL,
  `cob_nome` char(20) NOT NULL DEFAULT '',
  `cob_numero` smallint(5) unsigned DEFAULT NULL,
  `cob_quantidade` smallint(5) unsigned NOT NULL DEFAULT 0,
  `cob_data_cadastro` date NOT NULL DEFAULT '0000-00-00',
  `cob_equipamento_ip` char(15) DEFAULT NULL,
  `cob_ap_ip` char(15) DEFAULT NULL,
  `cep_id` int(11) unsigned DEFAULT NULL,
  `log_id` mediumint(8) unsigned DEFAULT NULL,
  `bai_id` smallint(5) unsigned DEFAULT NULL,
  `cid_id` smallint(5) unsigned DEFAULT NULL,
  `est_id` tinyint(3) unsigned DEFAULT NULL,
  `sei_id` smallint(5) unsigned DEFAULT NULL,
  `con_id` smallint(5) unsigned DEFAULT NULL,
  `cbs_id` tinyint(3) unsigned NOT NULL DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_blocos_status`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_blocos_status` (
  `cbs_id` tinyint(3) unsigned NOT NULL,
  `cbs_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_classes`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_classes` (
  `ccl_id` tinyint(3) unsigned NOT NULL,
  `ccl_nome` char(1) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_contatos`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_contatos` (
  `coc_id` mediumint(8) unsigned NOT NULL,
  `coc_nome` char(40) NOT NULL DEFAULT '',
  `coc_nascimento` date DEFAULT NULL,
  `coc_complemento` char(25) DEFAULT NULL,
  `coc_tel_residencial` char(15) DEFAULT NULL,
  `coc_tel_comercial` char(15) DEFAULT NULL,
  `coc_tel_celular` char(15) DEFAULT NULL,
  `coc_email` char(40) DEFAULT NULL,
  `coc_validade` date DEFAULT NULL,
  `coc_data_cadastro` date NOT NULL DEFAULT '0000-00-00',
  `coc_comentarios` text DEFAULT NULL,
  `cob_id` smallint(5) unsigned DEFAULT NULL,
  `con_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `cct_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_contatos_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_contatos_tipos` (
  `cct_id` tinyint(3) unsigned NOT NULL,
  `cct_nome` char(13) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_instalacoes_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_instalacoes_tipos` (
  `cit_id` tinyint(3) unsigned NOT NULL,
  `cit_nome` char(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_procedimentos`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_procedimentos` (
  `cop_id` mediumint(8) unsigned NOT NULL,
  `cop_inicio` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cop_termino` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cop_parametros` text DEFAULT NULL,
  `con_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `cpt_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_procedimentos_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_procedimentos_tipos` (
  `cpt_id` tinyint(3) unsigned NOT NULL,
  `cpt_nome` char(40) NOT NULL DEFAULT '',
  `cpt_disponivel` tinyint(3) unsigned NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_responsabilidades`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_responsabilidades` (
  `cod_id` mediumint(8) unsigned NOT NULL,
  `cod_inicio` datetime DEFAULT NULL,
  `cod_termino` datetime DEFAULT NULL,
  `cod_parametros` text DEFAULT NULL,
  `con_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `crg_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `crp_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cru_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_responsabilidades_atividades`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_responsabilidades_atividades` (
  `cov_id` tinyint(3) unsigned NOT NULL,
  `cov_nome` char(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_responsabilidades_categorias`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_responsabilidades_categorias` (
  `crg_id` tinyint(3) unsigned NOT NULL,
  `crg_nome` char(45) NOT NULL DEFAULT '',
  `crg_parametros` text DEFAULT NULL,
  `crg_disponivel` tinyint(3) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_responsabilidades_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_responsabilidades_comentarios` (
  `crm_id` mediumint(8) unsigned NOT NULL,
  `crm_datahora` datetime DEFAULT NULL,
  `crm_conteudo` text DEFAULT NULL,
  `cod_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_responsabilidades_status`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_responsabilidades_status` (
  `cru_id` tinyint(3) unsigned NOT NULL,
  `cru_nome` char(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_responsabilidades_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_responsabilidades_tipos` (
  `crp_id` tinyint(3) unsigned NOT NULL,
  `crp_nome` char(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_situacoes`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_situacoes` (
  `coi_id` tinyint(3) unsigned NOT NULL,
  `coi_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_status`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_status` (
  `cos_id` tinyint(3) unsigned NOT NULL,
  `cos_nome` char(9) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_tecnologias`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_tecnologias` (
  `cog_id` tinyint(3) unsigned NOT NULL,
  `cog_nome` char(13) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_tecnologias_equipamentos`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_tecnologias_equipamentos` (
  `coq_id` tinyint(3) unsigned NOT NULL,
  `coq_nome` char(40) NOT NULL DEFAULT '',
  `cog_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_condominios_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_condominios_tipos` (
  `cnt_id` tinyint(3) unsigned NOT NULL,
  `cnt_nome` char(11) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_contratos`
--

CREATE TABLE IF NOT EXISTS `tab_contratos` (
  `cor_id` mediumint(8) unsigned NOT NULL,
  `cor_pai` mediumint(8) unsigned DEFAULT NULL,
  `cor_codigo` char(20) NOT NULL DEFAULT '',
  `cor_codigo_origem` varchar(20) DEFAULT NULL,
  `cor_hash` varchar(40) NOT NULL DEFAULT '',
  `cor_data_geracao` date DEFAULT NULL,
  `cor_data_confirmacao` date DEFAULT NULL,
  `cor_data_ativacao` date DEFAULT NULL,
  `cor_data_cancelamento` date DEFAULT NULL,
  `cor_data_anulacao` date DEFAULT NULL,
  `cor_termino_fidelidade` date DEFAULT NULL,
  `cor_multa` mediumint(8) unsigned DEFAULT NULL,
  `cor_vigencia` tinyint(3) unsigned DEFAULT NULL,
  `cor_parametros` mediumtext DEFAULT NULL,
  `cor_sincronizado` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `cor_enviado` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cor_renegociacao` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cor_arquivo_extensao` char(4) DEFAULT NULL,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fop_id` smallint(5) unsigned DEFAULT NULL,
  `pls_id` tinyint(3) unsigned DEFAULT NULL,
  `cma_id` tinyint(3) unsigned DEFAULT NULL,
  `crt_id` tinyint(3) unsigned DEFAULT NULL,
  `crs_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Acionadores `tab_contratos`
--
DELIMITER $$
CREATE TRIGGER `tab_contratos_before_insert` BEFORE INSERT ON `tab_contratos`
 FOR EACH ROW BEGIN

	SET NEW.cor_hash := SHA1(CONCAT('oletelecom.', NEW.cor_codigo));

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_contratos_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_contratos_comentarios` (
  `coo_id` mediumint(8) unsigned NOT NULL,
  `coo_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `coo_conteudo` text DEFAULT NULL,
  `cor_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ccc_id` tinyint(3) unsigned DEFAULT NULL,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_contratos_comentarios_categorias`
--

CREATE TABLE IF NOT EXISTS `tab_contratos_comentarios_categorias` (
  `ccc_id` tinyint(3) unsigned NOT NULL,
  `ccc_nome` char(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_contratos_motivos_anulacao`
--

CREATE TABLE IF NOT EXISTS `tab_contratos_motivos_anulacao` (
  `cma_id` tinyint(3) unsigned NOT NULL,
  `cma_nome` char(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_contratos_status`
--

CREATE TABLE IF NOT EXISTS `tab_contratos_status` (
  `crs_id` tinyint(3) unsigned NOT NULL,
  `crs_nome` char(34) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_contratos_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_contratos_tipos` (
  `crt_id` tinyint(3) unsigned NOT NULL,
  `crt_nome` char(34) NOT NULL DEFAULT '',
  `crt_parametros` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_contratos_versoes`
--

CREATE TABLE IF NOT EXISTS `tab_contratos_versoes` (
  `cov_id` int(11) NOT NULL DEFAULT 0,
  `pls_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `pli_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cov_inicio` datetime DEFAULT NULL,
  `cov_termino` datetime DEFAULT NULL,
  `cov_numero` char(15) NOT NULL DEFAULT '',
  `cov_registro_numero` char(15) NOT NULL DEFAULT '',
  `cov_registro_livro` char(15) NOT NULL DEFAULT '',
  `cov_registro_folha` char(15) NOT NULL DEFAULT '',
  `cov_registro_data` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_contratos_vinculos`
--

CREATE TABLE IF NOT EXISTS `tab_contratos_vinculos` (
  `cov_id` smallint(5) unsigned NOT NULL,
  `cor_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cor_filho` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `pvt_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cron`
--

CREATE TABLE IF NOT EXISTS `tab_cron` (
  `cro_id` smallint(5) unsigned NOT NULL,
  `cro_classe` char(35) NOT NULL DEFAULT '',
  `cro_acao` char(35) NOT NULL DEFAULT '',
  `cro_dias_semana` char(20) DEFAULT NULL,
  `cro_dias_mes` char(20) DEFAULT NULL,
  `cro_intervalo_minutos` smallint(5) unsigned DEFAULT NULL,
  `cro_ultima_execucao_real` datetime DEFAULT NULL,
  `cro_ultima_execucao_inicio` datetime DEFAULT NULL,
  `cro_ultima_execucao_termino` datetime DEFAULT NULL,
  `cro_tempo_real` enum('s','n') NOT NULL DEFAULT 'n',
  `cro_em_execucao` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cro_ativo` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_cron_log`
--

CREATE TABLE IF NOT EXISTS `tab_cron_log` (
  `crl_id` int(10) unsigned NOT NULL,
  `crl_classe` char(35) NOT NULL DEFAULT '',
  `crl_acao` char(35) NOT NULL DEFAULT '',
  `crl_inicio` datetime DEFAULT NULL,
  `crl_termino` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_descontos`
--

CREATE TABLE IF NOT EXISTS `tab_descontos` (
  `des_id` smallint(5) unsigned NOT NULL,
  `des_nome` char(60) NOT NULL DEFAULT '',
  `des_parametros` text DEFAULT NULL,
  `des_adicionar_retorno` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `des_fidelidade` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `pls_id` tinyint(3) unsigned DEFAULT NULL,
  `pla_id` smallint(5) unsigned DEFAULT NULL,
  `pli_id` tinyint(3) unsigned DEFAULT NULL,
  `det_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `dea_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `clo_id` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_descontos_concessoes`
--

CREATE TABLE IF NOT EXISTS `tab_descontos_concessoes` (
  `dec_id` mediumint(8) unsigned NOT NULL,
  `dec_pai` mediumint(8) unsigned DEFAULT NULL,
  `dec_data_cadastro` date NOT NULL DEFAULT '0000-00-00',
  `dec_data_inicio` date DEFAULT NULL,
  `dec_data_termino` date DEFAULT NULL,
  `dec_instalacao` float(10,2) DEFAULT NULL,
  `dec_mensalidade` float(10,2) DEFAULT NULL,
  `dec_validade` tinyint(3) unsigned DEFAULT NULL,
  `dec_fidelidade` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `dec_ordem` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ass_id` mediumint(8) unsigned DEFAULT NULL,
  `con_id` smallint(5) unsigned DEFAULT NULL,
  `pla_id` smallint(5) unsigned DEFAULT NULL,
  `des_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `det_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `dcs_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_descontos_concessoes_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_descontos_concessoes_comentarios` (
  `dcc_id` mediumint(8) unsigned NOT NULL,
  `dcc_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dcc_conteudo` text DEFAULT NULL,
  `dec_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_descontos_concessoes_status`
--

CREATE TABLE IF NOT EXISTS `tab_descontos_concessoes_status` (
  `dcs_id` tinyint(3) unsigned NOT NULL,
  `dcs_nome` char(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_descontos_status`
--

CREATE TABLE IF NOT EXISTS `tab_descontos_status` (
  `dea_id` tinyint(3) unsigned NOT NULL,
  `dea_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_descontos_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_descontos_tipos` (
  `det_id` tinyint(3) unsigned NOT NULL,
  `det_nome` char(23) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_documentos`
--

CREATE TABLE IF NOT EXISTS `tab_documentos` (
  `doc_id` mediumint(8) unsigned NOT NULL,
  `doc_nome` char(50) NOT NULL DEFAULT '',
  `doc_arquivo_nome` char(50) DEFAULT NULL,
  `doc_arquivo_conteudo` text DEFAULT NULL,
  `doc_data_registro` date NOT NULL DEFAULT '0000-00-00',
  `doa_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `dou_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `dot_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_documentos_categorias`
--

CREATE TABLE IF NOT EXISTS `tab_documentos_categorias` (
  `doa_id` tinyint(3) unsigned NOT NULL,
  `doa_nome` char(22) NOT NULL DEFAULT '',
  `doa_pai` tinyint(3) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_documentos_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_documentos_comentarios` (
  `doe_id` mediumint(8) unsigned NOT NULL,
  `doe_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `doe_conteudo` text DEFAULT NULL,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `doc_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_documentos_faqs`
--

CREATE TABLE IF NOT EXISTS `tab_documentos_faqs` (
  `dof_id` tinyint(3) unsigned NOT NULL,
  `dof_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dof_conteudo` text DEFAULT NULL,
  `doc_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `dof_pai` tinyint(3) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_documentos_status`
--

CREATE TABLE IF NOT EXISTS `tab_documentos_status` (
  `dou_id` tinyint(3) unsigned NOT NULL,
  `dou_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_documentos_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_documentos_tipos` (
  `dot_id` tinyint(3) unsigned NOT NULL,
  `dot_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_dominios`
--

CREATE TABLE IF NOT EXISTS `tab_dominios` (
  `dom_id` mediumint(8) unsigned NOT NULL,
  `dom_dominio` char(60) NOT NULL DEFAULT '',
  `dom_responsavel` char(50) NOT NULL DEFAULT '',
  `dom_fapesp` char(10) NOT NULL DEFAULT '',
  `dom_emails` char(120) NOT NULL DEFAULT '',
  `dom_integracao` char(4) NOT NULL DEFAULT 'ldap',
  `dom_comentarios` text DEFAULT NULL,
  `dom_global` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ass_id` mediumint(8) unsigned DEFAULT NULL,
  `doo_id` tinyint(3) unsigned DEFAULT NULL,
  `dos_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_dominios_origens`
--

CREATE TABLE IF NOT EXISTS `tab_dominios_origens` (
  `doo_id` tinyint(3) unsigned NOT NULL,
  `doo_nome` char(13) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_dominios_status`
--

CREATE TABLE IF NOT EXISTS `tab_dominios_status` (
  `dos_id` tinyint(3) unsigned NOT NULL,
  `dos_nome` char(22) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_empresas`
--

CREATE TABLE IF NOT EXISTS `tab_empresas` (
  `emp_id` int(11) NOT NULL,
  `emp_nome` varchar(100) NOT NULL,
  `emp_razao_social` varchar(200) NOT NULL,
  `emp_cnpj` varchar(18) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_equipamentos`
--

CREATE TABLE IF NOT EXISTS `tab_equipamentos` (
  `equ_id` tinyint(3) unsigned NOT NULL,
  `equ_nome` char(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_equipamentos_modelos`
--

CREATE TABLE IF NOT EXISTS `tab_equipamentos_modelos` (
  `eqm_id` mediumint(8) unsigned NOT NULL,
  `eqm_nome` char(100) NOT NULL DEFAULT '',
  `equ_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `eqm_disponivel` tinyint(3) unsigned NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_estados`
--

CREATE TABLE IF NOT EXISTS `tab_estados` (
  `est_id` tinyint(3) unsigned NOT NULL,
  `est_sigla` char(2) NOT NULL DEFAULT '',
  `est_nome` char(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_estoque`
--

CREATE TABLE IF NOT EXISTS `tab_estoque` (
  `est_id` mediumint(8) unsigned NOT NULL,
  `est_quantidade` smallint(5) unsigned NOT NULL DEFAULT 0,
  `est_valor_medio` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `esi_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_estoque_alocacoes`
--

CREATE TABLE IF NOT EXISTS `tab_estoque_alocacoes` (
  `esa_id` mediumint(8) unsigned NOT NULL,
  `esa_quantidade` smallint(5) unsigned NOT NULL DEFAULT 0,
  `est_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `esg_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_estoque_comprovantes`
--

CREATE TABLE IF NOT EXISTS `tab_estoque_comprovantes` (
  `esc_id` mediumint(8) unsigned NOT NULL,
  `esc_codigo` varchar(10) NOT NULL DEFAULT '',
  `esc_data` date NOT NULL DEFAULT '0000-00-00',
  `esc_comentarios` text DEFAULT NULL,
  `esf_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ect_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_estoque_comprovantes_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_estoque_comprovantes_tipos` (
  `ect_id` tinyint(3) unsigned NOT NULL,
  `ect_nome` char(12) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_estoque_entradas`
--

CREATE TABLE IF NOT EXISTS `tab_estoque_entradas` (
  `ese_id` mediumint(8) unsigned NOT NULL,
  `ese_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ese_quantidade` smallint(5) unsigned NOT NULL DEFAULT 0,
  `ese_valor_unitario` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `esc_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `esi_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_estoque_fornecedores`
--

CREATE TABLE IF NOT EXISTS `tab_estoque_fornecedores` (
  `esf_id` mediumint(8) unsigned NOT NULL,
  `esf_nome` char(80) NOT NULL DEFAULT '',
  `esf_contato` char(80) DEFAULT NULL,
  `esf_tel_comercial` char(15) NOT NULL DEFAULT '',
  `esf_tel_celular` char(15) DEFAULT NULL,
  `esf_comentarios` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_estoque_grupos`
--

CREATE TABLE IF NOT EXISTS `tab_estoque_grupos` (
  `esg_id` tinyint(3) unsigned NOT NULL,
  `esg_nome` char(30) NOT NULL,
  `esg_descricao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_estoque_itens`
--

CREATE TABLE IF NOT EXISTS `tab_estoque_itens` (
  `esi_id` mediumint(8) unsigned NOT NULL,
  `esi_nome` char(80) NOT NULL DEFAULT '',
  `esi_descricao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_estoque_saidas`
--

CREATE TABLE IF NOT EXISTS `tab_estoque_saidas` (
  `esd_id` int(11) NOT NULL,
  `esd_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `esd_quantidade` smallint(5) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `esi_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_estoque_sublocacoes`
--

CREATE TABLE IF NOT EXISTS `tab_estoque_sublocacoes` (
  `ess_id` mediumint(8) unsigned NOT NULL,
  `ess_quantidade` smallint(5) unsigned NOT NULL DEFAULT 0,
  `esa_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_faturas`
--

CREATE TABLE IF NOT EXISTS `tab_faturas` (
  `fat_id` mediumint(8) unsigned NOT NULL,
  `fat_codigo` char(13) NOT NULL DEFAULT '',
  `fat_data_emissao` date NOT NULL DEFAULT '0000-00-00',
  `fat_data_vencimento` date NOT NULL DEFAULT '0000-00-00',
  `fat_natureza` char(8) NOT NULL DEFAULT '',
  `fat_serie` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `fat_numero` mediumint(8) unsigned DEFAULT NULL,
  `fat_valor` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fat_md5` char(32) DEFAULT NULL,
  `fat_cancelada` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `fal_id` smallint(5) unsigned DEFAULT NULL,
  `fap_id` tinyint(3) unsigned NOT NULL DEFAULT 2,
  `fcl_id` mediumint(8) unsigned DEFAULT NULL,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `coa_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `emp_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_faturas_filtros`
--

CREATE TABLE IF NOT EXISTS `tab_faturas_filtros` (
  `faf_id` tinyint(3) unsigned NOT NULL,
  `faf_nome` char(30) NOT NULL DEFAULT '',
  `faf_parametros` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_faturas_itens`
--

CREATE TABLE IF NOT EXISTS `tab_faturas_itens` (
  `fai_id` int(10) unsigned NOT NULL,
  `fai_quantidade` smallint(5) unsigned NOT NULL DEFAULT 0,
  `fai_valor_unitario` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fai_valor_total` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fai_valor_icms` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fai_tipo` enum('SCM','SVA') NOT NULL DEFAULT 'SCM',
  `fai_natureza` char(8) NOT NULL DEFAULT '',
  `fat_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cft_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_faturas_lotes`
--

CREATE TABLE IF NOT EXISTS `tab_faturas_lotes` (
  `fal_id` smallint(5) unsigned NOT NULL,
  `fal_ciclo` char(11) NOT NULL DEFAULT '',
  `fal_ciclo_sequencia` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `fal_data_ciclo` date NOT NULL DEFAULT '0000-00-00',
  `fal_data_geracao` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fal_data_processamento` datetime DEFAULT NULL,
  `fal_data_aprovacao` datetime DEFAULT NULL,
  `fap_id` tinyint(3) unsigned NOT NULL DEFAULT 2,
  `fls_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_faturas_lotes_clientes`
--

CREATE TABLE IF NOT EXISTS `tab_faturas_lotes_clientes` (
  `fcl_id` mediumint(8) unsigned NOT NULL,
  `fcl_nome` char(80) NOT NULL DEFAULT '',
  `fcl_nome_fantasia` char(50) NOT NULL DEFAULT '',
  `fcl_tipo_pessoa` enum('B','R') NOT NULL DEFAULT 'R',
  `fcl_cpf_cnpj` char(20) NOT NULL DEFAULT '',
  `fcl_inscricao_municipal` char(20) NOT NULL DEFAULT '',
  `fcl_inscricao_estadual` char(20) NOT NULL DEFAULT '',
  `fcl_endereco_logradouro` char(100) NOT NULL DEFAULT '',
  `fcl_endereco_numero` mediumint(8) unsigned DEFAULT NULL,
  `fcl_endereco_complemento` char(20) NOT NULL DEFAULT '',
  `fcl_endereco_bairro` char(30) NOT NULL DEFAULT '',
  `fcl_endereco_cep` int(10) unsigned NOT NULL DEFAULT 0,
  `fal_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_faturas_lotes_cobrancas`
--

CREATE TABLE IF NOT EXISTS `tab_faturas_lotes_cobrancas` (
  `fco_id` mediumint(8) unsigned NOT NULL,
  `fco_valor_scm` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fco_valor_sva` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fco_valor_juro` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fco_valor_instalacao` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fco_valor_equipamentos` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fco_valor_servicos` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fco_valor_garantia` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `coa_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fat_id` mediumint(8) unsigned DEFAULT NULL,
  `fal_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_faturas_lotes_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_faturas_lotes_comentarios` (
  `flc_id` mediumint(8) unsigned NOT NULL,
  `flc_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `flc_conteudo` text DEFAULT NULL,
  `fal_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_faturas_lotes_filtros`
--

CREATE TABLE IF NOT EXISTS `tab_faturas_lotes_filtros` (
  `flf_id` smallint(5) unsigned NOT NULL,
  `flf_comparacao` char(2) NOT NULL DEFAULT '',
  `flf_valor` char(10) NOT NULL DEFAULT '',
  `fal_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `faf_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_faturas_lotes_status`
--

CREATE TABLE IF NOT EXISTS `tab_faturas_lotes_status` (
  `fls_id` tinyint(3) unsigned NOT NULL,
  `fls_nome` char(33) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_faturas_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_faturas_tipos` (
  `fap_id` tinyint(3) unsigned NOT NULL,
  `fap_nome` char(11) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_formas_pagamento`
--

CREATE TABLE IF NOT EXISTS `tab_formas_pagamento` (
  `fop_id` smallint(11) unsigned NOT NULL,
  `fop_entrada` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fop_parcelas` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `fop_valor_parcela` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fop_multa` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `fop_fidelidade` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `pla_id` smallint(5) unsigned DEFAULT NULL,
  `pls_id` tinyint(3) unsigned DEFAULT NULL,
  `fpf_id` tinyint(3) unsigned DEFAULT NULL,
  `fpc_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_formas_pagamento_categorias`
--

CREATE TABLE IF NOT EXISTS `tab_formas_pagamento_categorias` (
  `fpc_id` tinyint(3) unsigned NOT NULL,
  `fpc_nome` char(23) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_formas_pagamento_formas`
--

CREATE TABLE IF NOT EXISTS `tab_formas_pagamento_formas` (
  `fpf_id` tinyint(3) unsigned NOT NULL,
  `fpf_nome` char(6) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_grupos`
--

CREATE TABLE IF NOT EXISTS `tab_grupos` (
  `gru_id` tinyint(3) unsigned NOT NULL,
  `gru_nome` char(36) NOT NULL DEFAULT '',
  `grs_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_grupos_status`
--

CREATE TABLE IF NOT EXISTS `tab_grupos_status` (
  `grs_id` tinyint(3) unsigned NOT NULL,
  `grs_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_logradouros`
--

CREATE TABLE IF NOT EXISTS `tab_logradouros` (
  `log_id` mediumint(8) unsigned NOT NULL,
  `log_nome` char(90) NOT NULL DEFAULT '',
  `log_observacao` char(90) NOT NULL DEFAULT '',
  `bai_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_mensagens`
--

CREATE TABLE IF NOT EXISTS `tab_mensagens` (
  `men_id` int(10) unsigned NOT NULL,
  `men_parametros` text DEFAULT NULL,
  `men_anexos` text DEFAULT NULL,
  `men_data_hora_geracao` datetime DEFAULT NULL,
  `men_data_hora_envio` datetime DEFAULT NULL,
  `cli_id` mediumint(8) unsigned DEFAULT NULL,
  `usu_id` smallint(5) unsigned DEFAULT NULL,
  `mlm_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `mel_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `mes_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_mensagens_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_mensagens_comentarios` (
  `mec_id` int(10) unsigned NOT NULL,
  `mec_datahora` datetime DEFAULT NULL,
  `mec_conteudo` text DEFAULT NULL,
  `men_id` int(10) unsigned NOT NULL DEFAULT 0,
  `mcc_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_mensagens_comentarios_categorias`
--

CREATE TABLE IF NOT EXISTS `tab_mensagens_comentarios_categorias` (
  `mcc_id` tinyint(3) unsigned NOT NULL,
  `mcc_nome` char(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_mensagens_envios`
--

CREATE TABLE IF NOT EXISTS `tab_mensagens_envios` (
  `mee_id` int(10) unsigned NOT NULL,
  `mee_datahora` datetime DEFAULT NULL,
  `men_id` int(10) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_mensagens_links`
--

CREATE TABLE IF NOT EXISTS `tab_mensagens_links` (
  `mei_id` mediumint(11) unsigned NOT NULL,
  `mei_md5` char(32) DEFAULT NULL,
  `mei_url_modulo` char(10) DEFAULT NULL,
  `mei_url_acao` char(10) DEFAULT NULL,
  `mei_url_id` int(10) unsigned DEFAULT NULL,
  `mei_datahora_visualizacao` datetime DEFAULT NULL,
  `men_id` int(10) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_mensagens_lotes`
--

CREATE TABLE IF NOT EXISTS `tab_mensagens_lotes` (
  `mel_id` tinyint(3) unsigned NOT NULL,
  `mel_nome` char(50) DEFAULT NULL,
  `mel_parametros` text DEFAULT NULL,
  `mel_data_inicio` date DEFAULT NULL,
  `mel_data_termino` date DEFAULT NULL,
  `mel_sistema` tinyint(3) unsigned DEFAULT NULL,
  `mlt_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `mlm_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `mls_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_mensagens_lotes_campos`
--

CREATE TABLE IF NOT EXISTS `tab_mensagens_lotes_campos` (
  `mla_id` tinyint(3) unsigned NOT NULL,
  `mla_nome` char(32) DEFAULT NULL,
  `mla_parametros` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_mensagens_lotes_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_mensagens_lotes_comentarios` (
  `mlc_id` mediumint(8) unsigned NOT NULL,
  `mlc_datahora` datetime DEFAULT NULL,
  `mlc_conteudo` text DEFAULT NULL,
  `mel_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `mlo_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_mensagens_lotes_comentarios_categorias`
--

CREATE TABLE IF NOT EXISTS `tab_mensagens_lotes_comentarios_categorias` (
  `mlo_id` tinyint(3) unsigned NOT NULL,
  `mlo_nome` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_mensagens_lotes_filtros`
--

CREATE TABLE IF NOT EXISTS `tab_mensagens_lotes_filtros` (
  `mli_id` tinyint(3) unsigned NOT NULL,
  `mli_nome` char(40) DEFAULT NULL,
  `mli_parametros` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_mensagens_lotes_modelos`
--

CREATE TABLE IF NOT EXISTS `tab_mensagens_lotes_modelos` (
  `mlm_id` tinyint(3) unsigned NOT NULL,
  `mlm_nome` char(40) DEFAULT NULL,
  `mlm_conteudo_texto` text DEFAULT NULL,
  `mlm_conteudo_html` text DEFAULT NULL,
  `mlm_versao` tinyint(3) unsigned DEFAULT NULL,
  `mlt_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `mlm_pai` tinyint(3) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_mensagens_lotes_status`
--

CREATE TABLE IF NOT EXISTS `tab_mensagens_lotes_status` (
  `mls_id` tinyint(3) unsigned NOT NULL,
  `mls_nome` char(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_mensagens_lotes_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_mensagens_lotes_tipos` (
  `mlt_id` tinyint(3) unsigned NOT NULL,
  `mlt_nome` char(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_mensagens_status`
--

CREATE TABLE IF NOT EXISTS `tab_mensagens_status` (
  `mes_id` tinyint(3) unsigned NOT NULL,
  `mes_nome` char(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_menus`
--

CREATE TABLE IF NOT EXISTS `tab_menus` (
  `men_id` tinyint(3) unsigned NOT NULL,
  `men_nome` char(30) NOT NULL DEFAULT '',
  `men_modulo` char(30) DEFAULT NULL,
  `men_acao` char(30) DEFAULT NULL,
  `men_url` char(60) DEFAULT NULL,
  `men_target` char(30) DEFAULT NULL,
  `men_ordem` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `men_pai` tinyint(3) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_modulos`
--

CREATE TABLE IF NOT EXISTS `tab_modulos` (
  `mod_id` tinyint(3) unsigned NOT NULL,
  `mod_nome` char(35) NOT NULL DEFAULT '',
  `mod_descricao` char(35) NOT NULL DEFAULT '',
  `mos_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_modulos_status`
--

CREATE TABLE IF NOT EXISTS `tab_modulos_status` (
  `mos_id` tinyint(3) unsigned NOT NULL,
  `mos_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_operacoes`
--

CREATE TABLE IF NOT EXISTS `tab_operacoes` (
  `ope_id` mediumint(8) unsigned NOT NULL,
  `ope_inicio` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ope_termino` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `opl_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_operacoes_contatos`
--

CREATE TABLE IF NOT EXISTS `tab_operacoes_contatos` (
  `opc_id` mediumint(8) unsigned NOT NULL,
  `opc_inicio` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `opc_termino` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `opc_comentario` text DEFAULT NULL,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ope_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `occ_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_operacoes_contatos_categorias`
--

CREATE TABLE IF NOT EXISTS `tab_operacoes_contatos_categorias` (
  `occ_id` tinyint(3) unsigned NOT NULL,
  `occ_nome` char(43) NOT NULL DEFAULT '',
  `oli_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_operacoes_lotes`
--

CREATE TABLE IF NOT EXISTS `tab_operacoes_lotes` (
  `opl_id` smallint(5) unsigned NOT NULL,
  `opl_nome` char(64) NOT NULL DEFAULT '',
  `opl_regras` text DEFAULT NULL,
  `opl_recuperacao_vencimento` date DEFAULT NULL,
  `ols_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `oli_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_operacoes_lotes_filtros`
--

CREATE TABLE IF NOT EXISTS `tab_operacoes_lotes_filtros` (
  `olf_id` tinyint(3) unsigned NOT NULL,
  `olf_nome` char(60) NOT NULL DEFAULT '',
  `olf_parametros` text DEFAULT NULL,
  `oli_id` tinyint(3) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_operacoes_lotes_periodos`
--

CREATE TABLE IF NOT EXISTS `tab_operacoes_lotes_periodos` (
  `olp_id` tinyint(3) unsigned NOT NULL,
  `olp_nome` varchar(8) NOT NULL DEFAULT '',
  `olp_inicio` char(4) NOT NULL DEFAULT '',
  `olp_termino` char(4) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_operacoes_lotes_responsabilidades`
--

CREATE TABLE IF NOT EXISTS `tab_operacoes_lotes_responsabilidades` (
  `olr_id` mediumint(8) unsigned NOT NULL,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `opl_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_operacoes_lotes_sistemas`
--

CREATE TABLE IF NOT EXISTS `tab_operacoes_lotes_sistemas` (
  `oli_id` tinyint(3) unsigned NOT NULL,
  `oli_nome` varchar(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_operacoes_lotes_status`
--

CREATE TABLE IF NOT EXISTS `tab_operacoes_lotes_status` (
  `ols_id` tinyint(3) unsigned NOT NULL,
  `ols_nome` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_operacoes_scripts`
--

CREATE TABLE IF NOT EXISTS `tab_operacoes_scripts` (
  `ops_id` smallint(5) unsigned NOT NULL,
  `ops_titulo` varchar(80) NOT NULL DEFAULT '',
  `ops_conteudo` longtext DEFAULT NULL,
  `ost_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `osf_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `osa_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `oli_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_operacoes_scripts_formatos`
--

CREATE TABLE IF NOT EXISTS `tab_operacoes_scripts_formatos` (
  `osf_id` tinyint(3) unsigned NOT NULL,
  `osf_nome` char(5) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_operacoes_scripts_status`
--

CREATE TABLE IF NOT EXISTS `tab_operacoes_scripts_status` (
  `osa_id` tinyint(3) unsigned NOT NULL,
  `osa_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_operacoes_scripts_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_operacoes_scripts_tipos` (
  `ost_id` tinyint(3) unsigned NOT NULL,
  `ost_nome` char(9) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_organizacao_funcoes`
--

CREATE TABLE IF NOT EXISTS `tab_organizacao_funcoes` (
  `orf_id` tinyint(3) unsigned NOT NULL,
  `orf_nome` char(11) NOT NULL DEFAULT '',
  `ofs_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_organizacao_funcoes_status`
--

CREATE TABLE IF NOT EXISTS `tab_organizacao_funcoes_status` (
  `ofs_id` tinyint(3) unsigned NOT NULL,
  `ofs_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_organizacao_setores`
--

CREATE TABLE IF NOT EXISTS `tab_organizacao_setores` (
  `ors_id` tinyint(3) unsigned NOT NULL,
  `ors_nome` char(26) NOT NULL DEFAULT '',
  `ors_pai` tinyint(3) unsigned DEFAULT NULL,
  `oss_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_organizacao_setores_status`
--

CREATE TABLE IF NOT EXISTS `tab_organizacao_setores_status` (
  `oss_id` tinyint(3) unsigned NOT NULL,
  `oss_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_pesquisas`
--

CREATE TABLE IF NOT EXISTS `tab_pesquisas` (
  `pes_id` smallint(5) unsigned NOT NULL,
  `pes_descricao` char(50) NOT NULL DEFAULT '',
  `pes_texto_inicial` text DEFAULT NULL,
  `pes_texto_final` text DEFAULT NULL,
  `pes_data_inicio` date DEFAULT NULL,
  `pes_data_termino` date DEFAULT NULL,
  `pet_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_pesquisas_aplicacoes`
--

CREATE TABLE IF NOT EXISTS `tab_pesquisas_aplicacoes` (
  `pea_id` mediumint(8) unsigned NOT NULL,
  `pea_cadastro` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pea_inicio` datetime DEFAULT NULL,
  `pea_termino` datetime DEFAULT NULL,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cha_id` mediumint(8) unsigned DEFAULT NULL,
  `cor_id` mediumint(8) unsigned DEFAULT NULL,
  `usu_id` smallint(5) unsigned DEFAULT NULL,
  `pes_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_pesquisas_aplicacoes_respostas`
--

CREATE TABLE IF NOT EXISTS `tab_pesquisas_aplicacoes_respostas` (
  `par_id` mediumint(8) unsigned NOT NULL,
  `par_valor_qualitativo` text DEFAULT NULL,
  `par_valor_quantitativo` float DEFAULT NULL,
  `par_valor_tabulado` text DEFAULT NULL,
  `pea_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `pqa_id` smallint(5) unsigned DEFAULT NULL,
  `peq_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_pesquisas_questoes`
--

CREATE TABLE IF NOT EXISTS `tab_pesquisas_questoes` (
  `peq_id` smallint(5) unsigned NOT NULL,
  `peq_numero` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `peq_conteudo` text DEFAULT NULL,
  `peq_minimo` float DEFAULT 0,
  `peq_maximo` float DEFAULT 0,
  `peq_maximo_alternativas` tinyint(3) unsigned DEFAULT 0,
  `pes_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `pvt_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `pqt_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_pesquisas_questoes_alternativas`
--

CREATE TABLE IF NOT EXISTS `tab_pesquisas_questoes_alternativas` (
  `pqa_id` smallint(5) unsigned NOT NULL,
  `pqa_ordem` tinyint(4) NOT NULL DEFAULT 0,
  `pqa_conteudo` text DEFAULT NULL,
  `pqa_minimo` float DEFAULT NULL,
  `pqa_maximo` float DEFAULT NULL,
  `pqa_eliminar` char(128) DEFAULT NULL,
  `peq_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_pesquisas_questoes_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_pesquisas_questoes_tipos` (
  `pqt_id` tinyint(3) unsigned NOT NULL,
  `pqt_nome` char(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_pesquisas_status`
--

CREATE TABLE IF NOT EXISTS `tab_pesquisas_status` (
  `pet_id` tinyint(3) unsigned NOT NULL,
  `pet_nome` char(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_pesquisas_variaveis_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_pesquisas_variaveis_tipos` (
  `pvt_id` tinyint(3) unsigned NOT NULL,
  `pvt_nome` char(23) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_placares`
--

CREATE TABLE IF NOT EXISTS `tab_placares` (
  `plc_id` mediumint(8) unsigned NOT NULL,
  `plc_data` date NOT NULL DEFAULT '0000-00-00',
  `plc_geracao` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `plc_conteudo` mediumtext DEFAULT NULL,
  `plb_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_placares_abas`
--

CREATE TABLE IF NOT EXISTS `tab_placares_abas` (
  `plb_id` tinyint(3) unsigned NOT NULL,
  `plb_nome` char(14) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_planos`
--

CREATE TABLE IF NOT EXISTS `tab_planos` (
  `pla_id` smallint(5) unsigned NOT NULL,
  `pla_nome` char(60) NOT NULL DEFAULT '',
  `pla_instalacao` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `pla_mensalidade` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `pla_parcelamento_maximo` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `pla_tipo_contrato_principal` tinyint(3) unsigned DEFAULT NULL,
  `pla_tipo_migracao` tinyint(3) unsigned DEFAULT NULL,
  `pla_parametros` text DEFAULT NULL,
  `con_id` smallint(5) unsigned DEFAULT NULL,
  `sac_id` tinyint(3) unsigned DEFAULT NULL,
  `pli_id` tinyint(3) unsigned DEFAULT NULL,
  `pls_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `plt_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `plu_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_planos_equipamentos`
--

CREATE TABLE IF NOT EXISTS `tab_planos_equipamentos` (
  `ple_id` smallint(5) unsigned NOT NULL,
  `ple_prazo` tinyint(3) unsigned DEFAULT NULL,
  `pla_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `equ_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_planos_servicos`
--

CREATE TABLE IF NOT EXISTS `tab_planos_servicos` (
  `pls_id` tinyint(3) unsigned NOT NULL,
  `pls_nome` char(34) NOT NULL DEFAULT '',
  `pls_parametros` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_planos_sistemas`
--

CREATE TABLE IF NOT EXISTS `tab_planos_sistemas` (
  `pli_id` tinyint(3) unsigned NOT NULL,
  `pli_nome` char(8) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_planos_status`
--

CREATE TABLE IF NOT EXISTS `tab_planos_status` (
  `plu_id` tinyint(3) unsigned NOT NULL,
  `plu_nome` char(7) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_planos_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_planos_tipos` (
  `plt_id` tinyint(3) unsigned NOT NULL,
  `plt_nome` char(9) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_planos_vinculos`
--

CREATE TABLE IF NOT EXISTS `tab_planos_vinculos` (
  `plv_id` smallint(5) unsigned NOT NULL,
  `pla_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `pla_filho` smallint(5) unsigned NOT NULL DEFAULT 0,
  `pva_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `pvt_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_planos_vinculos_aplicacoes`
--

CREATE TABLE IF NOT EXISTS `tab_planos_vinculos_aplicacoes` (
  `pva_id` tinyint(3) unsigned NOT NULL,
  `pva_nome` char(14) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_planos_vinculos_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_planos_vinculos_tipos` (
  `pvt_id` tinyint(3) unsigned NOT NULL,
  `pvt_nome` char(26) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1 PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_recuperacao_contatos`
--

CREATE TABLE IF NOT EXISTS `tab_recuperacao_contatos` (
  `rec_id` mediumint(8) unsigned NOT NULL,
  `rec_inicio` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rec_termino` datetime DEFAULT NULL,
  `rec_comentario` text DEFAULT NULL,
  `coa_id` mediumint(8) unsigned DEFAULT NULL,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `rct_id` tinyint(3) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_recuperacao_contatos_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_recuperacao_contatos_tipos` (
  `rct_id` tinyint(3) unsigned NOT NULL,
  `rct_nome` char(24) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_redes`
--

CREATE TABLE IF NOT EXISTS `tab_redes` (
  `red_id` smallint(5) unsigned NOT NULL,
  `red_endereco_ip` char(15) NOT NULL DEFAULT '',
  `red_mascara` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `red_publicar_dhcp` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `red_reserva_ips` tinyint(3) unsigned DEFAULT NULL,
  `ser_id` smallint(5) unsigned DEFAULT NULL,
  `sei_id` smallint(5) unsigned DEFAULT NULL,
  `red_pai` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servicos_ajustes`
--

CREATE TABLE IF NOT EXISTS `tab_servicos_ajustes` (
  `saj_id` mediumint(8) unsigned NOT NULL,
  `saj_inicio` datetime DEFAULT NULL,
  `saj_termino` datetime DEFAULT NULL,
  `saj_duracao` tinyint(3) unsigned DEFAULT NULL,
  `saj_valor` mediumint(8) unsigned DEFAULT NULL,
  `saj_manual` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `saj_pai` mediumint(8) unsigned DEFAULT NULL,
  `cli_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ass_id` mediumint(8) unsigned DEFAULT NULL,
  `ajf_id` mediumint(8) unsigned DEFAULT NULL,
  `sac_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sas_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servicos_ajustes_categorias`
--

CREATE TABLE IF NOT EXISTS `tab_servicos_ajustes_categorias` (
  `sac_id` tinyint(3) unsigned NOT NULL,
  `sac_nome` char(60) NOT NULL DEFAULT '',
  `sac_regras` text DEFAULT NULL,
  `sac_valor` mediumint(8) unsigned DEFAULT NULL,
  `sac_duracao` tinyint(3) unsigned DEFAULT NULL,
  `sac_editavel` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sac_sistema` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sac_plus` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sac_status` tinyint(3) unsigned NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servicos_ajustes_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_servicos_ajustes_comentarios` (
  `sam_id` mediumint(8) unsigned NOT NULL,
  `sam_datahora` datetime DEFAULT NULL,
  `sam_conteudo` text DEFAULT NULL,
  `saj_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servicos_ajustes_regras`
--

CREATE TABLE IF NOT EXISTS `tab_servicos_ajustes_regras` (
  `sar_id` mediumint(8) unsigned NOT NULL,
  `sar_dia_semana` tinyint(3) unsigned DEFAULT NULL,
  `sar_hora_inicio` time DEFAULT NULL,
  `sar_hora_termino` time DEFAULT NULL,
  `sar_ajuste` float(10,2) NOT NULL DEFAULT 0.00,
  `saj_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `sat_id` tinyint(3) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servicos_ajustes_status`
--

CREATE TABLE IF NOT EXISTS `tab_servicos_ajustes_status` (
  `sas_id` tinyint(3) unsigned NOT NULL,
  `sas_nome` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servicos_ajustes_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_servicos_ajustes_tipos` (
  `sat_id` tinyint(3) unsigned NOT NULL,
  `sat_nome` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores`
--

CREATE TABLE IF NOT EXISTS `tab_servidores` (
  `ser_id` smallint(5) unsigned NOT NULL,
  `ser_nome` char(60) DEFAULT NULL,
  `ser_endereco_ip` tinyint(3) unsigned DEFAULT NULL,
  `ser_ip_secundario` varchar(15) DEFAULT NULL,
  `ser_configuracao` text DEFAULT NULL,
  `ser_data_hora_situacao` datetime DEFAULT NULL,
  `ser_data_hora_publicacao` datetime DEFAULT NULL,
  `ser_data_hora_publicacao_inicio` datetime DEFAULT NULL,
  `ser_data_hora_publicacao_termino` datetime DEFAULT NULL,
  `ser_publicacao_versao` tinyint(3) unsigned NOT NULL DEFAULT 2,
  `ser_publicando` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ser_publicado` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ser_ajustado` tinyint(3) unsigned DEFAULT NULL,
  `cha_id` mediumint(8) unsigned DEFAULT NULL,
  `sei_id` smallint(5) unsigned DEFAULT NULL,
  `sso_id` tinyint(3) unsigned DEFAULT NULL,
  `sep_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `seu_id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `set_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_interfaces`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_interfaces` (
  `sei_id` smallint(5) unsigned NOT NULL,
  `sei_nome` char(32) NOT NULL DEFAULT '',
  `sei_descricao` text DEFAULT NULL,
  `sei_classe` char(1) NOT NULL DEFAULT '',
  `sei_prefixo_ip` char(11) DEFAULT NULL,
  `sei_mascara_rede` char(15) DEFAULT NULL,
  `sei_endereco_ip` tinyint(3) unsigned DEFAULT NULL,
  `sei_reserva_ips` tinyint(3) unsigned DEFAULT NULL,
  `sei_configuracao` text DEFAULT NULL,
  `sei_pai` smallint(5) unsigned DEFAULT NULL,
  `ser_id` smallint(5) unsigned DEFAULT NULL,
  `sit_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sip_id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `sia_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_interfaces_padroes`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_interfaces_padroes` (
  `sip_id` tinyint(3) unsigned NOT NULL,
  `sip_nome` char(13) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_interfaces_status`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_interfaces_status` (
  `sia_id` tinyint(3) unsigned NOT NULL,
  `sia_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_interfaces_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_interfaces_tipos` (
  `sit_id` tinyint(3) unsigned NOT NULL,
  `sit_nome` char(26) NOT NULL DEFAULT '',
  `sit_sigla` char(2) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_links`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_links` (
  `sel_id` mediumint(8) unsigned NOT NULL,
  `sel_nome` char(80) NOT NULL DEFAULT '',
  `sel_endereco_ip` tinyint(3) unsigned DEFAULT NULL,
  `sel_download` int(10) unsigned NOT NULL DEFAULT 0,
  `sel_upload` int(10) unsigned NOT NULL DEFAULT 0,
  `sei_id` smallint(5) unsigned DEFAULT NULL,
  `sls_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_links_status`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_links_status` (
  `sls_id` tinyint(3) unsigned NOT NULL,
  `sls_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_servicos`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_servicos` (
  `ses_id` smallint(5) unsigned NOT NULL,
  `ses_data_alteracao` datetime DEFAULT NULL,
  `ses_data_publicacao` datetime DEFAULT NULL,
  `ses_parametros` text DEFAULT NULL,
  `sei_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `sst_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sss_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_servicos_status`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_servicos_status` (
  `sss_id` tinyint(3) unsigned NOT NULL,
  `sss_nome` char(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_servicos_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_servicos_tipos` (
  `sst_id` tinyint(3) unsigned NOT NULL,
  `sst_nome` char(35) NOT NULL DEFAULT '',
  `sst_servico` char(35) DEFAULT NULL,
  `sst_comando` char(35) DEFAULT NULL,
  `sst_exportar` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sst_disponivel` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `sit_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_sistemas_operacionais`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_sistemas_operacionais` (
  `sso_id` tinyint(3) unsigned NOT NULL,
  `sso_nome` char(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_situacoes`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_situacoes` (
  `seu_id` tinyint(3) unsigned NOT NULL,
  `seu_nome` char(24) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1 PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_ssids`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_ssids` (
  `ssi_id` smallint(5) unsigned NOT NULL,
  `ssi_nome` char(32) NOT NULL DEFAULT '',
  `ssi_ip` char(15) DEFAULT NULL,
  `ssi_instalavel` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `sst_id` tinyint(3) unsigned NOT NULL DEFAULT 2,
  `sei_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_ssids_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_ssids_tipos` (
  `sst_id` tinyint(3) unsigned NOT NULL,
  `sst_nome` char(12) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_status`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_status` (
  `set_id` tinyint(3) unsigned NOT NULL,
  `set_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_servidores_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_servidores_tipos` (
  `sep_id` tinyint(3) unsigned NOT NULL,
  `sep_nome` char(12) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_sessoes`
--

CREATE TABLE IF NOT EXISTS `tab_sessoes` (
  `ses_id` char(32) NOT NULL DEFAULT '',
  `ses_datahora_inicio` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ses_datahora_termino` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ses_url` char(250) NOT NULL DEFAULT '',
  `ses_ip` char(15) NOT NULL DEFAULT '',
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_tarefas`
--

CREATE TABLE IF NOT EXISTS `tab_tarefas` (
  `tar_id` int(11) unsigned NOT NULL,
  `tar_titulo` char(50) NOT NULL DEFAULT '',
  `tar_abertura` datetime DEFAULT NULL,
  `tar_prazo_inicio` datetime DEFAULT NULL,
  `tar_prazo_termino` datetime DEFAULT NULL,
  `tar_previsao_inicio` datetime DEFAULT NULL,
  `tar_previsao_termino` datetime DEFAULT NULL,
  `tar_fechamento` datetime DEFAULT NULL,
  `tar_andamento` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL,
  `ors_id` tinyint(3) unsigned DEFAULT NULL,
  `orf_id` tinyint(3) unsigned DEFAULT NULL,
  `tat_id` tinyint(3) unsigned DEFAULT NULL,
  `tas_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_tarefas_etapas`
--

CREATE TABLE IF NOT EXISTS `tab_tarefas_etapas` (
  `tae_id` int(11) unsigned NOT NULL,
  `tae_inicio` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `tae_termino` datetime DEFAULT NULL,
  `tae_parametros` text DEFAULT NULL,
  `tar_id` int(11) unsigned NOT NULL DEFAULT 0,
  `ors_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `orf_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `tet_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `tes_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_tarefas_etapas_status`
--

CREATE TABLE IF NOT EXISTS `tab_tarefas_etapas_status` (
  `tes_id` tinyint(3) unsigned NOT NULL,
  `tes_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_tarefas_etapas_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_tarefas_etapas_tipos` (
  `tet_id` tinyint(3) unsigned NOT NULL,
  `tet_nome` char(26) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_tarefas_status`
--

CREATE TABLE IF NOT EXISTS `tab_tarefas_status` (
  `tas_id` tinyint(3) unsigned NOT NULL,
  `tas_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_tarefas_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_tarefas_tipos` (
  `tat_id` tinyint(3) unsigned NOT NULL,
  `tat_nome` char(22) NOT NULL DEFAULT '',
  `tat_disponivel` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ors_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_usuarios`
--

CREATE TABLE IF NOT EXISTS `tab_usuarios` (
  `usu_id` smallint(5) unsigned NOT NULL,
  `usu_nome` char(50) NOT NULL DEFAULT '',
  `usu_cpf` char(15) DEFAULT NULL,
  `usu_nascimento` date DEFAULT NULL,
  `usu_numero` smallint(5) unsigned DEFAULT NULL,
  `usu_complemento` char(40) DEFAULT NULL,
  `usu_tel_residencial` char(15) DEFAULT NULL,
  `usu_tel_celular` char(15) DEFAULT NULL,
  `usu_ramal` char(4) DEFAULT NULL,
  `usu_email` char(40) DEFAULT NULL,
  `usu_login` char(16) DEFAULT NULL,
  `usu_senha` char(32) DEFAULT NULL,
  `usu_agente_id` char(4) DEFAULT NULL,
  `usu_agente_senha` char(4) DEFAULT NULL,
  `usu_senha_discagem` char(4) DEFAULT NULL,
  `usu_comentarios` text DEFAULT NULL,
  `usu_administrador` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cep_id` int(11) unsigned DEFAULT NULL,
  `log_id` mediumint(5) unsigned DEFAULT NULL,
  `bai_id` smallint(5) unsigned DEFAULT NULL,
  `cid_id` smallint(5) unsigned DEFAULT NULL,
  `est_id` tinyint(3) unsigned DEFAULT NULL,
  `clo_id` int(10) unsigned NOT NULL DEFAULT 0,
  `uss_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_usuarios_status`
--

CREATE TABLE IF NOT EXISTS `tab_usuarios_status` (
  `uss_id` tinyint(3) unsigned NOT NULL,
  `uss_nome` char(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_vendas`
--

CREATE TABLE IF NOT EXISTS `tab_vendas` (
  `ven_id` mediumint(8) unsigned NOT NULL,
  `ven_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ven_primeiro_pagamento` date DEFAULT NULL,
  `ven_pontuacao` float(2,1) DEFAULT NULL,
  `ass_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `cor_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_vendas_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_vendas_comentarios` (
  `veo_id` mediumint(8) unsigned NOT NULL,
  `veo_datahora` datetime DEFAULT NULL,
  `veo_conteudo` text DEFAULT NULL,
  `ven_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_vendas_comissoes`
--

CREATE TABLE IF NOT EXISTS `tab_vendas_comissoes` (
  `vec_id` mediumint(8) unsigned NOT NULL,
  `vec_valor` mediumint(8) unsigned DEFAULT NULL,
  `ven_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `vct_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_vendas_comissoes_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_vendas_comissoes_comentarios` (
  `vco_id` mediumint(8) unsigned NOT NULL,
  `vco_datahora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `vco_conteudo` text DEFAULT NULL,
  `vec_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_vendas_comissoes_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_vendas_comissoes_tipos` (
  `vct_id` tinyint(3) unsigned NOT NULL,
  `vct_nome` char(33) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_visitas`
--

CREATE TABLE IF NOT EXISTS `tab_visitas` (
  `vis_id` mediumint(11) unsigned NOT NULL,
  `vis_data` date DEFAULT NULL,
  `vis_data_geracao` date DEFAULT NULL,
  `vis_agendamentos` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `con_id` smallint(5) unsigned DEFAULT NULL,
  `cli_id` mediumint(8) unsigned DEFAULT NULL,
  `cor_id` mediumint(8) unsigned DEFAULT NULL,
  `cha_id` mediumint(8) unsigned DEFAULT NULL,
  `usu_id` smallint(5) unsigned DEFAULT NULL,
  `vip_id` tinyint(3) unsigned DEFAULT NULL,
  `vit_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `via_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_visitas_comentarios`
--

CREATE TABLE IF NOT EXISTS `tab_visitas_comentarios` (
  `vic_id` mediumint(8) unsigned NOT NULL,
  `vic_datahora` datetime DEFAULT NULL,
  `vic_conteudo` text DEFAULT NULL,
  `vcc_id` tinyint(3) unsigned DEFAULT NULL,
  `vis_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `usu_id` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_visitas_comentarios_categorias`
--

CREATE TABLE IF NOT EXISTS `tab_visitas_comentarios_categorias` (
  `vcc_id` tinyint(3) unsigned NOT NULL,
  `vcc_nome` char(32) NOT NULL DEFAULT '',
  `vcc_tipo` tinyint(3) unsigned NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_visitas_cotas`
--

CREATE TABLE IF NOT EXISTS `tab_visitas_cotas` (
  `vic_id` smallint(11) unsigned NOT NULL,
  `vic_data` date NOT NULL DEFAULT '0000-00-00',
  `vic_periodo` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `vic_cota_residencia` tinyint(3) unsigned DEFAULT NULL,
  `vic_cota_condominio` tinyint(3) unsigned DEFAULT NULL,
  `vit_id` tinyint(3) unsigned NOT NULL DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_visitas_periodos`
--

CREATE TABLE IF NOT EXISTS `tab_visitas_periodos` (
  `vip_id` tinyint(3) unsigned NOT NULL,
  `vip_nome` char(5) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_visitas_status`
--

CREATE TABLE IF NOT EXISTS `tab_visitas_status` (
  `via_id` tinyint(3) unsigned NOT NULL,
  `via_nome` char(22) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_visitas_tipos`
--

CREATE TABLE IF NOT EXISTS `tab_visitas_tipos` (
  `vit_id` tinyint(3) unsigned NOT NULL,
  `vit_nome` char(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `log_integracao`
--
ALTER TABLE `log_integracao`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `rel_ass_cor_estacontido`
--
ALTER TABLE `rel_ass_cor_estacontido`
  ADD PRIMARY KEY (`ass_id`,`cor_id`),
  ADD KEY `IX_rel_ass_cor` (`ass_id`),
  ADD KEY `IX_rel_cor_ass` (`cor_id`);

--
-- Indexes for table `rel_cam_cac_contem`
--
ALTER TABLE `rel_cam_cac_contem`
  ADD PRIMARY KEY (`cam_id`,`cac_id`),
  ADD KEY `fk_tab_campanhas_contatos_has_tab_campanhas_tab_campanhas1` (`cam_id`),
  ADD KEY `fk_tab_campanhas_contatos_has_tab_campanhas_tab_campanhas_con1` (`cac_id`);

--
-- Indexes for table `rel_cha_caa_contem`
--
ALTER TABLE `rel_cha_caa_contem`
  ADD PRIMARY KEY (`cha_id`,`caa_id`),
  ADD KEY `caa_id` (`caa_id`);

--
-- Indexes for table `rel_cha_usu_ehacompanhado`
--
ALTER TABLE `rel_cha_usu_ehacompanhado`
  ADD PRIMARY KEY (`cha_id`,`usu_id`),
  ADD KEY `IX_rel_cha_usu` (`cha_id`),
  ADD KEY `IX_rel_usu_cha` (`usu_id`);

--
-- Indexes for table `rel_cha_usu_ehfavorito`
--
ALTER TABLE `rel_cha_usu_ehfavorito`
  ADD PRIMARY KEY (`cha_id`,`usu_id`),
  ADD KEY `IX_rel_cha_usu` (`cha_id`),
  ADD KEY `IX_rel_usu_cha` (`usu_id`);

--
-- Indexes for table `rel_clo_cid_contem`
--
ALTER TABLE `rel_clo_cid_contem`
  ADD PRIMARY KEY (`clo_id`,`cid_id`),
  ADD KEY `rel_clo_cid_contem_cid_id` (`cid_id`);

--
-- Indexes for table `rel_coa_coe_estacontido`
--
ALTER TABLE `rel_coa_coe_estacontido`
  ADD PRIMARY KEY (`coa_id`,`coe_id`),
  ADD KEY `IX_rel_coa_coe` (`coa_id`),
  ADD KEY `IX_rel_coe_coa` (`coe_id`);

--
-- Indexes for table `rel_cof_clo_disponivel`
--
ALTER TABLE `rel_cof_clo_disponivel`
  ADD PRIMARY KEY (`cof_id`,`clo_id`),
  ADD KEY `clo_id` (`clo_id`);

--
-- Indexes for table `rel_con_pla_tempermissao`
--
ALTER TABLE `rel_con_pla_tempermissao`
  ADD PRIMARY KEY (`con_id`,`pla_id`),
  ADD KEY `IX_rel_pla_con` (`pla_id`);

--
-- Indexes for table `rel_cov_cod_estacontido`
--
ALTER TABLE `rel_cov_cod_estacontido`
  ADD PRIMARY KEY (`cov_id`,`cod_id`),
  ADD KEY `IX_rel_cod_cov` (`cod_id`);

--
-- Indexes for table `rel_dec_cor_estacontido`
--
ALTER TABLE `rel_dec_cor_estacontido`
  ADD PRIMARY KEY (`dec_id`,`cor_id`),
  ADD KEY `IX_rel_dec_cor` (`dec_id`),
  ADD KEY `IX_rel_cor_dec` (`cor_id`);

--
-- Indexes for table `rel_equ_cor_comodato`
--
ALTER TABLE `rel_equ_cor_comodato`
  ADD PRIMARY KEY (`equ_id`,`cor_id`),
  ADD KEY `IX_rel_equ_cor` (`equ_id`),
  ADD KEY `IX_rel_cor_equ` (`cor_id`);

--
-- Indexes for table `rel_equ_pla_comodato`
--
ALTER TABLE `rel_equ_pla_comodato`
  ADD PRIMARY KEY (`equ_id`,`pla_id`),
  ADD KEY `IX_rel_equ_pla` (`equ_id`),
  ADD KEY `IX_rel_pla_equ` (`pla_id`);

--
-- Indexes for table `rel_equ_pls_default`
--
ALTER TABLE `rel_equ_pls_default`
  ADD PRIMARY KEY (`equ_id`,`pls_id`),
  ADD KEY `IX_rel_equ_pls` (`equ_id`),
  ADD KEY `IX_rel_pls_equ` (`pls_id`);

--
-- Indexes for table `rel_equ_pls_disponivel`
--
ALTER TABLE `rel_equ_pls_disponivel`
  ADD PRIMARY KEY (`equ_id`,`pls_id`),
  ADD KEY `IX_rel_equ_pls` (`equ_id`),
  ADD KEY `IX_rel_pls_equ` (`pls_id`);

--
-- Indexes for table `rel_esf_esi_fornece`
--
ALTER TABLE `rel_esf_esi_fornece`
  ADD PRIMARY KEY (`esf_id`,`esi_id`),
  ADD KEY `IX_rel_esi_esf` (`esi_id`),
  ADD KEY `IX_rel_esf_esi` (`esf_id`);

--
-- Indexes for table `rel_fop_cor_estacontido`
--
ALTER TABLE `rel_fop_cor_estacontido`
  ADD PRIMARY KEY (`fop_id`,`cor_id`),
  ADD KEY `IX_rel_fop_cor` (`fop_id`),
  ADD KEY `IX_rel_cor_fop` (`cor_id`);

--
-- Indexes for table `rel_gru_aco_tempermissao`
--
ALTER TABLE `rel_gru_aco_tempermissao`
  ADD PRIMARY KEY (`gru_id`,`aco_id`),
  ADD KEY `IX_rel_gru_aco` (`gru_id`),
  ADD KEY `IX_rel_aco_gru` (`aco_id`);

--
-- Indexes for table `rel_saj_ass_contem`
--
ALTER TABLE `rel_saj_ass_contem`
  ADD PRIMARY KEY (`saj_id`,`ass_id`),
  ADD KEY `IX_rel_saj_ass` (`saj_id`),
  ADD KEY `IX_rel_ass_saj` (`ass_id`);

--
-- Indexes for table `rel_saj_cau_contem`
--
ALTER TABLE `rel_saj_cau_contem`
  ADD PRIMARY KEY (`saj_id`,`cau_id`),
  ADD KEY `IX_rel_cau_saj` (`cau_id`),
  ADD KEY `IX_rel_saj_cau` (`saj_id`);

--
-- Indexes for table `rel_saj_cip_contem`
--
ALTER TABLE `rel_saj_cip_contem`
  ADD PRIMARY KEY (`saj_id`,`cip_id`),
  ADD KEY `IX_rel_cau_saj` (`cip_id`),
  ADD KEY `IX_rel_saj_cau` (`saj_id`);

--
-- Indexes for table `rel_saj_clb_contem`
--
ALTER TABLE `rel_saj_clb_contem`
  ADD PRIMARY KEY (`saj_id`,`clb_id`),
  ADD KEY `IX_rel_clb_saj` (`clb_id`),
  ADD KEY `IX_rel_saj_clb` (`saj_id`);

--
-- Indexes for table `rel_saj_cle_contem`
--
ALTER TABLE `rel_saj_cle_contem`
  ADD PRIMARY KEY (`saj_id`,`cle_id`),
  ADD KEY `IX_rel_cle_saj` (`cle_id`),
  ADD KEY `IX_rel_saj_cle` (`saj_id`);

--
-- Indexes for table `rel_saj_clh_contem`
--
ALTER TABLE `rel_saj_clh_contem`
  ADD PRIMARY KEY (`saj_id`,`clh_id`),
  ADD KEY `IX_rel_clh_saj` (`clh_id`),
  ADD KEY `IX_rel_saj_clh` (`saj_id`);

--
-- Indexes for table `rel_saj_clp_contem`
--
ALTER TABLE `rel_saj_clp_contem`
  ADD PRIMARY KEY (`saj_id`,`clp_id`),
  ADD KEY `IX_rel_clp_saj` (`clp_id`),
  ADD KEY `IX_rel_saj_clp` (`saj_id`);

--
-- Indexes for table `rel_tar_usu_aguardaciencia`
--
ALTER TABLE `rel_tar_usu_aguardaciencia`
  ADD PRIMARY KEY (`tar_id`,`usu_id`),
  ADD KEY `IX_rel_usu_tar_` (`usu_id`),
  ADD KEY `IX_rel_tar_usu` (`tar_id`);

--
-- Indexes for table `rel_tat_ors_orf_ehautoaprovada`
--
ALTER TABLE `rel_tat_ors_orf_ehautoaprovada`
  ADD PRIMARY KEY (`tat_id`,`ors_id`,`orf_id`),
  ADD KEY `IX_rel_ors_orf_tat` (`ors_id`),
  ADD KEY `IX_rel_orf_tat_ors` (`orf_id`),
  ADD KEY `IX_rel_tat_ors_orf` (`tat_id`);

--
-- Indexes for table `rel_usu_esg_fazparte`
--
ALTER TABLE `rel_usu_esg_fazparte`
  ADD PRIMARY KEY (`usu_id`,`esg_id`),
  ADD KEY `IX_rel_usu_esg` (`usu_id`),
  ADD KEY `IX_rel_esg_usu` (`esg_id`);

--
-- Indexes for table `rel_usu_gru_fazparte`
--
ALTER TABLE `rel_usu_gru_fazparte`
  ADD PRIMARY KEY (`usu_id`,`gru_id`),
  ADD KEY `IX_rel_usu_gru` (`usu_id`),
  ADD KEY `IX_rel_gru_usu` (`gru_id`);

--
-- Indexes for table `rel_usu_orf_ors_exerce`
--
ALTER TABLE `rel_usu_orf_ors_exerce`
  ADD PRIMARY KEY (`usu_id`,`ors_id`,`orf_id`),
  ADD KEY `IX_rel_usu_orf_ors` (`usu_id`),
  ADD KEY `IX_rel_ors_usu_orf` (`ors_id`),
  ADD KEY `IX_rel_orf_ors_usu` (`orf_id`);

--
-- Indexes for table `tab_acoes`
--
ALTER TABLE `tab_acoes`
  ADD PRIMARY KEY (`aco_id`),
  ADD KEY `IX_tab_acoes_mod_id` (`mod_id`),
  ADD KEY `IX_tab_acoes_act_id` (`act_id`);

--
-- Indexes for table `tab_acoes_tipos`
--
ALTER TABLE `tab_acoes_tipos`
  ADD PRIMARY KEY (`act_id`);

--
-- Indexes for table `tab_ajustes_financeiros`
--
ALTER TABLE `tab_ajustes_financeiros`
  ADD PRIMARY KEY (`ajf_id`),
  ADD KEY `IX_ajustes_financeiros_cli_id` (`cli_id`),
  ADD KEY `IX_ajustes_financeiros_afc_id` (`afc_id`),
  ADD KEY `IX_ajustes_financeiros_afs_id` (`afs_id`),
  ADD KEY `IX_ajustes_financeiros_aft_id` (`aft_id`),
  ADD KEY `IX_ajustes_financeiros_ass_id` (`ass_id`),
  ADD KEY `IX_ajustes_financeiros_ajf_id` (`ajf_pai`),
  ADD KEY `IX_ajustes_financeiros_cor_id` (`cor_id`),
  ADD KEY `IX_ajustes_financeiros_con_id` (`con_id`);

--
-- Indexes for table `tab_ajustes_financeiros_categorias`
--
ALTER TABLE `tab_ajustes_financeiros_categorias`
  ADD PRIMARY KEY (`afc_id`);

--
-- Indexes for table `tab_ajustes_financeiros_comentarios`
--
ALTER TABLE `tab_ajustes_financeiros_comentarios`
  ADD PRIMARY KEY (`afm_id`),
  ADD KEY `IX_rel_usu_ajm` (`usu_id`),
  ADD KEY `IX_rel_ajf_ajm` (`ajf_id`);

--
-- Indexes for table `tab_ajustes_financeiros_status`
--
ALTER TABLE `tab_ajustes_financeiros_status`
  ADD PRIMARY KEY (`afs_id`);

--
-- Indexes for table `tab_ajustes_financeiros_tipos`
--
ALTER TABLE `tab_ajustes_financeiros_tipos`
  ADD PRIMARY KEY (`aft_id`);

--
-- Indexes for table `tab_assinaturas`
--
ALTER TABLE `tab_assinaturas`
  ADD PRIMARY KEY (`ass_id`),
  ADD KEY `IX_rel_pla_ass` (`pla_id`),
  ADD KEY `IX_rel_ast_ass` (`ast_id`),
  ADD KEY `IX_rel_ass_ass` (`ass_pai`),
  ADD KEY `IX_rel_cli_ass` (`cli_id`),
  ADD KEY `IX_rel_ass_ass_migrada` (`ass_migrada`),
  ADD KEY `IX_rel_pli_ass` (`pli_id`),
  ADD KEY `ass_data_cadastro` (`ass_data_cadastro`),
  ADD KEY `ass_data_fechamento` (`ass_data_fechamento`),
  ADD KEY `ass_data_ativacao` (`ass_data_ativacao`),
  ADD KEY `ass_data_cancelamento` (`ass_data_cancelamento`),
  ADD KEY `ass_data_confirmacao` (`ass_data_confirmacao`);

--
-- Indexes for table `tab_assinaturas_status`
--
ALTER TABLE `tab_assinaturas_status`
  ADD PRIMARY KEY (`ast_id`);

--
-- Indexes for table `tab_assinaturas_vinculos`
--
ALTER TABLE `tab_assinaturas_vinculos`
  ADD PRIMARY KEY (`asv_id`),
  ADD KEY `IX_rel_ass_asv` (`ass_id`),
  ADD KEY `IX_rel_ass_asv_filha` (`ass_filha`),
  ADD KEY `IX_rel_pvt_asv` (`pvt_id`);

--
-- Indexes for table `tab_atividades`
--
ALTER TABLE `tab_atividades`
  ADD PRIMARY KEY (`ati_id`),
  ADD KEY `IX_rel_usu_ati` (`usu_id`),
  ADD KEY `IX_rel_aco_ati` (`aco_id`),
  ADD KEY `ati_parametro` (`ati_parametro`);

--
-- Indexes for table `tab_bairros`
--
ALTER TABLE `tab_bairros`
  ADD PRIMARY KEY (`bai_id`),
  ADD UNIQUE KEY `bai_cep_geral` (`bai_cep_geral`),
  ADD KEY `IX_rel_cid_bai` (`cid_id`),
  ADD KEY `bai_nome` (`bai_nome`);

--
-- Indexes for table `tab_campanhas`
--
ALTER TABLE `tab_campanhas`
  ADD PRIMARY KEY (`cam_id`),
  ADD KEY `fk_tab_campanhas_tab_campanhas_tipos1` (`cat_id`),
  ADD KEY `fk_tab_campanhas_tab_campanhas_status1` (`cas_id`);

--
-- Indexes for table `tab_campanhas_contatos`
--
ALTER TABLE `tab_campanhas_contatos`
  ADD PRIMARY KEY (`cac_id`),
  ADD KEY `fk_tab_campanhas_contatos_tab_campanhas_contatos_status1` (`ccs_id`),
  ADD KEY `log_id` (`log_id`),
  ADD KEY `bai_id` (`bai_id`),
  ADD KEY `cid_id` (`cid_id`),
  ADD KEY `est_id` (`est_id`),
  ADD KEY `cli_id` (`cli_id`),
  ADD KEY `cep_id` (`cep_id`);

--
-- Indexes for table `tab_campanhas_contatos_status`
--
ALTER TABLE `tab_campanhas_contatos_status`
  ADD PRIMARY KEY (`ccs_id`);

--
-- Indexes for table `tab_campanhas_contatos_telefones`
--
ALTER TABLE `tab_campanhas_contatos_telefones`
  ADD PRIMARY KEY (`cct_id`),
  ADD KEY `fk_tab_mailings_nomes_telefones_tab_mailings_nomes1` (`cac_id`);

--
-- Indexes for table `tab_campanhas_operacoes`
--
ALTER TABLE `tab_campanhas_operacoes`
  ADD PRIMARY KEY (`cao_id`),
  ADD KEY `fk_tab_mailings_operacoes_tab_mailings` (`cam_id`),
  ADD KEY `usu_id` (`usu_id`);

--
-- Indexes for table `tab_campanhas_operacoes_contatos`
--
ALTER TABLE `tab_campanhas_operacoes_contatos`
  ADD PRIMARY KEY (`coc_id`),
  ADD KEY `fk_tab_mailings_operacoes_contatos_tab_mailings_operacoes1` (`cao_id`),
  ADD KEY `fk_tab_campanhas_operacoes_contatos_tab_campanhas_contatos1` (`cac_id`),
  ADD KEY `fk_tab_campanhas_operacoes_contatos_tab_campanhas_contatos_te1` (`cct_id`),
  ADD KEY `fk_tab_campanhas_operacoes_contatos_tab_campanhas_operacoes_c1` (`cos_id`),
  ADD KEY `usu_id` (`usu_id`);

--
-- Indexes for table `tab_campanhas_operacoes_contatos_situacoes`
--
ALTER TABLE `tab_campanhas_operacoes_contatos_situacoes`
  ADD PRIMARY KEY (`cos_id`);

--
-- Indexes for table `tab_campanhas_status`
--
ALTER TABLE `tab_campanhas_status`
  ADD PRIMARY KEY (`cas_id`);

--
-- Indexes for table `tab_campanhas_tipos`
--
ALTER TABLE `tab_campanhas_tipos`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indexes for table `tab_cancelamentos`
--
ALTER TABLE `tab_cancelamentos`
  ADD PRIMARY KEY (`can_id`),
  ADD KEY `IX_rel_cli_can` (`cli_id`),
  ADD KEY `IX_rel_cao_can` (`cao_id`),
  ADD KEY `IX_rel_cas_can` (`cas_id`),
  ADD KEY `IX_rel_cam_can` (`cam_id`),
  ADD KEY `IX_rel_cae_can` (`cae_id`),
  ADD KEY `IX_rel_cor_can` (`cor_id`),
  ADD KEY `can_inicio` (`can_inicio`),
  ADD KEY `can_termino` (`can_termino`);

--
-- Indexes for table `tab_cancelamentos_etapas`
--
ALTER TABLE `tab_cancelamentos_etapas`
  ADD PRIMARY KEY (`cae_id`),
  ADD KEY `IX_rel_can_cae` (`can_id`),
  ADD KEY `IX_rel_cet_cae` (`cet_id`),
  ADD KEY `IX_rel_ces_cae` (`ces_id`),
  ADD KEY `IX_rel_cen_cae` (`cen_id`),
  ADD KEY `IX_rel_usu_cae` (`usu_id`);

--
-- Indexes for table `tab_cancelamentos_etapas_niveis`
--
ALTER TABLE `tab_cancelamentos_etapas_niveis`
  ADD PRIMARY KEY (`cen_id`);

--
-- Indexes for table `tab_cancelamentos_etapas_status`
--
ALTER TABLE `tab_cancelamentos_etapas_status`
  ADD PRIMARY KEY (`ces_id`);

--
-- Indexes for table `tab_cancelamentos_etapas_tipos`
--
ALTER TABLE `tab_cancelamentos_etapas_tipos`
  ADD PRIMARY KEY (`cet_id`);

--
-- Indexes for table `tab_cancelamentos_motivos`
--
ALTER TABLE `tab_cancelamentos_motivos`
  ADD PRIMARY KEY (`cam_id`),
  ADD KEY `IX_rel_cms_cam` (`cms_id`);

--
-- Indexes for table `tab_cancelamentos_motivos_status`
--
ALTER TABLE `tab_cancelamentos_motivos_status`
  ADD PRIMARY KEY (`cms_id`);

--
-- Indexes for table `tab_cancelamentos_origens`
--
ALTER TABLE `tab_cancelamentos_origens`
  ADD PRIMARY KEY (`cao_id`);

--
-- Indexes for table `tab_cancelamentos_status`
--
ALTER TABLE `tab_cancelamentos_status`
  ADD PRIMARY KEY (`cas_id`);

--
-- Indexes for table `tab_central_telefonica_agregados`
--
ALTER TABLE `tab_central_telefonica_agregados`
  ADD PRIMARY KEY (`ctl_id`),
  ADD UNIQUE KEY `ctl_data` (`ctl_data`,`ctl_hora`,`ctl_setor`,`ctl_operador`,`ctl_tipo`),
  ADD KEY `hora` (`ctl_hora`),
  ADD KEY `operador` (`ctl_operador`),
  ADD KEY `dia_semana` (`ctl_dia_semana`),
  ADD KEY `usu_id` (`usu_id`),
  ADD KEY `tab_central_telefonica_agregados_usu_id` (`usu_id`);

--
-- Indexes for table `tab_central_telefonica_ligacoes`
--
ALTER TABLE `tab_central_telefonica_ligacoes`
  ADD PRIMARY KEY (`ctl_id`),
  ADD UNIQUE KEY `ctl_data_origem_destino` (`ctl_data_hora`,`ctl_origem`,`ctl_destino`),
  ADD KEY `cli_id` (`cli_id`),
  ADD KEY `clt_id` (`clt_id`),
  ADD KEY `cls_id` (`cls_id`),
  ADD KEY `ctl_data_hora` (`ctl_data_hora`),
  ADD KEY `ctl_fila` (`ctl_fila`),
  ADD KEY `usu_origem_id` (`usu_origem_id`),
  ADD KEY `usu_destino_id` (`usu_destino_id`),
  ADD KEY `cts_origem_id` (`cts_origem_id`),
  ADD KEY `cts_destino_id` (`cts_destino_id`),
  ADD KEY `tab_central_telefonica_ligacoes_cts_origem_id` (`cts_origem_id`),
  ADD KEY `tab_central_telefonica_ligacoes_cts_destino_id` (`cts_destino_id`);

--
-- Indexes for table `tab_central_telefonica_ligacoes_situacoes`
--
ALTER TABLE `tab_central_telefonica_ligacoes_situacoes`
  ADD PRIMARY KEY (`cls_id`);

--
-- Indexes for table `tab_central_telefonica_ligacoes_tipos`
--
ALTER TABLE `tab_central_telefonica_ligacoes_tipos`
  ADD PRIMARY KEY (`clt_id`);

--
-- Indexes for table `tab_central_telefonica_setores`
--
ALTER TABLE `tab_central_telefonica_setores`
  ADD PRIMARY KEY (`cts_id`);

--
-- Indexes for table `tab_ceps`
--
ALTER TABLE `tab_ceps`
  ADD PRIMARY KEY (`cep_id`),
  ADD KEY `IX_rel_rua_cep` (`log_id`),
  ADD KEY `bai_id` (`bai_id`),
  ADD KEY `cid_id` (`cid_id`),
  ADD KEY `est_id` (`est_id`),
  ADD KEY `cep_exportacao` (`cep_exportacao`),
  ADD KEY `cep_zona` (`cep_zona`);

--
-- Indexes for table `tab_chamados`
--
ALTER TABLE `tab_chamados`
  ADD PRIMARY KEY (`cha_id`),
  ADD KEY `IX_rel_usu_cha` (`usu_id`),
  ADD KEY `IX_rel_con_cha` (`con_id`),
  ADD KEY `IX_rel_cli_cha` (`cli_id`),
  ADD KEY `IX_rel_ors_cha` (`ors_id`),
  ADD KEY `IX_rel_orf_cha` (`orf_id`),
  ADD KEY `IX_rel_chc_cha` (`chc_id`),
  ADD KEY `IX_rel_chs_cha` (`chs_id`),
  ADD KEY `IX_rel_chp_cha` (`chp_id`),
  ADD KEY `IX_rel_cha_cha` (`cha_pai`),
  ADD KEY `IX_rel_chi_cha` (`chi_id`),
  ADD KEY `cha_datahora_abertura` (`cha_datahora_abertura`),
  ADD KEY `cha_datahora_fechamento` (`cha_datahora_fechamento`),
  ADD KEY `cha_datahora_encaminhamento` (`cha_datahora_encaminhamento`),
  ADD KEY `cha_aguardando_retorno` (`cha_aguardando_retorno`),
  ADD KEY `vis_id` (`vis_id`),
  ADD KEY `IX_tab_chamados_chs_id_cha_datahora_encaminhamento` (`chs_id`,`cha_datahora_encaminhamento`);

--
-- Indexes for table `tab_chamados_acoes`
--
ALTER TABLE `tab_chamados_acoes`
  ADD PRIMARY KEY (`caa_id`);

--
-- Indexes for table `tab_chamados_categorias`
--
ALTER TABLE `tab_chamados_categorias`
  ADD PRIMARY KEY (`chc_id`),
  ADD KEY `IX_rel_chc_chc` (`chc_pai`),
  ADD KEY `vit_id` (`vit_id`);

--
-- Indexes for table `tab_chamados_categorias_etapas`
--
ALTER TABLE `tab_chamados_categorias_etapas`
  ADD PRIMARY KEY (`cce_id`),
  ADD KEY `IX_rel_ors_cce` (`ors_id`),
  ADD KEY `IX_rel_orf_cce` (`orf_id`),
  ADD KEY `IX_rel_cet_cce` (`cet_id`),
  ADD KEY `IX_rel_chc_cce` (`chc_id`);

--
-- Indexes for table `tab_chamados_etapas`
--
ALTER TABLE `tab_chamados_etapas`
  ADD PRIMARY KEY (`che_id`),
  ADD KEY `IX_rel_cha_che` (`cha_id`),
  ADD KEY `IX_rel_cen_che` (`cen_id`),
  ADD KEY `IX_rel_cet_che` (`cet_id`),
  ADD KEY `IX_rel_usu_che` (`usu_id`),
  ADD KEY `IX_rel_che_che` (`che_pai`),
  ADD KEY `che_datahora` (`che_datahora`),
  ADD FULLTEXT KEY `che_conteudo` (`che_conteudo`);

--
-- Indexes for table `tab_chamados_etapas_niveis_stress`
--
ALTER TABLE `tab_chamados_etapas_niveis_stress`
  ADD PRIMARY KEY (`cen_id`);

--
-- Indexes for table `tab_chamados_etapas_tipos`
--
ALTER TABLE `tab_chamados_etapas_tipos`
  ADD PRIMARY KEY (`cet_id`);

--
-- Indexes for table `tab_chamados_prioridades`
--
ALTER TABLE `tab_chamados_prioridades`
  ADD PRIMARY KEY (`chp_id`);

--
-- Indexes for table `tab_chamados_roteiros`
--
ALTER TABLE `tab_chamados_roteiros`
  ADD PRIMARY KEY (`chr_id`),
  ADD KEY `IX_rel_cre_chr` (`cre_id`);

--
-- Indexes for table `tab_chamados_roteiros_etapas`
--
ALTER TABLE `tab_chamados_roteiros_etapas`
  ADD PRIMARY KEY (`cre_id`),
  ADD KEY `IX_rel_cre_cre_sim` (`cre_sim_id`),
  ADD KEY `IX_rel_cre_cre_nao` (`cre_nao_id`),
  ADD KEY `IX_rel_chr_cre` (`chr_id`);

--
-- Indexes for table `tab_chamados_situacoes`
--
ALTER TABLE `tab_chamados_situacoes`
  ADD PRIMARY KEY (`chi_id`);

--
-- Indexes for table `tab_chamados_status`
--
ALTER TABLE `tab_chamados_status`
  ADD PRIMARY KEY (`chs_id`);

--
-- Indexes for table `tab_chamados_visualizacoes`
--
ALTER TABLE `tab_chamados_visualizacoes`
  ADD PRIMARY KEY (`chv_id`),
  ADD KEY `IX_rel_usu_chb` (`usu_id`),
  ADD KEY `IX_rel_cha_chb` (`cha_id`);

--
-- Indexes for table `tab_cidades`
--
ALTER TABLE `tab_cidades`
  ADD PRIMARY KEY (`cid_id`),
  ADD UNIQUE KEY `cid_cep_geral` (`cid_cep_geral`),
  ADD KEY `IX_rel_est_cid` (`est_id`),
  ADD KEY `cid_nome` (`cid_nome`),
  ADD KEY `clo_id` (`clo_id`);

--
-- Indexes for table `tab_classes_fiscais`
--
ALTER TABLE `tab_classes_fiscais`
  ADD PRIMARY KEY (`clf_id`),
  ADD KEY `pls_id` (`pls_id`),
  ADD KEY `cft_id` (`cft_id`),
  ADD KEY `afc_id` (`afc_id`),
  ADD KEY `pla_id` (`pla_id`);

--
-- Indexes for table `tab_classes_fiscais_tipos`
--
ALTER TABLE `tab_classes_fiscais_tipos`
  ADD PRIMARY KEY (`cft_id`),
  ADD UNIQUE KEY `cft_sigla` (`cft_sigla`);

--
-- Indexes for table `tab_clientes`
--
ALTER TABLE `tab_clientes`
  ADD PRIMARY KEY (`cli_id`),
  ADD KEY `IX_rel_usu_cli` (`usu_id`),
  ADD KEY `IX_rel_cep_cli` (`cep_id`),
  ADD KEY `IX_rel_cob_cli` (`cob_id`),
  ADD KEY `IX_rel_clt_cli` (`clt_id`),
  ADD KEY `IX_rel_cls_cli` (`cls_id`),
  ADD KEY `IX_rel_clu_cli` (`clu_id`),
  ADD KEY `IX_rel_cof_cli` (`cof_id`),
  ADD KEY `IX_rel_con_cli` (`con_id`),
  ADD KEY `IX_rel_cld_cli` (`cld_id`),
  ADD KEY `IX_rel_clo_cli` (`clo_id`),
  ADD KEY `IX_rel_cli_cli` (`cli_pai`),
  ADD KEY `cli_cpf_cnpj` (`cli_cpf_cnpj`),
  ADD KEY `cli_ccd_debito_codigo` (`cli_ccd_debito_codigo`),
  ADD KEY `cli_ccd_debito_agencia` (`cli_ccd_debito_agencia`),
  ADD KEY `cli_ccd_debito_conta` (`cli_ccd_debito_conta`),
  ADD KEY `IX_rel_coo_cli` (`coo_id`),
  ADD KEY `tab_clientes_bai_id` (`bai_id`),
  ADD KEY `tab_clientes_cid_id` (`cid_id`),
  ADD KEY `tab_clientes_est_id` (`est_id`),
  ADD KEY `tab_clientes_log_id` (`log_id`);

--
-- Indexes for table `tab_clientes_autenticacoes`
--
ALTER TABLE `tab_clientes_autenticacoes`
  ADD PRIMARY KEY (`cau_id`),
  ADD KEY `IX_rel_ass_cau` (`ass_id`),
  ADD KEY `IX_rel_cle_cau` (`cle_id`),
  ADD KEY `IX_rel_cas_cau` (`cas_id`),
  ADD KEY `IX_rel_sei_cau` (`sei_id`),
  ADD KEY `cau_equipamento_ssid` (`cau_equipamento_ssid`),
  ADD KEY `cau_concentrador_nome` (`cau_concentrador_nome`),
  ADD KEY `ssi_id` (`ssi_id`),
  ADD KEY `ser_id` (`ser_id`),
  ADD KEY `caa_id` (`caa_id`),
  ADD KEY `cau_equipamento_modelo` (`cau_equipamento_modelo`),
  ADD KEY `cau_publicado` (`cau_publicado`);

--
-- Indexes for table `tab_clientes_autenticacoes_ajustes`
--
ALTER TABLE `tab_clientes_autenticacoes_ajustes`
  ADD PRIMARY KEY (`caa_id`),
  ADD KEY `IX_rel_saj_caa` (`saj_id`),
  ADD KEY `IX_rel_cau_caa` (`cau_id`);

--
-- Indexes for table `tab_clientes_autenticacoes_autenticadores`
--
ALTER TABLE `tab_clientes_autenticacoes_autenticadores`
  ADD PRIMARY KEY (`caa_id`);

--
-- Indexes for table `tab_clientes_autenticacoes_comentarios`
--
ALTER TABLE `tab_clientes_autenticacoes_comentarios`
  ADD PRIMARY KEY (`cac_id`),
  ADD KEY `IX_rel_cau_cac` (`cau_id`),
  ADD KEY `IX_rel_usu_cac` (`usu_id`);

--
-- Indexes for table `tab_clientes_autenticacoes_status`
--
ALTER TABLE `tab_clientes_autenticacoes_status`
  ADD PRIMARY KEY (`cas_id`);

--
-- Indexes for table `tab_clientes_basesdedados`
--
ALTER TABLE `tab_clientes_basesdedados`
  ADD PRIMARY KEY (`clb_id`),
  ADD KEY `IX_rel_ser_clb` (`ser_id`),
  ADD KEY `IX_rel_cbs_clb` (`cbs_id`),
  ADD KEY `IX_rel_cbt_clb` (`cbt_id`),
  ADD KEY `IX_rel_ass_clb` (`ass_id`);

--
-- Indexes for table `tab_clientes_basesdedados_status`
--
ALTER TABLE `tab_clientes_basesdedados_status`
  ADD PRIMARY KEY (`cbs_id`);

--
-- Indexes for table `tab_clientes_basesdedados_tipos`
--
ALTER TABLE `tab_clientes_basesdedados_tipos`
  ADD PRIMARY KEY (`cbt_id`);

--
-- Indexes for table `tab_clientes_cobranca_dados`
--
ALTER TABLE `tab_clientes_cobranca_dados`
  ADD PRIMARY KEY (`cli_id`),
  ADD KEY `IX_rel_cli_clc` (`cli_id`),
  ADD KEY `IX_rel_cep_clc` (`cep_id`),
  ADD KEY `IX_rel_cob_clc` (`cob_id`),
  ADD KEY `IX_rel_con_clc` (`con_id`),
  ADD KEY `IX_rel_log_clc` (`log_id`),
  ADD KEY `IX_rel_bai_clc` (`bai_id`),
  ADD KEY `IX_rel_cid_clc` (`cid_id`),
  ADD KEY `IX_rel_est_clc` (`est_id`);

--
-- Indexes for table `tab_clientes_comodatos`
--
ALTER TABLE `tab_clientes_comodatos`
  ADD PRIMARY KEY (`clc_id`),
  ADD KEY `equ_id` (`equ_id`),
  ADD KEY `ccs_id` (`ccs_id`),
  ADD KEY `cli_id` (`cli_id`),
  ADD KEY `cor_id` (`cor_id`),
  ADD KEY `clc_id` (`clc_id`),
  ADD KEY `ass_id` (`ass_id`),
  ADD KEY `eqm_id` (`eqm_id`),
  ADD KEY `clc_substituto_id` (`clc_substituto_id`);

--
-- Indexes for table `tab_clientes_comodatos_comentarios`
--
ALTER TABLE `tab_clientes_comodatos_comentarios`
  ADD PRIMARY KEY (`ccc_id`),
  ADD KEY `clc_id` (`clc_id`),
  ADD KEY `usu_id` (`usu_id`);

--
-- Indexes for table `tab_clientes_comodatos_status`
--
ALTER TABLE `tab_clientes_comodatos_status`
  ADD PRIMARY KEY (`ccs_id`);

--
-- Indexes for table `tab_clientes_consultas_credito`
--
ALTER TABLE `tab_clientes_consultas_credito`
  ADD PRIMARY KEY (`ccc_id`),
  ADD KEY `IX_rel_cli_ccc` (`cli_id`),
  ADD KEY `IX_rel_ccs_ccc` (`ccs_id`),
  ADD KEY `IX_rel_usu_ccc` (`usu_id`),
  ADD KEY `IX_rel_cct_ccc` (`cct_id`),
  ADD KEY `ccc_cpf_cnpj` (`ccc_cpf_cnpj`);

--
-- Indexes for table `tab_clientes_consultas_credito_parametros`
--
ALTER TABLE `tab_clientes_consultas_credito_parametros`
  ADD PRIMARY KEY (`ccp_nsu`);

--
-- Indexes for table `tab_clientes_consultas_credito_situacoes`
--
ALTER TABLE `tab_clientes_consultas_credito_situacoes`
  ADD PRIMARY KEY (`ccs_id`);

--
-- Indexes for table `tab_clientes_consultas_credito_tipos`
--
ALTER TABLE `tab_clientes_consultas_credito_tipos`
  ADD PRIMARY KEY (`cct_id`);

--
-- Indexes for table `tab_clientes_contatos_emails`
--
ALTER TABLE `tab_clientes_contatos_emails`
  ADD PRIMARY KEY (`ccm_id`),
  ADD KEY `IX_rel_cli_ccm` (`cli_id`),
  ADD KEY `IX_rel_cca_ccm` (`cca_id`),
  ADD KEY `ccm_usuario` (`ccm_usuario`),
  ADD KEY `ccm_dominio` (`ccm_dominio`);

--
-- Indexes for table `tab_clientes_contatos_emails_tipos`
--
ALTER TABLE `tab_clientes_contatos_emails_tipos`
  ADD PRIMARY KEY (`cca_id`);

--
-- Indexes for table `tab_clientes_contatos_telefones`
--
ALTER TABLE `tab_clientes_contatos_telefones`
  ADD PRIMARY KEY (`ccl_id`),
  ADD KEY `IX_rel_cli_ccl` (`cli_id`),
  ADD KEY `IX_rel_ccf_ccl` (`ccf_id`),
  ADD KEY `ccl_ddd` (`ccl_ddd`),
  ADD KEY `ccl_numero` (`ccl_numero`),
  ADD KEY `ccl_ramal` (`ccl_ramal`);

--
-- Indexes for table `tab_clientes_contatos_telefones_tipos`
--
ALTER TABLE `tab_clientes_contatos_telefones_tipos`
  ADD PRIMARY KEY (`ccf_id`);

--
-- Indexes for table `tab_clientes_emails`
--
ALTER TABLE `tab_clientes_emails`
  ADD PRIMARY KEY (`cle_id`),
  ADD KEY `IX_rel_ass_cle` (`ass_id`),
  ADD KEY `IX_rel_ces_cle` (`ces_id`),
  ADD KEY `IX_rel_dom_cle` (`dom_id`),
  ADD KEY `IX_rel_cet_cle` (`cet_id`),
  ADD KEY `cle_publicado` (`cle_publicado`),
  ADD KEY `cle_bloqueado` (`cle_bloqueado`),
  ADD KEY `cle_usuario` (`cle_usuario`),
  ADD KEY `cle_principal` (`cle_principal`);

--
-- Indexes for table `tab_clientes_emails_ajustes`
--
ALTER TABLE `tab_clientes_emails_ajustes`
  ADD PRIMARY KEY (`cea_id`),
  ADD KEY `IX_rel_saj_cea` (`saj_id`),
  ADD KEY `IX_rel_cle_cea` (`cle_id`);

--
-- Indexes for table `tab_clientes_emails_comentarios`
--
ALTER TABLE `tab_clientes_emails_comentarios`
  ADD PRIMARY KEY (`cec_id`),
  ADD KEY `IX_rel_usu_cec` (`usu_id`),
  ADD KEY `IX_rel_cle_cec` (`cle_id`);

--
-- Indexes for table `tab_clientes_emails_status`
--
ALTER TABLE `tab_clientes_emails_status`
  ADD PRIMARY KEY (`ces_id`);

--
-- Indexes for table `tab_clientes_emails_tipos`
--
ALTER TABLE `tab_clientes_emails_tipos`
  ADD PRIMARY KEY (`cet_id`);

--
-- Indexes for table `tab_clientes_hospedagens`
--
ALTER TABLE `tab_clientes_hospedagens`
  ADD PRIMARY KEY (`clh_id`),
  ADD KEY `IX_rel_ass_clh` (`ass_id`),
  ADD KEY `IX_rel_chs_clh` (`chs_id`),
  ADD KEY `IX_rel_ser_clh` (`ser_id`);

--
-- Indexes for table `tab_clientes_hospedagens_status`
--
ALTER TABLE `tab_clientes_hospedagens_status`
  ADD PRIMARY KEY (`chs_id`);

--
-- Indexes for table `tab_clientes_indicacoes`
--
ALTER TABLE `tab_clientes_indicacoes`
  ADD PRIMARY KEY (`cld_id`);

--
-- Indexes for table `tab_clientes_iptv`
--
ALTER TABLE `tab_clientes_iptv`
  ADD PRIMARY KEY (`cip_id`),
  ADD UNIQUE KEY `cip_recuperacao_hash` (`cip_recuperacao_hash`),
  ADD KEY `ass_id` (`cor_id`),
  ADD KEY `cis_id` (`cis_id`),
  ADD KEY `cli_id` (`cli_id`),
  ADD KEY `pla_id` (`pla_id`);

--
-- Indexes for table `tab_clientes_iptv_ajustes`
--
ALTER TABLE `tab_clientes_iptv_ajustes`
  ADD PRIMARY KEY (`cia_id`),
  ADD KEY `IX_rel_saj_caa` (`saj_id`),
  ADD KEY `IX_rel_cau_caa` (`cip_id`);

--
-- Indexes for table `tab_clientes_iptv_comentarios`
--
ALTER TABLE `tab_clientes_iptv_comentarios`
  ADD PRIMARY KEY (`cic_id`),
  ADD KEY `cip_id` (`cip_id`),
  ADD KEY `usu_id` (`usu_id`);

--
-- Indexes for table `tab_clientes_iptv_status`
--
ALTER TABLE `tab_clientes_iptv_status`
  ADD PRIMARY KEY (`cis_id`),
  ADD UNIQUE KEY `cis_id` (`cis_id`);

--
-- Indexes for table `tab_clientes_negativacoes`
--
ALTER TABLE `tab_clientes_negativacoes`
  ADD PRIMARY KEY (`cln_id`),
  ADD KEY `cln_cpf_cnpj` (`cln_cpf_cnpj`),
  ADD KEY `cln_status` (`cln_status`);

--
-- Indexes for table `tab_clientes_negativacoes_comentarios`
--
ALTER TABLE `tab_clientes_negativacoes_comentarios`
  ADD PRIMARY KEY (`cnc_id`),
  ADD KEY `cln_id` (`cln_id`),
  ADD KEY `usu_id` (`usu_id`);

--
-- Indexes for table `tab_clientes_operadoras_origens`
--
ALTER TABLE `tab_clientes_operadoras_origens`
  ADD PRIMARY KEY (`coo_id`);

--
-- Indexes for table `tab_clientes_origens`
--
ALTER TABLE `tab_clientes_origens`
  ADD PRIMARY KEY (`clo_id`),
  ADD KEY `cep_id` (`cep_id`),
  ADD KEY `cos_id` (`cos_id`);

--
-- Indexes for table `tab_clientes_origens_historicoapi`
--
ALTER TABLE `tab_clientes_origens_historicoapi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tab_clientes_origens_status`
--
ALTER TABLE `tab_clientes_origens_status`
  ADD PRIMARY KEY (`cos_id`);

--
-- Indexes for table `tab_clientes_pontos`
--
ALTER TABLE `tab_clientes_pontos`
  ADD PRIMARY KEY (`clp_id`),
  ADD KEY `IX_rel_ass_clp` (`ass_id`),
  ADD KEY `IX_rel_sei_clp` (`sei_id`),
  ADD KEY `IX_rel_cps_clp` (`cps_id`),
  ADD KEY `clp_publicado` (`clp_publicado`),
  ADD KEY `clp_endereco_mac` (`clp_endereco_mac`),
  ADD KEY `clp_regra` (`clp_regra`),
  ADD KEY `clp_ip` (`clp_ip`);

--
-- Indexes for table `tab_clientes_pontos_ajustes`
--
ALTER TABLE `tab_clientes_pontos_ajustes`
  ADD PRIMARY KEY (`cpa_id`),
  ADD KEY `IX_rel_saj_cpa` (`saj_id`),
  ADD KEY `IX_rel_clp_cpa` (`clp_id`);

--
-- Indexes for table `tab_clientes_pontos_comentarios`
--
ALTER TABLE `tab_clientes_pontos_comentarios`
  ADD PRIMARY KEY (`cpc_id`),
  ADD KEY `IX_rel_usu_cpc` (`usu_id`),
  ADD KEY `IX_rel_clp_cpc` (`clp_id`);

--
-- Indexes for table `tab_clientes_pontos_status`
--
ALTER TABLE `tab_clientes_pontos_status`
  ADD PRIMARY KEY (`cps_id`);

--
-- Indexes for table `tab_clientes_responsabilidades`
--
ALTER TABLE `tab_clientes_responsabilidades`
  ADD PRIMARY KEY (`cln_id`),
  ADD KEY `IX_rel_cli_cln` (`cli_id`),
  ADD KEY `IX_rel_usu_cln` (`usu_id`),
  ADD KEY `IX_rel_cri_cln` (`cri_id`),
  ADD KEY `IX_rel_cra_cln` (`cra_id`),
  ADD KEY `IX_rel_cod_cln` (`cod_id`),
  ADD KEY `IX_rel_crt_cln` (`crt_id`);

--
-- Indexes for table `tab_clientes_responsabilidades_categorias`
--
ALTER TABLE `tab_clientes_responsabilidades_categorias`
  ADD PRIMARY KEY (`crt_id`);

--
-- Indexes for table `tab_clientes_responsabilidades_comentarios`
--
ALTER TABLE `tab_clientes_responsabilidades_comentarios`
  ADD PRIMARY KEY (`crc_id`),
  ADD KEY `IX_rel_usu_crc` (`usu_id`),
  ADD KEY `IX_rel_cln_crc` (`cln_id`);

--
-- Indexes for table `tab_clientes_responsabilidades_status`
--
ALTER TABLE `tab_clientes_responsabilidades_status`
  ADD PRIMARY KEY (`cra_id`);

--
-- Indexes for table `tab_clientes_responsabilidades_tipos`
--
ALTER TABLE `tab_clientes_responsabilidades_tipos`
  ADD PRIMARY KEY (`cri_id`);

--
-- Indexes for table `tab_clientes_retornos`
--
ALTER TABLE `tab_clientes_retornos`
  ADD PRIMARY KEY (`clr_id`),
  ADD KEY `IX_rel_usu_clr` (`usu_id`),
  ADD KEY `IX_rel_cli_clr` (`cli_id`),
  ADD KEY `IX_rel_cet_clr` (`cet_id`),
  ADD KEY `IX_rel_ces_clr` (`ces_id`),
  ADD KEY `IX_rel_cee_clr` (`cee_id`),
  ADD KEY `IX_indice_busca` (`usu_id`,`ces_id`);

--
-- Indexes for table `tab_clientes_retornos_periodos`
--
ALTER TABLE `tab_clientes_retornos_periodos`
  ADD PRIMARY KEY (`cee_id`);

--
-- Indexes for table `tab_clientes_retornos_status`
--
ALTER TABLE `tab_clientes_retornos_status`
  ADD PRIMARY KEY (`ces_id`);

--
-- Indexes for table `tab_clientes_retornos_tipos`
--
ALTER TABLE `tab_clientes_retornos_tipos`
  ADD PRIMARY KEY (`cet_id`);

--
-- Indexes for table `tab_clientes_situacoes`
--
ALTER TABLE `tab_clientes_situacoes`
  ADD PRIMARY KEY (`clu_id`);

--
-- Indexes for table `tab_clientes_status`
--
ALTER TABLE `tab_clientes_status`
  ADD PRIMARY KEY (`cls_id`);

--
-- Indexes for table `tab_clientes_telefones`
--
ALTER TABLE `tab_clientes_telefones`
  ADD PRIMARY KEY (`clt_id`),
  ADD KEY `clt_numero` (`clt_numero`),
  ADD KEY `ass_id` (`ass_id`),
  ADD KEY `cts_id` (`cts_id`),
  ADD KEY `ctt_id` (`ctt_id`);

--
-- Indexes for table `tab_clientes_telefones_recargas`
--
ALTER TABLE `tab_clientes_telefones_recargas`
  ADD PRIMARY KEY (`ctr_id`),
  ADD KEY `clt_id` (`clt_id`),
  ADD KEY `usu_id` (`usu_id`);

--
-- Indexes for table `tab_clientes_telefones_status`
--
ALTER TABLE `tab_clientes_telefones_status`
  ADD PRIMARY KEY (`cts_id`);

--
-- Indexes for table `tab_clientes_telefones_tipos`
--
ALTER TABLE `tab_clientes_telefones_tipos`
  ADD PRIMARY KEY (`ctt_id`);

--
-- Indexes for table `tab_clientes_tipos`
--
ALTER TABLE `tab_clientes_tipos`
  ADD PRIMARY KEY (`clt_id`);

--
-- Indexes for table `tab_cobrancas`
--
ALTER TABLE `tab_cobrancas`
  ADD PRIMARY KEY (`coa_id`),
  ADD KEY `IX_rel_cli_coa` (`cli_id`),
  ADD KEY `IX_rel_col_coa` (`col_id`),
  ADD KEY `IX_rel_cot_coa` (`cot_id`),
  ADD KEY `IX_rel_cof_coa` (`cof_id`),
  ADD KEY `IX_rel_coe_coa` (`coe_id`),
  ADD KEY `IX_rel_rec_coa` (`rec_id`),
  ADD KEY `IX_rel_coa_coa` (`coa_pai`),
  ADD KEY `coa_codigo` (`coa_codigo`),
  ADD KEY `coa_data_vencimento` (`coa_data_vencimento`),
  ADD KEY `coa_data_processamento` (`coa_data_processamento`),
  ADD KEY `coa_data_bloqueio` (`coa_data_bloqueio`),
  ADD KEY `coa_data_credito` (`coa_data_credito`),
  ADD KEY `fat_id` (`fat_id`),
  ADD KEY `coa_data_pagamento` (`coa_data_pagamento`),
  ADD KEY `coa_grupo_id` (`coa_grupo_id`),
  ADD KEY `tab_cobrancas_cop_id` (`cop_id`),
  ADD KEY `fco_id` (`fco_id`),
  ADD KEY `coa_nosso_numero` (`coa_nosso_numero`),
  ADD KEY `coa_aguardando_registro` (`coa_aguardando_registro`);

--
-- Indexes for table `tab_cobrancas_comentarios`
--
ALTER TABLE `tab_cobrancas_comentarios`
  ADD PRIMARY KEY (`com_id`),
  ADD KEY `IX_rel_coa_com` (`coa_id`),
  ADD KEY `IX_rel_usu_com` (`usu_id`);

--
-- Indexes for table `tab_cobrancas_detalhes`
--
ALTER TABLE `tab_cobrancas_detalhes`
  ADD PRIMARY KEY (`cod_id`),
  ADD KEY `IX_rel_ass_cod` (`ass_id`),
  ADD KEY `IX_rel_dec_cod` (`dec_id`),
  ADD KEY `IX_rel_cod_cod` (`cod_pai`),
  ADD KEY `IX_rel_ajf_cod` (`ajf_id`),
  ADD KEY `IX_rel_coa_cod` (`coa_id`),
  ADD KEY `cod_competencia_inicio` (`cod_competencia_inicio`),
  ADD KEY `cod_competencia_termino` (`cod_competencia_termino`),
  ADD KEY `cod_cancelado` (`cod_cancelado`);

--
-- Indexes for table `tab_cobrancas_formatos`
--
ALTER TABLE `tab_cobrancas_formatos`
  ADD PRIMARY KEY (`cof_id`),
  ADD KEY `IX_rel_cfs_cof` (`cfs_id`),
  ADD KEY `cft_id` (`cft_id`);

--
-- Indexes for table `tab_cobrancas_formatos_status`
--
ALTER TABLE `tab_cobrancas_formatos_status`
  ADD PRIMARY KEY (`cfs_id`);

--
-- Indexes for table `tab_cobrancas_formatos_tipos`
--
ALTER TABLE `tab_cobrancas_formatos_tipos`
  ADD PRIMARY KEY (`cft_id`);

--
-- Indexes for table `tab_cobrancas_lotes`
--
ALTER TABLE `tab_cobrancas_lotes`
  ADD PRIMARY KEY (`col_id`),
  ADD KEY `IX_rel_cof_col` (`cof_id`),
  ADD KEY `IX_rel_cla_col` (`cla_id`),
  ADD KEY `cop_id` (`cop_id`);

--
-- Indexes for table `tab_cobrancas_lotes_comentarios`
--
ALTER TABLE `tab_cobrancas_lotes_comentarios`
  ADD PRIMARY KEY (`clo_id`),
  ADD KEY `IX_rel_usu_clo` (`usu_id`),
  ADD KEY `IX_rel_col_clo` (`col_id`);

--
-- Indexes for table `tab_cobrancas_lotes_status`
--
ALTER TABLE `tab_cobrancas_lotes_status`
  ADD PRIMARY KEY (`cla_id`);

--
-- Indexes for table `tab_cobrancas_periodos`
--
ALTER TABLE `tab_cobrancas_periodos`
  ADD PRIMARY KEY (`cop_id`);

--
-- Indexes for table `tab_cobrancas_remessas`
--
ALTER TABLE `tab_cobrancas_remessas`
  ADD PRIMARY KEY (`cbr_id`),
  ADD UNIQUE KEY `cbr_nsa` (`cbr_nsa`,`cof_id`);

--
-- Indexes for table `tab_cobrancas_remessas_itens`
--
ALTER TABLE `tab_cobrancas_remessas_itens`
  ADD PRIMARY KEY (`cbr_id`,`coa_id`),
  ADD KEY `coa_id` (`coa_id`,`cri_status`),
  ADD KEY `cri_nosso_numero` (`cri_nosso_numero`),
  ADD KEY `cof_id` (`cof_id`);

--
-- Indexes for table `tab_cobrancas_retornos`
--
ALTER TABLE `tab_cobrancas_retornos`
  ADD PRIMARY KEY (`coe_id`),
  ADD KEY `IX_rel_cof_coe` (`cof_id`),
  ADD KEY `coe_data_geracao` (`coe_data_geracao`),
  ADD KEY `coe_data_publicacao` (`coe_data_publicacao`);

--
-- Indexes for table `tab_cobrancas_retornos_erros`
--
ALTER TABLE `tab_cobrancas_retornos_erros`
  ADD PRIMARY KEY (`cos_id`),
  ADD KEY `IX_rel_coe_cos` (`coe_id`),
  ADD KEY `IX_rel_coa_cos` (`coa_id`);

--
-- Indexes for table `tab_cobrancas_retornos_nao_encontradas`
--
ALTER TABLE `tab_cobrancas_retornos_nao_encontradas`
  ADD PRIMARY KEY (`crn_id`),
  ADD KEY `coe_id` (`coe_id`),
  ADD KEY `coa_id` (`coa_id`);

--
-- Indexes for table `tab_cobrancas_retornos_registros`
--
ALTER TABLE `tab_cobrancas_retornos_registros`
  ADD PRIMARY KEY (`coe_id`,`coa_id`,`cog_tipo`),
  ADD KEY `coa_id` (`coa_id`),
  ADD KEY `coe_id` (`coe_id`);

--
-- Indexes for table `tab_cobrancas_status`
--
ALTER TABLE `tab_cobrancas_status`
  ADD PRIMARY KEY (`cot_id`);

--
-- Indexes for table `tab_cobrancas_tipos`
--
ALTER TABLE `tab_cobrancas_tipos`
  ADD PRIMARY KEY (`cop_id`);

--
-- Indexes for table `tab_condominios`
--
ALTER TABLE `tab_condominios`
  ADD PRIMARY KEY (`con_id`),
  ADD KEY `IX_rel_cep_con` (`cep_id`),
  ADD KEY `IX_tel_cos_con` (`cos_id`),
  ADD KEY `IX_rel_usu_con` (`usu_id`),
  ADD KEY `IX_rel_coi_con` (`coi_id`),
  ADD KEY `IX_rel_cnt_con` (`cnt_id`),
  ADD KEY `IX_rel_ccl_con` (`ccl_id`),
  ADD KEY `IX_rel_cog_con` (`cog_id`),
  ADD KEY `IX_rel_con_con` (`con_pai`),
  ADD KEY `IX_rel_coq_con` (`coq_id`),
  ADD KEY `IX_rel_bai_con` (`bai_id`),
  ADD KEY `IX_rel_cid_con` (`cid_id`),
  ADD KEY `IX_rel_est_con` (`est_id`),
  ADD KEY `IX_rel_log_con` (`log_id`),
  ADD KEY `IX_rel_cit_con` (`cit_id`);

--
-- Indexes for table `tab_condominios_blocos`
--
ALTER TABLE `tab_condominios_blocos`
  ADD PRIMARY KEY (`cob_id`),
  ADD KEY `IX_rel_con_cob` (`con_id`),
  ADD KEY `IX_rel_cep_cob` (`cep_id`),
  ADD KEY `IX_rel_sei_cob` (`sei_id`),
  ADD KEY `tab_condominios_blocos_bai_id` (`bai_id`),
  ADD KEY `tab_condominios_blocos_cid_id` (`cid_id`),
  ADD KEY `tab_condominios_blocos_est_id` (`est_id`),
  ADD KEY `tab_condominios_blocos_log_id` (`log_id`),
  ADD KEY `tab_condominios_blocos_cbs_id` (`cbs_id`);

--
-- Indexes for table `tab_condominios_blocos_status`
--
ALTER TABLE `tab_condominios_blocos_status`
  ADD PRIMARY KEY (`cbs_id`);

--
-- Indexes for table `tab_condominios_classes`
--
ALTER TABLE `tab_condominios_classes`
  ADD PRIMARY KEY (`ccl_id`);

--
-- Indexes for table `tab_condominios_contatos`
--
ALTER TABLE `tab_condominios_contatos`
  ADD PRIMARY KEY (`coc_id`),
  ADD KEY `IX_rel_cob_cot` (`cob_id`),
  ADD KEY `IX_rel_cct_cot` (`cct_id`),
  ADD KEY `IX_rel_con_cot` (`con_id`);

--
-- Indexes for table `tab_condominios_contatos_tipos`
--
ALTER TABLE `tab_condominios_contatos_tipos`
  ADD PRIMARY KEY (`cct_id`);

--
-- Indexes for table `tab_condominios_instalacoes_tipos`
--
ALTER TABLE `tab_condominios_instalacoes_tipos`
  ADD PRIMARY KEY (`cit_id`);

--
-- Indexes for table `tab_condominios_procedimentos`
--
ALTER TABLE `tab_condominios_procedimentos`
  ADD PRIMARY KEY (`cop_id`),
  ADD KEY `IX_rel_usu_cop` (`usu_id`),
  ADD KEY `IX_tab_con_cop` (`con_id`),
  ADD KEY `IX_rel_cpt_cop` (`cpt_id`);

--
-- Indexes for table `tab_condominios_procedimentos_tipos`
--
ALTER TABLE `tab_condominios_procedimentos_tipos`
  ADD PRIMARY KEY (`cpt_id`);

--
-- Indexes for table `tab_condominios_responsabilidades`
--
ALTER TABLE `tab_condominios_responsabilidades`
  ADD PRIMARY KEY (`cod_id`),
  ADD KEY `IX_rel_usu_cod` (`usu_id`),
  ADD KEY `IX_rel_crp_cod` (`crp_id`),
  ADD KEY `IX_rel_cru_cod` (`cru_id`),
  ADD KEY `IX_rel_con_cod` (`con_id`),
  ADD KEY `IX_rel_crg_cod` (`crg_id`);

--
-- Indexes for table `tab_condominios_responsabilidades_atividades`
--
ALTER TABLE `tab_condominios_responsabilidades_atividades`
  ADD PRIMARY KEY (`cov_id`);

--
-- Indexes for table `tab_condominios_responsabilidades_categorias`
--
ALTER TABLE `tab_condominios_responsabilidades_categorias`
  ADD PRIMARY KEY (`crg_id`);

--
-- Indexes for table `tab_condominios_responsabilidades_comentarios`
--
ALTER TABLE `tab_condominios_responsabilidades_comentarios`
  ADD PRIMARY KEY (`crm_id`),
  ADD KEY `IX_rel_usu_crm` (`usu_id`),
  ADD KEY `IX_rel_cod_crm` (`cod_id`);

--
-- Indexes for table `tab_condominios_responsabilidades_status`
--
ALTER TABLE `tab_condominios_responsabilidades_status`
  ADD PRIMARY KEY (`cru_id`);

--
-- Indexes for table `tab_condominios_responsabilidades_tipos`
--
ALTER TABLE `tab_condominios_responsabilidades_tipos`
  ADD PRIMARY KEY (`crp_id`);

--
-- Indexes for table `tab_condominios_situacoes`
--
ALTER TABLE `tab_condominios_situacoes`
  ADD PRIMARY KEY (`coi_id`);

--
-- Indexes for table `tab_condominios_status`
--
ALTER TABLE `tab_condominios_status`
  ADD PRIMARY KEY (`cos_id`);

--
-- Indexes for table `tab_condominios_tecnologias`
--
ALTER TABLE `tab_condominios_tecnologias`
  ADD PRIMARY KEY (`cog_id`);

--
-- Indexes for table `tab_condominios_tecnologias_equipamentos`
--
ALTER TABLE `tab_condominios_tecnologias_equipamentos`
  ADD PRIMARY KEY (`coq_id`),
  ADD KEY `IX_rel_cog_coq` (`cog_id`);

--
-- Indexes for table `tab_condominios_tipos`
--
ALTER TABLE `tab_condominios_tipos`
  ADD PRIMARY KEY (`cnt_id`);

--
-- Indexes for table `tab_contratos`
--
ALTER TABLE `tab_contratos`
  ADD PRIMARY KEY (`cor_id`),
  ADD UNIQUE KEY `IX_codigo` (`cor_codigo`),
  ADD UNIQUE KEY `cor_hash` (`cor_hash`),
  ADD KEY `IX_rel_crs_cor` (`crs_id`),
  ADD KEY `IX_rel_crt_cor` (`crt_id`),
  ADD KEY `IX_rel_cor_cor` (`cor_pai`),
  ADD KEY `IX_rel_cli_cor` (`cli_id`),
  ADD KEY `IX_rel_pls_cor` (`pls_id`),
  ADD KEY `cor_data_ativacao` (`cor_data_ativacao`),
  ADD KEY `cor_data_cancelamento` (`cor_data_cancelamento`),
  ADD KEY `IX_data_geracao` (`cor_data_geracao`),
  ADD KEY `cor_data_confirmacao` (`cor_data_confirmacao`),
  ADD KEY `cor_multa` (`cor_multa`),
  ADD KEY `cor_vigencia` (`cor_vigencia`),
  ADD KEY `IX_rel_fop_cor` (`fop_id`),
  ADD KEY `cma_id` (`cma_id`);

--
-- Indexes for table `tab_contratos_comentarios`
--
ALTER TABLE `tab_contratos_comentarios`
  ADD PRIMARY KEY (`coo_id`),
  ADD KEY `IX_rel_usu_coo` (`usu_id`),
  ADD KEY `IX_rel_cor_coo` (`cor_id`),
  ADD KEY `IX_rel_ccc_coo` (`ccc_id`);

--
-- Indexes for table `tab_contratos_comentarios_categorias`
--
ALTER TABLE `tab_contratos_comentarios_categorias`
  ADD PRIMARY KEY (`ccc_id`);

--
-- Indexes for table `tab_contratos_motivos_anulacao`
--
ALTER TABLE `tab_contratos_motivos_anulacao`
  ADD PRIMARY KEY (`cma_id`);

--
-- Indexes for table `tab_contratos_status`
--
ALTER TABLE `tab_contratos_status`
  ADD PRIMARY KEY (`crs_id`);

--
-- Indexes for table `tab_contratos_tipos`
--
ALTER TABLE `tab_contratos_tipos`
  ADD PRIMARY KEY (`crt_id`);

--
-- Indexes for table `tab_contratos_versoes`
--
ALTER TABLE `tab_contratos_versoes`
  ADD PRIMARY KEY (`cov_id`,`pls_id`,`pli_id`),
  ADD KEY `IX_rel_pls_cov` (`pls_id`),
  ADD KEY `IX_rel_pli_cov` (`pli_id`);

--
-- Indexes for table `tab_contratos_vinculos`
--
ALTER TABLE `tab_contratos_vinculos`
  ADD PRIMARY KEY (`cov_id`),
  ADD KEY `IX_rel_cor_cov` (`cor_id`),
  ADD KEY `IX_rel_cor_cov_filho` (`cor_filho`),
  ADD KEY `IX_rel_pvt_cov` (`pvt_id`);

--
-- Indexes for table `tab_cron`
--
ALTER TABLE `tab_cron`
  ADD PRIMARY KEY (`cro_id`);

--
-- Indexes for table `tab_cron_log`
--
ALTER TABLE `tab_cron_log`
  ADD PRIMARY KEY (`crl_id`),
  ADD KEY `crl_acao` (`crl_acao`);

--
-- Indexes for table `tab_descontos`
--
ALTER TABLE `tab_descontos`
  ADD PRIMARY KEY (`des_id`),
  ADD KEY `IX_rel_pls_des` (`pls_id`),
  ADD KEY `IX_rel_det_des` (`det_id`),
  ADD KEY `IX_rel_dea_des` (`dea_id`),
  ADD KEY `IX_rel_pli_des` (`pli_id`),
  ADD KEY `IX_rel_pla_des` (`pla_id`),
  ADD KEY `tab_descontos_pla_id` (`pla_id`),
  ADD KEY `clo_id` (`clo_id`);

--
-- Indexes for table `tab_descontos_concessoes`
--
ALTER TABLE `tab_descontos_concessoes`
  ADD PRIMARY KEY (`dec_id`),
  ADD KEY `IX_rel_con_dec` (`con_id`),
  ADD KEY `IX_rel_des_dec` (`des_id`),
  ADD KEY `IX_rel_dcs_dec` (`dcs_id`),
  ADD KEY `IX_rel_dec_dec` (`dec_pai`),
  ADD KEY `IX_rel_det_dec` (`det_id`),
  ADD KEY `IX_rel_ass_dec` (`ass_id`),
  ADD KEY `IX_rel_pla_dec` (`pla_id`);

--
-- Indexes for table `tab_descontos_concessoes_comentarios`
--
ALTER TABLE `tab_descontos_concessoes_comentarios`
  ADD PRIMARY KEY (`dcc_id`),
  ADD KEY `IX_rel_usu_dcc` (`usu_id`),
  ADD KEY `IX_rel_dec_dcc` (`dec_id`);

--
-- Indexes for table `tab_descontos_concessoes_status`
--
ALTER TABLE `tab_descontos_concessoes_status`
  ADD PRIMARY KEY (`dcs_id`);

--
-- Indexes for table `tab_descontos_status`
--
ALTER TABLE `tab_descontos_status`
  ADD PRIMARY KEY (`dea_id`);

--
-- Indexes for table `tab_descontos_tipos`
--
ALTER TABLE `tab_descontos_tipos`
  ADD PRIMARY KEY (`det_id`);

--
-- Indexes for table `tab_documentos`
--
ALTER TABLE `tab_documentos`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `IX_rel_doa_doc` (`doa_id`),
  ADD KEY `IX_rel_dou_doc` (`dou_id`),
  ADD KEY `IX_rel_dot_doc` (`dot_id`);

--
-- Indexes for table `tab_documentos_categorias`
--
ALTER TABLE `tab_documentos_categorias`
  ADD PRIMARY KEY (`doa_id`),
  ADD KEY `doa_pai` (`doa_pai`);

--
-- Indexes for table `tab_documentos_comentarios`
--
ALTER TABLE `tab_documentos_comentarios`
  ADD PRIMARY KEY (`doe_id`),
  ADD KEY `IX_rel_doc_doe` (`doc_id`),
  ADD KEY `IX_rel_usu_doe` (`usu_id`);

--
-- Indexes for table `tab_documentos_faqs`
--
ALTER TABLE `tab_documentos_faqs`
  ADD PRIMARY KEY (`dof_id`),
  ADD KEY `IX_rel_doc_dof` (`doc_id`),
  ADD KEY `IX_rel_dof_dof` (`dof_pai`),
  ADD KEY `IX_rel_usu_dof` (`usu_id`);

--
-- Indexes for table `tab_documentos_status`
--
ALTER TABLE `tab_documentos_status`
  ADD PRIMARY KEY (`dou_id`);

--
-- Indexes for table `tab_documentos_tipos`
--
ALTER TABLE `tab_documentos_tipos`
  ADD PRIMARY KEY (`dot_id`);

--
-- Indexes for table `tab_dominios`
--
ALTER TABLE `tab_dominios`
  ADD PRIMARY KEY (`dom_id`),
  ADD KEY `IX_rel_ass_dom` (`ass_id`),
  ADD KEY `IX_rel_doo_dom` (`doo_id`),
  ADD KEY `IX_rel_dos_dom` (`dos_id`),
  ADD KEY `dom_dominio` (`dom_dominio`);

--
-- Indexes for table `tab_dominios_origens`
--
ALTER TABLE `tab_dominios_origens`
  ADD PRIMARY KEY (`doo_id`);

--
-- Indexes for table `tab_dominios_status`
--
ALTER TABLE `tab_dominios_status`
  ADD PRIMARY KEY (`dos_id`);

--
-- Indexes for table `tab_empresas`
--
ALTER TABLE `tab_empresas`
  ADD PRIMARY KEY (`emp_id`);

--
-- Indexes for table `tab_equipamentos`
--
ALTER TABLE `tab_equipamentos`
  ADD PRIMARY KEY (`equ_id`);

--
-- Indexes for table `tab_equipamentos_modelos`
--
ALTER TABLE `tab_equipamentos_modelos`
  ADD PRIMARY KEY (`eqm_id`),
  ADD KEY `equ_id` (`equ_id`);

--
-- Indexes for table `tab_estados`
--
ALTER TABLE `tab_estados`
  ADD PRIMARY KEY (`est_id`),
  ADD KEY `est_sigla` (`est_sigla`),
  ADD KEY `est_nome` (`est_nome`);

--
-- Indexes for table `tab_estoque`
--
ALTER TABLE `tab_estoque`
  ADD PRIMARY KEY (`est_id`),
  ADD KEY `IX_rel_esi_est` (`esi_id`);

--
-- Indexes for table `tab_estoque_alocacoes`
--
ALTER TABLE `tab_estoque_alocacoes`
  ADD PRIMARY KEY (`esa_id`),
  ADD KEY `IX_rel_est_esa` (`est_id`),
  ADD KEY `IX_rel_esg_esa` (`esg_id`);

--
-- Indexes for table `tab_estoque_comprovantes`
--
ALTER TABLE `tab_estoque_comprovantes`
  ADD PRIMARY KEY (`esc_id`),
  ADD KEY `IX_rel_esf_esc` (`esf_id`),
  ADD KEY `IX_rel_ect_esc` (`ect_id`);

--
-- Indexes for table `tab_estoque_comprovantes_tipos`
--
ALTER TABLE `tab_estoque_comprovantes_tipos`
  ADD PRIMARY KEY (`ect_id`);

--
-- Indexes for table `tab_estoque_entradas`
--
ALTER TABLE `tab_estoque_entradas`
  ADD PRIMARY KEY (`ese_id`),
  ADD KEY `IX_rel_esi_ese` (`esi_id`),
  ADD KEY `IX_rel_esc_ese` (`esc_id`);

--
-- Indexes for table `tab_estoque_fornecedores`
--
ALTER TABLE `tab_estoque_fornecedores`
  ADD PRIMARY KEY (`esf_id`);

--
-- Indexes for table `tab_estoque_grupos`
--
ALTER TABLE `tab_estoque_grupos`
  ADD PRIMARY KEY (`esg_id`);

--
-- Indexes for table `tab_estoque_itens`
--
ALTER TABLE `tab_estoque_itens`
  ADD PRIMARY KEY (`esi_id`);

--
-- Indexes for table `tab_estoque_saidas`
--
ALTER TABLE `tab_estoque_saidas`
  ADD PRIMARY KEY (`esd_id`),
  ADD KEY `IX_rel_usu_esd` (`usu_id`),
  ADD KEY `IX_rel_esi_esd` (`esi_id`);

--
-- Indexes for table `tab_estoque_sublocacoes`
--
ALTER TABLE `tab_estoque_sublocacoes`
  ADD PRIMARY KEY (`ess_id`),
  ADD KEY `IX_rel_usu_ess` (`usu_id`),
  ADD KEY `IX_rel_esa_ess` (`esa_id`);

--
-- Indexes for table `tab_faturas`
--
ALTER TABLE `tab_faturas`
  ADD PRIMARY KEY (`fat_id`),
  ADD KEY `fal_id` (`fal_id`),
  ADD KEY `cli_id` (`cli_id`),
  ADD KEY `fcl_id` (`fcl_id`),
  ADD KEY `coa_id` (`coa_id`),
  ADD KEY `emp_id` (`emp_id`),
  ADD KEY `IX_tab_faturas_fap_id` (`fap_id`) USING BTREE;

--
-- Indexes for table `tab_faturas_filtros`
--
ALTER TABLE `tab_faturas_filtros`
  ADD PRIMARY KEY (`faf_id`);

--
-- Indexes for table `tab_faturas_itens`
--
ALTER TABLE `tab_faturas_itens`
  ADD PRIMARY KEY (`fai_id`),
  ADD KEY `fat_id` (`fat_id`),
  ADD KEY ` cft_id` (`cft_id`);

--
-- Indexes for table `tab_faturas_lotes`
--
ALTER TABLE `tab_faturas_lotes`
  ADD PRIMARY KEY (`fal_id`),
  ADD UNIQUE KEY `fal_ciclo` (`fal_ciclo`,`fap_id`) USING BTREE,
  ADD KEY `fls_id` (`fls_id`),
  ADD KEY `fal_data_ciclo` (`fal_data_ciclo`),
  ADD KEY `IX_tab_faturas_lotes_fap_id` (`fap_id`) USING BTREE;

--
-- Indexes for table `tab_faturas_lotes_clientes`
--
ALTER TABLE `tab_faturas_lotes_clientes`
  ADD PRIMARY KEY (`fcl_id`),
  ADD KEY `cli_id` (`cli_id`),
  ADD KEY `fal_id` (`fal_id`);

--
-- Indexes for table `tab_faturas_lotes_cobrancas`
--
ALTER TABLE `tab_faturas_lotes_cobrancas`
  ADD PRIMARY KEY (`fco_id`),
  ADD KEY `fal_id` (`fal_id`),
  ADD KEY `coa_id` (`coa_id`),
  ADD KEY `fat_id` (`fat_id`);

--
-- Indexes for table `tab_faturas_lotes_comentarios`
--
ALTER TABLE `tab_faturas_lotes_comentarios`
  ADD PRIMARY KEY (`flc_id`),
  ADD KEY `fal_id` (`fal_id`),
  ADD KEY `usu_id` (`usu_id`);

--
-- Indexes for table `tab_faturas_lotes_filtros`
--
ALTER TABLE `tab_faturas_lotes_filtros`
  ADD PRIMARY KEY (`flf_id`),
  ADD KEY `fal_id` (`fal_id`),
  ADD KEY `faf_id` (`faf_id`);

--
-- Indexes for table `tab_faturas_lotes_status`
--
ALTER TABLE `tab_faturas_lotes_status`
  ADD PRIMARY KEY (`fls_id`);

--
-- Indexes for table `tab_faturas_tipos`
--
ALTER TABLE `tab_faturas_tipos`
  ADD PRIMARY KEY (`fap_id`);

--
-- Indexes for table `tab_formas_pagamento`
--
ALTER TABLE `tab_formas_pagamento`
  ADD PRIMARY KEY (`fop_id`),
  ADD KEY `IX_rel_fpc_fop` (`fpc_id`),
  ADD KEY `IX_rel_pla_fop` (`pla_id`),
  ADD KEY `IX_rel_fpf_fop` (`fpf_id`),
  ADD KEY `pls_id` (`pls_id`);

--
-- Indexes for table `tab_formas_pagamento_categorias`
--
ALTER TABLE `tab_formas_pagamento_categorias`
  ADD PRIMARY KEY (`fpc_id`);

--
-- Indexes for table `tab_formas_pagamento_formas`
--
ALTER TABLE `tab_formas_pagamento_formas`
  ADD PRIMARY KEY (`fpf_id`);

--
-- Indexes for table `tab_grupos`
--
ALTER TABLE `tab_grupos`
  ADD PRIMARY KEY (`gru_id`),
  ADD KEY `IX_rel_grs_gru` (`grs_id`);

--
-- Indexes for table `tab_grupos_status`
--
ALTER TABLE `tab_grupos_status`
  ADD PRIMARY KEY (`grs_id`);

--
-- Indexes for table `tab_logradouros`
--
ALTER TABLE `tab_logradouros`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `IX_rel_bai_rua` (`bai_id`),
  ADD FULLTEXT KEY `log_nome` (`log_nome`);

--
-- Indexes for table `tab_mensagens`
--
ALTER TABLE `tab_mensagens`
  ADD PRIMARY KEY (`men_id`),
  ADD KEY `IX_rel_cli_men` (`cli_id`),
  ADD KEY `IX_rel_mel_men` (`mel_id`),
  ADD KEY `IX_rel_mes_men` (`mes_id`),
  ADD KEY `IX_rel_mem_men` (`mlm_id`),
  ADD KEY `IX_rel_usu_men` (`usu_id`),
  ADD KEY `mlm_id` (`mlm_id`),
  ADD KEY `men_data_hora_envio` (`men_data_hora_envio`);

--
-- Indexes for table `tab_mensagens_comentarios`
--
ALTER TABLE `tab_mensagens_comentarios`
  ADD PRIMARY KEY (`mec_id`),
  ADD KEY `IX_rel_usu_mec` (`usu_id`),
  ADD KEY `IX_rel_men_mec` (`men_id`),
  ADD KEY `IX_rel_mcc_mec` (`mcc_id`);

--
-- Indexes for table `tab_mensagens_comentarios_categorias`
--
ALTER TABLE `tab_mensagens_comentarios_categorias`
  ADD PRIMARY KEY (`mcc_id`);

--
-- Indexes for table `tab_mensagens_envios`
--
ALTER TABLE `tab_mensagens_envios`
  ADD PRIMARY KEY (`mee_id`),
  ADD KEY `IX_rel_men_mle` (`men_id`);

--
-- Indexes for table `tab_mensagens_links`
--
ALTER TABLE `tab_mensagens_links`
  ADD PRIMARY KEY (`mei_id`),
  ADD KEY `IX_rel_men_mei` (`men_id`);

--
-- Indexes for table `tab_mensagens_lotes`
--
ALTER TABLE `tab_mensagens_lotes`
  ADD PRIMARY KEY (`mel_id`),
  ADD KEY `IX_rel_mls_mel` (`mls_id`),
  ADD KEY `IX_rel_mlt_mel` (`mlt_id`),
  ADD KEY `IX_rel_mlm_mel` (`mlm_id`);

--
-- Indexes for table `tab_mensagens_lotes_campos`
--
ALTER TABLE `tab_mensagens_lotes_campos`
  ADD PRIMARY KEY (`mla_id`);

--
-- Indexes for table `tab_mensagens_lotes_comentarios`
--
ALTER TABLE `tab_mensagens_lotes_comentarios`
  ADD PRIMARY KEY (`mlc_id`),
  ADD KEY `IX_rel_usu_mlc` (`usu_id`),
  ADD KEY `IX_rel_mel_mlc` (`mel_id`),
  ADD KEY `IX_rel_mlo_mlc` (`mlo_id`);

--
-- Indexes for table `tab_mensagens_lotes_comentarios_categorias`
--
ALTER TABLE `tab_mensagens_lotes_comentarios_categorias`
  ADD PRIMARY KEY (`mlo_id`);

--
-- Indexes for table `tab_mensagens_lotes_filtros`
--
ALTER TABLE `tab_mensagens_lotes_filtros`
  ADD PRIMARY KEY (`mli_id`);

--
-- Indexes for table `tab_mensagens_lotes_modelos`
--
ALTER TABLE `tab_mensagens_lotes_modelos`
  ADD PRIMARY KEY (`mlm_id`),
  ADD KEY `IX_rel_mlt_mlm` (`mlt_id`),
  ADD KEY `IX_rel_mem_mem` (`mlm_pai`);

--
-- Indexes for table `tab_mensagens_lotes_status`
--
ALTER TABLE `tab_mensagens_lotes_status`
  ADD PRIMARY KEY (`mls_id`);

--
-- Indexes for table `tab_mensagens_lotes_tipos`
--
ALTER TABLE `tab_mensagens_lotes_tipos`
  ADD PRIMARY KEY (`mlt_id`);

--
-- Indexes for table `tab_mensagens_status`
--
ALTER TABLE `tab_mensagens_status`
  ADD PRIMARY KEY (`mes_id`);

--
-- Indexes for table `tab_menus`
--
ALTER TABLE `tab_menus`
  ADD PRIMARY KEY (`men_id`),
  ADD KEY `IX_rel_men_men` (`men_pai`);

--
-- Indexes for table `tab_modulos`
--
ALTER TABLE `tab_modulos`
  ADD PRIMARY KEY (`mod_id`),
  ADD KEY `IX_rel_mos_mod` (`mos_id`),
  ADD KEY `mod_nome` (`mod_nome`);

--
-- Indexes for table `tab_modulos_status`
--
ALTER TABLE `tab_modulos_status`
  ADD PRIMARY KEY (`mos_id`);

--
-- Indexes for table `tab_operacoes`
--
ALTER TABLE `tab_operacoes`
  ADD PRIMARY KEY (`ope_id`),
  ADD KEY `IX_rel_opl_ope` (`opl_id`),
  ADD KEY `IX_rel_usu_ope` (`usu_id`),
  ADD KEY `ope_inicio` (`ope_inicio`),
  ADD KEY `ope_termino` (`ope_termino`);

--
-- Indexes for table `tab_operacoes_contatos`
--
ALTER TABLE `tab_operacoes_contatos`
  ADD PRIMARY KEY (`opc_id`),
  ADD KEY `IX_rel_ope_opc` (`ope_id`),
  ADD KEY `IX_rel_usu_opc` (`usu_id`),
  ADD KEY `IX_rel_cli_opc` (`cli_id`),
  ADD KEY `IX_rel_occ_opc` (`occ_id`),
  ADD KEY `tab_operacoes_contatos_cli_id` (`cli_id`);

--
-- Indexes for table `tab_operacoes_contatos_categorias`
--
ALTER TABLE `tab_operacoes_contatos_categorias`
  ADD PRIMARY KEY (`occ_id`),
  ADD KEY `IX_rel_oli_occ` (`oli_id`);

--
-- Indexes for table `tab_operacoes_lotes`
--
ALTER TABLE `tab_operacoes_lotes`
  ADD PRIMARY KEY (`opl_id`),
  ADD KEY `IX_rel_ols_opl` (`ols_id`),
  ADD KEY `IX_rel_oli_opl` (`oli_id`);

--
-- Indexes for table `tab_operacoes_lotes_filtros`
--
ALTER TABLE `tab_operacoes_lotes_filtros`
  ADD PRIMARY KEY (`olf_id`),
  ADD KEY `IX_rel_oli_olf` (`oli_id`);

--
-- Indexes for table `tab_operacoes_lotes_periodos`
--
ALTER TABLE `tab_operacoes_lotes_periodos`
  ADD PRIMARY KEY (`olp_id`);

--
-- Indexes for table `tab_operacoes_lotes_responsabilidades`
--
ALTER TABLE `tab_operacoes_lotes_responsabilidades`
  ADD PRIMARY KEY (`olr_id`),
  ADD KEY `IX_rel_opl_olr` (`opl_id`),
  ADD KEY `IX_rel_cli_olr` (`cli_id`),
  ADD KEY `IX_rel_usu_olr` (`usu_id`);

--
-- Indexes for table `tab_operacoes_lotes_sistemas`
--
ALTER TABLE `tab_operacoes_lotes_sistemas`
  ADD PRIMARY KEY (`oli_id`);

--
-- Indexes for table `tab_operacoes_lotes_status`
--
ALTER TABLE `tab_operacoes_lotes_status`
  ADD PRIMARY KEY (`ols_id`);

--
-- Indexes for table `tab_operacoes_scripts`
--
ALTER TABLE `tab_operacoes_scripts`
  ADD PRIMARY KEY (`ops_id`),
  ADD KEY `IX_rel_ost_ops` (`ost_id`),
  ADD KEY `IX_rel_osf_ops` (`osf_id`),
  ADD KEY `IX_rel_osa_ops` (`osa_id`),
  ADD KEY `IX_rel_oli_ops` (`oli_id`);

--
-- Indexes for table `tab_operacoes_scripts_formatos`
--
ALTER TABLE `tab_operacoes_scripts_formatos`
  ADD PRIMARY KEY (`osf_id`);

--
-- Indexes for table `tab_operacoes_scripts_status`
--
ALTER TABLE `tab_operacoes_scripts_status`
  ADD PRIMARY KEY (`osa_id`);

--
-- Indexes for table `tab_operacoes_scripts_tipos`
--
ALTER TABLE `tab_operacoes_scripts_tipos`
  ADD PRIMARY KEY (`ost_id`);

--
-- Indexes for table `tab_organizacao_funcoes`
--
ALTER TABLE `tab_organizacao_funcoes`
  ADD PRIMARY KEY (`orf_id`),
  ADD KEY `IX_rel_ofs_orf` (`ofs_id`);

--
-- Indexes for table `tab_organizacao_funcoes_status`
--
ALTER TABLE `tab_organizacao_funcoes_status`
  ADD PRIMARY KEY (`ofs_id`);

--
-- Indexes for table `tab_organizacao_setores`
--
ALTER TABLE `tab_organizacao_setores`
  ADD PRIMARY KEY (`ors_id`),
  ADD KEY `IX_rel_ors_ors` (`ors_pai`),
  ADD KEY `IX_rel_oss_ors` (`oss_id`);

--
-- Indexes for table `tab_organizacao_setores_status`
--
ALTER TABLE `tab_organizacao_setores_status`
  ADD PRIMARY KEY (`oss_id`);

--
-- Indexes for table `tab_pesquisas`
--
ALTER TABLE `tab_pesquisas`
  ADD PRIMARY KEY (`pes_id`),
  ADD KEY `IX_rel_pet_pes` (`pet_id`);

--
-- Indexes for table `tab_pesquisas_aplicacoes`
--
ALTER TABLE `tab_pesquisas_aplicacoes`
  ADD PRIMARY KEY (`pea_id`),
  ADD KEY `IX_rel_cli_pea` (`cli_id`),
  ADD KEY `IX_rel_pes_pea` (`pes_id`),
  ADD KEY `IX_rel_cha_pea` (`cha_id`),
  ADD KEY `IX_rel_cor_pea` (`cor_id`),
  ADD KEY `IX_rel_usu_pea` (`usu_id`);

--
-- Indexes for table `tab_pesquisas_aplicacoes_respostas`
--
ALTER TABLE `tab_pesquisas_aplicacoes_respostas`
  ADD PRIMARY KEY (`par_id`),
  ADD KEY `IX_rel_pea_par` (`pea_id`),
  ADD KEY `IX_rel_peq_par` (`peq_id`),
  ADD KEY `IX_rel_pqa_par` (`pqa_id`);

--
-- Indexes for table `tab_pesquisas_questoes`
--
ALTER TABLE `tab_pesquisas_questoes`
  ADD PRIMARY KEY (`peq_id`),
  ADD KEY `IX_rel_pes_peq` (`pes_id`),
  ADD KEY `IX_rel_pqt_peq` (`pqt_id`),
  ADD KEY `IX_rel_pvt_peq` (`pvt_id`);

--
-- Indexes for table `tab_pesquisas_questoes_alternativas`
--
ALTER TABLE `tab_pesquisas_questoes_alternativas`
  ADD PRIMARY KEY (`pqa_id`),
  ADD KEY `IX_rel_peq_pqa` (`peq_id`);

--
-- Indexes for table `tab_pesquisas_questoes_tipos`
--
ALTER TABLE `tab_pesquisas_questoes_tipos`
  ADD PRIMARY KEY (`pqt_id`);

--
-- Indexes for table `tab_pesquisas_status`
--
ALTER TABLE `tab_pesquisas_status`
  ADD PRIMARY KEY (`pet_id`);

--
-- Indexes for table `tab_pesquisas_variaveis_tipos`
--
ALTER TABLE `tab_pesquisas_variaveis_tipos`
  ADD PRIMARY KEY (`pvt_id`);

--
-- Indexes for table `tab_placares`
--
ALTER TABLE `tab_placares`
  ADD PRIMARY KEY (`plc_id`),
  ADD KEY `IX_rel_plb_plc` (`plb_id`),
  ADD KEY `plc_data` (`plc_data`),
  ADD KEY `plc_geracao` (`plc_geracao`);

--
-- Indexes for table `tab_placares_abas`
--
ALTER TABLE `tab_placares_abas`
  ADD PRIMARY KEY (`plb_id`);

--
-- Indexes for table `tab_planos`
--
ALTER TABLE `tab_planos`
  ADD PRIMARY KEY (`pla_id`),
  ADD KEY `IX_rel_plt_pla` (`plt_id`),
  ADD KEY `IX_rel_pls_pla` (`pls_id`),
  ADD KEY `IX_rel_plu_pla` (`plu_id`),
  ADD KEY `IX_rel_sac_pla` (`sac_id`),
  ADD KEY `IX_rel_pli_pla` (`pli_id`),
  ADD KEY `IX_rel_con_pla` (`con_id`);

--
-- Indexes for table `tab_planos_equipamentos`
--
ALTER TABLE `tab_planos_equipamentos`
  ADD PRIMARY KEY (`ple_id`),
  ADD UNIQUE KEY `pla_id_equi_id` (`pla_id`,`equ_id`),
  ADD KEY `pla_id` (`pla_id`),
  ADD KEY `equ_id` (`equ_id`);

--
-- Indexes for table `tab_planos_servicos`
--
ALTER TABLE `tab_planos_servicos`
  ADD PRIMARY KEY (`pls_id`);

--
-- Indexes for table `tab_planos_sistemas`
--
ALTER TABLE `tab_planos_sistemas`
  ADD PRIMARY KEY (`pli_id`);

--
-- Indexes for table `tab_planos_status`
--
ALTER TABLE `tab_planos_status`
  ADD PRIMARY KEY (`plu_id`);

--
-- Indexes for table `tab_planos_tipos`
--
ALTER TABLE `tab_planos_tipos`
  ADD PRIMARY KEY (`plt_id`);

--
-- Indexes for table `tab_planos_vinculos`
--
ALTER TABLE `tab_planos_vinculos`
  ADD PRIMARY KEY (`plv_id`),
  ADD KEY `pla_filho` (`pla_filho`),
  ADD KEY `pva_id` (`pva_id`),
  ADD KEY `pvt_id` (`pvt_id`),
  ADD KEY `IX_rel_pla_plv` (`pla_id`);

--
-- Indexes for table `tab_planos_vinculos_aplicacoes`
--
ALTER TABLE `tab_planos_vinculos_aplicacoes`
  ADD PRIMARY KEY (`pva_id`);

--
-- Indexes for table `tab_planos_vinculos_tipos`
--
ALTER TABLE `tab_planos_vinculos_tipos`
  ADD PRIMARY KEY (`pvt_id`);

--
-- Indexes for table `tab_recuperacao_contatos`
--
ALTER TABLE `tab_recuperacao_contatos`
  ADD PRIMARY KEY (`rec_id`),
  ADD KEY `IX_rel_usu_rec` (`usu_id`),
  ADD KEY `IX_rel_coa_rec` (`coa_id`),
  ADD KEY `IX_rel_rct_rec` (`rct_id`);

--
-- Indexes for table `tab_recuperacao_contatos_tipos`
--
ALTER TABLE `tab_recuperacao_contatos_tipos`
  ADD PRIMARY KEY (`rct_id`);

--
-- Indexes for table `tab_redes`
--
ALTER TABLE `tab_redes`
  ADD PRIMARY KEY (`red_id`),
  ADD KEY `IX_rel_red_red` (`red_pai`),
  ADD KEY `IX_rel_ser_red` (`ser_id`),
  ADD KEY `IX_rel_sei_red` (`sei_id`);

--
-- Indexes for table `tab_servicos_ajustes`
--
ALTER TABLE `tab_servicos_ajustes`
  ADD PRIMARY KEY (`saj_id`),
  ADD KEY `IX_rel_sas_saj` (`sas_id`),
  ADD KEY `IX_rel_sac_saj` (`sac_id`),
  ADD KEY `IX_rel_ass_saj` (`ass_id`),
  ADD KEY `IX_rel_saj_saj` (`saj_pai`),
  ADD KEY `IX_rel_cli_saj` (`cli_id`),
  ADD KEY `IX_rel_ajf_saj` (`ajf_id`);

--
-- Indexes for table `tab_servicos_ajustes_categorias`
--
ALTER TABLE `tab_servicos_ajustes_categorias`
  ADD PRIMARY KEY (`sac_id`);

--
-- Indexes for table `tab_servicos_ajustes_comentarios`
--
ALTER TABLE `tab_servicos_ajustes_comentarios`
  ADD PRIMARY KEY (`sam_id`),
  ADD KEY `IX_rel_usu_sam` (`usu_id`),
  ADD KEY `IX_rel_saj_sam` (`saj_id`);

--
-- Indexes for table `tab_servicos_ajustes_regras`
--
ALTER TABLE `tab_servicos_ajustes_regras`
  ADD PRIMARY KEY (`sar_id`),
  ADD KEY `IX_rel_saj_sar` (`saj_id`),
  ADD KEY `IX_rel_sat_sar` (`sat_id`);

--
-- Indexes for table `tab_servicos_ajustes_status`
--
ALTER TABLE `tab_servicos_ajustes_status`
  ADD PRIMARY KEY (`sas_id`);

--
-- Indexes for table `tab_servicos_ajustes_tipos`
--
ALTER TABLE `tab_servicos_ajustes_tipos`
  ADD PRIMARY KEY (`sat_id`);

--
-- Indexes for table `tab_servidores`
--
ALTER TABLE `tab_servidores`
  ADD PRIMARY KEY (`ser_id`),
  ADD KEY `IX_rel_sei_ser` (`sei_id`),
  ADD KEY `IX_rel_set_ser` (`set_id`),
  ADD KEY `IX_rel_sep_ser` (`sep_id`),
  ADD KEY `IX_rel_seu_ser` (`seu_id`),
  ADD KEY `IX_rel_cha_ser` (`cha_id`),
  ADD KEY `IX_rel_sso_ser` (`sso_id`);

--
-- Indexes for table `tab_servidores_interfaces`
--
ALTER TABLE `tab_servidores_interfaces`
  ADD PRIMARY KEY (`sei_id`),
  ADD KEY `IX_rel_ser_sei` (`ser_id`),
  ADD KEY `IX_rel_sit_sei` (`sit_id`),
  ADD KEY `IX_rel_sip_sei` (`sip_id`),
  ADD KEY `IX_rel_sia_sei` (`sia_id`),
  ADD KEY `IX_rel_sei_sei` (`sei_pai`);

--
-- Indexes for table `tab_servidores_interfaces_padroes`
--
ALTER TABLE `tab_servidores_interfaces_padroes`
  ADD PRIMARY KEY (`sip_id`);

--
-- Indexes for table `tab_servidores_interfaces_status`
--
ALTER TABLE `tab_servidores_interfaces_status`
  ADD PRIMARY KEY (`sia_id`);

--
-- Indexes for table `tab_servidores_interfaces_tipos`
--
ALTER TABLE `tab_servidores_interfaces_tipos`
  ADD PRIMARY KEY (`sit_id`);

--
-- Indexes for table `tab_servidores_links`
--
ALTER TABLE `tab_servidores_links`
  ADD PRIMARY KEY (`sel_id`),
  ADD KEY `IX_rel_sei_sel` (`sei_id`),
  ADD KEY `IX_rel_sls_sel` (`sls_id`);

--
-- Indexes for table `tab_servidores_links_status`
--
ALTER TABLE `tab_servidores_links_status`
  ADD PRIMARY KEY (`sls_id`);

--
-- Indexes for table `tab_servidores_servicos`
--
ALTER TABLE `tab_servidores_servicos`
  ADD PRIMARY KEY (`ses_id`),
  ADD KEY `IX_rel_sei_ses` (`sei_id`),
  ADD KEY `IX_rel_sst_ses` (`sst_id`),
  ADD KEY `IX_rel_sss_ses` (`sss_id`);

--
-- Indexes for table `tab_servidores_servicos_status`
--
ALTER TABLE `tab_servidores_servicos_status`
  ADD PRIMARY KEY (`sss_id`);

--
-- Indexes for table `tab_servidores_servicos_tipos`
--
ALTER TABLE `tab_servidores_servicos_tipos`
  ADD PRIMARY KEY (`sst_id`),
  ADD KEY `IX_rel_sit_sst` (`sit_id`);

--
-- Indexes for table `tab_servidores_sistemas_operacionais`
--
ALTER TABLE `tab_servidores_sistemas_operacionais`
  ADD PRIMARY KEY (`sso_id`);

--
-- Indexes for table `tab_servidores_situacoes`
--
ALTER TABLE `tab_servidores_situacoes`
  ADD PRIMARY KEY (`seu_id`);

--
-- Indexes for table `tab_servidores_ssids`
--
ALTER TABLE `tab_servidores_ssids`
  ADD PRIMARY KEY (`ssi_id`),
  ADD KEY `sei_id` (`sei_id`),
  ADD KEY `sst_id` (`sst_id`);

--
-- Indexes for table `tab_servidores_ssids_tipos`
--
ALTER TABLE `tab_servidores_ssids_tipos`
  ADD PRIMARY KEY (`sst_id`);

--
-- Indexes for table `tab_servidores_status`
--
ALTER TABLE `tab_servidores_status`
  ADD PRIMARY KEY (`set_id`);

--
-- Indexes for table `tab_servidores_tipos`
--
ALTER TABLE `tab_servidores_tipos`
  ADD PRIMARY KEY (`sep_id`);

--
-- Indexes for table `tab_sessoes`
--
ALTER TABLE `tab_sessoes`
  ADD PRIMARY KEY (`ses_id`),
  ADD KEY `IX_rel_usu_log` (`usu_id`);

--
-- Indexes for table `tab_tarefas`
--
ALTER TABLE `tab_tarefas`
  ADD PRIMARY KEY (`tar_id`),
  ADD KEY `IX_rel_usu_tar` (`usu_id`),
  ADD KEY `IX_rel_ors_tar` (`ors_id`),
  ADD KEY `IX_rel_orf_tar` (`orf_id`),
  ADD KEY `IX_rel_tat_tar` (`tat_id`),
  ADD KEY `IX_rel_tas_tar` (`tas_id`),
  ADD KEY `tab_tarefas_tas_id` (`tas_id`);

--
-- Indexes for table `tab_tarefas_etapas`
--
ALTER TABLE `tab_tarefas_etapas`
  ADD PRIMARY KEY (`tae_id`),
  ADD KEY `IX_rel_usu_tae` (`usu_id`),
  ADD KEY `IX_rel_ors_tae` (`ors_id`),
  ADD KEY `IX_rel_orf_tae` (`orf_id`),
  ADD KEY `IX_rel_tar_tae` (`tar_id`),
  ADD KEY `IX_rel_tet_tae` (`tet_id`),
  ADD KEY `IX_rel_tes_tae` (`tes_id`);

--
-- Indexes for table `tab_tarefas_etapas_status`
--
ALTER TABLE `tab_tarefas_etapas_status`
  ADD PRIMARY KEY (`tes_id`);

--
-- Indexes for table `tab_tarefas_etapas_tipos`
--
ALTER TABLE `tab_tarefas_etapas_tipos`
  ADD PRIMARY KEY (`tet_id`);

--
-- Indexes for table `tab_tarefas_status`
--
ALTER TABLE `tab_tarefas_status`
  ADD PRIMARY KEY (`tas_id`);

--
-- Indexes for table `tab_tarefas_tipos`
--
ALTER TABLE `tab_tarefas_tipos`
  ADD PRIMARY KEY (`tat_id`),
  ADD KEY `IX_rel_ors_tat` (`ors_id`);

--
-- Indexes for table `tab_usuarios`
--
ALTER TABLE `tab_usuarios`
  ADD PRIMARY KEY (`usu_id`),
  ADD UNIQUE KEY `usu_login` (`usu_login`),
  ADD KEY `IX_rel_uss_usu` (`uss_id`),
  ADD KEY `IX_rel_cep_usu` (`cep_id`),
  ADD KEY `usu_nome` (`usu_nome`),
  ADD KEY `log_id` (`log_id`),
  ADD KEY `bai_id` (`bai_id`),
  ADD KEY `cid_id` (`cid_id`),
  ADD KEY `est_id` (`est_id`),
  ADD KEY `clo_id` (`clo_id`);

--
-- Indexes for table `tab_usuarios_status`
--
ALTER TABLE `tab_usuarios_status`
  ADD PRIMARY KEY (`uss_id`);

--
-- Indexes for table `tab_vendas`
--
ALTER TABLE `tab_vendas`
  ADD PRIMARY KEY (`ven_id`),
  ADD KEY `IX_rel_usu_ven` (`usu_id`),
  ADD KEY `IX_rel_ass_ven` (`ass_id`),
  ADD KEY `IX_rel_cor_ven` (`cor_id`),
  ADD KEY `ven_primeiro_pagamento` (`ven_primeiro_pagamento`);

--
-- Indexes for table `tab_vendas_comentarios`
--
ALTER TABLE `tab_vendas_comentarios`
  ADD PRIMARY KEY (`veo_id`),
  ADD KEY `IX_rel_usu_veo` (`usu_id`),
  ADD KEY `IX_rel_ven_veo` (`ven_id`);

--
-- Indexes for table `tab_vendas_comissoes`
--
ALTER TABLE `tab_vendas_comissoes`
  ADD PRIMARY KEY (`vec_id`),
  ADD KEY `IX_rel_usu_vec` (`usu_id`),
  ADD KEY `IX_rel_ven_vec` (`ven_id`),
  ADD KEY `IX_rel_vct_vec` (`vct_id`);

--
-- Indexes for table `tab_vendas_comissoes_comentarios`
--
ALTER TABLE `tab_vendas_comissoes_comentarios`
  ADD PRIMARY KEY (`vco_id`),
  ADD KEY `IX_rel_usu_vco` (`usu_id`),
  ADD KEY `IX_rel_vec_vco` (`vec_id`);

--
-- Indexes for table `tab_vendas_comissoes_tipos`
--
ALTER TABLE `tab_vendas_comissoes_tipos`
  ADD PRIMARY KEY (`vct_id`);

--
-- Indexes for table `tab_visitas`
--
ALTER TABLE `tab_visitas`
  ADD PRIMARY KEY (`vis_id`),
  ADD UNIQUE KEY `cor_id` (`cor_id`),
  ADD KEY `IX_rel_cli_vis` (`cli_id`),
  ADD KEY `IX_rel_via_vis` (`via_id`),
  ADD KEY `IX_rel_vip_vis` (`vip_id`),
  ADD KEY `IX_rel_vit_vis` (`vit_id`),
  ADD KEY `vis_data` (`vis_data`),
  ADD KEY `IX_rel_usu_vis` (`usu_id`),
  ADD KEY `cha_id` (`cha_id`),
  ADD KEY `con_id` (`con_id`);

--
-- Indexes for table `tab_visitas_comentarios`
--
ALTER TABLE `tab_visitas_comentarios`
  ADD PRIMARY KEY (`vic_id`),
  ADD KEY `IX_rel_vis_vic` (`vis_id`),
  ADD KEY `IX_rel_vcc_vic` (`vcc_id`),
  ADD KEY `IX_rel_usu_vic` (`usu_id`);

--
-- Indexes for table `tab_visitas_comentarios_categorias`
--
ALTER TABLE `tab_visitas_comentarios_categorias`
  ADD PRIMARY KEY (`vcc_id`);

--
-- Indexes for table `tab_visitas_cotas`
--
ALTER TABLE `tab_visitas_cotas`
  ADD PRIMARY KEY (`vic_id`),
  ADD KEY `vit_id` (`vit_id`);

--
-- Indexes for table `tab_visitas_periodos`
--
ALTER TABLE `tab_visitas_periodos`
  ADD PRIMARY KEY (`vip_id`);

--
-- Indexes for table `tab_visitas_status`
--
ALTER TABLE `tab_visitas_status`
  ADD PRIMARY KEY (`via_id`);

--
-- Indexes for table `tab_visitas_tipos`
--
ALTER TABLE `tab_visitas_tipos`
  ADD PRIMARY KEY (`vit_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `log_integracao`
--
ALTER TABLE `log_integracao`
  MODIFY `log_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_acoes`
--
ALTER TABLE `tab_acoes`
  MODIFY `aco_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_acoes_tipos`
--
ALTER TABLE `tab_acoes_tipos`
  MODIFY `act_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_ajustes_financeiros`
--
ALTER TABLE `tab_ajustes_financeiros`
  MODIFY `ajf_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_ajustes_financeiros_categorias`
--
ALTER TABLE `tab_ajustes_financeiros_categorias`
  MODIFY `afc_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_ajustes_financeiros_comentarios`
--
ALTER TABLE `tab_ajustes_financeiros_comentarios`
  MODIFY `afm_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_ajustes_financeiros_status`
--
ALTER TABLE `tab_ajustes_financeiros_status`
  MODIFY `afs_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_ajustes_financeiros_tipos`
--
ALTER TABLE `tab_ajustes_financeiros_tipos`
  MODIFY `aft_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_assinaturas`
--
ALTER TABLE `tab_assinaturas`
  MODIFY `ass_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_assinaturas_status`
--
ALTER TABLE `tab_assinaturas_status`
  MODIFY `ast_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_assinaturas_vinculos`
--
ALTER TABLE `tab_assinaturas_vinculos`
  MODIFY `asv_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_atividades`
--
ALTER TABLE `tab_atividades`
  MODIFY `ati_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_bairros`
--
ALTER TABLE `tab_bairros`
  MODIFY `bai_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_campanhas`
--
ALTER TABLE `tab_campanhas`
  MODIFY `cam_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_campanhas_contatos`
--
ALTER TABLE `tab_campanhas_contatos`
  MODIFY `cac_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_campanhas_contatos_status`
--
ALTER TABLE `tab_campanhas_contatos_status`
  MODIFY `ccs_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_campanhas_contatos_telefones`
--
ALTER TABLE `tab_campanhas_contatos_telefones`
  MODIFY `cct_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_campanhas_operacoes`
--
ALTER TABLE `tab_campanhas_operacoes`
  MODIFY `cao_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_campanhas_operacoes_contatos`
--
ALTER TABLE `tab_campanhas_operacoes_contatos`
  MODIFY `coc_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_campanhas_operacoes_contatos_situacoes`
--
ALTER TABLE `tab_campanhas_operacoes_contatos_situacoes`
  MODIFY `cos_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_campanhas_status`
--
ALTER TABLE `tab_campanhas_status`
  MODIFY `cas_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_campanhas_tipos`
--
ALTER TABLE `tab_campanhas_tipos`
  MODIFY `cat_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cancelamentos`
--
ALTER TABLE `tab_cancelamentos`
  MODIFY `can_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cancelamentos_etapas`
--
ALTER TABLE `tab_cancelamentos_etapas`
  MODIFY `cae_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cancelamentos_etapas_niveis`
--
ALTER TABLE `tab_cancelamentos_etapas_niveis`
  MODIFY `cen_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cancelamentos_etapas_status`
--
ALTER TABLE `tab_cancelamentos_etapas_status`
  MODIFY `ces_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cancelamentos_etapas_tipos`
--
ALTER TABLE `tab_cancelamentos_etapas_tipos`
  MODIFY `cet_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cancelamentos_motivos`
--
ALTER TABLE `tab_cancelamentos_motivos`
  MODIFY `cam_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cancelamentos_motivos_status`
--
ALTER TABLE `tab_cancelamentos_motivos_status`
  MODIFY `cms_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cancelamentos_origens`
--
ALTER TABLE `tab_cancelamentos_origens`
  MODIFY `cao_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cancelamentos_status`
--
ALTER TABLE `tab_cancelamentos_status`
  MODIFY `cas_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_central_telefonica_agregados`
--
ALTER TABLE `tab_central_telefonica_agregados`
  MODIFY `ctl_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_central_telefonica_ligacoes`
--
ALTER TABLE `tab_central_telefonica_ligacoes`
  MODIFY `ctl_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_central_telefonica_ligacoes_situacoes`
--
ALTER TABLE `tab_central_telefonica_ligacoes_situacoes`
  MODIFY `cls_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_central_telefonica_ligacoes_tipos`
--
ALTER TABLE `tab_central_telefonica_ligacoes_tipos`
  MODIFY `clt_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_central_telefonica_setores`
--
ALTER TABLE `tab_central_telefonica_setores`
  MODIFY `cts_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_ceps`
--
ALTER TABLE `tab_ceps`
  MODIFY `cep_id` int(8) unsigned zerofill NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_chamados`
--
ALTER TABLE `tab_chamados`
  MODIFY `cha_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_chamados_acoes`
--
ALTER TABLE `tab_chamados_acoes`
  MODIFY `caa_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_chamados_categorias`
--
ALTER TABLE `tab_chamados_categorias`
  MODIFY `chc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_chamados_categorias_etapas`
--
ALTER TABLE `tab_chamados_categorias_etapas`
  MODIFY `cce_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_chamados_etapas`
--
ALTER TABLE `tab_chamados_etapas`
  MODIFY `che_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_chamados_etapas_niveis_stress`
--
ALTER TABLE `tab_chamados_etapas_niveis_stress`
  MODIFY `cen_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_chamados_etapas_tipos`
--
ALTER TABLE `tab_chamados_etapas_tipos`
  MODIFY `cet_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_chamados_prioridades`
--
ALTER TABLE `tab_chamados_prioridades`
  MODIFY `chp_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_chamados_roteiros`
--
ALTER TABLE `tab_chamados_roteiros`
  MODIFY `chr_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_chamados_roteiros_etapas`
--
ALTER TABLE `tab_chamados_roteiros_etapas`
  MODIFY `cre_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_chamados_situacoes`
--
ALTER TABLE `tab_chamados_situacoes`
  MODIFY `chi_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_chamados_status`
--
ALTER TABLE `tab_chamados_status`
  MODIFY `chs_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_chamados_visualizacoes`
--
ALTER TABLE `tab_chamados_visualizacoes`
  MODIFY `chv_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cidades`
--
ALTER TABLE `tab_cidades`
  MODIFY `cid_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_classes_fiscais`
--
ALTER TABLE `tab_classes_fiscais`
  MODIFY `clf_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_classes_fiscais_tipos`
--
ALTER TABLE `tab_classes_fiscais_tipos`
  MODIFY `cft_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes`
--
ALTER TABLE `tab_clientes`
  MODIFY `cli_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_autenticacoes`
--
ALTER TABLE `tab_clientes_autenticacoes`
  MODIFY `cau_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_autenticacoes_ajustes`
--
ALTER TABLE `tab_clientes_autenticacoes_ajustes`
  MODIFY `caa_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_autenticacoes_autenticadores`
--
ALTER TABLE `tab_clientes_autenticacoes_autenticadores`
  MODIFY `caa_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_autenticacoes_comentarios`
--
ALTER TABLE `tab_clientes_autenticacoes_comentarios`
  MODIFY `cac_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_autenticacoes_status`
--
ALTER TABLE `tab_clientes_autenticacoes_status`
  MODIFY `cas_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_basesdedados`
--
ALTER TABLE `tab_clientes_basesdedados`
  MODIFY `clb_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_basesdedados_status`
--
ALTER TABLE `tab_clientes_basesdedados_status`
  MODIFY `cbs_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_basesdedados_tipos`
--
ALTER TABLE `tab_clientes_basesdedados_tipos`
  MODIFY `cbt_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_comodatos`
--
ALTER TABLE `tab_clientes_comodatos`
  MODIFY `clc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_comodatos_comentarios`
--
ALTER TABLE `tab_clientes_comodatos_comentarios`
  MODIFY `ccc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_comodatos_status`
--
ALTER TABLE `tab_clientes_comodatos_status`
  MODIFY `ccs_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_consultas_credito`
--
ALTER TABLE `tab_clientes_consultas_credito`
  MODIFY `ccc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_consultas_credito_situacoes`
--
ALTER TABLE `tab_clientes_consultas_credito_situacoes`
  MODIFY `ccs_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_contatos_emails`
--
ALTER TABLE `tab_clientes_contatos_emails`
  MODIFY `ccm_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_contatos_emails_tipos`
--
ALTER TABLE `tab_clientes_contatos_emails_tipos`
  MODIFY `cca_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_contatos_telefones`
--
ALTER TABLE `tab_clientes_contatos_telefones`
  MODIFY `ccl_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_contatos_telefones_tipos`
--
ALTER TABLE `tab_clientes_contatos_telefones_tipos`
  MODIFY `ccf_id` tinyint(3) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_emails`
--
ALTER TABLE `tab_clientes_emails`
  MODIFY `cle_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_emails_ajustes`
--
ALTER TABLE `tab_clientes_emails_ajustes`
  MODIFY `cea_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_emails_comentarios`
--
ALTER TABLE `tab_clientes_emails_comentarios`
  MODIFY `cec_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_emails_status`
--
ALTER TABLE `tab_clientes_emails_status`
  MODIFY `ces_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_emails_tipos`
--
ALTER TABLE `tab_clientes_emails_tipos`
  MODIFY `cet_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_hospedagens`
--
ALTER TABLE `tab_clientes_hospedagens`
  MODIFY `clh_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_hospedagens_status`
--
ALTER TABLE `tab_clientes_hospedagens_status`
  MODIFY `chs_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_indicacoes`
--
ALTER TABLE `tab_clientes_indicacoes`
  MODIFY `cld_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_iptv`
--
ALTER TABLE `tab_clientes_iptv`
  MODIFY `cip_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_iptv_ajustes`
--
ALTER TABLE `tab_clientes_iptv_ajustes`
  MODIFY `cia_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_iptv_comentarios`
--
ALTER TABLE `tab_clientes_iptv_comentarios`
  MODIFY `cic_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_iptv_status`
--
ALTER TABLE `tab_clientes_iptv_status`
  MODIFY `cis_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_negativacoes`
--
ALTER TABLE `tab_clientes_negativacoes`
  MODIFY `cln_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_negativacoes_comentarios`
--
ALTER TABLE `tab_clientes_negativacoes_comentarios`
  MODIFY `cnc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_operadoras_origens`
--
ALTER TABLE `tab_clientes_operadoras_origens`
  MODIFY `coo_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_origens`
--
ALTER TABLE `tab_clientes_origens`
  MODIFY `clo_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_origens_historicoapi`
--
ALTER TABLE `tab_clientes_origens_historicoapi`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_origens_status`
--
ALTER TABLE `tab_clientes_origens_status`
  MODIFY `cos_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_pontos`
--
ALTER TABLE `tab_clientes_pontos`
  MODIFY `clp_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_pontos_ajustes`
--
ALTER TABLE `tab_clientes_pontos_ajustes`
  MODIFY `cpa_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_pontos_comentarios`
--
ALTER TABLE `tab_clientes_pontos_comentarios`
  MODIFY `cpc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_pontos_status`
--
ALTER TABLE `tab_clientes_pontos_status`
  MODIFY `cps_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_responsabilidades`
--
ALTER TABLE `tab_clientes_responsabilidades`
  MODIFY `cln_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_responsabilidades_categorias`
--
ALTER TABLE `tab_clientes_responsabilidades_categorias`
  MODIFY `crt_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_responsabilidades_comentarios`
--
ALTER TABLE `tab_clientes_responsabilidades_comentarios`
  MODIFY `crc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_responsabilidades_status`
--
ALTER TABLE `tab_clientes_responsabilidades_status`
  MODIFY `cra_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_responsabilidades_tipos`
--
ALTER TABLE `tab_clientes_responsabilidades_tipos`
  MODIFY `cri_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_retornos`
--
ALTER TABLE `tab_clientes_retornos`
  MODIFY `clr_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_retornos_periodos`
--
ALTER TABLE `tab_clientes_retornos_periodos`
  MODIFY `cee_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_retornos_status`
--
ALTER TABLE `tab_clientes_retornos_status`
  MODIFY `ces_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_retornos_tipos`
--
ALTER TABLE `tab_clientes_retornos_tipos`
  MODIFY `cet_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_situacoes`
--
ALTER TABLE `tab_clientes_situacoes`
  MODIFY `clu_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_status`
--
ALTER TABLE `tab_clientes_status`
  MODIFY `cls_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_telefones`
--
ALTER TABLE `tab_clientes_telefones`
  MODIFY `clt_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_telefones_recargas`
--
ALTER TABLE `tab_clientes_telefones_recargas`
  MODIFY `ctr_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_telefones_status`
--
ALTER TABLE `tab_clientes_telefones_status`
  MODIFY `cts_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_telefones_tipos`
--
ALTER TABLE `tab_clientes_telefones_tipos`
  MODIFY `ctt_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_clientes_tipos`
--
ALTER TABLE `tab_clientes_tipos`
  MODIFY `clt_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas`
--
ALTER TABLE `tab_cobrancas`
  MODIFY `coa_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas_comentarios`
--
ALTER TABLE `tab_cobrancas_comentarios`
  MODIFY `com_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas_detalhes`
--
ALTER TABLE `tab_cobrancas_detalhes`
  MODIFY `cod_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas_formatos`
--
ALTER TABLE `tab_cobrancas_formatos`
  MODIFY `cof_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas_formatos_status`
--
ALTER TABLE `tab_cobrancas_formatos_status`
  MODIFY `cfs_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas_formatos_tipos`
--
ALTER TABLE `tab_cobrancas_formatos_tipos`
  MODIFY `cft_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas_lotes`
--
ALTER TABLE `tab_cobrancas_lotes`
  MODIFY `col_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas_lotes_comentarios`
--
ALTER TABLE `tab_cobrancas_lotes_comentarios`
  MODIFY `clo_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas_lotes_status`
--
ALTER TABLE `tab_cobrancas_lotes_status`
  MODIFY `cla_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas_remessas`
--
ALTER TABLE `tab_cobrancas_remessas`
  MODIFY `cbr_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas_retornos`
--
ALTER TABLE `tab_cobrancas_retornos`
  MODIFY `coe_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas_retornos_erros`
--
ALTER TABLE `tab_cobrancas_retornos_erros`
  MODIFY `cos_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas_retornos_nao_encontradas`
--
ALTER TABLE `tab_cobrancas_retornos_nao_encontradas`
  MODIFY `crn_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas_status`
--
ALTER TABLE `tab_cobrancas_status`
  MODIFY `cot_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cobrancas_tipos`
--
ALTER TABLE `tab_cobrancas_tipos`
  MODIFY `cop_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios`
--
ALTER TABLE `tab_condominios`
  MODIFY `con_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_blocos`
--
ALTER TABLE `tab_condominios_blocos`
  MODIFY `cob_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_blocos_status`
--
ALTER TABLE `tab_condominios_blocos_status`
  MODIFY `cbs_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_classes`
--
ALTER TABLE `tab_condominios_classes`
  MODIFY `ccl_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_contatos`
--
ALTER TABLE `tab_condominios_contatos`
  MODIFY `coc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_contatos_tipos`
--
ALTER TABLE `tab_condominios_contatos_tipos`
  MODIFY `cct_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_instalacoes_tipos`
--
ALTER TABLE `tab_condominios_instalacoes_tipos`
  MODIFY `cit_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_procedimentos`
--
ALTER TABLE `tab_condominios_procedimentos`
  MODIFY `cop_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_procedimentos_tipos`
--
ALTER TABLE `tab_condominios_procedimentos_tipos`
  MODIFY `cpt_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_responsabilidades`
--
ALTER TABLE `tab_condominios_responsabilidades`
  MODIFY `cod_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_responsabilidades_atividades`
--
ALTER TABLE `tab_condominios_responsabilidades_atividades`
  MODIFY `cov_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_responsabilidades_categorias`
--
ALTER TABLE `tab_condominios_responsabilidades_categorias`
  MODIFY `crg_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_responsabilidades_comentarios`
--
ALTER TABLE `tab_condominios_responsabilidades_comentarios`
  MODIFY `crm_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_responsabilidades_status`
--
ALTER TABLE `tab_condominios_responsabilidades_status`
  MODIFY `cru_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_responsabilidades_tipos`
--
ALTER TABLE `tab_condominios_responsabilidades_tipos`
  MODIFY `crp_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_situacoes`
--
ALTER TABLE `tab_condominios_situacoes`
  MODIFY `coi_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_status`
--
ALTER TABLE `tab_condominios_status`
  MODIFY `cos_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_tecnologias`
--
ALTER TABLE `tab_condominios_tecnologias`
  MODIFY `cog_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_tecnologias_equipamentos`
--
ALTER TABLE `tab_condominios_tecnologias_equipamentos`
  MODIFY `coq_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_condominios_tipos`
--
ALTER TABLE `tab_condominios_tipos`
  MODIFY `cnt_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_contratos`
--
ALTER TABLE `tab_contratos`
  MODIFY `cor_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_contratos_comentarios`
--
ALTER TABLE `tab_contratos_comentarios`
  MODIFY `coo_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_contratos_comentarios_categorias`
--
ALTER TABLE `tab_contratos_comentarios_categorias`
  MODIFY `ccc_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_contratos_motivos_anulacao`
--
ALTER TABLE `tab_contratos_motivos_anulacao`
  MODIFY `cma_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_contratos_status`
--
ALTER TABLE `tab_contratos_status`
  MODIFY `crs_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_contratos_tipos`
--
ALTER TABLE `tab_contratos_tipos`
  MODIFY `crt_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_contratos_vinculos`
--
ALTER TABLE `tab_contratos_vinculos`
  MODIFY `cov_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cron`
--
ALTER TABLE `tab_cron`
  MODIFY `cro_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_cron_log`
--
ALTER TABLE `tab_cron_log`
  MODIFY `crl_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_descontos`
--
ALTER TABLE `tab_descontos`
  MODIFY `des_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_descontos_concessoes`
--
ALTER TABLE `tab_descontos_concessoes`
  MODIFY `dec_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_descontos_concessoes_comentarios`
--
ALTER TABLE `tab_descontos_concessoes_comentarios`
  MODIFY `dcc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_descontos_concessoes_status`
--
ALTER TABLE `tab_descontos_concessoes_status`
  MODIFY `dcs_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_descontos_status`
--
ALTER TABLE `tab_descontos_status`
  MODIFY `dea_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_descontos_tipos`
--
ALTER TABLE `tab_descontos_tipos`
  MODIFY `det_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_documentos`
--
ALTER TABLE `tab_documentos`
  MODIFY `doc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_documentos_categorias`
--
ALTER TABLE `tab_documentos_categorias`
  MODIFY `doa_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_documentos_comentarios`
--
ALTER TABLE `tab_documentos_comentarios`
  MODIFY `doe_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_documentos_faqs`
--
ALTER TABLE `tab_documentos_faqs`
  MODIFY `dof_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_documentos_status`
--
ALTER TABLE `tab_documentos_status`
  MODIFY `dou_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_documentos_tipos`
--
ALTER TABLE `tab_documentos_tipos`
  MODIFY `dot_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_dominios`
--
ALTER TABLE `tab_dominios`
  MODIFY `dom_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_dominios_origens`
--
ALTER TABLE `tab_dominios_origens`
  MODIFY `doo_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_dominios_status`
--
ALTER TABLE `tab_dominios_status`
  MODIFY `dos_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_empresas`
--
ALTER TABLE `tab_empresas`
  MODIFY `emp_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_equipamentos`
--
ALTER TABLE `tab_equipamentos`
  MODIFY `equ_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_equipamentos_modelos`
--
ALTER TABLE `tab_equipamentos_modelos`
  MODIFY `eqm_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_estados`
--
ALTER TABLE `tab_estados`
  MODIFY `est_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_estoque`
--
ALTER TABLE `tab_estoque`
  MODIFY `est_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_estoque_alocacoes`
--
ALTER TABLE `tab_estoque_alocacoes`
  MODIFY `esa_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_estoque_comprovantes`
--
ALTER TABLE `tab_estoque_comprovantes`
  MODIFY `esc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_estoque_comprovantes_tipos`
--
ALTER TABLE `tab_estoque_comprovantes_tipos`
  MODIFY `ect_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_estoque_entradas`
--
ALTER TABLE `tab_estoque_entradas`
  MODIFY `ese_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_estoque_fornecedores`
--
ALTER TABLE `tab_estoque_fornecedores`
  MODIFY `esf_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_estoque_grupos`
--
ALTER TABLE `tab_estoque_grupos`
  MODIFY `esg_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_estoque_itens`
--
ALTER TABLE `tab_estoque_itens`
  MODIFY `esi_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_estoque_saidas`
--
ALTER TABLE `tab_estoque_saidas`
  MODIFY `esd_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_estoque_sublocacoes`
--
ALTER TABLE `tab_estoque_sublocacoes`
  MODIFY `ess_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_faturas`
--
ALTER TABLE `tab_faturas`
  MODIFY `fat_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_faturas_filtros`
--
ALTER TABLE `tab_faturas_filtros`
  MODIFY `faf_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_faturas_itens`
--
ALTER TABLE `tab_faturas_itens`
  MODIFY `fai_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_faturas_lotes`
--
ALTER TABLE `tab_faturas_lotes`
  MODIFY `fal_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_faturas_lotes_clientes`
--
ALTER TABLE `tab_faturas_lotes_clientes`
  MODIFY `fcl_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_faturas_lotes_cobrancas`
--
ALTER TABLE `tab_faturas_lotes_cobrancas`
  MODIFY `fco_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_faturas_lotes_comentarios`
--
ALTER TABLE `tab_faturas_lotes_comentarios`
  MODIFY `flc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_faturas_lotes_filtros`
--
ALTER TABLE `tab_faturas_lotes_filtros`
  MODIFY `flf_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_faturas_lotes_status`
--
ALTER TABLE `tab_faturas_lotes_status`
  MODIFY `fls_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_faturas_tipos`
--
ALTER TABLE `tab_faturas_tipos`
  MODIFY `fap_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_formas_pagamento`
--
ALTER TABLE `tab_formas_pagamento`
  MODIFY `fop_id` smallint(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_formas_pagamento_categorias`
--
ALTER TABLE `tab_formas_pagamento_categorias`
  MODIFY `fpc_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_formas_pagamento_formas`
--
ALTER TABLE `tab_formas_pagamento_formas`
  MODIFY `fpf_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_grupos`
--
ALTER TABLE `tab_grupos`
  MODIFY `gru_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_grupos_status`
--
ALTER TABLE `tab_grupos_status`
  MODIFY `grs_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_logradouros`
--
ALTER TABLE `tab_logradouros`
  MODIFY `log_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_mensagens`
--
ALTER TABLE `tab_mensagens`
  MODIFY `men_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_mensagens_comentarios`
--
ALTER TABLE `tab_mensagens_comentarios`
  MODIFY `mec_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_mensagens_comentarios_categorias`
--
ALTER TABLE `tab_mensagens_comentarios_categorias`
  MODIFY `mcc_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_mensagens_envios`
--
ALTER TABLE `tab_mensagens_envios`
  MODIFY `mee_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_mensagens_links`
--
ALTER TABLE `tab_mensagens_links`
  MODIFY `mei_id` mediumint(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_mensagens_lotes`
--
ALTER TABLE `tab_mensagens_lotes`
  MODIFY `mel_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_mensagens_lotes_campos`
--
ALTER TABLE `tab_mensagens_lotes_campos`
  MODIFY `mla_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_mensagens_lotes_comentarios`
--
ALTER TABLE `tab_mensagens_lotes_comentarios`
  MODIFY `mlc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_mensagens_lotes_comentarios_categorias`
--
ALTER TABLE `tab_mensagens_lotes_comentarios_categorias`
  MODIFY `mlo_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_mensagens_lotes_filtros`
--
ALTER TABLE `tab_mensagens_lotes_filtros`
  MODIFY `mli_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_mensagens_lotes_modelos`
--
ALTER TABLE `tab_mensagens_lotes_modelos`
  MODIFY `mlm_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_mensagens_lotes_status`
--
ALTER TABLE `tab_mensagens_lotes_status`
  MODIFY `mls_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_mensagens_lotes_tipos`
--
ALTER TABLE `tab_mensagens_lotes_tipos`
  MODIFY `mlt_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_mensagens_status`
--
ALTER TABLE `tab_mensagens_status`
  MODIFY `mes_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_menus`
--
ALTER TABLE `tab_menus`
  MODIFY `men_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_modulos`
--
ALTER TABLE `tab_modulos`
  MODIFY `mod_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_modulos_status`
--
ALTER TABLE `tab_modulos_status`
  MODIFY `mos_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_operacoes`
--
ALTER TABLE `tab_operacoes`
  MODIFY `ope_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_operacoes_contatos`
--
ALTER TABLE `tab_operacoes_contatos`
  MODIFY `opc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_operacoes_contatos_categorias`
--
ALTER TABLE `tab_operacoes_contatos_categorias`
  MODIFY `occ_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_operacoes_lotes`
--
ALTER TABLE `tab_operacoes_lotes`
  MODIFY `opl_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_operacoes_lotes_filtros`
--
ALTER TABLE `tab_operacoes_lotes_filtros`
  MODIFY `olf_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_operacoes_lotes_periodos`
--
ALTER TABLE `tab_operacoes_lotes_periodos`
  MODIFY `olp_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_operacoes_lotes_responsabilidades`
--
ALTER TABLE `tab_operacoes_lotes_responsabilidades`
  MODIFY `olr_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_operacoes_lotes_sistemas`
--
ALTER TABLE `tab_operacoes_lotes_sistemas`
  MODIFY `oli_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_operacoes_lotes_status`
--
ALTER TABLE `tab_operacoes_lotes_status`
  MODIFY `ols_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_operacoes_scripts`
--
ALTER TABLE `tab_operacoes_scripts`
  MODIFY `ops_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_operacoes_scripts_formatos`
--
ALTER TABLE `tab_operacoes_scripts_formatos`
  MODIFY `osf_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_operacoes_scripts_status`
--
ALTER TABLE `tab_operacoes_scripts_status`
  MODIFY `osa_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_operacoes_scripts_tipos`
--
ALTER TABLE `tab_operacoes_scripts_tipos`
  MODIFY `ost_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_organizacao_funcoes`
--
ALTER TABLE `tab_organizacao_funcoes`
  MODIFY `orf_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_organizacao_funcoes_status`
--
ALTER TABLE `tab_organizacao_funcoes_status`
  MODIFY `ofs_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_organizacao_setores`
--
ALTER TABLE `tab_organizacao_setores`
  MODIFY `ors_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_organizacao_setores_status`
--
ALTER TABLE `tab_organizacao_setores_status`
  MODIFY `oss_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_pesquisas`
--
ALTER TABLE `tab_pesquisas`
  MODIFY `pes_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_pesquisas_aplicacoes`
--
ALTER TABLE `tab_pesquisas_aplicacoes`
  MODIFY `pea_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_pesquisas_aplicacoes_respostas`
--
ALTER TABLE `tab_pesquisas_aplicacoes_respostas`
  MODIFY `par_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_pesquisas_questoes`
--
ALTER TABLE `tab_pesquisas_questoes`
  MODIFY `peq_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_pesquisas_questoes_alternativas`
--
ALTER TABLE `tab_pesquisas_questoes_alternativas`
  MODIFY `pqa_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_pesquisas_questoes_tipos`
--
ALTER TABLE `tab_pesquisas_questoes_tipos`
  MODIFY `pqt_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_pesquisas_status`
--
ALTER TABLE `tab_pesquisas_status`
  MODIFY `pet_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_pesquisas_variaveis_tipos`
--
ALTER TABLE `tab_pesquisas_variaveis_tipos`
  MODIFY `pvt_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_placares`
--
ALTER TABLE `tab_placares`
  MODIFY `plc_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_placares_abas`
--
ALTER TABLE `tab_placares_abas`
  MODIFY `plb_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_planos`
--
ALTER TABLE `tab_planos`
  MODIFY `pla_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_planos_equipamentos`
--
ALTER TABLE `tab_planos_equipamentos`
  MODIFY `ple_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_planos_servicos`
--
ALTER TABLE `tab_planos_servicos`
  MODIFY `pls_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_planos_sistemas`
--
ALTER TABLE `tab_planos_sistemas`
  MODIFY `pli_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_planos_status`
--
ALTER TABLE `tab_planos_status`
  MODIFY `plu_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_planos_tipos`
--
ALTER TABLE `tab_planos_tipos`
  MODIFY `plt_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_planos_vinculos`
--
ALTER TABLE `tab_planos_vinculos`
  MODIFY `plv_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_planos_vinculos_aplicacoes`
--
ALTER TABLE `tab_planos_vinculos_aplicacoes`
  MODIFY `pva_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_planos_vinculos_tipos`
--
ALTER TABLE `tab_planos_vinculos_tipos`
  MODIFY `pvt_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_recuperacao_contatos`
--
ALTER TABLE `tab_recuperacao_contatos`
  MODIFY `rec_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_recuperacao_contatos_tipos`
--
ALTER TABLE `tab_recuperacao_contatos_tipos`
  MODIFY `rct_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_redes`
--
ALTER TABLE `tab_redes`
  MODIFY `red_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servicos_ajustes`
--
ALTER TABLE `tab_servicos_ajustes`
  MODIFY `saj_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servicos_ajustes_categorias`
--
ALTER TABLE `tab_servicos_ajustes_categorias`
  MODIFY `sac_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servicos_ajustes_comentarios`
--
ALTER TABLE `tab_servicos_ajustes_comentarios`
  MODIFY `sam_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servicos_ajustes_regras`
--
ALTER TABLE `tab_servicos_ajustes_regras`
  MODIFY `sar_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servicos_ajustes_status`
--
ALTER TABLE `tab_servicos_ajustes_status`
  MODIFY `sas_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servicos_ajustes_tipos`
--
ALTER TABLE `tab_servicos_ajustes_tipos`
  MODIFY `sat_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores`
--
ALTER TABLE `tab_servidores`
  MODIFY `ser_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_interfaces`
--
ALTER TABLE `tab_servidores_interfaces`
  MODIFY `sei_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_interfaces_padroes`
--
ALTER TABLE `tab_servidores_interfaces_padroes`
  MODIFY `sip_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_interfaces_status`
--
ALTER TABLE `tab_servidores_interfaces_status`
  MODIFY `sia_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_interfaces_tipos`
--
ALTER TABLE `tab_servidores_interfaces_tipos`
  MODIFY `sit_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_links`
--
ALTER TABLE `tab_servidores_links`
  MODIFY `sel_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_links_status`
--
ALTER TABLE `tab_servidores_links_status`
  MODIFY `sls_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_servicos`
--
ALTER TABLE `tab_servidores_servicos`
  MODIFY `ses_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_servicos_status`
--
ALTER TABLE `tab_servidores_servicos_status`
  MODIFY `sss_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_servicos_tipos`
--
ALTER TABLE `tab_servidores_servicos_tipos`
  MODIFY `sst_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_sistemas_operacionais`
--
ALTER TABLE `tab_servidores_sistemas_operacionais`
  MODIFY `sso_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_situacoes`
--
ALTER TABLE `tab_servidores_situacoes`
  MODIFY `seu_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_ssids`
--
ALTER TABLE `tab_servidores_ssids`
  MODIFY `ssi_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_ssids_tipos`
--
ALTER TABLE `tab_servidores_ssids_tipos`
  MODIFY `sst_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_status`
--
ALTER TABLE `tab_servidores_status`
  MODIFY `set_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_servidores_tipos`
--
ALTER TABLE `tab_servidores_tipos`
  MODIFY `sep_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_tarefas`
--
ALTER TABLE `tab_tarefas`
  MODIFY `tar_id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_tarefas_etapas`
--
ALTER TABLE `tab_tarefas_etapas`
  MODIFY `tae_id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_tarefas_etapas_status`
--
ALTER TABLE `tab_tarefas_etapas_status`
  MODIFY `tes_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_tarefas_etapas_tipos`
--
ALTER TABLE `tab_tarefas_etapas_tipos`
  MODIFY `tet_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_tarefas_status`
--
ALTER TABLE `tab_tarefas_status`
  MODIFY `tas_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_tarefas_tipos`
--
ALTER TABLE `tab_tarefas_tipos`
  MODIFY `tat_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_usuarios`
--
ALTER TABLE `tab_usuarios`
  MODIFY `usu_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_usuarios_status`
--
ALTER TABLE `tab_usuarios_status`
  MODIFY `uss_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_vendas`
--
ALTER TABLE `tab_vendas`
  MODIFY `ven_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_vendas_comentarios`
--
ALTER TABLE `tab_vendas_comentarios`
  MODIFY `veo_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_vendas_comissoes`
--
ALTER TABLE `tab_vendas_comissoes`
  MODIFY `vec_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_vendas_comissoes_comentarios`
--
ALTER TABLE `tab_vendas_comissoes_comentarios`
  MODIFY `vco_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_vendas_comissoes_tipos`
--
ALTER TABLE `tab_vendas_comissoes_tipos`
  MODIFY `vct_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_visitas`
--
ALTER TABLE `tab_visitas`
  MODIFY `vis_id` mediumint(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_visitas_comentarios`
--
ALTER TABLE `tab_visitas_comentarios`
  MODIFY `vic_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_visitas_comentarios_categorias`
--
ALTER TABLE `tab_visitas_comentarios_categorias`
  MODIFY `vcc_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_visitas_cotas`
--
ALTER TABLE `tab_visitas_cotas`
  MODIFY `vic_id` smallint(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_visitas_periodos`
--
ALTER TABLE `tab_visitas_periodos`
  MODIFY `vip_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_visitas_status`
--
ALTER TABLE `tab_visitas_status`
  MODIFY `via_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tab_visitas_tipos`
--
ALTER TABLE `tab_visitas_tipos`
  MODIFY `vit_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `rel_ass_cor_estacontido`
--
ALTER TABLE `rel_ass_cor_estacontido`
  ADD CONSTRAINT `rel_ass_cor_estacontido_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `rel_ass_cor_estacontido_cor_id` FOREIGN KEY (`cor_id`) REFERENCES `tab_contratos` (`cor_id`);

--
-- Limitadores para a tabela `rel_cam_cac_contem`
--
ALTER TABLE `rel_cam_cac_contem`
  ADD CONSTRAINT `rel_cam_cac_contem_cac_id` FOREIGN KEY (`cac_id`) REFERENCES `tab_campanhas_contatos` (`cac_id`),
  ADD CONSTRAINT `rel_cam_cac_contem_cam_id` FOREIGN KEY (`cam_id`) REFERENCES `tab_campanhas` (`cam_id`);

--
-- Limitadores para a tabela `rel_cha_usu_ehacompanhado`
--
ALTER TABLE `rel_cha_usu_ehacompanhado`
  ADD CONSTRAINT `rel_cha_usu_ehacompanhado_cha_id` FOREIGN KEY (`cha_id`) REFERENCES `tab_chamados` (`cha_id`),
  ADD CONSTRAINT `rel_cha_usu_ehacompanhado_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `rel_cha_usu_ehfavorito`
--
ALTER TABLE `rel_cha_usu_ehfavorito`
  ADD CONSTRAINT `rel_cha_usu_ehfavorito_cha_id` FOREIGN KEY (`cha_id`) REFERENCES `tab_chamados` (`cha_id`),
  ADD CONSTRAINT `rel_cha_usu_ehfavorito_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `rel_clo_cid_contem`
--
ALTER TABLE `rel_clo_cid_contem`
  ADD CONSTRAINT `rel_clo_cid_contem_cid_id` FOREIGN KEY (`cid_id`) REFERENCES `tab_cidades` (`cid_id`),
  ADD CONSTRAINT `rel_clo_cid_contem_clo_id` FOREIGN KEY (`clo_id`) REFERENCES `tab_clientes_origens` (`clo_id`);

--
-- Limitadores para a tabela `rel_coa_coe_estacontido`
--
ALTER TABLE `rel_coa_coe_estacontido`
  ADD CONSTRAINT `rel_coa_coe_estacontido_coa_id` FOREIGN KEY (`coa_id`) REFERENCES `tab_cobrancas` (`coa_id`),
  ADD CONSTRAINT `rel_coa_coe_estacontido_coe_id` FOREIGN KEY (`coe_id`) REFERENCES `tab_cobrancas_retornos` (`coe_id`);

--
-- Limitadores para a tabela `rel_cof_clo_disponivel`
--
ALTER TABLE `rel_cof_clo_disponivel`
  ADD CONSTRAINT `rel_cof_clo_disponivel_clo_id` FOREIGN KEY (`clo_id`) REFERENCES `tab_clientes_origens` (`clo_id`),
  ADD CONSTRAINT `rel_cof_clo_disponivel_cof_id` FOREIGN KEY (`cof_id`) REFERENCES `tab_cobrancas_formatos` (`cof_id`);

--
-- Limitadores para a tabela `rel_con_pla_tempermissao`
--
ALTER TABLE `rel_con_pla_tempermissao`
  ADD CONSTRAINT `rel_con_pla_tempermissao_con_id` FOREIGN KEY (`con_id`) REFERENCES `tab_condominios` (`con_id`),
  ADD CONSTRAINT `rel_con_pla_tempermissao_pla_id` FOREIGN KEY (`pla_id`) REFERENCES `tab_planos` (`pla_id`);

--
-- Limitadores para a tabela `rel_cov_cod_estacontido`
--
ALTER TABLE `rel_cov_cod_estacontido`
  ADD CONSTRAINT `rel_cov_cod_estacontido_cod_id` FOREIGN KEY (`cod_id`) REFERENCES `tab_condominios_responsabilidades` (`cod_id`),
  ADD CONSTRAINT `rel_cov_cod_estacontido_cov_id` FOREIGN KEY (`cov_id`) REFERENCES `tab_condominios_responsabilidades_atividades` (`cov_id`);

--
-- Limitadores para a tabela `rel_dec_cor_estacontido`
--
ALTER TABLE `rel_dec_cor_estacontido`
  ADD CONSTRAINT `rel_dec_cor_estacontido_cor_id` FOREIGN KEY (`cor_id`) REFERENCES `tab_contratos` (`cor_id`),
  ADD CONSTRAINT `rel_dec_cor_estacontido_dec_id` FOREIGN KEY (`dec_id`) REFERENCES `tab_descontos_concessoes` (`dec_id`);

--
-- Limitadores para a tabela `rel_equ_cor_comodato`
--
ALTER TABLE `rel_equ_cor_comodato`
  ADD CONSTRAINT `rel_equ_cor_comodato_cor_id` FOREIGN KEY (`cor_id`) REFERENCES `tab_contratos` (`cor_id`),
  ADD CONSTRAINT `rel_equ_cor_comodato_equ_id` FOREIGN KEY (`equ_id`) REFERENCES `tab_equipamentos` (`equ_id`);

--
-- Limitadores para a tabela `rel_equ_pla_comodato`
--
ALTER TABLE `rel_equ_pla_comodato`
  ADD CONSTRAINT `rel_equ_pla_comodato_equ_id` FOREIGN KEY (`equ_id`) REFERENCES `tab_equipamentos` (`equ_id`),
  ADD CONSTRAINT `rel_equ_pla_comodato_pla_id` FOREIGN KEY (`pla_id`) REFERENCES `tab_planos` (`pla_id`);

--
-- Limitadores para a tabela `rel_equ_pls_default`
--
ALTER TABLE `rel_equ_pls_default`
  ADD CONSTRAINT `rel_equ_pls_default_equ_id` FOREIGN KEY (`equ_id`) REFERENCES `tab_equipamentos` (`equ_id`),
  ADD CONSTRAINT `rel_equ_pls_default_pls_id` FOREIGN KEY (`pls_id`) REFERENCES `tab_planos_servicos` (`pls_id`);

--
-- Limitadores para a tabela `rel_equ_pls_disponivel`
--
ALTER TABLE `rel_equ_pls_disponivel`
  ADD CONSTRAINT `rel_equ_pls_disponivel_equ_id` FOREIGN KEY (`equ_id`) REFERENCES `tab_equipamentos` (`equ_id`),
  ADD CONSTRAINT `rel_equ_pls_disponivel_pls_id` FOREIGN KEY (`pls_id`) REFERENCES `tab_planos_servicos` (`pls_id`);

--
-- Limitadores para a tabela `rel_esf_esi_fornece`
--
ALTER TABLE `rel_esf_esi_fornece`
  ADD CONSTRAINT `rel_esf_esi_fornece_esf_id` FOREIGN KEY (`esf_id`) REFERENCES `tab_estoque_fornecedores` (`esf_id`),
  ADD CONSTRAINT `rel_esf_esi_fornece_esi_id` FOREIGN KEY (`esi_id`) REFERENCES `tab_estoque_itens` (`esi_id`);

--
-- Limitadores para a tabela `rel_fop_cor_estacontido`
--
ALTER TABLE `rel_fop_cor_estacontido`
  ADD CONSTRAINT `rel_fop_cor_estacontido_cor_id` FOREIGN KEY (`cor_id`) REFERENCES `tab_contratos` (`cor_id`),
  ADD CONSTRAINT `rel_fop_cor_estacontido_fop_id` FOREIGN KEY (`fop_id`) REFERENCES `tab_formas_pagamento` (`fop_id`);

--
-- Limitadores para a tabela `rel_gru_aco_tempermissao`
--
ALTER TABLE `rel_gru_aco_tempermissao`
  ADD CONSTRAINT `rel_gru_aco_tempermissao_aco_id` FOREIGN KEY (`aco_id`) REFERENCES `tab_acoes` (`aco_id`),
  ADD CONSTRAINT `rel_gru_aco_tempermissao_gru_id` FOREIGN KEY (`gru_id`) REFERENCES `tab_grupos` (`gru_id`);

--
-- Limitadores para a tabela `rel_saj_ass_contem`
--
ALTER TABLE `rel_saj_ass_contem`
  ADD CONSTRAINT `rel_saj_ass_contem_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `rel_saj_ass_contem_saj_id` FOREIGN KEY (`saj_id`) REFERENCES `tab_servicos_ajustes` (`saj_id`);

--
-- Limitadores para a tabela `rel_saj_cau_contem`
--
ALTER TABLE `rel_saj_cau_contem`
  ADD CONSTRAINT `rel_saj_cau_contem_saj_id` FOREIGN KEY (`saj_id`) REFERENCES `tab_servicos_ajustes` (`saj_id`);

--
-- Limitadores para a tabela `rel_saj_cip_contem`
--
ALTER TABLE `rel_saj_cip_contem`
  ADD CONSTRAINT `rel_saj_sip_contem_cip_id` FOREIGN KEY (`cip_id`) REFERENCES `tab_clientes_iptv` (`cip_id`),
  ADD CONSTRAINT `rel_saj_sip_contem_saj_id` FOREIGN KEY (`saj_id`) REFERENCES `tab_servicos_ajustes` (`saj_id`);

--
-- Limitadores para a tabela `rel_saj_clb_contem`
--
ALTER TABLE `rel_saj_clb_contem`
  ADD CONSTRAINT `rel_saj_clb_contem_clb_id` FOREIGN KEY (`clb_id`) REFERENCES `tab_clientes_basesdedados` (`clb_id`),
  ADD CONSTRAINT `rel_saj_clb_contem_saj_id` FOREIGN KEY (`saj_id`) REFERENCES `tab_servicos_ajustes` (`saj_id`);

--
-- Limitadores para a tabela `rel_saj_cle_contem`
--
ALTER TABLE `rel_saj_cle_contem`
  ADD CONSTRAINT `rel_saj_cle_contem_cle_id` FOREIGN KEY (`cle_id`) REFERENCES `tab_clientes_emails` (`cle_id`),
  ADD CONSTRAINT `rel_saj_cle_contem_saj_id` FOREIGN KEY (`saj_id`) REFERENCES `tab_servicos_ajustes` (`saj_id`);

--
-- Limitadores para a tabela `rel_saj_clh_contem`
--
ALTER TABLE `rel_saj_clh_contem`
  ADD CONSTRAINT `rel_saj_clh_contem_clh_id` FOREIGN KEY (`clh_id`) REFERENCES `tab_clientes_hospedagens` (`clh_id`),
  ADD CONSTRAINT `rel_saj_clh_contem_saj_id` FOREIGN KEY (`saj_id`) REFERENCES `tab_servicos_ajustes` (`saj_id`);

--
-- Limitadores para a tabela `rel_saj_clp_contem`
--
ALTER TABLE `rel_saj_clp_contem`
  ADD CONSTRAINT `rel_saj_clp_contem_clp_id` FOREIGN KEY (`clp_id`) REFERENCES `tab_clientes_pontos` (`clp_id`),
  ADD CONSTRAINT `rel_saj_clp_contem_saj_id` FOREIGN KEY (`saj_id`) REFERENCES `tab_servicos_ajustes` (`saj_id`);

--
-- Limitadores para a tabela `rel_tar_usu_aguardaciencia`
--
ALTER TABLE `rel_tar_usu_aguardaciencia`
  ADD CONSTRAINT `rel_tar_usu_aguardaciencia_tar_id` FOREIGN KEY (`tar_id`) REFERENCES `tab_tarefas` (`tar_id`),
  ADD CONSTRAINT `rel_tar_usu_aguardaciencia_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `rel_tat_ors_orf_ehautoaprovada`
--
ALTER TABLE `rel_tat_ors_orf_ehautoaprovada`
  ADD CONSTRAINT `rel_tat_ors_orf_ehautoaprovada_orf_id` FOREIGN KEY (`orf_id`) REFERENCES `tab_organizacao_funcoes` (`orf_id`),
  ADD CONSTRAINT `rel_tat_ors_orf_ehautoaprovada_ors_id` FOREIGN KEY (`ors_id`) REFERENCES `tab_organizacao_setores` (`ors_id`),
  ADD CONSTRAINT `rel_tat_ors_orf_ehautoaprovada_tat_id` FOREIGN KEY (`tat_id`) REFERENCES `tab_tarefas_tipos` (`tat_id`);

--
-- Limitadores para a tabela `rel_usu_esg_fazparte`
--
ALTER TABLE `rel_usu_esg_fazparte`
  ADD CONSTRAINT `rel_usu_esg_fazparte_esg_id` FOREIGN KEY (`esg_id`) REFERENCES `tab_estoque_grupos` (`esg_id`),
  ADD CONSTRAINT `rel_usu_esg_fazparte_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `rel_usu_gru_fazparte`
--
ALTER TABLE `rel_usu_gru_fazparte`
  ADD CONSTRAINT `rel_usu_gru_fazparte_gru_id` FOREIGN KEY (`gru_id`) REFERENCES `tab_grupos` (`gru_id`),
  ADD CONSTRAINT `rel_usu_gru_fazparte_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `rel_usu_orf_ors_exerce`
--
ALTER TABLE `rel_usu_orf_ors_exerce`
  ADD CONSTRAINT `rel_usu_orf_ors_exerce_orf_id` FOREIGN KEY (`orf_id`) REFERENCES `tab_organizacao_funcoes` (`orf_id`),
  ADD CONSTRAINT `rel_usu_orf_ors_exerce_ors_id` FOREIGN KEY (`ors_id`) REFERENCES `tab_organizacao_setores` (`ors_id`),
  ADD CONSTRAINT `rel_usu_orf_ors_exerce_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_acoes`
--
ALTER TABLE `tab_acoes`
  ADD CONSTRAINT `tab_acoes_act_id` FOREIGN KEY (`act_id`) REFERENCES `tab_acoes_tipos` (`act_id`),
  ADD CONSTRAINT `tab_acoes_mod_id` FOREIGN KEY (`mod_id`) REFERENCES `tab_modulos` (`mod_id`);

--
-- Limitadores para a tabela `tab_ajustes_financeiros`
--
ALTER TABLE `tab_ajustes_financeiros`
  ADD CONSTRAINT `tab_ajustes_financeiros_afc_id` FOREIGN KEY (`afc_id`) REFERENCES `tab_ajustes_financeiros_categorias` (`afc_id`),
  ADD CONSTRAINT `tab_ajustes_financeiros_afs_id` FOREIGN KEY (`afs_id`) REFERENCES `tab_ajustes_financeiros_status` (`afs_id`),
  ADD CONSTRAINT `tab_ajustes_financeiros_aft_id` FOREIGN KEY (`aft_id`) REFERENCES `tab_ajustes_financeiros_tipos` (`aft_id`),
  ADD CONSTRAINT `tab_ajustes_financeiros_ajf_pai` FOREIGN KEY (`ajf_pai`) REFERENCES `tab_ajustes_financeiros` (`ajf_id`),
  ADD CONSTRAINT `tab_ajustes_financeiros_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_ajustes_financeiros_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_ajustes_financeiros_con_id` FOREIGN KEY (`con_id`) REFERENCES `tab_condominios` (`con_id`),
  ADD CONSTRAINT `tab_ajustes_financeiros_cor_id` FOREIGN KEY (`cor_id`) REFERENCES `tab_contratos` (`cor_id`);

--
-- Limitadores para a tabela `tab_ajustes_financeiros_comentarios`
--
ALTER TABLE `tab_ajustes_financeiros_comentarios`
  ADD CONSTRAINT `tab_ajustes_financeiros_comentarios_ajf_id` FOREIGN KEY (`ajf_id`) REFERENCES `tab_ajustes_financeiros` (`ajf_id`),
  ADD CONSTRAINT `tab_ajustes_financeiros_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_assinaturas`
--
ALTER TABLE `tab_assinaturas`
  ADD CONSTRAINT `tab_assinaturas_ass_migrada` FOREIGN KEY (`ass_migrada`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_assinaturas_ass_pai` FOREIGN KEY (`ass_pai`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_assinaturas_ast_id` FOREIGN KEY (`ast_id`) REFERENCES `tab_assinaturas_status` (`ast_id`),
  ADD CONSTRAINT `tab_assinaturas_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_assinaturas_pla_id` FOREIGN KEY (`pla_id`) REFERENCES `tab_planos` (`pla_id`),
  ADD CONSTRAINT `tab_assinaturas_pli_id` FOREIGN KEY (`pli_id`) REFERENCES `tab_planos_sistemas` (`pli_id`);

--
-- Limitadores para a tabela `tab_assinaturas_vinculos`
--
ALTER TABLE `tab_assinaturas_vinculos`
  ADD CONSTRAINT `tab_assinaturas_vinculos_ass_filha` FOREIGN KEY (`ass_filha`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_assinaturas_vinculos_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_assinaturas_vinculos_pvt_id` FOREIGN KEY (`pvt_id`) REFERENCES `tab_planos_vinculos_tipos` (`pvt_id`);

--
-- Limitadores para a tabela `tab_atividades`
--
ALTER TABLE `tab_atividades`
  ADD CONSTRAINT `tab_atividades_aco_id` FOREIGN KEY (`aco_id`) REFERENCES `tab_acoes` (`aco_id`),
  ADD CONSTRAINT `tab_atividades_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_bairros`
--
ALTER TABLE `tab_bairros`
  ADD CONSTRAINT `tab_bairros_cid_id` FOREIGN KEY (`cid_id`) REFERENCES `tab_cidades` (`cid_id`);

--
-- Limitadores para a tabela `tab_campanhas`
--
ALTER TABLE `tab_campanhas`
  ADD CONSTRAINT `tab_campanhas_cas_id` FOREIGN KEY (`cas_id`) REFERENCES `tab_campanhas_status` (`cas_id`),
  ADD CONSTRAINT `tab_campanhas_cat_id` FOREIGN KEY (`cat_id`) REFERENCES `tab_campanhas_tipos` (`cat_id`);

--
-- Limitadores para a tabela `tab_campanhas_contatos`
--
ALTER TABLE `tab_campanhas_contatos`
  ADD CONSTRAINT `tab_campanhas_contatos_bai_id` FOREIGN KEY (`bai_id`) REFERENCES `tab_bairros` (`bai_id`),
  ADD CONSTRAINT `tab_campanhas_contatos_ccs_id` FOREIGN KEY (`ccs_id`) REFERENCES `tab_campanhas_contatos_status` (`ccs_id`),
  ADD CONSTRAINT `tab_campanhas_contatos_cep_id` FOREIGN KEY (`cep_id`) REFERENCES `tab_ceps` (`cep_id`),
  ADD CONSTRAINT `tab_campanhas_contatos_cid_id` FOREIGN KEY (`cid_id`) REFERENCES `tab_cidades` (`cid_id`),
  ADD CONSTRAINT `tab_campanhas_contatos_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_campanhas_contatos_est_id` FOREIGN KEY (`est_id`) REFERENCES `tab_estados` (`est_id`),
  ADD CONSTRAINT `tab_campanhas_contatos_log_id` FOREIGN KEY (`log_id`) REFERENCES `tab_logradouros` (`log_id`);

--
-- Limitadores para a tabela `tab_campanhas_contatos_telefones`
--
ALTER TABLE `tab_campanhas_contatos_telefones`
  ADD CONSTRAINT `tab_campanhas_contatos_telefones_cac_id` FOREIGN KEY (`cac_id`) REFERENCES `tab_campanhas_contatos` (`cac_id`);

--
-- Limitadores para a tabela `tab_campanhas_operacoes`
--
ALTER TABLE `tab_campanhas_operacoes`
  ADD CONSTRAINT `tab_campanhas_operacoes_cam_id` FOREIGN KEY (`cam_id`) REFERENCES `tab_campanhas` (`cam_id`),
  ADD CONSTRAINT `tab_campanhas_operacoes_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_campanhas_operacoes_contatos`
--
ALTER TABLE `tab_campanhas_operacoes_contatos`
  ADD CONSTRAINT `tab_campanhas_operacoes_contatos_cac_id` FOREIGN KEY (`cac_id`) REFERENCES `tab_campanhas_contatos` (`cac_id`),
  ADD CONSTRAINT `tab_campanhas_operacoes_contatos_cao_id` FOREIGN KEY (`cao_id`) REFERENCES `tab_campanhas_operacoes` (`cao_id`),
  ADD CONSTRAINT `tab_campanhas_operacoes_contatos_cct_id` FOREIGN KEY (`cct_id`) REFERENCES `tab_campanhas_contatos_telefones` (`cct_id`),
  ADD CONSTRAINT `tab_campanhas_operacoes_contatos_cos_id` FOREIGN KEY (`cos_id`) REFERENCES `tab_campanhas_operacoes_contatos_situacoes` (`cos_id`),
  ADD CONSTRAINT `tab_campanhas_operacoes_contatos_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_cancelamentos`
--
ALTER TABLE `tab_cancelamentos`
  ADD CONSTRAINT `tab_cancelamentos_cae_id` FOREIGN KEY (`cae_id`) REFERENCES `tab_cancelamentos_etapas` (`cae_id`),
  ADD CONSTRAINT `tab_cancelamentos_cam_id` FOREIGN KEY (`cam_id`) REFERENCES `tab_cancelamentos_motivos` (`cam_id`),
  ADD CONSTRAINT `tab_cancelamentos_cao_id` FOREIGN KEY (`cao_id`) REFERENCES `tab_cancelamentos_origens` (`cao_id`),
  ADD CONSTRAINT `tab_cancelamentos_cas_id` FOREIGN KEY (`cas_id`) REFERENCES `tab_cancelamentos_status` (`cas_id`),
  ADD CONSTRAINT `tab_cancelamentos_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_cancelamentos_cor_id` FOREIGN KEY (`cor_id`) REFERENCES `tab_contratos` (`cor_id`);

--
-- Limitadores para a tabela `tab_cancelamentos_etapas`
--
ALTER TABLE `tab_cancelamentos_etapas`
  ADD CONSTRAINT `tab_cancelamentos_etapas_can_id` FOREIGN KEY (`can_id`) REFERENCES `tab_cancelamentos` (`can_id`),
  ADD CONSTRAINT `tab_cancelamentos_etapas_cen_id` FOREIGN KEY (`cen_id`) REFERENCES `tab_cancelamentos_etapas_niveis` (`cen_id`),
  ADD CONSTRAINT `tab_cancelamentos_etapas_ces_id` FOREIGN KEY (`ces_id`) REFERENCES `tab_cancelamentos_etapas_status` (`ces_id`),
  ADD CONSTRAINT `tab_cancelamentos_etapas_cet_id` FOREIGN KEY (`cet_id`) REFERENCES `tab_cancelamentos_etapas_tipos` (`cet_id`),
  ADD CONSTRAINT `tab_cancelamentos_etapas_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_cancelamentos_motivos`
--
ALTER TABLE `tab_cancelamentos_motivos`
  ADD CONSTRAINT `tab_cancelamentos_motivos_cms_id` FOREIGN KEY (`cms_id`) REFERENCES `tab_cancelamentos_motivos_status` (`cms_id`);

--
-- Limitadores para a tabela `tab_central_telefonica_agregados`
--
ALTER TABLE `tab_central_telefonica_agregados`
  ADD CONSTRAINT `tab_central_telefonica_agregados_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_central_telefonica_ligacoes`
--
ALTER TABLE `tab_central_telefonica_ligacoes`
  ADD CONSTRAINT `tab_central_telefonica_ligacoes_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_central_telefonica_ligacoes_cls_id` FOREIGN KEY (`cls_id`) REFERENCES `tab_central_telefonica_ligacoes_situacoes` (`cls_id`),
  ADD CONSTRAINT `tab_central_telefonica_ligacoes_clt_id` FOREIGN KEY (`clt_id`) REFERENCES `tab_central_telefonica_ligacoes_tipos` (`clt_id`),
  ADD CONSTRAINT `tab_central_telefonica_ligacoes_cts_destino_id` FOREIGN KEY (`cts_destino_id`) REFERENCES `tab_central_telefonica_setores` (`cts_id`),
  ADD CONSTRAINT `tab_central_telefonica_ligacoes_cts_origem_id` FOREIGN KEY (`cts_origem_id`) REFERENCES `tab_central_telefonica_setores` (`cts_id`),
  ADD CONSTRAINT `tab_central_telefonica_ligacoes_usu_destino_id` FOREIGN KEY (`usu_destino_id`) REFERENCES `tab_usuarios` (`usu_id`),
  ADD CONSTRAINT `tab_central_telefonica_ligacoes_usu_origem_id` FOREIGN KEY (`usu_origem_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_ceps`
--
ALTER TABLE `tab_ceps`
  ADD CONSTRAINT `tab_ceps_bai_id` FOREIGN KEY (`bai_id`) REFERENCES `tab_bairros` (`bai_id`),
  ADD CONSTRAINT `tab_ceps_cid_id` FOREIGN KEY (`cid_id`) REFERENCES `tab_cidades` (`cid_id`),
  ADD CONSTRAINT `tab_ceps_est_id` FOREIGN KEY (`est_id`) REFERENCES `tab_estados` (`est_id`),
  ADD CONSTRAINT `tab_ceps_log_id` FOREIGN KEY (`log_id`) REFERENCES `tab_logradouros` (`log_id`);

--
-- Limitadores para a tabela `tab_chamados`
--
ALTER TABLE `tab_chamados`
  ADD CONSTRAINT `tab_chamados_cha_pai` FOREIGN KEY (`cha_pai`) REFERENCES `tab_chamados` (`cha_id`),
  ADD CONSTRAINT `tab_chamados_chc_id` FOREIGN KEY (`chc_id`) REFERENCES `tab_chamados_categorias` (`chc_id`),
  ADD CONSTRAINT `tab_chamados_chi_id` FOREIGN KEY (`chi_id`) REFERENCES `tab_chamados_situacoes` (`chi_id`),
  ADD CONSTRAINT `tab_chamados_chp_id` FOREIGN KEY (`chp_id`) REFERENCES `tab_chamados_prioridades` (`chp_id`),
  ADD CONSTRAINT `tab_chamados_chs_id` FOREIGN KEY (`chs_id`) REFERENCES `tab_chamados_status` (`chs_id`),
  ADD CONSTRAINT `tab_chamados_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_chamados_con_id` FOREIGN KEY (`con_id`) REFERENCES `tab_condominios` (`con_id`),
  ADD CONSTRAINT `tab_chamados_orf_id` FOREIGN KEY (`orf_id`) REFERENCES `tab_organizacao_funcoes` (`orf_id`),
  ADD CONSTRAINT `tab_chamados_ors_id` FOREIGN KEY (`ors_id`) REFERENCES `tab_organizacao_setores` (`ors_id`),
  ADD CONSTRAINT `tab_chamados_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`),
  ADD CONSTRAINT `tab_chamados_vis_id` FOREIGN KEY (`vis_id`) REFERENCES `tab_visitas` (`vis_id`);

--
-- Limitadores para a tabela `tab_chamados_categorias`
--
ALTER TABLE `tab_chamados_categorias`
  ADD CONSTRAINT `tab_chamados_categorias_chc_pai` FOREIGN KEY (`chc_pai`) REFERENCES `tab_chamados_categorias` (`chc_id`),
  ADD CONSTRAINT `tab_chamados_categorias_vit_id` FOREIGN KEY (`vit_id`) REFERENCES `tab_visitas_tipos` (`vit_id`);

--
-- Limitadores para a tabela `tab_chamados_categorias_etapas`
--
ALTER TABLE `tab_chamados_categorias_etapas`
  ADD CONSTRAINT `tab_chamados_categorias_etapas_cet_id` FOREIGN KEY (`cet_id`) REFERENCES `tab_chamados_etapas_tipos` (`cet_id`),
  ADD CONSTRAINT `tab_chamados_categorias_etapas_chc_id` FOREIGN KEY (`chc_id`) REFERENCES `tab_chamados_categorias` (`chc_id`),
  ADD CONSTRAINT `tab_chamados_categorias_etapas_orf_id` FOREIGN KEY (`orf_id`) REFERENCES `tab_organizacao_funcoes` (`orf_id`),
  ADD CONSTRAINT `tab_chamados_categorias_etapas_ors_id` FOREIGN KEY (`ors_id`) REFERENCES `tab_organizacao_setores` (`ors_id`);

--
-- Limitadores para a tabela `tab_chamados_etapas`
--
ALTER TABLE `tab_chamados_etapas`
  ADD CONSTRAINT `tab_chamados_etapas_cen_id` FOREIGN KEY (`cen_id`) REFERENCES `tab_chamados_etapas_niveis_stress` (`cen_id`),
  ADD CONSTRAINT `tab_chamados_etapas_cet_id` FOREIGN KEY (`cet_id`) REFERENCES `tab_chamados_etapas_tipos` (`cet_id`),
  ADD CONSTRAINT `tab_chamados_etapas_cha_id` FOREIGN KEY (`cha_id`) REFERENCES `tab_chamados` (`cha_id`),
  ADD CONSTRAINT `tab_chamados_etapas_che_pai` FOREIGN KEY (`che_pai`) REFERENCES `tab_chamados_etapas` (`che_id`),
  ADD CONSTRAINT `tab_chamados_etapas_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_chamados_roteiros`
--
ALTER TABLE `tab_chamados_roteiros`
  ADD CONSTRAINT `tab_chamados_roteiros_cre_id` FOREIGN KEY (`cre_id`) REFERENCES `tab_chamados_roteiros_etapas` (`cre_id`);

--
-- Limitadores para a tabela `tab_chamados_roteiros_etapas`
--
ALTER TABLE `tab_chamados_roteiros_etapas`
  ADD CONSTRAINT `tab_chamados_roteiros_etapas_chr_id` FOREIGN KEY (`chr_id`) REFERENCES `tab_chamados_roteiros` (`chr_id`),
  ADD CONSTRAINT `tab_chamados_roteiros_etapas_cre_nao_id` FOREIGN KEY (`cre_nao_id`) REFERENCES `tab_chamados_roteiros_etapas` (`cre_id`),
  ADD CONSTRAINT `tab_chamados_roteiros_etapas_cre_sim_id` FOREIGN KEY (`cre_sim_id`) REFERENCES `tab_chamados_roteiros_etapas` (`cre_id`);

--
-- Limitadores para a tabela `tab_chamados_visualizacoes`
--
ALTER TABLE `tab_chamados_visualizacoes`
  ADD CONSTRAINT `tab_chamados_visualizacoes_cha_id` FOREIGN KEY (`cha_id`) REFERENCES `tab_chamados` (`cha_id`),
  ADD CONSTRAINT `tab_chamados_visualizacoes_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_cidades`
--
ALTER TABLE `tab_cidades`
  ADD CONSTRAINT `tab_cidades_clo_id` FOREIGN KEY (`clo_id`) REFERENCES `tab_clientes_origens` (`clo_id`),
  ADD CONSTRAINT `tab_cidades_est_id` FOREIGN KEY (`est_id`) REFERENCES `tab_estados` (`est_id`);

--
-- Limitadores para a tabela `tab_classes_fiscais`
--
ALTER TABLE `tab_classes_fiscais`
  ADD CONSTRAINT `tab_classes_fiscais_afc_id` FOREIGN KEY (`afc_id`) REFERENCES `tab_ajustes_financeiros_categorias` (`afc_id`),
  ADD CONSTRAINT `tab_classes_fiscais_cft_id` FOREIGN KEY (`cft_id`) REFERENCES `tab_classes_fiscais_tipos` (`cft_id`),
  ADD CONSTRAINT `tab_classes_fiscais_pla_id` FOREIGN KEY (`pla_id`) REFERENCES `tab_planos` (`pla_id`),
  ADD CONSTRAINT `tab_classes_fiscais_pls_id` FOREIGN KEY (`pls_id`) REFERENCES `tab_planos_servicos` (`pls_id`);

--
-- Limitadores para a tabela `tab_clientes`
--
ALTER TABLE `tab_clientes`
  ADD CONSTRAINT `tab_clientes_bai_id` FOREIGN KEY (`bai_id`) REFERENCES `tab_bairros` (`bai_id`),
  ADD CONSTRAINT `tab_clientes_cep_id` FOREIGN KEY (`cep_id`) REFERENCES `tab_ceps` (`cep_id`),
  ADD CONSTRAINT `tab_clientes_cid_id` FOREIGN KEY (`cid_id`) REFERENCES `tab_cidades` (`cid_id`),
  ADD CONSTRAINT `tab_clientes_cld_id` FOREIGN KEY (`cld_id`) REFERENCES `tab_clientes_indicacoes` (`cld_id`),
  ADD CONSTRAINT `tab_clientes_cli_pai` FOREIGN KEY (`cli_pai`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_clientes_clo_id` FOREIGN KEY (`clo_id`) REFERENCES `tab_clientes_origens` (`clo_id`),
  ADD CONSTRAINT `tab_clientes_cls_id` FOREIGN KEY (`cls_id`) REFERENCES `tab_clientes_status` (`cls_id`),
  ADD CONSTRAINT `tab_clientes_clt_id` FOREIGN KEY (`clt_id`) REFERENCES `tab_clientes_tipos` (`clt_id`),
  ADD CONSTRAINT `tab_clientes_clu_id` FOREIGN KEY (`clu_id`) REFERENCES `tab_clientes_situacoes` (`clu_id`),
  ADD CONSTRAINT `tab_clientes_cob_id` FOREIGN KEY (`cob_id`) REFERENCES `tab_condominios_blocos` (`cob_id`),
  ADD CONSTRAINT `tab_clientes_cof_id` FOREIGN KEY (`cof_id`) REFERENCES `tab_cobrancas_formatos` (`cof_id`),
  ADD CONSTRAINT `tab_clientes_con_id` FOREIGN KEY (`con_id`) REFERENCES `tab_condominios` (`con_id`),
  ADD CONSTRAINT `tab_clientes_coo_id` FOREIGN KEY (`coo_id`) REFERENCES `tab_clientes_operadoras_origens` (`coo_id`),
  ADD CONSTRAINT `tab_clientes_est_id` FOREIGN KEY (`est_id`) REFERENCES `tab_estados` (`est_id`),
  ADD CONSTRAINT `tab_clientes_log_id` FOREIGN KEY (`log_id`) REFERENCES `tab_logradouros` (`log_id`),
  ADD CONSTRAINT `tab_clientes_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_clientes_autenticacoes`
--
ALTER TABLE `tab_clientes_autenticacoes`
  ADD CONSTRAINT `tab_clientes_autenticacoes_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_clientes_autenticacoes_caa_id` FOREIGN KEY (`caa_id`) REFERENCES `tab_clientes_autenticacoes_autenticadores` (`caa_id`),
  ADD CONSTRAINT `tab_clientes_autenticacoes_cas_id` FOREIGN KEY (`cas_id`) REFERENCES `tab_clientes_autenticacoes_status` (`cas_id`),
  ADD CONSTRAINT `tab_clientes_autenticacoes_cle_id` FOREIGN KEY (`cle_id`) REFERENCES `tab_clientes_emails` (`cle_id`),
  ADD CONSTRAINT `tab_clientes_autenticacoes_sei_id` FOREIGN KEY (`sei_id`) REFERENCES `tab_servidores_interfaces` (`sei_id`),
  ADD CONSTRAINT `tab_clientes_autenticacoes_ser_id` FOREIGN KEY (`ser_id`) REFERENCES `tab_servidores` (`ser_id`),
  ADD CONSTRAINT `tab_clientes_autenticacoes_ssi_id` FOREIGN KEY (`ssi_id`) REFERENCES `tab_servidores_ssids` (`ssi_id`);

--
-- Limitadores para a tabela `tab_clientes_autenticacoes_ajustes`
--
ALTER TABLE `tab_clientes_autenticacoes_ajustes`
  ADD CONSTRAINT `tab_clientes_autenticacoes_ajustes_cau_id` FOREIGN KEY (`cau_id`) REFERENCES `tab_clientes_autenticacoes` (`cau_id`),
  ADD CONSTRAINT `tab_clientes_autenticacoes_ajustes_saj_id` FOREIGN KEY (`saj_id`) REFERENCES `tab_servicos_ajustes` (`saj_id`);

--
-- Limitadores para a tabela `tab_clientes_autenticacoes_comentarios`
--
ALTER TABLE `tab_clientes_autenticacoes_comentarios`
  ADD CONSTRAINT `tab_clientes_autenticacoes_comentarios_cau_id` FOREIGN KEY (`cau_id`) REFERENCES `tab_clientes_autenticacoes` (`cau_id`),
  ADD CONSTRAINT `tab_clientes_autenticacoes_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_clientes_basesdedados`
--
ALTER TABLE `tab_clientes_basesdedados`
  ADD CONSTRAINT `tab_clientes_basesdedados_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_clientes_basesdedados_cbs_id` FOREIGN KEY (`cbs_id`) REFERENCES `tab_clientes_basesdedados_status` (`cbs_id`),
  ADD CONSTRAINT `tab_clientes_basesdedados_cbt_id` FOREIGN KEY (`cbt_id`) REFERENCES `tab_clientes_basesdedados_tipos` (`cbt_id`),
  ADD CONSTRAINT `tab_clientes_basesdedados_ser_id` FOREIGN KEY (`ser_id`) REFERENCES `tab_servidores` (`ser_id`);

--
-- Limitadores para a tabela `tab_clientes_cobranca_dados`
--
ALTER TABLE `tab_clientes_cobranca_dados`
  ADD CONSTRAINT `tab_clientes_cobranca_dados_bai_id` FOREIGN KEY (`bai_id`) REFERENCES `tab_bairros` (`bai_id`),
  ADD CONSTRAINT `tab_clientes_cobranca_dados_cep_id` FOREIGN KEY (`cep_id`) REFERENCES `tab_ceps` (`cep_id`),
  ADD CONSTRAINT `tab_clientes_cobranca_dados_cid_id` FOREIGN KEY (`cid_id`) REFERENCES `tab_cidades` (`cid_id`),
  ADD CONSTRAINT `tab_clientes_cobranca_dados_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_clientes_cobranca_dados_cob_id` FOREIGN KEY (`cob_id`) REFERENCES `tab_condominios_blocos` (`cob_id`),
  ADD CONSTRAINT `tab_clientes_cobranca_dados_con_id` FOREIGN KEY (`con_id`) REFERENCES `tab_condominios` (`con_id`),
  ADD CONSTRAINT `tab_clientes_cobranca_dados_est_id` FOREIGN KEY (`est_id`) REFERENCES `tab_estados` (`est_id`),
  ADD CONSTRAINT `tab_clientes_cobranca_dados_log_id` FOREIGN KEY (`log_id`) REFERENCES `tab_logradouros` (`log_id`);

--
-- Limitadores para a tabela `tab_clientes_comodatos`
--
ALTER TABLE `tab_clientes_comodatos`
  ADD CONSTRAINT `tab_clientes_comodatos_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_clientes_comodatos_ccs_id` FOREIGN KEY (`ccs_id`) REFERENCES `tab_clientes_comodatos_status` (`ccs_id`),
  ADD CONSTRAINT `tab_clientes_comodatos_clc_id` FOREIGN KEY (`clc_substituto_id`) REFERENCES `tab_clientes_comodatos` (`clc_id`),
  ADD CONSTRAINT `tab_clientes_comodatos_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_clientes_comodatos_cor_id` FOREIGN KEY (`cor_id`) REFERENCES `tab_contratos` (`cor_id`),
  ADD CONSTRAINT `tab_clientes_comodatos_eqm_id` FOREIGN KEY (`eqm_id`) REFERENCES `tab_equipamentos_modelos` (`eqm_id`),
  ADD CONSTRAINT `tab_clientes_comodatos_equ_id` FOREIGN KEY (`equ_id`) REFERENCES `tab_equipamentos` (`equ_id`);

--
-- Limitadores para a tabela `tab_clientes_comodatos_comentarios`
--
ALTER TABLE `tab_clientes_comodatos_comentarios`
  ADD CONSTRAINT `tab_clientes_comodatos_comentarios_clc_id` FOREIGN KEY (`clc_id`) REFERENCES `tab_clientes_comodatos` (`clc_id`),
  ADD CONSTRAINT `tab_clientes_comodatos_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_clientes_consultas_credito`
--
ALTER TABLE `tab_clientes_consultas_credito`
  ADD CONSTRAINT `tab_clientes_consultas_credito_ccs_id` FOREIGN KEY (`ccs_id`) REFERENCES `tab_clientes_consultas_credito_situacoes` (`ccs_id`),
  ADD CONSTRAINT `tab_clientes_consultas_credito_cct_id` FOREIGN KEY (`cct_id`) REFERENCES `tab_clientes_consultas_credito_tipos` (`cct_id`),
  ADD CONSTRAINT `tab_clientes_consultas_credito_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_clientes_consultas_credito_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_clientes_contatos_emails`
--
ALTER TABLE `tab_clientes_contatos_emails`
  ADD CONSTRAINT `tab_clientes_contatos_emails_cca_id` FOREIGN KEY (`cca_id`) REFERENCES `tab_clientes_contatos_emails_tipos` (`cca_id`),
  ADD CONSTRAINT `tab_clientes_contatos_emails_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`);

--
-- Limitadores para a tabela `tab_clientes_contatos_telefones`
--
ALTER TABLE `tab_clientes_contatos_telefones`
  ADD CONSTRAINT `tab_clientes_contatos_telefones_ccf_id` FOREIGN KEY (`ccf_id`) REFERENCES `tab_clientes_contatos_telefones_tipos` (`ccf_id`),
  ADD CONSTRAINT `tab_clientes_contatos_telefones_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`);

--
-- Limitadores para a tabela `tab_clientes_emails`
--
ALTER TABLE `tab_clientes_emails`
  ADD CONSTRAINT `tab_clientes_emails_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_clientes_emails_ces_id` FOREIGN KEY (`ces_id`) REFERENCES `tab_clientes_emails_status` (`ces_id`),
  ADD CONSTRAINT `tab_clientes_emails_cet_id` FOREIGN KEY (`cet_id`) REFERENCES `tab_clientes_emails_tipos` (`cet_id`),
  ADD CONSTRAINT `tab_clientes_emails_dom_id` FOREIGN KEY (`dom_id`) REFERENCES `tab_dominios` (`dom_id`);

--
-- Limitadores para a tabela `tab_clientes_emails_ajustes`
--
ALTER TABLE `tab_clientes_emails_ajustes`
  ADD CONSTRAINT `tab_clientes_emails_ajustes_cle_id` FOREIGN KEY (`cle_id`) REFERENCES `tab_clientes_emails` (`cle_id`),
  ADD CONSTRAINT `tab_clientes_emails_ajustes_saj_id` FOREIGN KEY (`saj_id`) REFERENCES `tab_servicos_ajustes` (`saj_id`);

--
-- Limitadores para a tabela `tab_clientes_emails_comentarios`
--
ALTER TABLE `tab_clientes_emails_comentarios`
  ADD CONSTRAINT `tab_clientes_emails_comentarios_cle_id` FOREIGN KEY (`cle_id`) REFERENCES `tab_clientes_emails` (`cle_id`),
  ADD CONSTRAINT `tab_clientes_emails_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_clientes_hospedagens`
--
ALTER TABLE `tab_clientes_hospedagens`
  ADD CONSTRAINT `tab_clientes_hospedagens_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_clientes_hospedagens_chs_id` FOREIGN KEY (`chs_id`) REFERENCES `tab_clientes_hospedagens_status` (`chs_id`),
  ADD CONSTRAINT `tab_clientes_hospedagens_ser_id` FOREIGN KEY (`ser_id`) REFERENCES `tab_servidores` (`ser_id`);

--
-- Limitadores para a tabela `tab_clientes_iptv`
--
ALTER TABLE `tab_clientes_iptv`
  ADD CONSTRAINT `FK_tab_clientes_iptv_clientes` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `FK_tab_clientes_iptv_contratos` FOREIGN KEY (`cor_id`) REFERENCES `tab_contratos` (`cor_id`),
  ADD CONSTRAINT `FK_tab_clientes_iptv_planos` FOREIGN KEY (`pla_id`) REFERENCES `tab_planos` (`pla_id`),
  ADD CONSTRAINT `FK_tab_clientes_iptv_tab_clientes_iptv_status` FOREIGN KEY (`cis_id`) REFERENCES `tab_clientes_iptv_status` (`cis_id`);

--
-- Limitadores para a tabela `tab_clientes_iptv_ajustes`
--
ALTER TABLE `tab_clientes_iptv_ajustes`
  ADD CONSTRAINT `tab_clientes_iptv_cip_id` FOREIGN KEY (`cip_id`) REFERENCES `tab_clientes_iptv` (`cip_id`),
  ADD CONSTRAINT `tab_clientes_iptv_saj_id` FOREIGN KEY (`saj_id`) REFERENCES `tab_servicos_ajustes` (`saj_id`);

--
-- Limitadores para a tabela `tab_clientes_iptv_comentarios`
--
ALTER TABLE `tab_clientes_iptv_comentarios`
  ADD CONSTRAINT `FK_tab_clientes_iptv_comentarios_tab_clientes_iptv` FOREIGN KEY (`cip_id`) REFERENCES `tab_clientes_iptv` (`cip_id`),
  ADD CONSTRAINT `FK_tab_clientes_iptv_comentarios_tab_usuarios` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_clientes_negativacoes_comentarios`
--
ALTER TABLE `tab_clientes_negativacoes_comentarios`
  ADD CONSTRAINT `tab_clientes_negativacoes_comentarios_ibfk_1` FOREIGN KEY (`cln_id`) REFERENCES `tab_clientes_negativacoes` (`cln_id`),
  ADD CONSTRAINT `tab_clientes_negativacoes_comentarios_ibfk_2` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_clientes_origens`
--
ALTER TABLE `tab_clientes_origens`
  ADD CONSTRAINT `FK_clientes_origens_cep` FOREIGN KEY (`cep_id`) REFERENCES `tab_ceps` (`cep_id`);

--
-- Limitadores para a tabela `tab_clientes_pontos`
--
ALTER TABLE `tab_clientes_pontos`
  ADD CONSTRAINT `tab_clientes_pontos_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_clientes_pontos_cps_id` FOREIGN KEY (`cps_id`) REFERENCES `tab_clientes_pontos_status` (`cps_id`),
  ADD CONSTRAINT `tab_clientes_pontos_sei_id` FOREIGN KEY (`sei_id`) REFERENCES `tab_servidores_interfaces` (`sei_id`);

--
-- Limitadores para a tabela `tab_clientes_pontos_ajustes`
--
ALTER TABLE `tab_clientes_pontos_ajustes`
  ADD CONSTRAINT `tab_clientes_pontos_ajustes_clp_id` FOREIGN KEY (`clp_id`) REFERENCES `tab_clientes_pontos` (`clp_id`),
  ADD CONSTRAINT `tab_clientes_pontos_ajustes_saj_id` FOREIGN KEY (`saj_id`) REFERENCES `tab_servicos_ajustes` (`saj_id`);

--
-- Limitadores para a tabela `tab_clientes_pontos_comentarios`
--
ALTER TABLE `tab_clientes_pontos_comentarios`
  ADD CONSTRAINT `tab_clientes_pontos_comentarios_clp_id` FOREIGN KEY (`clp_id`) REFERENCES `tab_clientes_pontos` (`clp_id`),
  ADD CONSTRAINT `tab_clientes_pontos_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_clientes_responsabilidades`
--
ALTER TABLE `tab_clientes_responsabilidades`
  ADD CONSTRAINT `tab_clientes_responsabilidades_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_clientes_responsabilidades_cod_id` FOREIGN KEY (`cod_id`) REFERENCES `tab_condominios_responsabilidades` (`cod_id`),
  ADD CONSTRAINT `tab_clientes_responsabilidades_cra_id` FOREIGN KEY (`cra_id`) REFERENCES `tab_clientes_responsabilidades_status` (`cra_id`),
  ADD CONSTRAINT `tab_clientes_responsabilidades_cri_id` FOREIGN KEY (`cri_id`) REFERENCES `tab_clientes_responsabilidades_tipos` (`cri_id`),
  ADD CONSTRAINT `tab_clientes_responsabilidades_crt_id` FOREIGN KEY (`crt_id`) REFERENCES `tab_clientes_responsabilidades_categorias` (`crt_id`),
  ADD CONSTRAINT `tab_clientes_responsabilidades_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_clientes_responsabilidades_comentarios`
--
ALTER TABLE `tab_clientes_responsabilidades_comentarios`
  ADD CONSTRAINT `tab_clientes_responsabilidades_comentarios_cln_id` FOREIGN KEY (`cln_id`) REFERENCES `tab_clientes_responsabilidades` (`cln_id`),
  ADD CONSTRAINT `tab_clientes_responsabilidades_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_clientes_retornos`
--
ALTER TABLE `tab_clientes_retornos`
  ADD CONSTRAINT `tab_clientes_retornos_cee_id` FOREIGN KEY (`cee_id`) REFERENCES `tab_clientes_retornos_periodos` (`cee_id`),
  ADD CONSTRAINT `tab_clientes_retornos_ces_id` FOREIGN KEY (`ces_id`) REFERENCES `tab_clientes_retornos_status` (`ces_id`),
  ADD CONSTRAINT `tab_clientes_retornos_cet_id` FOREIGN KEY (`cet_id`) REFERENCES `tab_clientes_retornos_tipos` (`cet_id`),
  ADD CONSTRAINT `tab_clientes_retornos_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_clientes_retornos_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_clientes_telefones`
--
ALTER TABLE `tab_clientes_telefones`
  ADD CONSTRAINT `tab_clientes_telefones_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_clientes_telefones_cts_id` FOREIGN KEY (`cts_id`) REFERENCES `tab_clientes_telefones_status` (`cts_id`),
  ADD CONSTRAINT `tab_clientes_telefones_ctt_id` FOREIGN KEY (`ctt_id`) REFERENCES `tab_clientes_telefones_tipos` (`ctt_id`);

--
-- Limitadores para a tabela `tab_clientes_telefones_recargas`
--
ALTER TABLE `tab_clientes_telefones_recargas`
  ADD CONSTRAINT `tab_clientes_telefones_recargas_clt_id` FOREIGN KEY (`clt_id`) REFERENCES `tab_clientes_telefones` (`clt_id`),
  ADD CONSTRAINT `tab_clientes_telefones_recargas_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_cobrancas`
--
ALTER TABLE `tab_cobrancas`
  ADD CONSTRAINT `tab_cobrancas_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_cobrancas_coa_grupo_id` FOREIGN KEY (`coa_grupo_id`) REFERENCES `tab_cobrancas` (`coa_id`),
  ADD CONSTRAINT `tab_cobrancas_coa_pai` FOREIGN KEY (`coa_pai`) REFERENCES `tab_cobrancas` (`coa_id`),
  ADD CONSTRAINT `tab_cobrancas_coe_id` FOREIGN KEY (`coe_id`) REFERENCES `tab_cobrancas_retornos` (`coe_id`),
  ADD CONSTRAINT `tab_cobrancas_cof_id` FOREIGN KEY (`cof_id`) REFERENCES `tab_cobrancas_formatos` (`cof_id`),
  ADD CONSTRAINT `tab_cobrancas_col_id` FOREIGN KEY (`col_id`) REFERENCES `tab_cobrancas_lotes` (`col_id`),
  ADD CONSTRAINT `tab_cobrancas_cop_id` FOREIGN KEY (`cop_id`) REFERENCES `tab_cobrancas_tipos` (`cop_id`),
  ADD CONSTRAINT `tab_cobrancas_cot_id` FOREIGN KEY (`cot_id`) REFERENCES `tab_cobrancas_status` (`cot_id`),
  ADD CONSTRAINT `tab_cobrancas_fat_id` FOREIGN KEY (`fat_id`) REFERENCES `tab_faturas` (`fat_id`),
  ADD CONSTRAINT `tab_cobrancas_fco_id` FOREIGN KEY (`fco_id`) REFERENCES `tab_faturas_lotes_cobrancas` (`fco_id`),
  ADD CONSTRAINT `tab_cobrancas_rec_id` FOREIGN KEY (`rec_id`) REFERENCES `tab_recuperacao_contatos` (`rec_id`);

--
-- Limitadores para a tabela `tab_cobrancas_comentarios`
--
ALTER TABLE `tab_cobrancas_comentarios`
  ADD CONSTRAINT `tab_cobrancas_comentarios_coa_id` FOREIGN KEY (`coa_id`) REFERENCES `tab_cobrancas` (`coa_id`),
  ADD CONSTRAINT `tab_cobrancas_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_cobrancas_detalhes`
--
ALTER TABLE `tab_cobrancas_detalhes`
  ADD CONSTRAINT `tab_cobrancas_detalhes_ajf_id` FOREIGN KEY (`ajf_id`) REFERENCES `tab_ajustes_financeiros` (`ajf_id`),
  ADD CONSTRAINT `tab_cobrancas_detalhes_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_cobrancas_detalhes_coa_id` FOREIGN KEY (`coa_id`) REFERENCES `tab_cobrancas` (`coa_id`),
  ADD CONSTRAINT `tab_cobrancas_detalhes_cod_pai` FOREIGN KEY (`cod_pai`) REFERENCES `tab_cobrancas_detalhes` (`cod_id`),
  ADD CONSTRAINT `tab_cobrancas_detalhes_dec_id` FOREIGN KEY (`dec_id`) REFERENCES `tab_descontos_concessoes` (`dec_id`);

--
-- Limitadores para a tabela `tab_cobrancas_formatos`
--
ALTER TABLE `tab_cobrancas_formatos`
  ADD CONSTRAINT `tab_cobrancas_formatos_cfs_id` FOREIGN KEY (`cfs_id`) REFERENCES `tab_cobrancas_formatos_status` (`cfs_id`),
  ADD CONSTRAINT `tab_cobrancas_formatos_cft_id` FOREIGN KEY (`cft_id`) REFERENCES `tab_cobrancas_formatos_tipos` (`cft_id`);

--
-- Limitadores para a tabela `tab_cobrancas_lotes`
--
ALTER TABLE `tab_cobrancas_lotes`
  ADD CONSTRAINT `tab_cobrancas_lotes_cla_id` FOREIGN KEY (`cla_id`) REFERENCES `tab_cobrancas_lotes_status` (`cla_id`),
  ADD CONSTRAINT `tab_cobrancas_lotes_cof_id` FOREIGN KEY (`cof_id`) REFERENCES `tab_cobrancas_formatos` (`cof_id`),
  ADD CONSTRAINT `tab_cobrancas_lotes_cop_id` FOREIGN KEY (`cop_id`) REFERENCES `tab_cobrancas_tipos` (`cop_id`);

--
-- Limitadores para a tabela `tab_cobrancas_lotes_comentarios`
--
ALTER TABLE `tab_cobrancas_lotes_comentarios`
  ADD CONSTRAINT `tab_cobrancas_lotes_comentarios_col_id` FOREIGN KEY (`col_id`) REFERENCES `tab_cobrancas_lotes` (`col_id`),
  ADD CONSTRAINT `tab_cobrancas_lotes_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_cobrancas_remessas_itens`
--
ALTER TABLE `tab_cobrancas_remessas_itens`
  ADD CONSTRAINT `fk_cobrancas_remessas_itens_cobrancas_formatos` FOREIGN KEY (`cof_id`) REFERENCES `tab_cobrancas_formatos` (`cof_id`),
  ADD CONSTRAINT `tab_cobrancas_remessas_itens_ibfk_1` FOREIGN KEY (`coa_id`) REFERENCES `tab_cobrancas` (`coa_id`);

--
-- Limitadores para a tabela `tab_cobrancas_retornos`
--
ALTER TABLE `tab_cobrancas_retornos`
  ADD CONSTRAINT `tab_cobrancas_retornos_cof_id` FOREIGN KEY (`cof_id`) REFERENCES `tab_cobrancas_formatos` (`cof_id`);

--
-- Limitadores para a tabela `tab_cobrancas_retornos_erros`
--
ALTER TABLE `tab_cobrancas_retornos_erros`
  ADD CONSTRAINT `tab_cobrancas_retornos_erros_coa_id` FOREIGN KEY (`coa_id`) REFERENCES `tab_cobrancas` (`coa_id`),
  ADD CONSTRAINT `tab_cobrancas_retornos_erros_coe_id` FOREIGN KEY (`coe_id`) REFERENCES `tab_cobrancas_retornos` (`coe_id`);

--
-- Limitadores para a tabela `tab_cobrancas_retornos_nao_encontradas`
--
ALTER TABLE `tab_cobrancas_retornos_nao_encontradas`
  ADD CONSTRAINT `tab_cobrancas_retornos_nao_encontradas_ibfk_1` FOREIGN KEY (`coe_id`) REFERENCES `tab_cobrancas_retornos` (`coe_id`),
  ADD CONSTRAINT `tab_cobrancas_retornos_nao_encontradas_ibfk_2` FOREIGN KEY (`coa_id`) REFERENCES `tab_cobrancas` (`coa_id`);

--
-- Limitadores para a tabela `tab_cobrancas_retornos_registros`
--
ALTER TABLE `tab_cobrancas_retornos_registros`
  ADD CONSTRAINT `tab_cobrancas_retornos_registros_coa_id` FOREIGN KEY (`coa_id`) REFERENCES `tab_cobrancas` (`coa_id`),
  ADD CONSTRAINT `tab_cobrancas_retornos_registros_coe_id` FOREIGN KEY (`coe_id`) REFERENCES `tab_cobrancas_retornos` (`coe_id`);

--
-- Limitadores para a tabela `tab_condominios`
--
ALTER TABLE `tab_condominios`
  ADD CONSTRAINT `tab_condominios_bai_id` FOREIGN KEY (`bai_id`) REFERENCES `tab_bairros` (`bai_id`),
  ADD CONSTRAINT `tab_condominios_ccl_id` FOREIGN KEY (`ccl_id`) REFERENCES `tab_condominios_classes` (`ccl_id`),
  ADD CONSTRAINT `tab_condominios_cep_id` FOREIGN KEY (`cep_id`) REFERENCES `tab_ceps` (`cep_id`),
  ADD CONSTRAINT `tab_condominios_cid_id` FOREIGN KEY (`cid_id`) REFERENCES `tab_cidades` (`cid_id`),
  ADD CONSTRAINT `tab_condominios_cit_id` FOREIGN KEY (`cit_id`) REFERENCES `tab_condominios_instalacoes_tipos` (`cit_id`),
  ADD CONSTRAINT `tab_condominios_cnt_id` FOREIGN KEY (`cnt_id`) REFERENCES `tab_condominios_tipos` (`cnt_id`),
  ADD CONSTRAINT `tab_condominios_cog_id` FOREIGN KEY (`cog_id`) REFERENCES `tab_condominios_tecnologias` (`cog_id`),
  ADD CONSTRAINT `tab_condominios_coi_id` FOREIGN KEY (`coi_id`) REFERENCES `tab_condominios_situacoes` (`coi_id`),
  ADD CONSTRAINT `tab_condominios_con_pai` FOREIGN KEY (`con_pai`) REFERENCES `tab_condominios` (`con_id`),
  ADD CONSTRAINT `tab_condominios_coq_id` FOREIGN KEY (`coq_id`) REFERENCES `tab_condominios_tecnologias_equipamentos` (`coq_id`),
  ADD CONSTRAINT `tab_condominios_cos_id` FOREIGN KEY (`cos_id`) REFERENCES `tab_condominios_status` (`cos_id`),
  ADD CONSTRAINT `tab_condominios_est_id` FOREIGN KEY (`est_id`) REFERENCES `tab_estados` (`est_id`),
  ADD CONSTRAINT `tab_condominios_log_id` FOREIGN KEY (`log_id`) REFERENCES `tab_logradouros` (`log_id`),
  ADD CONSTRAINT `tab_condominios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_condominios_blocos`
--
ALTER TABLE `tab_condominios_blocos`
  ADD CONSTRAINT `tab_condominios_blocos_bai_id` FOREIGN KEY (`bai_id`) REFERENCES `tab_bairros` (`bai_id`),
  ADD CONSTRAINT `tab_condominios_blocos_cbs_id` FOREIGN KEY (`cbs_id`) REFERENCES `tab_condominios_blocos_status` (`cbs_id`),
  ADD CONSTRAINT `tab_condominios_blocos_cep_id` FOREIGN KEY (`cep_id`) REFERENCES `tab_ceps` (`cep_id`),
  ADD CONSTRAINT `tab_condominios_blocos_cid_id` FOREIGN KEY (`cid_id`) REFERENCES `tab_cidades` (`cid_id`),
  ADD CONSTRAINT `tab_condominios_blocos_con_id` FOREIGN KEY (`con_id`) REFERENCES `tab_condominios` (`con_id`),
  ADD CONSTRAINT `tab_condominios_blocos_est_id` FOREIGN KEY (`est_id`) REFERENCES `tab_estados` (`est_id`),
  ADD CONSTRAINT `tab_condominios_blocos_log_id` FOREIGN KEY (`log_id`) REFERENCES `tab_logradouros` (`log_id`),
  ADD CONSTRAINT `tab_condominios_blocos_sei_id` FOREIGN KEY (`sei_id`) REFERENCES `tab_servidores_interfaces` (`sei_id`);

--
-- Limitadores para a tabela `tab_condominios_contatos`
--
ALTER TABLE `tab_condominios_contatos`
  ADD CONSTRAINT `tab_condominios_contatos_cct_id` FOREIGN KEY (`cct_id`) REFERENCES `tab_condominios_contatos_tipos` (`cct_id`),
  ADD CONSTRAINT `tab_condominios_contatos_cob_id` FOREIGN KEY (`cob_id`) REFERENCES `tab_condominios_blocos` (`cob_id`),
  ADD CONSTRAINT `tab_condominios_contatos_con_id` FOREIGN KEY (`con_id`) REFERENCES `tab_condominios` (`con_id`);

--
-- Limitadores para a tabela `tab_condominios_procedimentos`
--
ALTER TABLE `tab_condominios_procedimentos`
  ADD CONSTRAINT `tab_condominios_procedimentos_con_id` FOREIGN KEY (`con_id`) REFERENCES `tab_condominios` (`con_id`),
  ADD CONSTRAINT `tab_condominios_procedimentos_cpt_id` FOREIGN KEY (`cpt_id`) REFERENCES `tab_condominios_procedimentos_tipos` (`cpt_id`),
  ADD CONSTRAINT `tab_condominios_procedimentos_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_condominios_responsabilidades`
--
ALTER TABLE `tab_condominios_responsabilidades`
  ADD CONSTRAINT `tab_condominios_responsabilidades_con_id` FOREIGN KEY (`con_id`) REFERENCES `tab_condominios` (`con_id`),
  ADD CONSTRAINT `tab_condominios_responsabilidades_crg_id` FOREIGN KEY (`crg_id`) REFERENCES `tab_condominios_responsabilidades_categorias` (`crg_id`),
  ADD CONSTRAINT `tab_condominios_responsabilidades_crp_id` FOREIGN KEY (`crp_id`) REFERENCES `tab_condominios_responsabilidades_tipos` (`crp_id`),
  ADD CONSTRAINT `tab_condominios_responsabilidades_cru_id` FOREIGN KEY (`cru_id`) REFERENCES `tab_condominios_responsabilidades_status` (`cru_id`),
  ADD CONSTRAINT `tab_condominios_responsabilidades_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_condominios_responsabilidades_comentarios`
--
ALTER TABLE `tab_condominios_responsabilidades_comentarios`
  ADD CONSTRAINT `tab_condominios_responsabilidades_comentarios_cod_id` FOREIGN KEY (`cod_id`) REFERENCES `tab_condominios_responsabilidades` (`cod_id`),
  ADD CONSTRAINT `tab_condominios_responsabilidades_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_condominios_tecnologias_equipamentos`
--
ALTER TABLE `tab_condominios_tecnologias_equipamentos`
  ADD CONSTRAINT `tab_condominios_tecnologias_equipamentos_cog_id` FOREIGN KEY (`cog_id`) REFERENCES `tab_condominios_tecnologias` (`cog_id`);

--
-- Limitadores para a tabela `tab_contratos`
--
ALTER TABLE `tab_contratos`
  ADD CONSTRAINT `tab_contratos_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_contratos_cma_id` FOREIGN KEY (`cma_id`) REFERENCES `tab_contratos_motivos_anulacao` (`cma_id`),
  ADD CONSTRAINT `tab_contratos_cor_pai` FOREIGN KEY (`cor_pai`) REFERENCES `tab_contratos` (`cor_id`),
  ADD CONSTRAINT `tab_contratos_crs_id` FOREIGN KEY (`crs_id`) REFERENCES `tab_contratos_status` (`crs_id`),
  ADD CONSTRAINT `tab_contratos_crt_id` FOREIGN KEY (`crt_id`) REFERENCES `tab_contratos_tipos` (`crt_id`),
  ADD CONSTRAINT `tab_contratos_fop_id` FOREIGN KEY (`fop_id`) REFERENCES `tab_formas_pagamento` (`fop_id`),
  ADD CONSTRAINT `tab_contratos_pls_id` FOREIGN KEY (`pls_id`) REFERENCES `tab_planos_servicos` (`pls_id`);

--
-- Limitadores para a tabela `tab_contratos_comentarios`
--
ALTER TABLE `tab_contratos_comentarios`
  ADD CONSTRAINT `tab_contratos_comentarios_ccc_id` FOREIGN KEY (`ccc_id`) REFERENCES `tab_contratos_comentarios_categorias` (`ccc_id`),
  ADD CONSTRAINT `tab_contratos_comentarios_cor_id` FOREIGN KEY (`cor_id`) REFERENCES `tab_contratos` (`cor_id`),
  ADD CONSTRAINT `tab_contratos_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_contratos_versoes`
--
ALTER TABLE `tab_contratos_versoes`
  ADD CONSTRAINT `tab_contratos_versoes_pli_id` FOREIGN KEY (`pli_id`) REFERENCES `tab_planos_sistemas` (`pli_id`),
  ADD CONSTRAINT `tab_contratos_versoes_pls_id` FOREIGN KEY (`pls_id`) REFERENCES `tab_planos_servicos` (`pls_id`);

--
-- Limitadores para a tabela `tab_contratos_vinculos`
--
ALTER TABLE `tab_contratos_vinculos`
  ADD CONSTRAINT `tab_contratos_vinculos_cor_filho` FOREIGN KEY (`cor_filho`) REFERENCES `tab_contratos` (`cor_id`),
  ADD CONSTRAINT `tab_contratos_vinculos_cor_id` FOREIGN KEY (`cor_id`) REFERENCES `tab_contratos` (`cor_id`),
  ADD CONSTRAINT `tab_contratos_vinculos_pvt_id` FOREIGN KEY (`pvt_id`) REFERENCES `tab_planos_vinculos_tipos` (`pvt_id`);

--
-- Limitadores para a tabela `tab_descontos`
--
ALTER TABLE `tab_descontos`
  ADD CONSTRAINT `tab_descontos_clo_id` FOREIGN KEY (`clo_id`) REFERENCES `tab_clientes_origens` (`clo_id`),
  ADD CONSTRAINT `tab_descontos_dea_id` FOREIGN KEY (`dea_id`) REFERENCES `tab_descontos_status` (`dea_id`),
  ADD CONSTRAINT `tab_descontos_det_id` FOREIGN KEY (`det_id`) REFERENCES `tab_descontos_tipos` (`det_id`),
  ADD CONSTRAINT `tab_descontos_pla_id` FOREIGN KEY (`pla_id`) REFERENCES `tab_planos` (`pla_id`),
  ADD CONSTRAINT `tab_descontos_pli_id` FOREIGN KEY (`pli_id`) REFERENCES `tab_planos_sistemas` (`pli_id`),
  ADD CONSTRAINT `tab_descontos_pls_id` FOREIGN KEY (`pls_id`) REFERENCES `tab_planos_servicos` (`pls_id`);

--
-- Limitadores para a tabela `tab_descontos_concessoes`
--
ALTER TABLE `tab_descontos_concessoes`
  ADD CONSTRAINT `tab_descontos_concessoes_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_descontos_concessoes_con_id` FOREIGN KEY (`con_id`) REFERENCES `tab_condominios` (`con_id`),
  ADD CONSTRAINT `tab_descontos_concessoes_dcs_id` FOREIGN KEY (`dcs_id`) REFERENCES `tab_descontos_concessoes_status` (`dcs_id`),
  ADD CONSTRAINT `tab_descontos_concessoes_dec_pai` FOREIGN KEY (`dec_pai`) REFERENCES `tab_descontos_concessoes` (`dec_id`),
  ADD CONSTRAINT `tab_descontos_concessoes_des_id` FOREIGN KEY (`des_id`) REFERENCES `tab_descontos` (`des_id`),
  ADD CONSTRAINT `tab_descontos_concessoes_det_id` FOREIGN KEY (`det_id`) REFERENCES `tab_descontos_tipos` (`det_id`),
  ADD CONSTRAINT `tab_descontos_concessoes_pla_id` FOREIGN KEY (`pla_id`) REFERENCES `tab_planos` (`pla_id`);

--
-- Limitadores para a tabela `tab_descontos_concessoes_comentarios`
--
ALTER TABLE `tab_descontos_concessoes_comentarios`
  ADD CONSTRAINT `tab_descontos_concessoes_comentarios_dec_id` FOREIGN KEY (`dec_id`) REFERENCES `tab_descontos_concessoes` (`dec_id`),
  ADD CONSTRAINT `tab_descontos_concessoes_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_documentos`
--
ALTER TABLE `tab_documentos`
  ADD CONSTRAINT `tab_documentos_doa_id` FOREIGN KEY (`doa_id`) REFERENCES `tab_documentos_categorias` (`doa_id`),
  ADD CONSTRAINT `tab_documentos_dot_id` FOREIGN KEY (`dot_id`) REFERENCES `tab_documentos_tipos` (`dot_id`),
  ADD CONSTRAINT `tab_documentos_dou_id` FOREIGN KEY (`dou_id`) REFERENCES `tab_documentos_status` (`dou_id`);

--
-- Limitadores para a tabela `tab_documentos_categorias`
--
ALTER TABLE `tab_documentos_categorias`
  ADD CONSTRAINT `tab_documentos_categorias_doa_pai` FOREIGN KEY (`doa_pai`) REFERENCES `tab_documentos_categorias` (`doa_id`);

--
-- Limitadores para a tabela `tab_documentos_comentarios`
--
ALTER TABLE `tab_documentos_comentarios`
  ADD CONSTRAINT `tab_documentos_comentarios_doc_id` FOREIGN KEY (`doc_id`) REFERENCES `tab_documentos` (`doc_id`),
  ADD CONSTRAINT `tab_documentos_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_documentos_faqs`
--
ALTER TABLE `tab_documentos_faqs`
  ADD CONSTRAINT `tab_documentos_faqs_doc_id` FOREIGN KEY (`doc_id`) REFERENCES `tab_documentos` (`doc_id`),
  ADD CONSTRAINT `tab_documentos_faqs_dof_pai` FOREIGN KEY (`dof_pai`) REFERENCES `tab_documentos_faqs` (`dof_id`),
  ADD CONSTRAINT `tab_documentos_faqs_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_dominios`
--
ALTER TABLE `tab_dominios`
  ADD CONSTRAINT `tab_dominios_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_dominios_doo_id` FOREIGN KEY (`doo_id`) REFERENCES `tab_dominios_origens` (`doo_id`),
  ADD CONSTRAINT `tab_dominios_dos_id` FOREIGN KEY (`dos_id`) REFERENCES `tab_dominios_status` (`dos_id`);

--
-- Limitadores para a tabela `tab_equipamentos_modelos`
--
ALTER TABLE `tab_equipamentos_modelos`
  ADD CONSTRAINT `tab_equipamentos_modelos_equ_id` FOREIGN KEY (`equ_id`) REFERENCES `tab_equipamentos` (`equ_id`);

--
-- Limitadores para a tabela `tab_estoque`
--
ALTER TABLE `tab_estoque`
  ADD CONSTRAINT `tab_estoque_esi_id` FOREIGN KEY (`esi_id`) REFERENCES `tab_estoque_itens` (`esi_id`);

--
-- Limitadores para a tabela `tab_estoque_alocacoes`
--
ALTER TABLE `tab_estoque_alocacoes`
  ADD CONSTRAINT `tab_estoque_alocacoes_esg_id` FOREIGN KEY (`esg_id`) REFERENCES `tab_estoque_grupos` (`esg_id`),
  ADD CONSTRAINT `tab_estoque_alocacoes_est_id` FOREIGN KEY (`est_id`) REFERENCES `tab_estoque` (`est_id`);

--
-- Limitadores para a tabela `tab_estoque_comprovantes`
--
ALTER TABLE `tab_estoque_comprovantes`
  ADD CONSTRAINT `tab_estoque_comprovantes_ect_id` FOREIGN KEY (`ect_id`) REFERENCES `tab_estoque_comprovantes_tipos` (`ect_id`),
  ADD CONSTRAINT `tab_estoque_comprovantes_esf_id` FOREIGN KEY (`esf_id`) REFERENCES `tab_estoque_fornecedores` (`esf_id`);

--
-- Limitadores para a tabela `tab_estoque_entradas`
--
ALTER TABLE `tab_estoque_entradas`
  ADD CONSTRAINT `tab_estoque_entradas_esc_id` FOREIGN KEY (`esc_id`) REFERENCES `tab_estoque_comprovantes` (`esc_id`),
  ADD CONSTRAINT `tab_estoque_entradas_esi_id` FOREIGN KEY (`esi_id`) REFERENCES `tab_estoque_itens` (`esi_id`);

--
-- Limitadores para a tabela `tab_estoque_saidas`
--
ALTER TABLE `tab_estoque_saidas`
  ADD CONSTRAINT `tab_estoque_saidas_esi_id` FOREIGN KEY (`esi_id`) REFERENCES `tab_estoque_itens` (`esi_id`),
  ADD CONSTRAINT `tab_estoque_saidas_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_estoque_sublocacoes`
--
ALTER TABLE `tab_estoque_sublocacoes`
  ADD CONSTRAINT `tab_estoque_sublocacoes_esa_id` FOREIGN KEY (`esa_id`) REFERENCES `tab_estoque_alocacoes` (`esa_id`),
  ADD CONSTRAINT `tab_estoque_sublocacoes_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_faturas`
--
ALTER TABLE `tab_faturas`
  ADD CONSTRAINT `tab_faturas_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_faturas_coa_id` FOREIGN KEY (`coa_id`) REFERENCES `tab_cobrancas` (`coa_id`),
  ADD CONSTRAINT `tab_faturas_emp_id` FOREIGN KEY (`emp_id`) REFERENCES `tab_empresas` (`emp_id`),
  ADD CONSTRAINT `tab_faturas_fal_id` FOREIGN KEY (`fal_id`) REFERENCES `tab_faturas_lotes` (`fal_id`),
  ADD CONSTRAINT `tab_faturas_fap_id` FOREIGN KEY (`fap_id`) REFERENCES `tab_faturas_tipos` (`fap_id`),
  ADD CONSTRAINT `tab_faturas_fcl_id` FOREIGN KEY (`fcl_id`) REFERENCES `tab_faturas_lotes_clientes` (`fcl_id`);

--
-- Limitadores para a tabela `tab_faturas_itens`
--
ALTER TABLE `tab_faturas_itens`
  ADD CONSTRAINT `tab_faturas_itens_cft_id` FOREIGN KEY (`cft_id`) REFERENCES `tab_classes_fiscais_tipos` (`cft_id`),
  ADD CONSTRAINT `tab_faturas_itens_fat_id` FOREIGN KEY (`fat_id`) REFERENCES `tab_faturas` (`fat_id`);

--
-- Limitadores para a tabela `tab_faturas_lotes`
--
ALTER TABLE `tab_faturas_lotes`
  ADD CONSTRAINT `tab_faturas_lotes_fap_id` FOREIGN KEY (`fap_id`) REFERENCES `tab_faturas_tipos` (`fap_id`),
  ADD CONSTRAINT `tab_faturas_lotes_fls_id` FOREIGN KEY (`fls_id`) REFERENCES `tab_faturas_lotes_status` (`fls_id`);

--
-- Limitadores para a tabela `tab_faturas_lotes_clientes`
--
ALTER TABLE `tab_faturas_lotes_clientes`
  ADD CONSTRAINT `tab_faturas_lotes_clientes_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_faturas_lotes_clientes_fal_id` FOREIGN KEY (`fal_id`) REFERENCES `tab_faturas_lotes` (`fal_id`);

--
-- Limitadores para a tabela `tab_faturas_lotes_cobrancas`
--
ALTER TABLE `tab_faturas_lotes_cobrancas`
  ADD CONSTRAINT `tab_faturas_lotes_cobrancas_coa_id` FOREIGN KEY (`coa_id`) REFERENCES `tab_cobrancas` (`coa_id`),
  ADD CONSTRAINT `tab_faturas_lotes_cobrancas_fat_id` FOREIGN KEY (`fat_id`) REFERENCES `tab_faturas` (`fat_id`),
  ADD CONSTRAINT `tab_faturas_lotes_cobrancas_ibfk_1` FOREIGN KEY (`fal_id`) REFERENCES `tab_faturas_lotes` (`fal_id`);

--
-- Limitadores para a tabela `tab_faturas_lotes_comentarios`
--
ALTER TABLE `tab_faturas_lotes_comentarios`
  ADD CONSTRAINT `tab_faturas_lotes_comentarios_fal_id` FOREIGN KEY (`fal_id`) REFERENCES `tab_faturas_lotes` (`fal_id`),
  ADD CONSTRAINT `tab_faturas_lotes_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_faturas_lotes_filtros`
--
ALTER TABLE `tab_faturas_lotes_filtros`
  ADD CONSTRAINT `tab_faturas_lotes_filtros_faf_id` FOREIGN KEY (`faf_id`) REFERENCES `tab_faturas_filtros` (`faf_id`),
  ADD CONSTRAINT `tab_faturas_lotes_filtros_fal_id` FOREIGN KEY (`fal_id`) REFERENCES `tab_faturas_lotes` (`fal_id`);

--
-- Limitadores para a tabela `tab_formas_pagamento`
--
ALTER TABLE `tab_formas_pagamento`
  ADD CONSTRAINT `tab_formas_pagamento_fpc_id` FOREIGN KEY (`fpc_id`) REFERENCES `tab_formas_pagamento_categorias` (`fpc_id`),
  ADD CONSTRAINT `tab_formas_pagamento_fpf_id` FOREIGN KEY (`fpf_id`) REFERENCES `tab_formas_pagamento_formas` (`fpf_id`),
  ADD CONSTRAINT `tab_formas_pagamento_pla_id` FOREIGN KEY (`pla_id`) REFERENCES `tab_planos` (`pla_id`),
  ADD CONSTRAINT `tab_formas_pagamento_pls_id` FOREIGN KEY (`pls_id`) REFERENCES `tab_planos_servicos` (`pls_id`);

--
-- Limitadores para a tabela `tab_grupos`
--
ALTER TABLE `tab_grupos`
  ADD CONSTRAINT `tab_grupos_grs_id` FOREIGN KEY (`grs_id`) REFERENCES `tab_grupos_status` (`grs_id`);

--
-- Limitadores para a tabela `tab_logradouros`
--
ALTER TABLE `tab_logradouros`
  ADD CONSTRAINT `tab_logradouros_bai_id` FOREIGN KEY (`bai_id`) REFERENCES `tab_bairros` (`bai_id`);

--
-- Limitadores para a tabela `tab_mensagens`
--
ALTER TABLE `tab_mensagens`
  ADD CONSTRAINT `tab_mensagens_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_mensagens_mel_id` FOREIGN KEY (`mel_id`) REFERENCES `tab_mensagens_lotes` (`mel_id`),
  ADD CONSTRAINT `tab_mensagens_mes_id` FOREIGN KEY (`mes_id`) REFERENCES `tab_mensagens_status` (`mes_id`),
  ADD CONSTRAINT `tab_mensagens_mlm_id` FOREIGN KEY (`mlm_id`) REFERENCES `tab_mensagens_lotes_modelos` (`mlm_id`),
  ADD CONSTRAINT `tab_mensagens_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_mensagens_comentarios`
--
ALTER TABLE `tab_mensagens_comentarios`
  ADD CONSTRAINT `tab_mensagens_comentarios_mcc_id` FOREIGN KEY (`mcc_id`) REFERENCES `tab_mensagens_comentarios_categorias` (`mcc_id`),
  ADD CONSTRAINT `tab_mensagens_comentarios_men_id` FOREIGN KEY (`men_id`) REFERENCES `tab_mensagens` (`men_id`),
  ADD CONSTRAINT `tab_mensagens_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_mensagens_envios`
--
ALTER TABLE `tab_mensagens_envios`
  ADD CONSTRAINT `tab_mensagens_envios_men_id` FOREIGN KEY (`men_id`) REFERENCES `tab_mensagens` (`men_id`);

--
-- Limitadores para a tabela `tab_mensagens_links`
--
ALTER TABLE `tab_mensagens_links`
  ADD CONSTRAINT `tab_mensagens_links_men_id` FOREIGN KEY (`men_id`) REFERENCES `tab_mensagens` (`men_id`);

--
-- Limitadores para a tabela `tab_mensagens_lotes`
--
ALTER TABLE `tab_mensagens_lotes`
  ADD CONSTRAINT `tab_mensagens_lotes_mlm_id` FOREIGN KEY (`mlm_id`) REFERENCES `tab_mensagens_lotes_modelos` (`mlm_id`),
  ADD CONSTRAINT `tab_mensagens_lotes_mls_id` FOREIGN KEY (`mls_id`) REFERENCES `tab_mensagens_lotes_status` (`mls_id`),
  ADD CONSTRAINT `tab_mensagens_lotes_mlt_id` FOREIGN KEY (`mlt_id`) REFERENCES `tab_mensagens_lotes_tipos` (`mlt_id`);

--
-- Limitadores para a tabela `tab_mensagens_lotes_comentarios`
--
ALTER TABLE `tab_mensagens_lotes_comentarios`
  ADD CONSTRAINT `tab_mensagens_lotes_comentarios_mel_id` FOREIGN KEY (`mel_id`) REFERENCES `tab_mensagens_lotes` (`mel_id`),
  ADD CONSTRAINT `tab_mensagens_lotes_comentarios_mlo_id` FOREIGN KEY (`mlo_id`) REFERENCES `tab_mensagens_lotes_comentarios_categorias` (`mlo_id`),
  ADD CONSTRAINT `tab_mensagens_lotes_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_mensagens_lotes_modelos`
--
ALTER TABLE `tab_mensagens_lotes_modelos`
  ADD CONSTRAINT `tab_mensagens_lotes_modelos_mlm_pai` FOREIGN KEY (`mlm_pai`) REFERENCES `tab_mensagens_lotes_modelos` (`mlm_id`),
  ADD CONSTRAINT `tab_mensagens_lotes_modelos_mlt_id` FOREIGN KEY (`mlt_id`) REFERENCES `tab_mensagens_lotes_tipos` (`mlt_id`);

--
-- Limitadores para a tabela `tab_menus`
--
ALTER TABLE `tab_menus`
  ADD CONSTRAINT `tab_menus_men_pai` FOREIGN KEY (`men_pai`) REFERENCES `tab_menus` (`men_id`);

--
-- Limitadores para a tabela `tab_modulos`
--
ALTER TABLE `tab_modulos`
  ADD CONSTRAINT `tab_modulos_mos_id` FOREIGN KEY (`mos_id`) REFERENCES `tab_modulos_status` (`mos_id`);

--
-- Limitadores para a tabela `tab_operacoes`
--
ALTER TABLE `tab_operacoes`
  ADD CONSTRAINT `tab_operacoes_opl_id` FOREIGN KEY (`opl_id`) REFERENCES `tab_operacoes_lotes` (`opl_id`),
  ADD CONSTRAINT `tab_operacoes_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_operacoes_contatos`
--
ALTER TABLE `tab_operacoes_contatos`
  ADD CONSTRAINT `tab_operacoes_contatos_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_operacoes_contatos_occ_id` FOREIGN KEY (`occ_id`) REFERENCES `tab_operacoes_contatos_categorias` (`occ_id`),
  ADD CONSTRAINT `tab_operacoes_contatos_ope_id` FOREIGN KEY (`ope_id`) REFERENCES `tab_operacoes` (`ope_id`),
  ADD CONSTRAINT `tab_operacoes_contatos_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_operacoes_contatos_categorias`
--
ALTER TABLE `tab_operacoes_contatos_categorias`
  ADD CONSTRAINT `tab_operacoes_contatos_categorias_oli_id` FOREIGN KEY (`oli_id`) REFERENCES `tab_operacoes_lotes_sistemas` (`oli_id`);

--
-- Limitadores para a tabela `tab_operacoes_lotes`
--
ALTER TABLE `tab_operacoes_lotes`
  ADD CONSTRAINT `tab_operacoes_lotes_oli_id` FOREIGN KEY (`oli_id`) REFERENCES `tab_operacoes_lotes_sistemas` (`oli_id`),
  ADD CONSTRAINT `tab_operacoes_lotes_ols_id` FOREIGN KEY (`ols_id`) REFERENCES `tab_operacoes_lotes_status` (`ols_id`);

--
-- Limitadores para a tabela `tab_operacoes_lotes_filtros`
--
ALTER TABLE `tab_operacoes_lotes_filtros`
  ADD CONSTRAINT `tab_operacoes_lotes_filtros_oli_id` FOREIGN KEY (`oli_id`) REFERENCES `tab_operacoes_lotes_sistemas` (`oli_id`);

--
-- Limitadores para a tabela `tab_operacoes_lotes_responsabilidades`
--
ALTER TABLE `tab_operacoes_lotes_responsabilidades`
  ADD CONSTRAINT `tab_operacoes_lotes_responsabilidades_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_operacoes_lotes_responsabilidades_opl_id` FOREIGN KEY (`opl_id`) REFERENCES `tab_operacoes_lotes` (`opl_id`),
  ADD CONSTRAINT `tab_operacoes_lotes_responsabilidades_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_operacoes_scripts`
--
ALTER TABLE `tab_operacoes_scripts`
  ADD CONSTRAINT `tab_operacoes_scripts_oli_id` FOREIGN KEY (`oli_id`) REFERENCES `tab_operacoes_lotes_sistemas` (`oli_id`),
  ADD CONSTRAINT `tab_operacoes_scripts_osa_id` FOREIGN KEY (`osa_id`) REFERENCES `tab_operacoes_scripts_status` (`osa_id`),
  ADD CONSTRAINT `tab_operacoes_scripts_osf_id` FOREIGN KEY (`osf_id`) REFERENCES `tab_operacoes_scripts_formatos` (`osf_id`),
  ADD CONSTRAINT `tab_operacoes_scripts_ost_id` FOREIGN KEY (`ost_id`) REFERENCES `tab_operacoes_scripts_tipos` (`ost_id`);

--
-- Limitadores para a tabela `tab_organizacao_funcoes`
--
ALTER TABLE `tab_organizacao_funcoes`
  ADD CONSTRAINT `tab_organizacao_funcoes_ofs_id` FOREIGN KEY (`ofs_id`) REFERENCES `tab_organizacao_funcoes_status` (`ofs_id`);

--
-- Limitadores para a tabela `tab_organizacao_setores`
--
ALTER TABLE `tab_organizacao_setores`
  ADD CONSTRAINT `tab_organizacao_setores_ors_pai` FOREIGN KEY (`ors_pai`) REFERENCES `tab_organizacao_setores` (`ors_id`),
  ADD CONSTRAINT `tab_organizacao_setores_oss_id` FOREIGN KEY (`oss_id`) REFERENCES `tab_organizacao_setores_status` (`oss_id`);

--
-- Limitadores para a tabela `tab_pesquisas`
--
ALTER TABLE `tab_pesquisas`
  ADD CONSTRAINT `tab_pesquisas_pet_id` FOREIGN KEY (`pet_id`) REFERENCES `tab_pesquisas_status` (`pet_id`);

--
-- Limitadores para a tabela `tab_pesquisas_aplicacoes`
--
ALTER TABLE `tab_pesquisas_aplicacoes`
  ADD CONSTRAINT `tab_pesquisas_aplicacoes_cha_id` FOREIGN KEY (`cha_id`) REFERENCES `tab_chamados` (`cha_id`),
  ADD CONSTRAINT `tab_pesquisas_aplicacoes_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_pesquisas_aplicacoes_cor_id` FOREIGN KEY (`cor_id`) REFERENCES `tab_contratos` (`cor_id`),
  ADD CONSTRAINT `tab_pesquisas_aplicacoes_pes_id` FOREIGN KEY (`pes_id`) REFERENCES `tab_pesquisas` (`pes_id`),
  ADD CONSTRAINT `tab_pesquisas_aplicacoes_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_pesquisas_aplicacoes_respostas`
--
ALTER TABLE `tab_pesquisas_aplicacoes_respostas`
  ADD CONSTRAINT `tab_pesquisas_aplicacoes_respostas_pea_id` FOREIGN KEY (`pea_id`) REFERENCES `tab_pesquisas_aplicacoes` (`pea_id`),
  ADD CONSTRAINT `tab_pesquisas_aplicacoes_respostas_peq_id` FOREIGN KEY (`peq_id`) REFERENCES `tab_pesquisas_questoes` (`peq_id`),
  ADD CONSTRAINT `tab_pesquisas_aplicacoes_respostas_pqa_id` FOREIGN KEY (`pqa_id`) REFERENCES `tab_pesquisas_questoes_alternativas` (`pqa_id`);

--
-- Limitadores para a tabela `tab_pesquisas_questoes`
--
ALTER TABLE `tab_pesquisas_questoes`
  ADD CONSTRAINT `tab_pesquisas_questoes_pes_id` FOREIGN KEY (`pes_id`) REFERENCES `tab_pesquisas` (`pes_id`),
  ADD CONSTRAINT `tab_pesquisas_questoes_pqt_id` FOREIGN KEY (`pqt_id`) REFERENCES `tab_pesquisas_questoes_tipos` (`pqt_id`),
  ADD CONSTRAINT `tab_pesquisas_questoes_pvt_id` FOREIGN KEY (`pvt_id`) REFERENCES `tab_pesquisas_variaveis_tipos` (`pvt_id`);

--
-- Limitadores para a tabela `tab_pesquisas_questoes_alternativas`
--
ALTER TABLE `tab_pesquisas_questoes_alternativas`
  ADD CONSTRAINT `tab_pesquisas_questoes_alternativas_peq_id` FOREIGN KEY (`peq_id`) REFERENCES `tab_pesquisas_questoes` (`peq_id`);

--
-- Limitadores para a tabela `tab_placares`
--
ALTER TABLE `tab_placares`
  ADD CONSTRAINT `tab_placares_plb_id` FOREIGN KEY (`plb_id`) REFERENCES `tab_placares_abas` (`plb_id`);

--
-- Limitadores para a tabela `tab_planos`
--
ALTER TABLE `tab_planos`
  ADD CONSTRAINT `tab_planos_con_id` FOREIGN KEY (`con_id`) REFERENCES `tab_condominios` (`con_id`),
  ADD CONSTRAINT `tab_planos_pli_id` FOREIGN KEY (`pli_id`) REFERENCES `tab_planos_sistemas` (`pli_id`),
  ADD CONSTRAINT `tab_planos_pls_id` FOREIGN KEY (`pls_id`) REFERENCES `tab_planos_servicos` (`pls_id`),
  ADD CONSTRAINT `tab_planos_plt_id` FOREIGN KEY (`plt_id`) REFERENCES `tab_planos_tipos` (`plt_id`),
  ADD CONSTRAINT `tab_planos_plu_id` FOREIGN KEY (`plu_id`) REFERENCES `tab_planos_status` (`plu_id`),
  ADD CONSTRAINT `tab_planos_sac_id` FOREIGN KEY (`sac_id`) REFERENCES `tab_servicos_ajustes_categorias` (`sac_id`);

--
-- Limitadores para a tabela `tab_planos_equipamentos`
--
ALTER TABLE `tab_planos_equipamentos`
  ADD CONSTRAINT `tab_planos_equipamentos_equ_id` FOREIGN KEY (`equ_id`) REFERENCES `tab_equipamentos` (`equ_id`),
  ADD CONSTRAINT `tab_planos_equipamentos_pla_id` FOREIGN KEY (`pla_id`) REFERENCES `tab_planos` (`pla_id`);

--
-- Limitadores para a tabela `tab_planos_vinculos`
--
ALTER TABLE `tab_planos_vinculos`
  ADD CONSTRAINT `tab_planos_vinculos_pla_filho` FOREIGN KEY (`pla_filho`) REFERENCES `tab_planos` (`pla_id`),
  ADD CONSTRAINT `tab_planos_vinculos_pla_id` FOREIGN KEY (`pla_id`) REFERENCES `tab_planos` (`pla_id`),
  ADD CONSTRAINT `tab_planos_vinculos_pva_id` FOREIGN KEY (`pva_id`) REFERENCES `tab_planos_vinculos_aplicacoes` (`pva_id`),
  ADD CONSTRAINT `tab_planos_vinculos_pvt_id` FOREIGN KEY (`pvt_id`) REFERENCES `tab_planos_vinculos_tipos` (`pvt_id`);

--
-- Limitadores para a tabela `tab_recuperacao_contatos`
--
ALTER TABLE `tab_recuperacao_contatos`
  ADD CONSTRAINT `tab_recuperacao_contatos_coa_id` FOREIGN KEY (`coa_id`) REFERENCES `tab_cobrancas` (`coa_id`),
  ADD CONSTRAINT `tab_recuperacao_contatos_rct_id` FOREIGN KEY (`rct_id`) REFERENCES `tab_recuperacao_contatos_tipos` (`rct_id`),
  ADD CONSTRAINT `tab_recuperacao_contatos_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_redes`
--
ALTER TABLE `tab_redes`
  ADD CONSTRAINT `tab_redes_red_pai` FOREIGN KEY (`red_pai`) REFERENCES `tab_redes` (`red_id`),
  ADD CONSTRAINT `tab_redes_sei_id` FOREIGN KEY (`sei_id`) REFERENCES `tab_servidores_interfaces` (`sei_id`),
  ADD CONSTRAINT `tab_redes_ser_id` FOREIGN KEY (`ser_id`) REFERENCES `tab_servidores` (`ser_id`);

--
-- Limitadores para a tabela `tab_servicos_ajustes`
--
ALTER TABLE `tab_servicos_ajustes`
  ADD CONSTRAINT `tab_servicos_ajustes_ajf_id` FOREIGN KEY (`ajf_id`) REFERENCES `tab_ajustes_financeiros` (`ajf_id`),
  ADD CONSTRAINT `tab_servicos_ajustes_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_servicos_ajustes_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_servicos_ajustes_sac_id` FOREIGN KEY (`sac_id`) REFERENCES `tab_servicos_ajustes_categorias` (`sac_id`),
  ADD CONSTRAINT `tab_servicos_ajustes_saj_pai` FOREIGN KEY (`saj_pai`) REFERENCES `tab_servicos_ajustes` (`saj_id`),
  ADD CONSTRAINT `tab_servicos_ajustes_sas_id` FOREIGN KEY (`sas_id`) REFERENCES `tab_servicos_ajustes_status` (`sas_id`);

--
-- Limitadores para a tabela `tab_servicos_ajustes_comentarios`
--
ALTER TABLE `tab_servicos_ajustes_comentarios`
  ADD CONSTRAINT `tab_servicos_ajustes_comentarios_saj_id` FOREIGN KEY (`saj_id`) REFERENCES `tab_servicos_ajustes` (`saj_id`),
  ADD CONSTRAINT `tab_servicos_ajustes_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_servicos_ajustes_regras`
--
ALTER TABLE `tab_servicos_ajustes_regras`
  ADD CONSTRAINT `tab_servicos_ajustes_regras_saj_id` FOREIGN KEY (`saj_id`) REFERENCES `tab_servicos_ajustes` (`saj_id`),
  ADD CONSTRAINT `tab_servicos_ajustes_regras_sat_id` FOREIGN KEY (`sat_id`) REFERENCES `tab_servicos_ajustes_tipos` (`sat_id`);

--
-- Limitadores para a tabela `tab_servidores`
--
ALTER TABLE `tab_servidores`
  ADD CONSTRAINT `tab_servidores_cha_id` FOREIGN KEY (`cha_id`) REFERENCES `tab_chamados` (`cha_id`),
  ADD CONSTRAINT `tab_servidores_sei_id` FOREIGN KEY (`sei_id`) REFERENCES `tab_servidores_interfaces` (`sei_id`),
  ADD CONSTRAINT `tab_servidores_sep_id` FOREIGN KEY (`sep_id`) REFERENCES `tab_servidores_tipos` (`sep_id`),
  ADD CONSTRAINT `tab_servidores_set_id` FOREIGN KEY (`set_id`) REFERENCES `tab_servidores_status` (`set_id`),
  ADD CONSTRAINT `tab_servidores_seu_id` FOREIGN KEY (`seu_id`) REFERENCES `tab_servidores_situacoes` (`seu_id`),
  ADD CONSTRAINT `tab_servidores_sso_id` FOREIGN KEY (`sso_id`) REFERENCES `tab_servidores_sistemas_operacionais` (`sso_id`);

--
-- Limitadores para a tabela `tab_servidores_interfaces`
--
ALTER TABLE `tab_servidores_interfaces`
  ADD CONSTRAINT `tab_servidores_interfaces_sei_pai` FOREIGN KEY (`sei_pai`) REFERENCES `tab_servidores_interfaces` (`sei_id`),
  ADD CONSTRAINT `tab_servidores_interfaces_ser_id` FOREIGN KEY (`ser_id`) REFERENCES `tab_servidores` (`ser_id`),
  ADD CONSTRAINT `tab_servidores_interfaces_sia_id` FOREIGN KEY (`sia_id`) REFERENCES `tab_servidores_interfaces_status` (`sia_id`),
  ADD CONSTRAINT `tab_servidores_interfaces_sip_id` FOREIGN KEY (`sip_id`) REFERENCES `tab_servidores_interfaces_padroes` (`sip_id`),
  ADD CONSTRAINT `tab_servidores_interfaces_sit_id` FOREIGN KEY (`sit_id`) REFERENCES `tab_servidores_interfaces_tipos` (`sit_id`);

--
-- Limitadores para a tabela `tab_servidores_links`
--
ALTER TABLE `tab_servidores_links`
  ADD CONSTRAINT `tab_servidores_links_sei_id` FOREIGN KEY (`sei_id`) REFERENCES `tab_servidores_interfaces` (`sei_id`),
  ADD CONSTRAINT `tab_servidores_links_sls_id` FOREIGN KEY (`sls_id`) REFERENCES `tab_servidores_links_status` (`sls_id`);

--
-- Limitadores para a tabela `tab_servidores_servicos`
--
ALTER TABLE `tab_servidores_servicos`
  ADD CONSTRAINT `tab_servidores_servicos_sei_id` FOREIGN KEY (`sei_id`) REFERENCES `tab_servidores_interfaces` (`sei_id`),
  ADD CONSTRAINT `tab_servidores_servicos_sss_id` FOREIGN KEY (`sss_id`) REFERENCES `tab_servidores_servicos_status` (`sss_id`),
  ADD CONSTRAINT `tab_servidores_servicos_sst_id` FOREIGN KEY (`sst_id`) REFERENCES `tab_servidores_servicos_tipos` (`sst_id`);

--
-- Limitadores para a tabela `tab_servidores_servicos_tipos`
--
ALTER TABLE `tab_servidores_servicos_tipos`
  ADD CONSTRAINT `tab_servidores_servicos_tipos_sit_id` FOREIGN KEY (`sit_id`) REFERENCES `tab_servidores_interfaces_tipos` (`sit_id`);

--
-- Limitadores para a tabela `tab_servidores_ssids`
--
ALTER TABLE `tab_servidores_ssids`
  ADD CONSTRAINT `tab_servidores_ssids_sei_id` FOREIGN KEY (`sei_id`) REFERENCES `tab_servidores_interfaces` (`sei_id`),
  ADD CONSTRAINT `tab_servidores_ssids_sst_id` FOREIGN KEY (`sst_id`) REFERENCES `tab_servidores_ssids_tipos` (`sst_id`);

--
-- Limitadores para a tabela `tab_sessoes`
--
ALTER TABLE `tab_sessoes`
  ADD CONSTRAINT `tab_sessoes_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_tarefas`
--
ALTER TABLE `tab_tarefas`
  ADD CONSTRAINT `tab_tarefas_orf_id` FOREIGN KEY (`orf_id`) REFERENCES `tab_organizacao_funcoes` (`orf_id`),
  ADD CONSTRAINT `tab_tarefas_ors_id` FOREIGN KEY (`ors_id`) REFERENCES `tab_organizacao_setores` (`ors_id`),
  ADD CONSTRAINT `tab_tarefas_tas_id` FOREIGN KEY (`tas_id`) REFERENCES `tab_tarefas_status` (`tas_id`),
  ADD CONSTRAINT `tab_tarefas_tat_id` FOREIGN KEY (`tat_id`) REFERENCES `tab_tarefas_tipos` (`tat_id`),
  ADD CONSTRAINT `tab_tarefas_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_tarefas_etapas`
--
ALTER TABLE `tab_tarefas_etapas`
  ADD CONSTRAINT `tab_tarefas_etapas_orf_id` FOREIGN KEY (`orf_id`) REFERENCES `tab_organizacao_funcoes` (`orf_id`),
  ADD CONSTRAINT `tab_tarefas_etapas_ors_id` FOREIGN KEY (`ors_id`) REFERENCES `tab_organizacao_setores` (`ors_id`),
  ADD CONSTRAINT `tab_tarefas_etapas_tar_id` FOREIGN KEY (`tar_id`) REFERENCES `tab_tarefas` (`tar_id`),
  ADD CONSTRAINT `tab_tarefas_etapas_tes_id` FOREIGN KEY (`tes_id`) REFERENCES `tab_tarefas_etapas_status` (`tes_id`),
  ADD CONSTRAINT `tab_tarefas_etapas_tet_id` FOREIGN KEY (`tet_id`) REFERENCES `tab_tarefas_etapas_tipos` (`tet_id`),
  ADD CONSTRAINT `tab_tarefas_etapas_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_tarefas_tipos`
--
ALTER TABLE `tab_tarefas_tipos`
  ADD CONSTRAINT `tab_tarefas_tipos_ors_id` FOREIGN KEY (`ors_id`) REFERENCES `tab_organizacao_setores` (`ors_id`);

--
-- Limitadores para a tabela `tab_usuarios`
--
ALTER TABLE `tab_usuarios`
  ADD CONSTRAINT `tab_usuarios_bai_id` FOREIGN KEY (`bai_id`) REFERENCES `tab_bairros` (`bai_id`),
  ADD CONSTRAINT `tab_usuarios_cep_id` FOREIGN KEY (`cep_id`) REFERENCES `tab_ceps` (`cep_id`),
  ADD CONSTRAINT `tab_usuarios_cid_id` FOREIGN KEY (`cid_id`) REFERENCES `tab_cidades` (`cid_id`),
  ADD CONSTRAINT `tab_usuarios_clo_id` FOREIGN KEY (`clo_id`) REFERENCES `tab_clientes_origens` (`clo_id`),
  ADD CONSTRAINT `tab_usuarios_est_id` FOREIGN KEY (`est_id`) REFERENCES `tab_estados` (`est_id`),
  ADD CONSTRAINT `tab_usuarios_log_id` FOREIGN KEY (`log_id`) REFERENCES `tab_logradouros` (`log_id`),
  ADD CONSTRAINT `tab_usuarios_uss_id` FOREIGN KEY (`uss_id`) REFERENCES `tab_usuarios_status` (`uss_id`);

--
-- Limitadores para a tabela `tab_vendas`
--
ALTER TABLE `tab_vendas`
  ADD CONSTRAINT `tab_vendas_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`),
  ADD CONSTRAINT `tab_vendas_cor_id` FOREIGN KEY (`cor_id`) REFERENCES `tab_contratos` (`cor_id`),
  ADD CONSTRAINT `tab_vendas_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`);

--
-- Limitadores para a tabela `tab_vendas_comentarios`
--
ALTER TABLE `tab_vendas_comentarios`
  ADD CONSTRAINT `tab_vendas_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`),
  ADD CONSTRAINT `tab_vendas_comentarios_ven_id` FOREIGN KEY (`ven_id`) REFERENCES `tab_vendas` (`ven_id`);

--
-- Limitadores para a tabela `tab_vendas_comissoes`
--
ALTER TABLE `tab_vendas_comissoes`
  ADD CONSTRAINT `tab_vendas_comissoes_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`),
  ADD CONSTRAINT `tab_vendas_comissoes_vct_id` FOREIGN KEY (`vct_id`) REFERENCES `tab_vendas_comissoes_tipos` (`vct_id`),
  ADD CONSTRAINT `tab_vendas_comissoes_ven_id` FOREIGN KEY (`ven_id`) REFERENCES `tab_vendas` (`ven_id`);

--
-- Limitadores para a tabela `tab_vendas_comissoes_comentarios`
--
ALTER TABLE `tab_vendas_comissoes_comentarios`
  ADD CONSTRAINT `tab_vendas_comissoes_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`),
  ADD CONSTRAINT `tab_vendas_comissoes_comentarios_vec_id` FOREIGN KEY (`vec_id`) REFERENCES `tab_vendas_comissoes` (`vec_id`);

--
-- Limitadores para a tabela `tab_visitas`
--
ALTER TABLE `tab_visitas`
  ADD CONSTRAINT `tab_visitas_cha_id` FOREIGN KEY (`cha_id`) REFERENCES `tab_chamados` (`cha_id`),
  ADD CONSTRAINT `tab_visitas_cli_id` FOREIGN KEY (`cli_id`) REFERENCES `tab_clientes` (`cli_id`),
  ADD CONSTRAINT `tab_visitas_con_id` FOREIGN KEY (`con_id`) REFERENCES `tab_condominios` (`con_id`),
  ADD CONSTRAINT `tab_visitas_cor_id` FOREIGN KEY (`cor_id`) REFERENCES `tab_contratos` (`cor_id`),
  ADD CONSTRAINT `tab_visitas_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`),
  ADD CONSTRAINT `tab_visitas_via_id` FOREIGN KEY (`via_id`) REFERENCES `tab_visitas_status` (`via_id`),
  ADD CONSTRAINT `tab_visitas_vip_id` FOREIGN KEY (`vip_id`) REFERENCES `tab_visitas_periodos` (`vip_id`),
  ADD CONSTRAINT `tab_visitas_vit_id` FOREIGN KEY (`vit_id`) REFERENCES `tab_visitas_tipos` (`vit_id`);

--
-- Limitadores para a tabela `tab_visitas_comentarios`
--
ALTER TABLE `tab_visitas_comentarios`
  ADD CONSTRAINT `tab_visitas_comentarios_usu_id` FOREIGN KEY (`usu_id`) REFERENCES `tab_usuarios` (`usu_id`),
  ADD CONSTRAINT `tab_visitas_comentarios_vcc_id` FOREIGN KEY (`vcc_id`) REFERENCES `tab_visitas_comentarios_categorias` (`vcc_id`),
  ADD CONSTRAINT `tab_visitas_comentarios_vis_id` FOREIGN KEY (`vis_id`) REFERENCES `tab_visitas` (`vis_id`);

--
-- Limitadores para a tabela `tab_visitas_cotas`
--
ALTER TABLE `tab_visitas_cotas`
  ADD CONSTRAINT `tab_viistas_cotas_vit_id` FOREIGN KEY (`vit_id`) REFERENCES `tab_visitas_tipos` (`vit_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
