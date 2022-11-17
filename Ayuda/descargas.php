<?php
$fichero = str_replace(".php", "", basename(__FILE__));

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	    <div class="progress active" style="display:none" id="progressbarview">
			<div id="progressbarvalue" class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%">
			</div>
		</div>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Descargas &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.8kq7d94rqg5v' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li>Ayuda</li>
        <li class="active">Descargas</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content container-fluid">
		<div class="row">
			<div class="col-md-3">
                
                <div class="box box-primary">
                    <form role="form" method="post" action="descargas_actions.php" enctype="multipart/form-data">
                        <div class="box-header"><h3>DATADIS</h3></div>
                        <div class="box-body">
                            <div class="form-group">
                                <label>Cliente</label>

                                <select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cli">
                                    <?php
                                    $Lista = new Lista('clientes');
                                    $Lista->print_list($cli);
                                    ?>
                                </select>
                            </div>
                            
                            <!-- Desde -->
                              <div class="form-group">
                                <label>Desde:</label>

                                <div class="input-group date">
                                  <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                  </div>
                                  <input type="text" class="form-control pull-right fecha" name="desde" value="">
                                </div>
                              </div>

                            <!-- Hasta -->
                              <div class="form-group">
                                <label>Hasta (no incluido):</label>

                                <div class="input-group date">
                                  <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                  </div>
                                  <input type="text" class="form-control pull-right fecha" name="hasta" value="">
                                </div>
                              </div>

                          <div class="form-group">
                              <div class="form-group">
                                <label>Listado de CUPS (.xlsx)</label>
                                <input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]">
                                <input type="hidden" name="MAX_FILE_SIZE" value = "10000000000000" />

                                <p class="help-block">Listado de CUPS con <a href="plantillas/CUPS DATADIS.xlsx">este</a> formato (.xlsx)</p>
                              </div>

                            <div class="box-footer">
                                <!--<button type="submit" name="action" value="download_curvas_datadis" class="btn btn-success"><i class="fa fa-download"></i></button>-->
                            </div>
                        </div>
                    </div>
                    </div>
                </form>
			</div>
			
			<!-- Facturas -->
			<div class="col-md-3">
				<form role="form" method="post" action="descargas_actions.php" enctype="multipart/form-data">
				  <div class="box box-primary">
					  <div class="box-header with-border">
							<h3 class="box-title">Facturas</h3>
						  	<div class="box-tools pull-right">
								<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-dwldfras"><i class="fa fa-info-circle"></i></button>
							  </div>
						</div>
					<div class="box-body">
						
						<div class="form-group">
							<label>Cliente</label>

							<select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cli" id="cliente" onchange="update_comercializadoras()">
								<?php
								$Lista->print_list($cli);
								?>
							</select>
						</div>
						
						<div class="form-group">
							<label>Comercializadora</label>
							<select class="form-control select2" style="width: 100%;" id="comercializadora" name="comm" onchange="update_usuario()">
							</select>
						</div>
						
						<div class="form-group">
							<label>Usuario</label>
							<select class="form-control select2" style="width: 100%;" id="usuario" name="usuario">
							</select>
						</div>
                        
                        <div class="form-group" id="bearer_nexus_facturas" style="display: none;">
                            <label>Bearer</label>

                            <div class="input-group date">
                              <input type="text" class="form-control" style="width: 100%;" id="bearer" name="bearer" value="">
                            </div>
                          </div>
                        
                        <div class="form-group" id="ssid_viesgo_facturas" style="display: none;">
                            <label>JSESSIONID</label>

                            <div class="input-group date">
                              <input type="text" class="form-control" style="width: 100%;" id="ssid" name="ssid" value="">
                            </div>
                          </div>
						
                        <div class="form-group">
                            <label>
                              <div><input type="checkbox" class="flat-red" name="carpeta_fras" value="carpeta_fras"> <label> Carpeta a parte</label></div>
                            </label>
                        </div>
                        
					  <div class="form-group">
						  <div class="form-group">
							<label>Listado de facturas (.xlsx)</label>
							<input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]">
							<input type="hidden" name="MAX_FILE_SIZE" value = "10000000000000" />

							<p class="help-block" id="listado_fras">Listado de facturas con <a href="plantillas/listado_fras.xlsx">este</a> formato (.xlsx)</p>
							<p class="help-block" id="listado_fras_nexus">Listado de facturas NEXUS con <a href="plantillas/listado_fras NEXUS.xlsx">este</a> formato (.xlsx)</p>
						  </div>
						<div class="box-footer">
							<button type="submit" name="action" value="download_fras" class="btn btn-success"><i class="fa fa-download"></i></button>
							<button type="submit" name="action" value="rename_fras" class="btn btn-primary"><i class="fa fa-refresh"> Renombrar</i></button>
						</div>
					  </div>
					</div>
				  </div>
				</form>
			</div>
			
			
			<!-- INFO -->
			  <div class="modal fade" id="modal-dwldfras">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">DESCARGA FACTURAS</h4>
					  </div>
					  <div class="modal-body">
						<p>
							Tras haber seleccionado Cliente, Comercializadora, Usuario y un fichero con un listado de numeros de facturas, descarga los datos desde las siguientes comercializadoras:
							<ul>
								<li>ENDESA</li>
								<li>EDP</li>
								<li>NATURGY</li>
								<li>VIESGO</li>
								<li>NEXUS</li>
							</ul>
							En el caso de VIESGO es necesario haber subido las facturas a la intranet primero.<br>
                            Además en VIESGO y NEXUS se necesita un dato adicional.<br>
                          Se puede recuperar de la manera indicada <a href="https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.ua6swecq5sau" target=”_blank”>aquí</a> para VIESGO y <a href="https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.onudfw4vp5m" target=”_blank”>aquí</a> para NEXUS.
                          <br>
                          <br>
                          El botón "Renombrar", renombrará correctamente todas las facturas en las carpetas "FACTURAS EVM", "FACTURAS TOTAL ELEIA" y "FACTURAS NEXUS" en la NAS y las copiará en automático en NAS/FACTURAS.
						</p>
					  </div>
					  <div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
					  </div>
					</div>
				  </div>
				</div>
			
			<!-- GDOs -->
			<div class="col-md-3">
				<form role="form" method="post" action="descargas_actions.php" enctype="multipart/form-data">
				  <div class="box box-primary">
					  <div class="box-header with-border">
							<h3 class="box-title">GDOs</h3>
						  	<div class="box-tools pull-right">
								<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-dwldgdos"><i class="fa fa-info-circle"></i></button>
							  </div>
						</div>
					<div class="box-body">
						
						<div class="form-group">
							<label>Año</label>

							<select class="form-control select2" style="width: 100%;" name="ano" id="ano">
								<?php
								$Lista->change_list('ano');
								$Lista->print_list(date('Y'));
								?>
							</select>
						</div>
						
						<div class="form-group">
							<label>Tipo fichero</label>

							<select class="form-control select2" style="width: 100%;" name="tipo" id="tipo">
								<option selected="selected" value="PDF">PDF</option>
								<option value="XLS">XLS</option>
							</select>
						</div>
						
					  <div class="form-group">
						  <div class="form-group">
							<label>Listado de CUPS (.xlsx)</label>
							<input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]">
							<input type="hidden" name="MAX_FILE_SIZE" value = "10000000000000" />

							<p class="help-block">Listado de CUPS con <a href="plantillas/CUPS.xlsx">este</a> formato (.xlsx)</p>
						  </div>
						<div class="box-footer">
							<button type="submit" name="action" value="download_gdos" class="btn btn-success"><i class="fa fa-download"></i></button>
						</div>
					  </div>
					</div>
				  </div>
				</form>
			</div>
			
			
      
              <!-- CdC -->
			<div class="col-md-3">
				<form role="form" method="post" action="descargas_actions.php" enctype="multipart/form-data">
				  <div class="box box-primary">
					  <div class="box-header with-border">
							<h3 class="box-title">Curvas</h3>
						  	<div class="box-tools pull-right">
								<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-dwldcurvas"><i class="fa fa-info-circle"></i></button>
							  </div>
						</div>
					<div class="box-body">
						
						<div class="form-group">
							<label>Cliente</label>

							<select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cli" id="cliente2" onchange="update_comercializadoras2()">
								<?php
								$Lista->change_list('clientes');
								$Lista->print_list($cli);
								unset($Lista);
								?>
							</select>
						</div>
						
						<div class="form-group">
							<label>Comercializadora</label>
							<select class="form-control select2" style="width: 100%;" id="comercializadora2" name="comm" onchange="update_usuario2()">
							</select>
						</div>
						
						<div class="form-group">
							<label>Usuario</label>
							<select class="form-control select2" style="width: 100%;" id="usuario2" name="usuario">
							</select>
						</div>
						
                        <!-- Desde -->
                          <div class="form-group">
                            <label>Desde:</label>

                            <div class="input-group date">
                              <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                              </div>
                              <input type="text" class="form-control pull-right fecha" id="desde" name="desde" value="" onchange="saveDesde($(this).val())">
                            </div>
                          </div>

                        <!-- Hasta -->
                          <div class="form-group">
                            <label>Hasta (no incluido):</label>

                            <div class="input-group date">
                              <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                              </div>
                              <input type="text" class="form-control pull-right fecha" id="hasta" name="hasta" value="" onchange="saveHasta($(this).val())">
                            </div>
                          </div>
                        
                        <!-- Hasta -->
                          <div class="form-group" id="bearer_nexus_curva" style="display: none;">
                            <label>Bearer (Nexus)</label>

                            <div class="input-group date">
                              <input type="text" class="form-control" style="width: 100%;" id="bearer" name="bearer" value="">
                            </div>
                          </div>
                        
					  <div class="form-group">
						  <div class="form-group">
							<label>Listado de CUPS (.xlsx)</label>
							<input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]">
							<input type="hidden" name="MAX_FILE_SIZE" value = "10000000000000" />

							<p class="help-block">Listado de CUPS con <a href="plantillas/CUPS.xlsx">este</a> formato (.xlsx)</p>
						  </div>
                          
                          <div class="form-group" id="pagina_html_naturgy" style="display: none;">
							<label>Pagina html de Naturgy (.html)</label>
							<input type="file" accept=".html" name ="naturgy[]">
							<input type="hidden" name="MAX_FILE_SIZE" value = "10000000000000" />

							<p class="help-block"><a href="plantillas/Naturgy - Canal Cliente_files.zip">Esta</a> pagina que se puede guardar con Ctrl+S (.html)</p>
						  </div>
                          
						<div class="box-footer">
							<button type="submit" name="action" value="download_curvas" class="btn btn-success"><i class="fa fa-download"></i></button>
						</div>
					  </div>
					</div>
				  </div>
				</form>
			</div>
          
      
              <!-- INFO -->
			  <div class="modal fade" id="modal-dwldcurvas">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">DESCARGA CURVAS</h4>
					  </div>
					  <div class="modal-body">
						<p>
							Descarga las curvas desde las siguientes comercializadoras:
							<ul>
								<li>NATURGY</li>
								<li>NEXUS (3.0TD horarias)</li>
							</ul>
                            En NEXUS se necesita un dato adicional que hay que recuperar de la manera indicada <a href="https://sites.google.com/view/wikienertrade/francesco/intranet/apartadados#h.onudfw4vp5m" target=”_blank”>aquí</a>.<br>
                            En NATURGY se necesita una pagina HTML que se puede descargar de la manera indicada <a href="https://sites.google.com/view/wikienertrade/francesco/intranet/apartadados#h.2m27mi37622w" target=”_blank”>aquí</a>.
						</p>
					  </div>
					  <div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
					  </div>
					</div>
				  </div>
				</div>
		</div>
    </section>
    <!-- /.content -->
  
