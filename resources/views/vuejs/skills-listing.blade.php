<tr v-for="skill in skills">
	<td>
		@{{skill.name}}
		<span v-if="skill.is_verified" class="badge">Verified</span>
	</td>
	<td>
		<div v-if="skill.level == 1">
			Beginner/Junior
		</div>
		<div v-else-if="skill.level == 2">
			Intermediate
		</div>
		<div v-else>
			Advanced/Senior
		</div>
	</td>
	<td>
		<button :onclick="'deleteSkill('+skill.id+')'" class="btn red waves-effect right"><i class="fa fa-trash" aria-hidden="true"></i></button>
	</td>
</tr>