<template>
	<main>
		<section class="whats-new">
			<div class="container">
				<div class="row">
					<div class="col-md-12" >
						<h1>What's new?</h1>
					</div> <!-- col -->
				</div> <!-- row -->
				<div class="row" v-if="items">
					<div class="col-md-12" >
						<div class="panel panel-default news" v-for="i in items" :key="i._key">
							<h5 class="panel-heading">
								{{ moment(i.createdAt).format("LL") }}
							</h5>
							<div class="panel-body" v-html="marked(i.content)"></div>
						</div>
					</div>
				</div> <!-- row -->
			</div> <!-- container -->
		</section>
	</main>
</template>

<style lang="scss">
section.whats-new {
	.news {
		h1 {
			font-weight:bold;
			font-size:20px;
			line-height:24px;
			margin-bottom:0.5em;
			margin-top:0.5em;
		}
	}
}
</style>
  
<script>
import { mapGetters, mapActions } from 'vuex';
import marked from 'marked';
import moment from 'moment';

marked.setOptions({
	sanitize : true
});

export default {
	name : "WhatsNew",
	data : function() {
	     return {
				 items : null
	     }
    },
	created : function() {
		var me = this;

		me.$http.get("whatsnew/latest").then((d) => {
			d = d.data;
			if(d.ok) {
				me.items = d.items;
			} else {
				alert(d.msg);
			}
		});
	},
	computed : {
		...mapGetters([ "user" ])
	},
	methods : {
		marked,
		moment,
		updateWhatsNewStatus() {
			var me = this;
			if(!me.user) return;
			me.$http.post("whatsnew/statusUpdate").then((d) => {
				d = d.data;
				if(d.ok) {
					me.setWhatsNewStatus(d.item);
				}
			});
		},
		...mapActions([ "setWhatsNewStatus" ])
	},
	mounted : function() {
		this.updateWhatsNewStatus();
	},
	watch : {
	},
	components : {	
	}
}
</script>
