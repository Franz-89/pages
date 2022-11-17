<?php
$fichero = str_replace(".php", "", basename(__FILE__));
$sql_table = $fichero;

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");

$msg = "";
if (isset($_GET['fichada'])){
	switch ($_GET['fichada']){
		case "errorsql"	: $msg = "Error del servidor!"; break;
		case "exito"	: $msg = "Fichada registrada!"; break;
	}
}

if 		(isset($_GET['del'])){$msg = "Registro eliminado!";} 
elseif 	(isset($_GET['mod'])){$msg = "Registro actualizado!";}

?>

	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Fichar &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.uy6l2fl9gevz' target="_blank"><i class="fa fa-info"></i></a>
		  
        <small><strong><font color="red">
			<?php echo $msg;?>
		</font></strong></small>
		
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li class="active">Fichar</li>
      </ol>
    </section>
	
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">

          <div class="box">
            <div class="box-header">
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              	
				<a class="btn btn-app" onclick="entrada()">
					<i class="fa fa-sign-in"></i> Entrada
			    </a>
				<a class="btn btn-app" onclick="salida()">
					<i class="fa fa-sign-out"></i> Salida
			    </a>
				<a class="btn btn-app" href="php_actions.php?action=download">
					<i class="fa fa-file-excel-o"></i> Descargar
			    </a>
				
				
				<table id="fichadas" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>USUARIO</th>
							<th>ENTRADA/SALIDA</th>
							<th>HORA</th>
							<th>ACCIONES</th>
						</tr>
					</thead>
				</table>
				
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

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
	
  function delFichada(valor){
		  if (confirmation()){
			  $.ajax({
					url: "js_actions.php?action=delFichada",
					method: "POST",
					data: {id: valor},
					async: true,
					success: function () {setTimeout(reloadFichadas(), 500)}
				})
		  }
	  }
	
	function entrada(){
		if (confirmation()){
		  $.ajax({
				url: "js_actions.php?action=entrada",
				success: function () {setTimeout(reloadFichadas(), 500)}
			})
		}
	  }
	
	function salida(){
		if (confirmation()){
		  $.ajax({
				url: "js_actions.php?action=salida",
				success: function () {setTimeout(reloadFichadas(), 500)}
			})
		}
	  }
	
	function reloadFichadas() {$('#fichadas').DataTable().ajax.reload();}
	
  $(function () {
    	
	  $('#fichadas').DataTable({
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
		  order			: [[ 3, "desc" ]],
		  ajax			: {
			  url 		: "js_actions.php?action=getFichadas",
			  dataSrc	: ''
		  },
		  columnDefs	: [{
                targets		: [ 0 ],
                visible		: false,
                searchable	: false
            }]
		})
	  
  })
</script>
</body>
</html>