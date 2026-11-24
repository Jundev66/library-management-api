# Library Management API (Laravel 11 / PHP 8.4)

Aplicación de alto rendimiento para la gestión de una biblioteca de libros y categorías, desarrollada con **Laravel 11** y **PHP 8.4** bajo arquitectura **Clean MVC** con el **Patrón Action (Single-Action Classes)**, principios **SOLID**, tipado estricto al 100% y política de **cero comentarios en código**.

---

## 🏛️ Arquitectura y Principios de Diseño

### Clean MVC para APIs REST
- **M (Model & Domain Actions)**:
  - Modelos Eloquent (`app/Models/`) con casts tipados (`title`, `author`, `published_year`, `category_id`), relaciones estrictas (`belongsTo`, `hasMany`) y desacoplamiento de filtros con el patrón `QueryFilter`.
  - Cada caso de uso de negocio está desacoplado en una clase de acción única en `app/Actions/` (`CreateBookAction`, `UpdateBookAction`, `DeleteBookAction`, `ListBooksAction`, `CreateCategoryAction`, `DeleteCategoryAction`, `ListCategoriesAction`), garantizando el **Principio de Responsabilidad Única (SRP)** y transaccionalidad atómica con `lockForUpdate()`.
- **V (View / Presentation)**:
  - **Laravel API Resources** (`app/Http/Resources/`): Transforman la representación de los modelos en JSON consistente, aislando el esquema de la base de datos de la interfaz pública del cliente.
- **C (Controllers)**:
  - Controladores delgados en `app/Http/Controllers/Api/` que delegan la validación de entrada a los **Form Requests**, orquestan con DTOs inmutables hacia las **Actions**, y retornan Resources.

---

## 🚀 Puesta en Marcha con Docker (100% Automatizada)

El contenedor cuenta con un script de entrada defensivo ([entrypoint.sh](docker/entrypoint.sh)) que al levantarse:
1. Copia `.env.example` a `.env` de forma automática si este no existe (evitando subir credenciales a Git).
2. Instala dependencias con Composer si faltan.
3. Genera la `APP_KEY` si no está configurada.
4. Espera a que MySQL esté saludable (`healthcheck`).
5. Ejecuta las migraciones y seeders de forma automática e idempotente.
6. Inicia el servidor HTTP en el puerto `8000`.

> [!NOTE]
> **Gestión de `.env`**: Gracias a la automatización de Docker, no necesitás crear manualmente el `.env`. El contenedor lo aprovisiona desde `.env.example`. El archivo `.env` está estrictamente ignorado en `.gitignore` para proteger secretos. Si optás por correr el proyecto localmente sin Docker, ejecutá `cp .env.example .env && php artisan key:generate`.

