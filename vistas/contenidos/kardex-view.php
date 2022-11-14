<?php
    include "./vistas/inc/admin_security.php";
?>
<div class="full-box page-header">
    <h3 class="text-left text-uppercase">
        <i class="fas fa-pallet fa-fw"></i> &nbsp; Kardex general
    </h3>
    <?php include "./vistas/desc/desc_kardex.php"; ?>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs text-uppercase">
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>kardex/">
                <i class="fas fa-pallet fa-fw"></i> &nbsp; Kardex general
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>kardex-search/">
                <i class="fas fa-search fa-fw"></i> &nbsp; Buscar kardex
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>kardex-product/">
                <i class="fas fa-luggage-cart fa-fw"></i> &nbsp; Kardex por producto
            </a>
        </li>
    </ul>	
</div>

<div class="container-fluid">
    <?php
        require_once "./controladores/kardexControlador.php";
        $ins_kardex = new kardexControlador();

        echo $ins_kardex->paginador_kardex_controlador($pagina[1],15,$pagina[0],"","");
    ?>
</div>