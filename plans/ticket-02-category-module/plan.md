# Ticket 02: Category Module (Clean MVC)

## 1. Explicación en Lenguaje Claro
Implementamos la gestión de categorías dentro del patrón MVC: listar categorías, crear nuevas categorías garantizando que su nombre sea único, y eliminarlas. Incorporamos la regla de negocio fundamental: **no se puede eliminar una categoría si tiene libros asociados**.

## 2. Estrategia
- **Tipo**: **SDD (Spec-Driven Development)**
- **Justificación**: Implementa validación de unicidad, endpoints REST y la regla de negocio de integridad del catálogo.

---

## 3. Componentes Clean MVC

### Modelo (M)
- `app/Models/Category.php`:
  - Propiedades: `name`.
  - Relación: `public function books(): HasMany`.
  - Casts y Mass Assignment: `$fillable = ['name']`.

### Servicio de Negocio
- `app/Services/CategoryService.php`:
  - `create(array $data): Category`: Crea la categoría.
  - `delete(int $id): void`:
    - Busca la categoría con `Category::findOrFail($id)`.
    - Verifica si `category->books()->exists()`.
    - Si tiene libros, lanza `App\Exceptions\CategoryHasBooksException` (HTTP 409).
    - Si no tiene libros, elimina la categoría.

### Excepciones
- `app/Exceptions/CategoryHasBooksException.php`: Excepción de negocio que renderiza un HTTP 409 Conflict con mensaje descriptivo ("Cannot delete category because it has associated books").

### Form Requests (Validación)
- `app/Http/Requests/StoreCategoryRequest.php`:
  - `name`: `['required', 'string', 'max:255', 'unique:categories,name']`.

### Controladores (C)
- `app/Http/Controllers/Api/CategoryController.php`:
  - `index()`: Retorna `CategoryResource::collection(Category::all())`.
  - `store(StoreCategoryRequest $request, CategoryService $service)`: Llama a `$service->create($request->validated())` y retorna 201 Created con `CategoryResource`.
  - `destroy(int $id, CategoryService $service)`: Llama a `$service->delete($id)` y retorna 204 No Content.

### Vista / Presentación (V)
- `app/Http/Resources/CategoryResource.php`:
  - Transforma el modelo en JSON: `{ "id": ..., "name": ... }`.

---

## 4. Criterios de Aceptación y Tests
- [ ] `POST /categories` con nombre válido retorna 201 Created.
- [ ] `POST /categories` con nombre duplicado retorna 422 Unprocessable Entity.
- [ ] `GET /categories` retorna lista completa de categorías en JSON 200 OK.
- [ ] `DELETE /categories/{id}` sin libros asociados retorna 204 No Content.
- [ ] `DELETE /categories/{id}` con libros asociados retorna 409 Conflict.
- [ ] `DELETE /categories/{id}` con ID inexistente retorna 404 Not Found.
