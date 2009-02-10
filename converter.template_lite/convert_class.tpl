<div class="class-content"><div class="frame"><div class="content">
  <div class="content-title"><p>{$_class.title}</p></div>
  <div class="content-display"><h1>{if isset(#class_header_display#)}{#class_header_display#|string_format:$_class.display}{else}#class_header_display(class-name)#{/if}</h1></div>
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
