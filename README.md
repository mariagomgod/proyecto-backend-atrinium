**Proyecto Backend Atrinium**
### Instalación
1. [Instalar XAMPP](https://www.apachefriends.org/download.html)
2. Arrancar PHP y Apache en el panel de control de XAMPP
3. [Instalar Symfony](https://symfony.com/download)
4. Clonar este repositorio
``git clone https://github.com/mariagomgod/proyecto-backend-atrinium.git``
5. Instalar dependencias del proyecto
``cd proyecto-backend-atrinium && composer update``
6. Aplicar las migraciones de base de datos
``php bin/console doctrine:migrations:migrate``
6. Ejecutar el servidor
``symfony server:start``
7. [Acceder al servicio](http://localhost:8000)
