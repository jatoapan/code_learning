# Prolecom — Database Schema (Final v2)
> Documentación Exhaustiva: 37 Tablas (Fase 1 y 2). Generado directamente desde las migraciones finales de Laravel.

## Resumen del Motor
- **Motor Base:** PostgreSQL
- **Llaves Foráneas:** ON DELETE CASCADE en relaciones fuertes (ej: ítems de módulos, posts de hilos), SET NULL en usuarios borrados.
- **Tipos de Datos Críticos:** 
  - IDs Principales (`users`, `courses`, `challenges`, `forum_threads`, `forum_posts`, etc.) son **UUIDs**.
  - IDs Secundarios (`modules`, `quizzes`, pivot tables) son **BigInt (Auto-Increment)**.

---

## I. Tablas del Framework y Configuración (9 Tablas)

Estas tablas son generadas por Laravel y Spatie, o por nuestro sistema interno de configuración.

1. **`migrations`**: Registra los archivos de migración ejecutados. (Nativa Laravel)
2. **`jobs`**: Cola de trabajos asíncronos en segundo plano. (Campos: `id, queue, payload, attempts, reserved_at, available_at, created_at`).
3. **`job_batches`**: Lotes de trabajos de colas agrupados.
4. **`failed_jobs`**: Registros de trabajos fallidos. (Campos: `uuid, connection, queue, payload, exception, failed_at`).
5. **`password_reset_tokens`**: (Campos: `email (PK), token, created_at`). Usada para reseteo seguro.
6. **`personal_access_tokens`**: Guarda los JWT/Tokens Sanctum. (Campos: `id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at...`).
7. **`activity_logs`**: Bitácora de auditoría. (Campos: `id, user_id (UUID), action, description, ip_address, user_agent, created_at, updated_at`).
8. **`system_settings`**: Ajustes dinámicos. (Campos: `id, key (string), value (text), type, description, timestamps`).
9. **`notifications`**: Notificaciones polimórficas de Laravel. (Campos: `id (UUID), type, notifiable_type, notifiable_id, data (json), read_at, timestamps`).

---

## II. Tablas de Seguridad y Roles - Spatie (5 Tablas)

10. **`permissions`**: `id (BigInt), name, guard_name, timestamps`.
11. **`roles`**: `id (BigInt), name, guard_name, timestamps`. (Roles: `student`, `professor`, `ta`, `moderator`, `support`, `admin`).
12. **`model_has_permissions`**: `permission_id, model_type, model_id (UUID)`.
13. **`model_has_roles`**: `role_id, model_type, model_id (UUID)`.
14. **`role_has_permissions`**: `permission_id, role_id`.

---

## III. Core y Usuarios (3 Tablas)

15. **`institutions`**
    - `id` (bigInt, PK, AI)
    - `name` (string, unique)
    - `slug` (string, unique)
    - `domain` (string, nullable) - Auto detección de correo.
    - `logo_path`, `website` (string, nullable)
    - `type` (string: `university, bootcamp, company`)
    - `timestamps`

16. **`users`**
    - `id` (UUID, PK)
    - `name` (string)
    - `email` (string, unique)
    - `password` (string, bcrypt)
    - `avatar_path` (string, nullable)
    - `status` (string: `active, suspended, banned, deactivated`)
    - `xp` (unsigned int, default 0) - Experiencia global.
    - `institution_id` (bigInt FK -> institutions.id)
    - `email_verified_at` (timestamp, nullable)
    - `deleted_at` (SoftDelete)
    - `timestamps`

17. **`professor_applications`**
    - `id` (bigInt, PK)
    - `user_id` (UUID FK -> users.id)
    - `motivation` (text)
    - `qualifications` (text, nullable)
    - `status` (string: `pending, under_review, approved, rejected`)
    - `reviewer_id` (UUID FK -> users.id, nullable)
    - `review_comment` (text, nullable)
    - `timestamps`

---

## IV. Cursos y Syllabus (6 Tablas)

18. **`courses`**
    - `id` (UUID, PK)
    - `title` (string)
    - `slug` (string, unique)
    - `description` (text)
    - `image_path` (string, nullable)
    - `status` (string: `draft, public, unlisted`)
    - `category` (string: `programming, web, mobile, data_science, devops, design`)
    - `has_leaderboard` (boolean)
    - `owner_id` (UUID FK -> users.id)
    - `deleted_at`, `timestamps`

19. **`course_user`** (Pivote Matrícula y Staff)
    - `id` (bigInt, PK)
    - `course_id` (UUID FK -> courses.id)
    - `user_id` (UUID FK -> users.id)
    - `role` (string: `student, professor, ta`)
    - `status` (string: `enrolled, completed, dropped`)
    - `xp` (unsigned int) - Experiencia local del curso.
    - `progress_percent` (decimal 5,2)
    - `timestamps`

20. **`modules`**
    - `id` (bigInt, PK)
    - `course_id` (UUID FK -> courses.id)
    - `title` (string)
    - `description` (text, nullable)
    - `order` (unsigned int)
    - `prerequisite_module_id` (bigInt FK -> modules.id, nullable)
    - `deleted_at`, `timestamps`

21. **`module_items`** (Polimórfica para Orden Unificado)
    - `id` (bigInt, PK)
    - `module_id` (bigInt FK -> modules.id)
    - `itemable_type` (string: `App\Models\Material`, `App\Models\Challenge`, `App\Models\Quiz`)
    - `itemable_id` (string/int)
    - `order` (unsigned int)
    - `timestamps`

22. **`materials`**
    - `id` (bigInt, PK)
    - `title` (string)
    - `type` (string: `pdf, video_link, ppt, pptx`)
    - `content` (string/url)
    - `creator_id` (UUID FK -> users.id)
    - `deleted_at`, `timestamps`

