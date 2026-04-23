<?php

namespace App\Services;

use App\Models\ReportTemplate;
use Illuminate\Database\Eloquent\Model;

class ReportService
{
    /**
     * Generate report content by replacing tags with actual data.
     *
     * @param int|ReportTemplate $template
     * @param array|Model $data
     * @return string
     */
    public function generate($template, $data)
    {
        if (!$template instanceof ReportTemplate) {
            $template = ReportTemplate::findOrFail($template);
        }

        $content = $template->content;
        $replacements = $this->resolveData($data);

        foreach ($replacements as $tag => $value) {
            $content = str_replace($tag, $value, $content);
        }

        return $content;
    }

    /**
     * Resolve the data into a flat tag => value array.
     *
     * @param array|Model $data
     * @return array
     */
    protected function resolveData($data)
    {
        if (is_array($data)) {
            return $data;
        }

        if ($data instanceof Model) {
            // Default mapping for Employee model
            if ($data instanceof \App\Models\Employee) {
                return [
                    '#EmployeeName' => $data->name,
                    '#EmployeeCode' => $data->employee_code,
                    '#Department'   => $data->department->name ?? '',
                    '#Designation'  => $data->designation->name ?? '',
                    '#JoiningDate'  => $data->joining_date ? date('d M Y', strtotime($data->joining_date)) : '',
                    '#BasicSalary'  => number_format($data->gross_salary * 0.6, 2), // Example logic
                    '#GrossSalary'  => number_format($data->gross_salary, 2),
                    '#PresentAddress' => $data->present_address ?? '',
                    '#PermanentAddress' => $data->permanent_address ?? '',
                ];
            }

            return $data->toArray();
        }

        return [];
    }
}
