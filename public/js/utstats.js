$(document).ready(function() {

});

function updateLivePage() {
	$.get("/ajax/getVenueCheckins/" + $("#live-content").data('vid'), { minID: $("#live-feed").children("li").first().data('checkin') } )
	.done(function(data) {
		var append = "";
		var mediaAppend = "";
		var count = 0;
		var mediaCount = 0;
		$.each(data, function(key, value) {
    		append += '<li data-checkin="' + value.id + '">'
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
        	count++;
        	
        	if (value.medias[0] !== undefined) {
        		mediaAppend += '<div class="checkin-photo" data-checkin="' + value.id + '">';
        		mediaAppend += '<a href="' + value.medias[0].photo_img_og + '" target="_blank"><img src="' + value.medias[0].photo_img_lg + '"></img></a>';
        		mediaAppend += '</div>';
        		mediaCount++;
        	}
		});
		for (var i = 0; i < count; i++) {
			console.log("removing element");
			$("#live-feed").children("li").last().remove();
		}
		for (var i = 0; i < mediaCount; i++) {
			console.log("removing photo");
			$("#live-media").children("div").last().remove();
		}
    	$(append).hide().prependTo('#live-feed').fadeIn();
    	$(mediaAppend).hide().prependTo('#live-media').fadeIn();
	});
}