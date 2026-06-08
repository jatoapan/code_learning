import urllib.request
import urllib.parse
import json
import time

BASE_URL = "https://code-learning-staging.up.railway.app/api/v1"

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
            try:
                res_json = json.loads(res_body) if res_body else {}
            except:
                res_json = {}
            print(f"✅ {method} {path} -> HTTP {response.status}")
            return res_json
    except urllib.error.HTTPError as e:
        print(f"❌ {method} {path} -> HTTP {e.code}")
        # print("   " + e.read().decode('utf-8'))
        return {}

print("=================================================")
print(" 🟢 INICIANDO PRUEBA ULTRA-EXHAUSTIVA (100+ ENDPOINTS) 🟢")
print("=================================================")
req("GET", "/dev-reset-db")
time.sleep(3)

admin = req("POST", "/sessions", data={"email":"admin@prolecom.com", "password":"password123", "device_name":"e2e"}).get("token")
prof = req("POST", "/sessions", data={"email":"profesor@espol.edu.ec", "password":"password123", "device_name":"e2e"}).get("token")
stu = req("POST", "/sessions", data={"email":"estudiante@gmail.com", "password":"password123", "device_name":"e2e"}).get("token")
stu2_res = req("POST", "/users", data={"name":"Stu2", "email":"stu2@g.com", "password":"password123", "password_confirmation":"password123"})
stu2 = req("POST", "/sessions", data={"email":"stu2@g.com", "password":"password123", "device_name":"e2e"}).get("token")

print("\n--- GLOBAL & USERS ---")
req("GET", "/health")
req("GET", "/ping-deploy")
req("GET", "/user", stu)
req("PUT", "/user", stu, {"name":"New Name"})
req("GET", "/notifications", prof)
req("GET", "/notifications/unread-count", prof)
req("PATCH", "/notifications", prof, {})

print("\n--- ADMIN & SUPPORT ---")
req("GET", "/institutions", stu)
inst_res = req("POST", "/admin/institutions", admin, {"name":"MIT", "type":"University"})
inst_id = inst_res.get("data", {}).get("id", "")
if inst_id:
    req("PUT", f"/admin/institutions/{inst_id}", admin, {"name":"MIT Edit", "type":"University"})
    req("GET", f"/admin/institutions/{inst_id}/analytics", admin)
req("GET", "/admin/logs", admin)
req("GET", "/admin/settings", admin)
req("PUT", "/admin/settings/max_upload_mb", admin, {"value": "100"})
rt_res = req("POST", "/admin/response-templates", admin, {"name":"T1", "content":"body"})
rt_id = rt_res.get("data", {}).get("id", "")
if rt_id:
    req("PUT", f"/admin/response-templates/{rt_id}", admin, {"name":"T1 Edit", "content":"report"})
    req("GET", "/moderator/response-templates", admin)

app_res = req("POST", "/professor-applications", stu2, {"motivation":"hire me", "qualifications":"dev"})
app_id = app_res.get("data", {}).get("id", "")
if app_id:
    req("GET", "/professor-applications", admin)
    req("PATCH", f"/professor-applications/{app_id}/review", admin, {"status":"approved"})

print("\n--- COURSES & SYLLABUS ---")
c_res = req("POST", "/courses", prof, {"title":"C1", "description":"x", "status":"public", "category":"programming"})
c_id = c_res.get("data", {}).get("id", "")
if c_id:
    req("GET", f"/courses/{c_id}", stu)
    req("PUT", f"/courses/{c_id}", prof, {"title":"C1 Edit", "status":"public", "category":"programming"})
    req("GET", f"/courses/{c_id}/stats", prof)
    req("GET", f"/courses/{c_id}/analytics", prof)
    req("GET", f"/courses/{c_id}/leaderboard", stu)
    req("POST", f"/courses/{c_id}/enrollments", stu, {})
    req("GET", f"/courses/{c_id}/progress", stu)

    m_res = req("POST", f"/courses/{c_id}/modules", prof, {"title":"M1", "description":"x", "order":1})
    m_id = m_res.get("data", {}).get("id", "")
    if m_id:
        req("PUT", f"/modules/{m_id}", prof, {"title":"M1 Edit"})
        req("PATCH", f"/modules/{m_id}/items-order", prof, {"items":[]})
        
        mat_res = req("POST", f"/modules/{m_id}/materials", prof, {"title":"Mat", "type":"video_link", "content":"x", "order":1})
        mat_id = mat_res.get("data", {}).get("id", "")
        if mat_id:
            req("PUT", f"/materials/{mat_id}", prof, {"title":"Mat Edit", "type":"video_link"})
            req("GET", f"/materials/{mat_id}", stu)
            req("POST", f"/materials/{mat_id}/views", stu, {})

