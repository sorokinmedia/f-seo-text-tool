<?php
/*
Plugin Name: F-Seo Text Tool
Description: Добавляет кнопки для проверки текста на workhard.online/tools/seo
Author: F-Seo
Version: 1.0
Author URI: http://f-seo.ru/
*/

define ( 'F_SEO_TEXT_TOOL_CURRENT_VERSION',  '1.0' );

/**Подключаем JS скрипт и стили**/
function f_seo_text_tool_init(){
    $type = get_post_type((int) get_the_ID());
    var_dump($type);
    if($type != 'post') return null;

    wp_enqueue_style(
        'text_tool_style',
        plugins_url('css/text_tool_style.css', __FILE__),
        null,
        F_SEO_TEXT_TOOL_CURRENT_VERSION,
        'all'
    );
    wp_enqueue_script('text_tool', plugins_url( 'js/text_tool.js', __FILE__ ), false, F_SEO_TEXT_TOOL_CURRENT_VERSION);

}
add_action( 'admin_head', 'f_seo_text_tool_init' );

function f_seo_text_tool_enqueue_scripts(){

    if(!is_user_logged_in()) return null;

    wp_enqueue_style(
        'text_tool_style',
        plugins_url('css/text_tool_style.css', __FILE__),
        null,
        F_SEO_TEXT_TOOL_CURRENT_VERSION,
        'all'
    );
    wp_enqueue_script('text_tool', plugins_url( 'js/text_tool.js', __FILE__ ), false, F_SEO_TEXT_TOOL_CURRENT_VERSION);
}
add_action('wp_enqueue_scripts', 'f_seo_text_tool_enqueue_scripts');

/**страница администрирования**/
function f_seo_text_tool_setup_menu() {
    // верхний уровень
    add_menu_page('F-Seo Text Tool', 'F-Seo Text Tool', 'manage_option', 'f_seo_text_tool_settings_page', 'f_seo_text_tool_sb_admin');
    // подуровни
    add_submenu_page( 'f_seo_text_tool_settings_page', 'Настройки', 'Настройки', 'manage_options', 'f_seo_text_tool', 'f_seo_text_tool_sb_admin_settings');

    add_action( 'admin_init', 'register_f_seo_text_tool_settings' );
}
add_action('admin_menu', 'f_seo_text_tool_setup_menu');

//  настройки
function register_f_seo_text_tool_settings(){
    //register_setting( 'agi_settings-group', 'agi_img_width' );
    //register_setting( 'f_seo_text_tool_settings-group', 'react_keys_lighter_img_churl' );
}

function add_wp_admin_bar_new_item() {

    /**
     * Если пользователь на странице статьи или на странице редактирования статьи, покажем ссылку на WHO
     */
    if(isValidTextToolPage()){
        global $wp_admin_bar;
        global $post;
        $text_key = converUrlToKeyName(get_site_url()) . '_' . $post->ID;

        $wp_admin_bar->add_menu(array(
            'id' => 'who_tools_seo_link',
            'title' => __('Анализ текста'),
            'href' => 'https://workhard.online/tools/seo?text_key=' . $text_key,
            'meta' => array(
                'target' => '_blank',
                'class' => 'who_link'
            )
        ));
    }
}
add_action('wp_before_admin_bar_render', 'add_wp_admin_bar_new_item');

function isValidTextToolPage(){
    if(!is_user_logged_in()) return false;
    if(is_single()) return true;
    if(is_admin() && get_current_screen()->base == 'post' ) return true;

    return false;
}


function fseo_tt_get_post_text_by_id(){
    $post_id = $_POST['post'];
    $text = get_post($post_id)->post_content;
    echo json_encode($text);
    die();
}
add_action('wp_ajax_fseo_tt_get_post_text_by_id', 'fseo_tt_get_post_text_by_id' );


function converUrlToKeyName($url){
    $converter = ['http://' => '', 'https://' => '', '.' => '_'];

    return strtr($url, $converter);
}
