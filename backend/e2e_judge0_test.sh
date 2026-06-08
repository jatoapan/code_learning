#!/bin/bash

BASE_URL="https://code-learning-staging.up.railway.app/api/v1"

echo "================================================="
echo " E2E LIVE TEST: FASE 7 - RETOS DE CODIGO Y JUDGE0"
echo "================================================="

echo "[1] Iniciando sesion como profesor@espol.edu.ec..."
LOGIN_RESP=$(curl -s -X POST "${BASE_URL}/sessions" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -d '{"email":"profesor@espol.edu.ec", "password":"password123", "device_name":"e2e"}')
PROF_TOKEN=$(echo $LOGIN_RESP | grep -oP '"token":"\K[^"]+')

COURSE_ID=$(curl -s -X GET "${BASE_URL}/courses" -H "Accept: application/json" -H "Authorization: Bearer $PROF_TOKEN" | grep -oP '"id":\s*"?\K[^",}]+' | head -1 | tr -d '"')
echo "   -> Curso ID: $COURSE_ID"
MOD_ID=$(curl -s -X POST "${BASE_URL}/courses/$COURSE_ID/modules" -H "Accept: application/json" -H "Content-Type: application/json" -H "Authorization: Bearer $PROF_TOKEN" -d '{"title":"Modulo Algoritmos", "description":"x", "order":1}' | grep -oP '"id":\s*"?\K[^",}]+' | head -1 | tr -d '"')
echo "   -> Modulo ID: $MOD_ID"

echo -e "\n[2] Creando Reto de Codigo..."
CHALLENGE_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/modules/$MOD_ID/challenges" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $PROF_TOKEN" \
     -d '{"title":"Suma Simple", "description":"Suma dos numeros separados por espacio", "difficulty":"easy", "points":10, "language_id":71, "language_name":"python"}')
CHALLENGE_BODY=$(echo "$CHALLENGE_RESP" | head -n -1)
CHALLENGE_STATUS=$(echo "$CHALLENGE_RESP" | tail -n 1)
echo "   -> Body: $CHALLENGE_BODY"
CHAL_ID=$(echo "$CHALLENGE_BODY" | grep -oP '"id":\s*"?\K[^",}]+' | head -1 | tr -d '"')
echo "   -> HTTP $CHALLENGE_STATUS"

echo -e "\n[3] Agregando Caso de Prueba (Input: '2 2', Output: '4')..."
TC_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST "${BASE_URL}/challenges/$CHAL_ID/test-cases" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $PROF_TOKEN" \
     -d '{"input":"2 2", "expected_output":"4", "is_hidden":false}')
echo "   -> HTTP $TC_STATUS"

echo -e "\n[4] Iniciando sesion como estudiante..."
STU_LOGIN=$(curl -s -X POST "${BASE_URL}/sessions" -H "Accept: application/json" -H "Content-Type: application/json" -d '{"email":"estudiante@gmail.com", "password":"password123", "device_name":"e2e"}')
STU_TOKEN=$(echo $STU_LOGIN | grep -oP '"token":"\K[^"]+')

echo -e "\n[5] Estudiante envia codigo en Python 3 para evaluacion..."
CODE="user_input = input().split()\nprint(int(user_input[0]) + int(user_input[1]))"
EVAL_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/challenges/$CHAL_ID/attempts" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $STU_TOKEN" \
     -d "{\"submitted_code\":\"$CODE\", \"language_id\":71}")

EVAL_BODY=$(echo "$EVAL_RESP" | head -n -1)
EVAL_STATUS=$(echo "$EVAL_RESP" | tail -n 1)
echo "   -> HTTP $EVAL_STATUS"
echo "   -> Estado guardado en BDD:"
echo "$EVAL_BODY" | grep -oP '"status":"\K[^"]+' | sed 's/^/      Estado: /'
echo "   -> Feedback de Judge0 (Vacio si hubo error de API):"
echo "$EVAL_BODY" | grep -oP '"stderr":"\K[^"]*' | sed 's/^/      Error (si lo hay): /'

echo "================================================="
