$certDirectory = 'C:\certs'
$certPath = Join-Path $certDirectory 'cacert.pem'
$certUrl = 'https://curl.se/ca/cacert.pem'

if (-not (Test-Path -LiteralPath $certDirectory)) {
    New-Item -ItemType Directory -Path $certDirectory -Force | Out-Null
    Write-Host "Carpeta creada: $certDirectory"
} else {
    Write-Host "La carpeta ya existe: $certDirectory"
}

Write-Host "Descargando certificado CA desde $certUrl ..."
Invoke-WebRequest -Uri $certUrl -OutFile $certPath
Write-Host "Certificado guardado en: $certPath"

Write-Host ''
Write-Host 'Siguiente paso: ubica el php.ini activo con:'
Write-Host '  php --ini'
Write-Host ''
Write-Host 'Luego edita la linea "Loaded Configuration File" y agrega o corrige:'
Write-Host '  curl.cainfo = "C:\certs\cacert.pem"'
Write-Host '  openssl.cafile = "C:\certs\cacert.pem"'
Write-Host ''
Write-Host 'Despues reinicia PHP y limpia caches de Laravel con:'
Write-Host '  php artisan config:clear'
Write-Host '  php artisan cache:clear'
Write-Host '  php artisan optimize:clear'
Write-Host '  php artisan view:clear'
