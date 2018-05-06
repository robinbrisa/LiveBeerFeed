var infoLoadTimeout;
var nextRefreshInfoQueued = false;

var infoLoadDelay = infoLoadDelay || 5000;
var infoScrollAnimationDuration = infoScrollAnimationDuration || 500;
var eventPageInfoScrollAnimationDuration = eventPageInfoScrollAnimationDuration || 2000;
var locale = locale || 'en';

var taplistFilters = {};
var taplistSort = {key: 'brewery', order: -1}
var favorites = [];
var ticks = [];

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
	
	if ($('#taplist-content').length > 0) {
		$('#filters-open').click(function() {
			$('#filters-modal').modal('show')
		})
		
		$('#clear-search').click(function() {
			$('#search-beer').val("").trigger("keyup");
		})
		
		$('.tick').click(function() {
			var beerID = $(this).parents('.taplist-beer').data('id');
			if (beerID) {
				if ($(this).hasClass('active')) {
					$(this).removeClass('active');
					var idx = ticks.indexOf(beerID);
					if (idx > -1) {
						ticks.splice(idx, 1);
					}
				} else {
					ticks.push(beerID);
					$(this).addClass('active');
				}
				localStorage.setItem("ticks", JSON.stringify(ticks));
			}
		})
		
		$('.favorite').click(function() {
			var beerID = $(this).parents('.taplist-beer').data('id');
			if (beerID) {
				if ($(this).hasClass('active')) {
					$(this).removeClass('active');
					$(this).children('i').addClass('fa-star-o');
					$(this).children('i').removeClass('fa-star');
					var idx = favorites.indexOf(beerID);
					if (idx > -1) {
						favorites.splice(idx, 1);
					}
				} else {
					favorites.push(beerID);
					$(this).addClass('active');
					$(this).children('i').removeClass('fa-star-o');
					$(this).children('i').addClass('fa-star');
				}
				localStorage.setItem("favorites", JSON.stringify(favorites));
			}
		})
		
		$('#search-beer').keyup(function () {
			if ($(this).val() != "") {
				taplistFilters['search'] = $(this).val();
				$('#clear-search').css('display', 'inline-block');
			} else {
				delete taplistFilters['search'];
				$('#clear-search').css('display', 'none');
			}
			filterTapList();
		});
		
		$('.filter-enabler').change(function() {
			var value = true;
			var newState = $(this).prop('checked');
			setFilterStates();
			if ($('.filter-value[data-filter="'+$(this).data('filter')+'"]').length != 0) {
				value = $('.filter-value[data-filter="'+$(this).data('filter')+'"]').val();
			}
			if (newState) {
				taplistFilters[$(this).attr('id')] = value;
			} else {
				delete taplistFilters[$(this).attr('id')];
			}
			filterTapList();
		});
		
		$('.filter-value').change(function() {
			if ($('.filter-enabler[data-filter="'+$(this).data('filter')+'"]').prop('checked')) {
				taplistFilters[$(this).data('filter')] = $(this).val();
				filterTapList();
			}
		});
		
		$('.filter-modifier').change(function() {
			$('.filter-value[data-filter="'+$(this).data('filter')+'"]').trigger('change');
		});
		
		$('.session-filter').change(function() {
			taplistFilters['filteredSessions'] = [];
			$('.session-filter').each(function() {
				if (!$(this).prop('checked')) {
					taplistFilters['filteredSessions'].push($(this).data('session'));
				}
			});
			if (taplistFilters['filteredSessions'].length == 0) {
				delete taplistFilters['filteredSessions'];
			}
			filterTapList();
		});
	
		$('.style-filter').change(function() {
			taplistFilters['filteredStyles'] = [];
			$('.style-filter').each(function() {
				if (!$(this).prop('checked')) {
					taplistFilters['filteredStyles'].push($(this).data('style'));
				}
			});
			if (taplistFilters['filteredStyles'].length == 0) {
				delete taplistFilters['filteredStyles'];
			}
			filterTapList();
		});
		
		$('.filters-sort-select').change(function() {
			var optionSelected = $("option:selected", this);
			var keyVal = $(optionSelected).data('key');
			var orderVal = $(optionSelected).data('order');
			taplistSort = { key: keyVal, order: orderVal };
			localStorage.setItem("taplistSort", JSON.stringify(taplistSort));
			sortTaplist();
		})
		
		if (localStorage.getItem("taplistSort") == null) {
			localStorage.setItem("taplistSort", JSON.stringify(taplistSort));
		} else {
			taplistSort = JSON.parse(localStorage.getItem("taplistSort"));
		}
		
		if (localStorage.getItem("favorites") != null) {
			favorites = JSON.parse(localStorage.getItem("favorites"));
			initFavorites();
		}

		if (localStorage.getItem("ticks") != null) {
			ticks = JSON.parse(localStorage.getItem("ticks"));
			initTicks();
		}
		
		$('.open-untappd').click(function(e) {
			e.preventDefault();
			var beerID = $(this).parents('.taplist-beer').data('id');
			if (platform.os.family == "Android" || platform.os.family == "iOS") {
				window.location = 'untappd://beer/'+beerID
			} else {
				window.open($(this).prop('href'));
			}
		});
		
		initTaplistSort();
		setFilterStates();
	}
});

