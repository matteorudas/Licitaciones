# Licitaciones PT

Sistema de gestión de licitaciones desarrollado como prueba técnica para Suplos.
Permite crear, editar, listar y visualizar ofertas de licitación.

---

## ¿Qué hace este proyecto?

Es una aplicación web fullstack que replica un módulo real de licitaciones:

- Puedes **crear una licitación** con información básica y cronograma
- Puedes **editarla** y adjuntarle documentos
- Puedes **listar todas las licitaciones** con filtros y paginación
- Puedes **ver el detalle** de cada una organizado por pestañas
- Puedes **exportar el listado a Excel** directamente desde el backend


![Lista de Licitaciones](./src/assets/listalicitaciones.png)


---

## Tecnologías utilizadas

**Backend**
- PHP 8.3 sin frameworks 
- Eloquent ORM standalone (comunicación con la base de datos)
- MySQL

**Frontend**
- Vue 3 + Vite
- Axios (consumo de la API)
- Bootstrap 5.2

---

## Estructura del proyecto

```
suplos-licitaciones/
├── backend/                     → API REST en PHP 
│   ├── app/
│   │   ├── Controllers/         → Lógica de negocio y validaciones
│   │   ├── Models/              → Comunicación con la base de datos
│   │   └── Core/                → Router, BaseController y Validator
│   ├── config/
│   │   └── database.php         → Conexión con Eloquent
│   ├── public/
│   │   ├── index.php            → Punto de entrada de la API
│   │   └── uploads/             → Archivos subidos por el usuario
│   ├── bd/
│   │   ├── schema.sql           → Estructura de la base de datos
│   │   └── seed_actividades.php → Carga el catálogo UNSPSC.xlsx
│   ├── composer.json
│   └── .env
│
├── frontend/                 → Interfaz en Vue 3
│   ├── src/
│   │   ├── components/
│   │   │   ├── ListaOfertas.vue   → Tabla con filtros, paginación y exportar
│   │   │   ├── FormOferta.vue     → Formulario de creación y edición
│   │   │   └── DetalleOferta.vue  → Vista de detalle con pestañas
│   │   ├── App.vue                → Componente raíz y navegación entre vistas
│   │   └── main.js                → Punto de entrada, monta la app
│   ├── vite.config.js             → Configuración de Vite y proxy hacia el backend
│   └── package.json
│
└── README.md
```

---

## Cómo está organizado el backend

El backend sigue el patrón **Modelo – Vista – Controlador** adaptado a una API REST,
donde la "Vista" es el JSON que se devuelve al frontend.

### Modelos — `app/Models/`
Se encargan exclusivamente de hablar con la base de datos usando Eloquent ORM.
No contienen lógica de negocio, solo definen la tabla, los campos permitidos
y las relaciones entre tablas.

| Modelo | Tabla | Relaciones |
|--------|-------|------------|
| `Oferta.php` | `ofertas` | tiene muchos documentos, pertenece a una actividad |
| `OfertaDocumento.php` | `ofertas_documentos` | pertenece a una oferta |
| `Actividad.php` | `actividades` | es referenciada por las ofertas |

### Controladores — `app/Controllers/`
Reciben la petición HTTP, validan los datos con el `Validator`, llaman al modelo
que necesitan y devuelven la respuesta en JSON.

| Controlador | Responsabilidad |
|-------------|----------------|
| `OfertaController.php` | Crear, editar, listar, ver detalle y exportar Excel |
| `ActividadController.php` | Listar actividades para el select del formulario |

### Core — `app/Core/`
Piezas reutilizables que sostienen todo el sistema:

- **`Router.php`** — recibe la URL y el método HTTP y decide qué controlador ejecutar
- **`BaseController.php`** — métodos comunes para responder JSON desde cualquier controlador
- **`Validator.php`** — valida campos requeridos, longitudes, fechas, decimales

---

## Cómo está organizado el frontend 

La interfaz es una SPA (Single Page Application) sin cambios de página.
La navegación entre vistas la maneja `App.vue` con una variable de estado simple.

```
App.vue
 ├── ListaOfertas.vue   → se muestra al entrar o al volver al listado
 ├── FormOferta.vue     → se muestra al crear o al editar
 └── DetalleOferta.vue  → se muestra al hacer clic en ver detalle
```

