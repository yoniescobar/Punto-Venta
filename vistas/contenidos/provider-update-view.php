<?php
    include "./vistas/inc/admin_security.php";
?>
<div class="full-box page-header">
    <h3 class="text-left text-uppercase">
        <i class="fas fa-sync fa-fw"></i> &nbsp; Actualizar proveedor
    </h3>
    <?php include "./vistas/desc/desc_proveedor.php"; ?>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs text-uppercase">
        <li>
            <a href="<?php echo SERVERURL; ?>provider-new/">
                <i class="fas fa-shipping-fast fa-fw"></i> &nbsp; Nuevo proveedor
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>provider-list/">
                <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de proveedores
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>provider-search/">
                <i class="fas fa-search fa-fw"></i> &nbsp; Buscar proveedor
            </a>
        </li>
    </ul>	
</div>

<div class="container-fluid">
    <?php
        include "./vistas/inc/btn_go_back.php";
        
        $datos_proveedor=$lc->datos_tabla("Unico","proveedor","proveedor_id",$pagina[1]);

        if($datos_proveedor->rowCount()==1){
            $campos=$datos_proveedor->fetch();
    ?>
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/proveedorAjax.php" method="POST" data-form="update" autocomplete="off" >
        <input type="hidden" name="proveedor_id_up" value="<?php echo $pagina[1]; ?>" >
        <input type="hidden" name="modulo_proveedor" value="actualizar">
        <fieldset>
            <legend><i class="far fa-address-card"></i> &nbsp; Datos del proveedor</legend>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_tipo_documento" class="bmd-label-floating">Tipo de documento <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <select class="form-control" name="proveedor_tipo_documento_up" id="proveedor_tipo_documento">
                                <?php
                                    echo $lc->generar_select(DOCUMENTOS_PROVEEDORES,$campos['proveedor_tipo_documento']);
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_numero_documento" class="bmd-label-floating">Numero de documento <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <input type="text" pattern="[a-zA-Z0-9-]{7,30}" class="form-control" name="proveedor_numero_documento_up" value="<?php echo $campos['proveedor_numero_documento']; ?>" id="proveedor_numero_documento" maxlength="30">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_nombre" class="bmd-label-floating">Nombre <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <input type="text" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ., ]{4,75}" class="form-control" name="proveedor_nombre_up" value="<?php echo $campos['proveedor_nombre']; ?>" id="proveedor_nombre" maxlength="75">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_direccion" class="bmd-label-floating">Dirección</label>
                            <input type="text" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{4,97}" class="form-control" name="proveedor_direccion_up" value="<?php echo $campos['proveedor_direccion']; ?>" id="proveedor_direccion" maxlength="97">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_estado" class="bmd-label-floating">Estado <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <select class="form-control" name="proveedor_estado_up" id="proveedor_estado">
                                <?php
                                    $array_estado=["Habilitado","Deshabilitado"];
                                    echo $lc->generar_select($array_estado,$campos['proveedor_estado']);
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
        <br><br><br>
        <fieldset>
            <legend><i class="fas fa-phone-volume"></i> &nbsp; Información de contacto</legend>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_encargado" class="bmd-label-floating">Nombre del encargado</label>
                            <input type="text" pattern="[a-zA-Z ]{4,70}" class="form-control" name="proveedor_encargado_up" value="<?php echo $campos['proveedor_contacto']; ?>" id="proveedor_encargado" maxlength="70">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_telefono" class="bmd-label-floating">Teléfono</label>
                            <input type="text" pattern="[0-9()+]{8,20}" class="form-control" name="proveedor_telefono_up" value="<?php echo $campos['proveedor_telefono']; ?>" id="proveedor_telefono" maxlength="20">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="proveedor_email" class="bmd-label-floating">Email</label>
                            <input type="email" class="form-control" name="proveedor_email_up" value="<?php echo $campos['proveedor_email']; ?>" id="proveedor_email" maxlength="50">
                        </div>
                    </div>               
                </div>
            </div>
        </fieldset>
        <p class="text-center" style="margin-top: 40px;">
            <button type="submit" class="btn btn-raised btn-success btn-sm"><i class="fas fa-sync"></i> &nbsp; ACTUALIZAR</button>
        </p>
        <p class="text-center">
            <small>Los campos marcados con <?php echo CAMPO_OBLIGATORIO; ?> son obligatorios</small>
        </p>
    </form>
    <?php 
        }else{
            include "./vistas/inc/error_alert.php";
        } 
    ?>
</div>