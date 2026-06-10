# Prolecom — API Reference (v1) Completa
> Última actualización: 2026. Documentación exhaustiva para consumo del Frontend (Sprints 1 y 2).

## Base URL
```
https://code-learning-staging.up.railway.app/api/v1
```

## Convenciones Globales
| Header | Valor Obligatorio |
|---|---|
| `Authorization` | `Bearer <JWT>` (En todas las rutas protegidas) |
| `Content-Type` | `application/json` |
| `Accept` | `application/json` |

---

## 1. Sistema Base y DevOps

### `GET /health`
- **Descripción:** Verifica que la API esté viva.
- **Acceso:** Público
- **Respuesta (200):** `{ "status": "ok" }`

### `GET /ping-deploy`
- **Descripción:** Verifica carga de controladores.
- **Acceso:** Público
- **Respuesta (200):** `{ "auth_exists": true, "course_exists": true }`

### `GET /dev-reset-db`
- **Descripción:** (SOLO STAGING) Destruye y resiembra la BD.
- **Query Params:** `?token=railway_prolecom_secret_2026`
- **Respuesta (200):** `{ "message": "Base de datos destruida y re-sembrada exitosamente" }`

---

## 2. Autenticación (Auth)

### `POST /users` (Registro)
- **Acceso:** Público
- **Payload:**
  ```json
  {
    "name": "Juan Perez",
    "email": "juan@gmail.com",
    "password": "password123",
    "password_confirmation": "password123"
  }
  ```
- **Respuesta (201):** `{ "message": "User registered successfully", "data": { "id": "uuid", "name": "...", "roles": ["student"] } }`

### `POST /sessions` (Login)
- **Acceso:** Público
- **Payload:**
  ```json
  {
    "email": "juan@gmail.com",
    "password": "password123",
    "device_name": "web-browser"
  }
  ```
- **Respuesta (200):** `{ "token": "1|xyz..." }`

### `DELETE /sessions/current` (Logout)
- **Acceso:** Auth (Cualquier usuario logueado)
- **Respuesta (200):** `{ "message": "Logged out" }`

### `POST /password-reset-links`
- **Acceso:** Público
- **Payload:** `{ "email": "juan@gmail.com" }`
- **Respuesta (200):** `{ "message": "Si el correo existe, se enviará el enlace." }`

### `POST /password-resets`
- **Acceso:** Público
- **Payload:** 
  ```json
  { 
    "email": "juan@gmail.com", 
    "token": "token_del_email", 
    "password": "new_password123", 
    "password_confirmation": "new_password123" 
  }
  ```
- **Respuesta (200):** `{ "message": "Contraseña actualizada" }`

---

## 3. Perfil de Usuario

### `GET /user`
- **Acceso:** Auth
- **Respuesta (200):** `{ "data": { "id": "uuid", "name": "...", "email": "...", "roles": ["student"] } }`

### `PUT /user`
- **Acceso:** Auth
- **Payload:** `{ "name": "Juan Actualizado", "avatar_path": "https://img.com/a.jpg" }`
- **Respuesta (200):** `{ "data": { ... } }`

### `DELETE /users/me`
- **Acceso:** Auth
- **Respuesta (200):** `{ "message": "Account deactivated" }` (Realiza un SoftDelete)

---

## 4. Solicitudes para Profesor

### `POST /professor-applications`
- **Acceso:** Auth (Estudiantes que quieren ser profesores)
- **Payload:** `{ "motivation": "Tengo 5 años de experiencia..." }`
- **Respuesta (201):** `{ "data": { "id": 1, "status": "pending" } }`

### `GET /professor-applications/mine`
- **Acceso:** Auth
- **Respuesta (200):** Detalles de tu solicitud activa.

---

## 5. Cursos (Courses)

### `GET /courses`
- **Acceso:** Auth
- **Query Params:** `?category=programming`
- **Respuesta (200):** Lista paginada de cursos públicos. `{ "data": [ ... ] }`

### `GET /courses/{id}`
- **Acceso:** Auth (Inscrito) o Staff del curso.
- **Respuesta (200):** Detalles completos del curso, incluyendo su Syllabus (Módulos).

### `POST /courses`
- **Acceso:** `professor` | `ta`
- **Enums - Categorías:** `programming, web, mobile, data_science, devops, design`
- **Enums - Status:** `draft, public, unlisted`
- **Payload:**
  ```json
  {
    "title": "Aprende Python",
    "description": "Curso desde cero",
    "status": "draft",
    "category": "programming"
  }
  ```
