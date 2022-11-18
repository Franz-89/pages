<?php
$fichero = str_replace(".php", "", basename(__FILE__));
$sql_table = $fichero;

if (isset($_GET['cli']))		{$cli 	= $_GET['cli'];} 			else {$cli 		= "";}
if (isset($_GET['desde']))		{$desde = $_GET['desde'];} 			else {$desde 	= date_format(new DateTime(), '01/01/Y');}
if (isset($_GET['hasta']))		{$hasta = $_GET['hasta'];} 			else {$hasta 	= date('d/m/Y');}
if (isset($_GET['num_fras']))	{$num_fras = $_GET['num_fras'];
								 $msg = "Se han seleccionado $num_fras facturas. Seleccionar un intervalo de fechas más pequeño!";}
																	else {$num_fras = '';}
if (!isset($msg)){$msg = '';}

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Revisioness&nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.8kq7d94rqg5v' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li>Ayuda</li>
        <li class="active">Revisiones</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
		   
	<div class="row">
		
		
		<div class="col-md-9">
			
			<!-- PERIODO Y COMERCIALIZADORA -->
		<form role="form" method="POST" action="revisiones_actions.php">
		  <div class="box box-primary">
            <div class="box-body">
              <div class="form-group">
					
				  	<div class="col-md-4">
						<div class="box-tools pull-right">
							<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-revps"><i class="fa fa-info-circle"></i></button>
						  </div>
						<!-- CLIENTE -->
						<div class="form-group">
							<label>Cliente</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cli">
								<?php
								$Lista = new Lista('clientes');
								$Lista->print_list($cli);
								?>
							</select>
						</div>
						
						
						<div class="box-footer">
							<button class="btn btn-primary" name="action" value="PS">Revisar PS</button>
						</div>
					</div>
				  	
				  <!-- INFO -->
				  <div class="modal fade" id="modal-revps">
					  <div class="modal-dialog">
						<div class="modal-content">
						  <div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title">REVISIÓN PS</h4>
						  </div>
						  <div class="modal-body">
							<p>
								Ejecuta las siguientes comprobaciones en el PS:<br>
								<ul style="list-style-type:disc;">
									<li>Fecha de alta del registro duplicada</li>
									<li>Solapes de fechas</li>
									<li>Estados distintos en las lineas del mismo CUPS</li>
									<li>En vigor con fecha fin de contrato menor que hoy</li>
							  	</ul>
							</p>
						  </div>
						  <div class="modal-footer">
							<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
						  </div>
						</div>
					  </div>
					</div>
				  
				  
				  	<div class="col-md-4">
						<div class="box-tools pull-right">
							<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-revbbdd"><i class="fa fa-info-circle"></i></button>
						  </div>
						<!-- Desde -->
						  <div class="form-group">
							<label>Hasta (>=):</label>

							<div class="input-group date">
							  <div class="input-group-addon">
								<i class="fa fa-calendar"></i>
							  </div>
							  <input type="text" class="form-control pull-right fecha" id="desde" name="desde" value="<?php echo $desde; ?>">
							</div>
						  </div>

						<!-- Hasta -->
						  <div class="form-group">
							<label>Desde (<=):</label>

							<div class="input-group date">
							  <div class="input-group-addon">
								<i class="fa fa-calendar"></i>
							  </div>
							  <input type="text" class="form-control pull-right fecha" id="hasta" name="hasta" value="<?php echo $hasta; ?>">
							</div>
						  </div>
							
							<div class="box-footer">
								<button class="btn btn-danger" name="action" value="BBDD">Revisar BBDD<br>(max 50.000 fras)</button>
								<!-- <button class="btn btn-info" name="action" value="">Revisar TP</button> -->
							</div>
					</div>
                        
                    <div class="col-md-4">
						<div class="box-tools pull-right">
							<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-desvio_consumo"><i class="fa fa-info-circle"></i></button>
						  </div>

						<!-- Hasta -->
						  <div class="form-group">
							<label>Desde para atrás:</label>
                            
							<div class="input-group date">
							  <div class="input-group-addon">
								<i class="fa fa-calendar"></i>
							  </div>
							  <input type="text" class="form-control pull-right fecha" id="desde_consumo" name="desde_consumo" value="<?php echo date('d/m/Y'); ?>">
							</div>
						  </div>
                        
                        <div class="form-group">
                            <div class="input-group">
							<label>Si el consumo es > de:</label>
							  <input type="number" class="form-control pull-right fecha" id="consumo" name="consumo" value=5000>
						  </div>
				        </div>
							
							<div class="box-footer">
								<button class="btn btn-success" name="action" value="desvio_consumo">Desvio consumo<br></button>
							</div>
					</div>
					
					<!-- INFO -->
					<div class="modal fade" id="modal-revbbdd">
					  <div class="modal-dialog">
						<div class="modal-content">
						  <div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title">REVISIÓN BBDD</h4>
						  </div>
						  <div class="modal-body">
							<p>
								Ejecuta las siguientes comprobaciones en la BBDD:<br>
								<ul style="list-style-type:disc;">
									<li>Fechas:</li>
									<ul style="list-style-type:circle;">
										<li>Fecha desde mayor que fecha hasta</li>
										<li>Fecha emisión menor que fecha hasta</li>
										<li>Ultima fecha hasta de hace 3 meses</li>
										<li>No emite desde hace 3 meses</li>
										<li>No tiene facturas</li>
									</ul>
									
									<li>Consumos:</li>
									<ul style="list-style-type:circle;">
										<li>Suma activa por periodo no coincide con el total</li>
										<li>Suma reactiva por periodo no coincide con el total</li>
										<li>Consumo de reactiva sin activa</li>
										<li>Total reactiva mayor que el total activa</li>
										<li>Consumo activa sin máximetro</li>
										<li>Consumo activa en los periodos no coincide con la tarifa</li>
									</ul>
									
									<li>Importes:</li>
									<ul style="list-style-type:circle;">
										<li>Consumo/Importe rectificada <> rectificadora</li>
										<li>IE sin consumo</li>
										<li>Suma de los conceptos no coincide con BI</li>
										<li>BI+IVA no coincide con TOTAL</li>
										<li>TE sin consumo</li>
										<li>Excesos de reactiva sin reactiva</li>
										<li>TE negativo con consumo positivo</li>
										<li>Factura sin numero de contrato</li>
										<li>Tarifa de baja tensión con excesos de potencia</li>
									</ul>
									
									<li>Duplicadas:</li>
									<ul style="list-style-type:circle;">
										<li>Número de factura dulicado</li>
										<li>Facturas con la misma fecha y el mismo importe que sean más o menos de 3 (RF, FI, RA)</li>
									</ul>
									
									<li>Huecos</li>
							  	</ul>
							  	A parte se puede realizar un cruce para comprobar si la potencia máxima se corresponde a la contratada
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
		</form>
	</div>
		
		<div class="col-md-2">
			<div class="box box-primary">
				<div class="box-body">

					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-slavestatus"><i class="fa fa-info-circle"></i></button>
					</div>
					<label>Estado del servidor</label>
					<div class="box-footer">
						<button class="btn btn-primary pull-right" name="action" value="slave_status" onClick="showSlaveStatus()">Comprobar</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- INFO -->
		  <div class="modal fade" id="modal-slavestatus">
			  <div class="modal-dialog">
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					  <span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">ESTADO DE LA BBDD ESPEJO</h4>
				  </div>
				  <div class="modal-body">
					<p>
						Comprueba si hay algún retraso en la actualización de la BBDD o si ha ocurrido algún error y hay que informar a Palomares.
					</p>
				  </div>
				  <div class="modal-footer">
					<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
				  </div>
				</div>
			  </div>
			</div>
		
	</div>
      
      
    <div class="row">
        <div class="col-md-2">
            <div class="box box-primary">
				<div class="box-body">
                    
                    <div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-descargaps"><i class="fa fa-info-circle"></i></button>
					</div>
                    
                    <h4>Descarga PS</h4>
                    <!-- CLIENTE -->
                    <form role="form" method="POST" action="revisiones_actions.php">
                        <div class="form-group">
                            <label>Cliente</label>
                            <select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cli">
                                <?php
                                $Lista->print_list();
                                unset($Lista);
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Fecha inicio >=:</label>

                            <div class="input-group date">
                              <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                              </div>
                              <input type="text" class="form-control pull-right fecha" id="desde_ps" name="desde_ps" value="<?php echo date('d/m/Y'); ?>">
                            </div>
                          </div>

                        <div class="box-footer">
                            <button class="btn btn-success" name="action" value="descarga_ps"><i class="fa fa-download"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
      
      
      <!-- INFO -->
      <div class="modal fade" id="modal-descargaps">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">DESCARGA PS</h4>
              </div>
              <div class="modal-body">
                <p>
                    Descarga el PS con el formato de subida a la intranet de Palomares a partir de una fecha.
                    El sistema no va a sacar todas esas lineas que tengan fecha inicio > hoy en el ps.<br>
                    Si son muchas lineas lo va a dividir en varios ficheros que se podrán juntar sucesivamente con el complemento de Excel RDBMerge.
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

  
<?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php"); ?>

<script>

	function showSlaveStatus(){
		$.ajax({
			url: "revisiones_actions.php?action=showSlaveStatus",
			method: "POST",
			data: {action: 'showSlaveStatus'},
			async: true,
			success: function (data) {
				alert(data);
			}
		})
	}

</script>

</body>
</html>