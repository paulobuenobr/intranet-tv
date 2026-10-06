<?php
	date_default_timezone_set ( 'America/Sao_paulo' );
	include 'include/header.php';
?>

<body>

	<!-- <div class="container-fluid">
 -->
	<div class="container">

		<div id="clearfix"></div>

		<!-- <div class="col-xs-2"></div> -->

		<div class="col-xs-12">

			<div class="panel panel-default"><!-- panel-info"> -->
				<div class="panel-heading">
					<div class="panel-title" id="nomepainel">
						<div id="nomecliente">Nome Cliente</div>
						<div id="enderecocliente" style="width:400px; position: absolute; right: 20px; top: 3px">
							<select class="form-control" name="id_endereco" id="formGroupInputEndereco" >
<!-- 							<option value="">Todos os Endereços</option>									
 -->							</select>
						</div>

					</div>
				</div>

				<div style="padding-top: 20px" class="panel-body">

					<div class="row">

						<div class="col-xs-12">

							<form id="faturasform" class="form-inline">

  <div class="form-group">
									<div style="margin-bottom: 0" class="input-group">
										<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span> <input id="cpfcnpj" type="text" class="form-control" name="cpfcnpj" value="" required="" placeholder="CPF / CNPJ" onkeypress="return chama_mascara(this);" onblur="return chama_mascara(this);">
									</div>

								</div>

  <div class="form-group">
									<div style="margin-bottom: 0" class="input-group">
										<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span> <input id="datanasc" type="text" class="form-control" name="datanasc" value="" required="" placeholder="Data Nascimento" onkeypress="return chama_mascara_nasc(this);">
									</div>

								</div>

  <div class="form-group">

										<button type="button" name="btn_callback" class="btn btn-primary" id="btn_callback">Consultar</button>
										<button name="btn_clear" class="btn btn-danger" type="reset">Limpar</button>
										<button name="btn_desbloquear" id="desbloquear" class="btn btn-danger" style="display: none;" type="button">Desbloquear</button>
										<input type="hidden" id="cliente_id" value="">
									</div>
									
									<div id="text_desbloquear" style="display: none; margin-top: 8px; margin-left: 10px; float: left;"></div>
							</form>
						</div>

					</div>

						<div class="col-xs-12">

							<div id="resulttablefaturas" class="alert alert-info" role="alert">

								<table class="table table-fixed" id="tablestatusfaturas" 
										data-toggle="table" 
										data-classes="table table-hover table-condensed"
										style="font-size: small" 
										data-striped="true"
										data-search-align="right">

										<!-- data-height="320" -->
									<thead>
<!-- 										<tr>
											<th data-field="coa_codigo" data-sortable="false" data-width="15%">Código</th>
											<th data-field="coa_data_vencimento"  data-sortable="false" data-width="15%">Vencimento</th>
											<th data-field="coa_data_pagamento"  data-sortable="false" data-width="15%">Pagamento</th>
											<th data-field="coa_valor_original"  data-sortable="false" data-width="10%">Valor Original</th>
											<th data-field="coa_valor_total"  data-sortable="false" data-width="10%">Valor Pago</th>
											<th data-field="coa_status"  data-sortable="false" data-width="20%">Status</th>
											<th data-field="operate" data-formatter="operateFormatter" data-events="operateEvents" data-width="15%">Boleto</th>
										</tr> -->

										<tr>
											<th data-field="operate" data-formatter="operateFormatter" data-events="operateEvents" >Boleto</th>
											<th data-field="coa_status"  data-sortable="false" >Status</th>
											<th data-field="coa_codigo" data-sortable="false" >Código</th>
											<th data-field="coa_data_vencimento"  data-sortable="false" >Vencimento</th>
											<th data-field="coa_data_pagamento"  data-sortable="false" >Pagamento</th>
											<th data-field="coa_valor_original"  data-sortable="false" >Valor Original</th>
											<th data-field="coa_valor_total"  data-sortable="false" >Valor Pago</th>
										</tr>
									</thead>

									<tbody id="bodyTableFaturas" >

									</tbody>

								</table>
							</div>
						</div>

				</div>

			</div>

		</div>

		<!-- <div class="col-xs-2"></div> -->

		<!-- Modal -->
		<div class="modal fade" id="myModalStatusNotify" tabindex="-1" role="dialog" aria-labelledby="myModalStatusNotify" aria-hidden="true">
			<div class="modal-dialog modal-vertical-centered">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						<h4 class="modal-title" id="myModalLabel">Título</h4>
					</div>
					<div class="modal-body" id="myModalText">Mensagem</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal" id="ErrorOk">Ok</button>
					</div>
				</div>
			</div>
		</div>

	</div>

</body>
</html>

<?php
include 'include/footer.php';

?>