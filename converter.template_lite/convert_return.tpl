<div class="return-type">
{if is_object($_return.type)}{$_return.type.link}{else}{$_return.type}{/if}
</div>
{if $_return.from}
<div class="return-from">
{if isset(#return_from#)}{#return_from#|string_format:$_return.from.display:$_return.from.link}{else}#return_from(from.name,from.link)#{/if}
</div>
{/if}
<div class="return-description">
{$_return.description}
</div>
