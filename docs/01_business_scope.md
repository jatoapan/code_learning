# Especificación Definitiva del Alcance del MVP — Prolecom

> **Proyecto**: Prolecom (Programming Learning Community)  
> **Versión**: 1.0 (Final Scope)  
> **Estado**: Aprobado  
> **Audiencia**: Desarrolladores Backend / Frontend, Diseñadores de Producto  

Este documento constituye la **fuente de verdad oficial y definitiva** para el alcance funcional del Producto Mínimo Viable (MVP) de la plataforma **Prolecom**. Define los límites funcionales del sistema, los flujos críticos de usuario y las reglas de negocio inmutables. 

---

## 1. Módulos Funcionales en el Scope del MVP

El MVP de Prolecom se compone de los siguientes **12 módulos funcionales**:

### 1.1 Autenticación y Control de Accesos
*   **Métodos de Acceso**: Registro e inicio de sesión local mediante correo y contraseña únicamente.
*   **Validación de Credenciales**: Longitud mínima de contraseña de 8 caracteres.
*   **Mapeo Institucional Automático**: Al registrarse, el sistema compara el dominio del correo del usuario (ej: `espol.edu.ec`) con la base de instituciones registradas. Si existe coincidencia, lo afilia de manera automática a dicha institución.

### 1.2 Sistema de Roles Híbrido (Global y Local)
El sistema gestiona la autorización a través de dos niveles de roles:
1.  **Roles Globales (Spatie)**: 
    *   `student` (Estudiante): Rol asignado por defecto al registrarse.
    *   `professor` (Profesor): Habilitado únicamente tras la aprobación de una solicitud de validación por pares.
    *   `moderator` (Moderador): Rol de vigilancia de contenido en foros y denuncias.
    *   `support` (Soporte/Supervisor): Administrador operativo de cuentas, sanciones y roles globales.
    *   `admin` (Administrador): Acceso a configuraciones globales del sistema, analíticas y logs.
2.  **Roles Locales por Curso**:
    *   `ta` (Ayudante de Cátedra - Teaching Assistant): Rol local asignado por el profesor dueño de un curso a un estudiante específico dentro del ámbito exclusivo de ese curso.

### 1.3 Cursos, Módulos y Gestión de Syllabus
*   **Catálogo Público**: Listado dinámico de cursos con filtros de búsqueda por nombre, categoría y dificultad.
*   **Flujos de Inscripción**: 
    *   *Público*: El estudiante se inscribe libre y automáticamente.
    *   *Oculto / Privado*: El curso no está expuesto públicamente. Los estudiantes son inscritos o agregados manualmente por el profesor creador. No existe un flujo de solicitudes ni límites de capacidad de inscripción.
*   **Syllabus Composite**: Los módulos contienen materiales de estudio, quizzes y retos prácticos. El ordenamiento de estos recursos es libre dentro de cada módulo.
*   **Prerrequisitos de Módulos**: Los módulos pueden requerir la finalización secuencial de un módulo anterior. El acceso al contenido de un módulo bloqueado está inhabilitado.
*   **Formatos de Materiales**: Soporte de lecturas en formato PDF y enlaces externos de video. Tamaño de subida de archivos configurable (límite por defecto: 50 MB).

### 1.4 Foro de Preguntas y Respuestas (Q&A)
*   **Estructura Jerárquica**: Foros automáticos a tres niveles: General del curso, por Módulo y por Reto de Programación.
*   **Visibilidad por Progreso**: Los foros asociados a módulos y retos específicos están bloqueados para el estudiante hasta que este haya desbloqueado el contenido correspondiente en su avance secuencial.
*   **Funcionalidades Q&A**: Soporte para redacción en Markdown, sistema de votos (Upvote/Downvote) con puntuación acumulada en caché, y opción para que el Profesor o TA marque una respuesta como "Aceptada/Correcta".

### 1.5 IDE Integrado y Banco de Retos de Programación
*   **Editor Monaco**: Entorno de desarrollo enriquecido en el navegador con resaltado de sintaxis según el lenguaje del reto.
*   **Ejecución de Código en Sandbox**: Envío asíncrono del código a un motor de ejecución seguro. El frontend recibe el resultado en tiempo real mediante un evento WebSocket (Laravel Reverb) una vez finalizada la ejecución.
*   **Casos de Prueba (Test Cases)**: Validation con sets de entrada/salida (visibles y ocultos para el estudiante).
*   **Aprobación de Retos (TA Flow)**: Los ayudantes de cátedra (TAs) pueden crear retos, pero estos se guardan en estado "Pendiente de Revisión" y no son visibles para el curso hasta que el Profesor los aprueba.