- **Respuesta (201):** `{ "data": { ... } }`

### `PUT /courses/{id}`
- **Acceso:** Owner del curso | `ta`
- **Payload:** (Mismos campos que el POST, pero opcionales).
- **Respuesta (200):** `{ "data": { ... } }`

### `DELETE /courses/{id}`
- **Acceso:** Solo el Owner del curso (`professor`).
- **Respuesta (200):** `{ "message": "Curso eliminado" }`

### `GET /courses/{id}/stats`
- **Acceso:** Owner del curso | `ta`
- **Respuesta (200):** `{ "total_students": 150, "top_students": [...] }`

### `GET /courses/{id}/leaderboard`
- **Acceso:** Auth (Inscrito)
- **Respuesta (200):** `{ "leaderboard": [ { "user": "Juan", "xp": 450 } ] }`

### `GET /courses/{id}/progress`
- **Acceso:** Auth (Inscrito)
- **Respuesta (200):** `{ "progress_percent": 35.5, "completed_items": [...] }`

### `GET /courses/{id}/analytics`
- **Acceso:** Owner del curso | `ta`
- **Respuesta (200):** Dashboard analítico del curso.

---

## 6. Matrículas y Staff

### `POST /courses/{id}/enrollments`
- **Acceso:** Auth (El usuario se inscribe a sí mismo). El curso debe ser `public`.
- **Payload:** Vacio `{}`
- **Respuesta (201):** `{ "message": "Enrolled successfully" }`

### `DELETE /courses/{id}/enrollments/me`
- **Acceso:** Auth (Inscrito)
- **Respuesta (200):** `{ "message": "Dropped from course" }`

### `POST /courses/{id}/enrollments/manual`
- **Acceso:** Owner del curso | `ta`
- **Payload:** `{ "user_id": "uuid_del_usuario", "role": "student" }`
- **Respuesta (201):** `{ "message": "Usuario inscrito manualmente" }`

### `POST /courses/{id}/staff-members`
- **Acceso:** Owner del curso
- **Payload:** `{ "user_id": "uuid_del_usuario", "role": "ta" }`
- **Respuesta (200):** `{ "message": "Staff member added" }`

### `DELETE /courses/{id}/staff/{user_id}`
- **Acceso:** Owner del curso
- **Respuesta (200):** `{ "message": "Staff member removed" }`

---

## 7. Módulos y Materiales (Syllabus)

### `POST /courses/{id}/modules`
- **Acceso:** Owner | `ta`
- **Payload:** `{ "title": "Bases de Python", "description": "...", "order": 1 }`
- **Respuesta (201):** `{ "data": { ... } }`

### `PUT /modules/{id}` y `DELETE /modules/{id}`
- **Acceso:** Owner | `ta`

### `PATCH /modules/{id}/items-order`
- **Acceso:** Owner | `ta`
- **Descripción:** Reordena los items dentro del módulo (Retos, Materiales, Quizzes).
- **Payload:**
  ```json
  {
    "items": [
      { "id": 1, "type": "App\\Models\\Material", "order": 1 },
      { "id": "uuid-reto", "type": "App\\Models\\Challenge", "order": 2 }
    ]
  }
  ```

### `POST /modules/{id}/materials`
- **Acceso:** Owner | `ta`
- **Enums - Type:** `pdf, video_link, ppt, pptx`
- **Payload:** `{ "title": "Video 1", "type": "video_link", "content": "https://youtube..." }`
- **Respuesta (201):** `{ "data": { ... } }`

### `PUT /materials/{id}` y `DELETE /materials/{id}`
- **Acceso:** Owner | `ta`

### `GET /materials/{id}`
- **Acceso:** Inscrito.

### `GET /materials/{id}/download`
- **Acceso:** Inscrito.
- **Respuesta (200 o 302):** Descarga el archivo (si es PDF/PPT) o redirige (302) al link de video.

### `POST /materials/{id}/views`
- **Acceso:** Inscrito.
- **Descripción:** Registra que el estudiante vio el material y le otorga XP.

---

## 8. Retos de Programación (Judge0)

### `GET /languages`
- **Acceso:** Auth
- **Respuesta (200):** Retorna los IDs de lenguajes de Judge0 (ej. `71` = Python).

