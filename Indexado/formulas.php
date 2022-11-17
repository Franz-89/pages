<?php
$fichero = str_replace(".php", "", basename(__FILE__));

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

//cliente, comercializadora, tarifa, enum
session_start();
if (isset($_SESSION['INDEXADO'])){
	foreach ($_SESSION['INDEXADO'] as $key=>$value){$$key = $value;}
} else {
	$cliente = '';
	$comercializadora = '';
	$tarifa = '';
	$enum = '';
	$desde = '';
	$hasta = '';
}
session_write_close();


include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");

?>

	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Formulas &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.2tzhbl11nc9d' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li>Indexado</li>
		<li class="active">Formulas</li>
      </ol>
    </section>
	
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">

          <div class="box box-primary">
            <div class="box-body">
				
				<div class="col-md-12">
					<div class="col-md-2">
						
						<div class="form-group">
							<label>Cliente</label>
							<select class="form-control select2" style="width: 100%;" id="cliente" onchange="save_find()">
								<?php
								$Lista = new Lista('clientes');
								$Lista->print_list($cliente);
								?>
							</select>
						 </div>
						
						<div class="form-group">
							<label>Comercializadora</label>
							<select class="form-control select2" style="width: 100%;" id="comercializadora" onchange="save_find()">
								<?php
								$Lista->change_list('comm_elec_mainsip');
								$Lista->print_list($comercializadora);
								?>
							</select>
						 </div>
						
						<div class="form-group">
							<label>Tarifa</label>
							<select class="form-control select2" style="width: 100%;" id="tarifa" onchange="save_find()">
								<?php
								$Lista->change_list('tarifas');
								$Lista->print_list($tarifa);
                                $Lista->change_list('tarifas_nuevas');
                                $Lista->print_list($tarifa);
								?>
							</select>
						 </div>
						
						<div class="form-group">
							<label>Enumeración</label>
							 <select class="form-control select2" style="width: 100%;" id="enum" onchange="save_find()">
								<?php
								$Lista->change_list('prioridad');
								$Lista->print_list($enum);
								unset($Lista);
								?>
							</select>
						</div>
						
						<form role="form" method="post" action="indexado_actions.php" name="telemedida_form" enctype="multipart/form-data">
							
							<input type="hidden" id="id" name="id" value="">
							
							<div class="form-group">
								  <div class="form-group">
									<label>Apuntamientos (.xlsx) <div style="color:green;" id="apu_cargados">Cargados</div></label>
									<input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]">
									<input type="hidden" name="MAX_FILE_SIZE" value = "10485760" />

									<p class="help-block">Apuntamientos con <a href="plantillas/Apuntamientos.xlsx" download><u>este formato</u></a></p>
								  </div>
								
                                
								<div class="box-footer pull-right">
									<button class="btn btn-success" name="action" value="upload_apuntamientos"><i class="fa fa-upload"></i></button>
									<button class="btn btn-success" name="action" value="download_apuntamientos"><i class="fa fa-download"></i></button>
								</div>
							 </div>
						</form>
					</div>
					
					<div class="col-md-2">
						<div class="form-group">
							<label>Desde</label>
							<div class="input-group date">
							  <div class="input-group-addon">
								<i class="fa fa-calendar"></i>
							  </div>
							  <input type="text" class="form-control pull-right fecha" id="desde" name="desde" value="<?php echo $desde ?>">
							</div>
						 </div>
						
						<div class="form-group">
							<label>Hasta (No incluido)</label>
							<div class="input-group date">
							  <div class="input-group-addon">
								<i class="fa fa-calendar"></i>
							  </div>
							  <input type="text" class="form-control pull-right fecha" id="hasta" name="hasta" value="<?php echo $hasta ?>">
							</div>
						 </div>
						
						<div class="form-group">
							<textarea class="textarea" placeholder="Comentarios..."
                          style="width: 100%; height: 100px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" id="comentarios"></textarea>
						 </div>
						
						<a class="btn btn-app pull-right" onclick="saveFormula()">
							<i class="fa fa-save"></i> Guardar
						</a>
					 </div>
					
					<div class="col-md-4">
						<div class="form-group">
							<label>Formula</label>
							<textarea class="textarea" placeholder="Formula..."
                          style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" id="formula" onkeyup="translateFormula()"></textarea>
						 </div>
						
						<div class="form-group">
							<textarea class="textarea" placeholder="Formula..."
                          style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" id="translated" disabled="disabled"></textarea>
						 </div>
					</div>
					
					<div class="col-md-4">
						<table id="variables" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>ID</th>
									<th>VARIABLE</th>
									<th>DEFINICION</th>
								</tr>
							</thead>
						</table>
					</div>
					
				</div>
			</div>
		</div>
				
		<div class="box box-primary">
            <div class="box-body">
				<div class="col-md-12">
					<a class="btn btn-app" href="indexado_actions.php?action=downloadFormulas">
						<i class="fa fa-file-excel-o"></i> Descargar
					</a>
					<table id="formulas" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>ID</th>
								<th>CLIENTE</th>
								<th>COMERCIALIZADORA</th>
								<th>TARIFA</th>
								<th>ENUMERACIÓN</th>
								<th>DESDE</th>
								<th>HASTA</th>
								<th>COMENTARIOS</th>
								<th>FORMULA</th>
								<th>ACCIONES</th>
							</tr>
						</thead>
					</table>
				</div>
				
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php") ?>

