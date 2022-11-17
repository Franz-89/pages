<?php
$fichero = str_replace(".php", "", basename(__FILE__));

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");

$cli = (isset($_SESSION['info_ficha_cliente']['cli'])) ? $_SESSION['info_ficha_cliente']['cli'] : '';

?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Ficha cliente  &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.jm6k69f4nsvg' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li>Info</li>
        <li class="active">Ficha cliente</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
	
      <div class="row">
		  
		  <div class="col-md-12">
          	<div class="box box-primary">
            	<div class="box-body">
					
					<div class="row">
						<div class="col-md-3">
                            
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
				    </div>
				</div>
            </div>
                    

            <div id="box_gestores" class="box box-primary">
                <div class="box-header with-border">
                    <h3>Gestores</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
            	<div class="box-body">
                    <div class="row">
                        <div class="col-md-2">
							<div class="form-group">
								<label>BackOffice:</label>
								<select class="form-control select2" style="width: 100%;" id="backoffice" onchange="save('backoffice')">
                                    <option value="" selected="selected"></option>
									<?php
									$Lista->change_list('mail_empleados');
									$Lista->print_list();
									?>
								</select>
							</div>
						</div>
                        <div class="col-md-2">
							<div class="form-group">
								<label>FrontOffice:</label>
								<select class="form-control select2" style="width: 100%;" id="frontoffice" onchange="save('frontoffice')">
                                    <option value="" selected="selected"></option>
									<?php
									$Lista->print_list();
									?>
								</select>
							</div>
						</div>
                        <div class="col-md-2">
							<div class="form-group">
								<label>Gestor comercial:</label>
								<select class="form-control select2" style="width: 100%;" id="gestor_comercial" onchange="save('gestor_comercial')">
                                    <option value="" selected="selected"></option>
									<?php
									$Lista->print_list();
									?>
								</select>
							</div>
						</div>
                        <div class="col-md-2">
							<div class="form-group">
								<label>Responsable tractor:</label>
								<select class="form-control select2" style="width: 100%;" id="responsable_tractor" onchange="save('responsable_tractor')">
                                    <option value="" selected="selected"></option>
									<?php
									$Lista->print_list();
									?>
								</select>
							</div>
						</div>
                    </div>
                </div>
            </div>
                    

            <div id="box_resumen" class="box box-primary">
                <div class="box-header with-border">
                    <h3>Resumen</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
            	<div class="box-body">
                    <div class="row">
                        <div class="col-md-2">
							<div class="form-group">
                                <label>Suministros en vigor:</label>
                                <input type="number" class="form-control" placeholder="Cups en vigor" id="cups_en_vigor" value="" disabled="disabled">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label>Por tarifa:</label>
                            <table id="cups_por_tarifa" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>2.0TD</th>
                                        <th>3.0TD</th>
                                        <th>6.1TD</th>
                                        <th>6.2TD</th>
                                        <th>6.3TD</th>
                                        <th>6.4TD</th>
                                    </tr>
						        </thead>
							</table>
                        </div>
					</div>
					
                    <hr>
                
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Consumo anual:</label>
                                <table id="consumo_anual" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>AÑO</th>
                                            <th>CONSUMO (kW/h)</th>
                                        </tr>
						            </thead>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label>Por tarifa:</label>
                            <table id="consumo_anual_por_tarifa" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>AÑO</th>
                                        <th>2.0TD</th>
                                        <th>3.0TD</th>
                                        <th>6.1TD</th>
                                        <th>6.2TD</th>
                                        <th>6.3TD</th>
                                        <th>6.4TD</th>
                                    </tr>
						        </thead>
							</table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="box_empresas" class="box box-primary">
                <div class="box-header with-border">
                    <h3>Empresas</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
            	<div class="box-body">  
                    <div class="row">
                        <div class="col-md-12">
                            <table id="empresas" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>CIF</th>
                                        <th>RAZÓN SOCIAL</th>
                                    </tr>
						        </thead>
                            </table>
                        </div>
					</div>
				</div>
            </div>
              
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
                            
                            <form onsubmit="insert_ff();return false">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Comercializadora</label>
                                        <select class="form-control select2" style="width: 100%;" id="comm_ff" required>
                                            <option value="" selected="selected"></option>
                                            <?php
                                            $Lista->change_list('comm_elec_mainsip');
                                            $Lista->print_list();
                                            unset($Lista);
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Recepeción/Descarga/Picar</label>
                                        <select class="form-control select2" style="width: 100%;" id="recepcion_descarga_ff" required>
                                            <option value="RECEPCION" selected="selected">RECEPCION</option>
                                            <option value="DESCARGA">DESCARGA</option>
                                            <option value="PICAR">PICAR</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Comentarios</label>
                                        <input type="text" class="form-control" id="comentarios_ff">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Fecha</label>
                                        <div class="input-group date">
                                          <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                          </div>
                                          <input type="text" class="form-control pull-right fecha" id="fecha_ff" required>
                                        </div>
                                     </div>
                                 </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>  </label>
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn btn-success btn-flat"><i class="fa fa-plus"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </form>
                            
                            <table id="ff" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>COMERCIALIZADORA</th>
                                        <th>RECEPCIÓN/DESCARGA/PICAR</th>
                                        <th>COMENTARIOS</th>
                                        <th>FECHA RECEPCIÓN/DESCARGA/PICAR</th>
                                        <th>ACCIONES</th>
                                    </tr>
						        </thead>
							</table>
                        </div>
					</div>
				</div>
            </div>
              
              
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
                            
                            <form onsubmit="insert_otro_informe();return false">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Informe</label>
                                        <input type="text" class="form-control" id="otro_informe" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>A</label>
                                        <input type="text" class="form-control" id="A">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>CC</label>
                                        <input type="text" class="form-control" id="CC">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Comentarios</label>
                                        <input type="text" class="form-control" id="comentarios_informe">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Fecha de envío estimada</label>
                                        <div class="input-group date">
                                          <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                          </div>
                                          <input type="text" class="form-control pull-right fecha" id="fecha_envio_informe" required>
                                        </div>
                                     </div>
                                 </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>  </label>
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn btn-success btn-flat"><i class="fa fa-plus"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </form>
                            <table id="otros_informes" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>INFORME</th>
                                        <th>A</th>
                                        <th>CC</th>
                                        <th>COMENTARIOS</th>
                                        <th>FECHA ENVÍO</th>
                                        <th>ACCIONES</th>
                                    </tr>
						        </thead>
							</table>
                        </div>
					</div>
				</div>
            </div>
                    
            <div id="box_contactos" class="box box-primary">
                <div class="box-header with-border">
                    <h3>Contactos</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
            	<div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Cliente</h4>
                            <table id="contactos_cli" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>CONTACTO</th>
                                        <th>EMAIL</th>
                                        <th>TELEFONO</th>
                                        <th>COMENTARIOS</th>
                                    </tr>
						        </thead>
							</table>
                        </div>
                        
                        <div class="col-md-6">
                            <h4>Gestores</h4>
                            <table id="contactos_gestores" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>CONTACTO</th>
                                        <th>EMAIL</th>
                                        <th>TELEFONO</th>
                                        <th>COMENTARIOS</th>
                                        <th>COMERCIALIZADORA</th>
                                    </tr>
						        </thead>
							</table>
                        </div>
					</div>
                    
					
				</div>
				<div class="overlay" id="loading">
					<i class="fa fa-refresh fa-spin"></i>
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
		if(confirm("Si se está eliminando un informe o un fichero de facturación se perderán todos los datos historicos de carga y envío. Esta acción NO se puede anular. Proceder?"))
		{
			return true;
		}
		return false;
	}
    
    saveCli();
    
    function reloadValues(){
        saveCli()
        getCupsEnVigor();
        getDatosFicha();
        $('#cups_por_tarifa').DataTable().ajax.reload();
        $('#consumo_anual').DataTable().ajax.reload();
        $('#consumo_anual_por_tarifa').DataTable().ajax.reload();
        $('#otros_informes').DataTable().ajax.reload();
        $('#ff').DataTable().ajax.reload()
        $('#empresas').DataTable().ajax.reload();
        $('#contactos_cli').DataTable().ajax.reload();
        $('#contactos_gestores').DataTable().ajax.reload();
    }
    
    function getDatosFicha(){
        $.ajax({
			url: "js_actions.php?action=getDatosFicha" ,
			method: 'POST',
			async: true,
			success: function(data){
				if (data){
					data = data.split('|');
                    
					$('#backoffice').val(JSON.parse(data[0])).change();
					$('#frontoffice').val(JSON.parse(data[1])).change();
					$('#gestor_comercial').val(JSON.parse(data[2])).change();
					$('#responsable_tractor').val(JSON.parse(data[3])).change();
				} else {
                    $('#backoffice').val('').change();
					$('#frontoffice').val('').change();
					$('#gestor_comercial').val('').change();
					$('#responsable_tractor').val('').change();
                }
                
			}
		})
    }
    
    function saveCli(){
        $.ajax({
			url: "js_actions.php?action=saveVarsFichaCliente" ,
			method: 'POST',
            data: {
                cli: $('#cli').val()
			},
			async: false
		})
    }
    
	function getCupsEnVigor(){
		$.ajax({
			url: "js_actions.php?action=getCupsEnVigor" ,
			method: 'POST',
			async: true,
			success: function(result){
				$('#cups_en_vigor').val(result)
			}
		})
	}
    
    function save(tipo){
        
        var valor = $('#' + tipo).val()
        
        $.ajax({
			url: "js_actions.php?action=saveDatoFichaCliente" ,
			method: 'POST',
			data: {
                tipo: tipo,
				dato: valor
			},
			async: true
		})
    }
    
    function insert_otro_informe(){
        $.ajax({
			url: "js_actions.php?action=insertOtroInforme" ,
			method: 'POST',
			data: {
				otro_informe: $('#otro_informe').val(),
				comentarios_informe: $('#comentarios_informe').val(),
				A: $('#A').val(),
				CC: $('#CC').val(),
				fecha_envio_informe: $('#fecha_envio_informe').val()
			},
			async: true,
			success: function(result){
				setTimeout($('#otros_informes').DataTable().ajax.reload(), 200);
                $('#otro_informe').val('')
                $('#comentarios_informe').val('')
                $('#A').val('')
                $('#CC').val('')
                $('#fecha_envio_informe').val('')
			}
		})
    }
    
    function delOtroInforme(id){
        if (confirmar()){
            $.ajax({
                url: "js_actions.php?action=delOtroInforme" ,
                method: 'POST',
                data: {
                    id: id
                },
                async: true,
                success: function(){
                    setTimeout($('#otros_informes').DataTable().ajax.reload(), 200);
                }
            })
        }
    }
    
    function insert_ff(){
        $.ajax({
			url: "js_actions.php?action=insertFf" ,
			method: 'POST',
			data: {
				comm_ff: $('#comm_ff').val(),
				recepcion_descarga_ff: $('#recepcion_descarga_ff').val(),
				comentarios_ff: $('#comentarios_ff').val(),
				fecha_ff: $('#fecha_ff').val()
			},
			async: true,
			success: function(result){
                setTimeout($('#ff').DataTable().ajax.reload(), 200);
                $('#comm_ff').val('')
                $('#comentarios_ff').val('')
                $('#fecha_ff').val('')
			}
		})
    }
    
    function delFf(id){
        if (confirmar()){
            $.ajax({
                url: "js_actions.php?action=delFf" ,
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
    
	$('#cups_por_tarifa').DataTable({
	  serverSide	: false,
	  processing	: true,
	  paging      	: false,
	  lengthChange	: true,
	  statesave		: true,
	  searching   	: false,
	  ordering    	: false,
	  info        	: true,
	  autoWidth   	: true,
	  ajax			: {
		  url :"js_actions.php?action=getCupsEnVigorPorTarifa",
		  dataSrc: '',
					  }
	})
    
    $('#consumo_anual').DataTable({
	  serverSide	: false,
	  processing	: true,
	  paging      	: false,
	  lengthChange	: true,
	  statesave		: true,
	  searching   	: false,
	  ordering    	: false,
	  info        	: true,
	  autoWidth   	: true,
	  ajax			: {
		  url :"js_actions.php?action=getConsumoAnual",
		  dataSrc: '',
					  }
	})
    
    $('#consumo_anual_por_tarifa').DataTable({
	  serverSide	: false,
	  processing	: true,
	  paging      	: false,
	  lengthChange	: true,
	  statesave		: true,
	  searching   	: false,
	  ordering    	: false,
	  info        	: true,
	  autoWidth   	: true,
	  ajax			: {
		  url :"js_actions.php?action=getConsumoAnualPorTarifa",
		  dataSrc: '',
					  }
	})
    
    $('#ff').DataTable({
	  serverSide	: false,
	  processing	: true,
	  paging      	: false,
	  lengthChange	: true,
	  statesave		: true,
	  searching   	: false,
	  ordering    	: true,
	  info        	: true,
	  autoWidth   	: true,
    order			: [[ 1, "asc" ]],
	  ajax			: {
		  url :"js_actions.php?action=getFf",
		  dataSrc: '',
					  },
	  columnDefs : [
            {
                "targets": [ 0 ],
                "visible": false,
                "searchable": false
            }]
	})
    
    $('#otros_informes').DataTable({
	  serverSide	: false,
	  processing	: true,
	  paging      	: false,
	  lengthChange	: true,
	  statesave		: true,
	  searching   	: false,
	  ordering    	: true,
	  info        	: true,
	  autoWidth   	: true,
    order			: [[ 1, "asc" ]],
	  ajax			: {
		  url :"js_actions.php?action=getOtrosInformes",
		  dataSrc: '',
					  },
	  columnDefs : [
            {
                "targets": [ 0 ],
                "visible": false,
                "searchable": false
            }]
	})
    
    $('#empresas').DataTable({
	  serverSide	: false,
	  processing	: true,
	  paging      	: false,
	  lengthChange	: true,
	  statesave		: true,
	  searching   	: false,
	  ordering    	: false,
	  info        	: true,
	  autoWidth   	: true,
	  ajax			: {
		  url :"js_actions.php?action=getEmpresas",
		  dataSrc: '',
					  }
	})
    
    $('#contactos_cli').DataTable({
	  serverSide	: false,
	  processing	: true,
	  paging      	: true,
	  lengthChange	: true,
	  statesave		: true,
	  searching   	: true,
	  ordering    	: true,
	  info        	: true,
	  autoWidth   	: true,
	  ajax			: {
		  url :"js_actions.php?action=getContactosCliente",
		  dataSrc: '',
					  }
	})
    
    $('#contactos_gestores').DataTable({
	  serverSide	: false,
	  processing	: true,
	  paging      	: true,
	  lengthChange	: true,
	  statesave		: true,
	  searching   	: true,
	  ordering    	: true,
	  info        	: true,
	  autoWidth   	: true,
	  ajax			: {
		  url :"js_actions.php?action=getContactosGestores",
		  dataSrc: '',
					  }
	})
	
	$('.overlay').toggle()
	
	$(document).ajaxStart(function(){
		  $(".overlay").show();
	  })
	  $(document).ajaxComplete(function(){
		  $(".overlay").hide();
	  })
    
    getCupsEnVigor()
    getDatosFicha()
	
</script>
	  
</body>
</html>