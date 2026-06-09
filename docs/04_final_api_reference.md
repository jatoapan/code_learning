# Prolecom — API Reference (v1)
> Generado y verificado contra Railway Staging. Última sincronización: 2026-06-09.

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

**Throttle global:** `1000 req/min` (staging). En producción se reducirá a `60 req/min`.

---

## 1. Autenticación y Perfil
> Rutas públicas (sin token) y rutas de perfil (requieren token).

| Método | Endpoint | Acceso | Payload / Notas |
|---|---|---|---|
| `POST` | `/users` | Público | `{name, email, password, password_confirmation}` |
| `POST` | `/sessions` | Público | `{email, password, device_name}` → retorna `{token}` |
| `POST` | `/password-reset-links` | Público | `{email}` |
| `POST` | `/password-resets` | Público | `{email, token, password, password_confirmation}` |
| `GET` | `/user` | Auth | Perfil del usuario autenticado |
| `PUT` | `/user` | Auth | `{name?, avatar_path?}` |
| `DELETE` | `/users/me` | Auth | Desactiva y anonimiza la cuenta (SoftDelete) |
| `DELETE` | `/sessions/current` | Auth | Logout — invalida el token JWT actual |

---

## 2. Cursos y Matrículas
> Rutas de solo lectura disponibles para cualquier usuario autenticado. Escritura requiere `professor` o `ta`.

| Método | Endpoint | Acceso | Payload / Notas |
|---|---|---|---|
| `GET` | `/courses` | Auth | Catálogo público. Filtros: `title`, `category` |
| `POST` | `/courses` | `professor\|ta` | `{title, description, status (draft\|public\|unlisted), category}` |
| `GET` | `/courses/{id}` | Auth (inscrito\|owner) | Syllabus del curso con módulos |
| `PUT` | `/courses/{id}` | `professor\|ta` | `{title?, description?, status?, category?}` |
| `DELETE` | `/courses/{id}` | `professor\|ta` | SoftDelete. Solo el owner puede borrar |
| `GET` | `/courses/{id}/stats` | `professor\|ta` | Estadísticas + candidatos a TA por XP |
| `GET` | `/courses/{id}/analytics` | `professor\|ta` | Dashboard analítico (inscritos, progreso, quiz scores) |
| `GET` | `/courses/{id}/leaderboard` | Auth (inscrito) | Ranking por XP local del curso |
| `GET` | `/courses/{id}/progress` | Auth (inscrito) | Progreso ponderado del usuario autenticado |
| `POST` | `/courses/{id}/enrollments` | Auth | Auto-inscripción. Solo funciona en cursos `public` |
| `POST` | `/courses/{id}/enrollments/manual` | `professor\|ta` | `{user_id, role: "student"}` — inscripción forzada |
| `DELETE` | `/courses/{id}/enrollments/me` | Auth (inscrito) | Abandona el curso (status → `dropped`) |
| `POST` | `/courses/{id}/staff-members` | `professor\|ta` | `{user_id, role: "ta"}` — asigna TA al curso |
| `DELETE` | `/courses/{id}/staff/{user_id}` | `professor\|ta` | Remueve TA del curso |

---

## 3. Módulos y Materiales (Syllabus)
> Escritura restringida a `professor|ta`. Lectura a usuarios inscritos.

| Método | Endpoint | Acceso | Payload / Notas |
|---|---|---|---|
| `POST` | `/courses/{id}/modules` | `professor\|ta` | `{title, description?, order}` |
| `PUT` | `/modules/{id}` | `professor\|ta` | `{title?, description?, order?}` |
| `DELETE` | `/modules/{id}` | `professor\|ta` | SoftDelete. Gate verifica ownership del curso |
| `PATCH` | `/modules/{id}/items-order` | `professor\|ta` | `{items: [{id, type, order}]}` — array **no vacío** |
| `POST` | `/modules/{id}/materials` | `professor\|ta` | `{title, type (pdf\|video_link\|ppt\|pptx), content?, file?}` |
| `GET` | `/materials/{id}` | Auth (inscrito) | Detalle del material |
| `PUT` | `/materials/{id}` | `professor\|ta` | `{title?, type?}` |
| `DELETE` | `/materials/{id}` | `professor\|ta` | SoftDelete |
| `GET` | `/materials/{id}/download` | Auth (inscrito) | Descarga binaria o HTTP 302 hacia URL para `video_link` |
| `POST` | `/materials/{id}/views` | Auth (inscrito) | `{}` — registra visualización y suma XP |

---

## 4. Foros y Q&A
> Todos los usuarios inscritos pueden leer y publicar. Moderación requiere `moderator|admin`.

