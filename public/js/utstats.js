$(document).ready(function() {
	if ($('#live-feed').length !== 0) {
		infoLoadTimeout = setTimeout(function() {
			refreshInfo();
		}, infoLoadDelay);

		infoLoadTimeout = setInterval(function() {
			if (infoLoadTimeout === undefined) {
				console.log('Catched broken info refresh timer. Restarting...');
				refreshInfo();
			}
		}, 60000);
		
		
	}
});

var infoLoadTimeout;
var nextRefreshInfoQueued = false;

var infoLoadDelay = infoLoadDelay || 5000;
var infoScrollAnimationDuration = infoScrollAnimationDuration || 500;

function updateLivePage() {
	$.get("/ajax/getLiveCheckins/" + $("#live-content").data('live-type') + "/" + $("#live-content").data('live-id'), { minID: $("#live-feed").children("li").first().data('checkin') } )
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
        	if (value.medias[0] !== undefined) {
        		append += '<div class="has-media"><span class="media-number">' + (mediaCount + 1) + '</span></div>';
        	}
        	append += '</li>';
        	count++;
        	
        	if (value.medias[0] !== undefined) {
        		mediaAppend += '<div class="checkin-photo" data-checkin="' + value.id + '">';
        		mediaAppend += '<div class="media-id"><span class="media-number">' + (mediaCount + 1) + '</span></div>';
        		mediaAppend += '<a href="' + value.medias[0].photo_img_og + '" target="_blank"><img src="' + value.medias[0].photo_img_lg + '"></img></a>';
        		mediaAppend += '</div>';
        		mediaCount++;
        	}
		});
		$('.media-number').each(function() {
			var newID = Number($(this).html()) + mediaCount;
			$(this).html(newID);
		});
		for (var i = 0; i < count; i++) {
			$("#live-feed").children("li").last().remove();
		}
		for (var i = 0; i < mediaCount; i++) {
			$("#live-media").children("div").last().remove();
		}
    	$(append).hide().prependTo('#live-feed').animate({width:'toggle'},350);
    	$(mediaAppend).hide().prependTo('#live-media').fadeIn();
	});
}

function refreshInfo() {
	$.get("/ajax/getEventInfoMessage/" + $("#live-content").data('live-id'), {} ).done(function(data) {
		infoLoadTimeout = 'PENDING';
		$(".info-line-text").css('right' , '');
		$(".info-line-text").animate({ left: $(".info-line-text").parent().width() }, { duration : infoScrollAnimationDuration, easing : "swing" }).promise().then(
			function() {
				$("#info-line-1").html(data.line1);
				$("#info-line-2").html(data.line2);
				$("#info-line-3").html(data.line3);
				$(".info-line-text").css('left' , '');
				$(".info-line-text").css('right' , $(".info-line-text").parent().width());
				$(".info-line-text").animate({ right: '0' }, { duration : infoScrollAnimationDuration, easing : "swing" }).promise().then(
					function () {
						infoLoadTimeout = setTimeout(function() {
							refreshInfo();
						}, infoLoadDelay)
						$(".info-line-text").each(function() {
							if ($(this).width() > $("#info-content").width()) {
								var scrollLength = $("#info-content").width() - $(this).width() - 6;
								var scrollDuration = infoLoadDelay - infoScrollAnimationDuration * 2;
								$(this).animate({ left: scrollLength }, { duration : scrollDuration });
							}
						});
					}
				);
			}
		);
	}).fail(function() {
		infoLoadTimeout = undefined;
	});
}