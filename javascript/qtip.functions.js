<script type="text/javascript" >
$(document).ready(function()
		{
			// Match all <A/> links with a title tag and use it as the content (default).
			$('span[title]').qtip
			({
				position: { at: 'bottom center', my: 'top center' },
				style: { classes: 'ui-tooltip-rounded ui-tooltip-green' }
			});
		});
</script>