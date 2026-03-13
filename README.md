[README.md](https://github.com/user-attachments/files/25935999/README.md)
# trivium-web

Aplicación web desarrollada con Symfony 7.

## Requisitos

Asegúrate de tener instalado lo siguiente antes de empezar:

- [PHP 8.2+](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [Node.js + npm](https://nodejs.org/)
- [Symfony CLI](https://symfony.com/download)

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/trivium-web.git
cd trivium-web
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Configurar variables de entorno

Copia el archivo de entorno y edítalo con tus datos:

```bash
cp .env .env.local
```

Edita `.env.local` y configura la base de datos:

```
DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/trivium_web"
```

### 4. Crear la base de datos y ejecutar migraciones

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Instalar dependencias JavaScript y compilar assets

```bash
npm install
npm run dev
```

## Iniciar el servidor de desarrollo

```bash
symfony serve
```

La aplicación estará disponible en [http://127.0.0.1:8000](http://127.0.0.1:8000).

## Comandos útiles

| Comando | Descripción |
|---|---|
| `symfony serve` | Inicia el servidor de desarrollo |
| `npm run dev` | Compila los assets en modo desarrollo |
| `npm run watch` | Compila los assets automáticamente al guardar cambios |
| `npm run build` | Compila los assets para producción |
| `php bin/console doctrine:migrations:migrate` | Ejecuta las migraciones de base de datos |
| `php bin/console cache:clear` | Limpia la caché de Symfony |
