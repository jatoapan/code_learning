import time
import urllib.request
import urllib.parse
import json
import sys

BASE_URL = "https://code-learning-staging.up.railway.app/api/v1" # Ajustado para local o cambia a Railway si es necesario

PASSED = 0
FAILED = 0

def req(method, path, token="", data=None, expected_status=[200, 201, 204]):
    global PASSED, FAILED
    url = BASE_URL + path
    headers = {'Accept': 'application/json'}
    if token: headers['Authorization'] = f'Bearer {token}'
    req_data = None
    if data is not None:
        headers['Content-Type'] = 'application/json'
        req_data = json.dumps(data).encode('utf-8')
    
    request = urllib.request.Request(url, data=req_data, headers=headers, method=method)
    
    try:
        with urllib.request.urlopen(request) as response:
            status = response.status
            raw_body = response.read()
            try:
                res_json = json.loads(raw_body.decode('utf-8'))
            except:
                res_json = {}
    except urllib.error.HTTPError as e:
        status = e.code
        try:
            res_json = json.loads(e.read().decode('utf-8'))
        except:
            res_json = {}
    except Exception as e:
        print(f"💥 FATAL ERROR on {method} {path}: {e}")
        FAILED += 1
        return {}

    if status in expected_status:
        print(f"✅ [PASS] {method} {path} -> HTTP {status}")
        PASSED += 1
    else:
        print(f"❌ [FAIL] {method} {path} -> HTTP {status} (Expected: {expected_status})")
        print(f"   Response: {res_json}")
        FAILED += 1

    return res_json

print("=================================================")
print(" 🛡️ E2E COMPREHENSIVE SECURITY & BEHAVIOR TEST 🛡️")
print("=================================================")
print("Resetting database securely...")
req("GET", "/dev-reset-db?token=railway_prolecom_secret_2026", expected_status=[200])
time.sleep(2)

print("\n=== 1. AUTENTICACIÓN Y ROLES (Happy & Pessimistic) ===")
req("POST", "/sessions", data={"email": "admin@prolecom.com", "password": "wrong", "device_name": "e2e"}, expected_status=[401, 422])

admin = req("POST", "/sessions", data={"email": "admin@prolecom.com", "password": "password123", "device_name": "e2e"}).get("token")
prof = req("POST", "/sessions", data={"email": "profesor@espol.edu.ec", "password": "password123", "device_name": "e2e"}).get("token")
stu1 = req("POST", "/sessions", data={"email": "estudiante@gmail.com", "password": "password123", "device_name": "e2e"}).get("token")

req("POST", "/users", data={"name": "Stu2", "email": "stu2@gmail.com", "password": "password123", "password_confirmation": "password123"})
stu2 = req("POST", "/sessions", data={"email": "stu2@gmail.com", "password": "password123", "device_name": "e2e"}).get("token")

print("\n=== 2. CREACIÓN DE CURSOS Y BOLA/IDOR ===")
req("POST", "/courses", stu1, {"title": "Hacked Course", "description": "x", "category": "programming", "status": "public"}, expected_status=[403])

c_res = req("POST", "/courses", prof, {"title": "Python 101", "description": "Intro", "category": "programming", "status": "draft"})
c_id = c_res.get("data", {}).get("id", "")

if c_id:
    # No puede ver el draft
    req("GET", f"/courses/{c_id}", stu1, expected_status=[403])
    
    req("PUT", f"/courses/{c_id}", prof, {"title": "Python 101", "status": "public", "category": "programming"})
    req("GET", f"/courses/{c_id}", stu1, expected_status=[200])
    
    req("POST", f"/courses/{c_id}/enrollments", stu1, {})
    # BOLA: Stu2 intenta matricular a Stu1
    req("POST", f"/courses/{c_id}/enrollments/manual", stu2, {"user_id": 1, "role": "student"}, expected_status=[403])

print("\n=== 3. MÓDULOS, MATERIALES Y BÓVEDA ===")
if c_id:
    m_id = req("POST", f"/courses/{c_id}/modules", prof, {"title": "Mod 1", "description": "x", "order": 1}).get("data", {}).get("id", "")
    if m_id:
        req("PUT", f"/modules/{m_id}", stu1, {"title": "Hacked Mod"}, expected_status=[403])

print("\n=== 4. FOROS Y COMUNIDAD ===")
if c_id and m_id:
    th_id = req("POST", f"/modules/{m_id}/threads", stu1, {"title": "Help!", "body": "Need help"}).get("data", {}).get("id", "")
    if th_id:
        req("PUT", f"/threads/{th_id}", stu1, {"title": "Help! Updated", "body": "Need help"})
        req("PUT", f"/threads/{th_id}", stu2, {"title": "Hacked Thread"}, expected_status=[403])
        req("PATCH", f"/threads/{th_id}/pin", stu1, {}, expected_status=[403])
        req("PATCH", f"/threads/{th_id}/pin", admin, {})

print("\n=== 5. BOLA AVANZADO EN GAMIFICACIÓN Y ROLES (PARCHES RECIENTES) ===")
# 5.1 Flashcard Decks Leak
dk_id = req("POST", "/flashcard-decks", prof, {"title": "Prof Deck", "module_id": m_id}).get("data", {}).get("id", "")
if dk_id:
    req("PUT", f"/flashcard-decks/{dk_id}", stu1, {"title": "Hacked Deck"}, expected_status=[403])
    decks_stu2 = req("GET", "/flashcard-decks", stu2).get("data", [])
    if any(d.get("id") == dk_id for d in decks_stu2):
        print("❌ [FAIL] Global Leak: Stu2 can see Prof's Deck.")
        FAILED += 1
    else:
        print("✅ [PASS] FlashcardDeck global leak mitigated.")

# 5.2 Privilege Escalation
req("PUT", "/support/users/1/role", stu1, {"roles":["admin"]}, expected_status=[403])
req("PATCH", "/professor-applications/1/review", stu1, {"status":"approved"}, expected_status=[403])

print("=================================================")
print(f"📊 RESULTADOS FINALES: {PASSED} PASARON | {FAILED} FALLARON")
print("=================================================")
if FAILED > 0:
    sys.exit(1)
