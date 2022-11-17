<?php
$fichero = str_replace(".php", "", basename(__FILE__));

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");

$cli = isset($_SESSION['seguimiento_cliente']['cli']) ? $_SESSION['seguimiento_cliente']['cli'] : '';
$mes = isset($_SESSION['seguimiento_cliente']['mes']) ? $_SESSION['seguimiento_cliente']['mes'] : date('01/m/Y');
$empleado = isset($_SESSION['seguimiento_cliente']['empleado']) ? $_SESSION['seguimiento_cliente']['empleado'] : '';

?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Seguimiento cliente  &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.jm6k69f4nsvg' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li>Info</li>
        <li class="active">Seguimiento cliente</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content container-fluid">
	
      <div class="row">
		  
		  <div class="col-md-6">
          	<div class="box box-primary">
            	<div class="box-body">
					<div class="row">
						<div class="col-md-8">
                            <div class="form-group">
                                <h2>Cliente</h2>
								<select class="form-control select2" style="width: 100%;" id="cli" onchange=reloadValues()>
									<?php
									$Lista = new Lista('clientes');
									$Lista->print_list($cli);
									?>
								</select>
							</div>
				        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <h2>Mes</h2>
								<select class="form-control select2" style="width: 100%;" id="mes" onchange=reloadValues()>
									<?php
									$Lista->change_list('mes');
									$Lista->print_list($mes);
									?>
								</select>
							</div>
				        </div>
				    </div>
				</div>
            </div>
        </div>
          
        <div class="col-md-6">
          	<div class="box box-primary">
                <div class="box-header with-border">
                    <h4>Clientes por empleado</h4>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                
            	<div class="box-body">
					<div class="row">
						<div class="col-md-4">
                            <div class="form-group">
                                <label>Empleado</label>
								<select class="form-control select2" style="width: 100%;" id="empleado" onchange="reloadClientesGestionados()">
									<?php
									$Lista->change_list('mail_empleados');
									$Lista->print_list($empleado);
									?>
								</select>
							</div>
                            <div class="form-group">
                                <label>Rol</label>
								<select class="form-control select2" style="width: 100%;" id="rol" onchange="reloadClientesGestionados()">
									<option value="BACKOFFICE" selected>BACKOFFICE</option>
									<option value="FRONTOFFICE">FRONTOFFICE</option>
									<option value="GESTOR_COMERCIAL">GESTOR_COMERCIAL</option>
									<option value="RESPONSABLE_TRACTOR">RESPONSABLE_TRACTOR</option>
								</select>
							</div>
				        </div>
                        <div class="col-md-8">
                            <table id="clientes_gestionados" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>CLIENTES</th>
                                    </tr>
                                </thead>
                            </table>
				        </div>
				    </div>
				</div>
            </div>
        </div>
            
        <div class="col-md-12">
              <!-- FICHERO DE FACTURACIÓN -->
            <div id="box_ff" class="box box-primary">
                    
                <div class="box-header with-border">
                    <h3>Ficheros de facturación</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            
                            <form role="form" method="post" action="actions.php" enctype="multipart/form-data">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Comercializadora</label>
                                        <select class="form-control select2" style="width: 100%;" id="comercializadora" name="comercializadora" required>
                                            <?php
                                            $Lista->change_list('comm_elec_mainsip');
                                            $Lista->print_list();
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Fecha recepeción</label>
                                        <div class="input-group date">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                            <input type="text" class="form-control pull-right fecha" id="fecha_recepcion_ff" name="fecha_recepcion_ff" value="<?php echo date('d/m/Y'); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Fecha carga intranet</label>
                                        <div class="input-group date">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                          <input type="text" class="form-control pull-right fecha" id="fecha_carga_ff" name="fecha_carga_ff" value="<?php echo date('d/m/Y'); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Fecha validación</label>
                                        <div class="input-group date">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                          <input type="text" class="form-control pull-right fecha" id="fecha_validacion_ff" name="fecha_validacion_ff" value="">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Fecha validación TE</label>
                                        <div class="input-group date">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                          <input type="text" class="form-control pull-right fecha" id="fecha_validacion_te_ff" name="fecha_validacion__te_ff" value="">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>Fras cargadas</label>
                                        <input type="number" class="form-control" id="fras_cargadas" name="fras_cargadas">
                                    </div>
                                </div>
                                
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>Abonos cargados</label>
                                        <input type="number" class="form-control" id="abonos_cargados" name="abonos_cargados">
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Comentarios</label>
                                        <input type="text" class="form-control" id="comentarios_ff" name="comentarios_ff" value="">
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Ruta</label>
                                        <input type="text" class="form-control" id="ruta_ff" name="ruta_ff" value="">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>FF</label>
                                        <input type="file" name ="fichero[]" multiple="multiple">
                                        <input type="hidden" name="MAX_FILE_SIZE" value = "10000000000000" />
                                        
                                        <p class="help-block">Fichero de facturación original</p>
                                    </div>
                                </div>

                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>  </label>
                                        <span class="input-group-btn">
                                            <button type="submit" name="action" value="upload_ff" class="btn btn-success btn-flat"><i class="fa fa-plus"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </form>
                            
                            <table id="ff" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>COMERCIALIZADORA</th>
                                        <th>RECEPCIÓN</th>
                                        <th>CARGA</th>
                                        <th>VALIDACIÓN</th>
                                        <th>VALIDACIÓN TE</th>
                                        <th>FRAS CARGADAS</th>
                                        <th>ABONOS CARGADOS</th>
                                        <th>COMENTARIOS</th>
                                        <th>RUTA</th>
                                        <th>FICHEROS</th>
                                        <th>ACCIONES</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                
            </div>
              
              <!-- INFORMES -->
            <div id="box_informes" class="box box-primary">
                
                <div class="box-header with-border">
                    <h3>Informes</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            
                            <form role="form" method="post" action="actions.php" enctype="multipart/form-data">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Informe</label>
                                        <input type="text" class="form-control" name="informe" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Redactado</label>
                                        <div class="input-group date">
                                          <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                          </div>
                                          <input type="text" class="form-control pull-right fecha" name="fecha_redactado_informe" value="<?php echo date('d/m/Y'); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Enviado</label>
                                        <div class="input-group date">
                                          <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                          </div>
                                          <input type="text" class="form-control pull-right fecha" name="fecha_envio_informe" value="<?php echo date('d/m/Y'); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Comentarios</label>
                                        <input type="text" class="form-control" name="comentarios_informe">
                                     </div>
                                 </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Ruta</label>
                                        <input type="text" class="form-control" id="ruta_informe" name="ruta_informe" value="">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Informe</label>
                                        <input type="file" name ="fichero[]" multiple="multiple">
                                        <input type="hidden" name="MAX_FILE_SIZE" value = "10000000000000" />

                                        <p class="help-block">Informe redactado</p>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>  </label>
                                        <span class="input-group-btn">
                                            <button type="submit" name="action" value="upload_informe" class="btn btn-success btn-flat"><i class="fa fa-plus"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </form>

                            <table id="informes" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>INFORME</th>
                                        <th>REDACTADO</th>
                                        <th>ENVIADO</th>
                                        <th>COMENTARIOS</th>
                                        <th>RUTA</th>
                                        <th>FICHERO</th>
                                        <th>ACCIONES</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
		  
      </div>
		

    </section>
    <!-- /.content -->
  
