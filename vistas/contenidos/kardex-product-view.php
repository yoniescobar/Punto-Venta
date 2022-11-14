<?php
    include "./vistas/inc/admin_security.php";
?>
<div class="full-box page-header">
    <h3 class="text-left text-uppercase">
        <i class="fas fa-luggage-cart fa-fw"></i> &nbsp; Kardex por producto
    </h3>
    <?php include "./vistas/desc/desc_kardex.php"; ?>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs text-uppercase">
        <li>
            <a href="<?php echo SERVERURL; ?>kardex/">
                <i class="fas fa-pallet fa-fw"></i> &nbsp; Kardex general
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>kardex-search/">
                <i class="fas fa-search fa-fw"></i> &nbsp; Buscar kardex
            </a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>kardex-product/">
                <i class="fas fa-luggage-cart fa-fw"></i> &nbsp; Kardex por producto
            </a>
        </li>
    </ul>	
</div>
<?php
	if(isset($pagina[1]) && $pagina[1]!=""){
?>
<div class="container-fluid">
    <p class="text-center" style="margin-top: 20px;">
        <a href="<?php echo SERVERURL.$pagina[0]."/"; ?>" class="btn btn-raised btn-danger"><i class="far fa-trash-alt"></i> &nbsp; REALIZAR OTRA BÚSQUEDA</a>
    </p>
    <?php
        require_once "./controladores/kardexControlador.php";
        $ins_kardex = new kardexControlador();

        echo $ins_kardex->paginador_kardex_controlador($pagina[2],15,$pagina[0],$pagina[1],"");
    ?>
</div>
<?php }else{ ?>
<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/kardexAjax.php" data-form="default" method="POST" autocomplete="off" >
        <input type="hidden" name="modulo_url" value="<?php echo $pagina[0]; ?>">
        <input type="hidden" name="modulo_kardex" value="kardex_producto">
        <div class="container-fluid">
            <div class="row justify-content-md-center">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="inputSearch" class="bmd-label-floating">Introduzca el código de barras del producto</label>
                        <input type="text" class="form-control" name="producto_codigo_kardex" pattern="[a-zA-Z0-9- ]{1,70}" id="inputSearch" maxlength="70">
                    </div>
                </div>
                <div class="col-12">
                    <p class="text-center" style="margin-top: 40px;">
                        <button type="submit" class="btn btn-raised btn-info"><i class="fas fa-search"></i> &nbsp; BUSCAR</button>
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
<?php } ?>