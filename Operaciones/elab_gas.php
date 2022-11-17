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
				
				<!-- Consumo Mes/Periodo -->
				<div class="col-md-3">
					<form role="form" method="post" action="elab_gas_actions.php" enctype="multipart/form-data">
					  <div class="box box-primary">
						  <div class="box-header with-border">
								<h3 class="box-title">Validación Gas</h3>
							  
							 	<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-validaciongas"><i class="fa fa-info-circle"></i></button>
								  </div>
							</div>
						<div class="box-body">
						  <div class="form-group">
							  
						  <div class="form-group">
								<label>Cliente</label>
								
								<select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cli">
									<?php
									$Lista = new Lista('clientes');
									$Lista->print_list();
									?>
								</select>
							</div>
							  
							  <!-- FECHAS DE EMISIÓN -->
                            <label>Emisión</label>
                              <div class="form-group">
                                <label>Desde (incluido):</label>

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
							  
							<div class="box-footer">
								<button type="submit" name="action" value="validacion_gas" class="btn btn-primary">Validar</button>
							</div>
						  </div>
						</div>
					  </div>
					</form>
				</div>
				
				<!-- Consumo Mes/periodo Modal -->
				<div class="modal fade" id="modal-validaciongas">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">CdC</h4>
					  </div>
					  <div class="modal-body">
						<p>
							Saca los consumos dividos por mes y periodo de los ultimos 12 meses completos desde la sección Consumos(NT).<br><br>
							En el caso de que no se agrupen las tarifas el sistema avisará si faltan los datos de algunos meses.<br><br>
							En clientes muy grandes, si no se agrupa ninguna tarifa, el servidor podría no poder elaborar toda la información solicitada. En estos casos, si se necesitan todos los puntos por separado, lo ideal es recuperarlos agrupando primero unas tarifas y luego otras.
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