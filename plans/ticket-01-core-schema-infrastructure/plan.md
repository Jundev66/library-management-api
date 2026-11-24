# Ticket 01: Core Database Schema, Seeders & Shared Infrastructure (Clean MVC)

## 1. Explicación en Lenguaje Claro
Construimos los cimientos del sistema: las tablas en base de datos (`categories` y `books`), sus relaciones de integridad referencial, los seeders con datos iniciales para levantar el sistema funcionando, y la configuración base de Laravel 11 para que cualquier petición y error responda siempre en formato JSON limpio (sin redirecciones HTML ni páginas de error de Laravel).

## 2. Estrategia
- **Tipo**: **SDD (Spec-Driven Development)**
- **Justificación**: Define el esquema físico de la base de datos, relaciones de clave foránea e infraestructura transversal de la API.

---

## 3. Modelo de Base de Datos y Relaciones Eloquent

### Migraciones
1. `database/migrations/2026_11_23_000001_create_categories_table.php`:
   - `id`: BigIncrements (PK)
   - `name`: string, unique
   - `timestamps()`
2. `database/migrations/2026_11_23_000002_create_books_table.php`:
   - `id`: BigIncrements (PK)
   - `title`: string
   - `author`: string
   - `description`: text, nullable
   - `published_year`: integer
   - `category_id`: foreignId('category_id')->constrained('categories')->restrictOnDelete()
   - `timestamps()`

> `restrictOnDelete()` garantiza a nivel base de datos que ninguna categoría con libros pueda ser borrada por accidente, reforzando la regla de negocio.

### Seeders
- `CategorySeeder`: Crea 5 categorías iniciales (Ficción, Ciencia, Historia, Tecnología, Filosofía).
- `BookSeeder`: Crea libros de prueba vinculados a las categorías seeded.
- `DatabaseSeeder`: Ejecuta ambos seeders.

### Infraestructura API Compartida
- `app/Http/Middleware/ForceJsonResponse.php`: Asegura header `Accept: application/json`.
- `bootstrap/app.php`:
  - Registro global del middleware para rutas API.
  - Manejo unificado de excepciones:
    - `NotFoundHttpException` / `ModelNotFoundException` -> 404 JSON.
    - `ValidationException` -> 422 JSON.
    - Excepciones de negocio (`CategoryHasBooksException`) -> 409 JSON.

---

## 4. Criterios de Aceptación
- [ ] Migraciones ejecutadas limpiamente con `php artisan migrate:fresh --seed`.
- [ ] La tabla `categories` tiene restricción de unicidad en `name`.
- [ ] La tabla `books` tiene FK hacia `categories` con `restrictOnDelete`.
- [ ] Los seeders insertan registros válidos y verificables.
- [ ] Rutas inexistentes o peticiones inválidas devuelven siempre JSON estructurado.
