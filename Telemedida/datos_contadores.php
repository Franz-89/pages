<?php
$fichero = str_replace(".php", "", basename(__FILE__));
$sql_table = $fichero;

$horaria 	= (isset($_GET['horaria']) 		&& !empty($_GET['horaria'])) 	? 'checked' 			: '';
$prioridad 	= (isset($_GET['prioridad']) 	&& !empty($_GET['prioridad'])) 	? $_GET['prioridad'] 	: '';

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");

$desde 		= (isset($_SESSION['desde'])) 		? $_SESSION['desde'] 		: '';
$hasta 		= (isset($_SESSION['hasta'])) 		? $_SESSION['hasta'] 		: '';
$contadores = (isset($_SESSION['contadores'])) 	? $_SESSION['contadores'] 	: '';
?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Telemedida &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.srp2a21oggad' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li class="active">Telemedida</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
	
	<!-- DESCARGA CdC -->
	<div class="row">
		<div class="col-md-7">
		
		  <div class="box box-primary">
            <div class="box-body">
              <div class="form-group">
					
				  
				  <form role="form" method="post" action="gestinel_actions.php" name="telemedida_form" enctype="multipart/form-data">
				  	<div class="row">
					  
						<div class="col-md-6">
							
							<!-- Contadores -->
							<div class="form-group">
							  <label>Contadores:</label>
							  <select multiple class="form-control" size=10 name="contador[]" onclick="saveContadores($(this).val())" id="listaContadores">
								  <?php
								  $Lista = new Lista('contadores');
								  $Lista->print_list($contadores);
								  ?>
							  </select>
							</div>
						</div>
					  
					  	<div class="col-md-6">
							<!-- Desde -->
							  <div class="form-group">
								<label>Desde:</label>

								<div class="input-group date">
								  <div class="input-group-addon">
									<i class="fa fa-calendar"></i>
								  </div>
								  <input type="text" class="form-control pull-right fecha" id="desde" name="desde" value="<?php echo $desde; ?>" onchange="saveDesde($(this).val())">
								</div>
							  </div>
							
							<!-- Hasta -->
							  <div class="form-group">
								<label>Hasta (no incluido):</label>

								<div class="input-group date">
								  <div class="input-group-addon">
									<i class="fa fa-calendar"></i>
								  </div>
								  <input type="text" class="form-control pull-right fecha" id="hasta" name="hasta" value="<?php echo $hasta; ?>" onchange="saveHasta($(this).val())">
								</div>
							  </div>
							
							<div class="form-group">
								<label>Prioridad</label>
								<select class="form-control select2" style="width: 100%;" data-placeholder="Prioridad" name="prioridad" id="prioridad" onchange="saveContadores('AGU2')">
									<option selected="selected">Ninguna</option>
									<option value="Diario">Diario</option>
									<option value="Diario">Semanal</option>
									<?php
									$Lista->change_list('prioridad');
									$Lista->print_list($prioridad);
									?>
									
								</select>
							 </div>
							
							<div class="form-group">
								<div class="checkbox">
									<label>
										<input type="checkbox" name="horaria" <?php echo $horaria ?>> Curva horaria
									</label>
								</div>
							</div>
						</div>
					</div>
					  	
					  	<!-- SUBMIT -->
						<div class="col-md-10">
							<div class="box-footer pull-right">
								<button class="btn btn-success" name="action" value="download"><i class="fa fa-download"></i></button>
								<button class="btn btn-success" name="action" value="download_csv"><i class="fa fa-download"> .csv</i></button>
							</div>

							<div class="box-footer pull-right">
                                <button class="btn btn-warning" name="action" value="le2_curva"><i class="fa fa-envelope"></i> LE2</button>
                                <button class="btn btn-danger" name="action" value="seatFicticio"><i class="fa fa-refresh"></i> SEAT</button>
								<button class="btn btn-primary" name="action" value="update_precio"><i class="fa fa-refresh"></i> Precio</button>
								<button class="btn btn-warning" name="action" value="correos"><i class="fa fa-envelope"></i> Correos</button>
								<button class="btn btn-info" name="action" value="informes"><i class="fa fa-newspaper-o"></i> Informes</button>
								<button class="btn btn-primary" name="action" value="informes_diarios"><i class="fa fa-newspaper-o"></i> Diarios</button>
							</div>

							
					  	</div>
					  </form>
					  <div class="col-md-2">
						<div class="box-footer pull-right">
							<button class="btn btn-primary" id="update" onclick="updateContadores()"><i class="fa fa-refresh"></i></button>
							<button class="btn btn-primary" id="update_palomares" onclick="updateContadoresPalomares()"><i class="fa fa-refresh"></i> &nbsp;P</button>
                            
						</div>
					  </div>
				  	
				
				  
				<div class="row">
					
					<div class="col-md-6">
						<form role="form" method="post" action="gestinel_actions.php" name="telemedida_form" enctype="multipart/form-data">
							<div class="form-group">
								<label>Año:</label>
								<select class="form-control select2" style="width: 100%;" data-placeholder="Año" name="ano" id="ano">
									<?php
									$year = date('Y')+5;
									for ($x=1; $x<=10; $x++){
										if (($year-$x) == date('Y')){
											echo '<option selected="selected" value="'.($year-$x).'">'.($year-$x).'</option>';
										} else {
											echo '<option value="'.($year-$x).'">'.($year-$x).'</option>';
										}
									}
									?>
								</select>
								<p class="help-block"><a href="https://drive.google.com/uc?export=download&id=1BIyMws-Z95zj9yy_5NZkWpvf19OmF8qL" download><u>Calendarios</u></a> regulados</p>
								<div class="box-footer pull-right">
									<button class="btn btn-success" name="action" value="download_calendario"><i class="fa fa-download"></i></button>
								</div>
							</div>
						</form>
						<div class="box-footer pull-right">
							<button class="btn btn-primary" name="action" onclick="createCalendar($('#ano').val())"><i class="fa fa-refresh"></i></button>
						</div>
					</div>
				</div>
				  
				  
				  
              	</div>
			</div>
			  <div class="overlay" id="loadingContadores">
				  <i class="fa fa-refresh fa-spin"></i>
			  </div>
		  </div>
		
	  </div>
	
		
		<div class="col-md-5">
			<div class="box box-primary">
        		<div class="box-header with-border">
					<button class="btn btn-danger" name="action" value="check" onclick="reloadAlarmas()"><i class="fa fa-bar-chart"></i></button>
          			<h3 class="box-title">&nbsp;Alarmas</h3>
					
					  <div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
					  </div>
        		</div>
				<!-- /.box-header -->
				<div class="box-body">
					<table id="alarmas" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>Contador</th>
								<th>Huecos</th>
								<th>Ceros</th>
								<th>Actualizado hasta</th>
							</tr>
						</thead>
					</table>
					
          		</div>
			</div>
		</div>
		
	</div>
	
	
	<div class="row">
			<!-- BAR CHART -->
		<div class="col-md-12">
          <div class="box box-primary" id="CdC_chart">
            <div class="box-header with-border">
			  <button id="download" class="btn btn-success"><i class="fa fa-download"></i></button>
			  <a class="btn btn-primary" href="datos_contadores.php"><i class="fa fa-refresh"></i></a>
              <h3 class="box-title">&nbsp;Curva de Carga</h3>
				
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div id="chart" class="chart">
                <canvas id="barChart" style="height:400px">
					<?php
						if (isset($contadores) && !empty($desde) && !empty($hasta)){
						  $strSQL = "SELECT a.FECHA,
											b.kW_Compra
									FROM enertrade.nuevo_cal_periodos a

									LEFT JOIN 	(SELECT Fecha,
														kW_Compra
												FROM cdc.".$contadores[0]."
												WHERE Fecha>'".date_php_to_sql($desde)."'
												AND Fecha<='".date_php_to_sql($hasta)."'
									) b
									ON a.FECHA=b.Fecha

									WHERE a.FECHA>'".date_php_to_sql($desde)."'
									AND a.FECHA<='".date_php_to_sql($hasta)."'
									ORDER BY FECHA";

						  $Conn 	= new Conn('local', '');
						  $query 	= $Conn->Query($strSQL);

						  $dias = date_diff(date_create_from_format('d/m/Y', $desde), date_create_from_format('d/m/Y', $hasta))->format("%a");
						
						  while ($row = mysqli_fetch_assoc($query)){$datos_cruva[] = $row;}
						  mysqli_free_result($query);
						  unset($Conn);
						  
						  foreach ($datos_cruva as $num_row=>$row){
							  $fecha = date_create_from_format('Y-m-d H:i:s', $row['FECHA']);
							  if (date_format($fecha, 'Gi')==0){$etiquetas[] = date_format($fecha, 'd/m/Y');} else {$etiquetas[] = date_format($fecha, 'd/m/Y H:i');}
							  $valores[]	= $row['kW_Compra'];
						  }
						}
						if (empty($valores) || !isset($valores)){
							$cuenta 	= 0;
							$valores 	= 0;
							$etiquetas 	= 0;
						} else {$cuenta = count($valores);}
						?>
				</canvas>
              </div>
			  <input type="text" value="" class="slider form-control" data-slider-min="0" data-slider-max="<?php echo $cuenta; ?>" data-slider-step="1" data-slider-value="[0,<?php echo $cuenta; ?>]" data-slider-orientation="horizontal" data-slider-selection="before" data-slider-tooltip="show" data-slider-id="blue" onchange="return updateChart()" id="ElSlider"></input>
            </div>
          </div>
       </div>
		
	</div>
		
	<!-- DATOS CONTADORES -->
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary collapsed-box">
				<div class="box-header with-border">
				  <h3 class="box-title">Contadores</h3>

				  <div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
				  </div>
				</div>
            	<div class="box-body">
					
					<div class="col-md-12">
						<div class="col-md-3">
							<a class="btn btn-app" href="moddatos_contadores.php">
								<i class="fa fa-plus"></i> Nuevo
                            </a>
                            <a class="btn btn-app" href="js_actions.php?action=update_potencias">
								<i class="fa fa-refresh"></i> Potencias
							</a>
                            <a class="btn btn-app" href="acciones_get.php?action=datos_contadores">
								<i class="fa fa-file-excel-o"></i> Descargar
							</a>
						</div>
						
						<div class="col-md-3">
							<form role="form" method="post" action="gestinel_actions.php" enctype="multipart/form-data">
								<div class="form-group">
									<input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]">
									<input type="hidden" name="MAX_FILE_SIZE" value = "10485760" />

									<p class="help-block">Fichero de datos_contadores con <a href="plantillas/nuevos_contadores.xlsx" download><u>este formato</u></a></p>
									<button class="btn btn-success" name="action" value="nuevos_contadores"><i class="fa fa-upload"></i></button>
								</div>
							</div>
						</div>
					
					
					<table id="contadores" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>ID</th>
								<th>CONTADOR</th>
								<th>TARIFA</th>
								<th>CALENDARIO</th>
								<th>CUPS</th>
								<th>GRUPO</th>
								<th>CIF</th>
								<th>RAZON SOCIAL</th>
								<th>TIPO PRECIO</th>
								<th>COMERCIALIZADORA</th>
								<th>GRUPO ENVIO MENSUAL</th>
								<th>GRUPO ENVIO DIARIO</th>
								<th>ACCIONES</th>
							</tr>
						</thead>
					</table>
						
				</div>
			</div>
		</div>
	</div>
		
	<!-- GRUPOS DE ENVIO MENSUAL -->
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary collapsed-box">
				<div class="box-header with-border">
				  <h3 class="box-title">Grupos de envío mensual</h3>

				  <div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
				  </div>
				</div>
            	<div class="box-body">
					
					<div class="col-md-12">
						<div class="col-md-1">
							<a class="btn btn-app" href="modenvios_gestinel.php">
								<i class="fa fa-plus"></i> Nuevo
							</a>
						</div>
						<div class="col-md-1">
							<a class="btn btn-app" href="acciones_get.php?action=envios_gestinel">
								<i class="fa fa-file-excel-o"></i> Descargar
							</a>
						</div>
						
						<div class="col-md-2">
							<form role="form" method="post" action="gestinel_actions.php" enctype="multipart/form-data">
								<div class="form-group">
									<input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]">
									<input type="hidden" name="MAX_FILE_SIZE" value = "10485760" />

									<p class="help-block">Fichero de grupos_envio con <a href="plantillas/nuevos_grupos_envio.xlsx" download><u>este formato</u></a></p>
									<button class="btn btn-success" name="action" value="nuevos_grupos_envio"><i class="fa fa-upload"></i></button>
								</div>
							</form>
						</div>
						
					</div>
					
					<table id="grupos_envio" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>ID</th>
								<th>GRUPO ENVÍO</th>
								<th>PRIORIDAD</th>
								<th>A</th>
								<th>COPIA</th>
								<th>ENCARGADO</th>
								<th>OBSERVACIONES</th>
								<th>ACCIONES</th>
							</tr>
						</thead>
					</table>
					
				</div>
			</div>
		</div>
	</div>
	  
	  
	<!-- GRUPOS DE ENVIO DIARIO -->
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary collapsed-box">
				<div class="box-header with-border">
				  <h3 class="box-title">Grupos de envío diario</h3>

				  <div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
				  </div>
				</div>
            	<div class="box-body">
					
					<a class="btn btn-app" href="acciones_get.php?action=download_envios_diarios">
						<i class="fa fa-file-excel-o"></i> Descargar
					</a>
					
					<div class="col-md-12">
						<div class="col-md-2">
							<div class="form-group">
								<label>Grupo</label>
								<input type="text" class="form-control" placeholder="Grupo" id="grupo_diario">
							 </div>
						 </div>
						<div class="col-md-3">
							<div class="form-group">
								<label>A</label>
								<textarea class="textarea" placeholder="A..."
								style="width: 100%; height: 60px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" id="a_diario"></textarea>
							</div>
						 </div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Copia</label>
								<textarea class="textarea" placeholder="Copia..."
								style="width: 100%; height: 60px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" id="copia_diario"></textarea>
							</div>
						 </div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Observaciones</label>
								<textarea class="textarea" placeholder="Observaciones..."
								style="width: 100%; height: 60px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" id="observaciones_diario"></textarea>
							</div>
						 </div>

						<div class="col-md-1">
							<label>&nbsp;</label>
							<div class="form-group">
								<button type="submit" class="btn btn-success btn-flat" onclick="addEnvioDiario()"><i class="fa fa-plus"></i></button>
							</div>
						</div>
					</div>
					
					<table id="grupos_envio_diario" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>ID</th>
								<th>GRUPO ENVÍO</th>
								<th>A</th>
								<th>COPIA</th>
								<th>OBSERVACIONES</th>
								<th>ACCIONES</th>
							</tr>
						</thead>
					</table>
					
				</div>
			</div>
		</div>
	</div>
	  
