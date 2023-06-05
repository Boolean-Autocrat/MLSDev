<?php


class hooks
{
    public function load($hook_name, $hooked_id)
    {
        global $conn, $lang, $config, $jscript;
        $result = null;
        //Load each hook file and call specified hook
        if ($handle = opendir($config['basepath'] . '/hooks')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..' && $file != 'CVS' && $file != '.svn' && strpos($file, '.dist.php')===false && strpos($file, '.php')) {
                    if (is_file($config['basepath'] . '/hooks/' . $file)) {
                        $path_parts = pathinfo($config['basepath'] . '/hooks/' . $file);
                        //Make sure this is a PHP file and not something esle..
                        if ($path_parts['extension'] == 'php') {
                            include_once $config['basepath'] . '/hooks/' . $file;
                            $class_name = str_replace('.php', '', $file);
                            $sub_hook = new $class_name();
                            if (method_exists($sub_hook, $hook_name)) {
                                $result = $sub_hook->$hook_name($hooked_id);
                            }
                        }
                    }
                }
            }
            closedir($handle);
        }
        return $result;
    }
}
