<template v-if="permissions.is_logged_in">
	<template v-if="!permissions.is_owner">
		<div v-if="permissions.is_approved" class="row col m2 s12"><button disabled class="disabled btn btn-info waves-effect waves-light middle-red-purple s12 col"><i class="fa fa-check-square left" aria-hidden="true"></i>Approved</button></div>
		<div v-else class="row col m2 s12"><button :onclick="'approveReview(\''+permissions.review_id+'\');'" :disabled="!permissions.is_followed" class="btn btn-info waves-effect waves-light giants-orange s12 col"><i class="fa fa-thumbs-up left" aria-hidden="true"></i>Approve</button></div>
		<div v-if="permissions.is_followed" class="row col m2 s12"><button :onclick="'unfollowReview(\''+permissions.review_id+'\');'" class="btn btn-info waves-effect waves-light middle-red-purple s12 col"><i class="fa fa-minus-square left" aria-hidden="true"></i>Unfollow</button></div>
		<div v-else class="row col m2 s12"><button :onclick="'followReview(\''+permissions.review_id+'\');'" class="btn btn-info waves-effect waves-light giants-orange s12 col"><i class="fa fa-binoculars left" aria-hidden="true"></i>Follow</button></div>
	</template>
	<template v-else>
		<div v-if="permissions.is_open" class="row col m2 s12"><button class="btn btn-info waves-effect waves-light red s12 col" :onclick="'closeReview(\''+permissions.review_id+'\');'"><i class="fa fa-window-close left" aria-hidden="true"></i>Close</button></div>
		<div v-else class="row col m2 s12"><button class="btn btn-info waves-effect waves-light middle-red-purple s12 col" :onclick="'reopenReview(\''+permissions.review_id+'\');'"><i class="fa fa-window-maximize left" aria-hidden="true"></i>Re-Open</button></div>
		<div class="row col m2 s12"><a :disabled="!permissions.is_open" :href="'/reviews/'+permissions.review_id+'/edit'" class="btn btn-info waves-effect waves-light giants-orange s12 col"><i class="fa fa-pencil-square left" aria-hidden="true"></i>Edit</a></div>
	</template>
</template>
<div class="row col m2 s12"><a :href="permissions.review_url" target="_blank" class="btn btn-info waves-effect waves-light middle-red-purple s12 col"><i class="fa fa-external-link left" aria-hidden="true"></i>View</a></div>