function filterTapList() {
	$('.taplist-beer').removeClass("filtered");
	$.each(taplistFilters, function(key, val) {
		switch (key) {
			case "minScore":
				$('.taplist-beer').filter(function() { 
					  return $(this).data("score") <= val 
				}).addClass("filtered");
				break;
			case "maxScore":
				$('.taplist-beer').filter(function() { 
					  return $(this).data("score") >= val 
				}).addClass("filtered");
				break;
			case "minABV":
				$('.taplist-beer').filter(function() { 
					  return parseFloat($(this).data("abv")) <= parseFloat(val)
				}).addClass("filtered");
				break;
			case "maxABV":
				$('.taplist-beer').filter(function() { 
					  return parseFloat($(this).data("abv")) >= parseFloat(val)
				}).addClass("filtered");
				break
			case "filteredSessions":
				$.each(val, function(idx, session) {
					$('.taplist-beer').filter(function() { 
						  return $(this).data("session") == session
					}).addClass("filtered");
				});
				break;
			case "filteredStyles":
				$.each(val, function(idx, category) {
					$('.taplist-beer').filter(function() { 
						  return $(this).data("style-category") == category
					}).addClass("filtered");
				});
				break;
			case "search":
				$('.taplist-beer:not(:Contains(' + val + '))').addClass("filtered"); 
				break;
			case "showFavorites":
				$('.taplist-beer').filter(function() { 
					  return !$(this).find(".favorite").hasClass('active')
				}).addClass("filtered");
				break;
			case "hideTicked":
				$('.taplist-beer').filter(function() { 
					  return $(this).find(".tick").hasClass('active')
				}).addClass("filtered");
				break;
		}
	});
}

function sortTaplist() {
	var beers = $('#taplist-content'),
	beerDiv = beers.children('.taplist-beer');
	
	beerDiv.sort(function(a,b){
		var an = a.getAttribute("data-"+taplistSort.key),
			bn = b.getAttribute("data-"+taplistSort.key);
		if ($.isNumeric(an)) {
			an = parseFloat(an);
			bn = parseFloat(bn);
		} else {
			an = an.toUpperCase();
			bn = bn.toUpperCase();
		}
		if(an < bn) {
			return 1 * taplistSort.order;
		}
		if(an > bn) {
			return -1 * taplistSort.order;
		}
		return 0;
	});
	beerDiv.detach().appendTo(beers);
}

function initTaplistSort() {
	var option = $('option[data-key="'+taplistSort.key+'"][data-order="'+taplistSort.order+'"]');
	$('#taplist-sort-select').val($(option).val());
	sortTaplist();
}

function initFavorites() {
	$.each(favorites, function(idx, val) {
		var favoriteElement = $('.taplist-beer[data-id="'+val+'"]').find('.favorite');
		$(favoriteElement).addClass('active');
		$(favoriteElement).children('i').removeClass('fa-star-o');
		$(favoriteElement).children('i').addClass('fa-star');
	});
}

function initTicks() {
	$.each(ticks, function(idx, val) {
		var favoriteElement = $('.taplist-beer[data-id="'+val+'"]').find('.tick');
		$(favoriteElement).addClass('active');
	});
}

function setFilterStates() {
	$('.filter-enabler').each(function() {
		var newState = $(this).prop('checked');
		$(this).parent().parent().find('.input-group-remote').find('input').each(function() {
			$(this).prop('disabled', !newState);
			if (newState) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	});
}

function pushServer(){
	var conn = new ab.Session(websocket, function() {
        conn.subscribe("checkins-" + $("#live-content").data('live-type') + "-" + $("#live-content").data('live-id'), function(topic, data) {
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
			currentTitle = $('.card[data-stat="'+key+'"]').children('.stat-title').html();
			$('.card[data-stat="'+key+'"]').html('<span class="page-title stat-title info-major">' + currentTitle + '</span>' + stat.render);
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

jQuery.expr[":"].Contains = jQuery.expr.createPseudo(function(arg) {
    return function( elem ) {
        return jQuery(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };
});