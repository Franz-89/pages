<?php
$fichero = str_replace(".php", "", basename(__FILE__));

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");

//Comentario 1
$var = array();

?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  	<div class="progress active" style="display:none" id="progressbarview">
			<div id="progressbarvalue" class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%">
			</div>
		</div>
		<!-- Content Header (Page header) -->
		<section class="content-header">
		  <h1>
			Audax &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.8kq7d94rqg5v' target="_blank"><i class="fa fa-info"></i></a>
		  </h1>
		  <ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
			<li>Ayuda</li>
			<li class="active">Revisiones</li>
		  </ol>
		</section>
		<!-- Main content -->
		<section class="content">

		<div class="row">
			<div class="col-md-9">
				
				<div class="box box-primary">
					<div class="box-header with-border">
						<h3 class="box-title">Busqueda individual</h3>
					</div>
					<div class="box-body">
						
						<div class="row">
							<div class="col-md-4">
								<div class="input-group margin">
									<input type="text" class="form-control" id="cups" onchange="set_cups()">
									<span class="input-group-btn">
										<button type="button" class="btn btn-info btn-flat" onclick="datos_busqueda()"><i class="fa fa-search"></i></button>
									</span>
									<form method="POST" action="audax_actions.php">
										<input type="hidden" value="" name="cups" id="input_cups">
										<span class="input-group-btn">
											<button type="submit" name="action" value="download_unico" class="btn btn-success btn-flat"><i class="fa fa-download"></i></button>
										</span>
									</form>
								</div>
							</div>
							
							<div class="col-md-4">
								CUPS: <div id="codigo_cups"></div>
							</div>
						</div>
						
						<div class="nav-tabs-custom">
							<ul class="nav nav-tabs">
								<li class="active"><a href="#detalle" data-toggle="tab">Detalle</a></li>
								<li><a href="#activa" data-toggle="tab">Activa</a></li>
								<li><a href="#reactiva" data-toggle="tab">Reactiva</a></li>
								<li><a href="#maxima" data-toggle="tab">Maxima</a></li>
							</ul>
							<div class="tab-content">
								
								<!-- ACTIVA -->
								<div class="tab-pane active" id="detalle">
									<table id="tabla_detalle" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>ETIQUETA</th>
												<th>DATO</th>
											</tr>
										</thead>
									</table>
								</div>
								
								<!-- ACTIVA -->
								<div class="tab-pane" id="activa">
									<table id="tabla_activa" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>DESDE</th>
												<th>HASTA</th>
												<th>LECTURA</th>
												<th>P1</th>
												<th>P2</th>
												<th>P3</th>
												<th>P4</th>
												<th>P5</th>
												<th>P6</th>
												<th>TOT</th>
											</tr>
										</thead>
									</table>
								</div>
								
								<!-- REACTIVA -->
								<div class="tab-pane" id="reactiva">
									<table id="tabla_reactiva" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>DESDE</th>
												<th>HASTA</th>
												<th>LECTURA</th>
												<th>P1</th>
												<th>P2</th>
												<th>P3</th>
												<th>P4</th>
												<th>P5</th>
												<th>P6</th>
												<th>TOT</th>
											</tr>
										</thead>
									</table>
								</div>
								
								<!-- MAXIMA -->
								<div class="tab-pane" id="maxima">
									<table id="tabla_maxima" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>DESDE</th>
												<th>HASTA</th>
												<th>LECTURA</th>
												<th>P1</th>
												<th>P2</th>
												<th>P3</th>
												<th>P4</th>
												<th>P5</th>
												<th>P6</th>
											</tr>
										</thead>
									</table>
								</div>
							</div>
						</div>
					</div>
					<div class="overlay" id="loading">
						<i class="fa fa-refresh fa-spin"></i>
					</div>
				</div>
			</div>
			
			<div class="col-md-3">
				<form role="form" method="post" action="audax_actions.php" enctype="multipart/form-data" id="audax_list">
					<div class="box box-primary">
						<div class="box-header with-border">
							<h3 class="box-title">Descarga PS y BBDD</h3>
						</div>
						<div class="box-body">
							<div class="form-group">
								<div class="form-group">
									<label>Listado CUPS (.xlsx)</label>
									<input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]">
									<input type="hidden" name="MAX_FILE_SIZE" value = "10000000000000" />

									<p class="help-block">Listado CUPS con <a href="plantillas/CUPS.xlsx" download>este</a> formato</p>
								</div>
								<div class="box-footer">
									<button type="submit" name="action" value="download" class="btn btn-primary" onclick="esperar()">Descargar</button>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		
	</div>
		
    </section>

  
