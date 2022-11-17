<?php
$fichero = str_replace(".php", "", basename(__FILE__));

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
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
        Varias
        <small>Info varias</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li>Info</li>
        <li class="active">Varias</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content container-fluid">
		<div class="row">
			
			<!-- Facturas -->
			<div class="col-md-3">
				<form role="form" method="post" action="info_varias_actions.php" enctype="multipart/form-data">
				  <div class="box box-primary">
					  <div class="box-header with-border">
							<h3 class="box-title">GDOs</h3>
						  	<div class="box-tools pull-right">
								<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-dwldgdos"><i class="fa fa-info-circle"></i></button>
							  </div>
						</div>
					<div class="box-body">
						
						<div class="form-group">
							<label>Año</label>

							<select class="form-control select2" style="width: 100%;" name="ano" id="ano">
								<?php
								$Lista = new Lista('ano');
								$Lista->print_list(date('Y'));
								unset($Lista);
								?>
							</select>
						</div>
						
						<div class="form-group">
							<label>Tipo fichero</label>

							<select class="form-control select2" style="width: 100%;" name="tipo" id="tipo">
								<option selected="selected" value="PDF">PDF</option>
								<option value="XLS">XLS</option>
							</select>
						</div>
						
					  <div class="form-group">
						  <div class="form-group">
							<label>Listado de CUPS (.xlsx)</label>
							<input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]">
							<input type="hidden" name="MAX_FILE_SIZE" value = "10000000000000" />

							<p class="help-block">Listado de CUPS con <a href="plantillas/listado_cups.xlsx">este</a> formato (.xlsx)</p>
						  </div>
						<div class="box-footer">
							<button type="submit" name="action" value="download_gdos" class="btn btn-success"><i class="fa fa-download"></i></button>
						</div>
					  </div>
					</div>
				  </div>
				</form>
			</div>
			
			
			<!-- INFO -->
			  <div class="modal fade" id="modal-dwldfras">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">DESCARGA FACTURAS</h4>
					  </div>
					  <div class="modal-body">
						<p>
							Tras haber seleccionado Cliente, Comercializadora, Usuario y un fichero con un listado de numeros de facturas, descarga los datos desde las siguientes comercializadoras:
							<ul>
								<li>ENDESA</li>
								<li>EDP</li>
							</ul>
							En el caso de NATURGY sigue siendo más eficaz el complemento de Excel.
						</p>
					  </div>
					  <div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
					  </div>
					</div>
				  </div>
				</div>
			
			
			
		</div>
    </section>
    <!-- /.content -->
  
<?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php") ?>

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
			url: "descargas_actions.php",
			async: false,
			method: "POST",
			data: {action: 'get_timestamp'},
			success: function (data) {timestamp = data;}
		})
		
		$('#progressbarview').toggle();
		updateBar(timestamp);
	}
	
	function updateBar(timestamp){
		var porcentaje
		$.ajax({
			url: "descargas_actions.php",
			method: "POST",
			data: {
				timestamp: timestamp,
				action: 'get_porcentaje'
			},
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
	
	
</script>
	  
</body>
</html>