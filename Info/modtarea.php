<?php

$fichero 	= basename(__FILE__);
require($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/func/includes.php");

$id = $_GET['id'];

$Conn = new Conn('local', 'enertrade');
$row = $Conn->oneRow("SELECT * FROM tareas WHERE ID=$id");
unset($Conn);

$duplicate = (isset($_GET['action']) && $_GET['action']=='duplicate') ? true : false;
$action = ($duplicate) ? 'duplicate_tarea' : 'mod_tarea';
$str_fecha_caducidad = ($duplicate) ? 'Nueva fecha de caducidad' : 'Fecha de caducidad';

$date = new DateClass;
$row['FECHA_CADUCIDAD'] = $date->fromToFormat($row['FECHA_CADUCIDAD']);
unset($date);

include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");
?>

	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Tarea
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li>Info</li>
		<li class="active">Modifica Tarea</li>
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
                  <input type="hidden" id="action" name="action" value="<?php echo $action; ?>">
                  
                  <?php
                  switch (true){
                      case ($usuario=='vmrodriguez@enertrade.es'):
                      case ($usuario=='mmontero@enertrade.es'):
                      case ($usuario=='slizarralde@enertrade.es'):
                      case ($row['ASIGNADO_POR']==$usuario):
                          echo '<div class="form-group">
                                    <label>'.$str_fecha_caducidad.'</label>
                                    <div class="input-group date">
                                      <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                      </div>
                                      <input type="text" class="form-control pull-right fecha" id="fecha_caducidad" name="fecha_caducidad" value="'.$row['FECHA_CADUCIDAD'].'">
                                    </div>
                                 </div>';
                          break;
                  }
                  
                  if (!$duplicate){
                      echo '<div class="form-group">
                      <label>Prioridad</label>
                      <select class="form-control select2" style="width: 100%;" data-placeholder="Prioridad" id="prioridad" name="prioridad">';
                      
                      $Lista = new Lista('prioridad');
                      $Lista->print_list($row['PRIORIDAD']);
                      unset($Lista);
                      echo '</select>
                     </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" rows="3" placeholder="Descripcion ..." name="descripcion">'.$row['DESCRIPCION'].'</textarea>
                     </div>

                    <div class="form-group">
                        <label>Comentarios</label>
                        <textarea class="form-control" rows="3" placeholder="Comentarios ..." name="comentarios">'.$row['COMENTARIOS'].'</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Progreso</label>
                        <input class="form-control" type="number" min="0" max="100" step="1" name="progreso" value="'.$row['PROGRESO'].'">
                    </div>
                    ';
                      
                }
                ?>
				
              
                  
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