### 1. Requisitos Previos
- [Docker](https://www.docker.com/) y Docker Compose v2+.

### 2. Levantar el Proyecto
Ejecutá un único comando en la raíz del proyecto:
```bash
docker compose up -d --build
```

### 3. Verificar que esté Corriendo
Podés inspeccionar los logs para confirmar que las migraciones y seeders se ejecutaron con éxito:
```bash
docker compose logs -f app
```
O consultar el endpoint de salud:
```bash
curl -X GET http://localhost:8000/api/health -H "Accept: application/json"
```
Respuesta esperada:
```json
{
  "status": "healthy"
}
```

---

## 🧪 Ejecución de Pruebas Automatizadas (Zero Mocks)

Todas las pruebas corren contra la base de datos real en Docker con aislamiento transaccional (`RefreshDatabase`):

### 1. Pruebas Unitarias y de Feature (PHPUnit)
Estructuradas modularmente por dominio y acción (`tests/Unit/` y `tests/Feature/`):
```bash
docker compose exec app php artisan test
```
*Resultado: **44 tests pasados (157 aserciones)** al 100%.*

### 2. Pruebas E2E Adversariales y de Concurrencia (Playwright)
Las pruebas E2E corren desde el host hacia el contenedor. Asegurate de tener las dependencias de Node instaladas:
```bash
npm install
npx playwright test
```
*Resultado: **11 tests pasados**.*

---

## 🏷️ Categorías Precargadas en el Seeder

Para facilitar las pruebas manuales y el consumo inmediato de la API de libros (`POST /books`, `GET /books?category={id}`), el seeder inicializa las siguientes 5 categorías:

| ID | Nombre de Categoría | Género / Temática de Ejemplo |
|:---:|---|---|
| **1** | `Ficción` | Novelas clásicas, realismo mágico |
| **2** | `Ciencia Ficción` | Épica espacial, distopías, tecnología |
| **3** | `Historia` | Crónicas, evolución, ensayos históricos |
| **4** | `Tecnología` | Clean code, patrones de software, refactorización |
| **5** | `Filosofía` | Estoicismo, ética, pensamiento crítico |

---

## 📖 Documentación Formal de la API

La especificación OpenAPI y la colección de Postman se encuentran en `docs/`:
- **Especificación OpenAPI 3.0**: [`docs/openapi.yaml`](docs/openapi.yaml) (compatible con Swagger Editor / Redoc).
- **Colección de Postman**: [`docs/library-api.postman_collection.json`](docs/library-api.postman_collection.json) (con variables de entorno y assertions).

---

## 🔌 Referencia de Endpoints y Ejemplos cURL

La URL base local es `http://localhost:8000/api`.

### 📚 Libros

#### 1. Listar todos los libros almacenados
```bash
curl -X GET http://localhost:8000/api/books \
  -H "Accept: application/json"
```

#### 2. Filtrar libros por categoría
```bash
curl -X GET "http://localhost:8000/api/books?category=1" \
  -H "Accept: application/json"
```

#### 3. Crear un nuevo libro relacionándolo a una categoría
```bash
curl -X POST http://localhost:8000/api/books \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "El Aleph",
    "author": "Jorge Luis Borges",
    "description": "Cuentos clásicos de literatura fantástica.",
    "published_year": 1949,
    "category_id": 1
  }'
```

#### 4. Actualizar/Editar la información de un libro
```bash
curl -X PUT http://localhost:8000/api/books/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Cien años de soledad (Edición Ilustrada)",
    "author": "Gabriel García Márquez",
    "description": "Edición conmemorativa ilustrada.",
    "published_year": 1967,
    "category_id": 1
  }'
```

#### 5. Eliminar un libro específico (Verifica existencia previa -> 404 si no existe)
```bash
curl -X DELETE http://localhost:8000/api/books/1 \
  -H "Accept: application/json"
```

---

### 🏷️ Categorías

Endpoints complementarios que implementan las reglas de negocio de categorías solicitadas en la consigna:

#### 1. Listar categorías
```bash
curl -X GET http://localhost:8000/api/categories \
  -H "Accept: application/json"
```

#### 2. Registrar nueva categoría (Nombre Único)
```bash
curl -X POST http://localhost:8000/api/categories \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Poesía"
  }'
```

#### 3. Eliminar una categoría (Verifica que no tenga libros asociados -> 409 Conflict)
```bash
curl -X DELETE http://localhost:8000/api/categories/1 \
  -H "Accept: application/json"
```
*Si la categoría tiene libros vinculados, el sistema responderá HTTP 409:*
```json
{
  "message": "Cannot delete category because it has associated books."
}
```

---

## 🧪 Matriz de Respuestas HTTP

| Código HTTP | Significado | Escenario de Negocio |
|---|---|---|
| **200 OK** | Éxito | Lectura de libros, listado de categorías, actualización de libro |
| **201 Created** | Recurso creado | Creación exitosa de libro o categoría |
| **204 No Content** | Recurso eliminado | Eliminación exitosa de libro o categoría |
| **404 Not Found** | No encontrado | Consulta o eliminación de libro o categoría con ID inexistente |
| **409 Conflict** | Conflicto de negocio | Intento de eliminar categoría con libros asociados |
| **422 Unprocessable** | Error de validación | Título < 3 caracteres, año inválido, categoría inexistente, nombre duplicado |
| **405 Method Not Allowed** | Método no soportado | Petición HTTP no permitida para la ruta |
| **429 Too Many Requests** | Rate limit excedido | Protección contra ataques de fuerza bruta / DoS |
