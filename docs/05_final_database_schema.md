# Prolecom Database Schema

This document outlines the final structure of the 28 core database tables (plus Spatie permission tables) powering the Prolecom MVP.

## Primary Keys & IDs
- **UUIDs**: Are used for entities that might be exposed publicly or require decoupled generation (e.g. `users`, `courses`, `challenges`, `challenge_attempts`, `forum_threads`, `forum_posts`, `notifications`). 
- **Auto-increment Integers**: Used for internal relationships and smaller entities (e.g., `institutions`, `modules`, `materials`, `quizzes`, `flashcards`, etc.).

---

## 1. Authentication & Users
- **`users`**: Uses **UUID** (`id`). Contains `name`, `email`, `password`, `xp`, `status`, `institution_id`.
- **`institutions`**: Auto-increment `id`. Contains `name`, `slug`, `domain`, `type`, `logo_path`.
- **`personal_access_tokens`**: Sanctum token table.
- **Spatie Permission Tables**: `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`. 
- **`professor_applications`**: Auto-increment `id`. Contains `user_id`, `motivation`, `qualifications`, `status`, `reviewer_id`.

## 2. Courses & Syllabus
- **`courses`**: Uses **UUID** (`id`). Contains `category`, `title`, `slug`, `description`, `status`, `owner_id`.
- **`course_user`**: Auto-increment `id`. Pivot table tracking enrollments. Contains `course_id`, `user_id`, `role`, `status`, `xp`, `progress_percent`.
- **`modules`**: Auto-increment `id`. Contains `course_id`, `title`, `description`, `order`, `prerequisite_module_id`.
- **`materials`**: Auto-increment `id`. Contains `title`, `type`, `file_path`, `creator_id`, `moderator_endorsed_at`.
- **`material_user`**: Auto-increment `id`. Tracks viewed materials per user.
- **`module_items`**: Auto-increment `id`. Polymorphic junction to track the exact order of materials, quizzes, and challenges inside a module.

## 3. Forum Q&A
- **`forum_threads`**: Uses **UUID** (`id`).
  - **Polymorphic Relationship**: Uses `forumable_type` and `forumable_id` (string 36 to support UUIDs) to attach to Courses, Modules, or Challenges.
  - Contains `title`, `body`, `user_id`, `status`, `is_pinned`, `vote_score`, `moderator_endorsed_at`.
- **`forum_posts`**: Uses **UUID** (`id`). Contains `thread_id`, `parent_id`, `body`, `user_id`, `is_accepted_answer`, `vote_score`, `moderator_endorsed_at`.
- **`votes`**: Auto-increment `id`.
  - **Polymorphic Relationship**: Uses `votable_type` and `votable_id` (uuid) to vote on Threads or Posts.
  - Contains `user_id`, `vote_type` (+1, -1).

## 4. Quizzes & Flashcards
- **`quizzes`**: Auto-increment `id`. Contains `title`, `time_limit`, `passing_score`, `mode`, `status`.
- **`quiz_questions`**: Auto-increment `id`. Contains `quiz_id`, `question_text`, `type`, `points`.
- **`quiz_answers`**: Auto-increment `id`. Options for questions.
- **`quiz_attempts`**: Auto-increment `id`. Tracks student attempts, `user_id`, `quiz_id`, `score`, `status`.
- **`quiz_attempt_answers`**: Auto-increment `id`. Tracks individual answers given in an attempt.
- **`flashcard_decks`**: Auto-increment `id`. Contains `user_id`, `title`, `description`.
- **`flashcards`**: Auto-increment `id`. Contains `deck_id`, `question_text`, `answer_text`, and spaced repetition fields (e.g. `interval`, `ease_factor`).

## 5. Challenges & IDE (Gamification)
- **`challenges`**: Uses **UUID** (`id`). Contains `module_id`, `title`, `difficulty`, `language_id`, `language_name`, `starter_code`, `points`, `creator_id`.
- **`challenge_test_cases`**: Auto-increment `id`. Contains `challenge_id`, `input_data`, `expected_output`, `is_hidden`.
- **`challenge_attempts`**: Uses **UUID** (`id`). Contains `challenge_id`, `user_id`, `submitted_code`, `status`, `execution_time_ms`, `score`.

## 6. Moderation, Reports & System
- **`reports`**: Auto-increment `id`.
  - **Polymorphic Relationship**: Uses `reportable_type` and `reportable_id` (uuid) to report Threads, Posts, or Users.
  - Contains `reporter_id`, `reason`, `status`, `resolved_by`.
- **`response_templates`**: Auto-increment `id`. Canned responses for moderators/support.
- **`activity_logs`**: Auto-increment `id`. Spatie Activitylog table. Tracks system-wide events polymorphically.
- **`system_settings`**: Auto-increment `id`. Key-value store for global settings (`key`, `value`, `type`).
- **`notifications`**: Uses **UUID** (`id`).
  - **Polymorphic Relationship**: Uses `notifiable_type` and `notifiable_id` via `$table->morphs('notifiable')` to track who receives the notification.
  - Contains `type`, `data`, `read_at`.

*(Note: The Endorsements table was removed. Endorsement tracking is handled via `moderator_endorsed_at` timestamps on Materials, Threads, and Posts.)*

## 7. Caching & Infrastructure Notes
- **Idempotency Keys**: Handled completely in-memory (Cache/Redis) by the custom `Idempotency` middleware. No SQL table is used for this to ensure maximum speed (sub-15ms response times). Responses are cached for 24 hours based on the `Idempotency-Key` header.
- **Rate Limiting**: Throttling counters (e.g., 5 req/min for Auth, 10 req/min for Judge0) are also managed directly in the Application Cache, completely bypassing the SQL Database for optimal performance.
