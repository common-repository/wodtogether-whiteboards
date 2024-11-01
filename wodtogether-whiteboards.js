jQuery(document).ready(function() {
	// <![CDATA[
	(function(d, s, id) {
	  var js;
	  if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;		

		var now = new Date();
		var startOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate());
		var timestamp = startOfDay / 1000;

	  js.src = "https://wodtogether.com/js/widgets/widget-loader.js?ts="+timestamp;
	  d.body.appendChild(js);
	}(document, 'script', 'wodtogether-jssdk'));
	// ]]>
});