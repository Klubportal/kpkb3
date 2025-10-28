# Fix hardcoded colors in bm template
$homePath = "c:\xampp\htdocs\Klubportal-Laravel12\resources\views\templates\bm\home.blade.php"
$footerPath = "c:\xampp\htdocs\Klubportal-Laravel12\resources\views\templates\bm\partials\footer.blade.php"

# Read files
$homeContent = Get-Content $homePath -Raw
$footerContent = Get-Content $footerPath -Raw

# Replace all red- colors with primary class
$homeContent = $homeContent -replace 'from-red-600 via-red-700 to-red-900', 'from-primary via-primary to-primary'
$homeContent = $homeContent -replace 'text-red-100', 'text-white/90'
$homeContent = $homeContent -replace 'text-red-600', 'text-primary'
$homeContent = $homeContent -replace 'bg-red-100', 'bg-primary/10'
$homeContent = $homeContent -replace 'bg-red-900', 'bg-primary/90'
$homeContent = $homeContent -replace 'bg-red-800', 'bg-primary'
$homeContent = $homeContent -replace 'hover:bg-red-900', 'hover:bg-primary'
$homeContent = $homeContent -replace 'text-red-400', 'text-primary'
$homeContent = $homeContent -replace 'border-red-600', 'border-primary'
$homeContent = $homeContent -replace 'border-green-600', 'border-secondary'

# Footer replacements
$footerContent = $footerContent -replace 'from-gray-900 to-black', 'from-gray-900 to-gray-950'
$footerContent = $footerContent -replace 'text-red-600', 'text-primary'
$footerContent = $footerContent -replace 'text-red-500', 'text-primary'
$footerContent = $footerContent -replace 'hover:text-red-500', 'hover:text-primary'
$footerContent = $footerContent -replace 'from-red-600 to-red-800', 'from-primary to-primary'

# Write back
Set-Content $homePath -Value $homeContent -NoNewline
Set-Content $footerPath -Value $footerContent -NoNewline

Write-Host "Colors fixed in bm template!" -ForegroundColor Green
