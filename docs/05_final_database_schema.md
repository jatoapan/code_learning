# Prolecom — Database Schema (Final)
> Esquema exacto de base de datos PostgreSQL. Refleja Fase 1 y Fase 2.

## Convenciones Globales
- **Motor:** PostgreSQL (Railway)
- **UUIDs PKs:** `users`, `courses`, `challenges`, `challenge_attempts`, `forum_threads`, `forum_posts`
- **Soft Deletes:** Implementado en entidades principales (`deleted_at`).
- **Enums:** Se manejan mediante validación en aplicación (Backed Enums de PHP) y se almacenan como `string`.
- **Tablas Polimórficas:** `reports`, `module_items`, `forum_threads`

---

## 1. Tablas Core y Autenticación

### `users`
- `id` (UUID, PK)
- `name` (string)
- `email` (string, unique)
- `password` (string)
- `avatar_path` (string, nullable)
- `status` (string: `active`, `suspended`, `banned`, `deactivated`)
- `xp` (unsigned int, default 0)
- `institution_id` (bigInt FK -> institutions.id, nullable)
- `email_verified_at` (timestamp, nullable)
- `deleted_at` (timestamp, nullable)
- `created_at`, `updated_at`

### `password_reset_tokens`
- `email` (string, PK)
- `token` (string)
- `created_at` (timestamp)

### `institutions`
- `id` (bigInt, PK)
- `name` (string, unique)
- `slug` (string, unique)
- `domain` (string, nullable)
- `type` (string: `university`, `bootcamp`, `company`)
- `created_at`, `updated_at`

*(Las autorizaciones se basan en Spatie Permission: `roles`, `permissions`, `model_has_roles`, `role_has_permissions`, etc.)*

---

## 2. Cursos y Syllabus

### `courses`
- `id` (UUID, PK)
- `title` (string)
- `slug` (string, unique)
- `description` (text)
- `image_path` (string, nullable)
- `status` (string: `draft`, `public`, `unlisted`)
- `category` (string: `programming`, `web`, `mobile`, `data_science`, `devops`, `design`)
- `has_leaderboard` (boolean, default true)
- `owner_id` (UUID FK -> users.id, nullable)
- `deleted_at`, `created_at`, `updated_at`

### `course_user` (Pivot de Matrículas / Staff)
- `id` (bigInt, PK)
- `course_id` (UUID FK -> courses.id, onDelete Cascade)
- `user_id` (UUID FK -> users.id, onDelete Cascade)
- `role` (string: `student`, `professor`, `ta`)
- `status` (string: `enrolled`, `completed`, `dropped`)
- `xp` (unsigned int, default 0)
- `progress_percent` (decimal, default 0.00)
- `created_at`, `updated_at`

### `modules`
- `id` (bigInt, PK)
- `course_id` (UUID FK -> courses.id, onDelete Cascade)
- `title` (string)
- `description` (text, nullable)
- `order` (unsigned int)
- `deleted_at`, `created_at`, `updated_at`

### `module_items` (Polimórfica)
- `id` (bigInt, PK)
- `module_id` (bigInt FK -> modules.id, onDelete Cascade)
- `itemable_type` (string: `App\Models\Material`, `App\Models\Challenge`, `App\Models\Quiz`)
- `itemable_id` (string/int)
- `order` (unsigned int)

### `materials`
- `id` (bigInt, PK)
- `title` (string)
- `type` (string: `pdf`, `video_link`, `ppt`, `pptx`)
- `content` (string/url)
- `creator_id` (UUID FK -> users.id)
- `deleted_at`, `created_at`, `updated_at`

---

## 3. Retos Interactivos

### `challenges`
- `id` (UUID, PK)
- `module_id` (bigInt FK -> modules.id)
- `title` (string)
- `description` (text)
- `difficulty` (string: `easy`, `medium`, `hard`)
- `points` (unsigned int)
- `language_id` (int, ID del sistema Judge0, ej: 71 para Python)
- `language_name` (string)
- `starter_code` (text, nullable)
- `status` (string: `draft`, `pending_review`, `approved`, `rejected`)
- `review_feedback` (text, nullable)
- `creator_id` (UUID FK -> users.id)
- `deleted_at`, `created_at`, `updated_at`

