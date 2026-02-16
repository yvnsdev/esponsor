#!/bin/bash

# =============================================================================
# Link Builder - Script de Instalación Automática
# =============================================================================
# Este script configura y levanta todo el entorno de desarrollo
# de forma automática usando Docker.
# =============================================================================

set -e  # Detener si hay algún error

# Colores para mensajes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # Sin color

# Funciones auxiliares
print_header() {
    echo ""
    echo -e "${BLUE}============================================${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}============================================${NC}"
    echo ""
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# =============================================================================
# 1. Verificar requisitos
# =============================================================================
print_header "Verificando Requisitos"

# Verificar si Docker está instalado
if ! command -v docker &> /dev/null; then
    print_error "Docker no está instalado"
    print_info "Por favor instala Docker desde: https://docs.docker.com/get-docker/"
    exit 1
fi
print_success "Docker está instalado ($(docker --version | cut -d' ' -f3))"

# Verificar si Docker Compose está disponible
if ! docker compose version &> /dev/null; then
    print_error "Docker Compose no está disponible"
    print_info "Por favor instala Docker Compose o actualiza Docker"
    exit 1
fi
print_success "Docker Compose está disponible ($(docker compose version --short))"

# Verificar si Docker está corriendo
if ! docker info &> /dev/null 2>&1; then
    print_warning "Docker no está corriendo o requiere permisos"
    print_info "Intentando usar sudo..."
    DOCKER_CMD="sudo docker"
    COMPOSE_CMD="sudo docker compose"
    
    if ! sudo docker info &> /dev/null 2>&1; then
        print_error "No se puede conectar a Docker"
        print_info "Asegúrate de que Docker Desktop esté corriendo (en Mac/Windows)"
        print_info "O inicia el servicio Docker: sudo systemctl start docker"
        exit 1
    fi
else
    DOCKER_CMD="docker"
    COMPOSE_CMD="docker compose"
fi
print_success "Docker está corriendo correctamente"

# =============================================================================
# 2. Configurar variables de entorno
# =============================================================================
print_header "Configurando Variables de Entorno"

if [ -f .env ]; then
    print_warning "Archivo .env ya existe"
    read -p "¿Deseas sobrescribirlo? (s/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        cp .env.docker .env
        print_success "Archivo .env actualizado desde .env.docker"
    else
        print_info "Manteniendo .env existente"
    fi
else
    if [ -f .env.docker ]; then
        cp .env.docker .env
        print_success "Archivo .env creado desde .env.docker"
    else
        print_error "No se encontró .env.docker"
        print_info "Crea un archivo .env manualmente basándote en .env.example"
        exit 1
    fi
fi

# =============================================================================
# 3. Detener contenedores existentes (si hay)
# =============================================================================
print_header "Limpiando Contenedores Existentes"

if $COMPOSE_CMD ps -q 2>/dev/null | grep -q .; then
    print_info "Deteniendo contenedores existentes..."
    $COMPOSE_CMD down
    print_success "Contenedores detenidos"
else
    print_info "No hay contenedores corriendo"
fi

# =============================================================================
# 4. Construir imágenes Docker
# =============================================================================
print_header "Construyendo Imágenes Docker"

print_info "Esto puede tomar 5-10 minutos la primera vez..."
print_info "Se están descargando dependencias de PHP, Node.js y construyendo assets..."

if $COMPOSE_CMD build; then
    print_success "Imágenes construidas exitosamente"
else
    print_error "Error al construir las imágenes"
    exit 1
fi

# =============================================================================
# 5. Iniciar contenedores
# =============================================================================
print_header "Iniciando Contenedores"

print_info "Levantando servicios: MySQL, Redis, PHP-FPM, Nginx, Queue Worker..."

if $COMPOSE_CMD up -d; then
    print_success "Contenedores iniciados"
else
    print_error "Error al iniciar los contenedores"
    exit 1
fi

# =============================================================================
# 6. Esperar a que los servicios estén listos
# =============================================================================
print_header "Esperando a que los Servicios Estén Listos"

print_info "Esperando a que MySQL esté listo..."
sleep 5

# Esperar a que MySQL esté healthy
MAX_WAIT=60
WAITED=0
while [ $WAITED -lt $MAX_WAIT ]; do
    if $COMPOSE_CMD ps | grep linkbuilder-db | grep -q "healthy"; then
        print_success "MySQL está listo"
        break
    fi
    echo -n "."
    sleep 2
    WAITED=$((WAITED + 2))
done

if [ $WAITED -ge $MAX_WAIT ]; then
    print_warning "MySQL tardó más de lo esperado, pero continuamos..."
fi

print_info "Esperando a que Redis esté listo..."
sleep 2

if $COMPOSE_CMD ps | grep linkbuilder-redis | grep -q "healthy"; then
    print_success "Redis está listo"
fi

# =============================================================================
# 7. Generar Application Key (si no existe)
# =============================================================================
print_header "Configurando Laravel"

APP_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f2)
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    print_info "Generando Application Key..."
    $COMPOSE_CMD exec -T app php artisan key:generate --force
    print_success "Application Key generada"
else
    print_info "Application Key ya existe"
fi

# =============================================================================
# 8. Ejecutar migraciones
# =============================================================================
print_header "Ejecutando Migraciones de Base de Datos"

print_info "Creando tablas en la base de datos..."

if $COMPOSE_CMD exec -T app php artisan migrate --force; then
    print_success "Migraciones ejecutadas correctamente"
else
    print_warning "Las migraciones fallaron o ya están ejecutadas"
fi

# =============================================================================
# 9. Crear enlace simbólico de storage
# =============================================================================
print_info "Creando enlace simbólico de storage..."
$COMPOSE_CMD exec -T app php artisan storage:link 2>/dev/null || print_info "Enlace ya existe"

# =============================================================================
# 10. Verificar estado de los contenedores
# =============================================================================
print_header "Estado de los Servicios"

$COMPOSE_CMD ps

# =============================================================================
# 11. Mostrar información final
# =============================================================================
print_header "¡Instalación Completada!"

echo ""
print_success "Link Builder está corriendo correctamente"
echo ""
echo -e "${GREEN}🌐 Aplicación Web:${NC}      http://localhost:8080"
echo -e "${BLUE}🔧 MySQL:${NC}               localhost:3307"
echo -e "${BLUE}💾 Redis:${NC}               localhost:6380"
echo ""
echo -e "${YELLOW}Comandos útiles:${NC}"
echo ""
echo "  Ver logs en tiempo real:"
echo "    $COMPOSE_CMD logs -f"
echo ""
echo "  Acceder al contenedor de la app:"
echo "    $COMPOSE_CMD exec app sh"
echo ""
echo "  Detener servicios:"
echo "    $COMPOSE_CMD down"
echo ""
echo "  Reiniciar servicios:"
echo "    $COMPOSE_CMD restart"
echo ""
echo "  Ejecutar comandos Artisan:"
echo "    $COMPOSE_CMD exec app php artisan <comando>"
echo ""
echo "  Ver migraciones:"
echo "    $COMPOSE_CMD exec app php artisan migrate:status"
echo ""

if command -v make &> /dev/null; then
    echo -e "${BLUE}ℹ También puedes usar 'make help' para ver más comandos${NC}"
fi

echo ""
print_success "¡Listo para empezar a desarrollar! 🚀"
echo ""
