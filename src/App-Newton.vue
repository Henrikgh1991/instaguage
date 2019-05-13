<template>
<div @click="() => { this.$root.$emit('appRootClicked') }" :class="'layout-' + appLayout">
	<header>
		<nav class="navbar navbar-default navbar-fixed-top" data-spy="affix" data-offset="78">
			<div class="container">
				<div class="row">
					<div id="navbar-header" class="navbar-header">
						<a class="navbar-brand" href="http://www.siemens.com/">
							<img src="./assets/img/siemens-logo.png"  class="logo">
							<img src="./assets/img/siemens-claim.png" class="logo-claim">
						</a>
					</div>
					<div id="navbar">
						<ul class="nav navbar-nav navbar-right">
							<!--
							<li>
								<button type="button" 
									class="navbar-search-button navbar-toggle collapsed" 
									data-toggle="collapse" data-target="#navbar-search-content" 
									aria-expanded="false" aria-controls="navbar">
									<span class="newton-search"></span>
								</button>
							</li>
							-->
							<li>
								<button type="button" 
									class="navbar-toggle collapsed" 
									data-toggle="collapse" 
									data-target="#navbar-primary" 
									aria-expanded="false" 
									aria-controls="navbar">
									<span class="icon-bar"></span>
									<span class="icon-bar"></span>
									<span class="icon-bar"></span>
								</button>
							</li>
						</ul>
						<ul id="navbar-secondary" class="nav navbar-nav navbar-right phone-collapse">
							<li :class="{ tada : hasNews }">
								<router-link :to="{ name : 'WhatsNew' }" title="What's new?" class="animated slow delay-1s" :class="{ tada : hasNews }">
									<font-awesome-icon icon="rocket" />
									What's new?
								</router-link>
							</li>

							<li>
								<router-link :to="{ name : 'Contact' }">
									<i class="newton-e-mail icon-left"></i>
									<span>Contact</span>
								</router-link>
							</li>

<!--
							<li>
								<a v-on:click="forceLogin"
									:class="{ 'hidden' : (user != null) }">
									<i class="newton-log-in icon-left"></i>
									<span>login</span>
								</a>
							
								<router-link class="person"
									v-if="user != null" :class="{ 'hidden' : (user == null) }"
									:to="{ name : 'Home' }"
									title="show your profile">
									<i class="newton-user-account icon-left"></i>
									<span>{{ user.firstName }} {{ user.lastName }}</span>
								</router-link>
							</li>
							<li>
								<a @click.stop="forceLogout"
									v-if="user != null" :class="{ 'hidden' : (user == null) }">
									<i class="newton-log-out icon-left"></i>
									<span>logout</span>
								</a>
							</li>
-->							
							<li>
								<a v-if="user != null">
									<i class="newton-user-account icon-left"></i>
									<span>{{ user.firstName }} {{ user.lastName }}</span>
								</a>
							</li>
						</ul>	
									
					</div>
				</div>
				<div id="navbar-primary" class="row navbar-collapse collapse">
					<div id="mobile-background" class="mobile-background">
						<div id="nav-mobile" class="nav-mobile">
							<span id="site-identifier" class="site-identifier">{{ appTitle }}</span>
							<ul class="nav navbar-nav navbar-primary">
								<div class="navbar-underline"></div>
								<li class="active" @click.capture="hideNavbar">
									<router-link :to="{ name : 'Home' }">
									Home
									</router-link>
								</li>
								<li class="active" @click.capture="hideNavbar">
									<router-link :to="{ name : 'Create' }">
									Create
									</router-link>
								</li>
								<li class="active" @click.capture="hideNavbar">
									<router-link :to="{ name : 'AdminStart' }">
									Admin
									</router-link>
								</li>
								<li class="active" @click.capture="hideNavbar">
									<router-link :to="{ name : 'FAQ' }">
									FAQ
									</router-link>
								</li>
							</ul>
						</div>
					</div>			
				</div>
			</div>
		</nav>
	</header>

	<router-view></router-view>

	<footer>
		<div class="container">
			<div class="row sn-meta-navigation">
					<a class="hidden-xs hidden-sm hidden-md a3" href="#" title="Application name" target="_blank">{{ appTitle }}</a>
					<span class="p3" style="padding-right: 40px;"> &copy; Siemens AG, 1996 - 2019</span>
					<a href="https://new.siemens.com/global/en/general/legal.html" target="_blank" class="a3">Corporate Information</a>
					<a href="https://new.siemens.com/global/en/general/privacy-notice.html" target="_blank" class="a3">Privacy Policy</a>
					<a href="https://new.siemens.com/global/en/general/cookie-notice.html" target="_blank" class="a3">Cookie Policy</a>
					<a href="https://new.siemens.com/global/en/general/terms-of-use.html" target="_blank" class="a3">Terms of use</a>
					<a href="https://new.siemens.com/global/en/general/digital-id.html" target="_blank" class="a3">Digital ID</a>
			</div>
		</div>
	</footer>
</div>
</template>

<style lang="scss" scoped>
.tada {
	color:#900 !important
}
</style>

<script>
import $ from 'jquery'
import { mapActions, mapGetters } from 'vuex'
import '../node_modules/animate.css/animate.min.css';

window.jQuery = $;

require([ 
	"../node_modules/bootstrap/dist/js/bootstrap.min.js", 
])

require([
	"./assets/styles/simpl-icons-newton.css",
	"./assets/themes/siemens-theme-newton.css",
	"./css/main.scss"
])

export default {
	name : "App",
	data : function() {
		return {
			loggingIn : false,
			menu : null
		}
	},
	created : function() {
		var me = this;

		me.$http.get("whatsnew/status").then((d) => {
			d = d.data;
			if(d.ok) {
				this.setWhatsNewStatus(d.item);
			}
		});
	},
	mounted : function() {
		$(window).click(this.closeAllLayers);
		this.$root.$on("setAppSubtitle", this.setAppSubtitle);
		this.$root.$emit("setAppSubtitle", "");
	},
	computed : {
		hasNews() {
			if(!this.whatsNewStatus) return false;
			return this.whatsNewStatus;
		},
		routeName : function() {
			return this.$route.name;
		},
		...mapGetters([ "appTitle", "user", "appLayout", "whatsNewStatus" ])
	},
	methods : {
		hideNavbar : function() {
			$("#navbar-primary").collapse("hide");
		},
		setAppSubtitle : function(t) {
			document.title = this.appTitle + (t.length ? " - " + t : "");
		},
		closeAllLayers : function(ev) {
			$("div.layer").addClass("hidden");
		},
		closeLayer : function(ev) {
			$(ev.target).parent().addClass("hidden");
		},
		toggleLayer : function(ev) {
			$(ev.target).find("> div.layer").toggleClass("hidden");
		},
		forceLogin : function() {
			window.location.assign(this.$root.loginURL + encodeURIComponent(window.location));
		},
		forceLogout : function() {
			var me = this;

			this.$http.post("user/logout").then((d) => {
				d = d.data;
				if(d.ok) {
					me.setUser(null);
				}
			});
		},
		...mapActions([ "setUser", "setWhatsNewStatus" ])
	},
	watch : {
		user(newV) {
			if(!newV) return;			
		},
	},
}
</script>
