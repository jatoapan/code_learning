# Prolecom — Database Schema (Final)
> Verificado contra las migraciones activas en Railway PostgreSQL. Última sincronización: 2026-06-09.

## Motor y Convenciones
- **Motor:** PostgreSQL (Railway)
- **PKs UUID:** `users`, `courses`, `challenges`, `challenge_attempts`, `forum_threads`, `forum_posts`
- **PKs Auto-Increment (Int):** Todas las demás tablas
- **SoftDeletes:** `users`, `courses`, `modules`, `materials`, `challenges`, `forum_threads`, `forum_posts`
- **Enums:** Implementados como `string(50)` en BD + PHP 8.1 `enum` en capa de aplicación

---

## 1. Autenticación y Autorización

### `users`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | UUID (PK) | `HasUuids` trait |
| `name` | string(255) | |
| `email` | string(255) | Unique |
| `password` | string(255) | Bcrypt |
| `avatar_path` | string, nullable | |
| `status` | string(50) | Enum: `active`, `suspended`, `banned`, `deactivated` |
| `xp` | unsigned int | Default: 0 — XP global acumulado |
| `institution_id` | bigInt FK, nullable | → `institutions.id` (Set Null) |
| `email_verified_at` | timestamp, nullable | |
| `deleted_at` | timestamp, nullable | SoftDelete / anonimización GDPR |
| `created_at` / `updated_at` | timestamps | |

### Tablas Spatie Laravel-Permission (generadas automáticamente)
- `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`

---

## 2. Instituciones

### `institutions`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `name` | string(255) | Unique |
| `slug` | string(255) | Unique |
| `domain` | string(100), nullable | Ej: `espol.edu.ec` — auto-detección en registro |
| `logo_path` | string, nullable | |
| `website` | string, nullable | |
| `type` | string(50) | `university`, `bootcamp`, `company` |
| `created_at` / `updated_at` | timestamps | |

---

## 3. Cursos y Matrículas

### `courses`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | UUID (PK) | |
| `title` | string(255) | |
| `slug` | string(255) | Index Unique(slug, deleted_at) |
| `description` | text | |
| `image_path` | string, nullable | |
| `status` | string(50) | Enum: `draft`, `public`, `unlisted` |
| `category` | string(50) | Enum: `programming`, `web`, `mobile`, `data_science`, `devops`, `design` |
| `has_leaderboard` | boolean | Default: true |
| `owner_id` | UUID FK, nullable | → `users.id` (Set Null) |
| `deleted_at` | timestamp, nullable | SoftDelete |
| `created_at` / `updated_at` | timestamps | |

### `course_user` (Pivot — Inscripciones y Staff)
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `course_id` | UUID FK | → `courses.id` (Cascade Delete) |
| `user_id` | UUID FK, nullable | → `users.id` (Set Null) |
| `role` | string(50) | `student`, `professor`, `ta` |
| `status` | string(50) | `enrolled`, `completed`, `dropped` |
| `xp` | unsigned int | XP acumulado en este curso (usado para Leaderboard) |
| `progress_percent` | decimal(5,2) | Cacheado por observer. Default: 0.00 |
| `created_at` / `updated_at` | timestamps | |
> Index Unique: `(course_id, user_id)`

---

## 4. Syllabus (Módulos y Materiales)

### `modules`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `course_id` | UUID FK | → `courses.id` (Cascade Delete) |
| `title` | string(255) | |
| `description` | text, nullable | |
| `order` | unsigned int | Default: 0 |
| `prerequisite_module_id` | bigInt FK, nullable | → `modules.id` (Set Null) — para bloqueo secuencial |
| `deleted_at` | timestamp, nullable | SoftDelete |
| `created_at` / `updated_at` | timestamps | |