<?php
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php");
?>

<script>
	
	function confirmar() {
		if(confirm("Se está eliminando un fichero de su carpeta. Esta acción NO se puede anular. Proceder?"))
		{
			return true;
		}
		return false;
	}
	
    var cli = $('#cli').val();
    
    function reloadValues(){
        update_vars();
        $('#ff').DataTable().ajax.reload()
        $('#informes').DataTable().ajax.reload()
    }
    
    function reloadClientesGestionados(){
        update_vars();
        $('#clientes_gestionados').DataTable().ajax.reload()
    }
    
    function delFfSeguimiento(id){
        if (confirmar()){
            $.ajax({
                url: "js_actions.php?action=delFfSeguimiento" ,
                method: 'POST',
                data: {
                    id: id
                },
                async: true,
                success: function(){
                    setTimeout($('#ff').DataTable().ajax.reload(), 200);
                }
            })
        }
    }
    
    function delInformeSeguimiento(id){
        if (confirmar()){
            $.ajax({
                url: "js_actions.php?action=delInformeSeguimiento" ,
                method: 'POST',
                data: {
                    id: id
                },
                async: true,
                success: function(){
                    setTimeout($('#informes').DataTable().ajax.reload(), 200);
                }
            })
        }
    }
    
    function update_vars(){
        //Guarda cliente y mes en la sesión
        $.ajax({
			url:	'js_actions.php?action=saveSeguimientoClienteVars',
			method:	'POST',
			data:	{
				cli: $('#cli').val(),
                mes: $('#mes').val(),
                empleado: $('#empleado').val()
			},
			async: false
		})
    }
    
    update_vars();
    
    $('#clientes_gestionados').DataTable({
	  serverSide	: false,
	  processing	: true,
	  paging      	: true,
	  lengthChange	: true,
	  statesave		: true,
	  searching   	: false,
	  ordering    	: true,
	  info        	: true,
	  autoWidth   	: true,
      dom			: '<"top"f>rt<"bottom"ilp><"clear">',
    order			: [[ 0, "asc" ]],
	  ajax			: {
		  url :"js_actions.php?action=getClientesGestionadosSeguimiento",
          data: function(d) {
            d.empleado = $('#empleado').val();
            d.rol      = $('#rol').val();
          },
		  dataSrc: '',
					  }
	})
    
    $('#ff').DataTable({
	  serverSide	: false,
	  processing	: true,
	  paging      	: true,
	  lengthChange	: true,
	  statesave		: true,
	  searching   	: false,
	  ordering    	: true,
	  info        	: true,
	  autoWidth   	: true,
      dom			: '<"top"f>rt<"bottom"ilp><"clear">',
    order			: [[ 1, "asc" ]],
	  ajax			: {
		  url :"js_actions.php?action=getFfSeguimiento",
		  dataSrc: '',
					  },
	  columnDefs : [
            {
                "targets": [ 0 ],
                "visible": false,
                "searchable": false
            }]
	})
    
    $('#informes').DataTable({
	  serverSide	: false,
	  processing	: true,
	  paging      	: true,
	  lengthChange	: true,
	  statesave		: true,
	  searching   	: false,
	  ordering    	: true,
	  info        	: true,
	  autoWidth   	: true,
      dom			: '<"top"f>rt<"bottom"ilp><"clear">',
    order			: [[ 1, "asc" ]],
	  ajax			: {
		  url :"js_actions.php?action=getInformeSeguimiento",
		  dataSrc: '',
					  },
	  columnDefs : [
            {
                "targets": [ 0 ],
                "visible": false,
                "searchable": false
            }]
	})
	
	$('.overlay').toggle()
    
	$(document).ajaxStart(function(){
		  $(".overlay").show();
	  })
	  $(document).ajaxComplete(function(){
		  $(".overlay").hide();
	  })
    
</script>
	  
</body>
</html>