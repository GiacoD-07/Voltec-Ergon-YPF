# {nombre del proyecto}

**Descripción:**
{breve explicación del proyecto}

Integrantes:

- {Apellido, Nombre} | [@username](https://github.com/username)
- {Apellido, Nombre} | [@username](https://github.com/username)
- {Apellido, Nombre} | [@username](https://github.com/username)
- {Apellido, Nombre} | [@username](https://github.com/username)

Proyecto institucional **E.E.S.T Nº4 de Berazategui**.

## Elevator's Pitch

- Para {cliente objetivo}
- Quienes {necesidad y/o oportunidad}
- El {nombre del proyecto} es un {categoría del producto}
- Que {beneficio clave, razón para comprarlo}
- Diferente a {otras soluciones existentes, por ejemplo...}
- Nuestro proyecto {declaración de la diferencia}.

## Requisitos sin XAMPP

- PHP 8.1 o superior instalado de forma independiente.
- Extensiones PHP `pdo_mysql` y `curl` habilitadas.
- MySQL Server instalado y ejecutándose como servicio de Windows.
- Composer para instalar o verificar las dependencias PHP.
- Arduino IDE o PlatformIO para cargar el firmware del ESP32.

Apache no es obligatorio: el servidor integrado de PHP alcanza para este
proyecto. Para administrar MySQL podés usar MySQL Workbench, DBeaver o el
cliente `mysql`; phpMyAdmin también puede instalarse aparte, pero no es
necesario para que la aplicación funcione.

## Instalación local sin XAMPP

1. Instalá PHP y MySQL Server por separado. Durante la instalación de MySQL
   guardá la contraseña del usuario administrador.

2. Abrí PowerShell en la carpeta del proyecto y verificá las dependencias:

	```powershell
	composer install
	php -m | Select-String 'curl|pdo_mysql'
	```

3. Creá la base de datos ejecutando `scripts/setup.sql` desde MySQL Workbench,
   DBeaver o el cliente de consola:

	```powershell
	mysql -u root -p < scripts/setup.sql
	```

4. Verificá que `.env` tenga la configuración de MySQL:

	```env
	APP_ENV=dev
	DB_DRIVER=mysql
	DB_HOST=localhost
	DB_PORT=3306
	DB_NAME=ypf_energia
	DB_USER=root
	DB_PASS=
	```

5. Si configuraste una contraseña para el usuario `root`, escribila en
   `DB_PASS`. El archivo `.env` no debe subirse al repositorio.

6. Iniciá la aplicación con el script preparado para Windows:

	```powershell
	powershell -ExecutionPolicy Bypass -File .\scripts\start-local.ps1
	```

   También podés iniciarla manualmente:

	```powershell
	php -S 0.0.0.0:8000 -t public public/router.php
	```

7. Abrí `http://localhost:8000/`. Para acceder desde el ESP32, usá la IP de
   la PC y asegurate de permitir el puerto 8000 en el Firewall de Windows.

## Configuración del ESP32

Instalá también la librería `WiFiManager`. Al iniciar, el ESP32 intentará
conectarse a la última red guardada. Si no la encuentra, creará una red Wi-Fi
llamada `Energhost-Config`. Conectate a esa red desde el celular y seguí el
portal de configuración para elegir la red del lugar y escribir su contraseña.

De esta forma no necesitás recompilar el firmware cada vez que cambies de red.

Antes de cargar `firmware/main.ino`, modificá `serverApiUrl` para indicar la
IP de la PC y el puerto del servidor PHP:

```cpp
const char* serverApiUrl = "http://IP_DE_TU_PC:8000/voltec-slim/public/api";
const char* apiKey = "LA_MISMA_API_KEY_DEL_ARCHIVO_ENV";
```

La red Wi-Fi no se escribe en el código: `WiFiManager` la configura mediante
el portal `Energhost-Config` en el primer arranque.

La `IP_DE_TU_PC` es la dirección IPv4 de la computadora donde está instalado XAMPP. Podés verla ejecutando `ipconfig` y buscando **Dirección IPv4**. Si cambia la red y también cambia esa IP, deberás actualizar solo `serverApiUrl` y volver a cargar el firmware, salvo que uses una dirección fija o un nombre de red local.

La `apiKey` tiene que coincidir exactamente con `API_KEY` en `.env`. El panel web usa un token CSRF de corta duración y no necesita conocer esta clave.

Después de cargar el programa, abrí el monitor serial a `115200` baudios para verificar la conexión Wi-Fi y las respuestas de la API.

## Migraciones

Colocá tus archivos `.sql` en `src/database/migrations/` y ejecuta:

```bash
composer migrate
```

## Huella de carbono

El dashboard calcula la huella acumulada con la fórmula:

```text
kg CO2e = energia_total_kwh × CO2_EMISSION_FACTOR_KG_PER_KWH
```

El factor predeterminado es `0.39 kg CO2e/kWh`, tomado como referencia para
Argentina. Se puede ajustar en `.env` si se dispone de un factor actualizado
del proveedor eléctrico o de una fuente oficial. El resultado es una
estimación de emisiones asociadas al consumo de electricidad, no una medición
directa de gases.

## Servidor local de desarrollo

Para probar la aplicación en la misma computadora, ejecutá:

```bash
composer serve
```

Después abrí `http://localhost:8000/`. Este comando usa un router de desarrollo
para que la página, los archivos estáticos y los endpoints `/api/` funcionen en
el mismo servidor.

Para abrirla desde un celular conectado a la misma red Wi-Fi, ejecutá el
servidor escuchando en todas las interfaces:

```bash
php -S 0.0.0.0:8000 -t public public/router.php
```

En el celular abrí `http://IP_DE_TU_PC:8000/`. La PC y el celular deben estar en
la misma red y Windows debe permitir conexiones entrantes al puerto 8000.

## Licencia

Este proyecto está licenciado bajo la [Licencia MIT](./LICENSE).

---

Generado por la [plantilla](https://github.com/PDI-EEST4/slim-template-2026), a discreción de los integrantes del proyecto.