### `module_items` (Patrón Composite — Orden Unificado del Syllabus)
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `module_id` | bigInt FK | → `modules.id` (Cascade Delete) |
| `itemable_type` | string(255) | `App\Models\Material`, `App\Models\Quiz`, `App\Models\Challenge` |
| `itemable_id` | string(36) | UUID o Int según el tipo |
| `order` | unsigned int | Orden absoluto dentro del módulo |
| `created_at` / `updated_at` | timestamps | |
> Index Unique: `(module_id, itemable_type, itemable_id)`
> **Nota:** `materials` no tiene `module_id` directo. Toda consulta de Syllabus transita por `module_items`.

### `materials`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `title` | string(255) | |
| `description` | text, nullable | |
| `type` | string(50) | Enum: `pdf`, `video_link`, `ppt`, `pptx` |
| `content` | string, nullable | URL para `video_link` |
| `file_path` | string, nullable | Path en Storage para archivos subidos |
| `file_size` | bigInt, nullable | Bytes — validación máx 50MB |
| `creator_id` | UUID FK, nullable | → `users.id` (Set Null) |
| `moderator_endorsed_at` | timestamp, nullable | Aval de moderación |
| `deleted_at` | timestamp, nullable | SoftDelete |
| `created_at` / `updated_at` | timestamps | |

### `material_user` (Seguimiento de materiales vistos)
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `material_id` | bigInt FK | → `materials.id` (Cascade Delete) |
| `user_id` | UUID FK, nullable | → `users.id` (Set Null) |
| `viewed_at` | timestamp | |
> Index Unique: `(material_id, user_id)`

---

## 5. Foros y Q&A

### `forum_threads`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | UUID (PK) | |
| `forumable_type` | string(255) | `App\Models\Course`, `App\Models\Module`, `App\Models\Challenge` |
| `forumable_id` | string(36) | Relación polimórfica |
| `title` | string(255) | |
| `body` | text | Markdown |
| `user_id` | UUID FK, nullable | → `users.id` (Set Null) — nullable para GDPR |
| `status` | string(50) | `open`, `resolved`, `locked`, `hidden` |
| `is_pinned` | boolean | Default: false |
| `vote_score` | int | Caché de votos. Default: 0 |
| `view_count` | unsigned int | Default: 0 |
| `moderator_endorsed_at` | timestamp, nullable | |
| `deleted_at` | timestamp, nullable | SoftDelete |
| `created_at` / `updated_at` | timestamps | |
> Index: `(forumable_type, forumable_id)`

### `forum_posts`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | UUID (PK) | |
| `thread_id` | UUID FK | → `forum_threads.id` (Cascade Delete) |
| `parent_id` | UUID FK, nullable | → `forum_posts.id` (Set Null) — respuestas anidadas |
| `body` | text | Markdown |
| `user_id` | UUID FK, nullable | → `users.id` (Set Null) |
| `is_accepted_answer` | boolean | Default: false |
| `vote_score` | int | Caché. Default: 0 |
| `status` | string(50) | `visible`, `hidden` |
| `moderator_endorsed_at` | timestamp, nullable | |
| `deleted_at` | timestamp, nullable | SoftDelete |
| `created_at` / `updated_at` | timestamps | |

### `votes` (Polimórfica)
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `user_id` | UUID FK, nullable | → `users.id` (Set Null) |
| `votable_type` | string(255) | `App\Models\ForumThread` o `App\Models\ForumPost` |
| `votable_id` | UUID | |
| `vote_type` | tinyInt | `1` (Upvote), `-1` (Downvote) |
| `created_at` / `updated_at` | timestamps | |
> Index Unique: `(user_id, votable_type, votable_id)`

---

## 6. Gamificación e IDE

### `challenges`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | UUID (PK) | |
| `module_id` | bigInt FK | → `modules.id` (Cascade Delete) |
| `title` | string(255) | |
| `description` | text | Enunciado en Markdown |
| `difficulty` | string(50) | Enum: `easy`, `medium`, `hard` |
| `language_id` | unsigned int | ID en Judge0 |
| `language_name` | string(50) | Ej: `Python 3` |
| `starter_code` | text, nullable | Plantilla de código inicial |
| `points` | unsigned int | Default: 10 |
| `status` | string(50) | `draft`, `pending_review`, `approved`, `rejected` |
| `review_feedback` | text, nullable | Retroalimentación al rechazar |
| `creator_id` | UUID FK, nullable | → `users.id` (Set Null) |
| `deleted_at` | timestamp, nullable | SoftDelete |
| `created_at` / `updated_at` | timestamps | |

