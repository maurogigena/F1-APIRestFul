# 🏎️ F1 API

RESTful API para gestionar datos de Fórmula 1: usuarios, circuitos, pilotos y equipos.  
Incluye autenticación, autorización y endpoints para CRUD sobre las entidades principales.

---

## ✨ Features

- 🔐 **Autenticación:** Registro, Login, Logout con rate limiting  
- 🛡️ **Roles y permisos:** Admin y usuarios normales con políticas para control de acceso  
- 🏁 **Entidades:** Circuits, Drivers, Teams, Users  
- 🤝 **Relaciones:** Driver pertenece a Team, Team tiene muchos Drivers  
- 🔍 **Filtros:** Query filtering avanzado para circuits, drivers y users  
- 🎨 **Resources:** Respuestas API estandarizadas con clases Resource  
- 🔒 **Seguridad:** Laravel Sanctum para autenticación con tokens  
- ✅ **Tests:** Tests unitarios y de integración (feature) incluidos

---

## 🗂️ Estructura del Proyecto

### Archivos y carpetas principales

| Carpeta / Archivo                   | Descripción                                                  |
|-----------------------------------|--------------------------------------------------------------|
| `Api.php`                         | Define rutas: register, login, logout y endpoints de controladores |
| **Database**                      | CIDR con 19 usuarios falsos + 1 admin (campos: name, email, password, isAdminTrue) |
| Migrations & Factories            | Migraciones y factory para User                              |
| **Bootstrap**                     | `app.php` con manejo de errores en `withExceptions` y providers cargados |
| **Trades**                       | `ApiResponses.php` para respuestas uniformes                |
| **Providers**                    | `AppServiceProvider`, `AuthServiceProvider`, `RouteServiceProvider` |
| **Policies/Api**                 | `CircuitPolicy`, `DriverPolicy`, `UserPolicy`                |
| **Permissions/Api**              | `Abilities.php` conectado con `DriverPolicy`                  |
| **Models**                      | `Circuit`, `Driver` (belongsTo Team), `Team` (hasMany Drivers), `User` |
| **Resources/Api**                | `CircuitResource`, `DriverResource`, `UserResource`           |
| **Request/Api**                 | Requests base y específicos: `BaseRequest` (padre), `StoreRequest`, `UpdateRequest`, `ReplaceRequest`, además de `LoginUserRequest` y `RegisterUserRequest` |
| **Filters/Api**                 | `QueryFilter` (base), `CircuitFilter`, `DriverFilter`, `UserFilter` (extienden) |
| **Controllers/Api**             | `ApiController` (base), `CircuitController`, `DriverController`, `UserController`, `AuthController` (register, login, logout, rate limiter) |

---

## 🔐 Autenticación y Seguridad

- Autenticación con **Laravel Sanctum** para manejo de tokens  
- Control de acceso con políticas (`Policies`) y permisos (`Abilities`)  
- Rate limiter aplicado en endpoints de autenticación para evitar abusos

---

## 🧪 Testing

- **Feature Tests:**  
  - `AuthTest`  
  - `CircuitTest`  
  - `DriverTest`  
  - `UserTest`  
  - `ExampleTest`
- **Unit Tests:**  
  - `ApiResponseTest`  
  - `DriverFilterTest`

---

## 🚀 Instalación

```bash
# Clonar repo
git clone <repo-url>

# Ejecutar migraciones y seeders para usuarios y admin
php artisan migrate --seed

# Configurar .env (base de datos, Sanctum, etc)

# Correr servidor local
php artisan serve

# (Opcional) Ejecutar tests
php artisan test

## 🛠️ Endpoints principales

| Método              | Ruta         | Descripción                     |
|---------------------|--------------|---------------------------------|
| POST                | `/register`  | Registrar nuevo usuario          |
| POST                | `/login`     | Login y obtener token            |
| POST                | `/logout`    | Logout y revocar token           |
| GET/POST/PUT/DELETE | `/circuits`  | CRUD de circuitos                |
| GET/POST/PUT/DELETE | `/drivers`   | CRUD de pilotos                 |
| GET/POST/PUT/DELETE | `/teams`     | CRUD de equipos                 |
| GET/POST/PUT/DELETE | `/users`     | CRUD de usuarios (solo admin)  |

---

## 🤝 Contributions

Se aceptan issues y pull requests para mejoras o corrección de bugs.

---

## 📄 Licence

MIT License — proyecto open source.