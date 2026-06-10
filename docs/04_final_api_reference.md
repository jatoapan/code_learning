# Prolecom — API Reference (v1)
> Generado y verificado. Refleja la Fase 1 y Fase 2.

## Base URL
```
https://code-learning-staging.up.railway.app/api/v1
```

## Convenciones Globales
| Header | Valor |
|---|---|
| `Authorization` | `Bearer <JWT>` (todas las rutas protegidas) |
| `Content-Type` | `application/json` |
| `Accept` | `application/json` |

---

## 1. Autenticación y Perfil

### `POST /users` (Registro Público)
- **Payload:**
  ```json
  { "name": "Usuario", "email": "correo@gmail.com", "password": "password123", "password_confirmation": "password123" }
  ```
- **Response (201):**
  ```json
  { "message": "User registered successfully", "data": { "id": "uuid...", "name": "Usuario", "email": "correo@gmail.com", "roles": ["student"] } }
  ```

### `POST /sessions` (Login)
- **Payload:**
  ```json
  { "email": "correo@gmail.com", "password": "password123", "device_name": "web" }
  ```
- **Response (200):**
  ```json
  { "token": "1|xyz..." }
  ```

### `DELETE /sessions/current` (Logout)
- **Auth:** Requiere Token.
- **Response (200):** `{ "message": "Logged out" }`

### `POST /password-reset-links` (Recuperar Clave)
- **Payload:** `{ "email": "correo@gmail.com" }`
- **Response (200):** `{ "message": "Si el correo existe, se enviará el enlace." }`

### `POST /password-resets` (Cambiar Clave)
- **Payload:** `{ "email": "correo@gmail.com", "token": "token_del_correo", "password": "new_password", "password_confirmation": "new_password" }`
- **Response (200):** `{ "message": "Contraseña actualizada" }`

### `GET /user` (Perfil Actual)
- **Auth:** Requiere Token.
- **Response (200):** `{ "data": { "id": "uuid...", "name": "...", "email": "...", "roles": ["student"] } }`

### `PUT /user` (Actualizar Perfil)
- **Auth:** Requiere Token.
- **Payload:** `{ "name": "Nuevo Nombre", "avatar_path": "https://url.com/avatar.jpg" }`

### `DELETE /users/me` (Eliminar Cuenta)
- **Auth:** Requiere Token. (SoftDelete)

---

## 2. Cursos y Matrículas

### `GET /courses` (Listar Cursos Públicos)
- **Auth:** Requiere Token.
- **Query Params:** `?category=programming`
- **Response (200):**
  ```json
  {
    "data": [
      { "id": "uuid...", "title": "...", "category": "programming", "status": "public", "owner": { "name": "Profesor" } }
    ]
  }
  ```

### `POST /courses` (Crear Curso)
- **Auth:** `professor | ta`
- **Categorías (Enums):** `programming, web, mobile, data_science, devops, design`
- **Status (Enums):** `draft, public, unlisted`
- **Payload:**
  ```json
  { "title": "Introducción", "description": "...", "status": "draft", "category": "programming" }
  ```
- **Response (201):** `{ "message": "Course created", "data": { ... } }`

### `GET /courses/{id}` (Ver Curso)
- **Auth:** Inscrito o Owner. Retorna detalles y syllabus.

### `PUT /courses/{id}` y `DELETE /courses/{id}`
- **Auth:** `professor | ta` (DELETE solo Owner).
- **Payload PUT:** `{ "title": "Nuevo", "status": "public", "category": "web" }`

### `GET /courses/{id}/stats`
- **Auth:** `professor | ta`
- **Response (200):** `{ "total_students": 5, "average_progress": 20.5, "top_students": [...] }`

### `GET /courses/{id}/leaderboard` y `GET /courses/{id}/progress`
- **Auth:** Inscrito.
- **Response Leaderboard:** `{ "leaderboard": [ { "user": "Juan", "xp": 100 } ] }`

### `POST /courses/{id}/enrollments` (Auto-Inscripción)
- **Auth:** Token. Solo para cursos `public`.
- **Response (201):** `{ "message": "Enrolled successfully" }`

### `POST /courses/{id}/enrollments/manual` y `POST /courses/{id}/staff-members`
- **Auth:** `professor | ta`
- **Payload:** `{ "user_id": "uuid...", "role": "student" }` (o `role: "ta"`)
- **Response (201/200):** Agrega manualmente a un usuario.

### `DELETE /courses/{id}/enrollments/me` y `DELETE /courses/{id}/staff/{user_id}`
- **Auth:** Token. Salir del curso o remover TA.

---

## 3. Módulos y Materiales (Syllabus)

### `POST /courses/{id}/modules`
- **Auth:** `professor | ta`
- **Payload:** `{ "title": "Módulo 1", "description": "Bases", "order": 1 }`
- **Response (201):** `{ "data": { "id": 1, "title": "Módulo 1" } }`

### `PUT /modules/{id}` y `DELETE /modules/{id}`
- **Auth:** `professor | ta`. Edita/Elimina el módulo.

### `PATCH /modules/{id}/items-order`
- **Auth:** `professor | ta`
- **Payload:**
  ```json
  { "items": [ { "id": 1, "type": "material", "order": 1 } ] }
  ```

### `POST /modules/{id}/materials`
- **Auth:** `professor | ta`
- **Tipos (Enums):** `pdf, video_link, ppt, pptx`
- **Payload:**
  ```json
  { "title": "Clase 1", "type": "video_link", "content": "https://youtube.com/..." }
  ```

### `GET /materials/{id}` y `GET /materials/{id}/download`
- **Auth:** Inscrito. Download redirige a S3/YouTube o descarga binaria.

