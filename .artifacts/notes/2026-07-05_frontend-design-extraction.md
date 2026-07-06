# Frontend Design Extraction — AI Job Portal

This note records the design guidelines, variables, and screen structures extracted from the codebase to assist in creating design copies and mockups in Figma.

## 1. Design Reference File Location
*   Active configuration: [colors.php](file:///d:/Projects/6amtech%20project/AI-Job-Portal-Single-Company-demo/config/branding/colors.php)
*   Active style source: [app.css](file:///d:/Projects/6amtech%20project/AI-Job-Portal-Single-Company-demo/resources/css/app.css)
*   Main template generator: [app.blade.php](file:///d:/Projects/6amtech%20project/AI-Job-Portal-Single-Company-demo/resources/views/app.blade.php)

## 2. Dynamic Theme Palette
*   **Primary Brand:** `#179753` (Emerald Green)
*   **Primary Light:** `#ecfdf5`
*   **Secondary:** `#64748b` (Slate)
*   **Success:** `#22c55e`
*   **Danger:** `#ef4444`
*   **Warning:** `#f59e0b`
*   **Info:** `#06b6d4`

### Admin Theme Mapping
*   **Sidebar Light/Dark:** `#ffffff` / `#0f172a`
*   **Sidebar Rail Light/Dark:** `#064e3b` / `#022c22`
*   **Header Light/Dark:** `#ffffffcc` / `#0f172acc`
*   **Content BG Light/Dark:** `#f8fafc` / `#020617`

## 3. Screen Inventory

### visitor/applicant views:
1.  **Careers Board:** `Guest/Careers/Index.vue`
2.  **Job Posting Detail:** `Guest/Careers/Show.vue`
3.  **Auth (Login/Register):** `Guest/Pages/Login.vue` & `Register.vue`
4.  **Contact Support:** `Guest/Contact/Show.vue`
5.  **Legal Disclaimers:** `Guest/Legal/Show.vue`
6.  **Assessment Quiz gateway:** `Guest/Assessment/QuizEntry.vue`
7.  **Assessment Quiz interface:** `Guest/Assessment/QuizBoard.vue`
8.  **Assessment Practical tasks:** `Guest/Assessment/TaskExam.vue`

### candidate portal views:
1.  **Dashboard / Pipeline Tracker:** `Candidate/Pages/Dashboard.vue`
2.  **Profile & Resume Editor:** `Candidate/Pages/ProfileEdit.vue`

### admin management views:
1.  **Overview Board:** `Admin/Pages/Dashboard.vue`
2.  **Hiring Kanban Board:** `Admin/Hiring/Evaluation/Show.vue`
3.  **Job Post Creator:** `Admin/Hiring/Jobs/Create.vue` & `Edit.vue`
4.  **Hiring Stages Configurator:** `Admin/Hiring/Pipeline/Builder.vue`
5.  **Task Queues:** `Admin/MyTasks/Index.vue`
6.  **Branding & Config:** `Admin/Business/Branding.vue` & `BusinessLogic.vue`
7.  **SMTP Setup:** `Admin/Business/EmailSettings.vue`
8.  **Identity Access Control:** `Admin/Roles/` & `Admin/Pages/Users.vue`
