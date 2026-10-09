
# API RESTful de Productos

API REST desarrollada en PHP utilizando PDO y MySQL, desplegada en Oracle Cloud Infrastructure (OCI).

## URL pública

http://139.177.97.107/api/v1/productos/

## Tecnologías

- PHP
- PDO
- MySQL
- Apache
- Oracle Cloud Infrastructure
- Git y GitHub

## Endpoints

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/v1/productos/` | Consultar todos los productos |
| GET | `/api/v1/productos/{id}` | Consultar un producto por ID |
| POST | `/api/v1/productos/` | Crear un producto |
| PUT | `/api/v1/productos/{id}` | Actualizar un producto |
| DELETE | `/api/v1/productos/{id}` | Eliminar un producto |

## Campos de un producto

- `nombre`: nombre del producto.
- `precio`: precio.
- `stock`: cantidad disponible.
- `categoria`: categoría del producto.
- `descripcion`: descripción del producto.

## Ejemplo de JSON

```json
{
  "nombre": "Teclado Mecánico RGB",
  "precio": 85.50,
  "stock": 20,
  "categoria": "Periféricos",
  "descripcion": "Teclado mecánico con iluminación RGB"
}
```

## Configuración

Copia `.env.example` a `.env` y completa los datos de conexión a MySQL. No publiques el archivo `.env`.

## Estructura

- `productos/index.php`: lógica de la API.
- `productos/.htaccess`: reglas de rutas.
- `.env.example`: plantilla de configuración.
- `.gitignore`: archivos excluidos de Git.