### `GET /modules/{id}/challenges`
- **Acceso:** Owner | `ta`
- **Respuesta (200):** Lista los retos de un módulo (incluye borradores).

### `POST /modules/{id}/challenges`
- **Acceso:** Owner | `ta`
- **Enums - Difficulty:** `easy, medium, hard`
- **Enums - Status:** `draft, pending_review, approved, rejected`
- **Payload:**
  ```json
  {
    "title": "Two Sum",
    "description": "Encuentra dos números...",
    "difficulty": "medium",
    "points": 100,
    "language_id": 71,
    "language_name": "python",
    "status": "draft"
  }
  ```
- **Respuesta (201):** `{ "data": { ... } }`

### `GET /challenges/{id}`, `PUT /challenges/{id}`, `DELETE /challenges/{id}`
- **Acceso:** GET es para inscritos (solo ven `approved`). PUT/DELETE para Owner | `ta`.

### `POST /challenges/{id}/test-cases`
- **Acceso:** Owner | `ta`
- **Payload:** `{ "input": "2,2", "expected_output": "4", "is_hidden": true }`

### `PUT /challenge-test-cases/{id}` y `DELETE /challenge-test-cases/{id}`
- **Acceso:** Owner | `ta`

### `POST /challenges/{id}/attempts`
- **Acceso:** Inscrito.
- **Descripción:** Envía el código a Judge0 para evaluación real.
- **Payload:** `{ "submitted_code": "print(int(input()) * 2)", "language_id": 71 }`
- **Respuesta (201):** `{ "status": "passed", "score": 100, "details": "..." }`

### `GET /challenges/{id}/attempts`
- **Acceso:** Inscrito (ve los suyos) | Owner (ve todos).

### `POST /challenge-attempts/{id}/feedback`
- **Acceso:** Owner | `ta`. Da retroalimentación manual al código del estudiante.
- **Payload:** `{ "feedback": "Mejora tu complejidad algorítmica." }`

---

## 9. Quizzes y Flashcards

### `POST /modules/{id}/quizzes`
- **Acceso:** Owner | `ta`
- **Enums - Mode:** `practice, exam`
- **Payload:** `{ "title": "Examen", "mode": "exam", "passing_score": 70, "time_limit_minutes": 30 }`

### `GET /quizzes/{id}`, `PUT /quizzes/{id}`, `DELETE /quizzes/{id}`
- **Acceso:** GET para inscritos.

### `POST /quizzes/{id}/questions`
- **Acceso:** Owner | `ta`
- **Enums - Type:** `multiple_choice, true_false`
- **Payload:**
  ```json
  {
    "question_text": "¿Qué es HTML?",
    "type": "multiple_choice",
    "points": 10,
    "options": ["Un lenguaje", "Un protocolo"],
    "correct_answer": "Un lenguaje"
  }
  ```

### `PUT /quiz-questions/{id}` y `DELETE /quiz-questions/{id}`
- **Acceso:** Owner | `ta`

### `PUT /quiz-questions/{id}/answers`
- **Acceso:** Owner | `ta` (Actualiza las opciones de respuesta en lote).

### `POST /quizzes/{id}/attempts`
- **Acceso:** Inscrito
- **Payload:**
  ```json
  {
    "answers": [
      { "quiz_question_id": 1, "answer_text": "Un lenguaje" }
    ]
  }
  ```
- **Respuesta (201):** `{ "score": 10, "passed": true }`

### `GET /quiz-attempts/{id}`
- **Acceso:** Inscrito.

### `POST /practice-quizzes`
- **Acceso:** Auth. Genera un quiz aleatorio.
- **Payload:** `{ "quiz_id": 1, "question_count": 5 }`

### `GET /flashcard-decks` y `POST /flashcard-decks`
- **Acceso:** Auth
- **Payload (POST):** `{ "title": "Mi Mazo AWS", "description": "...", "module_id": null }`

### `GET /flashcard-decks/{id}`, `PUT /flashcard-decks/{id}`, `DELETE /flashcard-decks/{id}`
- **Acceso:** Owner del mazo.

### `POST /flashcard-decks/{id}/flashcards`
- **Acceso:** Owner del mazo.
- **Payload:** `{ "question_text": "EC2?", "answer_text": "Servidor" }`

### `PUT /flashcards/{id}` y `DELETE /flashcards/{id}`
- **Acceso:** Owner del mazo.