<?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php"); ?>

<script>
	
	function esperar(){
		setTimeout(function(){progressBar()}, 500)
	}
	
	function progressBar(){
		
		var timestamp
		var porcentaje
		porcentaje = true;
		
		//Espera un segundo para ver el nombre de la carpeta de la sesión
		$.ajax({
			url: "audax_actions.php?action=audax_timestamp",
			async: false,
			success: function (data) {timestamp = data;}
		})
		
		$('#progressbarview').toggle();
		updateBar(timestamp);
	}
	
	function updateBar(timestamp){
		var porcentaje
		$.ajax({
			url: "audax_actions.php?action=get_porcentaje",
			method: "POST",
			data: {timestamp: timestamp},
			async: false,
			success: function (data) {porcentaje = data;}
		})
		if (porcentaje){
			$('#progressbarvalue').attr('style', 'width: ' + porcentaje + '%');
			setTimeout(function(){updateBar(timestamp)}, 400);
		} else {
			$('#progressbarview').toggle();
		}
	}
	
	function datos_busqueda(){
		
		$.ajax({
			url: "audax_actions.php?action=datos_busqueda",
			method: "POST",
			data: {cups: $('#cups').val()},
			async: true,
			success: function (data) {
				
				if (data){
					data = data.split("|")
					
					$('#tabla_detalle').DataTable().clear()
					$('#tabla_detalle').DataTable().rows.add(JSON.parse(data[0])).draw()
					
					$('#tabla_activa').DataTable().clear()
					$('#tabla_activa').DataTable().rows.add(JSON.parse(data[1])).draw()

					$('#tabla_reactiva').DataTable().clear()
					$('#tabla_reactiva').DataTable().rows.add(JSON.parse(data[2])).draw()

					$('#tabla_maxima').DataTable().clear()
					$('#tabla_maxima').DataTable().rows.add(JSON.parse(data[3])).draw()

					$('#codigo_cups').text($('#cups').val())
				}
			}
		})
	}
	
	function set_cups(){
		$('#input_cups').val($('#cups').val())
	}
	
	$('#tabla_detalle').DataTable({
		  paging		: false,
		  searching		: true,
		  serverSide	: false,
		  lengthChange	: true,
		  statesave		: true,
		  ordering    	: false,
		  info        	: true,
		  autoWidth   	: true,
		  data			: ''
	})
	
	$('#tabla_activa').DataTable({
		  paging		: true,
		  searching		: false,
		  serverSide	: false,
		  lengthChange	: true,
		  statesave		: true,
		  ordering    	: false,
		  info        	: true,
		  autoWidth   	: true,
		  data			: ''
	})
	
	$('#tabla_reactiva').DataTable({
		  paging		: true,
		  searching		: false,
		  serverSide	: false,
		  processing	: true,
		  lengthChange	: true,
		  statesave		: true,
		  ordering    	: false,
		  info        	: true,
		  autoWidth   	: true,
		  data			: ''
	})
	
	$('#tabla_maxima').DataTable({
		  paging		: true,
		  searching		: false,
		  serverSide	: false,
		  processing	: true,
		  lengthChange	: true,
		  statesave		: true,
		  ordering    	: false,
		  info        	: true,
		  autoWidth   	: true,
		  data			: ''
	})
	
	$('#loading').toggle()
	
	$(document).ajaxStart(function(){
		$("#loading").show();
	})
	$(document).ajaxComplete(function(){
		$("#loading").hide();
	})
	
</script>

</body>
</html>