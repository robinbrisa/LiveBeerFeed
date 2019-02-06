var infoLoadTimeout;
var nextRefreshInfoQueued = false;

var infoLoadDelay = infoLoadDelay || 5000;
var infoScrollAnimationDuration = infoScrollAnimationDuration || 500;
var eventPageInfoScrollAnimationDuration = eventPageInfoScrollAnimationDuration || 2000;
var locale = locale || 'en';

var taplistFilters = {};
var taplistSort = {key: 'brewery', order: 'asc'}
var favorites = [];
var ticks = [];
var tickCheckIn = true;
var untappdButtonAction = 'quick-checkin';
var currentSession;

$(document).ready(function() {
	if (locale !== undefined) {
		moment.locale(locale);
	}

	$(".modal").on("shown.bs.modal", function()  {
	    var urlReplace = "#" + $(this).prop('id'); 
	    history.pushState(null, null, urlReplace); 
	});

	$(window).on('popstate', function() { 
		$(".modal").modal('hide');
	});
	
	if ($('#live-feed').length !== 0) {
		refreshTimes();
		pushServer();
	}

	if ($('#search-results').length !== 0) {
		$('.search-results-row').each(function(row) {
			if ($(this).find('.search-match-selected').length !== 0) {
				$(this).find('.search-match-not-selected').hide();
			}
		});
		
		$('.search-results-expand').click(function() {
			if ($(this).find('.fa-plus').length !== 0) {
				$(this).parent().find('.search-match-not-selected').show();
				$(this).find('.fa-plus').removeClass('fa-plus').addClass('fa-minus');
			} else {
				$(this).parent().find('.search-match-not-selected').hide();
				$(this).find('.fa-minus').removeClass('fa-minus').addClass('fa-plus');
			}
		});
		
		$('.search-match-name').click(function() {
			var matchElement = $(this);
			$.post('/ajax/selectSearchResult', { resultID: matchElement.parent().data('result') }, function(data) {
				if (data.success) {
					matchElement.parent().parent().find('.search-match-selected').removeClass('search-match-selected').addClass('search-match-not-selected');
					matchElement.parent().removeClass('search-match-not-selected').addClass('search-match-selected');
					matchElement.parent().parent().find('.search-match-not-selected').hide();
					matchElement.parent().parent().find('.fa-minus').removeClass('fa-minus').addClass('fa-plus');
				}
			})
			.fail(function(data) {
				alert('Error');
			});
		});
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
		$('#form_startTime').prop('min', minTime);
	}
	
	if ($('#taplist-content').length > 0) {
		$(".taplist-loading").fadeOut("slow");
		
		pushServerTaplist();
		
		$('#filters-open').click(function() {
			$('#filters-modal').modal('show')
		})
		
		$('#clear-search').click(function() {
			$('#search-beer').val("").trigger("keyup");
		})
		
		$('#taplist-content').on('click', '.tick', function() {
			var beerID = $(this).parents('.taplist-beer').data('id');
			if (!$(this).hasClass('active') || !(tickCheckIn && $(this).parents('.taplist-beer').find('.open-untappd').hasClass('active'))) {
				if (beerID) {
					tickBeer(beerID, !$(this).hasClass('active'));
				}
			}
		})
		
		$('#taplist-content').on('click', '.favorite', function() {
			var beerID = $(this).parents('.taplist-beer').data('id');
			if (beerID) {
				favoriteBeer(beerID, !$(this).hasClass('active'));
			}
		})
		
		$('#taplist-content').on('click', '.admin', function() {
			$('#quick-checkin-modal').modal('show');
			$('.modal-header').hide();
			$('#quick-checkin-modal').find('.modal-body').load( "/ajax/taplistAdminModal/" + $(this).parents('.taplist-beer').data('session-id') + "/" + $(this).parents('.taplist-beer').data('id'));
		});
		
		$('#quick-checkin-modal').on('click', '#flag-out-of-stock', function() {
			var beerID = $(this).parents('.admin-actions').data('beer-id');
			var sessionID = $(this).parents('.admin-actions').data('session-id');
			var currentState = $('.taplist-beer[data-id="'+beerID+'"][data-session-id="'+sessionID+'"]').find('.beer-info').hasClass('no-longer-available');
			if (beerID) {
				$('#flag-out-of-stock').attr('disabled', true);
				setBeerOutOfStock(beerID, sessionID, !currentState, function() {
					currentState = $('.taplist-beer[data-id="'+beerID+'"][data-session-id="'+sessionID+'"]').find('.beer-info').hasClass('no-longer-available');
					if (currentState) {
						$('#flag-out-of-stock').html('<i class="fa fa-check"/> Set as available');
 					} else {
						$('#flag-out-of-stock').html('<i class="fa fa-times"/> Set as out of stock');
					}
				});
				$('#flag-out-of-stock').attr('disabled', false);
			}
		})
		
		$('#quick-checkin-modal').on('click', '#remove-from-taplist', function() {
			var beerID = $(this).parents('.admin-actions').data('beer-id');
			var sessionID = $(this).parents('.admin-actions').data('session-id');
			console.log("here");
			if (beerID && confirm("Please confirm you want to remove this beer. This is no going back.")) {
				removeBeer(beerID, sessionID);
			}
		})
		
		$('#taplist-content').on('click', '.open-untappd', function(e) {
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
						$('.modal-header').show();
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
			if ($(this).attr('type') == "checkbox") {
				var newState = $(this).prop('checked');
			} else {
				if ($(this).val() == "") {
					var newState = false;
				} else {
					var newState = true;
				}
			}
			setFilterStates();
			if ($('.filter-value[data-filter="'+$(this).data('filter')+'"]').length != 0) {
				value = $('.filter-value[data-filter="'+$(this).data('filter')+'"]').val();
			} else {
				value = $(this).val();
			}
			if (newState) {
				taplistFilters[$(this).data('filter')] = value;
			} else {
				delete taplistFilters[$(this).data('filter')];
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
			refreshSessionFilters();
		});
	
		$('.style-filter').change(function() {
			refreshStyleFilters();
		});

		$('.mass-check').click(function() {
			$($(this).data('target')).prop('checked', $(this).data('state'));
			if ($(this).data('target') == ".style-filter") {
				refreshStyleFilters();
			}
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
					addCheckedInBeer(data.response.beer.bid);
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
			refreshTaplistCounts();
		});

		$('#tickCheckedIn').click(function() {
			tickCheckIn = $(this).prop('checked');
			initTicks();
			filterTapList();
			saveData();
		})
		
		// Show only 1 session if one is currently happening
		var epoch = Date.now() / 1000;
		$('.session-filter').each(function() {
			if($(this).data('session-start') < epoch && $(this).data('session-end') > epoch) {
				 currentSession = $(this).data('session');
					console.log(currentSession);
			}
		})
		if (currentSession) {
			$('.session-filter[data-session!="'+currentSession+'"]').prop('checked', false);
			refreshSessionFilters();
		}
		
		initOutOfStock();
		initTaplistSort();
		setFilterStates();
		refreshTaplistCounts();

		$(document).on("submit", '#add-beer-form', function(e) {
			e.preventDefault();
			$('#add-beer-submit').prop('disabled', true);
			$('#add-beer-error').hide();
			$('#add-beer-success').hide();
			$('#add-beer-submit').html('<i class="fa fa-spinner fa-pulse"></i>');
			$.post('/ajax/searchBeer', $('#add-beer-form').serialize(), function(data) {
				$('#add-beer-submit').prop('disabled', false);
				$('#add-beer-submit').html("Add");
				if (data.success) {
					if (data.count == 0) {
						$('#add-beer-error').html('No results found on Untappd for the requested keywords.').show();
					} else if (data.count == 1) {
						$.post('/ajax/addBeerToTaplist', { 'beer-id': Object.keys(data.results)[0], 'session-id': $('#add-beer-session').val() }, function(data2) {
							if (data2.success) {
								$('#add-beer-success').html('Successfully added <strong>' + data.results[Object.keys(data.results)[0]] + '</strong>.').show();
								$('#searchString').val("");
								$('#add-beer-session').val("");
							} else {
								if (data2.error == "DUPLICATE") {
									$('#add-beer-error').html("The beer has not been added because it already exists.").show();
									$('#searchString').val("");
									$('#add-beer-session').val("");
								} else {
									$('#add-beer-error').html("An error occured, please retry later.").show();
								}
							}
						})
						.fail(function(data2) {
							$('#add-beer-error').html('An error occured, please retry later.').show();
						});
					} else {
						$.each(data.results, function(idx, beer) {
							$('#select-beer-field').append('<option value="'+idx+'">'+beer+'</option>');
						});
						$('#add-beer-form').hide();
						$('#select-beer-form').show();
						$('#select-beer-count').html(data.count);
					}
				} else {
					$('#add-beer-error').html('An error occured, please retry later.').show();
				}
			})
			.fail(function(data) {
				$('#add-beer-submit').prop('disabled', false);
				$('#add-beer-error').html("An error occured, please retry later.").show();
			});
		});
		
		$(document).on("submit", '#select-beer-form', function(e) {
			e.preventDefault();
			$('#select-beer-submit').html('<i class="fa fa-spinner fa-pulse"></i>');
			$('#select-beer-submit').prop('disabled', true);
			$.post('/ajax/addBeerToTaplist', { 'beer-id': $('#select-beer-field').val(), 'session-id': $('#add-beer-session').val() }, function(data) {
				$('#select-beer-submit').prop('disabled', false);
				$('#select-beer-submit').html('Submit');
				if (data.success) {
					$('#add-beer-success').html('Successfully added <strong>' + $("#select-beer-field option:selected").text() + '</strong>.').show();
					$('#searchString').val("");
					$('#add-beer-session').val("");
					$('#select-beer-form').hide();
					$('#add-beer-form').show();
				} else {
					if (data.error == "DUPLICATE") {
						$('#add-beer-error').html("The beer has not been added because it already exists.").show();
						$('#searchString').val("");
						$('#add-beer-session').val("");
					} else {
						$('#add-beer-error').html("An error occured, please retry later.").show();
					}
					$('#select-beer-form').hide();
					$('#add-beer-form').show();
				}
				$('#select-beer-field').find('option').not(':first').remove();
			})
			.fail(function(data) {
				$('#select-beer-submit').prop('disabled', false);
				$('#select-beer-submit').html('Submit');
				$('#add-beer-error').html('An error occured, please retry later.').show();
				$('#select-beer-form').hide();
				$('#add-beer-form').show();
			});
		});
			
		$('#select-beer-cancel').click(function() {
			$('#select-beer-field').find('option').not(':first').remove();
			$('#select-beer-form').hide();
			$('#add-beer-form').show();
		});
		
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
			case "favorites":
				$('.taplist-beer').filter(function() { 
					if (val == "show") {
						return !$(this).find(".favorite").hasClass('active')
					} else if (val == "hide") {
						return $(this).find(".favorite").hasClass('active')
					}
				}).addClass("filtered");
				break;
			case "ticked":
				$('.taplist-beer').filter(function() { 
					if (val == "show") {
						return !$(this).find(".tick").hasClass('active')
					} else if (val == "hide") {
						return $(this).find(".tick").hasClass('active')
					}
				}).addClass("filtered");
				break;
			case "checkedIn":
				$('.taplist-beer').filter(function() { 
					if (val == "show") {
						return !$(this).find(".open-untappd").hasClass('active')
					} else if (val == "hide") {
						return $(this).find(".open-untappd").hasClass('active')
					}
				}).addClass("filtered");
				break;
		}
	});
	refreshTaplistCounts();
}

