<template>
	<main v-if="survey">
		<section v-if="!submitted">
			<div class="container">
				<div class="row">
					<div class="col-md-12" v-if="survey.votingDisabled">
						<p>
							Voting has been disabled for this survey.
						</p>
					</div>

					<div class="col-md-12" v-if="!survey.votingDisabled && (survey.status == 'draft')">
						<p>
							Survey has not been published yet.
						</p>
					</div>

					<div class="col-md-12" v-if="!survey.votingDisabled && (survey.status != 'draft')">
						<div v-for="(q, qidx) in survey.questions" :key="qidx" class="question-container">
							<q-scale
								v-if="q.type == 'scale'"
								:on-result-changed="onResultChanged(q)"
								:question-data="q"></q-scale>

							<q-multiple-choice
								v-if="q.type == 'multipleChoice'"
								:on-result-changed="onResultChanged(q)"
								:question-data="q"></q-multiple-choice>

							<q-free-text
								v-if="q.type == 'freeText'"
								:on-result-changed="onResultChanged(q)"
								:question-data="q" />

							<q-heading
								v-if="q.type == 'heading'"
								:on-result-changed="onResultChanged(q)"
								:question-data="q" />

							<q-word-cloud
								v-if="q.type == 'wordcloud'"
								:on-result-changed="onResultChanged(q)"
								:question-data="q" />

							<q-matrix
								v-if="q.type == 'matrix'"
								:on-result-changed="onResultChanged(q)"
								:question-data="q" />

							<q-order
								v-if="q.type == 'order'"
								:on-result-changed="onResultChanged(q)"
								:question-data="q" />
						</div>

						<button
							v-if="!survey.votingDisabled"
							:disabled="!allOK || (survey.votingDisabled) || submitting" 
							@click.stop="submitVote"
							class="btn btn-primary btn-block">
							<span v-if="!submitting">submit vote!</span>
							<span v-if="submitting">
								<font-awesome-icon icon="spinner" spin />
								submitting&hellip;
							</span>
						</button>
					</div> <!-- col -->
				</div> <!-- row -->
			</div> <!-- container -->
		</section>

		<section v-if="submitted">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<p>Thank you, your vote has been recorded.</p>

						<p v-if="reloadTimerSeconds > 0">
							The survey will restart automatically in {{ reloadTimerSeconds }} seconds.
						</p>

						<button
							v-if="survey.allowResultsAfterVote"
							@click.stop="showResults"
							class="btn btn-primary btn-block">
							<font-awesome-icon icon="chart-bar"/>
							show results...
						</button>

						<!--
						<button type="button" @click.stop="resetSubmission">reset</button>
						-->
					</div>
				</div>
			</div>
		</section>
	</main>
</template>

<style lang="scss" scoped>
</style>
  
<script>
import $ from 'jquery'


import qScale from './questionTypes/Scale.vue'
import qMultipleChoice from './questionTypes/MultipleChoice.vue'
import qFreeText from './questionTypes/FreeText.vue'
import qHeading from './questionTypes/Heading.vue'
import qWordCloud from './questionTypes/WordCloud.vue'
import qMatrix from './questionTypes/Matrix.vue'
import qOrder from './questionTypes/Order.vue'

export default {
	name : "Embed",
	data : function() {
	     return {
			 survey : null,
			 reloadTimerSeconds : 0,
			 localSubmission : false,
			 votes : { },
			 submitting : false,
	     }
	},
	created : function() {
		var me = this;
		
        me.$http.get("surveys/" + me.$route.params.id + "/details", { headers : { "X-SURVEY-PW" : me.$route.params.pw }}).then((d) => {
			d = d.data;
            if(d.ok) {
				me.survey = d.survey;

				if(me.survey == null) {
					me.$router.push({ name : "ErrorSurveyNotFound" });
				}
				
				if(me.submitted) {
					me.localSubmission = true;
				}
			}
        });
	},	
	computed : {
		allOK : function() {
			var retval = true;
			var me = this;

			for(var i in me.votes) {
				retval &= me.votes[i].ok;
			}

			return retval;
		},
		submitted : function() {
			if(!this.survey) return false;
			if(this.survey.allowMultiVote) {
				return this.localSubmission;
			} else {
				return this.localSubmission || (localStorage.getItem(this.survey._key) === "true");
			}
		}
	},
	methods : {
		resetSubmission : function() {
			localStorage.removeItem(this.survey._key);
		},
		onResultChanged : function(q) {
			var me = this;

			return function(p) {
				me.$set(me.votes, q.id, p);
			}
		},
		submitVote : function() {
			var me = this;

			me.submitting = true;

			me.$http.post("votes/cast", {
				surveyId : me.survey._key,
				votes : me.votes
			}).then((d) => {
				d = d.data;
				if(d.ok) {
					if(d.item) {
						me.localSubmission = true;
					} else {
						if(d.msg) {
							alert(d.msg);
						}
					}
					me.submitting = false;
				} 
			}, (d) => {
				me.submitting = false;
			});
		},
		showResults : function() {
			if(this.$route.meta.isEmbedded) {
				this.$router.push({ name : 'EmbedResults', params : { id : this.survey._key } });
			} else {
				this.$router.push({ name : 'Results', params : { id : this.survey._key } });
			}
		}
	},
	mounted : function() {
	},
	watch : {
		reloadTimerSeconds : function(newV, oldV) {
			var me = this;
			
			if((oldV > 0) && (newV == 0)) {
				// timer abgelaufen
				window.location.reload();
				return;
			}

			if((newV > 0) && (oldV == 0)) {
				// neuer timer-wert gesetzt => start
				window.setInterval(() => { me.reloadTimerSeconds--; }, 1000);
				return;
			}
		},
		submitted : function(newV) {
			if((newV === true) && (this.survey.reloadAutomatically === true)) {
				this.reloadTimerSeconds = 5;
			}
		},
		localSubmission : function(newV) {
			if(newV === true) {
				localStorage.setItem(this.survey._key, "true");
				if(this.survey.immediateResultsAfterVote) {
					this.showResults();
				}
			}
		}
	},
	components : {
		qScale,
		qMultipleChoice,
		qFreeText,
		qHeading,
		qWordCloud,
		qMatrix,
		qOrder,
		
	}
}
</script>
