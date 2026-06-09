# Prolecom Database Schema (Final)

La base de datos utiliza PostgreSQL y está diseñada respetando `UUIDs` para todas las tablas transaccionales mayores y relaciones polimórficas donde aplique.

## Autenticación y Autorización
- **users**: `id (UUID)`, `name`, `email`, `password`, `is_active`, `last_login_at`.
- **roles** / **permissions** / **model_has_roles** (Spatie Laravel-Permission).

## Core (Cursos y Sílabo)
- **institutions**: `id (Int)`, `name`, `domain_pattern`, `settings`.
- **courses**: `id (UUID)`, `title`, `slug`, `description`, `status (Enum: draft, public, unlisted)`, `category (Enum)`, `has_leaderboard`, `owner_id (UUID)`. SoftDeletes habilitado.
- **course_user** (Pivot): `course_id`, `user_id`, `role (student, ta)`, `status`, `xp`, `progress_percent`.
- **modules**: `id (Int)`, `course_id (UUID)`, `title`, `order`.
- **materials**: `id (Int)`, `title`, `type (Enum: pdf, video_link, ppt, pptx)`, `content`, `file_path`.
- **module_items** (Polimórfica Inversa para ordenar Syllabus): `id (Int)`, `module_id`, `itemable_id`, `itemable_type`, `order`. Sirve para entremezclar materiales, retos y quizzes.
- **material_views**: `material_id`, `user_id`, `viewed_at`.

## Foros y Q&A
- **forum_threads**: `id (UUID)`, `title`, `body`, `user_id`, `forumable_id`, `forumable_type` (Puede pertenecer a Course, Module o Challenge), `is_pinned`, `is_locked`, `votes_sum`.
- **forum_posts**: `id (UUID)`, `forum_thread_id`, `user_id`, `body`, `is_accepted`, `votes_sum`.
- **votes** (Polimórfica): `user_id`, `votable_id`, `votable_type`, `value (+1, -1)`. Protegido contra manipulación cruzada por Gate::authorize sobre el elemento padre.

## Gamificación e IDE
- **challenges**: `id (UUID)`, `title`, `description`, `difficulty (Enum)`, `points`, `language_id`, `language_name`.
- **challenge_test_cases**: `id (Int)`, `challenge_id`, `input_data`, `expected_output`, `is_hidden`.
- **challenge_attempts**: `id (UUID)`, `challenge_id`, `user_id`, `submitted_code`, `language_id`, `status (Enum: passed, failed, error)`, `score`, `execution_time_ms`, `feedback`.
- **quizzes**: `id (Int)`, `title`, `mode (Enum: practice, exam)`, `time_limit_minutes`, `passing_score`.
- **quiz_questions**: `id (Int)`, `quiz_id`, `type (Enum: multiple_choice, true_false)`, `question_text`, `options (JSON)`, `correct_answer`, `points`.
- **quiz_attempts**: `id (UUID)`, `quiz_id`, `user_id`, `score`, `passed`.
- **flashcard_decks**: `id (Int)`, `title`, `user_id`, `module_id`.
- **flashcards**: `id (Int)`, `flashcard_deck_id`, `question_text`, `answer_text`, `easiness_factor`, `interval`, `repetitions`, `next_review_at` (Algoritmo SuperMemo 2).

## Administración y Sistema
- **reports** (Polimórfica): `id (Int)`, `reporter_id`, `reportable_id`, `reportable_type`, `reason`, `status (Enum: pending, resolved, dismissed, escalated)`.
- **professor_applications**: `id (Int)`, `applicant_id`, `status (pending, approved, rejected)`, `qualifications`, `reviewer_id`.
- **admin_logs**: `id (Int)`, `admin_id`, `action`, `details (JSON)`.
- **system_settings**: `key (String PK)`, `value`, `type`.
- **response_templates**: `id (Int)`, `title`, `body`.
- **notifications**: Core table generada por Laravel Database Notifications (`id (UUID)`, `type`, `notifiable`, `data (JSON)`).

## Notas de Seguridad de Base de Datos
- Las operaciones transaccionales aseguran que un `CourseEnrollment` con `status: public` rechace BOLA si alguien intenta asignar otro `user_id` en peticiones manuales no autorizadas.
- La tabla de `votes` delega su permiso de `view` al `Course` originario a través del ancestro.
- La eliminación de foros, hilos y cursos se maneja con Cascade On Delete en la capa de la base de datos o SoftDeletes en Eloquent, según corresponda en las migraciones nativas.