| Método | Endpoint | Acceso | Payload / Notas |
|---|---|---|---|
| `GET` | `/courses/{id}/threads` | Auth (inscrito) | Lista hilos del curso |
| `GET` | `/modules/{id}/threads` | Auth (inscrito) | Lista hilos del módulo |
| `GET` | `/challenges/{id}/threads` | Auth (inscrito) | Lista hilos del reto |
| `POST` | `/courses/{id}/threads` | Auth (inscrito) | `{title, body}` |
| `POST` | `/modules/{id}/threads` | Auth (inscrito) | `{title, body}` |
| `POST` | `/challenges/{id}/threads` | Auth (inscrito) | `{title, body}` |
| `GET` | `/threads/{id}` | Auth (inscrito) | Detalle del hilo con sus posts |
| `PUT` | `/threads/{id}` | Autor del hilo | `{title, body}` — Gate verifica autoría |
| `DELETE` | `/threads/{id}` | Autor / `moderator\|admin` | SoftDelete |
| `PATCH` | `/threads/{id}/pin` | `moderator\|admin` | `{}` — alterna pin |
| `PATCH` | `/threads/{id}/lock` | `moderator\|admin` | `{}` — cierra el hilo |
| `PUT` | `/threads/{id}/votes/me` | Auth (inscrito) | `{value: 1 \| -1}` |
| `POST` | `/threads/{id}/posts` | Auth (inscrito) | `{body, parent_id?}` — crea respuesta |
| `PUT` | `/posts/{id}` | Autor del post | `{body}` — Gate verifica autoría |
| `DELETE` | `/posts/{id}` | Autor / `moderator\|admin` | SoftDelete |
| `PUT` | `/posts/{id}/votes/me` | Auth (inscrito) | `{value: 1 \| -1}` |
| `PATCH` | `/posts/{id}/accept` | Autor del hilo | `{}` — acepta como respuesta correcta |

---

## 5. Quizzes y Flashcards
| Método | Endpoint | Acceso | Payload / Notas |
|---|---|---|---|
| `POST` | `/modules/{id}/quizzes` | `professor\|ta` | `{title, description?, mode (practice\|exam), time_limit_minutes?, passing_score}` |
| `GET` | `/quizzes/{id}` | Auth (inscrito) | Preguntas (sin respuestas correctas para estudiantes) |
| `PUT` | `/quizzes/{id}` | `professor\|ta` | `{title?, mode?, passing_score?, time_limit_minutes?}` |
| `DELETE` | `/quizzes/{id}` | `professor\|ta` | SoftDelete |
| `POST` | `/quizzes/{id}/questions` | `professor\|ta` | `{question_text, type (multiple_choice\|true_false), points, options[], correct_answer}` |
| `PUT` | `/quiz-questions/{id}` | `professor\|ta` | `{question_text?, type?, points?}` |
| `DELETE` | `/quiz-questions/{id}` | `professor\|ta` | Elimina pregunta y sus respuestas |
| `PUT` | `/quiz-questions/{id}/answers` | `professor\|ta` | Reemplaza opciones de respuesta en bulk |
| `POST` | `/quizzes/{id}/attempts` | Auth (inscrito) | `{answers: [{quiz_question_id, answer_text}]}` |
| `GET` | `/quiz-attempts/{id}` | Auth (owner) | Detalle del intento con score y errores |
| `POST` | `/practice-quizzes` | Auth | `{quiz_id, question_count}` — genera quiz aleatorio |
| `GET` | `/flashcard-decks` | Auth | Lista mazos del usuario autenticado |
| `POST` | `/flashcard-decks` | Auth | `{title, description?, module_id?}` |
| `PUT` | `/flashcard-decks/{id}` | Auth (owner) | `{title?, description?}` |
| `DELETE` | `/flashcard-decks/{id}` | Auth (owner) | Elimina mazo y todas sus flashcards |
| `POST` | `/flashcard-decks/{id}/flashcards` | Auth (owner) | `{question_text, answer_text}` |
| `PUT` | `/flashcards/{id}` | Auth (owner) | `{question_text?, answer_text?}` |
| `PATCH` | `/flashcards/{id}` | Auth (owner) | `{quality: 0-5}` — recalcula intervalo SM-2 |
| `DELETE` | `/flashcards/{id}` | Auth (owner) | Elimina la flashcard |
| `POST` | `/flashcard-imports` | Auth | `{deck_id, quiz_id}` — importa preguntas falladas |
| `GET` | `/flashcard-decks/{id}/due-flashcards` | Auth (owner) | Flashcards con `next_review_at <= NOW()` |

---

