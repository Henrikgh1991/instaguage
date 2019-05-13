<template>
	<main>
		<section class="contact">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<h1>Contact</h1>
					</div>
				</div>

				<div class="row">
                    <div class="col-md-3">
                        <img 
                            class="img-responsive"
                            alt="InstaGauge logo"
                            src="../favicon.png">                                
                    </div>

                    <div class="col-md-3">
                        <img
                            class="img-responsive"
                            src="./assets/img/idea-logo.png" alt="#iDea. logo">
                    </div>
                </div>
                    
				<div class="row">
					<div class="col-md-12">
                        <p>
                            InstaGauge is a product from
                            <a target="_blank"
                                href="https://idea.siemens.com">
                                <em>#iDea. The Innovation Company</em>
                            </a> which can be contacted via email
                            addressed to 
                            <a href="mailto:idea-company.scm@siemens.com">idea-company.scm@siemens.com</a>
                            or via <a target="_blank" href="https://siemens.socialcast.com/groups/16965">SSN</a>.
                        </p>

						<p>
							If you have a question, 
							e.g. <router-link :to="{ name : 'FAQ', params : { id : 7 } }"><em>Why can't I see results for my survey?</em></router-link>,
							please <router-link :to="{ name : 'FAQ' }">check the FAQ</router-link> beforehand whether your question is already answered there.
						</p>

					</div> <!-- col -->
				</div> <!-- row -->

			</div>
		</section>
	</main>

</template>

<style lang="scss">
section.contact {
    .row {
        margin-bottom:1em;
    }
}
</style>
  
<script>
import $ from 'jquery'


export default {
	name : "Contact",
	data : function() {
	     return {
			 surveyKey : ""
	     }
	},
	created : function() {
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