function refreshTaplistCounts() {
	var filteredCount = $('.taplist-beer.filtered').length;
	var showedCount = $('.taplist-beer').length - filteredCount;
	$('#taplist-info-count').html(showedCount);
	$('#taplist-info-filtered').html(filteredCount);
	$('#taplist-info-favorites').html($('.taplist-beer').find('.favorite.active').length);
	$('#taplist-info-ticks').html($('.taplist-beer').find('.tick.active').length);
	$('#taplist-info-checkins').html($('.taplist-beer').find('.open-untappd.active').length);
	if (showedCount == 0) {
		$('#taplist-no-content').show();
	} else {
		$('#taplist-no-content').hide();
	}
}

function refreshStyleFilters() {
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
}

function refreshSessionFilters() {
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
}

function sortTaplist(key, order) {
	key = key || taplistSort.key;
	order = order || taplistSort.order;

	var level2 = null;
	var level3 = null;
	
	var beers = $('#taplist-content'),
	beerDiv = beers.children('.taplist-beer');
	
	if (key == "brewery" || key == "beer") {
		level2 = {key: "session-date", order: 'asc'};
	}
	
	beerDiv.sort(function(a, b) {
        var value1 = formatDataForSorting(a.getAttribute("data-"+key));
        var value2 = formatDataForSorting(b.getAttribute("data-"+key));
        var result = (value1 < value2 ? -1 : (value1 > value2 ? +1 : 0));
        result = result * (order == 'asc' ? +1 : -1);
        if (!result && level2) {
            value1 = formatDataForSorting(a.getAttribute("data-"+level2.key));
            value2 = formatDataForSorting(b.getAttribute("data-"+level2.key));
            result = (value1 < value2 ? -1 : (value1 > value2 ? +1 : 0));
            result = result * (level2.order == 'asc' ? +1 : -1);
        }
        return result;
    });
	
	beerDiv.detach().appendTo(beers);
}

