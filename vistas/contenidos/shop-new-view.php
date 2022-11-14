<?php
    include "./vistas/inc/admin_security.php";
?>
<div class="full-box page-header">
    <h3 class="text-left text-uppercase">
        <i class="fas fa-shopping-bag fa-fw"></i> &nbsp; Nueva compra
    </h3>
    <?php include "./vistas/desc/desc_compra.php"; ?>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs text-uppercase">
        <li>
			<a class="active" href="<?php echo SERVERURL; ?>shop-new/">
				<i class="fas fa-shopping-bag fa-fw"></i> &nbsp; Nueva compra
			</a>
		</li>
		<li>
			<a href="<?php echo SERVERURL; ?>shop-list/">
				<i class="fas fa-file-invoice-dollar fa-fw"></i> &nbsp; Compras realizadas
			</a>
		</li>
		<li>
			<a href="<?php echo SERVERURL; ?>shop-search/">
				<i class="fas fa-search-dollar fa-fw"></i> &nbsp; Buscar compra
			</a>
		</li>
    </ul>	
</div>

<div class="container-fluid">
    <?php
        $check_empresa=$lc->datos_tabla("Normal","empresa LIMIT 1","*",0);

        if($check_empresa->rowCount()==1){
            $datos_empresa=$check_empresa->fetch();
    ?>
    <div class="form-neon">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-9">
                    <p class="text-center">
                        Ingrese el código de barras del producto y luego haga clic en <strong>“Verificar producto”</strong> para cargar los datos en caso el producto ya este registrado, en caso contrario se cargará el formulario para registrar un nuevo producto.
                    </p>
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-8">
                                <div class="form-group">
                                    <label for="barcode-input" class="bmd-label-floating">Código de barras</label>
                                    <input type="text" pattern="[a-zA-Z0-9- ]{1,70}" class="form-control input-barcode" id="barcode-input" maxlength="70" >
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <button type="button" class="btn btn-primary" onclick="buscar_producto()" ><i class="far fa-check-circle"></i> &nbsp; Verificar producto</button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="bg-info">
                                <tr class="text-center">
                                    <th scope="col">#</th>
                                    <th scope="col">Código de barra</th>
                                    <th scope="col">Producto</th>
                                    <th scope="col">Cantidad</th>
                                    <th scope="col">Precio</th>
                                    <th scope="col">Subtotal</th>
                                    <th scope="col">Remover</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $_SESSION['compra_impuesto_nombre']=$datos_empresa['empresa_impuesto_nombre'];
                                $_SESSION['compra_impuesto_porcentaje']=$datos_empresa['empresa_impuesto_porcentaje'];
                                if(isset($_SESSION['datos_producto']) && count($_SESSION['datos_producto'])>=1){
                                    $_SESSION['compra_total']=0;
                                    $_SESSION['compra_impuestos']=0;
                                    $_SESSION['compra_subtotal']=0;
                                    $cc=1;
                                    foreach($_SESSION['datos_producto'] as $productos){ 
                                ?>
                                <tr class="text-center">
                                    <th scope="row"><?php echo $cc; ?></th>
                                    <td><?php echo $productos['producto_codigo']; ?></td>
                                    <td><?php echo $productos['producto_nombre']; ?></td>
                                    <td><?php echo $productos['compra_detalle_cantidad']; ?></td>
                                    <td><?php echo MONEDA_SIMBOLO.number_format($productos['compra_detalle_precio'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR)." ".MONEDA_NOMBRE; ?></td>
                                    <td><?php echo MONEDA_SIMBOLO.number_format($productos['compra_detalle_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR)." ".MONEDA_NOMBRE; ?></td>
                                    <td>
                                        <form class="FormularioAjax" action="<?php echo SERVERURL; ?>ajax/compraAjax.php" method="POST" data-form="shop" autocomplete="off">
                                            <input type="hidden" name="producto_codigo_del" value="<?php echo $productos['producto_codigo']; ?>">
                                            <input type="hidden" name="modulo_compra" value="quitar_producto">
                                            <button type="submit" class="btn btn-warning" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Remover producto" >
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
                                        $cc++;
                                        $_SESSION['compra_total']+=$productos['compra_detalle_total'];
                                        $_SESSION['compra_impuestos']+=$productos['compra_detalle_impuestos'];
                                        $_SESSION['compra_subtotal']+=$productos['compra_detalle_subtotal'];
                                    }
                                ?>
                                <tr class="text-center font-weight-bold">
                                    <td colspan="4"></td>
                                    <td>TOTAL</td>
                                    <td><?php echo MONEDA_SIMBOLO.number_format($_SESSION['compra_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR)." ".MONEDA_NOMBRE; ?></td>
                                    <td></td>
                                </tr>
                                <?php
                                }else{
                                    $_SESSION['compra_total']=0;
                                    $_SESSION['compra_impuestos']=0;
                                    $_SESSION['compra_subtotal']=0; 
                                ?>
                                <tr class="text-center">
                                    <th colspan="8">No hay productos agregados</th>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12 col-lg-3">
                    <h3 class="text-center text-uppercase">Datos de la compra</h3>
                    <hr>
                    <?php if($_SESSION['compra_total']>0){ ?>
                    <form class="FormularioAjax" action="<?php echo SERVERURL; ?>ajax/compraAjax.php" method="POST" data-form="save" autocomplete="off">
                        <input type="hidden" name="modulo_compra" value="agregar_compra">
                    <?php }else { echo "<form>"; } ?>
                        <div class="form-group">
                            <label for="compra_fecha">Fecha <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <input type="date" class="form-control input-barcode" name="compra_fecha_reg" id="compra_fecha" value="<?php echo date("Y-m-d"); ?>" >
                        </div>
                        <div class="form-group">
                            <label for="compra_proveedor" class="bmd-label-floating">Proveedor <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <select class="form-control" name="compra_proveedor_reg" id="compra_proveedor">
                                <option value="" selected="" >Seleccione una opción</option>
                                <?php
                                    $datos_proveedor=$lc->datos_tabla("Normal","proveedor","proveedor_id,proveedor_nombre,proveedor_estado",0);
                                    $cp=1;
                                    while($campos_proveedor=$datos_proveedor->fetch()){
                                        if($campos_proveedor['proveedor_estado']=="Habilitado"){
                                            echo '<option value="'.$campos_proveedor['proveedor_id'].'">'.$cp.' - '.$campos_proveedor['proveedor_nombre'].'</option>';
                                            $cp++;
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <?php
                            $_SESSION['compra_subtotal']=number_format($_SESSION['compra_subtotal'],MONEDA_DECIMALES,'.','');

                            $_SESSION['compra_impuestos']=number_format($_SESSION['compra_impuestos'],MONEDA_DECIMALES,'.','');

                            $_SESSION['compra_total']=number_format($_SESSION['compra_total'],MONEDA_DECIMALES,'.','');
                        ?>
                        <ul class="list-group list-unstyled">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Subtotal
                                <span class="badge badge-pill"><?php echo MONEDA_SIMBOLO.number_format($_SESSION['compra_subtotal'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR)." ".MONEDA_NOMBRE; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $_SESSION['compra_impuesto_nombre']." (".$_SESSION['compra_impuesto_porcentaje']; ?>%)
                                <span class="badge badge-pill"><?php echo MONEDA_SIMBOLO.number_format($_SESSION['compra_impuestos'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR)." ".MONEDA_NOMBRE; ?></span>
                            </li>
                            <li><hr></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total
                                <span class="badge badge-pill"><?php echo MONEDA_SIMBOLO.number_format($_SESSION['compra_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR)." ".MONEDA_NOMBRE; ?></span>
                            </li>
                        </ul>
                        <?php if($_SESSION['compra_total']>0){ ?>
                        <p class="text-center" style="margin-top: 40px;">
                            <button type="submit" class="btn btn-raised btn-info btn-sm"><i class="far fa-save"></i> &nbsp; GUARDAR COMPRA</button>
                        </p>
                        <?php } ?>
                        <p class="text-center">
                            <small>Los campos marcados con <?php echo CAMPO_OBLIGATORIO; ?> son obligatorios</small>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
        }else{
            echo '
                <div class="alert alert-danger text-center" role="alert">
                    <p><i class="fas fa-exclamation-triangle fa-5x"></i></p>
                    <h4 class="alert-heading">¡Ocurrió un error inesperado!</h4>
                    <p class="mb-0">No hemos podio seleccionar algunos datos sobre los impuestos, por favor <a href="'.SERVERURL.'company/" >verifique aquí los datos de la empresa</a></p>
                </div>
            ';
        }
    ?>
</div>

<!-- Modal agregar producto a compras -->
<div class="modal fade" id="add-product-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form class="modal-content FormularioAjax" action="<?php echo SERVERURL; ?>ajax/compraAjax.php" method="POST" data-form="default" autocomplete="off" >
            <input type="hidden" name="modulo_compra" value="agregar_producto">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Agregar producto a compra</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="formulario-producto-modal"></div>
            <p class="text-center">
                <small>Los campos marcados con <?php echo CAMPO_OBLIGATORIO; ?> son obligatorios</small>
            </p>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> &nbsp; Agregar producto
                </button>
             </div>
        </form>
    </div>
</div>

<script>
    /*----------  Buscar producto  ----------*/
    function buscar_producto(){
        let codigo_producto=document.querySelector('#barcode-input').value;
        
        codigo_producto=codigo_producto.trim();

        if(codigo_producto!=""){

            let datos = new FormData();
            datos.append("codigo_producto", codigo_producto);
            datos.append("modulo_compra", "verificar_producto");

            fetch('<?php echo SERVERURL; ?>ajax/compraAjax.php',{
                method: 'POST',
                body: datos
            })
            .then(respuesta => respuesta.text())
            .then(respuesta =>{
                let div_modal_productos=document.querySelector('#formulario-producto-modal');
                div_modal_productos.innerHTML=respuesta;
                $('#add-product-modal').modal({
                    'show': true,
                    'backdrop': 'static'
                });
            });

        }else{
            Swal.fire({
                title: 'Ocurrió un error inesperado',
                text: 'Debes de introducir el código de barras del producto',
                type: 'error',
                confirmButtonText: 'Aceptar'
            });
        }
    }
</script>