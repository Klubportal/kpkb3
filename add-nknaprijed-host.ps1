# PowerShell Script to add nknaprijed.localhost to hosts file
# Must be run as Administrator

$hostsPath = "C:\Windows\System32\drivers\etc\hosts"
$domain = "nknaprijed.localhost"
$ip = "127.0.0.1"
$entry = "$ip`t$domain"

# Check if entry already exists
$hostsContent = Get-Content $hostsPath
$exists = $hostsContent | Select-String -Pattern $domain

if ($exists) {
    Write-Host "✓ $domain is already in hosts file" -ForegroundColor Green
} else {
    Write-Host "Adding $domain to hosts file..." -ForegroundColor Yellow
    Add-Content -Path $hostsPath -Value "`n$entry"
    Write-Host "✓ Successfully added $domain to hosts file" -ForegroundColor Green
}

Write-Host "`nCurrent hosts entries for localhost domains:" -ForegroundColor Cyan
Get-Content $hostsPath | Select-String "localhost"
