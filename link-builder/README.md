# 🔗 Link Builder

Sistema moderno de gestión de páginas de enlaces tipo "link in bio" construido con Laravel, Vue.js e Inertia.js.

## ⚡ Inicio Súper Rápido

**¿Primera vez?** Solo necesitas Docker y ejecutar:

```bash
./setup.sh
```

Este script automático configura todo en minutos. 👇 Continúa leyendo para más detalles.

---

## 📋 Tabla de Contenidos

- [Inicio Súper Rápido](#-inicio-súper-rápido)
- [¿Qué es este proyecto?](#-qué-es-este-proyecto)
- [Tecnologías](#-tecnologías)
- [Requisitos Previos](#-requisitos-previos)
- [Instalación Rápida](#-instalación-rápida)
- [Instalación Detallada (Paso a Paso)](#-instalación-detallada-paso-a-paso)
- [Comandos Disponibles](#-comandos-disponibles)
- [Desarrollo](#-desarrollo)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Solución de Problemas](#-solución-de-problemas)

---

## 🎯 ¿Qué es este proyecto?

Link Builder es una aplicación web que permite crear páginas personalizadas con múltiples enlaces, similar a Linktree o Bio.link. Los usuarios pueden:

- Crear sitios personalizados con su propio dominio/slug
- Agregar múltiples enlaces con iconos
- Personalizar colores, temas y estilos
- Agregar bloques de contenido (CTAs, redes sociales, etc.)
- Administrar páginas múltiples por sitio

---

## 🛠 Tecnologías

Este proyecto está construido con:

- **Backend**: Laravel 12 (PHP 8.3)
- **Frontend**: Vue.js 3 + Inertia.js
- **Estilos**: Tailwind CSS 4
- **Build**: Vite 7
- **Base de Datos**: MySQL 8.0
- **Cache/Queue**: Redis 7
- **Servidor Web**: Nginx
- **Contenedores**: Docker + Docker Compose

---

## 📦 Requisitos Previos

Antes de comenzar, necesitas tener instalado en tu computadora:

### Opción 1: Con Docker (Recomendado - Más Fácil)

Solo necesitas instalar Docker:

- **Docker**: versión 20.10 o superior
- **Docker Compose**: versión 2.0 o superior

#### Instalar Docker en Linux (Ubuntu/Debian)

```bash
# Actualizar el sistema
sudo apt update

# Instalar dependencias
sudo apt install ca-certificates curl gnupg

# Agregar clave GPG oficial de Docker
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

# Agregar repositorio de Docker
echo \
  "deb [arch="$(dpkg --print-architecture)" signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  "$(. /etc/os-release && echo "$VERSION_CODENAME")" stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Instalar Docker
sudo apt update
sudo apt install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Agregar tu usuario al grupo docker (para no usar sudo)
sudo usermod -aG docker $USER
newgrp docker

# Verificar instalación
docker --version
docker compose version
```

#### Instalar Docker en macOS

1. Descarga [Docker Desktop para Mac](https://www.docker.com/products/docker-desktop)
2. Instálalo arrastrando a Aplicaciones
3. Abre Docker Desktop y espera a que inicie

#### Instalar Docker en Windows

1. Descarga [Docker Desktop para Windows](https://www.docker.com/products/docker-desktop)
2. Ejecuta el instalador
3. Reinicia tu computadora
4. Abre Docker Desktop

### Opción 2: Sin Docker (Manual)

Si prefieres no usar Docker, necesitas:

- PHP 8.3 o superior
- Composer
- Node.js 20 o superior
- MySQL 8.0
- Redis
- Nginx o Apache

---

## 🚀 Instalación Rápida

### Método 1: Script Automático (Más Fácil) ⚡

Si ya tienes Docker instalado y funcionando:

```bash
# 1. Clonar el repositorio
git clone <url-del-repo>
cd link-builder

# 2. Ejecutar el script de instalación automática
./setup.sh

# ¡Listo! La aplicación estará disponible en http://localhost:8080
```

El script `setup.sh` hace todo automáticamente:
- ✅ Verifica que Docker esté instalado
- ✅ Configura las variables de entorno
- ✅ Construye las imágenes Docker
- ✅ Levanta todos los contenedores
- ✅ Ejecuta las migraciones
- ✅ Te muestra cómo acceder a la aplicación

### Método 2: Usando Makefile

```bash
# Si prefieres usar Make
make install
```

---

## 📖 Instalación Detallada (Paso a Paso)

### Paso 1: Descargar el Proyecto

```bash
# Si usas Git
git clone <url-del-repo>
cd link-builder

# O descarga el ZIP y extráelo
```

### Paso 2: Configurar Variables de Entorno

El proyecto incluye un archivo `.env.docker` con la configuración lista para Docker:

```bash
# Copiar el archivo de configuración
cp .env.docker .env
```

**No necesitas editar nada**, pero si quieres personalizar:

```bash
# Abrir el archivo .env con tu editor favorito
nano .env    # o vim, o code, o cualquier editor
```

Configuraciones importantes que puedes cambiar:

```env
APP_NAME="Link Builder"          # Nombre de tu app
APP_URL=http://localhost:8080    # URL donde correrá

DB_DATABASE=linkbuilder          # Nombre de la base de datos
DB_USERNAME=linkbuilder          # Usuario de MySQL
DB_PASSWORD=secret               # Contraseña de MySQL

APP_PORT=8080                    # Puerto donde correrá la web
```

### Paso 3: Construir las Imágenes Docker

Este paso descarga y construye todos los contenedores necesarios. **Puede tomar 5-10 minutos la primera vez.**

```bash
# Si tu usuario NO está en el grupo docker (usarás sudo)
sudo docker compose build

# Si tu usuario SÍ está en el grupo docker
docker compose build

# O usando el Makefile
make build
```

Verás algo como:

```
[+] Building 317.4s (39/39) FINISHED
 ✔ Image link-builder-app   Built
 ✔ Image link-builder-queue Built
```

### Paso 4: Iniciar los Contenedores

```bash
# Levantar todos los servicios
sudo docker compose up -d

# O con el Makefile
make up
```

**El parámetro `-d`** significa "detached mode" (en segundo plano).

Verás:

```
[+] Running 6/6
 ✔ Network link-builder_linkbuilder Created
 ✔ Container linkbuilder-redis      Started
 ✔ Container linkbuilder-db         Started
 ✔ Container linkbuilder-app        Started
 ✔ Container linkbuilder-queue      Started
 ✔ Container linkbuilder-nginx      Started
```

### Paso 5: Verificar que Todo Esté Corriendo

```bash
# Ver el estado de los contenedores
sudo docker compose ps

# Deberías ver 5 contenedores en estado "Up"
```

Ejemplo de salida correcta:

```
NAME                STATUS                    PORTS
linkbuilder-app     Up 2 minutes             9000/tcp
linkbuilder-db      Up 2 minutes (healthy)   0.0.0.0:3307->3306/tcp
linkbuilder-nginx   Up 2 minutes             0.0.0.0:8080->80/tcp
linkbuilder-queue   Up 2 minutes             9000/tcp
linkbuilder-redis   Up 2 minutes (healthy)   0.0.0.0:6380->6379/tcp
```

### Paso 6: Verificar las Migraciones

Las migraciones se ejecutan automáticamente, pero puedes verificar:

```bash
sudo docker compose exec app php artisan migrate:status
```

Deberías ver todas las migraciones con estado `[Ran]`.

### Paso 7: ¡Acceder a la Aplicación!

Abre tu navegador y ve a:

```
http://localhost:8080
```

🎉 **¡Felicidades!** Tu aplicación está corriendo.

---

## 🎮 Comandos Disponibles

El proyecto incluye un `Makefile` con comandos útiles:

### Comandos Básicos

```bash
make help          # Muestra todos los comandos disponibles
make build         # Construye las imágenes Docker
make up            # Inicia los contenedores
make down          # Detiene los contenedores
make restart       # Reinicia todos los contenedores
make logs          # Ver logs en tiempo real
make destroy       # Elimina todo (contenedores, volúmenes, imágenes)
```

### Acceso a Shells

```bash
make shell         # Abre una terminal dentro del contenedor de PHP
make db-shell      # Abre el cliente de MySQL
make redis-cli     # Abre el cliente de Redis
```

Ejemplo de uso:

```bash
# Entrar al contenedor de la app
make shell

# Ahora estás dentro del contenedor, puedes ejecutar:
php artisan tinker
php artisan route:list
ls -la
exit  # para salir
```

### Comandos de Laravel

```bash
make migrate       # Ejecuta las migraciones
make seed          # Ejecuta los seeders
make fresh         # Borra la DB y ejecuta todo desde cero
make tinker        # Abre Laravel Tinker (consola interactiva)
make cache-clear   # Limpia todos los caches
make optimize      # Optimiza la app para producción
```

### Comandos de Queue

```bash
make queue         # Inicia un worker de queue manualmente
```

### Desarrollo Frontend

```bash
make up-dev        # Levanta contenedores + servidor de desarrollo Vite
make node          # Abre shell en el contenedor de Node
make npm-build     # Construye assets de producción
```

---

## 💻 Desarrollo

### Modo Desarrollo (con Hot Module Replacement)

Para desarrollo activo con recarga automática del frontend:

```bash
# Levantar con el profile dev (incluye Vite)
make up-dev

# O manualmente
sudo docker compose --profile dev up -d
```

Esto levanta un servidor Vite en `http://localhost:5173` que recarga automáticamente cuando editas archivos Vue/JS/CSS.

### Editar Código

El proyecto está montado como volumen, así que puedes editar directamente:

```bash
# Editar un componente Vue
nano resources/js/Components/MiComponente.vue

# Editar un controlador Laravel
nano app/Http/Controllers/MiController.php

# Los cambios se reflejan inmediatamente
```

### Ejecutar Comandos Artisan

```bash
# Crear un nuevo controlador
sudo docker compose exec app php artisan make:controller NuevoController

# Crear un modelo
sudo docker compose exec app php artisan make:model Producto -m

# Ver todas las rutas
sudo docker compose exec app php artisan route:list

# Limpiar cache
sudo docker compose exec app php artisan cache:clear
```

### Ejecutar Comandos NPM

```bash
# Instalar un nuevo paquete
sudo docker compose exec --profile dev node npm install lodash

# Ejecutar tests de frontend
sudo docker compose exec --profile dev node npm test
```

### Ver Logs en Tiempo Real

```bash
# Todos los servicios
make logs

# Solo un servicio específico
sudo docker compose logs -f app
sudo docker compose logs -f nginx
sudo docker compose logs -f queue

# Ver logs de Laravel directamente
sudo docker compose exec app tail -f storage/logs/laravel.log
```

---

## 📁 Estructura del Proyecto

```
link-builder/
├── app/                          # Código backend de Laravel
│   ├── Blocks/                   # Sistema de bloques customizables
│   ├── Http/Controllers/         # Controladores
│   ├── Models/                   # Modelos Eloquent
│   └── Helpers/                  # Funciones auxiliares
├── resources/
│   ├── js/                       # Código Vue.js
│   │   ├── Components/           # Componentes reutilizables
│   │   ├── Blocks/               # Componentes de bloques
│   │   ├── Pages/                # Páginas Inertia
│   │   └── app.js                # Entry point de JS
│   ├── css/                      # Estilos Tailwind
│   └── views/                    # Template Blade principal
├── routes/
│   ├── web.php                   # Rutas web
│   └── auth.php                  # Rutas de autenticación
├── database/
│   ├── migrations/               # Migraciones de DB
│   ├── factories/                # Factories para testing
│   └── seeders/                  # Seeders de datos
├── docker/                       # Configuraciones Docker
│   ├── nginx/                    # Config de Nginx
│   ├── php/                      # Config de PHP-FPM
│   └── entrypoint.sh             # Script de inicio
├── public/                       # Assets públicos
├── storage/                      # Archivos temporales/logs
├── tests/                        # Tests PHPUnit
├── .env                          # Variables de entorno
├── .env.docker                   # Variables para Docker
├── docker-compose.yml            # Definición de servicios
├── Dockerfile                    # Imagen de la aplicación
├── Makefile                      # Comandos útiles
├── composer.json                 # Dependencias PHP
├── package.json                  # Dependencias JS
└── README.md                     # Este archivo
```

---

## 🔧 Solución de Problemas

### Error: "docker: command not found"

**Problema**: Docker no está instalado o no está en el PATH.

**Solución**:
```bash
# Verificar si Docker está instalado
which docker

# Si no está, instálalo siguiendo la sección "Requisitos Previos"
```

### Error: "permission denied while connecting to Docker socket"

**Problema**: Tu usuario no tiene permisos para usar Docker.

**Solución**:
```bash
# Agregar tu usuario al grupo docker
sudo usermod -aG docker $USER

# Aplicar cambios (o cierra sesión y vuelve a entrar)
newgrp docker

# Verificar
docker ps
```

### Error: "port is already in use"

**Problema**: Los puertos 8080, 3307, o 6380 ya están siendo usados por otra aplicación.

**Solución 1**: Detén la aplicación que está usando el puerto.

**Solución 2**: Cambia el puerto en `.env`:
```bash
# Editar .env
nano .env

# Cambiar
APP_PORT=8080      # Por ejemplo, a 8081
DB_EXTERNAL_PORT=3307   # Por ejemplo, a 3308
REDIS_EXTERNAL_PORT=6380 # Por ejemplo, a 6381

# Reiniciar
make down
make up
```

### Las migraciones fallan

**Problema**: Error al conectar con la base de datos.

**Solución**:
```bash
# Verificar que la DB esté corriendo
sudo docker compose ps

# Ver logs de MySQL
sudo docker compose logs db

# Esperar ~30 segundos a que MySQL inicie completamente
sleep 30

# Intentar de nuevo
sudo docker compose exec app php artisan migrate
```

### Los cambios de frontend no se reflejan

**Problema**: Los assets no se están reconstruyendo.

**Solución**:
```bash
# Si estás en modo producción, reconstruir assets
sudo docker compose exec --profile dev node npm run build

# O levantar en modo desarrollo
make up-dev

# Limpiar cache del navegador (Ctrl+Shift+R en Chrome/Firefox)
```

### Error: "Vite manifest not found"

**Problema**: Los assets no se han construido.

**Solución**:
```bash
# Reconstruir la imagen (incluye npm run build)
sudo docker compose build app

# Reiniciar
make restart
```

### No puedo acceder a http://localhost:8080

**Problema**: Nginx no está corriendo o hay un problema de red.

**Solución**:
```bash
# Verificar estado de los contenedores
sudo docker compose ps

# Si nginx no está "Up", ver los logs
sudo docker compose logs nginx

# Verificar que el puerto esté expuesto
sudo netstat -tulpn | grep 8080

# O en macOS
lsof -i :8080
```

### Resetear Todo y Empezar de Cero

Si todo está mal y quieres empezar desde cero:

```bash
# Detener y eliminar TODO (contenedores, volúmenes, imágenes)
make destroy

# O manualmente
sudo docker compose down -v --rmi local

# Eliminar el archivo .env
rm .env

# Empezar de nuevo
make install
```

### Ver Logs Detallados

```bash
# Logs de todos los servicios
sudo docker compose logs

# Logs solo de la app
sudo docker compose logs app

# Logs en tiempo real
sudo docker compose logs -f

# Últimas 100 líneas
sudo docker compose logs --tail 100

# Logs de Laravel
sudo docker compose exec app tail -f storage/logs/laravel.log
```

### Problemas de Permisos en Linux

**Problema**: Archivos creados por Docker son propiedad de root.

**Solución**:
```bash
# Cambiar propietario de todo el proyecto
sudo chown -R $USER:$USER .

# O solo los directorios problemáticos
sudo chown -R $USER:$USER storage bootstrap/cache
```

### Limpiar Espacio en Disco (Docker ocupa mucho)

```bash
# Ver uso de disco
docker system df

# Limpiar todo lo no usado (cuidado!)
docker system prune -a

# Limpiar solo volúmenes no usados
docker volume prune

# Limpiar solo imágenes no usadas
docker image prune -a
```

---

## 🚀 Producción

Para desplegar en producción:

1. Cambia `APP_ENV` a `production` en `.env`
2. Cambia `APP_DEBUG` a `false`
3. Genera una nueva `APP_KEY`: `php artisan key:generate`
4. Configura un dominio real en `APP_URL`
5. Usa contraseñas seguras para MySQL/Redis
6. Considera usar SSL/HTTPS (Let's Encrypt)
7. Configura backups de la base de datos
8. Monitorea logs y recursos

---

## 📝 Licencia

MIT

---

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📧 Soporte

Si tienes problemas que no están en la sección de "Solución de Problemas":

1. Revisa los logs: `make logs`
2. Verifica que todos los contenedores estén corriendo: `sudo docker compose ps`
3. Intenta reiniciar: `make restart`
4. Como último recurso, resetea todo: `make destroy` y luego `make install`

---

**¿Te funcionó?** ⭐ Dale una estrella al repo si te sirvió este README.