function formatDataForSorting(data) {
	if ($.isNumeric(data)) { 
		return parseFloat(data);
	} else {
		return data.toUpperCase();
	}
}

function initSavedData() {
	if (typeof savedTickCheckIn !== "undefined") {
		tickCheckIn = savedTickCheckIn;
	} else {
		if (localStorage.getItem("tickCheckIn") != null) {
			tickCheckIn = localStorage.getItem("tickCheckIn");
		}
	}
	$('#tickCheckedIn').prop('checked', tickCheckIn);
	
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
	
	initCheckedIn();
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

function initCheckedIn() {
	$('.taplist-beer').find('.open-untappd').removeClass('active');
	$.each(checkedInBeers, function(idx, val) {
		var untappdElement = $('.taplist-beer[data-id="'+val+'"]').find('.open-untappd');
		$(untappdElement).addClass('active');
	});
}

function saveData() {
	localStorage.setItem("favorites", JSON.stringify(favorites));
	localStorage.setItem("ticks", JSON.stringify(ticks));
	localStorage.setItem("untappdButtonAction", untappdButtonAction);
	localStorage.setItem("tickCheckIn", $('#tickCheckedIn').prop('checked'));
	if ($("#logged-in").length > 0) {
		$.post('/ajax/saveTaplistData', { event: $('#event-taplist').data('event-id'), favorites: JSON.stringify(favorites), ticks: JSON.stringify(ticks), buttonAction: untappdButtonAction, tickCheckIn: $('#tickCheckedIn').prop('checked') })
	}
}

function initTicks() {
	$('.taplist-beer').find('.tick').removeClass('active');
	$.each(ticks, function(idx, val) {
		var favoriteElement = $('.taplist-beer[data-id="'+val+'"]').find('.tick');
		$(favoriteElement).addClass('active');
	});
	if (tickCheckIn) {
		$.each(checkedInBeers, function(idx, val) {
			var favoriteElement = $('.taplist-beer[data-id="'+val+'"]').find('.tick');
			$(favoriteElement).addClass('active');
		});
	}
}

function initOutOfStock() {
	$('.taplist-beer').find('.out-of-stock').removeClass('active');
	$('.taplist-beer').find('.beer-info').removeClass('no-longer-available');
	$.each(outOfStock, function(sessionID, session) {
		$.each(session, function(idx, beer) {
			var outOfStockElement = $('.taplist-beer[data-id="'+beer+'"][data-session-id="'+sessionID+'"]').find('.out-of-stock');
			$(outOfStockElement).addClass('active');
			$('.taplist-beer[data-id="'+beer+'"][data-session-id="'+sessionID+'"]').find('.beer-info').addClass('no-longer-available');
		});
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
	refreshTaplistCounts();
	if (taplistFilters['ticked']) {
		filterTapList();
	}
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
	refreshTaplistCounts();
	if (taplistFilters['favorites']) {
		filterTapList();
	}
}

function setBeerOutOfStock(beerID, sessionID, enable, callback) {
	var outOfStockElement = $('.taplist-beer[data-id="'+beerID+'"]').find('.out-of-stock');
	if (enable) {
		$.post('/ajax/setOutOfStock', { beerID: beerID, sessionID: sessionID, action: "ADD" }, function(data) {
			if (data.success) {
				outOfStockElement.addClass('active');
				callback();
			}
		})
	} else {
		$.post('/ajax/setOutOfStock', { beerID: beerID, sessionID: sessionID, action: "REMOVE" }, function(data) {
			if (data.success) {
				outOfStockElement.removeClass('active');
				callback();
			}
		})
	}
}

function removeBeer(beerID, sessionID) {
	var beerElement = $('.taplist-beer[data-id="'+beerID+'"][data-session-id="'+sessionID+'"]');
	$.post('/ajax/removeFromTaplist', { beerID: beerID, sessionID: sessionID }, function(data) {
		if (data.success) {
			beerElement.remove();
			$('#quick-checkin-modal').modal('hide');
		} else {
			alert('An error occured while trying to delete the beer')
		}
	})
}

function addCheckedInBeer(beerID) {
	var tickElement = $('.taplist-beer[data-id="'+beerID+'"]').find('.open-untappd');
	var idx = checkedInBeers.indexOf(beerID);
	if (idx == -1) {
		checkedInBeers.push(beerID);
	}
	tickElement.addClass('active');
	initTicks();
	refreshTaplistCounts();
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
        	console.log('Retrying to connect...')
        	pushServerEventInfo();
        }, 20000)
    }, {
        'skipSubprotocolCheck': true
    });	
}

