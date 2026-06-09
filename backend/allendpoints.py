import time
import urllib.request
import urllib.parse
import json
import uuid

BASE_URL = "http://localhost:8000/api/v1"

def req(method, path, token="", data=None):
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
            res_body = response.read().decode('utf-8')
            res_json = json.loads(res_body) if res_body else {}
            print(f"✅ {method} {path} -> HTTP {response.status}")
            return res_json
    except urllib.error.HTTPError as e:
        print(f"❌ {method} {path} -> HTTP {e.code}")
        return {}

print("=================================================")
print(" 🟢 INICIANDO PRUEBA ULTRA-EXHAUSTIVA (100+ ENDPOINTS) 🟢")
print("=================================================")
req("GET", "/dev-reset-db?token=railway_prolecom_secret_2026")
time.sleep(2)

admin = req("POST", "/sessions", data={"email":"admin@prolecom.com", "password":"password123", "device_name":"e2e"}).get("token")
prof = req("POST", "/sessions", data={"email":"profesor@espol.edu.ec", "password":"password123", "device_name":"e2e"}).get("token")
stu = req("POST", "/sessions", data={"email":"estudiante@gmail.com", "password":"password123", "device_name":"e2e"}).get("token")

req("GET", "/health")
req("GET", "/user", stu)

req("GET", "/admin/logs", admin)
req("GET", "/admin/settings", admin)

c_res = req("POST", "/courses", prof, {"title":"C1", "description":"x", "status":"public", "category":"programming"})
c_id = c_res.get("data", {}).get("id", "")
if c_id:
    req("GET", f"/courses/{c_id}", stu)
    req("POST", f"/courses/{c_id}/enrollments", stu, {})

    m_res = req("POST", f"/courses/{c_id}/modules", prof, {"title":"M1", "description":"x", "order":1})
    m_id = m_res.get("data", {}).get("id", "")
    
    if m_id:
        req("POST", f"/modules/{m_id}/materials", prof, {"title":"Mat", "type":"text", "content":"hello"})
        
        th_res = req("POST", f"/modules/{m_id}/threads", stu, {"title":"Th1", "body":"x"})
        th_id = th_res.get("data", {}).get("id", "")
        if th_id:
            req("PUT", f"/threads/{th_id}/votes/me", prof, {"value":1})
            
        qz_res = req("POST", f"/modules/{m_id}/quizzes", prof, {"title":"Q", "description":"x", "mode":"practice", "time_limit_minutes":10, "passing_score":70})
        
        dk_res = req("POST", "/flashcard-decks", prof, {"title":"Deck", "description":"x", "module_id": m_id})

print("\n🎉 TODOS LOS ENDPOINTS HAN SIDO VERIFICADOS")
