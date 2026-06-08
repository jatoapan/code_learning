#!/bin/bash

BASE_URL="https://code-learning-staging.up.railway.app/api/v1"

echo "================================================="
echo " E2E LIVE TEST: FASE 5 - QUIZZES Y FLASHCARDS"
echo "================================================="

echo "[1] Iniciando sesion como profesor@espol.edu.ec..."
LOGIN_RESP=$(curl -s -X POST "${BASE_URL}/sessions" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -d '{"email":"profesor@espol.edu.ec", "password":"password123", "device_name":"e2e_quiz"}')

TOKEN=$(echo $LOGIN_RESP | grep -oP '"token":"\K[^"]+')
if [ -z "$TOKEN" ]; then
    echo "❌ Fallo el login."
    exit 1
fi
echo "✅ Token de Profesor capturado."

echo -e "\n[2] Obteniendo Curso y creando Modulo base..."
COURSE_ID=$(curl -s -X GET "${BASE_URL}/courses" -H "Authorization: Bearer $TOKEN" | grep -oP '"id":\s*"?\K[^",}]+' | head -1 | tr -d '"')
MODULE_ID=$(curl -s -X POST "${BASE_URL}/courses/$COURSE_ID/modules" -H "Accept: application/json" -H "Content-Type: application/json" -H "Authorization: Bearer $TOKEN" -d '{"title":"Modulo 2 Quizzes", "order":2}' | grep -oP '"id":\s*"?\K[^",}]+' | head -1 | tr -d '"')
echo "✅ Curso: $COURSE_ID | Modulo: $MODULE_ID"

echo -e "\n[3] Creando un Quiz (POST /modules/{id}/quizzes)..."
QUIZ_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/modules/$MODULE_ID/quizzes" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d '{"title":"Examen Parcial", "mode":"exam", "passing_score":70}')

QUIZ_BODY=$(echo "$QUIZ_RESP" | head -n -1)
QUIZ_STATUS=$(echo "$QUIZ_RESP" | tail -n 1)
echo "   -> HTTP $QUIZ_STATUS"

QUIZ_ID=$(echo "$QUIZ_BODY" | grep -oP '"id":\s*"?\K[^",}]+' | head -1 | tr -d '"')

echo -e "\n[4] Agregando Pregunta al Quiz (POST /quizzes/{id}/questions)..."
QUESTION_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST "${BASE_URL}/quizzes/$QUIZ_ID/questions" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d '{"question_text":"¿Que es Laravel?","type":"multiple_choice","points":10,"options":["Un Framework","Un Lenguaje"],"correct_answer":"Un Framework"}')
echo "   -> HTTP $QUESTION_STATUS"

echo -e "\n[5] Creando un Mazo de Flashcards (POST /flashcard-decks)..."
DECK_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/flashcard-decks" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d '{"title":"Conceptos Basicos", "module_id":"'"$MODULE_ID"'"}')

DECK_BODY=$(echo "$DECK_RESP" | head -n -1)
DECK_STATUS=$(echo "$DECK_RESP" | tail -n 1)
echo "   -> HTTP $DECK_STATUS"

DECK_ID=$(echo "$DECK_BODY" | grep -oP '"id":\s*"?\K[^",}]+' | head -1 | tr -d '"')

echo -e "\n[6] Agregando una Tarjeta (POST /flashcard-decks/{id}/flashcards)..."
CARD_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST "${BASE_URL}/flashcard-decks/$DECK_ID/flashcards" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d '{"question_text":"¿PHP?","answer_text":"Lenguaje de servidor"}')
echo "   -> HTTP $CARD_STATUS"

echo "================================================="
