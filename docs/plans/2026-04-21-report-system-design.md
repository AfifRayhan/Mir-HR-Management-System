# Report Template System Design

This document outlines the design for implementing a flexible, database-driven reporting system that allows HRadmins to manage document templates (like Promotion Letters, ID Cards, etc.) using a rich text editor.

## Goals

*   Migrate template data from CSV files (`ReportTemplates.csv`, `ReportTemplateTypes.csv`) into the database.
*   Provide a CRUD interface for HRadmins to edit existing templates or create new ones.
*   Integrate CKEditor for rich text editing.
*   Support dynamic tags (placeholders) that are replaced with real data during report generation.

## Components

### 1. Data Model

Two new models will be created:

#### ReportTemplateType
*   `id`: Primary binary/integer ID (matches CSV).
*   `name`: Name of the report (e.g., "Promotion Letter").
*   `key_tags`: A JSON or comma-separated list of available placeholders (e.g., `#EmployeeName`, `#RefNo`).

#### ReportTemplate
*   `id`: Primary Key.
*   `report_template_type_id`: Foreign key to `report_template_types`.
*   `format`: The language/format (e.g., "English", "Bangla").
*   `content`: The HTML template content.
*   `is_active`: Boolean flag.

### 2. Management UI (Bootstrap 5)

*   **Index View**: List of all template types with their available formats.
*   **Editor View**:
    *   Dropdowns for Type and Format.
    *   **Tag Panel**: A list of clickable buttons for each available tag.
    *   **CKEditor Workspace**: The main editor instance.
    *   **JavaScript Integration**: A custom script to insert tags into the CKEditor instance at the current cursor position when a tag button is clicked.

### 3. Report Generator Service

A service class `App\Services\ReportService` will handle the substitution logic:
*   `generate($templateId, $data)`: Takes a template ID and an associative array of data (or a Model), replaces `#Tags` with values, and returns the final HTML.

## Data Migration Strategy

*   **Migration**: Create the two tables.
*   **Seeder**: A `ReportTemplateSeeder` will read the provided CSV files and populate the tables, ensuring the IDs are preserved for consistency with existing data if any.

## Technical Details

*   **Rich Text Editor**: CKEditor 5 (Classic build via CDN).
*   **Styling**: Bootstrap 5.
*   **Interactivity**: Vanilla JavaScript for editor interactions and tag insertion.

## Success Criteria

*   HRadmins can access a "Report Templates" menu item.
*   Templates can be edited and saved without loss of formatting.
*   Tags correctly display and are insertable into the editor.
*   Initial data from CSVs is correctly populated.
