<div class="file-content"><div class="frame"><div class="content">
  <div class="content-title"><p>{if isset(#file_title#)}{#file_title#|string_format:$_file.title:$_file.link}{else}#file_title(file-title,file-link)#{/if}</p></div>
  <div class="content-display"><h1>{if isset(#file_display#)}{#file_display#|string_format:$_file.display:$_file.link}{else}#file_display(file-name,file-link){/if}</h1></div>
  <div class="content-package">
{if $_file.packages}
		{if isset(#file_package#)}{#file_package#|string_format:$_file.display:$_file.link}{else}#file_package(file-name,file-link)#{/if}
		<ul>
{foreach from=$_file.packages item=package}
			<li>{if is_object($package)}$package.link{else}{$package}{/if}</li>
{/foreach}
		</ul>
{/if}
	</div>
  <div class="content-description">{$_file.description}</div>
  <div class="content-index">{classes_index file=$_file}</div>
  <div class="content-index">{functions_index file=$_file}</div>
	<div class="content-licence">{$_file.licence}</div>
</div></div></div>
