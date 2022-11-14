<?php

	if($peticion_ajax){
		require_once "../modelos/mainModel.php";
	}else{
		require_once "./modelos/mainModel.php";
	}

	class compraControlador extends mainModel{

        /*---------- Controlador verificar producto ----------*/
        public function verificar_producto_compra_controlador(){

            /*== Recuperando codigo del producto ==*/
			$codigo=mainModel::limpiar_cadena($_POST['codigo_producto']);

			/*== Comprobando producto en la DB ==*/
            $check_producto=mainModel::ejecutar_consulta_simple("SELECT * FROM producto WHERE producto_codigo='$codigo'");

            if($check_producto->rowCount()<=0){
                $formulario = '
                <input type="hidden" name="producto_tipo_add" value="Registrar">
                <input type="hidden" name="producto_id_add" value="0">
                <input type="hidden" name="producto_codigo_add" value="'.$codigo.'">
                <fieldset>
                    <legend><i class="fas fa-barcode"></i> &nbsp; Código y SKU</legend>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="producto_codigo" class="bmd-label-floating">Código de barras '.CAMPO_OBLIGATORIO.'</label>
                                    <input type="text" class="form-control" id="producto_codigo" value="'.$codigo.'" readonly >
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="producto_sku" class="bmd-label-floating">SKU</label>
                                    <input type="text" pattern="[a-zA-Z0-9- ]{1,70}" class="form-control" name="producto_sku_add" id="producto_sku" maxlength="70" >
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <br><br><br>
                <fieldset>
                    <legend><i class="fas fa-box"></i> &nbsp; Información del producto</legend>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="producto_nombre" class="bmd-label-floating">Nombre '.CAMPO_OBLIGATORIO.'</label>
                                    <input type="text" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\- ]{1,97}" class="form-control" name="producto_nombre_add" id="producto_nombre" maxlength="97" >
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="producto_stock_total" class="bmd-label-floating">Stock o existencias '.CAMPO_OBLIGATORIO.'</label>
                                    <input type="text" pattern="[0-9]{1,20}" class="form-control" name="producto_stock_total_add" id="producto_stock_total" maxlength="20">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="producto_stock_minimo" class="bmd-label-floating">Stock mínimo '.CAMPO_OBLIGATORIO.'</label>
                                    <input type="text" pattern="[0-9]{1,9}" class="form-control" name="producto_stock_minimo_add" id="producto_stock_minimo" maxlength="9">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="producto_unidad" class="bmd-label-floating">Presentación del producto '.CAMPO_OBLIGATORIO.'</label>
                                    <select class="form-control" name="producto_unidad_add" id="producto_unidad">
                                        <option value="" selected="" >Seleccione una opción</option>
                                            '.mainmodel::generar_select(PRODUCTO_UNIDAD,"VACIO").'
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="producto_precio_compra" class="bmd-label-floating">Precio de compra (Con impuesto incluido) '.CAMPO_OBLIGATORIO.'</label>
                                    <input type="text" pattern="[0-9.]{1,25}" class="form-control" name="producto_precio_compra_add" value="0.00" id="producto_precio_compra" maxlength="25">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="producto_precio_venta" class="bmd-label-floating">Precio de venta (Con impuesto incluido) '.CAMPO_OBLIGATORIO.'</label>
                                    <input type="text" pattern="[0-9.]{1,25}" class="form-control" name="producto_precio_venta_add" value="0.00" id="producto_precio_venta" maxlength="25">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="producto_precio_venta_mayoreo" class="bmd-label-floating">Precio de venta por mayoreo (Con impuesto incluido) '.CAMPO_OBLIGATORIO.'</label>
                                    <input type="text" pattern="[0-9.]{1,25}" class="form-control" name="producto_precio_venta_mayoreo_add" value="0.00" id="producto_precio_venta_mayoreo" maxlength="25">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="producto_descuento" class="bmd-label-floating">Descuento para ventas (%) '.CAMPO_OBLIGATORIO.'</label>
                                    <input type="text" pattern="[0-9]{1,2}" class="form-control" name="producto_descuento_add" value="0" id="producto_descuento" maxlength="2">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="producto_marca" class="bmd-label-floating">Marca</label>
                                    <input type="text" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,30}" class="form-control input-barcode" name="producto_marca_add" id="producto_marca" maxlength="30" >
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="producto_modelo" class="bmd-label-floating">Modelo</label>
                                    <input type="text" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,30}" class="form-control input-barcode" name="producto_modelo_add" id="producto_modelo" maxlength="30" >
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <br><br><br>
                <fieldset>
                    <legend><i class="fas fa-calendar-alt"></i> &nbsp; Vencimiento del producto</legend>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="producto_vencimiento_add" id="radio_vencimiento_1" value="Si" >
                                        <label class="form-check-label" for="radio_vencimiento_1">
                                            <i class="far fa-check-circle fa-fw"></i> &nbsp; Si vence
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="producto_vencimiento_add" id="radio_vencimiento_2" value="No" checked >
                                        <label class="form-check-label" for="radio_vencimiento_2">
                                        <i class="far fa-times-circle fa-fw"></i> &nbsp; No vence
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="producto_fecha_vencimiento" >Fecha de vencimiento (día/mes/año)</label>
                                        <input type="date" class="form-control" name="producto_fecha_vencimiento_add" id="producto_fecha_vencimiento" maxlength="30" value="'.date("Y-m-d").'" >
                                    </div>
                                </div>
                            </div>
                        </div>
                </fieldset>
                <br><br><br>
                <fieldset>
                    <legend><i class="fas fa-history"></i> &nbsp; Garantía de fabrica</legend>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="producto_garantia_unidad" class="bmd-label-floating">Unidad de tiempo '.CAMPO_OBLIGATORIO.'</label>
                                        <input type="text" pattern="[0-9]{1,2}" class="form-control input-barcode" name="producto_garantia_unidad_add" id="producto_garantia_unidad" maxlength="2" value="0" >
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="producto_garantia_tiempo" class="bmd-label-floating">Tiempo de garantía '.CAMPO_OBLIGATORIO.'</label>
                                        <select class="form-control" name="producto_garantia_tiempo_add" id="producto_garantia_tiempo">'.mainmodel::generar_select(GARANTIA_TIEMPO,"VACIO").'</select>
                                    </div>
                                </div>
                            </div>
                        </div>
                </fieldset>
                <br><br><br>
                <fieldset>
                    <legend><i class="fas fa-truck-loading"></i> &nbsp; categoría & estado</legend>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="producto_categoria" class="bmd-label-floating">Categoría '.CAMPO_OBLIGATORIO.'</label>
                                    <select class="form-control" name="producto_categoria_add" id="producto_categoria">
                                        <option value="" selected="" >Seleccione una opción</option>';
                                            $datos_categoria=mainModel::datos_tabla("Normal","categoria","categoria_id,categoria_nombre,categoria_estado",0);
                                            $cc=1;
                                            while($campos_categoria=$datos_categoria->fetch()){
                                                if($campos_categoria['categoria_estado']=="Habilitada"){
                                                    $formulario.='<option value="'.$campos_categoria['categoria_id'].'">'.$cc.' - '.$campos_categoria['categoria_nombre'].'</option>';
                                                    $cc++;
                                                }
                                            }
                                    $formulario.='</select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="producto_estado" class="bmd-label-floating">Estado del producto</label>
                                    <select class="form-control" name="producto_estado_add" id="producto_estado">
                                        <option value="Habilitado" selected="" >Habilitado</option>
                                        <option value="Deshabilitado">Deshabilitado</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>  
                ';
            }else{
                $campos=$check_producto->fetch();
                $formulario = '
                <input type="hidden" name="producto_tipo_add" value="Actualizar">
                <input type="hidden" name="producto_id_add" value="'.$campos['producto_id'].'">
                <input type="hidden" name="producto_codigo_add" value="'.$campos['producto_codigo'].'">
                <fieldset>
                    <legend><i class="fas fa-barcode"></i> &nbsp; Código y Nombre</legend>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="producto_codigo" class="bmd-label-floating">Código de barras</label>
                                    <input type="text" pattern="[a-zA-Z0-9- ]{1,70}" class="form-control" value="'.$campos['producto_codigo'].'"  id="producto_codigo" readonly >
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="producto_nombre" class="bmd-label-floating">Nombre</label>
                                    <input type="text" class="form-control" value="'.$campos['producto_nombre'].'" id="producto_nombre" readonly >
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <br><br><br>
                <fieldset>
                    <legend><i class="fas fa-box"></i> &nbsp; Información del producto</legend>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="producto_stock_total" class="bmd-label-floating">Stock o existencias compradas '.CAMPO_OBLIGATORIO.'</label>
                                    <input type="text" pattern="[0-9]{1,20}" class="form-control" name="producto_stock_total_add" id="producto_stock_total" maxlength="20">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="producto_precio_compra" class="bmd-label-floating">Precio de compra (Con impuesto incluido) '.CAMPO_OBLIGATORIO.'</label>
                                    <input type="text" pattern="[0-9.]{1,25}" class="form-control" name="producto_precio_compra_add" value="0.00" id="producto_precio_compra" maxlength="25">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="producto_precio_venta" class="bmd-label-floating">Precio de venta (Con impuesto incluido) '.CAMPO_OBLIGATORIO.'</label>
                                    <input type="text" pattern="[0-9.]{1,25}" class="form-control" name="producto_precio_venta_add" value="0.00" id="producto_precio_venta" maxlength="25">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="producto_precio_venta_mayoreo" class="bmd-label-floating">Precio de venta por mayoreo (Con impuesto incluido) '.CAMPO_OBLIGATORIO.'</label>
                                    <input type="text" pattern="[0-9.]{1,25}" class="form-control" name="producto_precio_venta_mayoreo_add" value="0.00" id="producto_precio_venta_mayoreo" maxlength="25">
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
                ';
            }
            $check_producto->closeCursor();
			$check_producto=mainModel::desconectar($check_producto);
            
            return $formulario;
        } /*-- Fin controlador --*/


        /*---------- Controlador agregar producto a compra ----------*/
        public function agregar_producto_carrito_controlador(){

            /*== Comprobando privilegios ==*/
			if($_SESSION['cargo_svi']!="Administrador"){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No tienes los permisos necesarios para realizar esta operación en el sistema.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }
            
            $tipo=mainModel::limpiar_cadena($_POST['producto_tipo_add']);

            if($tipo=="" || ($tipo!="Registrar" && $tipo!="Actualizar")){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No podemos agregar el producto por problemas de configuración",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            

            if($tipo=="Registrar"){

                $id=mainModel::limpiar_cadena($_POST['producto_id_add']);
                $codigo=mainModel::limpiar_cadena($_POST['producto_codigo_add']);
                $sku=mainModel::limpiar_cadena($_POST['producto_sku_add']);

                $nombre=mainModel::limpiar_cadena($_POST['producto_nombre_add']);
                
                $stock_minimo=mainModel::limpiar_cadena($_POST['producto_stock_minimo_add']);
                $stock_vendido=0;
                $unidad=mainModel::limpiar_cadena($_POST['producto_unidad_add']);
                
                $descuento=mainModel::limpiar_cadena($_POST['producto_descuento_add']);
                $marca=mainModel::limpiar_cadena($_POST['producto_marca_add']);
                $modelo=mainModel::limpiar_cadena($_POST['producto_modelo_add']);
                
                $vencimiento=mainModel::limpiar_cadena($_POST['producto_vencimiento_add']);
                $fecha_vencimiento=mainModel::limpiar_cadena($_POST['producto_fecha_vencimiento_add']);
                
                $garantia_unidad=mainModel::limpiar_cadena($_POST['producto_garantia_unidad_add']);
			    $garantia_tiempo=mainModel::limpiar_cadena($_POST['producto_garantia_tiempo_add']);
    
                $proveedor=0;
                $categoria=mainModel::limpiar_cadena($_POST['producto_categoria_add']);
                $estado=mainModel::limpiar_cadena($_POST['producto_estado_add']);

                /*== comprobar campos vacios ==*/
                if($codigo=="" || $nombre=="" || $stock_minimo=="" || $unidad=="" || $descuento==""){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"No has llenado todos los campos que son obligatorios",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                /*== Verificando integridad de los datos ==*/
                if(mainModel::verificar_datos("[a-zA-Z0-9- ]{1,70}",$codigo)){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"El código de barras no coincide con el formato solicitado",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                if($sku!=""){
                    if(mainModel::verificar_datos("[a-zA-Z0-9- ]{1,70}",$sku)){
                        $alerta=[
                            "Alerta"=>"simple",
                            "Titulo"=>"Ocurrió un error inesperado",
                            "Texto"=>"El SKU no coincide con el formato solicitado",
                            "Tipo"=>"error"
                        ];
                        echo json_encode($alerta);
                        exit();
                    }
                }

                if(mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\- ]{1,97}",$nombre)){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"El nombre no coincide con el formato solicitado",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                if(mainModel::verificar_datos("[0-9]{1,9}",$stock_minimo)){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"El stock mínimo debe de ser igual o mayor a 0, o no coincide con el formato solicitado.",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                if(mainModel::verificar_datos("[0-9]{1,2}",$descuento)){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"El descuento del producto debe de ser entre 0% a 99%, o no coincide con el formato solicitado.",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                if($marca!=""){
                    if(mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,30}",$marca)){
                        $alerta=[
                            "Alerta"=>"simple",
                            "Titulo"=>"Ocurrió un error inesperado",
                            "Texto"=>"La marca del producto no coincide con el formato solicitado.",
                            "Tipo"=>"error"
                        ];
                        echo json_encode($alerta);
                        exit();
                    }
                }
    
                if($modelo!=""){
                    if(mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,30}",$modelo)){
                        $alerta=[
                            "Alerta"=>"simple",
                            "Titulo"=>"Ocurrió un error inesperado",
                            "Texto"=>"El modelo del producto no coincide con el formato solicitado.",
                            "Tipo"=>"error"
                        ];
                        echo json_encode($alerta);
                        exit();
                    }
                }

                /*== Comprobando presentacion del producto ==*/
                if(!in_array($unidad, PRODUCTO_UNIDAD)){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"La presentación del producto no es correcta.",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                /*== Comprobando estado del producto ==*/
                if($estado!="Habilitado" && $estado!="Deshabilitado"){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"El estado del producto no es correcto.",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                /*== Comprobando vencimiento del producto ==*/
                if($vencimiento!="Si" && $vencimiento!="No"){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"El vencimiento del producto no es correcto.",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
                
                if(mainModel::verificar_fecha($fecha_vencimiento)){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"El fecha de vencimiento del producto no es correcta.",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                if($vencimiento=="Si"){
                    $fecha_hoy=date("Y-m-d");
                    if($fecha_hoy==$fecha_vencimiento){
                        $alerta=[
                            "Alerta"=>"simple",
                            "Titulo"=>"Ocurrió un error inesperado",
                            "Texto"=>"La fecha de vencimiento no puede ser la misma que hoy.",
                            "Tipo"=>"error"
                        ];
                        echo json_encode($alerta);
                        exit();
                    }
                }

                /*== Comprobando garantia del producto ==*/
                if(mainModel::verificar_datos("[0-9]{1,2}",$garantia_unidad)){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"La unidad de tiempo de la garantía no coincide con el formato solicitado.",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                if(!in_array($garantia_tiempo, GARANTIA_TIEMPO)){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"El tiempo de la garantía no es correcto.",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                if($garantia_unidad==0 || $garantia_tiempo=="N/A"){
                    if(($garantia_unidad==0 && $garantia_tiempo!="N/A") || (($garantia_unidad!=0 && $garantia_tiempo=="N/A"))){
                        $alerta=[
                            "Alerta"=>"simple",
                            "Titulo"=>"Ocurrió un error inesperado",
                            "Texto"=>"Si el producto no tiene garantía coloque 0 en la unidad de tiempo y N/A en tiempo de garantía.",
                            "Tipo"=>"error"
                        ];
                        echo json_encode($alerta);
                        exit();
                    }
                }

                /*== Comprobando codigo de producto ==*/
                $check_codigo=mainModel::ejecutar_consulta_simple("SELECT producto_codigo FROM producto WHERE producto_codigo='$codigo'");
                if($check_codigo->rowCount()>0){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"El código de producto que ha ingresado ya se encuentra registrado en el sistema",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
                $check_codigo->closeCursor();
			    $check_codigo=mainModel::desconectar($check_codigo);

                /*== Comprobando SKU de producto ==*/
                if($sku!=""){
                    $check_sku=mainModel::ejecutar_consulta_simple("SELECT producto_sku FROM producto WHERE producto_sku='$sku'");
                    if($check_sku->rowCount()>0){
                        $alerta=[
                            "Alerta"=>"simple",
                            "Titulo"=>"Ocurrió un error inesperado",
                            "Texto"=>"El SKU que ha ingresado ya se encuentra registrado en el sistema",
                            "Tipo"=>"error"
                        ];
                        echo json_encode($alerta);
                        exit();
                    }
                    $check_sku->closeCursor();
			        $check_sku=mainModel::desconectar($check_sku);
                }

                /*== Comprobando nombre de producto ==*/
                $check_nombre=mainModel::ejecutar_consulta_simple("SELECT producto_nombre FROM producto WHERE producto_codigo='$codigo' AND producto_nombre='$nombre'");
                if($check_nombre->rowCount()>0){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"Ya existe un producto registrado con el mismo nombre y código de barras",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
                $check_nombre->closeCursor();
			    $check_nombre=mainModel::desconectar($check_nombre);

                /*== Comprobando categoria ==*/
                $check_categoria=mainModel::ejecutar_consulta_simple("SELECT categoria_id FROM categoria WHERE categoria_id='$categoria' AND categoria_estado='Habilitada'");
                if($check_categoria->rowCount()<=0){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"La categoría seleccionada no se encuentra registrada en el sistema o no está disponible.",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
                $check_categoria->closeCursor();
                $check_categoria=mainModel::desconectar($check_categoria);
                
            }elseif($tipo=="Actualizar"){
                /*== Recuperando codigo & id del producto ==*/
                $id=mainModel::limpiar_cadena($_POST['producto_id_add']);
                $codigo=mainModel::limpiar_cadena($_POST['producto_codigo_add']);

                /*== Comprobando producto en la DB ==*/
                $check_producto=mainModel::ejecutar_consulta_simple("SELECT * FROM producto WHERE producto_id='$id' AND producto_codigo='$codigo'");
                if($check_producto->rowCount()<=0){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"No hemos encontrado el producto en el sistema.",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }else{
                    $campos=$check_producto->fetch();
                }
                $check_producto->closeCursor();
			    $check_producto=mainModel::desconectar($check_producto);

                $sku=$campos['producto_sku'];

                $nombre=$campos['producto_nombre'];
                $stock_minimo=$campos['producto_stock_minimo'];
                $stock_vendido=$campos['producto_stock_vendido'];
                $unidad=$campos['producto_tipo_unidad'];
                $descuento=$campos['producto_descuento'];
                $marca=$campos['producto_marca'];
                $modelo=$campos['producto_modelo'];
                
                $vencimiento=$campos['producto_vencimiento'];
                $fecha_vencimiento=$campos['producto_fecha_vencimiento'];
                
                $garantia_unidad=$campos['producto_garantia_unidad'];
			    $garantia_tiempo=$campos['producto_garantia_tiempo'];
    
                $proveedor=$campos['proveedor_id'];
                $categoria=$campos['categoria_id'];
                $estado=$campos['producto_estado'];

            }

            $stock_total=mainModel::limpiar_cadena($_POST['producto_stock_total_add']);
            $precio_compra=mainModel::limpiar_cadena($_POST['producto_precio_compra_add']);
            $precio_venta=mainModel::limpiar_cadena($_POST['producto_precio_venta_add']);
            $precio_venta_mayoreo=mainModel::limpiar_cadena($_POST['producto_precio_venta_mayoreo_add']);

            $detalle_cantidad=$stock_total;

            if($tipo=="Actualizar"){
                $stock_total=$campos['producto_stock_total'];
            }

            /*== comprobar campos vacios ==*/
            if($stock_total=="" || $precio_compra=="" || $precio_venta=="" || $precio_venta_mayoreo==""){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No has llenado todos los campos que son obligatorios",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            if(mainModel::verificar_datos("[0-9.]{1,25}",$precio_compra)){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"El precio de compra no coincide con el formato solicitado.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            if(mainModel::verificar_datos("[0-9.]{1,25}",$precio_venta)){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"El precio de venta no coincide con el formato solicitado.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            if(mainModel::verificar_datos("[0-9.]{1,25}",$precio_venta_mayoreo)){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"El precio de venta por mayoreo no coincide con el formato solicitado.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Verificando stock total o existencias ==*/
            if(mainModel::verificar_datos("[0-9]{1,20}",$stock_total)){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"El stock o existencias no coincide con el formato solicitado",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }
            
            if($detalle_cantidad<=0){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No puedes registrar o agregar un producto con stock o existencias en 0, debes de agregar al menos una unidad.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Comprobando precio de compra del producto ==*/
            $precio_compra=number_format($precio_compra,MONEDA_DECIMALES,'.','');
            if($precio_compra<=0){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"El precio de compra no puede ser menor o igual a 0.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Comprobando precio de venta del producto ==*/
            $precio_venta=number_format($precio_venta,MONEDA_DECIMALES,'.','');
            if($precio_venta<=0){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"El precio de venta no puede ser menor o igual a 0.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Comprobando precio de venta por mayoreo del producto ==*/
            $precio_venta_mayoreo=number_format($precio_venta_mayoreo,MONEDA_DECIMALES,'.','');
            if($precio_venta_mayoreo<=0){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El precio de venta por mayoreo no puede ser menor o igual a 0.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Comprobando precio de compra y venta del producto ==*/
            if($precio_compra>$precio_venta){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"El precio de compra del producto no puede ser mayor al precio de venta.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            if($precio_compra>$precio_venta_mayoreo){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El precio de compra del producto no puede ser mayor al precio de venta por mayoreo.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

            if($tipo=="Actualizar"){
                $precio_compra_old=$campos['producto_precio_compra'];
                $precio_venta_old=$campos['producto_precio_venta'];
                $precio_venta_mayoreo_old=$campos['producto_precio_mayoreo'];
            }else{
                $precio_compra_old=$precio_compra;
                $precio_venta_old=$precio_venta;
                $precio_venta_mayoreo_old=$precio_venta_mayoreo;
            }

            /*== Obteniendo datos de la empresa ==*/
            $check_empresa=mainModel::ejecutar_consulta_simple("SELECT * FROM empresa LIMIT 1");
            if($check_empresa->rowCount()<1){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido obtener algunos datos de los impuestos para agregar el producto.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }else{
                $datos_empresa=$check_empresa->fetch();
            }
            $check_empresa->closeCursor();
			$check_empresa=mainModel::desconectar($check_empresa);

            if($datos_empresa['empresa_impuesto_nombre']!=$_SESSION['compra_impuesto_nombre'] || $datos_empresa['empresa_impuesto_porcentaje']!=$_SESSION['compra_impuesto_porcentaje']){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"Hemos detectado un cambio en los datos de la empresa e impuestos, por favor recargue la página e intente nuevamente o verifique los datos de la empresa.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            $detalle_total=$detalle_cantidad*$precio_compra;
            $detalle_total=number_format($detalle_total,MONEDA_DECIMALES,'.','');

            $detalle_subtotal=$detalle_total/(($datos_empresa['empresa_impuesto_porcentaje']/100)+1);
            $detalle_subtotal=number_format($detalle_subtotal,MONEDA_DECIMALES,'.','');

            $detalle_impuestos=$detalle_total-$detalle_subtotal;
            $detalle_impuestos=number_format($detalle_impuestos,MONEDA_DECIMALES,'.','');

            if(empty($_SESSION['datos_producto'][$codigo])){

                $_SESSION['datos_producto'][$codigo]=[
                    "tipo_operacion"=>$tipo,
                    "producto_id"=>$id,
					"producto_codigo"=>$codigo,
					"producto_sku"=>$sku,
					"producto_nombre"=>$nombre,
					"producto_stock_total"=>$stock_total,
					"producto_stock_minimo"=>$stock_minimo,
					"producto_stock_vendido"=>$stock_vendido,
                    "producto_tipo_unidad"=>$unidad,
                    "producto_precio_compra"=>$precio_compra,
                    "producto_precio_compra_old"=>$precio_compra_old,
                    "producto_precio_venta"=>$precio_venta,
                    "producto_precio_venta_old"=>$precio_venta_old,
                    "producto_precio_mayoreo"=>$precio_venta_mayoreo,
                    "producto_precio_mayoreo_old"=>$precio_venta_mayoreo_old,
                    "producto_descuento"=>$descuento,
                    "producto_marca"=>$marca,
                    "producto_modelo"=>$modelo,
                    "producto_vencimiento"=>$vencimiento,
                    "producto_fecha_vencimiento"=>$fecha_vencimiento,
                    "producto_garantia_unidad"=>$garantia_unidad,
                    "producto_garantia_tiempo"=>$garantia_tiempo,
                    "producto_estado"=>$estado,
                    "categoria_id"=>$categoria,
                    "proveedor_id"=>$proveedor,
                    "compra_detalle_cantidad"=>$detalle_cantidad,
                    "compra_detalle_precio"=>$precio_compra,
                    "compra_detalle_subtotal"=>$detalle_subtotal,
                    "compra_detalle_impuestos"=>$detalle_impuestos,
                    "compra_detalle_total"=>$detalle_total
                ];
                
                $alerta=[
					"Alerta"=>"recargar",
					"Titulo"=>"¡Producto agregado!",
					"Texto"=>"El producto se agregó para realizar la compra.",
					"Tipo"=>"success"
				];
            }else{
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido agregar el producto a esta compra, por favor verifique que no esté agregado a la compra e intente nuevamente.",
					"Tipo"=>"error"
				];
            }
            echo json_encode($alerta);
        } /*-- Fin controlador --*/


        /*---------- Controlador eliminar producto de compra ----------*/
        public function eliminar_producto_carrito_controlador(){

            /*== Recuperando codigo del producto ==*/
            $codigo=mainModel::limpiar_cadena($_POST['producto_codigo_del']);
            
            unset($_SESSION['datos_producto'][$codigo]);

            if(empty($_SESSION['datos_producto'][$codigo])){
				$alerta=[
					"Alerta"=>"recargar",
					"Titulo"=>"¡Producto removido!",
					"Texto"=>"El producto se ha removido de la compra.",
					"Tipo"=>"success"
				];
			}else{
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido remover el producto, por favor intente nuevamente.",
					"Tipo"=>"error"
				];
            }
            echo json_encode($alerta);
        } /*-- Fin controlador --*/


        /*---------- Controlador agregar compra ----------*/
        public function agregar_compra_controlador(){

            $compra_fecha=mainModel::limpiar_cadena($_POST['compra_fecha_reg']);
            $compra_proveedor=mainModel::limpiar_cadena($_POST['compra_proveedor_reg']);

            /*== Comprobando datos de la compra ==*/
            if($compra_fecha=="" || $_SESSION['compra_total']<=0 || $_SESSION['compra_impuestos']<=0 || $_SESSION['compra_subtotal']<=0){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No ha llenado todos los campos obligatorios o no ha agregado productos.",
					"Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Comprobando productos en carrito ==*/
            if(!isset($_SESSION['datos_producto']) && count($_SESSION['datos_producto'])<=0){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hay productos agregados a esta compra.",
					"Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Comprobando fecha de la compra ==*/
            if(mainModel::verificar_fecha($compra_fecha)){
                $alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"La fecha no coincide con el formato solicitado (Año/Mes/Día).",
					"Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Comprobando proveedor ==*/
            $check_proveedor=mainModel::ejecutar_consulta_simple("SELECT proveedor_id FROM proveedor WHERE proveedor_id='$compra_proveedor' AND proveedor_estado='Habilitado'");
            if($check_proveedor->rowCount()<=0){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"El proveedor seleccionado no se encuentra registrado en el sistema o no está disponible.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }
            $check_proveedor->closeCursor();
			$check_proveedor=mainModel::desconectar($check_proveedor);


            /*== Registrando o actualizando productos ==*/
            $errores_productos=0;
			foreach($_SESSION['datos_producto'] as $productos){
                if($productos["tipo_operacion"]=="Actualizar"){

                    /*== Obteniendo datos del producto ==*/
                    $check_producto=mainModel::ejecutar_consulta_simple("SELECT * FROM producto WHERE producto_id='".$productos['producto_id']."' AND producto_codigo='".$productos['producto_codigo']."'");
                    if($check_producto->rowCount()<1){
                        $errores_productos=1;
                        break;
                    }else{
                        $datos_producto=$check_producto->fetch();
                    }
                    $check_producto->closeCursor();
			        $check_producto=mainModel::desconectar($check_producto);

                    /*== Respaldando datos de BD para poder restaurar en caso de errores ==*/
                    $_SESSION['datos_producto'][$productos['producto_codigo']]['producto_stock_total']=$datos_producto['producto_stock_total'];
                    $_SESSION['datos_producto'][$productos['producto_codigo']]['producto_precio_compra_old']=$datos_producto['producto_precio_compra'];
                    $_SESSION['datos_producto'][$productos['producto_codigo']]['producto_precio_venta_old']=$datos_producto['producto_precio_venta'];
                    $_SESSION['datos_producto'][$productos['producto_codigo']]['producto_precio_mayoreo_old']=$datos_producto['producto_precio_mayoreo'];

                    /*== Preparando datos para enviarlos al modelo ==*/
                    $producto_stock_total=$datos_producto['producto_stock_total']+$productos['compra_detalle_cantidad'];

                    $datos_producto_up=[
                        "producto_stock_total"=>[
                            "campo_marcador"=>":Stock",
                            "campo_valor"=>$producto_stock_total
                        ],
                        "producto_precio_compra"=>[
                            "campo_marcador"=>":PrecioC",
                            "campo_valor"=>$productos['producto_precio_compra']
                        ],
                        "producto_precio_venta"=>[
                            "campo_marcador"=>":PrecioV",
                            "campo_valor"=>$productos['producto_precio_venta']
                        ],
                        "producto_precio_mayoreo"=>[
                            "campo_marcador"=>":PrecioM",
                            "campo_valor"=>$productos['producto_precio_mayoreo']
                        ],
                        "proveedor_id"=>[
                            "campo_marcador"=>":Proveedor",
                            "campo_valor"=>$compra_proveedor
                        ]
                    ];

                    $condicion=[
                        "condicion_campo"=>"producto_id",
                        "condicion_marcador"=>":ID",
                        "condicion_valor"=>$productos['producto_id']
                    ];

                    /*== Actualizando producto ==*/
                    if(!mainModel::actualizar_datos("producto",$datos_producto_up,$condicion)){
                        $errores_productos=1;
                        break;
                    }
                }elseif($productos["tipo_operacion"]=="Registrar"){

                    /*== Preparando datos para enviarlos al modelo ==*/
                    $datos_producto_reg=[
                        "producto_codigo"=>[
                            "campo_marcador"=>":Codigo",
                            "campo_valor"=>$productos['producto_codigo']
                        ],
                        "producto_sku"=>[
                            "campo_marcador"=>":Sku",
                            "campo_valor"=>$productos['producto_sku']
                        ],
                        "producto_nombre"=>[
                            "campo_marcador"=>":Nombre",
                            "campo_valor"=>$productos['producto_nombre']
                        ],
                        "producto_stock_total"=>[
                            "campo_marcador"=>":StockT",
                            "campo_valor"=>$productos['producto_stock_total']
                        ],
                        "producto_stock_minimo"=>[
                            "campo_marcador"=>":StockM",
                            "campo_valor"=>$productos['producto_stock_minimo']
                        ],
                        "producto_stock_vendido"=>[
                            "campo_marcador"=>":Vendido",
                            "campo_valor"=>"0"
                        ],
                        "producto_tipo_unidad"=>[
                            "campo_marcador"=>":Unidad",
                            "campo_valor"=>$productos['producto_tipo_unidad']
                        ],
                        "producto_precio_compra"=>[
                            "campo_marcador"=>":Compra",
                            "campo_valor"=>$productos['producto_precio_compra']
                        ],
                        "producto_precio_venta"=>[
                            "campo_marcador"=>":Venta",
                            "campo_valor"=>$productos['producto_precio_venta']
                        ],
                        "producto_precio_mayoreo"=>[
                            "campo_marcador"=>":VentaMayoreo",
                            "campo_valor"=>$productos['producto_precio_mayoreo']
                        ],
                        "producto_descuento"=>[
                            "campo_marcador"=>":Descuento",
                            "campo_valor"=>$productos['producto_descuento']
                        ],
                        "producto_marca"=>[
                            "campo_marcador"=>":Marca",
                            "campo_valor"=>$productos['producto_marca']
                        ],
                        "producto_modelo"=>[
                            "campo_marcador"=>":Modelo",
                            "campo_valor"=>$productos['producto_modelo']
                        ],
                        "producto_vencimiento"=>[
                            "campo_marcador"=>":Vencimiento",
                            "campo_valor"=>$productos['producto_vencimiento']
                        ],
                        "producto_fecha_vencimiento"=>[
                            "campo_marcador"=>":VencimientoFecha",
                            "campo_valor"=>$productos['producto_fecha_vencimiento']
                        ],
                        "producto_garantia_unidad"=>[
                            "campo_marcador"=>":GarantiaUnidad",
                            "campo_valor"=>$productos['producto_garantia_unidad']
                        ],
                        "producto_garantia_tiempo"=>[
                            "campo_marcador"=>":GarantiaTiempo",
                            "campo_valor"=>$productos['producto_garantia_tiempo']
                        ],
                        "producto_estado"=>[
                            "campo_marcador"=>":Estado",
                            "campo_valor"=>$productos['producto_estado']
                        ],
                        "categoria_id"=>[
                            "campo_marcador"=>":Categoria",
                            "campo_valor"=>$productos['categoria_id']
                        ],
                        "proveedor_id"=>[
                            "campo_marcador"=>":Proveedor",
                            "campo_valor"=>$compra_proveedor
                        ]
                    ];

                    /*== Agregando producto ==*/
                    $agregar_producto=mainModel::guardar_datos("producto",$datos_producto_reg);
                    if($agregar_producto->rowCount()!=1){
                        $errores_productos=1;
                        break;
                    }
                    $agregar_producto->closeCursor();
			        $agregar_producto=mainModel::desconectar($agregar_producto);

                    /*== Obteniendo id del producto ==*/
                    $check_producto=mainModel::ejecutar_consulta_simple("SELECT producto_id FROM producto WHERE producto_codigo='".$productos['producto_codigo']."' AND producto_nombre='".$productos['producto_nombre']."'");
                    if($check_producto->rowCount()<1){
                        $errores_productos=1;
                        break;
                    }else{
                        $datos_producto=$check_producto->fetch();
                    }
                    $check_producto->closeCursor();
			        $check_producto=mainModel::desconectar($check_producto);

                    $_SESSION['datos_producto'][$productos['producto_codigo']]['producto_id']=$datos_producto['producto_id'];
                    $_SESSION['datos_producto'][$productos['producto_codigo']]['proveedor_id']=$compra_proveedor;

                }else{
                    $errores_productos=1;
                    break;
                }
            }

            /*== Reestableciendo DB debido a errores ==*/
            if($errores_productos==1){

                foreach($_SESSION['datos_producto'] as $producto){
                    if($producto["tipo_operacion"]=="Actualizar"){

                        $datos_producto_rs=[
                            "producto_stock_total"=>[
                                "campo_marcador"=>":Stock",
                                "campo_valor"=>$producto['producto_stock_total']
                            ],
                            "producto_precio_compra"=>[
                                "campo_marcador"=>":PrecioC",
                                "campo_valor"=>$producto['producto_precio_compra_old']
                            ],
                            "producto_precio_venta"=>[
                                "campo_marcador"=>":PrecioV",
                                "campo_valor"=>$producto['producto_precio_venta_old']
                            ],
                            "producto_precio_mayoreo"=>[
                                "campo_marcador"=>":PrecioM",
                                "campo_valor"=>$producto['producto_precio_mayoreo_old']
                            ],
                            "proveedor_id"=>[
                                "campo_marcador"=>":Proveedor",
                                "campo_valor"=>$producto['proveedor_id']
                            ]
                        ];
    
                        $condicion=[
                            "condicion_campo"=>"producto_id",
                            "condicion_marcador"=>":ID",
                            "condicion_valor"=>$producto['producto_id']
                        ];

                        mainModel::actualizar_datos("producto",$datos_producto_rs,$condicion);

                    }elseif($producto["tipo_operacion"]=="Registrar"){

                        $check_producto_del=mainModel::ejecutar_consulta_simple("SELECT producto_codigo FROM producto WHERE producto_codigo='".$producto['producto_codigo']."'");

                        if($check_producto_del->rowCount()==1){
                            mainModel::eliminar_registro("producto","producto_codigo",$producto["producto_codigo"]);
                        }
                        $check_producto_del->closeCursor();
			            $check_producto_del=mainModel::desconectar($check_producto_del);
                        
                    }
                }

                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No hemos podido agregar o actualizar los productos en el sistema.",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== generando codigo de compra ==*/
            $correlativo=mainModel::ejecutar_consulta_simple("SELECT compra_id FROM compra");
			$correlativo=($correlativo->rowCount())+1;
            $codigo_compra=mainModel::generar_codigo_aleatorio(10,$correlativo);

            /*== Preparando datos para enviarlos al modelo ==*/
			$datos_compra_reg=[
				"compra_codigo"=>[
					"campo_marcador"=>":Codigo",
					"campo_valor"=>$codigo_compra
				],
				"compra_fecha"=>[
					"campo_marcador"=>":Fecha",
					"campo_valor"=>$compra_fecha
				],
				"compra_impuesto_nombre"=>[
					"campo_marcador"=>":ImpuestoN",
					"campo_valor"=>$_SESSION['compra_impuesto_nombre']
				],
				"compra_impuesto_porcentaje"=>[
					"campo_marcador"=>":ImpuestoP",
					"campo_valor"=>$_SESSION['compra_impuesto_porcentaje']
				],
				"compra_subtotal"=>[
					"campo_marcador"=>":Subtotal",
					"campo_valor"=>$_SESSION['compra_subtotal']
				],
				"compra_impuestos"=>[
					"campo_marcador"=>":Impuestos",
					"campo_valor"=>$_SESSION['compra_impuestos']
				],
				"compra_descuento"=>[
					"campo_marcador"=>":Descuento",
					"campo_valor"=>"0"
				],
				"compra_total"=>[
					"campo_marcador"=>":Total",
					"campo_valor"=>$_SESSION['compra_total']
				],
				"usuario_id "=>[
					"campo_marcador"=>":Usuario",
					"campo_valor"=>$_SESSION['id_svi']
				],
				"proveedor_id"=>[
					"campo_marcador"=>":Proveedor",
					"campo_valor"=>$compra_proveedor
				]
            ];

            /*== Agregando compra ==*/
            $agregar_compra=mainModel::guardar_datos("compra",$datos_compra_reg);

            if($agregar_compra->rowCount()!=1){

                /*== Reestableciendo DB debido a errores ==*/
                foreach($_SESSION['datos_producto'] as $producto){
                    if($producto["tipo_operacion"]=="Actualizar"){

                        $datos_producto_rs=[
                            "producto_stock_total"=>[
                                "campo_marcador"=>":Stock",
                                "campo_valor"=>$producto['producto_stock_total']
                            ],
                            "producto_precio_compra"=>[
                                "campo_marcador"=>":PrecioC",
                                "campo_valor"=>$producto['producto_precio_compra_old']
                            ],
                            "producto_precio_venta"=>[
                                "campo_marcador"=>":PrecioV",
                                "campo_valor"=>$producto['producto_precio_venta_old']
                            ],
                            "producto_precio_mayoreo"=>[
                                "campo_marcador"=>":PrecioM",
                                "campo_valor"=>$producto['producto_precio_mayoreo_old']
                            ],
                            "proveedor_id"=>[
                                "campo_marcador"=>":Proveedor",
                                "campo_valor"=>$producto['proveedor_id']
                            ]
                        ];
    
                        $condicion=[
                            "condicion_campo"=>"producto_id",
                            "condicion_marcador"=>":ID",
                            "condicion_valor"=>$producto['producto_id']
                        ];

                        mainModel::actualizar_datos("producto",$datos_producto_rs,$condicion);

                    }elseif($producto["tipo_operacion"]=="Registrar"){

                        $check_producto_del=mainModel::ejecutar_consulta_simple("SELECT producto_codigo FROM producto WHERE producto_codigo='".$producto['producto_codigo']."'");

                        if($check_producto_del->rowCount()==1){
                            mainModel::eliminar_registro("producto","producto_codigo",$producto["producto_codigo"]);
                        }
                        $check_producto_del->closeCursor();
			            $check_producto_del=mainModel::desconectar($check_producto_del);

                    }
                }

				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido registrar la compra, por favor intente nuevamente.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }
            $agregar_compra->closeCursor();
			$agregar_compra=mainModel::desconectar($agregar_compra);

            /*== Agregando detalles de la compra ==*/
            $errores_compra_detalle=0;
            foreach($_SESSION['datos_producto'] as $compra_detalle){

                /*== Preparando datos para enviarlos al modelo ==*/
                $datos_compra_detalle_reg=[
                    "compra_detalle_cantidad"=>[
                        "campo_marcador"=>":Cantidad",
                        "campo_valor"=>$compra_detalle['compra_detalle_cantidad']
                    ],
                    "compra_detalle_precio"=>[
                        "campo_marcador"=>":Precio",
                        "campo_valor"=>$compra_detalle['compra_detalle_precio']
                    ],
                    "compra_detalle_subtotal"=>[
                        "campo_marcador"=>":Subtotal",
                        "campo_valor"=>$compra_detalle['compra_detalle_subtotal']
                    ],
                    "compra_detalle_impuestos"=>[
                        "campo_marcador"=>":Impuestos",
                        "campo_valor"=>$compra_detalle['compra_detalle_impuestos']
                    ],
                    "compra_detalle_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$compra_detalle['compra_detalle_total']
                    ],
                    "compra_detalle_descripcion"=>[
                        "campo_marcador"=>":Descripcion",
                        "campo_valor"=>$compra_detalle['producto_nombre']
                    ],
                    "compra_codigo"=>[
                        "campo_marcador"=>":Codigo",
                        "campo_valor"=>$codigo_compra
                    ],
                    "producto_id"=>[
                        "campo_marcador"=>":Producto",
                        "campo_valor"=>$compra_detalle['producto_id']
                    ]
                ];

                $agregar_detalle_compra=mainModel::guardar_datos("compra_detalle",$datos_compra_detalle_reg);

                if($agregar_detalle_compra->rowCount()!=1){
                    $errores_compra_detalle=1;
                    break;
                }
                $agregar_detalle_compra->closeCursor();
			    $agregar_detalle_compra=mainModel::desconectar($agregar_detalle_compra);
            }

            /*== Reestableciendo DB debido a errores ==*/
            if($errores_compra_detalle==1){

                mainModel::eliminar_registro("compra_detalle","compra_codigo",$codigo_compra);
                mainModel::eliminar_registro("compra","compra_codigo",$codigo_compra);

                foreach($_SESSION['datos_producto'] as $producto){
                    if($producto["tipo_operacion"]=="Actualizar"){

                        $datos_producto_rs=[
                            "producto_stock_total"=>[
                                "campo_marcador"=>":Stock",
                                "campo_valor"=>$producto['producto_stock_total']
                            ],
                            "producto_precio_compra"=>[
                                "campo_marcador"=>":PrecioC",
                                "campo_valor"=>$producto['producto_precio_compra_old']
                            ],
                            "producto_precio_venta"=>[
                                "campo_marcador"=>":PrecioV",
                                "campo_valor"=>$producto['producto_precio_venta_old']
                            ],
                            "producto_precio_mayoreo"=>[
                                "campo_marcador"=>":PrecioM",
                                "campo_valor"=>$producto['producto_precio_mayoreo_old']
                            ],
                            "proveedor_id"=>[
                                "campo_marcador"=>":Proveedor",
                                "campo_valor"=>$producto['proveedor_id']
                            ]
                        ];
    
                        $condicion=[
                            "condicion_campo"=>"producto_id",
                            "condicion_marcador"=>":ID",
                            "condicion_valor"=>$producto['producto_id']
                        ];

                        mainModel::actualizar_datos("producto",$datos_producto_rs,$condicion);

                    }elseif($producto["tipo_operacion"]=="Registrar"){

                        $check_producto_del=mainModel::ejecutar_consulta_simple("SELECT producto_codigo FROM producto WHERE producto_codigo='".$producto['producto_codigo']."'");

                        if($check_producto_del->rowCount()==1){
                            mainModel::eliminar_registro("producto","producto_codigo",$producto["producto_codigo"]);
                        }
                        $check_producto_del->closeCursor();
			            $check_producto_del=mainModel::desconectar($check_producto_del);

                    }
                }

				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido registrar algunos detalles de la compra, por favor intente nuevamente.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Agregando kardex y detalle kardex ==*/
            $year=date("Y");
            $mes=date("m");
            $fecha=date("Y-m-d");
            $errores_kardex=0;
            $array_kardex=[];
            foreach($_SESSION['datos_producto'] as $kardex){
                /*== Obteniendo datos de kardex ==*/
                $check_kardex=mainModel::ejecutar_consulta_simple("SELECT * FROM kardex WHERE kardex_mes='$mes' AND kardex_year='$year' AND producto_id='".$kardex['producto_id']."'");
                if($check_kardex->rowCount()<1){
                    $operacion="Guardar";

                    $correlativo=mainModel::ejecutar_consulta_simple("SELECT kardex_id FROM kardex");
                    $correlativo=($correlativo->rowCount())+1;

                    $kardex_codigo=mainModel::generar_codigo_aleatorio(10,$correlativo);
                    $kardex_mes=$mes;
                    $kardex_year=$year;

                    $array_kardex[$kardex_codigo]=[
                        "tipo_operacion"=>$operacion,
                        "kardex_codigo"=>$kardex_codigo,
                        "total_unidades"=>$kardex['compra_detalle_cantidad'],
                        "kardex_entrada_unidad"=>$kardex['compra_detalle_cantidad'],
                        "kardex_entrada_costo_total"=>$kardex['compra_detalle_total'],
                        "kardex_existencia_unidad"=>$kardex['compra_detalle_cantidad'],
                        "kardex_existencia_costo_total"=>$kardex['compra_detalle_total']
                    ];

                    $kardex_entrada_unidad=$kardex['compra_detalle_cantidad'];
                    $kardex_entrada_costo_total=$kardex['compra_detalle_total'];
                    $kardex_entrada_costo_total=number_format($kardex_entrada_costo_total,MONEDA_DECIMALES,'.','');

                    $kardex_salida_unidad=0;
                    $kardex_salida_costo_total=0.00;

                    $check_kardex_anterior=mainModel::ejecutar_consulta_simple("SELECT * FROM kardex WHERE producto_id='".$kardex['producto_id']."' ORDER BY kardex_id DESC LIMIT 1");
                    if($check_kardex_anterior->rowCount()==1){
                        $datos_ka=$check_kardex_anterior->fetch();
                        $kardex_existencia_costo_total_old=$datos_ka['kardex_existencia_costo_total'];
                    }else{
                        $kardex_existencia_costo_total_old=$kardex['producto_stock_total']*$kardex['producto_precio_compra_old'];
                    }
                    $check_kardex_anterior->closeCursor();
                    $check_kardex_anterior=mainModel::desconectar($check_kardex_anterior);


                    $kardex_existencia_inicial=$kardex['producto_stock_total'];
                    $kardex_existencia_unidad=$kardex['producto_stock_total']+$kardex['compra_detalle_cantidad'];
                    $kardex_existencia_costo_total=$kardex_existencia_costo_total_old+$kardex['compra_detalle_total'];  
                }else{
                    $operacion="Actualizar";

                    $datos_kardex=$check_kardex->fetch();
				
                    $kardex_codigo=$datos_kardex['kardex_codigo'];
                    $kardex_mes=$datos_kardex['kardex_mes'];
                    $kardex_year=$datos_kardex['kardex_year'];

                    $array_kardex[$kardex_codigo]=[
                        "tipo_operacion"=>$operacion,
                        "kardex_codigo"=>$kardex_codigo,
                        "total_unidades"=>$kardex['compra_detalle_cantidad'],
                        "kardex_entrada_unidad"=>$datos_kardex['kardex_entrada_unidad'],
                        "kardex_entrada_costo_total"=>$datos_kardex['kardex_entrada_costo_total'],
                        "kardex_existencia_unidad"=>$datos_kardex['kardex_existencia_unidad'],
                        "kardex_existencia_costo_total"=>$datos_kardex['kardex_existencia_costo_total']
                    ];

                    $kardex_entrada_unidad=$datos_kardex['kardex_entrada_unidad']+$kardex['compra_detalle_cantidad'];
                    $kardex_entrada_costo_total=$datos_kardex['kardex_entrada_costo_total']+$kardex['compra_detalle_total'];
                    $kardex_entrada_costo_total=number_format($kardex_entrada_costo_total,MONEDA_DECIMALES,'.','');

                    $kardex_salida_unidad=$datos_kardex['kardex_salida_unidad'];
                    $kardex_salida_costo_total=$datos_kardex['kardex_salida_costo_total'];

                    $kardex_existencia_inicial=$datos_kardex['kardex_existencia_inicial'];
                    $kardex_existencia_unidad=$datos_kardex['kardex_existencia_unidad']+$kardex['compra_detalle_cantidad'];
                    $kardex_existencia_costo_total=$datos_kardex['kardex_existencia_costo_total']+$kardex['compra_detalle_total'];
                    $kardex_existencia_costo_total=number_format($kardex_existencia_costo_total,MONEDA_DECIMALES,'.','');  
                }
                $check_kardex->closeCursor();
			    $check_kardex=mainModel::desconectar($check_kardex);

                /*== Preparando datos para enviarlos al modelo ==*/
                $datos_kardex_reg=[
                    "kardex_codigo"=>[
                        "campo_marcador"=>":Codigo",
                        "campo_valor"=>$kardex_codigo
                    ],
                    "kardex_mes"=>[
                        "campo_marcador"=>":Mes",
                        "campo_valor"=>$kardex_mes
                    ],
                    "kardex_year"=>[
                        "campo_marcador"=>":Year",
                        "campo_valor"=>$kardex_year
                    ],
                    "kardex_entrada_unidad"=>[
                        "campo_marcador"=>":EntradaU",
                        "campo_valor"=>$kardex_entrada_unidad
                    ],
                    "kardex_entrada_costo_total"=>[
                        "campo_marcador"=>":EntradaCT",
                        "campo_valor"=>$kardex_entrada_costo_total
                    ],
                    "kardex_salida_unidad"=>[
                        "campo_marcador"=>":SalidaU",
                        "campo_valor"=>$kardex_salida_unidad
                    ],
                    "kardex_salida_costo_total"=>[
                        "campo_marcador"=>":SalidaCT",
                        "campo_valor"=>$kardex_salida_costo_total
                    ],
                    "kardex_existencia_inicial"=>[
                        "campo_marcador"=>":ExistenciaI",
                        "campo_valor"=>$kardex_existencia_inicial
                    ],
                    "kardex_existencia_unidad"=>[
                        "campo_marcador"=>":ExistenciaU",
                        "campo_valor"=>$kardex_existencia_unidad
                    ],
                    "kardex_existencia_costo_total"=>[
                        "campo_marcador"=>":ExistenciaCT",
                        "campo_valor"=>$kardex_existencia_costo_total
                    ],
                    "producto_id"=>[
                        "campo_marcador"=>":Producto",
                        "campo_valor"=>$kardex['producto_id']
                    ]
                ];
                
                /*== Detectando la operacion a realizar ==*/
                if($operacion=="Guardar"){
                    $agregar_kardex=mainModel::guardar_datos("kardex",$datos_kardex_reg);
                    if($agregar_kardex->rowCount()!=1){
                        $errores_kardex=1;
                        break;
                    }
                    $agregar_kardex->closeCursor();
			        $agregar_kardex=mainModel::desconectar($agregar_kardex);
                }else{
                    $condicion=[
                        "condicion_campo"=>"kardex_id",
                        "condicion_marcador"=>":ID",
                        "condicion_valor"=>$datos_kardex['kardex_id']
                    ];

                    if(!mainModel::actualizar_datos("kardex",$datos_kardex_reg,$condicion)){
                        $errores_kardex=1;
                        break;
                    }
                }

                /*== Preparando datos para enviarlos al modelo ==*/
                $datos_kardex_detalle=[
                    "kardex_detalle_fecha"=>[
                        "campo_marcador"=>":Fecha",
                        "campo_valor"=>$fecha
                    ],
                    "kardex_detalle_tipo"=>[
                        "campo_marcador"=>":Tipo",
                        "campo_valor"=>"Entrada"
                    ],
                    "kardex_detalle_descripcion"=>[
                        "campo_marcador"=>":Descripcion",
                        "campo_valor"=>"Compra de producto (Mediante compra)"
                    ],
                    "kardex_detalle_unidad"=>[
                        "campo_marcador"=>":Unidad",
                        "campo_valor"=>$kardex['compra_detalle_cantidad']
                    ],
                    "kardex_detalle_costo_unidad"=>[
                        "campo_marcador"=>":Costo",
                        "campo_valor"=>$kardex['producto_precio_compra']
                    ],
                    "kardex_detalle_costo_total"=>[
                        "campo_marcador"=>":Total",
                        "campo_valor"=>$kardex['compra_detalle_total']
                    ],
                    "kardex_codigo"=>[
                        "campo_marcador"=>":Codigo",
                        "campo_valor"=>$kardex_codigo
                    ],
                    "producto_id"=>[
                        "campo_marcador"=>":Producto",
                        "campo_valor"=>$kardex['producto_id']
                    ],
                    "usuario_id"=>[
                        "campo_marcador"=>":Usuario",
                        "campo_valor"=>$_SESSION['id_svi']
                    ]
                ];

                $agregar_kardex_detalle=mainModel::guardar_datos("kardex_detalle",$datos_kardex_detalle);
                if($agregar_kardex_detalle->rowCount()!=1){
                    $errores_kardex=1;
                    break;
                }
                $agregar_kardex_detalle->closeCursor();
			    $agregar_kardex_detalle=mainModel::desconectar($agregar_kardex_detalle);
            }

            /*== Reestableciendo DB debido a errores ==*/
            if($errores_kardex==1){

                mainModel::eliminar_registro("compra_detalle","compra_codigo",$codigo_compra);
                mainModel::eliminar_registro("compra","compra_codigo",$codigo_compra);

                foreach($array_kardex as $kardexE){
                    $check_detalle_kardex=mainModel::ejecutar_consulta_simple("SELECT kardex_detalle_id FROM kardex_detalle WHERE kardex_detalle_fecha='$fecha' AND kardex_codigo='".$kardexE['kardex_codigo']."' AND kardex_detalle_unidad='".$kardexE['total_unidades']."'");

                    if($check_detalle_kardex->rowCount()==1){
                        mainModel::eliminar_registro("kardex_detalle","kardex_codigo",$kardexE['kardex_codigo']);
                    }
                    $check_detalle_kardex->closeCursor();
			        $check_detalle_kardex=mainModel::desconectar($check_detalle_kardex);

                    if($kardexE['tipo_operacion']=="Actualizar"){
                        $datos_kardex_rs=[
                            "kardex_entrada_unidad"=>[
                                "campo_marcador"=>":KardexEnU",
                                "campo_valor"=>$kardexE['kardex_entrada_unidad']
                            ],
                            "kardex_entrada_costo_total"=>[
                                "campo_marcador"=>":KardexEnCT",
                                "campo_valor"=>$kardexE['kardex_entrada_costo_total']
                            ],
                            "kardex_existencia_unidad"=>[
                                "campo_marcador"=>":KardexExU",
                                "campo_valor"=>$kardexE['kardex_existencia_unidad']
                            ],
                            "kardex_existencia_costo_total"=>[
                                "campo_marcador"=>":KardexExCT",
                                "campo_valor"=>$kardexE['kardex_existencia_costo_total']
                            ]
                        ];
    
                        $condicion=[
                            "condicion_campo"=>"kardex_codigo",
                            "condicion_marcador"=>":Codigo",
                            "condicion_valor"=>$kardexE['kardex_codigo']
                        ];

                        mainModel::actualizar_datos("kardex",$datos_kardex_rs,$condicion);

                    }elseif($kardexE['tipo_operacion']=="Guardar"){

                        $check_kardex_del=mainModel::ejecutar_consulta_simple("SELECT kardex_id FROM kardex WHERE kardex_codigo='".$kardexE['kardex_codigo']."'");

                        if($check_kardex_del->rowCount()==1){
                            mainModel::eliminar_registro("kardex","kardex_codigo",$kardexE['kardex_codigo']);
                        }
                        $check_kardex_del->closeCursor();
			            $check_kardex_del=mainModel::desconectar($check_kardex_del);

                    }
                }

                foreach($_SESSION['datos_producto'] as $producto){
                    if($producto["tipo_operacion"]=="Actualizar"){

                        $datos_producto_rs=[
                            "producto_stock_total"=>[
                                "campo_marcador"=>":Stock",
                                "campo_valor"=>$producto['producto_stock_total']
                            ],
                            "producto_precio_compra"=>[
                                "campo_marcador"=>":PrecioC",
                                "campo_valor"=>$producto['producto_precio_compra_old']
                            ],
                            "producto_precio_venta"=>[
                                "campo_marcador"=>":PrecioV",
                                "campo_valor"=>$producto['producto_precio_venta_old']
                            ],
                            "producto_precio_mayoreo"=>[
                                "campo_marcador"=>":PrecioM",
                                "campo_valor"=>$producto['producto_precio_mayoreo_old']
                            ],
                            "proveedor_id"=>[
                                "campo_marcador"=>":Proveedor",
                                "campo_valor"=>$producto['proveedor_id']
                            ]
                        ];
    
                        $condicion=[
                            "condicion_campo"=>"producto_id",
                            "condicion_marcador"=>":ID",
                            "condicion_valor"=>$producto['producto_id']
                        ];

                        mainModel::actualizar_datos("producto",$datos_producto_rs,$condicion);

                    }elseif($producto["tipo_operacion"]=="Registrar"){

                        $check_producto_del=mainModel::ejecutar_consulta_simple("SELECT producto_codigo FROM producto WHERE producto_codigo='".$producto['producto_codigo']."'");

                        if($check_producto_del->rowCount()==1){
                            mainModel::eliminar_registro("producto","producto_codigo",$producto["producto_codigo"]);
                        }
                        $check_producto_del->closeCursor();
			            $check_producto_del=mainModel::desconectar($check_producto_del);

                    }
                }

				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido registrar algunos detalles de la compra, por favor intente nuevamente.",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
            }

            /*== Vaciando variables de sesion ==*/
            unset($_SESSION['compra_total']);
            unset($_SESSION['compra_impuestos']);
            unset($_SESSION['compra_subtotal']);
            unset($_SESSION['datos_producto']);

            $alerta=[
                "Alerta"=>"recargar",
                "Titulo"=>"¡Compra registrada!",
                "Texto"=>"La compra se registró con éxito en el sistema",
                "Tipo"=>"success"
            ];
            echo json_encode($alerta);
        } /*-- Fin controlador --*/


        /*---------- Controlador paginador compras ----------*/
		public function paginador_compra_controlador($pagina,$registros,$url,$tipo,$fecha_inicio,$fecha_final){

			$pagina=mainModel::limpiar_cadena($pagina);
			$registros=mainModel::limpiar_cadena($registros);

			$url=mainModel::limpiar_cadena($url);
			$url=SERVERURL.$url."/";

			$tipo=mainModel::limpiar_cadena($tipo);
			$fecha_inicio=mainModel::limpiar_cadena($fecha_inicio);
			$fecha_final=mainModel::limpiar_cadena($fecha_final);
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
            $inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;
            
            if($tipo=="Busqueda"){
				if(mainModel::verificar_fecha($fecha_inicio) || mainModel::verificar_fecha($fecha_final)){
					return '
						<div class="alert alert-danger text-center" role="alert">
							<p><i class="fas fa-exclamation-triangle fa-5x"></i></p>
							<h4 class="alert-heading">¡Ocurrió un error inesperado!</h4>
							<p class="mb-0">Lo sentimos, no podemos realizar la búsqueda ya que al parecer a ingresado una fecha incorrecta.</p>
						</div>
					';
					exit();
				}
            }
            
            $campos_tablas="compra.compra_id,compra.compra_codigo,compra.compra_fecha,compra.compra_total,compra.usuario_id,compra.proveedor_id,usuario.usuario_id,usuario.usuario_nombre,usuario.usuario_apellido,proveedor.proveedor_id,proveedor.proveedor_nombre";

			if($tipo=="Busqueda" && $fecha_inicio!="" && $fecha_final!=""){
				$consulta="SELECT SQL_CALC_FOUND_ROWS $campos_tablas FROM compra INNER JOIN proveedor ON compra.proveedor_id=proveedor.proveedor_id INNER JOIN usuario ON compra.usuario_id=usuario.usuario_id WHERE (compra.compra_fecha BETWEEN '$fecha_inicio' AND '$fecha_final') ORDER BY compra.compra_id DESC LIMIT $inicio,$registros";
			}else{
				$consulta="SELECT SQL_CALC_FOUND_ROWS $campos_tablas FROM compra INNER JOIN proveedor ON compra.proveedor_id=proveedor.proveedor_id INNER JOIN usuario ON compra.usuario_id=usuario.usuario_id ORDER BY compra.compra_id DESC LIMIT $inicio,$registros";
			}

			$conexion = mainModel::conectar();

			$datos = $conexion->query($consulta);

			$datos = $datos->fetchAll();

			$total = $conexion->query("SELECT FOUND_ROWS()");
			$total = (int) $total->fetchColumn();

			$Npaginas =ceil($total/$registros);

			### Cuerpo de la tabla ###
			$tabla.='
				<div class="table-responsive">
				<table class="table table-dark table-sm">
					<thead>
						<tr class="text-center roboto-medium">
                            <th>#</th>
                            <th>FECHA</th>
							<th>TOTAL</th>
							<th>PROVEEDOR</th>
							<th>USUARIO</th>
							<th>DETALLES</th>
                        </tr>
					</thead>
					<tbody>
			';

			if($total>=1 && $pagina<=$Npaginas){
				$contador=$inicio+1;
				$pag_inicio=$inicio+1;
				foreach($datos as $rows){
					$tabla.='
						<tr class="text-center" >
                            <td>'.$contador.'</td>
                            <td>'.date("d-m-Y", strtotime($rows['compra_fecha'])).'</td>
							<td>'.MONEDA_SIMBOLO.number_format($rows['compra_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
							<td>'.$rows['proveedor_nombre'].'</td>
                            <td>'.$rows['usuario_nombre'].' '.$rows['usuario_apellido'].'</td>
                            <td>
                                <a class="btn btn-info" href="'.SERVERURL.'shop-detail/'.$rows['compra_codigo'].'/" >
                                    <i class="fas fa-shopping-bag fa-fw"></i>
                                </a>
                            </td>
                        </tr>
                    ';
                    $contador++;
				}
				$pag_final=$contador-1;
			}else{
				if($total>=1){
					$tabla.='
						<tr class="text-center" >
							<td colspan="7">
								<a href="'.$url.'" class="btn btn-raised btn-primary btn-sm">
									Haga clic acá para recargar el listado
								</a>
							</td>
						</tr>
					';
				}else{
					$tabla.='
						<tr class="text-center" >
							<td colspan="7">
								No hay registros en el sistema
							</td>
						</tr>
					';
				}
			}

			$tabla.='</tbody></table></div>';

			if($total>0 && $pagina<=$Npaginas){
				$tabla.='<p class="text-right">Mostrando compras <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';
			}

			### Paginacion ###
			if($total>=1 && $pagina<=$Npaginas){
				$tabla.=mainModel::paginador_tablas($pagina,$Npaginas,$url,7);
			}

			return $tabla;
		} /*-- Fin controlador --*/
    }