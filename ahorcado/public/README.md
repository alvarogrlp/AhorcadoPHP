# 🎮 Juego del Ahorcado en PHP

Este es un proyecto simple en **PHP** que implementa el clásico juego del **ahorcado** en el navegador usando sesiones.

---

## 🚀 Requisitos

- Tener docker y contenedor de docker de **PHP >= 7.4**.
- Un navegador web.
- Opcional: archivo `style.css` para mejorar el estilo.

---

## 📂 Estructura de archivos

```
ahorcado/
│── index.php
│── reset.php
│── style.css   (opcional)
```

---

## 🎨 Dibujo del ahorcado

El juego muestra el progreso del ahorcado en **ASCII** según los intentos restantes:

```
  +---+
  |   |
      |
      |
      |
      |
=========
```

```
  +---+
  |   |
  O   |
      |
      |
      |
=========
```

```
  +---+
  |   |
  O   |
  |   |
      |
      |
=========
```

```
  +---+
  |   |
  O   |
 /|   |
      |
      |
=========
```

```
  +---+
  |   |
  O   |
 /|\  |
      |
      |
=========
```

```
  +---+
  |   |
  O   |
 /|\  |
 /    |
      |
=========
```

```
  +---+
  |   |
  O   |
 /|\  |
 / \  |
      |
=========
```

---

## ▶️ Cómo ejecutar el proyecto

1. Copia los archivos en la carpeta de tu servidor web local.  
   Ejemplo en **XAMPP (Windows):**

   ```bash
   C:\xampp\htdocs\ahorcado\
   ```

   Ejemplo en **Linux (con Apache):**

   ```bash
   /var/www/html/ahorcado/
   ```

2. Inicia Apache desde tu servidor local.  
3. Abre en el navegador:

   ```bash
   http://localhost/ahorcado/index.php
   ```

---

## 📜 Licencia

Este proyecto es de uso libre para fines educativos.
