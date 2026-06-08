#!/bin/bash

BASE_URL="https://code-learning-staging.up.railway.app/api/v1"

echo "================================================="
echo " E2E LIVE TEST: FASE 6 - SOPORTE Y MODERACION"
echo "================================================="

echo "[1] Iniciando sesion como estudiante (aspirante)..."
LOGIN_RESP=$(curl -s -X POST "${BASE_URL}/sessions" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -d '{"email":"estudiante@gmail.com", "password":"password123", "device_name":"e2e_app"}')
STUDENT_TOKEN=$(echo $LOGIN_RESP | grep -oP '"token":"\K[^"]+')

echo -e "\n[2] Estudiante envia solicitud para ser Profesor (POST /professor-applications)..."
APP_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/professor-applications" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $STUDENT_TOKEN" \
     -d '{"motivation":"Amo enseñar", "qualifications":"Ingeniero en Software"}')

APP_BODY=$(echo "$APP_RESP" | head -n -1)
APP_STATUS=$(echo "$APP_RESP" | tail -n 1)
echo "   -> HTTP $APP_STATUS"
APP_ID=$(echo "$APP_BODY" | grep -oP '"id":\s*"?\K[^",}]+' | head -1 | tr -d '"')

echo -e "\n[3] Iniciando sesion como Administrador/Soporte..."
ADMIN_LOGIN=$(curl -s -X POST "${BASE_URL}/sessions" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -d '{"email":"admin@espol.edu.ec", "password":"password123", "device_name":"e2e_admin"}')
ADMIN_TOKEN=$(echo $ADMIN_LOGIN | grep -oP '"token":"\K[^"]+')

echo -e "\n[4] Admin se auto-asigna la Solicitud (PATCH /professor-applications/{id}/assign)..."
ASSIGN_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X PATCH "${BASE_URL}/professor-applications/$APP_ID/assign" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer $ADMIN_TOKEN" -d '')
echo "   -> HTTP $ASSIGN_STATUS"

echo -e "\n[5] Admin aprueba la Solicitud (PATCH /professor-applications/{id}/review)..."
REVIEW_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X PATCH "${BASE_URL}/professor-applications/$APP_ID/review" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $ADMIN_TOKEN" \
     -d '{"status":"approved", "reviewer_comment":"Bienvenido al equipo"}')
echo "   -> HTTP $REVIEW_STATUS"

echo -e "\n[6] Creando y Endosando un Thread en el Foro..."
# 1. Obtener un curso
COURSE_ID=$(curl -s -X GET "${BASE_URL}/courses" -H "Authorization: Bearer $STUDENT_TOKEN" | grep -oP '"id":\s*"?\K[^",}]+' | head -1 | tr -d '"')
# 2. Crear Thread
THREAD_ID=$(curl -s -X POST "${BASE_URL}/courses/$COURSE_ID/threads" -H "Accept: application/json" -H "Content-Type: application/json" -H "Authorization: Bearer $STUDENT_TOKEN" -d '{"title":"Pregunta Increible", "body":"Ayuda con Laravel"}' | grep -oP '"id":\s*"?\K[^",}]+' | head -1 | tr -d '"')
# 3. Endosar Thread
ENDORSE_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST "${BASE_URL}/threads/$THREAD_ID/endorsements" -H "Accept: application/json" -H "Authorization: Bearer $ADMIN_TOKEN" -d '')
echo "   -> HTTP $ENDORSE_STATUS"

echo "================================================="
