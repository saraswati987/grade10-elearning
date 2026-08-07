# Agent Guide for Grade 10 E-Learning

This project is a small learning project for a school LMS. The goal is to build useful pieces rather than a large system with every possible feature.

## Project direction

Focus on the first milestone:
- Courses CRUD
- Users with role CRUD
- Assignment CRUD

Keep the scope tight and practical. Build the core pieces well before adding extra features.

## Style and UI guidance

Stay consistent with the existing admin template in the admin folder.
- Use the same layout, spacing, form structure, and card-based design.
- Use Heroicons for icons.
- Keep the UI clean, simple, and professional.
- Avoid gradient backgrounds and flashy visual effects.
- Avoid em dashes and emojis.
- Do not make the interface look like generic AI-generated UI.

## Implementation guidance

- Keep PHP code simple and readable.
- Reuse the existing database connection from config/db.php.
- Follow the current admin structure with header, sidebar, and content area.
- Prefer small, focused pages and forms over large one-off implementations.
- Use existing table and field names where possible.

## Current priority

The first target is course CRUD.
- Add a course list page.
- Add create, edit, and delete actions.
- Keep the page style aligned with the current admin dashboard.
- Make sure forms and actions are easy to understand and use.
