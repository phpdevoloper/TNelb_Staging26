param(
    [Parameter(Mandatory = $true)][string]$Html,
    [Parameter(Mandatory = $true)][string]$Out,
    [int]$Width = 1024,
    [int]$Height = 1536
)

$chrome = "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
if (-not (Test-Path $chrome)) {
    throw "Chrome not found at $chrome"
}

$htmlPath = (Resolve-Path $Html).Path
$outPath = [System.IO.Path]::GetFullPath($Out)
$uri = "file:///" + $htmlPath.Replace('\', '/')

& $chrome --headless=new --disable-gpu --hide-scrollbars --no-first-run "--window-size=$Width,$Height" "--screenshot=$outPath" $uri | Out-Null

if (-not (Test-Path $outPath)) {
    throw "Screenshot was not written: $outPath"
}
Write-Output $outPath