### `challenge_test_cases`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `challenge_id` | UUID FK | → `challenges.id` (Cascade Delete) |
| `input` | text, nullable | Stdin |
| `expected_output` | text | Stdout esperado |
| `is_hidden` | boolean | Default: false — ocultos al estudiante |
| `created_at` / `updated_at` | timestamps | |

### `challenge_attempts`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | UUID (PK) | |
| `user_id` | UUID FK, nullable | → `users.id` (Set Null) |
| `challenge_id` | UUID FK | → `challenges.id` (Cascade Delete) |
| `submitted_code` | text | |
| `language_id` | unsigned int | |
| `status` | string(50) | `pending`, `accepted`, `wrong_answer`, `compile_error`, `runtime_error`, `time_limit_exceeded` |
| `test_cases_passed` | unsigned int | Default: 0 |
| `test_cases_total` | unsigned int | Default: 0 |
| `points_awarded` | unsigned int | Default: 0 |
| `execution_time_ms` | unsigned int, nullable | |
| `execution_memory_kb` | unsigned int, nullable | |
| `stdout` | text, nullable | |
| `stderr` | text, nullable | |
| `feedback` | text, nullable | Comentario del profesor |
| `created_at` / `updated_at` | timestamps | |

### `quizzes`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `title` | string(255) | |
| `description` | text, nullable | |
| `mode` | string(50) | Enum: `practice`, `exam` |
| `time_limit_minutes` | unsigned int, nullable | |
| `passing_score` | decimal(5,2) | Default: 60.00 |
| `random_question_limit` | unsigned int, nullable | Pool aleatorio de preguntas |
| `status` | string(50) | `draft`, `published` |
| `answers_visible_after` | timestamp, nullable | Solo modo `exam` |
| `deleted_at` | timestamp, nullable | SoftDelete |
| `created_at` / `updated_at` | timestamps | |

### `quiz_questions`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `quiz_id` | bigInt FK | → `quizzes.id` (Cascade Delete) |
| `question_text` | text | |
| `type` | string(50) | Enum: `multiple_choice`, `true_false` |
| `points` | unsigned int | Default: 1 |
| `options` | JSON | Array de opciones de respuesta |
| `correct_answer` | string | Respuesta correcta |
| `explanation` | text, nullable | Retroalimentación al revisar errores |
| `created_at` / `updated_at` | timestamps | |

### `quiz_attempts`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `user_id` | UUID FK, nullable | → `users.id` (Set Null) |
| `quiz_id` | bigInt FK | → `quizzes.id` (Cascade Delete) |
| `score` | decimal(5,2) | Nota sobre 100 |
| `passed` | boolean | Basado en `passing_score` |
| `questions_snapshot` | JSON, nullable | IDs de preguntas presentadas |
| `started_at` | timestamp | |
| `completed_at` | timestamp, nullable | |
| `created_at` / `updated_at` | timestamps | |

### `flashcard_decks`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `user_id` | UUID FK, nullable | → `users.id` (Set Null) |
| `module_id` | bigInt FK, nullable | → `modules.id` (Set Null) |
| `title` | string(255) | |
| `description` | text, nullable | |
| `created_at` / `updated_at` | timestamps | |

### `flashcards` (Algoritmo SuperMemo SM-2)
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `flashcard_deck_id` | bigInt FK | → `flashcard_decks.id` (Cascade Delete) |
| `question_text` | text | Frente |
| `answer_text` | text | Reverso |
| `source_question_id` | bigInt FK, nullable | → `quiz_questions.id` (Set Null) |
| `next_review_at` | timestamp | Default: NOW() |
| `interval` | unsigned int | Días de espera. Default: 0 |
| `repetitions` | unsigned int | Repeticiones exitosas. Default: 0 |
| `ease_factor` | decimal(5,2) | Factor SM-2. Default: 2.50 |
| `created_at` / `updated_at` | timestamps | |

