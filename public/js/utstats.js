var infoLoadTimeout;
var nextRefreshInfoQueued = false;

var infoLoadDelay = infoLoadDelay || 5000;
var infoScrollAnimationDuration = infoScrollAnimationDuration || 500;
var eventPageInfoScrollAnimationDuration = eventPageInfoScrollAnimationDuration || 2000;
var locale = locale || 'en';

var taplistFilters = {};

$(document).ready(function() {
	if (locale !== undefined) {
		moment.locale(locale);
	}
	
	if ($('#live-feed').length !== 0) {
		refreshTimes();
		pushServer();
	}
	
	if ($('#event-info').length !== 0) {
		pushServerEventInfo();	
		
		$("#stats-carousel").on("slide.bs.carousel", function(e) {
			var $e = $(e.relatedTarget);
			var idx = $e.index();
			var itemsPerSlide = 3;
			var totalItems = $(".carousel-item").length;
			
		    if (idx >= totalItems - (itemsPerSlide - 1)) {
			    var it = itemsPerSlide - (totalItems - idx);
			    for (var i = 0; i < it; i++) {
				      // append slides to end
					if (e.direction == "left") {
						$(".carousel-item").eq(i).appendTo(".carousel-inner");
					} else {
						$(".carousel-item").eq(0).appendTo(".carousel-inner");
				    }
			    }
			}
		});
		
		setInterval(function(){
			scrollLatestNotifications();
		}, 8000)
	}
	
	
	if ($('#post-message').length !== 0) {
		$('#form_startTime').attr('min', minTime);
	}

	$('#filters-close').click(function() {
		$('#filters-menu').fadeOut(100);
		$('#filters-open').fadeIn(100);
	});
	
	$('#filters-open').click(function() {
		$('#filters-menu').fadeIn(100);
		$('#filters-open').fadeOut(100);
	})
	
	$('.tick').click(function() {
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
		} else {
			$(this).addClass('active');
		}
	})
	
	$('.favorite').click(function() {
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
			$(this).children('i').addClass('fa-star-o');
			$(this).children('i').removeClass('fa-star');
		} else {
			$(this).addClass('active');
			$(this).children('i').removeClass('fa-star-o');
			$(this).children('i').addClass('fa-star');
		}
	})
});

function pushServer(){
	var conn = new ab.Session(websocket, function() {
        conn.subscribe("checkins-" + $("#live-content").data('live-type') + "-" + $("#live-content").data('live-id'), function(topic, data) {
            console.log('Received ' + data.count + ' new checkins');
        	handleNewCheckinData(data);
        });
        conn.subscribe("info-" + $("#live-content").data('live-type') + "-" + $("#live-content").data('live-id'), function(topic, data) {
        	if (data.action == "reload") {
        		location.reload();
        	}
            $(".info-line-text").stop();
        	handleNewInfoData(data);
        });
    }, function() {
        console.warn('WebSocket connection closed');
        setTimeout(function(){
            location = '/live/' + $("#live-content").data('live-type') + "/" + $("#live-content").data('live-id')
        }, 20000)
    }, {
        'skipSubprotocolCheck': true
    });	
}

function pushServerEventInfo(){
	var conn = new ab.Session(websocket, function() {
        conn.subscribe("stats-" + $("#event-info").data('event-id'), function(topic, data) {
        	if (data.action == "reload") {
        		location.reload();
        	}
        	handleUpToDateEventStats(data);
        });
        conn.subscribe("notifications-" + $("#event-info").data('event-id'), function(topic, data) {
        	if (data.action == "reload") {
        		location.reload();
        	}
        	handleNewNotification(data);
        });
    }, function() {
        console.warn('WebSocket connection closed');
        setTimeout(function(){
            location = '/event/' + $("#event-info").data('event-id')
        }, 20000)
    }, {
        'skipSubprotocolCheck': true
    });	
}

function updateLivePage() {
	// Replaced by push notifications
	$.get("/ajax/getLiveCheckins/" + $("#live-content").data('live-type') + "/" + $("#live-content").data('live-id'), { minID: $("#live-feed").children("li").first().data('checkin'), format: "html" } )
	.done(function(data) {
		handleNewCheckinData(data);
	});
}

