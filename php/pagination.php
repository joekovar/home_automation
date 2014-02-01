<?php

class pagination
{
	public $links, $previous, $next;

	public function __construct($o)
	{
		/*
			$o {url, on_page, page_count, padding}
		*/
	
		$this->links	= '';
		$this->previous	= ($o->on_page > 1) ? sprintf('<a href="%s"><span>Previous</span></a>', sprintf($o->url, $o->on_page - 1)) : '';
		$this->next		= ($o->on_page < $o->page_count) ? sprintf('<a href="%s"><span>Next</span></a>', sprintf($o->url, $o->on_page + 1)) : '';
		
		if($o->page_count > 1)
		{
			if($o->on_page > ($o->padding * 2))
			{
				for($i = 1; $i <= $o->padding; $i++)
				{
					$this->links .= sprintf('<a href="%1$s"><span>%2$s</span></a>', sprintf($o->url, $i), $i);
				}
			}
			for($i = max(1, $o->on_page - $o->padding); $i < $o->on_page; $i++)
			{
				$this->links .= sprintf('<a href="%1$s"><span>%2$s</span></a>', sprintf($o->url, $i), $i);
			}
			$this->links .= sprintf('<a href="%1$s" class="current"><span>%2$s</span></a>', sprintf($o->url, $o->on_page), $o->on_page);
			for($i = $o->on_page + 1, $toi = min($o->page_count, $o->on_page + $o->padding); $i <= $toi; $i++)
			{
				$this->links .= sprintf('<a href="%1$s"><span>%2$s</span></a>', sprintf($o->url, $i), $i);
			}
			if($o->on_page < ($o->page_count - ($o->padding * 2)))
			{
				for($i = 1 + ($o->page_count - $o->padding); $i <= $o->page_count; $i++)
				{
					$this->links .= sprintf('<a href="%1$s"><span>%2$s</span></a>', sprintf($o->url, $i), $i);
				}
			}
		}
		return $this;
	}
}

?>