function pushServerTaplist(){
	var conn = new ab.Session(websocket, function() {
        conn.subscribe("taplist-" + $('#event-taplist').data('event-id') + "-all", function(topic, data) {
        	handleUpToDateTaplistBeer(data);
        });
		if ($("#logged-in").length > 0) {
	        conn.subscribe("taplist-" + $('#event-taplist').data('event-id') + "-" + $("#logged-in").data('uid'), function(topic, data) {
	        	handleTaplistUserData(data);
	        });
		}
    }, function() {
        console.warn('WebSocket connection closed');
        setTimeout(function(){
        	console.log('Retrying to connect...')
        	pushServerTaplist();
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

function handleTaplistUserData(data) {
	if (data.push_type == "checked_in_beers") {
		checkedInBeers = data.list;
		initCheckedIn();
		initTicks();
		filterTapList();
	}
}

function handleUpToDateTaplistBeer(data) {
	if (data.push_type == "out_of_stock") {
		outOfStock = data.list;
		initOutOfStock();
	} else if (data.push_type == "remove") {
		$('.taplist-beer[data-id="'+data.beer+'"][data-session-id="'+data.session+'"]').remove();
	} else if (data.push_type == "add") {
		$('#taplist-content').append(data.html);
		fullyRefreshTaplist();
	} else {
		$.each(data.beers, function(sessionID, session) {
			$.each(session, function(beerID, beerElement) {
				var targetElement = $('.taplist-beer[data-id="'+beerID+'"][data-session-id="'+sessionID+'"]');
				if (targetElement.length !== 0) {
					targetElement.replaceWith(beerElement);
				} else {
					$('#taplist-content').append(beerElement);
				}
			});
		});
		fullyRefreshTaplist();
	}
}

function fullyRefreshTaplist() {
	initFavorites();
	initCheckedIn();
	initTicks();
	filterTapList();
	sortTaplist();
	initOutOfStock();
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
