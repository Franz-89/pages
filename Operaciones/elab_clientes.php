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
        Elaboraciones clientes
        <small>Elaboración especiales para los clientes</small>
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
			<div class="row">
				
				<!-- GRUPO DIA -->
				<div class="col-md-6">
					<div class="box box-primary">
						<div class="box-body">
							<div class="box-header with-border">
								<h3 class="box-title">Grupo Dia</h3>
								
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-csvdia"><i class="fa fa-info-circle"></i></button>
								  </div>
							</div>
							<!-- CSV BT GRUPO DIA (.xlsx) -->
							<div class="col-md-6">
								<form role="form" method="post" action="elab_actions.php" enctype="multipart/form-data">
								  <div class="form-group">
									  <div class="form-group">
										<label>CSV BT GRUPO DIA (.xlsx)</label>
										<input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]" multiple="multiple">
										<input type="hidden" name="MAX_FILE_SIZE" value = "10485760" />

										<p class="help-block">FF BT ENDESA (Max 10Mb)</p>
									  </div>
									<div class="box-footer">
										<button type="submit" name="action" value="csv_bt_dia" class="btn btn-primary">Elaborar</button>
									</div>
								  </div>
								</form>
							</div>

							<!-- CSV MT GRUPO DIA (.xlsx) -->
							<div class="col-md-6">
								<form role="form" method="post" action="elab_actions.php" enctype="multipart/form-data">
								  <div class="form-group">
									  <div class="form-group">
										<label>CSV MT GRUPO DIA (.txt)</label>
										<input type="file" accept="text/plain" name ="fichero[]" multiple="multiple">
										<input type="hidden" name="MAX_FILE_SIZE" value = "10485760" />

										<p class="help-block">FF MT (Max 10Mb)</p>
									  </div>
									<div class="box-footer">
										<button type="submit" name="action" value="csv_mt_dia" class="btn btn-primary">Elaborar</button>
									</div>
								  </div>
								</form>
							</div>
						</div>
					</div>
				</div>
				
				<!-- INFO -->
				<div class="modal fade" id="modal-csvdia">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">CSV GRUPO DIA</h4>
					  </div>
					  <div class="modal-body">
						<p>
							Tras haber actualizado los códigos de oficina de los suministros a través del fichero que envian mensualmente desde GRUPO DIA, se elaboran los CSVs a partir del fichero de facturación original. <br><br>
							BT y MT tienen el mismo formato de salida. <br><br>
							Una vez sacado, se comprueba que estén todos los códigos de oficina en la columna U. Si falta alguno se reclaman al cliente antes de enviar el fichero. <br><br>
							Como ultima comprobación habría que ver que la suma de los importes de cada CUPS de la columna M, menos la suma de los importes de la columna N se correspondan al total de la agrupada en la columna M (si es negativo) o N (si es positivo). <br><br>
							Cada factura agrupada se identifica con el numero 1 en la columna A. Ese numero identifica al encabezado.
						</p>
					  </div>
					  <div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
					  </div>
					</div>
					<!-- /.modal-content -->
				  </div>
				  <!-- /.modal-dialog -->
				</div>
				<!-- /.modal -->
				
				<!-- INDITEX -->
				<div class="col-md-6">
					<div class="box box-primary">
						<div class="box-body">
							<div class="box-header with-border">
								<h3 class="box-title">Inditex</h3>
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-csvinditex"><i class="fa fa-info-circle"></i></button>
								  </div>
							</div>
							<!-- Inditex BT -->
							<div class="col-md-6">
								<form role="form" method="post" action="elab_actions.php" enctype="multipart/form-data">
								  <div class="form-group">
									  <div class="form-group">
										<label>Inditex desde Endesa BT (.xlsx)</label>
										<input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]" multiple="multiple">
										<input type="hidden" name="MAX_FILE_SIZE" value = "10485760" />

										<p class="help-block">FF BT (Max 10Mb)</p>
									  </div>
									<div class="box-footer">
										<button type="submit" name="action" value="inditex_endesa_bt" class="btn btn-primary">Elaborar</button>
									</div>
								  </div>
								</form>
							</div>

							<!-- Inditex AT -->
							<div class="col-md-6">
								<form role="form" method="post" action="elab_actions.php" enctype="multipart/form-data">
								  <div class="form-group">
									  <div class="form-group">
										<label>Inditex desde Endesa MT (.xlsx)</label>
										<input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name ="fichero[]" multiple="multiple">
										<input type="hidden" name="MAX_FILE_SIZE" value = "10485760" />

										<p class="help-block">FF MT (Max 10Mb)</p>
									  </div>
									<div class="box-footer">
										<button type="submit" name="action" value="inditex_endesa_mt" class="btn btn-primary">Elaborar</button>
									</div>
								  </div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<!-- INFO -->
			<div class="modal fade" id="modal-csvinditex">
			  <div class="modal-dialog">
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					  <span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">CSV INDITEX</h4>
				  </div>
				  <div class="modal-body">
					<p>
						Saca unos datos necesarios al cliente para la contabilidad.<br><br>
						En el caso de que haya una hoja nombrada "Verifiaciones" hay que comprobar que los que se detalla esté bien e informar al cliente.<br>
						Se trata principalmente de cambios de contrato.<br><br>
						Tras haber comprobado todo, se elimina la hoja "Verificaciones" (si existe) y se guarda el fichero en formato CSV para remitirselo al cliente.
					</p>
				  </div>
				  <div class="modal-footer">
					<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
				  </div>
				</div>
				<!-- /.modal-content -->
			  </div>
			  <!-- /.modal-dialog -->
			</div>
			<!-- /.modal -->
			
			
			<div class="row">
				
				<!-- AON -->
				<div class="col-md-3">
					<div class="box box-primary">
						<div class="box-body">
							<div class="box-header with-border">
								<h3 class="box-title">Aon</h3>
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-auditadasaon"><i class="fa fa-info-circle"></i></button>
								  </div>
							</div>
							<!-- Elaboración Inditex -->
							<div class="col-md-12">
								<form role="form" method="post" action="elab_actions.php">
									<label>Informe fras auditadas</label>
								  <!-- Desde -->
								  <div class="form-group">
									<label>Desde (incluido):</label>

									<div class="input-group date">
									  <div class="input-group-addon">
										<i class="fa fa-calendar"></i>
									  </div>
									  <input type="text" class="form-control pull-right fecha" id="desde" name="desde" value="">
									</div>
								  </div>

								<!-- Hasta -->
								  <div class="form-group">
									<label>Hasta (no incluido):</label>

									<div class="input-group date">
									  <div class="input-group-addon">
										<i class="fa fa-calendar"></i>
									  </div>
									  <input type="text" class="form-control pull-right fecha" id="hasta" name="hasta" value="">
									</div>
								  </div>
									<div class="box-footer">
										<button type="submit" name="action" value="aon_auditadas" class="btn btn-primary">Elaborar</button>
									</div>
								  </div>
								</form>
							</div>
						</div>
				</div>
				
				<!-- INFO -->
				<div class="modal fade" id="modal-auditadasaon">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">AON AUDITADAS</h4>
					  </div>
					  <div class="modal-body">
						<p>
							Saca unos datos necesarios al cliente para su BBDD internacional basandose en el dato "fecha auditada" de nuestra BBDD.<br><br>
						</p>
					  </div>
					  <div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
					  </div>
					</div>
					<!-- /.modal-content -->
				  </div>
				  <!-- /.modal-dialog -->
				</div>
				<!-- /.modal -->
				
				
				<!-- EUSKALTEL -->
				<div class="col-md-3">
					<div class="box box-primary">
						<div class="box-body">
							<div class="box-header with-border">
								<h3 class="box-title">Euskaltel</h3>
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-euskemit"><i class="fa fa-info-circle"></i></button>
								  </div>
							</div>
							
							<div class="col-md-12">
								<form role="form" method="post" action="elab_actions.php">
									<label>Fact emitidas/prorrateadas</label>
								  <!-- Desde -->
								  <div class="form-group">
									<label>Desde (incluido):</label>

									<div class="input-group date">
									  <div class="input-group-addon">
										<i class="fa fa-calendar"></i>
									  </div>
									  <input type="text" class="form-control pull-right fecha" id="desde_dos" name="desde" value="">
									</div>
								  </div>
								
								<!-- Hasta -->
								  <div class="form-group">
									<label>Hasta (no incluido):</label>
									
									<div class="input-group date">
									  <div class="input-group-addon">
										<i class="fa fa-calendar"></i>
									  </div>
									  <input type="text" class="form-control pull-right fecha" id="hasta_dos" name="hasta" value="">
									</div>
								  </div>
									<div class="box-footer">
										<button type="submit" name="action" value="eusk_emitidas" class="btn btn-primary">Elaborar</button>
										<button type="submit" name="action" value="eusk_txt" class="btn btn-warning">TXT</button>
									</div>
								</form>
							</div>
						</div>
				</div>
			</div>
			
			<!-- INFO -->
			<div class="modal fade" id="modal-euskemit">
			  <div class="modal-dialog">
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					  <span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">EUSKALTEL EMITIDAS/PRORRATEADAS</h4>
				  </div>
				  <div class="modal-body">
					<p>
						Descarga, para cada mes y CUPS dentro de las fechas seleccionadas, los siguientes datos:
						<ul style="list-style-type:disc;">
							<li>suma_dias: la suma de los dias (hasta - desde) de todas las facturas emitidas en el mes</li>
							<li>suma_Consumo: la suma del consumo de todas las facturas emitidas en el mes</li>
							<li>suma_BI: la suma de la base imponible de todas las facturas emitidas en el mes</li>
							<li>prorr_dias: días facturados prorrateados en el mes.</li>
							<li>prorr_consumo: consumo real prorrateado en el mes.</li>
							<li>prorr_iva: BI prorrateada en el mes.</li>
						</ul>
						El resto de datos corresponden a los de la última línea del CUPS en el PS.
						
					</p>
				  </div>
				  <div class="modal-footer">
					<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
				  </div>
				</div>
				<!-- /.modal-content -->
			  </div>
			  <!-- /.modal-dialog -->
			</div>
				
			
				<!-- INFORME 3 AÑOS -->
				<div class="col-md-3">
					<form role="form" method="post" action="elab_actions.php">
					<div class="box box-primary">
						<div class="box-body">
							<div class="box-header with-border">
								<h3 class="box-title">Informe consumo 3 años</h3>
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-consumotresanos"><i class="fa fa-info-circle"></i></button>
								  </div>
							</div>
							
							<div class="col-md-12">
								<div class="form-group">
									<label>Cliente</label>

									<select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cli">
										<?php
										$Lista = new Lista('clientes');
										$Lista->print_list($cli);
										unset($Lista);
										?>
									</select>
								</div>
								<div class="box-footer">
									<button type="submit" name="action" value="informe_tres_anos" class="btn btn-primary">Elaborar</button>
								</div>
							</div>
						</div>
					</div>
					</form>
				</div>
				
				<!-- INFO -->
				<div class="modal fade" id="modal-consumotresanos">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">INFORME CONSUMO 3 AÑOS</h4>
					  </div>
					  <div class="modal-body">
						<p>
							Descarga para cada CUPS del cliente seleccionado los consumos prorrateados de los ultimos 3 años.
						</p>
					  </div>
					  <div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
					  </div>
					</div>
					<!-- /.modal-content -->
				  </div>
				  <!-- /.modal-dialog -->
				</div>
				
				
				<!-- 3.0/3.1 DE 3 A 6 PERIODOS -->
				<div class="col-md-3">
					<div class="box box-primary">
						<div class="box-body">
							<div class="box-header with-border">
								<h3 class="box-title">De 3 a 6 periodos</h3>
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-detresaseis"><i class="fa fa-info-circle"></i></button>
								  </div>
							</div>
							<!-- Elaboración Inditex -->
							<div class="col-md-12">
								<form role="form" method="post" action="elab_actions.php">
									<label>Informe suministros de 3 a 6 periodos</label>
									
									<div class="form-group">
										<label>Cliente</label>

										<select class="form-control select2" style="width: 100%;" data-placeholder="Cliente" name="cli">
											<?php
											$Lista = new Lista('clientes');
											$Lista->print_list($cli);
											unset($Lista);
											?>
										</select>
									</div>
									
								  <!-- Desde -->
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
										<button type="submit" name="action" value="deTresASeis" class="btn btn-primary">Elaborar</button>
									</div>
								  </div>
								</form>
							</div>
						</div>
				</div>
				
			<!-- INFO -->
				<div class="modal fade" id="modal-detresaseis">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">CSV GRUPO DIA</h4>
					  </div>
					  <div class="modal-body">
						<p>
							Saca:
							<ul>
								<li>CUPS</li>
								<li>CIF</li>
								<li>EMPRESA</li>
								<li>DIRECCIÓN</li>
								<li>POBLACIÓN</li>
								<li>PROVINCIA</li>
								<li>TARIFA</li>
								<li>MAXIMA P1 EN LA MUESTRA</li>
								<li>MAXIMA P2 EN LA MUESTRA</li>
								<li>MAXIMA P3 EN LA MUESTRA</li>
								<li>P1</li>
								<li>P2</li>
								<li>P3</li>
							</ul>
						  	<br><br>
						  	De los CUPS que tienen las siguientes caracteristicas:
						  	<ul>
								<li>En vigor</li>
								<li>Tarifa 3.0A/3.1A</li>
								<li>Una de las 3 potencias contratadas > 50</li>
								<li>Por lo menos una fra con pot max > pot max contratada</li>
						  	</ul>
						</p>
					  </div>
					  <div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
					  </div>
					</div>
				  </div>
				</div>
			
			
			</div>
			<!-- .row -->
        
            <div class="row">
                
                <!-- INFO -->
				<div class="modal fade" id="modal-singularesholdings">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">INFORME EDIFIOS SINGULARES/HOLINGS</h4>
					  </div>
					  <div class="modal-body">
						<p>
							Redacta y descarga los informes de edificios singulares y holdings de BBVA del año seleccionado.
						</p>
					  </div>
					  <div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
					  </div>
					</div>
					<!-- /.modal-content -->
				  </div>
				  <!-- /.modal-dialog -->
				</div>
                
                <!-- SINGULARES HOLDINGS BBVA-->
				<div class="col-md-3">
					<form role="form" method="post" action="elab_actions.php">
					<div class="box box-primary">
						<div class="box-body">
							<div class="box-header with-border">
								<h3 class="box-title">Edificios singulares/holdings BBVA</h3>
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-singularesholdings"><i class="fa fa-info-circle"></i></button>
								  </div>
							</div>
							
							<div class="col-md-12">
								<div class="form-group">
									<label>Año</label>

									<select class="form-control select2" style="width: 100%;" data-placeholder="Año" name="ano">
										<?php
										$Lista = new Lista('ano');
										$Lista->print_list(date('Y'));
										?>
									</select>
								</div>
								<div class="box-footer">
									<button type="submit" name="action" value="singulares_holdings" class="btn btn-primary">Elaborar</button>
								</div>
							</div>
						</div>
					</div>
					</form>
				</div>
                
                <!-- INFO -->
				<div class="modal fade" id="modal-gastoconsumosantander">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">INFORME GASTOS Y CONSUMOS SANTANDER</h4>
					  </div>
					  <div class="modal-body">
						<p>
							Redacta y descarga el informe de gastos y consumo de SANTANDER desde Consumos NT utilizando los siguientes datos.<br><br>
                            
                            <ul>
								<li>TODOS: los datos de las empresas BANCO POPULAR ESPAÑOL, S.A., BANCO POPULAR PASTOR, S.A., BANCO SANTANDER, S.A. menos los suministros de ABELIAS, JOSEFA y RECOLETOS</li>
								<li>ABELIAS: los datos del suministro ES0022000008956801CJ</li>
								<li>JOSEFA: los datos de los suminsitros ES0022000005731619AY, ES0022000005731620AF</li>
								<li>RECOLETOS: los datos del suministro ES0022000005731516KH</li>
								<li>LUCA9B: los datos del suminsitro ES0022000009106366ZD</li>
								<li>LUCA11B: los datos del suministro ES0022000009106364ZF</li>
							</ul>
						</p>
					  </div>
					  <div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
					  </div>
					</div>
					<!-- /.modal-content -->
				  </div>
				  <!-- /.modal-dialog -->
				</div>
                
                <div class="col-md-6">
					<form role="form" method="post" action="elab_actions.php">
					<div class="box box-primary">
						<div class="box-body">
							<div class="box-header with-border">
								<h3 class="box-title">SANTANDER</h3>
								<div class="box-tools pull-right">
									<button type="button" class="btn btn-box-tool" data-toggle="modal" data-target="#modal-gastoconsumosantander"><i class="fa fa-info-circle"></i></button>
								  </div>
							</div>
							
							<div class="col-md-6">
                                <label>GASTOS Y CONSUMOS</label>
								<div class="form-group">
                                    
									<label>Año</label>

									<select class="form-control select2" style="width: 100%;" data-placeholder="Año" name="ano">
										<?php
										$Lista->print_list(date('Y'));
										unset($Lista);
										?>
									</select>
								</div>
								<div class="box-footer">
									<button type="submit" name="action" value="gasto_consumo_santander" class="btn btn-primary">Elaborar</button>
								</div>
							</div>
                            
                            <div class="col-md-6">
                                <label>INFORME A-OK</label>
								<div class="form-group">
								<div class="box-footer">
									<button type="submit" name="action" value="a_ok_santander" class="btn btn-primary">Elaborar</button>
								</div>
							</div>
						</div>
					</div>
					</form>
				</div>
            </div>
            
            <!-- CSV MT GRUPO DIA (.xlsx) -->
            <div class="col-md-3">
                <form role="form" method="post" action="elab_actions.php" enctype="multipart/form-data">
                    <div class="box box-primary">
                        <div class="box-body">
                            <div class="box-header with-border">
								<h3 class="box-title">CORREOS</h3>
							</div>
                            <div class="col-md-12">
                              <div class="form-group">
                                  <div class="form-group">
                                    <label>TXT BI-IVA NATURGY (.txt)</label>
                                    <input type="file" accept="text/plain" name ="fichero[]" multiple="multiple">
                                    <input type="hidden" name="MAX_FILE_SIZE" value = "10485760" />
                                  </div>
                                <div class="box-footer">
                                    <button type="submit" name="action" value="txt_bi_iva_correos" class="btn btn-primary">Elaborar</button>
                                </div>
                            </div>
                          </div>
                        </div>
                    </div>
                </form>
            </div>
            
		</div>
		<!-- .col -->
	</div>
	<!-- .row -->
		
	</div>
	<!-- .content wrapper -->
		
    </section>

  
<?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php"); ?>

</body>
</html>