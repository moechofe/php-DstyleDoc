{if $_packages}
  <h3 class="list-header">{if isset(#packages_index_list_header#)}{#packages_index_list_header#}{else}#functions_index_list_header#{/if}</h3>
  <ul class="list">
{foreach from=$_packages item=_package}
    <li class="package">
      {$_package.link}
      {$_package.title}
    </li>
{/foreach}
  </ul>
{/if}
