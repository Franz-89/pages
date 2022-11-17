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
        Contactos &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.exy8dfd9kyb7' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li>Info</li>
		<li class="active">Contactos</li>
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
                    
                    <form onsubmit="addContactoDistr();return false">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Distribuidora</label>
                                <select class="form-control select2" style="width: 100%;" data-placeholder="Distribuidora" id="distr">
                                    <?php
                                    $Lista = new Lista('distribuidoras');
                                    $Lista->print_list();
                                    ?>
                                </select>
                             </div>
                         </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Contacto</label>
                                <input type="text" class="form-control" placeholder="Nombre..." id="contacto">
                             </div>
                         </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" class="form-control" placeholder="Email..." id="email">
                             </div>
                         </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Telefono</label>
                                <input type="text" class="form-control" placeholder="Telefono..." id="tel">
                             </div>
                         </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Comentarios</label>
                                <textarea class="form-control" rows="2" placeholder="Comentario ..." name="comentarios" id="comentarios"></textarea>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <label>&nbsp;</label>
                            <div class="form-group">
                                <button type="submit" class="btn btn-success btn-flat"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                    </form>
				</div>
                
				<table id="contactos" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>DISTRIBUIDORA</th>
							<th>CONTACTO</th>
							<th>EMAIL</th>
							<th>TELEFONO</th>
							<th>COMENTARIOS</th>
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
	
  function delContactoDistr(valor){
		  if (confirmation()){
			  $.ajax({
					url: "js_actions.php?action=delContactoDistr",
					method: "POST",
					data: {id: valor},
					async: false,
					success: function () {setTimeout(reloadContactos(), 500)}
				})
		  }
	  }
    
    function addContactoDistr(){
          $.ajax({
                url: "js_actions.php?action=addContactoDistr",
                method: "POST",
                data: {
                    distr:       $('#distr').val(),
                    contacto:    $('#contacto').val(),
                    email:       $('#email').val(),
                    tel:         $('#tel').val(),
                    comentarios: $('#comentarios').val()
                },
                async: false,
                success: function () {
                    setTimeout(reloadContactos(), 500)
                }
            })
	  }
	
	function reloadContactos() {$('#contactos').DataTable().ajax.reload();}
	
  $(function () {
    	
	  $('#contactos').DataTable({
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
			  url 		: "js_actions.php?action=getContactosDistr",
			  dataSrc	: ''
		  },
		  columnDefs	: [{
                targets		: [ 0 ],
                visible		: false,
                searchable	: true
            }]
		})
	  
  })
</script>
</body>
</html>