{if $_classes}
  <h3 class="list-header">{if isset(#classes_index_list_header#)}{#classes_index_list_header#|string_format:$_file.name:$_file.link}{else}#classes_index_list_header(file-name,file-link)#{/if}</h3>
  <ul class="list">
{foreach from=$_classes item=_class}
    <li class="class{if $this == $_class} displayed{/if}">
      {$_class.link}
      {$_class.title}
    </li>
{/foreach}
  </ul>
{/if}
