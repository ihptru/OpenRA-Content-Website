function strip_tags (input, allowed) {
    allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
        return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
    });
}

function updateContent (object, content) {
    var text = document.getElementById(content).value;
	text = text.replace(/\n\r?/g, '<br />');
    document.getElementById(object).innerHTML = text;
}

function updateContentWithStrip (object, content, allowed) {
	var text = strip_tags(document.getElementById(content).value, allowed);
	text = text.replace(/\n\r?/g, '<br />');
    document.getElementById(object).innerHTML = text;
}
