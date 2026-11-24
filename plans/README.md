# Plan Maestro de Implementación (SDD) - Sistema de Gestión de Biblioteca (Clean MVC)

Este repositorio descompone el desarrollo de la prueba técnica bajo **Clean MVC Idiomático en Laravel 11 / PHP 8.4**, aplicando principios **SOLID**, política estricta de **cero comentarios en código** y pruebas automatizadas.

## Arquitectura Clean MVC en APIs REST
En una API REST moderna con Laravel, las capas de MVC se traducen limpiamente en:
- **M (Model & Domain Services)**: Modelos Eloquent (`app/Models/`) con relaciones, casts y scopes; complementados con Servicios de Negocio (`app/Services/`) para orquestar reglas complejas (ej. validación previa antes de eliminar categorías o libros) sin inflar los controladores.
- **V (View / Presentation)**: Laravel API Resources (`app/Http/Resources/`) que transforman y proyectan la respuesta JSON estandarizada hacia el cliente, aislando la estructura de base de datos de la interfaz pública.
- **C (Controllers)**: Controladores delgados (*Skinny Controllers* en `app/Http/Controllers/Api/`) que delegan la validación a **Form Requests** (`app/Http/Requests/`), la lógica a los **Services/Models**, y la salida a los **Resources**.

---

## Roster de Tickets

| Ticket | Directorio | Alcance | Enfoque |
|---|---|---|---|
| **Ticket 01** | [`ticket-01-core-schema-infrastructure`](file:///C:/Users/PC/Desktop/preuba%20tecnica%20-%20!gaming/plans/ticket-01-core-schema-infrastructure/plan.md) | Base de datos, Migraciones, Seeders e Infraestructura API (`ForceJsonResponse`, Exception Handler) | SDD |
| **Ticket 02** | [`ticket-02-category-module`](file:///C:/Users/PC/Desktop/preuba%20tecnica%20-%20!gaming/plans/ticket-02-category-module/plan.md) | Módulo Categorías (Model, CategoryService, Form Requests, CategoryResource, Regla de negocio anti-eliminación) | SDD |
| **Ticket 03** | [`ticket-03-book-write-operations`](file:///C:/Users/PC/Desktop/preuba%20tecnica%20-%20!gaming/plans/ticket-03-book-write-operations/plan.md) | Módulo Libros - Mutaciones (StoreBookRequest, UpdateBookRequest, BookService, verificación de existencia) | SDD |
| **Ticket 04** | [`ticket-04-book-read-filtering`](file:///C:/Users/PC/Desktop/preuba%20tecnica%20-%20!gaming/plans/ticket-04-book-read-filtering/plan.md) | Módulo Libros - Lectura y Filtrado (`GET /books`, `GET /books?category={id}`, Query Scopes, Anti N+1 con Eager Loading) | SDD |
| **Ticket 05** | [`ticket-05-qa-docs-adversarial-review`](file:///C:/Users/PC/Desktop/preuba%20tecnica%20-%20!gaming/plans/ticket-05-qa-docs-adversarial-review/plan.md) | Documentación OpenAPI 3.0, Colección Postman, Matriz Adversarial de QA y Auditoría de Código | SDD |

---

## Reglas Inviolables
1. **Zero Comments Policy**: Cero comentarios (`//`, `/* */`, `#`) en código fuente. Código 100% autodocumentado mediante tipado estricto PHP 8.4 (`declare(strict_types=1);`) y nombres expresivos.
2. **SOLID en Laravel**:
   - **S (SRP)**: Controladores delgados, validación exclusiva en Form Requests, transformación en Resources.
   - **O (OCP)**: Filtrado extensible mediante Eloquent Scopes.
   - **L (LSP)**: Modelos y Servicios respetan contratos estándar del framework.
   - **I (ISP)**: Form Requests específicos por acción (`StoreBookRequest`, `UpdateBookRequest`).
   - **D (DIP)**: Inyección de dependencias en constructores y métodos de controladores.
3. **Documentación Externa**: La API se documenta mediante especificación estándar **OpenAPI 3.0** (`docs/openapi.yaml`) y **Colección Postman** (`docs/library-api.postman_collection.json`), sin ensuciar el código con docblocks o anotaciones.
