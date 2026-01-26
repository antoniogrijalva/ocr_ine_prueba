# Sistema OCR para Credencial INE

Sistema de validación de documentos con extracción automática de datos mediante Google Cloud Vision API.

## Stack Tecnológico
- Laravel 11 + Sail
- Vue 3 + Inertia.js
- Google Cloud Vision API
- Tailwind CSS

## Instalación

```bash
git clone https://github.com/antoniogrijalva/ocr_ine_prueba.git
cd ocr_ine_prueba
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail npm install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan storage:link
./vendor/bin/sail npm run dev
```

## Configuración Google Cloud Vision

Coloca tu archivo de credenciales en: `storage/app/google-key.json`

## Usuarios de prueba

- admin@example.com / password
- validador@example.com / password
- capturista@example.com / password
