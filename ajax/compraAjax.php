<?php
    $peticion_ajax=true;
    require_once "../config/APP.php";
	include "../vistas/inc/session_start.php";

	if(isset($_POST['modulo_compra'])){

		/*--------- Instancia al controlador ---------*/
		require_once "../controladores/compraControlador.php";
        $ins_compra = new compraControlador();
        
        /*--------- Agregar producto a compra ---------*/
        if($_POST['modulo_compra']=="verificar_producto"){
            echo $ins_compra->verificar_producto_compra_controlador();
		}

		/*--------- Agregar producto a carrito ---------*/
		if($_POST['modulo_compra']=="agregar_producto"){
			echo $ins_compra->agregar_producto_carrito_controlador();
		}

		/*--------- Eliminar producto de carrito ---------*/
		if($_POST['modulo_compra']=="quitar_producto"){
			echo $ins_compra->eliminar_producto_carrito_controlador();
		}

		/*--------- Agregar compra ---------*/
		if($_POST['modulo_compra']=="agregar_compra"){
			echo $ins_compra->agregar_compra_controlador();
		}

	}else{
		session_destroy();
		header("Location: ".SERVERURL."login/");
	}