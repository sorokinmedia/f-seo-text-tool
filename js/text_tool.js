console.log('Вы авторизованы!')
jQuery(document).ready(function(){

    jQuery('.who_link a').click(function (e) {
        console.log('here')
        location.pathname === '/wp-admin/post.php' ? saveArticleText() : saveArticleTextByPOSTQuery()
    })
})

function saveArticleTextByPOSTQuery(){
    var locParams = getUrlParameters(jQuery('#wp-admin-bar-edit a').attr('href').replace(location.origin + '/wp-admin/post.php?', ''));

    console.log(locParams)
    var data = {
        'action' : 'fseo_tt_get_post_text_by_id',
        'post' : locParams.post
    }

    jQuery.post('/wp-admin/admin-ajax.php', data, function(response){
        localStorage.setItem(location.host.replace('.', '_') + '_' + locParams.post, response)
    },"json")
}

function saveArticleText() {
    var locParams = getUrlParameters(location.search)
    console.log(jQuery('textarea#content').val(),'-----', locParams)
    localStorage.setItem(location.host.replace('.', '_') + '' + locParams.post, jQuery('textarea#content').val())
}


function getUrlParameters(query) {

    var result = {};
    query.split("&").forEach(function(part) {
        var item = part.split("=");
        result[item[0]] = decodeURIComponent(item[1]);
    });
    return result;
}