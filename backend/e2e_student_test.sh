#!/bin/bash

BASE_URL="https://code-learning-staging.up.railway.app/api/v1"

echo "================================================="
echo " E2E LIVE TEST: FASE 4 - INTERACCIONES DE ESTUDIANTE"
echo "================================================="

echo "[1] Iniciando sesion como estudiante@gmail.com..."
LOGIN_RESP=$(curl -s -X POST "${BASE_URL}/sessions" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -d '{"email":"estudiante@gmail.com", "password":"password123", "device_name":"e2e_student"}')

TOKEN=$(echo $LOGIN_RESP | grep -oP '"token":"\K[^"]+')
if [ -z "$TOKEN" ]; then
    echo "❌ Fallo el login."
    exit 1
fi
echo "✅ Token capturado."

echo -e "\n[2] Obteniendo un Curso..."
COURSE_ID=$(curl -s -X GET "${BASE_URL}/courses" -H "Authorization: Bearer $TOKEN" | grep -oP '"id":\s*"?\K[^",}]+' | head -1 | tr -d '"')
echo "✅ Curso ID: $COURSE_ID"

echo -e "\n[3] Matriculandose en el Curso (POST /courses/{id}/enrollments)..."
ENROLL_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST "${BASE_URL}/courses/$COURSE_ID/enrollments" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d '{}')
echo "   -> HTTP $ENROLL_STATUS"

echo -e "\n[4] Creando un Reporte de Moderacion (POST /reports)..."
REPORT_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST "${BASE_URL}/reports" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d '{"reportable_type":"App\\Models\\Course","reportable_id":"'"$COURSE_ID"'","reason":"spam","description":"Curso engañoso"}')
echo "   -> HTTP $REPORT_STATUS"

echo -e "\n[5] Abandonando el Curso (DELETE /courses/{id}/enrollments/me)..."
DROP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X DELETE "${BASE_URL}/courses/$COURSE_ID/enrollments/me" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer $TOKEN")
echo "   -> HTTP $DROP_STATUS"

echo "================================================="
