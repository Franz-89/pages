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
        Informes
        <small>Elaboraciónes de informes de mercado</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li>Mercado</li>
        <li class="active">Informes</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
		   
	<div class="row">
		
		
		<div class="col-md-12">
			<div class="row">
    
				<!-- Informe diario -->
				<div class="col-md-2">
                    
                    <form role="form" method="post" action="elab_mercado_actions.php" enctype="multipart/form-data">
						<div class="box box-primary">
							<div class="box-body">
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-info_diario"><i class="fa fa-info-circle"></i></button>
								</div>
								<div class="form-group">
									<label>Informe diario</label>
									<div class="box-footer">
										<button type="submit" name="action" value="informe_diario" class="btn btn-success"><i class="fa fa-download"></i></button>
									</div>
								</div>
							</div>
						</div>
					</form>
                    
                    <form role="form" method="post" action="elab_mercado_actions.php" enctype="multipart/form-data">
						<div class="box box-primary">
							<div class="box-body">
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-info_diario"><i class="fa fa-info-circle"></i></button>
								</div>
								<div class="form-group">
									<label>Froneri Elec-Gas</label>
									<div class="box-footer">
										<button type="submit" name="action" value="froneri_elec_gas" class="btn btn-success"><i class="fa fa-download"></i></button>
									</div>
								</div>
							</div>
						</div>
					</form>
                    
				</div>
				
                <div class="col-md-3">
                    <form role="form" method="post" action="elab_mercado_actions.php">
                    <div class="box box-primary">
                        <div class="box-body">
                            <div class="box-header with-border">
                                <h3 class="box-title">SIDENOR ELEC-GAS</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-singularesholdings"><i class="fa fa-info-circle"></i></button>
                                  </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Enviar a:</label>

                                    <input type="text" class="form-control" id="destinatario_sidenor" name="destinatario" value="maite.echarri@sidenor.com; emilio.hidalgo@sidenor.com; slizarralde@enertrade.es">
                                </div>
                                <div class="form-group">
                                    <label>Copia:</label>

                                    <input type="text" class="form-control" id="copia_sidenor" name="copia" value="german.quijano@sidenor.com; jorge.bas@sidenor.com; goyo.iparraguirre@sidenor.com; mikel.elizalde@sidenor.com; iciar.aguirre@sidenor.com; esteban.echaniz@sidenor.com; sergio.tudanca@sidenor.com; victor.morales@sidenor.com; felix.bayon@sidenor.com; jon.hidalgo@sidenor.com; mcarmen.zapatero@sidenor.com; leire.velasco@sidenor.com; ainara.garciaescudero@sidenor.com">
                                </div>
                                <div class="box-footer">
                                    <button type="submit" name="action" value="sidenor_elec_gas" class="btn btn-primary" onclick="confirmacion()"><i class="fa fa-envelope"></i> Enviar</button>
                                </div>
                            </div>
                        </div>
                        <div class="overlay" id="loading">
							<i class="fa fa-refresh fa-spin"></i>
						</div>
                    </div>
                    </form>
                    
                    <form role="form" method="post" action="elab_mercado_actions.php">
                    <div class="box box-primary">
                        <div class="box-body">
                            <div class="box-header with-border">
                                <h3 class="box-title">REINOSA</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-singularesholdings"><i class="fa fa-info-circle"></i></button>
                                  </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Enviar a:</label>

                                    <input type="text" class="form-control" id="destinatario_reinosa" name="destinatario" value="felipe.calleja@reinosafc.com; mangel.diez@reinosafc.com">
                                </div>
                                <div class="form-group">
                                    <label>Copia:</label>

                                    <input type="text" class="form-control" id="copia_reinosa" name="copia" value="slizarralde@enertrade.es">
                                </div>
                                <div class="form-group">
                                    <label>Ajuste estimado:</label>

                                    <input type="number" class="form-control" id="ajuste_estimado" name="ajuste_estimado" value=100>
                                </div>
                                <div class="box-footer">
                                    <button type="submit" name="action" value="reinosa_elec_gas" class="btn btn-primary" onclick="confirmacion()"><i class="fa fa-envelope"></i>Enviar</button>
                                </div>
                            </div>
                        </div>
                        <div class="overlay" id="loading">
							<i class="fa fa-refresh fa-spin"></i>
						</div>
                    </div>
                    </form>
                </div>
                
                <div class="col-md-3">
					<div class="box box-primary">
						<div class="box-body">
							<div class="box-header with-border">
								<h3 class="box-title">Precio objetivo</h3>
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-precioobjetivo"><i class="fa fa-info-circle"></i></button>
								</div>
							</div>
							
							<div class="col-md-12">
								<form role="form" method="post" action="elab_mercado_actions.php" enctype="multipart/form-data">
									<div class="form-group">
										<div class="form-group">
											<label>Datos (.xlsx)</label>
											<input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name="fichero[]">
											<input type="hidden" name="MAX_FILE_SIZE" value = "10485760" />

											<p class="help-block"><a href="plantillas/Subida datos precio objetivo.xlsx" download><u>Este</u></a> fichero de datos o <a href="plantillas/Subida datos precio objetivo CdC.xlsx" download><u>este</u></a> para la CdC</p>
										</div>
                                        <div class="form-group">
                                            <label>
                                              <div><input type="checkbox" checked class="flat-red" name="normal" value="normal"> <label> Normal</label></div>
                                            </label>
                                            <label>
                                              <div><input type="checkbox" class="flat-red" name="cdc" value="cdc"> <label> CdC</label></div>
                                            </label>
                                            <label>
                                              <div><input type="checkbox" class="flat-red" name="sqlite" value="sqlite"> <label> Sqlite</label></div>
                                            </label>
                                        </div>
										<div class="box-footer pull-right">
											<button class="btn btn-success" name="action" value="precio_objetivo"><i class="fa fa-upload"></i></button>
										</div>
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
	
    function confirmation() {
        if(confirm("Ejecutar esta acción?"))
        {
            return true;
        }
        return false;
    }
    
    function enviarSidenor(){
		  if (confirmation()){
			  $.ajax({
					url: "elab_mercado_actions.php",
					method: "POST",
					data: {
                        action: 'sidenor_elec_gas',
                        destinatario: $('#destinatario_sidenor').val(),
                        copia: $('#copia_sidenor').val()
                    },
					async: true
				})
		  }
	  }
    
    function enviarReinosa(){
		  if (confirmation()){
			  $.ajax({
					url: "elab_mercado_actions.php",
					method: "POST",
					data: {
                        action: 'reinosa_elec_gas',
                        destinatario: $('#destinatario_reinosa').val(),
                        copia: $('#copia_reinosa').val()
                    },
					async: true
				})
		  }
	  }
    
	function update_todo(){
		$.ajax({
			url:	'consulta_actions.php',
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