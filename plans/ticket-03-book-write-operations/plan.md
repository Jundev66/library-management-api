# Ticket 03: Book Module - Write Operations (Clean MVC)

## 1. Explicación en Lenguaje Claro
Implementamos las operaciones de escritura para los libros: creación relacionándolo obligatoriamente a una categoría existente, edición de sus datos y eliminación. Cumplimos las validaciones requeridas (título mínimo 3 caracteres, autor obligatorio, año válido, categoría existente) y la regla de negocio: **verificar la existencia del libro antes de proceder con su eliminación**.

## 2. Estrategia
- **Tipo**: **SDD (Spec-Driven Development)**
- **Justificación**: Abarca mutaciones de datos, validaciones de integridad referencial y control de excepciones.

---

## 3. Componentes Clean MVC

### Modelo (M)
- `app/Models/Book.php`:
  - Propiedades: `title`, `author`, `description`, `published_year`, `category_id`.
  - Relación: `public function category(): BelongsTo`.
  - Mass Assignment: `$fillable = ['title', 'author', 'description', 'published_year', 'category_id']`.

### Servicio de Negocio
- `app/Services/BookService.php`:
  - `create(array $data): Book`: Crea y persiste el libro.
  - `update(int $id, array $data): Book`: Busca el libro (`Book::findOrFail($id)`), actualiza los datos y retorna la instancia fresca.
  - `delete(int $id): void`:
    - Verifica explícitamente la existencia con `Book::findOrFail($id)` (si no existe, lanza `ModelNotFoundException` -> 404).
    - Ejecuta `$book->delete()`.

### Form Requests (Validaciones Estrictas)
- `app/Http/Requests/StoreBookRequest.php`:
  - `title`: `['required', 'string', 'min:3', 'max:255']`
  - `author`: `['required', 'string', 'max:255']`
  - `description`: `['nullable', 'string']`
  - `published_year`: `['required', 'integer', 'min:1000', 'max:' . (date('Y') + 1)]`
  - `category_id`: `['required', 'integer', 'exists:categories,id']`
- `app/Http/Requests/UpdateBookRequest.php`:
  - Mismas reglas de validación para garantizar consistencia.

### Controladores (C)
- `app/Http/Controllers/Api/BookController.php`:
  - `store(StoreBookRequest $request, BookService $service)`: Retorna 201 Created con `BookResource`.
  - `update(UpdateBookRequest $request, int $id, BookService $service)`: Retorna 200 OK con `BookResource`.
  - `destroy(int $id, BookService $service)`: Retorna 204 No Content tras ejecutar `$service->delete($id)`.

### Vista / Presentación (V)
- `app/Http/Resources/BookResource.php`:
  - Mapeo JSON limpio con datos del libro y categoría anidada (`category: new CategoryResource($this->whenLoaded('category'))`).

---

## 4. Criterios de Aceptación y Tests
- [ ] `POST /books` con datos válidos crea el libro y retorna 201.
- [ ] `POST /books` con título de menos de 3 caracteres falla con 422.
- [ ] `POST /books` con `category_id` inexistente falla con 422.
- [ ] `PUT /books/{id}` con datos válidos actualiza el registro y retorna 200.
- [ ] `PUT /books/{id}` con ID inexistente retorna 404.
- [ ] `DELETE /books/{id}` sobre libro existente lo elimina y retorna 204.
- [ ] `DELETE /books/{id}` sobre libro inexistente retorna 404 (Regla de negocio verificada).