<!-- page script -->
<script>
	
	function save_find(){
		$.ajax({
			url: "indexado_actions.php?action=saveFind",
			method: "POST",
			data: {
				cliente: 			$('#cliente').val(),
				comercializadora: 	$('#comercializadora').val(),
				tarifa: 			$('#tarifa').val(),
				enum: 				$('#enum').val(),
				desde: 				$('#desde').val(),
				hasta: 				$('#hasta').val(),
				  },
			async: true,
			success: function (data) {
				if (data){
					data = data.split('|');
					$('#id').val(JSON.parse(data[0]));
					$('#comentarios').val(JSON.parse(data[1]));
					$('#formula').val(JSON.parse(data[2]));
                    if (data[3]!=''){
                        $('#apu_cargados').show();
                    } else {
                        $('#apu_cargados').hide();
                    }
					setTimeout(translateFormula(), 200);
				} else {
					$('#id').val('');
					$('#comentarios').val('');
					$('#formula').val('');
					$('#translated').val('');
                    $('#apu_cargados').hide();
				}
			}
		})
	}
	
	function saveFormula(){
		$.ajax({
			url: "indexado_actions.php?action=saveFormula",
			method: "POST",
			data: {
				id: 			$('#id').val(),
				comentarios: 	$('#comentarios').val(),
				formula: 		$('#formula').val()
				  },
			async: true,
			success: function (data) {
				$('#id').val(data);
				setTimeout(reloadFormulas(), 500)
			}
		})
	}
	
	function confirmation() {
		if(confirm("Ejecutar esta acción?"))
		{
			return true;
		}
		return false;
	}
	
	function delFormula(valor){
		if (confirmation()){
			  $.ajax({
					url: "indexado_actions.php?action=delFormula",
					method: "POST",
					data: {id: valor},
					async: true,
					success: function (data) {setTimeout(reloadFormulas(), 500);}
				})
		  }
	}
	
	function translateFormula(){
		$.ajax({
			url: "indexado_actions.php?action=translateFormula",
			method: "POST",
			data: {formula: $('#formula').val()},
			async: true,
			success: function (data) {
				$('#translated').val(data)
			}
		})
	}
	
	function reloadFormulas() {$('#formulas').DataTable().ajax.reload();}
	
	save_find()
	
	$('#formulas').DataTable({
		  paging		: true,
		  searching		: true,
		  serverSide	: false,
		  processing	: true,
		  language		: {
			  loadingRecords : '&nbsp;',
			  processing 	 : 'Procesando...'
		  },
		  lengthChange	: true,
		  statesave		: true,
		  ordering    	: true,
		  info        	: true,
		  autoWidth   	: true,
		  dom			: '<"top"f>rt<"bottom"p><"clear">',
		  order			: [[ 1, "asc" ]],
		  ajax			: {
			  url 		: "indexado_actions.php?action=getFormulas",
			  dataSrc	: ''
		  },
		  columnDefs	: [{
                targets		: [ 0 ],
                visible		: false,
                searchable	: false
            }]
		})
    	
  $('#variables').DataTable({
	  paging		: true,
	  searching		: true,
	  serverSide	: false,
	  processing	: true,
	  language		: {
		  loadingRecords : '&nbsp;',
		  processing 	 : 'Procesando...'
	  },
	  lengthChange	: true,
	  statesave		: true,
	  ordering    	: true,
	  info        	: true,
	  autoWidth   	: true,
	  dom			: '<"top"f>rt<"bottom"p><"clear">',
	  order			: [[ 1, "asc" ]],
	  ajax			: {
		  url 		: "indexado_actions.php?action=getVariables",
		  dataSrc	: ''
	  },
	  columnDefs	: [{
			targets		: [ 0 ],
			visible		: false,
			searchable	: false
		}]
	})
	
    $('#apu_cargados').hide();
    
</script>
</body>
</html>