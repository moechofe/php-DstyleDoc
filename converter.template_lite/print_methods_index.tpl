{if $_methods}
  <h3 class="list-header">{if isset(#methods_index_list_header#)}{#methods_index_list_header#|string_format:$_class.display:$_class.link}{else}#methods_index_list_header(file-name,file-link)#{/if}</h3>
  <ul class="list">
{foreach from=$_methods item=_method}
    <li class="method">
      {$_method.link}
      {$_method.title}
    </li>
{/foreach}
  </ul>
{/if}
