<div id="painel_visita" style="width:746px;position:absolute;z-index:1;top:3px;display:none">
  <div style="color:#ffffff;font-family:Arial;font-size:10px;width:13px;height:13px;position:absolute;z-index:1;top:4px;left:723px;cursor:pointer;border:1px solid #ffffff" onclick="definirVisita(visita_ativa, visita_ativa_cliente_tipo)" title="Fechar">X</div>
  <table width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
      <td width="740"><div style="border:1px solid #ffffff">
        <table width="100%" cellspacing="1" cellpadding="3" border="0" bgcolor="#505050">
          <tr id="tabela_painel_visita_cabecalho" bgcolor="#505050"></tr>
          <tr></tr>
          <tr id="tabela_painel_visita" bgcolor="#ffcc66"></tr>
        </table></div>
      </td>
      <td width="3" height="100%">
        <table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
          <tr><td height="3"></td></tr>
          <tr><td bgcolor="#111111"></td></tr>
        </table>
      </td>
    </tr>
    <tr>
      <td height="3" align="right">
        <table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
          <tr><td height="3" width="3"></td>
            <td width="737" bgcolor="#111111"></td>
          </tr>
        </table>
      </td>
      <td bgcolor="#111111"></td>
    </tr>
  </table>
</div>

<script type="text/javascript">

  var agenda_mes = '[agenda_mes]';
  var agenda_ano = '[agenda_ano]';
  var visita_ativa = '';
  var visita_ativa_cliente_tipo = '';
  
  function atualizaPainelVisitas() {
    var painel = document.getElementById('painel_visita');
    var root = document.compatMode == "CSS1Compat" ? document.documentElement : document.body;
    painel.style.left = root.scrollLeft + (document.body.clientWidth - painel.scrollWidth - 8) + "px";
    painel.style.top = root.scrollTop + (document.body.clientHeight - painel.scrollHeight - 8) + "px";
  }
  
  var ns = (navigator.appName.indexOf("Netscape") != -1);
  var d = document;
  var px = document.layers ? "" : "px";
  var vTimer;
  
  function JSFX_FloatDiv(id) {
  	var el = d.getElementById ? d.getElementById(id) : d.all ? d.all[id] : d.layers[id];
    var sx = -(el.clientWidth + 25);
    var sy = 10;
  	window[id + "_obj"] = el;
  	if (d.layers) el.style = el;
  	var pos = findPos(el);
  	el.cx = pos[0];
  	el.cy = pos[1];
  	el.sx = sx;
  	el.sy = sy;
  	el.sP = function(x, y) {
  	  this.style.left = x + px;
  	  this.style.top = y + px;
  	}
  	clearTimeout(vTimer);
  	el.flt = function() {
  		var pX, pY;
  		pX = (this.sx >= 0) ? 0 : ns ? innerWidth : document.documentElement && document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body.clientWidth;
  		pY = ns ? pageYOffset : document.documentElement && document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop;
  		if (this.sy < 0) pY += ns ? innerHeight : document.documentElement && document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
  		this.cx += (pX + this.sx - this.cx) / 8;
  		this.cy += (pY + this.sy - this.cy) / 8;
  		this.sP(this.cx, this.cy);
  		vTimer = setTimeout(this.id + "_obj.flt()", 40);
  	}
  	return el;
  }

  function findPos(obj) {
  	var curleft = curtop = 0;
  	if (obj.offsetParent) {
  		curleft = obj.offsetLeft;
  		curtop = obj.offsetTop;
  		while (obj = obj.offsetParent) {
  			curleft += obj.offsetLeft;
  			curtop += obj.offsetTop;
  		}
  	}
  	return [curleft, curtop];
  }
  
</script>

<script type="text/javascript" src="rds.js.php?m=visitas"></script>
<script type="text/javascript" src="rds.js.php?m=clientes.visitas"></script>
<script type="text/javascript" src="rds.js.php?m=contratos"></script>

<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td height="5"></td>
  </tr>
  <tr align="center" class="txtBranco11">
    <td bgcolor="#828282"><b>Agenda de Migrações <span id="visitas_agenda_periodo">[visitas_agenda_periodo]</span></b></td>
  </tr>
</table>

<span class="txtAzulEscuro11" id="visitas_agenda">[visitas_agenda]</span>

<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td height="5"></td>
  </tr>
  <tr align="center" class="txtBranco11">
    <td bgcolor="#828282"><b>Visitas Agendadas (Hoje)</b>: <span id="visitas_hoje_casas">[visitas_hoje_casas]</span> casa(s), <span id="visitas_hoje_condominios">[visitas_hoje_condominios]</span> condominio(s)</td>
  </tr>
</table>

<span class="txtAzulEscuro11" id="visitas_hoje">[visitas_hoje]</span>

<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td height="5"></td>
  </tr>
  <tr align="center" class="txtBranco11">
    <td bgcolor="#828282"><b>Visitas Agendadas (Próximos dias)</b>: <span id="visitas_proximos_dias_casas">[visitas_proximos_dias_casas]</span> casa(s), <span id="visitas_proximos_dias_condominios">[visitas_proximos_dias_condominios]</span> condominio(s)</td>
  </tr>
</table>

<span class="txtAzulEscuro11" id="visitas_proximos_dias">[visitas_proximos_dias]</span>

<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td height="5"></td>
  </tr>
  <tr align="center" class="txtBranco11">
    <td bgcolor="#828282"><b>Visitas Agendadas (Pendentes)</b>: <span id="visitas_pendentes_casas">[visitas_pendentes_casas]</span> casa(s), <span id="visitas_pendentes_condominios">[visitas_pendentes_condominios]</span> condominio(s)</td>
  </tr>
</table>


<span class="txtAzulEscuro11" id="visitas_pendentes">[visitas_pendentes]</span>

<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td height="5"></td>
  </tr>
  <tr align="center" class="txtBranco11">
    <td bgcolor="#828282"><b>Visitas Aguardando Agendamento</b>: <span id="visitas_desagendadas_casas">[visitas_desagendadas_casas]</span> casa(s), <span id="visitas_desagendadas_condominios">[visitas_desagendadas_condominios]</span> condominio(s)</td>
  </tr>
</table>

<span class="txtAzulEscuro11" id="visitas_desagendadas">[visitas_desagendadas]</span>