<?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php") ?>

<script>
	
	function esperar(){
		setTimeout(function(){progressBar()}, 500)
	}
	
	function progressBar(){
		
		var timestamp
		var porcentaje
		porcentaje = true;
		
		//Espera un segundo para ver el nombre de la carpeta de la sesión
		$.ajax({
			url: "descargas_actions.php",
			async: false,
			method: "POST",
			data: {action: 'get_timestamp'},
			success: function (data) {timestamp = data;}
		})
		
		$('#progressbarview').toggle();
		updateBar(timestamp);
	}
	
	function updateBar(timestamp){
		var porcentaje
		$.ajax({
			url: "descargas_actions.php",
			method: "POST",
			data: {
				timestamp: timestamp,
				action: 'get_porcentaje'
			},
			async: false,
			success: function (data) {porcentaje = data;}
		})
		if (porcentaje){
			$('#progressbarvalue').attr('style', 'width: ' + porcentaje + '%');
			setTimeout(function(){updateBar(timestamp)}, 400);
		} else {
			$('#progressbarview').toggle();
		}
	}
	
	
	function update_comercializadoras(){
        
		$.ajax({
			url:	'descargas_actions.php',
			method:	'POST',
			data:	{
				action:	'get_comercializadoras',
				cliente: $('#cliente').val()
			},
			async: false,
			success: function(data){
				$('#comercializadora').empty()
				data = JSON.parse(data)
				var i;
				for (i=0; i<Object.keys(data).length; i++){
					$('#comercializadora').append('<option value="'+data[i].COMM_DISTR+'">'+data[i].COMM_DISTR+'</option>')
				}
			}
		})
		update_usuario()
	}
	
	function update_usuario(){
        
        if ($('#comercializadora').val()=='NEXUS'){
            $('#bearer_nexus_facturas').show();
            $('#listado_fras_nexus').show();
            $('#listado_fras').hide();
        }
        else {
            $('#bearer_nexus_facturas').hide();
            $('#listado_fras_nexus').hide();
            $('#listado_fras').show();
        }
        
        if ($('#comercializadora').val()=='VIESGO'){$('#ssid_viesgo_facturas').show();}
        else {$('#ssid_viesgo_facturas').hide();}
        
		$.ajax({
			url:	'descargas_actions.php',
			method:	'POST',
			data:	{
				action:	'get_usuario',
				cliente: $('#cliente').val(),
				comm: $('#comercializadora').val()
			},
			async: false,
			success: function(data){
				$('#usuario').empty()
				data = JSON.parse(data)
				var i;
				for (i=0; i<Object.keys(data).length; i++){
					$('#usuario').append('<option value="'+data[i].USUARIO+'">'+data[i].USUARIO+'</option>')
				}
			}
		})
	}
    
    function update_comercializadoras2(){
		$.ajax({
			url:	'descargas_actions.php',
			method:	'POST',
			data:	{
				action:	'get_comercializadoras',
				cliente: $('#cliente2').val()
			},
			async: false,
			success: function(data){
				$('#comercializadora2').empty()
				data = JSON.parse(data)
				var i;
				for (i=0; i<Object.keys(data).length; i++){
					$('#comercializadora2').append('<option value="'+data[i].COMM_DISTR+'">'+data[i].COMM_DISTR+'</option>')
				}
			}
		})
		update_usuario2()
	}
	
	function update_usuario2(){
        
        if ($('#comercializadora2').val()=='NEXUS'){$('#bearer_nexus_curva').show();}
        else {$('#bearer_nexus_curva').hide();}
        
        if ($('#comercializadora2').val()=='NATURGY'){$('#pagina_html_naturgy').show();}
        else {$('#pagina_html_naturgy').hide();}
        
		$.ajax({
			url:	'descargas_actions.php',
			method:	'POST',
			data:	{
				action:	'get_usuario',
				cliente: $('#cliente2').val(),
				comm: $('#comercializadora2').val()
			},
			async: false,
			success: function(data){
				$('#usuario2').empty()
				data = JSON.parse(data)
				var i;
				for (i=0; i<Object.keys(data).length; i++){
					$('#usuario2').append('<option value="'+data[i].USUARIO+'">'+data[i].USUARIO+'</option>')
				}
			}
		})
	}
	
	update_comercializadoras()
	update_comercializadoras2()
	
</script>
	  
</body>
</html>