El archivo `vite.config.js` tiene configurado un **proxy** que redirige
todas las llamadas a `/api/...` hacia el backend PHP.
Esto evita errores de CORS durante el desarrollo sin tocar ninguna configuración
del servidor.

---

## Requisitos previos

Antes de correr el proyecto asegúrate de tener instalado:

- Servidor web con PHP 8+ (Apache o Nginx)
- MySQL o MariaDB
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) v18 o superior

---

## Paso a paso para correr el proyecto

### 1. Clonar el repositorio

```bash
git clone https://github.com/matteorudas/licitaciones.git
cd licitaciones
```

### 2. Configurar el backend

```bash
cd backend
composer install
```

Crea el archivo de variables de entorno copiando el ejemplo:

```bash
cp .env.example .env
```

Abre el `.env` y ajusta los datos de tu base de datos:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=licitaciones
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Crear la base de datos

Ejecuta el script en tu gestor de base de datos o directamente desde la terminal:

```bash
mysql -u root -p < bd/schema.sql
```

![Tablas creadas ](./src/assets/tables.png)

### 4. Cargar el catálogo de actividades

Este paso importa el archivo UNSPSC.xlsx de bienes y servicios a la tabla `actividades`.
Desde la terminal dentro de la carpeta `backend/`:

```bash
php bd/seed_actividades.php
```

Verás en pantalla cuántas filas se insertaron. Este proceso puede tardar
unos minutos por el volumen de datos (cerca de 49,000 registros).

### 5. Configurar el servidor web

El backend necesita un servidor web que apunte a la carpeta
`backend/public/` como raíz del sitio. Cómo hacerlo depende de tu entorno:

**Si usas Laragon:**
Ve a Menu → Apache → Virtual Hosts y apunta el dominio al directorio
`backend/public/` de este proyecto.

**Si usas XAMPP:**
Copia la carpeta del proyecto dentro de `htdocs/` y accede por
`http://localhost/suplos-licitaciones/backend/public/`.

**Si usas un servidor Linux (Ubuntu/Debian):**
Configura un VirtualHost en Apache apuntando el `DocumentRoot` a la
carpeta `backend/public/` del proyecto.

Lo importante sin importar el entorno es que:
- El servidor sirva desde `backend/public/`
- `mod_rewrite` esté habilitado en Apache
- PHP tenga acceso de escritura a la carpeta `backend/public/uploads/`

### 6. Configurar y correr el frontend

Abre `frontend/vite.config.js` y ajusta el `target` del proxy con la URL
donde está corriendo tu backend:

```javascript
proxy: {
  '/api': {
    target: 'http://localhost/licitaciones/backend/public',
    changeOrigin: true,
    rewrite: (path) => path,
  }
}
```

Luego en una terminal:

```bash
cd frontend
npm install
npm run dev
```

Abre el navegador en:

```
http://localhost:5173
```

---

## Flujo de uso de la aplicación

### Crear una licitación

1. Haz clic en **Nueva oferta**
2. Completa los campos de **Información Básica**: objeto, descripción, moneda,
   presupuesto y actividad (escribe al menos 3 letras para buscar)
3. Completa el **Cronograma**: fechas y horas de inicio y cierre
4. Haz clic en **Guardar** — el sistema genera automáticamente el consecutivo
   con el formato `PO-0001-25`

![Licitaciones creadas](./src/assets/listalicitaciones.png)

### Editar una licitación y agregar documentos

1. Desde el listado haz clic en el ícono de **lápiz**
2. Los campos se precargan con los datos existentes
3. En la sección **Documentos** haz clic en **Agregar documento**
4. Completa el título, descripción y sube un archivo PDF o ZIP
5. Haz clic en **Agregar** para vincularlo a la licitación
6. Haz clic en **Actualizar** para guardar todos los cambios

![Agregar documento](./src/assets/editlicitaciones.png)


### Ver el detalle de una licitación

1. Desde el listado haz clic en el ícono de **ojo**
2. Navega entre las pestañas: Información Básica, Cronograma y Documentos
3. Desde aquí también puedes ir directamente a editar

