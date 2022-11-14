<?php
    $peticion_ajax=true;
    require_once "../config/APP.php";
	include "../vistas/inc/session_start.php";

	if(isset($_POST['modulo_kardex'])){

		/*--------- Instancia al controlador ---------*/
		require_once "../controladores/kardexControlador.php";
        $ins_kardex = new kardexControlador();

        /*--------- Buscar kardex de producto ---------*/
        if($_POST['modulo_kardex']=="kardex_producto"){
            echo $ins_kardex->buscar_kardex_producto_controlador();
		}

	}else{
		session_destroy();
		header("Location: ".SERVERURL."login/");
	}