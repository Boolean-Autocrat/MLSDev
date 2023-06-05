<?php
// ENGLISH LANGUAGE FILE
$lang = array();
//Step 1: Check File Permissions:
$lang['install_Page_Title'] = "Instalar Open-Realty";
$lang['install_version_warn'] = "Su versión de php no puede ejecutar la versión actual de Open-Realty Installation ha sido cancelada";
$lang['install_sqlversion_warn'] = "Su versión de ql no es capaz de ejecutar la versión actual de la instalación de Open-Realty ha sido cancelada";
$lang['install_php_version'] = "Su versión actual de PHP es ";
$lang['install_sql_version'] = "Tu versión actual de MySql es ";
$lang['install_php_required'] = "La versión actual de Open-Realty requiere una versión mínima de PHP de ";
$lang['install_sql_required'] = "La versión actual de Open-Realty requiere una versión mínima de MySql ";
$lang['install_welcome'] = "Bienvenido a la herramienta de instalación de Open-Realth.";
$lang['install_intro'] = "Esta herramienta le guiará a través de la configuración de su instalación de Open-Reality. Antes de comenzar debe haber creado una base de datos en blanco en su sistema. También debe tener los permisos establecidos para que el servidor web pueda escribir los siguientes archivos y directorios.";
$lang['install_step_one_header'] = "Paso 1: Comprobar permisos de archivo:";
$lang['install_Permission_on'] = "Permiso en";
$lang['install_are_correct'] = "son correctos";
$lang['install_are_incorrect'] = "son incorrectos";
$lang['install_all_correct'] = "Todos los permisos son correctos.";
$lang['install_continue'] = "Haga clic para continuar con la instalación";
$lang['install_please_fix'] = "Por favor, tenga su host habilitado los requisitos anteriores.";

//Step 1: Determine Install Type
$lang['install_select_type'] = 'Seleccione el tipo de instalación:';
$lang['install_new'] = 'Nueva instalación de Open-Realty';
$lang['move'] = 'Actualizar sólo ruta e información de URL';
$lang['upgrade_200'] = 'Actualizar de Open-Realty 2.x.x (2.0.0 Beta 1) o posterior)';

//Step 2: Setup Database Connection:
$lang['install_setup__database_settings'] = "Configurar conexión de base de datos:";
$lang['install_Database_Type'] = "Tipo de base de datos:";
$lang['install_mySQL'] = "MySQL";
$lang['install_PostgreSQL'] = "PostgreSQL";
$lang['install_Database_Server'] = "Servidor de base de datos:";
$lang['install_Database_Name'] = "Nombre de la Base de Datos:";
$lang['install_Database_User'] = "Usuario de la base de datos:";
$lang['install_Database_Password'] = "Contraseña de la base de datos:";
$lang['install_Table Prefix'] = "Prefijo de tabla:";
$lang['install_Base_URL'] = "URL base:";
$lang['install_Base_Path'] = "Ruta base:";
$lang['install_Language'] = "Idioma:";
$lang['install_English'] = "Inglés";
$lang['install_Spanish'] = "Español";
$lang['install_Italian'] = "Italiano";
$lang['install_French'] = "Francés";
$lang['install_Portuguese'] = "Portugués";
$lang['install_Russian'] = "Ruso";
$lang['install_Turkish'] = "Turco";
$lang['install_German'] = "Alemán";
$lang['install_Dutch'] = "Holandés";
$lang['install_Lithuanian'] = "Lituano";
$lang['install_Arabic'] = "Árabe";
$lang['install_Polish'] = "Polaco";
$lang['install_Czech'] = "Checo";
$lang['install_Indonesian'] = "Indonesio/a";
$lang['install_Bulgarian'] = "Búlgaro";
$lang['install_connection_fail'] = "No podemos conectar a tu base de datos. Por favor, comprueba tu configuración e inténtalo de nuevo.";

//Step Three
$lang['install_get_old_version'] = 'Determinar la versión antigua de Open-Realty';
$lang['install_get_old_version_error'] = 'Error al determinar la versión antigua de Open-Realth. La actualización no puede continuar.';
$lang['install_cleared_cache'] = "Caché Limpiado";
$lang['install_connection_ok'] = "Somos capaces de conectar a la base de datos.";
$lang['install_save_settings'] = "Ahora vamos a guardar su configuración en su archivo common.php";
$lang['install_settings_saved'] = "Configuración de base de datos guardada.";
$lang['install_continue_db_setup'] = "Continuar con la configuración de la base de datos.";
$lang['install_populate_db'] = "Ahora vamos a llenar la base de datos.";

//finalize installation
$lang['install_installation_complete'] = "La instalación se ha completado.";
$lang['install_configure_installation'] = "Haga clic aquí para configurar su instalación";

//2.2.0 additions.
$lang['install_devel_mode'] = "Instalación en modo desarrollador - Esto permitirá que la instalación continúe incluso con errores. ESTO NO ES RECOMENDADO.";
$lang['yes'] = "Sí";
$lang['no'] = "Nu";

//3.0.4 additions
$lang['curl_not_enabled'] = 'La extensión PHP Curl no está instalada';
$lang['warnings_php_zip'] = 'Su instalación de PHP no tiene las funciones ZIP instaladas';
$lang['warnings_nothing'] = 'No se ha detectado ningún error en la configuración probada.';
$lang['warnings_magic_quotes_gpc'] = '- Usted tiene "magic_quotes_gpc" configurado a "ON" en su servidor mientras que usted debería tenerlo establecido en "OFF". Póngase en contacto con su equipo de soporte y pida que lo desactive.';
$lang['warnings_mb_convert_encoding'] = '- MBString no está activado en su servidor y lo tiene configurado a "Sí". Modifique esta configuración a "No" (en la pestaña "Configuración del sitio", "Editor/HTML") o póngase en contacto con el soporte de su host y pida que la active.';
$lang['warnings_mod_rewrite'] = '- Su servidor tiene "mod_rewrite" DISABLEADO y ha habilitado el URL TYPE como "Search Engine Friendly" (en la pestaña "Site Config", "SEO"). Póngase en contacto con su equipo de soporte y pida que lo active.';
$lang['warnings_htaccess'] = '- No tiene un archivo ".htaccess" pero ha habilitado el tipo de URL como "Search Engine Friendly" (en la pestaña "Site Config", "SEO"). Revertir a la "URL estándar" y leer la documentación Open-Realty® (<a href="http://docs.google.com/View?id=dhk2ckgx_4dw62x5fh#_SEO_Search_Engine_Optimizatio" title="Search engine optimization settings">aquí</a>).';
$lang['warnings_admin_password'] = '- Por razones de seguridad debe modificar la contraseña de la cuenta de usuario "admin". En realidad todavía se establece como el conjunto predeterminado durante la instalación: "password".';
$lang['warnings_openssl'] = '- Open-Realty® v.3.0.0 y posterior, requiere que PHP cargue "openssl" en su servidor. En realidad lo tienes DISABLED - contacta a tu Soporte de Anfitrión y pide que lo actives.';
$lang['warnings_safe_mode'] = 'El modo seguro de PHP está activado, esto debe desactivarse.';
$lang['warnings_php_gd'] = 'Su instalación de PHP no tiene las librerías GD instaladas';
$lang['warnings_php_exif'] = 'Su instalación de PHP no tiene la extensión Exif instalada';
$lang['file_path_contains_a_space'] = 'La ruta del sistema de archivos contiene un espacio, esto no está permitido.';
$lang['warnings_php_freetype'] = 'Su instalación de PHP no tiene las librerías TTF FreeType instaladas';
