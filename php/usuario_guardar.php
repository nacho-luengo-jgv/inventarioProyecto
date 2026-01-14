<?php
    require_once "main.php"; 

    $nombre=limpiar_cadena($_POST['usuario_nombre']);
    $apellido=limpiar_cadena($_POST['usuario_apellido']);
    $usuario=limpiar_cadena($_POST['usuario_usuario']);
    $email=limpiar_cadena($_POST['usuario_email']);

    $clave_1=limpiar_cadena($_POST['usuario_clave_1']);
    $clave_2=limpiar_cadena($_POST['usuario_clave_2']);

    #verifico campos obligatorios

    if($nombre=="" || $apellido=="" || $usuario=="" || $clave_1=="" || $clave_2==""){
        echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            No has llenado todos los campos que son obligatorios
        </div>
        ';
        exit();
    }

    #verifico integridad de datos

    if(verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}",$nombre)){
        echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El nombre no coincide con el formato solicitado
        </div>
        ';
        exit();
    }
    if(verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}",$apellido)){
        echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El apellido no coincide con el formato solicitado
        </div>
        ';
        exit();
    }
    if(verificarDatos("[a-zA-Z0-9]{4,20}",$usuario)){
        echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El usuario no coincide con el formato solicitado
        </div>
        ';
        exit();
    }
    if(!verificarDatos("[a-zA-Z0-9$@.-]{7,100}",$clave_1) || !verificarDatos("[a-zA-Z0-9$@.-]{7,100}",$clave_2)){
        echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            Las claves no coincide con el formato solicitado
        </div>
        ';
        exit();
    }

    #verificando email

    if($email!=""){
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            $check_email=conexion();
            $check_email=$check_email->query("SELECT usuario_email FROM 
            usuario WHERE usuario_email='$email'");
            if($check_email->rowCount()>0){
                  echo '
                    <div class="notification is-danger is-light">
                        <strong>¡Ocurrio un error inesperado!</strong><br>
                        El email ya esta registrado
                    </div>
                    ';
                    exit();
            }    
            $check_email=null;  
        }else{
               echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El email no coincide con el formato solicitado
        </div>
        ';
        exit();
        }
    }

    #verificando usuario
            $check_usuario=conexion();
            $check_usuario=$check_usuario->query("SELECT usuario_usuario FROM 
            usuario WHERE usuario_usuario='$usuario'");
            if($check_usuario->rowCount()>0){
                  echo '
                    <div class="notification is-danger is-light">
                        <strong>¡Ocurrio un error inesperado!</strong><br>
                        El nombre de usuario ya existe, escoje otro
                    </div>
                    ';
                    exit();
            }    
            $check_usuario=null; 

    #veroficar claves
    if($clave_1!=$clave_2){
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                Las claves no coinciden
            </div>
                    ';
        exit();
    }else{
        $clave=password_hash($clave_1,PASSWORD_BCRYPT,["cost"=>10]);
    }

    #Guardando datos
    $guardar_usuario=conexion();
    $guardar_usuario=$guardar_usuario->prepare("INSERT INTO usuario(usuario_nombre,usuario_apellido,usuario_usuario,usuario_clave,usuario_email) 
    VALUES(:nombre,:apellido,:usuario,:clave,:email)");

    $marcadores=[
        ":nombre"=>$nombre,
        ":apellido"=>$apellido,
        ":usuario"=>$usuario,
        ":clave"=>$clave,
        ":email"=>$email
    ];
    $guardar_usuario->execute($marcadores);

    if($guardar_usuario->rowCount()==1){
         echo '
            <div class="notification is-info is-light">
                <strong>¡USUARIO REGISTRADO!</strong><br>
                El usuario se registro con exito
            </div>
        ';
    }else{
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                No se pudo registrar el usuario, intente denuevo
            </div>
        ';
    }
    $guardar_usuario=null;


