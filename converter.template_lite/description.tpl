{if $_description}
<p>
{$_description[0]}
{section name=index loop=$_description start=1}
</p>
<p>
{$_description[index]}
{/section}
</p>
{/if}
