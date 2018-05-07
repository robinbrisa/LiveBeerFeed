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
var untappdButtonAction = 'quick-checkin';

$(document).ready(function() {
	if (locale !== undefined) {
		moment.locale(locale);
	}

	$(".modal").on("shown.bs.modal", function()  {
	    var urlReplace = "#" + $(this).attr('id'); 
	    history.pushState(null, null, urlReplace); 
	});

	$(window).on('popstate', function() { 
		$(".modal").modal('hide');
	});
	
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
				tickBeer(beerID, !$(this).hasClass('active'));
			}
		})
		
		$('.favorite').click(function() {
			var beerID = $(this).parents('.taplist-beer').data('id');
			if (beerID) {
				favoriteBeer(beerID, !$(this).hasClass('active'));
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
		
		$('#taplist-sort-select').change(function() {
			var optionSelected = $("option:selected", this);
			var keyVal = $(optionSelected).data('key');
			var orderVal = $(optionSelected).data('order');
			taplistSort = { key: keyVal, order: orderVal };
			localStorage.setItem("taplistSort", JSON.stringify(taplistSort));
			sortTaplist();
		})
		
		$('#taplist-buttonAction-select').change(function() {
			untappdButtonAction = $(this).val();
			saveData();
		})
				
		if (localStorage.getItem("taplistSort") == null) {
			localStorage.setItem("taplistSort", JSON.stringify(taplistSort));
		} else {
			taplistSort = JSON.parse(localStorage.getItem("taplistSort"));
		}

		$('#quick-checkin-modal').on("keyup", '#checkin-comment', function() {
			var remainingChars = 140 - $(this).val().length;
			$('#comment-chars-left').html(remainingChars);
		});
		
		$('#quick-checkin-modal').on("change", '#ratingScoreRange', function() {
			if ($(this).val() == 0) {
				$('#ratingScoreInput').val('No Rating');
			}
		});

		$(document).on("click", '.open-checkin', function(e) {
			e.preventDefault();
			if (platform.os.family == "Android" || platform.os.family == "iOS") {
				window.location = 'untappd://checkin/'+$(this).data('checkin');
			} else {
				window.open($(this).prop('href'));
			}
		});
		
		$("#quick-checkin-modal").on("hidden.bs.modal", function(){
			$("#quick-checkin-modal").find(".modal-body").html('<div class="quick-checkin-loading"><i class="fa fa-spinner fa-pulse"></i></div>');
		});
		
		$(document).on("submit", '#quick-checkin-form', function(e) {
			e.preventDefault();
			$('#submit-quick-checkin').prop('disabled', true);
			$('#submit-quick-checkin').html('<i class="fa fa-spinner fa-pulse"></i>')
			$.post('/ajax/addCheckin', $('#quick-checkin-form').serialize(), function(data) {
				if (data.success) {
					$('#quick-checkin-modal').find('.modal-body').html(data.display);
					tickBeer(data.response.beer.bid, true);
				} else {
					$('#submit-quick-checkin').prop('disabled', false);
					$('#quick-checkin-error').show();
					$('#submit-quick-checkin').html("Retry");
				}
			})
			.fail(function(data) {
				$('#submit-quick-checkin').prop('disabled', false);
				$('#quick-checkin-error').show();
				$('#submit-quick-checkin').html("Retry");
			});
		});

		initSavedData();
		
		$('#data-keep-remote').click(function() {
			favorites = JSON.parse(savedFavorites);
			ticks = JSON.parse(savedTicks);
			initFavorites();
			initTicks();
			saveData();
		});

		
		$('.open-untappd').click(function(e) {
			e.preventDefault();
			var beerID = $(this).parents('.taplist-beer').data('id');
			switch (untappdButtonAction) {
				case "open-app":
					if (platform.os.family == "Android" || platform.os.family == "iOS") {
						window.location = 'untappd://beer/'+beerID
					} else {
						window.open($(this).prop('href'));
					}
					break;
				case "open-web":
					window.open($(this).prop('href'));
					break;
				case "quick-checkin":
					if ($("#logged-in").length > 0) {
						$('#quick-checkin-modal').modal('show');
						$('#quick-checkin-modal').find('.modal-body').load( "/ajax/quickCheckInModal/" + $('#event-taplist').data('event-id') + "/" + beerID);
					} else {
						if (platform.os.family == "Android" || platform.os.family == "iOS") {
							window.location = 'untappd://beer/'+beerID
						} else {
							window.open($(this).prop('href'));
						}
					}
					break;
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

function initSavedData() {
	if (localStorage.getItem("favorites") != null) {
		favorites = JSON.parse(localStorage.getItem("favorites"));
		if (typeof savedFavorites !== "undefined") {
			if (!favorites.equals(JSON.parse(savedFavorites))) {
				$('#data-conflict-modal').modal({backdrop: 'static'});
			}
		}
	} else {
		if (typeof savedFavorites !== "undefined") {
			favorites = JSON.parse(savedFavorites);
		}
	}
	initFavorites();
	
	if (localStorage.getItem("ticks") != null) {
		ticks = JSON.parse(localStorage.getItem("ticks"));
		if (typeof savedTicks !== "undefined") {
			if (!ticks.equals(JSON.parse(savedTicks))) {
				$('#data-conflict-modal').modal({backdrop: 'static'});
			}
		}
	} else {
		if (typeof savedTicks !== "undefined") {
			ticks = JSON.parse(savedTicks);
		}
	}
	initTicks();

	if (typeof savedUntappdButtonAction !== "undefined" && savedUntappdButtonAction != "") {
		untappdButtonAction = savedUntappdButtonAction;
	} else {
		if (localStorage.getItem("untappdButtonAction") != null) {
			untappdButtonAction = localStorage.getItem("untappdButtonAction");
		}
	}
	$('#taplist-buttonAction-select').val(untappdButtonAction);
}

function initTaplistSort() {
	var option = $('option[data-key="'+taplistSort.key+'"][data-order="'+taplistSort.order+'"]');
	$('#taplist-sort-select').val($(option).val());
	sortTaplist();
}

function initFavorites() {
	$('.taplist-beer').find('.favorite').removeClass('active');
	$.each(favorites, function(idx, val) {
		var favoriteElement = $('.taplist-beer[data-id="'+val+'"]').find('.favorite');
		$(favoriteElement).addClass('active');
		$(favoriteElement).children('i').removeClass('fa-star-o');
		$(favoriteElement).children('i').addClass('fa-star');
	});
}

function saveData() {
	localStorage.setItem("favorites", JSON.stringify(favorites));
	localStorage.setItem("ticks", JSON.stringify(ticks));
	localStorage.setItem("untappdButtonAction", untappdButtonAction);
	if ($("#logged-in").length > 0) {
		$.post('/ajax/saveTaplistData', { event: $('#event-taplist').data('event-id'), favorites: JSON.stringify(favorites), ticks: JSON.stringify(ticks), buttonAction: untappdButtonAction })
	}
}

function initTicks() {
	$('.taplist-beer').find('.tick').removeClass('active');
	$.each(ticks, function(idx, val) {
		var favoriteElement = $('.taplist-beer[data-id="'+val+'"]').find('.tick');
		$(favoriteElement).addClass('active');
	});
}

function tickBeer(beerID, enable) {
	var tickElement = $('.taplist-beer[data-id="'+beerID+'"]').find('.tick'); 
	var idx = ticks.indexOf(beerID);
	if (enable) {
		// Avoid dupes
		if (idx == -1) {
			ticks.push(beerID);
		}
		tickElement.addClass('active');
	} else {
		if (idx > -1) {
			ticks.splice(idx, 1);
		}
		tickElement.removeClass('active');
	}
	saveData();
	filterTapList();
}

function favoriteBeer(beerID, enable) {
	var favoriteElement = $('.taplist-beer[data-id="'+beerID+'"]').find('.favorite'); 
	var idx = favorites.indexOf(beerID);
	if (enable) {
		// Avoid dupes
		if (idx == -1) {
			favorites.push(beerID);
		}
		favoriteElement.addClass('active');
		favoriteElement.children('i').removeClass('fa-star-o');
		favoriteElement.children('i').addClass('fa-star');
	} else {
		if (idx > -1) {
			favorites.splice(idx, 1);
		}
		favoriteElement.removeClass('active');
		favoriteElement.children('i').addClass('fa-star-o');
		favoriteElement.children('i').removeClass('fa-star');
	}
	saveData();
	filterTapList();
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

if(Array.prototype.equals)
    console.warn("Overriding existing Array.prototype.equals. Possible causes: New API defines the method, there's a framework conflict or you've got double inclusions in your code.");
// attach the .equals method to Array's prototype to call it on any array
Array.prototype.equals = function (array) {
    // if the other array is a falsy value, return
    if (!array)
        return false;

    // compare lengths - can save a lot of time 
    if (this.length != array.length)
        return false;

    for (var i = 0, l=this.length; i < l; i++) {
        // Check if we have nested arrays
        if (this[i] instanceof Array && array[i] instanceof Array) {
            // recurse into the nested arrays
            if (!this[i].equals(array[i]))
                return false;       
        }           
        else if (this[i] != array[i]) { 
            // Warning - two different object instances will never be equal: {x:20} != {x:20}
            return false;   
        }           
    }       
    return true;
}
// Hide method from for-in loops
Object.defineProperty(Array.prototype, "equals", {enumerable: false});
