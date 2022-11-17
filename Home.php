<?php
$fichero = str_replace(".php", "", basename(__FILE__));

include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/header.php");

?>
	
	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Home
        <small>Optional description</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Intranet interna</a></li>
        <li class="active">Home</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content container-fluid">
	
        <h3>
            ¿Tienes dudas?<br>
            Consulta nuestra <a href="https://sites.google.com/view/wikienertrade/inicio" target="_blank">Wiki</a>!
        </h3>
		
		<!-- CONTENIDO -->
		

    </section>
    <!-- /.content -->
  
<?php include ($_SERVER['DOCUMENT_ROOT']."/Enertrade/php/sections/footer.php") ?>

</body>
</html>