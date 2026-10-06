/**************************************************************************/
window.onresize = function (event)
{
	$('#tablestatusfaturas').bootstrapTable('resetView');
};
/**************************************************************************/
$(document).ready(function ()
{
	var max = 18;
	var max_datanasc = 10;
	var jsonResult = "";
	var $table = $("#tablestatusfaturas");
	var $rowLength = "";


	$('#cpfcnpj').focus();

	$('#tablestatusfaturas').bootstrapTable('resetView');
	$("#resulttablefaturas").hide();
	//$("#nomecliente").hide();
	$("#formGroupInputEndereco").hide();
	
	$('#tablestatusfaturas').bootstrapTable({}).on('all.bs.table', function (e, name, args)
	{
		console.log('Event:', name, ', data:', args);
	})
	.on('click-row.bs.table', function (e, row, $element)
	{
		console.log('Event: click-row.bs.table, data: ' + JSON.stringify(row));
	})
	.on('dbl-click-row.bs.table', function (e, row, $element)
	{
		console.log('Event: dbl-click-row.bs.table, data: ' + JSON.stringify(row));
	})
	.on('sort.bs.table', function (e, name, order)
	{
		console.log('Event: sort.bs.table, data: ' + name + ', ' + order);
	})
	.on('check.bs.table', function (e, row)
	{
		console.log('Event: check.bs.table, data: ' + JSON.stringify(row));
	})
	.on('uncheck.bs.table', function (e, row)
	{
		console.log('Event: uncheck.bs.table, data: ' + JSON.stringify(row));
	})
	.on('check-all.bs.table', function (e)
	{
		console.log('Event: check-all.bs.table');
	})
	.on('uncheck-all.bs.table', function (e)
	{
		console.log('Event: uncheck-all.bs.table');
	})
	.on('load-success.bs.table', function (e, data)
	{
		console.log('Event: load-success.bs.table');
	})
	.on('load-error.bs.table', function (e, status)
	{
		console.log('Event: load-error.bs.table, data: ' + status);
	})
	.on('column-switch.bs.table', function (e, field, checked)
	{
		console.log('Event: column-switch.bs.table, data: ' + field + ', ' + checked);
	})
	.on('page-change.bs.table', function (e, size, number)
	{
		console.log('Event: page-change.bs.table, data: ' + number + ', ' + size);
	})
	.on('search.bs.table', function (e, text)
	{
		console.log('Event: search.bs.table, data: ' + text);
	});

	/**************************************************************************/
	
	flag_time = true;
	timer = '';
	
	/**************************************************************************/

	setInterval(function ()
	{
		phpJavascriptClock();
	}, 1000);

	/**************************************************************************/

	function phpJavascriptClock()
	{
		var d = new Date();
		months = new Array('Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez');
		month_array = new Array('Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
		day_array = new Array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado');
		currentYear = d.getFullYear();
		month = d.getMonth();
		var currentMonth = months[month];
		var currentMonth1 = month_array[month];
		var currentDate = d.getDate();
		currentDate = currentDate < 10 ? '0' + currentDate : currentDate;
		var day = d.getDay();
		var current_day = day_array[day];
		var hours = d.getHours();
		var minutes = d.getMinutes();
		var seconds = d.getSeconds();
		minutes = minutes < 10 ? '0' + minutes : minutes;
		seconds = seconds < 10 ? '0' + seconds : seconds;

		if (seconds % 10 == 0)
		{
			//updateStatusLines();
		}

		var strTime = hours + ':' + minutes + ':' + seconds;// + ' ' + ampm;
		timer = timer + 1000;
		document.getElementById("clock2").innerHTML = currentDate + ' de ' + currentMonth1 + ' de ' + currentYear + ' ' + strTime;
		flag_time = false;

	}

	/**************************************************************************/
	
	$("#desbloquear").click(function () {
		var $cpfcnpj 	= document.getElementById("cpfcnpj").value;
		var $datanasc 	= document.getElementById("datanasc").value;
		var $cliente_id 	= document.getElementById("cliente_id").value;

		var $cpflength = $cpfcnpj.length;
		
		if ($cpfcnpj == "")
		{
			$('#myModalLabel').html("ERRO!");
			$('#myModalText').html("Informe o número do cpf / cnpj!");
			$('#myModalStatusNotify').modal('show');
			$('#myModalStatusNotify').on('shown.bs.modal', function ()
			{
				$('#ErrorOk').focus();
			})
		}	
		else if (($cpflength < 15) && ($datanasc == ""))
		{
			$('#myModalLabel').html("ERRO!");
			$('#myModalText').html("Informe a data de nascimento!");
			$('#myModalStatusNotify').modal('show');
			$('#myModalStatusNotify').on('shown.bs.modal', function ()
			{
				$('#ErrorOk').focus();
			})
		}		
		else
		{
			$.ajax(
			{
				url: 'callback.php',
				data: ({acao: "getDesbloquear", idcliente: $cpfcnpj, cliente_id: $cliente_id, idfatura: "nada", datanasc: $datanasc}),
				type: 'POST',
				dataType: "json",
				async: false,
				success: function (oJSON)
				{

					if (oJSON.success)
					{
						//salva json
						jsonResult = oJSON;

						if(oJSON.desbloqueado == true) {
							$("#desbloquear").hide();
						} else {
							$("#desbloquear").show();
						}

						if(oJSON.jaFoiDesbloqueado == true) {
							$("#desbloquear").prop('disabled', true);
							$("#text_desbloquear").html('');
							$("#text_desbloquear").html(oJSON.txt_jaFoiDesbloqueado);
							$("#text_desbloquear").show();
						} else {
							$("#desbloquear").prop('disabled', false); 
						}

					}
					else
					{
						alert("Erro na consulta!");
					}
				},
				error: function()
				{
					alert("Erro na consulta!");
				}
			});
		}
	});
	
	/**************************************************************************/

	$("#btn_callback").click(function ()
	{		
		var $cpfcnpj 	= document.getElementById("cpfcnpj").value;
		var $datanasc 	= document.getElementById("datanasc").value;
		
		var $cpflength = $cpfcnpj.length;
		
		if ($cpfcnpj == "")
		{
			$('#myModalLabel').html("ERRO!");
			$('#myModalText').html("Informe o número do cpf / cnpj!");
			$('#myModalStatusNotify').modal('show');
			$('#myModalStatusNotify').on('shown.bs.modal', function ()
			{
				$('#ErrorOk').focus();
			})
		}		
		else if (($cpflength < 15) && ($datanasc == ""))
		{
			$('#myModalLabel').html("ERRO!");
			$('#myModalText').html("Informe a data de nascimento!");
			$('#myModalStatusNotify').modal('show');
			$('#myModalStatusNotify').on('shown.bs.modal', function ()
			{
				$('#ErrorOk').focus();
			})
		}		
		else
		{
			$.ajax(
			{
				url: 'callback.php',
				data: ({acao: "getFaturas", idcliente: $cpfcnpj, idfatura: "nada", datanasc: $datanasc}),
				type: 'POST',
				dataType: "json",
				async: false,
				success: function (oJSON)
				{
					$("#resulttablefaturas").show();
					var $dataAddresses = '';

					if (oJSON.success)
					{
						//salva json
						jsonResult = oJSON;

						if(jsonResult.clientes[0].temBloqueioAtivo == true) {
							$("#desbloquear").show();
							$("#text_desbloquear").html(jsonResult.clientes[0].txt_temBloqueioAtivo);
							$("#text_desbloquear").show();
							$("#cliente_id").val(jsonResult.clientes[0].cliente_id);
						} else {
							$("#desbloquear").hide();
							$("#text_desbloquear").hide();
							$("#text_desbloquear").html('');
						}

						if(jsonResult.clientes[0].jaFoiDesbloqueado == true) {
							$("#desbloquear").prop('disabled', true); 
							$("#text_desbloquear").html('');
							$("#text_desbloquear").html(jsonResult.clientes[0].txt_jaFoiDesbloqueado);
							$("#text_desbloquear").show();
						} else {
							$("#desbloquear").prop('disabled', false); 
						}

						$table.bootstrapTable('load', jsonResult.clientes[0].faturas);

						document.getElementById("nomecliente").innerHTML = jsonResult.nomecli;
						$("#formGroupInputEndereco").show();

						var myTable = document.getElementById('tablestatusfaturas');
						rowLength = myTable.rows.length;


						for (var j = 0; j < jsonResult.clientes.length; j++)
						{
							$dataAddresses = $dataAddresses + '<option value="' + j + '">'+ jsonResult.clientes[j].endereco + '</option>';
						}

						for(var i=1; i<rowLength; i+=1)
						{
							var row = myTable.rows[i];
							
							if ((jsonResult.clientes[0].faturas[i-1].coa_id == "0"))
							{
								row.cells[6].innerHTML = '';
							}
						}

						document.getElementById("formGroupInputEndereco").innerHTML = $dataAddresses;
						$('#tablestatusfaturas').bootstrapTable('resetView');
					}
					else
					{
						alert("Erro na consulta!");
					}	
				},
				error: function()
				{
					alert("Erro na consulta!");
				}
			});
		}
	});
	
	/**************************************************************************/

	$("#formGroupInputEndereco").change(function()
	{
		var index = document.getElementById("formGroupInputEndereco").selectedIndex;
		$table.bootstrapTable('load', jsonResult.clientes[index].faturas);
		var myTable = document.getElementById('tablestatusfaturas');
		rowLength = myTable.rows.length;

		for(var i = 1; i < rowLength; i += 1)
		{
			var row = myTable.rows[i];
			
			if ((jsonResult.clientes[index].faturas[i-1].coa_id == "0"))
			{
				row.cells[6].innerHTML = '';
			}

			if(jsonResult.clientes[index].temBloqueioAtivo == true) {
				$("#desbloquear").show();
				$("#text_desbloquear").html(jsonResult.clientes[index].txt_temBloqueioAtivo);
				$("#text_desbloquear").show();
				$("#cliente_id").val(jsonResult.clientes[index].cliente_id);
			} else {
				$("#desbloquear").hide();
				$("#text_desbloquear").hide();
				$("#text_desbloquear").html('');
			}

			if(jsonResult.clientes[index].jaFoiDesbloqueado == true) {
				$("#desbloquear").prop('disabled', true); 
				$("#text_desbloquear").html('');
				$("#text_desbloquear").html(jsonResult.clientes[index].txt_jaFoiDesbloqueado);
				$("#text_desbloquear").show();
			} else {
				$("#desbloquear").prop('disabled', false); 
				$("#text_desbloquear").html('');
				$("#text_desbloquear").html(jsonResult.clientes[index].txt_jaFoiDesbloqueado);
				$("#text_desbloquear").show();
			}
		}
	});


	/**************************************************************************/

	$("#cpfcnpj").keyup(function ()
	{
		var len_mensagem = $("#cpfcnpj").val().length;

		if ((max - len_mensagem) <= 4)
		{
			this.value = this.value.substring(0, max);

		if(len_mensagem > 14)
		{
			$("#datanasc").prop('disabled', true);
			$("#datanasc").val("");
		}
		else
		{
			$("#datanasc").prop('disabled', false);
			
		}
		}
		else
		{
		} 
	});// KeyUP cpf / cnpj

	$("#datanasc").keyup(function ()
	{
		var len_mensagem = $("#datanasc").val().length;

		if ((max_datanasc - len_mensagem) <= 0)
		{
			this.value = this.value.substring(0, max_datanasc);
		}
		else
		{
		} 
	});// KeyUP cpf / cnpj

});


function operateFormatter(value, row, index)
{
    return [
        '<a class="print" href="javascript:void(0)" title="print">',
            '<i class="glyphicon glyphicon-print"></i>',
        '</a>',
        '<a class="download ml10" href="javascript:void(0)" title="download">',
            '<i class="glyphicon glyphicon-download"></i>',
        '</a>'
    ].join('');
}


window.operateEvents = 
{
    'click .print': function (e, value, row, index) 
    {

		window.open("callback.php?acao=imprimefatura&idcliente=nada&idfatura=" + row.coa_id, "_blank");
        console.log(value, row, index);
    },
    'click .download': function (e, value, row, index)
    {
 		window.location = "callback.php?acao=downloadfatura&idcliente=nada&idfatura=" + row.coa_id;
 		console.log(value, row, index);
    }
};

/**************************************************************************/

function chama_mascara(o)
{
	if (o.value.length > 14)
		mascara(o, cnpj);
	else
		mascara(o, cpf);
}

function chama_mascara_nasc (o)
{
	//if (o.value.length > 10)
		mascara(o, datanasc);
}

function mascara(o, f) {
	v_obj = o;
	v_fun = f;
	setTimeout("execmascara()", 1);
}

function execmascara() {
	v_obj.value = v_fun(v_obj.value);
}

function telefone(v) {
	v = v.replace( /\D/g , ""); //Remove tudo o que não é dígito
	v = v.replace( /^(\d\d)(\d)/g , "($1) $2"); //Coloca parênteses em volta dos dois primeiros dígitos
	v = v.replace( /(\d{4})(\d)/ , "$1-$2"); //Coloca hífen entre o quarto e o quinto dígitos
	return v;
}

function cpf(v) {
	v = v.replace( /\D/g , ""); //Remove tudo o que não é dígito
	v = v.replace( /(\d{3})(\d)/ , "$1.$2"); //Coloca um ponto entre o terceiro e o quarto dígitos
	v = v.replace( /(\d{3})(\d)/ , "$1.$2"); //Coloca um ponto entre o terceiro e o quarto dígitos
	//de novo (para o segundo bloco de números)
	v = v.replace( /(\d{3})(\d{1,2})$/ , "$1-$2"); //Coloca um hífen entre o terceiro e o quarto dígitos
	return v;
}

function cep(v) {
	v = v.replace( /D/g , ""); //Remove tudo o que não é dígito
	v = v.replace( /^(\d{2})(\d)/ , "$1.$2");
	v = v.replace( /(\d{3})(\d{1,2})$/ , "$1-$2"); //Esse é tão fácil que não merece explicações
	return v;
}

function cnpj(v) {
	v = v.replace( /\D/g , ""); //Remove tudo o que não é dígito
	v = v.replace( /^(\d{2})(\d)/ , "$1.$2"); //Coloca ponto entre o segundo e o terceiro dígitos
	v = v.replace( /^(\d{2})\.(\d{3})(\d)/ , "$1.$2.$3"); //Coloca ponto entre o quinto e o sexto dígitos
	v = v.replace( /\.(\d{3})(\d)/ , ".$1/$2"); //Coloca uma barra entre o oitavo e o nono dígitos
	v = v.replace( /(\d{4})(\d)/ , "$1-$2"); //Coloca um hífen depois do bloco de quatro dígitos
	return v;
}

function datanasc(v)
{
	v = v.replace( /\D/g , ""); //Remove tudo o que não é dígito
	v = v.replace( /(\d{2})(\d)/ , "$1/$2"); // XX/
	v = v.replace( /(\d{2})(\d)/ , "$1/$2"); // XX/XX/

	return v;
}

/**************************************************************************/

