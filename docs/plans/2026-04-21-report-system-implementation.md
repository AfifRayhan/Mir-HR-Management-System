# Report Template System Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Build a database-driven reporting system where HRadmins can manage document templates using CKEditor and dynamic tags, prepopulated from existing CSV data.

**Architecture:** traditional Laravel MVC with a dedicated Service for tag replacement. Uses Bootstrap 5 for UI and CKEditor 5 for rich text editing.

**Tech Stack:** PHP 8.2+, Laravel 12, Bootstrap 5, CKEditor 5, PHP Spreadsheet (for CSV import).

---

### Task 1: Database Schema & Models

**Files:**
- `[ ]` Create migration for `report_template_types` and `report_templates`
- `[ ]` Create Model `app/Models/ReportTemplateType.php`
- `[ ]` Create Model `app/Models/ReportTemplate.php`

**Step 1: Create Migration**
Run: `php.exe artisan make:migration create_report_system_tables`

Implementation:
```php
Schema::create('report_template_types', function (Blueprint $table) {
    $table->id(); // Matches CSV IDs
    $table->string('name');
    $table->text('key_tags'); // Comma separated tags
    $table->timestamps();
});

Schema::create('report_templates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('report_template_type_id')->constrained()->cascadeOnDelete();
    $table->string('format'); // English, Bangla, etc.
    $table->longText('content');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

**Step 2: Run Migration**
Run: `php.exe artisan migrate`

**Step 3: Define Models**
Define relationships (Type hasMany Templates) and fillable properties in `app/Models/ReportTemplateType.php` and `app/Models/ReportTemplate.php`.

---

### Task 2: Data Seeding from CSV

**Files:**
- `[ ]` Create Seeder `database/seeders/ReportTemplateSeeder.php`

**Step 1: Implementation**
The seeder will:
1.  Read `ReportTemplateTypes.csv` and populate `report_template_types`.
2.  Read `ReportTemplates.csv` and populate `report_templates`.
3.  Handle the IDs explicitly to ensure consistency.

Run: `php.exe artisan db:seed --class=ReportTemplateSeeder`

---

### Task 3: Management Controller & Routes

**Files:**
- `[ ]` Create Controller `app/Http/Controllers/ReportTemplateController.php`
- `[ ]` Update `routes/web.php`
- `[ ]` Update Sidebar Navigation

**Step 1: Controller Logic**
Implement standard Index, Create, Edit, Store, Update, Destroy methods.
Ensure it fetches all types and formats for the forms.

---

### Task 4: UI Development (Bootstrap 5)

**Files:**
- `[ ]` Create `resources/views/reports/templates/index.blade.php`
- `[ ]` Create `resources/views/reports/templates/create.blade.php`
- `[ ]` Create `resources/views/reports/templates/edit.blade.php`

**Step 1: Editor Page & CKEditor Integration**
*   Load CKEditor from CDN.
*   Implement the **Tag Gallery**: A div displaying tags as clickable buttons.
*   **JavaScript**: Add a script to insert tags into CKEditor at the selection point.

```javascript
// Example logic
const editor = ClassicEditor.create(...);
document.querySelectorAll('.tag-btn').forEach(btn => {
    btn.onclick = () => {
        editor.model.change(writer => {
            writer.insertText(btn.dataset.tag, editor.model.document.selection.getFirstPosition());
        });
    };
});
```

---

### Task 5: Report Generation Service

**Files:**
- `[ ]` Create Service `app/Services/ReportService.php`

**Step 1: Implementation**
A `replaceTags($content, $data)` method that uses `str_replace` or regex to swap tags with actual values from the passed data.

---

### Task 6: Verification

**Step 1: Final Check**
1.  Open the Report Templates page.
2.  Edit a "Promotion Letter (English)".
3.  Add a tag, change some text, and save.
4.  Verify changes in DB.
5.  Create a "Test View" that renders a template with dummy data.

---

**Note:** As per instructions, all artisan commands will use `php.exe`. Git commands are excluded.
