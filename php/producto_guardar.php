<?php

    require_once "main.php"; 
    require_once "../inc/session_start.php";

    $codigo=limpiar_cadena($_POST['producto_codigo']);
    $nombre=limpiar_cadena($_POST['producto_nombre']);
    $precio=limpiar_cadena($_POST['producto_precio']);
    $stock=limpiar_cadena($_POST['producto_stock']);
    $categoria=limpiar_cadena($_POST['producto_categoria']);

    if($codigo=="" || $nombre=="" || $precio=="" || $stock=="" || $categoria==""){
        echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            No has llenado todos los campos que son obligatorios
        </div>
        ';
        exit();
    }

     if(verificarDatos("[a-zA-Z0-9- ]{1,70}",$codigo)){
        echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El codigo no coincide con el formato solicitado
        </div>
        ';
        exit();
    }
     if(verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,70}",$nombre)){
        echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El nombre no coincide con el formato solicitado
        </div>
        ';
        exit();
    }
    if(verificarDatos("[0-9.]{1,25}",$precio)){
        echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El precio no coincide con el formato solicitado
        </div>
        ';
        exit();
    }
     if(verificarDatos("[0-9]{1,25}",$stock)){
        echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El stock no coincide con el formato solicitado
        </div>
        ';
        exit();
    }

    $check_codigo=conexion();
    $check_codigo=$check_codigo->query("SELECT producto_codigo FROM 
    producto WHERE producto_codigo='$codigo'");
    if($check_codigo->rowCount()>0){
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                El codigo de producto ya existe, escoje otro
            </div>
            ';
            exit();
    }    
    $check_codigo=null; 

    $check_nombre=conexion();
    $check_nombre=$check_nombre->query("SELECT producto_nombre FROM 
    producto WHERE producto_nombre='$nombre'");
    if($check_nombre->rowCount()>0){
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                El nombre de producto ya existe, escoje otro
            </div>
            ';
            exit();
    }    
    $check_nombre=null; 

    $check_categoria=conexion();
    $check_categoria=$check_categoria->query("SELECT categoria_id FROM 
    categoria WHERE categoria_id='$categoria'");
    if($check_categoria->rowCount()<=0){
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                La categoria seleccionada no existe
            </div>
            ';
            exit();
    }    
    $check_categoria=null; 

    //Directorio de imagenes
    $img_dir="../img/Producto/";
    // Comprobar imagen
    if($_FILES['producto_foto']['name']!="" && $_FILES['producto_foto']['size']>0){
        //verificando directorio
        if(!file_exists($img_dir)){
            if(mkdir($img_dir,0777)){
                echo '
                <div class="notification is-danger is-light">
                    <strong>¡Ocurrio un error inesperado!</strong><br>
                    No se pudo crear el directorio
                </div>
                ';
                exit();
            }
        }
        //verificar formato imagenes
        if(mime_content_type($_FILES['producto_foto']['tmp_name'])!="image/jpeg" && 
        mime_content_type($_FILES['producto_foto']['tmp_name'])!="image/png"){
             echo '
                <div class="notification is-danger is-light">
                    <strong>¡Ocurrio un error inesperado!</strong><br>
                    La imagen es de un formato no permitido
                </div>
                ';
                exit();
        }
        //verificar peso imagen
        if(($_FILES['producto_foto']['size']/1024)>3072){
           echo '
                <div class="notification is-danger is-light">
                    <strong>¡Ocurrio un error inesperado!</strong><br>
                    La imagen que has seleccionado supera el peso permitido
                </div>
                ';
                exit(); 
        }
        //extension de la imagen
        switch(mime_content_type($_FILES['producto_foto']['tmp_name'])){
            case 'image/jpeg':
                $img_ext=".jpg";
            break;
            case 'image/png':
                $img_ext=".png";
            break;
        }
        chmod($img_dir,0777);
        $img_nombre=renombrar_fotos($nombre);
        $foto=$img_nombre.$img_ext;
        //moviendo imagen al directorio
        if(!move_uploaded_file($_FILES['producto_foto']['tmp_name'], $img_dir.$foto)){
                echo '
                <div class="notification is-danger is-light">
                    <strong>¡Ocurrio un error inesperado!</strong><br>
                    No pudimos cargar la imagen al sistema en este momento
                </div>
                ';
                exit(); 
        }
    }else{
        $foto="";
    }

    $guardar_producto=conexion();
    $guardar_producto=$guardar_producto->prepare("INSERT INTO producto(producto_codigo, producto_nombre, producto_precio,producto_stock,producto_foto,categoria_id,usuario_id) 
    VALUES(:codigo,:nombre,:precio,:stock,:foto,:categoria,:usuario)");
    $marcadores=[
        ":codigo"=>$codigo,
        ":nombre"=>$nombre,
        ":precio"=>$precio,
        ":stock"=>$stock,
        ":foto"=>$foto,
        ":categoria"=>$categoria,
        ":usuario"=>$_SESSION['id']
    ];
    $guardar_producto->execute($marcadores);
    if($guardar_producto->rowCount()==1){
        echo '
            <div class="notification is-info is-light">
                <strong>¡USUARIO REGISTRADO!</strong><br>
                Producto registrado con exito
            </div>
        ';
    }else{
        if(is_file($img_dir.$foto)){
            chmod($img_dir.$foto,0777);
            unlink($img_dir.$foto);
        }
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                No se pudo registrar el producto
            </div>
        ';
    }


