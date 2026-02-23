# Dream-Notebook-Project

Dream Notebook is a web-based application that allows users to record dreams or diary entries
and analyze them using AI. The system will eventually visualize emotions, keywords, and patterns
using charts. This repository is developed as part of a university group project.

---

## Workshop 3 – Iteration 1: Core Diary Functionality

### Iteration Goal
The goal of Iteration 1 is to establish a stable foundation for the Dream Notebook system by
implementing basic diary functionality and setting up project structure and planning.
Advanced features such as AI analysis, databases, and visualizations are intentionally excluded
from this iteration.

---

### Implemented Features
- Console-based diary application allowing users to:
  - Create dream or diary entries
  - View saved entries
- Entries are stored using simple file-based storage
- Basic error handling to ensure stable execution

---

### Progress Summary
- GitHub project board created and maintained for Iteration 1
- User stories reviewed and organised into Todo, In Progress, and Done
- Core diary functionality implemented and tested
- Repository structure and documentation maintained

---

### Current Focus
Iteration 1 focuses on core diary functionality and clean project integration.
AI-based analysis, databases, and data visualisation features are planned for later iterations.


## Practical 5: Iteration 1 – Week 3 (Execution & Tracking)

### A) SRP & DRY Review (Code Quality)

**Files checked:** db_connect.php, register.php, login.php, logout.php, create_entry.php, view_entries.php

#### SRP (Single Responsibility Principle) findings
- db_connect.php: ✅ Single responsibility (DB connection + session_start)
- register.php: Validation + hashing + DB insert + debug output in one file → Plan: remove debug output, keep register logic only
- login.php: Validation + DB + session + output in one file → Plan: move reusable session check to a common file
- create_entry.php: Authorization + insert query + output in one file → Plan: move authorization to reusable file
- view_entries.php: DB query + UI output in one file → Plan: separate/reuse query logic later
- logout.php: ✅ Single responsibility (logout only)

#### DRY (Don’t Repeat Yourself) findings
- ✅ DRY improvement done: Reused DB connection using `include 'db_connect.php'` across files
- Repeated session check (Unauthorized) in protected pages → Plan: create `auth_check.php` and include it
- Repeated POST request check in multiple files → Plan: reuse helper/function
- Repeated validation patterns → Plan: reusable `validation.php`

### B) Tracking (GitHub)
- Project board used: Todo | In Progress | Done
- Labels used on tasks: todo, in-progress, done
- Cards/tasks were moved across columns to show progress