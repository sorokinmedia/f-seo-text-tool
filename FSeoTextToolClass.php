<?php
/**
 * Class FSeoTextTool
 */
class FSeoTextToolClass
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
        add_action('wp_ajax_fseo_tt_update_comment', [$this, 'fseo_tt_update_comment'] );
        add_action('wp_ajax_fseo_tt_delete_comment', [$this, 'fseo_tt_delete_comment'] );
        add_action('wp_ajax_fseo_tt_is_valid_user', [$this, 'fseo_tt_is_valid_user'] );
    }

    /**
     * Подключаем JS скрипт и стили в админке
     */
    public function f_seo_text_tool_init()
    {
        $type = get_post_type((int) get_the_ID());
        if($type != 'post') return null;

        wp_enqueue_script('text_tool', plugins_url( 'js/text_tool.js', __FILE__ ), false, self::F_SEO_TEXT_TOOL_CURRENT_VERSION);
        wp_enqueue_style('text_tool_style', plugins_url( 'css/textTool.css', __FILE__ ), false, self::F_SEO_TEXT_TOOL_CURRENT_VERSION);
    }

    /**
     * Подключаем скрипты на сайте
     */
    public function f_seo_text_tool_enqueue_scripts()
    {
        if(!is_user_logged_in()) return null;

        wp_enqueue_script('text_tool', plugins_url( 'js/text_tool.js', __FILE__ ), [], self::F_SEO_TEXT_TOOL_CURRENT_VERSION, true);
        wp_enqueue_style('text_tool_style', plugins_url( 'css/textTool.css', __FILE__ ), [], self::F_SEO_TEXT_TOOL_CURRENT_VERSION);
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
        global $wpdb;
        $post_id = $_POST['post'];
        $post = get_post($post_id);
        $text = $post->post_title . " \n" . $post->post_content;
        $query = 'select c.comment_ID, c.comment_content from wp_comments as c
                  where c.comment_post_ID = %d AND c.comment_approved=1';

        $comments = $wpdb->get_results( $wpdb->prepare($query, $post_id), ARRAY_A );

        $comments_content = $this->build_comments_string($comments);

        echo json_encode([$text . $comments_content]);
        die();
    }

    /**
     * @param array $comments
     * @return string
     */
    function build_comments_string(array $comments) : string
    {
        $res = "\n\r----------------------------------------------------------------------\n\r";
        foreach($comments as $comment)
        {
            $res.= "\n\r" . $comment['comment_content'];
        }

        return $res;
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

    /**
     * Update post comment by ajax
     */
    function fseo_tt_update_comment()
    {
        $id = $_POST['commentId'];
        $content = $_POST['commentContent'];
        $comment = get_comment($id, ARRAY_A);
        $comment['comment_content'] = $content;

        $res = wp_update_comment($comment);

        echo json_encode(['status' => $res ? 'success' : 'fail']);
        die();
    }

    /**
     * Delete post comment by ajax
     */
    function fseo_tt_delete_comment()
    {
        $id = $_POST['commentId'];
        $res = wp_delete_comment($id);

        echo json_encode(['status' => $res ? 'success' : 'fail']);
        die();
    }

    function fseo_tt_is_valid_user() {
        $res = $this->checkUserRole('wambleChecker');

        $role = get_role('wambleChecker');

        echo json_encode(['status' => $res ? 'success' : 'fail', 'role' => $role]);
        die();
    }

    public function checkUserRole( $role, $user_id = null ) {
        if ( is_numeric( $user_id ) )
            $user = get_userdata( $user_id );
        else
            $user = wp_get_current_user();
        if ( empty( $user ) )
            return false;
        return in_array( $role, (array) $user->roles );
    }
}