function handleNewCheckinData(data) {
	var append = "";
	var mediaAppend = "";
	$.each(data.checkins, function(key, value) {
		append += value;
    	$('#no-checkins').hide();
	});
	$.each(data.medias, function(key, value) {
		mediaAppend += value;
	});
	$('.media-number').each(function() {
		var newID = Number($(this).html()) + data.mediaCount;
		$(this).html(newID);
	});
	for (var i = 0; i < data.count; i++) {
		// Remove check-ins if more than 30 are displayed
		if ($("#live-feed").children("li").length > 30) {
			$("#live-feed").children("li").last().remove();
		}
	}
	for (var i = 0; i < data.mediaCount; i++) {
		// Remove photos if more than 35 are displayed
		if ($("#live-media").children("div").length > 35) {
			$("#live-media").children("div").last().remove();
		}
	}
	$(append).hide().prependTo('#live-feed').css('white-space', 'nowrap').animate({width:'toggle'}, { duration : 500, easing : "swing", complete : function() { $('#live-feed > li').css('white-space', ''); } });
	$(mediaAppend).hide().prependTo('#live-media').fadeIn();
}

function handleNewInfoData(data) {
    if (document.visibilityState == "visible") {
		$(".info-line-text").css('right' , '');
		$(".info-line-text").animate({ left: $(".info-line-text").parent().width() }, { duration : infoScrollAnimationDuration, easing : "swing" }).promise().then(
			function() {
				$("#info-line-1").html(data.line1);
				$("#info-line-2").html(data.line2);
				$("#info-line-3").html(data.line3);
				$(".info-line-text").css('left' , '');
				$(".info-line-text").each(function() {
					if ($(this).width() < $(this).parent().width()) {
						$(this).css('right' , $(this).parent().width());
					} else {
						$(this).css('right' , $(this).width());
					}
				});
				$(".info-line-text").animate({ right: '0' }, { duration : infoScrollAnimationDuration, easing : "swing" }).promise().then(
					function () {
						$(".info-line-text").each(function() {
							if ($(this).width() > $("#info-content").width()) {
								var scrollLength = $("#info-content").width() - $(this).width() - 6;
								var scrollDuration = infoLoadDelay - infoScrollAnimationDuration * 2;
								$(this).animate({ left: scrollLength }, { duration : scrollDuration });
							}
							$(this).find('.animated-increment').each(function () {
								$(this).animateNumber({ number: $(this).data('value') });
							});
						});
					}
				);
			}
		);
    } else {
		$("#info-line-1").html(data.line1);
		$("#info-line-2").html(data.line2);
		$("#info-line-3").html(data.line3);
		$(".info-line-text").each(function() {
			$(this).find('.animated-increment').each(function () {
				$(this).html($(this).data('value'));
			});
		});
    }
}

function handleUpToDateEventStats(data) {
	if ($('#stats-carousel').length === 0) {
		location.reload();
	}
	$.each(data.stats, function(key, stat) {
		if ($('.card[data-stat="'+key+'"]').length !== 0) {
			$('.card[data-stat="'+key+'"]').html('<span class="page-title stat-title info-major">' + stat.label + '</span>' + stat.render);
		} else {
			$('.carousel-inner').append('<div class="carousel-item col-md-4"><div class="card" data-stat="' + key + '"><span class="page-title stat-title info-major">' + stat.label + '</span>' + stat.render + '</div></div>');
		}
	});
}

function handleNewNotification(data) {
	$('#latest-notifications').show();
	$('#latest-notifications-block').prepend(data.message);
}

function refreshTimes() {
	$('.checkin-date').each(function() {
		$(this).html("(" + moment($(this).data('date')).fromNow() + ")");
	});
}

function scrollLatestNotifications() {
    if (document.visibilityState == "visible") {
		$(".info-line-text").each(function() {
			if ($(this).width() > $("#info-content").width()) {
				var scrollLength = $("#info-content").width() - $(this).width() - 6;
				$(this).animate({ left: scrollLength }, { duration : eventPageInfoScrollAnimationDuration, easing : "swing" }).promise().then(
					function() {
						$(this).delay(2000).animate({ left: 0 }, { duration : eventPageInfoScrollAnimationDuration / 3, easing : "swing" });
					}
				);
			}
		});
    }
}