{if isset($_file)}
<ul class="ascent">
  <li class="file">
    {$_file.link}
    {$_file.title}
  </li>
{/if}
{if isset($_class)}
  <li class="class">
    {$_class.link}
    {$_class.title}
  </li>
{/if}
{if isset($_method)}
  <li class="method{if $this === $_method} displayed{/if}">
    {$_method.link}
    {$_method.title}
  </li>
</ul>
{/if}
