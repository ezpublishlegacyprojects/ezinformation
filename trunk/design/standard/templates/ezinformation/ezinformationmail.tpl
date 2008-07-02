{let $sitename=ezini( 'SiteSettings','SiteURL' )}
{set-block scope=root variable=subject}Information data "{$object.name}" was published [{$sitename}]{/set-block}

This e-mail is to inform you that an new item has been published at {$sitename}.
The item can be viewed by using the URL below.

{$object.name}
http://{$hostname}{concat('content/view/full/',$object.main_node_id)|ezurl(no)}

-- 
{$sitename} notification system
{/let}