### 1.6 Módulo de Evaluaciones (Quizzes y Exámenes)
El sistema diferencia dos comportamientos operativos según el modo de la evaluación:
*   **Modo Práctica (`practice`)**: Intentos ilimitados. Prevalece la nota más alta en el historial. Las respuestas correctas y explicaciones teóricas son visibles inmediatamente al finalizar. El temporizador es pausable.
*   **Modo Examen (`exam`)**: Intento único obligatorio. Temporizador estricto no pausable en el frontend. Las respuestas correctas y las explicaciones permanecen ocultas para todos los estudiantes hasta una fecha configurada por el profesor (`answers_visible_after`).

### 1.7 Gamificación, XP y Leaderboards
*   **Esquema de XP Dual**:
    *   *XP Local*: Puntos acumulados exclusivamente dentro de un curso por ver materiales, aprobar quizzes y resolver retos. Alimenta el ranking local del curso.
    *   *XP Global*: Suma total acumulada de la XP de todos los cursos en el perfil del usuario.
*   **Candidatos a TA**: El sistema destaca en el panel del profesor a los estudiantes que superen cierto umbral de XP local, sugiriéndolos para su promoción a Ayudante del curso.

### 1.8 Moderación y Denuncias
*   **Flujo de Reportes**: Estudiantes, Profesores y TAs pueden denunciar hilos, posts o perfiles de usuarios por spam, plagio, lenguaje ofensivo o deshonestidad académica.
*   **Acción del Moderador**: Panel con cola de denuncias. El moderador puede archivar la denuncia, ocultar contenido, bloquear hilos o suspender/banear cuentas de usuario. Cuenta con soporte de plantillas de respuesta predefinidas.
*   **Aval de Moderación**: Los moderadores pueden avalar de forma explícita hilos, posts de respuesta y materiales de estudio de alta calidad, marcándolos con una marca de tiempo `moderator_endorsed_at` para destacarlos.
*   **Escalación**: Opción de redirigir disputas complejas al rol de Soporte Técnico.

### 1.9 Desactivación de Cuenta y Anonimización
*   **Desactivación**: Solicitada por el usuario o ejecutada por Soporte. Revoca de inmediato los tokens de acceso activos, cambia el estado de la cuenta a `deactivated` y ejecuta de forma inmediata una anonimización irreversible y soft delete de la cuenta en cumplimiento con GDPR (sin período de gracia).

### 1.10 Auto-Estudio con Flashcards (SRS)
*   **Generación de Tarjetas**: Creación manual de tarjetas (pregunta/respuesta) o importación directa de preguntas falladas en quizzes del estudiante.
*   **Algoritmo SuperMemo SM-2**: Planificación de repasos espaciados basados en la calificación de dificultad dada por el usuario (0 a 5), calculando la fecha del próximo repaso (`next_review_at`).
*   **Evaluación Práctica**: Generación en memoria de cuestionarios rápidos de práctica a partir de un mazo de tarjetas seleccionado.

---

## 2. Restricciones de Negocio Inmutables

1.  **Integridad Académica**: Bajo ninguna circunstancia los foros Q&A deben mostrar la solución de código directa a un estudiante que no haya resuelto el reto previamente.
2.  **Aprobación de Contenido de TAs**: Ningún reto creado por un Ayudante de Cátedra (TA) puede publicarse en el catálogo del curso sin la aprobación explícita del Profesor dueño del curso.
3.  **Límite de Intentos en Exámenes**: Las evaluaciones en modo `exam` deben bloquear de forma estricta cualquier intento secundario una vez iniciado o finalizado el primer intento.

---

## 3. Diferido a Fase 2 (Fuera del Scope del MVP)

Para garantizar un desarrollo ágil y foco en las características core, se excluyen del MVP las siguientes características:

1.  **Búsqueda Global Unificada**: Buscador transversal en toda la plataforma desde un solo input.
2.  **Mensajería Privada y Foros en Tiempo Real**: Las notificaciones del sistema y actualizaciones del foro se realizarán en tiempo real utilizando Laravel Reverb (WebSockets). No habrá chat 1:1 en el MVP.
3.  **Integración con IA (Gemini API)**: El tutor de inteligencia artificial (AI Tutor) y el creador de contenido con IA (AI Creator) quedan postergados para fases posteriores.
4.  **Torneos y Equipos**: La modalidad competitiva por equipos y los torneos cronometrados quedan diferidos a futuras versiones del producto.
