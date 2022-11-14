<?php
    include "./vistas/inc/admin_security.php";
?>
<div class="full-box page-header">
    <h3 class="text-left text-uppercase">
        <i class="fas fa-shopping-bag fa-fw"></i> &nbsp; Detalles de compra
    </h3>
    <?php include "./vistas/desc/desc_compra.php"; ?>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs text-uppercase">
        <li>
			<a href="<?php echo SERVERURL; ?>shop-new/">
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

<div class="container-fluid form-neon">
    <?php
        include "./vistas/inc/btn_go_back.php";

        $datos_compra=$lc->datos_tabla("Normal","compra INNER JOIN usuario ON compra.usuario_id=usuario.usuario_id INNER JOIN proveedor ON compra.proveedor_id=proveedor.proveedor_id WHERE (compra.compra_codigo='".$pagina[1]."')","*",0);

        if($datos_compra->rowCount()==1){
            $datos_compra=$datos_compra->fetch();
    ?>
    <div class="container-fluid" id="invoice-shop">
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-6">
                    <p><strong class="text-uppercase">Fecha:</strong> <?php echo date("d-m-Y", strtotime($datos_compra['compra_fecha'])); ?></p>
                    <p><strong class="text-uppercase">Compra registrada por:</strong> <?php echo $datos_compra['usuario_nombre']." ".$datos_compra['usuario_apellido']; ?></p>
                </div>
                <div class="col-12 col-md-6">
                    <p><strong class="text-uppercase">Proveedor:</strong><br><?php echo $datos_compra['proveedor_nombre']."<br>".$datos_compra['proveedor_direccion']; ?></p>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="bg-info">
                        <tr class="text-center text-uppercase">
                            <th scope="col">#</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Cantidad</th>
                            <th scope="col">Precio</th>
                            <th scope="col">Subtotal</th>
                            <th scope="col">Devolución</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $detalle_compra=$lc->datos_tabla("Normal","compra_detalle WHERE compra_codigo='".$datos_compra['compra_codigo']."'","*",0);
                            if($detalle_compra->rowCount()>=1){
                                $detalle_compra=$detalle_compra->fetchAll();
                                $cc=1;
                                foreach($detalle_compra as $detalle){
                                    echo '
                                    <tr class="text-center text-uppercase">
                                        <th scope="row">'.$cc.'</th>
                                        <td>'.$detalle['compra_detalle_descripcion'].'</td>
                                        <td>'.$detalle['compra_detalle_cantidad'].'</td>
                                        <td>'.MONEDA_SIMBOLO.number_format($detalle['compra_detalle_precio'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
                                        <td>'.MONEDA_SIMBOLO.number_format($detalle['compra_detalle_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
                                        <td>
                                            <button type="button" class="btn btn-primary" data-toggle="popover" data-trigger="hover" title="Realizar devolución" data-content="'.$detalle['compra_detalle_descripcion'].'" onclick="devolucion_producto(\''.$detalle['producto_id'].'\',\''.$detalle['compra_detalle_descripcion'].'\')" >
                                                <i class="fas fa-dolly fa-fw"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    ';
                                    $cc++;
                                }
                                echo '
                                <tr class="text-center text-uppercase font-weight-bold">
                                    <td colspan="3"></td>
                                    <td>Subtotal</td>
                                    <td>'.MONEDA_SIMBOLO.number_format($datos_compra['compra_subtotal'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
                                    <td></td>
                                </tr>
                                <tr class="text-center text-uppercase font-weight-bold">
                                    <td colspan="3"></td>
                                    <td>'.$datos_compra['compra_impuesto_nombre'].' '.$datos_compra['compra_impuesto_porcentaje'].'%</td>
                                    <td>'.MONEDA_SIMBOLO.number_format($datos_compra['compra_impuestos'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
                                    <td></td>
                                </tr>
                                <tr class="text-center text-uppercase font-weight-bold">
                                    <td colspan="3"></td>
                                    <td>Total</td>
                                    <td>'.MONEDA_SIMBOLO.number_format($datos_compra['compra_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
                                    <td></td>
                                </tr>
                                ';
                            }else{
                                echo '<tr class="text-center text-uppercase"><td colspan="9">No hay datos para mostrar</td></tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <p class="text-center">
        <a href="#" class="btn btn-outline-info print-barcode" data-id="#invoice-shop"><i class="fas fa-print"></i> &nbsp; Imprimir</a>
    </p>
    
    <hr style="margin: 70px 0; ">

    <h4 class="text-center">Devoluciones realizadas</h4>
    <div class="container-fluid" id="return-shop">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="bg-success">
                    <tr class="text-center text-uppercase">
                        <th scope="col">#</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Producto</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Precio</th>
                        <th scope="col">Total</th>
                        <th scope="col">Vendedor</th>
                        <th scope="col">Caja</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $datos_devolucion=$lc->datos_tabla("Normal","devolucion INNER JOIN usuario ON devolucion.usuario_id=usuario.usuario_id INNER JOIN caja ON devolucion.caja_id=caja.caja_id WHERE (devolucion.compra_venta_codigo='".$datos_compra['compra_codigo']."' AND devolucion.devolucion_tipo='Devolución de compra')","*",0);
                        if($datos_devolucion->rowCount()>=1){
                            $datos_devolucion=$datos_devolucion->fetchAll();
                            $cc=1;
                            foreach($datos_devolucion as $devolucion){
                                echo '
                                <tr class="text-center text-uppercase">
                                    <th scope="row">'.$cc.'</th>
                                    <td>'.date("d-m-Y", strtotime($devolucion['devolucion_fecha']))." ".$devolucion['devolucion_hora'].'</td>
                                    <td>'.$devolucion['devolucion_descripcion'].'</td>
                                    <td>'.$devolucion['devolucion_cantidad'].'</td>
                                    <td>'.MONEDA_SIMBOLO.number_format($devolucion['devolucion_precio'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
                                    <td>'.MONEDA_SIMBOLO.number_format($devolucion['devolucion_total'],MONEDA_DECIMALES,MONEDA_SEPARADOR_DECIMAL,MONEDA_SEPARADOR_MILLAR).' '.MONEDA_NOMBRE.'</td>
                                    <td>'.$devolucion['usuario_nombre']." ".$devolucion['usuario_apellido'].'</td>
                                    <td>Caja #'.$devolucion['caja_numero']." - ".$devolucion['caja_nombre'].'</td>
                                </tr>
                                ';
                                $cc++;
                            }
                        }else{
                            echo '<tr class="text-center text-uppercase"><td colspan="8">No hay datos para mostrar</td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <p class="text-center">
        <a href="#" class="btn btn-outline-info print-barcode" data-id="#return-shop"><i class="fas fa-print"></i> &nbsp; Imprimir</a>
    </p>
    
    <!-- Modal devolucion -->
    <div class="modal fade" id="ModalReturn" tabindex="-1" role="dialog" aria-labelledby="ModalReturn" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content FormularioAjax" action="<?php echo SERVERURL; ?>ajax/devolucionAjax.php" method="POST" data-form="save" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalReturn">Realizar devolución (<span id="devolucion_descripcion"></span>)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="modulo_devolucion" value="compra">
                    <input type="hidden" name="codigo_compra" id="codigo_compra" value="<?php echo $datos_compra['compra_codigo']; ?>">
                    <input type="hidden" name="id_producto" id="id_producto" value="">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <?php
                                        $datos_caja=$lc->datos_tabla("Normal","caja WHERE caja_id='".$_SESSION['caja_svi']."'","*",0);
                                        $datos_caja=$datos_caja->fetch();
                                    ?>
                                    <label for="devolucion_caja">Caja</label>
                                    <input type="text" class="form-control" value="Caja Nro.<?php echo $datos_caja['caja_numero']." (".$datos_caja['caja_nombre']; ?>)" id="devolucion_caja" readonly >
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="devolucion_cantidad">Cantidad a devolver</label>
                                    <input type="text" class="form-control" id="devolucion_cantidad" name="devolucion_cantidad" pattern="[0-9]{1,9}" maxlength="9" >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="far fa-times-circle"></i> &nbsp; Cancelar</button>
                    <button type="submit" class="btn btn-info"><i class="fas fa-dolly fa-fw"></i> &nbsp; Realizar devolución</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        /*----------  Hacer devolucion  ----------*/
        function devolucion_producto(producto,descripcion){
            producto.trim();
            descripcion.trim();

            document.querySelector('#id_producto').value=producto;
            document.querySelector('#devolucion_descripcion').innerHTML=descripcion;

            $('#ModalReturn').modal('show');
        }
    </script>
    <?php 
        }else{
            include "./vistas/inc/error_alert.php";
        } 
    ?>
</div>