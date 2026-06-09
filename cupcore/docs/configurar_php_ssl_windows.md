# Solucion cURL error 60 en Windows

## Problema

Si CU16 Parte 6 falla al llamar a Groq con un error similar a este:

```text
cURL error 60: SSL certificate problem: unable to get local issuer certificate
```

el problema no es la API key ni el endpoint de Groq. El problema es que PHP/cURL en Windows no encuentra un certificado CA valido para verificar HTTPS.

La solucion correcta es configurar `curl.cainfo` y `openssl.cafile` apuntando a un archivo `cacert.pem`.

No se debe usar `verify => false`.
No se debe desactivar SSL.

## Paso 1: Descargar cacert.pem

Puedes hacerlo manualmente:

```powershell
mkdir C:\certs
powershell -Command "Invoke-WebRequest -Uri https://curl.se/ca/cacert.pem -OutFile C:\certs\cacert.pem"
```

O puedes usar el script opcional del proyecto:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\setup-cacert-windows.ps1
```

## Paso 2: Encontrar el php.ini activo

Ejecuta:

```powershell
php --ini
```

Busca esta linea:

```text
Loaded Configuration File
```

Ese es el `php.ini` que debes editar.

## Paso 3: Editar el php.ini activo

Agrega o corrige estas lineas:

```ini
curl.cainfo = "C:\certs\cacert.pem"
openssl.cafile = "C:\certs\cacert.pem"
```

Si ya existen comentadas con `;`, debes quitar el `;`.

Ejemplo:

```ini
;curl.cainfo=
;openssl.cafile=
```

Debe quedar:

```ini
curl.cainfo = "C:\certs\cacert.pem"
openssl.cafile = "C:\certs\cacert.pem"
```

## Paso 4: Reiniciar procesos PHP y limpiar caches Laravel

Si estas usando `php artisan serve`, detenlo con `CTRL + C` y vuelve a iniciarlo despues.

Ejecuta:

```powershell
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
php artisan view:clear
```

Luego reinicia el servidor:

```powershell
php artisan serve
```

## Paso 5: Verificacion segura en Tinker

Abre Tinker:

```powershell
php artisan tinker
```

Verifica PHP/cURL:

```php
ini_get('curl.cainfo');
ini_get('openssl.cafile');
```

Resultado esperado:

```text
"C:\certs\cacert.pem"
```

Verifica Groq:

```php
config('services.groq.api_key') ? 'GROQ_API_KEY CARGADA' : 'GROQ_API_KEY VACIA';
config('services.groq.model');
```

Resultado esperado:

```text
"GROQ_API_KEY CARGADA"
"llama-3.1-8b-instant"
```

## Paso 6: Prueba HTTPS opcional desde Tinker

No imprime la API key. Solo verifica que HTTPS y Groq respondan.

```php
use Illuminate\Support\Facades\Http;

$response = Http::withToken(config('services.groq.api_key'))
    ->timeout(20)
    ->post('https://api.groq.com/openai/v1/chat/completions', [
        'model' => config('services.groq.model', 'llama-3.1-8b-instant'),
        'messages' => [
            ['role' => 'user', 'content' => 'Responde solo OK'],
        ],
        'temperature' => 0,
        'max_tokens' => 10,
    ]);

$response->status();
$response->json();
```

## Paso 7: Probar CU16 Parte 6

1. Entra a `/gestion-academica-cup/reportes/consulta`.
2. Escribe o dicta: `mostrar resultados aprobados de la gestion 2-2028`.
3. Presiona `Interpretar con IA`.
4. Si SSL esta bien configurado y la API key existe, la llamada a Groq ya no debe fallar por `cURL error 60`.

## Resumen

- `cURL error 60` en Windows suele ser un problema local de certificados CA de PHP/cURL.
- No es un problema de Groq.
- No es un problema de la API key.
- No se debe desactivar SSL.
- La solucion correcta es configurar:
  - `curl.cainfo`
  - `openssl.cafile`
  apuntando a `C:\certs\cacert.pem`.
