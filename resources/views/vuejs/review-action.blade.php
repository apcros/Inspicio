<template v-if="permissions.is_logged_in">
	<template v-if="!permissions.is_owner">
		<button v-if="permissions.is_approved" disabled class="disabled btn btn-info waves-effect waves-light middle-red-purple"><i class="fa fa-check-square left" aria-hidden="true"></i>Approved</button>
		<button v-else :onclick="'approveReview(\''+permissions.review_id+'\');'" :disabled="!permissions.is_followed" class="btn btn-info waves-effect waves-light giants-orange"><i class="fa fa-thumbs-up left" aria-hidden="true"></i>Approve</button>
		<button :onclick="'unfollowReview(\''+permissions.review_id+'\');'" v-if="permissions.is_followed" class="btn btn-info waves-effect waves-light middle-red-purple"><i class="fa fa-minus-square left" aria-hidden="true"></i>Unfollow</button>
		<button :onclick="'followReview(\''+permissions.review_id+'\');'" v-else class="btn btn-info waves-effect waves-light giants-orange"><i class="fa fa-binoculars left" aria-hidden="true"></i>Follow</button>
	</template>
	<template v-else>
		<button v-if="permissions.is_open" class="btn btn-info waves-effect waves-light red" :onclick="'closeReview(\''+permissions.review_id+'\');'"><i class="fa fa-window-close left" aria-hidden="true"></i>Close</button>
		<button v-else class="btn btn-info waves-effect waves-light middle-red-purple" :onclick="'reopenReview(\''+permissions.review_id+'\');'"><i class="fa fa-window-maximize left" aria-hidden="true"></i>Re-Open</button>
		<a :disabled="!permissions.is_open" :href="'/reviews/'+permissions.review_id+'/edit'" class="btn btn-info waves-effect waves-light giants-orange"><i class="fa fa-pencil-square left" aria-hidden="true"></i>Edit</a>
	</template>
</template>
<a :href="permissions.review_url" target="_blank" class="btn btn-info waves-effect waves-light middle-red-purple"><i class="fa fa-external-link left" aria-hidden="true"></i>View</a>