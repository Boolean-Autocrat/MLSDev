<?php
// ENGLISH LANGUAGE FILE
$lang = array();
//Step 1: Check File Permissions:
$lang['install_Page_Title'] = "Instalação Open-Realty";
$lang['install_version_warn'] = "Sua versão do php não é capaz de executar a versão atual do Open-Realty A instalação foi cancelada";
$lang['install_sqlversion_warn'] = "Sua versão do mysql não é capaz de executar a versão atual do Open-Realty A instalação foi cancelada";
$lang['install_php_version'] = "Sua versão atual do PHP é ";
$lang['install_sql_version'] = "Sua versão atual do MySQL é ";
$lang['install_php_required'] = "A versão atual do Open-Realty requer uma versão mínima do PHP do ";
$lang['install_sql_required'] = "A versão atual do Open-Realty requer uma versão mínima do MySQL ";
$lang['install_welcome'] = "Bem-vindo à ferramenta de instalação Open-Realty.";
$lang['install_intro'] = "Essa ferramenta o guiará na configuração da instalação do Open-Realty. Antes de começar, você deve ter criado um banco de dados em branco no seu sistema. Você também deve ter permissões de arquivo definidas para que os seguintes arquivos e diretórios possam ser gravados pelo servidor da Web.";
$lang['install_step_one_header'] = "Etapa 1: Verifique as permissões do arquivo:";
$lang['install_Permission_on'] = "Permissão em";
$lang['install_are_correct'] = "estão corretos";
$lang['install_are_incorrect'] = "estão incorretos";
$lang['install_all_correct'] = "Todas as permissões estão corretas.";
$lang['install_continue'] = "Clique para continuar a instalação";
$lang['install_please_fix'] = "Faça com que seu anfitrião ative os requisitos acima.";

//Step 1: Determine Install Type
$lang['install_select_type'] = 'Selecione o tipo de instalação:';
$lang['install_new'] = 'Nova instalação do Open-Realty';
$lang['move'] = 'Atualizar somente as informações de caminho e URL';
$lang['upgrade_200'] = 'Atualize do Open-Realty 2.x.x (2.0.0 Beta 1) ou mais recente)';

//Step 2: Setup Database Connection:
$lang['install_setup__database_settings'] = "Configurar conexão de banco de dados:";
$lang['install_Database_Type'] = "Tipo de banco de dados";
$lang['install_mySQL'] = "MySQL";
$lang['install_PostgreSQL'] = "PostgreSQL";
$lang['install_Database_Server'] = "Banco de dados:";
$lang['install_Database_Name'] = "Nome do banco de dados";
$lang['install_Database_User'] = "Usuário do banco de dados";
$lang['install_Database_Password'] = "Senha do banco de dados";
$lang['install_Table Prefix'] = "Prefixo da tabela:";
$lang['install_Base_URL'] = "URL base:";
$lang['install_Base_Path'] = "Caminho base:";
$lang['install_Language'] = "Idioma:";
$lang['install_English'] = "Inglês";
$lang['install_Spanish'] = "espanhol";
$lang['install_Italian'] = "italiano";
$lang['install_French'] = "francês";
$lang['install_Portuguese'] = "portuguesa";
$lang['install_Russian'] = "russa";
$lang['install_Turkish'] = "turca";
$lang['install_German'] = "alemã";
$lang['install_Dutch'] = "holandês";
$lang['install_Lithuanian'] = "lituano";
$lang['install_Arabic'] = "árabe";
$lang['install_Polish'] = "polonês";
$lang['install_Czech'] = "Tcheco";
$lang['install_Indonesian'] = "indonésio";
$lang['install_Bulgarian'] = "búlgaro";
$lang['install_connection_fail'] = "Não conseguimos nos conectar ao seu banco de dados. Verifique suas configurações e tente novamente.";

//Step Three
$lang['install_get_old_version'] = 'Determinando a versão antiga do Open-Realty';
$lang['install_get_old_version_error'] = 'Erro ao determinar a versão antiga do Open-Realty. A atualização não pode continuar.';
$lang['install_cleared_cache'] = "Cache limpo";
$lang['install_connection_ok'] = "Conseguimos nos conectar ao banco de dados.";
$lang['install_save_settings'] = "Agora vamos salvar suas configurações no seu arquivo common.php";
$lang['install_settings_saved'] = "Configurações de banco de dados salvas";
$lang['install_continue_db_setup'] = "Continue a configurar o banco de dados.";
$lang['install_populate_db'] = "Agora vamos preencher o banco de dados.";

//finalize installation
$lang['install_installation_complete'] = "A instalação está concluída.";
$lang['install_configure_installation'] = "Clique aqui para configurar sua instalação";

//2.2.0 additions.
$lang['install_devel_mode'] = "Instalação do modo de desenvolvedor - Isso permitirá que a instalação continue mesmo com erros. ISSO NÃO É RECOMENDADO.";
$lang['yes'] = "sim";
$lang['no'] = "Não";

//3.0.4 additions
$lang['curl_not_enabled'] = 'O PHP Curl Extenstion não está instalado';
$lang['warnings_php_zip'] = 'Sua instalação do PHP não tem as funções ZIP do PHP instaladas';
$lang['warnings_nothing'] = 'Nada de errado foi detectado para as configurações testadas.';
$lang['warnings_magic_quotes_gpc'] = '- Você tem “magic_quotes_gpc” realmente definido como “ON” no seu servidor, enquanto você deve tê-lo definido como “OFF”. Entre em contato com o suporte do anfitrião e peça para desativá-lo.';
$lang['warnings_mb_convert_encoding'] = '- O MBString não está habilitado no seu servidor e você o definiu como “Sim”. Modifique essa configuração para “Não” (na guia “Configuração do site”, “Editor/HTML”) ou entre em contato com o suporte do host e peça para ativá-la.';
$lang['warnings_mod_rewrite'] = '- Seu servidor tem “mod_rewrite” DESATIVADO e você ativou o URL TYPE como “Search Engine Friendly” (em “Configuração do site”, guia “SEO”). Entre em contato com o suporte do anfitrião e peça para ativá-lo.';
$lang['warnings_htaccess'] = '- Você não tem um arquivo “.htaccess”, mas ativou o URL TYPE como “Search Engine Friendly” (em “Configuração do site”, guia “SEO”). Volte para “URL padrão” e leia a documentação do Open-Realty® (<a href="http://docs.google.com/View?id=dhk2ckgx_4dw62x5fh#_SEO_Search_Engine_Optimizatio" title="Search engine optimization settings">aqui</a>).';
$lang['warnings_admin_password'] = '- Por motivos de segurança, você deve modificar a senha da conta de nome de usuário “admin”. Na verdade, ele ainda está definido como o padrão definido durante a instalação: “senha”.';
$lang['warnings_openssl'] = '- Open-Realty® v.3.0.0 e posterior, requer que o “openssl” seja carregado pelo PHP no seu servidor. Na verdade, você o tem DESATIVADO - entre em contato com o Suporte ao Host e peça para ativá-lo.';
$lang['warnings_safe_mode'] = 'O modo de segurança do PHP está habilitado, isso deve ser desativado.';
$lang['warnings_php_gd'] = 'Sua instalação do PHP não tem as bibliotecas GD instaladas';
$lang['warnings_php_exif'] = 'Sua instalação do PHP não tem a extensão Exif instalada';
$lang['file_path_contains_a_space'] = 'O caminho do sistema de arquivos contém um espaço, isso não é permitido.';
$lang['warnings_php_freetype'] = 'Sua instalação do PHP não tem as bibliotecas TTF FreeType instaladas';
