<template>
	<main>
		<section>
			<div class="container">
				<div class="row">
					<div class="col-md-12" >
						<h1>FAQ</h1>
					</div> <!-- col -->
				</div> <!-- row -->
				<div class="row">
					<div class="col-md-12" >
						<div class="panel-group" role="tablist" aria-multiselectable="true">

							<div class="panel panel-default" :class="{ shown : shown['1'] }">
								<div class="panel-heading" role="tab" @click.stop="$set(shown, '1', !!!shown['1'])">
									<div class="expand-or-collapse">
										<font-awesome-icon
											class="icon" :icon="shown['1'] ? 'compress' : 'expand'" />
									</div>

									<h4 class="panel-title">
										Are the surveys anonymous?
									</h4>
								</div>
								<div class="panel-body">
									<p>
										When creating a survey, the following personal data 
										from the creating user
										is stored alongside
										the survey structure: surname, given name, GID, email address;
										this data is only used to allow tech support to help
										users in case they have problems with their survey.
									</p>

									<p>
										No personal data is collected from the
										participants
										when submitting results, so polls
										are completely anonymous.
									</p>

									<p>
										Hence, the tool can not be used for personalized
										surveys, and yes, that also means that cheating/casting multiple votes
										per user is technically possible (although there's a very basic
										client-side protection in place to prohibit accidential multi-vote-casting,
										but that can be circumvented easily by malicious colleagues).
									</p>

									<p>
										This anonymity also means that anyone having the URL/participation code of your survey
										can cast a vote, with no way to limit participation to a defined group of persons. 
										It also applies to viewing results: they're also viewable by anyone having the
										URL/participation code.
									</p>
								</div>
							</div> <!-- panel -->

							<div class="panel panel-default" :class="{ shown : shown['7'] }">
								<div class="panel-heading" role="tab" @click.stop="$set(shown, '7', !!!shown['7'])">
									<div class="expand-or-collapse">
										<font-awesome-icon
											class="icon" :icon="shown['7'] ? 'compress' : 'expand'" />
									</div>
									<h4 class="panel-title">
										Why don't I see results although I know some participants already have cast their vote?
									</h4>
								</div>
								<div class="panel-body">
									<p>
										Participants' anonymity is one of the most important design goals of
										InstaGauge; after consultation with data privacy officers
										and researching established law practice, it was decided
										to only show survey results when at least five votes have been
										cast.
									</p>
								</div>
							</div> <!-- panel -->

							<div class="panel panel-default" :class="{ shown : shown['2'] }">
								<div class="panel-heading" role="tab" @click.stop="$set(shown, '2', !!!shown['2'])">
									<div class="expand-or-collapse">
										<font-awesome-icon
											class="icon" :icon="shown['2'] ? 'compress' : 'expand'" />
									</div>
									<h4 class="panel-title">
										How much money does it cost to conduct a poll?
									</h4>
								</div>
								<div class="panel-body">
									<p>
										None &ndash; but you have to set up 
										the poll on your own. It's just you and the free tool.
									</p>

									<p>
										This means that it's also your responsibility to
										stick to the rules and regulations regarding
										polls/surveys at your location on your own.
									</p>
								</div>
							</div> <!-- panel -->
							
							<div class="panel panel-default" :class="{ shown : shown['3'] }">
								<div class="panel-heading" role="tab" @click.stop="$set(shown, '3', !!!shown['3'])">
									<div class="expand-or-collapse">
										<font-awesome-icon
											class="icon" :icon="shown['3'] ? 'compress' : 'expand'" />
									</div>
									<h4 class="panel-title">
										What's the tool's ACP rating?
									</h4>
								</div>
								<div class="panel-body">
									<p>
										There hasn't been an official
										ACP rating yet, so make sure the content
										you or your participants put into the
										tool is classified as "unrestricted"!
									</p>
								</div>
							</div> <!-- panel -->

							<div class="panel panel-default" :class="{ shown : shown['4'] }">
								<div class="panel-heading" role="tab" @click.stop="$set(shown, '4', !!!shown['4'])">
									<div class="expand-or-collapse">
										<font-awesome-icon
											class="icon" :icon="shown['4'] ? 'compress' : 'expand'" />
									</div>
									<h4 class="panel-title">
										How do I invite participants?
									</h4>
								</div>
								<div class="panel-body">
									<p>
										After you have created your poll, you'll get
										a list of URLs for your survey; one of them,
										the <em>participant link</em>, can be sent
										to your participants on your own outside the tool
										(e.g. via Outlook) or used for hyperlinks
										within e.g. Powerpoint slides.
									</p>
									<p>
										On that survey detail page, you'll also find a
										QR code image you can copy&amp;paste into your
										document to show potential participants,
										and a multi-letter (&approx; 6 characters) code
										that people can enter manually on the website to
										participate.
									</p>
								</div>
							</div> <!-- panel -->

							<div class="panel panel-default" :class="{ shown : shown['5'] }">
								<div class="panel-heading" role="tab" @click.stop="$set(shown, '5', !!!shown['5'])">
									<div class="expand-or-collapse">
										<font-awesome-icon
											class="icon" :icon="shown['5'] ? 'compress' : 'expand'" />
									</div>
									<h4 class="panel-title">
										Can I change my survey after I have created it?
									</h4>
								</div>
								<div class="panel-body">
									<p>
										You can change your survey as long as it is not published. 
										Once published, the only way to change a survey is to clone it (creating a new survey
										with a different code), edit it and then publish it.
										The reason for this 
										is immutability: if some people already have submitted their results,
										changing the questions/answer options afterwards would allow changing the
										meaning of the results people had given.
									</p>

									<p>
										Imagine the question initially had been "Are you OK with that?", people
										already answered "No" &ndash; and then the creator changes the question to
										read "Are you <em>not</em> OK with that?", completely turning the already given
										answers around.
									</p>

									<p>
										We don't want such situations to occur, so we don't allow changing
										surveys once they have been published. So you have to make sure your survey
										is the way you want it beforehand! Otherwise you have to create a new one
										before inviting your participants.
									</p>
								</div>
							</div> <!-- panel -->

							<div class="panel panel-default" :class="{ shown : shown['6'] }">
								<div class="panel-heading" role="tab" @click.stop="$set(shown, '6', !!!shown['6'])">
									<div class="expand-or-collapse">
										<font-awesome-icon
											class="icon" :icon="shown['6'] ? 'compress' : 'expand'" />
									</div>
									<h4 class="panel-title">
										How do I export the results of my survey?
									</h4>
								</div>
								<div class="panel-body">
									<p>
										There's a button on each survey's detail page (down below
										the result display, labeled <em>export results</em>) which provides
										the results as an Excel file.
									</p>
								</div>
							</div> <!-- panel -->

						</div>
					</div>
				</div> <!-- row -->
			</div> <!-- container -->
		</section>
	</main>
</template>

<style lang="scss" scoped>
.panel-group .panel-heading  h4.panel-title::before {
	content:"";
}

.panel {
	.expand-or-collapse {
		float:right;
		line-height:24px;
	}

	.panel-body {
		display:none;
	}

	&.shown {
		.panel-title {
			text-decoration:underline;
		}

		.panel-body {
			display:block;
		}
	}
}
</style>
  
<script>


export default {
	name : "FAQ",
	data : function() {
	     return {
			 shown : {}
	     }
    },
	created : function() {		
		if(this.$route.params.id) {
			this.$set(this.shown, this.$route.params.id, true);
		}
    },	
	methods : {
	},
	mounted : function() {
	},
	watch : {
	},
	components : {
		
	}
}
</script>
