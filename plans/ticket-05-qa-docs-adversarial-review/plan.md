# Ticket 05: Quality Assurance, API Documentation & Adversarial Review

## 1. Explicación en Lenguaje Claro
Cierre integral del proyecto. Documentamos la API de forma profesional sin violar la regla de cero comentarios en código, mediante una especificación estándar **OpenAPI 3.0** y una colección de **Postman** lista para importar. Ejecutamos pruebas de estrés y validaciones adversariales (casos nominales, negativos y reglas de negocio) para garantizar una entrega impecable.

## 2. Estrategia
- **Tipo**: **SDD (Spec-Driven Development)**
- **Justificación**: Verificación de calidad, cobertura de pruebas y entrega de especificación formal de la API.

---

## 3. Documentación de la API (Sin Anotaciones en Código)
Para cumplir rigurosamente con la política de **Zero Comments en código PHP**, la documentación se genera en archivos desacoplados:

### 1. Especificación OpenAPI 3.0 (`docs/openapi.yaml`)
- Define rutas, métodos, parámetros, payloads de request y schemas de respuesta para:
  - `GET /books`
  - `GET /books?category={category_id}`
  - `POST /books`
  - `PUT /books/{id}`
  - `DELETE /books/{id}`
  - `GET /categories`
  - `POST /categories`
  - `DELETE /categories/{id}`
- Define respuestas de error: 400 Bad Request, 404 Not Found, 409 Conflict, 422 Unprocessable Entity.

### 2. Colección de Postman (`docs/library-api.postman_collection.json`)
- Organizada por carpetas (`Books`, `Categories`).
- Variables de entorno (`{{base_url}}`).
- Scripts de validación automática de códigos de estado HTTP.

### 3. Guía de Consumo en `README.md`
- Ejemplos con `curl` para cada endpoint.
- Instrucciones de ejecución con Docker (`docker compose up -d`).
- Instrucciones para correr tests (`docker compose exec app php artisan test`).

---

## 4. Matriz Adversarial de QA

| Escenario | Endpoint | Input | Resultado Esperado | HTTP Status |
|---|---|---|---|---|
| **Crear libro con datos válidos** | `POST /books` | Payload completo y válido | Libro creado con categoría | 201 Created |
| **Título menor a 3 caracteres** | `POST /books` | `{"title": "Ab"}` | Error de validación en `title` | 422 Unprocessable |
| **Categoría inexistente** | `POST /books` | `{"category_id": 9999}` | Error de validación en `category_id` | 422 Unprocessable |
| **Año inválido** | `POST /books` | `{"published_year": "año"}` | Error de validación en `published_year` | 422 Unprocessable |
| **Editar libro existente** | `PUT /books/{id}` | Nuevos datos válidos | Libro actualizado | 200 OK |
| **Editar libro inexistente** | `PUT /books/9999` | Datos válidos | Not Found | 404 Not Found |
| **Eliminar libro existente** | `DELETE /books/{id}` | ID existente | Eliminado con éxito | 204 No Content |
| **Eliminar libro inexistente** | `DELETE /books/9999` | ID inexistente | Not Found (regla verificada) | 404 Not Found |
| **Eliminar categoría con libros** | `DELETE /categories/{id}` | Categoría con libros asociados | Conflicto de negocio (bloqueo) | 409 Conflict |
| **Eliminar categoría sin libros** | `DELETE /categories/{id}` | Categoría vacía | Eliminada con éxito | 204 No Content |
| **Crear categoría duplicada** | `POST /categories` | Nombre ya existente | Error de validación de unicidad | 422 Unprocessable |
| **Filtrar por categoría** | `GET /books?category={id}` | ID válido | Solo libros de esa categoría | 200 OK |

---

## 5. Auditoría de Código
- [ ] **Zero Comments Audit**: Ningún archivo en `app/` contiene `//`, `/* */` o `#`.
- [ ] **Strict Typing**: Todos los archivos inician con `declare(strict_types=1);` y tipado riguroso.
- [ ] **Estructura Clean MVC**: Controladores delgados, Form Requests para validación, API Resources para respuestas y Services para reglas de negocio.
- [ ] **Suite de Tests**: Cobertura al 100% pasando con `php artisan test`.
