# Ticket 04: Book Module - Read & Filter Operations (Clean MVC)

## 1. Explicación en Lenguaje Claro
Permitir a los usuarios consultar la biblioteca: listar todos los libros almacenados o filtrarlos según la categoría seleccionada a través del parámetro de consulta (`GET /books?category={category_id}`). La consulta debe ser de alto rendimiento, evitando el problema de N+1 consultas al precargar las relaciones Eloquent de forma óptima.

## 2. Estrategia
- **Tipo**: **SDD (Spec-Driven Development)**
- **Justificación**: Maneja consultas optimizadas, filtrado dinámico mediante Query Scopes en Eloquent y serialización uniforme en JSON.

---

## 3. Componentes Clean MVC

### Modelo (M) - Query Scopes (Open/Closed Principle)
- En `app/Models/Book.php`:
  - `public function scopeFilterByCategory(Builder $query, ?int $categoryId): Builder`:
    - Si `$categoryId` viene definido y no es nulo, aplica `$query->where('category_id', $categoryId)`.

### Servicio de Negocio
- En `app/Services/BookService.php`:
  - `list(?int $categoryId = null): Collection`:
    - Ejecuta `Book::with('category')->filterByCategory($categoryId)->get()`.
    - La instrucción `with('category')` resuelve de raíz el problema de N+1 consultas.

### Controlador (C)
- En `app/Http/Controllers/Api/BookController.php`:
  - `index(Request $request, BookService $service)`:
    - Extrae opcionalmente `$request->query('category')`.
    - Obtiene la colección a través de `$service->list($categoryId)`.
    - Retorna `BookResource::collection($books)`.

### Vista / Presentación (V)
- `app/Http/Resources/BookResource.php`:
  - Presenta cada libro con su categoría asociada:
    ```json
    {
      "id": 1,
      "title": "Cien Años de Soledad",
      "author": "Gabriel García Márquez",
      "description": "Obra cumbre del realismo mágico.",
      "published_year": 1967,
      "category_id": 1,
      "category": {
        "id": 1,
        "name": "Ficción"
      }
    }
    ```

---

## 4. Criterios de Aceptación y Tests
- [ ] `GET /books` lista todos los libros almacenados con HTTP 200.
- [ ] `GET /books?category={category_id}` retorna únicamente los libros pertenecientes a esa categoría.
- [ ] `GET /books?category=9999` (categoría sin libros o inexistente) retorna una lista vacía `[]` con HTTP 200.
- [ ] Test de regresión anti N+1: La consulta con relaciones ejecuta exactamente 2 queries a la base de datos sin importar la cantidad de libros devueltos.
