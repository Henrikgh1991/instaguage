import 'babel-polyfill'

import Vue from 'vue'
import VueRouter from 'vue-router'
import VueResource from 'vue-resource'
import App from './App-Newton.vue'
import Home from './Home.vue'
const Create = () => import('./Create.vue');
import Contact from './Contact.vue'
import Details from './components/Details.vue';
const Embed = () => import('./components/Embed.vue');
import Results from './components/Results.vue'
import FAQ from './components/FAQ.vue'
const WhatsNew = () => import('./components/WhatsNew.vue');

import ErrorSurveyNotFound from './components/errors/SurveyNotFound.vue'
const AdminStart = () => import('./components/Admin/Start.vue');
const AdminSurveyEdit = () => import('./components/Admin/Survey/Edit.vue');

import store from './vuex/store.js'

import { library, dom } from '@fortawesome/fontawesome-svg-core'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

library.add(fas);
dom.watch();

Vue.use(VueRouter)
Vue.use(VueResource)
Vue.component('font-awesome-icon', FontAwesomeIcon)

var baseURL = window.location.origin + document.base;
var loginURL = baseURL + "/backend/login/?returnTo=";

Vue.http.options.root = document.base + '/backend/v1';
Vue.http.headers.common['X-CSRF-TOKEN'] = document.csrfToken;
var defaultAppLayout = "newton";

var router = new VueRouter({ 
	mode : "history",
	base : document.base,
	routes : [
		{
			path : "/",
			name : "Home",
			component : Home,
			meta : {
				requireLogin : false
			}
		},
		{
			path : "/create",
			name : "Create",
			component : Create,
			meta : {
				requireLogin : true
			}
		},
		{
			path : "/contact",
			name : "Contact",
			component : Contact,
			meta : {
				requireLogin : false
			}
		},
		{
			path : "/faq/:id?",
			name : "FAQ",
			component : FAQ,
			meta : {
				requireLogin : false
			}
		},
		{
			path : "/whats-new",
			name : "WhatsNew",
			component : WhatsNew,
			meta : {
				requireLogin : false
			}
		},
		{
			path : "/details/:id/:pw",
			name : "Details",
			component : Details,
			meta : {
				requireLogin : true
			}
		},
		{
			path : "/embed/:id",
			name : "Embed",
			component : Embed,
			meta : {
				requireLogin : false,
				appLayout : "minimal",
				isEmbedded : true
			}
		},
		{
			path : "/embed/results/:id",
			name : "EmbedResults",
			component : Results,
			meta : {
				requireLogin : false,
				appLayout : "minimal",
				isEmbedded : true
			}
		},
		{
			path : "/survey/:id",
			name : "Survey",
			component : Embed,
			meta : {
				requireLogin : false,
			}
		},
		{
			path : "/results/:id",
			name : "Results",
			component : Results,
			meta : {
				requireLogin : false,
			}
		},
		{
			path : "/error/surveyNotFound",
			name : "ErrorSurveyNotFound",
			component : ErrorSurveyNotFound,
			meta : {
				requireLogin : false,
			}
		},
		{
			path : "/admin",
			name : "AdminStart",
			component : AdminStart,
			meta : {
				requireLogin : true,
			},
		},
		{
			path : "/admin/survey/:id/edit",
			name : "AdminSurveyEdit",
			component : AdminSurveyEdit,
			meta : {
				requireLogin : true
			},
		},
{
			path : "*",
			redirect : {
				name : "Home"
			}
		}
	]
});

router.beforeEach((to, from, next) => {
	if(to.meta && to.meta.appLayout) {
		store.dispatch("setAppLayout", to.meta.appLayout);
	} else {
		store.dispatch("setAppLayout", defaultAppLayout);
	}
	if(appVue) {
		if(to.meta && to.meta.appSubtitle) {
			appVue.$emit("setAppSubtitle", to.meta.appSubtitle);
		} else {
			appVue.$emit("setAppSubtitle", "");
		}
	}
	if(store.getters.user === null) {
		if(document.user) {
			store.dispatch("setUser", document.user);
			next();
		} else {
			Vue.http.get("user").then((d) => {
				store.dispatch("setUser", d.data);
				if(to.meta.requireLogin && (d.data == null)) {
					window.location.assign(loginURL + encodeURIComponent(baseURL + to.fullPath));
					return;
				}
				next();
			});
		}
	} else {
		next();
	}
});

router.afterEach((to, from) => {
	if((store.getters.user !== null)) {
		// still logged in/valid session?
		Vue.http.get("user").then((d) => {
			if(to.meta.requireLogin && (d.data == null)) {
				window.location.assign(loginURL + encodeURIComponent(baseURL + to.fullPath));
				return;
			}
		});
	}
});

Vue.directive('focus', {
	inserted: function (el) {
		el.focus()
	}
});

var appVue = new Vue({
	router : router,
	store : store,
	data : {
		baseURL : baseURL,
		loginURL : loginURL
	},
	el: '#app',
	render: h => h(App)
});