23. **`material_user`** (Pivote Vistas)
    - `id` (bigInt, PK)
    - `material_id` (bigInt FK -> materials.id)
    - `user_id` (UUID FK -> users.id)
    - `viewed_at` (timestamp)
    - `xp_awarded` (boolean)
    - `timestamps`

---

## V. Retos Interactivos / Judge0 (3 Tablas)

24. **`challenges`**
    - `id` (UUID, PK)
    - `module_id` (bigInt FK -> modules.id)
    - `title` (string)
    - `description` (text)
    - `difficulty` (string: `easy, medium, hard`)
    - `points` (unsigned int)
    - `language_id` (int, Ej: 71 para Python en Judge0)
    - `language_name` (string)
    - `starter_code` (text, nullable)
    - `status` (string: `draft, pending_review, approved, rejected`)
    - `review_feedback` (text, nullable)
    - `creator_id` (UUID FK -> users.id)
    - `deleted_at`, `timestamps`

25. **`challenge_test_cases`**
    - `id` (bigInt, PK)
    - `challenge_id` (UUID FK -> challenges.id)
    - `input` (text)
    - `expected_output` (text)
    - `is_hidden` (boolean, default false)
    - `timestamps`

26. **`challenge_attempts`**
    - `id` (UUID, PK)
    - `challenge_id` (UUID FK -> challenges.id)
    - `user_id` (UUID FK -> users.id)
    - `submitted_code` (text)
    - `language_id` (int)
    - `status` (string: `pending, passed, failed, error`)
    - `score` (unsigned int)
    - `judge0_token` (string, nullable)
    - `timestamps`

---

## VI. Quizzes y Flashcards (8 Tablas)

27. **`quizzes`**
    - `id` (bigInt, PK)
    - `module_id` (bigInt FK -> modules.id)
    - `title` (string)
    - `description` (text, nullable)
    - `mode` (string: `practice, exam`)
    - `time_limit_minutes` (int, nullable)
    - `passing_score` (int)
    - `creator_id` (UUID FK -> users.id)
    - `deleted_at`, `timestamps`

28. **`quiz_questions`**
    - `id` (bigInt, PK)
    - `quiz_id` (bigInt FK -> quizzes.id)
    - `question_text` (text)
    - `type` (string: `multiple_choice, true_false`)
    - `points` (int)
    - `correct_answer` (text)
    - `timestamps`

29. **`quiz_answers`** (Opciones)
    - `id` (bigInt, PK)
    - `quiz_question_id` (bigInt FK -> quiz_questions.id)
    - `option_text` (text)
    - `timestamps`

30. **`quiz_attempts`**
    - `id` (bigInt, PK)
    - `quiz_id` (bigInt FK -> quizzes.id)
    - `user_id` (UUID FK -> users.id)
    - `score` (int)
    - `passed` (boolean)
    - `timestamps`

31. **`quiz_attempt_answers`**
    - `id` (bigInt, PK)
    - `quiz_attempt_id` (bigInt FK -> quiz_attempts.id)
    - `quiz_question_id` (bigInt FK -> quiz_questions.id)
    - `user_answer` (text)
    - `is_correct` (boolean)
    - `timestamps`

32. **`flashcard_decks`**
    - `id` (bigInt, PK)
    - `user_id` (UUID FK -> users.id)
    - `module_id` (bigInt FK -> modules.id, nullable)
    - `title` (string)
    - `description` (text, nullable)
    - `timestamps`

33. **`flashcards`**
    - `id` (bigInt, PK)
    - `deck_id` (bigInt FK -> flashcard_decks.id)
    - `question_text` (text)
    - `answer_text` (text)
    - `ease_factor` (decimal 5,2) - Algoritmo SM-2
    - `interval` (int)
    - `repetitions` (int)
    - `next_review_at` (timestamp)
    - `timestamps`

---

## VII. Foros, Q&A y Moderación (5 Tablas)

34. **`forum_threads`**
    - `id` (UUID, PK)
    - `forumable_type` (string: `App\Models\Course`, `App\Models\Module`, `App\Models\Challenge`)
    - `forumable_id` (string/int)
    - `author_id` (UUID FK -> users.id)
    - `title` (string)
    - `body` (text)
    - `is_pinned` (boolean)
    - `is_locked` (boolean)
    - `upvotes`, `downvotes` (int)
    - `deleted_at`, `timestamps`

35. **`forum_posts`**
    - `id` (UUID, PK)
    - `thread_id` (UUID FK -> forum_threads.id)
    - `author_id` (UUID FK -> users.id)
    - `parent_id` (UUID FK -> forum_posts.id, nullable)
    - `body` (text)
    - `is_correct_answer` (boolean)
    - `upvotes`, `downvotes` (int)
    - `deleted_at`, `timestamps`

36. **`votes`** (Pivote Polimórfico de Up/Downvotes)
    - `id` (bigInt, PK)
    - `user_id` (UUID FK -> users.id)
    - `votable_type` (string: `App\Models\ForumThread`, `App\Models\ForumPost`)
    - `votable_id` (UUID)
    - `value` (tinyint: `1` o `-1`)
    - `timestamps`

37. **`reports`**
    - `id` (bigInt, PK)
    - `reporter_id` (UUID FK -> users.id)
    - `reportable_type` (string: `course, forum_thread, forum_post, user`)
    - `reportable_id` (string/int)
    - `reason` (string: `spam, plagiarism, offensive_language, academic_dishonesty, other`)
    - `details` (text, nullable)
    - `status` (string: `pending, resolved, escalated, dismissed`)
    - `resolved_by` (UUID FK -> users.id, nullable)
    - `resolved_at` (timestamp, nullable)
    - `timestamps`
