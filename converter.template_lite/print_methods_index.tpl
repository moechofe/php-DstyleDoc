{if $_methods}
<div class="methods-index">
  <h3 class="list-header">{#methods_index_list_header#|string_format:$_class.display}</h3>
  <ul class="methods-list">
{foreach from=$_methods item=_method}
    <li class="file">
      {$_method.link}
      {$_method.title}
    </li>
{/foreach}
  </ul>
</div>
{/if}
