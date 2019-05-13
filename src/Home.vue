<template>
	<main>
		<section>
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<h1>Home</h1>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<p>
							InstaGauge is a free,
							online, customizable survey tool
							for use within Siemens,
							to help you quickly gather feedback from your audience.
							Available directly from your smartphone, computer or tablet without any installation,
							it’s perfect for conducting simple polls during meetings, workshops, and presentations.
						</p>

						<p>
							The tool is suitable for only ‘<em>unrestricted</em>’ data. Voting is anonymous,
							and results are <em>viewable by anyone</em> with the URL.
						</p>
					</div> <!-- col -->
				</div> <!-- row -->

				<div class="row">

					<div class="col-md-6">
						<h2>Create</h2>

						<p>
							Simply <router-link :to="{ name : 'Create' }">create</router-link>
							your own little survey:
						</p>

						<div class="form-group" style="visibility:hidden;">
							<label class="selected">Survey title (optional)</label>
							<input type="text"
								class="form-control"
								placeholder=""
								v-model="surveyTitle">
						</div>

						<router-link 
							class="btn btn-primary btn-block"
							:to="{ name : 'Create' }">
							<font-awesome-icon icon="plus"/>
							create&hellip;
						</router-link>
					</div> <!-- col -->

					<div class="col-md-6">
						<h2>Participate</h2>

						<p>Enter your survey code to participate:</p>

						<div class="form-group">
							<label class="selected">Code</label>
							<input type="text"
								@keyup.enter.stop="participate"
								class="form-control"
								placeholder=""
								v-model="surveyKey">
						</div>
						<button
							@click.stop="participate"
							:disabled="surveyKey.length == 0"
							class="btn btn-primary btn-block">
							<i class="fas fa-sign-in-alt"></i>
							<font-awesome-icon icon="users"/>
							participate&hellip;
						</button>
					</div> <!-- col -->
				</div> <!-- row -->

			</div>
		</section>
	</main>

</template>

<style lang="scss">
</style>
  
<script>
import $ from 'jquery'

export default {
	name : "Home",
	data : function() {
	     return {
			 surveyKey : "",
			 surveyTitle : ""
	     }
	},
	created : function() {
	},	
	methods : {
		participate : function() {
			var key = this.surveyKey.replace(/\s/g, "");
			this.$router.push({ name : "Survey", params : { id : key }});
		}
	},
	mounted : function() {
	},
	watch : {
	},
	components : {
	}
}
</script>
