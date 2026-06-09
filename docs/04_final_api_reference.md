# Prolecom API Reference (v1)

## Base URL
`https://code-learning-staging.up.railway.app/api/v1`

## Autenticación
Todas las peticiones protegidas requieren un header: `Authorization: Bearer <token>` (JWT).
- `POST /users` (Registro)
- `POST /sessions` (Login)
- `POST /password-reset-links` (Olvido de contraseña)
- `POST /password-resets` (Resetear contraseña)
- `DELETE /sessions/current` (Logout)
- `GET /user` (Obtener perfil actual)
- `PUT /user` (Actualizar perfil)
- `DELETE /users/me` (Desactivar cuenta)

## Cursos y Matrículas
- `GET /courses`
- `POST /courses` (Requiere `role:professor|ta`)
- `GET /courses/{id}`
- `PUT /courses/{id}`
- `DELETE /courses/{id}`
- `POST /courses/{id}/enrollments` (Auto-matrícula para cursos públicos)
- `POST /courses/{id}/enrollments/manual` (Matriculación forzada, requiere `role:professor|ta`)
- `DELETE /courses/{id}/enrollments/me` (Darse de baja)
- `GET /courses/{id}/progress`
- `GET /courses/{id}/leaderboard`
- `GET /courses/{id}/stats`
- `GET /courses/{id}/analytics`
- `POST /courses/{id}/staff-members` (Añadir TA)
- `DELETE /courses/{id}/staff/{user_id}`

## Syllabus (Módulos y Materiales)
- `POST /courses/{id}/modules`
- `PUT /modules/{id}`
- `DELETE /modules/{id}`
- `PATCH /modules/{id}/items-order` (Payload: `{"items": [{"id": 1, "type": "material", "order": 1}]}`)
- `POST /modules/{id}/materials` (Soporta `pdf, video_link, ppt, pptx`. Usa `file` o `content`)
- `PUT /materials/{id}`
- `DELETE /materials/{id}`
- `GET /materials/{id}`
- `GET /materials/{id}/download` (Retorna archivo binario o redirección HTTP 302 para links)
- `POST /materials/{id}/views` (Registra vista)

## Foros y Q&A
- `GET /courses/{id}/threads`
- `GET /modules/{id}/threads`
- `GET /challenges/{id}/threads`
- `POST /courses/{id}/threads` (Body: `title`, `body`)
- `POST /modules/{id}/threads`
- `POST /challenges/{id}/threads`
- `GET /threads/{id}`
- `PUT /threads/{id}`
- `DELETE /threads/{id}`
- `PATCH /threads/{id}/pin` (Moderadores)
- `PATCH /threads/{id}/lock` (Moderadores)
- `PUT /threads/{id}/votes/me` (Body: `value: 1 | -1`)
- `POST /threads/{id}/posts` (Crear respuesta)
- `PUT /posts/{id}`
- `DELETE /posts/{id}`
- `PUT /posts/{id}/votes/me`
- `PATCH /posts/{id}/accept` (Aceptar respuesta, solo autor del hilo)

## Evaluaciones (Quizzes y Flashcards)
- `POST /modules/{id}/quizzes`
- `PUT /quizzes/{id}`
- `DELETE /quizzes/{id}`
- `GET /quizzes/{id}`
- `POST /quizzes/{id}/questions`
- `PUT /quiz-questions/{id}`
- `DELETE /quiz-questions/{id}`
- `PUT /quiz-questions/{id}/answers`
- `POST /quizzes/{id}/attempts` (Body: `{"answers":[{"quiz_question_id": 1, "answer_text":"x"}]}`)
- `GET /quiz-attempts/{id}`
- `POST /practice-quizzes` (Genera quiz aleatorio, Body: `{"quiz_id": 1, "question_count": 5}`)
- `GET /flashcard-decks`
- `POST /flashcard-decks`
- `PUT /flashcard-decks/{id}`
- `DELETE /flashcard-decks/{id}`
- `POST /flashcard-decks/{id}/flashcards`
- `PUT /flashcards/{id}`
- `PATCH /flashcards/{id}` (Body: `{"quality": 0-5}` Algoritmo SuperMemo)
- `DELETE /flashcards/{id}`
- `POST /flashcard-imports` (Desde un Quiz)
- `GET /flashcard-decks/{id}/due-flashcards` (Retorna las que tocan repasar hoy)

## Retos y Juez (Judge0 IDE)
- `GET /languages`
- `GET /modules/{id}/challenges`
- `POST /modules/{id}/challenges`
- `GET /challenges/{id}`
- `PUT /challenges/{id}`
- `DELETE /challenges/{id}`
- `POST /challenges/{id}/test-cases`
- `PUT /challenge-test-cases/{id}`
- `DELETE /challenge-test-cases/{id}`
- `POST /challenges/{id}/attempts` (Evalúa código. Limitado por Throttle de seguridad)
- `GET /challenges/{id}/attempts` (Profesor revisa intentos)
- `POST /challenge-attempts/{id}/feedback` (Feedback manual de profesor)

## Moderación y Soporte
- `POST /reports` (Reportar contenido inapropiado)
- `GET /moderator/reports`
- `PATCH /reports/{id}/resolve`
- `PATCH /reports/{id}/escalate`
- `GET /professor-applications`
- `GET /professor-applications/mine`
- `POST /professor-applications`
- `PATCH /professor-applications/{id}/assign`
- `PATCH /professor-applications/{id}/review` (Aprobar o rechazar)
- `GET /support/users`
- `GET /support/users/{id}`
- `PUT /support/users/{id}/role` (Spatie: admin, support, moderator, professor, student)
- `PATCH /support/users/{id}/deactivate`

## Configuración y Logs (Administración)
- `GET /admin/logs`
- `GET /admin/settings`
- `PUT /admin/settings/{key}`
- `GET /moderator/response-templates`
- `POST /admin/response-templates`
- `PUT /admin/response-templates/{id}`
- `DELETE /admin/response-templates/{id}`
- `GET /notifications`
- `GET /notifications/unread-count`
- `PATCH /notifications`
