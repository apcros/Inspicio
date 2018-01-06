<div v-for="review in reviews.data" class="card">
    <div class="card-content">
        <span class="card-title">@{{review.name}}</span>
        <div class="row">
            <div class="col s6 m3">
                <i class="fa fa-user left" aria-hidden="true"></i>@{{review.author}}
            </div>
            <div class="col s6 m3">
                <i class="fa fa-code left" aria-hidden="true"></i>@{{review.language}}
            </div>
            <div class="col s12 m3">
                <i class="fa fa-calendar left" aria-hidden="true"></i>@{{review.created_at}}
            </div>
            <div class="col s12 m3">
                <i class="fa fa-users left" aria-hidden="true"></i>@{{review.followers}} follower(s)
            </div>
        </div>
    </div>
    <div class="card-action">
        <a :href="'/reviews/'+review.id+'/view'" class="action-btn-orange btn btn-flat waves-effect waves-light"><i class="fa fa-info-circle left" aria-hidden="true"></i>See more</a>
    </div>
</div>
<ul class="pagination">
	<li v-if="reviews.prev_page_url != null">
		<button class="waves-effect btn-flat" id='previous-a' aria-label='Previous'><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
	</li>
	<li v-else class="disabled">
		<button class="waves-effect btn-flat disabled" disabled id='previous-a' aria-label='Previous'><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
	</li>
	<li class='active'><a href='#'>@{{reviews.current_page}}</a></li>
	<li v-if="reviews.next_page_url != null">
		<button class="waves-effect btn-flat" id='next-a' aria-label='Next'><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
	</li>
	<li v-else class="disabled">
		<button class="waves-effect btn-flat disabled" disabled id='next-a' aria-label='Next'><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
	</li>
</ul>