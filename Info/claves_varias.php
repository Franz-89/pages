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
        Claves varias &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.exy8dfd9kyb7' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li>Info</li>
		<li class="active">Claves varias</li>
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
              	
                <form onsubmit="addClaveVaria();return false">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipo</label>
                            <input type="text" class="form-control" placeholder="Tipo..." id="tipo" required>
                         </div>
                     </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Usuario</label>
                            <input type="text" class="form-control" placeholder="Usuario..." id="usuario" required>
                         </div>
                     </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Contraseña</label>
                             <input type="text" class="form-control" placeholder="Contraseña..." id="contrasena" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Comentarios</label>
                            <input type="text" class="form-control" id="comentarios" placeholder="Comentarios...">
                         </div>
                     </div>
                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-flat"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                </form>
                
                <div class="col-md-12">
                    <table id="claves" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>USUARIO</th>
                                <th>CONTRASEÑA</th>
                                <th>COMENTARIOS</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                    </table>
                </div>
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
	
  function addClaveVaria(){

          $.ajax({
                url: "js_actions.php?action=addClaveVaria",
                method: "POST",
                data: {
                    tipo: $('#tipo').val(),
                    usu: $('#usuario').val(),
                    pwd: $('#contrasena').val(),
                    comentarios: $('#comentarios').val()
                },
                async: false,
                success: function () {setTimeout(reloadClaves(), 500)}
            })
	  }
    
    function delClaveVaria(valor){
		  if (confirmation()){
			  $.ajax({
					url: "js_actions.php?action=delClaveVaria",
					method: "POST",
					data: {id: valor},
					async: false,
					success: function () {setTimeout(reloadClaves(), 500)}
				})
		  }
	  }
	
	function reloadClaves() {$('#claves').DataTable().ajax.reload();}
	
  $(function () {
    	
	  $('#claves').DataTable({
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
			  url 		: "js_actions.php?action=getClavesVarias",
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