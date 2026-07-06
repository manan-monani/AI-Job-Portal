# AI Job Portal — Frontend Design & Screen Specifications

This document outlines the design tokens, layout structures, and screen breakdowns for the **AI Job Portal (Single Company)** frontend. It serves as a specification for creating Figma mockups and copies.

---

## 1. Design Tokens & Styling

The frontend styling utilizes Tailwind CSS v4 custom theme bindings defined in [app.css](file:///d:/Projects/6amtech%20project/AI-Job-Portal-Single-Company-demo/resources/css/app.css) and configurable via [colors.php](file:///d:/Projects/6amtech%20project/AI-Job-Portal-Single-Company-demo/config/branding/colors.php).

### A. Color Palette

#### Brand & Core Accents
*   **Primary Brand Color (Emerald Green):** `#179753` (used for buttons, active states, key CTAs)
*   **Primary Light (Mint BG):** `#ecfdf5` (soft background for highlighted elements, badges)
*   **Primary Dark Text (Green Accent):** `#6ee7b7` (for high contrast text in dark mode)
*   **Secondary Color (Slate Neutral):** `#64748b` (used for body text, subtitles, secondary elements)

#### Status Indicators
*   **Success:** `#22c55e` (Green — success toasts, accepted applicants, passed exams)
*   **Danger:** `#ef4444` (Red — error toasts, rejected applicants, alerts)
*   **Warning:** `#f59e0b` (Amber — warning flags, pending actions)
*   **Info:** `#06b6d4` (Cyan — general informative badges)

#### Admin Dashboard Theme (Light / Dark Modes)
| Element | Light Mode Value | Dark Mode Value |
| :--- | :--- | :--- |
| **Sidebar BG** | `#ffffff` | `#0f172a` (Slate 900) |
| **Sidebar Border** | `#e2e8f0` | `#1e293b` |
| **Sidebar Rail BG** | `#064e3b` (Deep Emerald) | `#022c22` |
| **Active Item BG** | `#ecfdf5` (Primary Light) | `#17975320` (12% Opacity Primary) |
| **Active Item Text / Border** | `#179753` (Primary) | `#6ee7b7` (Primary Dark Text) |
| **Header BG** | `#ffffffcc` (80% opacity) | `#0f172acc` (80% opacity) |
| **Content Area BG** | `#f8fafc` | `#020617` (Almost Black) |
| **Card BG** | `#ffffff` | `#0f172a` |
| **Card Border** | `#f1f5f9` | `#1e293b` |

### B. Typography & Fonts
*   **Primary Font Family:** `Inter, sans-serif` or `Instrument Sans, sans-serif` (main interface headings, labels, and text)
*   **Secondary Font Family:** `Roboto, sans-serif` (fallback and body paragraphs)

---

## 2. Layout Frameworks

The application implements five main structural layout frames:
1.  **PublicLayout (`PublicLayout.vue`):** Headers, footers, language switches, and custom branding wrappers for all applicant-facing public screens.
2.  **AdminLayout (`AdminLayout.vue`):** Collapsible navigation sidebar, administrative header bar, user menu, and dark/light mode toggle.
3.  **CustomerLayout (`CustomerLayout.vue`):** Candidate portal framing featuring a simplified sidebar (Dashboard, Profile) and application status tracker.
4.  **GuestLayout (`GuestLayout.vue`):** Centered card form structure with dynamic brand logos for login and registration views.
5.  **BlankLayout (`BlankLayout.vue`):** Standard full-width grid frame without navigation headers, used for focused environments like quizzes and tasks.

---

## 3. Screen Summaries by Role

Below is the directory mapping and brief description of all views used in the application.

### A. Public & Guest Screens (Applicant Portal)

*   **Careers Home / Job Board** (`Guest/Careers/Index.vue` / `Welcome.vue`)
    *   *Purpose:* Landing page displaying open roles, search bar, department filters, and company values.
*   **Job Specification & Application** (`Guest/Careers/Show.vue`)
    *   *Purpose:* Single job posting detail view with a sidebar application drawer/form (supports resume parser uploads, custom applicant screening questions).
*   **Candidate Authentication** (`Guest/Pages/Login.vue` & `Register.vue`)
    *   *Purpose:* Sign-in and sign-up pages for candidates to track applications.
*   **Contact Us & Support** (`Guest/Contact/Show.vue`)
    *   *Purpose:* Message submission form to reach the company's hiring team.
*   **Legal / Information Pages** (`Guest/Legal/Show.vue`)
    *   *Purpose:* Clean reading layout for legal disclosures (Privacy Policy, Terms of Service, About Us).
*   **Assessment - Quiz Gateway** (`Guest/Assessment/QuizEntry.vue`)
    *   *Purpose:* Instruction and introduction card displaying quiz details (time limit, questions count) and "Start Quiz" CTA.
*   **Assessment - Active Quiz Board** (`Guest/Assessment/QuizBoard.vue`)
    *   *Purpose:* Focused interface displaying a live timer, progress bar, multiple-choice question layout, and navigation triggers.
*   **Assessment - Task Submission** (`Guest/Assessment/TaskExam.vue`)
    *   *Purpose:* Assignment brief workspace displaying instructions, reference files, submission text editor, and file upload fields.
*   **Assessment - Validation Messages** (`AlreadyParticipated.vue` & `NotEligible.vue`)
    *   *Purpose:* Informational screen alerts warning users who have completed or do not qualify for the assessment.

### B. Candidate Portal (Customer Area)

*   **Candidate Dashboard** (`Candidate/Pages/Dashboard.vue`)
    *   *Purpose:* List of current applications, showing progress in the ATS pipeline (e.g. Screening, Assessment, Interview), active invitations to quizzes/tasks, and status messages.
*   **Candidate Profile Editor** (`Candidate/Pages/ProfileEdit.vue`)
    *   *Purpose:* Profile detail editor for contact info, CV file updates, education, experience, and custom skill tags.

### C. Administrative Portal (Backoffice)

*   **Admin Dashboard** (`Admin/Pages/Dashboard.vue`)
    *   *Purpose:* Executive metrics display presenting statistics (Total Jobs, Total Candidates, Active Stages, Hired candidates) and visual dashboard cards.
*   **Hiring Kanban & Candidate Evaluator** (`Admin/Hiring/Evaluation/Index.vue` & `Show.vue`)
    *   *Purpose:* Visual pipeline workflow. Contains applicant cards that can be dragged across hiring stages, candidate detail modals, submission grading screens (for quizzes/tasks), and internal evaluation comments.
*   **Job Builder & Manager** (`Admin/Hiring/Jobs/Index.vue`, `Create.vue`, `Edit.vue`)
    *   *Purpose:* Tooling to write, customize, and edit job description postings, configure screening questionnaire lists, and define pipeline requirements.
*   **Hiring Pipeline Configurator** (`Admin/Hiring/Pipeline/Builder.vue`)
    *   *Purpose:* Editor to customize specific workflow stages (e.g., Screening, Quiz, Coding Challenge, Panel Interview) and define automated email response triggers.
*   **Departments Management** (`Admin/Hiring/Departments/Index.vue`)
    *   *Purpose:* Simple list/modal form system to create and configure corporate departments.
*   **Candidate Tags Directory** (`Admin/Hiring/Tags/Index.vue`)
    *   *Purpose:* Directory of tags used to label candidate backgrounds (e.g., "Senior Vue", "Laravel Expert").
*   **Tasks Checklist Board** (`Admin/MyTasks/Index.vue`)
    *   *Purpose:* Internal to-do list showing pending review actions (e.g., manual resume checks, grading tasks, scheduled interviews).
*   **All Candidates Index** (`Admin/Hiring/Candidates/Index.vue`)
    *   *Purpose:* Searching, sorting, and indexing database of all applications.
*   **System Settings & Custom Branding** (`Admin/Business/Branding.vue` & `BusinessLogic.vue`)
    *   *Purpose:* Customization interface for uploading site logo/icon, updating brand color values, primary font selections, and managing system logic details.
*   **Legal Page Editor** (`Admin/Legal/Index.vue`)
    *   *Purpose:* Content editor to publish/update information displayed on the public Legal pages.
*   **Email Templates Setup** (`Admin/Business/EmailSettings.vue`)
    *   *Purpose:* Settings panel for SMTP connections and composing trigger-based automatic response emails.
*   **Access Control - Roles & Users** (`Admin/Roles/` & `Admin/Pages/Users.vue`)
    *   *Purpose:* Permission panels to configure administrative groups, access policies, and manage internal HR staff accounts.
*   **Security & Audit Log** (`Admin/ActivityLog/Index.vue` & `Show.vue`)
    *   *Purpose:* View/audit log showing records of HR and admin user events (deletions, modifications).
*   **Admin Profile** (`Admin/Pages/Profile.vue` & `Admin/Auth/Login.vue`)
    *   *Purpose:* Login gateway and personal preferences editor for admin team members.
