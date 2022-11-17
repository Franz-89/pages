<?php

$fichero 	= basename(__FILE__);
require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$id = $_GET['id'];

$Conn = new Conn('local', 'enertrade');
$row = $Conn->oneRow("SELECT * FROM seguimiento_cliente_informes WHERE ID=$id");
unset($Conn);

$date = new DateClass;
$row['ENVIADO'] = $date->fromToFormat($row['ENVIADO']);
unset($date);

include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>

	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Informes
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li>Info</li>
		<li class="active">Modifica informes seguimiento cliente</li>
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
                  <input type="hidden" id="action" name="action" value="mod_informe_seguimiento">
                  
				<div class="form-group">
					<label>Enviado</label>
					<div class="input-group date">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" class="form-control pull-right fecha" id="fecha_envio_informe" name="fecha_envio_informe" value="<?php echo $row['ENVIADO']; ?>">
                    </div>
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