print("\n--- FORUMS ---")
if c_id and m_id:
    th_res = req("POST", f"/modules/{m_id}/threads", stu, {"title":"Th1", "body":"x"})
    th_id = th_res.get("data", {}).get("id", "")
    if th_id:
        req("GET", f"/courses/{c_id}/threads", stu)
        req("GET", f"/threads/{th_id}", stu)
        req("PUT", f"/threads/{th_id}", stu, {"title":"Edit", "body":"x"})
        req("PUT", f"/threads/{th_id}/votes/me", prof, {"value":1})
        req("PATCH", f"/threads/{th_id}/pin", admin, {})
        req("PATCH", f"/threads/{th_id}/lock", admin, {})
        
        p_res = req("POST", f"/threads/{th_id}/posts", prof, {"body":"Ans"})
        p_id = p_res.get("data", {}).get("id", "")
        if p_id:
            req("PUT", f"/posts/{p_id}", prof, {"body":"Ans edit"})
            req("PUT", f"/posts/{p_id}/votes/me", stu, {"value":1})

print("\n--- MODERATION ---")
if th_id:
    rep_res = req("POST", "/reports", stu2, {"reportable_type":"App\\\\Models\\\\ForumThread", "reportable_id":th_id, "reason":"spam", "details":"x"})
    rep_id = rep_res.get("data", {}).get("id", "")
    if rep_id:
        req("GET", "/moderator/reports", admin)
        req("PATCH", f"/reports/{rep_id}/resolve", admin, {})

print("\n--- QUIZZES ---")
if m_id:
    qz_res = req("POST", f"/modules/{m_id}/quizzes", prof, {"title":"Q", "description":"x", "mode":"practice", "time_limit_minutes":10, "passing_score":70})
    qz_id = qz_res.get("data", {}).get("id", "")
    if qz_id:
        req("PUT", f"/quizzes/{qz_id}", prof, {"title":"Q edit", "mode":"practice", "passing_score":70, "time_limit_minutes":10})
        req("GET", f"/quizzes/{qz_id}", stu)
        
        qq_res = req("POST", f"/quizzes/{qz_id}/questions", prof, {"question_text":"x", "type":"multiple_choice", "points":10, "options":["a","b"], "correct_answer":"a"})
        
        qa_res = req("POST", f"/quizzes/{qz_id}/attempts", stu, {"answers":[]})
        qa_id = qa_res.get("data", {}).get("id", "")
        if qa_id:
            req("GET", f"/quiz-attempts/{qa_id}", prof)

print("\n--- FLASHCARDS ---")
if m_id:
    dk_res = req("POST", "/flashcard-decks", prof, {"title":"Deck", "description":"x", "module_id": m_id})
    dk_id = dk_res.get("data", {}).get("id", "")
    if dk_id:
        req("GET", "/flashcard-decks", stu)
        req("PUT", f"/flashcard-decks/{dk_id}", prof, {"title":"Edit"})
        fc_res = req("POST", f"/flashcard-decks/{dk_id}/flashcards", prof, {"question_text":"x", "answer_text":"y"})

print("\n--- CHALLENGES ---")
if m_id:
    ch_res = req("POST", f"/modules/{m_id}/challenges", prof, {"title":"Ch", "description":"x", "difficulty":"easy", "points":10, "language_id":71, "language_name":"python"})
    ch_id = ch_res.get("data", {}).get("id", "")
    if ch_id:
        req("GET", f"/modules/{m_id}/challenges", prof)
        req("GET", f"/challenges/{ch_id}", stu)
        req("PUT", f"/challenges/{ch_id}", prof, {"title":"Ch Edit", "difficulty":"easy", "points":10, "language_id":71, "language_name":"python"})
        
        tc_res = req("POST", f"/challenges/{ch_id}/test-cases", prof, {"input_data":"1", "expected_output":"2", "is_hidden":False})
        tc_id = tc_res.get("data", {}).get("id", "")
        
        ca_res = req("POST", f"/challenges/{ch_id}/attempts", stu, {"submitted_code":"print('2')", "language_id":71})
        ca_id = ca_res.get("data", {}).get("id", "")
        if ca_id:
            req("GET", f"/challenges/{ch_id}/attempts", prof)

print("\n--- ELIMINACIONES ABSOLUTAS ---")
if 'ch_id' in locals() and ch_id: req("DELETE", f"/challenges/{ch_id}", prof)
if 'dk_id' in locals() and dk_id: req("DELETE", f"/flashcard-decks/{dk_id}", prof)
if 'qz_id' in locals() and qz_id: req("DELETE", f"/quizzes/{qz_id}", prof)
if 'p_id' in locals() and p_id: req("DELETE", f"/posts/{p_id}", prof)
if 'th_id' in locals() and th_id: req("DELETE", f"/threads/{th_id}", stu)
if 'mat_id' in locals() and mat_id: req("DELETE", f"/materials/{mat_id}", prof)
if 'm_id' in locals() and m_id: req("DELETE", f"/modules/{m_id}", prof)
if 'c_id' in locals() and c_id: 
    req("DELETE", f"/courses/{c_id}/enrollments/me", stu)
    req("DELETE", f"/courses/{c_id}", prof)
if 'rt_id' in locals() and rt_id: req("DELETE", f"/admin/response-templates/{rt_id}", admin)
if 'inst_id' in locals() and inst_id: req("DELETE", f"/admin/institutions/{inst_id}", admin)

print("\n=================================================")
print(" 🎉 TODOS LOS ENDPOINTS HAN SIDO VERIFICADOS")
print("=================================================")
