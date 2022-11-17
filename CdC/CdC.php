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
        Optimizaciones
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li>Operaciones</li>
        <li class="active">Elaboraciones clientes</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
		   
	<div class="row">
		
		
		<div class="col-md-12">
			<form role="form" method="post" action="actions.php" enctype="multipart/form-data">
				<div class="col-md-3">
					<div class="box box-primary">
						<div class="box-body">
							<div class="row">
								<div class="box-header with-border">
									<h3 class="box-title">Optimización</h3>

									<div class="box-tools pull-right">
										<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-opt"><i class="fa fa-info-circle"></i></button>
									  </div>
								</div>
								<!-- CSV BT GRUPO DIA (.xlsx) -->
								<div class="col-md-12">
                                    
                                <div class="form-group">
									<label>Cliente</label>

									<select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cli">
										<?php
										$Lista = new Lista('clientes');
										$Lista->print_list();
										?>
									</select>
								</div>
                                    
                                <div class="form-group">
                                    <label>Desde:</label>

                                    <div class="input-group date">
                                      <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                      </div>
                                      <input type="text" class="form-control pull-right fecha" id="desde" name="desde">
                                    </div>
                                  </div>

                                <!-- Hasta -->
                                  <div class="form-group">
                                    <label>Hasta (no incluido):</label>

                                    <div class="input-group date">
                                      <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                      </div>
                                      <input type="text" class="form-control pull-right fecha" id="hasta" name="hasta">
                                    </div>
                                  </div>
                                  <div><input type="checkbox" class="flat-red" name="detalle" value="detalle"> <label> Detalle</label></div>
                                  <div><input type="checkbox" class="flat-red" name="tresunoaseisdos" value="tresunoaseisdos"> <label> Forzar 3.1A a 62TD</label></div>
                                    <div class="form-group">
                                        <label>Año precio excesos</label>

                                        <select class="form-control select2" style="width: 100%;" data-placeholder="Año" name="ano_excesos">
                                            <?php
                                            $date = new DateClass;
                                            $Lista->change_list('ano');
                                            $Lista->print_list($date->format('Y'));
                                            unset($Lista, $date);
                                            ?>
                                        </select>
                                    </div>
                                    
								  <div class="form-group">
									  <div class="form-group">
										<label>Listado CUPS (.xlsx)</label>
										<input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]">
                                        <input type="hidden" name="MAX_FILE_SIZE" value = "10000000000000" />

                                        <p class="help-block">Listado de CUPS con <a href="plantillas/Listado.xlsx">este</a> formato (.xlsx)</p>
									  </div>

								  </div>
								</div>
							</div>
                            
							<div class="row">
								<div class="col-md-12">
                                    <div class="box-footer">
                                        <button type="submit" name="action" value="optimizar" class="btn btn-success">Optimizar</button>
                                        <button type="submit" name="action" value="maxima_todos" class="btn btn-warning">Informe prefactura</button>
                                        <button type="submit" name="action" value="huecos_detallados" class="btn btn-danger">Huecos detallados</button>
                                        <button type="submit" name="action" value="download" class="btn btn-success">Descarga</button>
                                    </div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
            
            <!-- Conversion -->
            <div class="col-md-3">
                <form role="form" method="post" action="actions.php" enctype="multipart/form-data">
                  <div class="box box-primary">
                      <div class="box-header with-border">
                            <h3 class="box-title">Conversión</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-conversion"><i class="fa fa-info-circle"></i></button>
                              </div>
                        </div>
                    <div class="box-body">
                      <div class="form-group">
                          <div class="form-group">
                            <label>Curvas (.xlsx,, .xls, .html, .csv)</label>
                            <input type="file" name ="fichero[]" multiple="multiple">
                            <input type="hidden" name="MAX_FILE_SIZE" value = "100000000000000" />

                            <p class="help-block">CdC de las comercializadoras</p>
                          </div>

                        <div class="box-footer">
                            <button type="submit" name="action" value="conversion" class="btn btn-primary">Elaborar</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
            </div>
            
			<!-- .row -->
		</div>
		<!-- .col -->
	</div>
	<!-- .row -->
		
        
        <!-- Conversion -->
        <div class="modal fade" id="modal-conversion">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Conversión</h4>
              </div>
              <div class="modal-body">
                <p>
                    Este apartado convierte las curvas de carga descargadas de las diferentes comercializadoras en un CSV para la subida a la intranet.<br><br>
                    Las curvas aceptadas son las siguientes:
                    <ul style="list-style-type:disc;">
                        <li>ENDESA (.html)</li>
                        <li>EDP (.csv)</li>
                        <li>ENGIE (.csv)</li>
                        <li>NATURGY (.xlsx)</li>
                        <li>TOTAL (.xlsx)</li>
                        <li>IBERDROLA cuartohoraria potencia (.xlsx)</li>
                        <li>IBERDROLA horaria activa (.xlsx)</li>
                        <li>GESTINEL (.xlsx)</li>
                    </ul>
                    Las curvas han de subirse sin modificarse a excepción de:
                    <ul style="list-style-type:disc;">
                        <li>NATURGY: como se descarga mes a mes, es necesario juntarlas en un unico fichero .xlsx, manteniendo el encabezado.</li>
                        <li>ENDESA: solo hay que renombrar el fichero con el CUPS y cambiar la extensión de .xls a .html</li>
                    </ul>
                </p>
              </div>
              <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
        <!-- /.modal -->
        
        
        
		<!-- INFO -->
        <div class="modal fade" id="modal-opt">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">OPTIMIZACIÓN</h4>
              </div>
              <div class="modal-body">
                <p>
                    En esta sección hay 3 formas de seleccionar los CUPS a optimizar:
                    <ul style="list-style-type:decimal;">
                        <li>Insertando un código CUPS individual</li>
                        <li>Subiendo un fichero con un listado de CUPS</li>
                        <li>Seleccionando el cliente y/o la tarifa. En el caso de que solo se seleccione el cliente se optimizarán todos los CUPS</li>
                    </ul>
                    Estos tres metodos se pueden combinar.<br>
                    Se realizará la optimización de todos los suministros en vigor que tengan curva de carga.<br>
                    Se aconseja, antes de optimizar, de realizar una revisión de los huecos para obtener una optimización válida.
                    <br><br>
                    Se pueden comprobar los huecos de dos maneras (en ambos casos la comprobación de los huecos tendrá como 'fecha hasta' la fecha máxima, el dato más reciente de la curva, y como 'fecha desde' la misma fecha menos un año):
                    <ul style="list-style-type:decimal;">
                        <li>Sencilla: saca CUPS, Numero de huecos, Numero de ceros, Fecha minima (dato más antiguo de la curva), Fehca máxima (dato más reciente de la curva).<br>
                        Los huecos se obtienen calculando el numero de cuartos de hora entre la Fecha desde y la Fecha hasta y comparandolos con el numero de cuartos en la curva de carga.</li>
                        <li>Detallada: saca un listado de los huecos con los datos CUPS, Fecha inicio del hueco, Fecha fin del hueco, Hueco expresado en minutos.</li>
                    </ul>
                </p>
              </div>
              <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
        <!-- /.modal -->
		
		
	</div>
	<!-- .content wrapper -->
		
    </section>

  
<?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php"); ?>

</body>
</html>