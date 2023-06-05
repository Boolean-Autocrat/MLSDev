<?php


class calculators
{
    /**
     * start_calc()
     * This is the function which displays the calculator.
     *
     * @return string Returns the html to dispaly the calculators
     */
    public function start_calc()
    {
        global $config, $lang,$jscript;
        include_once $config['basepath'] . '/include/core.inc.php';
        $page = new page_user();
        
        //Load TEmplate File
        $page->load_page($config['template_path'] . '/calculator.html');

        //We are done finish output
        $page->replace_lang_template_tags(true);
        $page->replace_permission_tags();
        $page->auto_replace_tags('', true);
        return $page->return_page();
    }
}
