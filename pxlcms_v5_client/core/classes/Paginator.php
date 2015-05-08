<?php
	class Paginator {
		protected $item_count        = 5;
		protected $CMS_Query         = null;
		protected $page              = 1;
		protected $use_previous_next = true;
		protected $use_first_last    = true;
		protected $link_format       = '';
		protected $jump_size         = 4;
		protected $fixed_link_count  = false;
		protected $link_contents     = array('&lt;&lt;', '&lt;', '&gt;', '&gt;&gt;');
		
		public function __construct(&$CMS_Query, $item_count, $link_format, $page = null) {
			$this->link_format = $link_format;
			$this->CMS_Query   = $CMS_Query;
			$this->item_count  = $item_count;
			
			// if no page was given, try $_GET['page'] by default
			$page = (int) $page;
			if (!$page) {
				if (isset($_GET['page'])) {
					$this->page = max(1, (int) $_GET['page']);
				}
			} else {
				$this->page = max(1, $page);
			}
			
			$this->CMS_Query->find_total_count = true;
			
			if (!is_null($this->CMS_Query->limit) || !is_null($this->CMS_Query->limit)) {
				trigger_error('Don\'t configure $CMS_Query->limit or $CMS_Query->start manually when using the Paginator object');
				exit;
			}
			
			if ($this->CMS_Query->processed()) {
				trigger_error('Set up Paginator object before calling $CMS_Query->entries()');
				exit;
			}
			
			$this->CMS_Query->limit = $this->item_count;
			$this->CMS_Query->start = ($this->page - 1) * $this->item_count;
		}
		
		public function set_size($s) {
			$this->jump_size = $s;
		}
		
		public function link_contents($first, $previous, $next, $last) {
			$this->link_contents = array($first, $previous, $next, $last);
		}
		
		public function fixed_link_count($s) {
			$this->fixed_link_count = $s;
		}
		
		public function use_first_last($b) {
			$this->use_first_last = $b;
		}
		
		public function use_previous_next($b) {
			$this->use_previous_next = $b;
		}
		
		public function pagination() {
			if ($this->CMS_Query->limit != $this->item_count || $this->CMS_Query->start != ($this->page - 1) * $this->item_count) {
				trigger_error('Don\'t configure $CMS_Query->limit or $CMS_Query->start manually when using the Paginator object');
				exit;
			}
			if (is_null($this->CMS_Query->total_count)) {
				trigger_error('Can\'t generate pagination when no $CMS_Query->total_count is available, probably caused by calling Paginator->pagination() before CMS_Query->entries()');
				exit;
			}
			
			$pages = ceil($this->CMS_Query->total_count / $this->item_count);
			if ($pages > 1) {
				$last_links = array();
				$extralinks = 0;
				if ($this->page > 1) {
					if ($this->page > 2 && $this->use_first_last) {
						$extralinks++;
						echo $this->link(1, $this->link_contents[0], array('inactive', 'first'));
					}
					if ($this->use_previous_next) {
						$extralinks++;
						echo $this->link($this->page - 1, $this->link_contents[1], array('inactive', 'previous'));
					}
				}
				
				if ($this->page < $pages) {
					if ($this->use_previous_next) {
						$extralinks++;
						$last_links[] = $this->link($this->page + 1, $this->link_contents[2], array('inactive', 'next'));
					}
					if ($this->page < $pages - 1 && $this->use_first_last) {
						$extralinks++;
						$last_links[] = $this->link($pages, $this->link_contents[3], array('inactive', 'last'));
					}
				}
				
				$min = max(1, $this->page - $this->jump_size);
				$max = min($pages, $this->page + $this->jump_size);
				
				if ($this->fixed_link_count) {
					$allowed = $this->fixed_link_count - $extralinks;
					$min = max(1, $this->page - floor($allowed / 2) + 1);
					$max = min($pages, $this->page + $allowed - ($this->page - $min + 1));
					$now = $max - $min + $extralinks + 1;
					$min = max(1, $min - ($this->fixed_link_count - $now));
				}
				
				for ($i = $min; $i <= $max; $i++) {
					$classes = array();
					$classes[] = ($this->page == $i ? 'active' : 'inactive');
					$classes[] = 'page';
					echo $this->link($i, $i, $classes);
				}
				
				echo implode("", $last_links);
			}
		}
		
		protected function link($page, $desc, $classes = array()) {
			return "<a class='".(implode(" ", $classes))."' href='".str_replace("%d", $page, $this->link_format)."'>".$desc."</a>";
		}
	}
