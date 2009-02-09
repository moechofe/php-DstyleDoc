<dt class="return-type">
{if is_object($_return.type)}{$_return.type.link}{else}{$_return.type}{/if}
</dt>
{if $_return.from}
<dd class="return-from">
{if isset(#return_from#)}{#return_from#|string_format:$_return.from.display:$_return.from.link}{else}#return_from(from.name,from.link)#{/if}
</dd>
{/if}
<dd class="return-description">
{$_return.description}
</dd>
