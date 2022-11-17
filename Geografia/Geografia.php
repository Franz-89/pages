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
        Geografía &nbsp;<a href='https://sites.google.com/view/wikienertrade/formaci%C3%B3n/franet#h.7sug7l9yzyfr' target="_blank"><i class="fa fa-info"></i></a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
		<li class="active">Geografia</li>
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
                            <div class="box-header with-border">
                                <h3 class="box-title">Obtener coordenadas</h3>
                            </div>

                              <div class="form-group">
                                  <div class="form-group">
                                    <label>Direcciones (.xlsx)</label>
                                    <input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]">
                                    <input type="hidden" name="MAX_FILE_SIZE" value = "10000000000000" />

                                    <p class="help-block">Listado de direcciones con <a href="plantillas/Direcciones.xlsx">este</a> formato (.xlsx)</p>
                                  </div>

                              </div>
                            
                            <div class="box-footer">
                                <button type="submit" name="action" value="download_coordenadas" class="btn btn-success"><i class="fa fa-download"></i></button>
                            </div>
						</div>
					</div>
				</div>
			</form>
            
            <form role="form" method="post" action="actions.php" enctype="multipart/form-data">
				<div class="col-md-3">
					<div class="box box-primary">
						<div class="box-body">
                            <div class="box-header with-border">
                                <h3 class="box-title">Distancia minima</h3>

                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-cruce_coordenadas"><i class="fa fa-info-circle"></i></button>
                                  </div>
                            </div>

                              <div class="form-group">
                                  <div class="form-group">
                                    <label>Coordenadas (.xlsx)</label>
                                    <input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]">
                                    <input type="hidden" name="MAX_FILE_SIZE" value = "10000000000000" />

                                    <p class="help-block">Listado de direcciones con <a href="plantillas/Cruce coordenadas.xlsx">este</a> formato (.xlsx)</p>
                                  </div>

                              </div>
                            
                            <div class="box-footer">
                                <button type="submit" name="action" value="download_cruce_coordenadas" class="btn btn-success"><i class="fa fa-download"></i></button>
                            </div>
						</div>
					</div>
				</div>
			</form>
			<!-- .row -->
		</div>
		<!-- .col -->
	</div>
	<!-- .row -->
		
		<!-- INFO -->
					<div class="modal fade" id="modal-cruce_coordenadas">
					  <div class="modal-dialog">
						<div class="modal-content">
						  <div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title">DISTANCIA MINIMA</h4>
						  </div>
						  <div class="modal-body">
							<p>
                                Esta elaboración saca la distancia minima entre unas coordenadas.<br>
                                <br>                                
                                Se buscará por cada dato de la hoja "RESULTADOS" la dirección más cercana analizando las coordenadas de la hoja "REFERENCIAS".<br>
                                Los datos de las columnas DATO y REFERENCIA relativos respectivamente a las hojas "RESULTADOS" y "REFERENCIAS" son datos que sirven de ayuda.<br>
                                Por ejemplo en la columna DATO de la hoja "RESULTADOS" se podrían poner los CUPS y en la columna REFERENCIA de la hoja "REFERENCIAS" unas direcciones.<br>
                                Por las coordenadas de cada CUPS se encontrará la dirección más cercana.<br>
                                <br>
								NO SE PUEDEN AÑADIR COLUMNAS ADICIONALES.
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