<?php
if (!function_exists('tienePermiso')) {
    function tienePermiso($conex, $rol, $modulo, $accion = 'ver') {
        if (!$conex) return false;
        $accion_columna = "puede_" . $accion;
        $query = "SELECT $accion_columna FROM permisos WHERE rol = ? AND modulo = ?";
        $stmt = $conex->prepare($query);
        $stmt->bind_param("ss", $rol, $modulo);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row[$accion_columna] == 1;
        }
        return false;
    }
}

if (!function_exists('verificarPermiso')) {
    function verificarPermiso($conex, $rol, $modulo, $accion = 'ver') {
        if (!$conex) {
            header("Location: sin_permiso.php");
            exit();
        }
        if (!tienePermiso($conex, $rol, $modulo, $accion)) {
            header("Location: sin_permiso.php");
            exit();
        }
    }
}

if (!function_exists('getModulosPorRol')) {
    function getModulosPorRol($conex, $rol) {
        if (!$conex) return [];
        $query = "SELECT m.* FROM modulos m 
                    INNER JOIN permisos p ON m.nombre = p.modulo 
                    WHERE p.rol = ? AND p.puede_ver = 1 AND m.activo = 1 
                    ORDER BY m.orden ASC";
        $stmt = $conex->prepare($query);
        $stmt->bind_param("s", $rol);
        $stmt->execute();
        $result = $stmt->get_result();
        $modulos = [];
        while ($row = $result->fetch_assoc()) {
            $modulos[] = $row;
        }
        return $modulos;
    }
}

if (!function_exists('getAllModulos')) {
    function getAllModulos($conex) {
        if (!$conex) return [];
        $query = "SELECT * FROM modulos WHERE activo = 1 ORDER BY orden ASC";
        $result = $conex->query($query);
        $modulos = [];
        while ($row = $result->fetch_assoc()) {
            $modulos[] = $row;
        }
        return $modulos;
    }
}

if (!function_exists('getAllRoles')) {
    function getAllRoles() {
        return ['usuario', 'jardinero', 'empleado', 'administrador'];
    }
}