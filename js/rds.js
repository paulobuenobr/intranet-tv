function RDSCarregar (nome, url) {
  if (document.getElementById('rds_' + nome) != null) {
	  document.getElementById('rds_' + nome).innerHTML = "";
	}	else {
    var div = document.createElement('div');
    div.id = 'rds_' + nome;
    div.style.visibility = "hidden";
	  document.getElementById('rds').appendChild(div);
	}
  var script = document.createElement('script');
	data = new Date();
  url += (url.indexOf('?') == -1) ? '?' : '&';
	script.src = url + 'hash=' + data.getTime() + Math.floor(1000000 * Math.random() * Math.random());
  document.getElementById('rds_' + nome).appendChild(script);
}