<?php

header("Content-Type: application/json; charset=UTF-8");

// Permitir solicitudes desde cualquier origen
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Configuración de la base de datos desde .env
$envPath = dirname(__DIR__) . '/.env';

if (!file_exists($envPath)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Falta el archivo de configuración"
    ]);
    exit;
}

$config = parse_ini_file($envPath);

$host = $config['DB_HOST'] ?? 'localhost';
$dbname = $config['DB_NAME'] ?? '';
$username = $config['DB_USER'] ?? '';
$password = $config['DB_PASSWORD'] ?? '';

// Conexión mediante PDO
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Error de conexión con la base de datos"
    ]);

    exit;
}

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Obtener el ID desde la URL
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($requestUri, '/'));

$id = null;

if (isset($segments[3]) && is_numeric($segments[3])) {
    $id = (int)$segments[3];
}

// Procesar solicitudes
try {

    /*
    ==========================================
    GET /api/v1/productos
    GET /api/v1/productos/{id}
    ==========================================
    */

    if ($method === 'GET') {

        // Obtener un producto específico
        if ($id !== null) {

            $stmt = $pdo->prepare(
                "SELECT * FROM productos WHERE id = ?"
            );

            $stmt->execute([$id]);

            $producto = $stmt->fetch();

            if (!$producto) {

                http_response_code(404);

                echo json_encode([
                    "status" => "error",
                    "message" => "Producto no encontrado"
                ]);

                exit;
            }

            http_response_code(200);

            echo json_encode([
                "status" => "success",
                "data" => $producto
            ]);

            exit;
        }

        // Obtener todos los productos
        $stmt = $pdo->query(
            "SELECT * FROM productos ORDER BY id ASC"
        );

        $productos = $stmt->fetchAll();

        http_response_code(200);

        echo json_encode([
            "status" => "success",
            "data" => $productos
        ]);

        exit;
    }


    /*
    ==========================================
    POST /api/v1/productos
    ==========================================
    */

    if ($method === 'POST') {

        $input = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (
            !isset($input['nombre']) ||
            !isset($input['precio']) ||
            !isset($input['stock']) ||
            !isset($input['categoria'])
        ) {

            http_response_code(400);

            echo json_encode([
                "status" => "error",
                "message" => "Faltan datos obligatorios"
            ]);

            exit;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO productos
            (nombre, precio, stock, categoria, descripcion)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $input['nombre'],
            $input['precio'],
            $input['stock'],
            $input['categoria'],
            $input['descripcion'] ?? null
        ]);

        $nuevoId = $pdo->lastInsertId();

        http_response_code(201);

        echo json_encode([
            "status" => "success",
            "message" => "Registro creado exitosamente",
            "id" => (int)$nuevoId
        ]);

        exit;
    }


    /*
    ==========================================
    PUT /api/v1/productos/{id}
    ==========================================
    */

    if ($method === 'PUT') {

        if ($id === null) {

            http_response_code(400);

            echo json_encode([
                "status" => "error",
                "message" => "Debe proporcionar un ID válido"
            ]);

            exit;
        }

        $input = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (
            !isset($input['nombre']) ||
            !isset($input['precio']) ||
            !isset($input['stock']) ||
            !isset($input['categoria'])
        ) {

            http_response_code(400);

            echo json_encode([
                "status" => "error",
                "message" => "Faltan datos obligatorios"
            ]);

            exit;
        }

        // Verificar que exista
        $check = $pdo->prepare(
            "SELECT id FROM productos WHERE id = ?"
        );

        $check->execute([$id]);

        if (!$check->fetch()) {

            http_response_code(404);

            echo json_encode([
                "status" => "error",
                "message" => "Producto no encontrado"
            ]);

            exit;
        }

        $stmt = $pdo->prepare(
            "UPDATE productos
             SET nombre = ?,
                 precio = ?,
                 stock = ?,
                 categoria = ?,
                 descripcion = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $input['nombre'],
            $input['precio'],
            $input['stock'],
            $input['categoria'],
            $input['descripcion'] ?? null,
            $id
        ]);

        http_response_code(200);

        echo json_encode([
            "status" => "success",
            "message" => "Registro actualizado correctamente"
        ]);

        exit;
    }


    /*
    ==========================================
    DELETE /api/v1/productos/{id}
    ==========================================
    */

    if ($method === 'DELETE') {

        if ($id === null) {

            http_response_code(400);

            echo json_encode([
                "status" => "error",
                "message" => "Debe proporcionar un ID válido"
            ]);

            exit;
        }

        $stmt = $pdo->prepare(
            "DELETE FROM productos WHERE id = ?"
        );

        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {

            http_response_code(404);

            echo json_encode([
                "status" => "error",
                "message" => "Producto no encontrado"
            ]);

            exit;
        }

        http_response_code(200);

        echo json_encode([
            "status" => "success",
            "message" => "Producto eliminado correctamente"
        ]);

        exit;
    }


    // Método no permitido
    http_response_code(405);

    echo json_encode([
        "status" => "error",
        "message" => "Método HTTP no permitido"
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Error interno del servidor"
    ]);
}
?>
