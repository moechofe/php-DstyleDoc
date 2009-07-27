<div class="class-content{if $_class.abstract} class-abstract-content{elseif $_class.final} class-final-content{/if}"><div class="frame"><div class="content">
  <div class="content-title"><p>{$_class.title}</p></div>
  <div class="content-display"><h1>{if $_class.abstract}{if isset(#class_abstract_header_display#)}{#class_abstract_header_display#|string_format:$_class.display}{else}#class_abstract_header_display(class-name)#{/if}{elseif $_class.final}{if isset(#class_final_header_display#)}{#class_final_header_display#|string_format:$_class.display}{else}#class_final_header_display(class-name)#{/if}{else}{if isset(#class_header_display#)}{#class_header_display#|string_format:$_class.display}{else}#class_header_display(class-name)#{/if}{/if}</h1></div>
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
