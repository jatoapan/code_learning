#!/bin/bash

BASE_URL="https://code-learning-staging.up.railway.app/api/v1"

echo "================================================="
echo " E2E LIVE TEST: FASE 3 - COMUNIDAD Y FOROS (Q&A)"
echo "================================================="

echo "[1] Iniciando sesion como estudiante@gmail.com..."
LOGIN_RESP=$(curl -s -X POST "${BASE_URL}/sessions" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -d '{"email":"estudiante@gmail.com", "password":"password123", "device_name":"e2e_forum"}')

TOKEN=$(echo $LOGIN_RESP | grep -oP '"token":"\K[^"]+')
if [ -z "$TOKEN" ]; then
    echo "❌ Fallo el login."
    exit 1
fi
echo "✅ Token de Estudiante capturado."

echo -e "\n[2] Obteniendo un Curso de la BD..."
COURSE_ID=$(curl -s -X GET "${BASE_URL}/courses" -H "Authorization: Bearer $TOKEN" | grep -oP '"id":\s*"?\K[^",}]+' | head -1 | tr -d '"')
if [ -z "$COURSE_ID" ]; then
    echo "❌ No hay cursos disponibles para crear el hilo."
    exit 1
fi
echo "✅ Usando Curso ID: $COURSE_ID"

echo -e "\n[3] Creando una Pregunta (Thread) en el Curso (POST /courses/{id}/threads)..."
THREAD_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/courses/$COURSE_ID/threads" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d '{"title":"¿Como instalo Docker?", "body":"No entiendo como arrancar el contenedor en Windows"}')

THREAD_BODY=$(echo "$THREAD_RESP" | head -n -1)
THREAD_STATUS=$(echo "$THREAD_RESP" | tail -n 1)
echo "   -> HTTP $THREAD_STATUS"

THREAD_ID=$(echo "$THREAD_BODY" | grep -oP '"id":\s*"?\K[^",}]+' | head -1 | tr -d '"')
if [ -z "$THREAD_ID" ]; then
    echo "❌ Fallo al extraer THREAD_ID."
    echo "$THREAD_BODY"
    exit 1
fi
echo "✅ Hilo guardado en BD con UUID: $THREAD_ID"

echo -e "\n[4] Respondiendo a la Pregunta (POST /threads/{id}/posts)..."
POST_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/threads/$THREAD_ID/posts" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d '{"body":"Usa Docker Desktop y asegurate de tener WSL2 activado."}')

POST_BODY=$(echo "$POST_RESP" | head -n -1)
POST_STATUS=$(echo "$POST_RESP" | tail -n 1)
echo "   -> HTTP $POST_STATUS"

POST_ID=$(echo "$POST_BODY" | grep -oP '"id":\s*"?\K[^",}]+' | head -1 | tr -d '"')
if [ -z "$POST_ID" ]; then
    echo "❌ Fallo al extraer POST_ID."
    echo "$POST_BODY"
    exit 1
fi
echo "✅ Respuesta (Post) guardada en BD con UUID: $POST_ID"

echo -e "\n[5] Dando un Upvote a la Respuesta (PUT /posts/{id}/votes/me)..."
VOTE_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X PUT "${BASE_URL}/posts/$POST_ID/votes/me" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d '{"value":1}')
echo "   -> HTTP $VOTE_STATUS"

echo -e "\n[6] Consultando el Hilo Completo (GET /threads/{id})..."
GET_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "${BASE_URL}/threads/$THREAD_ID" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer $TOKEN")
echo "   -> HTTP $GET_STATUS (Deberia retornar Hilo + Posts)"

echo "================================================="
