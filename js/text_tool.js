jQuery(document).ready(function() {
    jQuery('.who_link a').click(function (e) {
        e.preventDefault();
        sendArticleTextByPOSTQuery();
    });


    //setTimeout(function () {
        if(jQuery('#wp-admin-bar-who_tools_seo_link')) {
            extendCommentsInterface()
        }
    //},1000)
});

/**
 * ОТправка статьи
 */
function sendArticleTextByPOSTQuery() {
    //getting url params
    var isAdmin = location.pathname ===  '/wp-admin/post.php';
    var urlParStr = isAdmin ? location.search.substr(1) : jQuery('#wp-admin-bar-edit a')
        .attr('href')
        .replace(location.origin + '/wp-admin/post.php?', '');
    var locParams = getUrlParameters(urlParStr);

    var wpData = {
        'action' : 'fseo_tt_get_post_text_by_id',
        'post' : locParams.post
    };

    //send post to get post id, then send post to save text
    jQuery.post('/wp-admin/admin-ajax.php', wpData, function(response) {
        console.log(response);
        var whoData = {
            text : response[0],
            url : location.origin,
            wp_post_id :  locParams.post
        };
        jQuery.post('https://api.workhard.online/v1/common/wamble/text', whoData, function(answer){

            var url = 'https://workhard.online/tools/seo?text_id=' + answer.response;

            window.open(url);

        },"json");
    },"json");
}

/**
 * Управление комментариями
 */

//точка входа в упрвлении комментами, проверяет роль,
// если валидная то добавляет интерфейс для редактирования комментов
function extendCommentsInterface() {
    console.log("Extending comments interface...");
    var wpData = {
        'action' : 'fseo_tt_is_valid_user'
    };
    jQuery.post('/wp-admin/admin-ajax.php', wpData, function(response) {
        console.log('CHECK',response);
        if(response.status === "success") {
            jQuery('#comments .comment-list .comment .comment-meta').each(function (index, elem) {
                var comment = Number(jQuery(jQuery(elem).parent())
                    .attr('id').replace('li-comment-', '')
                );
                console.log(comment);
                jQuery(elem).append(generateExtendedInterFace(comment));
            })
        }
    },"json");

}

//генерирует html для управления коммениариями
function generateExtendedInterFace(comment) {
    return '<span class="extended">' +
        '<button ' +
            'onclick="changeCommentClick(' +  comment + ')" ' +
            'class="comment-manage-button blue"' +
        '>' +
            'Изменить' +
        '</button>' +
        '<button ' +
        'class="comment-manage-button red" ' +
        'onclick="handleDeleteCommentClick(' +  comment + ')">Удалить</button>' +
    '</span>'
}

//удаляет интерфейс для управления комментарием по id
function hideCommentInterfaceById(id) {
    jQuery('#comment-' + id + ' span.extended').remove()
}

// добавляет интерфейс для управления комментарием по id
function extendCommentsInterfaceById(id) {
    jQuery('#li-comment-' + id + ' .comment-meta').append(generateExtendedInterFace(id))
}

//обработчик кнопки Изсменить
function changeCommentClick(id) {
    hideCommentInterfaceById(id);

    var content = jQuery("#li-comment-" + id).find(".comment-content .comment-content-text");
    var contentText =  jQuery(content).text();

    console.log(contentText)
    jQuery(content).html('<div>' +
        '<textarea rows="5" class="comment-content-text-input">' + contentText + '</textarea>' +
        '<input type="hidden" value="' + contentText + '" name="old-text" />' +
        '<button ' +
        'class="comment-manage-button blue" ' +
        'onclick="updateComment(' + id + ')">Сохранить</button>' +
        '<button ' +
        'class="comment-manage-button red" ' +
        'onclick="setCommentHtml(' + id + ')">Отмена</button>' +
    '</div>')
}
//обработчик кнопки удалить
function handleDeleteCommentClick(id) {
    var conf = confirm('Хотитие удалить комментарий ' + id + ' ?');
    if(conf) deleteComment(id)
}

//удаление коммента
function deleteComment(id) {
    var wpData = {
        'action' : 'fseo_tt_delete_comment',
        'commentId' : Number(id)
    };
    console.log('delete ID: ' + id, wpData);
    jQuery.post('/wp-admin/admin-ajax.php', wpData, function(response) {
        console.log(response);
        if(response.status === "success") jQuery("#li-comment-" + id).remove();
    },"json");
}
//обработчик кнопки сохоранить
function updateComment(id) {
    var commentContent = jQuery("#li-comment-" + id + " .comment-content-text-input").val();
    var wpData = {
        'action' : 'fseo_tt_update_comment',
        'commentId' : Number(id),
        'commentContent' : commentContent
    };
    console.log('ID: ' + id, wpData);
    jQuery.post('/wp-admin/admin-ajax.php', wpData, function(response) {
        var whoData = {
            text : response[0]
        };
        console.log(response);
        if(response.status === "success") setCommentHtml(id, commentContent);

    },"json");
}

//возрващает прежнний html коммента
function setCommentHtml(id, commentContent) {
    var contentElem = jQuery("#li-comment-" + id)
        .find(".comment-content .comment-content-text ");
    var text = commentContent ? commentContent : jQuery(contentElem).find("input[name='old-text']").val();
    jQuery(contentElem)
        .html('<p>' + text.replace(/\n/g, '\n<br/>') + '</p>');

    extendCommentsInterfaceById(id);
}

/*
* Parse URL params
* */
function getUrlParameters(query) {
    var result = {};

    query.split("&").forEach(function(part) {
        var item = part.split("=");
        result[item[0]] = decodeURIComponent(item[1]);
    });

    return result;
}