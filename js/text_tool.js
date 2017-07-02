jQuery(document).ready(function(){

    jQuery('.who_link a').click(function (e) {

        location.pathname === '/wp-admin/post.php' ? saveArticleText() : saveArticleTextByPOSTQuery()
    })
})



function saveArticleTextByPOSTQuery(){

    var locParams = getUrlParameters(jQuery('#wp-admin-bar-edit a')
        .attr('href')
        .replace(location.origin + '/wp-admin/post.php?', ''));

    var data = {
        'action' : 'fseo_tt_get_post_text_by_id',
        'post' : locParams.post
    }

    jQuery.post('/wp-admin/admin-ajax.php', data, function(response){
        localStorage.setItem(location.host.replace('.', '_') + '_' + locParams.post, response)
    },"json")
}

function saveArticleText() {

    var locParams = getUrlParameters(location.search.replace('?', ''))

    localStorage.setItem(location.host.replace('.', '_') + '_' + locParams.post, jQuery('textarea#content').val())
}


function getUrlParameters(query) {

    var result = {};

    query.split("&").forEach(function(part) {
        var item = part.split("=");
        result[item[0]] = decodeURIComponent(item[1]);
    });

    return result;
}