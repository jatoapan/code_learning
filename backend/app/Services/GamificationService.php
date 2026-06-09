<?php

namespace App\Services;

use App\Models\Module;
use App\Models\Quiz;
use App\Models\ModuleItem;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\FlashcardDeck;
use App\Models\Flashcard;
use App\Models\User;
use App\Enums\QuizStatus;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    public function createQuiz(Module $module, array $data): Quiz
    {
        return DB::transaction(function () use ($module, $data) {
            $quiz = new Quiz();
            $quiz->title = $data['title'];
            $quiz->description = $data['description'] ?? null;
            $quiz->mode = $data['mode'];
            $quiz->time_limit = $data['time_limit_minutes'] ?? null;
            $quiz->passing_score = $data['passing_score'];
            $quiz->status = QuizStatus::Draft->value;
            $quiz->save();

            ModuleItem::create([
                'module_id' => $module->id,
                'itemable_type' => Quiz::class,
                'itemable_id' => $quiz->id,
                'order' => 1
            ]);

            return $quiz;
        });
    }

    public function updateQuiz(Quiz $quiz, array $data): Quiz
    {
        if (isset($data['time_limit_minutes'])) {
            $data['time_limit'] = $data['time_limit_minutes'];
            unset($data['time_limit_minutes']);
        }

        $quiz->update($data);
        return $quiz;
    }

    public function deleteQuiz(Quiz $quiz): void
    {
        $quiz->delete();
    }

    public function submitQuizAttempt(Quiz $quiz, User $user, array $answers): QuizAttempt
    {
        return DB::transaction(function () use ($quiz, $user, $answers) {
            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'score' => 0,
                'passed' => false,
                'started_at' => now(),
                'completed_at' => now(),
            ]);

            return $attempt;
        });
    }

    public function generatePracticeQuiz(Quiz $quiz, int $count)
    {
        return QuizQuestion::where('quiz_id', $quiz->id)
            ->inRandomOrder()
            ->limit($count)
            ->with('answers')
            ->get();
    }

    public function createQuizQuestion(Quiz $quiz, array $data): QuizQuestion
    {
        return DB::transaction(function () use ($quiz, $data) {
            $question = new QuizQuestion();
            $question->quiz_id = $quiz->id;
            $question->question_text = $data['question_text'];
            $question->type = $data['type'];
            $question->points = $data['points'];
            $question->explanation = $data['explanation'] ?? null;
            $question->save();

            foreach ($data['options'] as $option) {
                QuizAnswer::create([
                    'question_id' => $question->id,
                    'answer_text' => $option,
                    'is_correct' => ($option === $data['correct_answer'])
                ]);
            }

            return $question;
        });
    }

    public function updateQuizQuestion(QuizQuestion $question, array $data): QuizQuestion
    {
        $question->update($data);
        return $question;
    }

    public function deleteQuizQuestion(QuizQuestion $question): void
    {
        $question->delete();
    }

    public function updateQuizAnswers(QuizQuestion $question, array $answers): void
    {
        DB::transaction(function () use ($question, $answers) {
            foreach ($answers as $answerData) {
                if (isset($answerData['id'])) {
                    $answer = QuizAnswer::where('question_id', $question->id)->findOrFail($answerData['id']);
                    $answer->update([
                        'answer_text' => $answerData['answer_text'],
                        'is_correct' => $answerData['is_correct']
                    ]);
                } else {
                    QuizAnswer::create([
                        'question_id' => $question->id,
                        'answer_text' => $answerData['answer_text'],
                        'is_correct' => $answerData['is_correct']
                    ]);
                }
            }
        });
    }

    public function createFlashcardDeck(Module $module, User $user, array $data): FlashcardDeck
    {
        return DB::transaction(function () use ($module, $user, $data) {
            $deck = new FlashcardDeck();
            $deck->title = $data['title'];
            $deck->description = $data['description'] ?? null;
            $deck->user_id = $user->id;
            $deck->save();

            ModuleItem::create([
                'module_id' => $module->id,
                'itemable_type' => FlashcardDeck::class,
                'itemable_id' => $deck->id,
                'order' => 1
            ]);

            return $deck;
        });
    }

    public function updateFlashcardDeck(FlashcardDeck $deck, array $data): FlashcardDeck
    {
        $deck->update($data);
        return $deck;
    }

    public function deleteFlashcardDeck(FlashcardDeck $deck): void
    {
        $deck->delete();
    }

    public function createFlashcard(FlashcardDeck $deck, array $data): Flashcard
    {
        $flashcard = new Flashcard();
        $flashcard->deck_id = $deck->id;
        $flashcard->question_text = $data['question_text'];
        $flashcard->answer_text = $data['answer_text'];
        $flashcard->save();

        return $flashcard;
    }

    public function updateFlashcard(Flashcard $flashcard, array $data): Flashcard
    {
        $flashcard->update($data);
        return $flashcard;
    }

    public function deleteFlashcard(Flashcard $flashcard): void
    {
        $flashcard->delete();
    }

    public function importFlashcardsFromQuiz(FlashcardDeck $deck, string $quizId): void
    {
        DB::transaction(function () use ($deck, $quizId) {
            $questions = QuizQuestion::where('quiz_id', $quizId)->get();

            foreach ($questions as $question) {
                Flashcard::create([
                    'deck_id' => $deck->id,
                    'question_text' => $question->question_text,
                    'answer_text' => $question->correct_answer ?? 'No answer provided'
                ]);
            }
        });
    }

    public function reviewFlashcard(Flashcard $flashcard, int $quality): Flashcard
    {
        $flashcard->next_review_at = now()->addDays($quality);
        $flashcard->save();

        return $flashcard;
    }
}
