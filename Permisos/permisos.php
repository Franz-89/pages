<?php
$fichero = str_replace(".php", "", basename(__FILE__));

session_start();
if (isset($_SESSION['email'])){$usuario = $_SESSION['email'];} else {header ("Location: /Enertrade/index.php");}
session_write_close();

if ($usuario == "mmontero@enertrade.es" || $usuario == "fannunziato@enertrade.es" || $usuario == "vmrodriguez@enertrade.es"){}else{header ("Location: /Enertrade/pages/Home.php"); die;}

if (isset($_GET['mod'])){$msg = "Permisos actualizados!";} else {$msg = "";}

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Permisos
        <small><strong><font color="red">
			<?php echo $msg;?>
		</font></strong></small>
           &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.yuhhamj8inc4' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li class="active">Home</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content container-fluid">
		
		<div class="box">
			<div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Empleado</label>
                            <select class="form-control select2" style="width: 100%;" id="usuario">
                                <?php
                                $Lista = new Lista('mail_empleados');
                                $Lista->print_list();
                                unset($Lista);
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>Horario desde</label>
                        <div class="form-group">
                            <input type="text" class="form-control timepicker" id="horario_desde">
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <label>Horario hasta</label>
                        <div class="form-group">
                            <input type="text" class="form-control timepicker" id="horario_hasta">
                        </div>
                    </div>
                    
                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <div class="form-group">
                            <button type="button" class="btn btn-success btn-flat" onclick="setHorario()"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                </div>
                
				<table id="permisos" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>USUARIO</th>
							<th>MOD_FICHADAS</th>
							<th>HORARIO DESDE</th>
							<th>HORARIO HASTA</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>

    </section>
    <!-- /.content -->
  
<?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php") ?>

<script>
	
	function setPermiso(valor){
		$.ajax({
			url: "js_actions.php?action=setPermiso" ,
			method: 'POST',
			data: {
				id: valor
			},
			async: true
		})
	}
    
    function setHorario(){
        
		$.ajax({
			url: "js_actions.php?action=setHorario" ,
			method: 'POST',
			data: {
				usuario: $('#usuario').val(),
				horario_desde: $('#horario_desde').val(),
				horario_hasta: $('#horario_hasta').val()
			},
			async: true,
            success: function (){
                setTimeout(reloadPermisos(), 500)
            }
		})
	}
    
    function reloadPermisos(){$('#permisos').DataTable().ajax.reload();}
	  
  $('#permisos').DataTable({
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
	  dom			: '<"top"f>rt<"bottom"ilp><"clear">',
	  order			: [[ 1, "asc" ]],

	  ajax			: {
		  url 		: "js_actions.php?action=getPermisos",
		  dataSrc	: ''
	  },
	  columnDefs	: [{
			targets		: [ 0 ],
			visible		: false,
			searchable	: false
		}]
	})
	
</script>
	  
</body>
</html>