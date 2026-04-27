$files = Get-ChildItem -Path "resources/views" -Filter "*.blade.php" -Recurse
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    
    # Generic bar names
    $content = $content -replace '(?<!ui-)filter-bar', 'ui-filter-bar'
    $content = $content -replace '(?<!ui-)download-bar', 'ui-download-bar'
    
    # Buttons
    $content = $content -replace 'btn-hr-search', 'ui-btn-search'
    $content = $content -replace 'btn-emp-search', 'ui-btn-search'
    $content = $content -replace 'btn-hr-clear', 'ui-btn-clear'
    $content = $content -replace 'btn-emp-clear', 'ui-btn-clear'
    
    # Metrics
    $content = $content -replace '(?<!ui-)metric-card', 'ui-metric-card'
    $content = $content -replace '(?<!ui-)metric-icon', 'ui-metric-icon'
    $content = $content -replace '(?<!ui-)metric-value', 'ui-metric-value'
    $content = $content -replace '(?<!ui-)metric-label', 'ui-metric-label'

    # Panels (just in case any missed prefix)
    $content = $content -replace '(?<!ui-)panel-title', 'ui-panel-title'
    
    $content | Set-Content $file.FullName
}
