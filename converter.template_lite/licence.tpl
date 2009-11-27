{if $_licence}
<dfn>
{$_licence[0]}
{section name=index loop=$_licence start=1}
<br/>
{$_licence[index]}
{/section}
</dfn>
{/if}
