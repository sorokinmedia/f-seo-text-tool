<?php

/**
 * Class FSeoTextTool
 */
class FSeoTextTool
{
    const F_SEO_TEXT_TOOL_CURRENT_VERSION = '1.0'; // текущая версия

    /**
     * FSeoTextTool constructor.
     */
    public function __construct()
    {
        add_action('admin_head', [$this,'f_seo_text_tool_init'] );
        add_action('wp_enqueue_scripts', [$this, 'f_seo_text_tool_enqueue_scripts']);
        add_action('wp_before_admin_bar_render', [$this,'add_wp_admin_bar_new_item']);
        add_action('wp_ajax_fseo_tt_get_post_text_by_id', [$this, 'fseo_tt_get_post_text_by_id'] );

    }

    /**
     * Подключаем JS скрипт и стили
     */
    public function f_seo_text_tool_init()
    {
        $type = get_post_type((int) get_the_ID());
        var_dump($type);
        if($type != 'post') return null;

        wp_enqueue_style(
            'text_tool_style',
            plugins_url('css/text_tool_style.css', __FILE__),
            null,
            self::F_SEO_TEXT_TOOL_CURRENT_VERSION,
            'all'
        );
        wp_enqueue_script('text_tool', plugins_url( 'js/text_tool.js', __FILE__ ), false, self::F_SEO_TEXT_TOOL_CURRENT_VERSION);
    }

    /**
     * тоже подключает скрипты?
     */
    public function f_seo_text_tool_enqueue_scripts()
    {
        if(!is_user_logged_in()) return null;
        wp_enqueue_style(
            'text_tool_style',
            plugins_url('css/text_tool_style.css', __FILE__),
            null,
            self::F_SEO_TEXT_TOOL_CURRENT_VERSION,
            'all'
        );
        wp_enqueue_script('text_tool', plugins_url( 'js/text_tool.js', __FILE__ ), false, self::F_SEO_TEXT_TOOL_CURRENT_VERSION);
    }

    /**
     * добавляет кнопку в админ бар
     */
    public function add_wp_admin_bar_new_item()
    {
        // Если пользователь на странице статьи или на странице редактирования статьи, покажем ссылку на WHO
        if($this->isValidTextToolPage()){
            global $wp_admin_bar;
            global $post;
            $text_key = $this->convertUrlToKeyName(get_site_url()) . '_' . $post->ID;

            $wp_admin_bar->add_menu(array(
                'id' => 'who_tools_seo_link',
                'title' => __('SEO анализ текста'),
                'href' => 'https://workhard.online/tools/seo',
                'meta' => array(
                    'target' => '_blank',
                    'class' => 'who_link'
                )
            ));
        }
    }

    /**
     * проверяет текущую страницу, на которой находится пользователь
     * @return bool
     */
    function isValidTextToolPage() : bool
    {
        if(!is_user_logged_in()) return false;
        if(is_single()) return true;
        if(is_admin() && get_current_screen()->base == 'post' ) return true;

        return false;
    }

    /**
     * получает текст поста по ID
     */
    function fseo_tt_get_post_text_by_id()
    {
        $post_id = $_POST['post'];
        $text = get_post($post_id)->post_content;

        echo json_encode([$text]);
        die();
    }

    /**
     * конвертер урла
     * @param string $url
     * @return string
     */
    function convertUrlToKeyName(string $url) : string
    {
        $converter = ['http://' => '', 'https://' => '', '.' => '_'];
        return strtr($url, $converter);
    }

}