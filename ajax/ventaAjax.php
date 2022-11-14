<?php
    $peticion_ajax=true;
    require_once "../config/APP.php";
	include "../vistas/inc/session_start.php";

	if(isset($_POST['modulo_venta'])){

		/*--------- Instancia al controlador ---------*/
		require_once "../controladores/ventaControlador.php";
        $ins_venta = new ventaControlador();
        
		/*--------- Agregar producto a carrito ---------*/
		if($_POST['modulo_venta']=="agregar_producto"){
			echo $ins_venta->agregar_producto_carrito_controlador();
        }
        
        /*--------- Eliminar producto de carrito ---------*/
		if($_POST['modulo_venta']=="eliminar_producto"){
			echo $ins_venta->eliminar_producto_carrito_controlador();
		}

		/*--------- Actualizar producto de carrito ---------*/
		if($_POST['modulo_venta']=="actualizar_producto"){
			echo $ins_venta->actualizar_producto_carrito_controlador();
		}

		/*--------- Buscar cliente ---------*/
		if($_POST['modulo_venta']=="buscar_cliente"){
			echo $ins_venta->buscar_cliente_venta_controlador();
		}

		/*--------- Agregar cliente a carrito ---------*/
		if($_POST['modulo_venta']=="agregar_cliente"){
			echo $ins_venta->agregar_cliente_venta_controlador();
		}

		/*--------- Eliminar cliente de carrito ---------*/
		if($_POST['modulo_venta']=="eliminar_cliente"){
			echo $ins_venta->eliminar_cliente_venta_controlador();
		}

		/*--------- Buscar codigo ---------*/
		if($_POST['modulo_venta']=="buscar_codigo"){
			echo $ins_venta->buscar_codigo_venta_controlador();
		}

		/*--------- Aplicar descuento ---------*/
		if($_POST['modulo_venta']=="aplicar_descuento"){
			echo $ins_venta->aplicar_descuento_venta_controlador();
		}

		/*--------- Remover descuento ---------*/
		if($_POST['modulo_venta']=="remover_descuento"){
			echo $ins_venta->remover_descuento_venta_controlador();
		}

		/*--------- Registrar venta ---------*/
		if($_POST['modulo_venta']=="registrar_venta"){
			echo $ins_venta->registrar_venta_controlador();
		}

		/*--------- Registrar pago de venta---------*/
		if($_POST['modulo_venta']=="agregar_pago"){
			echo $ins_venta->agregar_pago_venta_controlador();
		}

		/*--------- Eliminar venta---------*/
		if($_POST['modulo_venta']=="eliminar_venta"){
			echo $ins_venta->eliminar_venta_controlador();
		}
		
	}else{
		session_destroy();
		header("Location: ".SERVERURL."login/");
	}