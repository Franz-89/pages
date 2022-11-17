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
              	<div class="row">
                    
                    <form onsubmit="addContacto();return false">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Cliente</label>
                                <select class="form-control select2" style="width: 100%;" id="cli" required>
                                    <?php
                                    $Lista = new Lista('clientes');
                                    $Lista->print_list();
                                    ?>
                                </select>
                             </div>
                         </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Contacto</label>
                                <input type="text" class="form-control" placeholder="Nombre contacto..." id="contacto">
                             </div>
                         </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Email</label>
                                 <input type="email" class="form-control" placeholder="Email..." id="email">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Telefono</label>
                                <input type="text" class="form-control" id="tel" placeholder="Telefono...">
                             </div>
                         </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Comentarios</label>
                                <input type="text" class="form-control" id="comentarios" placeholder="Comentarios...">
                             </div>
                         </div>
                        <div class="col-md-4">
                            <label>Comercializadora <span style="color:red">(si se especifica el contacto se guardará como gestor)</span></label>
                            <select class="form-control select2" style="width: 100%;" id="comercializadora">
                                <option name="" id="" value="" selected></option>
                                <?php
                                $Lista->change_list('comercializadoras');
                                $Lista->print_list();
                                ?>

                            </select>
                         </div>

                        <div class="col-md-1">
                            <label>&nbsp;</label>
                            <div class="form-group">
                                <button type="submit" class="btn btn-success btn-flat"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
				
                <h2>Contactos</h2>
				<div class="col-md-3">
					<div class="form-group">
						<select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" id="contact" onchange="filterDataType($(this).val())">
							<option selected="selected" value="C">CLIENTE</option>
							<option value="G">GESTOR</option>
						</select>
					 </div>
				</div>
				
				<table id="contactos" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>CLIENTE</th>
							<th>CONTACTO</th>
							<th>EMAIL</th>
							<th>TELEFONO</th>
							<th>COMENTARIOS</th>
							<th>COMERCIALIZADORA</th>
							<th>IDENTIFICADOR</th>
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
	
    function addContacto(){
          $.ajax({
                url: "js_actions.php?action=addContacto",
                method: "POST",
                data: {
                    cli: $('#cli').val(),
                    contacto: $('#contacto').val(),
                    email: $('#email').val(),
                    tel: $('#tel').val(),
                    comentarios: $('#comentarios').val(),
                    comercializadora: $('#comercializadora').val()
                },
                async: false,
                success: function () {setTimeout(reloadContactos(), 500)}
            })
	  }
    
  function delContacto(valor){
		  if (confirmation()){
			  $.ajax({
					url: "js_actions.php?action=delContacto",
					method: "POST",
					data: {id: valor},
					async: false,
					success: function () {setTimeout(reloadContactos(), 500)}
				})
		  }
	  }
	
	function reloadContactos() {$('#contactos').DataTable().ajax.reload();}
	
	function filterDataType(dato){$('#contactos').DataTable().column(7).search(dato).draw()}
	
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
			  url 		: "js_actions.php?action=getContactos",
			  dataSrc	: ''
		  },
		  columnDefs	: [{
                targets		: [ 0, 7 ],
                visible		: false,
                searchable	: true
            }]
		}).column(7).search($('#contact').val()).draw()
	  
  })
</script>
</body>
</html>