![Detalles de licitacion](./src/assets/detalleslicitacion.png)

### Exportar a Excel

1. En el listado aplica los filtros que necesites (o déjalos vacíos para exportar todo)
2. Haz clic en **Exportar Excel**
3. Se descarga automáticamente un archivo `.xls` con los resultados visibles

---

## Pruebas del backend con Postman o Thunder Client

Puedes probar la API directamente sin necesidad de abrir el frontend.
La URL base es la que configuraste en el servidor, por ejemplo:

```
http://localhost/licitaciones/backend/public
```

---

### Listar actividades

```
GET /api/actividades?search=gato
```

Respuesta:
```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 1234,
      "codigo_producto": 10101,
      "producto": "Gatos hidráulicos"
    }
  ]
}
```

---

### Listar ofertas con filtros

```
GET /api/ofertas?consecutivo=PO-0001&page=1
```

Respuesta:
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "data": [...],
    "total": 1,
    "per_page": 10,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

### Crear una oferta

```
POST /api/ofertas
Content-Type: application/json
```

Body:
```json
{
  "objeto": "Compra de equipos de cómputo",
  "descripcion": "Adquisición de equipos para las oficinas principales",
  "moneda": "COP",
  "presupuesto": "15000000.00",
  "actividad_id": 1234,
  "fecha_inicio": "2026-07-01",
  "hora_inicio": "08:00",
  "fecha_cierre": "2026-07-30",
  "hora_cierre": "17:00"
}
```

Respuesta exitosa:
```json
{
  "success": true,
  "message": "Oferta creada",
  "data": {
    "id": 1,
    "consecutivo": "PO-0001-26",
    "objeto": "Compra de equipos de cómputo",
    "estado": "activo",
    ...
  }
}
```

Respuesta con errores de validación:
```json
{
  "success": false,
  "message": "Errores de validación",
  "errors": {
    "objeto": "El campo objeto es obligatorio.",
    "fecha_cierre": "La fecha/hora de cierre debe ser posterior a la de inicio."
  }
}
```

---

### Ver detalle de una oferta

```
GET /api/ofertas/1
```

Respuesta:
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "consecutivo": "PO-0001-26",
    "objeto": "Compra de equipos de cómputo",
    "descripcion": "Adquisición de equipos para las oficinas principales",
    "moneda": "COP",
    "presupuesto": "15000000.00",
    "fecha_inicio": "2026-07-01",
    "hora_inicio": "08:00:00",
    "fecha_cierre": "2026-07-30",
    "hora_cierre": "17:00:00",
    "estado": "activo",
    "actividad": {
      "id": 1234,
      "producto": "Computadores personales"
    },
    "documentos": []
  }
}
```

---

### Editar una oferta

```
POST /api/ofertas/1
Content-Type: application/json
```

Body (misma estructura que crear):
```json
{
  "objeto": "Compra de equipos de cómputo actualizado",
  "descripcion": "Adquisición de equipos para todas las sedes",
  "moneda": "USD",
  "presupuesto": "8000.00",
  "actividad_id": 1234,
  "fecha_inicio": "2026-07-01",
  "hora_inicio": "08:00",
  "fecha_cierre": "2026-08-15",
  "hora_cierre": "17:00"
}
```

---

### Agregar un documento a una oferta

```
POST /api/ofertas/1/documentos
Content-Type: multipart/form-data
```

| Campo | Valor |
|-------|-------|
| titulo | Términos de referencia |
| descripcion | Documento oficial del proceso |
| archivo | archivo.pdf ← archivo binario |

Respuesta:
```json
{
  "success": true,
  "message": "Documento agregado",
  "data": {
    "id": 1,
    "licitacion_id": 1,
    "titulo": "Términos de referencia",
    "descripcion": "Documento oficial del proceso",
    "archivo": "uploads/doc_abc123.pdf"
  }
}
```

---

### Exportar listado a Excel

```
GET /api/ofertas/export?objeto=equipos
```

Descarga directamente un archivo `.xls` con los resultados filtrados.
Este endpoint se abre en el navegador o se configura en Postman
como descarga de archivo binario.

---

## Autor

Julian Mateo — [github.com/matteorudas](https://github.com/matteorudas)