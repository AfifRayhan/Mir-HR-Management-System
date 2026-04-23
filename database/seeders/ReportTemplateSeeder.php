<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use App\Models\ReportTemplateType;
use Illuminate\Database\Seeder;

class ReportTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Report Template Types
        $typesFile = base_path('ReportTemplateTypes.csv');
        if (file_exists($typesFile)) {
            $handle = fopen($typesFile, 'r');
            $header = fgetcsv($handle); // Skip header: Id,ReportName,KeyTag

            while (($data = fgetcsv($handle)) !== false) {
                if (empty($data[0])) continue;

                ReportTemplateType::updateOrCreate(
                    ['id' => $data[0]],
                    [
                        'name' => $data[1],
                        'key_tags' => '', // Initialize empty, will be populated from templates
                    ]
                );
            }
            fclose($handle);
        }

        // 2. Seed Report Templates
        $templatesFile = base_path('ReportTemplates.csv');
        if (file_exists($templatesFile)) {
            $handle = fopen($templatesFile, 'r');
            $header = fgetcsv($handle); // Skip header: Id,ReportTemplateTypeId,ReportFormat,Text

            while (($data = fgetcsv($handle)) !== false) {
                if (empty($data[0])) continue;

                $content = $data[3] ?? '';

                // Extract key tags from content (words starting with # followed by letters)
                preg_match_all('/#[a-zA-Z]\w*/', $content, $matches);
                $tags = !empty($matches[0]) ? array_unique($matches[0]) : [];

                $typeId = $data[1];

                // Update the corresponding ReportTemplateType with the found tags
                if (!empty($tags)) {
                    $type = ReportTemplateType::find($typeId);
                    if ($type) {
                        $existingTags = array_filter(explode(',', $type->key_tags));
                        $allTags = array_unique(array_merge($existingTags, $tags));
                        $type->update(['key_tags' => implode(',', $allTags)]);
                    }
                }

                ReportTemplate::updateOrCreate(
                    ['id' => $data[0]],
                    [
                        'report_template_type_id' => $typeId,
                        'format' => $data[2],
                        'content' => $content,
                        'is_active' => true,
                    ]
                );
            }
            fclose($handle);
        }
    }
}
