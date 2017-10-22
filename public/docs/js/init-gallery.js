$(function() {

	// don't init gallery if it consists of a single photo
	if($("div.image").length > 1) {
		// wrap images
		$("p#project-count").replaceWith('<p id="gallery-count" class="col one"><span>1/1</span></p>');
		$('<div id="gallery-navigation" class="col three"><p><a href="#" id="previous-image">&larr;</a>&nbsp;&nbsp;<a href="#" id="next-image">&rarr;</a></p></div>').insertAfter("p#gallery-count");
		$("div.image").wrapAll("<div id='image-wrapper'><div id='image-holder'></div></div>");
		
		// init gallery: Gallery.init(imageHolder, imageWrapperWidth, imageCountHolder, nextButton, prevButton)
		Gallery.init($("div#image-holder"), 560, $("p#gallery-count").children("span")[0], $("a#next-image"), $("a#previous-image"));
		
		$("div.image").show();
	}
});