### `challenge_test_cases`
- `id` (bigInt, PK)
- `challenge_id` (UUID FK -> challenges.id, onDelete Cascade)
- `input` (text)
- `expected_output` (text)
- `is_hidden` (boolean, default false)

### `challenge_attempts`
- `id` (UUID, PK)
- `challenge_id` (UUID FK -> challenges.id, onDelete Cascade)
- `user_id` (UUID FK -> users.id)
- `submitted_code` (text)
- `language_id` (int)
- `status` (string: `pending`, `passed`, `failed`, `error`)
- `score` (unsigned int)
- `judge0_token` (string, nullable)
- `created_at`, `updated_at`

---

## 4. Quizzes y Flashcards

### `quizzes`
- `id` (bigInt, PK)
- `module_id` (bigInt FK -> modules.id)
- `title` (string)
- `description` (text, nullable)
- `mode` (string: `practice`, `exam`)
- `time_limit_minutes` (int, nullable)
- `passing_score` (int, default 70)
- `creator_id` (UUID FK -> users.id)

### `quiz_questions`
- `id` (bigInt, PK)
- `quiz_id` (bigInt FK -> quizzes.id, onDelete Cascade)
- `question_text` (text)
- `type` (string: `multiple_choice`, `true_false`)
- `points` (int)
- `correct_answer` (text)

### `quiz_question_options`
- `id` (bigInt, PK)
- `quiz_question_id` (bigInt FK -> quiz_questions.id, onDelete Cascade)
- `option_text` (text)

### `quiz_attempts`
- `id` (bigInt, PK)
- `quiz_id` (bigInt FK -> quizzes.id)
- `user_id` (UUID FK -> users.id)
- `score` (int)
- `passed` (boolean)

### `flashcard_decks`
- `id` (bigInt, PK)
- `user_id` (UUID FK -> users.id)
- `module_id` (bigInt FK -> modules.id, nullable)
- `title` (string)
- `description` (text, nullable)

### `flashcards`
- `id` (bigInt, PK)
- `deck_id` (bigInt FK -> flashcard_decks.id, onDelete Cascade)
- `question_text` (text)
- `answer_text` (text)
- `ease_factor` (decimal)
- `interval` (int)
- `repetitions` (int)
- `next_review_at` (timestamp)

---

## 5. Foros y Reportes

### `forum_threads`
- `id` (UUID, PK)
- `forumable_type` (string: `App\Models\Course`, `App\Models\Module`, `App\Models\Challenge`)
- `forumable_id` (string/int)
- `author_id` (UUID FK -> users.id)
- `title` (string)
- `body` (text)
- `is_pinned` (boolean)
- `is_locked` (boolean)
- `upvotes` (int, default 0)
- `downvotes` (int, default 0)
- `deleted_at`, `created_at`, `updated_at`

### `forum_posts`
- `id` (UUID, PK)
- `thread_id` (UUID FK -> forum_threads.id, onDelete Cascade)
- `author_id` (UUID FK -> users.id)
- `parent_id` (UUID FK -> forum_posts.id, nullable)
- `body` (text)
- `is_correct_answer` (boolean)
- `upvotes` / `downvotes` (int, default 0)
- `deleted_at`, `created_at`, `updated_at`

### `reports`
- `id` (bigInt, PK)
- `reporter_id` (UUID FK -> users.id)
- `reportable_type` (string: `App\Models\Course`, `App\Models\ForumThread`, `App\Models\ForumPost`, `App\Models\User`)
- `reportable_id` (string/int)
- `reason` (string: `spam`, `plagiarism`, `offensive_language`, `academic_dishonesty`, `other`)
- `details` (text, nullable)
- `status` (string: `pending`, `resolved`, `escalated`, `dismissed`)
- `resolved_by` (UUID FK -> users.id, nullable)
- `resolved_at` (timestamp, nullable)
- `created_at`, `updated_at`
