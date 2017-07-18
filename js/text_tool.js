jQuery(document).ready(function(){

    jQuery('.who_link a').click(function (e) {
        e.preventDefault()
        sendArticleTextByPOSTQuery()
    })
})



function sendArticleTextByPOSTQuery(){

    // getting url params
    var isAdmin = location.pathname ===  '/wp-admin/post.php'
    var urlParStr = isAdmin ? location.search.substr(1) : jQuery('#wp-admin-bar-edit a')
        .attr('href')
        .replace(location.origin + '/wp-admin/post.php?', '')
    var locParams = getUrlParameters(urlParStr);

    //send query to get post id, then send query to save text
    var data = {
        'action' : 'fseo_tt_get_post_text_by_id',
        'post' : locParams.post
    }

    jQuery.post('/wp-admin/admin-ajax.php', data, function(response){
        jQuery.post('http://api.workhard.kosmoz.online/v1/common/wamble/text', {text : response[0]}, function(answer){

            var url = 'http://workhard.kosmoz.online/tools/seo?text_id=' + answer.response

            window.open(url)

        },"json")
    },"json")
}


/**
 * Parse url parameters
 * **/
function getUrlParameters(query) {

    var result = {};

    query.split("&").forEach(function(part) {
        var item = part.split("=");
        result[item[0]] = decodeURIComponent(item[1]);
    });

    return result;
}