</div>
		
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
	
	function saveDesde(valor){
		$.ajax({
			url	: "js_actions.php?action=saveDate",
			method	: 'POST',
			data	: {
				desde : valor
			}
		})
	}
	function saveHasta(valor){
		$.ajax({
			url	: "js_actions.php?action=saveDate",
			method	: 'POST',
			data	: {
				hasta : valor
			}
		})
	}
	function saveContadores(valor){
		$.ajax({
			url	: "js_actions.php?action=saveContadores",
			method	: 'POST',
			data	: {
				contadores 	: valor,
				prioridad	: function() {return $('#prioridad').val()}
			},
			success	: function(data){
				if ($('#prioridad').val()!='Ninguna'){
					var lista = JSON.parse(data)
					$('#listaContadores').val(lista).trigger('change')
				}
			}
		})
	}
	
	function delContador(valor){
		  if (confirmation()){
			  $.ajax({
					url: "js_actions.php?action=delContador",
					method: "POST",
					data: {id: valor},
					async: false,
					success: function () {setTimeout(reloadContadores(), 500)}
				})
		  }
	  }
	function delGrupoEnvio(valor){
		  if (confirmation()){
			  $.ajax({
					url: "js_actions.php?action=delGrupoEnvio",
					method: "POST",
					data: {id: valor},
					async: false,
					success: function () {setTimeout(reloadGruposEnvio(), 500)}
				})
		  }
	  }
	function delGrupoEnvioDiario(valor){
		  if (confirmation()){
			  $.ajax({
					url: "js_actions.php?action=delGrupoEnvioDiario",
					method: "POST",
					data: {id: valor},
					async: false,
					success: function () {setTimeout(reloadGruposEnvioDiario(), 500)}
				})
		  }
	  }
	
	function updateContadores(){
		$.ajax({
			url: "js_actions.php?action=updateContadores",
			async: true,
			success: function () {setTimeout(reloadAlarmas(), 500)}
		})
	}
    
    function updateContadoresPalomares(){
		$.ajax({
			url: "js_actions.php?action=updateContadoresPalomares",
			async: true,
			success: function () {setTimeout(reloadAlarmas(), 500)}
		})
	}
    
	function createCalendar(valor){
		$.ajax({
			url: "js_actions.php?action=createCalendar",
			method: 'POST',
			data: {ano: valor},
			async: true,
			success: function () {setTimeout(reloadContadores(), 500)}
		})
	}
	
	function addEnvioDiario(){
		$.ajax({
			url: "js_actions.php?action=addEnvioDiario",
			method: 'POST',
			async: true,
			data: {
				grupo: $('#grupo_diario').val(),
				a: $('#a_diario').val(),
				copia: $('#copia_diario').val(),
				observaciones: $('#observaciones_diario').val()
			},
			success: function (data) {
				if (data){
					alert(data);
				} else {
					$('#grupo_diario').val('');
					$('#a_diario').val('');
					$('#copia_diario').val('');
					$('#observaciones_diario').val('');
					
					setTimeout(reloadGruposEnvioDiario(), 500);
				}
				
			}
		})
	}
	
	function reloadContadores() 		{$('#contadores').DataTable().ajax.reload();}
	function reloadGruposEnvio() 		{$('#grupos_envio').DataTable().ajax.reload();}
	function reloadGruposEnvioDiario() 	{$('#grupos_envio_diario').DataTable().ajax.reload();}
	function reloadAlarmas() 			{$('#alarmas').DataTable().ajax.reload();}
	
	function updateChart() {
		
		var valor = document.getElementById('ElSlider').value.split(",")
		
		var etiquetas 	= <?php echo json_encode($etiquetas); ?>;
		var valores 	= <?php echo json_encode($valores); ?>;
		
		var dias = (valor[1]-valor[0])/96
		switch (true){
			  case (dias>150)				: var sstep = 96*30; 	break;
			  case (dias<=300 && dias>150)	: var sstep = 96*3; 	break;
			  case (dias<=150 && dias>75)	: var sstep = 96*2; 	break;
			  case (dias<=75 && dias>4)		: var sstep = 1*96; 	break;
			  case (dias<=4 && dias>1)		: var sstep = 1*4; 		break;
			  case (dias<=1)				: var sstep = 1; 		break;
		}
		
		
		var etiquetass 	= new Array()
		var valoress 	= new Array()
		var cnt 		= 0
		var i
		for (i=valor[0]; i<valor[1]-1; i++){
			if (((i+1)%sstep)==0){etiquetass[cnt] = etiquetas[i]} else {etiquetass[cnt] = ''}
			valoress[cnt] = valores[i]
			++cnt
		}
		
		var barChartData = {
		  labels  : etiquetass,
		  datasets: [
			{
			  label               : 'Digital Goods',
			  fillColor           : 'rgba(60,141,188,0.9)',
			  strokeColor         : 'rgba(60,141,188,0.8)',
			  pointColor          : '#3b8bba',
			  pointStrokeColor    : 'rgba(60,141,188,1)',
			  pointHighlightFill  : '#fff',
			  pointHighlightStroke: 'rgba(60,141,188,1)',
			  data                : valoress
			}
		  ]
		}
		var barChartOptions                  = {
		  animation				  : false,
		  scaleBeginAtZero        : true,
		  scaleShowGridLines      : false,
		  scaleGridLineColor      : 'rgba(0,0,0,.05)',
		  scaleGridLineWidth      : 0,
		  scaleShowHorizontalLines: true,
		  scaleShowVerticalLines  : true,
		  barShowStroke           : true,
		  barStrokeWidth          : 0,
		  barValueSpacing         : 0,
		  barDatasetSpacing       : 0,
		  legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
		  responsive              : true,
		  maintainAspectRatio     : true
		}
		
		$('#barChart').remove()
		$('#chart').append('<canvas id="barChart" style="height:500px"></canvas>');
		var barChartCanvas                   = $('#barChart').get(0).getContext('2d')
    	var barChart                         = new Chart(barChartCanvas)
		barChart.Bar(barChartData, barChartOptions)
 	}
	
      
  $('#loadingContadores').toggle()

  $('.slider').slider()

  $('#contadores').DataTable({
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
		  url 		: "js_actions.php?action=getContadores",
		  dataSrc	: ''
	  },
	  columnDefs	: [{
			targets		: [ 0 ],
			visible		: false,
			searchable	: false
		}]
	})

  $('#grupos_envio').DataTable({
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
		  url 		: "js_actions.php?action=getGruposEnvio",
		  dataSrc	: ''
	  },
	  columnDefs	: [{
			targets		: [ 0 ],
			visible		: false,
			searchable	: false
		}]
	})
	
	$('#grupos_envio_diario').DataTable({
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
		  url 		: "js_actions.php?action=getGruposEnvioDiario",
		  dataSrc	: ''
	  },
	  columnDefs	: [{
			targets		: [ 0 ],
			visible		: false,
			searchable	: false
		}]
	})


  $('#alarmas').DataTable({
	  paging		: false,
	  searching		: false,
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
	  order			: [[ 0, "asc" ]],
	  ajax			: {
		  url 		: "js_actions.php?action=getAlarmas",
		  dataSrc	: ''
	  }
	})

  $(document).ajaxStart(function(){
	  $("#loadingContadores").show();
  })
  $(document).ajaxComplete(function(){
	  $("#loadingContadores").hide();
  })

updateChart()

 $("#download").click(function(){
	var dataURL = $('#barChart').get(0).toDataURL("image/png");
	var link = document.createElement('a');
	link.href = dataURL;
	link.download = 'Grafica CdC.png';
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
});
	
	$('#CdC_chart').attr('class', 'box box-primary collapsed-box')
	
</script>

</body>
</html>