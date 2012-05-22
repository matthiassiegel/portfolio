<?php

// Take an array of elements and return the ones for the current page
function portfolioPaginationItems($items, $page) {
	
	// How many items should be displayed per page
	$per_page = 15;
	
	return array_slice($items, ($page - 1) * $per_page, $per_page);
}


// Prepare the pagination HTML
function portfolioPaginationHTML($items, $page, $base_url) {
	
	// How many items should be displayed per page
	$per_page = 15;

	$count = count($items);
	$pages = ceil($count / $per_page);
	$first = ($page == 1) ? 1 : (($page - 1) * $per_page) + 1;
	$last = ($page == $pages) ? ($count) : ($first + $per_page - 1);

	
	// Only return HTML if more than one page exists and requested page exists
	if ($count > $per_page && $page <= $pages) :
		
		$result = '<span class="displaying-num">Displaying ' . $first . '-<span class="pagination-pagetotal">' . $last . '</span> of <span class="pagination-total">' . $count . '</span></span>';
		
		
		// Prev button
		if ($page > 1) :
			$result .= '<a href="' . $base_url . 'p=' . ($page - 1) . '" class="prev page-numbers">&laquo;</a>';
		endif;
		
		
		// Always display first page
		if ($page == 1) :
			$result .= '<span class="page-numbers current">1</span>';
		else :
			$result .= '<a href="' . $base_url . 'p=1" class="page-numbers">1</a>';
		endif;


		// If there are more than two pages between the first page and the current one, display dots
		if (($page - 3) > 1) :
			$result .= '<span class="page-numbers dots">...</span>';
		endif;
		
		
		for ($i = $page - 2; $i < $page + 3; $i++) :
			if ($i > 1 && $i < $pages) :
				if ($i == $page) :
					$result .= '<span class="page-numbers current">' . $i . '</span>';
				else :
					$result .= '<a href="' . $base_url . 'p=' . $i . '" class="page-numbers">' . $i . '</a>';
				endif;
			endif;
		endfor;
		
		
		// If there are more than two pagea between the current one and the last one, display dots
		if (($pages - $page) > 3) :
			$result .= '<span class="page-numbers dots">...</span>';
		endif;
		
		
		// Always display last page
		if ($page == $pages) :
			$result .= '<span class="page-numbers current">' . $pages . '</span>';
		else :
			$result .= '<a href="' . $base_url . 'p=' . $pages . '" class="page-numbers">' . $pages . '</a>';
		endif;


		// Next button
		if ($page < $pages) :
			$result .= '<a href="' . $base_url . 'p=' . ($page + 1) . '" class="prev page-numbers">&raquo;</a>';
		endif;
	
	
		return '<div class="tablenav-pages">' . $result . '</div>';
	else :
		return false;
	endif;
}

?>