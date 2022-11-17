<?php
$fichero 	= str_replace(".php", "", basename(__FILE__));
$sql_table 	= $fichero;

$empleado = (isset($_SESSION['info']['tareas']['empleado'])) ? $_SESSION['info']['tareas']['empleado'] : '';
    
require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>

	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Tareas &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.a60xr1bio07z' target="_blank"><i class="fa fa-info"></i></a>
		  
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li>Info</li>
		<li class="active">Tareas</li>
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
                
              	<form role="form" method="post" action="actions.php" enctype="multipart/form-data">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" class="form-control" placeholder="Nombre..." name="nombre" required>
                         </div>
                     </div>
                    
                    <?php
                    
                    switch ($usuario){
                        case 'vmrodriguez@enertrade.es':
                        case 'mmontero@enertrade.es':
                        case 'slizarralde@enertrade.es':
                            $disabled = '';
                            break;
                        default:
                            $disabled = 'disabled="disabled"';
                            break;
                    }
                    
                    ?>
                    
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Empleado</label>
                            <select class="form-control select2" style="width: 100%;" name="empleado" id="empleado" onchange="filterData($(this).val())"  >
                                
                                <?php
                                
                                $Lista = new Lista('mail_empleados');
                                
                                switch ($usuario){
                                    case 'vmrodriguez@enertrade.es':
                                    case 'mmontero@enertrade.es':
                                    case 'slizarralde@enertrade.es':
                                        $Lista->print_list($empleado);
                                        break;
                                        
                                    default:
                                        echo "<option>$usuario</option>";
                                        
                                }
                                
                                ?>
                            </select>
                         </div>
                     </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Fecha caducidad</label>
                             <div class="input-group date">
								  <div class="input-group-addon">
								      <i class="fa fa-calendar"></i>
								  </div>
								  <input type="text" class="form-control pull-right fecha" name="fecha_caducidad">
				            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>Prioridad</label>
                            <select class="form-control select2" style="width: 100%;" data-placeholder="Prioridad" name="prioridad">
                                <?php
                                $Lista->change_list('prioridad');
                                $Lista->print_list(10);
                                unset($Lista);
                                ?>
                            </select>
                         </div>
				    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Descripción</label>
                            <input type="text" class="form-control" name="descripcion" placeholder="Descripcion...">
                         </div>
                     </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Comentarios</label>
                            <input type="text" class="form-control" name="comentarios" placeholder="Comentarios...">
                         </div>
                     </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Adjuntos</label>
                            <input type="file" name ="fichero[]" multiple="multiple">
                            <input type="hidden" name="MAX_FILE_SIZE" value = "10000000000000" />
                        </div>
                    </div>
                    
                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-flat" name="action" value="add_tarea"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                </form>
                
                
                <div class="col-md-12">
                    <h2>En curso</h2>
                    <div class="col-md-12">
                        <a class="btn btn-app" href="js_actions.php?action=dwd_tareas_en_curso">
                            <i class="fa fa-file-excel-o"></i> Descargar
                        </a>
                    </div>
                    <table id="tareas_no_gestionadas" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>NOMBRE</th>
                                <th>EMPLEADO</th>
                                <th>ASIGNADO POR</th>
                                <th>FECHA APERTURA</th>
                                <th>FECHA CADUCIDAD</th>
                                <th>PRIORIDAD</th>
                                <th>DESCRIPCIÓN</th>
                                <th>COMENTARIOS</th>
                                <th>ADJUNTOS</th>
                                <th>PROGRESO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                
                <div class="col-md-12">
                    <h2>Gestionadas</h2>
                    <div class="col-md-12">
                        <a class="btn btn-app" href="js_actions.php?action=dwd_tareas_gestionadas">
                            <i class="fa fa-file-excel-o"></i> Descargar
                        </a>
                    </div>
                    <table id="tareas_gestionadas" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>NOMBRE</th>
                                <th>EMPLEADO</th>
                                <th>ASIGNADO POR</th>
                                <th>FECHA APERTURA</th>
                                <th>FECHA CIERRE</th>
                                <th>FECHA CADUCIDAD</th>
                                <th>PRIORIDAD</th>
                                <th>DESCRIPCIÓN</th>
                                <th>COMENTARIOS</th>
                                <th>ADJUNTOS</th>
                                <th>PROGRESO</th>
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
    if(confirm("Ejecutar esta acción?")){
        return true;
    }
    return false;
    }
	
    function addTarea(){

          $.ajax({
                url: "js_actions.php?action=addTarea",
                method: "POST",
                data: {
                    nombre: $('#nombre').val(),
                    empleado: $('#empleado').val(),
                    fecha_caducidad: $('#fecha_caducidad').val(),
                    prioridad: $('#prioridad').val(),
                    descripcion: $('#descripcion').val(),
                    comentarios: $('#comentarios').val(),
                    recurrente: $('#recurrente').val()
                },
                async: false,
                success: function () {setTimeout(reloadTareas(), 500)}
            })
	  }
    
    function delTarea(valor){
		  if (confirmation()){
			  $.ajax({
					url: "js_actions.php?action=delTarea",
					method: "POST",
					data: {id: valor},
					async: false,
					success: function () {
                        setTimeout(reloadTareas(), 500)
                    }
				})
		  }
	  }
    
    function setTareaAsDone(valor){
		  if (confirmation()){
			  $.ajax({
					url: "js_actions.php?action=setTareaAsDone",
					method: "POST",
					data: {id: valor},
					async: false,
					success: function () {
                        setTimeout(reloadTareas(), 500)
                    }
				})
		  }
	  }
    
    function reactivateTarea(valor){
		  if (confirmation()){
			  $.ajax({
					url: "js_actions.php?action=reactivateTarea",
					method: "POST",
					data: {id: valor},
					async: false,
					success: function () {
                        setTimeout(reloadTareas(), 500)
                    }
				})
		  }
	  }
	
	function reloadTareas() {
        $('#tareas_no_gestionadas').DataTable().ajax.reload();
        $('#tareas_gestionadas').DataTable().ajax.reload();
    }
	
    function filterData(dato){
        $('#tareas_no_gestionadas').DataTable().column(2).search(dato).draw();
        $('#tareas_gestionadas').DataTable().column(2).search(dato).draw();
        saveEmpleado(dato);
    }
    
    function saveEmpleado(empleado){
        $.ajax({
            url: "js_actions.php?action=save_empleado",
            method: "POST",
            data: {empleado: empleado},
            async: false
        })
    }
    	
	  $('#tareas_no_gestionadas').DataTable({
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
		  ajax			: {
			  url 		: "js_actions.php?action=getTareasNoGestionadas",
			  dataSrc	: ''
		  },
		  columnDefs	: [{
                targets		: [ 0 ],
                visible		: false,
                searchable	: false
            }]
		}).column(2).search($('#empleado').val()).draw()
      
      $('#tareas_gestionadas').DataTable({
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
		  ajax			: {
			  url 		: "js_actions.php?action=getTareasGestionadas",
			  dataSrc	: ''
		  },
		  columnDefs	: [{
                targets		: [ 0 ],
                visible		: false,
                searchable	: false
            }]
		}).column(2).search($('#empleado').val()).draw()
	  

</script>
</body>
</html>