---

## 7. Administración y Sistema

### `reports` (Polimórfica)
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `reporter_id` | UUID FK, nullable | → `users.id` (Set Null) |
| `reportable_type` | string(255) | `App\Models\ForumThread`, `App\Models\ForumPost`, `App\Models\User` |
| `reportable_id` | string(36) | |
| `reason` | string(50) | `spam`, `plagiarism`, `offensive_language`, `academic_dishonesty`, `other` |
| `details` | text, nullable | |
| `status` | string(50) | `pending`, `resolved`, `escalated`, `dismissed` |
| `resolved_by` | UUID FK, nullable | → `users.id` (Set Null) |
| `resolution_note` | text, nullable | |
| `resolved_at` | timestamp, nullable | |
| `created_at` / `updated_at` | timestamps | |

### `professor_applications`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `applicant_id` | UUID FK, nullable | → `users.id` (Set Null) |
| `reviewer_id` | UUID FK, nullable | → `users.id` (Set Null) |
| `status` | string(50) | `pending`, `under_review`, `approved`, `rejected` |
| `motivation` | text | |
| `qualifications` | text, nullable | |
| `reviewer_comment` | text, nullable | |
| `reviewed_at` | timestamp, nullable | |
| `created_at` / `updated_at` | timestamps | |
> Index: `(applicant_id, status)`

### `system_settings`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `key` | string(100) | Unique. Ej: `max_upload_mb`, `maintenance_mode` |
| `value` | text | Claves sensibles cifradas con `Crypt::encryptString()` |
| `description` | string(255), nullable | |
| `created_at` / `updated_at` | timestamps | |

### `response_templates`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `title` | string(100) | |
| `body` | text | |
| `created_at` / `updated_at` | timestamps | |

### `admin_logs` (activity_logs)
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigInt (PK, AI) | |
| `user_id` | UUID FK, nullable | → `users.id` (Set Null) |
| `action` | string(100) | |
| `target_type` | string(255), nullable | |
| `target_id` | string(36), nullable | |
| `details` | JSON, nullable | |
| `ip_address` | string(45), nullable | |
| `created_at` | timestamp | |

### `notifications` (Nativa Laravel)
| Columna | Tipo | Notas |
|---|---|---|
| `id` | UUID (PK) | |
| `type` | string(255) | Clase de notificación |
| `notifiable_type` | string(255) | `App\Models\User` |
| `notifiable_id` | string(36) | UUID del usuario |
| `data` | JSON | Payload del evento |
| `read_at` | timestamp, nullable | |
| `created_at` / `updated_at` | timestamps | |

---

## 8. Relaciones Polimórficas Resumen

| Tabla | Columnas polimórficas | Tipos posibles |
|---|---|---|
| `forum_threads` | `forumable_type`, `forumable_id` | `Course`, `Module`, `Challenge` |
| `module_items` | `itemable_type`, `itemable_id` | `Material`, `Quiz`, `Challenge` |
| `votes` | `votable_type`, `votable_id` | `ForumThread`, `ForumPost` |
| `reports` | `reportable_type`, `reportable_id` | `ForumThread`, `ForumPost`, `User` |

---

## 9. Notas de Seguridad

- **BOLA/IDOR:** El `Gate::authorize()` en cada controlador verifica que el usuario sea propietario del recurso antes de ejecutar cualquier mutación, independientemente del rol.
- **SoftDeletes + GDPR:** Al desactivar un usuario se ejecuta SoftDelete + anonimización inmediata. Los contenidos del foro conservan `user_id = NULL` para preservar la integridad referencial del hilo.
- **Cascade Delete:** Módulos → module_items → eliminación en cascada. Cursos → course_user en cascada.
- **Rate Limiting:** Staging configurado en `1000 req/min`. Producción deberá reducirse a `60 req/min` general y `10 req/min` para Judge0 attempts.
