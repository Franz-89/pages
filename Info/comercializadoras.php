<?php
$fichero 	= str_replace(".php", "", basename(__FILE__));
$sql_table 	= $fichero;

$msg = "";
if 		(isset($_GET['del'])){$msg = "Registro eliminado!";} 
elseif 	(isset($_GET['mod'])){$msg = "Registro actualizado!";}
elseif 	(isset($_GET['add'])){$msg = "Registro añadido!";}

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>

	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Comercializadoras &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.6yth7bqxg58b' target="_blank"><i class="fa fa-info"></i></a>
		  
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li>Info</li>
		<li class="active">Comercializadoras</li>
      </ol>
    </section>
	
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-12">

          <div class="box box-primary">
            <div class="box-header">
            </div>
            <!-- /.box-header -->
            <div class="box-body">
				
				<div class="row">
					
					<div class="col-md-6" align="center">
						<div class="form-group">
							<label><a href="" id="link_emp" target="_blank">Empresas <i class="fa fa-external-link"></i> </a>- COMERCIALIZADORA -<a href="" id="link_hog" target="_blank"> Hogares <i class="fa fa-external-link"></i></a></label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Comercializadora" id="comm" onchange="filterDataType($(this).val())">
								<?php
								$Lista = new Lista('comercializadoras');
								$Lista->print_list();
								?>
							</select>
						 </div>
					</div>
					
					<div class="col-md-6" align="center">
						<div class="form-group">
							<label>DISTRIBUIDORA</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Distribuidora" id="distr" onchange="filterDataTypeDistr($(this).val())">
								<?php
								$Lista->change_list('distribuidoras');
								$Lista->print_list();
								unset($Lista);
								?>
							</select>
						 </div>
					</div>
					
				</div>
				
				<!-- TABLAS -->
				<div class="row">
					<!-- COMERCIALIZADORAS -->
					<div class="col-md-6">
						<div class="box box-success">
							<div class="col-md-6">
								<section class="content-header" align="center"><h1>Telefonos</h1></section>
								<table id="telefonos" class="table table-bordered table-striped">
									<thead>
										<tr>
											<th>COMERCIALIZADORA</th>
											<th>DESCRIPCIÓN</th>
											<th>TELEFONO</th>
										</tr>
									</thead>
								</table>
							</div>

							<div class="col-md-6">
								<section class="content-header" align="center"><h1>Emails</h1></section>
								<table id="mails" class="table table-bordered table-striped">
									<thead>
										<tr>
											<th>COMERCIALIZADORA</th>
											<th>DESCRIPCIÓN</th>
											<th>EMAIL</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
					
					<!-- DISTRIBUIDORAS -->
					<div class="col-md-6">
						<div class="box box-info">
							<div class="col-md-6">
								<section class="content-header" align="center"><h1>Telefonos</h1></section>
								<table id="telefonos_distr" class="table table-bordered table-striped">
									<thead>
										<tr>
											<th>DISTRIBUIDORA</th>
											<th>DESCRIPCIÓN</th>
											<th>TELEFONO</th>
										</tr>
									</thead>
								</table>
							</div>

							<div class="col-md-6">
								<section class="content-header" align="center"><h1>Emails</h1></section>
								<table id="mails_distr" class="table table-bordered table-striped">
									<thead>
										<tr>
											<th>DISTRIBUIDORA</th>
											<th>DESCRIPCIÓN</th>
											<th>EMAIL</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
					
					
				</div>
					
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
        
    <div class="row">
        <div class="col-xs-12">

          <div class="box box-primary">
            <div class="box-header">
                <h1>Códigos distribuidoras</h1>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
				<table id="distr_code" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>CODIGO</th>
							<th>DISTRIBUIDORA</th>
						</tr>
					</thead>
				</table>
            </div>
          </div>
        </div>
      </div>
        
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php") ?>


<!-- page script -->
<script>

	function getEnlacesComm(dato){
		$.ajax({
			url		: "js_actions.php?action=getEnlacesComm",
			method	: 'POST',
			data	: {
				comm : dato
			},
			success	: function(data){
				var datos = data.split("|")
				$('#link_hog').attr("href", datos[0])
				$('#link_emp').attr("href", datos[1])
			}
		})
	}
	
	function filterDataType(dato){
		getEnlacesComm(dato)
		$('#telefonos').DataTable().column(0).search(dato).draw()
		$('#mails').DataTable().column(0).search(dato).draw()
	}
	
	function filterDataTypeDistr(dato){
		$('#telefonos_distr').DataTable().column(0).search(dato).draw()
		$('#mails_distr').DataTable().column(0).search(dato).draw()
	}
	
  $(function () {
	  
	  $('#mails_distr').DataTable({
		  paging		: false,
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
		  dom			: '<"top">rt<"bottom"><"clear">',
		  order			: [[ 1, "asc" ]],
		  ajax			: {
			  url 		: "js_actions.php?action=getDatosDistr&campo=MAIL",
			  dataSrc	: ''
		  },
		  columnDefs	: [{
                targets		: [ 0 ],
                visible		: false,
                searchable	: true
            }]
		}).column(0).search($('#distr').val()).draw()
	  
	  $('#telefonos_distr').DataTable({
		  paging		: false,
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
		  dom			: '<"top">rt<"bottom"><"clear">',
		  order			: [[ 1, "asc" ]],
		  ajax			: {
			  url 		: "js_actions.php?action=getDatosDistr&campo=TEL",
			  dataSrc	: ''
		  },
		  columnDefs	: [{
                targets		: [ 0 ],
                visible		: false,
                searchable	: true
            }]
		}).column(0).search($('#distr').val()).draw()
	  
	  
	  $('#mails').DataTable({
		  paging		: false,
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
		  dom			: '<"top">rt<"bottom"><"clear">',
		  order			: [[ 1, "asc" ]],
		  ajax			: {
			  url 		: "js_actions.php?action=getDatosComm&campo=MAIL",
			  dataSrc	: ''
		  },
		  columnDefs	: [{
                targets		: [ 0 ],
                visible		: false,
                searchable	: true
            }]
		}).column(0).search($('#comm').val()).draw()
	  
	  $('#telefonos').DataTable({
		  paging		: false,
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
		  dom			: '<"top">rt<"bottom"><"clear">',
		  order			: [[ 1, "asc" ]],
		  ajax			: {
			  url 		: "js_actions.php?action=getDatosComm&campo=TEL",
			  dataSrc	: ''
		  },
		  columnDefs	: [{
                targets		: [ 0 ],
                visible		: false,
                searchable	: true
            }]
		}).column(0).search($('#comm').val()).draw()
	  
      $('#distr_code').DataTable({
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
		  order			: [[ 1, "asc" ]],
		  ajax			: {
			  url 		: "js_actions.php?action=getDistrCode",
			  dataSrc	: ''
		  },
		  columnDefs	: [{
                targets		: [ 0 ],
                visible		: false,
                searchable	: false
            }]
		})
      
	  getEnlacesComm($('#comm').val())
  })
</script>
</body>
</html>