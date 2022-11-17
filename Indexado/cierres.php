<?php
$fichero = str_replace(".php", "", basename(__FILE__));

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");

?>

	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Cierres &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.2tzhbl11nc9d' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li>Indexado</li>
		<li class="active">Cierres</li>
      </ol>
    </section>
	
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">

          <div class="box box-primary">
            <div class="box-header">
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              	
				<div class="col-md-12">
                    <div class="col-md-2">
                        <a class="btn btn-app" href="indexado_actions.php?action=downloadCierres">
                            <i class="fa fa-file-excel-o"></i> Descargar
                        </a>
                    </div>
                    
                    <div class="col-md-2">
                        <form role="form" method="post" action="indexado_actions.php" enctype="multipart/form-data">
                            <div class="form-group">
                                <input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]">
                                <input type="hidden" name="MAX_FILE_SIZE" value = "10485760" />

                                <p class="help-block">Fichero de cierres con <a href="plantillas/cierres.xlsx" download><u>este formato</u></a></p>
                                <button class="btn btn-success" name="action" value="upload_cierres"><i class="fa fa-upload"></i></button>
                            </div>
                        </div>
                    </div>
				</div>
				
                
                
				<div class="col-md-12">
					<div class="col-md-2">
						<div class="form-group">
							<label>Cliente</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Usuario" id="cliente">
								<?php
								$Lista = new Lista('clientes');
								$Lista->print_list();
								?>
							</select>
						 </div>
					 </div>
					<div class="col-md-2">
						<div class="form-group">
							<label>Fecha</label>
							<div class="input-group date">
							  <div class="input-group-addon">
								<i class="fa fa-calendar"></i>
							  </div>
							  <input type="text" class="form-control pull-right fecha" id="desde" name="desde">
							</div>
						 </div>
					 </div>
					<div class="col-md-2">
						<div class="form-group">
							<label>Producto</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Producto" id="producto">
								<?php
								$Lista->change_list('productos');
								$Lista->print_list();
								?>
							</select>
						 </div>
					 </div>
					<div class="col-md-1">
						<div class="form-group">
							<label>Enumeración</label>
							 <select class="form-control select2" style="width: 100%;" id="enum">
								<?php
								$Lista->change_list('prioridad');
								$Lista->print_list();
								unset($Lista);
								?>
							</select>
						</div>
					</div>
					<div class="col-md-1">
						<div class="form-group">
							<label>Precio</label>
							<input type="number" step=0.01 class="form-control" placeholder="Precio" id="precio">
						 </div>
					 </div>
					<div class="col-md-1">
						<div class="form-group">
							<label>Porcentaje</label>
							<input type="number" step=0.001 min=0 max=1 class="form-control" placeholder="Porcentaje" id="porcentaje">
						 </div>
					 </div>
					<div class="col-md-1">
						<div class="form-group">
							<label>Volumen</label>
							<input type="number" step=0.01 class="form-control" placeholder="Volumen" id="volumen">
						 </div>
					 </div>
					<div class="col-md-1">
						<div class="form-group">
							<label>Electricidad/Gas</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Tipo" id="tipo" onchange="filterDataType($(this).val())">
								<option selected="selected">ELECTRICIDAD</option>
								<option>GAS</option>
							</select>
						 </div>
					 </div>
					
					<div class="col-md-1">
						<label>&nbsp;</label>
						<div class="form-group">
							<button type="submit" class="btn btn-success btn-flat" onclick="anadir_cierre()"><i class="fa fa-plus"></i></button>
						</div>
					</div>
				</div>
				
				<table id="cierres" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>CLIENTE</th>
							<th>FECHA</th>
							<th>PRODUCTO</th>
							<th>ENUMERACION</th>
							<th>PRECIO</th>
							<th>PORCENTAJE</th>
							<th>VOLUMEN</th>
							<th>TIPO</th>
							<th>ACCION</th>
						</tr>
					</thead>
				</table>
				
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php") ?>

<!-- page script -->
<script>

	function confirmation() {
		if(confirm("Ejecutar esta acción?"))
		{
			return true;
		}
		return false;
	}
	
	function delCierre(valor){
		if (confirmation()){
			  $.ajax({
					url: "indexado_actions.php?action=delCierre",
					method: "POST",
					data: {id: valor},
					async: true,
					success: function () {setTimeout(reloadCierres(), 500)}
				})
		  }
	}
	
	function anadir_cierre(){
		if (confirmation()){
			  $.ajax({
					url: "indexado_actions.php?action=anadir_cierre",
					method: "POST",
					data: {
						cliente: 	$('#cliente').val(),
						fecha: 		$('#desde').val(),
						producto: 	$('#producto').val(),
						precio: 	$('#precio').val(),
						porcentaje: $('#porcentaje').val(),
						enum: 		$('#enum').val(),
						volumen: 	$('#volumen').val(),
						tipo: 		$('#tipo').val()
					},
					async: true,
					success: function (data) {
						setTimeout(reloadCierres(), 500)
						
						$('#desde').val('')
						$('#producto').val('')
						$('#precio').val('')
						$('#porcentaje').val('')
						$('#enum').val('1')
						$('#volumen').val('')
					}
				})
		  }
	}
	
	function reloadCierres() {$('#cierres').DataTable().ajax.reload();}
	
	function filterDataType(dato){$('#cierres').DataTable().column(7).search(dato).draw()}
	
  $(function () {
    	
	  $('#cierres').DataTable({
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
		  dom			: '<"top"f>rt<"bottom"ilp><"clear">',
		  order			: [[ 1, "desc" ]],
		  ajax			: {
			  url 		: "indexado_actions.php?action=getCierres",
			  dataSrc	: ''
		  },
		  columnDefs	: [{
                targets		: [ 0 ],
                visible		: false,
                searchable	: false
            }]
		}).column(8).search($('#tipo').val()).draw()
	  
  })
</script>
</body>
</html>