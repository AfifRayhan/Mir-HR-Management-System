$files = Get-ChildItem -Path "resources/views" -Filter "*.blade.php" -Recurse
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    # Remove old dashboard CSS imports
    $content = $content -replace "@vite\(\['resources/css/custom-hr-dashboard.css'\]\)", ""
    $content = $content -replace "@vite\(\['resources/css/custom-employee-dashboard.css'\]\)", ""
    $content = $content -replace "@vite\(\['resources/css/custom-hr-dashboard.css', 'resources/css/custom-employee-dashboard.css'\]\)", ""
    $content = $content -replace "@vite\(\['resources/css/custom-employee-dashboard.css', 'resources/css/custom-hr-dashboard.css'\]\)", ""
    
    # Cleanup empty push blocks if any (optional but nice)
    $content = $content -replace "@push\('styles'\)\s+@endpush", ""
    
    $content | Set-Content $file.FullName
}