### `POST /flashcard-imports`
- **Acceso:** Auth. Importa preguntas falladas de un quiz a un mazo.
- **Payload:** `{ "quiz_id": 1, "deck_id": 1 }`

### `GET /flashcard-decks/{id}/due-flashcards`
- **Acceso:** Owner. Obtiene flashcards que requieren repaso hoy (SM-2 Algorithm).

### `PATCH /flashcards/{id}` (Repaso SM-2)
- **Acceso:** Owner.
- **Payload:** `{ "quality": 4 }` (0=Fallo, 5=Perfecto).

---

## 10. Foros de Q&A

### Listar Hilos
- **`GET /courses/{id}/threads`**
- **`GET /modules/{id}/threads`**
- **`GET /challenges/{id}/threads`**
- **Acceso:** Inscritos.

### Crear Hilos
- **`POST /courses/{id}/threads`**
- **`POST /modules/{id}/threads`**
- **`POST /challenges/{id}/threads`**
- **Payload:** `{ "title": "Ayuda con Python", "body": "No entiendo loops." }`

### `GET /threads/{id}`, `PUT /threads/{id}`, `DELETE /threads/{id}`
- **Acceso:** Inscritos / Moderadores.

### `POST /threads/{id}/posts` (Responder Hilo)
- **Acceso:** Inscritos.
- **Payload:** `{ "body": "Usa un ciclo for." }`

### `PUT /posts/{id}`, `DELETE /posts/{id}`
- **Acceso:** Autor del post / Moderador.

### Votaciones
- **`PUT /threads/{id}/votes/me`**
- **`PUT /posts/{id}/votes/me`**
- **Payload:** `{ "value": 1 }` (1 Upvote, -1 Downvote).

### `PATCH /posts/{id}/accept`
- **Acceso:** Autor del hilo. (Marca respuesta como correcta).

---

## 11. Moderación y Reportes

### `POST /reports`
- **Acceso:** Auth
- **Enums - Type:** `course, forum_thread, forum_post, user` (Deben mandarse tal cual, ej: `forum_thread`).
- **Enums - Reason:** `spam, plagiarism, offensive_language, academic_dishonesty, other`
- **Payload:**
  ```json
  { "reportable_type": "forum_thread", "reportable_id": "uuid-hilo", "reason": "spam", "details": "Enlace dudoso" }
  ```

### Moderadores y Admins (`moderator`, `admin`)
- **`GET /moderator/reports`**: Lista de reportes.
- **`PATCH /reports/{id}/resolve`**: Marca como resuelto.
- **`PATCH /reports/{id}/escalate`**: Escala a admin superior.
- **`PATCH /threads/{id}/pin`**: Fija un hilo en el foro.
- **`PATCH /threads/{id}/lock`**: Bloquea un hilo para no recibir más respuestas.
- **`GET /moderator/response-templates`**: Plantillas pre-hechas para respuestas de moderación.

---

## 12. Administración Global y Soporte

### Solicitudes de Profesores
- **`GET /professor-applications`** (`support|admin`)
- **`PATCH /professor-applications/{id}/assign`** (`support|admin`): `{ "reviewer_id": "uuid" }`
- **`PATCH /professor-applications/{id}/review`** (`support|admin`): `{ "status": "approved" }`

### Gestión de Usuarios (`support|admin`)
- **`GET /support/users`**: Lista todos.
- **`GET /support/users/{id}`**: Perfil.
- **`PATCH /support/users/{id}/deactivate`**: Banea usuario.
- **`PUT /support/users/{id}/role`**: `{ "roles": ["student", "moderator"] }`

### Logs y Ajustes (`admin`)
- **`GET /admin/logs`**: Registro de auditoría.
- **`GET /admin/settings`**: Ajustes del sistema.
- **`PUT /admin/settings/{key}`**: `{ "value": "nuevo valor" }`
- **`POST /admin/response-templates`**: Crear plantillas de soporte.
- **`PUT /admin/response-templates/{id}`**: Editar plantillas.
- **`DELETE /admin/response-templates/{id}`**: Eliminar plantillas.

---

## 13. Notificaciones

### `GET /notifications`
- **Acceso:** Auth. Lista notificaciones del usuario.

### `GET /notifications/unread-count`
- **Acceso:** Auth. `{ "count": 5 }`

### `PATCH /notifications`
- **Acceso:** Auth. Marca todas como leídas.

### `PATCH /notifications/{id}`
- **Acceso:** Auth. Marca una como leída.
