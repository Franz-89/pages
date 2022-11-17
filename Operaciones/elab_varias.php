<?php
$fichero = str_replace(".php", "", basename(__FILE__));
$sql_table = $fichero;

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");
include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Varias
        <small>Elaboraciónes varias</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li>Operaciones</li>
        <li class="active">Varias</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
		   
	<div class="row">
		
		
		<div class="col-md-12">
			<div class="row">
				
				<!-- ARTURITO -->
				<div class="col-md-3">
                    
                    <form role="form" method="post" action="elab_varias_actions.php" enctype="multipart/form-data">
						<div class="box box-primary">
							<div class="box-body">
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-reparticion_nueva"><i class="fa fa-info-circle"></i></button>
								</div>
								<div class="form-group">
									<label>Nueva repartición de consumos</label>

									<select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cli">
										<?php
										$Lista = new Lista('clientes');
										$Lista->print_list();
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
                                        <input type="text" class="form-control pull-right fecha" id="desde" name="desde" value="">
                                    </div>
                                </div>

                                <!-- Hasta -->
                                <div class="form-group">
                                    <label>Hasta:</label>

                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control pull-right fecha" id="hasta" name="hasta" value="">
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button type="submit" name="action" value="consumos_anitugos_reorganizados" class="btn btn-success"><i class="fa fa-download"></i></button>
                                </div>
							</div>
						</div>
					</form>
                    
				</div>
				
				<!-- OPTIMIZACION -->
				<div class="col-md-3">
                    
                    <form role="form" method="post" action="elab_varias_actions.php" enctype="multipart/form-data">
						<div class="box box-primary">
							<div class="box-body">
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-optimizacion"><i class="fa fa-info-circle"></i></button>
								</div>
								<div class="form-group">
									<label>Optimizacion</label>

									<select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cli">
										<?php
										$Lista->print_list();
										?>
									</select>
                                    <label>Año precios excesos</label>
                                    <select class="form-control select2" style="width: 100%;" data-placeholder="Año" name="ano_precios_excesos">
										<?php
										$Lista->change_list('ano');
                                        $date = new DateClass;
										$Lista->print_list($date->format('Y'));
                                        unset($date);
										?>
									</select>
									<div class="box-footer">
										<button type="submit" name="action" value="optimizacion" class="btn btn-primary">Optimizar</button>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
				
				<!-- Optimización Modal -->
				<div class="modal fade" id="modal-optimizacion">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Optimización</h4>
					  </div>
					  <div class="modal-body">
						<p>
                            Optimiza solo los suministros con tarifa 3.0A/3.0TD con potencia menor o igual a 50kW.<br>
                            Convierte la potencia a 6 periodos según la nueva normativa:<br>
                            <ul style="list-style-type:circle;">
                                <li>P1 = P1</li>
                                <li>P2,P3,P4,P5 = P2</li>
                                <li>P6 = P3</li>
                            </ul>
							
                          También convierte la máxima a 6 periodos según el calendario nuevo de PENINSULA.<br>
                          Los periodos utilizados para cada factura dependen de cuantos días tenga en el mes. Si una fra tiene más de 15 días en el primer mes que incluye, se le asigna ese mes.<br><br>
                          Ejemplo:<br>
                          Si la factura va del 13/01/2021 al 22/02/2021 auqnue tenga más días en febrero, como tiene más de 15 días en enero se asignará a enero.<br><br>
                          
							<ul style="list-style-type:disc;">
								<li>CUPS: todos los CUPS en vigor de las tarifas 3.0A/3.0TD con potencia menor a 50kW</li>
								<li>FACTURAS: se seleccionan entre las facturas del ultimo año y medio hasta llegar a 365 días acumulados con</li>
								<ul style="list-style-type:circle;">
									<li>Consumo > 0</li>
									<li>Fecha desde <> Fecha hasta</li>
									<li>TP <> 0</li>
								</ul>
							</ul>
							
						  	La elaboración saca los siguientes resultados:<br><br>
						  
						  	<ul style="list-style-type:disc;">
								<li>Optimización CON y SIN derechos</li>
								<li>Ahorros con IE</li>
								<li>Numero de fras utlizadas</li>
								<li>Numero de fras con 0 en los 3 periodos</li>
								<li>Numero de fras con max = contratada</li>
								<li>Dias acumulados</li>
								<li>Dias de la resta entre fecha minima desde y fecha máxima hasta de la muestra de facturas</li>
								<li>Potencia máxima registrada en la muestra por cada periodo con relativa fecha hasta</li>
								<li>Fecha del ultimo cambio di potencia si ha habido alguno</li>
								<li>Listado de las fras utilizadas</li>
							</ul>
						</p>
					  </div>
					  <div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
					  </div>
					</div>
				  </div>
				</div>
        
                <!-- Optimización Nueva Modal -->
				<div class="modal fade" id="modal-reparticion_nueva">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Repartición</h4>
					  </div>
					  <div class="modal-body">
						<p>
							Los datos del suministros así como las potencias se refieren a la linea del PS con fecha inicio 01/06/2021
						</p>
					  </div>
					  <div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
					  </div>
					</div>
				  </div>
				</div>
				
				<!-- INFORME DIARIO -->
				<div class="col-md-3">
                    
                    <form role="form" method="post" action="elab_varias_actions.php" enctype="multipart/form-data">
						<div class="box box-primary">
							<div class="box-body">
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-reparticion_nueva"><i class="fa fa-info-circle"></i></button>
								</div>
								<div class="form-group">
									<label>Validación IEE nuevo, IVA</label>

									<select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cli">
										<?php
										$Lista->change_list('clientes');
										$Lista->print_list();
                                        unset($Lista);
										?>
									</select>
								</div>
                                
                                <!-- Desde -->
                                <div class="form-group">
                                    <label>Fecha emisión</label>
                                    <label>Desde:</label>
                                    
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control pull-right fecha" id="desde" name="desde" value="">
                                    </div>
                                </div>

                                <!-- Hasta -->
                                <div class="form-group">
                                    <label>Hasta:</label>

                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control pull-right fecha" id="hasta" name="hasta" value="">
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button type="submit" name="action" value="validacion_ieenuevo_iva" class="btn btn-success"><i class="fa fa-download"></i></button>
                                </div>
							</div>
						</div>
					</form>
				</div>
				
				<!-- Informe Diario Modal -->
				<div class="modal fade" id="modal-info_diario">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Informe Diario</h4>
					  </div>
					  <div class="modal-body">
						<p>
							Con un botón se actualizan en esta intranet todos los datos de mercado necesarios a realizar los informes.<br>
							A utilizar solo en el caso de que no se hayan actualizado en automatico a las 8:00.<br><br>
							Con el otro se descarga un fichero que servirá a redactar los informes con la aplicación excel.
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

</body>
</html>