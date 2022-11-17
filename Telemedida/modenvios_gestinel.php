<?php

$fichero 	= basename(__FILE__);
$sql_table 	= 'envios_gestinel';

require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$conn = connect_server("local", 'enertrade');

if (isset($_GET['id']) && !empty($_GET['id'])){
	
	$action 	= "mod";
	$readonly 	= "readonly";
	
	$id 		= $_GET['id'];
	$strSQL 	= "SELECT * FROM $sql_table WHERE id=$id";

	$query 		= mysqli_query($conn, $strSQL);
	$result 	= mysqli_fetch_assoc($query);
	
	$grupo_envio	= $result['GRUPO_ENVIO'];
	$prioridad		= $result['PRIORIDAD'];
	$encargado		= $result['ENCARGADO'];
	$a 				= $result['A'];
	$copia			= $result['COPIA'];
	$observaciones	= $result['OBSERVACIONES'];
	
} else {
	
	$action 	= "add";
	$readonly 	= "";
	
	$id = "";
	if (isset($_GET['grupo_envio']))	{$grupo_envio 	= $_GET['grupo_envio'];} 	else {$grupo_envio 		= "";}
	if (isset($_GET['prioridad']))		{$prioridad 	= $_GET['prioridad'];} 		else {$prioridad 		= 1;}
	if (isset($_GET['encargado']))		{$encargado 	= $_GET['encargado'];} 		else {$encargado 		= "";}
	if (isset($_GET['a']))				{$a 			= $_GET['a'];} 				else {$a 				= "";}
	if (isset($_GET['copia']))			{$copia 		= $_GET['copia'];} 			else {$copia 			= "";}
	if (isset($_GET['observaciones']))	{$observaciones = $_GET['observaciones'];} 	else {$observaciones 	= "";}
}

include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>

	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Datos contadores
		 <small><strong><font color="red">
			<?php if (isset($_GET['error'])){if ($_GET['error']=='duplicado'){echo "Este grupo ya existe!<br>";};}; ?>
			Grupo de envío y Destinatario no pueden ser vacíos!
		</font></strong></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li>Telemedida</li>
		<li class="active">Datos contadores</li>
      </ol>
    </section>
	
    <!-- Main content -->
    <section class="content">

      <!-- SELECT2 EXAMPLE -->
	<div class="row">
      <div class="col-md-12">
		  <form role="form" method="post" action="<?php echo "acciones_envio.php?table=$sql_table&action=$action";?>">
		  <div class="box box-default">
			<div class="box-body">
				<div class="form-group">
          		<div class="row">
              		<div class="col-md-3">
						<div class="form-group">
							<label>Grupo de envío</label>
							<input class="form-control" type="text" placeholder="Grupo de envío" name="grupo_envio" value="<?php echo $grupo_envio;?>" <?php echo $readonly;?>/>
						</div>
					</div>
					
					<div class="col-md-1">
						<div class="form-group">
							<label>Prioridad</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Prioridad" name="prioridad">
								<?php
								$Lista = new Lista('prioridad');
								$Lista->print_list($prioridad);
								?>
							</select>
						 </div>
					</div>
					
					<div class="col-md-3">
						<div class="form-group">
							<label>Encargado</label>
							<select class="form-control select2" style="width: 100%;" data-placeholder="Encargado" name="encargado">
								<?php
								$Lista->change_list('mail_empleados');
								$Lista->print_list($encargado);
								unset($Lista);
								?>
							</select>
						 </div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label>Destinatarios</label>
							<textarea class="form-control" rows="3" placeholder="Destinatarios" name="a"><?php echo $a;?></textarea>
						</div>
					</div>
					
					<div class="col-md-4">
						<div class="form-group">
							<label>Copia</label>
							<textarea class="form-control" rows="3" placeholder="Copia" name="copia"><?php echo $copia;?></textarea>
						</div>
					</div>
					
					<div class="col-md-4">
						<div class="form-group">
							<label>Comentarios</label>
							<textarea class="form-control" rows="3" placeholder="Comentarios ..." name="observaciones"><?php echo $observaciones;?></textarea>
						</div>
					</div>
					
					
				</div>
				
				<input type="hidden" name="id" value="<?php echo $id;?>"/>
				
				<div class="box-footer">
					<button type="submit" class="btn btn-primary">Guardar</button>
				</div>
				
              </div>
              <!-- /.form-group -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
	  </form>
	<!-- /form -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php") ?>


<!-- page script -->
<script>

	function confirmation() {
    if(confirm("Ejecutar esta acción?"))
    {
        return true;
    }
    return false;
	}
</script>
</body>
</html>