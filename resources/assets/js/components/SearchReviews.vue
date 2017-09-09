<li v-for="review in reviews.data" class="list-group-item">
	<a href="/reviews/'{{review.id}}'"/view" class="btn btn-info pull-right" target="_blank">View</a>
	<h4 class="list-group-item-heading">{{review.name}}</h4>
	<p><span class="label label-primary">{{review.language}}</span></p>
	<p><span class="label label-default"> Author : {{review.author}}</span></p>
</li>
<ul class="pagination">
	<li v-if="reviews.prev_page_url != null">
		<a href="search({{reviews.current_page-1)}}">Previous</a>
	</li>
	<li v-else class="disabled">
		<a href='#' aria-label='Next'>Previous</a>
	</li>
	<li class='active'><a href='#'>{{reviews.current_page}}</a></li>
	<li v-if="reviews.next_page_url != null">
		<a onclick='search("{{reviews.current_page+1}}");' >Next</a>
	</li>
	<li v-else class="disabled">
		<a href='#' aria-label='Next'>Next</a>
	</li>
</ul>