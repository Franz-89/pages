<?php
$fichero = str_replace(".php", "", basename(__FILE__));
$sql_table = $fichero;

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$Conn = new Conn('local', 'enertrade');	

$fecha_desde_componentes 	= date_from_to_format($Conn->oneData("SELECT MAX(fecha) FROM componentes_precio WHERE mercado_diario!=0"), 'Y-m-d H:i:s', 'd/m/Y');
$fecha_hasta_componentes 	= date('d/m/Y');
$fecha_kest 				= date_from_to_format($Conn->oneData("SELECT MAX(fecha) FROM componentes_precio WHERE kest!='NULL'"), 'Y-m-d H:i:s', 'd/m/Y');
$fecha_perfiles 			= date_from_to_format($Conn->oneData("SELECT MAX(fecha) FROM componentes_precio WHERE perfil_a!='NULL'"), 'Y-m-d H:i:s', 'd/m/Y');
$fecha_pvpc 			    = date_from_to_format($Conn->oneData("SELECT MAX(fecha) FROM componentes_precio WHERE PVPC_20A!='NULL'"), 'Y-m-d H:i:s', 'd/m/Y');

unset($Conn);

include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Precios
        <small>Elaboraciónes de precios</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li>Operaciones</li>
        <li class="active">Precios</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
		   
	<div class="row">
		
		
		<div class="col-md-12">
			<div class="row">
				
				<!-- Componentes -->
				<div class="col-md-3">
					<div class="box box-primary">
						<div class="box-body">
							<div class="box-header with-border">
								<h3 class="box-title">Componentes precio</h3>
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-componentesprecio"><i class="fa fa-info-circle"></i></button>
								</div>
							</div>
							
							<div class="col-md-12">
								<form role="form" method="post" action="consulta_mercado_actions.php" name="telemedida_form" enctype="multipart/form-data">
									<!-- Desde -->
									<div class="form-group">
										<label>Desde:</label>

										<div class="input-group date">
											<div class="input-group-addon">
												<i class="fa fa-calendar"></i>
											</div>
											<input type="text" class="form-control pull-right fecha" id="desde" name="desde" value="<?php echo $fecha_desde_componentes; ?>">
										</div>
									</div>

									<!-- Hasta -->
									<div class="form-group">
										<label>Hasta:</label>

										<div class="input-group date">
											<div class="input-group-addon">
												<i class="fa fa-calendar"></i>
											</div>
											<input type="text" class="form-control pull-right fecha" id="hasta" name="hasta" value="<?php echo $fecha_hasta_componentes; ?>">
										</div>
									</div>
									<div class="box-footer pull-right">
										<button type="submit" name="action" value="download_componentes" class="btn btn-success"><i class="fa fa-download"></i></button>
									</div>
								</form>
								<div class="box-footer">
									<button name="action" value="" class="btn btn-primary pull-right" onclick="update_todo()"><i class="fa fa-refresh"></i></button>
								</div>
								<p><b>SSAA</b> actualizados hasta el <u id="fecha_componentes"><?php echo $fecha_desde_componentes; ?></u></p>
								<p><b>Kest</b> actualizado hasta el <u id="fecha_kest"><?php echo $fecha_kest; ?></u></p>
								<p><b>PVPC</b> actualizados hasta el <u id="fecha_pvpc"><?php echo $fecha_pvpc; ?></u></p>
								<p><b><u><a href="https://www.ree.es/es/actividades/operacion-del-sistema-electrico/medidas-electricas#" target="_blank">Perfiles</a></u></b> actualizados hasta el <u id="fecha_perfiles"><?php echo $fecha_perfiles; ?></u></p>
                                
                                <form role="form" method="post" action="consulta_mercado_actions.php" enctype="multipart/form-data">
									<div class="form-group">
										<div class="form-group">
											<label>Perfiles (.gz)</label>
											<input type="file" accept=".gz" name="fichero[]" multiple="multiple">
											<input type="hidden" name="MAX_FILE_SIZE" value = "10485760" />
										</div>
										<div class="box-footer pull-right">
											<button class="btn btn-success" name="action" value="perfiles"><i class="fa fa-upload"></i></button>
										</div>
									</div>
								</form>
							</div>
							
						</div>
						<div class="overlay" id="loading">
							<i class="fa fa-refresh fa-spin"></i>
						</div>
					</div>
                    
                    <form role="form" method="post" action="consulta_mercado_actions.php" enctype="multipart/form-data">
						<div class="box box-primary">
							<div class="box-body">
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-info_diario"><i class="fa fa-info-circle"></i></button>
								</div>
								<div class="form-group">
									<label>Actualización diaria datos mercado</label>
									<div class="box-footer">
										<button type="submit" name="action" value="actualizacion_diaria" class="btn btn-info"><i class="fa fa-refresh"></i></button>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
                
                
                <div class="col-md-2">
                    
                  <div class="box box-primary">
                      <div class="box-header with-border">
                            <h3 class="box-title">Tablas resumen</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-dwldfras"><i class="fa fa-info-circle"></i></button>
                              </div>
                        </div>
                    <div class="box-body">
                        <form role="form" method="post" action="consulta_mercado_actions.php" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Dato</label>

                                <select class="form-control select2" style="width: 100%;" data-placeholder="Dato" name="dato" id="dato">
                                    <?php
                                    $Lista = new Lista('tablas_resumen');
                                    $Lista->print_list();
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Producto</label>
                                <select class="form-control select2" style="width: 100%;" id="producto" name="producto">
                                    <option value="TODOS" selected="selected">TODOS</option>'
                                    <option value="M" >M</option>'
                                    <option value="Q" >Q</option>'
                                    <option value="Y" >Y</option>'
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Año desde</label>
                                <select class="form-control select2" style="width: 100%;" id="ano" name="ano">
                                    <?php
                                    $Lista->change_list('ano');
                                    $Lista->print_list(date('Y'));
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                              <div class="form-group">
                                <div class="box-footer">
                                    <button type="submit" name="action" value="download_tabla_resumen" class="btn btn-success"><i class="fa fa-download"></i></button>
                                </div>
                              </div>
                          </div>
                        </form>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="box-footer">
                                    <button name="action" value="actualizar_tabla_resumen" class="btn btn-info" onclick="update_tablas_res()"><i class="fa fa-refresh"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                      
                      <div class="overlay" id="loading">
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                  </div>
                    
                </div>
				
                <div class="col-md-2">
					<div class="box box-primary">
						<div class="box-body">
							<div class="box-header with-border">
								<h3 class="box-title">Mercado a futuros</h3>
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-componentesprecio"><i class="fa fa-info-circle"></i></button>
								</div>
							</div>
							
							<div class="col-md-12">
								<form role="form" method="post" action="consulta_mercado_actions.php" name="telemedida_form" enctype="multipart/form-data">
									<!-- Desde -->
									<div class="form-group">
										<label>Desde:</label>

										<div class="input-group date">
											<div class="input-group-addon">
												<i class="fa fa-calendar"></i>
											</div>
											<input type="text" class="form-control pull-right fecha" id="desde" name="desde" value="<?php 
                                                    $CalculosSimples = new CalculosSimples;
                                                    $dia_anterior = $CalculosSimples->lunesDiaAnterior();
                                                    unset($CalculosSimples);
                                                    $date = new DateClass;
                                                    echo $date->fromToFormat($dia_anterior);
                                                ?>">
										</div>
									</div>
                                    
                                    <!-- Hasta -->
									<div class="form-group">
										<label>Hasta: (no incluido)</label>

										<div class="input-group date">
											<div class="input-group-addon">
												<i class="fa fa-calendar"></i>
											</div>
											<input type="text" class="form-control pull-right fecha" id="hasta" name="hasta" value="<?php 
                                                    $date->resetDate();
                                                    echo $date->format('d/m/Y');
                                                ?>">
										</div>
									</div>
                                    
									<div class="box-footer pull-right">
										<button type="submit" name="action" value="download_futuros" class="btn btn-success"><i class="fa fa-download"></i></button>
									</div>
								</form>
							</div>
							
						</div>
						<div class="overlay" id="loading">
							<i class="fa fa-refresh fa-spin"></i>
						</div>
					</div>
                    
                    <div class="box box-primary">
						<div class="box-body">
							<div class="box-header with-border">
								<h3 class="box-title">OMIP/MIBGAS</h3>
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-componentesprecio"><i class="fa fa-info-circle"></i></button>
								</div>
							</div>
							
							<div class="col-md-12">
								<form role="form" method="post" action="consulta_mercado_actions.php" name="telemedida_form" enctype="multipart/form-data">
									<!-- Desde -->
									<div class="form-group">
										<label>Desde:</label>

										<div class="input-group date">
											<div class="input-group-addon">
												<i class="fa fa-calendar"></i>
											</div>
											<input type="text" class="form-control pull-right fecha" id="desde" name="desde" value="<?php 
                                                    echo $date->fromToFormat($dia_anterior);
                                                ?>">
										</div>
									</div>
                                    
                                    <!-- Hasta -->
									<div class="form-group">
										<label>Hasta: (no incluido)</label>

										<div class="input-group date">
											<div class="input-group-addon">
												<i class="fa fa-calendar"></i>
											</div>
											<input type="text" class="form-control pull-right fecha" id="hasta" name="hasta" value="<?php 
                                                    $date->resetDate();
                                                    echo $date->format('d/m/Y');
                                                ?>">
										</div>
									</div>
                                    
									<div class="box-footer pull-right">
										<button type="submit" name="action" value="download_omip_mibgas" class="btn btn-success"><i class="fa fa-download"></i></button>
									</div>
								</form>
							</div>
							
						</div>
						<div class="overlay" id="loading">
							<i class="fa fa-refresh fa-spin"></i>
						</div>
					</div>
                    
				</div>
				
                <div class="col-md-2">
                    
                    <div class="box box-primary">
						<div class="box-body">
							<div class="box-header with-border">
								<h3 class="box-title">Mercado spot</h3>
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-componentesprecio"><i class="fa fa-info-circle"></i></button>
								</div>
							</div>
							
							<div class="col-md-12">
								<form role="form" method="post" action="consulta_mercado_actions.php" name="telemedida_form" enctype="multipart/form-data">
									<!-- Desde -->
									<div class="form-group">
										<label>Desde:</label>

										<div class="input-group date">
											<div class="input-group-addon">
												<i class="fa fa-calendar"></i>
											</div>
											<input type="text" class="form-control pull-right fecha" id="desde" name="desde" value="<?php 
                                                    echo $date->format('d/m/Y');
                                                ?>">
										</div>
									</div>
                                    
                                    <!-- Hasta -->
									<div class="form-group">
										<label>Hasta: (no incluido)</label>

										<div class="input-group date">
											<div class="input-group-addon">
												<i class="fa fa-calendar"></i>
											</div>
											<input type="text" class="form-control pull-right fecha" id="hasta" name="hasta" value="<?php 
                                                    $date->add(0,0,1);
                                                    echo $date->format('d/m/Y');
                                                ?>">
										</div>
									</div>
                                    
									<div class="box-footer pull-right">
										<button type="submit" name="action" value="download_spot" class="btn btn-success"><i class="fa fa-download"></i></button>
									</div>
								</form>
							</div>
							
						</div>
						<div class="overlay" id="loading">
							<i class="fa fa-refresh fa-spin"></i>
						</div>
					</div>
                    
				</div>
                
				
				<!-- Componentes precio Modal -->
				<div class="modal fade" id="modal-precioobjetivo">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Componentes precio</h4>
					  </div>
					  <div class="modal-body">
						<p>
                          Hay 3 formas de realizar el precio objetivo:<br>
                          <ul>
                              <li>Normal (Normal)</li>
                              <li>Con curva de carga a través del excel (CdC)</li>
                              <li>Con curva de carga sqlite (Sqlite)</li>
                          </ul>
                          <br><br>
                          La plantilla para cadauno se puede descargar en los enlaces justo debajo del botón de selección del fichero.<br>
                          Para el Normal y el Sqlite la plantilla es el primer 'Este', para el CdC es el segundo 'este'.<br><br>
                          La primera pestaña es igual para todos.<br><br>
                          La segunda pestaña tiene algunas diferencias:<br><br>
                          <ul>
                              <li>Normal: se rellenan todos los datos</li>
                              <li>Sqlite: se rellenan todos los datos menos el consumo</li>
                              <li>CdC: se rellenan todos los datos indicados</li>
                          </ul>
                          <br><br>
                          Al seleccionar 'Nuevo calendario' el precio objetivo se calculará con el calendario nuevo
						</p>
					  </div>
					  <div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
					  </div>
					</div>
				  </div>
				</div>
				
			</div>
		</div>
	</div>
		
	</div>
	<!-- .content wrapper -->
		
    </section>

  
<?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php"); ?>

<script>
	
	function update_todo(){
		$.ajax({
			url:	'consulta_mercado_actions.php',
			method: 'POST',
			async:	true,
			data:	{
				action: 'update_todo',
				desde:	$('#desde').val(),
				hasta:	$('#hasta').val()
			},
			success:	function(data){
				data = data.split("|")
				$('#fecha_componentes').text(data[0])
				$('#fecha_kest').text(data[1])
				$('#fecha_perfiles').text(data[2])
				$('#fecha_pvpc').text(data[3])
			}
		})
	}
    
    function update_tablas_res(){
		$.ajax({
			url:	'consulta_mercado_actions.php',
			method: 'POST',
			async:	true,
			data:	{
				action: 'update_tablas_resumen',
				dato:	$('#dato').val(),
				ano:	$('#ano').val()
			}
		})
	}
	
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