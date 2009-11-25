<div class="class-content{if $_class.abstract} class-abstract-content{elseif $_class.final} class-final-content{/if}"><div class="frame"><div class="content">
  <div class="content-title"><p>{if isset(#class_title#)}{#class_title#|string_format:$_class.title:$_class.link}{else}#class_title(class-title,class-link)#"{/if}</p></div>
  <div class="content-display"><h1>{if $_class.abstract}{if isset(#class_abstract_display#)}{#class_abstract_display#|string_format:$_class.display:$_class.link}{else}#class_abstract_display(class-name,class-link)#{/if}{elseif $_class.final}{if isset(#class_final_display#)}{#class_final_display#|string_format:$_class.display:$_class.link}{else}#class_final_display(class-name,class-link)#{/if}{else}{if isset(#class_display#)}{#class_display#|string_format:$_class.display:$_class.link}{else}#class_display(class-name,class-link)#{/if}{/if}</h1></div>
  <div class="content-package">
{if $_class.packages}
		{if isset(#class_package#)}{#class_package#|string_format:$_class.display:$_class.link}{else}#class_package(class-name,class-link)#{/if}
		<ul>
{foreach from=$_class.packages item=package}
			<li>{if is_object($package)}$package.link{else}{$package}{/if}</li>
{/foreach}
		</ul>
{/if}
	</div>
  <div class="content-description">{$_class.description}</div>
  <div class="content-index">{methods_index class=$_class}</div>
{if $_class.todos}
  <div class="content-todos">
    <h2>{if isset(#todos#)}{#todos#|string_format:$_class.display:$_class.link}{else}#todos(class.name,class.link)#{/if}</h2>
{if $_class.todos}
    <ul>
{foreach item=todo from=$_class.todos}
      <li>{$todo}</li>
{/foreach}
    </ul>
{/if}
  </div>
{/if}
</div></div></div>
