<div class="card">
    <div class="card-content">
    	<span class="card-title">{{$review->name}}</span>
    	<div class="row">
    	@if ($review->status == 'closed')
    		<p class="red-text"><i class="fa fa-lock left" aria-hidden="true"></i><b>Closed</b></p>
    	@endif
    	@if ($review->is_approved)
    		<p class="green-text"><i class="fa fa-check left" aria-hidden="true"></i>Approved</p>
    	@else
    		<p class="giants-orange-text"><i class="fa fa-clock-o left" aria-hidden="true"></i>Pending approval</p>
    	@endif
    </div>
           <div class="row">
              <div class="col s6 m4">
                  <a class="giants-orange-text" href="/members/{{$review->author_id}}/profile"><i class="fa fa-user left" aria-hidden="true"></i>{{$review->author_name}}</a>
              </div>
              <div class="col s6 m4">
                  <i class="fa fa-code left" aria-hidden="true"></i>{{$review->language}}
              </div>
              <div class="col s6 m4">
                  <i class="fa fa-calendar left" aria-hidden="true"></i>{{$review->created_at}}
              </div>
           </div>
           <p>Your status with this review was last updated <b>{{$review->tracking_time}}</b></p>
    </div>
    <div class="card-action">
            <a href="/reviews/{{$review->id}}/view" class="action-btn-orange btn btn-flat waves-effect waves-light"><i class="fa fa-info-circle left" aria-hidden="true"></i>More info</a>
   </div>
</div>