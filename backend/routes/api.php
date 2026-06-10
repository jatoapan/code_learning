<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:1000,1')->group(function () {
    Route::get('/health', function () { return response()->json(['status' => 'ok']); });
    Route::get('/ping-deploy', function () { 
        return response()->json([
            'auth_exists' => class_exists(\App\Http\Controllers\Api\V1\AuthenticationController::class),
            'course_exists' => class_exists(\App\Http\Controllers\Api\V1\CourseController::class)
        ]); 
    });

    // Endpoint de testing E2E protegido por un token secreto
    Route::get('/dev-reset-db', function (\Illuminate\Http\Request $request) {
        if ($request->query('token') !== 'railway_prolecom_secret_2026') {
            abort(403, 'Acceso Denegado. Token inválido.');
        }
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
        return response()->json(['message' => 'Base de datos destruida y re-sembrada exitosamente (Protegido por Token).']);
    });

    // 4.1 Auth
    Route::post('/users', [\App\Http\Controllers\Api\V1\AuthenticationController::class, 'register'])->middleware('throttle:100,1');
    Route::post('/sessions', [\App\Http\Controllers\Api\V1\AuthenticationController::class, 'login'])->middleware('throttle:100,1');
    Route::post('/password-reset-links', [\App\Http\Controllers\Api\V1\AuthenticationController::class, 'sendResetLink'])->middleware('throttle:100,1');
    Route::post('/password-resets', [\App\Http\Controllers\Api\V1\AuthenticationController::class, 'resetPassword'])->middleware('throttle:100,1');
    
    // Ruta dummy para que el correo de reseteo de contraseña funcione en el API
    Route::get('/password-reset/{token}', function (string $token) {
        return response()->json(['message' => 'Redirigir al frontend', 'token' => $token]);
    })->name('password.reset');

    // Rutas protegidas
    Route::middleware(['auth:api', \App\Http\Middleware\Idempotency::class])->group(function () {
        // Auth & Profile
        Route::delete('/sessions/current', [\App\Http\Controllers\Api\V1\AuthenticationController::class, 'logout']);
        Route::get('/user', [\App\Http\Controllers\Api\V1\UserController::class, 'me']);
        Route::put('/user', [\App\Http\Controllers\Api\V1\UserController::class, 'update']);
        Route::delete('/users/me', [\App\Http\Controllers\Api\V1\UserController::class, 'deactivate']);
        
        // 4.3 Professor Applications
        Route::post('/professor-applications', [\App\Http\Controllers\Api\V1\ProfessorApplicationController::class, 'store']);
        Route::get('/professor-applications/mine', [\App\Http\Controllers\Api\V1\ProfessorApplicationController::class, 'mine']);
        
        // 4.4 Courses
        Route::get('/courses', [\App\Http\Controllers\Api\V1\CourseController::class, 'index']);
        Route::get('/courses/{id}', [\App\Http\Controllers\Api\V1\CourseController::class, 'show']);
        Route::post('/courses/{id}/enrollments', [\App\Http\Controllers\Api\V1\CourseEnrollmentController::class, 'enroll']);
        Route::delete('/courses/{id}/enrollments/me', [\App\Http\Controllers\Api\V1\CourseEnrollmentController::class, 'drop']);
        Route::get('/courses/{id}/progress', [\App\Http\Controllers\Api\V1\CourseController::class, 'progress']);
        Route::get('/courses/{id}/leaderboard', [\App\Http\Controllers\Api\V1\CourseController::class, 'leaderboard']);
        
        // 4.6 Syllabus (Materials & Modules)
        Route::get('/materials/{id}', [\App\Http\Controllers\Api\V1\MaterialController::class, 'show']);
        Route::get('/materials/{id}/download', [\App\Http\Controllers\Api\V1\MaterialController::class, 'download']); // Visor seguro
        Route::post('/materials/{id}/views', [\App\Http\Controllers\Api\V1\MaterialController::class, 'recordView']);
        
        // 4.7 Notifications
        Route::get('/notifications', [\App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
        Route::patch('/notifications/{id}', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markAsRead']);
        Route::patch('/notifications', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markAllAsRead']);
        Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\V1\NotificationController::class, 'unreadCount']);

        // 4.8 Forum Q&A
        Route::get('/courses/{id}/threads', [\App\Http\Controllers\Api\V1\ForumThreadController::class, 'indexByCourse']);
        Route::get('/modules/{id}/threads', [\App\Http\Controllers\Api\V1\ForumThreadController::class, 'indexByModule']);
        Route::get('/challenges/{id}/threads', [\App\Http\Controllers\Api\V1\ForumThreadController::class, 'indexByChallenge']);
        Route::post('/courses/{id}/threads', [\App\Http\Controllers\Api\V1\ForumThreadController::class, 'storeCourseThread']);
        Route::post('/modules/{id}/threads', [\App\Http\Controllers\Api\V1\ForumThreadController::class, 'storeModuleThread']);
        Route::post('/challenges/{id}/threads', [\App\Http\Controllers\Api\V1\ForumThreadController::class, 'storeChallengeThread']);
        Route::get('/threads/{id}', [\App\Http\Controllers\Api\V1\ForumThreadController::class, 'show']);
        Route::put('/threads/{id}', [\App\Http\Controllers\Api\V1\ForumThreadController::class, 'update']);
        Route::delete('/threads/{id}', [\App\Http\Controllers\Api\V1\ForumThreadController::class, 'destroy']);
        Route::post('/threads/{id}/posts', [\App\Http\Controllers\Api\V1\ForumPostController::class, 'store']);
        Route::put('/posts/{id}', [\App\Http\Controllers\Api\V1\ForumPostController::class, 'update']);
        Route::delete('/posts/{id}', [\App\Http\Controllers\Api\V1\ForumPostController::class, 'destroy']);
        Route::put('/threads/{id}/votes/me', [\App\Http\Controllers\Api\V1\VoteController::class, 'voteThread']);
        Route::put('/posts/{id}/votes/me', [\App\Http\Controllers\Api\V1\VoteController::class, 'votePost']);
        Route::patch('/posts/{id}/accept', [\App\Http\Controllers\Api\V1\ForumPostController::class, 'acceptAnswer']);

        // 4.9 Challenges & IDE
        Route::get('/languages', [\App\Http\Controllers\Api\V1\ChallengeController::class, 'languages']);
        Route::get('/challenges/{id}', [\App\Http\Controllers\Api\V1\ChallengeController::class, 'show']);
        Route::post('/challenges/{id}/attempts', [\App\Http\Controllers\Api\V1\ChallengeAttemptController::class, 'submit'])->middleware('throttle:1000,1');
        Route::get('/challenges/{id}/attempts', [\App\Http\Controllers\Api\V1\ChallengeAttemptController::class, 'index']);

        // 4.10 Quizzes & Flashcards
        Route::get('/quizzes/{id}', [\App\Http\Controllers\Api\V1\QuizController::class, 'show']);
        Route::post('/quizzes/{id}/attempts', [\App\Http\Controllers\Api\V1\QuizController::class, 'submit']);
        Route::get('/quiz-attempts/{id}', [\App\Http\Controllers\Api\V1\QuizController::class, 'showAttempt']);
        Route::apiResource('flashcard-decks', \App\Http\Controllers\Api\V1\FlashcardDeckController::class);
        Route::post('/flashcard-decks/{id}/flashcards', [\App\Http\Controllers\Api\V1\FlashcardController::class, 'store']);
        Route::put('/flashcards/{id}', [\App\Http\Controllers\Api\V1\FlashcardController::class, 'update']);
        Route::delete('/flashcards/{id}', [\App\Http\Controllers\Api\V1\FlashcardController::class, 'destroy']);
        Route::post('/flashcard-imports', [\App\Http\Controllers\Api\V1\FlashcardController::class, 'importFromQuiz']);
        Route::get('/flashcard-decks/{id}/due-flashcards', [\App\Http\Controllers\Api\V1\FlashcardController::class, 'due']);
        Route::patch('/flashcards/{id}', [\App\Http\Controllers\Api\V1\FlashcardController::class, 'review']);
        Route::post('/practice-quizzes', [\App\Http\Controllers\Api\V1\QuizController::class, 'generatePracticeQuiz']);

        // 4.11 Reports
        Route::post('/reports', [\App\Http\Controllers\Api\V1\ReportController::class, 'store']);

        // Professor / TA Routes
        Route::middleware('role:professor|ta')->group(function () {
            Route::post('/courses', [\App\Http\Controllers\Api\V1\CourseController::class, 'store']);
            Route::put('/courses/{id}', [\App\Http\Controllers\Api\V1\CourseController::class, 'update']);
            Route::delete('/courses/{id}', [\App\Http\Controllers\Api\V1\CourseController::class, 'destroy']);
            Route::post('/courses/{id}/enrollments/manual', [\App\Http\Controllers\Api\V1\CourseEnrollmentController::class, 'manualEnroll']);
            Route::get('/courses/{id}/stats', [\App\Http\Controllers\Api\V1\CourseController::class, 'stats']);
            Route::post('/courses/{id}/staff-members', [\App\Http\Controllers\Api\V1\CourseController::class, 'addStaff']);
            Route::delete('/courses/{id}/staff/{user_id}', [\App\Http\Controllers\Api\V1\CourseController::class, 'removeStaff']);
            Route::get('/courses/{id}/analytics', [\App\Http\Controllers\Api\V1\CourseController::class, 'analytics']);
            
            Route::post('/courses/{id}/modules', [\App\Http\Controllers\Api\V1\ModuleController::class, 'store']);
            Route::put('/modules/{id}', [\App\Http\Controllers\Api\V1\ModuleController::class, 'update']);
            Route::delete('/modules/{id}', [\App\Http\Controllers\Api\V1\ModuleController::class, 'destroy']);
            Route::patch('/modules/{id}/items-order', [\App\Http\Controllers\Api\V1\ModuleController::class, 'reorderItems']);
            
            Route::post('/modules/{id}/materials', [\App\Http\Controllers\Api\V1\MaterialController::class, 'store']);
            Route::put('/materials/{id}', [\App\Http\Controllers\Api\V1\MaterialController::class, 'update']);
            Route::delete('/materials/{id}', [\App\Http\Controllers\Api\V1\MaterialController::class, 'destroy']);
            
            Route::get('/modules/{id}/challenges', [\App\Http\Controllers\Api\V1\ChallengeController::class, 'indexByModule']);
            Route::post('/modules/{id}/challenges', [\App\Http\Controllers\Api\V1\ChallengeController::class, 'store']);
            Route::put('/challenges/{id}', [\App\Http\Controllers\Api\V1\ChallengeController::class, 'update']);
            Route::delete('/challenges/{id}', [\App\Http\Controllers\Api\V1\ChallengeController::class, 'destroy']);
            
            Route::post('/challenges/{id}/test-cases', [\App\Http\Controllers\Api\V1\ChallengeTestCaseController::class, 'store']);
            Route::put('/challenge-test-cases/{id}', [\App\Http\Controllers\Api\V1\ChallengeTestCaseController::class, 'update']);
            Route::delete('/challenge-test-cases/{id}', [\App\Http\Controllers\Api\V1\ChallengeTestCaseController::class, 'destroy']);
            Route::post('/challenge-attempts/{id}/feedback', [\App\Http\Controllers\Api\V1\ChallengeAttemptController::class, 'feedback']);
            
            Route::post('/modules/{id}/quizzes', [\App\Http\Controllers\Api\V1\QuizController::class, 'store']);
            Route::put('/quizzes/{id}', [\App\Http\Controllers\Api\V1\QuizController::class, 'update']);
            Route::delete('/quizzes/{id}', [\App\Http\Controllers\Api\V1\QuizController::class, 'destroy']);
            Route::post('/quizzes/{id}/questions', [\App\Http\Controllers\Api\V1\QuizQuestionController::class, 'store']);
            Route::put('/quiz-questions/{id}', [\App\Http\Controllers\Api\V1\QuizQuestionController::class, 'update']);
            Route::delete('/quiz-questions/{id}', [\App\Http\Controllers\Api\V1\QuizQuestionController::class, 'destroy']);
            Route::put('/quiz-questions/{id}/answers', [\App\Http\Controllers\Api\V1\QuizQuestionController::class, 'updateAnswers']);
        });

        // Moderator Routes
        Route::middleware('role:moderator|admin')->group(function () {
            Route::patch('/threads/{id}/pin', [\App\Http\Controllers\Api\V1\ForumThreadController::class, 'togglePin']);
            Route::patch('/threads/{id}/lock', [\App\Http\Controllers\Api\V1\ForumThreadController::class, 'lock']);
            Route::get('/moderator/reports', [\App\Http\Controllers\Api\V1\ReportController::class, 'index']);
            Route::get('/moderator/response-templates', [\App\Http\Controllers\Api\V1\ResponseTemplateController::class, 'index']);
            Route::patch('/reports/{id}/resolve', [\App\Http\Controllers\Api\V1\ReportController::class, 'resolve']);
            Route::patch('/reports/{id}/escalate', [\App\Http\Controllers\Api\V1\ReportController::class, 'escalate']);
        });

        // Support Routes
        Route::middleware('role:support|admin')->group(function () {
            Route::get('/professor-applications', [\App\Http\Controllers\Api\V1\ProfessorApplicationController::class, 'index']);
            Route::patch('/professor-applications/{id}/assign', [\App\Http\Controllers\Api\V1\ProfessorApplicationController::class, 'assignReviewer']);
            Route::patch('/professor-applications/{id}/review', [\App\Http\Controllers\Api\V1\ProfessorApplicationController::class, 'review']);
            Route::get('/support/users', [\App\Http\Controllers\Api\V1\SupportUserController::class, 'index']);
            Route::get('/support/users/{id}', [\App\Http\Controllers\Api\V1\SupportUserController::class, 'show']);
            Route::patch('/support/users/{id}/deactivate', [\App\Http\Controllers\Api\V1\SupportUserController::class, 'deactivate']);
            Route::put('/support/users/{id}/role', [\App\Http\Controllers\Api\V1\SupportUserController::class, 'updateRole']);
        });

        // Admin Routes
        Route::middleware('role:admin')->group(function () {
            Route::get('/admin/logs', [\App\Http\Controllers\Api\V1\AdminLogController::class, 'index']);
            Route::get('/admin/settings', [\App\Http\Controllers\Api\V1\SystemSettingController::class, 'index']);
            Route::put('/admin/settings/{key}', [\App\Http\Controllers\Api\V1\SystemSettingController::class, 'update']);
            Route::post('/admin/response-templates', [\App\Http\Controllers\Api\V1\ResponseTemplateController::class, 'store']);
            Route::put('/admin/response-templates/{id}', [\App\Http\Controllers\Api\V1\ResponseTemplateController::class, 'update']);
            Route::delete('/admin/response-templates/{id}', [\App\Http\Controllers\Api\V1\ResponseTemplateController::class, 'destroy']);
        });
    });
});
