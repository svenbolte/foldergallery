// Step 1/3 - Create template for the button
// =========================================
jQuery.fancybox.defaults.btnTpl.print = '<button data-fancybox-print class="fancybox-button fancybox-button--print" title="Print">' +
  '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 129 129"><path d="m10.5,105h22.9v13.5c0,2.3 1.8,4.1 4.1,4.1h54c2.3,0 4.1-1.8 4.1-4.1v-13.5h22.9c2.3,0 4.1-1.8 4.1-4.1v-72.8c0-2.3-1.8-4.1-4.1-4.1h-22.9v-13.5c0-2.3-1.8-4.1-4.1-4.1h-54c-2.3,0-4.1,1.8-4.1,4.1v13.5h-22.9c-2.3,0-4.1,1.8-4.1,4.1v72.8c0,2.2 1.9,4.1 4.1,4.1zm76.9,9.4h-45.8v-33.8h45.8v33.8zm-45.8-99.8h45.8v9.4h-45.8v-9.4zm-27,17.6h22.9 54 22.9v64.6h-18.8v-16.2h7.3c2.3,0 4.1-1.8 4.1-4.1s-1.8-4.1-4.1-4.1h-11.4-54-11.3c-2.3,0-4.1,1.8-4.1,4.1s1.8,4.1 4.1,4.1h7.3v16.2h-18.9v-64.6z"/<path d="m86.2,53.3h10.6c2.3,0 4.1-1.8 4.1-4.1s-1.8-4.1-4.1-4.1h-10.6c-2.3,0-4.1,1.8-4.1,4.1s1.8,4.1 4.1,4.1z"/></svg>' +
  '</button>';

// Step 2/3 - Make button clickable
// ================================
jQuery('body').on('click', '[data-fancybox-print]', function() {
  function printElement(e) {
    var ifr = document.createElement('iframe');
    ifr.style='height:0px; width:0px; position:absolute';
    document.body.appendChild(ifr);
    ifr.contentDocument.body.style='position:relative;left:0;margin-left:-400px';
    jQuery(e).clone().appendTo(ifr.contentDocument.body);
	ifr.contentWindow.print();
    ifr.parentElement.removeChild(ifr);
  }
  var instance = jQuery.fancybox.getInstance();
  if (instance) {
    printElement(instance.current.$content)
  }
});


jQuery(document).ready(function() {
	jQuery("[data-fancybox]").fancybox({
		loop : (FancyBoxGalleryOptions.loop == 1),
		toolbar : (FancyBoxGalleryOptions.toolbar == 1),
		infobar : (FancyBoxGalleryOptions.infobar == 1),
		arrows : (FancyBoxGalleryOptions.arrows == 1),
		slideShow : {
			autoStart : (FancyBoxGalleryOptions.autostart == 1),
        	speed     : FancyBoxGalleryOptions.speed*1000
    	},
    	fullScreen : {
        	autoStart : (FancyBoxGalleryOptions.fullscreen == 1),
   		},
		buttons: [
			"zoom",
			"slideShow",
			"fullScreen",
			"download",
			"thumbs",
			"print", "close"
		]
	});
});