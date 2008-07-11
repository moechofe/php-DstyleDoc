{if $_description}
<p>
{$_description[0]}
{section name=index loop=$_description start=1}
</p>
<p>
{$_description[$index]}
{/section}
</p>
{/if}{* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 fileformat=unix foldmethod=marker encoding=utf8 setlocal noendofline binary *}