## 6. Retos y Juez IDE (Judge0)
| Método | Endpoint | Acceso | Payload / Notas |
|---|---|---|---|
| `GET` | `/languages` | Auth | Lista lenguajes soportados en Judge0 |
| `GET` | `/modules/{id}/challenges` | `professor\|ta` | Lista retos del módulo (incluye pendientes de revisión) |
| `POST` | `/modules/{id}/challenges` | `professor\|ta` | `{title, description, difficulty (easy\|medium\|hard), points, language_id, language_name}` |
| `GET` | `/challenges/{id}` | Auth (inscrito) | Detalle del reto. Solo retos `approved` son visibles a estudiantes |
| `PUT` | `/challenges/{id}` | `professor\|ta` | `{title?, description?, difficulty?, points?}` |
| `DELETE` | `/challenges/{id}` | `professor\|ta` | SoftDelete |
| `POST` | `/challenges/{id}/test-cases` | `professor\|ta` | `{input_data, expected_output, is_hidden}` |
| `PUT` | `/challenge-test-cases/{id}` | `professor\|ta` | `{input_data?, expected_output?, is_hidden?}` |
| `DELETE` | `/challenge-test-cases/{id}` | `professor\|ta` | Elimina caso de prueba |
| `POST` | `/challenges/{id}/attempts` | Auth (inscrito) | `{submitted_code, language_id}` — evalúa en Judge0 |
| `GET` | `/challenges/{id}/attempts` | Auth (inscrito\|professor\|ta) | Historial de intentos. Prof/TA ven todos |
| `POST` | `/challenge-attempts/{id}/feedback` | `professor\|ta` | `{feedback}` — feedback manual al código |

---

## 7. Moderación y Soporte
| Método | Endpoint | Acceso | Payload / Notas |
|---|---|---|---|
| `POST` | `/reports` | Auth | `{reportable_type, reportable_id, reason, details?}` |
| `GET` | `/moderator/reports` | `moderator\|admin` | Cola de denuncias pendientes |
| `PATCH` | `/reports/{id}/resolve` | `moderator\|admin` | `{}` — resuelve el reporte |
| `PATCH` | `/reports/{id}/escalate` | `moderator\|admin` | `{}` — escala a soporte |
| `GET` | `/moderator/response-templates` | `moderator\|admin` | Lista plantillas de respuesta |
| `GET` | `/professor-applications` | `support\|admin` | Lista todas las solicitudes de profesor |
| `GET` | `/professor-applications/mine` | Auth | Estado de la propia solicitud activa |
| `POST` | `/professor-applications` | Auth | `{motivation, qualifications?}` |
| `PATCH` | `/professor-applications/{id}/assign` | `support\|admin` | `{reviewer_id}` — asigna revisor |
| `PATCH` | `/professor-applications/{id}/review` | `support\|admin` | `{status (approved\|rejected), comment?}` |
| `GET` | `/support/users` | `support\|admin` | Lista usuarios con filtros |
| `GET` | `/support/users/{id}` | `support\|admin` | Perfil completo de un usuario |
| `PUT` | `/support/users/{id}/role` | `support\|admin` | `{roles: ["student"\|"professor"\|"moderator"\|"support"\|"admin"]}` |
| `PATCH` | `/support/users/{id}/deactivate` | `support\|admin` | `{}` — desactiva cuenta y revoca tokens |

---

## 8. Administración
| Método | Endpoint | Acceso | Payload / Notas |
|---|---|---|---|
| `GET` | `/admin/logs` | `admin` | Bitácora de auditoría paginada |
| `GET` | `/admin/settings` | `admin` | Lista configuraciones del sistema |
| `PUT` | `/admin/settings/{key}` | `admin` | `{value}` — actualiza parámetro (ej: `max_upload_mb`) |
| `POST` | `/admin/response-templates` | `admin` | `{title, body}` |
| `PUT` | `/admin/response-templates/{id}` | `admin` | `{title?, body?}` |
| `DELETE` | `/admin/response-templates/{id}` | `admin` | Elimina plantilla |

---

## 9. Notificaciones
| Método | Endpoint | Acceso | Payload / Notas |
|---|---|---|---|
| `GET` | `/notifications` | Auth | Lista notificaciones del usuario (paginado) |
| `GET` | `/notifications/unread-count` | Auth | Retorna `{count: N}` |
| `PATCH` | `/notifications` | Auth | `{}` — marca todas como leídas |
| `PATCH` | `/notifications/{id}` | Auth | `{}` — marca una como leída |

---

## 10. Utilidades (Testing / DevOps)
| Método | Endpoint | Acceso | Notas |
|---|---|---|---|
| `GET` | `/health` | Público | Retorna `{status: "ok"}` |
| `GET` | `/ping-deploy` | Público | Verifica que los controladores cargaron correctamente |
| `GET` | `/dev-reset-db?token=<secret>` | Token secreto | **Solo staging.** Destruye y re-siembra la BD |

---

## Roles del Sistema (Spatie Laravel Permission)
| Rol | Permisos |
|---|---|
| `admin` | Acceso total a todas las rutas |
| `support` | Gestión de usuarios, aplicaciones de profesor |
| `moderator` | Moderación de reportes, hilos, posts |
| `professor` | CRUD de cursos, módulos, materiales, retos, quizzes |
| `ta` | Igual que professor pero con Gate adicional de ownership |
| `student` | Lectura, inscripción, participación en foros y evaluaciones |

> **Nota BOLA:** Aunque un rol permita el acceso a una ruta, el `Gate::authorize()` a nivel de controlador verifica que el usuario sea efectivamente owner del recurso (curso, mazo, hilo, etc.) antes de ejecutar la operación.
