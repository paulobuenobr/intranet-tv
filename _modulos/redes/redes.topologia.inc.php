<script type="text/javascript" language="JavaScript1.2">

	var ie = document.all;
	var nn6 = document.getElementById && !document.all;
	var isdrag = false;
	var x, y;
	var dobj;
	
	function movemouse(e) {
	  if (isdrag) {
	    dobj.style.left = nn6 ? tx + e.clientX - x : tx + event.clientX - x;
	    dobj.style.top  = nn6 ? ty + e.clientY - y : ty + event.clientY - y;
	    return false;
	  }
	}
	
	function mousedown(e) {
	  var fobj       = nn6 ? e.target : event.srcElement;
	  var topelement = nn6 ? "HTML" : "BODY";
	  if (fobj.id != "dragarea") return;
	  fobj.style.cursor = "move";
	  while (fobj.tagName != topelement && fobj.className != "dragme")  {
	    fobj = nn6 ? fobj.parentNode : fobj.parentElement;
	  }
	  if (fobj.className == "dragme") {
	    isdrag = true;
	    dobj = fobj;
	    tx = parseInt(dobj.style.left + 0);
	    ty = parseInt(dobj.style.top + 0);
	    x = nn6 ? e.clientX : event.clientX;
	    y = nn6 ? e.clientY : event.clientY;
	    document.onmousemove = movemouse;
	    return false;
	  }
	}
	
	function mouseup(e) {
	  var fobj = nn6 ? e.target : event.srcElement;
	  if (fobj.id == "dragarea") fobj.style.cursor = "";
		isdrag = false;
	}
	
	document.onmousedown = mousedown;
	document.onmouseup = mouseup;

</script>

<script type="text/javascript" src="rds.js.php?m=redes"></script>
<div id="carregando" class="txtBranco11" style="position:absolute;display:none;z-index:1;top:1px;margin:1px;padding-left:4px;padding-right:4px;padding-top:2px;padding-bottom:2px;border:1px solid #FFFFFF;background-color:#CC0505">Carregando...</div>
<div id="bloqueio" class="txtBranco11" style="position:absolute;display:none;z-index:2;top:0px;left:0px;cursor:wait"></div>
<div id="container_servidores" style="width:300px;padding-left:2px;padding-right:2px;padding-top:4px;padding-bottom:4px;background-color:#E8E8E8;border:solid 1px #252525;position:absolute;z-index:1;top:3px;display:none" onmouseover="divServidoresOver = true" onmouseout="divServidoresOver = false">
  <table border="0" cellspacing="0" cellpadding="0" width="296" height="100">
    <tr>
      <td colspan="3"><select id="lista_servidores" size="8" class="textBrancoAzulEscuro11" style="width:296px" onclick="if (this.value) {document.getElementById('associar_servidor').disabled = false; document.getElementById('auto_distribuir_servidor').disabled = false; }" ondblclick="defineServidor(this.tag, this.value)"></select></td>
    </tr>
    <tr>
      <td colspan="3" height="2"></td>
    </tr>
    <tr>
      <td width="147"><input type="button" class="buttonAzulEscuroBranco11" style="width:100%" id="associar_servidor" value="Associar" onClick="defineServidor(document.getElementById('lista_servidores').tag, document.getElementById('lista_servidores').value)"></td>
      <td width="2"></td>
      <td width="147"><input type="button" class="buttonAzulEscuroBranco11" style="width:100%" id="auto_distribuir_servidor" value="Auto Distribuir" onClick="autoDistribuir(document.getElementById('lista_servidores').tag, document.getElementById('lista_servidores').value)"></td>
    </tr>
  </table>
</div>
<div id="container_interfaces" style="width:140px;height:100px;border:solid 1px #FFFFFF;position:absolute;z-index:1;top:3px;display:none" onmouseover="divInterfacesOver = true" onmouseout="divInterfacesOver = false">
	<select id="lista_interfaces" size="7" class="textBrancoAzulEscuro11" style="width:100%;" onblur="this.parentNode.style.display = 'none'" ondblclick="defineInterface(this.tag, this.value)"></select>
</div>
<div id="container_informacoes" class="dragme" style="width:340px;border:solid 1px #FFFFFF;background-color:#252525;position:absolute;z-index:1;top:3px;display:none;padding:2px" onmouseover="divInformacoesOver = true" onmouseout="divInformacoesOver = false"></div>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td>
	    <table border="0" cellspacing="1" cellpadding="3" width="100%">
	      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Topologia&nbsp;</strong></td></tr>
	    </table>
    </td>
  </tr>
	<tr>
    <td>
    	<table width="100%" border="0" cellspacing="1" cellpadding="3">
      	<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      	  <td style="padding:10px" id="topologia">[topologia]</td>
      	</tr>
      </table>
	</tr>
</table>
<script type="text/javascript">
	var divServidoresOver  = false;
	var divInterfacesOver  = false;
	var divInformacoesOver = false;
	document.onscroll = function() {
  	if (document.getElementById('carregando')) document.getElementById('carregando').style.top = document.body.scrollTop + 2;
	}
	window.onresize = function() {
 		if (document.getElementById('carregando')) document.getElementById('carregando').style.left = document.body.clientWidth - 80;
	}
	document.onclick = function() {
		if (!divServidoresOver) {
			document.getElementById('container_servidores').style.display = 'none';
		}
		if (!divInterfacesOver) {
			document.getElementById('container_interfaces').style.display = 'none';
		}
		if (!divInformacoesOver) {
			document.getElementById('container_informacoes').style.display = 'none';
		}
	}
</script>
