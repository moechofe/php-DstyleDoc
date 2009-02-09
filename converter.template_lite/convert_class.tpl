<div class="class-content"><div class="frame"><div class="content">
  <div class="content-title"><p>{$_class.title}</p></div>
  <div class="content-display"><h1>{if isset(#class_header_display#)}{#class_header_display#|string_format:$_class.display}{else}#class_header_display(class-name)#{/if}</h1></div>
  <div class="content-description">{$_class.description}</div>
  <div class="content-index">{methods_index class=$_class}</div>
  <div class="content-todo">{$_class.todos}</div>
</div></div></div>
