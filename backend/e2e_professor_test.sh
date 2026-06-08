#!/bin/bash

BASE_URL="https://code-learning-staging.up.railway.app/api/v1"

echo "================================================="
echo " E2E LIVE TEST: FASE 2 - GESTION DE CURSOS (PROFESOR)"
echo "================================================="

echo "[1] Iniciando sesion como profesor@espol.edu.ec..."
LOGIN_RESP=$(curl -s -X POST "${BASE_URL}/sessions" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -d '{"email":"profesor@espol.edu.ec", "password":"password123", "device_name":"e2e_professor"}')

TOKEN=$(echo $LOGIN_RESP | grep -oP '"token":"\K[^"]+')

if [ -z "$TOKEN" ]; then
    echo "❌ Fallo el login de profesor."
    exit 1
fi
echo "✅ Token de Profesor capturado."

echo -e "\n[2] Creando un nuevo Curso (POST /courses)..."
COURSE_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/courses" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d '{"title":"Curso Avanzado E2E", "description":"Curso creado para probar relaciones DB", "category":"programming", "status":"public", "has_leaderboard":true}')

COURSE_BODY=$(echo "$COURSE_RESP" | head -n -1)
COURSE_STATUS=$(echo "$COURSE_RESP" | tail -n 1)

echo "   -> HTTP $COURSE_STATUS"
if [ "$COURSE_STATUS" -ne 201 ]; then
    echo "❌ Error al crear curso:"
    echo "$COURSE_BODY"
    exit 1
fi

COURSE_ID=$(echo "$COURSE_BODY" | grep -o '"id":"[^"]*' | head -1 | cut -d'"' -f4)

if [ -z "$COURSE_ID" ]; then
    echo "❌ No se pudo extraer el COURSE_ID de la base de datos."
    exit 1
fi
echo "✅ Curso guardado en BD con UUID: $COURSE_ID"

echo -e "\n[3] Actualizando titulo del Curso (PUT /courses/{id})..."
UPDATE_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X PUT "${BASE_URL}/courses/$COURSE_ID" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d '{"description":"Actualizado por Script E2E (Verificando UPDATE en DB)"}')
echo "   -> HTTP $UPDATE_STATUS"

echo -e "\n[4] Creando un Modulo (POST /courses/{id}/modules)..."
MODULE_RESP=$(curl -s -w "\n%{http_code}" -X POST "${BASE_URL}/courses/$COURSE_ID/modules" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d '{"title":"Modulo 1: Introduccion Arquitectonica", "description":"Primer modulo E2E", "order":1}')

MODULE_BODY=$(echo "$MODULE_RESP" | head -n -1)
MODULE_STATUS=$(echo "$MODULE_RESP" | tail -n 1)
echo "   -> HTTP $MODULE_STATUS"

MODULE_ID=$(echo "$MODULE_BODY" | grep -oP '"id":\s*"?\K[^",}]+' | head -1)
if [ -n "$MODULE_ID" ]; then
    echo "✅ Modulo guardado en BD con UUID: $MODULE_ID"

    echo -e "\n[5] Agregando Material al Modulo (POST /modules/{id}/materials)..."
    MAT_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST "${BASE_URL}/modules/$MODULE_ID/materials" \
         -H "Accept: application/json" \
         -H "Content-Type: application/json" \
         -H "Authorization: Bearer $TOKEN" \
         -d '{"title":"Video Intro", "type":"video_link", "content":"https://youtube.com/watch?v=mock", "estimated_minutes":15, "is_required":true}')
    echo "   -> HTTP $MAT_STATUS"
else
    echo "❌ Fallo al crear o extraer el modulo."
    echo "$MODULE_BODY"
fi

echo -e "\n[6] Consultando el Curso Publico para verificar Relaciones (GET /courses/{id})..."
GET_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "${BASE_URL}/courses/$COURSE_ID" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer $TOKEN")
echo "   -> HTTP $GET_STATUS (Deberia retornar el Curso + Modulos + Materiales anidados)"

echo "================================================="
