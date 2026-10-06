<html>
	<head>
		<title>Fatura Digital Olé</title>
		<style type="text/css">
			body {
				font-family: arial;
				background: url('./imagens/logo.png') no-repeat 10px 10px #1a97cc;
				color: #ffffff;
			}
			.container {
				width: 480px;
				top: 170px;
				border: 1px solid #ffffff;
				left: 50%;
				margin-left: 250px;
				padding: 0 10px 0 10px;
				box-shadow: 3px 3px 3px rgba(100, 100, 100, 0.5);
				background-color: #1a97cc;
				clear: both;
			}
			.mensagem {
				position: absolute;
				top: 170px;
				left: 20px;
				width: 180px;
			}
			input {
				border: 0;
				padding: 2px;				
			}
			p {
				margin: 10px 0 10px 0;
			}
			form {
				margin: 0;
				width: 480px;
			}
			.container table {
				height: 100%;
				border-collapse: collapse;
			}
			.submit {
				font-size: 16px;
				background-color: #ffffff;
				border: 3px solid #7abf28;
				border-radius: 3px;
				padding: 4px;
				box-shadow: 3px 3px 3px rgba(100, 100, 100, 0.5);
				cursor: pointer;
			}
		</style>
	</head>
	<body>
	  <div class="mensagem">
	  	<p>Por favor, confira seus dados de cobrança no formulário ao lado. Caso encontre alguma divergência ou deseje alterar seu endereço de cobrança, faça as alterações necessárias e clique em enviar.</p>
	  	<p>Obrigado.</p>
	 	</div>
		<div class="container">
			<form method="post">
				<p>Nome</p>
				<input type="text" name="nome" value="[nome]" maxlength="200" style="width:100%" disabled />
				<p>Logradouro</p>
				<input type="text" name="logradouro" value="[logradouro]" maxlength="200" style="width:100%" />
				<p>Número</p>
				<input type="text" name="numero" value="[numero]" maxlength="200" style="width:60%" />
				<p>Complemento</p>
				<input type="text" name="complemento" value="[complemento]" maxlength="200" style="width:60%" />
				<p>Condomínio</p>
				<input type="text" name="condominio" value="[condominio]" maxlength="200" style="width:60%" />
				<p>Bairro</p>
				<input type="text" name="bairro" value="[bairro]" maxlength="200" style="width:60%" />
				<p>Cidade</p>
				<input type="text" name="cidade" value="[cidade]" maxlength="200" style="width:60%" />
				<p>Estado</p>
				<input type="text" name="estado" value="[estado]" maxlength="200" style="width:40%" />
				<p>CEP</p>
				<input type="text" name="cep" value="[cep]" maxlength="9" style="width:40%" />
				<p style="text-align: right"><input type="submit" value="Enviar endereço de cobrança" class="submit" /></p>
			</form>
		</div>
	</body>
</html>
