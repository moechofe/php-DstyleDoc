  <div class="footer-content"><div class="frame"><div class="content">
    <ul>
      <li><a href="?"><span>{if isset(#logo#)}{#logo#}{else}#logo#{/if}</span></a></li>
			<li class="version">{$version}<li>
    </ul>
  </div></div></div>
<script type="text/javascript">
{literal}
$(function(){
	$('ul.list li').not('.displayed').map( function(){
		$(this).hover(
			function(){$(this).addClass('hover');},
			function(){$(this).removeClass('hover');});
		$(this).click( function(){
			document.location = $(this).children('a').attr('href');
		});
		$(this).css('cursor', 'pointer');
		$(this).attr('title', {/literal}{if $browse_mode}document.location.protocol+'//'+document.location.hostname+document.location.port+document.location.pathname+{/if}{literal}$(this).children('a').attr('href'));
	});
});
{/literal}
</script>