### `POST /materials/{id}/views`
- **Auth:** Inscrito. Registra vista y otorga XP.

---

## 4. Retos Interactivos (Challenges)

### `POST /modules/{id}/challenges`
- **Auth:** `professor | ta`
- **Dificultad (Enums):** `easy, medium, hard`
- **Status (Enums):** `draft, pending_review, approved, rejected`
- **Payload:**
  ```json
  { "title": "Suma", "description": "...", "difficulty": "easy", "points": 50, "language_id": 71, "language_name": "python", "status": "draft" }
  ```

### `GET /challenges/{id}` y `PUT /challenges/{id}` y `DELETE /challenges/{id}`
- **Auth:** Inscrito (GET solo `approved`). PUT/DELETE requiere `professor | ta`.

### `POST /challenges/{id}/test-cases`
- **Auth:** `professor | ta`
- **Payload:** `{ "input": "2,2", "expected_output": "4", "is_hidden": true }`

### `POST /challenges/{id}/attempts` (Ejecutar en Judge0)
- **Auth:** Inscrito.
- **Payload:** `{ "submitted_code": "print(4)", "language_id": 71 }`
- **Response (201):**
  ```json
  { "status": "passed", "score": 50, "details": [...] }
  ```

### `GET /challenges/{id}/attempts`
- **Auth:** Inscrito (solo suyos) o Prof/TA (todos).

---

## 5. Quizzes y Flashcards

### `POST /modules/{id}/quizzes`
- **Auth:** `professor | ta`
- **Modos:** `practice, exam`
- **Payload:** `{ "title": "Examen", "mode": "exam", "passing_score": 70, "time_limit_minutes": 60 }`

### `POST /quizzes/{id}/questions`
- **Auth:** `professor | ta`
- **Tipos:** `multiple_choice, true_false`
- **Payload:** `{ "question_text": "A?", "type": "multiple_choice", "points": 10, "options": ["A","B"], "correct_answer": "A" }`

### `POST /quizzes/{id}/attempts`
- **Auth:** Inscrito.
- **Payload:** `{ "answers": [ { "quiz_question_id": 1, "answer_text": "A" } ] }`
- **Response (201):** `{ "score": 100, "passed": true }`

### `POST /practice-quizzes`
- **Auth:** Token.
- **Payload:** `{ "quiz_id": 1, "question_count": 5 }` — Quiz aleatorio para practicar.

### `POST /flashcard-decks` y `POST /flashcard-decks/{id}/flashcards`
- **Auth:** Token.
- **Payload (Deck):** `{ "title": "AWS" }`
- **Payload (Card):** `{ "question_text": "EC2?", "answer_text": "Servidor" }`

### `GET /flashcard-decks/{id}/due-flashcards`
- **Auth:** Owner. Obtiene flashcards que requieren repaso hoy (SM-2 Algorithm).

### `PATCH /flashcards/{id}` (Repaso SM-2)
- **Auth:** Owner.
- **Payload:** `{ "quality": 4 }` (0=Fallo, 5=Perfecto). Calcula el `interval` y `next_review_at`.

---

## 6. Foros y Q&A

### `POST /courses/{id}/threads`, `/modules/{id}/threads`, `/challenges/{id}/threads`
- **Auth:** Inscrito.
- **Payload:** `{ "title": "Tengo un problema", "body": "En la línea 5..." }`

### `POST /threads/{id}/posts` (Responder)
- **Auth:** Inscrito.
- **Payload:** `{ "body": "Prueba haciendo esto..." }`

### `PUT /threads/{id}/votes/me` y `PUT /posts/{id}/votes/me` (Votar)
- **Auth:** Inscrito.
- **Payload:** `{ "value": 1 }` (1 para Upvote, -1 para Downvote).

### `PATCH /posts/{id}/accept`
- **Auth:** Autor del hilo.
- **Payload:** `{}` (Marca el post como solución correcta).

### `PATCH /threads/{id}/pin` y `PATCH /threads/{id}/lock`
- **Auth:** `moderator | admin`. Fija o cierra el hilo.

---

## 7. Moderación, Reportes y Administración

### `POST /reports`
- **Auth:** Token.
- **Reportables:** `course, forum_thread, forum_post, user`
- **Razones:** `spam, plagiarism, offensive_language, academic_dishonesty, other`
- **Payload:**
  ```json
  { "reportable_type": "forum_thread", "reportable_id": "uuid...", "reason": "spam", "details": "Enlace fraudulento" }
  ```

### `GET /moderator/reports` y `PATCH /reports/{id}/resolve`
- **Auth:** `moderator | admin`.

### `POST /professor-applications` y `PATCH /professor-applications/{id}/review`
- **Auth:** Token (para POST), `admin` (para PATCH).
- **Payload:** `{ "motivation": "Quiero ser prof." }`

### `PUT /support/users/{id}/role` y `PATCH /support/users/{id}/deactivate`
- **Auth:** `admin | support`. Modifica roles (`student, professor, ta, moderator, support, admin`) o banea usuarios.

### `GET /admin/settings` y `PUT /admin/settings/{key}`
- **Auth:** `admin`. Ajusta variables globales de la API.

---

## 8. Notificaciones

### `GET /notifications` y `GET /notifications/unread-count`
- **Auth:** Token.

### `PATCH /notifications` y `PATCH /notifications/{id}`
- **Auth:** Token. Marca notificaciones como leídas.

---

## 9. Utilidades DevOps

### `GET /health` y `GET /ping-deploy`
- **Auth:** Ninguna. (Monitor de salud API).

### `GET /dev-reset-db?token=<secreto>`
- **Auth:** Exclusivo de entorno Staging/Testing. Limpia la base de datos por completo.
