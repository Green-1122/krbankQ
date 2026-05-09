$p = 'C:\Users\FOR WORK\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe'
$current = [Environment]::GetEnvironmentVariable('Path','User')
$parts = @()
foreach ($part in $current -split ';') {
    if ([string]::IsNullOrWhiteSpace($part)) { continue }
    if ($parts -notcontains $part) { $parts += $part }
}
if ($parts -notcontains $p) { $parts += $p }
[Environment]::SetEnvironmentVariable('Path', [string]::Join(';', $parts), 'User')
