$(document).ready(function() {

});

function updateLivePage() {
	$.get("/ajax/getVenueCheckins/" + $("#live-content").data('vid'), { minID: $("#live-feed").children("li").first().data('checkin') } )
	.done(function(data) {
		console.log(data);
		$.each(data, function(key, value) {
    		var append = '<li data-checkin="' + value.id + '">'
			append += '<div class="avatar-wrapper">';
    		if (value.user.is_supporter) {
				append += '<span class="supporter"></span>';
    		}
			append += '<a href="https://www.untappd.com/user/' + value.user.user_name + '" target="_blank">';
			append += '<img src="' + value.user.user_avatar + '" />';
			append += '</a>';
			append += '</div>';
			append += '<div class="checkin-info">';
			append += '<div class="line">';
			append += '<span class="checkin-item">' + value.user.untappd_link + ' is drinking a ' + value.beer.untappd_link + ' by ' + value.beer.brewery.untappd_link + ' <span class="checkin-date">(' + moment(value.created_at).fromNow() + ')</span></span>';
			append += '</div>';
			append += '<div class="line">';
			append += '<span class="rating small r' + value.integer_rating_score + '"></span>';
			if (value.comment != "") {
				append += '<span class="comment">"' + value.comment + '"</span>';
			}
        	append += '</div>';
        	append += '</div>';
        	append += '</li>';
        	$(append).hide().prependTo('#live-feed').fadeIn();
		});
	});
}