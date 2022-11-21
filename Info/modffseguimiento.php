<?php

$fichero 	= basename(__FILE__);
require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$id = $_GET['id'];

$Conn = new Conn('local', 'enertrade');
$row = $Conn->oneRow("SELECT * FROM seguimiento_cliente_ff WHERE ID=$id");
unset($Conn);

$date = new DateClass;
$row['FECHA_CARGA']         = $date->fromToFormat($row['FECHA_CARGA']);
$row['FECHA_VALIDACION']    = $date->fromToFormat($row['FECHA_VALIDACION']);
$row['FECHA_VALIDACION_TE'] = $date->fromToFormat($row['FECHA_VALIDACION_TE']);
unset($date);

include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>

	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Fichero de facturación
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li>Info</li>
		<li class="active">Modifica FF seguimiento cliente</li>
      </ol>
    </section>
	
    <!-- Main content -->
    <section class="content">

      <!-- SELECT2 EXAMPLE -->
	  <form role="form" method="post" action="actions.php">
      <div class="box box-primary">
        <div class="box-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
				
                  <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
                  <input type="hidden" id="action" name="action" value="mod_ff_seguimiento">
                  
				<div class="form-group">
					<label>Fecha de carga</label>
					<div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="text" class="form-control pull-right fecha" id="fecha_carga_ff" name="fecha_carga_ff" value="<?php echo $row['FECHA_CARGA']; ?>">
              </div>
				 </div>
				  
                <div class="form-group">
                    <label>Fecha de validación</label>
                    <div class="input-group date">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" class="form-control pull-right fecha" id="fecha_validacion_ff" name="fecha_validacion_ff" value="<?php echo $row['FECHA_VALIDACION']; ?>">
                    </div>
                 </div>

                 <div class="form-group">
                    <label>Fecha de validación TE</label>
                    <div class="input-group date">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" class="form-control pull-right fecha" id="fecha_validacion_te_ff" name="fecha_validacion_te_ff" value="<?php echo $row['FECHA_VALIDACION_TE']; ?>">
                    </div>
                 </div>
                  
                <div class="form-group">
                    <label>Fras cargadas</label>
                    <input type="number" class="form-control" id="fras_cargadas" name="fras_cargadas" value="<?php echo $row['FACTURAS_CARGADAS']; ?>">
                </div>

                <div class="form-group">
                    <label>Abonos cargados</label>
                    <input type="number" class="form-control" id="abonos_cargados" name="abonos_cargados" value="<?php echo $row['ABONOS_CARGADOS']; ?>">
                </div>
                  
				<div class="form-group">
					<label>Comentarios</label>
					<textarea class="form-control" rows="3" placeholder="Comentarios ..." name="comentarios"><?php echo $row['COMENTARIOS'];?></textarea>
				</div>
				
				<div class="box-footer">
					<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i></button>
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

</script>
</body>
</html>